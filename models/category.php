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

class JeproshopCategoryModelCategory extends JModelLegacy
{
    public $category_id;

    public $lang_id;

    public $shop_id;

    public $name;

    public $published = true;

    public $position;

    public $description;

    public $parent_id;

    public $level_depth;

    public $n_left;

    public $n_right;

    public $link_rewrite;

    public $meta_title;
    public $meta_keywords;
    public $meta_description;

    public $date_add;
    public $date_upd;

    public $is_root_category;

    public $default_shop_id;

    public $groupBox;

    private $pagination;

    public $image_id = 'default';
    public $image_dir = '';

    public $multiLang = true;
    public $multiLangShop = true;

    protected $deleted_category = FALSE;

    protected static $_links = array();

    public function __construct($category_id = NULL, $lang_id = NULL, $shop_id = NULL) {
        parent::__construct();

        if($lang_id !== null){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($lang_id) !== FALSE) ? $lang_id : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($shop_id && $this->isMultiShop()){
            $this->shop_id = (int)$shop_id;
            $this->getShopFromContext = FALSE;
        }

        if($this->isMultiShop() && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }
        $db = JFactory::getDBO();

        if($category_id){
            /** load category from data base if id provided **/
            $cache_id = 'jeproshop_model_category_'. (int)$category_id . '_' . $lang_id . '_' . $shop_id;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_category') . " AS category ";
                $where = "";
                if($lang_id){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (";
                    $query .= "category.category_id = category_lang.category_id AND category_lang.lang_id = " . (int)$lang_id . ")";
                    if($this->shop_id && !empty($this->multiLangShop)){
                        $where .= " AND category_lang.shop_id = " . (int)  $this->shop_id;
                    }
                }
                /** Get Shop information **/
                if(JeproshopShopModelShop::isTableAssociated('category')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_category_shop') . " AS shop ON ( category.";
                    $query .= "category_id = shop.category_id AND shop.shop_id = " . (int)$this->shop_id . ")";
                }
                $query .= " WHERE category.category_id = " . (int)$category_id . $where;

                $db->setQuery($query);
                $category_data = $db->loadObject();
                if($category_data){
                    if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_category_lang') . " WHERE " . $db->quoteName('category_id');
                        $query .= (($this->shop_id && $this->isLangMultiShop()) ? " AND " . $db->quoteName('shop_id') . " = " . $this->shop_id : "");

                        $db->setQuery($query);
                        $category_lang_data = $db->loadObjectList();
                        if($category_lang_data){
                            foreach($category_lang_data as $row){
                                foreach ($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'category_id'){
                                        if(!isset($category_data->{$key}) || !is_array($category_data->{$key})){
                                            $category_data->{$key} = array();
                                        }
                                        $category_data->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cache_id, $category_data);
                }
            }else{
                $category_data = JeproshopCache::retrieve($cache_id);
            }

            if($category_data){
                $category_data->category_id = $category_id;
                foreach($category_data as $key =>$value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        $this->image_id = (file_exists(COM_JEPROSHOP_CATEGORY_IMAGE_DIRECTORY . (int)  $this->category_id . '.jpg')) ? (int)$this->category_id : FALSE;
        $this->image_dir = COM_JEPROSHOP_CATEGORY_IMAGE_DIRECTORY;
    }

    public static function getLinkRewrite($category_id, $lang_id){
        if (!JeproshopTools::isUnsignedInt($category_id) || !JeproshopTools::isUnsignedInt($lang_id))
            return false;

        if (!isset(self::$_links[$category_id . '_' . $lang_id])){
            $db = JFactory::getDBO();

            $query = "SELECT category_lang." . $db->quoteName('link_rewrite') . " FROM " . $db->quoteName('#__jeproshop_category_lang');
            $query .= " AS category_lang WHERE " . $db->quoteName('lang_id') . " = ".(int)$lang_id ;
			$query .= JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang') . " AND category_lang." . $db->quoteName('category_id');
            $query .= " = " . (int)$category_id;

            $db->setQuery($query);
            self::$_links[$category_id . '_' . $lang_id] = $db->loadResult();
        }
        return self::$_links[$category_id . '_' . $lang_id];
    }

    /**
     * Return current category products
     *
     * @param integer $lang_id Language ID
     * @param $limit_start
     * @param $limit
     * @param null $order_by
     * @param null $order_way
     * @param boolean $get_total return the number of results instead of the results them self
     * @param bool $published
     * @param boolean $random active a random filter for returned products
     * @param int $random_number_products number of products to return if random is activated
     * @param boolean $check_access set to false to return all products (even if customer hasn't access)
     * @param JeproshopContext $context
     * @internal param int $p Page number
     * @internal param int $n Number of products per page
     * @internal param bool $active return only active products
     * @return mixed Products or number of products
     */
    public function getProducts($lang_id, $limit_start, $limit, $order_by = null, $order_way = null, $get_total = false, $published = true, $random = false, $random_number_products = 1, $check_access = true, JeproshopContext $context = null) {
        if (!$context){ $context = JeproshopContext::getContext(); }
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        if ($check_access && !$context->controller->checkAccess($context->customer->customer_id, $this->category_id)){ return false; }

        if ($limit_start < 1){ $limit_start = 0; }

        if (empty($order_by)){
            $order_by = 'position';
        }else{
            /* Fix for all modules which are now using lowercase values for 'orderBy' parameter */
            $order_by = strtolower($order_by);
        }
        if (empty($order_way))
            $order_way = "ASC";

        $order_by_prefix = false;
        if ($order_by == 'product_id' || $order_by == 'date_add' || $order_by == 'date_upd'){
            $order_by_prefix = "product";
        }elseif ($order_by == 'name'){
            $order_by_prefix = "product_lang";
        }elseif ($order_by == 'manufacturer'){
            $order_by_prefix = "manufacturer";
            $order_by = "name";
        }elseif ($order_by == 'position'){
            $order_by_prefix = "product_category";
        }

        if ($order_by == 'price')
            $order_by = 'order_price';

        if (!JeproshopTools::isOrderBy($order_by) || !JeproshopTools::isOrderWay($order_way))
            die (JError::raiseError());

        $supplier_id = (int)$app->input->get('supplier_id');

        /* Return only the number of products */
        if ($get_total){
            $query = "SELECT COUNT(product_category." . $db->quoteName('product_id') . ") AS total FROM " . $db->quoteName('#__jeproshop_product') . " AS product ";
            $query .= JeproshopShopModelShop::addSqlAssociation('product') . " LEFT JOIN" . $db->quoteName('#__jeproshop_product_category') . " AS product_category ON";
            $query .= " product." . $db->quoteName('product_id') . " = product_category." . $db->quoteName('product_id') . " WHERE product_category." . $db->quoteName('category_id');
            $query .= " = " .(int)$this->category_id . " AND product_shop." . $db->quoteName('visibility') . " IN ('both', 'catalog') AND product_shop." . $db->quoteName('published');
            $query .= " = 1 " . ($supplier_id ? " AND product.supplier_id = ".(int)$supplier_id : "");

            $db->setQuery($query);
            return (int)$db->loadResult();
        }

        $number_days_new_product = JeproshopSettingModelSetting::getValue('number_days_new_product');
        $query = "SELECT product.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, MAX(product_attribute_shop.product_attribute_id) AS  ";
        $query .= "product_attribute_id, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity,  product_lang." . $db->quoteName('description');
        $query .= ", product_lang." . $db->quoteName('short_description') . ", product_lang." . $db->quoteName('available_now') . ", product_lang." . $db->quoteName('available_later');
        $query .= ", product_lang." . $db->quoteName('link_rewrite') . ", product_lang." . $db->quoteName('meta_description')  . ", product_lang." . $db->quoteName('meta_keywords') ;
        $query .= ", product_lang." . $db->quoteName('meta_title') . ", product_lang." . $db->quoteName('name') . ", MAX(image_shop." . $db->quoteName('image_id') . " ) AS ";
        $query .= "image_id, image_lang." . $db->quoteName('legend') . ", manufacturer." . $db->quoteName('name') . " AS manufacturer_name, category_lang." . $db->quoteName('name');
        $query .= " AS category_default, DATEDIFF(product_shop." . $db->quoteName('date_add') . ", DATE_SUB(NOW(), INTERVAL " . (JeproshopTools::isUnsignedInt($number_days_new_product) ? $number_days_new_product : 20);
        $query .= "	DAY)) > 0 AS new, product_shop.price AS order_price FROM " . $db->quoteName('#__jeproshop_product_category') . " AS product_category LEFT JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON product.";
        $query .= $db->quoteName('product_id') . " = product_category." . $db->quoteName('product_id') . JeproshopShopModelShop::addSqlAssociation('product') . " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute');
        $query .= " AS product_attribute ON (product." . $db->quoteName('product_id') . " = product_attribute." . $db->quoteName('product_id') . ") " . JeproshopShopModelShop::addSqlAssociation('product_attribute', false, 'product_attribute_shop.`default_on` = 1');
        $query .= JeproshopProductModelProduct::sqlStock('product', 'product_attribute_shop', false, $context->shop) . " LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang'). " AS category_lang ON (product_shop." . $db->quoteName('default_category_id');
        $query .= " = category_lang." . $db->quoteName('category_id') . " AND category_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id  . JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang');
        $query .= " AS product_lang ON (product." . $db->quoteName('product_id') . " = product_lang." . $db->quoteName('product_id') . " AND product_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang');
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_image') . " AS image ON (image." . $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . ") " . JeproshopShopModelShop::addSqlAssociation('image', false, 'image_shop.cover=1') . " LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image_shop." . $db->quoteName('image_id') . " = image_lang." . $db->quoteName('image_id') . " AND image_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_manufacturer') . " AS manufacturer ON manufacturer." . $db->quoteName('manufacturer_id') . " = product." . $db->quoteName('manufacturer_id')  . "	WHERE product_shop." . $db->quoteName('shop_id') . " = " .(int)$context->shop->shop_id;
        $query .= " AND product_shop." . $db->quoteName('published') . " = 1 AND product_shop." . $db->quoteName('visibility') . " IN ('both', 'catalog') AND product_category." . $db->quoteName('category_id') . " = " . (int)$this->category_id;
        $query .= ($supplier_id ? " AND product.supplier_id = " . (int)$supplier_id : "") . " GROUP BY product_shop.product_id";

        if ($random === true){
            $query .= " ORDER BY RAND() LIMIT " . (int)$random_number_products;
        }else{
            $query .= " ORDER BY " . (!empty($order_by_prefix) ? $order_by_prefix. "." : "") . $db->quoteName($order_by);
            $query .= " " . $order_way . " LIMIT ".((int)$limit_start) . ", " . (int)$limit;
        }

        $db->setQuery($query);
        $results = $db->loadObjectList();
        if ($order_by == 'order_price'){
            JeproshopTools::orderbyPrice($results, $order_way);
        }
        if (!$results){ return array(); }

        /* Modify SQL result */
        return JeproshopProductModelProduct::getProductsProperties($lang_id, $results);
    }

    public static function inShopStatic($category_id, JeproshopShopModelShop $shop = null) {
        if (!$shop || !is_object($shop)){
            $shop = JeproshopContext::getContext()->shop;
        }
        if (!$interval = JeproshopCategoryModelCategory::getInterval($shop->getCategoryId()))
            return false;
        $db = JFactory::getDBO();
        $query = "SELECT n_left, n_right FROM " . $db->quoteName('#__jeproshop_category')  . " WHERE category_id = ".(int)$category_id;
        $db->setQuery($query);
        $row = $db->loadObject();
        return ($row->n_left >= $interval->n_left && $row->n_right <= $interval->n_right);
    }

    /**
     * Return n_left and n_right fields for a given category
     *
     * @since 1.5.0
     * @param int $id
     * @return array
     */
    public static function getInterval($id) {
        $cache_id = 'Category::getInterval_'.(int)$id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT n_left, n_right, depth_level FROM " . $db->quoteName('#__jeproshop_category') . " WHERE category_id = " .(int)$id;
            $db->setQuery($query);
            $result = $db->loadObject();

            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    public function isAssociatedToShop($shop_id = NULL){
        if($shop_id === NULL){
            $shop_id = (int)JeproshopContext::getContext()->shop->shop_id;
        }

        $cache_id = 'jeproshop_shop_model_category_' . (int)$this->category_id . '_' . (int)$this->shop_id;
        if(!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT shop_id FROM " . $db->quoteName('#__jeproshop_category_shop') . " WHERE " . $db->quoteName('category_id') . " = " . (int)$this->category_id;
            $query .= " AND shop_id = " . (int)$shop_id;

            $db->setQuery($query);
            $result = (bool)$db->loadResult();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Return main categories
     *
     * @param integer $lang_id Language ID
     * @param boolean $published return only active categories
     * @param bool $shop_id
     * @return array categories
     */
    public static function getHomeCategories($lang_id, $published = true, $shop_id = false) {
        return self::getChildren(JeproshopSettingModelSetting::getValue('root_category'), $lang_id, $published, $shop_id);
    }

    /**
     * Return the field value for the specified language if the field is multilang, else the field value.
     *
     * @param $field_name
     * @param null $lang_id
     * @return mixed
     * @throws PrestaShopException
     */
    public function getFieldByLang($field_name, $lang_id = null){
        //$definition = ObjectModel::getDefinition($this);
        // Is field in definition
        $field = $this->{$field_name};
        // Is field multilang?
        if (isset($field['lang']) && $field['lang']) {
            if (is_array($this->{$field_name}))
                return $this->{$field_name}[$lang_id ? $lang_id : JeproshopContext::getContext()->language->lang_id];
        }
        return $this->{$field_name};
    }

    /**
     * Return current category childs
     *
     * @param integer $lang_id Language ID
     * @param boolean $published return only active categories
     * @return array Categories
     */
    public function getSubCategories($lang_id, $published = true) {
        $sql_groups_where = '';
        $sql_groups_join = '';
        $db = JFactory::getDBO();
        if (JeproshopGroupModelGroup::isFeaturePublished())
        {
            $sql_groups_join = " LEFT JOIN " . $db->quoteName('#__jeproshop_category_group') . " AS category_group ON (category_group." . $db->quoteName('category_id') . " = category." . $db->quoteName('category_id') . ")";
            $groups = JeproshopController::getCurrentCustomerGroups();
            $sql_groups_where = " AND category_group." . $db->quoteName('group_id') . " " .(count($groups) ? " IN (".implode(',', $groups).")" : " = ".(int)JeproshopGroupModelGroup::getCurrent()->group_id);
        }

        $query = "SELECT category.*, category_lang.lang_id, category_lang.name, category_lang.description, category_lang.link_rewrite, category_lang.meta_title, category_lang.meta_keywords, ";
        $query .= " category_lang.meta_description FROM " . $db->quoteName('#__jeproshop_category') . " AS category " . JeproshopShopModelShop::addSqlAssociation('category') . " LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (category." . $db->quoteName('category_id') . " = category_lang." . $db->quoteName('category_id') ;
        $query .= " AND " . $db->quoteName('lang_id') . " = " . (int)$lang_id . " " . JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang') . ") " . $sql_groups_join;
        $query .= " WHERE " . $db->quoteName('parent_id') . " = " . (int)$this->category_id . ($published ? " AND " . $db->quoteName('published') . " = 1 " : "") . $sql_groups_where;
        $query .= "	GROUP BY category." . $db->quoteName('category_id') . " ORDER BY " . $db->quoteName('depth_level') . " ASC, category_shop." . $db->quoteName('position') . " ASC";
        $db->setQuery($query);
        $result = $db->loadObjectList();

        foreach ($result as &$row) {
            $row->image_id = JeproshopTools::file_exists_cache(COM_JEPROSHOP_CATEGORY_IMAGE_DIRECTORY . $row->category_id.'.jpg') ? (int)$row->category_id : JeproshopLanguageModelLanguage::getIsoById($lang_id).'_default';
            $row->legend = JText::_('COM_JEPROSHOP_NO_PICTURE_LABEL');
        }
        return $result;
    }

    /**
     *
     * @param int $parent_id
     * @param int $lang_id
     * @param bool $published
     * @param bool $shop_id
     * @return array
     */
    public static function getChildren($parent_id, $lang_id, $published = true, $shop_id = false) {
        if (!JeproshopTools::isBool($published))
            die(JError::_());

        $cache_id = 'Category::getChildren_'.(int)$parent_id.'_'.(int)$lang_id.'-'.(bool)$published.'_'.(int)$shop_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT category." . $db->quoteName('category_id') . ", category_lang." . $db->quoteName('name') . ", category_lang." . $db->quoteName('link_rewrite');
            $query .= ", category_shop." . $db->quoteName('shop_id') . " FROM " . $db->quoteName('#__jeproshop_category') . " AS category LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang');
            $query .= " AS category_lang ON (category." . $db->quoteName('category_id') . " = category_lang." . $db->quoteName('category_id') .JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang');
            $query .= ") " . JeproshopShopModelShop::addSqlAssociation('category') . " WHERE " . $db->quoteName('lang_id') . " = ".(int)$lang_id . " AND category." . $db->quoteName('parent_id');
            $query .= " = " .(int)$parent_id . ($published ? " AND " . $db->quoteName('published') . " = 1" : "") . " GROUP BY category." . $db->quoteName('category_id') . " ORDER BY category_shop.";
            $query .= $db->quoteName('position') . " ASC";
            $db->setQuery($query);
            $result = $db->loadObjectList();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }



    /**
     * Check if current category is a child of shop root category
     *
     * @param JeproshopShopModelShop $shop
     * @return bool
     */
    public function inShop(JeproshopShopModelShop $shop = null){
        if (!$shop){
            $shop = JeproshopContext::getContext()->shop;
        }
        if (!$interval = JeproshopCategoryModelCategory::getInterval($shop->getCategoryId())){ return false;  }

        return ($this->n_left >= $interval->n_left && $this->n_right <= $interval->n_right);
    }

    public function isMultiShop(){
        return JeproshopShopModelShop::isTableAssociated('category') || !empty($this->multiLangShop);
    }

    public function getPagination(){
        return $this->pagination;
    }

    /**
     * @see Controller::checkAccess()
     *
     * @return boolean
     */
    public function checkAccess(){
        return true;
    }
}