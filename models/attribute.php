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

class JeproshopAttributeModelAttribute extends JModelLegacy {
    /**
     * Get quantity for a given attribute combination
     * Check if quantity is enough to deserve customer
     *
     * @param integer $product_attribute_id Product attribute combination id
     * @param integer $qty Quantity needed
     * @return boolean Quantity is available or not
     */
    public static function checkAttributeQty($product_attribute_id, $qty, JeproshopShopModelShop $shop = null){
        if (!$shop)
            $shop = JeproshopContext::getContext()->shop;

        $result = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct(null, (int)$product_attribute_id, $shop->shop_id);

        return ($result && $qty <= $result);
    }

    /**
     * Get minimal quantity for product with attributes quantity
     *
     * @acces public static
     * @param integer $id_product_attribute
     * @return mixed Minimal Quantity or false
     */
    public static function getAttributeMinimalQty($product_attribute_id) {
        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('minimal_quantity') . " FROM " . $db->quoteName('#__jeproshop_product_attribute_shop') . " AS product_attribute_shop ";
        $query .= " WHERE " . $db->quoteName('shop_id') . " = " . (int)JeproshopContext::getContext()->shop->shop_id . " AND " . $db->quoteName('product_attribute_id');
        $query .= (int)$product_attribute_id;

        $db->setQuery($query);
        $minimal_quantity = $db->loaResult();

        if ($minimal_quantity > 1)
            return (int)$minimal_quantity;

        return false;
    }
}