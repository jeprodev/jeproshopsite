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
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/jeproshop.css');
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/order.css');

if(!$this->order_process_type != 'standard'){
$path = 'Shipping:';
$this->current_step = 'shipping'; ?>
<div id="jform_carrier_area">
    <?php echo include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'order_steps.php'); ?>
    <h1 class="page_heading" ><?php echo JText::_('COM_JEPROSHOP_SHIPPING_LABEL'); ?></h1>
    <form id="form" action="<?php echo $this->context->controller->getPageLink('order', true, NULL, 'multi-shipping=' . $this->multi_shipping); ?>" method="post" name="jform[carrier_area]" >
        <?php }else{ ?>
        <div id="jform_carrier_area" class="opc_main_block">
            <h1 class="page-heading step-num"><span>2</span> <?php echo JText::_('COM_JEPROSHOP_DELIVERY_METHODS_LABEL'); ?></h1>
            <div id="opc_delivery_methods" class="opc-main-block">
                <div id="jform_opc_delivery_methods-overlay" class="opc_overlay" style="display: none;"></div>
                <?php } ?>
                <div class="order_carrier_content box">
                    <?php if(isset($this->virtual_cart) && $this->virtual_cart){ ?>
                        <input id="jform_input_virtual_carrier" class="hidden" type="hidden" name="jform[carrier_id]" value="0" />
                    <?php }else{ ?>
                        <div id="HOOK_BEFORE_CARRIER">
                            <?php if(isset($this->carriers) && isset($this->HOOK_BEFORECARRIER)){ echo $this->HOOK_BEFORECARRIER; } ?>
                        </div>
                        <?php if(isset($this->isVirtualCart) && $this->isVirtualCart){ ?>
                        <p class="alert alert-warning"><?php echo JText::_('COM_JEPROSHOP_NO_CARRIER_NEEDED_FOR_THIS_ORDER_MESSAGE'); ?>.</p>
                        <?php }else{
                            if($this->recyclable_pack_allowed){ ?>
                        <div class="checkbox">
                            <label for="recyclable">
                                <input type="checkbox" name="recyclable" id="recyclable" value="1" <?php if($this->recyclable == 1){ ?>checked="checked" <?php } ?> />
                                <?php echo JText::_('COM_JEPROSHOP_I_WOULD_LIKE_TO_RECEIVE_MY_ORDER_IN_RECYCLED_PACKAGING_LABEL'); ?>.
                            </label>
                         </div>
                            <?php } ?>
                        <?php } ?>
                        <div class="delivery_options_address" >
                            <?php if(isset($this->delivery_option_list)){
                                foreach($this->delivery_option_list as $address_id => $option_list){ ?>
                            <p class="carrier_title">
                                <?php if(isset($this->address_collection[$address_id])){
                                    echo JText::_('COM_JEPROSHOP_CHOOSE_A_SHIPPING_OPTION_FOR_THIS_ADDRESS_LABEL') . ' : ' . $this->address_collection[$address_id]->alias;
                                }else{
                                    echo JText::_('COM_JEPROSHOP_CHOOSE_A_SHIPPING_OPTION_LABEL');
                                }
                                ?>
                            </p>
                            <div class="delivery_options" >
                                <?php if($option_list){
                                    $index = 0;
                                foreach($option_list as $key => $option){ ?>
                                <div class="delivery_option <?php if($index % 2){ ?>alternate_<?php } ?>item" >
                                    <div>
                                        <table class="resume table table-bordered<?php if(!$option->unique_carrier){ ?> not_displayable<?php } ?>" >
                                            <tr>
                                                <td class="delivery_option_radio">
                                                    <input id="jform_delivery_option_<?php echo (int)$address_id . '_' . $index; ?>" class="delivery_option_radio" type="radio" name="delivery_option[<?php echo (int)$address_id; ?>]" data-key="<?php echo $key; ?>" data-address_id="<?php echo (int)$address_id; ?>" value="<?php echo $key; ?>" <?php if(isset($delivery_option[$address_id]) && $delivery_option[$address_id] == $key){ ?> checked="checked"<?php } ?> />
                                                </td>
                                                <td class="delivery_option_logo" >
                                                    <?php $countCarriers = count($option->carrier_list); $carrierIndex = 0;
                                                    foreach($option->carrier_list as $carrier){
                                                        if($carrier->logo){ ?>
                                                    <img src="<?php echo $carrier->logo; ?>" alt="<?php echo $carrier->instance->name; ?>"/>
                                                        <?php }else if(!$option->unique_carrier){
                                                            $carrier->instance->name;
                                                            if($carrierIndex != $countCarriers){ ?> - <?php }
                                                        }
                                                    } ?>
                                                </td>
                                                <td>
                                                    <?php if($option->unique_carrier){
                                                        foreach($option->carrier_list as $carrier){
                                                            echo '<strong>' . ucfirst($carrier->instance->name) . '</strong>';
                                                        }
                                                        if(isset($carrier->instance->delay[$this->context->cookie->lang_id])){
                                                            echo $carrier->instance->delay[$this->context->cookie->cookie->lang_id];
                                                        }
                                                    }
                                                    if(count($option_list) > 1){
                                                        if($option->is_best_grade){
                                                            if($option->is_best_price){
                                                                echo JText::_('COM_JEPROSHOP_THE_BEST_PRICE_AND_SPEED_LABEL');
                                                            }else{
                                                                echo JText::_('COM_JEPROSHOP_THE_FASTEST_LABEL');
                                                            }
                                                        }else{
                                                            if($option->is_best_price){
                                                                echo JText::_('COM_JEPROSHOP_THE_BEST_PRICE_LABEL');
                                                            }
                                                        }
                                                    } ?>
                                                </td>
                                                <td class="delivery_option_price" >
                                                    <div class="delivery_option_price" >
                                                        <?php if($option->total_price_with_tax && !$option->is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))){
                                                            if ($this->use_taxes == 1){
                                                                if($this->priceDisplay == 1){
                                                                    echo JeproshopValidator::convertPrice($option->total_price_without_tax);
                                                                    if($this->display_tax_label){echo '(' . JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ')'; }
                                                                }else{
                                                                    echo JeproshopValidator::convertPrice($option->total_price_with_tax);
                                                                    if($this->display_tax_label){  echo '(' . JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL') . ')'; }
                                                                }
                                                            }else{
                                                                echo JeproshopValidator::convertPrice($option->total_price_without_tax);;
                                                            }
                                                        }else{
                                                            echo JText::_('COM_JEPROSHOP_FREE_LABEL');
                                                        } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <?php if(!$option->unique_carrier){ ?>
                                        <table class="delivery_option_carrier<?php if(isset($delivery_option[$address_id]) && $delivery_option[$address_id] == $key){?> selected<?php } ?>resume table table-bordered<?php if($option->unique_carrier){ ?> not_displayable<?php } ?>" >
                                            <tr>
                                                <?php if(!$option->unique_carrier){ ?>
                                                <td rowspan="<?php echo count($option->carrier_list); ?>" class="delivery_option_radio first_item">
                                                    <input id="delivery_option_<?php echo $address_id . '_' . $index ; ?>" class="delivery_option_radio" type="radio" name="delivery_option[<?php echo (int)$address_id; ?>]" data-key="<?php echo $key; ?>" data-id_address="<?php echo $address_id ?>" value="<?php echo $key; ?>" <?php if(isset($delivery_option[$id_address]) && $delivery_option[$address_id] == $key){ ?> checked="checked"<?php } ?> />
                                                </td>
                                                <?php }
                                                $first = current($option->carrier_list); ?>
                                                <td class="delivery_option_logo<?php if($first->product_list[0]->carrier_list[0] == 0){ ?> not_displayable<?php } ?>">
                                                    <?php if($first->logo){ ?>
                                                        <img src="<?php echo $first->logo; ?>" alt="<?php echo $first->instance->name; ?>"/>
                                                    <?php }else if(!$option->unique_carrier){
                                                        echo $first->instance->name;
                                                    } ?>
                                                </td>
                                                <td class="<?php if($option->unique_carrier){ ?>first_item<?php } if($first->product_list[0]->carrier_list[0] == 0){?> not_displayable<?php } ?>">
                                                    <input type="hidden" value="<?php echo $first->instance->carrier_id; ?>" name="jform[carrier_id]" />
                                                    <?php if(isset($first->instance->delay[$cookie->lang_id])){ ?>
                                                        <i class="icon-info-sign"></i><?php echo $first->instance->delay[$cookie->lang_id];
                                                        if(count($first->product_list) <= 1){
                                                            echo JText::_('COM_JEPROSHOP_PRODUCT_CONCERNED_LABEL') . ' : ';
                                                        }else{
                                                            echo '(' . JText::_('COM_JEPROSHOP_PRODUCTS_CONCERNED_LABEL') . ' : ';
                                                        }
                                                        $productIndex = 0;
                                                        $productNumber = count($first->product_list);
                                                        $productIteration = 1;
                                                        foreach($first->product_list as $product){
                                                            if($productIndex == 4){
                                                                echo '<acronym title="';
                                                            }
                                                            if($productIndex >= 4){
                                                                echo $product->name;
                                                                if(isset($product->attributes) && $product->attributes){
                                                                    echo$product->attributes;
                                                                }
                                                                if($productIteration != $productNumber){
                                                                    echo ',&nbsp;';
                                                                }else{
                                                                    echo '">&hellip;</acronym>)';
                                                                }
                                                            }else{
                                                                echo $product->name;
                                                                if(isset($product->attributes) && $product->attributes){
                                                                    echo $product->attributes;
                                                                }
                                                                if($productIteration != $productNumber){
                                                                    echo ',&nbsp;';
                                                                }else{
                                                                    echo ')';
                                                                }
                                                            }
                                                            $productIndex += 1;
                                                            $productIteration += 1;
                                                        }
                                                    } ?>
                                                </td>
                                                <td rowspan="<?php echo count($option->carrier_list); ?>" class="delivery_option_price">
                                                    <div class="delivery_option_price">
                                                        <?php if($option->total_price_with_tax && !$option->is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))){
                                                            if($this->use_taxes == 1){
                                                                if($this->display_price == 1){
                                                                    echo JeproshopValidator::convertPrice($option->total_price_without_tax);
                                                                    if($this->display_tax_label){ echo JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ')'; }
                                                                }else{
                                                                    echo JeproshopValidator::convertPrice($option->total_price_with_tax);
                                                                    if($this->display_tax_label){ echo '(' . JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL') . ')';  }
                                                                }
                                                            }else{
                                                                echo JeproshopValidator::convertPrice($option->total_price_without_tax);
                                                            }
                                                        }else{
                                                            echo JText::_('COM_JEPROSHOP_FREE_LABEL');
                                                        } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="delivery_option_logo<?php if($carrier->product_list[0]->carrier_list[0] == 0){ ?> not_displayable<?php } ?>" >
                                                    <?php $carrierIndex = 0; $carrierIteration = 1;

                                                    foreach($option->carrier_list as $carrier){
                                                        if($carrierIteration != 1){
                                                            if($carrier->logo){ ?>
                                                                <img src="<?php echo $carrier->logo; ?>" alt="<?php echo $carrier->instance->name; ?>"/>
                                                            <?php }else if(!$option->unique_carrier){
                                                                echo $carrier->instance->name;
                                                            }
                                                        }
                                                        $carrierIndex +=1; $carrierIteration += 1;
                                                    } ?>
                                                </td>
                                                <td class="<?php if($option->unique_carrier){ ?> first_item<?php } if($carrier->product_list[0]->carrier_list[0] == 0){ ?> not_displayable<?php } ?>">
                                                    <input type="hidden" value="<?php echo $first->instance->id; ?>" name="jform[carrier_id]" />
                                                    <?php if(isset($carrier->instance->delay[$this->context->cookie->lang_id])){ ?>
                                                        <i class="icon-info-sign"></i>
                                                        <?php echo $first->instance->delay[$cookie->lang_id];
                                                        if(count($carrier->product_list) <= 1){
                                                            echo JText::_('COM_JEPROSHOP_PRODUCT_CONCERNED_LABEL') . ' : ';
                                                        }else{
                                                            echo JText::_('COM_JEPROSHOP_PRODUCTS_CONCERNED_LABEL') . ' : ';
                                                        }
                                                        $productIndex = 0;
                                                        $productIteration = 1;
                                                        $productNumber = count($carrier->product_list);
                                                        foreach($carrier->product_list as $product){
                                                            if($productIndex == 4){
                                                                echo '<acronym title="';
                                                            }
                                                            if($productIndex >= 4){
                                                                echo $product->name;
                                                                if(isset($product->attributes) && $product->attributes){
                                                                    echo $product->attributes;
                                                                }
                                                                if($productIteration < $productNumber){
                                                                    echo ',&nbsp;';
                                                                }else{
                                                                    echo '">&hellip;</acronym>)';
                                                                }
                                                            }else{
                                                                echo $product->name;
                                                                if(isset($product->attributes) && $product->attributes){
                                                                    echo $product->attributes;
                                                                }
                                                                if($productIteration < $productNumber){
                                                                    echo ',&nbsp;';
                                                                }else{
                                                                    echo ')';
                                                                }
                                                            }
                                                            $productIteration += 1; $productIndex += 1;
                                                        }
                                                    } ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <?php } ?>
                                    </div>
                                </div><!-- end of delivery option -->
                                    <?php $index += 1; } ?> <!-- end of foreach option list -->
                                <?php } ?>
                            </div>
                                <?php } ?>
                                <div class="hook_extra_carrier" id="jform_extra_carrier_<?php echo $address_id; ?>" >
                                    <?php //if(isset($HOOK_EXTRACARRIER_ADDR) &&  isset($HOOK_EXTRACARRIER_ADDR.$id_address)){ echo $HOOK_EXTRACARRIER_ADDR.$id_address; }  ?>
                                </div>
                            <?php }else{ ?>
                            <p class="alert alert-warning" id="jform_no_carrier_warning" >
                                <?php $addresses  = $this->context->cart->getDeliveryAddressesWithoutCarriers(true);
                                if($addresses){
                                    $itemsNbr = count($addresses);
                                    $addressIndex = 0;
                                    foreach($this->context->cart->getDeliveryAddressesWithoutCarriers(true) as $address){
                                        if(empty($address->alias)){
                                            echo JText::_('COM_JEPROSHOP_NO_CARRIERS_AVAILABLE_LABEL');
                                        }else{
                                            echo JText::_('COM_JEPROSHOP_NO_CARRIERS_AVAILABLE_FOR_THE_ADDRESS_LABEL') . ' ' . $address->alias;
                                        }

                                        if(($addressIndex != $itemsNbr)){ ?><br /> <?php }
                                        $addressIndex += 1;
                                    }
                                }else{
                                    echo JText::_('COM_JEPROSHOP_NO_CARRIERS_AVAILABLE_LABEL');
                                }
                                ?>
                            </p>
                            <?php } ?>
                        </div> <!-- end delivery_options_address -->
                    <?php } ?>
                    <?php if($this->order_process_type != 'standard'){ ?>
                        <p class="carrier_title"><?php echo JText::_('COM_JEPROSHOP_LEAVE_A_MESSAGE_LABEL'); ?></p>
                        <div>
                            <p><?php echo JText::_('COM_JEPROSHOP_IF_YOU_WOULD_LIKE_TO_ADD_A_COMMENT_ABOUT_YOUR_ORDER_PLEASE_WRITE_IT_IN_THE_FIELD_BELOW_MESSAGE'); ?></p>
                            <textarea class="form-control" cols="120" rows="2" name="jform[message]" id="message">
                                <?php if(isset($this->oldMessage)){ echo $this->oldMessage; } ?>
                            </textarea>
                        </div>
                        <hr style="" />
                    <?php } ?>
                    <div id="jform_extra_carrier" style="display: none;"></div>
                    <?php if($this->gift_allowed){ ?>
                    <p class="carrier_title"><?php echo JText::_('COM_JEPROSHOP_GIFT_LABEL'); ?></p>
                    <p class="checkbox gift">
                        <input type="checkbox" name="jform[gift]" id="gift" value="1" <?php if($this->cart->gift == 1){ ?>checked="checked"<?php } ?> />
                        <label for="gift">
                            <?php echo JText::_('COM_JEPROSHOP_I_WOULD_LIKE_MY_ORDER_TO_BE_GIFT_WRAPPED_LABEL');
                            if($this->gift_wrapping_price > 0){
                                echo '&nbsp;<i>(' . JText::_('COM_JEPROSHOP_ADDITIONAL_COST_OF_LABEL'); ?>
                            <span class="price" id="jform_gift_price" >
                                <?php 	if($this->display_price == 1){
                                    echo JeproshopValidator::convertPrice($total_wrapping_tax_exc_cost);
                                }else{
                                    echo JeproshopValidator::convertPrice($total_wrapping_cost);
                                } ?>
				            </span>
                                <?php if($this->use_taxes && $this->display_tax_label){
                                    if($this->display_price == 1){
                                        echo JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL') . ')';
                                    }else{
                                        echo JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL') . ')';
                                    }
                                }
                                echo ')</i> ';
                            } ?>
                        </label>
                    </p>
                    <p id="gift_div">
                        <label for="gift_message"><?php echo JText::_('COM_JEPROSHOP_IF_YOU_WISH_YOU_CAN_ADD_A_NOTE_TO_THE_GIFT_LABEL') ; ?></label>
                        <textarea rows="2" cols="120" id="jform_gift_message" class="form-control" name="jform[gift_message]"><?php echo $this->cart->gift_message; ?></textarea>
                    </p>
                    <?php if($this->order_process_type != 'standard'){ ?>
                            <hr style="" />
                    <?php   } ?>
                    <?php } ?>
                </div>
                <?php if($conditions AND $cms_id){ ?>
                <p class="carrier_title"><?php echo JText::_('COM_JEPROSHOP_TERMS_OF_SERVICE_LABEL'); ?></p>
                <p class="checkbox" >
                    <input type="checkbox" name="cgv" id="cgv" value="1" <?php if($checkedTOS){ ?>checked="checked"<?php } ?> />
                    <label for="cgv"><?php echo JText::_('COM_JEPROSHOP_I_AGREE_TO_THE_TERMS_OF_SERVICE_AND_WILL_ADHERE_TO_THEM_UNCONDITIONALLY_MESSAGE'); ?></label>
                    <a href="<?php echo $link_conditions; ?>" class="iframe" rel="nofollow"><?php echo '(' . JText::_('COM_JEPROSHOP_READ_THE_TERMS_OF_SERVICE_LABEL') . ')';  ?></a>
                </p>
                <?php } ?>
                <?php if(!$this->order_process_type != 'standard'){ ?>
                <p class="cart_navigation clearfix">
                    <input type="hidden" name="current_step" value="3" />
                    <input type="hidden" name="back" value="<?php echo $back; ?>" />
                    <?php if(!$this->is_guest){
                        if($back){ ?>
                            <a href="<?php echo $this->context->controller->getPageLink('order', true, NULL, 'current_step=1&back=' .  $back . '&multi-shipping=' . multi_shipping); ?>" title="<?php echo JText::_('COM_JEPROSHOP_PREVIOUS_LABEL'); ?>" class="button-exclusive btn btn-default">
                                <i class="icon-chevron-left"></i>
                                <?php echo JText::_('COM_JEPROSHOP_CONTINUE_SHOPPING_LABEL') ?>
                            </a>
                        <?php }else{ ?>
                            <a href="<?php echo $this->context->controller->getPageLink('order', true, NULL, 'current_step=1&multi-shipping=' . $multi_shipping); ?>" title="<?php echo JText::_('COM_JEPROSHOP_PREVIOUS_LABEL'); ?>" class="button-exclusive btn btn-default">
                                <i class="icon-chevron-left"></i> <?php echo JText::_('COM_JEPROSHOP_CONTINUE_SHOPPING_LABEL'); ?>
                            </a>
                        <?php }
                    }else{ ?>
                        <a href="<?php echo $this->context->controller->getPageLink('order', true, NULL, 'multi-shipping=' . $multi_shipping); ?>" title="<?php echo JText::_('COM_JEPROSHOP_PREVIOUS_LABEL'); ?>" class="button-exclusive btn btn-default">
                            <i class="icon-chevron-left"></i> <?php echo JText::_('COM_JEPROSHOP_CONTINUE_SHOPPING_LABEL'); ?>
                        </a>
                    <?php }
                    if(isset($this->virtual_cart) && $this->virtual_cart || (isset($this->delivery_option_list) && !empty($this->delivery_option_list))){ ?>
                        <button type="submit" name="process_carrier" class="button btn btn-default standard-checkout button-medium">
                            <span><?php echo JText::_('COM_JEPROSHOP_PROCEED_TO_CHECKOUT_LABEL'); ?> <i class="icon-chevron-right right"></i></span>
                        </button>
                    <?php } ?>
                </p>
    </form>
    <?php }else{ ?>
    </div> <!-- end opc_delivery_methods -->
<?php } ?>
</div> <!-- end carrier_area -->
<script type="text/javascript" >
    <?php if(!$this->order_process_type != 'standard'){ ?>
    var orderProcess = 'order';
    var currencySign = '<?php echo $this->currencySign; ?>';
    var currencyRate = <?php echo (float)$this->currencyRate; ?>
    var currencyFormat = <?php echo (int)$this->currencyFormat; ?>
    var currencyBlank = '<?php echo $this->currencyBlank; ?>';
    var cart_gift;
    <?php if(isset($virtual_cart) && !$virtual_cart && $this->gift_allowed && $this->cart->gift == 1){ ?>
    cart_gift = true;
    <?php }else{ ?>
    cart_gift = false;
    <?php } ?>
    var orderUrl = '<?php echo $this->context->controller->getPageLink("order", true); ?>';
    var txtProduct = '<?php echo JText::_('COM_JEPROSHOP_PRODUCT_LABEL'); ?>';
    var txtProducts = '<?php echo JText::_('COM_JEPROSHOP_PRODUCTS_LABEL');  ?>';
    <?php }
     if($conditions){ ?>
    var msg_order_carrier = '<?php echo JText::_('COM_JEPROSHOP_YOU_MUST_AGREE_MUST_TO_THE_TERMS_OF_SERVICES_BEFORE_CONTINUING_LABEL'); ?>
    <?php }  ?>
</script>