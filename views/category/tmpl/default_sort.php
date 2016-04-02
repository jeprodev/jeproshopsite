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

if(isset($this->orderBy) AND isset($this->orderWay)){ ?>
<ul class="display hidden-xs">
    <li class="display_title"><?php echo JText::_('COM_JEPROSHOP_VIEW_LABEL'); ?> : </li>
    <li id="grid"><a rel="nofollow" href="#" title="<?php echo JText::_('COM_JEPROSHOP_GRID_LABEL'); ?>"><i class="icon-th-large"></i><?php echo JText::_('COM_JEPROSHOP_GRID_LABEL'); ?></a></li>
    <li id="list"><a rel="nofollow" href="#" title="<?php echo JText::_('COM_JEPROSHOP_LIST_LABEL'); ?>"><i class="icon-th-list"></i><?php echo JText::_('COM_JEPROSHOP_LIST_LABEL'); ?></a></li>
</ul>
<?php
/* On 1.5 the var request is settled on the front controller. The next lines assure the retrocompatibility with some modules */
if(!isset($this->request)){
    /** -- Sort products -- **/
    if(isset($this->category->category_id) && $this->category->category_id){
        $this->request = $this->context->controller->getPaginationLink('category', $this->category, false, true);
    }/*elseif isset($smarty.get.id_manufacturer) && $smarty.get.id_manufacturer}
        {assign var='request' value=$link->getPaginationLink('manufacturer', $this->manufacturer, false, true)}
{elseif isset($smarty.get.id_supplier) && $smarty.get.id_supplier}
{assign var='request' value=$link->getPaginationLink('supplier', $supplier, false, true)}
{else}
{assign var='request' value=$link->getPaginationLink(false, false, false, true)}
    } */
} ?>
<form id="products_sort_form<?php if(isset($this->paginationId)){ echo '_' . $this->paginationId; } ?>" action="<?php echo $this->request; ?>" class="products_sort_form" >
    <div class="select selector1">
        <label for="jform_select_product_sort<?php if(isset($this->paginationId)){ echo '_' . $this->paginationId; } ?>"><?php echo JText::_('COM_JEPROSHOP_SORT_BY_LABEL'); ?></label>
        <select id="jform_select_product_sort<?php if(isset($this->paginationId)){ echo '_' . $this->paginationId; } ?>" class="select_product_sort form_control">
            <option value="<?php echo $this->order_by_default . ':' . $this->order_way_default; ?>" <?php if($this->orderBy == $this->order_by_default){?>selected="selected"<?php } ?> >--</option>
            <?php if(!$this->catalog_mode){ ?>
            <option value="price:asc" <?php if($this->orderBy == 'price' AND $this->orderWay == 'asc'){?>selected="selected"<?php } ?> ><?php echo JText::_('COM_JEPROSHOP_LOWEST_PRICE_FIRST_LABEL'); ?></option>
            <option value="price:desc" <?php if($this->orderBy == 'price' AND $this->orderWay == 'desc'){?>selected="selected"<?php } ?> ><?php echo JText::_('COM_JEPROSHOP_HIGHEST_PRICE_FIRST_LABEL'); ?></option>
            <?php } ?>
            <option value="name:asc" <?php if($this->orderBy == 'name' AND $this->orderWay == 'asc'){?>selected="selected"<?php } ?> ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_NAME_A_TO_Z_LABEL'); ?></option>
            <option value="name:desc" <?php if($this->orderBy == 'name' AND $this->orderWay == 'desc'){?>selected="selected"<?php } ?> ><?php echo JText::_('COM_JEPROSHOP_PRODUCT_NAME_Z_TO_A_LABEL'); ?></option>
            <?php if($this->stock_management && !$this->catalog_mode){ ?>
            <option value="quantity:desc" <?php if($this->orderBy == 'quantity' AND $this->orderWay == 'desc'){?>selected="selected"<?php } ?> ><?php echo JText::_('COM_JEPROSHOP_IN_STOCK_LABEL'); ?></option>
            <?php } ?>
            <option value="reference:asc" <?php if($this->orderBy == 'reference' AND $this->orderWay == 'asc'){?>selected="selected"<?php } ?> ><?php echo JText::_('COM_JEPROSHOP_LOWEST_REFERENCE_FIRST_LABEL'); ?>/option>
            <option value="reference:desc" <?php if($this->orderBy == 'reference' AND $this->orderWay == 'desc'){?>selected="selected"<?php } ?> ><?php echo JText::_('COM_JEPROSHOP_HIGHEST_REFERENCE_FIRST_LABEL'); ?></option>
        </select>
    </div>
</form>
<!-- /Sort products -->
<?php if(!isset($this->paginationId) || $$this->paginationId == ''){ ?>
<script type="text/javascript" > var request = '<?php echo $this->request; ?>'; </script>
<?php   }
}