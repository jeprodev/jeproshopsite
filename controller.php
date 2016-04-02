<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net

 * @copyright (C)   2009 - 2011
 * @license         http://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of,
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JeproshopController extends JControllerLegacy
{
    public $has_errors = false;

    public $use_ajax = true;

    public $authenticated = false;

    public $isLogged = false;

    public $guest_allowed = false;

    public $default_form_language;

    public $allow_employee_form_language;

    public $allow_link_rewrite;

    protected $ssl_enabled;

    protected $authentication_redirection;

    protected static $initialized = false;

    protected static $_context_params = array();

    protected static $currentCustomerGroups;

    protected $restricted_country = false;

    /**
     * @var bool If true, use routes to build URL (mod rewrite must be activated)
     */
    protected $use_routes = false;

    protected $multilang_activated = false;

    /**
     * @var array List of loaded routes
     */
    protected $routes = array();

    public function display($cachable = FALSE, $urlParams = FALSE){
        $view = $this->input->get('view', 'default');
        $layout = $this->input->get('layout', 'default');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->display();
    }

    public function initContent(){
        if(!$this->viewAccess()) {
            JError::raiseWarning(500, JText::_('COM_JEPROSHOP_YOU_DO_NOT_HAVE_PERMISSION_TO_VIEW_THIS_PAGE_MESSAGE'));
        }

        $this->getLanguages();
        $app = JFactory::getApplication();

        $task = $app->input->get('task');
        $view = $app->input->get('view');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());

        if($task == 'edit'){
            if(!$viewClass->loadObject(true)){ return; }
            $viewClass->setLayout('edit');
            $viewClass->renderEditForm();
        }elseif($task == 'add'){
            $viewClass->setLayout('add');
            $viewClass->renderAddForm();
        }elseif($task == 'view'){
            $viewClass->setLayout('view');
            $viewClass->renderView();
        }elseif($task == 'display' || $task  == ''){
            $viewClass->renderDetails();
        }elseif(!$this->use_ajax){

        }else {
            $this->execute($task);
        }
    }

    public function initialize(){
        if(self::$initialized){ return; }
        self::$initialized = true;

        $app = JFactory::getApplication();
        $context = JeproshopContext::getContext();
        $view = $app->input->get('view', 'default');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());

        if(JeproshopTools::usingSecureMode()){ $this->ssl_enabled = true; }

        if(isset($context->cookie->account_created)){
            $accountCreated = true;
            $viewClass->assignRef('account_created', $accountCreated);
            $context->cookie->account_created = 0;
        }

        JeproshopTools::setCookieLanguage($context->cookie);

        $cart_id = (int)$this->recoverCart();
        if($cart_id){ $context->cookie->cart_id = $cart_id; }

        if($this->authenticated && !$context->customer->isLogged($this->guest_allowed)){
            $app->redirect('index.php?option=com_jeproshop&view=authentication') ; // todo add retun option
        }

        if(JeproshopSettingModelSetting::getValue('enable_geolocation')){
            $defaultCountry = $this->geolocationManagement($context->country);
            if($defaultCountry && JeproshopTools::isLoadedObject($defaultCountry, 'country_id')){
                $context->country = $defaultCountry;
            }
        }
        $currency = JeproshopTools::setCurrency($context->cookie);
