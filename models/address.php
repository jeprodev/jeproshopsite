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

class JeproshopAddressModelAddress extends JModelLegacy
{
    public $address_id;

    /** @var integer Customer id which address belongs to */
    public $customer_id = null;

    /** @var integer Manufacturer id which address belongs to */
    public $manufacturer_id = null;

    /** @var integer Supplier id which address belongs to */
    public $supplier_id = null;

    public $developer_id = null;

    /** @var int Warehouse id which address belongs to  */
    public $warehouse_id = null;

    /** @var integer Country id */
    public $country_id;

    /** @var integer State id */
    public $state_id;

    /** @var string Country name */
    public $country;

    /** @var string Alias (eg. Home, Work...) */
    public $alias;

    /** @var string Company (optional) */
    public $company;

    /** @var string Lastname */
    public $lastname;

    /** @var string Firstname */
    public $firstname;

    /** @var string Address first line */
    public $address1;

    /** @var string Address second line (optional) */
    public $address2;

    /** @var string Postal code */
    public $postcode;

    /** @var string City */
    public $city;

    /** @var string Any other useful information */
    public $other;

    /** @var string Phone number */
    public $phone;

    /** @var string Mobile phone number */
    public $phone_mobile;

    /** @var string VAT number */
    public $vat_number;

    /** @var string DNI number */
    public $dni;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /** @var boolean True if address has been deleted (staying in database as deleted) */
    public $deleted = 0;

    public $published = 1;

    protected static $_zone_ids = array();
    protected static $_country_ids = array();
    protected static $_database_required_fields = array();

