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
?>
<!-- quantity discount -->
<div class="page_product_box"  id="jform_quantity_discount">
    <table class="table-striped table-product-discounts">
        <thead>
            <tr>
                <th class="nowrap center" width="15%" ><?php echo JText::_('COM_JEPROSHOP_QUANTITY_LABEL'); ?> </th>
                <th class="nowrap center" width="20%"><?php if($this->display_discount_price){ echo JText::_('COM_JEPROSHOP_PRICE_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_DISCOUNT_LABEL'); } ?></th>
                <th class="nowrap" ><?php echo JText::_('COM_JEPROSHOP_YOU_SAVE_LABEL'); ?> </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($this->quantity_discounts as $quantity_discount){ ?>
            <tr id="jform_quantity_discount_<?php $quantity_discount->product_attribute_id; ?>" class="quantity_discount_<?php echo $quantity_discount->product_attribute_id; ?>" data-discount-type="<?php echo $quantity_discount->reduction_type; ?>" data-discount="<?php echo $quantity_discount->real_value; ?>" data-discount-quantity="<?php echo $quantity_discount->quantity; ?>">
                <td><?php echo $quantity_discount->quantity; ?> </td>
                <td>
                    <?php if($quantity_discount->price >= 0 || $quantity_discount->reduction_type == 'amount'){
                        if($this->display_discount_price){
                            echo JeproshopTools::convertPrice($product_price-(float)$quantity_discount->real_value);
                        }else{
                            echo JeproshopTools::convertPrice((float)$quantity_discount->real_value);
                        }
                    }else{
                        if($this->display_discount_price){
                            echo JeproshopTools::convertPrice($product_price-(float)($product_price*$quantity_discount->reduction));
                        }else{
                            echo $quantity_discount->real_value . '%';
                        }
                    } ?>
                </td>
                <td>
                    <span><?php echo JText::_('COM_JEPROSHOP_UP_TO_LABEL'); ?></span>
                    <?php if($this->quantity_discount->price >= 0 || $this->quantity_discount->reduction_type == 'amount'){
                        $discountPrice = $product_price - (float)$this->quantity_discount->real_value;
                    }else{
                        $discountPrice = $product_price - (float)($product_price* $this->quantity_discount->reduction);
                    }
                    $discountPrice = $discountPrice*$this->quantity_discount->quantity;
                    $qtyProductPrice = $product_price*$this->quantity_discount->quantity;
                    echo JeproshopTools::convertPrice($qtyProductPrice-$discountPrice);  ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>
<!-- quantity discount -->