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

class JeproshopShopModelShop extends JModelLegacy
{
    /** @var integer Description */
    public $shop_id;

    /** @var integer Description */
    public $shop_group_id;

    /** @var integer Description */
    public $category_id;

    /** @var integer Description */
    public $theme_id;

    /** @var type Description */
    public $name;

    /** @var boolean Description */
    public $published = true;

    /** @var boolean Description */
    public $deleted;

    /** @var String  JeproshopShopModelShop theme name **/
    public $theme_name;

    /** @var String  JeproshopShopModelShop theme directory **/
    public $theme_directory ;

    /** @var String physical uri of the main url (read only) **/
    public $physical_uri;

    /** @var String virtual uri of the main url (read only) **/
    public $virtual_uri;

    /** @var String main url domain (read only) **/
    public $domain;

    /** @var String main url for ssl domain (read only) **/
    public $ssl_domain;
    /** @var JeproshopShopGroupModelShopGroup **/
    protected $shop_group;

    /**
     * Some data can be shared between shops, like customers or orders
     */
    const SHARE_CUSTOMER = 'share_customer';
    const SHARE_ORDER = 'share_order';
    const SHARE_STOCK = 'share_stock';

    /** @var type Description */
    protected static $shop_context;

    /** @var type Description */
    public static $context_shop_id;

    /** @var type Description */
    protected static $context_shop_group_id;

    const CONTEXT_SHOP = 1;
    const CONTEXT_GROUP = 2;
    const CONTEXT_ALL = 4;

    protected static $shops;
    protected static $associated_tables = array();
    protected static $default_shop_tables_id = array();
    protected static $initialized = FALSE;

    public function __construct($shop_id = NULL, $lang_id = NULL) {
        parent::__construct();

        $db = JFactory::getDBO();

        if($lang_id !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($lang_id) != FALSE) ? $lang_id : JeproshopSettingModelSetting::getSettingValue('default_lang');
        }

        if($shop_id){
            $this->shop_id = (int)$shop_id;
            $this->get_shop_from_context = FALSE;
        }
/*
        if(!$this->shop_id){ 
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }*/

        if($shop_id){
            //load object from database if object id is present
            $cache_id = 'jeproshop_shop_model_' . $shop_id . '_' . $lang_id ;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_shop') . " AS shop ";
                $where = "";

                if($lang_id){ //Get lang informations
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_shop_lang') . " AS shop_lang ";
                    $query .= " ON (shop.shop_id = shop_lang.shop_id AND shop_lang.lang_id = " . (int)$lang_id . ")";
                    if($this->shop_id && !empty($this->multiLangShop)){
                        $where = " AND shop_lang.shop_id = " . $this->shop_id;
                    }
                }

                $query .= "WHERE shop.shop_id = " . (int)$shop_id . $where;

                $db->setQuery($query);

                $shop_data = $db->loadObject();
                if($shop_data){
                    if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_shop_lang') . " WHERE shop_id = ". (int)$shop_id;

                        $db->setQuery($query);
                        $shop_lang_data = $db->loadObjectList();
                        if($shop_lang_data){
                            foreach($shop_lang_data as $row){
                                foreach ($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'shop_id'){
                                        if(!isset($shop_data->{$key}) || !is_array($shop_data->{$key})){
                                            $shop_data->{$key} = array();
                                        }
                                        $shop_data->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cache_id, $shop_data);
                }
            }else{
                $shop_data = JeproshopCache::retrieve($cache_id);
            }

            if($shop_data){
                $shop_data->shop_id = (int)$shop_id;
                foreach($shop_data as $key => $value){
                    if(array_key_exists($key, $this)){ $this->{$key} = $value; }
                }
            }
        }
        if($this->shop_id){ $this->setShopUrl(); }
    }

