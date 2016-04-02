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

class JeproshopStockAvailableModelStockAvailable extends JModelLegacy
{
    public $product_id;
    public $product_attribute_id;
    public $shop_id;
    public $shop_group_id;
    public $quantity = 0;
    public $depends_on_stock = false;
    public $out_of_stock = false;

    public static function getQuantityAvailableByProduct($product_id = null, $product_attribute_id = null, $shop_id = null){
        // if null, it's a product without attributes
        if ($product_attribute_id === null){ $product_attribute_id = 0; }

        $db = JFactory::getDBO();
        $query = "SELECT SUM(quantity) FROM " . $db->quoteName('#__jeproshop_stock_available');
        $query .= " WHERE product_attribute_id = " . (int)$product_attribute_id;
        if($product_id !== null){
            $query .= " AND product_id = " . (int)$product_id;
        }
        $query .= JeproshopStockAvailableModelStockAvailable::addShopRestriction($shop_id);

        $db->setQuery($query);
        return$db->loadResult();
    }

    public static function addShopRestriction($shop = NULL, $alias = NULL){
        $context = JeproshopContext::getContext();

        if(!empty($alias)) { $alias .= '.'; }

        /** If there is no shop id, get the context one **/
        if ($shop === null){
            if (JeproshopShopModelShop::getShopContext() == JeproshopShopModelShop::CONTEXT_GROUP){
                $shop_group = JeproshopShopModelShop::getContextShopGroup();
            }else{
                $shop_group = $context->shop->getShopGroup();
            }

            $shop = $context->shop;
        }elseif (is_object($shop)){
            $shop_group = $shop->getShopGroup();
        }else{
            $shop = new JeproshopShopModelShop($shop);
            $shop_group = $shop->getShopGroup();
        }

        /* if quantities are shared between shops of the group */
        $db = JFactory::getDBO();
        if ($shop_group->share_stock){
            $query = " AND " . $db->escape($alias). "shop_group_id = " .(int)$shop_group->shop_group_id . " AND " . $db->escape($alias) . "shop_id = 0 ";

        }else{
            $query = " AND " . $db->escape($alias) . "shop_id = " .(int)$shop->shop_id.' ';
        }
        return $query;
    }


    public static function outOfStock($product_id, $shop_id = null){
        if (!JeproshopTools::isUnsignedInt($product_id)){ return false; }

        $db = JFactory::getDBO();
        $query = "SELECT out_of_stock FROM " . $db->quoteName('#__jeproshop_stock_available') . " WHERE product_id = ";
        $query .= (int)$product_id . " AND product_attribute_id = 0 " . JeproshopStockAvailableModelStockAvailable::addShopRestriction($shop_id);

        $db->setQuery($query);

        return (int)$db->loadResult();
    }

    /**
     * For a given product, tells if it depends on the physical (usable) stock
     *
     * @param int $product_id
     * @param int $shop_id Optional : gets context if null @see Context::getContext()
     * @return bool : depends on stock @see $depends_on_stock
     */
    public static function dependsOnStock($product_id, $shop_id = null){
        if(!JeproshopTools::isUnsignedInt($product_id)){ return false; }
        $db = JFactory::getDBO();

        $query = "SELECT depends_on_stock FROM " . $db->quoteName('#__jeproshop_stock_available') . " WHERE product_id = " . (int)$product_id;
        $query .= " AND product_attribute_id = 0 " . JeproshopStockAvailableModelStockAvailable::addShopRestriction($shop_id);

        $db->setQuery($query);

        return $db->loadResult();
    }

}