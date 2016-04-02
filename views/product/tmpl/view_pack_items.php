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
<div id="jform_block_pack">
    <?php $page_name = JFactory::getApplication()->input->get('view');
    if(isset($this->packItems) && $this->packItems){
        /* define numbers of product per line in other page for desktop */
        if($page_name != '' && $page_name !='product'){
            $nbItemsPerLine = 3;
            $nbItemsPerLineTablet = 2;
            $nbItemsPerLineMobile = 3;
        }else{
            $nbItemsPerLine = 4;
            $nbItemsPerLineTablet = 3;
            $nbItemsPerLineMobile = 2;
        }
        /* define numbers of product per line in other page for tablet */
        $nbLi =  count($this->packItems);
        $nbLines = $nbLi/$nbItemsPerLine;
        $nbLinesTablet = $nbLi/$nbItemsPerLineTablet; ?>
        <!-- Products list -->
    <ul <?php if(isset($id) && $id){ ?> id="<?php echo $id; ?>"<?php } ?> class="product_list grid row <?php if(isset($class) && $class){ echo ' ' . $class; } ?>" >
        <?php
        $totalItems = count($this->packItems);
        foreach($this->packItems as  $iteration => $product){
            $totModulo = count($this->packItems)% $nbItemsPerLine;
            $totModuloTablet = count($this->packItems)% $nbItemsPerLineTablet;
            $totModuloMobile = count($this->packItems)% $nbItemsPerLineMobile;
            if($totModulo == 0){ $totModulo = $nbItemsPerLine; }
            if($totModuloTablet == 0){ $totModuloTablet = $nbItemsPerLineTablet; }
            if($totModuloMobile == 0){ $totModuloMobile = $nbItemsPerLineMobile; }

            ?>

        <li class="ajax_block_product<?php if($page_name == 'index' || $page_name == 'product'){ ?> col-xs-12 col-sm-4 col-md-3<?php }else{ ?> col-xs-12 col-sm-6 col-md-4<?php } if($iteration%$nbItemsPerLine == 0){ ?> last_in_line<?php }elseif($iteration%$nbItemsPerLine == 1){ ?> first_in_line<?php } if($iteration > ($countItems - $totModulo)){ ?> last_line<?php } if($iteration%$nbItemsPerLineTablet == 0){ ?> last_item_of_tablet_line <?php }elseif($iteration%$nbItemsPerLineTablet == 1){ ?> first_item_of_tablet_line<?php } if($iteration%$nbItemsPerLineMobile == 0){ ?> last_item_of_mobile_line<?php }elseif($iteration%$nbItemsPerLineMobile == 1){ ?> first_item_of_mobile_line<?php } if($iteration > ($countItems- $totModuloMobile)){ ?> last_mobile_line<?php } ?>">
            <div class="product_container" itemscope itemtype="http://schema.org/Product">
                <div class="half_wrapper left">
                    <div class="product_image_container" >
                        <a class="product_img_link"	href="<?php echo JRoute::_($product->link); ?>" title="<?php echo $product->name; ?>" itemprop="url">
                            <img class="replace_2x image_responsive" src="<?php echo $this->context->controller->getImageLink($product->link_rewrite, $product->image_id, 'home_default'); ?>" alt="<?php if(!empty($product->legend)){ echo $product->legend; }else{ $product->name; } ?>" title="<?php if(!empty($product->legend)){ echo $product->legend; }else{ echo $product->name; } ?>" <?php if(isset($this->homeSize)){ ?> width="<?php echo $this->homeSize->width; ?>" height="<?php echo $this->homeSize->height; ?>"<?php } ?> itemprop="image" />
                        </a>
                        <?php if(isset($this->quick_view) && $this->quick_view){ ?>
                        <div class="quick_view_wrapper-mobile">
                            <a class="quick-view-mobile" href="<?php echo $product->link; ?>" rel="<?php echo $product->link; ?>"> <i class="icon-eye-open"></i>  </a>
                        </div>
                        <a class="quick_view" href="<?php echo $product->link; ?>" rel="<?php echo $product->link; ?>" >
                            <span><?php echo  JText::_('COM_JEPROSHOP_QUICK_VIEW_LABEL'); ?></span>
                        </a>
                        <?php }
                        if (!$this->catalog_mode && ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order))){ ?>
                        <div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                            <?php if(isset($product->show_price) && $product->show_price && !isset($restricted_country_mode)){ ?>
                            <span itemprop="price" class="price product-price">
								<?php if(!$this->display_price){ echo JeproshopValidator::convertPrice($product->price); }else{ echo JeproshopValidator::convertPrice($product->price_tax_exc); } ?>
							</span>
                            <meta itemprop="price_currency" content="<?php echo $this->currency->iso_code; ?>" />
                            <?php if(isset($product->specific_prices) && $product->specific_prices && isset($product->specific_prices->reduction) && $product->specific_prices->reduction > 0){ ?>
                                        {hook h="displayProductPriceBlock" product=$product type="old_price"}
                            <span class="old_price product_price"><?php echo JeproshopValidator::displayWtPrice($product->price_without_reduction); ?></span>
                            <?php if($product->specific_prices->reduction_type == 'percentage'){ ?>
                            <span class="price_percent_reduction">-<?php echo ($product->specific_prices->reduction * 100) . '%'; ?></span>
                            <?php }
                                }
                                    //{hook h="displayProductPriceBlock" product=$product type="price"}
                                    //{hook h="displayProductPriceBlock" product=$product type="unit_price"}
                            } ?>
                        </div>
                        <?php }
                        if(isset($product->new) && $product->new == 1){ ?>
                        <a class="new_box" href="<?php echo JRoute::_($product->link); ?>" ><span class="new-label"><?php echo JText::_('COM_JEPROSHOP_NEW_LABEL'); ?></span></a>
                        <?php }
                        if(isset($product->on_sale) && $product->on_sale && isset($product->show_price) && $product->show_price && !$this->catalog_mode){ ?>
                        <a class="sale_box" href="<?php echo JRoute::_($product->link); ?>"> <span class="sale_label"><?php echo JText::_('COM_JEPROSHOP_SALE_LABEL'); ?> </span> </a>
                        <?php } ?>
                    </div>
                    ?php       /* {hook h="displayProductDeliveryTime" product=$product}
                    {hook h="displayProductPriceBlock" product=$product type="weight"} */ ?>
                </div>
                <div class="half_wrapper right">
                    <h5 itemprop="name">
                        <?php if(isset($product->pack_quantity) && $product->pack_quantity){ echo $product->pack_quantity . ' x '; } ?>
                        <a class="product-name" href="<?php echo JRoute::_($product->link); ?>" title="<?php echo $product->name; ?>" itemprop="url" >
                            <?php echo substr($product->name, 0, 45); ?>
                        </a>
                    </h5>
                    <?php //{hook h='displayProductListReviews' product=$product} ?>
                    <p class="product_description" itemprop="description"><?php echo substr(strip_tags($product->short_description[$this->context->language->lang_id]), 0, 360); ?></p>
                    <?php if((!$this->catalog_mode AND ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order)))){ ?>
                    <div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="content_price">
                        <?php if(isset($product->show_price) && $product->show_price && !isset($restricted_country_mode)){ ?>
                        <span itemprop="price" class="price product_price">
							<?php if(!$this->display_price){ echo JeproshopValidator::convertPrice($product->price); }else{ echo JeproshopValidator::convertPrice($product->price_tax_exc);} ?>
						</span>
                        <meta itemprop="priceCurrency" content="<?php echo $this->currency->iso_code; ?>" />
                        <?php if(isset($product->specific_prices) && $product->specific_prices && isset($product->specific_prices->reduction) && $product->specific_prices->reduction > 0){
                                //{hook h="displayProductPriceBlock" product=$product type="old_price"} ?>
                        <span class="old-price product-price"><?php echo JeproshopValidator::displayWtPrice($product->price_without_reduction); ?></span>
                        <?php //{hook h="displayProductPriceBlock" id_product=$product.id_product type="old_price"} ?>
                        <?php if($product->specific_prices->reduction_type == 'percentage'){ ?>
                        <span class="price-percent-reduction">-<?php echo $product->specific_prices->reduction * 100 ?>%</span>
                        <?php }
                            }
                            //{hook h="displayProductPriceBlock" product=$product type="price"}
                            //{hook h="displayProductPriceBlock" product=$product type="unit_price"}
                        } ?>
                    </div>
                    <?php } ?>
                    <div class="button_container">
                        <?php if(($product->product_attribute_id == 0 || (isset($add_prod_display) && ($add_prod_display == 1))) && $product->available_for_order && !isset($restricted_country_mode) && $product->minimal_quantity <= 1 && $product->customizable != 2 && !$this->catalog_mode){
                            if((!isset($product->customization_required) || !$product->customization_required) && ($product->allow_oosp || $product->quantity > 0)){
                                if(isset($static_token)){ ?>
                                    <a class="button ajax_add_to_cart_button btn btn-default" href="<?php echo JRoute::_($this->context->controller->getPageLink('cart',false, NULL, 'task=add&quantity=1&product_id=' . (int)$product->product_id, false)); ?>|escape:'html':'UTF-8'}" rel="no_follow" title="<?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?>" data-product-id="<?php echo $product->product_id; ?>">
                                        <span><?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?></span>
                                    </a>
                                <?php }else{ ?>
                                    <a class="button ajax_add_to_cart_button btn btn-default" href="<?php echo JRoute::_($this->context->controller->getPageLink('cart',false, NULL, 'task=add&quantity=1&product_id=' . (int)$product->product_id , false)); ?>" rel="no_follow" title="<?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?>" data-id-product="<?php echo $product->product_id; ?>">
                                        <span><?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?></span>
                                    </a>
                                <?php }
                            }else{ ?>
                                <span class="button ajax_add_to_cart_button btn btn-default disabled"><?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?></span>
                            <?php }
                        } ?>
                        <a itemprop="url" class="button lnk_view btn btn-default" href="<?php echo JRoute::_($product->link); ?>" title="<?php echo JText::_('COM_JEPROSHOP_VIEW_LABEL'); ?>">
                            <span><?php if(isset($product->customization_required) && $product->customization_required){ echo JText::_('COM_JEPROSHOP_CUSTOMIZE_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_MORE_LABEL'); } ?></span>
                        </a>
                    </div>
                    <?php if(isset($product->color_list)){ ?><div class="color_list_container"><?php echo $product->color_list; ?></div><?php } ?>
                    <div class="product_flags">
                        <?php if(!$this->catalog_mode AND ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order))){
                            if(isset($product->online_only) && $product->online_only){ ?>
                                <span class="online_only"><?php echo JText::_('COM_JEPROSHOP_ONLINE_ONLY_LABEL'); ?>{l s='Online only'}</span>
                            <?php }
                        }
                        if(isset($product->on_sale) && $product->on_sale && isset($product->show_price) && $product->show_price && !$this->catalo_mode){
                        }elseif(isset($product->reduction) && $product->reduction && isset($product->show_price) && $product->show_price && !$this->catalog_mode){ ?>
                            <span class="discount"><?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?><?php echo JText::_('COM_JEPROSHOP_REDUCED_PRICE_LABEL'); ?></span>
                        <?php } ?>
                    </div>
                    <?php if((!$this->catalog_mode && $this->stock_management && ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order)))){
                        if(isset($product->available_for_order) && $product->available_for_order && !isset($restricted_country_mode)){ ?>
                            <span itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="availability">
						<?php if($product->allow_oosp || $product->quantity > 0){ ?>
                            <span class="<?php if($product->quantity <= 0 && !$product->allow_out_of_stock_ordering){ ?>out-of-stock<?php }else{ ?>available-now <?php } ?>">
						    <link itemprop="availability" href="http://schema.org/InStock" />
                                <?php if($product->quantity <= 0){
                                    if($product->allow_oosp){
                                        if(isset($product->available_later) && $product->available_later){
                                            echo $product->available_later;
                                        }else{
                                            echo JText::_('COM_JEPROSHOP_IN_STOCK_LABEL');
                                        }
                                    }else{
                                        echo JText::_('COM_JEPROSHOP_OUT_OF_STOCK_LABEL');
                                    }
                                }else{
                                    if(isset($product->available_now) && $product->available_now){
                                        echo $product->available_now;
                                    }else{
                                        echo JText::_('COM_JEPROSHOP_IN_STOCK_LABEL');
                                    }
                                } ?>
						</span>
                        <?php }elseif(isset($product->quantity_all_versions) && $product->quantity_all_versions > 0){ ?>
                            <span class="available-dif">
						        <link itemprop="availability" href="http://schema.org/LimitedAvailability" /><?php echo JText::_('COM_JEPROSHOP_PRODUCT_AVAILABLE_WITH_DIFFERENT_OPTIONS_LABEL'); ?>
						    </span>
                        <?php }else{ ?>
                            <span class="out-of-stock">
						    <link itemprop="availability" href="http://schema.org/OutOfStock" /><?php echo JText::_('COM_JEPROSHOP_OUT_OF_STOCK_LABEL'); ?>
						</span>
                        <?php } ?>
					</span>
                        <?php }
                    } ?>

                </div>
                <?php if($page_name != 'index'){ ?>
                    <div class="functional_buttons clearfix">
                        <?php //{hook h='displayProductListFunctionalButtons' product=$product} ?>
                        <?php if(isset($this->comparator_max_item) && $this->comparator_max_item){ ?>
                            <div class="compare">
                                <a class="add_to_compare" href="<?php echo $product->link; ?>" data-product-id="<?php echo $product->product_id; ?>" ><?php echo JText::_('COM_JEPROSHOP_ADD_TO_COMPARE_LABEL'); ?></a>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </li>
        <?php } ?>
    </ul>
    {addJsDefL name=min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
    {addJsDefL name=max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
    {addJsDef comparator_max_item=$comparator_max_item}
    {addJsDef comparedProductsIds=$compared_products}
    <?php } ?>
</div>