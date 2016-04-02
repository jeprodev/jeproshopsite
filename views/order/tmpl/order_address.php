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

if($this->order_process_type != 'standard'){
    $current_step = 'address';
    //{capture name=path}<?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); {/capture}
    $back_order_page  = JRoute::_('index.php?option=com_jeproshop&view=order', true, 1);  ?>
<h1 class="page-heading"><?php echo JText::_('COM_JEPROSHOP_ADDRESSES_LABEL'); ?></h1>
{include file="$tpl_dir./order-steps.tpl"}
{include file="$tpl_dir./errors.tpl"}
<form action="<?php echo $back_order_page; ?>" method="post">
    <?php } else{
    $back_order_page = JRoute::_('index.php?option=com_jeproshop&view=order&task=opc'); ?>
    <h1 class="page-heading step-num"><span>1</span> <?php echo JText::_('COM_JEPROSHOP_ADDRESSES_LABEL'); ?> </h1>
    <div id="opc_account" class="opc-main-block">
        <div id="opc_account-overlay" class="opc-overlay" style="display: none;"></div>
        <?php } ?>
        <div class="addresses clearfix">
            <div class="row">
                <div class="col-xs-12 col-sm-6">
                    <div class="address_delivery select form-group selector1">
                        <label for="id_address_delivery"><?php if($this->context->cart->isVirtualCart()){ echo JText::_('COM_JEPROSHOP_CHOOSE_A_BILLING_ADDRESS_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_CHOOSE_A_DELIVERY_ADDRESS_LABEL'); } ?> </label>
                        <select name="address_delivery_id" id="jform_address_delivery_id" class="address_select form-control">
                            <?php foreach($this->addresses as $address){ ?>
                            <option value="<?php echo $address->address_id; ?>" <?php if($address->address_id == $this->context->cart->address_delivery_id){ ?> selected="selected"<?php } ?> ><?php echo $address->alias; ?></option>
                            <?php } ?>
                        </select><span class="wait_image"></span>
                    </div>
                    <p class="checkbox addressesAreEquals" <?php if($this->context->cart->isVirtualCart()){ ?> style="display:none;" <?php } ?> >
                        <input type="checkbox" name="same" id="addressesAreEquals" value="1" <?php if($this->context->cart->address_invoice_id == $this->context->cart->address_delivery_id || count($this->addresses) == 1){ ?> checked="checked"<?php } ?> />
                        <label for="addressesAreEquals"><?php echo JText::_('COM_JEPROSHOP_USE_DELIVERY_ADDRESS_AS_BILLING_ADDRESS_LABEL'); ?></label>
                    </p>
                </div>
                <div class="col-xs-12 col-sm-6">
                    <div id="address_invoice_form" class="select form-group selector1"<?php if($this->context->cart->address_invoice_id == $this->context->cart->address_delivery_id){ ?> style="display: none;"<?php } ?>>
                    <?php if(count($this->addresses)  > 1){ ?>
                    <label for="id_address_invoice" class="strong"><?php echo JText::_('COM_JEPROSHOP_CHOOSE_A_BILLING_ADDRESS_LABEL'); ?> </label>
                    <select name="id_address_invoice" id="id_address_invoice" class="address_select form-control">
                        <?php foreach($this->addresses as $address){ ?>
                        <option value="<?php echo $address->address_id; ?>" <?php if($address->address_id == $this->context->cart->address_invoice_id && $this->context->cart->address_delivery_id != $this->context->cart->address_invoice_id){ ?> selected="selected"<?php } ?> >
                        <?php echo $address->alias; ?>
                        </option>
                        <?php } ?>
                    </select><span class="wait_image"></span>
                    <?php }else{ ?>
                    <a href="<?php JRoute::_($this->context->controller->getPageLink('address', true, NULL, 'back=' . $back_order_page . '&step=1&select_address=1' . ($back ? '&mod=' . $back : ''))); ?>" title="<?php echo JText::_('COM_JEPROSHOP_ADD_LABEL'); ?>" class="button button-small btn btn-default">
						<span>	<?php echo JText::_('COM_JEPROSHOP_ADD_NEW_ADDRESS_LABEL'); ?> 	<i class="icon-chevron-right right"></i></span>
                    </a>
                    <?php } ?>
                </div>
            </div>
        </div> <!-- end row -->
        <div class="row">
            <div class="col-xs-12 col-sm-6" <?php if($this->context->cart->isVirtualCart()){ ?> style="display:none;"<?php } ?> >
            <ul class="address item box" id="address_delivery">  </ul>
        </div>
        <div class="col-xs-12 col-sm-6">
            <ul class="address alternate_item<?php if($this->context->cart->isVirtualCart()){ ?> full_width<?php } ?> box" id="address_invoice"> </ul>
        </div>
    </div> <!-- end row -->
    <p class="address_add submit">
        <a href="<?php echo JRoute::_($this->context->controller->getPageLink('address', true, NULL, '&back=' . $back_order_page . '&step=1' . ($back ? '&mod=' . $back : ''))); ?>" title="<?php echo JText::_('COM_JEPROSHOP_ADD_LABEL'); ?>" class="button button-small btn btn-default">
        <span><?php echo JText::_('COM_JEPROSHOP_ADD_NEW_ADDRESS_LABEL'); ?> <i class="icon-chevron-right right"></i></span>
        </a>
    </p>
    <?php if(!$this->order_process_type){ ?>
    <div id="order_message" class="form-group">
        <label><?php echo JText::_('COM_JEPROSHOP_IF_YOU_WOULD_LIKE_TO_ADD_A_COMMENT_ABOUT_YOUR_ORDER_PLEASE_WRITE_IT_IN_THE_FIELD_BELOW_MESSAGE'); ?>  </label>
        <textarea class="form-control" cols="80" rows="6" name="message"><?php if(isset($this->oldMessage)){ echo $this->oldMessage;  } ?></textarea>
    </div>
    <?php } ?>
    </div> <!-- end addresses -->
    <?php if(!$this->order_process_type){ ?>
    <p class="cart_navigation clearfix">
        <input type="hidden" class="hidden" name="step" value="2" />
        <input type="hidden" name="back" value="<?php echo $back; ?> " />
        <a href="<?php echo JRoute::_($this->context->controller->getPageLink($back_order_page, true, NULL, 'step=0' . ($back ? '&back=' . $back : ''))); ?>" title="<?php echo JText::_('COM_JEPROSHOP_PREVIOUS_LABEL'); ?>" class="button-exclusive btn btn-default">
            <i class="icon-chevron-left"></i> <?php echo JText::_('COM_JEPROSHOP_CONTINUE_SHOPPING_LABEL'); ?>
        </a>
        <button type="submit" name="processAddress" class="button btn btn-default button-medium">
            <span><?php echo JText::_('COM_JEPROSHOP_PROCEED_TO_CHECKOUT_LABEL'); ?> <i class="icon-chevron-right right"></i></span>
        </button>
    </p>
</form>
<?php }else{ ?>
</div> <!--  end opc_account -->
<?php } ?>
{strip}
<?php if(!$this->order_process_type){ ?>
{addJsDef orderProcess='order'}
{addJsDef currencySign=$currencySign|html_entity_decode:2:"UTF-8"}
{addJsDef currencyRate=$currencyRate|floatval}
{addJsDef currencyFormat=$currencyFormat|intval}
{addJsDef currencyBlank=$currencyBlank|intval}
{addJsDefL name=txtProduct}<?php echo JText::_('COM_JEPROSHOP_PRODUCT_LABEL'); ?> s='product' js=1}{/addJsDefL}
{addJsDefL name=txtProducts}<?php echo JText::_('COM_JEPROSHOP_PRODUCTS_LABEL'); ?> s='products' js=1}{/addJsDefL}
{addJsDefL name=CloseTxt}<?php echo JText::_('COM_JEPROSHOP_SUBMIT_LABEL'); ?> s='Submit' js=1}{/addJsDefL}
<?php } ?>
{capture}<?php if($back){ ?>&mod={$back|urlencode}<?php } ?>{/capture}
{capture name=addressUrl}{$link->getPageLink('address', true, NULL, 'back='|cat:$back_order_page|cat:'?step=1'|cat:$smarty.capture.default)|escape:'quotes':'UTF-8'}{/capture}
{addJsDef addressUrl=$smarty.capture.addressUrl}
{capture}{'&multi-shipping=1'|urlencode}{/capture}
{addJsDef addressMultishippingUrl=$smarty.capture.addressUrl|cat:$smarty.capture.default}
{capture name=addressUrlAdd}{$smarty.capture.addressUrl|cat:'&id_address='}{/capture}
{addJsDef addressUrlAdd=$smarty.capture.addressUrlAdd}
{addJsDef formatedAddressFieldsValuesList=$formatedAddressFieldsValuesList}
{addJsDef opc=$opc|boolval}
{capture}<h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_LABEL'); ?> s='Your billing address' js=1}</h3>{/capture}
{addJsDefL name=titleInvoice}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{capture}<h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_LABEL'); ?> s='Your delivery address' js=1}</h3>{/capture}
{addJsDefL name=titleDelivery}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{capture}<a class="button button-small btn btn-default" href="{$smarty.capture.addressUrlAdd}" title="<?php echo JText::_('COM_JEPROSHOP_UPDATE_LABEL'); ?>  js=1}"><span><?php echo JText::_('COM_JEPROSHOP_UPDATE_LABEL'); ?> js=1}<i class="icon-chevron-right right"></i></span></a>{/capture}
{addJsDefL name=liUpdate}{$smarty.capture.default|@addcslashes:'\''}{/addJsDefL}
{/strip}