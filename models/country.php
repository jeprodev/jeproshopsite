<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net
 *
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

class JeproshopCountryModelCountry extends JModelLegacy
{
    public $country_id;

    public $lang_id;

    public $shop_id;

    public $zone_id;

    public $currency_id;

    public $states = array();

    public $name = array();
    public $iso_code;

    public $call_prefix;

    public $published;

    public $contains_states;

    public $need_identification_number;

    public $need_zip_code;

    public $zip_code_format;

    public $display_tax_label;

    public $country_display_tax_label;
    public $get_shop_from_context = false;

    public $multiLangShop = true;
    public $multiLang = true;

    public function __construct($country_id = null, $lang_id = null, $shop_id = NULL){
        $db = JFactory::getDBO();

        if($shop_id  && $this->isMultiShop()){
            $this->shop_id = (int)$shop_id;
            $this->get_shop_from_context = FALSE;
        }

        if($this->isMultiShop() && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }
    }

    public function isMultiShop(){
        return JeproshopShopModelShop::isTableAssociated('country') || !empty($this->multiLangShop);
    }

    /**
     * Get a country ID with its iso code
     *
     * @param string $iso_code Country iso code
     * @return integer Country ID
     */
    public static function getByIso($iso_code){
        if (!JeproshopTools::isLanguageIsoCode($iso_code))
            die(Tools::displayError());

        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('country_id') . " FROM " . $db->quoteName('#__jeproshop_country') . " WHERE " . $db->quoteName('iso_code') . " = " . $db->quote(strtoupper($iso_code));
        $db->setQuery($query);
        $result = $db->loadObject();

        return $result->country_id;
    }

    /**
     * Replace letters of zip code format And check this format on the zip code
     * @param $zip_code
     * @return bool (bool)
     */
    public function checkZipCode($zip_code){
        $zip_regexp = '/^'.$this->zip_code_format.'$/ui';
        $zip_regexp = str_replace(' ', '( |)', $zip_regexp);
        $zip_regexp = str_replace('-', '(-|)', $zip_regexp);
        $zip_regexp = str_replace('N', '[0-9]', $zip_regexp);
        $zip_regexp = str_replace('L', '[a-zA-Z]', $zip_regexp);
        $zip_regexp = str_replace('C', $this->iso_code, $zip_regexp);

        return (bool)preg_match($zip_regexp, $zip_code);
    }

    public function getCountries($lang_id){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();
        $option = $app->input->get('option');
        $view = $app->input->get('view');

        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart = $app->getUserStateFromRequest($option. $view. '.limit_start', 'limit_start', 0, 'int');

        $query = "SELECT SQL_CALC_FOUND_ROWS country." . $db->quoteName('country_id') . ", country_lang." . $db->quoteName('name') . " AS name,";
        $query .= $db->quoteName('iso_code') . ", " . $db->quoteName('call_prefix') . ",zone." . $db->quoteName('zone_id'). " AS zone,";
        $query .= "country.published AS published, zone." . $db->quoteName('name') . " AS zone_name FROM " . $db->quoteName('#__jeproshop_country');
        $query .= " AS country LEFT JOIN " . $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country_lang.";
        $query .= $db->quoteName('country_id') . " = country." . $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id');
        $query .= " = " . $lang_id . ") LEFT JOIN " . $db->quoteName('#__jeproshop_zone') . " AS zone ON (zone." . $db->quoteName('zone_id');
        $query .= " = country." . $db->quoteName('zone_id') .") WHERE 1 ORDER BY country." . $db->quoteName('country_id'). " ASC ";

        $db->setQuery($query);
        $countries = $db->loadObjectList();

        //$total = count($countries);

        //$this->pagination = new JPagination($total, $limitstart, $limit);
        return $countries;
    }

    /**
     * @param $countries_ids
     * @param $zone_id
     * @return bool
     */
    public function affectZoneToSelection($countries_ids, $zone_id)
    {
        // cast every array values to int (security)
        $countries_ids = array_map('intval', $countries_ids);

        $db = JFactory::getDBO();
        $query = "UPDATE " . $db->query('#__jeproshop_country') . " SET " . $db->quoteName('zone_id') . " = " . (int)$zone_id . " WHERE " . $db->quoteName('country_id') . " IN (" . implode(',', $countries_ids) . ")";
        $db->setQuery($query);
        return $db->query();
    }

    public function delete()
    {
        $db = JFactory::getDBO();
        if (!parent::delete())
            return false;

        $query = "DELETE FROM " . $db->quoteName('#__jeproshop_cart_rule_country') . " WHERE " . $db->quoteName('country_id') . " = " . (int)$this->country_id;
        $db->setQuery($query);
        return $db->query();
    }

