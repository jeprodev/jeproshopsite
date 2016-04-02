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
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/address.css');
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/history.css');

if(isset($this->order)){
    if(isset($this->reordering_allowed) && $this->reordering_allowed){ ?>
<div class="box box-small clearfix">
    <form id="jform_submit_reorder" action="<?php if(isset($this->order_process_type) && $this->order_process_type == 'one_page_check'){ echo $this->context->controller->getPageLink('order-opc', true);}else{ echo $this->context->controller->getPageLink('order', true); } ?>" method="post" class="submit" >
         <input type="hidden" value="<?php echo $this->order->order_id; ?>" name="order_id" id="jform_order_id" />
         <input type="hidden" value="" name="submitReorder"/>
         <a href="#" onclick="$(this).closest('form').submit(); return false;" class="button btn btn-default button-medium pull-right"><span><?php echo JText::_('COM_JEPROSHOP_REORDER_LABEL'); ?> <i class="icon-chevron-right right"></i></span></a>
         <p class="dark">
             <strong><?php echo '<span class="pull-right" >' . JText::_('COM_JEPROSHOP_ORDER_REFERENCE_LABEL') . ' : ' . $this->order->getUniqueReference() . '</span><span class="pull-right" >' . JText::_('COM_JEPROSHOP_PLACED_ON_LABEL') . ' : ' .  JeproshopValidator::dateFormat($this->order->date_add,  false) . '</span>'; ?></strong>
         </p>
    </form>
</div>
    <?php } ?>
<div class="info_order box horizontal-form" >
    <?php if($this->carrier->carrier_id){ ?>
    <div class="control-group">
        <div class="control-label" ><label ><?php echo JText::_('COM_JEPROSHOP_CARRIER_LABEL'); ?></label></div>
        <div class="controls" ><?php if($this->carrier->name == ""){ echo $this->shop_name; }else{ echo $this->carrier->name; } ?></div>
    </div>
    <?php } ?>
    <div class="control-group">
        <div class="control-label" ><label><?php echo JText::_('COM_JEPROSHOP_PAYMENT_METHOD_LABEL'); ?></label></div>
        <div class="controls" ><span class="color_my_account" ><?php echo $this->order->payment; ?></span></div>
    </div>
    <?php if($this->invoice && $this->invoiceAllowed){ ?>
    <div class="control-group">
         <?php $pdf_invoice_link = $this->context->controller->getPageLink('invoice', true) . '&type=pdf&order_id=' . (int)$this->order->order_id;
         if($this->is_guest ){ $pdf_invoice_link .= '&secure_key=' . $this->order->secure_key; } ?>
         <div class="controls" ><i class="icon-file-text"></i> <a target="_blank" href="<?php echo $pdf_invoice_link; ?>" ><?php echo JText::_('COM_JEPROSHOP_DOWNLOAD_YOUR_INVOICE_AS_FOF_FILE_MESSAGE'); ?></a></div>
    </div>
    <?php }
    if($this->order->recyclable){ ?>
    <div class="control-group" ><p class="message"><i class="icon-repeat"></i>&nbsp;<?php echo JText::_('COM_JEPROSHOP_YOU_HAVE_GIVEN_PERMISSION_TO_RECEIVE_YOUR_ORDER_IN_RECYCLED_PACKAGING_MESSAGE'); ?></p></div>
    <?php }
    if($this->order->gift){ ?>
    <div class="control-group" ><p><i class="icon-gift"></i>&nbsp; <?php echo JText::_('COM_JEPROSHOP_YOU_HAVE_REQUESTED_GIFT_WRAPPING_FOR_THIS_ORDER_MESSAGE'); ?></p></div>
    <div class="control-group" >
        <div class="control-label"><label><?php echo JText::_('COM_JEPROSHOP_MESSAGE_LABEL'); ?></label></div>
        <div class="controls" ><p><?php echo nl2br($this->order->gift_message); ?></p></div>
    </div>
    <?php } ?>
</div>
    <?php if(count($this->order_history)){ ?>
        <h1 class="page-heading"><?php echo JText::_('COM_JEPROSHOP_FOLLOW_YOUR_ORDER_STATUS_STEP_BY_STEP_LABEL') ; ?></h1>
        <div class="table_block">
            <table class="detail_step_by_step table table-bordered">
                <thead>
                    <tr>
                        <th class="first_item"><?php echo JText::_('COM_JEPROSHOP_DATE_LABEL'); ?></th>
                        <th class="last_item"><?php echo JText::_('COM_JEPROSHOP_STATUS_LABEL'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $current_index = 0; $count = count($this->order_history);
                    foreach($this->order_history as $state => $orderState){ ?>
                        <tr class="<?php if($current_index == 0){ ?>first_item <?php }elseif($current_index == ($count - 1)){ ?>last_item<?php } if($current_index % 2){ ?>alternate_item <?php }else{ ?>item <?php } $current_index++; ?>" >
                            <td class="step-by.-step-date"><?php echo JeproshopValidator::dateFormat($orderState->date_add, false); ?></td>
                            <td>
                                <span <?php if(isset($orderState->color) && $orderState->color){ ?> style="background-color:<?php echo $orderState->color; ?>; border-color:<?php echo $orderState->color; ?>;"<?php } ?>
                                    class="label<?php if(isset($orderState->color) && JeproshopValidator::getBrightness($orderState->color) > 128){ ?> dark<?php } ?>"><?php echo $orderState->ostate_name; ?></span>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php }
    if(isset($this->followup)){ ?>
        <div class="horizontal-form" >
            <div class="control-group" >
                <p class="bold"><?php echo JText::_('COM_JEPROSHOP_CLICK_THE_FOLLOWING_LINK_TO_TRACK_THE_DELIVERY_OF_YOUR_ORDER_MESSAGE'); ?></p>
                <a href="<?php echo JRoute::_($this->followup); ?>"><?php echo $this->followup; ?></a>
            </div>
        </div>
    <?php } ?>
    <div style="clear: both;" ></div>
    <div class="addresses_bloc" >
        <div class="row">
            <div class="half_wrapper left" <?php if($this->order->isVirtual()){ ?> style="display:none;" <?php } ?> >
                <h3 class="page_subheading"><?php echo JText::_('COM_JEPROSHOP_DELIVERY_ADDRESS_LABEL'); ?> ( <?php echo $this->delivery_address->alias; ?>)</h3>
                <ul class="address alternate_item box">
                    <?php foreach($this->delivery_address_fields as $dlv_loop => $field_item){
                        if($field_item == "company" && isset($this->delivery_address->company)){ ?>
                    <li class="address_company"><?php echo $this->delivery_address->company; ?></li>
                        <?php }elseif($field_item == "address2" && $this->delivery_address->address2){ ?>
                    <li class="address_address2"><?php echo $this->delivery_address->address2; ?></li>
                        <?php }elseif($field_item == "phone_mobile" && $this->delivery_address->phone_mobile){ ?>
                    <li class="address_phone_mobile"><?php echo $this->delivery_address->phone_mobile; ?></li>
                        <?php }else{
                        $address_words = explode(" ",$field_item); ?>
                    <li>
                        <?php
                            $loop_index = 0;
                            foreach($address_words as $word_item => $word_loop){
                                if(!($loop_index == 0)){  } ?>
                        <span class="address_<?php echo $word_item; //|replace:',':''} ?>" ><?php if(isset($this->delivery_address_format_values[$word_item])){ echo $this->delivery_address_format_values[$word_item]; }  ?></span>
                        <?php } $loop_index += 1;?>
                    </li>
                    <?php }
                    } ?>
                </ul>
            </div>
            <div class="half_wrapper right" >
                <h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_INVOICE_ADDRESS_LABEL') . ' (' . $this->invoice_address->alias . ')'; ?> </h3>
                <ul class="address item <?php if($this->order->isVirtual()){ ?>full_width <?php  } ?> box" >
                    <?php foreach($this->invoice_address_fields as $index => $field_item){
                        if($field_item  == "company" && isset($this->invoice_address->company)){ ?>
                    <li class="address_company"><?php echo $this->invoice_address->company; ?></li>
                        <?php }elseif($field_item == "address2" && $this->invoice_address->address2){ ?>
                    <li class="address_address2"><?php echo $this->invoice_address->address2; ?></li>
                        <?php }elseif($field_item == "phone_mobile" && $this->invoice_address->phone_mobile){ ?>
                    <li class="address_phone_mobile"><?php echo $this->invoice_address->phone_mobile; ?></li>
                        <?php }else{
                          $address_words = explode(" ",$field_item); ?>
                    <li>
                         <?php $current_index = 0; $count = count($address_words);
                         foreach($address_words as $word_item => $word_loop){
                                    // if(!$smarty.foreach.word_loop.first){ } ?>
                         <span class="address_<?php echo $word_item;  ?>"><?php if(isset($this->invoice_address_format_values[$word_item])){ echo $this->invoice_address_format_values[$word_item]; } ?></span>
                        <?php }  ?>
                    </li>
                        <?php }
                    } ?>
                </ul>
            </div>
        </div>
    </div>
    <?php echo $HOOK_ORDERDETAILDISPLAYED; ?>
    <?php if(!$this->is_guest){ ?>
    <form action="<?php echo JRoute::_($this->context->controller->getPageLink('order-follow', true)); ?>" method="post" >
    <?php } ?>
        <div id="order_detail_content" class="table_block table_responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <?php if($this->return_allowed){ ?><th class="first_item"><?php echo JHtml::_('grid.checkall'); ?></th> <?php } ?>
                        <th class="<?php if($this->return_allowed){ ?>item <?php }else{ ?>first_item<?php } ?>"><?php echo JText::_('COM_JEPROSHOP_REFERENCE_LABEL'); ?></th>
                        <th class="item"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_LABEL'); ?></th>
                        <th class="item"><?php echo JText::_('COM_JEPROSHOP_QUANTITY_LABEL'); ?></th>
                        <?php if($this->order->hasProductReturned()){ ?><th class="item"><?php echo JText::_('COM_JEPROSHOP_STATUS_LABEL'); ?></th><?php } ?>
                        <th class="item" ><?php echo JText::_('COM_JEPROSHOP_UNIT_PRICE_LABEL'); ?></th>
                        <th class="last_item"><?php echo JText::_('COM_JEPROSHOP_TOTAL_PRICE_LABEL'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($this->products as $index => $product){
                        if(!isset($product->deleted)){
                            $product_id = $product->product_id;
                            $product_attribute_id = $product->product_attribute_id;
                            if(isset($product->customizedDatas)){
                                $productQuantity = $product->product_quantity - $product->customizationQuantityTotal;
                            }else{
                                $productQuantity = $product->product_quantity;
                            }
                            /** Customized products **/
                            if(isset($product->customizedDatas)){ ?>
                    <tr class="item">
                        <?php if($this->return_allowed){ ?><td class="order_cb"><?php echo JHtml::_('grid.id', $index, $product->product_id); ?></td><?php } ?>
                        <td><label for="cb_<?php echo (int)$product->order_detail_id; ?>"><?php if($product->product_reference){ echo $product->product_reference; }else{ ?>--<?php } ?></label></td>
                        <td class="bold"><label for="cb_<?php echo (int)$product->order_detail_id; ?>"><?php echo $product->product_name; ?></label></td>
                        <td>
                            <input class="order_qte_input form-control grey"  name="order_qte_input_[<?php echo $index; ?>]" type="text" size="2" value="<?php echo $product->customizationQuantityTotal; ?>" />
                            <div class="clearfix return_quantity_buttons">
                                <a href="#" class="return_quantity_down btn btn-default button-minus"><span><i class="icon-minus"></i></span></a>
                                <a href="#" class="return_quantity_up btn btn-default button-plus"><span><i class="icon-plus"></i></span></a>
                            </div>
                            <label for="cb_<?php echo $product->order_detail_id; ?>"><span class="order_qte_span editable"><?php echo (int)$product->customizationQuantityTotal; ?></span></label>
                        </td>
                        <?php if($this->order->hasProductReturned()){ ?><td><?php $product->qty_returned; ?></td><?php } ?>
                        <td>
                            <label class="price" for="cb_<?php echo $product->order_detail_id; ?>">
                                <?php if($this->group_use_tax){
                                    echo JeproshopProductModelProduct::convertPriceWithCurrency($product->unit_price_tax_incl, $this->currency);
                                }else{
                                    echo JeproshopProductModelProduct::convertPriceWithCurrency($product->unit_price_tax_excl, $this->currency);
                                } ?>
                            </label>
                        </td>
                        <td>
                            <label class="price" for="cb_<?php echo $product->order_detail_id; ?>">
                                <?php if(isset($this->customizedDatas->$product_id->$product_attribute_id)){
                                    if($this->group_use_tax){
                                        echo JeproshopProductModelProduct::convertPriceWithCurrency($product->total_customization_wt, $this->currency);
                                    }else{
                                        echo JeproshopProductModelProduct::convertPriceWithCurrency($product->total_customization, $this->currency);
                                    }
                                }else{
                                    if($this->group_use_tax){
                                        echo JeproshopProductModelProduct::convertPriceWithCurrency($product->total_price_tax_incl, $this->currency);
                                    }else{
                                        echo JeproshopProductModelProduct::convertPriceWithCurrency($product->total_price_tax_excl, $this->currency);
                                    }
                                } ?>
                            </label>
                        </td>
                    </tr>
                    <?php foreach($product->customizedDatas  as $customizationPerAddress){
                        foreach($customizationPerAddress as $customization_id => $customization){ ?>
                    <tr class="alternate_item">
                    <?php if($this->return_allowed){ ?>
                        <td class="order_cb"><input type="checkbox" id="cb_<?php echo (int)$product->order_detail_id; ?>" name="customization_ids[<?php echo (int)$product->order_detail_id; ?>][]" value="<?php echo (int)$customizationId; ?>" /></td>
                    <?php } ?>
                    <td colspan="2">
                    <?php foreach($customization->datas as $type => $datas){
                        if($type == COM_JEPROSHOP_CUSTOMIZE_FILE){ ?>
                        <ul class="customizationUploaded" >
                            <?php foreach($datas as $data){ ?>
                            <li><img src="<?php echo $this->pic_dir . $data->value . '_small'; ?>" alt="" class="customizationUploaded" /></li>
                            <?php } ?>
                        </ul>
                        <?php }elseif($type == COM_JEPROSHOP_CUSTOMIZE_TEXT_FIELD){ ?>
                        <ul class="typedText">{counter start=0 print=false}
                            <?php foreach($datas as $data){
                                $customizationFieldName =  'Text #' . $data->customization_field_id; ?>
                            <li><?php //echo $data->name|default:$customizationFieldName} : {$data.value} ?></li>
                            <?php } ?>
                        </ul>
                        <?php  }
                    } ?>
                    </td>
                    <td>
                        <input class="order_qte_input form-control grey" name="customization_qty_input[<?php echo $customizationId; ?>]" type="text" size="2" value="<?php echo $customization->quantity; ?>" />
                        <div class="clearfix return_quantity_buttons">
                            <a href="#" class="return_quantity_down btn btn-default button-minus"><span><i class="icon-minus"></i></span></a>
                            <a href="#" class="return_quantity_up btn btn-default button-plus"><span><i class="icon-plus"></i></span></a>
                        </div>
                        <label for="cb_<?php echo $product->order_detail_id; ?>"><span class="order_qte_span editable"><?php echo $customization->quantity ?></span></label>
                    </td>
                    <td colspan="2"></td>
                </tr>
                    <?php }
                        }
                    } ?>
                <!-- Classic products -->
                <?php if($product->product_quantity > $product->customizationQuantityTotal){ ?>
                <tr class="item" >
                    <?php if($this->return_allowed){ ?><td class="order_cb" ><input type="checkbox" id="cb_<?php echo $product->order_detail_id; ?>" name="ids_order_detail[<?php echo $product->order_detail_id; ?>]" value="<?php echo (int)$product->order_detail_id; ?>" /></td><?php } ?>
                    <td><label for="cb_<?php echo (int)$product->order_detail_id; ?>"><?php if($product->product_reference){ echo $product->product_reference; }else{ ?>--<?php } ?></label></td>
                    <td class="bold" >
                        <label for="cb_<?php echo (int)$product->order_detail_id; ?>" >
                            <?php if($product->download_hash && $this->invoice && $product->display_filename != '' && $product->product_quantity_refunded == 0 && $product->product_quantity_return == 0){
                                    if(isset($this->is_guest) && $this->is_guest){ ?>
                            <a href="<?php echo $this->context->controller->getPageLink('get-file', true, NULL, 'key=' .$product->filename . '-' . $product->download_hash . '&order_id=' . $order->order_id . '&secure_key=' . $order->secure_key); ?>" title="<?php echo JText::_('COM_JEPROSHOP_DOWNLOAD_THIS_PRODUCT_LABEL'); ?> ">
                                    <?php }else{ ?>
                            <a href="{$link->getPageLink('get-file', true, NULL, "key={$product.filename|escape:'html':'UTF-8'}-{$product.download_hash|escape:'html':'UTF-8'}")|escape:'html':'UTF-8'}" title="<?php echo JText::_('COM_JEPROSHOP_DOWNLOAD_THIS_PRODUCT_LABEL'); ?>">
                                    <?php } ?>
                                <img src="{$img_dir}icon/download_product.gif" class="icon" alt="<?php echo JText::_('COM_JEPROSHOP_DOWNLOAD_THIS_PRODUCT_LABEL'); ?>" />
                            </a>
                                <?php if(isset($this->is_guest) && $this->is_guest){ ?>
                            <a href="{$link->getPageLink('get-file', true, NULL, "key={$product.filename|escape:'html':'UTF-8'}-{$product.download_hash|escape:'html':'UTF-8'}&id_order={$order->id}&secure_key={$order->secure_key}")|escape:'html':'UTF-8'}" title="<?php echo JText::_('COM_JEPROSHOP_DOWNLOAD_THIS_PRODUCT_LABEL'); ?>"> {$product.product_name|escape:'html':'UTF-8'} 	</a>
                                <?php }else{ ?>
                            <a href="{$link->getPageLink('get-file', true, NULL, "key={$product.filename|escape:'html':'UTF-8'}-{$product.download_hash|escape:'html':'UTF-8'}")|escape:'html':'UTF-8'}" title="<?php echo JText::_('COM_JEPROSHOP_DOWNLOAD_THIS_PRODUCT_LABEL'); ?>"> {$product.product_name|escape:'html':'UTF-8'} 	</a>
                                <?php }
                                }else{
                                    echo $product->product_name;
                                } ?>
                        </label>
                    </td>
                    <td class="return_quantity">
                                            <input class="order_qte_input form-control grey" name="order_qte_input[{$product.id_order_detail|intval}]" type="text" size="2" value="{$productQuantity|intval}" />
                                            <div class="clearfix return_quantity_buttons">
                                                <a href="#" class="return_quantity_down btn btn-default button-minus"><span><i class="icon-minus"></i></span></a>
                                                <a href="#" class="return_quantity_up btn btn-default button-plus"><span><i class="icon-plus"></i></span></a>
                                            </div>
                                            <label for="cb_{$product.id_order_detail|intval}"><span class="order_qte_span editable">{$productQuantity|intval}</span></label>
                    </td>
                    <?php if($this->order->hasProductReturned()){ ?><td>{$product['qty_returned']} 	</td> <?php } ?>
                    <td class="price">
                        <label for="cb_{$product.id_order_detail|intval}">
                            <?php if($this->group_use_tax){
                            echo JeproshopProductModelProduct::convertPriceWithCurrency($product->unit_price_tax_incl, $this->currency);
                            }else{
                            echo JeproshopProductModelProduct::convertPriceWithCurrency($product->unit_price_tax_excl, $this->currency);
                            } ?>
                        </label>
                    </td>
                    <td class="price">
                        <label for="cb_{$product.id_order_detail|intval}">
                            <?php if($this->group_use_tax){
                                echo JeproshopProductModelProduct::convertPriceWithCurrency($product->total_price_tax_incl, $this->currency);
                            }else {
                                echo JeproshopProductModelProduct::convertPriceWithCurrency($product->total_price_tax_excl, $this->currency);
                            } ?>
                        </label>
                    </td>
                </tr>
                        <?php }
                            }
                        }
                        foreach($this->discounts as $discount){ ?>
                <tr class="item">
                    <td><?php echo $discount->name; ?></td>
                    <td><?php echo JText::_('COM_JEPROSHOP_VOUCHER_LABEL') . ' : ' . $discount->name; ?></td>
                    <td><span class="order_qte_span editable">1</span></td>
                    <td>&nbsp;</td>
                    <td><?php if($discount->value != 0.00){ ?>-<?php } echo JeproshopProductModelProduct::convertPriceWithCurrency($discount->value, $this->currency); ?></td>
                    <?php if($this->return_allowed){ ?> <td>&nbsp;</td> <?php } ?>
                </tr>
                        <?php } ?>
            </tbody>
            <tfoot>
                <?php if($this->display_price && $this->use_tax){ ?>
                <tr class="item">
                            <td colspan="<?php if($this->return_allowed){ ?>2 <?php }else{ ?>1<?php } ?>">
                                <strong><?php echo JText::_('COM_JEPROSHOP_ITEMS_LABEL') . '  ' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL'); ?></strong>
                            </td>
                            <td colspan="<?php if($this->order->hasProductReturned()){ ?> 5 <?php }else{ ?>4<?php } ?>">
                                <span class="price"><?php echo JeproshopProductModelProduct::displayWtPriceWithCurrency($this->order->getTotalProductsWithoutTaxes(), $this->currency); ?></span>
                            </td>
                </tr>
                <?php } ?>
                <tr class="item">
                    <td colspan="<?php if($this->return_allowed){ ?>2<?php }else{ ?>1<?php  } ?>">
                        <strong><?php echo JText::_('COM_JEPROSHOP_ITEMS_LABEL'); ?>  <?php if($this->use_tax){ echo  JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL');  } ?> </strong>
                    </td>
                    <td colspan="<?php if($this->order->hasProductReturned()){ ?>5<?php }else{ ?>4<?php } ?>">
                        <span class="price"><?php echo JeproshopProductModelProduct::displayWtPriceWithCurrency($this->order->getTotalProductsWithTaxes(), $this->currency); ?></span>
                    </td>
                </tr>
                <?php if($this->order->total_discounts > 0){ ?>
                <tr class="item">
                    <td colspan="<?php if($this->return_allowed){ ?>2<?php }else{ ?>1<?php } ?>">
                        <strong><?php echo JText::_('COM_JEPROSHOP_TOTAL_VOUCHERS_LABEL'); ?></strong>
                    </td>
                    <td colspan="<?php if($this->order->hasProductReturned()){ ?>5 <?php }else{ ?>4<?php } ?>">
                        <span class="price-discount"><?php echo JeproshopProductModelProduct::displayWtPriceWithCurrency($this->order->total_discounts, $this->currency, 1); ?></span>
                    </td>
                </tr>
                <?php }
                if($this->order->total_wrapping > 0){ ?>
                <tr class="item">
                    <td colspan="<?php if($this->return_allowed){ ?>2 <?php }else{ ?>1<?php } ?>">
                        <strong><?php echo JText::_('COM_JEPROSHOP_TOTAL_GIFT_WRAPPING_COST_LABEL'); ?></strong>
                    </td>
                    <td colspan="<?php if($this->order->hasProductReturned()){ ?>5<?php }else{ ?>4<?php } ?>">
                        <span class="price-wrapping"><?php echo JeproshopProductModelProduct::displayWtPriceWithCurrency($this->order->total_wrapping, $this->currency); ?></span>
                    </td>
                </tr>
                <?php } ?>
                <tr class="item">
                    <td colspan="<?php if($this->return_allowed){ ?>2<?php }else{ ?>1<?php } ?>" >
                        <strong><?php echo JText::_('COM_JEPROSHOP_SHIPPING_AND_HANDLING_LABEL'); ?>  <?php if($this->use_tax){ ?><?php echo JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL'); ?> <?php } ?> </strong>
                    </td>
                    <td colspan="<?php if($this->order->hasProductReturned()){ ?>5<?php }else{ ?>4<?php } ?>">
                        <span class="price-shipping"><?php echo JeproshopProductModelProduct::displayWtPriceWithCurrency($this->order->total_shipping, $this->currency); ?></span>
                    </td>
                </tr>
                <tr class="total_price item">
                    <td colspan="<?php if($this->return_allowed){ ?>2<?php  }else{ ?>1<?php } ?>"><strong><?php echo JText::_('COM_JEPROSHOP_TOTAL_LABEL'); ?> </strong></td>
                    <td colspan="<?php if($this->order->hasProductReturned()){?>5<?php }else{ ?>4<?php } ?>">
                        <span class="price"><?php echo JeproshopProductModelProduct::displayWtPriceWithCurrency($this->order->total_paid, $this->currency); ?></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <?php if($this->return_allowed){ ?>
        <div id="returnOrderMessage">
            <h3 class="page-heading bottom-indent"><?php echo JText::_('COM_JEPROSHOP_MERCHANDISE_RETURN_LABEL'); ?> </h3>
            <p><?php echo JText::_('COM_JEPROSHOP_IF_YOU_WITH_TO_RETURN_ONE_OR_MORE_PRODUCTS_PLEASE_MARK_BUTTON_BELLOW_MESSAGE'); ?> s='If you wish to return one or more products, please mark the corresponding boxes and provide an explanation for the return. When complete, click the button below.'}</p>
            <p class="form-group"><textarea class="form-control" cols="67" rows="3" name="returnText"></textarea></p>
            <p class="form-group">
                <button type="submit" name="submitReturnMerchandise" class="btn btn-default button button-small"><span><?php echo JText::_('COM_JEPROSHOP_MAKE_AN_RMA_SLIP_LABEL'); ?> <i class="icon-chevron-right right"></i></span></button>
                <input type="hidden" class="hidden" value="<?php echo $this->order->order_id; ?>}" name="order_id" />
            </p>
        </div>
    <?php } ?>
    <?php if(!$this->is_guest){ ?> </form><?php } ?>
    <?php if(count($this->order->getShipping()) > 0){ ?>
        <table class="table table-bordered footab">
            <thead>
            <tr>
                <th class="first_item"><?php echo JText::_('COM_JEPROSHOP_DATE_LABEL'); ?> </th>
                <th class="item" data-sort-ignore="true"><?php echo JText::_('COM_JEPROSHOP_CARRIER_LABEL'); ?> </th>
                <th data-hide="phone" class="item"><?php echo JText::_('COM_JEPROSHOP_WEIGHT_LABEL'); ?> </th>
                <th data-hide="phone" class="item"><?php echo JText::_('COM_JEPROSHOP_SHIPPING_COST_LABEL'); ?> </th>
                <th data-hide="phone" class="last_item" data-sort-ignore="true"><?php echo JText::_('COM_JEPROSHOP_TRACKING_NUMBER_LABEL'); ?> </th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($this->order->getShipping() as $line){ ?>
                <tr class="item">
                    <td data-value="<?php echo $line->date_add; ?>"><?php echo JeproshopValidator::dateFormat($line->date_add, false); ?></td>
                    <td><?php echo $line->carrier_name; ?></td>
                    <td data-value="<?php if($line->weight > 0){ echo $line->weight;}else{ ?>0<?php } ?>" ><?php if($line->weight > 0){ echo $line->weight . ' ' . JeproshopSettingModelSetting::getValue('weight_unit'); }else{ ?>-<?php } ?></td>
                    <td data-value="<?php if($this->order->getTaxCalculationMethod() == COM_JEPROSHOP_TAX_INCLUDED){ echo $line->shipping_cost_tax_incl; }else{ echo $line->shipping_cost_tax_excl; } ?>" >
                        <?php if($this->order->getTaxCalculationMethod() == COM_JEPROSHOP_TAX_EXCLUDED){
                            echo JeproshopValidator::displayPrice($line->shipping_cost_tax_incl, $this->currency->currency_id);
                        }else{ echo JeproshopValidator::displayPrice($line->shipping_cost_tax_excl, $this->currency->currency_id);  } ?>
                    </td>
                    <td>
                        <span id="shipping_number_show">
                            <?php if($line->tracking_number){
                                if($line->url && $line->tracking_number){ ?>
                            <a href="<?php echo JRoute::_($line->url); ?>|replace:'@':$line.tracking_number}"><?php echo $line->tracking_number; ?></a>
                            <?php }else{ echo $line->tracking_number; } }else{ ?>-<?php  } ?>
                        </span>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php }
    if(!$this->is_guest){
        if(count($this->messages)){ ?>
    <h3 class="page-heading"><?php echo JText::_('COM_JEPROSHOP_MESSAGES_LABEL'); ?></h3>
    <div class="table_block">
        <table class="detail_step_by_step table table-bordered">
            <thead>
            <tr>
                <th class="first_item" style="width:150px;"><?php echo JText::_('COM_JEPROSHOP_FROM_LABEL'); ?></th>
                <th class="last_item"><?php echo JText::_('COM_JEPROSHOP_MESSAGE_LABEL'); ?></th>
            </tr>
            </thead>
            <tbody>
                <?php $current_index = 0; $count = count($this->messages);
                foreach($this->messages as $message){ // name="messageList"} ?>
                <tr class="<?php if($current_index == 0){ ?>first_item <?php }elseif($current_index == ($count -1)){ ?>last_item<?php } if($current_index % 2){ ?>alternate_item <?php }else{ ?>item<?php } $current_index++; ?>" >
                    <td>
                        <strong class="dark">
                            <?php if(isset($message->elastname) && $message->elastname){
                                echo $message->efirstname . ' ' . $message->elastname;
                            }elseif($message->clastname){
                                echo $message->cfirstname . ' ' . $message->clastname;
                            }else{
                                echo $this->shop_name;
                            } ?>
                        </strong>
                        <br />
                        <?php echo JeproshopValidator::dateFormat($message->date_add, true); ?>
                    </td>
                    <td><?php echo nl2br($message->message); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<?php }
if(isset($this->context->controller->errors) && $this->context->controller->errors){ ?>
    <div class="alert alert-danger">
        <p><?php  if(count($this->context->controller->errors) > 1){ echo JText::_('COM_JEPROSHOP_THERE_ARE_LABEL') . ' ' . count($this->context->controller->errors) . ' ' . JText::_('COM_JEPROSHOP_ERRORS_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_THERE_IS_LABEL') . ' ' . count($this->context->controller->errors) . ' ' .  JText::_('COM_JEPROSHOP_ERROR_LABEL'); } ?></p>
        <ol>
            <?php foreach($this->context->controller->errors as $key => $error){ ?><li><? echo $error; ?></li> <?php } ?>
        </ol>
    </div>
<?php }
if(isset($this->confirmation_message) && $this->confirmation_message){ ?>
    <p class="alert alert-success"><?php echo JText::_('COM_JEPROSHOP_MESSAGE_SUCCESSFULLY_SENT_LABEL'); ?> </p>
<?php } ?>
    <form action="<?php echo $this->context->controller->getPageLink('order-detail', true); ?>" method="post" class="std" id="sendOrderMessage">
        <h3 class="page-heading bottom-indent"><?php echo JText::_('COM_JEPROSHOP_ADD_A_MESSAGE_LABEL'); ?></h3>
        <p><?php echo JText::_('COM_JEPROSHOP_IF_YOU_WOULD_LIKE_TO_ADD_A_COMMENT_ABOUT_YOUR_ORDER_PLEASE_WRITE_IT_IN_THE_FIELD_BELOW_MESSAGE'); ?> </p>
        <p class="form-group" >
            <label for="id_product"><?php echo JText::_('COM_JEPROSHOP_PRODUCT_LABEL'); ?> </label>
            <select name="jform[product_id]" class="form-control">
                <option value="0"><?php echo JText::_('COM_JEPROSHOP_CHOOSE_LABEL'); ?> </option>
                <?php foreach($this->products as $product){  ?>
                    <option value="<?php echo $product->product_id; ?>"><?php echo $product->product_name; ?></option>
                <?php } ?>
            </select>
        </p>
        <p class="form-group"><textarea class="form-control" cols="67" rows="3" name="msgText"></textarea></p>
        <div class="submit">
            <input type="hidden" name="order_id" value="<?php echo $this->order->order_id; ?>" />
            <input type="submit" class="invisible" name="submitMessage" value="<?php echo JText::_('COM_JEPROSHOP_SEND_LABEL'); ?>"/>
            <button type="submit" name="submitMessage" class="button btn btn-default button-medium"><span><?php echo JText::_('COM_JEPROSHOP_SEND_LABEL'); ?><i class="icon-chevron-right right"></i></span></button>
        </div>
    </form>
<?php }else{ ?>
    <p class="alert alert-info"><i class="icon-info-sign"></i> <?php echo JText::_('COM_JEPROSHOP_YOU_CANNOT_RETURN_PRODUCT_WITH_A_GUEST_ACCOUNT_MESSAGE'); ?></p>
<?php  }
}