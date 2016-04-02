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

class JeproshopOrderModelOrder extends JModelLegacy
{
    public $order_id;

    public $address_delivery_id;
    public $address_delivery;

    public $address_invoice_id;
    public $address_invoice;

    public $shop_group_id;

    public $shop_id;

    public $cart_id;

    public $currency_id;

    public $lang_id;

    public $customer_id;

    public $carrier_id;

    public $current_state;

    public $secure_key;

    public $payment;

    public $conversion_rate;

    public $recyclable = 1;

    public $gift = 0;

    public $gift_message;

    public $mobile_theme;

    public $shipping_number;

    public $total_discounts;

    public $total_discounts_tax_included;
    public $total_discounts_tax_excluded;

    public $total_paid;
    public $total_paid_tax_included;
    public $total_paid_tax_excluded;

    public $total_paid_real;

    public $total_products;

    public $total_products_wt;

    public $total_shipping;
    public $total_shipping_tax_excluded;
    public $total_shipping_tax_included;

    public $carrier_tax_rate;

    public $total_wrapping;
    public $total_wrapping_tax_incl;
    public $total_wrapping_tax_excl;

    public $invoice_number;
    public $invoice_date;

    public $delivery_number;
    public $delivery_date;

    public $valid;

    public $date_add;
    public $date_upd;

    public $reference;

    protected $context;

    protected $_taxCalculationMethod = COM_JEPROSHOP_TAX_EXCLUDED;
    protected static $_historyCache = array();

    public function __construct($order_id = null, $lang_id = null) {
        //parent::__construct();

        $db = JFactory::getDBO();

        if($lang_id !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($lang_id) ? (int)$lang_id : JeproshopSettingModelSetting::getValue('default_lang'));
        }

