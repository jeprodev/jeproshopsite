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


//{capture name="url_back"}
if(isset($this->back) && $this->back){ $back = $this->back; }else{ $back = ''; }


if(!isset($multi_shipping)){ $this->multi_shipping  = 0; }

if($this->order_process_type == "standard"){ ?>
    <!-- Steps -->
    <ul class="step clearfix" id="order_step">
        <li class="<?php if($this->current_step=='summary') { ?>step_current <?php }elseif($this->current_step=='login'){ ?>step_done_last step_done<?php }else{ if($this->current_step=='payment' || $this->current_step=='shipping' || $this->current_step=='address' || $this->current_step=='login'){ ?>step_done <?php }else{ ?>step_todo <?php } } ?>first">
            <?php if($this->current_step=='payment' || $this->current_step=='shipping' || $this->current_step=='address' || $this->current_step=='login'){ ?>
                <a href="<?php echo JRoute::_($this->context->controller->getPageLink('order', true)); ?>">
                    <em>01.</em> <?php echo JText::_('COM_JEPROSHOP_SUMMARY_LABEL'); ?>
                </a>
            <?php }else{ ?>
                <span><em>01.</em> <?php echo JText::_('COM_JEPROSHOP_SUMMARY_LABEL'); ?></span>
            <?php } ?>
        </li>
        <li class="<?php if($this->current_step=='login'){ ?>step_current<?php }elseif($this->current_step=='address'){ ?>step_done step_done_last <?php }else{ if($this->current_step=='payment' || $this->current_step=='shipping' || $this->current_step=='address'){ ?>step_done<?php }else{ ?>step_todo<?php } } ?> second">
            <?php if($this->current_step=='payment' || $this->current_step=='shipping' || $this->current_step=='address'){ ?>}
                <a href="<?php echo $this->context->controller->getPageLink('order', true, NULL, '$smarty.capture.url_back}&step=1&multi-shipping=' . $this->multi_shipping); ?>">
                    <em>02.</em> <?php echo JText::_('COM_JEPROSHOP_SIGN_IN_LABEL'); ?>
                </a>
            <?php }else{ ?>
                <span><em>02.</em> <?php echo JText::_('COM_JEPROSHOP_SIGN_IN_LABEL'); ?></span>
            <?php } ?>
        </li>
        <li class="<?php if($this->current_step=='address'){ ?>step_current<?php }elseif($this->current_step=='shipping'){ ?>step_done step_done_last<?php }else{ if($this->current_step=='payment' || $this->current_step=='shipping'){ ?>step_done<?php }else{ ?>step_todo <?php }} ?> third">
            <?php if($this->current_step=='payment' || $this->current_step=='shipping'){ ?>
                <a href="<?php echo $this->context->controller->getPageLink('order', true, NULL, $url_back . '&step=1&multi-shipping=' . $this->multi_shipping); ?>">
                    <em>03.</em> <?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?>
                </a>
            <?php }else{ ?>
                <span><em>03.</em> <?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?></span>
            <?php } ?>
        </li>
        <li class="<?php if($this->current_step=='shipping'){ ?> step_current<?php }else{ if($this->current_step=='payment'){ ?>step_done step_done_last<?php }else{ ?>step_todo <?php }} ?> four">
            <?php if($this->current_step=='payment'){ ?>
                <a href="<?php echo $this->context->controller->getPageLink('order', true, NULL, $url_back . '&step=2&multi-shipping=' . $this->multi_shipping); ?>">
                    <em>04.</em> <?php echo JText::_('COM_JEPROSHOP_SHIPPING_LABEL'); ?>
                </a>
            <?php }else{ ?>
                <span><em>04.</em> <?php echo JText::_('COM_JEPROSHOP_SHIPPING_LABEL'); ?></span>
            <?php } ?>
        </li>
        <li id="step_end" class="<?php if($this->current_step=='payment'){ ?>step_current<?php }else{ ?>step_todo<?php } ?> last">
            <span><em>05.</em> <?php echo JText::_('COM_JEPROSHOP_PAYMENT_LABEL'); ?></span>
        </li>
    </ul>
    <!-- /Steps -->
<?php } ?>