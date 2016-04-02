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


class JeproshopAddressController extends JeproshopController
{
    public function initialize(){
        parent::initialize();
        $app = JFactory::getApplication();
        $context = JeproshopContext::getContext();

        // Get address ID
        $address_id = 0;
        $type = $app->input->get('type');
        if ($this->use_ajax && isset($type)){
            if ($type == 'delivery' && isset($context->cart->address_delivery_id))
                $address_id = (int)$context->cart->address_delivery_id;
            else if ($type == 'invoice' && isset($context->cart->address_invoice_id)
                && $context->cart->id_address_invoice != $context->cart->address_delivery_id)
                $address_id = (int)$context->cart->address_invoice_id;
        }
        else
            $address_id = (int)$app->input->get('address_id', 0);

        // Initialize address
        if ($address_id){
            $address = new JeproshopAddressModelAddress($address_id);
            $view = $app->input->get('view');
            $viewClass = $this->getView($view, JFactory::getDocument()->getType());
            if (JeproshopTools::isLoadedObject($address, 'address_id') && JeproshopCustomerModelCustomer::customerHasAddress($context->customer->customer_id, $address_id)){
                $task = $app->input->get('task');
                if (isset($task) && $task == 'delete'){
                    if ($address->delete()){
                        if ($context->cart->address_invoice_id == $address->address_id)
                            unset($context->cart->address_invoice_id);
                        if ($context->cart->id_address_delivery == $address->address_id){
                            unset($context->cart->id_address_delivery);
                            $context->cart->updateAddressId($address->address_id, (int)JeproshopAddressModelAddress::getCustomerFirstAddressId($context->customer->customer_id));
                        }
                        $app->redirect('index.php?option=com_jeproshop&view=address');
                    }
                    $this->has_errors = true;
                    JError::raiseError(500, 'This address cannot be deleted.');
                }
            }elseif ($this->use_ajax) {
                $app->close;
            }else {
                $app->redirect('index.php?option=com_jeproshop&view=address');
            }
        }

    }
}