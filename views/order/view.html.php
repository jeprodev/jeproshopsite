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

class JeproshopOrderViewOrder extends JViewLegacy
{
    public $step;

    public $context = null;

    public $current_step = 'summary';

    public $nbProducts;

    protected $errors = array();

    public function renderDetails($tpl = null){
        $app = JFactory::getApplication();
        $order_id = $app->input->get('order_id');

        if(!isset($this->context) || $this->context == null){
            $this->context = JeproshopContext::getContext();
        }

        /*global $orderTotal;

        $this->step = (int)($app->input->get('step'));
        if (!$this->nbProducts) {
            $this->step = -1;
        }

        if ($this->nbProducts) {
            $virtual_cart = $this->context->cart->isVirtualCart();
            $this->assignRef('virtual_cart', $virtual_cart);
        }


        if (!$app->input->get('multi-shipping'))
            $this->context->cart->setNoMultiShipping();

        // If some products have disappear
        if (!$this->context->cart->checkQuantities()){
            $this->step = 0;
            $this->context->controller->has_errors = true;
            JEroor::raiseError(500, JText::_('COM_JEPROSHOP_AN_ITEM_IN_YOUR_CART_IS_NO_LONGER_AVAILABLE_IN_THIS_QUANTITY_YOU_CAN_NOT_PROCEED_WITH_YOUR_ORDER_UNTIL_THE_QUANTITY_IS_ADJUSTED_MESSAGE'));
        }

        // Check minimal amount
        $currency = JeproshopCurrencyModelCurrency::getCurrency((int)$this->context->cart->currency_id);

        $orderTotal = $this->context->cart->getOrderTotal();
        $minimal_purchase = JeproshopTools::convertPrice((float)JeproshopSettingModelSetting::getValue('minimum_purchase'), $currency);
        if ($this->context->cart->getOrderTotal(false, JeproshopCartModelCart::ONLY_PRODUCTS) < $minimal_purchase && (isset($this->current_step) && $this->current_step != 'summary')){
            $this->current_step = 0;
            $this->errors[] = sprintf(
                Tools::displayError('A minimum purchase total of %1s (tax excl.) is required in order to validate your order, current purchase total is %2s (tax excl.).'),
                Tools::displayPrice($minimal_purchase, $currency), Tools::displayPrice($this->context->cart->getOrderTotal(false, JeproshopCartModelCart::ONLY_PRODUCTS), $currency)
            );
        }
        if (!$this->context->customer->isLogged(true) && in_array($this->current_step, array(1, 2, 3))) {
            $back_url = $this->context->controller->getPageLink('order', true, (int)$this->context->language->lang_id,'&step=' . $this->current_step . '&multi_shipping=' . (int)$app->input->get('multi_shipping'));
            $params = array('multi_shipping' => (int)Tools::getValue('multi_shipping'), 'display_guest_checkout' => (int)Configuration::get('PS_GUEST_CHECKOUT_ENABLED'), 'back' => $back_url);
            Tools::redirect($this->context->controller->getPageLink('authentication', true, (int)$this->context->language->lang_id, $params));
        }

        if ($app->input->get('multi_shipping') == 1){
            $multi_shipping =  true;
        } else{
            $multi_shipping =  false;
        }

        $this->assignRef('multi_shipping', $multi_shipping);
        if ($this->context->customer->customer_id){
            $address_list = $this->context->customer->getAddresses($this->context->language->lang_id);
        }else{
            $address_list = array();
        }
        $this->assignRef('address_list', $address_list);



        if ($app->input->get('use_ajax') && $app->input->get('method') == 'update_extra_carrier'){
            // Change virtually the currents delivery options
            $delivery_option = $this->context->cart->getDeliveryOption();
            $delivery_option[(int)$app->input->get('address_id')] = $app->input->get('delivery_option_id');
            $this->context->cart->setDeliveryOption($delivery_option);
            $this->context->cart->save();
            $return = array(
                'content' => Hook::exec(
                        'displayCarrierList',
                        array(
                            'address' => new JeproshopAddressModelAddress((int)$app->input->get('address_id'))
                        )
                    )
            );
            die(Tools::jsonEncode($return));
        }

        if ($this->nbProducts)
            $this->assignRef('virtual_cart', $this->context->cart->isVirtualCart());

        if (!$app->input->get('multi-shipping'))
            $this->context->cart->setNoMultiShipping();
*/
        // 4 steps to the order
        switch ((int)$this->step){
            case -1;
                $this->assignRef('empty', 1);
                $this->setLayout('shopping-_cart');
                break;
            case 1:
                $this->assignAddress();
                $this->processAddressFormat();
                if ($app->input->get('multi_shipping') == 1){
                    $this->assignSummaryInformations();
                    $this->assignRef('product_list', $this->context->cart->getProducts());
                    $this->setLayout('order_address_multi_shipping');
                }else
                    $this->setLayout('order_address');
                break;
            case 2:
                if ($app->input->get('process_address')){ $this->processAddress(); }
                $this->autoStep();
                $this->context->controller->assignCarrier();
                $this->setLayout('order_carrier');
                break;
            case 3:
                // Check that the conditions (so active) were accepted by the customer
                $cgv = $app->input->get('cgv') || $this->context->cookie->check_cgv;
                if (JeproshopSettingModelSetting::getValue('conditions') && (!JeproshopTools::isBool($cgv) || $cgv == false))
                    $app->redirect('index.php?option=com_jeproshop&view=order&current_step=address');
                JeproshopContext::getContext()->cookie->check_cgv = true;

                // Check the delivery option is set
                if (!$this->context->cart->isVirtualCart()){
                    if (!Tools::getValue('delivery_option') && !$app->input->get('carrier_id') && !$this->context->cart->delivery_option && !$this->context->cart->carrier_id){
                       $app->redirect('index.php?option=com_jeproshop&view=order&current_step=address');
                    }elseif (!$app->input->get('carrier_id') && !$this->context->cart->carrier_id){
                        $deliveries_options = $app->input->get('delivery_option');
                        if (!$deliveries_options)
                            $deliveries_options = $this->context->cart->delivery_option;

                        foreach ($deliveries_options as $delivery_option)
                            if (empty($delivery_option))
                                $app->redirect('index.php?option=com_jeproshop&view=order&current_step=address');
                    }
                }

                $this->autoStep();

                // Bypass payment step if total is 0
                $order_id = $this->context->controller->checkFreeOrder();
                if ($order_id){
                    if ($this->context->customer->is_guest){
                        $order = new JeproshopOrderModelOrder((int)$order_id);
                        $email = $this->context->customer->email;
                        $this->context->customer->mylogout(); // If guest we clear the cookie for security reason
                        $app->redirect('index.php?option=com_jeproshop&view=guest_tracking&order_id='.urlencode($order->reference).'&email='.urlencode($email));
                    }
                    else
                        $app->redirect('index.php?option=com_jeproshop&view=history');
                }
                $this->assignPayment();
                // assign some informations to display cart
                $this->assignSummaryInformations();
                $this->setLayout('order_payment');
                break;
            default:
                $this->assignSummaryInformations();
                $this->setLayout('shopping_cart');
                break;
        }

        $this->assignRef('currency_sign', $this->context->currency->sign);
        $this->assignRef('currency_rate', $this->context->currency->conversion_rate);
        $this->assignRef('currency_format', $this->context->currency->format);
        $this->assignRef('currency_blank', $this->context->currency->blank);


        parent::display($tpl);
    }

