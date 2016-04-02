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

class JeproshopCarrierModelCarrier extends JModelLegacy
{
    /**
     * getCarriers method filter
     */
    const JEPROSHOP_CARRIERS_ONLY = 1;
    const JEPROSHOP_CARRIERS_MODULE = 2;
    const JEPROSHOP_CARRIERS_MODULE_NEED_RANGE = 3;
    const JEPROSHOP_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE = 4;
    const JEPROSHOP_ALL_CARRIERS = 5;

    const SORT_BY_PRICE = 0;
    const SORT_BY_POSITION = 1;

    const SORT_BY_ASC = 0;
    const SORT_BY_DESC = 1;

    const DEFAULT_SHIPPING_METHOD = 0;
    const WEIGHT_SHIPPING_METHOD = 1;
    const PRICE_SHIPPING_METHOD = 2;
    const FREE_SHIPPING_METHOD = 3;

    public $carrier_id;

    public $shop_id;
    /** @var int common id for carrier history */
    public $reference_id;

    /** @var string Name */
    public $name;

    /** @var string URL with a '@' for */
    public $url;

    /** @var string Delay needed to deliver customer */
    public $delay;

    /** @var boolean Carrier statues */
    public $published = true;

    /** @var boolean True if carrier has been deleted (staying in database as deleted) */
    public $deleted = 0;

    /** @var boolean Active or not the shipping handling */
    public $shipping_handling = true;

    /** @var int Behavior taken for unknown range */
    public $range_behavior;

    /** @var boolean Carrier module */
    public $is_module;

    /** @var boolean Free carrier */
    public $is_free = false;

    /** @var int shipping behavior: by weight or by price */
    public $shipping_method = 0;

    /** @var boolean Shipping external */
    public $shipping_external = 0;

    /** @var string Shipping external */
    public $external_module_name = null;

    /** @var boolean Need Range */
    public $need_range = 0;

    /** @var int Position */
    public $position;

    /** @var int maximum package width managed by the transporter */
    public $max_width;

    /** @var int maximum package height managed by the transporter */
    public $max_height;

    /** @var int maximum package deep managed by the transporter */
    public $max_depth;

    /** @var int maximum package weight managed by the transporter */
    public $max_weight;

    /** @var int grade of the shipping delay (0 for longest, 9 for shortest) */
    public $grade;

    protected static $price_by_weight = array();
    protected static $price_by_weight2 = array();
    protected static $price_by_price = array();
    protected static $price_by_price2 = array();

    protected static $cache_tax_rule = array();

    public function __construct($carrier_id = null, $lang_id = null){
        //parent::__construct($id, $id_lang);
        $db = JFactory::getDBO();

        if($lang_id !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($lang_id) ? (int)$lang_id : JeproshopSettingModelSetting::getValue('default_lang'));
        }