        if($this->isMultiShop() && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if($order_id){
            $cache_id = 'jeproshop_order_model_' . $order_id . '_' . $lang_id . '_' . $this->shop_id;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord ";
                $where = "";
                /** get language information **/
                if($lang_id){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ";
                    $query .= "ON (product.product_id = product_lang.product_id AND product_lang.lang_id = " . (int)$lang_id . ") ";
                    if($this->shop_id && !(empty($this->multiLangShop))){
                        $where = " AND product_lang.shop_id = " . $this->shop_id;
                    }
                }

                /** Get shop information **/
                if(JeproshopShopModelShop::isTableAssociated('order')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_order_shop') . " AS order_shop ON (";
                    $query .= "ord.order_id = order_shop.order_id AND order_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE ord.order_id = " . (int)$order_id . $where;

                $db->setQuery($query);
                $order_data = $db->loadObject();

                if($order_data){
                    if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_product_lang');
                        $query .= " WHERE product_id = " . (int)$order_id;

                        $db->setQuery($query);
                        $order_lang_data = $db->loadObjectList();
                        if($order_lang_data){
                            foreach ($order_lang_data as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'order_id'){
                                        if(!isset($order_data->{$key}) || !is_array($order_data->{$key})){
                                            $order_data->{$key} = array();
                                        }
                                        $order_data->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cache_id, $order_data);
                }
            }else{
                $order_data = JeproshopCache::retrieve($cache_id);
            }

            if($order_data){
                $order_data->product_id = $order_id;
                foreach($order_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        if ($this->customer_id){
            $customer = new JeproshopCustomerModelCustomer((int)($this->customer_id));
            $this->_taxCalculationMethod = JeproshopGroupModelGroup::getPriceDisplayMethod((int)$customer->default_group_id);
        }

    }

    /**
     * Can this order be returned by the client?
     *
     * @return bool
     */
    public function isReturnable() {
        if (JeproshopSettingModelSetting::getValue('return_order') && $this->isPaidAndShipped()){
            return $this->getNumberOfDays();
        }
        return false;
    }

    public function getNumberOfDays() {
        $nbReturnDays = (int)(JeproshopSettingModelSetting::getValue('return_order_nb_days'));
        if (!$nbReturnDays)
            return true;
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT TO_DAYS(NOW()) - TO_DAYS(`delivery_date`)  AS days FROM `'._DB_PREFIX_.'orders`
		WHERE `id_order` = '.(int)($this->order_id));
        if ($result['days'] <= $nbReturnDays)
            return true;
        return false;
    }

    /**
     * Checks if the current order status is paid and shipped
     *
     * @return bool
     */
    public function isPaidAndShipped(){
        $order_state = $this->getCurrentOrderStatus();
        if ($order_state && $order_state->paid && $order_state->shipped)
            return true;
        return false;
    }

    /**
     * @since 1.5.0.4
     * @return JeproshopOrderStatusModelOrderStatus or null if Order haven't a state
     */
    public function getCurrentOrderStatus(){
        if ($this->current_state){
            return new JeproshopOrderStatusModelOrderStatus($this->current_state);
        }
        return null;
    }

    /**
     * Get current order status (eg. Awaiting payment, Delivered...)
     *
     * @return int Order status id
     */
    public function getCurrentState(){
        return $this->current_state;
    }

    /**
     * Check if order contains (only) virtual products
     *
     * @param boolean $strict If false return true if there are at least one product virtual
     * @return boolean true if is a virtual order or false
     *
     */
    public function isVirtual($strict = true) {
        $products = $this->getProducts();
        if (count($products) < 1){	return false; }
        $virtual = true;
        foreach ($products as $product){
            $pd = JeproshopProductDownloadModelProductDownload::getIdFromProductId((int)($product->product_id));
            if ($pd && JeproshopValidator::isUnsignedInt($pd) && $product->download_hash && $product->display_filename != ''){
                if ($strict === false){ return true; }
            }
            else
                $virtual &= false;
        }
        return $virtual;
    }

    /**
     * Has products returned by the merchant or by the customer?
     */
    public function hasProductReturned(){
        $db = JFactory::getDBO();

        $query = "SELECT IFNULL(SUM(order_return_detail.product_quantity), SUM(product_quantity_return)) FROM " . $db->quoteName('#__jeproshop_orders') ;
        $query .= " AS ord INNER JOIN " . $db->quoteName('#__jeproshop_order_detail') . " AS order_detail ON order_detail.order_id = ord.order_id LEFT JOIN " ;
        $query .= $db->quoteName('#__jeproshop_order_return_detail') . " AS order_return_detail ON order_return_detail.order_detail_id = order_detail.order_detail_id ";
        $query .= "WHERE ord.order_id = " . (int)$this->order_id;

        $db->setQuery($query);
        return $db->loadResult();
    }


    /**
     * @return array return all shipping method for the current order
     * state_name sql var is now deprecated - use order_state_name for the state name and carrier_name for the carrier_name
     */
    public function getShipping() {
        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT order_carrier." . $db->quoteName('order_invoice_id') . ", order_carrier." . $db->quoteName('weight');
        $query .= ", order_carrier." . $db->quoteName('shipping_cost_tax_excl') . ", order_carrier." . $db->quoteName('shipping_cost_tax_incl');
        $query .= ", carrier." . $db->quoteName('url') . ", order_carrier." . $db->quoteName('carrier_id') . ", carrier." . $db->quoteName('name');
        $query .= " AS carrier_name, order_carrier." . $db->quoteName('date_add') . ", \"Delivery\" AS " . $db->quoteName('type') . ", \"true\" AS";
        $query .= " can_edit, order_carrier." . $db->quoteName('tracking_number') . ", order_carrier." . $db->quoteName('order_carrier_id');
        $query .= ", order_state_lang." . $db->quoteName('name') . " AS order_state_name, carrier." . $db->quoteName('name') . " AS state_name ";
        $query .= " FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord LEFT JOIN " . $db->quoteName('#__jeproshop_order_history');
        $query .= " AS order_history ON (ord." . $db->quoteName('order_id') . " = order_history." . $db->quoteName('order_id') . ") LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_order_carrier') . " AS order_carrier ON (ord." . $db->quoteName('order_id') . " = order_carrier.";
        $query .= $db->quoteName('order_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_carrier') . " AS carrier ON (order_carrier.";
        $query .= $db->quoteName('carrier_id') . " = carrier." . $db->quoteName('carrier_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_order_state_lang');
        $query .= " AS order_state_lang ON (order_history." . $db->quoteName('order_state_id') . " = order_state_lang." . $db->quoteName('order_state_id');
        $query .= " AND order_state_lang." . $db->quoteName('lang_id') . " = " . (int)JeproshopContext::getContext()->language->lang_id . ") WHERE ord.";
        $query .= $db->quoteName('order_id') . " = " .(int)$this->order_id . " GROUP BY carrier.carrier_id ";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Get order products
     *
     * @param bool $products
     * @param bool $selectedProducts
     * @param bool $selectedQty
     * @return array Products with price, quantity (with tax and without)
     */
    public function getProducts($products = false, $selectedProducts = false, $selectedQty = false){
        if (!$products){
            $products = $this->getProductsDetail();
        }
        $customized_datas = JeproshopProductModelProduct::getAllCustomizedDatas($this->cart_id);

        $resultArray = array();
        foreach ($products as $row){
            // Change qty if selected
            if ($selectedQty){
                $row->product_quantity = 0;
                foreach ($selectedProducts as $key => $product_id){
                    if ($row->order_detail_id == $product_id){
                        $row->product_quantity = (int)($selectedQty[$key]);
                    }
                }
                if (!$row->product_quantity){ continue; }
            }

            $this->setProductImageInformations($row);
            $this->setProductCurrentStock($row);

            // Backward compatibility 1.4 -> 1.5
            $this->setProductPrices($row);

            $this->setProductCustomizedDatas($row, $customized_datas);

            // Add information for virtual product
            if ($row->download_hash && !empty($row->download_hash))	{
                $row->filename = JeproshopProductDownloadModelProductDownload::getFilenameFromProductIdId((int)$row->product_id);
                // Get the display filename
                $row->display_filename = JeproshopProductDownloadModelProductDownload::getFilenameFromFilename($row->filename);
            }

            $row->address_delivery_id = $this->address_delivery_id;

            /* Stock product */
            $resultArray[(int)$row->order_detail_id] = $row;
        }

        if ($customized_datas)
            JeproshopProductModelProduct::addCustomizationPrice($resultArray, $customized_datas);

        return $resultArray;
    }

    /**
     * Get order history
     *
     * @param integer $lang_id Language id
     * @param bool|int $order_state_id Filter a specific order status
     * @param bool|int $no_hidden Filter no hidden status
     * @param integer $filters Flag to use specific field filter
     *
     * @return array History entries ordered by date DESC
     */
    public function getHistory($lang_id, $order_state_id = false, $no_hidden = false, $filters = 0){
        if (!$order_state_id){ $order_state_id = 0; }

        $logable = false;
        $delivery = false;
        $paid = false;
        $shipped = false;
        if ($filters > 0){
            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_NO_HIDDEN){ $no_hidden = true; }

            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_DELIVERY){ $delivery = true; }

            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_LOGABLE){ $logable = true; }

            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_PAID){ $paid = true;}

            if ($filters & JeproshopOrderStatusModelOrderStatus::FLAG_SHIPPED){ $shipped = true; }
        }

        if (!isset(self::$_historyCache[$this->order_id.'_'.$order_state_id .'_'.$filters]) || $no_hidden){
            $db = JFactory::getDBO();
            $lang_id = $lang_id ? (int)($lang_id) : 'o.`id_lang`';

            $query = "SELECT order_state.*, order_history.*, employee." . $db->quoteName('username') . " AS employee_firstname,";
            $query .= " employee." .$db->quoteName('name') . " AS employee_lastname, order_state_lang." . $db->quoteName('name');
            $query .= " AS order_state_name FROM " . $db->quoteName('#__jeproshop_orders') . " AS ord LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_order_history') . " AS order_history ON ord." . $db->quoteName('order_id');
            $query .= " = order_history." . $db->quoteName('order_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_order_state');
            $query .= " AS order_state ON order_state." . $db->quoteName('order_state_id') . " = order_history." . $db->quoteName('order_state_id');
            $query .= "	LEFT JOIN " . $db->quoteName('#__jeproshop_order_state_lang') . " AS order_state_lang ON (order_state.";
            $query .= $db->quoteName('order_state_id') . " = order_state_lang." . $db->quoteName('order_state_id') . " AND order_state_lang.";
            $query .= $db->quoteName('lang_id') . " = " . (int)($lang_id) . ") LEFT JOIN " . $db->quoteName('#__users') . " AS employee ON";
            $query .= " employee." . $db->quoteName('id') . " = order_history." . $db->quoteName('employee_id') . " WHERE order_history.order_id = ";
            $query .= (int)($this->order_id) . ($no_hidden ? " AND order_state.hidden = 0" : "") . ($logable ? " AND order_state.logable = 1" : "");
            $query .= ($delivery ? " AND order_state.delivery = 1" : "") . ($paid ? " AND order_state.paid = 1" : "") . ($shipped ? " AND order_state.shipped = 1" : "");
            $query .= ((int)($order_state_id) ? " AND order_history." . $db->quoteName('order_state_id') . " = " . (int)($order_state_id) : "");
            $query .= " ORDER BY order_history.date_add DESC, order_history.order_history_id DESC";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            if ($no_hidden)
                return $result;
            self::$_historyCache[$this->order_id.'_'.$order_state_id .'_'.$filters] = $result;
        }
        return self::$_historyCache[$this->order_id.'_'.$order_state_id.'_'.$filters];
    }

    public function getCartRules(){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_cart_rule') . " AS order_cart_rule WHERE order_cart_rule.";
        $query .= $db->quoteName('order_id') . " = " . (int)$this->order_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }
    /**
     * Marked as deprecated but should not throw any "deprecated" message
     * This function is used in order to keep front office backward compatibility 14 -> 1.5
     * (Order History)
     *
     * @deprecated
     */
    public function setProductPrices(&$row){
        $tax_calculator = JeproshopOrderDetailModelOrderDetail::getStaticTaxCalculator((int)$row->order_detail_id);
        $row->tax_calculator = $tax_calculator;
        $row->tax_rate = $tax_calculator->getTotalRate();

        $row->product_price = JeproshopValidator::roundPrice($row->unit_price_tax_excl, 2);
        $row->product_price_with_tax = JeproshopValidator::roundPrice($row->unit_price_tax_incl, 2);

        $group_reduction = 1;
        if ($row->group_reduction > 0){
            $group_reduction = 1 - $row->group_reduction / 100;
        }
        $row->product_price_with_tax_but_ecotax = $row->product_price_with_tax - $row->ecotax;

        $row->total_with_tax = $row->total_price_tax_incl;
        $row->total_price = $row->total_price_tax_excl;
    }

    protected function setProductCustomizedDatas(&$product, $customized_datas){
        $product->customizedDatas = null;
        if (isset($customized_datas[$product->product_id][$product->product_attribute_id])){
            $product->customizedDatas = $customized_datas[$product->product_id][$product->product_attribute_id];
        }else{
            $product->customizationQuantityTotal = 0;
        }
    }

    /**
     *
     * This method allow to add stock information on a product detail
     *
     * If advanced stock management is active, get physical stock of this product in the warehouse associated to the product for the current order
     * Else get the available quantity of the product in fucntion of the shop associated to the order
     *
     * @param array &$product
     */
    protected function setProductCurrentStock(&$product){
        if(JeproshopSettingModelSetting::getValue('advanced_stock_management') && (int)$product->advanced_stock_management == 1	&& (int)$product->warehouse_id > 0){
            $product->current_stock = JeproshopStockManagerFactory::getManager()->getProductPhysicalQuantities($product->product_id, $product->product_attribute_id, (int)$product->warehouse_id, true);
        }else{
            $product->current_stock = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($product->product_id, $product->product_attribute_id, (int)$this->shop_id);
        }
    }

    /**
     *
     * This method allow to add image information on a product detail
     * @param array &$product
     */
    protected function setProductImageInformations(&$product){
        $db = JFactory::getDBO();

        if (isset($product->product_attribute_id) && $product->product_attribute_id){
            $query = "SELECT image_shop.image_id FROM " . $db->quoteName('#__jeproshop_product_attribute_image') . " AS product_attribte_image";
            $query .= JeproshopShopModelShop::addSqlAssociation('image', 'pai', true) . " WHERE product_attribute_id = " . (int)$product->product_attribute_id;

            $db->setQuery($query);
            $image_id = $db->loadResult();
        }

        if (!isset($image_id) || !$image_id){
            $query = "SELECT image_shop.image_id FROM " . $db->quoteName('#__jeproshop_image') . " AS image";
            $query .= JeproshopShopModelShop::addSqlAssociation('image', true, 'image_shop.cover=1') . " WHERE";
            $query .= " product_id = " . (int)($product->product_id);

            $db->setQuery($query);
            $image_id = $db->loadResult();
        }

        $product->image = null;
        $product->image_size = null;

        if ($image_id){
            $product->image = new JeproshopImageModelImage($image_id);
        }
    }

    public function getTotalProductsWithoutTaxes($products = false){
        return $this->total_products;
    }

    /**
     * Get product total with taxes
     *
     * @param bool $products
     * @return Product total with taxes
     */
    public function getTotalProductsWithTaxes($products = false){
        if ($this->total_products_wt != '0.00' && !$products)
            return $this->total_products_wt;
        /* Retro-compatibility (now set directly on the validateOrder() method) */

        if (!$products)
            $products = $this->getProductsDetail();

        $return = 0;
        foreach ($products as $row)
            $return += $row->total_price_tax_incl;

        if (!$products){
            $this->total_products_wt = $return;
            $this->update();
        }
        return $return;
    }

    /**
     * Return a unique reference like : GWJTHMZUN#2
     *
     * With multi shipping, order reference are the same for all orders made with the same cart
     * in this case this method suffix the order reference by a # and the order number
     *
     */
    public function getUniqueReference(){
        $db = JFactory::getDBO();
        $query = "SELECT MIN(order_id) as min, MAX(order_id) as max FROM " . $db->quoteName('#__jeproshop_orders');
        $query .= " WHERE " .$db->quoteName('cart_id') . " = " . (int)$this->cart_id;

        $db->setQuery($query);
        $order = $db->loadObject();

        if ($order->min == $order->max)
            return $this->reference;
        else
            return $this->reference.'#'.($this->order_id + 1 - $order->min);
    }


    public function getProductsDetail(){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_detail') . " AS order_detail LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_product') . " AS product ON (product.product_id = order_detail.";
        $query .= "product_id) LEFT JOIN " . $db->quoteName('#__jeproshop_product_shop') . " AS product_shop ON (";
        $query .= " product_shop.product_id = product.product_id AND product_shop.shop_id = order_detail.shop_id) ";
        $query .= "WHERE order_detail." . $db->quoteName('order_id') . " = " .(int)($this->order_id);

        $db->setQuery($query);
        return $db->loadObjectList();
    }


    public function getTaxCalculationMethod(){
        return (int)($this->_taxCalculationMethod);
    }



    public function isMultiShop(){
        return (JeproshopShopModelShop::isTableAssociated('order') || !empty($this->multiLangShop));
    }
}


class JeproshopOrderDetailModelOrderDetail extends JModelLegacy
{
    /** @var integer */
    public $order_detail_id;