    private function orderDetails(){
        if(!$this->context){ $this->context = JeproshopContext::getContext(); }
        $this->context->customer->customer_id = 2;
        $app = JFactory::getApplication();
        $this->initOrderDetails();
        $order_id = $app->input->get('order_id');
        if (!($order_id ) || !JeproshopTools::isUnsignedInt($order_id)){
            $this->errors[] = JText::_('COM_JEPROSHOP_ORDER_ID_REQUIRED_MESSAGE');
        }else {
            $order = new JeproshopOrderModelOrder($order_id);
            if (JeproshopTools::isLoadedObject($order, 'order_id') && $order->customer_id == $this->context->customer->customer_id) {
                $order_state_id = (int)($order->getCurrentState());
                $carrier = new JeproshopCarrierModelCarrier((int)($order->carrier_id), (int)($order->lang_id));
                $addressInvoice = new JeproshopAddressModelAddress((int)($order->address_invoice_id));
                $addressDelivery = new JeproshopAddressModelAddress((int)($order->address_delivery_id));

                $inv_adr_fields = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields($addressInvoice->country_id);
                $dlv_adr_fields = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields($addressDelivery->country_id);

                $invoiceAddressFormatValues = JeproshopAddressFormatModelAddressFormat::getFormattedAddressFieldsValues($addressInvoice, $inv_adr_fields);
                $deliveryAddressFormatValues = JeproshopAddressFormatModelAddressFormat::getFormattedAddressFieldsValues($addressDelivery, $dlv_adr_fields);

                if ($order->total_discounts > 0){
                    $this->assignRef('total_old', (float)($order->total_paid - $order->total_discounts));
                }
                $products = $order->getProducts();

                /* DEPRECATED: customizedDatas @since 1.5 */
                $customizedDatas = JeproshopProductModelProduct::getAllCustomizedDatas((int)($order->cart_id));
                JeproshopProductModelProduct::addCustomizationPrice($products, $customizedDatas);

                JeproshopOrderReturnModelOrderReturn::addReturnedQuantity($products, $order->order_id);

                $customer = new JeproshopCustomerModelCustomer($order->customer_id);

                //$this->assignRef('shop_name', strval(JeproshopSettingModelSetting::getValue('PS_SHOP_NAME')));


                    /* DEPRECATED: customizedDatas @since 1.5 */
                //$this->assignRef('reordering_allowed', !(int)(JeproshopSettingModelSetting::getValue('PS_DISALLOW_HISTORY_REORDERING')));
/*
                if ($carrier->url && $order->shipping_number)
                    $this->context->smarty->assign('followup', str_replace('@', $order->shipping_number, $carrier->url));
                $this->context->smarty->assign('HOOK_ORDERDETAILDISPLAYED', Hook::exec('displayOrderDetail', array('order' => $order)));
                Hook::exec('actionOrderDetail', array('carrier' => $carrier, 'order' => $order)); */

                unset($carrier, $addressInvoice, $addressDelivery);
            }
            else
                $this->errors[] = JText::_('COM_JEPROSHOP_THIS_ORDER_CAN_NOT_BE_FOUND_MESSAGE');
            unset($order);
        }
    }

