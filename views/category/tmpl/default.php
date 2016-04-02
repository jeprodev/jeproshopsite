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

//{include file="$tpl_dir./errors.tpl"}
if(isset($this->category)){
    if($this->category->category_id AND $this->category->published){
        if($this->scenes || $this->category->description || $this->category->image_id){
?>
<div class="content_scene_category" >
    <?php if($this->scenes){ ?>
    <div class="content_scene">
        <!-- Scenes -->
        <?php echo $this->loadTemplate('scenes'); ?>
        <?php if($this->category->description){ ?>
        <div class="category_description rte">
            <?php if(strlen($this->category->description) > 350){ ?>
            <div id="category_description_short"><?php echo $this->short_description; ?></div>
            <div id="category_description_full" class="invisible"><?php echo $this->category->description; ?></div>
            <a href="<?php echo $this->context->controller->getCategoryLink($this->category->category_id, $this->category->link_rewrite); ?>" class="lnk_more"><?php echo JText::_('COM_JEPROSHOP_MORE_LABEL'); ?></a>
            <?php }else{ ?>
            <div><?php echo $this->category->description; ?></div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php }else{ ?>
    <!-- Category image -->
    <div class="content_scene_cat_bg"<?php if($this->category->image_id){ ?> style="background:url(<?php echo $this->context->controller->getCatImageLink($this->category->link_rewrite, $this->category->image_id, 'category_default'); ?>) right center no-repeat; background-size:cover; min-height:<?php echo $this->categorySize->height. 'px;'; ?>"<?php } ?> >
        <?php if($this->category->description){ ?>
        <div class="category_description">
            <span class="category_name">
                <?php echo ucfirst($this->category->name);
                if(isset($this->categoryNameComplement)){ echo $this->categoryNameComplement; }
                ?>
            </span>
            <?php if(strlen($this->category->description) > 350){ ?>
            <div id="category_description_short" class="rte"><?php echo $this->short_description_short; ?></div>
            <div id="category_description_full" class="invisible rte"><?php echo $this->category->description; ?></div>
            <a href="<?php echo $this->context->controller->getCategoryLink($this->category->category_id, $this->category->link_rewrite); ?>" class="lnk_more"><?php echo JText::_('COM_JEPROSHOP_MORE_LABEL'); ?></a>
            <?php }else{ ?>
            <div class="rte"><?php echo $this->category->description; ?></div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>
        <?php } ?>
<h1 class="page-heading{if (isset($subcategories) && !$products) || (isset($subcategories) && $products) || !isset($subcategories) && $products} product-listing{/if}">
    <span class="cat-name"><?php echo ucfirst($this->category->name); if(isset($categoryNameComplement)){ echo ' ' . $categoryNameComplement; } ?></span>
    <?php echo $this->loadTemplate('count'); ?>
</h1>
        <?php if(isset($this->subcategories)){
            if((isset($this->display_subcategories) && $this->display_subcategories == 1) || !isset($this->display_subcategories)){ ?>
<!-- Subcategories -->
<div id="sub_categories">
    <p class="sub_category_heading"><?php echo JText::_('COM_JEPROSHOP_SUB_CATEGORIES_LABEL'); ?></p>
    <ul class="clearfix">
        <?php foreach($this->subcategories as $subcategory){ ?>
        <li>
            <div class="sub_category_image">
                <a href="<?php echo $this->context->controller->getCategoryLink($subcategory->category_id, $subcategory->link_rewrite); ?>" title="<?php echo $subcategory->name; ?>" class="img">
                    <?php if($subcategory->image_id){ ?>
                    <img class="replace_2x" src="<?php echo $this->context->controller->getCategoryImageLink($subcategory->link_rewrite, $subcategory->image_id, 'medium_default'); ?>" alt="" width="<?php echo $this->mediumSize->width; ?>" height="<?php echo $this->mediumSize->height; ?>" />
                    <?php } else{ ?>
                    <img class="replace_2x" src="<?php echo $this->img_cat_dir. 'default_medium_default.jpg'; ?>" alt="" width="<?php echo $this->mediumSize->width; ?>" height="<?php echo $this->mediumSize->height; ?>" />
                    <?php } ?>
                </a>
            </div>
            <h5><a class="sub_category_name" href="<?php $this->context->controller->getCategoryLink($subcategory->category_id, $subcategory->link_rewrite); ?>"><?php echo substr($subcategory->name, 0, 24); ?></a></h5>
            <?php if($subcategory->description){ ?>
            <div class="category_description" ><?php echo $subcategory->description; ?></div>
            <?php } ?>
        </li>
        <?php } ?>
    </ul>
</div>
        <?php }
        }
        if($this->category_products){ ?>
<div class="content_sort_page_bar clearfix">
    <div class="sort_page_bar clearfix">
        <?php echo $this->loadTemplate('sort'); //{include file="./product-sort.tpl"}
        //{include file="./nbr-product-page.tpl"} ?>
    </div>
    <div class="top_pagination_content clearfix">
        <?php
        echo $this->loadTemplate('compare');
        if(isset($this->pagination)){ echo $this->pagination->getFootList(); } ?>
    </div>
</div>
<?php echo $this->loadTemplate('list'); ?>
<div class="content_sort_page_bar">
    <div class="bottom_pagination_content clearfix">
        <?php
        echo $this->loadTemplate('compare');
        if(isset($this->pagination)){ echo $this->pagination->getFootList(); } ?>
    </div>
</div>
<?php }
    }elseif($this->category->category_id){ ?>
<p class="alert alert-warning"><?php echo JText::_('COM_JEPROSHOP_THIS_CATEGORY_IS_CURRENTLY_UNAVAILABLE_MESSAGE'); ?></p>
<?php }
}