        if($carrier_id){
            $cache_id = 'jeproshop_carrier_model_' . $carrier_id . '_' . $lang_id;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_carrier') . " AS carrier ";
                $where = "";
                /** get language information **/
                if($lang_id){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_carrier_lang') . " AS carrier_lang ON (carrier.";
                    $query .= "carrier_id = carrier_lang.carrier_id AND carrier_lang.lang_id = " . (int)$lang_id . ") ";
                    /*if($this->shop_id && !(empty($this->multiLangShop))){
                        $where = " AND carrier_lang.shop_id = " . $this->shop_id;
                    }*/
                }

                /** Get shop informations **/
                if(JeproshopShopModelShop::isTableAssociated('group')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_carrier_shop') . " AS carrier_shop ON (carrier.";
                    $query .= "carrier_id = carrier_shop.carrier_id AND carrier_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE carrier.carrier_id = " . (int)$carrier_id . $where;

                $db->setQuery($query);
                $carrier_data = $db->loadObject();

                if($carrier_data){
                    if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_carrier_lang');
                        $query .= " WHERE carrier_id = " . (int)$carrier_id;

                        $db->setQuery($query);
                        $carrier_lang_data = $db->loadObjectList();
                        if($carrier_lang_data){
                            foreach ($carrier_lang_data as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'carrier_id'){
                                        if(!isset($carrier_data->{$key}) || !is_array($carrier_data->{$key})){
                                            $carrier_data->{$key} = array();
                                        }
                                        $carrier_data->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cache_id, $carrier_data);
                }
            }else{
                $carrier_data = JeproshopCache::retrieve($cache_id);
            }

            if($carrier_data){
                $carrier_data->carrier_id = $carrier_id;
                foreach($carrier_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        /**
         * keep retro-compatibility SHIPPING_METHOD_DEFAULT
         * @deprecated 1.5.5
         */
        if ($this->shipping_method == JeproshopCarrierModelCarrier::DEFAULT_SHIPPING_METHOD){
            $this->shipping_method = ((int)JeproshopSettingModelSetting::getValue('shipping_method') ? JeproshopCarrierModelCarrier::WEIGHT_SHIPPING_METHOD : JeproshopCarrierModelCarrier::PRICE_SHIPPING_METHOD);
        }
        /**
         * keep retro-compatibility id_tax_rules_group
         * @deprecated 1.5.0
         */
        if ($this->carrier_id){
            $this->tax_rules_group_id = $this->getTaxRulesGroupId(JeproshopContext::getContext());
        }
        if ($this->name == '0'){
            $this->name = JeproshopSettingModelSetting::getValue('shop_name');
        }
        $this->image_dir = COM_JEPROSHOP_CARRIER_IMAGE_DIRECTORY;
    }

    public static function getTaxRulesGroupIdMostUsed(){
        $db = JFactoery::getDBO();

        $query = "SELECT tax_rules_group_id FROM ( SELECT COUNT(*) n, carrier.tax_rules_group_id FROM " . $db->quoteName('#__jeproshop_carrier') . " AS carrier JOIN ";
        $query .= $db->quoteName('#__jeproshop_tax_rules_group') . " AS tax_rule_group ON (carrier.tax_rules_group_id = tax_rule_group.tax_rules_group_id) WHERE ";
        $query .= "tax_rule_group.published = 1 GROUP BY carrier.tax_rules_group_id ORDER BY n DESC LIMIT 1 ) most_used";

        $db->setQuery($query);
        return $db->loadResult();
    }

    public static function getDeliveredCountries($lang_id, $active_countries = false, $active_carriers = false, $contain_states = null){
        if (!JeproshopTools::isBool($active_countries) || !JeproshopTools::isBool($active_carriers)) {
            die(Tools::displayError());
        }

        $db = JFactory::getDBO();

        $query = "SELECT state.* FROM " . $db->quoteName('#__jeproshop_state') . " AS state ORDER BY state." . $db->quoteName('name') . " ASC";
        $db->setQuery($query);
        $states = $db->loadObjectList(); print_r($states);

        $query = "SELECT country_lang.*, country.*, country_lang." . $db->quoteName('name') . " AS country_name, zone." . $db->quoteName('name') . " AS zone_name FROM " . $db->quoteName('#__jeproshop_country') . " AS country ";
        $query .= JeproshopShopModelShop::addSqlAssociation('country') . " LEFT JOIN " . $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country." . $db->quoteName('country_id') . " = country_lang." . $db->quoteName('country_id');
        $query .= " AND country_lang." . $db->quoteName('lang_id') . " = " .(int)$lang_id . ") INNER JOIN " . $db->quoteName('#__jeproshop_carrier_zone') . " AS carrier_zone INNER JOIN " . $db->quoteName('#__jeproshop_carrier') ;
        $query .= " AS carrier ON ( carrier.carrier_id = carrier_zone.carrier_id AND carrier.deleted = 0 " . ($active_carriers ? " AND carrier.published = 1) " : ") " ) . " LEFT JOIN " . $db->quoteName('#__jeproshop_zone') . " AS zone ON (carrier_zone.";
        $query .= $db->quoteName('zone_id') . " = zone." . $db->quoteName('zone_id') . " AND zone." . $db->quoteName('zone_id') . " = country."  . $db->quoteName('zone_id') . ") WHERE 1 " . ($active_countries ? " AND country."  . $db->quoteName('published') ." = 1" : "");
        $query .= (!is_null($contain_states) ? " AND country." . $db->quoteName('contains_states') . " = " .(int)$contain_states : "") . " ORDER BY country_lang.name ASC";

        $db->setQuery($query);
        $result = $db->loadObjectList(); print_r($result);

        $countries = array();
        foreach ($result as &$country)
            $countries[$country->country_id] = $country;
        foreach ($states as &$state)
            if (isset($countries[$state->country_id])) /* Does not keep the state if its country has been disabled and not selected */
                if ($state->published == 1)
                    $countries[$state->country_id]['states'][] = $state;

        return $countries;
    }

    public function getTaxRulesGroupId(JeproshopContext $context = null){
        return JeproshopCarrierModelCarrier::getTaxRulesGroupIdByCarrierId((int)$this->carrier_id, $context);
    }

    public static function getTaxRulesGroupIdByCarrierId($carrier_id, JeproshopContext $context = null){
        if (!$context)
            $context = JeproshopContext::getContext();
        $key = 'carrier_tax_rules_group_id_'.(int)$carrier_id . '_'.(int)$context->shop->shop_id;
        if (!JeproshopCache::isStored($key)){
            $db = JFactory::getDBO();
            $query = "SELECT " . $db->quoteName('tax_rules_group_id') . " FROM " . $db->quoteName('#__jeproshop_carrier_tax_rules_group_shop') . " WHERE ";
            $query .= $db->quoteName('carrier_id') . " = " .(int)$carrier_id . " AND shop_id = " . (int)JeproshopContext::getContext()->shop->shop_id;

            $db->setQuery($query);

            JeproshopCache::store($key, $db->loadResult());
        }
        return JeproshopCache::retrieve($key);
    }

    public static function checkDeliveryPriceByWeight($carrier_id, $totalWeight, $zone_id) {
        $cache_key = $carrier_id . '_' . $totalWeight . '_' . $zone_id;
        if (!isset(self::$price_by_weight2[$cache_key])){
            $db = JFactory::getDBO();
            $query = "SELECT delivery." . $db->quoteName('price') . " FROM " . $db->quoteName('#__jeproshop_delivery') . " AS delivery LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_range_weight') . " AS range_weight ON delivery." . $db->quoteName('range_weight_id') . " = range_weight.";
            $query .= $db->quoteName('range_weight_id') . " WHERE delivery." . $db->quoteName('zone_id') . " = " . (int)$zone_id . " AND " .(float)$totalWeight;
            $query .= " >= range_weight." . $db->quoteName('delimiter1') . " AND " . (float)$totalWeight . " < range_weight." . $db->quoteName('delimiter2');
            $query .= " AND delivery." . $db->quoteName('carrier_id') . " = " .(int)$carrier_id . JeproshopCarrierModelCarrier::sqlDeliveryRangeShop('range_weight');
            $query .= " ORDER BY range_weight." . $db>quoteName('delimiter1') . " ASC";

            $db->setQuery($query);
            $result = $db->loadObject();
            self::$price_by_weight2[$cache_key] = (isset($result->price));
        }
        return self::$price_by_weight2[$cache_key];
    }

    /**
     * Check delivery prices for a given order
     *
     * @param id_carrier
     * @param float $orderTotal Order total to pay
     * @param integer $zone_id Zone id (for customer delivery address)
     * @param integer $currency_id
     * @return float Delivery price
     */
    public static function checkDeliveryPriceByPrice($carrier_id, $orderTotal, $zone_id, $currency_id = null) {
        $cache_key = $carrier_id .'_'.$orderTotal.'_'.$zone_id.'_'.$currency_id;
        if (!isset(self::$price_by_price2[$cache_key])){
            if (!empty($currency_id)) {
                $orderTotal = JeproshopValidator::convertPrice($orderTotal, $currency_id, false);
            }
            $db = JFactory::getDBO();
            $query = "SELECT d." . $db->quoteName('price') . " FROM " . $db->quoteName('#__jeproshop_delivery') . " AS delivery LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_range_price') . " AS price_range ON delivery." . $db->quoteName('range_price_id') . " = range_price.";
            $query .= $db->quoteName('range_price_id') . " WHERE delivery." . $db->quoteName('zone_id') . " = " . (int)$zone_id . " AND " . (float)$orderTotal;
            $query .= " >= price_range." . $db->quoteName('delimiter1') . " AND " . (float)$orderTotal . " < price_range." . $db->quoteName('delimiter2');
            $query .= " AND delivery." . $db->quoteName('carrier_id') . " = " .(int)$carrier_id . JeproshopCarrierModelCarrier::sqlDeliveryRangeShop('range_price');
            $query .= " ORDER BY price_range." . $db->quoteName('delimiter1') . " ASC";

            $db->setQuery($query);
            $result = $db->loadObject();
            self::$price_by_price2[$cache_key] = (isset($result->price));
        }
        return self::$price_by_price2[$cache_key];
    }


    /**
     * Get delivery prices for a given shipping method (price/weight)
     *
     * @param string $rangeTable Table name (price or weight)
     * @return array Delivery prices
     */
    public static function getDeliveryPriceByRanges($rangeTable, $carrier_id) {
        $db = JFactory::getDBO();
        $query = 'SELECT d.`id_'.bqSQL($rangeTable).'`, d.id_carrier, d.zone_id, d.price
				FROM '._DB_PREFIX_.'delivery d
				LEFT JOIN `'._DB_PREFIX_.bqSQL($rangeTable).'` r ON r.`id_'.bqSQL($rangeTable).'` = d.`id_'.bqSQL($rangeTable).'`
				WHERE d.id_carrier = '.(int)$carrier_id .'
					AND d.`id_'.bqSQL($rangeTable).'` IS NOT NULL
					AND d.`id_'.bqSQL($rangeTable).'` != 0
					'.JeproshopCarrierModelCarrier::sqlDeliveryRangeShop($rangeTable).'
				ORDER BY r.delimiter1';
        return $db->loadObjectList();
    }

    /**
     * Get all carriers in a given language
     *
     * @param integer $id_lang Language id
     * @param $modules_filters, possible values:
    PS_CARRIERS_ONLY
    CARRIERS_MODULE
    CARRIERS_MODULE_NEED_RANGE
    PS_CARRIERS_AND_CARRIER_MODULES_NEED_RANGE
    ALL_CARRIERS
     * @param boolean $active Returns only active carriers when true
     * @return array Carriers
     */
    public static function getCarriers($id_lang, $active = false, $delete = false, $zone_id = false, $ids_group = null, $modules_filters = self::PS_CARRIERS_ONLY)
    {
        // Filter by groups and no groups => return empty array
        if ($ids_group && (!is_array($ids_group) || !count($ids_group)))
            return array();

        $sql = '
		SELECT c.*, cl.delay
		FROM `'._DB_PREFIX_.'carrier` c
		LEFT JOIN `'._DB_PREFIX_.'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND cl.`id_lang` = '.(int)$id_lang.Shop::addSqlRestrictionOnLang('cl').')
		LEFT JOIN `'._DB_PREFIX_.'carrier_zone` cz ON (cz.`id_carrier` = c.`id_carrier`)'.
            ($zone_id ? 'LEFT JOIN `'._DB_PREFIX_.'zone` z ON (z.`zone_id` = '.(int)$zone_id.')' : '').'
		'.Shop::addSqlAssociation('carrier', 'c').'
		WHERE c.`deleted` = '.($delete ? '1' : '0');
        if ($active)
            $sql .= ' AND c.`active` = 1 ';
        if ($zone_id)
            $sql .= ' AND cz.`zone_id` = '.(int)$zone_id.' AND z.`active` = 1 ';
        if ($ids_group)
            $sql .= ' AND c.id_carrier IN (SELECT id_carrier FROM '._DB_PREFIX_.'carrier_group WHERE id_group IN ('.implode(',', array_map('intval', $ids_group)).')) ';

        switch ($modules_filters)
        {
            case 1 :
                $sql .= ' AND c.is_module = 0 ';
                break;
            case 2 :
                $sql .= ' AND c.is_module = 1 ';
                break;
            case 3 :
                $sql .= ' AND c.is_module = 1 AND c.need_range = 1 ';
                break;
            case 4 :
                $sql .= ' AND (c.is_module = 0 OR c.need_range = 1) ';
                break;
        }
        $sql .= ' GROUP BY c.`id_carrier` ORDER BY c.`position` ASC';


        $cache_id = 'Carrier::getCarriers_'.md5($sql);
        if (!Cache::isStored($cache_id))
        {
            $carriers = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $carriers);
        }
        $carriers = Cache::retrieve($cache_id);
        foreach ($carriers as $key => $carrier)
            if ($carrier['name'] == '0')
                $carriers[$key]['name'] = Configuration::get('PS_SHOP_NAME');
        return $carriers;
    }

    /**
     * For a given {product, warehouse}, gets the carrier available
     *
     * @param JeproshopProductModelProduct $product The id of the product, or an array with at least the package size and weight
     * @param int $warehouse_id
     * @param int $address_delivery_id
     * @param int $shop_id
     * @param $cart
     * @return array
     */
    public static function getAvailableCarrierList(JeproshopProductModelProduct $product, $warehouse_id, $address_delivery_id = null, $shop_id = null, $cart = null)
    {
        if (is_null($shop_id))
            $shop_id = JeproshopContext::getContext()->shop->shop_id;
        if (is_null($cart))
            $cart = JeproshopContext::getContext()->cart;

        $address_id = (int)((!is_null($address_delivery_id) && $address_delivery_id != 0) ? $address_delivery_id : $cart->address_delivery_id);
        if ($address_id) {
            $address = new JeproshopAddressModelAddress($address_id);
            $zone_id = JeproshopAddressModelAddress::getZoneIdByAddressId($address->address_id);

            // Check the country of the address is activated
            if (!JeproshopAddressModelAddress::isCountryActiveById($address->address_id))
                return array();
        } else {
            $country = new JeproshopCountryModelCountry(JeproshopSettingModelSetting::getValue('default_country'));
            $izone_id = $country->zone_id;
        }

        // Does the product is linked with carriers?
        $query = new DbQuery();
        $query->select('id_carrier');
        $query->from('product_carrier', 'pc');
        $query->innerJoin('carrier', 'c', 'c.id_reference = pc.id_carrier_reference AND c.deleted = 0');
        $query->where('pc.id_product = ' . (int)$product->product_id);
        $query->where('pc.id_shop = ' . (int)$shop_id);

        $cache_id = 'Carrier::getAvailableCarrierList_' . (int)$product->id . '-' . (int)$id_shop;
        if (!Cache::isStored($cache_id)) {
            $carriers_for_product = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
            Cache::store($cache_id, $carriers_for_product);
        }
        $carriers_for_product = Cache::retrieve($cache_id);

        $carrier_list = array();
        if (!empty($carriers_for_product)) {
            //the product is linked with carriers
            foreach ($carriers_for_product as $carrier) //check if the linked carriers are available in current zone
                if (Carrier::checkCarrierZone($carrier['id_carrier'], $id_zone))
                    $carrier_list[] = $carrier['id_carrier'];
            if (empty($carrier_list))
                return array();//no linked carrier are available for this zone
        }

        // The product is not directly linked with a carrier
        // Get all the carriers linked to a warehouse
        if ($warehouse_id) {
            $warehouse = new JeproshopWarehouseModelWarehouse($warehouse_id);
            $warehouse_carrier_list = $warehouse->getCarriers();
        }

        $available_carrier_list = array();
        $customer = new JeproshopCustomerModelCustomer($cart->customer_id);
        $carriers = JeproshopCarrierModelCarrier::getCarriersForOrder($zone_id, $customer->getGroups(), $cart);

        foreach ($carriers as $carrier) {
            $available_carrier_list[] = $carrier->carrier_id;
        }

        if ($carrier_list) {
            $carrier_list = array_intersect($available_carrier_list, $carrier_list);
        } else {
            $carrier_list = $available_carrier_list;
        }
        if (isset($warehouse_carrier_list))
            $carrier_list = array_intersect($carrier_list, $warehouse_carrier_list);

        if ($product->width > 0 || $product->height > 0 || $product->depth > 0 || $product->weight > 0) {
            foreach ($carrier_list as $key => $carrier_id) {
                $carrier = new JeproshopCarrierModelCarrier($carrier_id);
                if (($carrier->max_width > 0 && $carrier->max_width < $product->width)
                    || ($carrier->max_height > 0 && $carrier->max_height < $product->height)
                    || ($carrier->max_depth > 0 && $carrier->max_depth < $product->depth)
                    || ($carrier->max_weight > 0 && $carrier->max_weight < $product->weight))
                    unset($carrier_list[$key]);
            }
        }
        return $carrier_list;
    }

    /**
     * Get delivery prices for a given order
     *
     * @param float $totalWeight Order total weight
     * @param integer $zone_id Zone id (for customer delivery address)
     * @return float Delivery price
     */
    public function getDeliveryPriceByWeight($totalWeight, $zone_id){
        $cache_key = $this->carrier_id . '_' . $totalWeight . '_' . $zone_id;
        if (!isset(self::$price_by_weight[$cache_key]))
        {
            $sql = 'SELECT d.`price`
					FROM `'._DB_PREFIX_.'delivery` d
					LEFT JOIN `'._DB_PREFIX_.'range_weight` w ON (d.`id_range_weight` = w.`id_range_weight`)
					WHERE d.`zone_id` = '.(int)$zone_id.'
						AND '.(float)$total_weight.' >= w.`delimiter1`
						AND '.(float)$total_weight.' < w.`delimiter2`
						AND d.`id_carrier` = '.(int)$this->id.'
						'.Carrier::sqlDeliveryRangeShop('range_weight').'
					ORDER BY w.`delimiter1` ASC';
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
            if (!isset($result['price']))
                self::$price_by_weight[$cache_key] = $this->getMaxDeliveryPriceByWeight($zone_id);
            else
                self::$price_by_weight[$cache_key] = $result['price'];
        }
        return self::$price_by_weight[$cache_key];
    }

    public function getMaxDeliveryPriceByWeight($zone_id) {
        $cache_id = 'Carrier::getMaxDeliveryPriceByWeight_'.(int)$this->id.'-'.(int)$zone_id;
        if (!Cache::isStored($cache_id))
        {
            $sql = 'SELECT d.`price`
					FROM `'._DB_PREFIX_.'delivery` d
					INNER JOIN `'._DB_PREFIX_.'range_weight` w ON d.`id_range_weight` = w.`id_range_weight`
					WHERE d.`zone_id` = '.(int)$zone_id.'
						AND d.`id_carrier` = '.(int)$this->id.'
						'.Carrier::sqlDeliveryRangeShop('range_weight').'
					ORDER BY w.`delimiter2` DESC';
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            Cache::store($cache_id, $result);
        }
        return Cache::retrieve($cache_id);
    }

    /**
     * Get delivery prices for a given order
     *
     * @param float $orderTotal Order total to pay
     * @param integer $zone_id Zone id (for customer delivery address)
     * @param integer $currency_id
     * @return float Delivery price
     */
    public function getDeliveryPriceByPrice($orderTotal, $zone_id, $currency_id = null) {
        $cache_key = $this->id.'_'.$order_total.'_'.$zone_id.'_'.$currency_id;
        if (!isset(self::$price_by_price[$cache_key]))
        {
            if (!empty($currency_id))
                $order_total = Tools::convertPrice($order_total, $currency_id, false);

            $sql = 'SELECT d.`price`
					FROM `'._DB_PREFIX_.'delivery` d
					LEFT JOIN `'._DB_PREFIX_.'range_price` r ON d.`id_range_price` = r.`id_range_price`
					WHERE d.`zone_id` = '.(int)$zone_id.'
						AND '.(float)$order_total.' >= r.`delimiter1`
						AND '.(float)$order_total.' < r.`delimiter2`
						AND d.`id_carrier` = '.(int)$this->id.'
						'.Carrier::sqlDeliveryRangeShop('range_price').'
					ORDER BY r.`delimiter1` ASC';
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
            if (!isset($result['price']))
                self::$price_by_price[$cache_key] = $this->getMaxDeliveryPriceByPrice($zone_id);
            else
                self::$price_by_price[$cache_key] = $result['price'];
        }
        return self::$price_by_price[$cache_key];
    }

    public function getShippingMethod() {
        if ($this->is_free)
            return Carrier::SHIPPING_METHOD_FREE;

        $method = (int)$this->shipping_method;

        if ($this->shipping_method == Carrier::SHIPPING_METHOD_DEFAULT)
        {
            // backward compatibility
            if ((int)Configuration::get('PS_SHIPPING_METHOD'))
                $method = Carrier::SHIPPING_METHOD_WEIGHT;
            else
                $method = Carrier::SHIPPING_METHOD_PRICE;
        }

        return $method;
    }

}