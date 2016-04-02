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

$document = JFactory::getDocument();
$app = JFactory::getApplication();
$css_dir = JeproshopContext::getContext()->shop->theme_directory;
$css_dir = $css_dir ? $css_dir : 'default';
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/jeproshop.css');
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/order.css');

$path = JText::_('COM_JEPROSHOP_YOUR_SHOPPING_CART_LABEL');
?>
<h1 id="cart_title" class="page-heading"><?php echo JText::_('COM_JEPROSHOP_SHOPPING_CART_SUMMARY_LABEL'); ?>
    <?php if(!isset($this->empty) && !$this->catalog_mode){ ?>
        <span class="heading-counter"><?php echo JText::_('COM_JEPROSHOP_YOUR_SHOPPING_CART_CONTAINS_LABEL') . ' : '; ?>
            <span id="summary_products_quantity">
                <?php echo $this->product_number;
                if($this->product_number == 1){ echo ' ' . JText::_('COM_JEPROSHOP_PRODUCT_LABEL'); }else{ echo ' ' . JText::_('COM_JEPROSHOP_PRODUCTS_LABEL'); } ?>
            </span>
		</span>
    <?php } ?>
</h1>
<?php if(isset($this->context->controller->account_created)){ ?>
    <p class="alert alert-success"><?php echo JText::_('COM_JEPROSHOP_YOU_ACCOUNT_HAS_BEEN_CREATED_MESSAGE'); ?></p>
<?php } $this->current_step = 'summary';
echo include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'order_steps.php');

