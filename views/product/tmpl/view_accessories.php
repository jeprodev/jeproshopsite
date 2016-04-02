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
<!--Accessories -->
<div class="page-product-box block products_block accessories-block clearfix">
    <div class="block_content">
        <ul id="box_slider" class="box_slider clearfix">
            <?php $current_index = 0; $count = count($this->accessories);
            foreach($this->accessories as $accessory => $accessories_list){
                if(($accessory->allow_out_os_stock_ordering || $accessory->quantity_all_versions > 0 || $accessory->quantity > 0) && $accessory->available_for_order && !isset($this->restricted_country_mode)){
                    $accessoryLink = $this->context->controller->getProductLink($accessory->product_id, $accessory->link_rewrite, $accessory->category); ?>
                    <li class="product_box ajax_block_product<?php if($current_index == 0){ ?> first_item<?php }elseif($current_index ==($count - 1)){ ?> last_item<?php }else{ ?> item<?php } ?> product_accessories_description" >
                        <div class="product_description">
                            <a href="<?php echo $accessoryLink; ?>" title="<?php echo $accessory->legend; ?>" class="product-image product_image">
                                <img class="lazyOwl" src="<?php echo $this->context->controller->getImageLink($accessory->link_rewrite, $accessory->image_id, 'home_default'); ?>" alt="<?php echo $accessory->legend; ?>" width="<?php echo $homeSize->width; ?>" height="<?php echo $homeSize->height; ?>" />
                            </a>
                            <div class="block_description" >
                                <a href="<?php echo $accessoryLink; ?>" title="<?php echo JText::_('COM_JEPROSHOP_MORE_LABEL'); ?>" class="product_description">
                                    <?php echo substr(strip_tags($accessory->short_description), 0, 25) . '...'; ?>
                                </a>
                            </div>
                        </div>
                        <div class="title_block">
                            <h5 class="product-name"><a href="<?php echo $accessoryLink; ?>" ><?php echo substr($accessory->name, 0, 25); ?></a></h5>
                            <?php if($accessory->show_price && !isset($restricted_country_mode) && !$this->catalog_mode){ ?>
                                <span class="price">
							<?php if($this->display_price != 1){
                                echo JeproshopTools::displayWtPrice($accessory->price); }else{ echo JeproshopTools::displayWtPrice($accessory->price_tax_exc);
                            } ?>
						        </span>
                            <?php } ?>
                        </div>
                        <div class="clearfix" style="margin-top:5px">
                            <?php if(!$this->catalog_mode && ($accessory->allow_out_of_stock_ordering || $accessory->quantity > 0)){ ?>
                                <div class="no_print">
                                    <a class="exclusive button ajax_add_to_cart_button" href="<?php echo $this->context->controller->getPageLink('cart', true, NULL, 'quantity=1&;product_id=' .(int)$accessory->product_id . '&' . JSession::getFormToken() . '=1&task=add'); ?>" data-product_id="<?php echo $accessory->product; ?>" title="<?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?>">
                                        <span><?php echo JText::_('COM_JEPROSHOP_ADD_TO_CART_LABEL'); ?></span>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    </li>
                <?php }
            } ?>
        </ul>
    </div>
</div>
<!--Accessories -->