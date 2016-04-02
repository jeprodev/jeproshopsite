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

class JeproshopProductViewProduct extends JViewLegacy
{
    public $errors = array();

    public $context = null;

    public $product = null;

    public $category = null;


    public function renderView($tpl = null){
        $app = JFactory::getApplication();
        if(!isset($this->context) && (null == $this->context)){ $this->context = JeproshopContext::getContext(); }

        if(!isset($this->context->cart)){ $this->context->cart = new JeproshopCartModelCart(); }
        if(!$this->context->controller->isInitialized()){ $this->initialize();}

        $useSSL = ((isset($this->context->controller->ssl_enabled) && $this->context->controller->ssl_enabled && $app->input->get('enable_ssl')) || JeproshopTools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https://' : 'http://';

        $this->loadObject();
        if (JeproshopProductPack::isPack((int)$this->product->product_id) && !JeproshopProductPack::isInStock((int)$this->product->product_id)){
            $this->product->quantity = 0;
        }

        $this->product->description = $this->transformDescriptionWithImg($this->product->description);

        // Assign to the template the id of the virtual product. "0" if the product is not downloadable.
        $virtual = JeproshopProductDownloadModelProductDownload::getIdFromProductId((int)$this->product->product_id);
        $this->assignRef('virtual', $virtual);
        $customization_form_target = JeproshopTools::safeOutput(urldecode($_SERVER['REQUEST_URI']));
        $this->assignRef('customization_form_target', $customization_form_target);

        $delete_picture = $app->input->get('delete_picture');
        if ($app->input->get('submit_customized_datas')){
            // If cart has not been saved, we need to do it so that customization fields can have an id_cart
            // We check that the cookie exists first to avoid ghost carts
            if (!$this->context->cart->cart_id && isset($_COOKIE[$this->context->cookie->getName()])) {
                $this->context->cart->add();
                $this->context->cookie->cart_id = (int)$this->context->cart->cart_id;
            }
            $this->pictureUpload();
            $this->textRecord();
            $this->formTargetFormat();
        } else if (isset($delete_picture) && !$this->context->cart->deleteCustomizationToProduct($this->product->product_id, $app->input->get('delete_picture')))
            $this->errors[] = JText::_('An error occurred while deleting the selected picture.');

        $pictures = array();
        $text_fields = array();
        if ($this->product->customizable){
            $files = $this->context->cart->getProductCustomization($this->product->product_id, JeproshopProductModelProduct::CUSTOMIZE_FILE, true);
            foreach ($files as $file)
                $pictures['pictures_'.$this->product->product_id.'_'.$file['index']] = $file['value'];

            $texts = $this->context->cart->getProductCustomization($this->product->product_id, JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD, true);

            foreach ($texts as $text_field)
                $text_fields['textFields_'.$this->product->product_id.'_'.$text_field['index']] = str_replace('<br />', "\n", $text_field['value']);
        }

        $this->assignRef('pictures', $pictures);
        $this->assignRef('textFields', $text_fields);

        $this->product->customization_required = false;
        $customizationFields = $this->product->customizable ? $this->product->getCustomizationFields($this->context->language->lang_id) : false;
        if (is_array($customizationFields))
            foreach($customizationFields as $customizationField)
                if ($this->product->customization_required = $customizationField['required'])
                    break;

        // Assign template vars related to the category + execute hooks related to the category
        $this->assignCategory();
        // Assign template vars related to the price and tax
        $this->assignPriceAndTax();

        // Assign template vars related to the images
        $this->assignImages();
        // Assign attribute groups to the template
        $this->assignAttributesGroups();

        // Assign attributes combinations to the template
        $this->assignAttributesCombinations();

        // Pack management
        $pack_items = $this->product->cache_is_pack ? JeproshopProductPack::getItemTable($this->product->product_id, $this->context->language->lang_id, true) : array();
        $this->assignRef('packItems', $pack_items);
        $packs = JeproshopProductPack::getPacksTable($this->product->product_id, $this->context->language->lang_id, true, 1);
        $this->assignRef('packs', $packs);

        if (isset($this->category->category_id) && $this->category->category_id)
            $return_link = JeproshopTools::safeOutput($this->context->controller->getCategoryLink($this->category));
        else
            $return_link = 'javascript: history.back();';
        if(!$this->context->controller->useMobileTheme()){

        }

        //'stock_management' => Configuration::get('PS_STOCK_MANAGEMENT'),
        //		'customizationFields' => $customizationFields,
        $accessories = $this->product->getAccessories($this->context->language->lang_id);
        $this->assignRef('accessories', $accessories);
        $enable_jqzoom = JeproshopSettingModelSetting::getValue('enable_jqzoom');
        $this->assignRef('jqZoomEnabled', $enable_jqzoom);
        $manufacturer = new JeproshopManufacturerModelManufacturer((int)$this->product->manufacturer_id, $this->context->language->lang_id);
        $this->assignRef('product_manufacturer', $manufacturer);
        $features = $this->product->getFrontFeatures($this->context->language->lang_id);
        $this->assignRef('features', $features);
        $attachments = (($this->product->cache_has_attachments) ? $this->product->getAttachments($this->context->language->lang_id) : array());
        $this->assignRef('attachments', $attachments);
        $display_discount_price = JeproshopSettingModelSetting::getValue('display_discount_price');
        $this->assignRef('display_discount_price', $display_discount_price);
        $this->assignRef('return_link', $return_link);
        $content_only = $app->input->get('content_only');
        $this->assignRef('content_only', $content_only);
        $last_quantities = JeproshopSettingModelSetting::getValue('last_quantities');
        $this->assignRef('last_quantities', $last_quantities);
        $display_quantities = JeproshopSettingModelSetting::getValue('display_quantities');
        $this->assignRef('display_quantities', $display_quantities);
        $allow_out_of_stock_ordering = JeproshopSettingModelSetting::getValue('allow_out_of_stock_ordering');
        $this->assignRef('allow_out_of_stock_ordering', $allow_out_of_stock_ordering);
        $catalog_mode = (bool)(JeproshopSettingModelSetting::getValue('catalog_mode') || !JeproshopGroupModelGroup::getCurrent()->show_prices);
        $this->assignRef('catalog_mode', $catalog_mode);
        $extra_left = null;
        $this->assignRef('extra_left', $extra_left);
        $extra_right = null;
        $this->assignRef('extra_right', $extra_right);

        parent::display($tpl);
    }

    /**
     * Assign template vars related to images
     */
    protected function assignImages(){
        $app = JFactory::getApplication();
        $images = $this->product->getImages((int)$this->context->cookie->lang_id);
        $product_images = array();

        if(isset($images[0])){ $this->assignRef('mainImage', $images[0]); }
        foreach ($images as $image){
            if ($image->cover){
                $this->assignRef('mainImage', $image);
                $cover = $image;
                $cover->image_id = (JeproshopSettingModelSetting::getValue('legacy_images') ? ($this->product->product_id.'_'.$image->image_id) : $image->image_id);
                $cover->only_image_id = (int)$image->image_id;
            }
            $product_images[(int)$image->image_id] = $image;
        }

        if (!isset($cover)){
            if(isset($images[0])){
                $cover = $images[0];
                $cover->image_id = (JeproshopSettingModelSetting::getValue('legacy_images') ? ($this->product->product_id.'-'.$images[0]->image_id) : $images[0]->image_id);
                $cover->only_image_id = (int)$images[0]->image_id;
            }else{
                $cover = new JObject();
                $cover->image_id = $this->context->language->iso_code . '_default';
                $cover->legend = JText::_('COM_JEPROSHOP_NO_PICTURE_LABEL');
                $cover->title = JText::_('COM_JEPROSHOP_NO_PICTURE_LABEL');
            }
        }
        $size = JeproshopImageModelImage::getSize(JeproshopImageTypeModelImageType::getFormatName('large'));
        $has_image = (isset($cover->image_id) && (int)$cover->image_id) ? array((int)$cover->image_id) : JeproshopProductModelProduct::getCover((int)$app->input->get('product_id'));
        $this->assignRef('has_image', $has_image);

        $this->assignRef('cover', $cover);
        $width = (int)$size->width;
        $this->assignRef('image_width', $width);
        $medium_size = JeproshopImageModelImage::getSize(JeproshopImageTypeModelImageType::getFormatName('medium'));
        $this->assignRef('medium_size', $medium_size);
        $large_size = JeproshopImageModelImage::getSize(JeproshopImageTypeModelImageType::getFormatName('large'));
        $this->assignRef('large_size', $large_size);
        $home_size = JeproshopImageModelImage::getSize(JeproshopImageTypeModelImageType::getFormatName('home'));
        $this->assignRef('home_size', $home_size);
        $cart_size = JeproshopImageModelImage::getSize(JeproshopImageTypeModelImageType::getFormatName('cart'));
        $this->assignRef('cart_size', $cart_size);
        //$this->assignRef('col_img_dir', _PS_COL_IMG_DIR_));

        if (count($product_images)){
            $this->assignRef('images', $product_images);
        }
    }

    protected function formTargetFormat(){
        $customization_form_target = Tools::safeOutput(urldecode($_SERVER['REQUEST_URI']));
        foreach ($_GET as $field => $value)
            if (strncmp($field, 'group_', 6) == 0)
                $customization_form_target = preg_replace('/&group_([[:digit:]]+)=([[:digit:]]+)/', '', $customization_form_target);
        if (isset($_POST['quantity_backup'])){
            $quantity_backup = (int)$_POST['quantity_backup'];
            $this->assignRef('quantity_backup', $quantity_backup);
        }
        $this->assignRef('customization_form_target', $customization_form_target);
    }

    protected function transformDescriptionWithImg($desc) {
        $reg = '/\[img\-([0-9]+)\-(left|right)\-([a-zA-Z0-9-_]+)\]/';
        while (preg_match($reg, $desc, $matches)) {
            $link_lmg = $this->context->controller->getImageLink($this->product->link_rewrite, $this->product->product_id.'-'.$matches[1], $matches[3]);
            $class = $matches[2] == 'left' ? 'class="image_float_left"' : 'class="image_float_right"';
            $html_img = '<img src="'.$link_lmg.'" alt="" '.$class.'/>';
            $desc = str_replace($matches[0], $html_img, $desc);
        }
        return $desc;
    }

    protected function pictureUpload(){
        if (!$field_ids = $this->product->getCustomizationFieldIds())
            return false;
        $authorized_file_fields = array();
        foreach ($field_ids as $field_id)
            if ($field_id['type'] == Product::CUSTOMIZE_FILE)
                $authorized_file_fields[(int)$field_id['id_customization_field']] = 'file'.(int)$field_id['id_customization_field'];
        $indexes = array_flip($authorized_file_fields);
        foreach ($_FILES as $field_name => $file) {
            if (in_array($field_name, $authorized_file_fields) && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
                $file_name = md5(uniqid(rand(), true));
                if ($error = ImageManager::validateUpload($file, (int)Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE')))
                    $this->errors[] = $error;

                $product_picture_width = (int)Configuration::get('PS_PRODUCT_PICTURE_WIDTH');
                $product_picture_height = (int)Configuration::get('PS_PRODUCT_PICTURE_HEIGHT');
                $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');
                if ($error || (!$tmp_name || !move_uploaded_file($file['tmp_name'], $tmp_name)))
                    return false;
                /* Original file */
                if (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_ . $file_name))
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                /* A smaller one */
                elseif (!ImageManager::resize($tmp_name, _PS_UPLOAD_DIR_ . $file_name . '_small', $product_picture_width, $product_picture_height))
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                elseif (!chmod(_PS_UPLOAD_DIR_ . $file_name, 0777) || !chmod(_PS_UPLOAD_DIR_ . $file_name . '_small', 0777))
                    $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
                else
                    $this->context->cart->addPictureToProduct($this->product->id, $indexes[$field_name], Product::CUSTOMIZE_FILE, $file_name);
                unlink($tmp_name);
            }
        }
        return true;
    }

    protected function textRecord() {
        if (!$field_ids = $this->product->getCustomizationFieldIds())
            return false;

        $authorized_text_fields = array();
        foreach ($field_ids as $field_id)
            if ($field_id['type'] == Product::CUSTOMIZE_TEXTFIELD)
                $authorized_text_fields[(int)$field_id['id_customization_field']] = 'textField'.(int)$field_id['id_customization_field'];

        $indexes = array_flip($authorized_text_fields);
        foreach ($_POST as $field_name => $value)
            if (in_array($field_name, $authorized_text_fields) && $value != '')
            {
                if (!Validate::isMessage($value))
                    $this->errors[] = Tools::displayError('Invalid message');
                else
                    $this->context->cart->addTextFieldToProduct($this->product->id, $indexes[$field_name], Product::CUSTOMIZE_TEXTFIELD, $value);
            }
            else if (in_array($field_name, $authorized_text_fields) && $value == '')
                $this->context->cart->deleteCustomizationToProduct((int)$this->product->id, $indexes[$field_name]);
    }

    /**
     * Assign template vars related to category
     */
    protected function assignCategory(){
        // Assign category to the template
        if ($this->category !== false && JeproshopTools::isLoadedObject($this->category, 'category_id') && $this->category->inShop() && $this->category->isAssociatedToShop()){
            $path = JeproshopTools::getPath($this->category->category_id, $this->product->name, true);
        }elseif (JeproshopCategoryModelCategory::inShopStatic($this->product->default_category_id, $this->context->shop)) {
            $this->category = new JeproshopCategoryModelCategory((int)$this->product->default_category_id, (int)$this->context->language->lang_id);
            if (JeproshopTools::isLoadedObject( $this->category, 'category_id') &&  $this->category->published &&  $this->category->isAssociatedToShop()){
                $path = JeproshopTools::getPath((int)$this->product->default_category_id, $this->product->name[$this->context->language->lang_id]);
            }
        }
        if (!isset($path) || !$path)
            $path = JeproshopTools::getPath((int)$this->context->shop->category_id, $this->product->name[$this->context->language->lang_id]);

        $subCategories = array();
        if (JeproshopTools::isLoadedObject($this->category, 'category_id')) {
            $subCategories = $this->category->getSubCategories($this->context->language->lang_id, true);

            // various assignments before Hook::exec
            $this->assignRef('path', $path);
            $this->assignRef('category', $this->category);
            $this->assignRef('sub_categories', $subCategories);
            $this->assignRef('current_category_id', $this->category->category_id);
            $this->assignRef('parent_category_id', $this->category->parent_id);
            $return_category_name = JeproshopTools::safeOutput($this->category->getFieldByLang('name'));
            $this->assignRef('return_category_name', $return_category_name);
            $categories = JeproshopCategoryModelCategory::getHomeCategories($this->context->language->lang_id, true, (int)$this->context->shop->shop_id);
            $this->assignRef('categories', $categories);

        }
        //$this->context->smarty->assign(array('HOOK_PRODUCT_FOOTER' => Hook::exec('displayFooterProduct', array('product' => $this->product, 'category' => $this->category))));
    }

    /**
     * Assign template vars related to attribute groups and colors
     */
    protected function assignAttributesGroups()  {
        $colors = array();
        $groups = array();
        if($this->context == null){ $this->context = JeproshopContext::getContext(); }

        // @todo (RM) should only get groups and not all declination ?
        $attributes_groups = $this->product->getAttributesGroups($this->context->language->lang_id);
        if (is_array($attributes_groups) && $attributes_groups){
            $combination_images = $this->product->getCombinationImages($this->context->language->lang_id);
            $combination_prices_set = array();
            $combinations = array();
            foreach ($attributes_groups as $k => $row){
                // Color management
                if (isset($row->is_color_group) && $row->is_color_group && (isset($row->attribute_color) && $row->attribute_color) || (file_exists(JURI::base() . COM_JEPROSHOP_COLOR_IMAGE_DIRECTORY .$row->attribute_id .'.jpg'))) {
                    $colors[$row->attribute_id]['value'] = $row->attribute_color;
                    $colors[$row->attribute_id]['name'] = $row->attribute_name;
                    if (!isset($colors[$row->attribute_id]['attributes_quantity'])) {
                        $colors[$row->attribute_id]['attributes_quantity'] = 0;
                    }
                    $colors[$row->attribute_id]['attributes_quantity'] += (int)$row->quantity;
                }
                if (!isset($groups[$row->attribute_group_id])){
                    $attribute_row = new JObject();
                    $attribute_row->set('group_name', $row->group_name);
                    $attribute_row->set('name', $row->public_group_name);
                    $attribute_row->set('group_type', $row->group_type);
                    $attribute_row->set('default', -1);
                    $groups[$row->attribute_group_id] = $attribute_row;
                }

                $groups[$row->attribute_group_id]->attributes[$row->attribute_id] = $row->attribute_name;
                if ($row->default_on && $groups[$row->attribute_group_id]->default == -1)
                    $groups[$row->attribute_group_id]->default = (int)$row->attribute_id;
                if (!isset($groups[$row->attribute_group_id]->attributes_quantity[$row->attribute_id]))
                    $groups[$row->attribute_group_id]->attributes_quantity[$row->attribute_id] = 0;
                $groups[$row->attribute_group_id]->attributes_quantity[$row->attribute_id] += (int)$row->quantity;

                $data = new JObject();
                $combinations[$row->product_attribute_id]->attributes_values[$row->attribute_group_id] = $row->attribute_name;
                $combinations[$row->product_attribute_id]->attributes[] = (int)$row->attribute_id;
                $combinations[$row->product_attribute_id]->price = (float)$row->price;

                // Call getPriceStatic in order to set $combination_specific_price
                if (!isset($combination_prices_set[(int)$row->product_attribute_id])){
                    $combination_specific_price = null;
                    JeproshopProductModelProduct::getStaticPrice((int)$this->product->product_id, false, $row->product_attribute_id, 6, null, false, true, 1, false, null, null, null, $combination_specific_price);
                    $combination_prices_set[(int)$row->product_attribute_id] = true;
                    $combinations[$row->product_attribute_id]->specific_price = $combination_specific_price;
                }
                $combinations[$row->product_attribute_id]->ecotax = (float)$row->ecotax;
                $combinations[$row->product_attribute_id]->weight = (float)$row->weight;
                $combinations[$row->product_attribute_id]->quantity = (int)$row->quantity;
                $combinations[$row->product_attribute_id]->reference = $row->reference;
                $combinations[$row->product_attribute_id]->unit_impact = $row->unit_price_impact;
                $combinations[$row->product_attribute_id]->minimal_quantity = $row->minimal_quantity;
                if ($row->available_date != '0000-00-00'){
                    $combinations[$row->product_attribute_id]->available_date = $row->available_date;
                    $combinations[$row->product_attribute_id]->date_formatted = JeproshopTools::displayDate($row->available_date);
                } else{
                    $combinations[$row->product_attribute_id]->available_date = '';
                }

                if (!isset($combination_images[$row->product_attribute_id][0]->image_id)){
                    $combinations[$row->product_attribute_id]->image_id = -1;
                }else{
                    $combinations[$row->product_attribute_id]->image_id = $image_id = (int)$combination_images[$row->product_attribute_id][0]->image_id;
                    if ($row->default_on){
                        if (isset($this->cover->value))
                            $current_cover = $this->cover->value;

                        if (is_array($combination_images[$row->product_attribute_id])){
                            foreach ($combination_images[$row->product_attribute_id] as $tmp)
                                if ($tmp->image_id == $current_cover->image_id) {
                                    $combinations[$row->product_attribute_id]->image_id = $image_id = (int)$tmp->image_id;
                                    break;
                                }
                        }

                        if ($image_id > 0)  {
                            if (isset($this->images->value))
                                $product_images = $this->images->value;
                            if (isset($product_images) && is_array($product_images) && isset($product_images[$image_id])){
                                $product_images[$image_id]->cover = 1;
                                $this->assignRef('mainImage', $product_images[$image_id]);
                                if (count($product_images)){
                                    $this->assignRef('images', $product_images);
                                }
                            }
                            if (isset($this->cover->value))
                                $cover = $this->cover->value;
                            if (isset($cover) && is_array($cover) && isset($product_images) && is_array($product_images))
                            {
                                $product_images[$cover->image_id]->cover = 0;
                                if (isset($product_images[$image_id]))
                                    $cover = $product_images[$image_id];
                                $cover->image_id = (JeproshopSettingModelSetting::getValue('legacy_images') ? ($this->product->product_id.'_'.$image_id) : (int)$image_id);
                                $cover->image_only_id = (int)$image_id;
                                $this->assignRef('cover', $cover);
                            }
                        }
                    }
                }
            }

            // wash attributes list (if some attributes are unavailable and if allowed to wash it)
            if (!JeproshopProductModelProduct::isAvailableWhenOutOfStock($this->product->out_of_stock) && JeproshopSettingModelSetting::getValue('display_unavailable_attributes') == 0) {
                foreach ($groups as &$group)
                    foreach ($group->attributes_quantity as $key => &$quantity)
                        if ($quantity <= 0)
                            unset($group->attributes[$key]);

                foreach ($colors as $key => $color)
                    if ($color->attributes_quantity <= 0)
                        unset($colors[$key]);
            }
            foreach ($combinations as $product_attribute_id => $comb)  {
                $attribute_list = '';
                foreach ($comb->attributes as $attribute_id)
                    $attribute_list .= '\''.(int)$attribute_id.'\',';
                $attribute_list = rtrim($attribute_list, ',');
                $combinations[$product_attribute_id]->list = $attribute_list;
            }

            $this->assignRef('groups', $groups);
            $isColors = (count($colors)) ? $colors : false;
            $this->assignRef('colors', $isColors);
            $this->assignRef('combinations', $combinations);
            $this->assignRef('combinationImages', $combination_images);
        }
    }

    /**
     * Get and assign attributes combinations informations
     */
    protected function assignAttributesCombinations() {
        $attributes_combinations = JeproshopProductModelProduct::getAttributesInformationsByProduct($this->product->product_id);
        if (is_array($attributes_combinations) && count($attributes_combinations)){
            foreach ($attributes_combinations as &$ac){
                foreach ($ac as &$val){
                    $val = str_replace(JeproshopSettingModelSetting::getValue('attribute_anchor_separator'), '_', JeproshopTools::str2url(str_replace(array(',', '.'), '-', $val)));
                }
            }
        }else{
            $attributes_combinations = array();
        }
        $this->assignRef('attributes_combinations',  $attributes_combinations);
        $attribute_anchor_separator = JeproshopSettingModelSetting::getValue('attribute_anchor_separator');
        $this->assignRef('attribute_anchor_separator', $attribute_anchor_separator);
    }
    /**
     * Assign price and tax to the template
     */
    protected function assignPriceAndTax(){
        $customer_id = (isset($this->context->customer) ? (int)$this->context->customer->customer_id : 0);
        $group_id = (int)JeproshopGroupModelGroup::getCurrent()->group_id;
        $country_id = (int)$customer_id ? JeproshopCustomerModelCustomer::getCurrentCountry($customer_id) : JeproshopSettingModelSetting::getValue('default_country');

        $group_reduction = JeproshopGroupReductionModelGroupReduction::getValueForProduct($this->product->product_id, $group_id);
        if ($group_reduction === false){
            $group_reduction = JeproshopGroupModelGroup::getReduction((int)$this->context->cookie->customer_id) / 100;
        }
        // Tax
        $tax = (float)$this->product->getTaxesRate(new JeproshopAddressModelAddress((int)$this->context->cart->{JeproshopSettingModelSetting::getValue('tax_address_type')}));
        $this->assignRef('tax_rate', $tax);

        $product_price_with_tax = JeproshopProductModelProduct::getStaticPrice($this->product->product_id, true, null, 6);
        if (JeproshopProductModelProduct::$_taxCalculationMethod == COM_JEPROSHOP_TAX_INCLUDED)
            $product_price_with_tax = JeproshopTools::roundPrice($product_price_with_tax, 2);
        $product_price_without_eco_tax = (float)$product_price_with_tax - $this->product->ecotax;

        $ecotax_rate = (float)JeproshopTaxModelTax::getProductEcotaxRate($this->context->cart->{JeproshopSettingModelSetting::getValue('tax_address_type')});
        $ecotax_tax_amount = JeproshopTools::roundPrice($this->product->ecotax, 2);
        if (JeproshopProductModelProduct::$_taxCalculationMethod == COM_JEPROSHOP_TAX_INCLUDED && (int)JeproshopSettingModelSetting::getValue('use_tax'))
            $ecotax_tax_amount = JeproshopTools::roundPrice($ecotax_tax_amount * (1 + $ecotax_rate / 100), 2);

        $currency_id = (int)$this->context->cookie->currency_id;
        $product_id = (int)$this->product->product_id;
        $shop_id = $this->context->shop->shop_id;

        $quantity_discounts = JeproshopSpecificPriceModelSpecificPrice::getQuantityDiscounts($product_id, $shop_id, $currency_id, $country_id, $group_id, null, true, (int)$this->context->customer->customer_id);
        foreach ($quantity_discounts as &$quantity_discount){
            if ($quantity_discount->product_attribute_id){
                $combination = new JeproshopCombinationModelCombination((int)$quantity_discount->product_attribute_id);
                $attributes = $combination->getAttributesName((int)$this->context->language->lang_id);
                foreach ($attributes as $attribute){
                    $quantity_discount->attributes = $attribute->name .' - ';
                }
                $quantity_discount->attributes = rtrim($quantity_discount->attributes , ' - ');
            }
            if ((int)$quantity_discount->currency_id == 0 && $quantity_discount->reduction_type == 'amount')
                $quantity_discount->reduction = JeproshopTools::convertPriceFull($quantity_discount->reduction, null, JeproshopContext::getContext()->currency);
        }

        $product_price = $this->product->getPrice(JeproshopProductModelProduct::$_taxCalculationMethod == COM_JEPROSHOP_TAX_INCLUDED, false);
        $address = new JeproshopAddressModelAddress($this->context->cart->{JeproshopSettingModelSetting::getValue('tax_address_type')});
        $quantity_discounts = $this->formatQuantityDiscounts($quantity_discounts, $product_price, (float)$tax, $ecotax_tax_amount);
        $this->assignRef('quantity_discounts', $quantity_discounts);
        $this->assignRef('ecotax_tax_included', $ecotax_tax_amount);
        $ecotax_tax_excluded = JeproshopTools::roundPrice($this->product->ecotax, 2);
        $this->assignRef('ecotax_tax_excluded', $ecotax_tax_excluded);
        $this->assignRef('ecotaxTax_rate', $ecotax_rate);
        $display_price = JeproshopSettingModelSetting::getValue('display_price');
        $this->assignRef('display_price', $display_price);
        $product_price_without_eco_tax = (float)$product_price_without_eco_tax;
        $this->assignRef('product_price_without_ecotax', $product_price_without_eco_tax);
        $this->assignRef('group_reduction', $group_reduction);
        $no_tax = JeproshopTaxModelTax::taxExcludedOption() || !$this->product->getTaxesRate($address);
        $this->assignRef('no_tax', $no_tax);
        $ecotax = (!count($this->errors) && $this->product->ecotax > 0 ? JeproshopTools::convertPrice((float)$this->product->ecotax) : 0);
        $this->assignRef('ecotax', $ecotax);
        $tax_enabled = JeproshopSettingModelSetting::getValue('use_tax');
        $this->assignRef('tax_enabled', $tax_enabled);
        $customer_group_without_tax = JeproshopGroupModelGroup::getPriceDisplayMethod($this->context->customer->default_group_id);
        $this->assignRef('customer_group_without_tax', $customer_group_without_tax);
    }

    protected function formatQuantityDiscounts($specific_prices, $price, $tax_rate, $ecotax_amount) {
        foreach ($specific_prices as $key => &$row) {
            $row->quantity = &$row->from_quantity;
            if ($row->price >= 0) // The price may be directly set
            {
                $cur_price = (JeproshopProductModelProduct::$_taxCalculationMethod == COM_JEPROSSHOP_TAX_EXCLUED ? $row->price : $row->price * (1 + $tax_rate / 100)) + (float)$ecotax_amount;
                if ($row->reduction_type == 'amount'){
                    $cur_price -= (JeproshopProductModelProduct::$_taxCalculationMethod == COM_JEPROSSHOP_TAX_EXCLUED ? $row->reduction : $row->reduction / (1 + $tax_rate / 100));
                }else{
                    $cur_price *= 1 - $row->reduction;
                }
                $row->real_value = $price - $cur_price;
            } else {
                if ($row->reduction_type == 'amount')
                    $row->real_value = JeproshopProductModelProduct::$_taxCalculationMethod == COM_JEPROSSHOP_TAX_INCLUED ? $row->reduction : $row->reduction / (1 + $tax_rate / 100);
                else
                    $row->real_value = $row->reduction * 100;
            }
            $row->nextQuantity = (isset($specific_prices[$key + 1]) ? (int)$specific_prices[$key + 1]->from_quantity : -1);
        }
        return $specific_prices;
    }

    protected function loadObject($option = false){
        $app = JFactory::getApplication();
        $product_id = $app->input->get('product_id');
        $isLoaded = false;
        $context = JeproshopContext::getContext();
        if($product_id && JeproshopTools::isUnsignedInt($product_id)){
            if(!$this->product){
                $this->product = new JeproshopProductModelProduct($product_id, false, $context->language->lang_id);
            }

            if(!JeproshopTools::isLoadedObject($this->product, 'product_id')){
                JError::raiseError(500, JText::_('COM_JEPROSHOP_PRODUCT_NOT_FOUND_MESSAGE'));
                $isLoaded = false;
            }else{
                $isLoaded = true;
            }
        }elseif($option){
            if(!$this->product){
                $this->product = new JeproshopProductModelProduct();
            }
        }else{
            JError::raiseError(500, JText::_('COM_JEPROSHOP_PRODUCT_DOES_NOT_EXIST_MESSAGE'));
            $isLoaded = false;
        }

        //specified
        if($isLoaded && JeproshopTools::isLoadedObject($this->product, 'product_id')){
            if(JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && JeproshopShopModelShop::isFeaturePublished() && !$this->product->isAssociatedToShop()){
                $this->product = new JeproshopProductModelProduct((int)$this->product->product_id, false,  $context->language->lang_id, (int)$this->product->default_shop_id);
            }
            $this->product->loadStockData();
        }
        return $isLoaded;
    }
}