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

if($this->comparator_max_item){  ?>
<form method="post" action="{$link->getPageLink('products-comparison')|escape:'html':'UTF-8'}" class="compare_form">
    <button type="submit" class="btn btn-default button button_medium bt_compare bt_compare{if isset($paginationId)}_{$paginationId}{/if}" disabled="disabled">
        <span><?php echo JText::_('COM_JEPROSHOP_COMPARE_LABEL'); ?> (<strong class="total_compare_val"><?php echo count($this->compared_products); ?></strong>)<i class="icon-chevron-right right"></i></span>
    </button>
    <input type="hidden" name="compare_product_count" class="compare_product_count" value="<?php echo count($this->compared_products); ?>" />
    <input type="hidden" name="compare_product_list" class="compare_product_list" value="" />
</form>
<?php }
if(!isset($this->paginationId) || $this->paginationId == ''){ ?>
    <script type="text/javascript" >
    var min_item = '<?php echo JText::_('COM_JEPROSHOP_PLEASE_SELECT_AT_LEAST_ONE_PRODUCT_LABEL'); ?>';
    var max_item = '<?php echo JText::_('COM_JEPROSHOP_YOU_CANT_ADD_MORE_THAN_LABEL') . ' ' . $this->comparator_max_item . ' ' . JText::_('COM_JEPROSHOP_PRODUCT'. (($this->comparator_max_item > 1)? 'S' : '') . '_LABEL'); ?>';
    var comparator_max_item = <?php echo $this->comparator_max_item; ?>;
    var comparedProductsIds = <?php echo $this->compared_products; ?>;
    </script>
<?php }