    public function initOrderDetails(){
        $this->context->controller->init();
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
        //$this->initContent();
        $app = JFactory::getApplication();
        $order_id = $app->input->get('order_id');
        if (!($order_id) || !JeproshopTools::isUnsignedInt($order_id)){
            $this->errors[] = Tools::displayError('Order ID required');
        }else {
            $order = new JeproshopOrderModelOrder($order_id);
            if (JeproshopTools::isLoadedObject($order, 'order_id') && $order->customer_id == $this->context->customer->customer_id){
                $order_status_id = (int)($order->getCurrentState());
                $carrier = new JeproshopCarrierModelCarrier((int)($order->carrier_id), (int)($order->lang_id));
                $invoiceAddress = new JeproshopAddressModelAddress((int)($order->address_invoice_id));
                $deliveryAddress = new JeproshopAddressModelAddress((int)($order->address_delivery_id));

                $invoice_address_fields = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields($invoiceAddress->country_id);
                $delivery_address_fields = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields($deliveryAddress->country_id);

                $invoiceAddressFormatValues = JeproshopAddressFormatModelAddressFormat::getFormattedAddressFieldsValues($invoiceAddress, $invoice_address_fields);
                $deliveryAddressFormatValues = JeproshopAddressFormatModelAddressFormat::getFormattedAddressFieldsValues($deliveryAddress, $delivery_address_fields);

                if ($order->total_discounts > 0){
                    $oldTotal = (float)($order->total_paid - $order->total_discounts);
                    $this->assignRef('old_total', $oldTotal);
                }
                $products = $order->getProducts();

                /* DEPRECATED: customizedDatas @since 1.5 */
                $customizedDatas = JeproshopProductModelProduct::getAllCustomizedDatas((int)($order->cart_id));
                JeproshopProductModelProduct::addCustomizationPrice($products, $customizedDatas);

                JeproshopOrderReturnModelOrderReturn::addReturnedQuantity($products, $order->order_id);

                $customer = new JeproshopCustomerModelCustomer($order->customer_id);

                $this->assignRef('order', $order);
                $isReturnable = (int)$order->isReturnable();
                $this->assignRef('return_allowed', $isReturnable);
                $this->assignRef('currency', new JeproshopCurrencyModelCurrency($order->currency_id));
                $order_status_id = (int)$order_status_id;
                $this->assignRef('order_status_id', $order_status_id);
                $invoiceAllowed = (int)JeproshopSettingModelSetting::getValue('invoice_allowed');
                $this->assignRef('invoice_allowed', $invoiceAllowed);
                $invoice = (JeproshopOrderStatusModelOrderStatus::invoiceAvailable($order_status_id) && count($order->getInvoicesCollection()));
                $this->assignRef('invoice', $invoice);
                $this->assignRef('order_history', $order->getHistory($this->context->language->lang_id, false, true));
                $this->assignRef('products', $products);
                $this->assignRef('discounts', $order->getCartRules());
                $this->assignRef('carrier', $carrier);
                $this->assignRef('invoice_address', $invoiceAddress);
                $this->assignRef('invoice_status', (JeproshopTools::isLoadedObject($invoiceAddress, 'address_id') && $invoiceAddress->state_id) ? new JeproshopStatusModelStatus($invoiceAddress->state_id) : false);
                $this->assignRef('delivery_address', $deliveryAddress);
                $this->assignRef('invoice_address_fields', $invoice_address_fields);
                $this->assignRef('delivery_address_fields', $delivery_address_fields);
                $this->assignRef('invoice_address_format_values', $invoiceAddressFormatValues);
                $this->assignRef('delivery_address_format_values', $deliveryAddressFormatValues);
                $this->assignRef('delivery_status', (JeproshopTools::isLoadedObject($deliveryAddress, 'address_id') && $deliveryAddress->state_id) ? new JeproshopStatusModelStatus($deliveryAddress->state_id) : false);

                $this->assignRef('messages', JeproshopCustomerMessageModelCustomerMessage::getMessagesByOrderId((int)($order->order_id), false));
                $files = JeproshopProductModelProduct::CUSTOMIZE_FILE;
                $this->assignRef('CUSTOMIZE_FILE', $files);
                $text_fields = JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD;
                $this->assignRef('CUSTOMIZE_TEXT_FIELD', $text_fields);
                //$this->assignRef('isRecyclable', JeproshopSettingModelSetting::getValue('PS_RECYCLABLE_PACK'));
                $this->assignRef('use_tax', JeproshopSettingModelSetting::getValue('use_tax'));
                $group_use_tax = (JeproshopGroupModelGroup::getPriceDisplayMethod($customer->default_group_id) == COM_JEPROSHOP_TAX_INCLUDED);
                $this->assignRef('group_use_tax', $group_use_tax);
                $this->assignRef('display_price', JeproshopSettingModelSetting::getValue('display_price'));
                /* DEPRECATED: customizedDatas @since 1.5 */
                $this->assignRef('customizedDatas', $customizedDatas);
                $reordering_allowed = (int)(JeproshopSettingModelSetting::getValue('enable_history_reordering'));
                $this->assignRef('reordering_allowed', $reordering_allowed);
                /*
                $this->context->smarty->assign(array(
                    'deliveryState' => (Validate::isLoadedObject($addressDelivery) && $addressDelivery->state_id) ? new State($addressDelivery->id_state) : false,
                    'is_guest' => false,
                    'messages' => CustomerMessage::getMessagesByOrderId((int)($order->id), false),
                    'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
                    'CUSTOMIZE_TEXT_FIELD' => Product::CUSTOMIZE_TEXT_FIELD,
                    'isRecyclable' => Configuration::get('PS_RECYCLABLE_PACK'),
                    'use_tax' => Configuration::get('PS_TAX'),
                    'group_use_tax' => (Group::getPriceDisplayMethod($customer->default_group_id) == PS_TAX_INC),
                    /* DEPRECATED: customizedDatas @since 1.5 * /
                    'customizedDatas' => $customizedDatas,
                    /* DEPRECATED: customizedDatas @since 1.5 * /

                )); */

                if ($carrier->url && $order->shipping_number){
                    $this->assignRef('followup', str_replace('@', $order->shipping_number, $carrier->url));
                }
 /*               $this->context->smarty->assign('HOOK_ORDERDETAILDISPLAYED', Hook::exec('displayOrderDetail', array('order' => $order)));
                Hook::exec('actionOrderDetail', array('carrier' => $carrier, 'order' => $order)); */

                unset($carrier, $addressInvoice, $addressDelivery);
            }
            else
                $this->errors[] = Tools::displayError('This order cannot be found.');
            unset($order);
        }

        //$this->setTemplate(_PS_THEME_DIR_.'order-detail.tpl');
    }