    /**
     * Build an address
     *
     * @param null $address_id Existing address id in order to load object (optional)
     * @param null $lang_id
     */
    public function __construct($address_id = null, $lang_id = null){
        parent::__construct();
        if($address_id){
            //Load address from database if address id is provided
            $cache_id = 'jeproshop_address_model_' . $address_id . '_' . $lang_id;
            if(!JeproshopCache::isStored($cache_id)){
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_address') . " AS address ";
                $query .= " WHERE address.address_id = " . (int)$address_id;

                $db->setQuery($query);
                $address_data = $db->loadObject();
                if($address_data){
                    JeproshopCache::store($cache_id, $address_data);
                }
            }  else {
                $address_data = JeproshopCache::retrieve($cache_id);
            }

            if($address_data){
                $address_data->address_id = $address_id;
                foreach($address_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        /* Get and cache address country name */
        if ($this->address_id){
            $this->country = JeproshopCountryModelCountry::getNameById($lang_id ? $lang_id : JeproshopSettingModelSetting::getValue('default_lang'), $this->country_id);
        }
    }

    public static function getCustomerFirstAddressId($customer_id, $published = true){
        if (!$customer_id){ return false; }
        $cache_id = 'jeproshop_address_getFirstCustomerAddressId_'.(int)$customer_id . '_'.(bool)$published;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT " . $db->quoteName('address_id') . " FROM " . $db->quoteName('#__jeproshop_address') . " WHERE " . $db->quoteName('customer_id');
            $query .= " = " . (int)$customer_id . " AND " . $db->quoteName('deleted') . " = 0 " . ($published ? " AND " . $db->quoteName('published'). " =1" : "");

            $db->setQuery($query);
            $result = (int)$db->loadResult();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Get zone id for a given address
     *
     * @param int $address_id Address id for which we want to get zone id
     * @return integer Zone id
     */
    public static function getZoneIdByAddressId($address_id){
        if(!isset($address_id) || empty($address_id)){
            return false;
        }
        if (isset(self::$_zone_ids[$address_id]))
            return self::$_zone_ids[$address_id];

        $db = JFactory::getDBO();
        $query = "SELECT state." .  $db->quoteName('zone_id') . " AS zone_state_id, country." .  $db->quoteName('zone_id') . " FROM ";
        $query .=  $db->quoteName('#__jeproshop_address') . " AS address LEFT JOIN " .  $db->quoteName('#__jeproshop_country') . " AS ";
        $query .= "country ON country." .  $db->quoteName('country_id') . " = address." .  $db->quoteName('country_id') . " LEFT JOIN ";
        $query .=  $db->quoteName('#__jeproshop_state') . " AS state ON state." .  $db->quoteName('state_id') . " = address.";
        $query .=  $db->quoteName('state_id') . " WHERE address." .  $db->quoteName('address_id') . " = " .(int)$address_id;

        $db->setQuery($query);
        $result = $db->loadObject();

        self::$_zone_ids[$address_id] = (int)((int)$result['id_zone_state'] ? $result['id_zone_state'] : $result['id_zone']);
        return self::$_zone_ids[$address_id];
    }

    /**
     * Specify if an address is already in base
     *
     * @param int $address_id Address id
     * @return boolean
     */
    public static function addressExists($address_id){
        $key = 'address_exists_'.(int)$address_id;
        if (!JeproshopCache::isStored($key)){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('address_id') . " FROM " . $db->quoteName('#__jeproshop_address');
            $query .= " AS address WHERE address." . $db->quoteName('address_id') . " = " . (int)$address_id;

            $db->setQuery($query);
            $address_id = $db->loadResult();
            JeproshopCache::store($key, (bool)$address_id);
        }
        return Cache::retrieve($key);
    }

    public function validateController($htmlentities = true){
        $this->cacheDatabaseRequiredFields();
        $errors = array();
        $databaseRequiredFields = (isset(self::$_database_required_fields[get_class($this)])) ? self::$_database_required_fields[get_class($this)] : array();
        $fields = JeproshopTools::getTableFields('#__jeproshop_address');
        $input = JRequest::get('post');
        foreach ($fields as $field => $data){
            $value = isset($input[$data->Field]) ? $input[$data->Field] : $this->{$data->Field};
            // Check if field is required by user
            if (in_array($data->Field, $databaseRequiredFields))
                $data->Null = true;

            // Checking for required fields
            if (isset($data->Null) && $data->Null && empty($value) && $value !== '0') {
                if (!$this->customer_id || $data->Field != 'passwd')
                    $errors[$data->Field] = '<b>' . self::displayFieldName($field, get_class($this), $htmlentities) . '</b> ' . JText::_('COM_JEPROSHOP_IS_REQUIRED_LABEL');
            }
            // Checking for maximum fields sizes
            preg_match('#\((.*)\)#', $data->Type, $matches);

            if (isset($matches[1]) && !empty($value) && strlen($value) > $matches[1])
                $errors[$data->Field] = sprintf(
                    Tools::displayError('%1$s is too long. Maximum length: %2$d'),
                    self::displayFieldName($data->Field, get_class($this), $htmlentities),
                    $matches[1]
                );

            // Checking for fields validity
            // Hack for postcode required for country which does not have postcodes
            if (!empty($value) || $value === '0' || ($data->Field == 'postcode' && $value == '0')){
                if (isset($data->validate) && !JeproshopTools::$data['validate']($value) && (!empty($value) || $data->Null))
                    $errors[$field] = '<b>'.self::displayFieldName($field, get_class($this), $htmlentities).'</b> ' . JText::_('COM_JEPROSHOP_IS_INVALID_LABEL');
                else
                {
                    if (isset($data->copy_post) && !$data['copy_post'])
                        continue;
                    if ($data->Field == 'passwd')
                    {
                        if ($value = $input[$data->Field])
                            $this->{$field} = JeproshopTools::encrypt($value);
                    }
                    else
                        $this->{$field} = $value;
                }
            }
        }
        return $errors;
    }

    public function cacheDatabaseRequiredFields(){
        if (!is_array(self::$_database_required_fields))
        {
            $fields = JeproshopTools::getDatabaseRequiredFields(true, 'address');
            if ($fields)
                foreach ($fields as $row)
                    self::$_database_required_fields[$row->object_name][(int)$row->required_fiel_id] = pSQL($row->field_name);
            else
                self::$_database_required_fields = array();
        }
    }

    public static function displayFieldName($field, $class = __CLASS__, $htmlentities = true, JeproshopContext $context = null)
    {
        /*global $_FIELDS;

        if(!isset($context))
            $context = JeproshopContext::getContext();

        if ($_FIELDS === null && file_exists(_PS_TRANSLATIONS_DIR_.$context->language->iso_code.'/fields.php'))
            include_once(_PS_TRANSLATIONS_DIR_.$context->language->iso_code.'/fields.php');

        $key = $class.'_'.md5($field);
        return ((is_array($_FIELDS) && array_key_exists($key, $_FIELDS)) ? ($htmlentities ? htmlentities($_FIELDS[$key], ENT_QUOTES, 'utf-8') : $_FIELDS[$key]) : $field);*/
    }
}


class JeproshopAddressFormatModelAddressFormat extends JModelLegacy
{
    /** @var integer */
    public $address_format_id;

    /** @var integer */
    public $country_id;

    /** @var string */
    public $format;

    protected $_errorFormatList = array();

    public static $requireFormFieldsList = array( 'firstname', 'name', 'address1', 'city', 'postcode',  'JeproshopCountryModelCountry:name', 'JeproshopStateModelState:name');

    public static $forbiddenPropertyList = array(
        'deleted',  'date_add', 'alias', 'secure_key', 'note', 'newsletter', 'ip_registration_newsletter', 'newsletter_date_add',
        'optin','passwd', 'last_passwd_gen', 'published', 'is_guest', 'date_upd', 'country', 'years', 'days', 'months', 'description',
        'meta_description', 'short_description', 'link_rewrite', 'meta_title', 'meta_keywords', 'display_tax_label',
        'need_zip_code', 'contains_states', 'call_prefixes', 'show_public_prices', 'max_payment', 'max_payment_days',
        'geoloc_postcode', 'logged', 'account_number', 'groupBox', 'ape', 'max_payment', 'outstanding_allow_amount',
        'call_prefix', 'definition', 'debug_list'
    );

    public static $forbiddenClassList = array(
        'Manufacturer',
        'Supplier');

    const _CLEANING_REGEX_ = '#([^\w:_]+)#i';

    /*
	 * Returns the formatted fields with associated values
	 *
	 * @address is an instantiated Address object
	 * @addressFormat is the format
	 * @return double Array
	 */
    public static function getFormattedAddressFieldsValues($address, $addressFormat, $lang_id = null) {
        if (!$lang_id)
            $lang_id = JeproshopContext::getContext()->language->lang_id;
        $tab = array();
        $temporaryObject = array();

        // Check if $address exist and it's an instantiate object of JeproshopAddressModelAddress
        if ($address && ($address instanceof JeproshopAddressModelAddress)){
            foreach ($addressFormat as $line){
                if (($keyList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY)) && is_array($keyList)){
                    foreach ($keyList as $pattern){
                        if ($associateName = explode(':', $pattern)){
                            $totalName = count($associateName);
                            if ($totalName == 1 && isset($address->{$associateName[0]})){
                                $tab[$associateName[0]] = $address->{$associateName[0]};
                            }else {
                                $tab[$pattern] = '';

                                // Check if the property exist in both classes
                                if (($totalName == 2) && class_exists($associateName[0]) && property_exists($associateName[0], $associateName[1]) && property_exists($address, strtolower($associateName[0]) . '_id')) {
                                    $idFieldName = strtolower($associateName[0]) . '_id';

                                    if (!isset($temporaryObject[$associateName[0]])){
                                        $temporaryObject[$associateName[0]] = new $associateName[0]($address->{$idFieldName});
                                    }
                                    if ($temporaryObject[$associateName[0]]){
                                        $tab[$pattern] = (is_array($temporaryObject[$associateName[0]]->{$associateName[1]})) ?
                                            ((isset($temporaryObject[$associateName[0]]->{$associateName[1]}[$lang_id])) ?
                                                $temporaryObject[$associateName[0]]->{$associateName[1]}[$lang_id] : '') :
                                            $temporaryObject[$associateName[0]]->{$associateName[1]};
                                    }
                                }
                            }
                        }
                    }
                    JeproshopAddressFormatModelAddressFormat::setOriginalDisplayFormat($tab, $line, $keyList);
                }
            }
        }
        JeproshopAddressFormatModelAddressFormat::cleanOrderedAddress($addressFormat);
        // Free the instantiate objects
        foreach ($temporaryObject as &$object)
            unset($object);
        return $tab;
    }

    /**
     * Generates the full address text
     * @param JeproshopAddressModelAddress $address is an instance object of Address class
     * @param Array $patternRules is a defined rules array to avoid some pattern
     * @param String $newLine is a string containing the newLine format
     * @param String $separator is a string containing the separator format
     * @return string
     */
    public static function generateAddress(JeproshopAddressModelAddress $address, $patternRules = array(), $newLine = "\r\n", $separator = ' ', $style = array()){
        $addressFields = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields($address->country_id);
        $addressFormattedValues = JeproshopAddressFormatModelAddressFormat::getFormattedAddressFieldsValues($address, $addressFields);

        $addressText = '';
        foreach ($addressFields as $line)
            if (($patternsList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY)))
            {
                $tmpText = '';
                foreach ($patternsList as $pattern)
                    if ((!array_key_exists('avoid', $patternRules)) ||
                        (array_key_exists('avoid', $patternRules) && !in_array($pattern, $patternRules['avoid'])))
                        $tmpText .= (isset($addressFormattedValues[$pattern]) && !empty($addressFormattedValues[$pattern])) ?
                            (((isset($style[$pattern])) ?
                                    (sprintf($style[$pattern], $addressFormattedValues[$pattern])) :
                                    $addressFormattedValues[$pattern]).$separator) : '';
                $tmpText = trim($tmpText);
                $addressText .= (!empty($tmpText)) ? $tmpText.$newLine: '';
            }

        $addressText = preg_replace('/'.preg_quote($newLine,'/').'$/i', '', $addressText);
        $addressText = rtrim($addressText, $separator);

        return $addressText;
    }

    public static function generateAddressSmarty($params, &$smarty)
    {
        return AddressFormat::generateAddress(
            $params['address'],
            (isset($params['patternRules']) ? $params['patternRules'] : array()),
            (isset($params['newLine']) ? $params['newLine'] : "\r\n"),
            (isset($params['separator']) ? $params['separator'] : ' '),
            (isset($params['style']) ? $params['style'] : array())
        );
    }

    /**
     * Returns selected fields required for an address in an array according to a selection hash
     *
     * @param $className
     * @return array String values
     */
    public static function getValidateFields($className){
        $propertyList = array();

        if (class_exists($className)){
            $object = new $className();
            $reflect = new ReflectionObject($object);

            // Check if the property is accessible
            $publicProperties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($publicProperties as $property)
            {
                $propertyName = $property->getName();
                if ((!in_array($propertyName, AddressFormat::$forbiddenPropertyList)) &&
                    (!preg_match('#id|id_\w#', $propertyName)))
                    $propertyList[] = $propertyName;
            }
            unset($object);
            unset($reflect);
        }
        return $propertyList;
    }

    /*
     * Return a list of liable class of the className
     */
    public static function getLiableClass($className){
        $objectList = array();

        if (class_exists($className))
        {
            $object = new $className();
            $reflect = new ReflectionObject($object);

            // Get all the name object liable to the Address class
            $publicProperties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($publicProperties as $property)
            {
                $propertyName = $property->getName();
                if (preg_match('#id_\w#', $propertyName) && strlen($propertyName) > 3)
                {
                    $nameObject = ucfirst(substr($propertyName, 3));
                    if (!in_array($nameObject, self::$forbiddenClassList) &&
                        class_exists($nameObject))
                        $objectList[$nameObject] = new $nameObject();
                }
            }
            unset($object);
            unset($reflect);
        }
        return $objectList;
    }

    /**
     * Returns address format fields in array by country
     *
     * @param int $country_id PS_COUNTRY.id if null using default country
     * @param bool $split_all
     * @param bool $cleaned
     * @return Array String field address format
     */
    public static function getOrderedAddressFields($country_id = 0, $split_all = false, $cleaned = false)  {
        $out = array();
        $field_set = explode("\n", JeproshopAddressFormatModelAddressFormat::getAddressCountryFormat($country_id));
        foreach ($field_set as $field_item){
            if ($split_all){
                $keyList = array();
                if ($cleaned){
                    $keyList = ($cleaned) ? preg_split(self::_CLEANING_REGEX_, $field_item, -1, PREG_SPLIT_NO_EMPTY) : explode(' ', $field_item);
                }
                foreach ($keyList as $word_item){
                    $out[] = trim($word_item);
                }
            }
            else{
                $out[] = ($cleaned) ? implode(' ', preg_split(self::_CLEANING_REGEX_, trim($field_item), -1, PREG_SPLIT_NO_EMPTY)) : trim($field_item);
            }
        }
        return $out;
    }

    /***
     ** Return a data array containing ordered, formattedValue and object fields
     * @param $address
     * @return array
     */
    public static function getFormattedLayoutData($address){
        $layoutData = array();

        if ($address && $address instanceof JeproshopAddressModelAddress){
            $layoutData['ordered'] = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields((int)$address->country_id);
            $layoutData['formatted'] = JeproshopAddressFormatModelAddressFormat::getFormattedAddressFieldsValues($address, $layoutData['ordered']);
            $layoutData['object'] = array();

            $reflect = new ReflectionObject($address);
            $public_properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
            foreach ($public_properties as $property)
                if (isset($address->{$property->getName()}))
                    $layoutData['object'][$property->getName()] = $address->{$property->getName()};
        }
        return $layoutData;
    }

    /**
     * Returns address format by country if not defined using default country
     *
     * @param Integer PS_COUNTRY.id
     * @return String field address format
     */
    public static function getAddressCountryFormat($country_id = 0) {
        $country_id = (int)$country_id;

        $tmp_obj = new JeproshopAddressFormatModelAddressFormat();
        $tmp_obj->country_id = $country_id;
        $out = $tmp_obj->getFormat($tmp_obj->country_id);
        $tmp_obj = null;
        return $out;
    }

    /**
     * Returns address format by country
     *
     * @param Integer PS_COUNTRY.id
     * @return String field address format
     */
    public function getFormat($country_id){
        $out = $this->getFormatFromDataBase($country_id);
        if(empty($out))
            $out = $this->getFormatFromDataBase(JeproshopSettingModelSetting::getValue('default_country'));
        return $out;
    }

    protected function getFormatFromDataBase($country_id) {
        $cache_key = 'jeproshop_address_format_get_formatDB_' .$country_id;
        if (!JeproshopCache::isStored($cache_key)){
            $db = JFactory::getDBO();

            $query = "SELECT format FROM " . $db->quoteName('#__jeproshop_address_format') . " WHERE " . $db->quoteName('country_id') . " = " .(int)$country_id;

            $db->setQuery($query);
            $format = $db->loadResult();
            JeproshopCache::store($cache_key, trim($format));

        }
        return JeproshopCache::retrieve($cache_key);
    }

    /*
    ** Cleaned the layout set by the user
    */
    public static function cleanOrderedAddress(&$orderedAddressField){
        foreach ($orderedAddressField as &$line) {
            $cleanedLine = '';
            if (($keyList = preg_split(self::_CLEANING_REGEX_, $line, -1, PREG_SPLIT_NO_EMPTY))){
                foreach ($keyList as $key)
                    $cleanedLine .= $key.' ';
                $cleanedLine = trim($cleanedLine);
                $line = $cleanedLine;
            }
        }
    }
}