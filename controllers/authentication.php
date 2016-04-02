<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package     com_jeproshop
 * @link            http://jeprodev.net
 *
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

class JeproshopAuthenticationController extends JeproshopController
{
    /**
     * @var bool create_account
     */
    protected $create_account;

    public function initialize(){
        parent::initialize();

        /*if (!Tools::getIsset('step') && $context->customer->isLogged() && !$this->use_ajax)
            Tools::redirect('index.php?controller='.(($this->authRedirection !== false) ? urlencode($this->authRedirection) : 'my-account'));

        if (Tools::getValue('create_account'))
            $this->create_account = true;*/
    }

    public function create(){
        $app = JFactory::getApplication();
        $formData = JRequest::get('post');
        $document = JFactory::getDocument();
        $email = $formData['email_create'];
        $document->setMimeEncoding('application/json');
        $errors = array();
        $creationData = array("success" =>  true);
        if (!JeproshopTools::isEmail($email) || empty($email)) {
            $creationData['has_errors'] = true;
            $errors[] = JText::_('Invalid email address.');
            $creationData['errors'] = $errors;
        }elseif(JeproshopCustomerModelCustomer::customerExists($email)){
            $creationData['has_errors'] = true;
            $errors[] = JText::_('An account using this email address has already been registered. Please enter a valid password or request a new one. ', false);
            $_POST['email'] = $_POST['email_create'];
            $creationData['errors'] = $errors;
            unset($_POST['email_create']);
        }else{
            $creationData['has_errors'] = false;
            $this->create_account = true;
            $view = $app->input->get('view');
            $viewClass = $this->getView($view, JFactory::getDocument()->getType());
            $viewClass->assign('email_create', JeproshopTools::safeOutput($email));
            //$app->input->set('jform[email]', $email);
        }
        $creationData['success'] = true;
        $creationData['email'] = $formData['email_create'];

        echo json_encode($creationData);
        $app->close();
    }