    /** @var integer */
    public $order_id;

    /** @var integer */
    public $order_invoice_id;

    /** @var integer */
    public $product_id;

    /** @var integer */
    public $shop_id;

    /** @var integer */
    public $product_attribute_id;

    /** @var string */
    public $product_name;

    /** @var integer */
    public $product_quantity;

    /** @var integer */
    public $product_quantity_in_stock;

    /** @var integer */
    public $product_quantity_return;

    /** @var integer */
    public $product_quantity_refunded;

    /** @var integer */
    public $product_quantity_reinjected;

    /** @var float */
    public $product_price;

    /** @var float */
    public $original_product_price;

    /** @var float */
    public $unit_price_tax_incl;

    /** @var float */
    public $unit_price_tax_excl;

    /** @var float */
    public $total_price_tax_incl;

    /** @var float */
    public $total_price_tax_excl;

    /** @var float */
    public $reduction_percent;

    /** @var float */
    public $reduction_amount;

    /** @var float */
    public $reduction_amount_tax_excl;

    /** @var float */
    public $reduction_amount_tax_incl;

    /** @var float */
    public $group_reduction;

    /** @var float */
    public $product_quantity_discount;

    /** @var string */
    public $product_ean13;

    /** @var string */
    public $product_upc;

    /** @var string */
    public $product_reference;

