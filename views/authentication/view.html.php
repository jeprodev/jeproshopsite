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

class JeproshopAuthenticationViewAuthentication extends JViewLegacy
{
    protected $context = null;

    public function renderDetails($tpl = null){
        $app = JFactory::getApplication();
        if(!isset($this->context)){ $this->context = JeproshopContext::getContext(); }
        $this->assignDate();

        $this->assignCountries();

        $this->assign('news_letter', 1);

        /*$back = $app->input->get('back');
        $key = JeproshopTools::safeOutput($app->input->get('key'));
        if (!empty($key))
            $back .= (strpos($back, '?') !== false ? '&' : '?').'key='.$key;
        if ($back == JeproshopTools::secureReferrer(Tools::getValue('back')))
            $this->context->smarty->assign('back', html_entity_decode($back));
        else
            $this->context->smarty->assign('back', Tools::safeOutput($back));
/*
        if ($app->input->get('display_guest_checkout'))
        {
            if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES'))
                $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
            else
                $countries = Country::getCountries($this->context->language->id, true);

            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
            {
                // get all countries as language (xy) or language-country (wz-XY)
                $array = array();
                preg_match("#(?<=-)\w\w|\w\w(?!-)#", $_SERVER['HTTP_ACCEPT_LANGUAGE'], $array);
                if (!Validate::isLanguageIsoCode($array[0]) || !($sl_country = Country::getByIso($array[0])))
                    $sl_country = (int)Configuration::get('PS_COUNTRY_DEFAULT');
            }else{
                $sl_country = (int)Tools::getValue('id_country', Configuration::get('PS_COUNTRY_DEFAULT'));

            $this->context->smarty->assign(array(
                'inOrderProcess' => true,
                'PS_GUEST_CHECKOUT_ENABLED' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
                'PS_REGISTRATION_PROCESS_TYPE' => Configuration::get('PS_REGISTRATION_PROCESS_TYPE'),
                'sl_country' => (int)$sl_country,
                'countries' => $countries
            ));
        }

        if (Tools::getValue('create_account'))
            $this->assign('email_create', 1);

        if (Tools::getValue('multi-shipping') == 1)
            $this->context->smarty->assign('multi_shipping', true);
        else
            $this->context->smarty->assign('multi_shipping', false);
*/
        $this->assignAddressFormat();
/*
        // Call a hook to display more information on form
        $this->context->smarty->assign(array(
            'HOOK_CREATE_ACCOUNT_FORM' => Hook::exec('displayCustomerAccountForm'),
            'HOOK_CREATE_ACCOUNT_TOP' => Hook::exec('displayCustomerAccountFormTop')
        )); */
        parent::display($tpl);
    }

    private function assignDate(){
        //Generate years, month and days
        $app = JFactory::getApplication();
        $years = $app->input->get('years');
        $selectedYear = 0;
        if(isset($years) && is_numeric($years)){ $selectedYear = $years;}
        $years = JeproshopTools::dateYears();

        $months = $app->input->get('months');
        $selectedMonths = 0;
        if(isset($months) && is_numeric($months)){ $selectedMonths = $months; }

        $days = $app->input->get('days');
        $selectedDay = 0;
        if(isset($days) && is_numeric($days)){ $selectedDay = $days; }

        $onePhoneAtLeast = (int)JeproshopSettingModelSetting::getValue('one_phone_at_least');
        $this->assignRef('years', $years);
        $this->assignRef('selected_year', $selectedYear);
        $this->assignRef('selected_month', $selectedMonths);
        //$this->assignRef('days', $days);
        $this->assignRef('selected_day', $selectedDay);
        $this->assignRef('one_phone_at_least', $onePhoneAtLeast);
    }

    private function assignCountries(){
        // Select the most appropriate country
        $app = JFactory::getApplication();
        $country_id = $app->input->get('country_id');
        if (isset($country_id) && is_numeric($country_id)) {
            $selectedCountry = (int)($country_id);
        }
        if (!isset($selectedCountry)) {
            $selectedCountry = (int)(JeproshopSettingModelSetting::getValue('default_country'));
        }

        if (JeproshopSettingModelSetting::getValue('restrict_delivered_countries')) {
            $countries = JeproshopCarrierModelCarrier::getDeliveredCountries($this->context->language->lang_id, true, true);print_r($countries);
        }else {
            $countries = JeproshopCountryModelCountry::getCountries($this->context->language->lang_id, true);
        }
        $zones = JeproshopZoneModelZone::getZones();
        $this->assignRef('zones', $zones);
        $this->assignRef('countries', $countries);
        $registrationProcessType = JeproshopSettingModelSetting::getValue('registration_process_type');
        $this->assignRef('registration_process_type', $registrationProcessType);
        $selectedCountry = (isset($selectedCountry) ? $selectedCountry : 0);
        $this->assignRef('selected_country', $selectedCountry);
        $vatNumberManagement = JeproshopSettingModelSetting::getValue('vat_number_management');
        $this->assignRef('vat_management', $vatNumberManagement);
    }

    /**
     * Assign address var to smarty
     */
    protected function assignAddressFormat(){
        $addressItems = array();
        $addressFormat = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields(JeproshopSettingModelSetting::getValue('default_country'), false, true);
        $requireFormFieldsList = JeproshopAddressFormatModelAddressFormat::$requireFormFieldsList;

        foreach ($addressFormat as $addressLine) {
            foreach (explode(' ', $addressLine) as $addressItem) {
                $addressItems[] = trim($addressItem);
            }
        }
        // Add missing require fields for a new user subscription form
        foreach ($requireFormFieldsList as $fieldName) {
            if (!in_array($fieldName, $addressItems)) {
                $addressItems[] = trim($fieldName);
            }
        }

        foreach (array('invoice', 'delivery') as $addressType) {
            $this->assignRef($addressType . '_address_fields', $addressFormat);
            $this->assignRef($addressType . '_all_fields', $addressItems);
        }
    }
}