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

class JeproshopOrderController extends JeproshopController
{


    public function display($cache = FALSE, $urlParams = FALSE) {
        $view = $this->input->get('view', 'order');
        $layout = $this->input->get('layout', 'order');
        $this->globalInitialization();
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $viewClass->setLayout($layout);
        $viewClass->display();
    }

    public function initialize(){
        $this->globalInitialization();
    }

    /**
     * Check if order is free
     * @return boolean
     * /
    public function checkFreeOrder(){
        $context = JeproshopContext::getContext();
        if ($context->cart->getOrderTotal() <= 0) {
            $order = new JeproshopFreeOrderModelFreeOrder();
            $order->free_order_class = true;
            $order->validateOrder($context->cart->cart_id, Configuration::get('PS_OS_PAYMENT'), 0, Tools::displayError('Free order', false), null, array(), null, false, $context->cart->secure_key);
            return (int)JeproshopOrderModelOrder::getOrderByCartId($context->cart->cart_id);
        }
        return false;
    }


    public function assignCarrier(){
        $view = JFactory::getDBO();
        $context = JeproshopContext::getContext();
        $address = new JeproshopAddressModelAddress($context->cart->address_delivery_id);
        $zone_id = JeproshopAddressModelAddress::getZoneIdByAddressId($address->address_id);
        $carriers = $context->cart->simulateCarriersOutput(null, true);
        $checked = $context->cart->simulateCarrierSelectedOutput(false);
        $delivery_option_list = $context->cart->getDeliveryOptionList();
        $delivery_option = $context->cart->getDeliveryOption(null, false);
        $this->setDefaultCarrierSelection($delivery_option_list);

        $view->assignRef('address_collection', $context->cart->getAddressCollection());
        $view->assignRef('delivery_option_list', $delivery_option_list);
        $view->assignRef('carriers', $carriers);
        $view->assignRef('checked',  $checked);
        $view->assignRef('delivery_option', $delivery_option);


        $vars = array(
            /*'HOOK_BEFORECARRIER' => Hook::exec('displayBeforeCarrier', array(
                    'carriers' => $carriers,
                    'checked' => $checked,
                    'delivery_option_list' => $delivery_option_list,
                    'delivery_option' => $delivery_option
                ))*  /
        );

        JeproshopCartModelCart::addExtraCarriers($vars);

        $view->assignRef('extra_carriers', $vars);
    }

    /**
     * Decides what the default carrier is and update the cart with it
     *
     * @todo this function must be modified - id_carrier is now delivery_option
     *
     * @param array $carriers
     *
     * @deprecated since 1.5.0
     *
     * @return number the id of the default carrier
     * /
    protected function setDefaultCarrierSelection($carriers) {
        $context = JeproshopContext::getContext();
        if (!$context->cart->getDeliveryOption(null, true))
            $context->cart->setDeliveryOption($context->cart->getDeliveryOption());
    }*/