    /**
     * Process submit on an account
     */
    public function register(){
        $app = JFactory::getApplication();
        $context = JeproshopContext::getContext();
        $view = $app->input->get('view');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $input = JRequest::get('get');
        $jsonData = array("success" =>  true);
        $errors = array();

        $task = $app->input->get('task');
        if (isset($task) && $task == 'register'){
            $emailCreate = 1;
            $viewClass->assign('email_create', 1);
        }
        $isNewCustomer = isset($input['is_new_customer']) ? 1 : 0;
        if (!$isNewCustomer && !JeproshopSettingModelSetting::getValue('enable_guest_checkout')) {
            $this->has_errors = true;
            $errors[] = JText::_('You cannot create a guest account..');
        }


        $guestEmail = isset($input['guest_email']) ? $input['guest_email'] : '';
        $email = isset($input['email']) ? $input['email'] : '';
        if (isset($guestEmail) && $guestEmail) {
            $email = $guestEmail;
        }

        // Checked the user address in case he changed his email address
        if(JeproshopTools::isEmail($email) && !empty($email)){
            if(JeproshopCustomerModelCustomer::customerExists($email)) {
                $this->has_errors = true;
                $errors[] = JText::_('An account using this email address has already been registered.'); //, false);
            }
        }
        // Preparing customer
        $customer = new JeproshopCustomerModelCustomer();
        $lastNameAddress = isset($input['lastname']) ? $input['lastname'] : '';
        $firstNameAddress = isset($input['firstname']) ? $input['firstname'] : '';

        $lastname = isset($input['customer_lastname']) ? $input['customer_lastname'] : $lastNameAddress;
        $firstname = isset($input['customer_firstname']) ? $input['customer_firstname'] : $firstNameAddress;
        $addresses_types = array('address');
        $inputInvoiceAddress = isset($input['invoice_address']) ? $input['invoice_address'] : 0;
        if ((JeproshopSettingModelSetting::getValue('order_process_type') == 'standard') && JeproshopSettingModelSetting::getValue('enable_guest_checkout') && $inputInvoiceAddress) {
            $addresses_types[] = 'invoice_address';
        }

        $error_phone = false;
        $phone = isset($input['phone']) ? $input['phone'] : null;
        $phone_mobile = isset($input['phone_mobile']) ? $input['phone_mobile'] : null;
        if (JeproshopSettingModelSetting::getValue('one_phone_at_least')){
            if ($task == 'submitGuestAccount' || !$isNewCustomer){
                if (!$phone && !$phone_mobile) {
                    $error_phone = true;
                }
            }elseif ((((JeproshopSettingModelSetting::getValue('registration_process_type') != 'account_only') && JeproshopSettingModelSetting::getValue('order_process_type') != 'standard')
                    || (JeproshopSettingModelSetting::getValue('order_process_type') != 'standard' && !$viewClass->email_create)
                    || ((JeproshopSettingModelSetting::getValue('registration_process_type') != 'account_only') && $viewClass->email_create))
                && (!$phone && !$phone_mobile))
                $error_phone = true;
        }

        if ($error_phone) {
            $this->has_errors = true;
            $errors[] = JText::_('You must register at least one phone number.');
        }
        $errors = array_unique(array_merge($errors, $customer->validateController()));

        if ((JeproshopSettingModelSetting::getValue('registration_process_type') == 'account_only') && !$this->use_ajax && !Tools::isSubmit('submitGuestAccount')){
            if (!$this->has_errors){
                if (isset($input['newsletter']) && $input['newsletter']) {
                    $this->processCustomerNewsletter($customer);
                }

                $customer->firstname = JeproshopTools::ucwords($customer->firstname);
                $customer->birthday = (empty($input['year']) ? '' : (int)$input['year'].'-'.(int)$input['month'].'-'.(int)$input['day']);
                if (!JeproshopTools::isBirthDate($customer->birthday)) {
                    $this->has_errors = true;
                    $errors[] = JText::_('Invalid date of birth.');
                }

                // New Guest customer
                $customer->is_guest = (isset($isNewCustomer) ? !$isNewCustomer : 0);
                $customer->published = 1;

                if (!$this->has_errors){
                    if ($customer->add()){
                        if (!$customer->is_guest) {
                            if (!$this->sendConfirmationMail($customer)) {
                                $this->has_errors = true;
                                $errors[] = JText::_('The email cannot be sent.');
                            }
                        }
                        $this->updateContext($customer);

                        $context->cart->update();
                        Hook::exec('actionCustomerAccountAdd', array(
                            '_POST' => $_POST,
                            'newCustomer' => $customer
                        ));
                        if ($this->use_ajax){
                            $return = array(
                                'hasError' => $this->has_errors,
                                'errors' => $errors,
                                'isSaved' => true,
                                'customer_id' => (int)$context->cookie->customer_id,
                                'delivery_address_id' => $context->cart->delivery_address_id,
                                'invoice_address_id' => $context->cart->invoice_address_id,
                                'token' => Tools::getToken(false)
                            );
                            echo json_encode($return);
                            $app->close();
                        }

                        if (($back = Tools::getValue('back')) && $back == Tools::secureReferrer($back))
                            $app->redirect(html_entity_decode($back));
                        // redirection: if cart is not empty : redirection to the cart
                        if (count($context->cart->getProducts(true)) > 0)
                            $app->redirect('index.php?option=com_jeproshop&view=order&multi-shipping='.(int)Tools::getValue('multi-shipping'));
                        // else : redirection to the account
                        else
                            Tools::redirect('index.php?controller='.(($this->authRedirection !== false) ? urlencode($this->authRedirection) : 'my-account'));
                    }else {
                        $this->has_errors = true;
                        JText::_('An error occurred while creating your account.');
                    }
                }
            }
        }else{ // if registration type is in one step, we save the address

            $input['lastname'] = $lastNameAddress;
            $input['firstname'] = $firstNameAddress;
            $post_back = $_POST;
            // Preparing addresses
            foreach($addresses_types as $addresses_type){
                $address_type = new JeproshopAddressModelAddress();
                $address_type->customer_id = 1;

                if ($addresses_type == 'invoice_address') {
                    foreach ($input as $key => &$post) {
                        if (isset($input['invoice_' . $key])) {
                            $post = $input['invoice_' . $key];
                        }
                    }
                }

                $this->has_errors = true;
                $errors = array_unique(array_merge($errors, $address_type->validateController()));
                if ($addresses_type == 'invoice_address')
                    $_POST = $post_back;

                if (!($country = new JeproshopCountryModelCountry($address_type->country_id)) || !JeproshopTools::isLoadedObject($country, 'country_id')) {
                    $this->has_errors = true;
                    $errors[] = JText::_('Country cannot be loaded with address->id_country');
                }

                if (!$country->published) {
                    $this->has_errors = true;
                    $errors[] = JText::_('This country is not active.');
                }

                $postcode = isset($input['postcode']) ? $input['postcode'] :  '';
                /* Check zip code format */
                if ($country->zip_code_format && !$country->checkZipCode($postcode)) {
                    $this->has_errors = true;
                    $errors[] = JText::_('The Zip/Postal code you\'ve entered is invalid. It must follow this format: %s'); //, str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $country->zip_code_format))));
                }elseif(empty($postcode) && $country->need_zip_code) {
                    $this->has_errors = true;
                    $errors[] = JText::_('A Zip / Postal code is required.');
                }elseif ($postcode && !JeproshopTools::isPostCode($postcode)) {
                    $this->has_errors = true;
                    $errors[] = JText::_('The Zip / Postal code is invalid.');
                }
                $identificationNumber = isset($input['dni']) ? $input['dni'] : '';
                if ($country->need_identification_number && (!$identificationNumber || !JeproshopTools::isDniLite($identificationNumber))) {
                    $this->has_errors = true;
                    $errors[] = JText::_('The identification number is incorrect or has already been used.');
                }elseif (!$country->need_identification_number) {
                    $address_type->dni = null;
                }

                if ($task == 'register' || $task == 'submitGuestAccount') {
                    if (!($country = new JeproshopCountryModelCountry($address_type->country_id, JeproshopSettingModelSetting::getValue('default_lang'))) || !JeproshopTools::isLoadedObject($country, 'country_id')) {
                        $this->has_errors = true;
                        $errors[] = JText::_('Country is invalid');
                    }
                }
                $contains_state = isset($country) && is_object($country) ? (int)$country->contains_states: 0;
                $state_id = isset($address_type) && is_object($address_type) ? (int)$address_type->state_id : 0;
                if (($task == 'register'|| $task == 'submitGuestAccount') && $contains_state && !$state_id) {
                    $this->has_errors = true;
                    $errors[] = JText::_('This country requires you to choose a State.');
                }
            }
        }

        $days =  isset($input['day']) ? $input['day'] : '';
        $months =  isset($input['month']) ? $input['month'] : '';
        $years =  isset($input['year']) ? $input['year'] : '';
        if (!@checkdate($months, $days, $years) && !($months == '' && $days == '' && $years == '')) {
            $this->has_errors = true;
            $errors[] = JText::_('Invalid date of birth');
        }

        if ($this->has_errors){ //todo set negation
            $email = isset($input['email']) ? $input['email'] : '';
            if (JeproshopCustomerModelCustomer::customerExists($email)) {
                $this->has_errors = true;
                $errors[] = JText::_('An account using this email address has already been registered. Please enter a valid password or request a new one. ');
            }
            if (isset($input['newsletter'])) {
                $this->processCustomerNewsletter($customer);
            }
            $customer->birthday = (empty($years) ? '' : (int)$years.'-'.(int)$months .'-'.(int)$days);
            if (!JeproshopTools::isBirthDate($customer->birthday)) {
                $this->has_errors = true;
                $errors[] = JText::_('Invalid date of birth');
            }
            echo $input['passwd'] . ' on line ' . __LINE__;
            if ($this->has_errors){ //todo reset negation
                $customer->published = 1;
                // New Guest customer
                if (isset($isNewCustomer)) {
                    $customer->is_guest = !$isNewCustomer ? 1 : 0;
                }else {
                    $customer->is_guest = 0;
                }
                if (!$customer->add()) {
                    $this->has_errors = true;
                    $errors[] = JText::_('An error occurred while creating your account.');
                }else{
                    foreach($addresses_types as $addresses_type){
                        $address_type->customer_id = (int)$customer->customer_id;
                        if ($addresses_type == 'invoice_address') {
                            foreach ($input as $key => &$post) {
                                if (isset($input['invoice_' . $key])) {
                                    $post = $input['invoice_' . $key];
                                }
                            }
                        }

                        $errors = array_unique(array_merge($errors, $address_type->validateController()));
                        if ($address_type == 'invoice_address')
                            $input = $post_back;
                        if (!$this->has_errors && (JeproshopSettingModelSetting::getValue('registration_process_type') || $this->use_ajax || $task == 'submitGuestAccount') && !$address_type->add()) {
                            $this->has_errors = true;
                            $errors[] = JText::_('An error occurred while creating your address.');
                        }
                    }

                    if (!$this->has_errors){
                        if (!$customer->is_guest){
                            $context->customer = $customer;
                            $customer->cleanGroups();
                            // we add the guest customer in the default customer group
                            $customer->addGroups(array((int)JeproshopSettingModelSetting::getValue('customer_group')));
                            if (!$this->sendConfirmationMail($customer))
                                $this->has_errors = true;
                                $errors[] = JText::_('The email cannot be sent.');
                        }else{
                            $customer->cleanGroups();
                            // we add the guest customer in the guest customer group
                            $customer->addGroups(array((int)JeproshopSettingModelSetting::getValue('guest_group')));
                        }
                        $this->updateContext($customer);
                        $context->cart->delivery_address_id = (int)JeproshopAddressModelAddress::getCustomerFirstAddressId((int)$customer->customer_id);
                        $context->cart->invice_address_id = (int)JeproshopAddressModelAddress::getCustomerFirstAddressId((int)$customer->customer_id);
                        if (isset($invoice_address) && JeproshopTools::isLoadedObject($invoice_address, 'address_id'))
                            $context->cart->invoice_address_id = (int)$invoice_address->address_id;

                        if ($this->use_ajax && (JeproshopSettingModelSetting::getValue('order_process_type')  != 'standard')){
                            $delivery_option = array((int)$context->cart->delivery_address_id => (int)$context->cart->carrier_id .',');
                            $context->cart->setDeliveryOption($delivery_option);
                        }

                        // If a logged guest logs in as a customer, the cart secure key was already set and needs to be updated
                        $context->cart->update();

                        // Avoid articles without delivery address on the cart
                        $context->cart->autosetProductAddress();

                        Hook::exec('actionCustomerAccountAdd', array(
                            '_POST' => $_POST,
                            'newCustomer' => $customer
                        ));
                        if ($this->use_ajax){
                            $return = array(
                                'hasError' => $this->has_errors,
                                'errors' => $errors,
                                'isSaved' => true,
                                'customer_id' => (int)$context->cookie->customer_id,
                                'id_address_delivery' => $context->cart->delivery_address_id,
                                'id_address_invoice' => $context->cart->invoice_address_id,
                                'token' => Tools::getToken(false)
                            );
                            echo json_encode($return);
                            $app->close();
                        }
                        // if registration type is in two steps, we redirect to register address
                        if (JeproshopSettingModelSetting::getValue('registration_process_type') == 'account_only' && !$this->use_ajax && ($task != 'submitGuestAccount')) {
                            $app->redirect('index.php?option=com_jeproshop&view=address');
                        }
                        if (($back = Tools::getValue('back')) && $back == Tools::secureReferrer($back))
                            Tools::redirect(html_entity_decode($back));

                        // redirection: if cart is not empty : redirection to the cart
                        if(count($context->cart->getProducts(true)) > 0) {
                            $app->redirect('index.php?option=com_jeproshop&view=order&multi-shipping=' . (int)$input['multi-shipping']);
                            // else : redirection to the account
                        }else {
                            //todo$app->redirect('index.php?controller=' . (($this->authRedirection !== false) ? urlencode($this->authRedirection) : 'my-account'));
                        }
                    }
                }
            }
        }

        if ($this->has_errors){
            //for retro compatibility to display guest account creation form on authentication page
            if ($task == 'guest_account')
                $_GET['display_guest_checkout'] = 1;

            if (!$isNewCustomer) {
                unset($_POST['passwd']);
            }
            if ($this->use_ajax){
                $return = array(
                    'hasError' => $this->has_errors,
                    'errors' => $errors,
                    'isSaved' => false,
                    'customer_id' => 0
                );
                echo json_encode($return);
                $app->close();
            }
            $viewClass->assign('account_error', $this->has_errors);
        }

        echo json_encode($jsonData);
        $app->close();
    }


