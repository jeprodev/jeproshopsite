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

if(!file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'load.php')){
    JError::raiseError(500, JText::_(''));
}
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'load.php';


/** initialize the shop **/
JeproshopContext::getContext()->shop = JeproshopShopModelShop::initialize();

$context = JeproshopContext::getContext();

/** load configuration  */
JeproshopSettingModelSetting::loadSettings();

/** load languages  */
JeproshopLanguageModelLanguage::loadLanguages();

/** set context cookie */
$life_time = time() + (max(JeproshopSettingModelSetting::getValue('bo_life_time'), 1) * 3600);
$context->cookie = new JeproshopCookie('jeproshop_site', '', $life_time);

/** @var  employee */
$context->employee = new JeproshopEmployeeModelEmployee(JFactory::getUser()->id);
$context->cookie->employee_id = $context->employee->employee_id;

/** Loading default country */
$context->country = new JeproshopCountryModelCountry(JeproshopSettingModelSetting::getValue('default_country'), JeproshopSettingModelSetting::getValue('default_lang'));

/** if the cookie stored language is not an available language, use default language */
if(isset($context->cookie->lang_id) && $context->cookie->lang_id){
    $language = new JeproshopLanguageModelLanguage($context->cookie->lang_id);
}

if(!isset($language) || !JeproshopTools::isLoadedObject($language, 'lang_id')){
    $language = new JeproshopLanguageModelLanguage(JeproshopSettingModelSetting::getValue('default_lang'));
}

$context->language = $language;


$currency_id = ($context->cookie->currency_id ) ? $context->cookie->currency_id : JeproshopSettingModelSetting::getValue('default_currency');
$context->currency = new JeproshopCurrencyModelCurrency($currency_id);

if(isset($context->cookie->customer_id) && (int)$context->customer_id){
    $customer = new JeproshopCustomerModelCustomer($context->cookie->customer_id);
    if(!JeproshopTools::isLoadedObject($customer)){
        $context->cookie->logout();
    }else{
        $customer->logged = TRUE;
        if($customer->lang_id != $context->language->lang_id){
            $customer->lang_id = $context->language->lang_id;
            $customer->update();
        }
    }
}

if(!isset($customer) || !JeproshopTools::isLoadedObject($customer)){
    $customer = new JeproshopCustomerModelCustomer();
    if(JeproshopGroupModelGroup::isFeaturePublished()){
        $customer->default_group_id = (int)JeproshopSettingModelSetting::getValue('unidentified_group');
    }
}

$customer->guest_id = $context->cookie->guest_id;
$context->customer = $customer;

/** controller and redirection */
$controller = JFactory::getApplication()->input->get('view');

if($controller) {
    if(file_exists(dirname(__FILE__). DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller . '.php')){
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'controller.php';
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $controller . '.php';
    }else{
        $controller = '';
    }
    $context->controller = JControllerLegacy::getInstance('Jeproshop' . $controller);
    $context->controller->initialize();
    $context->controller->initContent();
}else{
    $context->controller = JControllerLegacy::getInstance('Jeproshop' . $controller);
    $task = JFactory::getApplication()->input->get('task') != '' ? JFactory::getApplication()->input->get('task') : 'display'; 
    $context->controller->execute($task);
    $context->controller->redirect();
} 