    public static function getCountriesByShopId($shop_id, $lang_id)
    {
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_country') . " AS country LEFT JOIN " . $db->quoteName('#__jeproshop_country_shop') . " AS country_shop ON (country_shop." . $db->quoteName('country_id') . " = country." . $db->quoteName('country_id');
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_country_lang') . " AS country_lang ON (country" . $db->quoteName('country_id') . " = country_lang." . $db->quoteName('country_id') . " AND country_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id;
        $query .= " WHERE " . $db->quoteName('shop_id') . " = " . (int)$shop_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getZoneId($country_id)
    {
        if (!JeproshopTools::isUnsignedInt($country_id))
            die(JError::raiseError());

        if (isset(self::$_zones_ids[$id_country]))
            return self::$_idZones[$id_country];
        $db = JFactory::getDBO();
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `id_zone`
		FROM `'._DB_PREFIX_.'country`
		WHERE `id_country` = '.(int)$id_country);

        self::$_idZones[$id_country] = $result['id_zone'];
        return $result['id_zone'];
    }

    /**
     * Get a country name with its ID
     *
     * @param integer $lang_id Language ID
     * @param integer $id_country Country ID
     * @return string Country name
     */
    public static function getNameById($id_lang, $id_country)
    {
        $key = 'country_getNameById_'.$id_country.'_'.$id_lang;
        if (!Cache::isStored($key)) {
            $db = JFactory::getDBO();
            Cache::store($key, Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT `name`
				FROM `' . _DB_PREFIX_ . 'country_lang`
				WHERE `id_lang` = ' . (int)$id_lang . '
				AND `id_country` = ' . (int)$id_country
            ));
        }
        return Cache::retrieve($key);
    }

    /**
     * Get a country iso with its ID
     *
     * @param integer $id_country Country ID
     * @return string Country iso
     */
    public static function getIsoById($id_country)
    {
        if (!isset(Country::$cache_iso_by_id[$id_country]))
        {
            $db = JFactory::getDBO();
            Country::$cache_iso_by_id[$id_country] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `iso_code`
			FROM `'._DB_PREFIX_.'country`
			WHERE `id_country` = '.(int)($id_country));
        }

        return JeproshopCountryModelCountry::$cache_iso_by_id[$country_id];
    }

    /**
     * Get a country id with its name
     *
     * @param integer $id_lang Language ID
     * @param string $country Country Name
     * @return intval Country id
     */
    public static function getIdByName($id_lang = null, $country)
    {
        $db = JFactory::getDBO();
        $sql = '
		SELECT `id_country`
		FROM `'._DB_PREFIX_.'country_lang`
		WHERE `name` LIKE \''.pSQL($country).'\'';
        if ($id_lang)
            $sql .= ' AND `id_lang` = '.(int)$id_lang;

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

        return (int)$result['id_country'];
    }

    public static function getNeedZipCode($id_country)
    {
        if (!(int)$id_country)
            return false;
        $db = JFactory::getDBO();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `need_zip_code`
		FROM `'._DB_PREFIX_.'country`
		WHERE `id_country` = '.(int)$id_country);
    }

    public static function getZipCodeFormat($id_country)
    {
        if (!(int)$id_country)
            return false;

        $db = JFactory::getDBO();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `zip_code_format`
		FROM `'._DB_PREFIX_.'country`
		WHERE `id_country` = '.(int)$id_country);
    }

    /**
     * Returns the default country Id
     *
     * @return integer default country id
     */
    public static function getDefaultCountryId()
    {
        return JeproshopContext::getContext()->country->country_id;
    }