    public function setMedia(){
        parent::setMedia();
        $document = JFactory::getDocument();
        $path = JURI::base() . 'components/com_jeproshop/assets/';
        if (!$this->useMobileTheme()) {
            //$this->addCSS(_THEME_CSS_DIR_ . 'authentication.css');
        }
        //$this->addJqueryPlugin('typewatch');
        foreach(array('jeprovat.js', 'jeprostate.js', 'jeproauthentication.js',  'jeprotools.js') as $js){
            $document->addScript($path . 'javascript/script/' . $js);
        }
    }

    /**
     * Process the newsletter settings and set the customer infos.
     *
     * @param JeproshopCustomerModelCustomer $customer Reference on the customer Object.
     *
     * @note At this point, the email has been validated.
     */
    protected function processCustomerNewsletter(&$customer){
        /*if (Tools::getValue('newsletter'))
        {
            $customer->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
            $customer->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));

            if ($module_newsletter = Module::getInstanceByName('blocknewsletter'))
                if ($module_newsletter->active)
                    $module_newsletter->confirmSubscription(Tools::getValue('email'));
        }*/
    }

    /**
     * Update context after customer creation
     * @param JeproshopCustomerModelCustomer $customer Created customer
     * @param JeproshopContext $context
     */
    protected function updateContext(JeproshopCustomerModelCustomer $customer, JeproshopContext $context = null){
        if(!isset($context)){ $context = JeproshopContext::getContext(); }
        $data = JRequest::get('post');
        $context->customer = $customer;
        $confirmation = 1;
        $context->cookie->customer_id = (int)$customer->customer_id;
        $context->cookie->customer_lastname = $customer->lastname;
        $context->cookie->customer_firstname = $customer->firstname;
        $context->cookie->passwd = $customer->passwd;
        $context->cookie->logged = 1;
        // if register process is in two steps, we display a message to confirm account creation
        if (!JeproshopSettingModelSetting::getValue('registration_process_type')) {
            $context->cookie->account_created = 1;
        }
        $customer->logged = 1;
        $context->cookie->email = $customer->email;
        $context->cookie->is_guest = isset($data['is_new_customer']) ? !$data['is_new_customer'] : 0;
        // Update cart address
        $context->cart->secure_key = $customer->secure_key;
    }

    /**
     * sendConfirmationMail
     * @param JeproshopCustomerModelCustomer $customer
     * @return bool
     */
    protected function sendConfirmationMail(JeproshopCustomerModelCustomer $customer){
        if (!Configuration::get('PS_CUSTOMER_CREATION_EMAIL'))
            return true;
        $context = JeproshopContext::getContext();
        return Mail::Send(
            $context->language->id,
            'account',
            Mail::l('Welcome!'),
            array(
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{email}' => $customer->email,
                '{passwd}' => Tools::getValue('passwd')),
            $customer->email,
            $customer->firstname.' '.$customer->lastname
        );
    }

}