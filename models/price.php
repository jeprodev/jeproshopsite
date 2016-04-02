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

class JeproshopPriceModelPrice extends JModelLegacy
{

}


class JeproshopSpecificPriceModelSpecificPrice extends JModelLegacy
{
    public $product_id;

    public $specific_price_rule_id = 0;

    public $cart_id = 0;

    public $product_attribute_id;

    public $specific_price_id;

    public $shop_id;

    public $shop_group_id;

    public $currency_id;

    public $country_id;

    public $group_id;

    public $customer_id;

    public $price;

    public $from_quantity;

    public $reduction;

    public $reduction_type;

    public $from;

    public $to;

    protected static $_specific_price_cache = array();
    protected static $_cache_priorities = array();


    public function __construct() {
        ;

    }

    public static function getSpecificPrice($product_id, $shop_id, $currency_id, $country_id, $group_id, $quantity, $product_attribute_id = null, $customer_id = 0, $cart_id = 0, $real_quantity = 0){
        if (!JeproshopSpecificPriceModelSpecificPrice::isFeaturePublished()){
            return array();
        }

        /*
         ** The date is not taken into account for the cache, but this is for the better because it keeps the consistency for the whole script.
	     ** The price must not change between the top and the bottom of the page
	     ****/
        $db = JFactory::getDBO();
        $key = ((int)$product_id .'_' .(int)$shop_id .'_'.(int)$currency_id.'_'.(int)$country_id.'-'.(int)$group_id.'-'.(int)$quantity.'-'.(int)$product_attribute_id.'_'.(int)$cart_id.'_'.(int)$customer_id.'_'.(int)$real_quantity);
        if (!array_key_exists($key, JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache)){
            $now = date('Y-m-d H:i:s');
            $query = "SELECT *, " . JeproshopSpecificPriceModelSpecificPrice::getScoreQuery($product_id, $shop_id, $currency_id, $country_id, $group_id, $customer_id) . " FROM " ;
            $query .= $db->quoteName('#__jeproshop_specific_price') . " WHERE " . $db->quoteName('product_id') . " IN (0, " . (int)$product_id . ") AND " . $db->quoteName('product_attribute_id');
            $query .= " IN (0, " .(int)$product_attribute_id . ") AND " . $db->quoteName('shop_id') . " IN (0, " . (int)$shop_id. ") AND " . $db->quoteName('currency_id') . " IN (0, ".(int)$currency_id;
            $query .= ") AND " . $db->quoteName('country_id') . " IN (0, " .(int)$country_id . ") AND " . $db->quoteName('group_id') . " IN (0, " .(int)$group_id . ") 	AND " . $db->quoteName('customer_id');
            $query .= " IN (0, " .(int)$customer_id. ") AND ((" . $db->quoteName('from') . " = '0000-00-00 00:00:00' OR '" . $now ."' >= " . $db->quoteName('from') . ") AND (" . $db->quoteName('to') ;
            $query .= " = '0000-00-00 00:00:00' OR '".$now. "' <= " . $db->quoteName('to') . ")) AND cart_id IN (0, " .(int)$cart_id . ") AND IF(" . $db->quoteName('from_quantity') . " > 1, ";
            $query .= $db->quoteName('from_quantity') . ", 0) <= ";
            $query .= (JeproshopSettingModelSetting::getValue('qty_discount_on_combination') || !$cart_id || !$real_quantity) ? (int)$quantity : max(1, (int)$real_quantity);
            $query .= " ORDER BY " . $db->quoteName('product_attribute_id') . " DESC, " . $db->quoteName('from_quantity') . " DESC, " . $db->quoteName('specific_price_rule_id') . " ASC, " . $db->quoteName('score') . " DESC";

            $db->setQuery($query);
            JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache[$key] = $db->loadObject();
        }
        return JeproshopSpecificPriceModelSpecificPrice::$_specific_price_cache[$key];
    }

    /**
     * score generation for quantity discount
     */
    protected static function getScoreQuery($product_id, $shop_id, $currency_id, $country_id, $group_id, $customer_id){
        $db = JFactory::getDBO();
        $now = date('Y-m-d H:i:s');

        $select = "( IF ('" . $now."' >= " . $db->quoteName('from') . " AND '" . $now."' <= " . $db->quoteName('to') . ", " . pow(2, 0). ", 0) + ";

        $priority = JeproshopSpecificPriceModelSpecificPrice::getPriority($product_id);
        foreach (array_reverse($priority) as $k => $field){
            if (!empty($field)){
                $select .= " IF (". $db->quoteName($db->escape($field)). " = ".(int)$field.", " .pow(2, $k + 1).", 0) + ";
            }
        }
        return rtrim($select, " +"). ") AS " . $db->quoteName('score');
    }