    private function initContent(){
        $order_process_type = (bool)JeproshopSettingModelSetting::getValue('order_process_type');
        $this->assignRef('order_process_type', $order_process_type);
        $isGuest = false;
        $this->assignRef('is_guest', $isGuest);
        $catalog_mode = (bool)(JeproshopSettingModelSetting::getValue('catalog_mode') || !JeproshopGroupModelGroup::getCurrent()->show_prices);
        $this->assignRef('catalog_mode', $catalog_mode);
    }

    /**
     * Order process controller
     */
    public function autoStep(){
        $app = JFactory::getApplication();
        if (($this->current_step == 'address' || $this->current_step == 'delivery' || $this->current_step == 'payment') && (!$this->context->cart->address_delivery_id || !$this->context->cart->address_invoice_id))
            $app->redirect('index.php?option=com_jeproshop&view=order&step=summary');

        if (($this->current_step == 'delivery' || $this->current_step == 'payment') && !$this->context->cart->isVirtualCart()){
            $redirect = false;
            if (count($this->context->cart->getDeliveryOptionList()) == 0)
                $redirect = true;

            if (!$this->context->cart->isMultiAddressDelivery())
                foreach ($this->context->cart->getProducts() as $product)
                    if (!in_array($this->context->cart->carrier_id, Carrier::getAvailableCarrierList(new Product($product['id_product']), null, $this->context->cart->id_address_delivery)))
                    {
                        $redirect = true;
                        break;
                    }

            if ($redirect)
                Tools::redirect('index.php?option=com_jeproshop&view=order&step=address');
        }

        $delivery = new JeproshopAddressModelAddress((int)$this->context->cart->address_delivery_id);
        $invoice = new JeproshopAddressModelAddress((int)$this->context->cart->address_invoice_id);

        if ($delivery->deleted || $invoice->deleted)
        {
            if ($delivery->deleted)
                unset($this->context->cart->address_delivery_id);
            if ($invoice->deleted)
                unset($this->context->cart->address_invoice_id);
            $app->redirect('index.php?option=com_jeproshop&view=order&step=summary');
        }
    }

