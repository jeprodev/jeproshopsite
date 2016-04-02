<?php

/**
 * @version         1.0.3
 * @package         components
 * @sub package      com_jeproshop
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

class JeproshopSupplierModelSupplier extends JModelLegacy
{
    static protected $cache_name = array();

    public static function getNameById($supplier_id){
        if (!isset(self::$cache_name[$supplier_id])){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_supplier');
            $query .= " WHERE " . $db->quoteName('supplier_id') . " = " .(int)$supplier_id;

            $db->setQuery($query);
            self::$cache_name[$supplier_id] = $db->loadResult();
        }
        return self::$cache_name[$supplier_id];
    }

    public static function getIdByName($name){
        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('supplier_id') . " FROM " . $db->quoteName('#__jeproshop_supplier');
        $query .= " WHERE " . $db->quoteName('name') . " = " . $db->quote($db->escape($name)) ;

        $db->setQuery($query);
        $result = $db->loadOject();

        if (isset($result->supplier_id)){
            return (int)$result['id_supplier'];
        }
        return false;
    }

    /**
     * Return suppliers
     *
     * @param bool $get_nb_products
     * @param int $lang_id
     * @param bool $published
     * @param bool $p
     * @param bool $n
     * @param bool $all_groups
     * @return array Suppliers
     */
    public static function getSuppliers($get_nb_products = false, $lang_id = 0, $published = true, $p = false, $n = false, $all_groups = false){
        if (!$lang_id){
            $lang_id = JeproshopSettingModelSetting::getValue('default_lang');
        }

        if (!JeproshopGroupModelGroup::isFeatureActive()){
            $all_groups = true;
        }

        $db = JFactory::getDBO();
        $query = "SELECT supplier.*, supplier_lang." . $db->quoteName('description') . " FROM " . $db->quoteName('#__jeproshop_supplier') . " AS supplier LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_supplier_lang') . " AS supplier_lang ON (supplier." . $db->quoteName('supplier_id') . " = supplier_lang.";
        $query .= $db->quoteName('supplier_id') . " AND supplier_lang." . $db->quoteName('supplier_id') . " = " . (int)$lang_id;
        $query .= JeproshopShopModelShop::addSqlAssociation('supplier') . ")" . ($published ? " WHERE supplier." . $db->quoteName('published') . " = 1" : "") . " ORDER BY supplier.";
        $query .= $db->quoteName('name') . " ASC " . ( $n && $p ? "LIMIT " . $n . ", " . ($p - 1) * $n : "");

        $db->setQuery($query);
        $suppliers = $db->loadObjectList();

        if ($suppliers === false){ return false; }

        if ($get_nb_products){
            $sql_groups = '';
            if (!$all_groups){
                $groups = FrontController::getCurrentCustomerGroups();
                $sql_groups = (count($groups) ? 'IN ('.implode(',', $groups).')' : '= 1');
            }

            foreach ($suppliers as $key => $supplier){
                $query = "SELECT DISTINCT(product_supplier." . $db->quoteName('product_id') . ") FROM " . $db->quoteName('#__jeproshop_product_supplier') . " AS product_supplier ";
                $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON (product_supplier." . $db->quoteName('product_id') . " = product.";
                $query .= $db->quoteName('supplier_id') . ") " . JeproshopShopModelShop::addSqlAssociation('product') . " WHERE product_supplier." . $db->quoteName('supplier_id') ;
                $query .= " = " . (int)$supplier->supplier_id . " AND product_supplier.product_attribute_id = 0 " . ($published ? "AND product_supplier." . $db->quoteName('published') . " = 1" : "");
                $query .= " AND product_shop." . $db->quoteName('visibility') . " NOT IN (\"none\") " . ($all_groups ? "" : " AND product_supplier." . $db->quoteName('product_id') . " IN (SELECT category_product." . $db->quoteName('product_id'). " FROM " . $db->quoteName('#__jeproshop_category_group') . " AS category_group LEFT JOIN " . $db->quoteName('#__jeproshop_category_product'). "  AS category_group ON (category_group." . $db->quoteName('category_id') . " = category_product." . $db->quoteName('category_id') . ") WHERE category_group." . $db->quoteName('group_id') . $sql_groups . ")");

                $db->setQuery($query);
                $result = $db->loadObjectList();

                $suppliers[$key]['nb_products'] = count($result);
            }
        }
        $nb_suppliers = count($suppliers);
        $rewrite_settings = (int)  JeproshopSettingModelSetting::getValue('rewrite_settings');
        for ($i = 0; $i < $nb_suppliers; $i++){
            $suppliers[$i]['link_rewrite'] = ($rewrite_settings ? JeproshopTools::link_rewrite($suppliers[$i]['name']) : 0);
        }
        return $suppliers;
    }

}