    /** @var string */
    public $product_supplier_reference;

    /** @var float */
    public $product_weight;

    /** @var float */
    public $ecotax;

    /** @var float */
    public $ecotax_tax_rate;

    /** @var integer */
    public $discount_quantity_applied;

    /** @var string */
    public $download_hash;

    /** @var integer */
    public $download_nb;

    /** @var date */
    public $download_deadline;

    /** @var string $tax_name **/
    public $tax_name;

    /** @var float $tax_rate **/
    public $tax_rate;

    /** @var float $tax_computation_method **/
    public $tax_computation_method;

    /** @var int Id warehouse */
    public $warehouse_id;

    /** @var float additional shipping price tax excl */
    public $total_shipping_price_tax_excl;

    /** @var float additional shipping price tax incl */
    public $total_shipping_price_tax_incl;

    /** @var float */
    public $purchase_supplier_price;

    private  $pagination = null;

    /**
     * Returns the tax calculator associated to this order detail.
     * @since 1.5.0.1
     * @return TaxCalculator
     */
    public function getTaxCalculator(){
        return JeproshopOrderDetailModelOrderDetail::getStaticTaxCalculator($this->order_detail_id);
    }

    /**
     * Return the tax calculator associated to this order_detail
     *
     * @param int $order_detail_id
     * @return TaxCalculator
     */
    public static function getStaticTaxCalculator($order_detail_id){
        $db = JFactory::getDBO();

        $query = "SELECT order_detail_tax.*, order_detail." .$db->quoteName('tax_computation_method') . " FROM ";
        $query .= $db->quoteName('#__jeproshop_order_detail_tax') . " AS order_detail_tax LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_order_detail') . " AS  order_detail ON (order_detail.";
        $query .= $db->quoteName('order_detail_id') . " = order_detail_tax." . $db->quoteName('order_detail_id');
        $query .= ") WHERE order_detail." . $db->quoteName('order_detail_id') . " = " .(int)$order_detail_id;

        $computation_method = 1;
        $taxes = array();

        $db->setQuery($query);
        $results = $db->loadObjectList();
        if ($results){
            foreach ($results as $result){
                $taxes[] = new JeproshopTaxModelTax((int)$result->tax_id);
            }
            $computation_method = $result->tax_computation_method;
        }
        return new JeproshopTaxCalculator($taxes, $computation_method);
    }

    public function getOderDetailList($order_id){
        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_detail') . " WHERE ";
        $query .= $db->quoteName('order_id') . " = " . (int)$order_id;

        $db->setQuery($query);
        return  $db->loadObjectList();
    }

}


class JeproshopOrderReturnModelOrderReturn extends JModelLegacy
{
    /** @var integer */
    public $order_return_id;