    public function assignSummaryInformations(){
        $context = JeproshopContext::getContext();
        $summary = $context->cart->getSummaryDetails();
        
        $customizedDatas = JeproshopProductModelProduct::getAllCustomizedDatas($context->cart->cart_id);

        // override customization tax rate with real tax (tax rules)
        if ($customizedDatas) {
            foreach ($summary['products'] as &$productUpdate){
                $productId = (int)(isset($productUpdate->product_id) ? $productUpdate->product_id : $productUpdate->product_id);
                $productAttributeId = (int)(isset($productUpdate->product_attribute_id) ? $productUpdate->product_attribute_id : $productUpdate->product_attribute_id);

                if (isset($customizedDatas[$productId][$productAttributeId]))
                    $productUpdate->tax_rate = JeproshopTaxModelTax::getProductTaxRate($productId, $context->cart->{JeproshopSettingModelSetting::getValue('tax_address_type')});
            }

            JeproshopProductModelProduct::addCustomizationPrice($summary->products, $customizedDatas);
        }

        $cart_product_context = JeproshopContext::getContext()->cloneContext();
        foreach ($summary['products'] as $key => $product){
            $product->quantity = $product->cart_quantity;// for compatibility with 1.2 themes

            if ($cart_product_context->shop->shop_id != $product->shop_id)
                $cart_product_context->shop = new JeproshopModelShop((int)$product->shop_id);
            $null = null;
            $product->price_without_specific_price = JeproshopProductModelProduct::getStaticPrice(
                $product->product_id, !JeproshopProductModelProduct::getTaxCalculationMethod(), $product->product_attribute_id,
                2, null, false, false, 1, false, null, null, null, $null, true, true, $cart_product_context);

            if (JeproshopProductModelProduct::getTaxCalculationMethod()){
                $product->is_discounted = $product->price_without_specific_price != $product->price;
            }else{
                $product->is_discounted = $product->price_without_specific_price != $product->price_wt;
            }
        }

        // Get available cart rules and unset the cart rules already in the cart
        $available_cart_rules = JeproshopCartRuleModelCartRule::getCustomerCartRules($this->context->language->lang_id, (isset($this->context->customer->customer_id) ? $this->context->customer->customer_id : 0), true, true, true, $this->context->cart);
        $cart_cart_rules = $context->cart->getCartRules();
        foreach ($available_cart_rules as $key => $available_cart_rule){
            if (!$available_cart_rule->high_light || strpos($available_cart_rule->code, 'BO_ORDER_') === 0){
                unset($available_cart_rules[$key]);
                continue;
            }
            foreach ($cart_cart_rules as $cart_cart_rule){
                if ($available_cart_rule->cart_rule_id == $cart_cart_rule->cart_rule_id){
                    unset($available_cart_rules[$key]);
                    continue 2;
                }
            }
        }

        $show_option_allow_separate_package = (!$this->context->cart->isAllProductsInStock(true) && JeproshopSettingModelSetting::getValue('ship_when_available'));

        $this->assign($summary);
        //$this->assign('token_cart', Tools::getToken(false));
        $this->assign('is_logged', $this->context->controller->isLogged);
        $this->assign('is_virtual_cart', $this->context->cart->isVirtualCart());
        $this->assign('product_number', $this->context->cart->numberOfProducts());
        $this->assign('voucher_allowed', JeproshopCartRuleModelCartRule::isFeaturePublished());
        $this->assign('shipping_cost', $this->context->cart->getOrderTotal(true, JeproshopCartModelCart::ONLY_SHIPPING));
        $this->assign('shipping_cost_tax_excluded', $this->context->cart->getOrderTotal(false, JeproshopCartModelCart::ONLY_SHIPPING));
        $this->assign('customizedDatas', $customizedDatas);
        $this->assign('CUSTOMIZE_FILE', JeproshopProductModelProduct::CUSTOMIZE_FILE);
        $this->assign('CUSTOMIZE_TEXT_FIELD', JeproshopProductModelProduct::CUSTOMIZE_TEXT_FIELD);
        $this->assign('last_product_added', $this->context->cart->getLastProduct());
        $this->assign('display_vouchers', $available_cart_rules);
        $this->assign('currency_sign', $this->context->currency->sign);
        $this->assign('currency_rate', $this->context->currency->conversion_rate);
        $this->assign('currency_format', $this->context->currency->format);
        $this->assign('currency_blank', $this->context->currency->blank);
        $this->assign('show_option_allow_separate_package', $show_option_allow_separate_package);
        $this->assign('small_size', JeproshopImageModelImage::getSize(JeproshopImageTypeModelImageType::getFormatName('small')));

/*
        $this->context->smarty->assign(array(
            'HOOK_SHOPPING_CART' => Hook::exec('displayShoppingCartFooter', $summary),
            'HOOK_SHOPPING_CART_EXTRA' => Hook::exec('displayShoppingCart', $summary)
        ));*/
    }

