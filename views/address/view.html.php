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

class JeproshopAddressViewAddress extends JViewLegacy
{
    protected $address;
    public function renderDetails(){
        $context = JeproshopContext::getContext();
        $app = JFactory::getApplication();
        $country_id = $app->input->get('country_id');
        // Get address ID
        $address_id = 0;
        $type = $app->input->get('type');
        if ($context->controller->use_ajax && isset($type)){
            if ($type == 'delivery' && isset($context->cart->address_delivery_id))
                $address_id = (int)$context->cart->address_delivery_id;
            else if ($type == 'invoice' && isset($context->cart->address_invoice_id)
                && $context->cart->id_address_invoice != $context->cart->address_delivery_id)
                $address_id = (int)$context->cart->address_invoice_id;
        }else {
            $address_id = (int)$app->input->get('address_id', 0);
        }

        $this->address = new JeproshopAddressModelAddress($address_id);
        $this->assignCountries();
        $this->assignVatNumber();
        $this->assignAddressFormat();

        /*/ Assign common vars
        $this->assignRef(array(
            'address_validation' => Address::$definition['fields'],
            'one_phone_at_least' => (int)Configuration::get('PS_ONE_PHONE_AT_LEAST'),
            'ajaxurl' => _MODULE_DIR_,
            'errors' => $this->errors,
            'token' => Tools::getToken(false),
            'select_address' => (int)Tools::getValue('select_address'),

            'id_address' => (Validate::isLoadedObject($this->_address)) ? $this->_address->id : 0,
        ));*/
        $this->setLayout('address');
        parent::display();
    }

    /**
     * Assign template vars related to countries display
     */
    protected function assignCountries(){
        $context = JeproshopContext::getContext();
        $app = JFactory::getApplication();
        $country_id = $app->input->get('country_id');

        // Get selected country
        if (isset($country_id) && !is_null($country_id) && is_numeric($country_id))
            $selected_country = (int)$country_id;
        else if (isset($this->address) && isset($this->address->country_id) && !empty($this->address->country_id) && is_numeric($this->address->country_id))
            $selected_country = (int)$this->address->country_id;
        else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            // get all countries as language (xy) or language-country (wz-XY)
            $array = array();
            preg_match("#(?<=-)\w\w|\w\w(?!-)#",$_SERVER['HTTP_ACCEPT_LANGUAGE'],$array);
            if (!JeproshopTools::isLanguageIsoCode($array[0]) || !($selected_country = JeproshopCountryModelCountry::getByIso($array[0])))
                $selected_country = (int)JeproshopSettingModelSetting::getValue('default_country');
        }
        else
            $selected_country = (int)JeproshopSettingModelSetting::getValue('default_country');

        // Generate countries list
        if (JeproshopSettingModelSetting::getValue('restrict_delivered_countries')) {
            $countries = JeproshopCarrierModelCarrier::getDeliveredCountries($context->language->lang_id, true, true);
        }else {
            $countries = JeproshopCountryModelCountry::getCountries($context->language->lang_id, true);
        }
        // @todo use helper
        $list = '';
        foreach ($countries as $country){
            $selected = ($country->country_id == $selected_country) ? 'selected="selected"' : '';
            $list .= '<option value="'.(int)$country->country_id .'" '.$selected.'>'.htmlentities(ucfirst($country->name)) . '</option>';
        }

        // Assign vars
        $this->assignRef('countries_list', $list);
        $this->assignRef('countries', $countries);
    }

    /**
     * Assign template vars related to address format
     */
    protected function assignAddressFormat(){
        $country_id = is_null($this->address)? 0 : (int)$this->address->country_id;
        $ordered_adr_fields = JeproshopAddressFormatModelAddressFormat::getOrderedAddressFields($country_id, true, true);
        $this->assignRef('ordered_address_fields', $ordered_adr_fields);
    }

    /**
     * Assign template vars related to vat number
     * @todo move this in vat-number module !
     */
    protected function assignVatNumber() {
        /*$vat_number_exists = file_exists(_PS_MODULE_DIR_.'vatnumber/vatnumber.php');
        $vat_number_management = Configuration::get('VATNUMBER_MANAGEMENT');
        if ($vat_number_management && $vat_number_exists)
            include_once(_PS_MODULE_DIR_.'vatnumber/vatnumber.php');

        if ($vat_number_management && $vat_number_exists && VatNumber::isApplicable(Configuration::get('PS_COUNTRY_DEFAULT')))
            $vat_display = 2;
        else if ($vat_number_management)
            $vat_display = 1;
        else
            $vat_display = 0;

        $this->context->smarty->assign(array(
            'vatnumber_ajax_call' => file_exists(_PS_MODULE_DIR_.'vatnumber/ajax.php'),
            'vat_display' => $vat_display,
        )); */
    }
}