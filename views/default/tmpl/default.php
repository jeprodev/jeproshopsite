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
$page_name = $app->input->get('view');
$css_dir = JeproshopContext::getContext()->shop->theme_directory;
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/jeproshop.css');
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/product_list.css');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
/** add javascript */
JHtml::_('jquery.framework');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/themes/' . $css_dir . '/javascript/global.js');
$document->addScript(JURI::base() . 'components/com_jeproshop/assets/themes/' . $css_dir . '/javascript/ajax_cart.js');

if(isset($this->products) && $this->products){
    /*define numbers of product per line in other page for desktop*/
    if($page_name !='' && $page_name !='product'){
        $nbItemsPerLine = 3;
        $nbItemsPerLineTablet =2;
        $nbItemsPerLineMobile =3;
    }else{
        $nbItemsPerLine = 4;
        $nbItemsPerLineTablet = 3;
        $nbItemsPerLineMobile =2;
    }
    /*define numbers of product per line in other page for tablet*/
    $nbLi =  count($this->products);
    $nbLines = $nbLi/$nbItemsPerLine;
    $nbLinesTablet = $nbLi/$nbItemsPerLineTablet; ?>
    <!-- Products list -->
    <ul id="featured_products_block_center" class="product_list grid row" >
        <?php $liHeight =250;
        $nbItemsPerLine = 3;
        $nbLi = count($this->products);
        $nbLines = $nbLi/$nbItemsPerLine;
        $ulHeight = ceil($nbLines) * $liHeight;
        $currentIndex = 0;
        foreach($this->products as $product){
            $totModulo = (count($this->products))%$nbItemsPerLine;
            $total = count($this->products);
            $totModuloTablet = $total%$nbItemsPerLineTablet;
            $totModuloMobile = $total%$nbItemsPerLineMobile;

		    if($totModulo == 0){ $totModulo = $nbItemsPerLine; }
		    if($totModuloTablet == 0){ $totModuloTablet = $nbItemsPerLineTablet; }
		    if($totModuloMobile == 0){ $totModuloMobile = $nbItemsPerLineMobile; }
        ?>
		<li class="ajax_block_product <?php if($page_name == '' || $page_name == 'product'){} ?><?php if($currentIndex == 0){ ?>first_item<?php }elseif($currentIndex == ($nbLi-1)){ ?>last_item <?php }else{ ?>item<?php } if((($currentIndex+1)%$nbItemsPerLine) == 0){ ?>last_item_of_line<?php }elseif((($currentIndex +1)%$nbItemsPerLine) == 1){} if(($currentIndex+1) > ($nbLi - $totModulo)){ ?>last_line<?php } ?>">
            <div class="product_container" itemscope itemtype="http://schema.org/Product">
                <div class="left_block">
                    <div class="product_image_container">
                        <a class="product_img_link"	href="<?php echo JRoute::_($product->link); ?>" title="<?php echo $product->name; ?>" itemprop="url">
                            <img class="replace-2x img-responsive" src="<?php echo $this->context->controller->getImageLink($product->link_rewrite, $product->image_id, 'home_default'); ?>" alt="<?php if(!empty($product->legend)){ echo $product->legend; }else{ echo $product->name; } ?>" title="<?php if(!empty($product->legend)){ echo $product->legend; }else{ echo $product->name; } ?>" <?php if(isset($this->homeSize)){ ?> width="<?php echo $this->homeSize->width; ?>" height="<?php echo $this->homeSize->height; ?>"<?php } ?> itemprop="image" />
                        </a>
                        <div class="product-flags">
                            <?php if(!$this->catalog_mode AND ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order))){
                            if(isset($product->online_only) && $product->online_only){ ?>
                            <span class="online-only"><?php echo JText::_('COM_JEPROSHOP_ONLINE_ONLY_LABEL'); ?> </span>
                            <?php }
                            }
                            if(isset($product->on_sale) && $product->on_sale && isset($product->show_price) && $product->show_price && !$this->catalog_mode){
                            }elseif(isset($product->reduction) && $product->reduction && isset($product->show_price) && $product->show_price && !$this->catalog_mode){ ?>
                            <span class="discount"><?php echo JText::_('COM_JEPROSHOP_REDUCED_PRICE_LABEL'); ?></span>
                            <?php } ?>
                        </div>
                        <?php if(isset($this->quick_view) && $this->quick_view){ ?>
                        <div class="quick_view_wrapper_mobile">
                            <a class="quick_view_mobile" href="<?php echo JRoute::_($product->link); ?>" rel="<?php echo $product->link; ?>"><i class="icon-eye-open"></i></a>
                        </div>
                        <a class="quick_view" href="<?php echo JRoute::_($product->link); ?>" rel="<?php echo $product->link; ?>"><span><?php echo JText::_('COM_JEPROSHOP_QUICK_VIEW_LABEL'); ?> </span></a>
                        <?php } ?>
                        <?php if(!$this->catalog_mode && ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order))){ ?>
                        <div class="content_price" itemprop="offers" itemscope itemtype="http://schema.org/Offer">
                            <?php if(isset($product->show_price) && $product->show_price && !isset($this->restricted_country_mode)){ ?>
							<span itemprop="price" class="price product-price" >
							    <?php if(!$this->display_price){ echo JeproshopTools::convertPrice($product->price); }else{ echo JeproshopTools::convertPrice($product->price_tax_exc); } ?>
							</span>
                            <meta itemprop="price_currency" content="<?php echo $this->currency->iso_code; ?>" />
                            <?php if(isset($product->specific_prices) && $product->specific_prices && isset($product->specific_prices->reduction) && $product->specific_prices->reduction > 0){ ?>
                            <?php /*{hook h="displayProductPriceBlock" product=$product type="old_price"} **/ ?>
                            <span class="old-price product-price">{displayWtPrice p=$product->price_without_reduction}</span>
                            <?php if($product->specific_prices->reduction_type == 'percentage'){ ?>
                            <span class="price_percent_reduction">-<?php echo $product->specific_prices->reduction * 100; ?>%</span>
                            <?php }
                            }
                            /*{hook h="displayProductPriceBlock" product=$product type="price"}
                            {hook h="displayProductPriceBlock" product=$product type="unit_price"} */
                            }  ?>
                        </div>
                        <?php } ?>
                        <?php if(isset($product->new) && $product->new == 1){ ?>
                        <a class="new_box" href="<?php echo JRoute::_($product->link); ?>"><span class="new_label"><?php echo JText::_('COM_JEPROSHOP_NEW_LABEL'); ?> </span></a>
                        <?php }  ?>
                        <?php if(isset($product->on_sale) && $product->on_sale && isset($product->show_price) && $product->show_price && !$this->catalog_mode){ ?>
                        <a class="sale_box" href="<?php echo JRoute::_($product->link); ?>"><?php echo JText::_('COM_JEPROSHOP_SALE_LABEL'); ?></a>
                        <?php }  ?>
                    </div>
                    <?php /*{hook h="displayProductDeliveryTime" product=$product}
                    {hook h="displayProductPriceBlock" product=$product type="weight"} */ ?>
                </div>
                <div class="right_block">
                    <h5 itemprop="name">
                        <?php if(isset($product->pack_quantity) && $product->pack_quantity){ echo (int)$product->pack_quantity .' x '; }  ?>
                        <a class="product_name" href="<?php echo JRoute::_($product->link); ?>" title="<?php echo $product->name; ?>" itemprop="url" >
                            <?php echo $product->name; ?>
                        </a>
                    </h5>
                    <?php /*{hook h='displayProductListReviews' product=$product} */ ?>
                    <p class="product-desc" itemprop="description"><?php echo substr(strip_tags($product->short_description), 0, 360); ?></p>
                    <?php if (!$this->catalog_mode AND ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order))){ ?>
                    <div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="content-price">
                        <?php if(isset($product->show_price) && $product->show_price && !isset($this->restricted_country_mode)){ ?>
						<span itemprop="price" class="price product-price">
						    <?php if(!$this->display_price){echo JeproshopTools::convertPrice($product->price); }else{ echo JeproshopTools::convertPrice($product->price_tax_exc); }  ?>
						</span>
                        <meta itemprop="priceCurrency" content="<?php echo $this->currency->iso_code; ?>" />
                        <?php if(isset($product->specific_prices) && $product->specific_prices && isset($product->specific_prices->reduction) && $product->specific_prices->reduction > 0){ ?>
                        <?php /* {hook h="displayProductPriceBlock" product=$product type="old_price"} */ ?>
						<span class="old-price product-price"><?php echo JeproshopTools::displayPrice($product->price_without_reduction, $this->context->currency); ?></span>
                        <?php //{hook h="displayProductPriceBlock" id_product=$product->id_product type="old_price"} ?>
                        <?php if($product->specific_prices->reduction_type == 'percentage'){ ?>
                        <span class="price-percent-reduction">-<?php echo $product->specific_prices->reduction * 100 . '%'; ?>
                        <?php }  ?>
                        <?php }  ?>
                        <?php /*{hook h="displayProductPriceBlock" product=$product type="price"}
                        {hook h="displayProductPriceBlock" product=$product type="unit_price"} **/ ?>
                        <?php }  ?>
                    </div>
                    <?php }  ?>
                    <div class="button-container">
                        <?php if(($product->product_attribute_id == 0 || (isset($this->display_add_product) && ($this->display_add_product == 1))) && $product->available_for_order && !isset($this->restricted_country_mode) && $product->minimal_quantity <= 1 && $product->customizable != 2 && !$this->catalog_mode){
                            if((!isset($product->customization_required) || !$product->customization_required) && ($product->allow_out_of_stock_ordering || $product->quantity > 0)){
                               $static_token = JeproshopTools::getCartToken();
                                if(isset($static_token)){ ?>
                        <a class="button ajax_add_to_cart_button btn btn-default" href="<?php echo JRoute::_($this->context->controller->getPageLink('cart',false, NULL, 'task=add&product_id=' . (int)$product->product_id .'&' . $static_token . '=1', false)); ?>" rel="nofollow" title="<?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?>" data-product-id="<?php echo (int)$product->product_id; ?>"><span><?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?></span></a>
                                <?php }else{ ?>
                        <a class="button ajax_add_to_cart_button btn btn-default" href="<?php echo JRoute::_($this->context->controller->getPageLink('cart',false, NULL, 'task=add&product_id=' . (int)$product->product_id, false)); ?>" rel="nofollow" title="<?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?>" data-product-id="<?php echo (int)$product->product_id; ?>"><span><?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?></span></a>
                                <?php }
                            }else{ ?>
						<span class="button ajax_add_to_cart_button btn btn-default disabled"><span><?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?> </span></span>
                        <?php }
                        }  ?>
                        <a itemprop="url" class="button lnk-view btn btn-default" href="<?php echo JRoute::_($product->link); ?>" title="<?php echo JText::_('COM_JEPROSHOP_VIEW_LABEL'); ?> " >
                            <span><?php if((isset($product->customization_required) && $product->customization_required)){ echo JText::_('COM_JEPROSHOP_CUSTOMIZE_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_MORE_LABEL'); } ?></span>
                        </a>
                    </div>
                    <?php if(isset($product->color_list)){ ?>
                    <div class="color-list-container"><?php echo $product->color_list; ?></div>
                    <?php }  ?>

                    <?php if(!$this->catalog_mode && $this->stock_management && ((isset($product->show_price) && $product->show_price) || (isset($product->available_for_order) && $product->available_for_order))){
                        if(isset($product->available_for_order) && $product->available_for_order && !isset($this->restricted_country_mode)){ ?>
							<span itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="availability">
								<?php if ($product->allow_out_of_stock_ordering || $product->quantity > 0){ ?>
									<span class="<?php if($product->quantity <= 0 && !$product->allow_out_of_stock_ordering){ ?>out_of_stock<?php }else{ ?>available_now<?php }  ?>">
										<link itemprop="availability" href="http://schema.org/InStock" /><?php if($product->quantity <= 0){ if($product->allow_out_of_stock_ordering){ if(isset($product->available_later) && $product->available_later){ echo $product->available_later; }else{ echo JText::_('COM_JEPROSHOP_IN_STOCK_LABEL'); } }else{ echo JText::_('COM_JEPROSHOP_OUT_OF_STOCK_LABEL'); } }else{ if(isset($product->available_now) && $product->available_now){ echo $product->available_now; }else{ echo JText::_('COMè_JEPROSHOP_IN_STOCK_LABEL'); } }  ?>
									</span>
								<?php }elseif(isset($product->quantity_all_versions) && $product->quantity_all_versions > 0){ ?>
									<span class="available_dif">
										<link itemprop="availability" href="http://schema.org/LimitedAvailability" /><?php echo JText::_('COM_JEPROSHOP_PRODUCT_AVAILABLE_WITH_DIFFERENT_OPTIONS_LABEL'); ?>
									</span>
								<?php } else{ ?>
									<span class="out-of-stock">
										<link itemprop="availability" href="http://schema.org/OutOfStock" /><?php echo JText::_('COM_JEPROSHOP_OUT_OF_STOCK_LABEL'); ?>
									</span>
								<?php }  ?>
							</span>
                    <?php }
                    }  ?>
                </div>
                <?php //if($page_name != ''){ ?>
                <div class="functional_buttons clearfix">
                    <?php /*{hook h='displayProductListFunctionalButtons' product=$product} */ ?>
                    <?php if(isset($this->comparator_max_item) && $this->comparator_max_item){ ?>
                    <div class="compare">
                        <a class="add_to_compare btn btn-default" href="<?php echo JRoute::_($product->link); ?>" data-product-id="<?php echo $product->product_id; ?>"><?php echo JText::_('COM_JEPROSHOP_ADD_TO_COMPARE_LABEL'); ?> </a>
                    </div>
                    <?php } ?>
                </div>
                <?php // }  ?>
            </div><!-- .product-container> -->
        </li>
            <?php if((($currentIndex+1)%$nbItemsPerLine) == 0){ ?><div style="clear: both" ></div><?php } $currentIndex = $currentIndex + 1; ?>
        <?php } ?>
        <div style="clear: both" ></div>
	</ul>

    <script type="text/javascript" >
        /*var min_item}{l s='Please select at least one product' js=1}{/addJsDefL}
        var max_item}{l s='You cannot add more than %d product(s) to the product comparison' sprintf=$comparator_max_item js=1}{/addJsDefL}
        var comparator_max_item=$comparator_max_item}
        var comparedProductsIds=$compared_products}*/
        jQuery(document).ready(function(){
            //jQuery('#featured_products_block_center').JeproshopAjaxCart();
        });
    </script>
    <div style="clear: both" ></div>
    <!-- /MODULE Home Featured Products -->
    <?php echo $this->pagination->getListFooter(); ?>
<?php }