if(isset($this->empty)){ ?>
    <p class="alert alert-warning"><?php echo JText::_('COM_JEPROSHOP_YOUR_SHOPPING_CART_IS_EMPTY_MESSAGE'); ?>.</p>
<?php } elseif($this->catalog_mode){ ?>
    <p class="alert alert-warning"><?php echo JText::_('COM_JEPROSHOP_THIS_STORE_HAS_NOT_ACCEPTED_YOUR_NEW_ORDER_MASSAGE'); ?>.</p>
<?}else { ?>
    <p style="display:none" id="emptyCartWarning" class="alert alert-warning"><?php echo JText::_('COM_JEPROSHOP_YOUR_SHOPPING_CART_IS_EMPTY_MESSAGE'); ?></p>
    <?php if (isset($this->last_product_added) AND $this->last_product_added) { ?>
        <div class="cart_last_product">
            <div class="cart_last_product_header">
                <div class="left"><?php echo JText::_('COM_JEPROSHOP_LAST_PRODUCT_ADDED_LABEL'); ?></div>
            </div>
            <a class="cart_last_product_img"
               href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=product&product_id=' . $this->last_product_added->product_id); ?>, $lastProductAdded.link_rewrite, $lastProductAdded.category, null, null, $lastProductAdded.id_shop)|escape:'html':'UTF-8'}">
                <img
                    src="{$link->getImageLink($this->last_product_added->link_rewrite, $this->last_product_added->image_id, 'small_default')|escape:'html':'UTF-8'}"
                    alt="<?php echo $this->last_product_added->name; ?>"/>
            </a>

            <div class="cart_last_product_content">
                <p class="product-name">
                    <a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=product&product_id=' . $this->last_product_added->product_id . '&link_rewrite=' . $this->last_product_added->link_rewrite . '&category_id=' . $this->last_product_added->category_id . '&product_attribute_id=' . $this->last_product_added->product_attribute_id); ?>" >
                        <?php echo $this->last_product_added->name; ?>
                    </a>
                </p>
                <?php if (isset($this->last_product_added->attributes) && $this->last_product_added->attributes) { ?>
                    <small>
                        <a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=product&product_id=' . $this->last_product_added->product_id . '&link_rewrite=' . $this->last_product_added->link_rewrite . '&category_id=' . $this->last_product_added->category_id . '&product_attribute_id=' . $this->last_product_added->product_attribute_id); ?>" >
                            <?php echo $this->last_product_added->attributes; ?>
                        </a>
                    </small>
                <?php } ?>
            </div>
        </div>
    <?php }
    $total_discounts_num = ($this->total_discounts != 0) ? 1 : 0;
    $use_show_taxes = ($this->use_taxes && $this->use_taxes) ? 2 : 0;
    $total_wrapping_taxes_num = ($this->total_wrapping != 0) ? 1 : 0; ?>
    {hook h="displayBeforeShoppingCartBlock"}
    <div id="order-detail-content" class="table_block table-responsive">
        <table id="cart_summary" class="table table-bordered <?php if ($this->stock_management) { ?> stock-management-on<?php } else { ?>stock-management-off<?php } ?>">
            <thead>
                <tr>
                    <th class="cart_product first_item"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_LABEL'); ?></th>
                    <th class="cart_description item"><?php echo JText::_('COM_JEPROSHOP_DESCRIPTION_LABEL'); ?></th>
                    <?php if ($this->stock_management) {
                        $col_span_subtotal = 3; ?>
                        <th class="cart_avail item"><?php echo JText::_('COM_JEPROSHOP_AVAILABLE_LABEL') ?></th>
                    <?php } else {
                        $col_span_subtotal = 2;
                    } ?>
                    <th class="cart_unit item"><?php echo JText::_('COM_JEPROSHOP_UNIT_PRICE_LABEL'); ?></th>
                    <th class="cart_quantity item"><?php echo JText::_('COM_JEPROSHOP_QUANTITY_LABEL'); ?></th>
                    <th class="cart_total item"><?php echo JText::_('COM_JEPROSHOP_TOTAL_LABEL'); ?></th>
                    <th class="cart_delete last_item">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
    <?php
    $odd = 0;
    $have_non_virtual_products = false;
    foreach($this->products as $productIndex => $product) {
        if ($product->is_virtual == 0) {
            $have_non_virtual_products = true;
        }
        $productId = $product->product_id;
        $productAttributeId = $product->product_attribute_id;
        $quantityDisplayed = 0;
        $odd = ($odd + 1) % 2;
        $ignoreProductLast = isset($customizedDatas[$productId][$productAttributeId]) || count($this->gift_products);
        /* Display the product line */
        //{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
        /* Then the customized datas ones*/
        if (isset($customizedDatas[$productId][$productAttributeId])) {
            $combinationIndex = 0;
            $countCombination = count($customizedDatas[$productId][$productAttributeId[$product->address_delivery_id]]);

            foreach ($customizedDatas[$productId][$productAttributeId[$product->address_delivery_id]] as $customization_id => $customization) { ?>
                <tr id="<?php echo 'product_' . $product->product_id . '_' . $product->product_attribute_id . '_' . $customization_id . '_' . $product->address_delivery_id; ?>"
                    class="<?php echo 'product_customization_for_' . $product->product_id . '_' . $product->product_attribute_id . '_' . $product->address_delivery_id . ' ' . ($odd ? ' odd' : ' even') . ' customization alternate_item ' . ((($productIndex == (count($this->products) - 1)) && ($combinationIndex ==($countCombination - 1)) && !count($gift_products)) ? ' last_item' : ''); ?>">
                    <td></td>
                    <td colspan="3" >
                        <?php foreach($customization->datas as $type => $custom_data){
                            if($type == $CUSTOMIZE_FILE){ ?>
                                <div class="customizationUploaded">
                                    <ul class="customizationUploaded">
                                        <?php foreach($custom_data as $picture){ ?>
                                            <li><img src="<?php echo $pic_dir . $picture->value . '_small'; ?>" alt="" class="customizationUploaded"/></li>
                                        <?php }?>
                                    </ul>
                                </div>
                            <?php }elseif($type == $CUSTOMIZE_TEXTFIELD){ ?>
                                <ul class="typedText">
                                    <?php foreach($custom_data as $index => $textField){ ?>
                                        <li>
                                            <?php if($textField->name) {
                                                echo $textField->name;
                                            }else{
                                                echo JText::_('COM_JEPROSHOP_TEXT_LABEL') . ' #' . $index+1;
                                            }
                                            echo  ' : ' .  $textField->value; ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            <?php } } ?>
                    </td>
                    <td class="cart_quantity" colspan="2">
                        <?php if(isset($this->cannot_modify) AND $this->cannot_modify == 1){ ?>
                            <span><?php if($quantityDisplayed == 0 AND isset($customizedDatas[$productId][$productAttributeId])){ echo count($customizedDatas[$productId][$productAttributeId]); }else{ echo $product->cart_quantity-$quantityDisplayed; } ?></span>
                        <?php }else{ ?>
                            <input type="hidden" value="<?php echo $customization->quantity; ?>"
                                   name="<?php echo 'quantity_' . $product->product_id . '_' . $product->product_attribute_id . '_' . $customization_id . '_' . $product->address_delivery_id . '_hidden'; ?>"/>
                            <input type="text" value="<?php echo $customization->quantity; ?>"
                                   class="cart_quantity_input form-control grey"
                                   name="<?php echo 'quantity_' . $product->product_id . '_' . $product->product_attribute_id .'_' . $customization_id . '_' . $product->address_delivery_id; ?>"/>

                            <div class="cart_quantity_button clearfix">
                                <?php if($product->minimal_quantity < ($customization->quantity - $quantityDisplayed) OR $product->minimal_quantity <= 1){ ?>
                                    <a
                                        id="<?php echo 'cart_quantity_down_' . $product->product_id . '_' . $product->product_attribute_id . '_' . $customization_id . '_' . $product->address_delivery_id; ?>"
                                        class="cart_quantity_down btn btn-default button-minus"
                                        href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=cart&task=add&product_id=' .$product->product_id . '&product_attribute_id=' . $product->product_attribute_id . '&address_delivery_id=' . $product->address_delivery_id . '&customization_id=' . $customization_id . '&' . JeproshopTools::getCartToken() . '=1', true, 1); ?>"
                                        rel="nofollow"
                                        title="<?php echo JText::_('COM_JEPROSHOP_SUBTRACT_LABEL'); ?>">
                                        <span><i class="icon-minus"></i></span>
                                    </a>
                                <?php }else{ ?>
                                    <a
                                        id="<?php echo 'cart_quantity_down_' . $product->product_id . '_' . $product->product_attribute_id . '_' . $customization_id; ?>"
                                        class="cart_quantity_down btn btn-default button-minus disabled" href="#" title="<?php echo JText::_('COM_JEPROSHOP_SUBTRACT_LABEL'); ?>">
                                        <span><i class="icon-minus"></i></span>
                                    </a>
                                <?php } ?>
                                <a
                                    id="cart_quantity_up_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_{$product.id_address_delivery|intval}"
                                    class="cart_quantity_up btn btn-default button-plus"
                                    href="{$link->getPageLink('cart', true, NULL, "
                                    add=1&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_address_delivery={$product.id_address_delivery}&amp;id_customization={$id_customization}&amp;token={$token_cart}")|escape:'html':'UTF-8'}"
                                    rel="nofollow"
                                    title="<?php echo JText::_('COM_JEPROSHOP_ADD_LABEL'); ?>" >
                                    <span><i class="icon-plus"></i></span>
                                </a>
                            </div>
                        <?php }   ?>
                    </td>
                    <td class="cart_delete">
                        <?php if(isset($cannotModify) AND $cannotModify == 1){}else{ ?>
                            <div>
                                <a id="<?php echo $product->product_id . '_' . $product->product_attribute_id . '_' . $customization_id . '_' . $product->address_delivery_id; ?>"
                                   class="cart_quantity_delete" rel="nofollow" title="<?php echo JText::_('COM_JEPROSHOP_DELETE_LABEL'); ?>"
                                   href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=cart&task=delete&product_id=' . (int)$product->product_id . '&product_attribute_id=' . (int)$product->product_attribute_id . '&customization_id=' . (int)$customization_id . '&address_delvery_id=' . (int)$product->address_delivery_id . '&' . JeproshopTools::getCartToken() . '=1', true, 1); ?>"  >
                                    <i class=" icon-trash"></i>
                                </a>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                $quantityDisplayed = $quantityDisplayed + $customization->quantity;
            }

            /* If it exists also some un-customized products */
            if ($product->quantity - $quantityDisplayed > 0) {
                $this->loadTemplate('product_line');
                //echo JeproshopHelper::shoppingCartProductLine(); include file = "$tpl_dir./shopping-cart-product-line.tpl" productLast = $product@last productFirst = $product@first;
            }
        }
    }
    $last_was_odd = $index%2;
    foreach($this->gift_products as $index => $product){
        $productId = $product->product_id;
        $productAttributeId = $product->product_attribute_id;
        $quantityDisplayed = 0;
        $odd = ($index + $last_was_odd)%2;
        $ignoreProductLast = isset($customizedDatas[$productId][$productAttributeId]);
        $cannotModify = 1;
        /* Display the gift product line */
        $this->loadTemplate('product_line');
        //{include file="$tpl_dir./shopping-cart-product-line.tpl" productLast=$product@last productFirst=$product@first}
    } ?>
            </tbody>
    <?php if(sizeof($this->discounts)){  ?>
        <tbody>
        <?php $nb = count($this->discounts);
        foreach($this->discounts as $ind => $discount){ ?>
            <tr class="cart_discount <?php if($ind == ($nb - 1)){ ?>last_item <?php }elseif($ind == 0){ ?>first_item<?php }else{ ?>item<?php } ?>" id="cart_discount_<?php echo $discount->discount_id; ?>">
                <td class="cart_discount_name" colspan="<?php if($this->stock_management){ ?> 3<?php }else{ ?>2 <?php } ?>"><?php echo $discount->name; ?></td>
                <td class="cart_discount_price">
                    <span class="price-discount"><?php if(!$this->display_price){ echo JeproshopTools::displayPrice($discount->value_real*-1); }else{ echo JeproshopTools::displayPrice($discount->value_tax_exc*-1); } ?></span>
                </td>
                <td class="cart_discount_delete">1</td>
                <td class="cart_discount_price">
                    <span class="price-discount price"><?php if(!$this->display_price){ echo JeproshopTools::displayPrice($discount->value_real*-1); }else{ echo JeproshopTools::displayPrice($discount->value_tax_exc*-1); } ?></span>
                </td>
                <td class="price_discount_del text-center">
                    <?php if(strlen($discount->code)){
                        $orderLinkTarget = 'index.php?option=com_jeproshop&view=order' . (($this->order_process_type == 'page_checkout') ? '&task=opc' : '') . '&operation=delete_discount&discount_id=' . (int)$discount->discount_id; ?>?>
                        <a href="<?php echo JRoute::_($orderLinkTarget, true, 1); ?>" class="price_discount_delete" title="<?php echo JText::_('COM_JEPROSHOP_DELETE_LABEL'); ?>" >
                            <i class="icon-trash"></i>
                        </a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    <?php } ?>
            <tfoot>
            <?php if ($this->use_taxes) {
        if ($this->display_price) { ?>
            <tr class="cart_total_price">
                <td rowspan="<?php echo 3 + $total_discounts_num + $use_show_taxes + $total_wrapping_taxes_num; ?>"
                    colspan="3" id="cart_voucher" class="cart_voucher">
                    <?php if ($this->voucher_allowed) {
                        if (isset($this->errors_discount) && $this->errors_discount) { ?>
                            <ul class="alert alert-danger">
                                <?php foreach ($this->errors_discount as $k => $error) {
                                    echo '<li>' . $error . '</li>';
                                } ?>
                            </ul>
                        <?php }
                        $orderLinkTarget = 'index.php?option=com_jeproshop&view=order' . (($this->order_process_type == 'page_checkout') ? '&task=opc' : ''); ?>
                        <form action="<?php echo JRoute::_($orderLinkTarget, true, 1); ?>" method="post" id="voucher">
                            <fieldset>
                                <h4><?php echo JText::_('COM_JEPROSHOP_VOUCHERS_LABEL'); ?></h4>
                                <input type="text" class="discount_name form-control" id="discount_name"
                                       name="discount_name" value="<?php if (isset($discount_name) && $discount_name) {
                                    echo $discount_name;
                                } ?>"/>
                                <input type="hidden" name="submitDiscount"/>
                                <button type="submit" name="submitAddDiscount"
                                        class="button btn btn-default button-small">
                                    <span><?php echo JText::_('COM_JEPROSHOP_OK_LABEL'); ?></span></button>
                            </fieldset>
                        </form>
                        <?php if ($this->display_vouchers) { ?>
                            <p id="title"
                               class="title-offers"><?php echo JText::_('COM_JEPROSHOP_TAKE_ADVANTAGE_OF_OUR_EXCLUSIVE_OFFERS_MESSAGE') . ' : '; ?></p>

                            <div id="display_cart_vouchers">
                                <?php foreach ($this->display_vouchers as $voucher) {
                                    if ($voucher->code != '') { ?>
                                        <span class="voucher_name"
                                              data-code="<?php echo $voucher->code; ?>"><?php echo $voucher->code; ?></span>
                                        - <?php }
                                    echo $voucher->name; ?><br/>
                                <?php } ?>
                            </div>
                        <?php }
                    } ?>
                </td>
                <td colspan="<?php echo $col_span_subtotal; ?>"
                    class="text-right"><?php echo JText::_('COM_JEPROSHOP_TOTAL_PRODUCTS_LABEL') . ($this->display_tax_label ? ' (' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ') ' : ''); ?></td>
                <td colspan="2" class="price"  id="total_product"><?php echo JeproshopTools::displayPrice($this->total_products); ?></td>
            </tr>
        <?php }else{ ?>
            <tr class="cart_total_price">
                <td rowspan="<?php echo 3 + $total_discounts_num + $use_show_taxes + $total_wrapping_taxes_num; ?>"
                    colspan="2" id="cart_voucher" class="cart_voucher">
                    <?php if ($this->voucher_allowed) {
                        if (isset($this->errors_discount) && $this->errors_discount) { ?>
                            <ul class="alert alert-danger">
                                <?php foreach ($this->errors_discount as $k => $error) {
                                    echo '<li>' . $error . '</li>';
                                } ?>
                            </ul>
                        <?php }
                        $orderLinkTarget = 'index.php?option=com_jeproshop&view=order' . (($this->order_process_type == 'page_checkout') ? '&task=opc' : ''); ?>
                        <form action="<?php echo JRoute::_($orderLinkTarget, true, 1); ?>" method="post" id="voucher">
                            <fieldset>
                                <h4><?php echo JText::_('COM_JEPROSHOP_VOUCHERS_LABEL'); ?></h4>
                                <input type="text" class="discount_name" id="discount_name" name="discount_name"
                                       value="<?php if(isset($discount_name) && $discount_name){ echo $discount_name; } ?>"/>
                                <input type="hidden" name="submitDiscount"/>
                                <button type="submit" name="submitAddDiscount"
                                        class="button btn btn-default button-small">
                                    <span><?php echo JText::_('COM_JEPROSHOP_OK_LABEL'); ?></span></button>
                            </fieldset>
                        </form>
                        <?php if ($this->display_vouchers) { ?>
                            <p id="title"
                               class="title-offers"><?php echo JText::_('COM_JEPROSHOP_TAKE_ADVANTAGE_OF_OUR_EXCLUSIVE_OFFERS_LABEL'); ?></p>

                            <div id="display_cart_vouchers">
                                <?php foreach ($this->display_vouchers as $voucher) {
                                    if ($voucher->code != '') { ?>
                                        <span class="voucher_name" data-code="<?php echo $voucher->code; ?>"><?php echo $voucher->code; ?></span>
                                        - <?php }
                                    echo $voucher->name; ?><br/>
                                <?php } ?>
                            </div>
                        <?php }
                    } ?>
                </td>
                <td colspan="<?php $col_span_subtotal; ?>"
                    class="text-right"><?php echo JText::_('COM_JEPROSHOP_TOTAL_PRODUCTS_LABEL') . ($this->display_tax_label ? ' (' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ') ' : ''); ?></td>
                <td colspan="2" class="price" id="total_product"><?php echo JeproshopTools::displayPrice($total_products_wt); ?></td>
            </tr>
        <?php }
            }else{ ?>
            <tr class="cart_total_price">
                <td rowspan="<?php echo 3+$total_discounts_num+$use_show_taxes+$total_wrapping_taxes_num; ?>" colspan="2"
                    id="cart_voucher" class="cart_voucher">
                    <?php if($this->voucher_allowed){
                        if(isset($this->errors_discount) && $this->errors_discount){ ?>
                            <ul class="alert alert-danger">
                                <?php foreach ($this->errors_discount as $k => $error) {
                                    echo '<li>' . $error . '</li>';
                                } ?>
                            </ul>
                        <?php }
                        $orderLinkTarget = 'index.php?option=com_jeproshop&view=order' . (($this->order_process_type == 'page_checkout') ? '&task=opc' : ''); ?>
                        <form action="<?php echo JRoute::_($orderLinkTarget, true, 1); ?>"  method="post" id="voucher">
                            <fieldset>
                                <h4><?php echo JText::_('COM_JEPROSHOP_VOUCHERS_LABEL'); ?></h4>
                                <input type="text" class="discount_name form-control" id="discount_name" name="discount_name"
                                       value="<?php if(isset($discount_name) && $discount_name){ echo $discount_name; } ?>"/>
                                <input type="hidden" name="submitDiscount"/>
                                <button type="submit" name="submitAddDiscount" class="button btn btn-default button-small">
                                    <span><?php echo JText::_('COM_JEPROSHOP_OK_LABEL'); ?></span>
                                </button>
                            </fieldset>
                        </form>
                        <?php if($this->display_vouchers){ ?>
                            <p id="title" class="title-offers"><?php echo JText::_('COM_JEPROSHOP_TAKE_ADVANTAGE_OF_OUR_EXCLUSIVE_OFFERS_LABEL'); ?></p>

                            <div id="display_cart_vouchers">
                                <?php foreach($this->display_vouchers as $voucher){ ?>
                                    <?php if($voucher->code != ''){ ?><span class="voucher_name"
                                                                            data-code="<?php echo $voucher->code; ?>"><?php echo $voucher->code; ?></span>
                                        - <?php } echo $voucher->name; ?><br/>
                                <?php } ?>
                            </div>
                        <?php } }?>
                </td>
                <td colspan="{$col_span_subtotal}" class="text-right"><?php echo JText::_('COM_JEPROSHOP_TOTAL_PRODUCTS_LABEL'); ?></td>
                <td colspan="2" class="price" id="total_product"><?php echo JeproshopTools::displayPrice($total_products); ?></td>
            </tr>
            <?php }?>
            <tr <?php if($total_wrapping == 0){ ?> style="display: none;"<?php } ?> >
                <td colspan="3" class="text-right">
                    <?php if ($this->use_taxes) {
                        if ($this->display_tax_label) {
                            echo JText::_('COM_JEPROSHOP_TOTAL_GIFT_WRAPPING_LABEL') . ' ' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ' :';
                        }
                    }
                    echo JText::_('COM_JEPROSHOP_TOTAL_GIFT_WRAPPING_COST_LABEL') . ' : '; ?>
                </td>
                <td colspan="2" class="price-discount price" id="total_wrapping">
                    <?php if ($this->use_taxes) {
                        if ($this->display_price) {
                            echo JeproshopTools::displayPrice($total_wrapping_tax_exc);
                        } else {
                            echo JeproshopTools::displayPrice($total_wrapping);
                        }
                    } else {
                        echo JeproshopTools::displayPrice($total_wrapping_tax_exc);
                    } ?>
                </td>
            </tr>
            <?php if($total_shipping_tax_exc <= 0 && !isset($virtual_cart)){ ?>
                <tr class="cart_total_delivery" style="<?php if(!isset($this->carrier->carrier_id) || is_null($this->carrier->carrier_id)){ ?>display:none;<?php } ?>">
                    <td colspan="<?php echo $col_span_subtotal; ?>" class="text-right"><?php echo JText::_('COM_JEPROSHOP_SHIPPING_LABEL'); ?></td>
                    <td colspan="2" class="price" id="total_shipping"><?php echo JText::_('COM_JEPROSHOP_FREE_SHIPPING_LABEL'); ?></td>
                </tr>
            <?php }else{
                if($this->use_taxes && $total_shipping_tax_exc != $total_shipping) {
                    if ($this->display_price) { ?>
                        <tr class="cart_total_delivery" <?php if($total_shipping_tax_exc <= 0){ ?> style="display:none;"<?php } ?> >
                            <td colspan="<?php echo $col_span_subtotal; ?>" class="text-right"><?php  echo JText::_('COM_JEPROSHOP_TOTAL_SHIPPING_LABEL') . ' ' . ($this->display_tax_label ?   ' (' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ')' : '' ); ?></td>
                            <td colspan="2" class="price" id="total_shipping"><?php echo JeproshopTools::displayPrice($total_shipping_tax_exc); ?></td>
                        </tr>
                    <?php } else { ?>
                        <tr class="cart_total_delivery"<?php if($total_shipping <= 0){ ?> style="display:none;"<?php } ?> >
                            <td colspan="<?php echo $col_span_subtotal; ?>" class="text-right"><?php  echo JText::_('COM_JEPROSHOP_TOTAL_SHIPPING_LABEL') . ($this->display_tax_label ?  ' (' . JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL') . ') ' : ''); ?></td>
                            <td colspan="2" class="price" id="total_shipping" ><?php echo JeproshopTools::displayPrice($total_shipping); ?></td>
                        </tr>
                    <?php }
                }else{ ?>}
                    <tr class="cart_total_delivery"<?php if($total_shipping_tax_exc <= 0){ ?> style="display:none;"<?php } ?> >
                        <td colspan="<?php echo $col_span_subtotal; ?>" class="text-right"><?php echo JText::_('COM_JEPROSHOP_TOTAL_SHIPPING_LABEL'); ?></td>
                        <td colspan="2" class="price" id="total_shipping" ><?php echo JeproshopTools::displayPrice($total_shipping_tax_exc); ?></td>
                    </tr>
                <?php }
            } ?>
            <tr class="cart_total_voucher" <?php if($total_discounts == 0){ ?>style="display:none"<?php } ?>>
                <td colspan="<?php echo $col_span_subtotal; ?>" class="text-right">
                    <?php echo JText::_('COM_JEPROSHOP_VOUCHERS_LABEL');
                    if($this->display_tax_label){
                        if($this->use_taxes && $this->display_price == 0){
                            echo ' (' . JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL') . ')';
                        }else{
                            echo ' (' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ')';
                        }
                    } ?>
                </td>
                <td colspan="2" class="price-discount price" id="total_discount">
                    <?php if($this->use_taxes && $this->display_price == 0){
                        $total_discounts_negative = $total_discounts * -1;
                    }else{
                        $total_discounts_negative = $total_discounts_tax_exc * -1;
                    }
                    echo JeproshopTools::displayPrice($total_discounts_negative); ?>
                </td>
            </tr>
            <?php if($this->use_taxes && $this->show_taxes){ ?>
                <tr class="cart_total_price">
                    <td colspan="<?php echo $col_span_subtotal; ?>" class="text-right"><?php echo JText::_('COM_JEPROSHOP_TOTAL_LABEL') . (($this->display_tax_label) ?  ' (' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ')' : '') ?></td>
                    <td colspan="2" class="price" id="total_price_without_tax"><?php echo JeproshopTools::displayPrice($total_price_without_tax); ?></td>
                </tr>
                <tr class="cart_total_tax">
                    <td colspan="<?php echo $col_span_subtotal; ?>" class="text-right"><?php echo JText::_('COM_JEPROSHOP_TAX_LABEL'); ?></td>
                    <td colspan="2" class="price" id="total_tax"><?php echo JeproshopTools::displayPrice($total_tax); ?></td>
                </tr>
            <?php } ?>
            <tr class="cart_total_price">
                <td colspan="<?php echo $col_span_subtotal; ?>" class="total_price_container text-right">
                    <span><?php echo JText::_('COM_JEPROSHOP_TOTAL_LABEL'); ?></span>
                </td>
                <?php if($this->use_taxes){ ?>
                    <td colspan="2" class="price" id="total_price_container">
                        <span id="total_price"><?php echo JeproshopTools::displayPrice($total_price); ?></span>
                    </td>
                <?php }else{ ?>
                    <td colspan="2" class="price" id="total_price_container">
                        <span id="total_price"><?php echo JeproshopTools::displayPrice($total_price_without_tax); ?></span>
                    </td>
                <?php } ?>
            </tr>
            </tfoot>
        </table>
    </div>
    <?php if($this->show_option_allow_separate_package){ ?>
        <p>
            <input type="checkbox" name="allow_separated_package" id="allow_separated_package" <?php if($this->cart->allow_separated_package){ ?>checked="checked"<?php } ?> autocomplete="off"/>
            <label for="allow_separated_package"><?php echo JText::_('COM_JEPROSHOP_SEND_AVAILABLE_PRODUCTS_FIRST_LABEL'); ?></label>
        </p>
    <?php }
    /* Define the style if it doesn't exist in the JeproShop version*/
    /* Will be deleted for 1.5 version and more */
    if(!isset($addresses_style)){
        $addresses_style->company = 'address_company';
        $addresses_style->vat_number = 'address_company';
        $addresses_style->firstname = 'address_name';
        $addresses_style->lastname = 'address_name';
        $addresses_style->address1 = 'address_address1';
        $addresses_style->address2 = 'address_address2';
        $addresses_style->city = 'address_city';
        $addresses_style->country = 'address_country';
        $addresses_style->phone = 'address_phone';
        $addresses_style->phone_mobile = 'address_phone_mobile';
        $addresses_style->alias = 'address_title';
    }

    if(((!empty($delivery_option) AND !isset($virtualCart)) OR $this->delivery->address_id OR $this->invoice->address_id) AND ($this->order_process_type == 'standard')){ ?>
        <div class="order_delivery clearfix row">
        <?php if(!isset($formattedAddresses) || (count($formattedAddresses->invoice) == 0 && count($formattedAddresses->delivery) == 0) || (count($formattedAddresses->invoice->formated) == 0 && count($formattedAddresses->delivery->formated) == 0)) {
            if ($delivery->id) { ?>
                <div
                    class="col-xs-12 col-sm-6" <?php if (!$have_non_virtual_products) { ?> style="display: none;" <?php } ?> >
                    <ul id="delivery_address" class="address item box">
                        <li><h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_DELIVERY_ADDRESS_LABEL'); ?>
                                &nbsp;<span class="address_alias">(<?php echo $delivery->alias; ?>)</span></h3></li>
                        <?php if ($delivery->company) { ?>
                            <li class="address_company"><?php echo $delivery->company; ?></li><?php } ?>
                        <li class="address_name">{$delivery->firstname|escape:'html':'UTF-8'}
                            {$delivery->lastname|escape:'html':'UTF-8'}
                        </li>
                        <li class="address_address1">{$delivery->address1|escape:'html':'UTF-8'}</li>
                        <?php if ($delivery->address2) { ?>
                            <li class="address_address2">{$delivery->address2|escape:'html':'UTF-8'}</li><?php } ?>
                        <li class="address_city">{$delivery->postcode|escape:'html':'UTF-8'}
                            {$delivery->city|escape:'html':'UTF-8'}
                        </li>
                        <li class="address_country">
                            {$delivery->country|escape:'html':'UTF-8'} <?php if ($this->delivery_status) {
                                echo ' (' . $this->delivery_status . ')';
                            } ?></li>
                    </ul>
                </div>
            <?php }
            if ($this->invoice->order_status_id) { ?>
                <div class="col-xs-12 col-sm-6">
                    <ul id="invoice_address" class="address alternate_item box">
                        <li><h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_INVOICE_ADDRESS_LABEL'); ?>
                                &nbsp;<span class="address_alias">(<?php echo $this->invoice->alias; ?>)</span></h3></li>
                        <?php if ($this->invoice->company) { ?>
                            <li class="address_company"><?php echo $this->invoice->company; ?></li> <?php } ?>
                        <li class="address_name"><?php echo $this->invoice->firstname . ' ' . $this->invoice->lastname; ?></li>
                        <li class="address_address1"><?php echo $this->invoice->address1; ?></li>
                        <?php if ($this->invoice->address2){ ?>
                            <li class="address_address2"><?php echo $this->invoice->address2; ?></li><?php } ?>
                        <li class="address_city"><?php echo $this->invoice->postcode . ' ' . $this->invoice->city; ?></li>
                        <li class="address_country">
                            <?php echo $this->invoice->country;  if ($this->invoice_status) {
                                echo ' (' . $this->invoice_status . ')';
                            } ?>
                        </li>
                    </ul>
                </div>
            <?php }
        }else {
            $nbFormattedAddress = count($formattedAddresses);
            foreach ($formattedAddresses as $key => $address) { ?>
                <div
                    class="col-xs-12 col-sm-6"<?php if ($key == 'delivery' && !$have_non_virtual_products) { ?> style="display: none;" <?php } ?>>
                    <ul class="address <?php if ($key == ($nbFormattedAddress - 1)) { ?> last_item <?php } elseif ($key == 0) { ?>first_item<?php }
                    if ($key % 2) { ?>alternate_item<?php } else { ?>item <?php }?> box">
                        <li>
                            <h3 class="page-subheading">
                                <?php if ($key == 'invoice') {
                                    echo JText::_('COM_JEPROSHOP_INVOICE_ADDRESS_LABEL');
                                } elseif ($key == 'delivery' && $delivery->id) {
                                    echo JText::_('COM_JEPROSHOP_DELIVERY_ADDRESS_LABEL');
                                }
                                if (isset($address->object->alias)) { ?>
                                    <span class="address_alias">(<?php echo $address->object->alias; ?>)</span>
                                <?php } ?>
                            </h3>
                        </li>
                        <?php foreach ($address->ordered as $pattern) {
                            $addressKey = explode(" ", $pattern);
                            $addedLi = false;
                            $nbAddressKey = count($addressKey);
                            foreach ($addressKey as $item_key => $foo) {
                                $key_str = $key; //todo|regex_replace:AddressFormat::_CLEANING_REGEX_:"";
                                if (isset($address->formated[$key_str]) && !empty($address->formated[$key_str])) {
                                    if (!$addedLi) {
                                        $addedLi = true; ?>
                                        <li>
                                        <span class="<?php if (isset($addresses_style[$key_str])) {
                                            echo $addresses_style[$key_str];
                                        } ?>" >
                                    <?php }
                                    echo $address->formated[$key_str];
                                }
                                if (($item_key == ($nbAddressKey - 1)) && $addedLi) { ?>
                                    </span>
                                    </li>
                                <?php }
                            }
                        } ?>
                    </ul>
                </div>
            <?php }
        } ?>
        </div>
    <?php }  ?>
    <div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>
    <p class="cart_navigation clearfix">
        <?php if($this->order_process_type =="standard"){ ?>
            <a href="{if($back}{$link->getPageLink('order', true, NULL, 'step=1&amp;back={$back}')|escape:'html':'UTF-8'}{else}{$link->getPageLink('order', true, NULL, 'step=1')|escape:'html':'UTF-8'}{/if}"
               class="button btn btn-default standard-checkout button-medium"
               title=" <?php echo JText::_('COM_JEPROSHOP_PROCEED_TO_CHECKOUT_TITLE_DESC'); ?>" >
                <span> <?php echo JText::_('COM_JEPROSHOP_PROCEED_TO_CHECKOUT_LABEL'); ?><i class="icon-chevron-right right"></i></span>
            </a>
        <?php } ?>
        <a href="{if((isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, 'order.php')) || isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, 'order-opc') || !isset($smarty.server.HTTP_REFERER)}{$link->getPageLink('index')}{else}{$smarty.server.HTTP_REFERER|escape:'html':'UTF-8'|secureReferrer}{/if}"
           class="button-exclusive btn btn-default"
           title=" <?php echo JText::_('COM_JEPROSHOP_CONTINUE_SHOPPING_TITLE_DESC'); ?>">
            <i class="icon-chevron-left"></i> <?php echo JText::_('COM_JEPROSHOP_CONTINUE_SHOPPING_LABEL'); ?>
        </a>
    </p>
    <?php if(!empty($HOOK_SHOPPING_CART_EXTRA)){ ?>
        <div class="clear"></div>
        <div class="cart_navigation_extra">
            <div id="HOOK_SHOPPING_CART_EXTRA">{$HOOK_SHOPPING_CART_EXTRA}</div>
        </div>
    <?php } ?>
    {strip}
    {addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
    {addJsDef currencyRate=$currencyRate|floatval}
    {addJsDef currencyFormat=$currencyFormat|intval}
    {addJsDef currencyBlank=$currencyBlank|intval}
    {addJsDef deliveryAddress=$cart->id_address_delivery|intval}
    {addJsDefL name=txtProduct}{l s='product' js=1}{/addJsDefL}
    {addJsDefL name=txtProducts}{l s='products' js=1}{/addJsDefL}
    {/strip}
<?php }