    /**
     * Address step
     */
    protected function _assignAddress()
    {
        parent::_assignAddress();

        if (Tools::getValue('multi-shipping'))
            $this->context->cart->autosetProductAddress();

        $this->context->smarty->assign('cart', $this->context->cart);

    }

    /**
     * Carrier step
     */
    protected function _assignCarrier()
    {
        if (!isset($this->context->customer->id))
            die(Tools::displayError('Fatal error: No customer'));
        // Assign carrier
        parent::_assignCarrier();
        // Assign wrapping and TOS
        $this->_assignWrappingAndTOS();

        $this->context->smarty->assign(
            array(
                'is_guest' => (isset($this->context->customer->is_guest) ? $this->context->customer->is_guest : 0)
            ));
    }

    /**
     * Payment step
     */
    protected function _assignPayment()
    {
        global $orderTotal;

        // Redirect instead of displaying payment modules if any module are grefted on
        Hook::exec('displayBeforePayment', array('module' => 'order.php?step=3'));

        /* We may need to display an order summary */
        $this->context->smarty->assign($this->context->cart->getSummaryDetails());
        $this->context->smarty->assign(array(
            'total_price' => (float)($orderTotal),
            'taxes_enabled' => (int)(Configuration::get('PS_TAX'))
        ));
        $this->context->cart->checkedTOS = '1';

        parent::_assignPayment();
    }

}