    /** @var integer */
    public $customer_id;

    /** @var integer */
    public $order_id;

    /** @var integer */
    public $state_id;

    /** @var string message content */
    public $question;

    /** @var string Object creation date */
    public $date_add;

    /** @var string Object last modification date */
    public $date_upd;

    /**
     *
     * Get return details for one product line
     * @param $order_detail_id
     */
    public static function getProductReturnDetail($order_detail_id){
        $db = JFactory::getDBO();
        $query = "SELECT product_quantity, date_add, order_return_state_lang.name as state FROM " . $db->quoteName('#__jeproshop_order_return_detail');
        $query .= " AS order_return_detail LEFT JOIN " . $db->quoteName('#__jeproshop_order_return') . " AS order_return ON order_return.";
        $query .= "order_return_id = order_return_detail.order_return_id LEFT JOIN " . $db->quoteName('#__jeproshop_order_return_state_lang');
        $query .= " AS order_return_state_lang ON order_return_state_lang.order_return_state_id = order_return.state AND ";
        $query .= "order_return_state_lang.lang_id = " . (int)JeproshopContext::getContext()->language->lang_id;
        $query .= "	WHERE order_return_detail." . $db->quoteName('order_detail_id') . " = ".(int)$order_detail_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getOrdersReturn($customer_id, $order_id = false, $no_denied = false, JeproshopContext $context = null){
        if (!$context){	$context = JeproshopContext::getContext(); }

        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_return') . " WHERE " . $db->quoteName('customer_id');
        $query .= " = " . (int)($customer_id) . ($order_id ? " AND " .$db->quoteName('order_id') . " = ".(int)($order_id) : "");
        $query .= ($no_denied ? " AND " . $db->quoteName('state') . " != 4" : ""). " ORDER BY " . $db->quoteName('date_add') . " DESC ";

        $db->setQuery($query);
        $data = $db->loadObjectList();

        foreach ($data as $k => $order){
            $state = new JeproshopOrderReturnStateModelOrderReturnState($order->state);
            $data[$k]->state_name = $state->name[$context->language->lang_id];
            $data[$k]->type = 'Return';
            $data[$k]->tracking_number = $order->order_return_id;
            $data[$k]->can_edit = false;
            $data[$k]->reference = JeproshopOrderModelOrder::getUniqueReferenceOf($order->order_id);
        }
        return $data;
    }

    /**
     *
     * Add returned quantity to products list
     * @param array $products
     * @param int $order_id
     */
    public static function addReturnedQuantity(&$products, $order_id){
        $db = JFactory::getDBO();

        $query = "SELECT order_detail.order_detail_id, GREATEST(order_detail.product_quantity_return, IFNULL(SUM(order_return_detail.product_quantity),0)) AS qty_returned FROM " ;
        $query .= $db->quoteName('#__jeproshop_order_detail') . " AS order_detail	LEFT JOIN " . $db->quoteName('#__jeproshop_order_return_detail') . " AS order_return_detail ON ";
        $query .= "order_return_detail.order_detail_id = order_detail.order_detail_id WHERE order_detail.order_id = " . (int)$order_id . " GROUP BY order_detail.order_detail_id ";

        $db->setQuery($query);
        $details = $db->loadObjectList();
        if (!$details){ return; }

        $detail_list = array();
        foreach ($details as $detail)
            $detail_list[$detail->order_detail_id] = $detail;

        foreach ($products as &$product){
            if (isset($detail_list[$product->order_detail_id]->qty_returned)){
                $product->qty_returned = $detail_list[$product->order_detail_id]->qty_returned;
            }
        }
    }
}

class JeproshopOrderStatusModelOrderStatus extends JModelLegacy
{
    /** @var string Name */
    public $name;

