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
<p class="info-title">{l s='Choose the delivery addresses'}</p>
<div id="order-detail-content" class="table_block table-responsive">
    <table id="cart_summary" class="table table-bordered multishipping-cart">
        <thead>
        <tr>
            <th class="cart_product first_item">{l s='Product'}</th>
            <th class="cart_description item">{l s='Description'}</th>
            <th class="cart_ref item">{l s='Ref.'}</th>
            <th class="cart_avail item">{l s='Avail.'}</th>
            <th class="cart_quantity item">{l s='Qty'}</th>
            <th class="shipping_address last_item">{l s='Shipping address'}</th>
        </tr>
        </thead>
        <tbody>
        {foreach $product_list as $product}
        {assign var='productId' value=$product.id_product}
        {assign var='productAttributeId' value=$product.id_product_attribute}
        {assign var='quantityDisplayed' value=0}
        {assign var='odd' value=$product@iteration%2}
        {* Display the product line *}
        {include file="$tpl_dir./order-address-product-line.tpl" productLast=$product@last productFirst=$product@first}
        {/foreach}
        </tbody>
    </table>
</div>
{addJsDefL name=CloseTxt}{l s='Submit' js=1}{/addJsDefL}
{addJsDefL name=QtyChanged}{l s='Some product quantities have changed. Please check them' js=1}{/addJsDefL}
{addJsDefL name=ShipToAnOtherAddress}{l s='Ship to multiple addresses' js=1}{/addJsDefL}