    public function setShopUrl()    {
        $db = JFactory::getDBO();

        $query = "SELECT shop_url.physical_uri, shop_url.virtual_uri, shop_url.domain, shop_url.ssl_domain, t.theme_id, t.name, t.directory FROM ";
        $query .= $db->quoteName('#__jeproshop_shop') .  " AS shop LEFT JOIN " . $db->quoteName('#__jeproshop_shop_url') . " shop_url ON (shop.shop_id";
        $query .= " = shop_url.shop_id) LEFT JOIN " . $db->quoteName('#__jeproshop_theme') . " t ON (t.theme_id = shop.theme_id) WHERE shop.shop_id = ";
        $query .= (int)$this->shop_id . " AND shop.published = 1 AND shop.deleted = 0 AND shop_url.main = 1";

        $db->setQuery($query);
        if (!$row = $db->loadObject()){ return; }

        $this->theme_id = $row->theme_id;
        $this->theme_name = $row->name;
        $this->theme_directory = $row->directory;
        $this->physical_uri = $row->physical_uri;
        $this->virtual_uri = $row->virtual_uri;
        $this->domain = $row->domain;
        $this->ssl_domain = $row->ssl_domain;

        return true;
    }

    public static function initialize(){
        $app = JFactory::getApplication();
        $db = JFactory::getDBO();

        $shop_id = (int)$app->input->get("shop_id");
        //find current shop from url
        if(!$shop_id){
            $found_uri = '';
            $host = '';
            $request_uri = rawurldecode($_SERVER['REQUEST_URI']);

            $query = "SELECT shop." . $db->quoteName('shop_id') . ", CONCAT(shop_url.";
            $query .= $db->quoteName('physical_uri') . ", shop_url." .  $db->quoteName('virtual_uri');
            $query .= ") AS uri, shop_url." .  $db->quoteName('domain') . ", shop_url." ;
            $query .= $db->quoteName('main') . " FROM " .  $db->quoteName('#__jeproshop_shop_url');
            $query .= " AS shop_url LEFT JOIN " .  $db->quoteName('#__jeproshop_shop') . " AS shop ON ";
            $query .= "(shop.shop_id = shop_url.shop_id) WHERE (shop_url.domain = " . $db->quote($db->escape($host));
            $query .= " OR shop_url.ssl_domain = " . $db->quote($db->escape($host)) . ") AND shop.published = 1 AND ";
            $query .= "shop.deleted = 0 ORDER BY LENGTH (CONCAT(shop_url.physical_uri, shop_url.virtual_uri)) DESC";

            $db->setQuery($query);
            $results = $db->loadObjectList();
            $through = false;
            foreach($results as $result){
                if(preg_match('#^' . preg_quote($result->uri, '#') . '#i', $request_uri)){
                    $through = true;
                    $shop_id = $result->shop_id;
                    $found_uri =  $result->uri;
                    if($result->main){
                        $is_main_uri = true;
                    }
                    break;
                }
            }

            /** If an URL was found and it's not the main URL, redirect to main url  **/
            if($through && $shop_id &&!$is_main_uri){
                foreach ($results as $result){
                    if($result->shop_id == $shop_id && $result->main){
                        $request_uri = substr($request_uri, strlen($found_uri));
                        $url = str_replace('//', '/', $result->domain. $result->uri . $request_uri);
                        $redirect_type = JeproshopSettingModelSetting::getValue('canonical_redirect') == 2 ? '301' : '302';

                        exit();
                    }
                }
            }
        }

        if((!$shop_id) || JeproshopTools::isPHPCLI() || in_array(JeproshopTools::getHttpHost(), array(COM_JEPROSHOP_MEDIA_SERVER_1, COM_JEPROSHOP_MEDIA_SERVER_2, COM_JEPROSHOP_MEDIA_SERVER_3))){
            if((!$shop_id && JeproshopTools::isPHPCLI())){
                $shop_id = (int)JeproshopSettingModelSetting::getValue('default_shop');
            }
            $shop = new JeproshopShopModelShop($shop_id);

            if(!JeproshopTools::isLoadedObject($shop, 'shop_id')){
                $shop = new JeproshopShopModelShop((int)JeproshopSettingModelSetting::getValue('default_shop'));
            }

            $shop->physical_uri = preg_replace('#/+#', '/', str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))) . '/');
            $shop->virtual_uri = '';

            //Define some $_SERVER variables like HTTP_HOST
            if(JeproshopTools::isPHPCLI()){
                if(!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])){
                    $_SERVER['HTTP_HOST'] = $shop->domain;
                }
                if(!isset($_SERVER['SERVER_NAME']) || empty($_SERVER['SERVER_NAME'])){
                    $_SERVER['SERVER_NAME'] = $shop->domain;
                }
                if(!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADD'])){
                    $_SERVER['REMOTE_ADD'] =  '127.0.0.1';
                }
            }
        }else{
            $shop = new JeproshopShopModelShop($shop_id);
            if(!JeproshopTools::isLoadedObject($shop, 'shop_id') || !$shop->published){
                // No shop found too bad let's redirectg to default shop
                $default_shop = new JeproshopShopModelShop((int)JeproshopSettingModelSetting::getValue('default_shop'));

                if(!JeproshopTools::isLoadedObject($default_shop, 'shop_id')){
                    JError::raiseError(500, JText::_('COM_JEPROSHOP_NO SHOP_FOUN_MESSAGE'));
                }

                $inputs = $app->input;
                $inputs->set('shop_id', NULL);
                $url = $default_shop->domain;
                if(!JeproshopSettingModelSetting::getValue('rewrite_settings')){
                    $url .= $default_shop->getBaseUrl() . 'index.php?option=com_jeproshop' . JeproshopTools::buildHttpQuery($inputs);
                }else{
                    /** catch subdomain url "www" **/
                    if(strpos($url, 'www.') === 0 && 'www.' . $_SERVER['HTTP_HOST'] === $url || $_SERVER['HTTP_HOST'] === 'www.' . $url){
                        $url .= $_SERVER['REQUEST_URI'];
                    }else{
                        $url .= $default_shop->getBaseUrl();
                    }

                    if(count($inputs)){
                        $url .= '?option=com_jeproshop' . JeproshopTools::httpBuildQuery($inputs);
                    }
                }
                $redirect_type = JeproshopSettingModelSetting::getValue('canonical_redirect') == 2 ? '301' : '302';
                exit();
            }elseif(empty($shop->physical_uri)) {
                $default_shop  = new JeproshopShopModelShop((int)JeproshopSettingModelSetting::getValue('default_shop'));
                $shop->physical_uri = $default_shop->physical_uri;
                $shop->virtual_uri = $default_shop->virtual_uri;
            }
        }

        self::$context_shop_id =  $shop->shop_id;
        self::$context_shop_group_id =  $shop->shop_group_id;
        self::$shop_context = self::CONTEXT_SHOP;

        return $shop;
    }

    /**
     * Check if given table is associated to shop
     *
     * @param string $table
     * @return bool
     */
    public static function isTableAssociated($table){
        if(!JeproshopShopModelShop::$initialized){
            JeproshopShopModelShop::init();
        }
        return isset(JeproshopShopModelShop::$associated_tables[$table]) && JeproshopShopModelShop::$associated_tables[$table]['type'] == 'shop';
    }

    protected static function init(){
        JeproshopShopModelShop::$default_shop_tables_id = array('product', 'category');

        $associated_tables = array(
            'category' => array('type' => 'shop'),
            'category_lang' => array('type' => 'fk_shop'),
            'contact' => array('type' => 'shop'),
            'country' => array('type' => 'shop'),
            'currency' => array('type' => 'shop'),
            'employee' => array('type' => 'shop'),
            'image' => array('type' => 'shop'),
            'lang' => array('type' => 'shop'),
            'meta_lang' => array('type' => 'fk_shop'),
            'product' => array('type' => 'shop'),
            'product_attribute' => array('type' => 'shop'),
            'product_lang' => array('type' => 'fk_shop'),
            'referrer' => array('type' => 'shop'),
            'attribute' => array('type' => 'shop'),
            'feature' => array('type' => 'shop'),
            'group' => array('type' => 'shop'),
            'attribute_group' => array('type' => 'shop'),
            'tax_rules_group' => array('type' => 'shop'),
            'zone' => array('type' => 'shop'),
            'developer' => array('type' => 'shop')
        );

        foreach($associated_tables as $tale_name => $table_details){
            JeproshopShopModelShop::addTableAssociation($tale_name, $table_details);
        }

        JeproshopShopModelShop::$initialized = true;
    }

    /**
     * Add table associated to shop
     * @param string $table_name
     * @param array $table_details
     * @return boolean
     */
    private static function addTableAssociation($table_name, $table_details){
        if(!isset(JeproshopShopModelShop::$associated_tables[$table_name])){
            JeproshopShopModelShop::$associated_tables[$table_name] = $table_details;
        }else{
            return false;
        }
        return true;
    }

    /**
     * Add an sql join in query between a table and its associated table in multi-shop
     *
     * @param string $table Table name (E.g. product, module, etc)
     * @param bool $inner_join
     * @param null $on
     * @param null $force_not_default
     * @internal param $alias
     * @return string
     */
    public static function addSqlAssociation($table, $inner_join = true, $on = null, $force_not_default = null){
        $db = JFactory::getDBO();
        $table_alias = $table. '_shop';
        if(strpos($table, '.') !== false){
            list($table_alias, $table) = explode('.', $table);
        }

        if($table == 'group'){ $output_alias = 'grp'; }
        else{ $output_alias = $table; }

        $associated_table = JeproshopShopModelShop::getAssociatedTable($table);
        if($associated_table === false || $associated_table['type'] != 'shop'){ return; }

        $query = (($inner_join) ? " INNER " : " LEFT ") . "JOIN " . $db->quoteName('#__jeproshop_' . $table .'_shop') . " AS ";
        $query .= $table_alias . " ON( " . $table_alias . ".". $table . "_id = " . $output_alias . "." . $table . "_id";

        if((int)self::$context_shop_id){
            $query .= " AND " . $table_alias . ".shop_id = " . (int)self::$context_shop_id;
        }elseif(JeproshopShopModelShop::checkDefaultShopId($table) && !$force_not_default){
            $query .= " AND " . $table_alias . ".shop_id = " . $output_alias . ".default_shop_id";
        }else{
            $query .= " AND " . $table_alias . ".shop_id IN (" . implode(', ', JeproshopShopModelShop::getContextListShopId()) . ")" ;
        }
        $query .= (($on) ? " AND " . $on : "" ). ")";

        return $query;
    }

    public static function addSqlRestrictionOnLang($alias = NULL, $shop_id = NULL){
        if(isset(JeproshopContext::getContext()->shop) && is_null($shop_id)){
            $shop_id = (int)  JeproshopContext::getContext()->shop->shop_id;
        }

        if(!$shop_id){
            $shop_id = JeproshopSettingModelSetting::getValue('default_shop');
        }
        $db = JFactory::getDBO();
        return " AND " . ($alias ? $alias . "." : "") . $db->quoteName('shop_id') . " = " . (int)$shop_id;
    }

    /**
     * Get the associated table if available
     *
     * @param $table
     * @return array
     */
    public static function getAssociatedTable($table){
        if (!JeproshopShopModelShop::$initialized){
            JeproshopShopModelShop::init();
        }
        return (isset(JeproshopShopModelShop::$associated_tables[$table]) ? JeproshopShopModelShop::$associated_tables[$table] : false);
    }

    public static function getShopContext(){
        return self::$shop_context;
    }

    /*
	 * Get group  of the current shop
	* @return JeproshopShopGroupModelShopGroup
	*/
    public function getShopGroup(){
        if(!$this->shop_group){
            $this->shop_group = new JeproshopShopGroupModelShopGroup($this->shop_group_id);
        }
        return $this->shop_group;
    }

    public static function isFeaturePublished(){
        static $feature_published = null;

        if ($feature_published === null){
            $db = JFactory::getDBO();
            $query = "SELECT COUNT(*) FROM " . $db->quoteName('#__jeproshop_shop');
            $db->setQuery($query);
            $feature_published = JeproshopSettingModelSetting::getValue('multishop_feature_active') && (($db->loadResult()) > 1);
        }
        return $feature_published;
    }

    /**
     * Get root category of current shop
     * @return int
     */
    public function getCategoryId(){
        return ($this->category_id ? $this->category_id : 1 );
    }

    public function getBaseUrl(){
        if($this->domain){
            return FALSE;
        }
        return 'http://';
    }

    /**
     * Add an sql restriction for shops fields
     *
     * @param boolean $share If false, dont check share datas from group. Else can take a Shop::SHARE_* constant value
     * @param string $alias
     * @return string
     */
    public static function addSqlRestriction($share = false, $alias = null){
        if ($alias){
            $alias .= '.';
        }

        $group = JeproshopShopModelShop::getShopGroupFromShop(JeproshopShopModelShop::getContextShopID(), false);
        if ($share == JeproshopShopModelShop::SHARE_CUSTOMER && JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP && $group['share_customer']){
            $restriction = " AND ".$alias."shop_group_id = ".(int)  JeproshopShopModelShop::getContextShopGroupId();
        }else{
            $restriction = " AND ".$alias."shop_id IN (".implode(', ', JeproshopShopModelShop::getContextListShopIds($share)).") ";
        }
        return $restriction;
    }

    /**
     * Retrieve group ID of a shop
     *
     * @param int $shop_id Shop ID
     * @param bool $as_id
     * @return int Group ID
     */
    public static function getShopGroupFromShop($shop_id, $as_id = true){
        JeproshopShopModelShop::cacheShops();
        foreach (self::$shops as $group_id => $group_data)
            if (array_key_exists($shop_id, $group_data['shops']))
                return ($as_id) ? $group_id : $group_data;
        return false;
    }

    public static function getContextListShopIds($share = false){
        if(JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_SHOP){
            $list = ($share) ? JeproshopShopModelShop::getSharedShops(JeproshopShopModelShop::getContextShopId(), $share) : array(JeproshopShopModelShop::getContextShopId());
        } elseif(JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_GROUP) {
            $list = JeproshopShopModelShop::getShops(true, JeproshopShopModelShop::getContextShopGroupId(), true);
        }else{
            $list = JeproshopShopModelShop::getShops(TRUE, null, true);
        }
        return $list;
    }

    /**
     * Get current ID of shop group if context is CONTEXT_SHOP or CONTEXT_GROUP
     *
     * @param bool $null_value_without_multishop
     * @return int
     */
    public static function getContextShopGroupId($null_value_without_multishop = false){
        if ($null_value_without_multishop && !JeproshopShopModelShop::isFeaturePublished()){
            return null;
        }
        return self::$context_shop_group_id;
    }

    /**
     * If the shop group has the option $type activated, get all shops ID of this group, else get current shop ID
     *
     * @param int $shop_id
     * @param int $type Shop::SHARE_CUSTOMER | Shop::SHARE_ORDER
     * @return array
     */
    public static function getSharedShops($shop_id, $type){
        if (!in_array($type, array(JeproshopShopModelShop::SHARE_CUSTOMER, JeproshopShopModelShop::SHARE_ORDER, JeproshopShopModelShop::SHARE_STOCK))){
            die('Wrong argument ($type) in Shop::getSharedShops() method');
        }

        JeproshopShopModelShop::cacheShops();
        foreach (self::$shops as $group_data){
            if (array_key_exists($shop_id, $group_data['shops']) && $group_data[$type]){
                return array_keys($group_data['shops']);
            }
        }
        return array($shop_id);
    }

    public static function getContextShopId($null_value_without_multishop = false){
        if($null_value_without_multishop && !JeproshopShopModelShop::isFeaturePublished()){
            return null;
        }
        return self::$context_shop_id;
    }

    /**
     * Get shops list
     *
     * @param bool $published
     * @param int $shop_group_id
     * @param bool $get_as_list_id
     * @return array
     */
    public static function getShops($published = true, $shop_group_id = null, $get_as_list_id = false){
        JeproshopShopModelShop::cacheShops();

        $results = array();
        foreach (self::$shops as $group_id => $group_data){
            foreach ($group_data['shops'] as $shop_id => $shop_data){
                if((!$published || $shop_data->published) && (!$shop_group_id || $shop_group_id == $group_id)){
                    if ($get_as_list_id){
                        $results[$shop_id] = $shop_id;
                    }else{
                        $results[$shop_id] = $shop_data;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Load list of groups and shops, and cache it
     *
     * @param bool $refresh
     */
    public static function cacheShops($refresh = false){
        if (!is_null(self::$shops) && !$refresh){
            return;
        }

        self::$shops = array();
        $db = JFactory::getDBO();
        $from = "";
        $where = "";

        $employee = JeproshopContext::getContext()->employee;

        // If the profile isn't a superAdmin
        if (JeproshopTools::isLoadedObject($employee, 'employee_id') && $employee->profile_id != _PS_ADMIN_PROFILE_){
            $from .= " LEFT JOIN ". $db->quoteName('#__jeproshop_employee_shop') . " AS employee_shop ON employee_shop.shop_id = shop.shop_id";
            $where .= " AND employee_shop.employee_id = " . (int)$employee->employee_id;
        }

        $query = "SELECT shop_group.*, shop.*, shop_group.name AS group_name, shop.shop_name AS shop_name, ";
        $query .= " shop.published, shop_url.domain, shop_url.ssl_domain, shop_url.physical_uri, shop_url.";
        $query .= "virtual_uri FROM " . $db->quoteName('#__jeproshop_shop_group') . " AS shop_group LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_shop') . " AS shop ON shop.shop_group_id = shop_group.shop_group_id ";
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_shop_url') . " AS shop_url ON shop.shop_id =";
        $query .= " shop_url.shop_id AND shop_url.main = 1 " . $from . " WHERE shop.deleted = 0 AND shop_group.";
        $query .= "deleted = 0 " . $where . " ORDER BY shop_group.name, shop.shop_name";

        $db->setQuery($query);
        $results = $db->loadObjectList();

        if($results ){
            foreach ($results as $row){
                if (!isset(self::$shops[$row->shop_group_id])){
                    self::$shops[$row->shop_group_id] = array(
                        'shop_group_id' => $row->shop_group_id,
                        'name' => $row->group_name,
                        'share_customer' => $row->share_customer,
                        'share_order' => $row->share_order,
                        'share_stock' => $row->share_stock,
                        'shops' => array()
                    );

                    self::$shops[$row->shop_group_id]['shops'][$row->shop_id] = $row; /*array(
							'shop_id' => $row->shop_id,
							'shop_group_id' => $row->shop_group_id,
							'name' => $row->shop_name,
							'theme_id' => $row->theme_id,
							'category_id' => $row->category_id,
							'domain' => $row->domain,
							'ssl_domain' =>	$row->ssl_domain,
							'uri' =>  $row->physical_uri . $row->virtual_uri,
							'published' => $row->published
					);*/
                }
            }
        }
    }
}


/***** ---------- SHOP GROUP --------******/
class JeproshopShopGroupModelShopGroup extends JModelLegacy
{
    public $name;
    public $shop_group_id;
    public $published = true;
    public $share_customer;
    public $share_stock;
    public $share_order;
    public $deleted;

    public function __construct($shop_group_id = NULL) {
        $db = JFactory::getDBO();
        if($shop_group_id){
            /** Load object from database if shop group id is present **/
            $cache_id = 'jeproshop_shop_group_model_' . $shop_group_id;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_shop_group') . " AS shop_group ";
                $query .= " WHERE shop_group." . $db->quoteName('shop_group_id') . " = ". (int)$shop_group_id;

                $db->setQuery($query);
                $shop_group_data = $db->loadObject();
                if($shop_group_data){
                    JeproshopCache::store($cache_id, $shop_group_data);
                }
            }else{
                $shop_group_data = JeproshopCache::retrieve($cache_id);
            }

            if($shop_group_data){
                $shop_group_data->shop_group_id = $shop_group_id;
                foreach($shop_group_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
    }

    public static function getShopGroups($published = TRUE){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_shop_group') . " WHERE 1 ";
        if($published){
            $query .= " AND " . $db->quoteName('published') . " = " . $published;
        }

        $db->setQuery($query);
        $groups = $db->loadObjectList();
        return $groups;
    }
}