    /** @var string Template name if there is any e-mail to send */
    public $template;

    /** @var boolean Send an e-mail to customer ? */
    public $send_email;

    public $module_name;

    /** @var boolean Allow customer to view and download invoice when order is at this state */
    public $invoice;

    /** @var string Display state in the specified color */
    public $color;

    public $unremovable;

    /** @var boolean Log authorization */
    public $logable;

    /** @var boolean Delivery */
    public $delivery;

    /** @var boolean Hidden */
    public $hidden;

    /** @var boolean Shipped */
    public $shipped;

    /** @var boolean Paid */
    public $paid;

    /** @var boolean True if carrier has been deleted (staying in database as deleted) */
    public $deleted = 0;

    const FLAG_NO_HIDDEN	= 1;  /* 00001 */
    const FLAG_LOGABLE		= 2;  /* 00010 */
    const FLAG_DELIVERY		= 4;  /* 00100 */
    const FLAG_SHIPPED		= 8;  /* 01000 */
    const FLAG_PAID			= 16; /* 10000 */

    /**
     * Get all available order statuses
     *
     * @param integer $lang_id Language id for status name
     * @return array Order statuses
     */
    public static function getOrderStatus($lang_id){
        $cache_id = 'jeproshop_order_status_get_order_status_'.(int)$lang_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();

            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_order_state') . " AS order_state LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_order_state_lang') . " AS order_state_lang ON (order_state.";
            $query .= $db->quoteName('order_state_id') . " = order_state_lang." . $db->quoteName('order_state_id');
            $query .= " AND order_state_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") WHERE deleted = 0 ";
            $query .= " ORDER BY " . $db->quoteName('name') . " ASC";

            $db->setQuery($query);
            $result = $db->loadObjectList();

            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Check if we can make a invoice when order is in this state
     *
     * @param integer $order_status_id State ID
     * @return boolean availability
     */
    public static function invoiceAvailable($order_status_id){
        $result = false;
        if (JeproshopSettingModelSetting::getValue('invoice_allowed')){
            $db = JFactory::getDBO();
            $query = "SELECT " . $db->quoteName('invoice') . " FROM " . $db->quoteName('#__jeproshop_order_status') . " WHERE " . $db->quoteName('order_status_id') . " = " . (int)$order_status_id;

            $db->setQuery($query);
            $result = $db->loadResult();
        }
        return (bool)$result;
    }
}