    public static function isNeedDniByCountryId($id_country)
    {
        $db = JFactory::getDBO();
        return (bool)Db::getInstance()->getValue('
			SELECT `need_identification_number`
			FROM `'._DB_PREFIX_.'country`
			WHERE `id_country` = '.(int)$id_country);
    }

    public static function containsStates($id_country)
    {
        $db = JFactory::getDBO();
        return (bool)Db::getInstance()->getValue('
			SELECT `contains_states`
			FROM `'._DB_PREFIX_.'country`
			WHERE `id_country` = '.(int)$id_country);
    }

    public static function getCountriesByZoneId($id_zone, $id_lang)
    {
        if (empty($id_zone) || empty($id_lang))
            die(Tools::displayError());

        $db = JFactory::getDBO();
        $sql = ' SELECT DISTINCT c.*, cl.*
				FROM `'._DB_PREFIX_.'country` c
				'.Shop::addSqlAssociation('country', 'c', false).'
				LEFT JOIN `'._DB_PREFIX_.'state` s ON (s.`id_country` = c.`id_country`)
				LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country`)
				WHERE (c.`id_zone` = '.(int)$id_zone.' OR s.`id_zone` = '.(int)$id_zone.')
				AND `id_lang` = '.(int)$id_lang;
        return Db::getInstance()->executeS($sql);
    }

    public function isNeedDni()
    {
        return JeproshopCountryModelCountry::isNeedDniByCountryId($this->country_id);
    }

    public static function addModuleRestrictions(array $shops = array(), array $countries = array(), array $modules = array())
    {
        if (!count($shops))
            $shops = JeproshopShopModelShop::getShops(true, null, true);

        if (!count($countries))
            $countries = JeproshopCountryModelCountry::getCountries((int)JeproshopContext::getContext()->cookie->lang_id);

        if (!count($modules))
            $modules = Module::getPaymentModules();

        $sql = false;
        foreach ($shops as $id_shop)
            foreach ($countries as $country)
                foreach ($modules as $module)
                    $sql .= '('.(int)$module['id_module'].', '.(int)$id_shop.', '.(int)$country['id_country'].'),';

        if ($sql)
        {
            $sql = 'INSERT IGNORE INTO `'._DB_PREFIX_.'module_country` (`id_module`, `id_shop`, `id_country`) VALUES '.rtrim($sql, ',');
            return Db::getInstance()->execute($sql);
        }
        else
            return true;
    }

    public function add($autodate = true, $null_values = false)
    {
        $return = parent::add($autodate, $null_values) && self::addModuleRestrictions(array(), array(array('id_country' => $this->id)), array());
        return $return;
    }
}

class JeproshopStateModelState extends JModelLegacy
{
    public $state_id;

    /** @var integer Country id which state belongs */
    public $country_id;

    /** @var integer Zone id which state belongs */
    public $zone_id;

    /** @var string 2 letters iso code */
    public $iso_code;

    /** @var string Name */
    public $name;

    /** @var boolean Status for delivery */
    public $published = true;

    public function __construct($state_id = null){
        $db = JFactory::getDBO();
        /*
        if ($lang_id !== null)
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($lang_id) !== false) ? $lang_id : JeproshopSettingModelSetting::getValue('default_lang');

        if ($shop_id && $this->isMultishop()){
            $this->shop_id = (int)$shop_id;
            $this->get_shop_from_context = false;
        }

        if ($this->isMultishop() && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        } */

        if ($state_id){
            // Load object from database if object id is present
            $cache_id = 'jeproshop_model_state_'.(int)$state_id.'_'; //.(int)$this->shop_id . '_'.(int)$lang_id;
            if (!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_state') . " AS state WHERE state.state_id = " . (int)$state_id;


                /*/ Get lang informations
                if ($lang_id)
                {
                    $sql->leftJoin($this->def['table'].'_lang', 'b', 'a.'.$this->def['primary'].' = b.'.$this->def['primary'].' AND b.id_lang = '.(int)$id_lang);
                    if ($this->id_shop && !empty($this->def['multilang_shop']))
                        $sql->where('b.id_shop = '.$this->id_shop);
                }

                // Get shop informations
                if (Shop::isTableAssociated($this->def['table']))
                    $sql->leftJoin($this->def['table'].'_shop', 'c', 'a.'.$this->def['primary'].' = c.'.$this->def['primary'].' AND c.id_shop = '.(int)$this->id_shop);
                */

                $db->setQuery($query);
                $state_data = $db->loadObject();

                if ($state_data){
                    /*if (!$id_lang && isset($this->def['multilang']) && $this->def['multilang'])
                    {
                        $sql = 'SELECT * FROM `'.pSQL(_DB_PREFIX_.$this->def['table']).'_lang`
                                WHERE `'.bqSQL($this->def['primary']).'` = '.(int)$id
                                        .(($this->id_shop && $this->isLangMultishop()) ? ' AND `id_shop` = '.$this->id_shop : '');
                        if ($object_datas_lang = ObjectModel::$db->executeS($sql))
                            foreach ($object_datas_lang as $row)
                                foreach ($row as $key => $value)
                                {
                                    if (array_key_exists($key, $this) && $key != $this->def['primary'])
                                    {
                                        if (!isset($object_datas[$key]) || !is_array($object_datas[$key]))
                                            $object_datas[$key] = array();
                                        $object_datas[$key][$row['id_lang']] = $value;
                                    }
                                }
                    }*/
                    JeproshopCache::store($cache_id, $state_data);
                }
            }else{
                $state_data = JeproshopCache::retrieve($cache_id);
            }

            if ($state_data){
                $this->state_id = (int)$state_id;
                foreach ($state_data as $key => $value){
                    if (array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    

    

    
}

class JeproshopZoneModelZone extends JModelLegacy
{
    public $zone_id;

    /** @var string Name */
    public $name;

    public $allow_delivery;

    /**
     * Get all available geographical zones
     *
     * @param bool|type $allow_delivery
     * @return type
     */
    public static function getZones($allow_delivery = FALSE){
        $cache_id = 'jeproshop_zone_model_get_zones_' . (bool)$allow_delivery;
        if(!JeproshopCache::isStored($cache_id)) {
            $db = JFactory::getDBO();

            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_zone') . ($allow_delivery ? " WHERE allow_delivery = 1 " : "");
            $query .= " ORDER BY " . $db->quoteName('name') . " ASC ";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Get a zone ID from its default language name
     *
     * @param string $name
     * @return integer id_zone
     */
    public static function getIdByName($name)
    {
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('zone_id') . " FROM " . $db->quoteName('#__jeproshop_zone') . " WHERE " . $db->quoteName('name') . " = " . $db->quote($name);
        $db->setQuery($query);
        return $db->loadResult();
    }
}