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

class JeproshopCustomerModelCustomer extends JModelLegacy
{
    public $customer_id;

    public $shop_id;

    public $shop_group_id;

    public $secure_key;

    public $note;

    public $gender_id = 0;

    public $default_group_id;

    public $lang_id;

    public $lastname;

    public $firstname;

    public $birthday = null;

    public $title;

    public $show_public_prices = 0;

    public $email;

    public $newsletter;

    public $ip_registration_newsletter;

    public $newsletter_date_add;

    public $optin;

    public $website;

    public $company;

    public $siret;

    public $ape;

    public $date_add;
    public $date_upd;

    /** @var boolean Status */
    public $is_guest = 0;

    /** @var int customer country_id as determined by geolocation */
    public $geoloc_country_id;
    /** @var int customer state_id as determined by geolocation */
    public $geoloc_state_id;
    /** @var string customer postcode as determined by geolocation */
    public $geoloc_postcode;

    /** @var boolean is the customer logged in */
    public $logged = 0;

    /** @var int id_guest meaning the guest table, not the guest customer  */
    public $guest_id;

    public $groupBox;

    /** @var float Outstanding allow amount (B2B opt)  */
    public $outstanding_allow_amount = 0;

    /** @var int Risk ID (B2B opt) */
    public $risk_id;

    /** @var integer Max payment day */
    public $max_payment_days = 0;

    /** @var integer Password */
    public $passwd;

    /** @var string Datetime Password */
    public $last_passwd_gen;

    /** @var boolean Status */
    public $published= true;

    /** @var boolean True if carrier has been deleted (staying in database as deleted) */
    public $deleted = 0;

    public $years;
    public $days;
    public $months;

    protected static $_customer_groups = array();
    protected static $_customerHasAddress = array();
    protected static $_database_required_fields = array();

