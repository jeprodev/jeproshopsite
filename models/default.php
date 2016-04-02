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

class JeproshopDefaultModelDefault extends JModelLegacy
{
    public static $_pagination;
    public static function getProducts($lang_id, $limit_start, $limit, $order_by = null, $order_way = null, $get_total = false, $published = true, $random = false, $random_number_products = 1, $check_access = true, JeproshopContext $context = null){
        if (!$context){ $context = JeproshopContext::getContext(); }
        $db = JFactory::getDBO();
        $app = JFactory::getApplication();
        jimport('joomla.html.pagination');
        //if ($check_access && !$context->controller->checkAccess($context->customer->customer_id, $category_id)){ return false; }

        if ($limit_start < 1){ $limit_start = 0; }

        if (empty($order_by)){
            $order_by = 'position';
        }else{
            /* Fix for all modules which are now using lowercase values for 'orderBy' parameter */
            $order_by = strtolower($order_by);
        }
        if (empty($order_way))
            $order_way = "DESC";

        $order_by_prefix = false;
        if ($order_by == 'product_id' || $order_by == 'date_add' || $order_by == 'date_upd'){
            $order_by_prefix = "product";
        }elseif ($order_by == 'name'){
            $order_by_prefix = "product_lang";
        }elseif ($order_by == 'manufacturer'){
            $order_by_prefix = "manufacturer";
            $order_by = "name";
        }elseif ($order_by == 'position'){
            $order_by_prefix = "product";
        }

        if ($order_by == 'price')
            $order_by = 'order_price';

        if (!JeproshopTools::isOrderBy($order_by) || !JeproshopTools::isOrderWay($order_way))
            die (JError::raiseError());

        $supplier_id = (int)$app->input->get('supplier_id');

        /* Return only the number of products */

        $query = "SELECT COUNT(product." . $db->quoteName('product_id') . ") AS total FROM " . $db->quoteName('#__jeproshop_product') . " AS product ";
        $query .= JeproshopShopModelShop::addSqlAssociation('product') . " WHERE product_shop." . $db->quoteName('visibility') . " IN ('both', 'catalog')";;
        $query .=  " AND product_shop."  . $db->quoteName('published') . " = 1 " . ($supplier_id ? " AND product.supplier_id = ".(int)$supplier_id : "");

        $db->setQuery($query);
        $total =  (int)$db->loadResult();


        $number_days_new_product = JeproshopSettingModelSetting::getValue('number_days_new_product');
        $query = "SELECT product.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, MAX(product_attribute_shop.product_attribute_id) AS  ";
        $query .= "product_attribute_id, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity,  product_lang." . $db->quoteName('description');
        $query .= ", product_lang." . $db->quoteName('short_description') . ", product_lang." . $db->quoteName('available_now') . ", product_lang." . $db->quoteName('available_later');
        $query .= ", product_lang." . $db->quoteName('link_rewrite') . ", product_lang." . $db->quoteName('meta_description')  . ", product_lang." . $db->quoteName('meta_keywords') ;
        $query .= ", product_lang." . $db->quoteName('meta_title') . ", product_lang." . $db->quoteName('name') . ", MAX(image_shop." . $db->quoteName('image_id') . " ) AS ";
        $query .= "image_id, image_lang." . $db->quoteName('legend') . ", manufacturer." . $db->quoteName('name') . " AS manufacturer_name, DATEDIFF(product_shop." . $db->quoteName('date_add');
        $query .= ", DATE_SUB(NOW(), INTERVAL " . (JeproshopTools::isUnsignedInt($number_days_new_product) ? $number_days_new_product : 20) . "	DAY)) > 0 AS new, product_shop.price AS";
        $query .= " order_price FROM " . $db->quoteName('#__jeproshop_product') . " AS product ". JeproshopShopModelShop::addSqlAssociation('product') . " LEFT JOIN ";
        $query .=  $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ON (product." . $db->quoteName('product_id') . " = product_attribute." . $db->quoteName('product_id');
        $query .= ") " . JeproshopShopModelShop::addSqlAssociation('product_attribute', false, 'product_attribute_shop.`default_on` = 1');
        $query .= JeproshopProductModelProduct::sqlStock('product', 'product_attribute_shop', false, $context->shop) . " LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') ;

        $query .= " AS product_lang ON (product." . $db->quoteName('product_id') . " = product_lang." . $db->quoteName('product_id') . " AND product_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang');
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_image') . " AS image ON (image." . $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . ") " . JeproshopShopModelShop::addSqlAssociation('image', false, 'image_shop.cover=1') . " LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image_shop." . $db->quoteName('image_id') . " = image_lang." . $db->quoteName('image_id') . " AND image_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_manufacturer') . " AS manufacturer ON manufacturer." . $db->quoteName('manufacturer_id') . " = product." . $db->quoteName('manufacturer_id')  . "	WHERE product_shop." . $db->quoteName('shop_id') . " = " .(int)$context->shop->shop_id;
        $query .= " AND product_shop." . $db->quoteName('published') . " = 1 AND product_shop." . $db->quoteName('visibility') . " IN ('both', 'catalog') " ;
        $query .= ($supplier_id ? " AND product.supplier_id = " . (int)$supplier_id : "") . " GROUP BY product_shop.product_id";

        if ($random === true){
            $query .= " ORDER BY RAND() LIMIT " . (int)$random_number_products;
        }else{
            //$query .= " ORDER BY " . (!empty($order_by_prefix) ? $order_by_prefix. "." : "") . $db->quoteName($order_by);
            $query .= " " . $order_way . " LIMIT ".((int)$limit_start) . ", " . (int)$limit;
        }

        $db->setQuery($query);
        $result = $db->loadObjectList();
        if ($order_by == 'order_price'){
            JeproshopTools::orderbyPrice($result, $order_way);
        }
        if (!$result){ return array(); }
        self::$_pagination = new JPagination($total, $limit_start, $limit);
        /* Modify SQL result */
        return JeproshopProductModelProduct::getProductsProperties($lang_id, $result);
    }
}