    public function globalInitialization(){
        $app = JFactory::getApplication();
        $view = $app->input->get('view');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $context = JeproshopContext::getContext();
        $this->isLogged = (bool)($context->customer->customer_id && JeproshopCustomerModelCustomer::customerIdExistsStatic((int)$context->cookie->customer_id));

        parent::initialize();

        /* Disable some cache related bugs on the cart/order */
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        if (!$context->customer->isLogged(true) && $this->useMobileTheme() && $app->input->get('step'))
            $app->redirect(JRoute::_('index.php?option=com_jeproshop&view=authentication&lang_id=' . $context->language->lang_id, true, 1));

        // Redirect to the good order process
        $task = $app->input->get('task', '');
        $order_process_type = JeproshopSettingModelSetting::getValue('order_process_type');
        if ($order_process_type == 'standard' && ($task != '' || $task != 'display')) {
            //$app->redirect('index.php?option=com_jeproshop&view=order');
        }

        if ($order_process_type == 'page_checkout' && $task != 'opc') {
            $step = $app->input->get('step');
            if (isset($step) && $step == 3) {
                $app->redirect('index.php?option=com_jeproshop&view=order&task=opc&isPaymentStep=true');
            }
            $app->redirect('index.php?option=com_jeproshop&view=order&task=opc');
        }

         $catalog_mode = JeproshopSettingModelSetting::getValue('catalog_mode');
         if ($catalog_mode) {
             $this->has_errors = true;
             JError::raiseError(500, JText::_('COM_JEPROSHOP_THIS_STORE_DOES_NOT_ACCEPT_NEW_ORDER_MESSAGE'));
         }
         $order_id = (int)$app->input->get('order_id');
        $currentTask = $app->input->get('task');
         if (($currentTask == 'submitReorder') && $order_id) {
             $oldCart = new JeproshopCartModelCart(JeproshopOrderModelOrder::getStaticCartId($order_id, $context->customer->customer_id));
             $duplication = $oldCart->duplicate();
             if (!$duplication || !JeproshopTools::isLoadedObject($duplication->cart, 'cart_id')) {
                 $this->has_errors = true;
                 Tools::displayError('Sorry. We cannot renew your order.');
             } else if (!$duplication->success) {
                 $this->has_errors = true;
                 Tools::displayError('Some items are no longer available, and we are unable to renew your order.');
             } else {
                 $context->cookie->cart_id = $duplication->cart->cart_id;
                 $context->cookie->write();
                 if ($order_process_type == 'page_checkout')
                     $app->redirect('index.php?option=com_jeproshop&view=order&task=opc');
                 $app->redirect('index.php?option=com_jeproshop&view=order');
             }
         }
        $viewClass->assignRef('order_process_type', $order_process_type);
        $nbProducts = $context->cart->numberOfProducts();
        if($nbProducts){
            if (JeproshopCartRuleModelCartRule::isFeaturePublished()){
                if (Tools::isSubmit('submitAddDiscount')){
                    if (!($code = trim(Tools::getValue('discount_name')))) {
                        $this->has_errors = true;
                        JError::raiseError('You must enter a voucher code.');
                    }elseif (!Validate::isCleanHtml($code)) {
                        $this->has_errors = true;
                        JEroor::raiseError('The voucher code is invalid.');
                    }else{
                        $cartRule = new JeproshopCartRuleModelCartRule(JeproshopCartRuleModelCartRule::getIdByCode($code));
                        if ($cartRule && JeproshopTools::isLoadedObject($cartRule, 'cart_rule_id')){
                            if ($error = $cartRule->checkValidity($context, false, true)) {
                                $this->has_errors = true;
                                JError::raiseError(500, $error);
                            }else{
                                $context->cart->addCartRule($cartRule->cart_rule_id);
                                if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1)
                                    Tools::redirect('index.php?controller=order-opc&addingCartRule=1');
                                Tools::redirect('index.php?controller=order&task=adding_cart_rule');
                            }
                        }else {
                            $this->has_errors = true;
                            Tools::displayError('This voucher does not exists.');
                        }
                    }
                    $discountName = JeproshopTools::safeOutput($code);
                    $viewClass->assignRef('discount_name', $discountName);
                } elseif (($cart_rule_id = (int)Tools::getValue('delete_discount')) && JeproshopTools::isUnsignedId($cart_rule_id)){
                    $context->cart->removeCartRule($cart_rule_id);
                    $app->redirect('index.php?option=com_jeproshop&view=order&task=opc');
                }
            }
            /* Is there only virtual product in cart */
            if ($isVirtualCart = $context->cart->isVirtualCart())
                $this->setNoCarrier();
        }
        //$viewClass->assignRef('back', JTools::safeOutput(Tools::getValue('back')));*/
    }

    /**
     * Set id_carrier to 0 (no shipping price)
     */
    protected function setNoCarrier(){
        $context = JeproshopContext::getContext();
        $context->cart->setDeliveryOption(null);
        $context->cart->update();
    }
}