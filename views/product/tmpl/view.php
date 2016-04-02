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
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/product.css');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('jquery.framework');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/themes/' . $css_dir . '/javascript/fancybox/lib/jquery.mousewheel-3.0.6.pack.js');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/themes/' . $css_dir . '/javascript/fancybox/jquery.fancybox.js?v=2.1.5');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/themes/' . $css_dir . '/javascript/fancybox/helpers/jquery.fancybox-buttons.js?v=1.0.5');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/themes/' . $css_dir . '/javascript/fancybox/helpers/jquery.fancybox-thumbs.js?v=1.0.7');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/themes/' . $css_dir . '/javascript/fancybox/helpers/jquery.fancybox-media.js?v=1.0.6');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/themes/' . $css_dir . '/javascript/product.js');
//$document->addScript(JURI::base() . 'components/com_jeproshop/assets/javascript/jeproshop.js');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/javascript/script/jeprotools.js');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/javascript/script/jeprowish.js');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/javascript/script/jeprocart.js');

if(!count($this->errors)){
    if(!isset($this->price_display_precision)){ $this->price_display_precision = 2; }
    if(!$this->display_price || $this->display_price == 2){
        $product_price = $this->product->getPrice(true, null, $this->price_display_precision);
        $product_price_without_reduction = $this->product->getPriceWithoutReduction(false, NULL);
    }elseif($this->display_price == 1){
        $product_price = $this->product->getPrice(false, null, $this->price_display_precision);
        $product_price_without_reduction = $this->product->getPriceWithoutReduction(true, null);
    }else{
        $product_price = '';
        $product_price_without_reduction = '';
    }?>
<div class="primary_block row" id="jform_primary_block" xmlns="http://www.w3.org/1999/html">
    <?php if(!$this->content_only){ ?><div class="container" ><div class="top_hr" ></div></div><?php } ?>
    <?php if(isset($this->admin_action_display) && $this->admin_action_display){ ?>
        <p>
            <?php echo JText::_('COM_JEPROSHOP_PRODUCT_NOT_VISIBLE_MESSAGE'); ?>
            <input type="hidden" id="jform_admin_action_product_id" value="<?php echo $this->product->product_id; ?>" />
            <input type="submit" value="<?php echo JText::_('COM_JEPROSHOP_PUBLISH_LABEL'); ?>" name="jform[publish_button]" class="exclusive" >
            <input type="submit" value="<?php echo JText::_('COM_JEPROSHOP_BACK_LABEL'); ?>" name="jform[link_view]" class="exclusive" />
        </p>
        <p id="jform_admin_action_result" ></p>
    <?php } ?>
    <?php if(isset($this->confirmation) && $this->confirmation){ ?> <p class="confirmation" ><?php echo $this->confirmation; ?></p><?php } ?>
    <div id="jform_product_wrapper" >
        <div class="half_wrapper_left">
            <div id="jform_image_block" >
                <?php if($this->product->is_new){ ?>
                    <span class="new_box" ><span class="new_label" ><?php echo JText::_('COM_JEPROSHOP_NEW_LABEL'); ?></span></span>
                <?php } ?>
                <?php if($this->product->online_only){ ?>
                    <span class="online_only" ><span class="online_label" ><?php echo JText::_('COM_JEPROSHOP_ONLINE_ONLY_LABEL'); ?></span></span>
                <?php } ?>
                <?php if($this->product->on_sale){ ?>
                    <span class="sale_box no_print" ><span class="sale_label" ><?php echo JText::_('COM_JEPROSHOP_ON_SALE_LABEL'); ?></span></span>
                <?php }elseif($this->product->specific_price && $this->product->specific_price->reduction && ($product_price_without_reduction > $product_price)){ ?>
                    <span class="discount" ><?php echo JText::_('COM_JEPROSHOP_REDUCED_PRICE_LABEL'); ?></span>
                <?php } ?>
                <?php if($this->has_image){ ?>
                    <span id="jform_view_full_size" >
                <?php if($this->jqZoomEnabled && $this->has_image && !$this->content_only){ ?>
                    <a class="jqzoom" title="<?php if(!empty($this->cover->legend)){ echo htmlentities($this->cover->legend); }else{ echo htmlentities($this->product->name); }?>"
                       rel="gal_1" href="<?php echo $this->context->controller->getImageLink($this->product->link_rewrite, $this->cover->image_id, 'default_thick_box'); ?>" >
                        <img src="<?php echo $this->context->controller->getImageLink($this->product->link_rewrite, $this->cover->image_id, 'large_default'); ?>"
                             title="<?php if(!empty($this->cover->legend)){ echo htmlentities($this->cover->legend); }else{ echo htmlentities($this->product->name); }?>"
                             alt="<?php if(!empty($this->cover->legend)){ echo htmlentities($this->cover->legend); }else{ echo htmlentities($this->product->name); }?>" />
                    </a>
                <?php }else{ ?>
                    <a id="jform_big_picture" >
                        <img src="<?php echo $this->context->controller->getImageLink($this->product->link_rewrite, $this->cover->image_id, 'large_default'); ?>"
                             title="<?php if(!empty($this->cover->legend)){ echo htmlentities($this->cover->legend); }else{ echo htmlentities($this->product->name); }?>"
                             alt="<?php if(!empty($this->cover->legend)){ echo htmlentities($this->cover->legend); }else{ echo htmlentities($this->product->name); }?>"
                            width="<?php echo $this->large_size->width; ?>" height="<?php echo $this->large_size->height; ?>" />
                        <?php if(!$this->content_only){ ?><span class="span_link no_print" ><?php echo JText::_('COM_JEPROSHOP_VIEW_LARGER_LABEL'); ?></span><?php } ?>
                    </a>
                <?php } ?>
                </span>
                <?php }else{ ?>
                    <span id="jform_view_full_size" >
                    <img src="<?php echo $this->product_image_directory . $this->lang_iso . '_default_large_default.jpg'; ?>" id="jform_big_picture"  alt=""
                         title="<?php echo htmlentities($this->product->name); ?>" width="<?php echo $this->large_size->width; ?>" height="<?php echo $this->large_size->height; ?>" />
                        <?php if(!$this->content_only){ ?><span class="span_link" ><?php echo JText::_('COM_JEPROSHOP_VIEW_LARGER_LABEL'); ?></span><?php } ?>
                </span>
                <?php } ?>
            </div><!-- end image-block -->
            <?php if(isset($this->images) && count($this->images)){ ?>
            <!-- thumbnails -->
            <div id="jform_views_block" class="clear_fix <?php if(isset($this->images) && count($this->images)< 2){ ?> hidden <?php } ?>" >
                <?php if(isset($this->images) && count($this->images)< 2){ ?>
                <span class="view_scroll_spacer" >
                    <a id="jform_view_scroll_left" title="<?php echo JText::_('COM_JEPROSHOP_OTHER_VIEWS_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_PREVIOUS_LABEL'); ?></a>
                </span>
                <?php } ?>
                <div id="jform_thumbs_list" >
                    <ul id="jform_thumbs_list_frame" >
                        <?php if(isset($this->images)){
                            $current = 0; $nbrImages = count($this->images);
                            foreach($this->images as $image){
                                $image_ids = $this->product->product_id . '_' . $image->image_id;
                                if(!empty($image->legend)){
                                    $imageTitle = htmlentities($image->legend);
                                }else{
                                    $imageTitle = htmlentities($this->product->name);
                                }
                                $small_image = htmlentities($this->context->controller->getImageLink($this->product->link_rewrite, $image_ids, 'large_default'));
                                ?>
                                <li id="jform_thumbnail_<?php echo $image->image_id; ?>" <?php if($current == $nbrImages){ ?> class="last" <?php } ?> >
                                    <a <?php if($this->jqZoomEnabled && $this->has_image && !$this->content_only){ ?> href="javascript:void(0) "
                                        rel="{gallery: 'gal_1', small_image:'<?php echo $small_image; ?>', large_image:'<?php echo $large_image; ?>'}"
                                    <?php }else{ ?> href="<?php echo htmlentities($this->context->controller->getImageLink($this->product->link_rewrite, $image_ids, 'default_thick_box')); ?>"
                                        data-fancybox-group="other_views" class="fancy_box <?php if($image->image_id == $this->cover->image_id){ ?> shown<?php } ?>" <?php } ?> title="<?php echo $imageTitle; ?>">
                                        <img class="img_responsive" id="thumb_<?php echo $image->image_id; ?>" src="<?php echo $this->context->controller->getImageLink($this->product->link_rewrite, $image_ids, 'default_cart'); ?>"
                                             alt="<?php echo $imageTitle; ?>" title="<?php echo $imageTitle; ?>" height="<?php echo $this->cart_size->height; ?>" width="<?php echo $this->cart_size->width; ?>" />
                                    </a>
                                </li>
                            <?php }
                            $current++;
                        }?>
                    </ul>
                </div>
                <?php if(isset($this->images) && count($this->images) > 2){ ?>
                    <a id="jform_view_scroll_right" title="<?php echo JText::_('COM_JEPROSHOP_OTHER_VIEWS_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_NEXT_LABEL'); ?></a>
                <?php } ?>
            </div>
            <!-- thumbnails -->
            <?php } ?>
            <?php if(isset($this->images) && count($this->images) > 1){ ?>
            <p class="reset_image clear no_print" >
                <span id="jform_wrap_reset_images" style="display: none;" >
                    <a href="<?php echo htmlentities($this->context->controller->getProductLink($this->product)); ?>" name="jform[reset_images]" >
                        <i class="icon-repeat" ></i><?php echo JText::_('COM_JEPROSHOP_DISPLAY_ALL_PICTURES_LABEL'); ?>
                    </a>
                </span>
            </p>
            <?php } ?>
        </div><!-- end of left block -->
        <div class="half_wrapper_right">
            <div id="jform_product_details" class="horizontal-form">
                <h1><?php echo htmlentities($this->product->name); ?></h1>
                <div class="control-group" id="jform_product_reference" <?php if(empty($this->product->reference) || (!$this->product->reference)){ ?> style="display:none;" <?php } ?> >
                    <div class="control-label" ><label><?php echo JText::_('COM_JEPROSHOP_MODEL_LABEL'); ?></label></div>
                    <div class="controls" ><span class="editable" ><?php if(isset($this->groups)){ echo htmlentities($this->product->reference); } ?></span></div>
                </div>
                <?php if($this->product->condition){ ?>
                <div id="jform_product_condition" class="control-group" >
                    <div class="control-label" ><label><?php echo JText::_('COM_JEPROSHOP_CONDITION_LABEL'); ?></label></div>
                    <div class="controls" >
                        <span class="editable <?php echo $this->product->condition; ?>_product" >
                            <?php
                            if($this->product->condition == 'new' ){ echo JText::_('COM_JEPROSHOP_NEW_LABEL'); }
                            elseif($this->product->condition == 'used' ){ echo JText::_('COM_JEPROSHOP_USED_LABEL'); }
                            elseif($this->product->condition == 'refurbished' ){ echo JText::_('COM_JEPROSHOP_REFURBISHED_LABEL'); }
                            ?>
                        </span>
                    </div>
                </div>
                <?php } ?>
                <?php if($this->product->short_description || count($this->packItems) > 0){ ?>
                    <div id="jform_short_description_content" class="rte " ><?php echo $this->product->short_description; ?></div>
                    <?php if($this->product->description){ ?>
                 <p class="buttons_bottom_block">
                    <a href="#" id="jform_buttons_bottom_block" class="btn button"><?php echo JText::_('COM_JEPROSHOP_MORE_DETAILS_LABEL'); ?></a>
                 </p>
                    <?php } ?>
                <?php } ?>
                <div class="well" >
                <?php if(($this->display_quantities == 1) && !$this->catalog_mode && $this->stock_management && $this->product->available_for_order){ ?>
                <div class="control-group" id="jform_product_available_quantity" <?php if($this->product->quantity <= 0){ ?> style="display:none;" <?php } ?> >
                    <div class="control-label" ><label id="jform_available_quantity_label" ><?php echo JText::_('COM_JEPROSHOP_AVAILABLE_QUANTITY_LABEL'); ?></label></div>
                    <div class="controls">
                        <span id="jform_available_quantity" ><?php echo (int)($this->product->quantity); ?></span>
                        <span <?php if($this->product->quantity > 1){ ?> style="display: none;" <?php } ?> id="jform_available_quantity_label" ><?php echo JText::_('COM_JEPROSHOP_ITEM_LABEL'); ?></span>
                        <span <?php if($this->product->quantity == 1){ ?> style="display: none;" <?php } ?> id="jform_available_quantities_label" ><?php echo JText::_('COM_JEPROSHOP_ITEMS_LABEL'); ?></span>
                    </div>
                </div>
                <?php } ?>
                <?php if($this->stock_management){ ?>
                    <p id="jform_availability_status" <?php if(($this->product->quantity <= 0 && !$this->product->available_later && $this->allow_out_of_stock_ordering) || ($this->product->quantity > 0 && !$this->product->available_now) || $this->product->available_for_order || $this->catalog_mode){ ?> style="display: none" <?php } ?> >
                    <span id="jform_available_value" <?php if($this->product->quantity <= 0 && $this->allow_out_of_stock_ordering){ ?> class="warning alert" <?php } ?> >
                        <?php if($this->product->quantity <= 0){
                            if($this->allow_out_of_stock_ordering){ echo $this->product->available_later; }else{ echo JText::_('COM_JEPROSHOP_PRODUCT_NO_LONGER_IN_STOCK_MESSAGE'); }
                        }else{echo $this->product->available_now; } ?>
                    </span>
                    </p>
                    <p class="warning alert" id="jform_last_quantities" <?php if(($this->product->quantity > $this->last_quantities) || ($this->product->quantity <= 0 )|| $this->allow_out_of_stock_ordering || !$this->product->available_for_order || $this->catalog_mode){ ?> style="display:none; " <?php } ?> >
                        <?php echo Jtext::_('COM_JEPROSHOP_LAST_ITEMS_IN_STOCK_MESSAGE'); ?>
                    </p>
                <?php } ?>
                <div id="jform_availability_date" <?php if(($this->product->quantity > 0) || !$this->product->available_for_order || $this->catalog_mode || !isset($this->product->available_date) || $this->product->available_date < date('%Y-%m-%d')){ ?> style="display: none;"<?php } ?> class="control-group" >
                    <div id="jform_availability_date_label" class="control-label" ><label><?php echo JText::_('COM_JEPROSHOP_AVAILABILITY_DATE_LABEL') ; ?></label></div>
                    <div id="jform_availability_date_value" class="controls" ><?php echo JeproshopTools::dateFormat($this->product->available_date, false); ?></div>
                </div>
                <!-- Out of stock hook -->
                <div id="jform_out_of_stock_fields" <?php if($this->product->quantity > 0){ ?> style="display: none;" <?php } ?> >{$HOOK_PRODUCT_OOS} </div>
                <?php if(isset($this->extra_right) && $this->extra_right){ echo $this->extra_right;  } ?>
                <?php if(!$this->content_only){ ?>
                    <!-- use full links-->
                    <ul id="jform_use_full_link_block" class="clearfix no_print">
                        <?php if($this->extra_left){ echo $this->extra_left; } ?>
                        <li class="print">
                            <a href="javascript:print();"><?php echo JText::_('COM_JEPROSHOP_PRINT_LABEL'); ?></a>
                        </li>
                        <?php if($this->has_image && !$this->jqZoomEnabled){} ?>
                    </ul>
                <?php } ?>
                </div>
                <!-- Out of stock hook -->
            </div>
            <div id="jform_product_cart_form">
                <?php if(($this->product->show_price && !isset($this->restricted_country_mode)) || isset($this->groups) || $this->product->reference || (isset($this->product_actions) && $this->product_actions)){ ?>
                <!-- add to cart form-->
                <form id="jform_buy_block" action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=cart'); ?>" <?php if($this->catalog_mode && !isset($this->groups) && ($this->product->quantity > 0)){ ?> class="hidden" <?php } ?> method="post" >
                    <div class="product_info_block horizontal-form" >
                        <div class="content_prices clearfix" >
                            <?php if(!($this->product->show_price && !isset($this->restricted_country_mode) && !$this->catalog_mode)){ ?>
                            <div class="price" >
                                <div class="control-group price_display" >
                                    <div class="control-label" ></div>
                                    <div class="controls" >
                                        <?php if(($this->display_price >= 0) && ($this->display_price <= 2)){ ?>
                                        <span id="jform_price_display"><?php echo JeproshopTools::convertPrice($product_price); ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="control-group" id="jform_reduction_percent" <?php if(!$this->product->specific_price || $this->product->specific_price->reduction_type != 'percentage'){ ?> style="display:none;" <?php } ?> >
                                    <div class="control-label" ></div>
                                    <div class="controls" >
                                        <span id="jform_reduction_percent_display">
                                            <?php if($this->product->specific_price && $this->product->specific_price->reduction_type == 'percentage'){  echo '-' . ($this->product->specific_price->reduction * 100) . '%'; }  ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="control-group" id="jform_reduction_amount" <?php if(!$this->product->specific_price || $this->product->specific_price->reduction_type != 'amount' || (float)($this->product->specificPrice->reduction) ==0){ ?> style="display:none" <?php } ?> >
                                    <div class="control-label" ></div>
                                    <div class="controls" >
                                        <span id="jform_reduction_amount_display">
                                        <?php if($this->product->specific_price && $this->product->specific_price->reduction_type == 'amount' && (float)($this->product->specific_price->reduction) !=0){
                                            echo '-' . JeproshopTools::convertPrice((float)($product_price_without_reduction - $product_price));
                                        } ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="control-group" id="jform_old_price" <?php if((!$this->product->specific_price || !$this->product->specific_price->reduction) && $this->group_reduction == 0){ ?> class="hidden"<?php } ?> >
                                    <div class="control-label" ></div>
                                    <div class="controls" >
                                        <?php if($this->display_price >= 0 && $this->display_price <= 2){ ?>
                                            <!--{hook h="displayProductPriceBlock" product=$product type="old_price"} -->
                                            <span id="old_price_display"><?php if($product_price_without_reduction > $product_price){ echo JeproshopTools::convertPrice($product_price_without_reduction);  } ?></span>
                                            <?php if($this->tax_enabled && $this->context->country->display_tax_label == 1){ if($this->display_price == 1){ echo JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL'); } } ?>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php if($this->display_price == 2){ ?>
                                <div class="control-group" id="jform_pretax_price" >
                                    <div class="control-label" ></div>
                                    <div class="controls" >
                                        <span id="jform_pretax_price_display"><?php echo JeproshopTools::convertPrice($this->product->getPrice(false, NULL)); ?></span>
                                        <?php echo 	JText::_('COM_JEPROSHOP_TAX_EXCLUDED_LABEL'); ?>
                                    </div>
							    </div>
                                <?php }
                                if(count($this->packItems) && $this->product_price < $this->product->getNoPackPrice()){ ?>
                                <div class="control-group pack_price">
                                    <div class="control-label" ><?php echo JText::_('COM_JEPROSHOP_INSTEAD_OF_LABEL'); ?></div>
                                    <div class="controls" ><span style="text-decoration: line-through;"><?php echo JeproshopTools::convertPrice($this->product->getNoPackPrice()); ?></span></div>
                                </div>
                                <?php }
                                if($this->product->ecotax != 0){ ?>
                                <div class="control-group price_ecotax">
                                    <div class="controls" >
                                        <span id="jform_ecotax_price_display">
                                            <?php if($this->display_price == 2){ echo JeproshopTools::convertAndFormatPrice($this->ecotax_tax_excluded); }else{ echo JeproshopTools::convertAndFormatPrice($this->ecotax_tax_included);  } ?>
                                        </span>
                                        <?php echo ' ' . JText::_('COM_JEPROSHOP_TAX_INCLUDED_LABEL') . ' ' . JText::_('COM_JEPROSHOP_FOR_ECOTAX_LABEL'); ?>
                                        <?php if($this->product->specific_price && $this->product->specific_price->reduction) { ?>
                                            <br /><?php echo JText::_('COM_JEPROSHOP_NOT_IMPACTED_BY_DISCOUNT_LABEL'); ?>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php }
                                if(!empty($this->product->unity) && $this->product->unit_price_ratio > 0.000000){
                                    $unit_price = $product_price/$this->unit_price_ratio;  ?>
                                    <p class="unit_price"><span id="jform_unit_price_display"><?php JeproshopTools::convertPrice($unit_price); ?></span> <?php echo JText::_('COM_JEPROSHOP_PER_LABEL') . ' ' . $this->product->unity; ?></p>
                                        <!-- {hook h="displayProductPriceBlock" product=$product type="unit_price"} -->
                                <?php } ?>
                            </div>
                            <div class="product_attributes clearfix horizontal-form well">
                                <!-- quantity wanted -->
                                <?php if(!$this->catalog_mode){ ?>
                                <div class="control-group" id="jform_quantity_wanted_p" <?php if((!$this->allow_out_of_stock_ordering && $this->product->quantity <= 0) || !$this->product->available_for_order ||$this->catalog_mode){ ?> style="display: none;" <?php } ?> >
                                    <div class="control-label" ><label><?php echo JText::_('COM_JEPROSHOP_QUANTITY_LABEL'); ?></label></div>
                                    <div class="controls" >
                                        <div class="input-append">
                                            <a href="#" data-field-qty="quantity" class="btn btn-default button-minus product_quantity_down"><img src="<?php echo JURI::base() . 'components/com_jeproshop/assets/themes/default/images/minus-icon.png'; ?>"  width="14" height="14" /></a>
                                            <input type="text" name="jform[quantity]" id="jform_quantity_wanted" class="text" value="<?php if(isset($this->quantity_backup)){ echo $this->quantity_backup; }else{ if($this->product->minimal_quantity > 1){ echo $this->product->minimal_quantity; }else{ echo '1' ; } } ?>" />
                                            <a href="#" data-field-qty="quantity" class="btn btn-default button-plus product_quantity_up"><img src="<?php echo JURI::base() . 'components/com_jeproshop/assets/themes/default/images/plus-icon.png'; ?>" width="14" height="14"/> </a>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                                <!-- minimal quantity wanted -->
                                <p id="minimal_quantity_wanted_p" <?php if($this->product->minimal_quantity <= 1 || !$this->product->available_for_order || $this->catalog_mode){ ?> style="display: none;"<?php } ?> >
                                   <?php echo JText::_('COM_JEPROSHOP_THIS_PRODUCT_IS_NOT_SOLD_INDIVIDUALLY_YOU_MUST_SELECT_AT_LEAST_LABEL'); ?> <b id="minimal_quantity_label"><?php $this->product->minimal_quantity; ?></b> <?php echo JText::_('COM_JEPROSHOP_QUANTITY_FOR_THIS_PRODUCT_LABEL'); ?>
                                </p>
                                <?php if(isset($this->groups)){ ?>
                                <!-- attributes -->
                                <div id="jform_attributes" >
                                    <div class="clearfix"></div>
                                    <?php foreach($this->groups as $key => $group){
                                        if(count($group->attributes)){ ?>
                                    <!-- field set class="" -->
                                    <div class="control-group attribute_field_set" >
                                        <div class="control-label" ><label <?php if($group->group_type != 'color' && $group->group_type != 'radio'){ ?> for="jform_group_<?php echo $key; ?>" <?php } ?> ><?php echo ucfirst($group->name); ?>&nbsp;:</label></div>
                                        <?php $groupName = 'group_' . $key; ?>
                                        <div class="attribute_list controls" <?php if(($group->group_type == 'select')){ ?>style="" <?php } ?> >
                                            <?php if(($group->group_type == 'select')){ ?>
                                            <select name="<?php echo $groupName; ?>" id="jform_group_<?php echo $key; ?>" class="attribute_select no-print"  >
                                                    <?php foreach($group->attributes as $attribute_key => $attribute){ ?>
                                                        <option value="<?php echo $attribute_key; ?>" <?php if((isset($groupName) && $groupName == $attribute_key) || $group->default == $attribute_key){ ?> selected="selected"<?php } ?> title="<?php echo $attribute; ?>" ><?php echo $attribute; ?></option>
                                                    <?php } ?>
                                            </select>
                                            <?php }elseif($group->group_type == 'color'){ ?>
                                            <ul id="color_to_pick_list" class="clearfix">
                                                <?php $default_color_picker = "";
                                                foreach($group->attributes as $attribute_key => $attribute){
                                                    $img_color_exists = file_exists(COM_JEPROSHOP_COLOR_IMAGE_DIRECTORY); // |cat:$attribute_key|cat:'.jpg'); ?>
                                                <li <?php if($group->default == $attribute_key){ ?> class="selected"<?php } ?>>
                                                     <a href="<?php echo $this->context->controller->getProductLink($this->product); ?>" id="color_<?php echo $attribute_key; ?>" name="<?php echo $this->colors->{$attribute_key}->name; ?>" class="color_pick <?php if($group->default == $attribute_key){ ?>selected<?php } ?>" <?php if(!$img_color_exists && isset($colors->{$attribute_key}->value) && $colors->{$attribute_key}->value){ ?> style="background:<?php echo $colors->{$attribute_key}->value; ?>;"<?php } ?> title="<?php echo $colors->{$attribute_key}->name; ?>">
                                                        <?php if($img_color_exists){ ?>
                                                        <img src="<?php echo $this->img_col_dir . $attribute_key . '.jpg'; ?>" alt="<?php echo $this->colors->{$attribute_key}->name; ?>" title="<?php echo $this->colors->{$attribute_key}->name; ?>" width="20" height="20" />
                                                        <?php } ?>
                                                     </a>
                                                </li>
                                                <?php if($group->default == $attribute_key){ $default_color_picker = $attribute_key; }
                                                } ?>
                                            </ul>
                                            <input type="hidden" class="color_pick_hidden" name="<?php echo $groupName; ?>" value="<?php echo $default_color_picker; ?>" />
                                            <?php }elseif($group->group_type == 'radio'){ ?>
                                                <ul>
                                                    <?php foreach($group->attributes as $attributeKey => $attribute){ ?>
                                                    <li>
                                                        <input type="radio" class="attribute_radio" name="<?php echo $groupName; ?>" value="<?php echo $attributeKey; ?>" <?php if($group->default == $key){ ?> checked="checked"<?php } ?> />
                                                        <span>{$group_attribute|escape:'html':'UTF-8'}</span>
                                                    </li>
                                                    <?php } ?>
                                                </ul>
                                            <?php } ?>
                                        </div> <!-- end attribute_list -->
                                    </div>
                                    <!-- field set -->
                                    <?php }
                                     } ?>
                                </div> <!-- end attributes -->
                                <?php } ?>
                                <div style="clear: both;" ></div>
                                <div class="box_cart_bottom">
                                    <div <?php  if((!$this->allow_out_of_stock_ordering && $this->product->quantity <= 0) || !$this->product->available_for_order || (isset($this->restricted_country_mode) && $this->restricted_country_mode) || $this->catalog_mode){ ?> class="invisible"<?php }  ?>>
                                        <p id="jform_add_to_cart" class="buttons_bottom_block no-print">
                                            <button type="submit" name="Submit" class="exclusive button btn">
                                                <span><?php  if($this->content_only && (isset($this->product->customization_required) && $this->product->customization_required)){  echo JText::_('COM_JEPROSHOP_CUSTOMIZE_LABEL'); }else{  echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL');  } ?></span>
                                            </button>
                                        </p>
                                    </div>
                                    <?php if(isset($this->product_actions) && $this->product_actions){ echo $this->product_actions;  } ?>
                                    <strong></strong>
                                </div> <!-- end box-cart-bottom -->
                            </div> <!-- end product_attributes -->
                            <?php } ?>
                        </div>
                    </div>
                    <input type="hidden" name="jform[product_id]" value="<?php echo $this->product->product_id; ?>" id="jform_product_page_product_id" />
                    <input type="hidden" name="jform[product_attribute_id]" value="" id="jform_combination_id" />
                    <input type="hidden" name="task" value="add" />
                    <?php echo JHtml::_('form.token'); ?>
                </form>
                <!-- add to cart form-->
                <?php } ?>
            </div>
        </div>
        <div style="clear:both;" ></div>
        <?php if(!$this->content_only){
            echo JHtml::_('bootstrap.startTabSet', 'product_form', array('active' =>'discounts'));
            if((isset($this->quantity_discounts) && count($this->quantity_discounts) > 0)){
                echo JHtml::_('bootstrap.addTab', 'product_form', 'discounts', JText::_('COM_JEPROSHOP_VOLUME_DISCOUNTS_TAB_LABEL')) . $this->loadTemplate('discounts') . JHtml::_('bootstrap.endTab');
            }
            if(isset($this->features) && $this->features){
                echo JHtml::_('bootstrap.addTab', 'product_form', 'features', JText::_('COM_JEPROSHOP_FEATURES_TAB_LABEL')) . $this->loadTemplate('features') . JHtml::_('bootstrap.endTab');
            }
            if($this->product->description){
                echo JHtml::_('bootstrap.addTab', 'product_form', 'more_info', JText::_('COM_JEPROSHOP_MORE_INFO_TAB_LABEL')) . $this->loadTemplate('description') . JHtml::_('bootstrap.endTab');
            }
            echo JHtml::_('bootstrap.addTab', 'product_form', 'content', JText::_('COM_JEPROSHOP_CONTENT_TAB_LABEL')) . $this->loadTemplate('content') . JHtml::_('bootstrap.endTab');
            if(isset($this->accessories) && $this->accessories){
                echo JHtml::_('bootstrap.addTab', 'product_form', 'accessories', JText::_('COM_JEPROSHOP_ACCESSORIES_TAB_LABEL')) . $this->loadTemplate('accessories') . JHtml::_('bootstrap.endTab');
            }
            if(isset($this->product_footer) && $this->product_footer){
                echo JHtml::_('bootstrap.addTab', 'product_form', 'product_footer', JText::_('COM_JEPROSHOP_PRODUCT_FOOTER_TAB_LABEL')) . $this->product_footer . JHtml::_('bootstrap.endTab');
            }
            if((isset($this->product) && $this->product->description) || (isset($this->features) && $this->features) || (isset($this->accessories) && $this->accessories) || (isset($HOOK_PRODUCT_TAB) && $HOOK_PRODUCT_TAB) || (isset($this->attachments) && $this->attachments) || isset($this->product) && $this->product->customizable){
                if(isset($this->attachments) && $this->attachments){
                    echo JHtml::_('bootstrap.addTab', 'product_form', 'attachments', JText::_('COM_JEPROSHOP_ATTACHMENTS_TAB_LABEL')) . $this->loadTemplate('attachments') . JHtml::_('bootstrap.endTab');
                }
                if(isset($product) && $this->product->customizable){
                    echo JHtml::_('bootstrap.addTab', 'product_form', 'customization', JText::_('COM_JEPROSHOP_CUSTOMIZATION_TAB_LABEL')) . $this->loadTemplate('customization') . JHtml::_('bootstrap.endTab') ;
                }
            }
            if(isset($this->packItems) && count($this->packItems) > 0){
                echo JHtml::_('bootstrap.addTab', 'product_form', 'pack_content', JText::_('COM_JEPROSHOP_PACK_CONTENT_TAB_LABEL')) . $this->loadTemplate('pack_items') . JHtml::_('bootstrap.endTab');
            }
            echo JHtml::_('bootstrap.endTabSet');
        }
        ?>
    </div>
</div>

<?php } ?>


<script type="text/javascript" >
    var half_wrapper_left = jQuery(".half_wrapper_left"), half_wrapper_right = jQuery(".half_wrapper_right");
    var half_wrapper_left_width = <?php echo $this->large_size->width; ?>;
    var half_wrapper_right_width, product_wrapper_width;

    var primary_block = jQuery("#jform_primary_block");
    primary_block.JeproshopAjaxCart();
    /* window.addEvent('domready', function() {
        product_wrapper_width = jQuery("#jform_product_wrapper").width();
        half_wrapper_right_width =(product_wrapper_width - half_wrapper_left_width - 2);
        half_wrapper_left.css('width', half_wrapper_left_width + 'px');
        half_wrapper_left.css('float', 'left');
        half_wrapper_right.css('width', half_wrapper_right_width + 'px');
        half_wrapper_right.css('float', 'right');

    }); */
    //jQuery('#jform_primary_block')
</script>

<?php /*$script = 'jQuery(document).ready(function(){
    jQuery("#jform_product_wrapper").JeproshopProduct({
        allow_buy_when_out_of_stock : "' . $this->allow_out_of_stock_ordering . '", available_now_value : "' . htmlentities($this->product->available_now) . '"
, available_later_value : "' . htmlentities($this->product->available_later) . '", attribute_anchor_separator : "' . $this->attribute_anchor_separator . '",
attributes_combinations : "' . $this->attributes_combinations . '", currency_sign : "' . $this->currency_sign . '", currency_rate : "'. $this->currency_rate . '",
  currency_format :' . (int)$this->currency_format . ', currency_blank :' . (int)$this->currency_blank;
if(isset($this->combinations) && $this->combinations){
    $script .= ', combinations : ' . $this->combinations . ', combinations_from_controller : ' .  $this->combinations . ', display_discount_price : ' . $this->display_discount_price;
}
    $script .= '
    });
    }); ';
$document->addScriptDeclaration($script); */
?>