    public function __construct($customer_id = NULL){
        $this->default_group_id = JeproshopSettingModelSetting::getValue('customer_group');
        if($customer_id){
            $cache_id = 'jeproshop_customer_model_' . $customer_id . ( $this->shop_id ? '_' . $this->shop_id : '');
            if(!JeproshopCache::isStored($cache_id)){
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_customer') . " AS customer ";
                $where = "";
            /** get language information ** /
            if($lang_id){
            $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_order_lang') . " AS order_lang ";
            $query .= "ON (ord.order_id = order_lang.order_id AND order_lang.lang_id = " . (int)$lang_id . ") ";
            if($this->shop_id && !(empty($this->multiLangShop))){
            $where = " AND order_lang.shop_id = " . $this->shop_id;
            }
            }

            /** Get shop informations **/
                if(JeproshopShopModelShop::isTableAssociated('order')){
                $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_order_shop') . " AS order_shop ON (";
                $query .= "ord.order_id = order_shop.order_id AND order_shop.shop_id = " . (int)  $this->shop_id . ")";
            }
            $query .= " WHERE customer.customer_id = " . (int)$customer_id . $where;

            $db->setQuery($query);
            $customer_data = $db->loadObject();

            if($customer_data){
                /*if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                    $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_lang');
                    $query .= " WHERE order_id = " . (int)$order_id;

                    $db->setQuery($query);
                    $order_lang_data = $db->loadObjectList();
                    if($order_lang_data){
                        foreach ($order_lang_data as $row){
                            foreach($row as $key => $value){
                                if(array_key_exists($key, $this) && $key != 'order_id'){
                                    if(!isset($order_data->{$key}) || !is_array($order_data->{$key})){
                                        $order_data->{$key} = array();
                                    }
                                    $order_data->{$key}[$row->lang_id] = $value;
                                }
                            }
                        }
                    }
                    }*/
                JeproshopCache::store($cache_id, $customer_data);
                }
            }else{
                $customer_data = JeproshopCache::retrieve($cache_id);
            }

            if($customer_data){
                $customer_data->order_id = $customer_id;
                foreach($customer_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    /**
     * Check if an address is owned by a customer
     *
     * @param integer $customer_id Customer ID
     * @param integer $address_id Address ID
     * @return boolean result
     */
    public static function customerHasAddress($customer_id, $address_id) {
        $key = (int)$customer_id .'_' . (int)$address_id;
        if (!array_key_exists($key, self::$_customerHasAddress)){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('address_id') . " FROM " . $db->quoteName('#__jeproshop_address') . " WHERE " . $db->quoteName('customer_id');
            $query .= " = " . (int)$customer_id . " AND " . $db->quoteName('address_id') . " = " . (int)$address_id . " ANN " . $db->quoteName('deleted') . " = 0";

            $db->setQuery($query);
            self::$_customerHasAddress[$key] = (bool)$db->loadResult();
        }
        return self::$_customerHasAddress[$key];
    }

    /**
     * Check customer informations and return customer validity
     *
     * @since 1.5.0
     * @param boolean $with_guest
     * @return boolean customer validity
     */
    public function isLogged($with_guest = false){
        if (!$with_guest && $this->is_guest == 1){
            return false;
        }

        /* Customer is valid only if it can be load and if object password is the same as database one */
        if ($this->logged == 1 && $this->customer_id && JeproshopValidator::isUnsignedInt($this->customer_id) && JeproshopCustomerModelCustomer::checkPassword($this->customer_id, $this->passwd)){
            return true;
        }
        return false;
    }

    public static function customerIdExistsStatic($customer_id){
        $cache_id = 'jeproshop_customer_exists_id_'.(int)$customer_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('customer_id') . " FROM " . $db->quoteName('#__jeproshop_customer') . " AS customer WHERE customer." . $db->quoteName('customer_id') . " = " . (int)$customer_id;
            $db->setQuery($query);
            $result = $db->loadResult();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Soft logout, delete everything links to the customer
     * but leave there affiliate's informations
     **/
    public function mylogout(){
        if (isset(JeproshopContext::getContext()->cookie)){
            JeproshopContext::getContext()->cookie->mylogout();
        }
        $this->logged = 0;
    }

    public function isGuest(){
        return (bool)$this->is_guest;
    }

    /**
     * Check if e-mail is already registered in database
     *
     * @param string $email e-mail
     * @param $return_id boolean
     * @param $ignore_guest boolean, to exclude guest customer
     * @return Customer ID if found, false otherwise
     */
    public static function customerExists($email, $return_id = false, $ignore_guest = true){
        if (!JeproshopTools::isEmail($email)){
            if (defined('COM_JEPROSHOP_DEV_MODE') && COM_JEPROSHOP_DEV_MODE)
                die (JError::raiseError('Invalid email'));
            else
                return false;
        }
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('customer_id') . " FROM " . $db->quoteName('#__jeproshop_customer');
        $query .= " WHERE " . $db->quoteName('email') . " = " . $db->quote($db->escape($email));
        $query .= JeproshopShopModelShop::addSqlRestriction(JeproshopShopModelShop::SHARE_CUSTOMER);
        $query .= ($ignore_guest ? " AND " . $db->quoteName('is_guest') . " = 0" : "");

        $db->setQuery($query);
        $result = $db->loadObject();

        if ($return_id)
            return $result->customer_id;
        return isset($result->customer_id);
    }

    public function add(){
        $this->shop_id = ($this->shop_id) ? $this->shop_id : JeproshopContext::getContext()->shop->shop_id;
        $this->shop_group_id = ($this->shop_group_id) ? $this->shop_group_id : JeproshopContext::getContext()->shop->shop_group_id;
        $this->lang_id = ($this->lang_id) ? $this->lang_id : JeproshopContext::getContext()->language->lang_id;
        $this->birthday = (empty($this->years) ? $this->birthday : (int)$this->years.'-'.(int)$this->months.'-'.(int)$this->days);
        $this->secure_key = md5(uniqid(rand(), true));
        $this->last_passwd_gen = date('Y-m-d H:i:s', strtotime('-'.JeproshopSettingModelSetting::getValue('password_regeneration_delay').' minutes'));

        if ($this->newsletter && !JeproshopTools::isDate($this->newsletter_date_add))
            $this->newsletter_date_add = date('Y-m-d H:i:s');

        if ($this->default_group_id == JeproshopSettingModelSetting::getValue('customer_group')) {
            if ($this->is_guest) {
                $this->default_group_id = (int)JeproshopSettingModelSetting::getValue('guest_group');
            } else {
                $this->default_group_id = (int)JeproshopSettingModelSetting::getValue('customer_group');
            }
        }
        /* Can't create a guest customer, if this feature is disabled */
        if ($this->is_guest && !JeproshopSettingModelSetting::getValue('enable_guest_checkout')) {
            return false;
        }
        $this->date_add = date('Y-m-d H:i:s');
        $this->date_upd = date('Y-m-d H:i:s');

        $db = JFactory::getDBO();
        $input = JRequest::get('get');
        $isNewCustomer = isset($input['is_new_customer']) ? 1 : 0;

        $email = isset($input['email']) ? $input['email'] : '';
        $company = '';
        if (!$isNewCustomer){
            $password = md5(time() . COM_JEPROSHOP_COOKIE_KEY);
        }else {
            $password = md5($input['passwd'] . COM_JEPROSHOP_COOKIE_KEY);
        }
        $siret = isset($input['siret']) ? $input['siret'] : '';
        $ape = isset($input['ape']) ? $input['ape'] : '';
        $title = isset($input['title']) ? $input['title'] : '';
        $firstName = isset($input['firstname']) ? $input['firstname'] : '';
        $lastName = isset($input['lastname']) ? $input['lastname'] : '';
        $deleted = isset($input['deleted']) ? $input['deleted'] : 0;

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_customer') . "(" . $db->quoteName('shop_group_id') . ", " . $db->quoteName('shop_id') . ", " . $db->quoteName('default_group_id') . ", " . $db->quoteName('lang_id') . ", ";
        $query .= $db->quoteName('company') . ", " . $db->quoteName('siret') . ", " . $db->quoteName('ape') . ", " . $db->quoteName('title') . ", "  . $db->quoteName('firstname') . ", " . $db->quoteName('lastname') . ", " ;
        $query .= $db->quoteName('email') . ", " . $db->quoteName('passwd') . ", " . $db->quoteName('last_passwd_gen') . ", " . $db->quoteName('secure_key') . ", "  . $db->quoteName('published') . ", " . $db->quoteName('is_guest') . ", ";
        $query .= $db->quoteName('deleted') . ", " . $db->quoteName('date_add') . ", " . $db->quoteName('date_upd') . ") VALUES (" . (int)$this->shop_group_id . ", " . (int)$this->shop_id . ", " . (int)$this->default_group_id . ", " ;
        $query .= (int)$this->lang_id . ", " . $db->quote($company) . ", " . $db->quote($siret) . ", "  . $db->quote($ape) . ", "  . $db->quote($title) . ", "  . $db->quote($firstName) . ", " . $db->quote($lastName) . ", ";
        $query .= $db->quote($email) . ", " . $db->quote($password) . ", " . $db->quote($this->last_passwd_gen) . ", " . $db->quote($this->secure_key) . ", " . (int)$this->published . ", " . (int)$this->is_guest . ", " . (int)$deleted;
        $query .= ", " . $db->quote($this->date_add) . ", " . $db->quote($this->date_upd) . ")";

        $db->setQuery($query);
        $success = $db->query();
        $this->customer_id = $db->insertid();
        $this->updateGroup($this->groupBox);
        return $success;
    }

    /**
     * Update customer groups associated to the object
     *
     * @param array $list groups
     */
    public function updateGroup($list){
        if ($list && !empty($list)){
            $this->cleanGroups();
            $this->addGroups($list);
        }else {
            $this->addGroups(array($this->default_group_id));
        }
    }

    public function cleanGroups() {
        $db = JFactory::getDBO();

        $query = "DELETE FROM " . $db->quoteName('#__jeproshop_.customer_group') . " WHERE " . $db->quoteName('customer_id') . " = " . (int)$this->customer_id;
        $db->setQuery($query);
        $db->query();
    }

    public function addGroups($groups){
        $db = JFactory::getDBO();
        foreach ($groups as $group) {
            $query = "INSERT INTO " . $db->quoteName('#__jeproshop_customer_group') . "(" . $db->quoteName('customer_id') . ", " . $db->quoteName('group_id') . ") VALUES ( " . (int)$this->customer_id . ", " . (int)$group .")";
            $db->setQuery($query);
            $db->query();
        }
    }

    public function validateController($htmlentities = true){
        $this->cacheDatabaseRequiredFields();
        $errors = array();
        $databaseRequiredFields = (isset(self::$_database_required_fields[get_class($this)])) ? self::$_database_required_fields[get_class($this)] : array();
        $fields = JeproshopTools::getTableFields('#__jeproshop_customer');
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

            if (isset($matches[1]) && !empty($value) && strlen($value) > $matches[1]) {/*
                $errors[$data->Field] = sprintf(
                    Tools::displayError('%1$s is too long. Maximum length: %2$d'),
                    self::displayFieldName($data->Field, get_class($this), $htmlentities),
                    $matches[1]
                ); */
            }

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

    public function cacheDatabaseRequiredFields()
    {
        if (!is_array(self::$_database_required_fields))
        {
            $fields = JeproshopTools::getDatabaseRequiredFields(true, 'customer');
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

class JeproshopCustomerMessageModelCustomerMessage extends JModelLegacy
{
    public $customer_message_id;
    public $customer_thread_id;
    public $employee_id;
    public $message;
    public $file_name;
    public $ip_address;
    public $user_agent;
    public $private;
    public $date_add;
    public $date_upd;
    public $read;

    public static function getMessagesByOrderId($order_id, $private = true) {
        $db = JFactory::getDBO();

        $query = "SELECT customer_message.*, customer." . $db->quoteName('firstname') . " AS customer_firstname, customer." . $db->quoteName('lastname');
        $query .= "AS customer_lastname, employee." . $db->quoteName('username') . " AS employee_firstname, employee." . $db->quoteName('name');
        $query .= " AS employee_lastname, (COUNT(customer_message.customer_message_id) = 0 AND customer_thread.customer_id != 0) AS is_new_for_me FROM ";
        $query .= $db->quoteName('#__jeproshop_customer_message') . " AS customer_message LEFT JOIN " .  $db->quoteName('#__jeproshop_customer_thread');
        $query .= " AS customer_thread ON customer_thread." . $db->quoteName('customer_thread_id') . " = customer_message." . $db->quoteName('customer_thread_id');
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_customer') . " AS customer ON customer_thread." . $db->quoteName('customer_id') . " = customer.";
        $query .= $db->quoteName('customer_id') . " LEFT OUTER JOIN " .  $db->quoteName('#__users') . " AS employee ON employee." . $db->quoteName('id') . " = customer_message.";
        $query .= $db->quoteName('employee_id') . " WHERE customer_thread.order_id = " .(int)$order_id .(!$private ? " AND customer_message." . $db->quoteName('private') . "= 0" : "");
        $query .= "	GROUP BY customer_message.customer_message_id ORDER BY customer_message.date_add DESC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getTotalCustomerMessages($where = null)
    {
        if (is_null($where))
            return (int)Db::getInstance()->getValue('
				SELECT COUNT(*)
				FROM '._DB_PREFIX_.'customer_message
			');
        else
            return (int)Db::getInstance()->getValue('
				SELECT COUNT(*)
				FROM '._DB_PREFIX_.'customer_message
				WHERE '.$where
            );
    }

    public function delete(){
        if (!empty($this->file_name))
            @unlink(_PS_UPLOAD_DIR_.$this->file_name);
        return parent::delete();
    }
}