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
<!--tr id="product_<?php echo $product->product_id .'_' . $product->product_attribute_id . '_0_' . $product->address_delivery_id; ?>" class="<?php if($productLast){ ?>last_item <?php }elseif($productFirst){ ?>first_item  <?php } if(isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0){ ?>alternate_item<?php } ?> cart_item <?php if($odd){ ?>odd<?php }else{ ?>even<?php } ?>">
    <td class="cart_product">
        <a href="<?php echo $this->context->controller->getProductLink($product->product_id, $product->link_rewrite, $product->category); ?>" ><img src="<?php echo $this->context->controller->getImageLink($product->link_rewrite, $product->image_id, 'small_default'); ?>" alt="{$product.name|escape:'html':'UTF-8'}" <?php if(isset($smallSize)){ ?>width="<?php echo $smallSize->width; ?>" height="<?php echo $smallSize->height; ?>" <?php } ?> /></a>
    </td>
    <td class="cart_description">
        <p class="product_name"><a href="<?php echo $this->context->controller->getProductLink($product->product_id, $product->link_rewrite, $product->category); ?>" >{$product.name|escape:'html':'UTF-8'}</a></p>
        <?php if(isset($product->attributes) && $product->attributes){ ?><small><a href="<?php echo $this->context->controller->getProductLink($product->product_id, $product->link_rewrite, $product->category); ?>">{$product.attributes|escape:'html':'UTF-8'}</a></small>{/if}
    </td>
    <td class="cart_reference"><?php if($product->reference){ echo $product->reference; }else{ echo '--'; } ?></td>
    <td class="cart_available"><?php if($product->stock_quantity > 0){ ?><span class="label label-success"><?php echo JText::_('COM_JEPROSHOP_IN_STOCK_LABEL'); ?></span><?php }else{ ?><span class="label label-warning"><?php echo JText::_('COM_JEPROSHOP_OUT_OF_STOCK_LABEL'); ?></span>{/if}</td>
    <td class="cart_quantity <?php if(isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0){ ?> text-center <?php } ?>">
        <?php if(isset($cannotModify) AND $cannotModify == 1){ ?>
        <span><?php if($quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId){ echo count($customizedDatas.$productId.$productAttributeId); }else{ echo ($product->cart_quantity - $quantityDisplayed); } ?></span>
        <?php }else{
            if(!isset($customizedDatas.$productId.$productAttributeId) OR $quantityDisplayed > 0){ ?>
        <input type="hidden" value="<?php if($quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)){ echo count($customizedDatas.$productId.$productAttributeId); }else{ echo ($product->cart_quantity-$quantityDisplayed); } ?>" name="quantity_<?php echo $product->product_id .'_' . $product->product_attribute_id . '_0_' . $product->address_delivery_id; ?>_hidden" />
        <input size="2" type="text" class="cart_quantity_input form-control grey" value="<?php if($quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)){ echo count($customizedDatas.$productId.$productAttributeId); }else{ echo ($product->cart_quantity-$quantityDisplayed); } ?>"  name="quantity_<?php echo $product->product_id . '_' . $product->product_attribute_id . '_0_' . $product->address_delivery_id; ?>" />
        <div class="cart_quantity_button">
            <?php if($product->minimal_quantity < ($product->cart_quantity-$quantityDisplayed) OR $product->minimal_quantity <= 1){ ?>
            <a rel="nofollow" class="cart_quantity_down btn btn-default button-minus" id="cart_quantity_down_<?php echo $product->product_id . '_' . $product->product_attribute_id . '_0_' . $product->address_delivery_id; ?>" href="<?php echo $this->context->controller->getPageLink('cart', true, NULL, 'task=add&product_id=' . (int)$product->product_id . '&product_attribute_id=' . (int)$product->product_attribute_id . '&address_delivery_id=' . (int)$product->address_delivery_id . '&op=down&' . $token_cart . '=1'); ?>" title="<?php echo JText::_('COM_JEPROSHOP_SUBTRACT_LABEL') ?>" ><span><i class="icon-minus"></i></span></a>
            <?php } else{ ?>
            <a class="cart_quantity_down btn btn-default button-minus disabled" href="#" id="cart_quantity_down_<?php echo $product->product_id . '_' . $product->product_attribute_id . '_0_' . $product->address_delivery_id; ?>" title="<?php echo JText::_('COM_JEPROSHOP_YOU_MUST_PURCHASE') . $product->minimal_quantity . JText::_('COM_JEPROSHOP_OF_THIS_PRODUCT_LABEL'); ?>"><span><i class="icon-minus"></i></span></a>
            <?php } ?>
            <a rel="nofollow" class="cart_quantity_up btn btn-default button-plus" id="cart_quantity_up_<?php echo $product->product_id . '_' . $product->product_attribute_id . '_0_' . (int)$product->address_delivery_id ?>" href="<?php echo $this->context->controller->getPageLink('cart', true, NULL, 'task=add&product_id=' . (int)$product->product_id . '&product_attribute_id=' . (int)$product->product_attribute_id . '&address_delivery_id=' . (int)$product->address_delivery_id . '&' . $token_cart . '=1'); ?>" title="<?php echo JText::_('COM_JEPROSHOP_ADD_LABEL'); ?>" ><span><i class="icon-plus"></i></span></a>
        </div>
        <?php }
        } ?>
    </td>
    <td>
        <form method="post" action="<?php echo $this->context->controller->getPageLink('cart', true, NULL, "token={$token_cart}"); ?>">
            <div class="selector2">
                <input type="hidden" name="product_id" value="<?php echo $product->product_id; ?>" />
                <input type="hidden" name="product_attribute_id" value="<?php echo $product->product_attribute_id; ?>" />
                <select name="address_delivery" id="select_address_delivery_<?php echo $product->product_id . '_' . $product->product_attribute_id . '_' . $product->address_delivery; ?>" class="cart_address_delivery form-control">
                    <?php if($product->address_delivery_id == 0 && $delivery->address_id == 0){ ?>
                    <option></option>
                    <?php } ?>
                    <option value="-1"><?php echo JText::_('COM_JEPROSHOP_CREATE_A_NEW_ADDRESS_LABEL'); ?></option>
                    <?php foreach($address_list as $address){ ?>
                    <option value="<?php echo $address->address_id; ?>"
                    <?php if(($product.id_address_delivery > 0 && $product->address_delivery_id == $address->address_id) || ($product->address_delivery_id == 0  && $address->address_id == $delivery->address_id)){ ?>
                    selected="selected" <?php } ?> ><?php echo $address->alias; ?>  </option>
                    <?php } ?>
                    <option value="-2"><?php echo JText::_('COM_JEPROSHOP_SHIP_TO_MULTIPLE_ADDRESSES_LABEL'); ?></option>
                </select>
            </div>
        </form>
    </td>
</tr -->