    public static function getQuantityDiscounts($product_id, $shop_id, $currency_id, $country_id, $group_id, $product_attribute_id = null, $all_combinations = false, $customer_id = 0){
        if (!JeproshopSpecificPriceModelSpecificPrice::isFeaturePublished()){
            return array();
        }

        $now = date('Y-m-d H:i:s');
        $db = JFactory::getDBO();

        $query = "SELECT *, " . JeproshopSpecificPriceModelSpecificPrice::getScoreQuery($product_id, $shop_id, $currency_id, $country_id, $group_id, $customer_id);
        $query .= " FROM " . $db->quoteName('#__jeproshop_specific_price') . " WHERE " . $db->quoteName('product_id'). " IN(0, " . (int)$product_id . ") AND ";
        $query .= (!$all_combinations ? $db->quoteName('product_attribute_id') . " IN(0, " . (int)$product_attribute_id . ") AND " : ""). $db->quoteName('shop_id');
        $query .= " IN(0, " . (int)$shop_id . ") AND " . $db->quoteName('currency_id') . " IN(0, " . (int)$currency_id . ") AND " . $db->quoteName('country_id');
        $query .= " IN(0, " . (int)$country_id . ") AND " . $db->quoteName('group_id') . " IN(0, " . (int)$group_id . ") AND " . $db->quoteName('customer_id') . " IN(0, ";
        $query .= (int)$customer_id . " ) AND( (" . $db->quoteName('from') . " = '0000-00-00 00:00:00' OR '" . $now . "' >= " . $db->quoteName('from') . ") AND (";
        $query .= $db->quoteName('to') . " = '0000-00-00 00:00:00' OR '" . $now . "' <= " . $db->quoteName('to') . ")) ORDER BY " . $db->quoteName('product_attribute_id');
        $query .= " DESC, " . $db->quoteName('from_quantity') . " DESC, " . $db->quoteName('specific_price_rule_id') ." ASC, ". $db->quoteName('score') . " DESC ";

        $db->setQuery($query);

        $res = $db->loadObjectList();

        $targeted_prices = array();
        $last_quantity = array();

        foreach ($res as $specific_price){
            if (!isset($last_quantity[(int)$specific_price->product_attribute_id])){
                $last_quantity[(int)$specific_price->product_attribute_id] = $specific_price->from_quantity;
            }elseif ($last_quantity[(int)$specific_price->product_attribute_id] == $specific_price->from_quantity){
                continue;
            }

            $last_quantity[(int)$specific_price->product_attribute_id] = $specific_price->from_quantity;
            if ($specific_price->from_quantity > 1){
                $targeted_prices[] = $specific_price;
            }
        }
        return $targeted_prices;
    }

    public static function getPriority($product_id){
        if(!JeproshopSpecificPriceModelSpecificPrice::isFeaturePublished()){
            return explode(';', JeproshopSettingModelSetting::getValue('specific_price_priorities'));
        }

        if(!isset(JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$product_id])){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('priority') . ", " . $db->quoteName('specific_price_priority_id') . " FROM ";
            $query .= $db->quoteName('#__jeproshop_specific_price_priority') ." WHERE " . $db->quoteName('product_id') . " = ";
            $query .= (int)$product_id . " ORDER BY " . $db->quoteName('specific_price_priority_id') . " DESC ";

            $db->setQuery($query);
            JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$product_id] = $db->loadObject();
        }
        $priorities = JeproshopSpecificPriceModelSpecificPrice::$_cache_priorities[(int)$product_id];
        if(!$priorities){
            $priority = JeproshopSettingModelSetting::getValue('specific_price_priorities');
            $priorities = 'customer_id;' . $priority;
        }else{
            $priorities = $priorities->priority;
        }
        return preg_split('/;/', $priorities);
    }

    public static function getByProductId($product_id, $product_attribute_id = false, $cart_id = FALSE){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_specific_price') . " WHERE " . $db->quoteName('product_id');
        $query .= " = " . (int)$product_id . ($product_attribute_id ? " AND " . $db->quoteName('product_attribute_id') . " = " . (int)$product_attribute_id : " ");
        $query .= " AND cart_id = " . (int)$cart_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function isFeaturePublished(){
        static $feature_active = NULL;
        if($feature_active === NULL){
            $feature_active = JeproshopSettingModelSetting::getValue('specific_price_feature_active');
        }
        return $feature_active;
    }
}