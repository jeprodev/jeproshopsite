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

class JeproshopCartController extends JeproshopController {
    protected $context = null;

    protected $product_id;
    protected $product_attribute_id;
    protected $address_delivery_id;
     protected $customization_id;
    protected $quantity;

    protected $ajax_refresh = false;

    public $ssl = true;

    public function initialize(){
        parent::initialize();

        // Send noindex to avoid ghost carts by bots
        header("X-Robots-Tag: noindex, nofollow", true); if($this->has_errors){ echo 'mon cul'; }

        $app = JFactory::getApplication();
        $this->product_id = (int)$app->input->get('product_id', null);
        $this->product_id = (int)$app->input->get('product_attribute_id', 0);
        $this->customization_id = (int)$app->input->get('customization_id');
        $this->quantity = (int)$app->input->get('quantity', 1);
        $this->address_delivery_id = (int)$app->input->get('address_delivery_id');
    }

    public function product(){
        // Update the cart ONLY if $this->cookies are available, in order to avoid ghost carts created by bots
        if(!$this->context){ $this->context = JeproshopContext::getContext(); }

        if ($this->context->cookie->exists() && !$this->has_errors && !($this->context->customer->isLogged() && !$this->isTokenValid())){
            // Send noindex to avoid ghost carts by bots
            header("X-Robots-Tag: noindex, nofollow", true);

            if(!$this->isInitialized()){ $this->initialize(); }
            $app = JFactory::getApplication();
            $product_id = $app->input->get('product_id', null);
            $customization_id = (int)$app->input->get('customization_id', null);
            $address_delivery_id = (int)$app->input->get('address_delivery_id', null);
            $product_attribute_id = $app->input->get('product_attribute_id', null);
            $mode = (($app->input->get('task') == 'update') && $product_id) ? 'update' : 'add';

            if ($app->input->get('quantity') == 0){
                $this->has_errors = true;
                echo JText::_('COM_JEPROSHOP_NULL_QUANTITY_MESSAGE') . ' ' .  !$app->input->get('use_ajax');
            }elseif (!$product_id){
                $this->has_errors = true;
                echo JText::_('COM_JEPROSHOP_PRODUCT_ID_NOT_FOUND_MESSAGE') . ' ' . !$app->input->get('use_ajax');
            }

            $product = new JeproshopProductModelProduct($product_id, true, $this->context->language->lang_id);
            if (!$product->product_id || !$product->published){
                $this->has_errors = true;
                echo JText::_('COM_JEPROSHOP_THIS_PRODUCT_IS_NO_LONGER_AVAILABLE_MESSAGE.') . ' ' . !$app->input->get('use_ajax');
                exit();
            }

            $quantity = abs($app->input->get('quantity', 1));
            $qty_to_check = $quantity;
            $cart_products = $this->context->cart->getProducts();

            if (is_array($cart_products)){
                foreach ($cart_products as $cart_product) {
                    if ((!isset($this->product_attribute_id) || $cart_product->product_attribute_id == $product_attribute_id) &&
                        (isset($product_id) && $cart_product->product_id == $product_id)) {
                        $qty_to_check = $cart_product->cart_quantity;

                        if ($app->input->get('op', 'up') == 'down'){
                            $qty_to_check -= $quantity;
                        }else{
                            $qty_to_check += $quantity;
                        }
                        break;
                    }
                }
            }

           // Check product quantity availability
            if ($product_attribute_id) {
                if (!JeproshopProductModelProduct::isAvailableWhenOutOfStock($product->out_of_stock) && !JeproshopAttributeModelAttribute::checkAttributeQty($product_attribute_id, $qty_to_check)){
                    $this->has_errors = true;
                    echo JText::_('COM_JEPROSHOP_THERE_IS_NOT_ENOUGH_PRODUCT_IN_STOCK_MESSAGE') . ' ' . __LINE__  . !$app->input->get('use_ajax');
                }
            } elseif ($product->hasAttributes()){
                $minimumQuantity = ($product->out_of_stock == 2) ? !JeproshopSettingModelSetting::getValue('order_out_of_stock') : !$product->out_of_stock;
                $product_attribute_id = JeproshopProductModelProduct::getDefaultAttribute($product->product_id, $minimumQuantity);
                // @todo do something better than a redirect admin !!
                if (!$product_attribute_id){
                    $app->redirect($this->getProductLink($product));
                }elseif (!JeproshopProductModelProduct::isAvailableWhenOutOfStock($product->out_of_stock) && !JeproshopAttributeModelAttribute::checkAttributeQty($product_attribute_id, $qty_to_check)){
                    $this->has_errors = true;
                    echo JText::_('COM_JEPROSHOP_THERE_IS_NOT_ENOUGH_PRODUCT_IN_STOCK_MESSAGE') . ' ' . __LINE__ .  !$app->input->get('use_ajax');
                }
            } elseif (!$product->checkQuantity($qty_to_check)){
                $this->has_errors = true;
                echo JText::_('COM_JEPROSHOP_THERE_IS_NOT_ENOUGH_PRODUCT_IN_STOCK_MESSAGE') . ' '. __LINE__ . !$app->input->get('use_ajax');
            }

            // If no errors, process product addition
            if ($mode == 'add'){
                // Add cart if no cart found
                if (!$this->context->cart->cart_id){
                    if (JeproshopContext::getContext()->cookie->guest_id){
                        $guest = new JeproshopGuestModelGuest(JeproshopContext::getContext()->cookie->guest_id);
                        $this->context->cart->mobile_theme = $guest->mobile_theme;
                    }
                    $this->context->cart->add();
                    if ($this->context->cart->cart_id){
                        $this->context->cookie->cart_id = (int)$this->context->cart->cart_id;
                    }
                }

                // Check customizable fields
                if (!$product->hasAllRequiredCustomizableFields() && !$customization_id){
                   // $this->errors[] = Tools::displayError('Please fill in all of the required fields, and then save your customizations.', !Tools::getValue('ajax'));
                }

                if (!$this->has_errors){
                    $cart_rules = $this->context->cart->getCartRules();
                    $update_quantity = $this->context->cart->updateQuantity($quantity, $product_id, $product_attribute_id, $customization_id, $app->input->get('op', 'up'), $address_delivery_id);
                    if ($update_quantity < 0){
                        // If product has attribute, minimal quantity is set with minimal quantity of attribute
                        $minimal_quantity = ($product_attribute_id) ? JeproshopAttributeModelAttribute::getAttributeMinimalQty($product_attribute_id) : $product->minimal_quantity;
                        $this->has_errors = true;
                        sprintf(Tools::displayError('You must add %d minimum quantity', !$app->input->get('use_ajax')), $minimal_quantity);
                    }elseif (!$update_quantity){
                        $this->has_errors= true;
                        echo JText::_('COM_JEPROSHOP_YOU_ALREADY_HAVE_THE_MAXIMUM_AVAILABLE_FOR_THIS_PRODUCT_MESSAGE') . ' ' . !$app->input->get('use_ajax');
                    }elseif ((int)$app->input->get('allow_refresh')){
                        // If the cart rules has changed, we need to refresh the whole cart
                        $cart_rules2 = $this->context->cart->getCartRules();
                        if (count($cart_rules2) != count($cart_rules)){
                            $this->ajax_refresh = true;
                        }else {
                            $rule_list = array();
                            foreach ($cart_rules2 as $rule){
                                $rule_list[] = $rule->cart_rule_id;
                            }
                            foreach ($cart_rules as $rule){
                                if (!in_array($rule->cart_rule_id, $rule_list)) {
                                    $this->ajax_refresh = true;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            $removed = JeproshopCartRuleModelCartRule::autoRemoveFromCart();
            JeproshopCartRuleModelCartRule::autoAddToCart();
            if (count($removed) && (int)$app->input->get('allow_refresh')){ $this->ajax_refresh = true; }  echo 'bonjour'; exit();
        }elseif(!$this->context->cookie->exists()){
            echo 'bonjour cookkie'; exit();
        }elseif($this->has_errors ){
        }elseif(($this->context->customer->isLogged() && !$this->isTokenValid())){}
    }

    function isTokenValid(){
        return false;
    }

    /**
     * This is not a public page, so the canonical redirection is disabled
     * @param string $canonicalURL
     */
    public function canonicalRedirection($canonicalURL = ''){ }

    /**
     * Initialize cart controller
     * @see FrontController::init()
     */
    public function init(){
        if(!$this->context || ($this->context == null)){ $this->context = JeproshopContext::getContext(); }
        parent::init();

        // Send noindex to avoid ghost carts by bots
        header("X-Robots-Tag: noindex, nofollow", true);

        $app = JFactory::getApplication();
        // Get page main parameters
        $this->product_id = (int)$app->input->get('product_id', null);
        $this->product_attribute_id = (int)$app->input->get('product_attribute_id', $app->input->get('product_attribute_id'));
        $this->customization_id = (int)$app->input->get('customization_id');
        $this->quantity = abs($app->input->get('quantity', 1));
        $this->address_delivery_id = (int)$app->input->get('address_delivery_id');
        $this->use_ajax = $app->input->get('use_ajax');
    }
}