/*
        $logout = $app->input->get('logout');
        $myLogout = $app->input->get('mylogout');
        if(isset($logout) || ($context->customer->logged && JeproshopCustomerModelCustomer::isBanned($context->cutomer->customer_id))){
            $context->customer->logout();
            //$app->input->get('')
        }elseif(isset($myLogout)){
            $context->customer->mylogout();
        }
/*
        if((int)$context->cookie->cart_id){
            $cart = new JeproshopCartModelCart($context->cookie->cart_id);
            if($cart->orderExists()){
                $context->cookie->cart_id = null;
                $context->cookie->check_selling_condition = false;
            }elseif((int)JeproshopSettingModelSetting::getValue('enable_geolocation') && !in_array(strtoupper($context->cookie->iso_code_country), explode(';', JeproshopSettingModelSetting::getValue('allowed_countries'))) && $cart->numberOfProducts() && (int)JeproshopSettingModelSetting::getValue('geolocation_behavior') != -1 && !self::isInWhiteListForGeolocation() && !in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))){
                $context->cookie->cart_id = null;
                $cart = null;
            }elseif($context->cookie->customer_id != $cart->customer_id || $context->cookie->lang_id != $cart->lang_id || $currency->currency_id != $cart->currency_id){
                if($context->cookie->customer_id){
                    $cart->customer_id = (int)$context->cookie->customer_id;
                }
                $cart->lang_id = (int)$context->cookie->lang_id;
                $cart->currency_id = (int)$currency->currency_id;
                $cart->update();
            }

            if(isset($cart) && (!isset($cart->address_delivery_id) || $cart->address_delivery_id == 0 || !isset($cart->address_invoice_id) || $cart->address_invoice_id) && $context->cookie->customer_id){
                $toUpdate = false;
                if(!isset($cart->address_delivery_id) || $cart->address_delivery_id == 0){
                    $toUpdate = true;
                    $cart->address_delivery_id = (int)JeproshopAddressModelAddress::getCustomerFirstAddressId($cart->customer_id);
                }

                if(!isset($cart->address_invoice_id) || $cart->address_invoice_id == 0){
                    $toUpdate = true;
                    $cart->address_invoice_id = (int)JeproshopAddressModelAddress::getCustomerFirstAddressId($cart->customer_id);
                }

                if($toUpdate){ $cart->update(); }
            }
        }

        if(!isset($cart) || $cart->cart_id){
            $cart = new JeproshopCartModelCart();
            $cart->lang_id = (int)$context->cookie->lang_id;
            $cart->currency_id = (int)$context->cookie->currency_id;
            $cart->guest_id = (int)($context->cookie->guest_id);
            $cart->shop_group_id = (int)$context->shop->shop_group_id;
            $cart->shop_id = $context->shop->shop_id;
            if ($context->cookie->customer_id){
                $cart->customer_id = (int)($context->cookie->id_customer);
                $cart->address_delivery_id = (int)(JeproshopAddressModelAddress::getCustomerFirstAddressId($cart->customer_id));
                $cart->address_invoice_id = $cart->address_delivery_id;
            } else{
                $cart->address_delivery_id = 0;
                $cart->address_invoice_id = 0;
            }

            // Needed if the merchant want to give a free product to every visitors
            $context->cart = $cart;
            JeproshopCartRuleModelCartRule::autoAddToCart($context);
        }else{
            $context->cart = $cart;
        }
/*
        JeproshopProductModelProduct::initPricesComputation();
        $display_tax_label = $context->country->display_tax_label;
        if(isset($cart->{JeproshopSettingModelSetting::getValue('tax_address_type')}) && $cart->{JeproshopSettingModelSetting::getValue('tax_address_type')}){
            $info = JeproshopAddressModelAddress::getCountryAndState($cart->{JeproshopSettingModelSetting::getValue('tax_address_type')});
            $country = new JeproshopCountryModelCountry((int)$info->country_id);
            $context->country = $country;
            if(JeproshopTools::isLoadedObject($country, 'country_id')){
                $display_tax_label = $country->display_tax_label;
            }
        }

        $languages = JeproshopLanguageModelLanguage::getLanguages(true);
        $meta_language = array();
        foreach($languages as $lang){
            $meta_language[] = $lang->iso_code;
        }

        $compared_products = array();
        $comparatorMaxItem = JeproshopSettingModelSetting::getValue('comparator_max_item');
        if( $comparatorMaxItem && isset($context->cookie->compare_id)){
            $compared_products = JeproshopProductComparedModelProductCompared::getComparedProducts($context->cookie->compare_id);
        }
/*
        $mobileDevice =  $context->getMobileDevice();
        $viewClass->assignRef('mobile_device', $mobileDevice);
        $viewClass->assignRef('cart', $cart);
        $viewClass->assignRef('currency', $currency);
        $viewClass->assignRef('display_tax_label', $display_tax_label);
        $isLogged = (bool)$context->customer->isLogged();
        $viewClass->assignRef('is_logged', $isLogged);
        $isGuest = (bool)$context->customer->isGuest();
        $viewClass->assignRef('is_guest', $isGuest);
        $priceRoundMode = JeproshopSettingModelSetting::getValue('price_round_mode');
        $viewClass->assignRef('price_round_mode', $priceRoundMode);
        $useTax = JeproshopSettingModelSetting::getValue('use_tax');
        $viewClass->assignRef('use_taxes', $useTax);
        $showTax = (int)JeproshopSettingModelSetting::getValue('display_tax') == 1 && JeproshopSettingModelSetting::getValue('use_tax');
        $viewClass->assignRef('show_tax', $showTax);
        $catalogMode = (bool)JeproshopSettingModelSetting::getValue('catalog_mode') || !JeproshopGroupModelGroup::getCurrent()->show_prices;
        $viewClass->assignRef('catalog_mode', $catalogMode);
        $enableB2bMode = (bool)JeproshopSettingModelSetting::getValue('enable_b2b_mode');
        $viewClass->assignRef('enable_b2b_mode', $enableB2bMode);
        $stockManagement = JeproshopSettingModelSetting::getValue('stock_management');
        $viewClass->assignRef('stock_management', $stockManagement);
        $metaLanguages = implode(',', $meta_language);
        $viewClass->assignRef('meta_languages', $metaLanguages);
        $viewClass->assignRef('languages', $languages);
        $numberOfProducts = $cart->numberOfProducts();
        $viewClass->assignRef('cart_quantities', $numberOfProducts);
        $currencies = JeproshopCurrencyModelCurrency::getCurrencies();
        $viewClass->assignRef('currencies', $currencies);
        $comparatorMaxItem = JeproshopSettingModelSetting::getValue('comparator_max_item');
        $viewClass->assignRef('comparator_max_item', $comparatorMaxItem);
        $quickView = (bool)JeproshopSettingModelSetting::getValue('quick_view');
        $viewClass->assignRef('quick_view', $quickView);
        $restrictedCountryMode = false;
        $viewClass->assignRef('restricted_country_mode', $restrictedCountryMode);
        $displayPrice = JeproshopProductModelProduct::getTaxCalculationMethod((int)$context->cookie->customer_id);
        $viewClass->assignRef('display_price', $displayPrice);
        /*$viewClass->assignRef('');
        $viewClass->assignRef('');
        $viewClass->assignRef('');* /
        $viewClass->assignRef('compared_products', $compared_products);
        /*$viewClass->assignRef('comparator_max_item', $comparatorMaxItem); */
    }

    protected function recoverCart(){
        $app = JFactory::getApplication();
        $cart_id = (int)$app->input->get('recover_cart');
        $context = JeproshopContext::getContext();
        $is_cart_token = JeproshopTools::getCartToken() == md5(COM_JEPROSHOP_COOKIE_KEY . 'recover_cart' . $cart_id);
        if($cart_id && $is_cart_token){
            $cart = new JeproshopCartModelCart((int)$cart_id);
            if(JeproshopTools::isLoadedObject($cart, 'cart_id')){
                $customer = new JeproshopCustomerModelCustomer((int)$cart->customer_id);
                if(JeproshopTools::isLoadedObject($customer, 'customer_id')){
                    $customer->logged = true;
                    $context->customer = $customer;
                    $context->cookie->customer_id = (int)$customer->customer_id;
                    $context->cookie->customer_lastname = $customer->lastname;
                    $context->cookie->customer_firstname = $customer->firstname;
                    $context->cookie->logged = 1;
                    $context->cookie->check_selling_conition = 1;
                    $context->cookie->is_guest = $customer->isGuest();
                    $context->cookie->passwd = $customer->passwd;
                    $context->cookie->email = $customer->email;
                    return $cart_id;
                }
            }else{
                return false;
            }
        }
    }

    //TODO correct it
    protected function geolocationManagement($default_country) {
        $context = JeproshopContext::getContext();
        if (!in_array($_SERVER['SERVER_NAME'], array('localhost', '127.0.0.1'))){
            /* Check if Maxmind Database exists */
            if (file_exists(COM_JEPROSHOP_GEOIP_DIR . 'GeoLiteCity.dat')){
                if (!isset($context->cookie->iso_code_country) || (isset($context->cookie->iso_code_country) && !in_array(strtoupper($context->cookie->iso_code_country), explode(';', JeproshopSettingModelSetting::getValue('allowed_countries'))))){
                    include_once(COM_JEPROSHOP_GEOIP_DIR .'geoipcity.inc');

                    $gi = geoip_open(realpath(COM_JEPROSHOP_GEOIP_DIR .'GeoLiteCity.dat'), COM_JEPROSHOP_GEOIP_STANDARD);
                    $record = geoip_record_by_addr($gi, Tools::getRemoteAddr());

                    if (is_object($record)){
                        if (!in_array(strtoupper($record->country_code), explode(';', JeproshopSettingModelSetting::getValue('allowed_countries'))) && !self::isInWhitelistForGeolocation()){
                            if (JeproshopSettingModelSetting::getValue('geolocation_behavior') == COM_JEPROSHOP_GEOLOCATION_NO_CATALOG)
                                $this->restricted_country = true;
                            elseif (JeproshopSettingModelSetting::getValue('PS_GEOLOCATION_BEHAVIOR') == COM_JEPROSHOP_GEOLOCATION_NO_ORDER)
                                $context->smarty->assign(array(
                                    'restricted_country_mode' => true,
                                    'geolocation_country' => $record->country_name
                                ));
                        }else {
                            $has_been_set = !isset($context->cookie->iso_code_country);
                            $context->cookie->iso_code_country = strtoupper($record->country_code);
                        }
                    }
                }

                if (isset($context->cookie->iso_code_country) && $context->cookie->iso_code_country && !Validate::isLanguageIsoCode($this->context->cookie->iso_code_country))
                    $this->context->cookie->iso_code_country = Country::getIsoById(JeproshopSettingModelSetting::get('PS_COUNTRY_DEFAULT'));
                if (isset($this->context->cookie->iso_code_country) && ($id_country = Country::getByIso(strtoupper($this->context->cookie->iso_code_country))))
                {
                    /* Update defaultCountry */
                    if ($default_country->iso_code != $this->context->cookie->iso_code_country)
                        $default_country = new Country($id_country);
                    if (isset($has_been_set) && $has_been_set)
                        $this->context->cookie->id_currency = (int)(Currency::getCurrencyInstance($default_country->id_currency ? (int)$default_country->id_currency : Configuration::get('PS_CURRENCY_DEFAULT'))->id);
                    return $default_country;
                }
                elseif (JeproshopSettingModelSetting::get('PS_GEOLOCATION_NA_BEHAVIOR') == _PS_GEOLOCATION_NO_CATALOG_ && !FrontController::isInWhitelistForGeolocation())
                    $this->restricted_country = true;
                elseif (JeproshopSettingModelSetting::get('PS_GEOLOCATION_NA_BEHAVIOR') == _PS_GEOLOCATION_NO_ORDER_ && !FrontController::isInWhitelistForGeolocation())
                    $this->context->smarty->assign(array(
                        'restricted_country_mode' => true,
                        'geolocation_country' => 'Undefined'
                    ));
            }
            /* If not exists we disabled the geolocation feature */
            else
                JeproshopSettingModelSetting::updateValue('PS_GEOLOCATION_ENABLED', 0);
        }
        return false;
    }

    protected function canonicalRedirection($canonical_url = ''){
        if (!$canonical_url || !JeproshopSettingModelSetting::get('PS_CANONICAL_REDIRECT') || strtoupper($_SERVER['REQUEST_METHOD']) != 'GET' || Tools::getValue('live_edit'))
            return;

        $match_url = rawurldecode(Tools::getCurrentUrlProtocolPrefix().$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        if (!preg_match('/^'.Tools::pRegexp(rawurldecode($canonical_url), '/').'([&?].*)?$/', $match_url))
        {
            $params = array();
            $str_params = '';
            $url_details = parse_url($canonical_url);

            if (!empty($url_details['query']))
            {
                parse_str($url_details['query'], $query);
                foreach ($query as $key => $value)
                    $params[Tools::safeOutput($key)] = Tools::safeOutput($value);
            }
            $excluded_key = array('isolang', 'id_lang', 'controller', 'fc', 'id_product', 'id_category', 'id_manufacturer', 'id_supplier', 'id_cms');
            foreach ($_GET as $key => $value)
                if (!in_array($key, $excluded_key) && Validate::isUrl($key) && Validate::isUrl($value))
                    $params[Tools::safeOutput($key)] = Tools::safeOutput($value);

            $str_params = http_build_query($params, '', '&');
            if (!empty($str_params))
                $final_url = preg_replace('/^([^?]*)?.*$/', '$1', $canonical_url).'?'.$str_params;
            else
                $final_url = preg_replace('/^([^?]*)?.*$/', '$1', $canonical_url);

            // Don't send any cookie
            Context::getContext()->cookie->disallowWriting();

            if (defined('_PS_MODE_DEV_') && _PS_MODE_DEV_ && $_SERVER['REQUEST_URI'] != __PS_BASE_URI__)
                die('[Debug] This page has moved<br />Please use the following URL instead: <a href="'.$final_url.'">'.$final_url.'</a>');

            $redirect_type = Configuration::get('PS_CANONICAL_REDIRECT') == 2 ? '301' : '302';
            header('HTTP/1.0 '.$redirect_type.' Moved');
            header('Cache-Control: no-cache');
            Tools::redirectLink($final_url);
        }
    }

    public function getLanguages(){
        $cookie = JeproshopContext::getContext()->cookie;
        $this->allow_employee_form_language = (int)JeproshopSettingModelSetting::getValue('allow_employee_form_lang');
        if($this->allow_employee_form_language && !$cookie->employee_form_lang){
            $cookie->employee_form_lang = (int)JeproshopSettingModelSetting::getValue('default_lang');
        }

        $lang_exists = false;
        $languages = JeproshopLanguageModelLanguage::getLanguages(false);
        foreach($languages as $language){
            if(isset($cookie->employee_form_language) && $cookie->employee_form_language == $language->lang_id){
                $lang_exists = true;
            }
        }

        $this->default_form_language = $lang_exists ? (int)$cookie->employee_form_language : (int)JeproshopSettingModelSetting::getValue('default_lang');

        return $languages;
    }

    public static function getCurrentCustomerGroups() {
        if (!JeproshopGroupModelGroup::isFeaturePublished())
            return array();

        $context = JeproshopContext::getContext();
        if (!isset($context->customer) || !$context->customer->customer_id){ return array(); }

        if (!is_array(self::$currentCustomerGroups)){
            self::$currentCustomerGroups = array();
            $db = JFactory::getDBO();
            $query = "SELECT group_id FROM " . $db->quoteName('#__jeproshop_customer_group') . " WHERE customer_id = " .(int)$context->customer->customer_id;
            $db->setQuery($query);
            $result = $db->loadObjectList();
            foreach ($result as $row)
                self::$currentCustomerGroups[] = $row->group_id;
        }
        return self::$currentCustomerGroups;
    }

    /**
     * Create a link to a product
     *
     * @param mixed $product Product object (can be an ID product, but deprecated)
     * @param string $alias
     * @param string $category
     * @param string $ean13
     * @param null $lang_id
     * @param null $shop_id
     * @param int $product_attribute_id ID product attribute
     * @param bool $force_routes
     * @throws JException
     * @internal param int $id_lang
     * @internal param int $id_shop (since 1.5.0) ID shop need to be used when we generate a product link for a product in a cart
     * @return string
     */
    public function getProductLink($product, $alias = null, $category = null, $ean13 = null, $lang_id = null, $shop_id = null, $product_attribute_id = 0, $force_routes = false)
    {
        if (!$lang_id) {
            $lang_id = JeproshopContext::getContext()->language->lang_id;
        }

        if (!is_object($product)) {
            if (is_array($product) && isset($product['product_id'])) {
                $product = new JeproshopProductModelProduct($product['product_id'], false, $lang_id, $shop_id);
            } elseif ((int)$product) {
                $product = new JeproshopProductModelProduct((int)$product, false, $lang_id, $shop_id);
            } else {
                throw new JException(JText::_('COM_JEPROSHOP_INVALID_PRODUCT_VARS_MESSAGE'));
            }
        }

        // Set available keywords
        $anchor = '&task=view&product_id=' . $product->product_id .  ((!$alias) ? '&rewrite=' . $product->getFieldByLang('link_rewrite') : $alias) . ((!$ean13) ? '&ean13=' . $product->ean13 : $ean13);
        $anchor .= '&meta_keywords=' . JeproshopTools::str2url($product->getFieldByLang('meta_keywords')) . '&meta_title=' . JeproshopTools::str2url($product->getFieldByLang('meta_title'));

        if ($this->hasKeyword('product', $lang_id, 'manufacturer', $shop_id)) {
            $params['manufacturer'] = JeproshopTools::str2url($product->isFullyLoaded ? $product->manufacturer_name : JeproshopManufacturerModelManufacturer::getNameById($product->manufacturer_id));
        }
        if ($this->hasKeyword('product', $lang_id, 'supplier', $shop_id)) {
            $params['supplier'] = JeproshopTools::str2url($product->isFullyLoaded ? $product->supplier_name : JeproshopSupplierModelSupplier::getNameById($product->supplier_id));
        }
        if ($this->hasKeyword('product', $lang_id, 'price', $shop_id)) {
            $params['price'] = $product->isFullyLoaded ? $product->price : JeproshopProductModelProduct::getStaticPrice($product->product_id, false, null, 6, null, false, true, 1, false, null, null, null, $product->specific_price);
        }
        if ($this->hasKeyword('product', $lang_id, 'tags', $shop_id)) {
            $params['tags'] = JeproshopTools::str2url($product->getTags($lang_id));
        }
        if ($this->hasKeyword('product', $lang_id, 'category', $shop_id)) {
            $params['category'] = (!is_null($product->category) && !empty($product->category)) ? JeproshopTools::str2url($product->category) : JeproshopTools::str2url($category);
        }
        if ($this->hasKeyword('product', $lang_id, 'reference', $shop_id)) {
            $params['reference'] = JeproshopTools::str2url($product->reference);
        }

        if ($this->hasKeyword('product', $lang_id, 'categories', $shop_id))
        {
            $params['category'] = (!$category) ? $product->category : $category;
            $cats = array();
            foreach ($product->getParentCategories() as $cat)
                if (!in_array($cat->category_id, Link::$category_disable_rewrite))//remove root and home category from the URL
                    $cats[] = $cat->link_rewrite;
            $params['categories'] = implode('/', $cats);
        }
        $anchor .= $product_attribute_id ? '&product_attribute_id='  . $product_attribute_id : '';



        return JRoute::_('index.php?option=com_jeproshop&view=product' . $anchor);
    }

    /**
     * Check if a keyword is written in a route rule
     *
     * @param string $route_id
     * @param int $lang_id
     * @param string $keyword
     * @param int $shop_id
     * @return bool
     */
    public function hasKeyword($route_id, $lang_id, $keyword, $shop_id = null) {
        if ($shop_id === null){
            $shop_id = (int)JeproshopContext::getContext()->shop->shop_id;
        }
        /*if (!isset($this->routes[$shop_id])){
            $this->loadRoutes($shop_id);
        }*/
        if (!isset($this->routes[$shop_id]) || !isset($this->routes[$shop_id][$lang_id]) || !isset($this->routes[$shop_id][$lang_id][$route_id]))
            return false;

        return preg_match('#\{([^{}]*:)?'.preg_quote($keyword, '#').'(:[^{}]*)?\}#', $this->routes[$shop_id][$lang_id][$route_id]['rule']);
    }


    /**
     * Returns a link to a product image for display
     * Note: the new image filesystem stores product images in subdirectories of img/p/
     *
     * @param string $name rewrite link of the image
     * @param string $ids id part of the image filename - can be "id_product-id_image" (legacy support, recommended) or "id_image" (new)
     * @param string $type
     * @return string
     */
    public function getImageLink($name, $ids, $type = null){
        $not_default = false;
        if(is_array($name)){ $name = $name[JeproshopContext::getContext()->language->lang_id]; }
        // legacy mode or default image
        $theme = ((JeproshopShopModelShop::isFeaturePublished() && file_exists(COM_JEPROSHOP_PRODUCT_IMAGE_DIRECTORY . $ids . ($type ? '_'.$type : '').'_'.(int)JeproshopContext::getContext()->shop->theme_id .'.jpg')) ? '_'.JeproshopContext::getContext()->shop->theme_id : '');
        if ((JeproshopSettingModelSetting::getValue('legacy_images') && (file_exists(COM_JEPROSHOP_PRODUCT_IMAGE_DIRECTORY . $ids . ($type ? '-'.$type : '').$theme.'.jpg'))) || ($not_default = strpos($ids, 'default') !== false)) {
            if ($this->allow_link_rewrite == 1 && !$not_default){ echo $name;
                $uri_path = JURI::base() . $ids.($type ? '_'.$type : '').$theme.'/'.$name.'.jpg';
            }else{
                $uri_path = JURI::base() . 'components/com_jeproshop/assets/themes/' . $ids.($type ? '_'.$type : '').$theme.'.jpg';
            }
        } else {
            // if ids if of the form product_id-id_image, we want to extract the id_image part
            $split_ids = explode('_', $ids);
            $image_id = (isset($split_ids[1]) ? $split_ids[1] : $split_ids[0]);
            $theme = ((JeproshopShopModelShop::isFeaturePublished() && file_exists(COM_JEPROSHOP_PRODUCT_IMAGE_DIRECTORY . JeproshopImageModelImage::getStaticImageFolder($image_id). $image_id .($type ? '_'.$type : '').'_'.(int)JeproshopContext::getContext()->shop->theme_id . '.jpg')) ? '_'. JeproshopContext::getContext()->shop->theme_id : '');
            if ($this->allow_link_rewrite == 1){
                $uri_path = JURI::base() . $image_id .($type ? '_'.$type : '').$theme.'/'.$name.'.jpg';
            }else{
                $uri_path = JURI::base() . COM_JEPROSHOP_PRODUCT_IMAGE_DIRECTORY . JeproshopImageModelImage::getStaticImageFolder($image_id).$image_id .($type ? '_'.$type : '').$theme.'.jpg';
            }
        }
        //return JeproshopTools::getMediaServer($uri_path).$uri_path;
        return $uri_path;
    }

    /**
     * Create a link to a category
     *
     * @param mixed $category Category object (can be an ID category, but deprecated)
     * @param string $alias
     * @param int $lang_id
     * @param string $selected_filters Url parameter to auto check filters of the module mod_block_layered
     * @param null $shop_id
     * @return string
     */
    public function getCategoryLink($category, $alias = null, $lang_id = null, $selected_filters = null, $shop_id = null){
        if (!$lang_id){ $lang_id = JeproshopContext::getContext()->language->lang_id; }

        $url = "" ; //$this->getBaseLink($shop_id).$this->getLangLink($lang_id, null, $shop_id);

        if (!is_object($category))
            $category = new JeproshopCategoryModelCategory($category, $lang_id);

        // Set available keywords
        $params = array();
        $params['id'] = $category->category_id;
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        $params['meta_keywords'] =	JeproshopTools::str2url($category->getFieldByLang('meta_keywords'));
        $params['meta_title'] = JeproshopTools::str2url($category->getFieldByLang('meta_title'));

        // Selected filters is used by the module blocklayered
        $selected_filters = is_null($selected_filters) ? '' : $selected_filters;

        if (empty($selected_filters))
            $rule = 'category_rule';
        else
        {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selected_filters;
        }

        return $url;  // TODO add query$this->createUrl($rule, $lang_id, $params, $this->allow_link_rewrite, '', $shop_id);
    }

    public function setMedia(){

    }


    private function viewAccess(){ return true; }

    public function isInitialized(){ return self::$initialized; }

    public function isModuleInstalled($moduleName){ return true; }
    public function isModuleEnabled($moduleName){ return true; }
    public function useMobileTheme(){
        return false;
    }

}