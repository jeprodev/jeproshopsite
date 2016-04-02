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

class JeproshopCartModelCart extends JModelLegacy
{
    public $cart_id;

    public $shop_group_id;

    public $shop_id;

    /** @var integer Customer delivery address ID */
    public $address_delivery_id;

    /** @var integer Customer invoicing address ID */
    public $address_invoice_id;

    /** @var integer Customer currency ID */
    public $currency_id;

    /** @var integer Customer ID */
    public $customer_id;

    /** @var integer Guest ID */
    public $guest_id;

    /** @var integer Language ID */
    public $lang_id;

    /** @var boolean True if the customer wants a recycled package */
    public $recyclable = 0;

    /** @var boolean True if the customer wants a gift wrapping */
    public $gift = 0;

    /** @var string Gift message if specified */
    public $gift_message;

    /** @var boolean Mobile Theme */
    public $mobile_theme;

    /** @var string Object creation date */
    public $date_add;

    /** @var string secure_key */
    public $secure_key;

    /** @var integer Carrier ID */
    public $carrier_id = 0;

    /** @var string Object last modification date */
    public $date_upd;

    public $checkedTos = false;
    public $pictures;
    public $textFields;

    public $delivery_option;

    /** @var boolean Allow to separate order in multiple package in order to receive as soon as possible the available products */
    public $allow_separated_package = false;

    protected static $_nbProducts = array();
    protected static $_isVirtualCart = array();

    protected $_products = null;
    protected static $_totalWeight = array();
    protected $_taxCalculationMethod = COM_JEPROSHOP_TAX_EXCLUDED;
    protected static $_carriers = null;
    protected static $_taxes_rate = null;
    protected static $_attributesLists = array();

    const ONLY_PRODUCTS = 1;
    const ONLY_DISCOUNTS = 2;
    const BOTH = 3;
    const BOTH_WITHOUT_SHIPPING = 4;
    const ONLY_SHIPPING = 5;
    const ONLY_WRAPPING = 6;
    const ONLY_PRODUCTS_WITHOUT_SHIPPING = 7;
    const ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING = 8;

    public function __construct($cart_id = null, $lang_id = null){
        $db = JFactory::getDBO();

        if (!is_null($lang_id)){
            $this->lang_id = (int)(JeproshopLanguageModelLanguage::getLanguage($lang_id) !== false) ? $lang_id : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($cart_id){
            //Load object from database if object
            $cache_id = 'jeproshop_cart_model_cart_' . $cart_id . '_'. $lang_id;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_cart') . " AS cart WHERE " . $db->quoteName('cart_id') . " = " . (int)$lang_id;

                $db->setQuery($query);
                $cartData = $db->loadObject();

                if($cartData){
                    JeproshopCache::store($cache_id, $cartData);
                }
            }else{
                $cartData = JeproshopCache::retrieve($cache_id);
            }

            if($cartData){
                $this->cart_id = (int)$cart_id;
                foreach($cartData as $key => $value){
                    if(array_key_exists($key, $this)){ $this->{$key} = $value; }
                }
            }print_r($cartData);
        }

        if ($this->customer_id) {
            if (isset(JeproshopContext::getContext()->customer) && JeproshopContext::getContext()->customer->customer_id == $this->customer_id)
                $customer = JeproshopContext::getContext()->customer;
            else
                $customer = new JeproshopCustomerModelCustomer((int)$this->customer_id);

            if ((!$this->secure_key || $this->secure_key == '-1') && $customer->secure_key) {
                $this->secure_key = $customer->secure_key;
                $this->save();
            }
        }
        $this->_taxCalculationMethod = JeproshopGroupModelGroup::getPriceDisplayMethod(JeproshopGroupModelGroup::getCurrent()->group_id);
    }

    /**
     * Return cart products quantity
     *
     * @result integer Products quantity
     */
    public function numberOfProducts(){
        if (!$this->cart_id){
            return 0;
        }
        return JeproshopCartModelCart::getNumberOfProducts($this->cart_id);
    }

    public static function getNumberOfProducts($cart_id){
        // Must be strictly compared to NULL, or else an empty cart will bypass the cache and add dozens of queries
        if (isset(self::$_nbProducts[$cart_id]) && self::$_nbProducts[$cart_id] !== null){
            return self::$_nbProducts[$cart_id];
        }

        $db = JFactory::getDBO();
        $query = "SELECT SUM(" . $db->quoteName('quantity') . ") FROM " . $db->quoteName('#__jeproshop_cart_product');
        $query .= " WHERE " . $db->quoteName('cart_id') . " = " . (int)$cart_id;

        $db->setQuery($query);
        self::$_nbProducts[$cart_id] = (int)$db->loadObjectList();

        return self::$_nbProducts[$cart_id];
    }

    /**
     * Return custom pictures in this cart for a specified product
     *
     * @param int $product_id
     * @param int $type only return customization of this type
     * @param bool $not_in_cart only return customizations that are not in cart already
     * @return array result rows
     */
    public function getProductCustomization($product_id, $type = null, $not_in_cart = false){
        if (!JeproshopCustomization::isFeaturePublished())
            return array();

        $result = Db::getInstance()->executeS('
			SELECT cu.id_customization, cd.index, cd.value, cd.type, cu.in_cart, cu.quantity
			FROM `'._DB_PREFIX_.'customization` cu
			LEFT JOIN `'._DB_PREFIX_.'customized_data` cd ON (cu.`id_customization` = cd.`id_customization`)
			WHERE cu.id_cart = '.(int)$this->id.'
			AND cu.id_product = '.(int)$product_id.
            ($type === Product::CUSTOMIZE_FILE ? ' AND type = '.(int)Product::CUSTOMIZE_FILE : '').
            ($type === Product::CUSTOMIZE_TEXTFIELD ? ' AND type = '.(int)Product::CUSTOMIZE_TEXTFIELD : '').
            ($not_in_cart ? ' AND in_cart = 0' : '')
        );
        return $result;
    }

    public function getLastProduct() {
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('product_id') . ", " . $db->quoteName('product_attribute_id') . ", shop_id FROM ";
        $query .= $db->quoteName('#__jeproshop_cart_product') . " WHERE " . $db->quoteName('cart_id') . " = " . (int)$this->cart_id;
        $query .= "	ORDER BY " . $db->quoteName('date_add') . " DESC";

        $db->setQuery($query);

        $result = $db->loadObject();
        if ($result && isset($result->product_id) && $result->product_id){
            foreach ($this->getProducts() as $product){
                if ($result->product_id == $product->product_id
                    && (
                        !$result->product_attribute_id
                        || $result->product_attribute_id == $product->product_attribute_id )){
                    return $product;
                }
            }
        }
        return false;
    }

    /**
     * Check if cart contains only virtual products
     *
     * @param bool $strict
     * @return boolean true if is a virtual cart or false
     */
    public function isVirtualCart($strict = false) {
        if (!JeproshopProductDownloadModelProductDownload::isFeaturePublished()){ return false; }

        if (!isset(self::$_isVirtualCart[$this->cart_id])) {
            $products = $this->getProducts();
            if (!count($products)){
                return false;
            }
            $is_virtual = 1;
            foreach ($products as $product) {
                if (empty($product->is_virtual)){ $is_virtual = 0; }

            }
            self::$_isVirtualCart[$this->cart_id] = (int)$is_virtual;
        }

        return self::$_isVirtualCart[$this->cart_id];
    }

    /**
     * Return cart products
     *
     * @result array Products
     * @param bool $refresh
     * @param bool $product_id
     * @param null $country_id
     * @return array|null
     */
    public function getProducts($refresh = false, $product_id = false, $country_id = null) {
        if (!$this->cart_id){  return array(); }
        // Product cache must be strictly compared to NULL, or else an empty cart will add dozens of queries
        if ($this->_products !== null && !$refresh){
            // Return product row with specified ID if it exists
            if (is_int($product_id)){
                foreach ($this->_products as $product)
                    if ($product->product_id == $product_id)
                        return array($product);
                return array();
            }
            return $this->_products;
        }

        // Build query
        $db = JFactory::getDBO();
        $select = "";
        $left_join = "";
        if (JeproshopCustomization::isFeaturePublished()){
            $select .= ", customization." . $db->quoteName('customization_id') . ", customization." . $db->quoteName('quantity') . " AS customization_quantity";
            $left_join .= " LEFT JOIN " . $db->quoteName('#__jeproshop_customization') . " AS customization ON product." . $db->quoteName('product_id') . " = ";
            $left_join .= "customization." . $db->quoteName('product_id') . " AND cart_product." . $db->quoteName('product_attribute_id') . " = customization.";
            $left_join .= $db->quoteName('product_attribute_id') . " AND customization." . $db->quoteNam('cart_id') . " = " . (int)$this->cart_id;
        }else{
            $select .= ", NULL AS customization_quantity, NULL AS customization_id";
        }

        if (JeproshopCombinationModelCombination::isFeaturePublished()){
            $select .= ", product_attribute_shop." . $db->quoteName('price') . " AS price_attribute, product_attribute_shop." . $db->quoteName('ecotax') . " AS ecotax_attr, IF (IFNULL(product_attribute." . $db->quoteName('reference');
            $select .= ", '') = '', product." . $db->quoteName('reference') . ", product_attribute." . $db->quoteName('reference') . ") AS reference, (product." . $db->quoteName('weight') . "+ product_attribute." . $db->quoteName('weight');
            $select .= ") weight_attribute, IF (IFNULL(product_attribute." . $db->quoteName('ean13') . ", '') = '', product." . $db->quoteName('ean13') . ", product_attribute." . $db->quoteName('ean13') . ") AS ean13, IF (IFNULL(product_attribute.";
            $select .= $db->quoteName('upc') . ", '') = '', product." . $db->quoteName('upc') . ", product_attribute." . $db->quoteName('upc') . ") AS upc, product_attribute_image." . $db->quoteName('image_id') . " AS product_attribute_image_image_id,";
            $select .= " image_lang." . $db->quoteName('legend') . " AS product_attribute_image_legend, IFNULL(product_attribute_shop." . $db->quoteName('minimal_quantity') . ", product_shop." . $db->quoteName('minimal_quantity') . ")) as minimal_quantity ";


            $left_join .= $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ON product_attribute." . $db->quoteName('product_attribute_id') . " = cart_product." . $db->quoteName('product_attribute_id') . " LEFT JOIN ";
            $left_join .= $db->quoteName('#__jeproshop_product_attribute_shop') . " AS product_attribute_shop ON (product_attribute_shop." . $db->quoteName('shop_id') . " = cart_product." . $db->quoteName('shop_id') . " AND product_attribute_shop.";
            $left_join .= $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_image') . " AS product_attribute_image ON product_attribute_image.";
            $left_join .= $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON image_lang." . $db->quoteName('image_id');
            $left_join .= " = product_attribute_image." . $db->quoteName('image_id') . " AND image_lang." . $db->quoteName('lang_id') . " = " . (int)$this->lang_id;
        } else {
            $select .= ", product." . $db->quoteName('reference') . " AS reference, product." . $db->quoteName('ean13') . ", product." . $db->quoteName('upc') . " AS upc, product_shop." . $db->quoteName('minimal_quantity') . " AS minimal_quantity";
        }
        $query = "SELECT cart_product." . $db->quoteName('product_attribute_id') . ", cart_product." . $db->quoteName('product_id') . ", cart_product." . $db->quoteName('quantity');
        $query .= " AS cart_quantity, cart_product.shop_id, product_lang." . $db->quoteName('name') . ", product." . $db->quoteName('is_virtual') . ", product_lang." . $db->quoteName('short_description');
        $query .= ", product_lang." . $db->quoteName('available_now') . ", product_lang." . $db->quoteName('available_later') . ", product_shop." . $db->quoteName('default_category_id') . ", product.";
        $query .= $db->quoteName('supplier_id') . ", product." . $db->quoteName('manufacturer_id') . ", product_shop." . $db->quoteName('on_sale') . ", product_shop." . $db->quoteName('ecotax');
        $query .= ", product_shop." . $db->quoteName('additional_shipping_cost') . ", product_shop." . $db->quoteName('available_for_order') . ", product_shop." . $db->quoteName('price') . ", product_shop.";
        $query .= $db->quoteName('published') . ", product_shop." . $db->quoteName('unity') . ", product_shop." . $db->quoteName('unit_price_ratio') . ", stock." . $db->quoteName('quantity');
        $query .= " AS quantity_available, product." . $db->quoteName('width') . ", product." . $db->quoteName('height') . ", product." . $db->quoteName('depth') . ", stock." . $db->quoteName('out_of_stock');
        $query .= ", product." . $db->quoteName('weight') . ", product." . $db->quoteName('date_add') . ", product." . $db->quoteName('date_upd') . ", IFNULL(stock.quantity, 0) as quantity, product_lang.";
        $query .= $db->quoteName('link_rewrite') . ", category_lang." . $db->quoteName('link_rewrite') . " AS category, CONCAT(LPAD(cart_product." . $db->quoteName('product_id') . ", 10, 0), LPAD(IFNULL(cart_product.";
        $query .= $db->quoteName('product_attribute_id') . ", 0), 10, 0), IFNULL(cart_product." . $db->quoteName('address_delivery_id') . ", 0)) AS unique_id, cart_product.address_delivery_id, product_shop.";
        $query .= $db->quoteName('wholesale_price') . ", product_shop.advanced_stock_management, product_suppliers.product_supplier_reference supplier_reference, IFNULL(specific_price." . $db->quoteName('reduction_type');
        $query .= ", 0) AS reduction_type " . $select . " FROM " . $db->quoteName('#__jeproshop_cart_product') . " AS cart_product LEFT JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON product." . $db->quoteName('_product_id');
        $query .= " = cart_product." . $db->quoteName('product_id`') . " LEFT JOIN " . $db->quoteName('#__jeproshop_product_shop') . " AS product_shop ON (product_shop." . $db->quoteName('shop_id') . " = cart_product.";
        $query .= $db->quoteName('shop_id') . " AND product_shop." . $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang');
        $query .= " AS product_lang ON (product." . $db->quoteName('product_id') . " = product_lang." . $db->quoteName('product_id') . " AND product_lang." . $db->quoteName('lang_id') . " = " . (int)$this->lang_id ;
        $query .= JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang', 'cart_product.' . $db->quoteName('shop_id')) . "é LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang";
        $query .= " product_shop." . $db->quoteName('default_category_id') . " = category_lang." . $db->quoteName('category_id') . " AND category_lang." . $db->quoteName('lang_id') . " = " . (int)$this->lang_id;
        $query .= JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang', 'cart_product.' . $db->quoteName('shop_id')) .  "LEFT JOIN " . $db->quoteName('#__jeproshop_product_supplier') . " AS product_supplier ";
        $query .= " ON (product_supplier.".$db->quoteName('product_id') . " = cart_product." . $db->quoteName('product_id') . " AND product_supplier." . $db->quoteName('product_attribute_id') . " = cart_product.";
        $query .= $db->quoteName('product_attribute_id') . " AND product_supplier." . $db->quoteName('supplier_id') . " = product." . $db->quoteName('supplier_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_specific_price');
        $query .= " AS specific_price ON specific_price." . $db->quoteName('product_id') . " = cart_product." . $db->quoteName('product_id') . " JOIN " . JeproshopProductModelProduct::sqlStock('cart_product') . $left_join . " WHERE ";
        $query .= " cart_product." . $db->quopteName('cart_id') . "= " . (int)$this->cart_id . ($product_id ? " AND cart_product." . $db->quoteName('product_id') . " = " . (int)$product_id : "") . " AND product." . $db->quoteName('product_id');
        $query .= " IS NOT NULL GROUP BY unique_id ORDER BY cart_product." . $db->quoteName('date_add') . ", product." . $db->quoteName('product_id') . ", cart_product." . $db->quoteName('product_attribute_id') . " ASC";

        $db->setQuery($query);

        $result = $db->loadObjectList();

        // Reset the cache before the following return, or else an empty cart will add dozens of queries
        $products_ids = array();
        $product_attribute_ids = array();
        if ($result){
            foreach ($result as $row){
                $products_ids[] = $row->product_id;
                $product_attribute_ids[] = $row->product_attribute_id;
            }
        }
        // Thus you can avoid one query per product, because there will be only one query for all the products of the cart
        JeproshopProductModelProduct::cacheProductsFeatures($products_ids);
        JeproshopCartModelCart::cacheSomeAttributesLists($product_attribute_ids, $this->lang_id);

        $this->_products = array();
        if (empty($result))
            return array();

        $cart_shop_context = JeproshopContext::getContext()->cloneContext();
        foreach ($result as &$row){
            if (isset($row->ecotax_attr) && $row->ecotax_attr > 0){
                $row->ecotax = (float)$row->ecotax_attr;
            }
            $row->stock_quantity = (int)$row->quantity;
            // for compatibility with 1.2 themes
            $row->quantity = (int)$row->cart_quantity;

            if (isset($row->product_attribute_id) && (int)$row->product_attribute_id && isset($row->weight_attribute))
                $row->weight = (float)$row->weight_attribute;

            if (JeproshopSettingModelSeting::getValue('PS_TAX_ADDRESS_TYPE') == 'address_invoice_id')
                $address_id = (int)$this->address_invoice_id;
            else
                $address_id = (int)$row->address_delivery_id;
            if (!Address::addressExists($address_id))
                $address_id = null;

            if ($cart_shop_context->shop->shop_id != $row['id_shop'])
                $cart_shop_context->shop = new Shop((int)$row['id_shop']);

            $specific_price_output = null;
            $null = null;
            if ($this->_taxCalculationMethod == PS_TAX_EXC)
            {
                $row->price = JeproshopProductModelProduct::getStaticPrice( (int)$row->product_id, false, isset($row->product_attribute_id) ? (int)$row->product_attribute_id : null,
                    2, null, false, true, (int)$row->cart_quantity, false, ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id, ((int)$address_id ? (int)$address_id : null),
                    $specific_price_output, true, true,  $cart_shop_context
                ); // Here taxes are computed only once the quantity has been applied to the product price

                $row->price_wt = JeproshopProductModelProduct::getStaticPrice( (int)$row->product_id, true, isset($row->product_attribute_id) ? (int)$row->product_attribute_id : null,
                    2, null, false, true, (int)$row->cart_quantity, false, ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id, ((int)$address_id ? (int)$address_id : null),
                    $null, true, true, $cart_shop_context
                );

                $tax_rate = JeproshopTaxModelTax::getProductTaxRate((int)$row->product_id, (int)$address_id);

                $row->total_wt =JeproshopValidator::roundPrice($row->price * (float)$row->cart_quantity * (1 + (float)$tax_rate / 100), 2);
                $row->total = $row->price * (int)$row->cart_quantity;
            } else {
                $row->price = JreproshopProductModelProduct::getStaticPrice( (int)$row->product_id, false, (int)$row->product_attribute_id,
                    2, null, false, true, $row->cart_quantity, false, ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id,
                    ((int)$address_id ? (int)$address_id : null), $specific_price_output, true, true, $cart_shop_context
                );

                $row->price_wt = JeproshopProductModelProduct::getStaticPrice(
                    (int)$row['id_product'],
                    true,
                    (int)$row['id_product_attribute'],
                    2,
                    null,
                    false,
                    true,
                    $row['cart_quantity'],
                    false,
                    ((int)$this->customer_id ? (int)$this->customer_id : null),
                    (int)$this->cart_id,
                    ((int)$address_id ? (int)$address_id : null),
                    $null,
                    true,
                    true,
                    $cart_shop_context
                );

                // In case when you use QuantityDiscount, getPriceStatic() can be return more of 2 decimals
                $row->price_wt = JeproshopValidator::roundPrice($row->price_wt, 2);
                $row->total_wt = $row->price_wt * (int)$row->cart_quantity;
                $row->total = JeproshopValidator::roundPrice($row['price'] * (int)$row['cart_quantity'], 2);
                $row->description_short = Tools::nl2br($row['description_short']);
            }

            if (!isset($row['pai_id_image']) || $row['pai_id_image'] == 0)
            {
                $cache_id = 'Cart::getProducts_'.'-pai_id_image-'.(int)$row['id_product'].'-'.(int)$this->lang_id .'-'.(int)$row['id_shop'];
                if (!Cache::isStored($cache_id))
                {
                    $row2 = Db::getInstance()->getRow('
						SELECT image_shop.`id_image` id_image, il.`legend`
						FROM `'._DB_PREFIX_.'image` i
						JOIN `'._DB_PREFIX_.'image_shop` image_shop ON (i.id_image = image_shop.id_image AND image_shop.cover=1 AND image_shop.id_shop='.(int)$row['id_shop'].')
						LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (image_shop.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$this->lang_id .')
						WHERE i.`id_product` = '.(int)$row['id_product'].' AND image_shop.`cover` = 1'
                    );
                    Cache::store($cache_id, $row2);
                }
                $row2 = Cache::retrieve($cache_id);
                if (!$row2)
                    $row2 = array('id_image' => false, 'legend' => false);
                else
                    $row = array_merge($row, $row2);
            } else {
                $row->image_id = $row['pai_id_image'];
                $row->legend = $row['pai_legend'];
            }

            $row->reduction_applies = ($specific_price_output && (float)$specific_price_output->reduction);
            $row->quantity_discount_applies = ($specific_price_output && $row->cart_quantity >= (int)$specific_price_output->from_quantity);
            $row->image_id = JeproshopProductModelProduct::defineProductImage($row, $this->lang_id);
            $row->allow_oosp = JeproshopProductModelProduct::isAvailableWhenOutOfStock($row->out_of_stock);
            $row->features = JeproshopProductModelProduct::getStaticFeatures((int)$row->product_id);

            if (array_key_exists($row->product_attribute_id .'_'.$this->lang_id, self::$_attributesLists))
                $row = array_merge($row, self::$_attributesLists[$row->product_attribute_id.'_'.$this->lang_id]);

            $row = JeproshopProductModelProduct::getTaxesInformations($row, $cart_shop_context);

            $this->_products[] = $row;
        }

        return $this->_products;
    }

    /**
     * Update product quantity
     *
     * @param integer $quantity Quantity to add (or substract)
     * @param $product_id
     * @param null $product_attribute_id
     * @param bool $customization_id
     * @param string $operator Indicate if quantity must be increased or decreased
     * @param int $address_delivery_id
     * @param JeproshopShopModelShop $shop
     * @param bool $auto_add_cart_rule
     * @return bool|int
     * @internal param int $id_product Product ID
     * @internal param int $id_product_attribute Attribute ID if needed
     */
    public function updateQuantity($quantity, $product_id, $product_attribute_id = null, $customization_id = false, $operator = 'up', $address_delivery_id = 0, JeproshopShopModelShop $shop = null, $auto_add_cart_rule = true){
        if (!$shop)
            $shop = JeproshopContext::getContext()->shop;

        $db = JFactory::getDBO();
        if (JeproshopContext::getContext()->customer->customer_id)
        {
            if ($address_delivery_id == 0 && (int)$this->address_delivery_id) // The $id_address_delivery is null, use the cart delivery address
                $address_delivery_id = $this->address_delivery_id;
            elseif ($address_delivery_id == 0) // The $id_address_delivery is null, get the default customer address
                $address_delivery_id = (int)JeproshopAddressModelAddress::getCustomerFirstAddressId((int)JeproshopContext::getContext()->customer->customer_id);
            elseif (!JeproshopCustomerModelCustomer::customerHasAddress(JeproshopContext::getContext()->customer->customer_id, $address_delivery_id)) // The $id_address_delivery must be linked with customer
                $address_delivery_id = 0;
        }

        $quantity = (int)$quantity;
        $product_id = (int)$product_id;
        $product_attribute_id = (int)$product_attribute_id;
        $product = new JeproshopProductModelProduct($product_id, false, JeproshopSettingModelSetting::getValue('default_lang'), $shop->shop_id);

        if ($product_attribute_id){
            $combination = new JeproshopCombinationModelCombination((int)$product_attribute_id);
            if ($combination->product_id != $product_id)
                return false;
        }

        /* If we have a product combination, the minimal quantity is set with the one of this combination */
        if (!empty($product_attribute_id))
            $minimal_quantity = (int)JeproshopAttributeModelAttribute::getAttributeMinimalQty($product_attribute_id);
        else
            $minimal_quantity = (int)$product->minimal_quantity;

        if (!JeproshopValidator::isLoadedObject($product, 'product_id'))
            die(Tools::displayError());

        if (isset(self::$_nbProducts[$this->cart_id]))
            unset(self::$_nbProducts[$this->cart_id]);

        if (isset(self::$_totalWeight[$this->cart_id]))
            unset(self::$_totalWeight[$this->cart_id]);

        if ((int)$quantity <= 0)
            return $this->deleteProduct($product_id, $product_attribute_id, (int)$customization_id);
        elseif (!$product->available_for_order || JeproshopSettingModelSeting::getValue('catalog_mode'))
            return false;
        else
        {
            /* Check if the product is already in the cart */
            $result = $this->containsProduct($product_id, $product_attribute_id, (int)$customization_id, (int)$address_delivery_id);

            /* Update quantity if product already exist */
            if ($result){
                if ($operator == 'up'){
                    $query = "SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity FROM " . $db->quoteName('#__jeproshop_product') . " AS product" . JeproshopProductModelProduct::sqlStock('p', $product_attribute_id, true, $shop);
                    $query .= " WHERE product.product_id = " . $product_id;

                    $db->setQuery($query);

                    $result2 = $db->loadObject();
                    $product_qty = (int)$result2->quantity;
                    // Quantity for product pack
                    if (JeproshopProductPack::isPack($product_id)) {
                        $product_qty = JeproshopProductPack::getQuantity($product_id, $product_attribute_id);
                    }
                    $new_qty = (int)$result->quantity + (int)$quantity;
                    $qty = '+ '.(int)$quantity;

                    if (!JeproshopProductModelProduct::isAvailableWhenOutOfStock((int)$result2->out_of_stock)) {
                        if ($new_qty > $product_qty) {
                            return false;
                        }
                    }
                }else if ($operator == 'down'){
                    $qty = '- '.(int)$quantity;
                    $new_qty = (int)$result->quantity - (int)$quantity;
                    if ($new_qty < $minimal_quantity && $minimal_quantity > 1)
                        return -1;
                }else {
                    return false;
                }

                /* Delete product from cart */
                if ($new_qty <= 0) {
                    return $this->deleteProduct((int)$product_id, (int)$product_attribute_id, (int)$customization_id);
                }else if ($new_qty < $minimal_quantity) {
                    return -1;
                }else {
                    $query = "UPDATE " . $db->quoteName('#__jeproshop_cart_product') . " SET " . $db->quoteName('quantity') . " = " . $db->quoteName('quantity') . $qty . ", ";
                    $query .= $db->quoteName('date_add') . " = NOW() WHERE " . $db->quoteName('product_id') . " = " . (int)$product_id;
                    $query .= (!empty($product_attribute_id) ? " AND " . $db->quoteName('product_attribute_id') . " = " . (int)$product_attribute_id : "") . " AND " . $db->quoteName('cart_id') . " = " . (int)$this->cart_id ;
                    $query .=(JeproshopSettingModelSeting::getValue('allow_multi_shipping') && $this->isMultiAddressDelivery() ? " AND " . $db->quoteName('address_delivery_id') . " = " . (int)$address_delivery_id : "") . " LIMIT 1";

                    $db->setQuery($query);
                    $db->query();
                }
            }
            /* Add product to the cart */
            elseif ($operator == 'up'){
                $sql = 'SELECT stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity
						FROM '._DB_PREFIX_.'product p
						'.Product::sqlStock('p', $product_attribute_id, true, $shop).'
						WHERE p.id_product = '.$product_id;

                $result2 = Db::getInstance()->getRow($sql);

                // Quantity for product pack
                if (Pack::isPack($product_id))
                    $result2['quantity'] = Pack::getQuantity($product_id, $product_attribute_id);

                if (!Product::isAvailableWhenOutOfStock((int)$result2['out_of_stock']))
                    if ((int)$quantity > $result2['quantity'])
                        return false;

                if ((int)$quantity < $minimal_quantity)
                    return -1;

                $result_add = Db::getInstance()->insert('cart_product', array(
                    'id_product' => 			(int)$product_id,
                    'id_product_attribute' => 	(int)$product_attribute_id,
                    'id_cart' => 				(int)$this->cart_id,
                    'id_address_delivery' => 	(int)$address_delivery_id,
                    'id_shop' => 				$shop->shop_id,
                    'quantity' => 				(int)$quantity,
                    'date_add' => 				date('Y-m-d H:i:s')
                ));

                if (!$result_add)
                    return false;
            }
        }

        // refresh cache of self::_products
        $this->_products = $this->getProducts(true);
        $this->update(true);
        $context = Context::getContext()->cloneContext();
        $context->cart = $this;
        Cache::clean('getContextualValue_*');
        if ($auto_add_cart_rule)
            CartRule::autoAddToCart($context);

        if ($product->customizable)
            return $this->updateCustomizationQuantity((int)$quantity, (int)$customization_id, (int)$product_id, (int)$product_attribute_id, (int)$address_delivery_id, $operator);
        else
            return true;
    }

    /**
     * Save current object to database (add or update)
     *
     * @param bool $null_values
     * @param bool $auto_date
     * @return boolean Insertion result
     */
    public function save($null_values = false, $auto_date = true){
        return (int)$this->cart_id > 0 ? $this->update($null_values) : $this->add($auto_date, $null_values);
    }

    public function add($auto_date = true, $null_values = false){
        $db = JFactory::getDBO();
        if(!$this->lang_id){
            $this->lang_id = JeproshopSettingModelSetting::getValue('default_lang');
        }
        if(!$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }
        $shop = new JeproshopShopModelShop($this->shop_id);

        if(isset($this->cart_id) && !$this->force_id){ unset($this->cart_id); }

        // Automatically fill dates
        if ($auto_date && property_exists($this, 'date_add'))
            $this->date_add = date('Y-m-d H:i:s');
        if ($auto_date && property_exists($this, 'date_upd'))
            $this->date_upd = date('Y-m-d H:i:s');
/*
        if(JeproshopShopModelShop::isTableAssociated('cart')){
            $shop_list_id = JeproshopShopModelShop::getContextShopListId();
            if(count($this->shop_list_id) > 0){
                $shop_list_id = $this->shop_list_id;
            }
        }

        //DataBase insertion
        if(JeproshopShopModelShop::checkDefaultShopId('cart')){
            $this->default_shop_id = min($shop_list_id);
        }*/

        $query = "INSERT INTO " . $db->quoteName('#__jeproshop_cart') . "(" . $db->quoteName('shop_group_id') . ", " . $db->quoteName('shop_id') . ", ";
        $query .= $db->quoteName('lang_id') . ", " . $db->quoteName('customer_id') . ", " . $db->quoteName('date_add') . ", " . $db->quoteName('date_upd');
        $query .= ") VALUES ( " . (int)$shop->shop_group_id . ", " . (int)$this->shop_id . ", " . (int)$this->lang_id . ", " . (int)$this->customer_id . ", ";
        $query .= $db->quote($this->date_add) . ", " . $db->quote($this->date_upd) . ")";

        $db->setQuery($query);
        $return = $db->query();
        if(!$return){ return false; }

        $this->cart_id = $db->insertId();

        return $return;

    }

    public function update(){

    }

    public function getCartRules($filter = JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_ALL) {
        // If the cart has not been saved, then there can't be any cart rule applied
        if (!JeproshopCartRuleModelCartRule::isFeaturePublished() || !$this->cart_id){ return array(); }


        $cache_key = 'jeproshop_cart_getCartRules_'. $this->cart_id . '_' . $filter;
        if (!JeproshopCache::isStored($cache_key)){
            $db = JFactory::getDBO();

            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_cart_cart_rule') . " AS cd LEFT JOIN " . $db->quoteName('#__jeproshop_cart_rule');
            $query .= " AS cart_rule ON cd." . $db->quoteName('cart_rule_id') . " = cart_rule." . $db->quoteName('cart_rule_id') . " LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_cart_rule_lang') . " AS cart_rule_lang ON ( cd." . $db->quoteName('cart_rule_id') . " = cart_rule_lang.";
            $query .= $db->quoteName('cart_rule_id') . " AND cart_rule_lang.lang_id = " .(int)$this->lang_id . ") WHERE " . $db->quoteName('cart_id') . " = " .(int)$this->cart_id;
            $query .= ($filter == JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_SHIPPING ? " AND free_shipping = 1" : ""). ($filter == JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_GIFT ? " AND gift_product != 0" : "");
            $query .= ($filter == JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_REDUCTION ? " AND (reduction_percent != 0 OR reduction_amount != 0)"  : "") . " ORDER by cart_rule.priority ASC ";

            $db->setQuery($query);
            $result = $db->loadObjectList();
            JeproshopCache::store($cache_key, $result);
        }
        $result = JeproshopCache::retrieve($cache_key);

        // Define virtual context to prevent case where the cart is not the in the global context
        $virtual_context = JeproshopContext::getContext()->cloneContext();
        $virtual_context->cart = $this;

        foreach ($result as &$row){
            $row->obj = new JeproshopCartRuleModelCartRule($row->cart_rule_id, (int)$this->lang_id);
            $row->value_real = $row->obj->getContextualValue(true, $virtual_context, $filter);
            $row->value_tax_exc = $row->obj->getContextualValue(false, $virtual_context, $filter);

            // Retro compatibility < 1.5.0.2
            $row->discount_id = $row->cart_rule_id;
            $row->description = $row->name;
        }

        return $result;
    }

    public function checkQuantities(){
        if (JeproshopSettingModelSetting::getValue('catalog_mode'))
            return false;

        foreach ($this->getProducts() as $product){
            if (!$product->published || !$product->available_for_order || (!$product->allow_out_of_stock_ordering && $product->stock_quantity < $product->cart_quantity)){
                return false;
            }
        }
        return true;
    }

    /**
     * This function returns the total cart amount
     *
     * Possible values for $type:
     * JeproshopCartModelCart::ONLY_PRODUCTS
     * JeproshopCartModelCart::ONLY_DISCOUNTS
     * JeproshopCartModelCart::BOTH
     * JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING
     * JeproshopCartModelCart::ONLY_SHIPPING
     * JeproshopCartModelCart::ONLY_WRAPPING
     * JeproshopCartModelCart::ONLY_PRODUCTS_WITHOUT_SHIPPING
     * JeproshopCartModelCart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING
     *
     * @param bool $with_taxes
     * @param integer $type Total type
     * @param null $products
     * @param null $carrier_id
     * @param boolean $use_cache Allow using cache of the method CartRule::getContextualValue
     * @internal param bool $withTaxes With or without taxes
     * @return float Order total
     */
    public function getOrderTotal($with_taxes = true, $type = JeproshopCartModelCart::BOTH, $products = null, $carrier_id = null, $use_cache = true){
        if (!$this->cart_id){ return 0; }

        $type = (int)$type;
        $array_type = array(
            JeproshopCartModelCart::ONLY_PRODUCTS, JeproshopCartModelCart::ONLY_DISCOUNTS, JeproshopCartModelCart::BOTH,
            JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING, JeproshopCartModelCart::ONLY_SHIPPING, JeproshopCartModelCart::ONLY_WRAPPING,
            JeproshopCartModelCart::ONLY_PRODUCTS_WITHOUT_SHIPPING, JeproshopCartModelCart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING,
        );

        // Define virtual context to prevent case where the cart is not the in the global context
        $virtual_context = JeproshopContext::getContext()->cloneContext();
        $virtual_context->cart = $this;

        if (!in_array($type, $array_type))
            die(Tools::displayError());

        $with_shipping = in_array($type, array(JeproshopCartModelCart::BOTH, JeproshopCartModelCart::ONLY_SHIPPING));

        // if cart rules are not used
        if ($type == JeproshopCartModelCart::ONLY_DISCOUNTS && !JeproshopCartRuleModelCartRule::isFeaturePublished()){ return 0; }

        // no shipping cost if is a cart with only virtual products
        $virtual = $this->isVirtualCart();
        if ($virtual && $type == JeproshopCartModelCart::ONLY_SHIPPING){ return 0; }

        if ($virtual && $type == JeproshopCartModelCart::BOTH)
            $type = JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING;

        if ($with_shipping || $type == JeproshopCartModelCart::ONLY_DISCOUNTS){
            if (is_null($products) && is_null($carrier_id))
                $shipping_fees = $this->getTotalShippingCost(null, (boolean)$with_taxes);
            else
                $shipping_fees = $this->getPackageShippingCost($carrier_id, (bool)$with_taxes, null, $products);
        }
        else
            $shipping_fees = 0;

        if ($type == JeproshopCartModelCart::ONLY_SHIPPING)
            return $shipping_fees;

        if ($type == JeproshopCartModelCart::ONLY_PRODUCTS_WITHOUT_SHIPPING)
            $type = JeproshopCartModelCart::ONLY_PRODUCTS;

        $param_product = true;
        if (is_null($products))
        {
            $param_product = false;
            $products = $this->getProducts();
        }

        if ($type == JeproshopCartModelCart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING)
        {
            foreach ($products as $key => $product)
                if ($product['is_virtual'])
                    unset($products[$key]);
            $type = JeproshopCartModelCart::ONLY_PRODUCTS;
        }

        $order_total = 0;
        if (JeproshopTaxModelTax::taxExcludedOption())
            $with_taxes = false;

        foreach ($products as $product) // products refer to the cart details
        {
            if ($virtual_context->shop->shop_id != $product->shop_id)
                $virtual_context->shop = new JeproshopShopModelShop((int)$product->shop_id);

            if (JeproshopSettingModelSetting::getValue('tax_address_type') == 'address_invoice_id')
                $address_id = (int)$this->address_invoice_id;
            else
                $address_id = (int)$product->address_delivery_id; // Get delivery address of the product from the cart
            if (!JeproshopAddressModelAddress::addressExists($address_id))
                $address_id = null;

            if ($this->_taxCalculationMethod == COM_JEPROSHOP_TAX_EXCLUDED){
                $null = null;
                // Here taxes are computed only once the quantity has been applied to the product price
                $price = JeproshopProductModelProduct::getStaticPrice(
                    (int)$product->product_id, false, (int)$product->product_attribute_id, 2, null, false, true, $product->cart_quantity, false,
                    (int)$this->customer_id ? (int)$this->customer_id : null, (int)$this->cart_id, $address_id, $null, true, true, $virtual_context
                );

                $total_ecotax = $product->ecotax * (int)$product->cart_quantity;
                $total_price = $price * (int)$product->cart_quantity;

                if ($with_taxes) {
                    $product_tax_rate = (float)JeproshopTaxModelTax::getProductTaxRate((int)$product->product_id, (int)$address_id, $virtual_context);
                    $product_eco_tax_rate = JeproshopTaxModelTax::getProductEcotaxRate((int)$address_id);

                    $total_price = ($total_price - $total_ecotax) * (1 + $product_tax_rate / 100);
                    $total_ecotax = $total_ecotax * (1 + $product_eco_tax_rate / 100);
                    $total_price = JeproshopValidator::roundPrice($total_price + $total_ecotax, 2);
                }
            } else{
                $null = null;
                if ($with_taxes)
                    $price = JeproshopProductModelProduct::getStaticPrice(
                        (int)$product->product_id, true, (int)$product->product_attribute_id, 2, null, false, true, $product->cart_quantity, false,
                        ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id, ((int)$address_id ? (int)$address_id : null),
                        $null, true, true, $virtual_context
                    );
                else
                    $price = JeproshopProductModelProduct::getStaticPrice(
                        (int)$product->product_id, false, (int)$product->product_attribute_id, 2, null, false, true, $product->cart_quantity, false,
                        ((int)$this->customer_id ? (int)$this->customer_id : null), (int)$this->cart_id, ((int)$address_id ? (int)$address_id : null),
                        $null, true, true, $virtual_context
                    );

                $total_price = JeproshopValidator::roundPrice($price * (int)$product->cart_quantity, 2);
            }
            $order_total += $total_price;
        }

        $order_total_products = $order_total;

        if ($type == JeproshopCartModelCart::ONLY_DISCOUNTS){ $order_total = 0; }

        // Wrapping Fees
        $wrapping_fees = 0;
        if ($this->gift)
            $wrapping_fees = JeproshopValidator::convertPrice(JeproshopValidator::roundPrice($this->getGiftWrappingPrice($with_taxes), 2), JeproshopCurrencyModelCurrency::getCurrencyInstance((int)$this->currency_id));
        if ($type == JeproshopCartModelCart::ONLY_WRAPPING)
            return $wrapping_fees;

        $order_total_discount = 0;
        if (!in_array($type, array(JeproshopCartModelCart::ONLY_SHIPPING, JeproshopCartModelCart::ONLY_PRODUCTS)) && JeproshopCartRuleModelCartRule::isFeaturePublished()){
            // First, retrieve the cart rules associated to this "getOrderTotal"
            if ($with_shipping || $type == JeproshopCartModelCart::ONLY_DISCOUNTS)
                $cart_rules = $this->getCartRules(JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_ALL);
            else {
                $cart_rules = $this->getCartRules(JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_REDUCTION);
                // Cart Rules array are merged manually in order to avoid doubles
                foreach ($this->getCartRules(JeproshopCartRuleModelCartRule::JEPROSHOP_FILTER_ACTION_GIFT) as $tmp_cart_rule) {
                    $flag = false;
                    foreach ($cart_rules as $cart_rule){
                        if ($tmp_cart_rule->cart_rule_id == $cart_rule->cart_rule_id)
                            $flag = true;
                    }
                    if (!$flag)
                        $cart_rules[] = $tmp_cart_rule;
                }
            }

            $address_delivery_id = 0;
            if (isset($products[0]))
                $address_delivery_id = (is_null($products) ? $this->address_delivery_id : $products[0]->address_delivery_id);
            $package = array('carrier_id' => $carrier_id, 'address_id' => $address_delivery_id, 'products' => $products);

            // Then, calculate the contextual value for each one
            foreach ($cart_rules as $cart_rule)
            {
                // If the cart rule offers free shipping, add the shipping cost
                if (($with_shipping || $type == Cart::ONLY_DISCOUNTS) && $cart_rule->obj->free_shipping)
                    $order_total_discount += JeproshopValidator::roundPrice($cart_rule->obj->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_SHIPPING, ($param_product ? $package : null), $use_cache), 2);

                // If the cart rule is a free gift, then add the free gift value only if the gift is in this package
                if ((int)$cart_rule['obj']->gift_product)
                {
                    $in_order = false;
                    if (is_null($products))
                        $in_order = true;
                    else
                        foreach ($products as $product)
                            if ($cart_rule['obj']->gift_product == $product['id_product'] && $cart_rule['obj']->gift_product_attribute == $product['id_product_attribute'])
                                $in_order = true;

                    if ($in_order)
                        $order_total_discount += $cart_rule->obj->getContextualValue($with_taxes, $virtual_context, JeproshopCartRuleModelCartRule::FILTER_ACTION_GIFT, $package, $use_cache);
                }

                // If the cart rule offers a reduction, the amount is prorated (with the products in the package)
                if ($cart_rule['obj']->reduction_percent > 0 || $cart_rule['obj']->reduction_amount > 0)
                    $order_total_discount += JeproshopValidator::roundPrice($cart_rule['obj']->getContextualValue($with_taxes, $virtual_context, CartRule::FILTER_ACTION_REDUCTION, $package, $use_cache), 2);
            }
            $order_total_discount = min(JeproshopValidator::roundPrice($order_total_discount, 2), $wrapping_fees + $order_total_products + $shipping_fees);
            $order_total -= $order_total_discount;
        }

        if ($type == JeproshopCartModelCart::BOTH)
            $order_total += $shipping_fees + $wrapping_fees;

        if ($order_total < 0 && $type != JeproshopCartModelCart::ONLY_DISCOUNTS)
            return 0;

        if ($type == JeproshopCartModelCart::ONLY_DISCOUNTS)
            return $order_total_discount;

        return JeproshopValidator::roundPrice((float)$order_total, 2);
    }

    /**
     * Update products cart address delivery with the address delivery of the cart
     */
    public function setNoMultiShipping(){
        $emptyCache = false;
        $db = JFactory::getDBO();
        if (JeproshopSettingModelSetting::getValue('allow_multi_shipping')){
            // Upgrading quantities
            $query = "SELECT sum("  . $db->quoteName('quantity') . ") AS quantity, product_id, product_attribute_id, count(*) as count FROM ";
            $query .= $db->quoteName('#__jeproshop_cart_product') . " WHERE " . $db->quoteName('cart_id') . " = " . (int)$this->cart_id . " AND " ;
            $query .= $db->quoteName('shop_id') . " = " . (int)$this->shop_id . " GROUP BY product_id, product_attribute_id HAVING count > 1";

            $db->setQuery($query);
            $products = $db->loadObjectList();

            foreach ($products as $product){
                $query = "UPDATE " . $db->quoteName('#__jeproshop_cart_product') . " SET " . $db->quoteName('quantity') . " = " . $product->quantity;
                $query .= "	WHERE " . $db->quoteName('cart_id')  . " = ".(int)$this->cart_id . " AND " . $db->quoteName('shop_id') . " = " . (int)$this->shop_id ;
                $query .= " AND product_id = " . $product->product_id . " AND product_attribute_id = " . $product->product_attribute_id;
                $db->setQuery($query);
                if ($db->query())
                    $emptyCache = true;
            }

            // Merging multiple lines
            $query = "DELETE cart_product_1 FROM " . $db->quoteName('#__jeproshop_cart_product') . " AS cart_product_1 INNER JOIN ";
            $query .=  $db->quoteName('#__jeproshop_cart_product') . " AS cart_product_2 ON ((cart_product_1.cart_id = cart_product_2.";
            $query .= "cart_id) AND (cart_product_1.product_id = cart_product_2.product_id) AND (cart_product_1.product_attribute_id = ";
            $query .= "cart_product_2.product_attribute_id) AND (cart_product_1.address_delivery_id <> cart_product_2.address_delivery_id) ";
            $query .= " AND (cart_product_1.date_add > cart_product_2.date_add) )";
            $db->setQuery($query);
            $db->query();
        }

        // Update delivery address for each product line
        $query = "UPDATE " . $db->quoteName('#__jeproshop_cart_product') . " SET " . $db->quoteName('address_delivery_id') . " = ( SELECT ";
        $query .=  $db->quoteName('address_delivery_id') . " FROM " . $db->quoteName('#__jeproshop_cart') . " WHERE " .  $db->quoteName('cart_id');
        $query .= " = " . (int)$this->cart_id . " AND " .  $db->quoteName('shop_id') . " = " . (int)$this->shop_id . ") WHERE " .  $db->quoteName('cart_id');
        $query .= " = " . (int)$this->cart_id . (JeproshopSettingModelSetting::getValue('allow_multi_shipping') ? " AND " .  $db->quoteName('shop_id') . " = " .(int)$this->shop_id : "");

        $db->setQuery($query);

        $cache_id = 'jeproshop_cart_set_no_multi_shipping'.(int)$this->cart_id.'_'.(int)$this->shop_id .((isset($this->address_delivery_id) && $this->address_delivery_id) ? '-'.(int)$this->address_delivery_id : '');
        if (!JeproshopCache::isStored($cache_id)){
            $db->setQuery($query);
            if ($result = (bool)$db->query())
                $emptyCache = true;
            JeproshopCache::store($cache_id, $result);
        }

        if (JeproshopCustomization::isFeaturePublished()){
            //Db::getInstance()->execute(
			$query = " UPDATE " . $db->quoteName('#__jeproshop_customization') . " SET " . $db->quoteName('address_delivery_id') . " = ( SELECT ";
            $query .= $db->quoteName('address_delivery_id') . " FROM " . $db->quoteName('#__jeproshop_cart') . " WHERE " . $db->quoteName('cart_id');
            $query .= " = " . (int)$this->cart_id . " ) WHERE " .  $db->quoteName('cart_id') . " = " .(int)$this->cart_id;

            $db->setQuery($query);
            $db->query();
        }
        if ($emptyCache){
            $this->_products = null;
        }
    }

    /**
     * Return package shipping cost
     *
     * @param integer $carrier_id Carrier ID (default : current carrier)
     * @param boolean $use_tax
     * @param JeproshopCountryModelCountry $default_country
     * @param Array $product_list
     * @param array $product_list List of product concerned by the shipping. If null, all the product of the cart are used to calculate the shipping cost
     *
     * @return float Shipping total
     */
    public function getPackageShippingCost($carrier_id = null, $use_tax = true, JeproshopCountryModelCountry $default_country = null, $product_list = null, $zone_id = null){
        if ($this->isVirtualCart())
            return 0;

        if (!$default_country)
            $default_country = JeproshopContext::getContext()->country;

        $complete_product_list = $this->getProducts();
        if (is_null($product_list))
            $products = $complete_product_list;
        else
            $products = $product_list;

        if (JeproshopSettingModelSetting::getValue('tax_address_type') == 'address_invoice_id') {
            $address_id = (int)$this->address_invoice_id;
        }elseif (count($product_list)){
            $prod = current($product_list);
            $address_id = (int)$prod->address_delivery_id;
        } else {
            $address_id = null;
        }
        if (!JeproshopAddressModelAddress::addressExists($address_id)){   $address_id = null; }

        $cache_id = 'get_package_shipping_cost_'.(int)$this->cart_id . '_' . (int)$address_id.'_'.(int)$carrier_id . '_' .(int)$use_tax.'_'.(int)$default_country->country_id;
        if ($products) {
            foreach ($products as $product)
                $cache_id .=  '_' . (int)$product->product_id . '_' . (int)$product->product_attribute_id;
        }

        if (JeproshopCache::isStored($cache_id))
            return JeproshopCache::retrieve($cache_id);

        // Order total in default currency without fees
        $order_total = $this->getOrderTotal(true, JeproshopCartModelCart::ONLY_PHYSICAL_PRODUCTS_WITHOUT_SHIPPING, $product_list);

        // Start with shipping cost at 0
        $shipping_cost = 0;
        // If no product added, return 0
        if (!count($products)) {
            JeproshopCache::store($cache_id, $shipping_cost);
            return $shipping_cost;
        }

        if (!isset($zone_id)){
            // Get id zone
            if (!$this->isMultiAddressDelivery() && isset($this->address_delivery_id) // Be carefull, id_address_delivery is not usefull one 1.5
                && $this->address_delivery_id && JeproshopCustomerModelCustomer::customerHasAddress($this->customer_id, $this->address_delivery_id ))
                $zone_id = JeproshopAddressModelAddress::getZoneIdByAddressId((int)$this->address_delivery_id);
            else
            {
                if (!JeproshopValidator::isLoadedObject($default_country, 'country_id'))
                    $default_country = new Country(JeproshopSettingModelSeting::getValue('PS_COUNTRY_DEFAULT'), JeproshopSettingModelSeting::getValue('PS_LANG_DEFAULT'));

                $zone_id = (int)$default_country->zone_id;
            }
        }

        if ($carrier_id && !$this->isCarrierInRange((int)$carrier_id, (int)$zone_id))
            $carrier_id = '';

        if (empty($carrier_id) && $this->isCarrierInRange((int)JeproshopSettingModelSeting::getValue('default_carrier'), (int)$zone_id))
            $carrier_id = (int)JeproshopSettingModelSeting::getValue('default_carrier');

        $total_package_without_shipping_tax_inc = $this->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING, $product_list);
        if (empty($carrier_id))
        {
            if ((int)$this->customer_id)
            {
                $customer = new JeproshopCustomerModelCustomer((int)$this->customer_id);
                $result = JeproshopCarrierModelCarrier::getCarriers((int)JeproshopSettingModelSeting::getValue('default_lang'), true, false, (int)$zone_id, $customer->getGroups());
                unset($customer);
            }
            else
                $result = JeproshopCarrierModelCarrier::getCarriers((int)JeproshopSettingModelSeting::getValue('default_lang'), true, false, (int)$zone_id);

            foreach ($result as $k => $row)
            {
                if ($row->carrier_id == JeproshopSettingModelSeting::getValue('default_carrier'))
                    continue;

                if (!isset(self::$_carriers[$row->carrier_id]))
                    self::$_carriers[$row->carrier_id] = new JeproshopCarrierModelCarrier((int)$row->carrier_id);

                $carrier = self::$_carriers[$row->carrier_id];

                // Get only carriers that are compliant with shipping method
                if (($carrier->getShippingMethod() == JeproshopCarrierModelCarrier::WEIGHT_SHIPPING_METHOD && $carrier->getMaxDeliveryPriceByWeight((int)$zone_id) === false)
                    || ($carrier->getShippingMethod() == JeproshopCarrierModelCarrier::PRICE_SHIPPING_METHOD && $carrier->getMaxDeliveryPriceByPrice((int)$zone_id) === false))
                {
                    unset($result[$k]);
                    continue;
                }

                // If out-of-range behavior carrier is set on "Deactivated carrier"
                if ($row->range_behavior){
                    $check_delivery_price_by_weight = JeproshopCarrierModelCarrier::checkDeliveryPriceByWeight($row->carrier_id, $this->getTotalWeight(), (int)$zone_id);

                    $total_order = $total_package_without_shipping_tax_inc;
                    $check_delivery_price_by_price = JeproshopCarrierModelCarrier::checkDeliveryPriceByPrice($row->carrier_id, $total_order, (int)$zone_id, (int)$this->currency_id);

                    // Get only carriers that have a range compatible with cart
                    if (($carrier->getShippingMethod() == JeproshopCarrierModelCarrier::WEIGHT_SHIPPING_METHOD && !$check_delivery_price_by_weight)
                        || ($carrier->getShippingMethod() == JeproshopCarrierModelCarrier::PRICE_SHIPPING_METHOD && !$check_delivery_price_by_price))
                    {
                        unset($result[$k]);
                        continue;
                    }
                }

                if ($carrier->getShippingMethod() == JeproshopCarrierModelCarrier::WEIGHT_SHIPPING_METHOD) {
                    $shipping = $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), (int)$zone_id);
                }else {
                    $shipping = $carrier->getDeliveryPriceByPrice($order_total, (int)$zone_id, (int)$this->currency_id);
                }
                
                if (!isset($min_shipping_price)){ $min_shipping_price = $shipping;  }

                if ($shipping <= $min_shipping_price){
                    $carrier_id = (int)$row->carrier_id;
                    $min_shipping_price = $shipping;
                }
            }
        }

        if (empty($carrier_id))
            $carrier_id = JeproshopSettingModelSeting::getValue('default_carrier');

        if (!isset(self::$_carriers[$carrier_id])) {
            self::$_carriers[$carrier_id] = new JeproshopCarrierModelCarrier((int)$carrier_id, JeproshopSettingModelSeting::getValue('default_lang'));
        }
        $carrier = self::$_carriers[$carrier_id];

        // No valid Carrier or $carrier_id <= 0 ?
        if (!JeproshopValidator::isLoadedObject($carrier, 'carrier_id')) {
            JeproshopCache::store($cache_id, 0);
            return 0;
        }

        if (!$carrier->published){
            JeproshopCache::store($cache_id, $shipping_cost);
            return $shipping_cost;
        }

        // Free fees if free carrier
        if ($carrier->is_free == 1){
            JeproshopCache::store($cache_id, 0);
            return 0;
        }

        // Select carrier tax
        if ($use_tax && !JeproshopTaxModelTax::excludedTaxOption()){
            $address = JeproshopAddressModelAddress::initialize((int)$address_id);
            $carrier_tax = $carrier->getTaxesRate($address);
        }

        $configuration = JeproshopSettingModelSeting::getValueMultiple(array(
            'PS_SHIPPING_FREE_PRICE',
            'PS_SHIPPING_HANDLING',
            'PS_SHIPPING_METHOD',
            'PS_SHIPPING_FREE_WEIGHT'
        ));

        // Free fees
        $free_fees_price = 0;
        if (isset($configuration['PS_SHIPPING_FREE_PRICE'])) {
            $free_fees_price = JeproshopValidator::convertPrice((float)$configuration['PS_SHIPPING_FREE_PRICE'], JeproshopCurrencyModelCurrency::getCurrencyInstance((int)$this->currency_id));
        }
        $orderTotalWithDiscounts = $this->getOrderTotal(true, JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING, null, null, false);
        
        if ($orderTotalWithDiscounts >= (float)($free_fees_price) && (float)($free_fees_price) > 0){
            Cache::store($cache_id, $shipping_cost);
            return $shipping_cost;
        }

        if (isset($configuration['PS_SHIPPING_FREE_WEIGHT'])
            && $this->getTotalWeight() >= (float)$configuration['PS_SHIPPING_FREE_WEIGHT']
            && (float)$configuration['PS_SHIPPING_FREE_WEIGHT'] > 0)
        {
            JeproshopCache::store($cache_id, $shipping_cost);
            return $shipping_cost;
        }

        // Get shipping cost using correct method
        if ($carrier->range_behavior){
            if(!isset($zone_id)) {
                // Get id zone
                if (isset($this->address_delivery_id)
                    && $this->address_delivery_id && JeproshopCustomerModelCustomer::customerHasAddress($this->customer_id, $this->address_delivery_id)) {
                    $zone_id = JeproshopAddressModelAddress::getZoneIdByAddressId((int)$this->address_delivery_id);
                }else {
                    $zone_id = (int)$default_country->zone_id;
                }
            }

            if (($carrier->getShippingMethod() == JeproshopCarrierModelCarrier::WEIGHT_SHIPPING_METHOD && !JeproshopCarrierModelCarrier::checkDeliveryPriceByWeight($carrier->id, $this->getTotalWeight(), (int)$zone_id))
                || ($carrier->getShippingMethod() == JeproshopCarrierModelCarrier::PRICE_SHIPPING_METHOD && !JeproshopCarrierModelCarrier::checkDeliveryPriceByPrice($carrier->id, $total_package_without_shipping_tax_inc, $zone_id, (int)$this->currency_id)
                ))
                $shipping_cost += 0;
            else{
                if ($carrier->getShippingMethod() == Carrier::SHIPPING_METHOD_WEIGHT)
                    $shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), $zone_id);
                else // by price
                    $shipping_cost += $carrier->getDeliveryPriceByPrice($order_total, $zone_id, (int)$this->currency_id);
            }
        } else {
            if ($carrier->getShippingMethod() == JeproshopCarrierModelCarrier::WEIGHT_SHIPPING_METHOD) {
                $shipping_cost += $carrier->getDeliveryPriceByWeight($this->getTotalWeight($product_list), $zone_id);
            }else {
                $shipping_cost += $carrier->getDeliveryPriceByPrice($order_total, $zone_id, (int)$this->currency_id);
            }
        }
        // Adding handling charges
        if (isset($configuration['PS_SHIPPING_HANDLING']) && $carrier->shipping_handling)
            $shipping_cost += (float)$configuration['PS_SHIPPING_HANDLING'];

        // Additional Shipping Cost per product
        foreach ($products as $product)
            if (!$product->is_virtual)
                $shipping_cost += $product->additional_shipping_cost * $product->cart_quantity;

        $shipping_cost = JeproshopValidator::convertPrice($shipping_cost, JeproshopCurrencyModelCurrency::getCurrencyInstance((int)$this->currency_id));

        /*/get external shipping cost from module
        if ($carrier->shipping_external)
        {
            $module_name = $carrier->external_module_name;
            $module = Module::getInstanceByName($module_name);

            if (JeproshopValidator::isLoadedObject($module))
            {
                if (array_key_exists('carrier_id', $module))
                    $module->carrier_id = $carrier->id;
                if ($carrier->need_range)
                    if (method_exists($module, 'getPackageShippingCost'))
                        $shipping_cost = $module->getPackageShippingCost($this, $shipping_cost, $products);
                    else
                        $shipping_cost = $module->getOrderShippingCost($this, $shipping_cost);
                else
                    $shipping_cost = $module->getOrderShippingCostExternal($this);

                // Check if carrier is available
                if ($shipping_cost === false)
                {
                    JeproshopCache::store($cache_id, false);
                    return false;
                }
            }
            else
            {
                JeproshopCache::store($cache_id, false);
                return false;
            }
        } */

        // Apply tax
        if ($use_tax && isset($carrier_tax))
            $shipping_cost *= 1 + ($carrier_tax / 100);

        $shipping_cost = (float)JeproshopValidator::roundPrice((float)$shipping_cost, 2);
        JeproshopCache::store($cache_id, $shipping_cost);

        return $shipping_cost;
    }

    /**
     * isCarrierInRange
     *
     * Check if the specified carrier is in range
     *
     * @param int $carrier_id
     * @param int $zone_id
     * @return bool
     */
    public function isCarrierInRange($carrier_id, $zone_id) {
        $carrier = new JeproshopCarrierModelCarrier((int)$carrier_id, JeproshopSettingModelSetting::getValue('default_lang'));
        $shipping_method = $carrier->getShippingMethod();
        if (!$carrier->range_behavior)
            return true;

        if ($shipping_method == JeproshopCarrierModelCarrier::SHIPPING_METHOD_FREE)
            return true;

        $check_delivery_price_by_weight = JeproshopCarrierModelCarrier::checkDeliveryPriceByWeight( (int)$carrier_id, $this->getTotalWeight(), $zone_id );
        if ($shipping_method == JeproshopCarrierModelCarrier::SHIPPING_METHOD_WEIGHT && $check_delivery_price_by_weight)
            return true;

        $check_delivery_price_by_price = JeproshopCarrierModelCarrier::checkDeliveryPriceByPrice(
            (int)$carrier_id,
            $this->getOrderTotal( true, JeproshopCartModelCart::BOTH_WITHOUT_SHIPPING ),
            $zone_id, (int)$this->currency_id
        );
        if ($shipping_method == JeproshopCarrierModelCarrier::SHIPPING_METHOD_PRICE && $check_delivery_price_by_price)
            return true;

        return false;
    }

    /**
     * Return cart weight
     *
     * @param $products
     * @return float Cart weight
     */
    public function getTotalWeight($products = null) {
        if (!is_null($products)){
            $total_weight = 0;
            foreach ($products as $product){
                if (!isset($product->weight_attribute) || is_null($product->weight_attribute))
                    $total_weight += $product->weight * $product->cart_quantity;
                else
                    $total_weight += $product->weight_attribute * $product->cart_quantity;
            }
            return $total_weight;
        }

        if (!isset(self::$_totalWeight[$this->cart_id])) {
            $db = JFactory::getDBO();
            if (JeproshopCombinationModelCombination::isFeaturePublished()) {
                $query = "SELECT SUM((product." . $db->quoteName('weight') . " + product_attribute." . $db->quoteName('weight') . ") * cart_product.";
                $query .= $db->quoteName('quantity') . ") AS nb FROM "  . $db->quoteName('#__jeproshop_cart_product') . " AS cart_product LEFT JOIN ";
                $query .= $db->quoteName('#__jeproshop_product') . " AS product ON (cart_product." . $db->quoteName('product_id') . " = product.";
                $query .= $db->quoteName('product_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
                $query .= " ON (cart_product." . $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id');
                $query .= ") WHERE (cart_product."  . $db->quoteName('product_attribute_id') . " IS NOT NULL AND cart_product." . $db->quoteName('product_attribute_id');
                $query .= " != 0) AND cart_product." . $db->quoteName('cart_id') . " = " .  (int)$this->cart_id;

                $db->setQuery($query);
                $weight_product_with_attribute = $db->loadResult();
            }else {
                $weight_product_with_attribute = 0;
            }

            $query = "SELECT SUM(product." . $db->quoteName('weight') . " * cart_product." . $db->quoteName('quantity') . ") AS nb FROM " . $db->quoteName('#__jeproshop_cart_product');
            $query .= " cart_product LEFT JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON (cart_product." . $db->quoteName('product_id') . " = product.";
            $query .= $db->quoteName('product_id') . ") WHERE (cart_product." . $db->quoteName('product_attribute_id') . " IS NULL OR cart_product." . $db->quoteName('product_attribute_id');
            $query .= " = 0) AND cart_product." . $db->quoteName('cart_id') . " = " . (int)$this->cart_id;

            $db->setQuery($query);
            $weight_product_without_attribute = $db->loadResult();

            self::$_totalWeight[$this->cart_id] = round((float)$weight_product_with_attribute + (float)$weight_product_without_attribute, 3);
        }

        return self::$_totalWeight[$this->cart_id];
    }

    /**
     * Get all deliveries options available for the current cart formatted like Carriers::getCarriersForOrder
     * This method was wrote for retro-compatibility with 1.4 theme
     * New theme need to use Cart::getDeliveryOptionList() to generate carriers option in the checkout process
     *
     *
     * @param \Country|\JeproshopCountryModelCountry $default_country
     * @param boolean $flush Force flushing cache
     * @return array
     */
    public function simulateCarriersOutput(JeproshopCountryModelCountry $default_country = null, $flush = false){
        static $cache = false;
        if ($cache !== false && !$flush)
            return $cache;

        $delivery_option_list = $this->getDeliveryOptionList($default_country, $flush);

        // This method cannot work if there is multiple address delivery
        if (count($delivery_option_list) > 1 || empty($delivery_option_list))
            return array();

        $carriers = array();
        foreach (reset($delivery_option_list) as $key => $option){
            $price = $option->total_price_with_tax;
            $price_tax_exc = $option->total_price_without_tax;

            if ($option->unique_carrier){
                $carrier = reset($option->carrier_list);
                $name = $carrier->instance->name;
                $img = $carrier->logo;
                $delay = $carrier->instance->delay;
                $delay = isset($delay[JeproshopContext::getContext()->language->lang_id]) ? $delay[JeproshopContext::getContext()->language->lang_id] : $delay[(int)JeproshopSettingModelSetting::getValue('default_lang')];
            } else {
                $nameList = array();
                foreach ($option->carrier_list as $carrier){
                    $nameList[] = $carrier->instance->name;
                }
                $name = join(' -', $nameList);
                $img = ''; // No images if multiple carriers
                $delay = '';
            }
            $carriers[] = array(
                'name' => $name,
                'img' => $img,
                'delay' => $delay,
                'price' => $price,
                'price_tax_exc' => $price_tax_exc,
                'carrier_id' => Cart::intifier($key), // Need to translate to an integer for retro-compatibility reason, in 1.4 template we used intval
                'is_module' => false,
            );
        }
        return $carriers;
    }

    public function simulateCarrierSelectedOutput($use_cache = true){
        $delivery_option = $this->getDeliveryOption(null, false, $use_cache);

        if (count($delivery_option) > 1 || empty($delivery_option))
            return 0;

        return JeproshopCartModelCart::intifier(reset($delivery_option));
    }

    /**
     * Translate a string option_delivery identifier ('24,3,') in a int (3240002000)
     *
     * The  option_delivery identifier is a list of integers separated by a ','.
     * This method replace the delimiter by a sequence of '0'.
     * The size of this sequence is fixed by the first digit of the return
     *
     * @param $string
     * @param string $delimiter
     * @return int
     */
    public static function intifier($string, $delimiter = ','){
        $elm = explode($delimiter, $string);
        $max = max($elm);
        return strlen($max).implode(str_repeat('0', strlen($max) + 1), $elm);
    }

    /**
     * Translate a int option_delivery identifier (3240002000) in a string ('24,3,')
     */
    public static function desIntifier($int, $delimiter = ','){
        $delimiter_len = $int[0];
        $int = strrev(substr($int, 1));
        $elm = explode(str_repeat('0', $delimiter_len + 1), $int);
        return strrev(implode($delimiter, $elm));
    }

    /**
     * Set the delivery option and carrier_id, if there is only one carrier
     */
    public function setDeliveryOption($delivery_option = null){
        if (empty($delivery_option) || count($delivery_option) == 0){
            $this->delivery_option = '';
            $this->carrier_id = 0;
            return;
        }
        JeproshopCache::clean('getContextualValue_*');
        $delivery_option_list = $this->getDeliveryOptionList(null, true);

        foreach ($delivery_option_list as $address_id => $options){
            if (!isset($delivery_option[$address_id])){
                foreach ($options as $key => $option){
                    if ($option['is_best_price']){
                        $delivery_option[$address_id] = $key;
                        break;
                    }
                }
            }
        }

        if (count($delivery_option) == 1)
            $this->carrier_id = $this->getIdCarrierFromDeliveryOption($delivery_option);

        $this->delivery_option = serialize($delivery_option);
    }

    public function getDeliveryOptionList(JeproshopCountryModelCountry $default_country = null, $flush = false){
        static $cache = null;
        if ($cache !== null && !$flush)
            return $cache;

        $delivery_option_list = array();
        $carriers_price = array();
        $carrier_collection = array();
        $package_list = $this->getPackageList();

        // Foreach addresses
        foreach ($package_list as $address_id => $packages) {
            // Initialize vars
            $delivery_option_list[$address_id] = array();
            $carriers_price[$address_id] = array();
            $common_carriers = null;
            $best_price_carriers = array();
            $best_grade_carriers = array();
            $carriers_instance = array();

            // Get country
            if ($address_id){
                $address = new JeproshopAddressModelAddress($address_id);
                $country = new JeproshopCountryModelCountry($address->country_id);
            }
            else
                $country = $default_country;

            // Foreach packages, get the carriers with best price, best position and best grade
            foreach ($packages as $package_id => $package){
                // No carriers available
                    if (count($package['carrier_list']) == 1 && current($package['carrier_list']) == 0){
                    $cache = array();
                    return $cache;
                }

                $carriers_price[$address_id][$package_id] = array();

                // Get all common carriers for each packages to the same address
                if (is_null($common_carriers))
                    $common_carriers = $package['carrier_list'];
                else
                    $common_carriers = array_intersect($common_carriers, $package['carrier_list']);

                $best_price = null;
                $best_price_carrier = null;
                $best_grade = null;
                $best_grade_carrier = null;

                // Foreach carriers of the package, calculate his price, check if it the best price, position and grade
                foreach ($package['carrier_list'] as $carrier_id){
                    if (!isset($carriers_instance[$carrier_id]))
                        $carriers_instance[$carrier_id] = new JeproshopCarrierModelCarrier($carrier_id);

                    $price_with_tax = $this->getPackageShippingCost($carrier_id, true, $country, $package['product_list']);
                    $price_without_tax = $this->getPackageShippingCost($carrier_id, false, $country, $package['product_list']);
                    if (is_null($best_price) || $price_with_tax < $best_price){
                        $best_price = $price_with_tax;
                        $best_price_carrier = $carrier_id;
                    }
                    $carriers_price[$address_id][$package_id][$carrier_id] = array(
                        'without_tax' => $price_without_tax,
                        'with_tax' => $price_with_tax);

                    $grade = $carriers_instance[$carrier_id]->grade;
                    if (is_null($best_grade) || $grade > $best_grade) {
                        $best_grade = $grade;
                        $best_grade_carrier = $carrier_id;
                    }
                }

                $best_price_carriers[$package_id] = $best_price_carrier;
                $best_grade_carriers[$package_id] = $best_grade_carrier;
            }

            // Reset $best_price_carrier, it's now an array
            $best_price_carrier = array();
            $key = '';

            // Get the delivery option with the lower price
            foreach ($best_price_carriers as $package_id => $carrier_id){
                $key .= $carrier_id . ',';
                if (!isset($best_price_carrier[$carrier_id]))
                    $best_price_carrier[$carrier_id] = array(
                        'price_with_tax' => 0,
                        'price_without_tax' => 0,
                        'package_list' => array(),
                        'product_list' => array(),
                    );
                $best_price_carrier[$carrier_id]['price_with_tax'] += $carriers_price[$address_id][$package_id][$carrier_id]['with_tax'];
                $best_price_carrier[$carrier_id]['price_without_tax'] += $carriers_price[$address_id][$package_id][$carrier_id]['without_tax'];
                $best_price_carrier[$carrier_id]['package_list'][] = $package_id;
                $best_price_carrier[$carrier_id]['product_list'] = array_merge($best_price_carrier[$carrier_id]['product_list'], $packages[$package_id]['product_list']);
                $best_price_carrier[$carrier_id]['instance'] = $carriers_instance[$carrier_id];
            }

            // Add the delivery option with best price as best price
            $delivery_option_list[$address_id][$key] = array(
                'carrier_list' => $best_price_carrier,
                'is_best_price' => true,
                'is_best_grade' => false,
                'unique_carrier' => (count($best_price_carrier) <= 1)
            );

            // Reset $best_grade_carrier, it's now an array
            $best_grade_carrier = array();
            $key = '';

            // Get the delivery option with the best grade
            foreach ($best_grade_carriers as $package_id => $carrier_id) {
                $key .= $carrier_id.',';
                if (!isset($best_grade_carrier[$carrier_id]))
                    $best_grade_carrier[$carrier_id] = array(
                        'price_with_tax' => 0,
                        'price_without_tax' => 0,
                        'package_list' => array(),
                        'product_list' => array(),
                    );
                $best_grade_carrier[$carrier_id]['price_with_tax'] += $carriers_price[$address_id][$package_id][$carrier_id]['with_tax'];
                $best_grade_carrier[$carrier_id]['price_without_tax'] += $carriers_price[$address_id][$package_id][$carrier_id]['without_tax'];
                $best_grade_carrier[$carrier_id]['package_list'][] = $package_id;
                $best_grade_carrier[$carrier_id]['product_list'] = array_merge($best_grade_carrier[$carrier_id]['product_list'], $packages[$package_id]['product_list']);
                $best_grade_carrier[$carrier_id]['instance'] = $carriers_instance[$carrier_id];
            }

            // Add the delivery option with best grade as best grade
            if (!isset($delivery_option_list[$address_id][$key]))
                $delivery_option_list[$address_id][$key] = array(
                    'carrier_list' => $best_grade_carrier,
                    'is_best_price' => false,
                    'unique_carrier' => (count($best_grade_carrier) <= 1)
                );
            $delivery_option_list[$address_id][$key]['is_best_grade'] = true;

            // Get all delivery options with a unique carrier
            foreach ($common_carriers as $carrier_id){
                $key = '';
                $package_list = array();
                $product_list = array();
                $price_with_tax = 0;
                $price_without_tax = 0;

                foreach ($packages as $package_id => $package){
                    $key .= $carrier_id.',';
                    $price_with_tax += $carriers_price[$address_id][$package_id][$carrier_id]['with_tax'];
                    $price_without_tax += $carriers_price[$address_id][$package_id][$carrier_id]['without_tax'];
                    $package_list[] = $package_id;
                    $product_list = array_merge($product_list, $package['product_list']);
                }

                if (!isset($delivery_option_list[$address_id][$key]))
                    $delivery_option_list[$address_id][$key] = array(
                        'is_best_price' => false,
                        'is_best_grade' => false,
                        'unique_carrier' => true,
                        'carrier_list' => array(
                            $carrier_id => array(
                                'price_with_tax' => $price_with_tax,
                                'price_without_tax' => $price_without_tax,
                                'instance' => $carriers_instance[$carrier_id],
                                'package_list' => $package_list,
                                'product_list' => $product_list,
                            )
                        )
                    );
                else
                    $delivery_option_list[$address_id][$key]['unique_carrier'] = (count($delivery_option_list[$address_id][$key]['carrier_list']) <= 1);
            }
        }

        $cart_rules = JeproshopCartRuleModelCartRule::getCustomerCartRules(JeproshopContext::getContext()->cookie->lang_id, JeproshopContext::getContext()->cookie->customer_id, true, true, false, $this);

        $free_carriers_rules = array();
        foreach ($cart_rules as $cart_rule){
            if ($cart_rule->free_shipping && $cart_rule->carrier_restriction){
                $cartRule = new JeproshopCartRuleModelCartRule((int)$cart_rule->cart_rule_id);
                if (JeproshopValidator::isLoadedObject($cartRule, 'cart_rule_id')){
                    $carriers = $cart_rule->getAssociatedRestrictions('carrier', true, false);
                    if (is_array($carriers) && count($carriers) && isset($carriers['selected'])){
                        foreach($carriers['selected'] as $carrier){
                            if (isset($carrier->carrier_id) && $carrier->carrier_id){
                                $free_carriers_rules[] = (int)$carrier->carrier_id;
                            }
                        }
                    }
                }
            }
        }

        // For each delivery options :
        //    - Set the carrier list
        //    - Calculate the price
        //    - Calculate the average position
        foreach ($delivery_option_list as $address_id => $delivery_option)
            foreach ($delivery_option as $key => $value)
            {
                $total_price_with_tax = 0;
                $total_price_without_tax = 0;
                $position = 0;
                foreach ($value['carrier_list'] as $carrier_id => $data)
                {
                    $total_price_with_tax += $data['price_with_tax'];
                    $total_price_without_tax += $data['price_without_tax'];
                    $total_price_without_tax_with_rules = (in_array($carrier_id, $free_carriers_rules)) ? 0 : $total_price_without_tax ;

                    if (!isset($carrier_collection[$carrier_id]))
                        $carrier_collection[$carrier_id] = new Carrier($carrier_id);
                    $delivery_option_list[$address_id][$key]['carrier_list'][$carrier_id]['instance'] = $carrier_collection[$carrier_id];

                    if (file_exists(_PS_SHIP_IMG_DIR_.$carrier_id.'.jpg'))
                        $delivery_option_list[$address_id][$key]['carrier_list'][$carrier_id]['logo'] = _THEME_SHIP_DIR_.$carrier_id.'.jpg';
                    else
                        $delivery_option_list[$address_id][$key]['carrier_list'][$carrier_id]['logo'] = false;

                    $position += $carrier_collection[$carrier_id]->position;
                }
                $delivery_option_list[$address_id][$key]['total_price_with_tax'] = $total_price_with_tax;
                $delivery_option_list[$address_id][$key]['total_price_without_tax'] = $total_price_without_tax;
                $delivery_option_list[$address_id][$key]['is_free'] = !$total_price_without_tax_with_rules ? true : false;
                $delivery_option_list[$address_id][$key]['position'] = $position / count($value['carrier_list']);
            }

        // Sort delivery option list
        foreach ($delivery_option_list as &$array)
            uasort ($array, array('Cart', 'sortDeliveryOptionList'));

        $cache = $delivery_option_list;
        return $delivery_option_list;
    }


    protected function getIdCarrierFromDeliveryOption($delivery_option)
    {
        $delivery_option_list = $this->getDeliveryOptionList();
        foreach ($delivery_option as $key => $value)
            if (isset($delivery_option_list[$key]) && isset($delivery_option_list[$key][$value]))
                if (count($delivery_option_list[$key][$value]['carrier_list']) == 1)
                    return current(array_keys($delivery_option_list[$key][$value]['carrier_list']));

        return 0;
    }

    public function getPackageList($flush = false){
        static $cache = array();
        if (isset($cache[(int)$this->cart_id.'_'.(int)$this->address_delivery_id]) && $cache[(int)$this->cart_id.'_'.(int)$this->address_delivery_id] !== false && !$flush)
            return $cache[(int)$this->cart_id.'_'.(int)$this->address_delivery_id];

        $product_list = $this->getProducts();
        // Step 1 : Get product informations (warehouse_list and carrier_list), count warehouse
        // Determine the best warehouse to determine the packages
        // For that we count the number of time we can use a warehouse for a specific delivery address
        $warehouse_count_by_address = array();
        $warehouse_carrier_list = array();

        $stock_management_active = JeproshopSettingModelSetting::getValue('advanced_stock_management');

        foreach ($product_list as &$product){
            if ((int)$product->address_delivery_id == 0){
                $product->address_delivery_id = (int)$this->address_delivery_id;
            }

            if (!isset($warehouse_count_by_address[$product->address_delivery_id])){
                $warehouse_count_by_address[$product->address_delivery_id] = array();
            }

            $product->warehouse_list = array();

            if ($stock_management_active &&
                ((int)$product['advanced_stock_management'] == 1 || Pack::usesAdvancedStockManagement((int)$product->product_id)))
            {
                $warehouse_list = Warehouse::getProductWarehouseList($product->product_id, $product->roduct_attribute_id, $this->shop_id);
                if (count($warehouse_list) == 0)
                    $warehouse_list = Warehouse::getProductWarehouseList($product->product_id, $product->roduct_attribute_id);
                // Does the product is in stock ?
                // If yes, get only warehouse where the product is in stock

                $warehouse_in_stock = array();
                $manager = StockManagerFactory::getManager();

                foreach ($warehouse_list as $key => $warehouse)
                {
                    $product_real_quantities = $manager->getProductRealQuantities(
                        $product->product_id,
                        $product->product_attribute_id,
                        array($warehouse->warehouse_id),
                        true
                    );

                    if ($product_real_quantities > 0 || Pack::isPack((int)$product->product_id))
                        $warehouse_in_stock[] = $warehouse;
                }

                if (!empty($warehouse_in_stock)){
                    $warehouse_list = $warehouse_in_stock;
                    $product->in_stock = true;
                }
                else
                    $product->in_stock = false;
            }
            else
            {
                //simulate default warehouse
                $warehouse_list = array(0);
                $product->in_stock = StockAvailable::getQuantityAvailableByProduct($product->product_id, $product->product_attribute_id) > 0;
            }

            foreach ($warehouse_list as $warehouse)
            {
                if (!isset($warehouse_carrier_list[$warehouse->warehouse_id]))
                {
                    $warehouse_object = new JeproshopWarehouseModelWarehouse($warehouse->warehouse_id);
                    $warehouse_carrier_list[$warehouse->warehouse_id] = $warehouse_object->getCarriers();
                }

                $product->warehouse_list[] = $warehouse->warehouse_id;
                if (!isset($warehouse_count_by_address[$product->address_delivery_id][$warehouse->warehouse_id]))
                    $warehouse_count_by_address[$product->address_delivery_id][$warehouse->warehouse_id] = 0;

                $warehouse_count_by_address[$product->address_delivery_id][$warehouse->warehouse_id]++;
            }
        }
        unset($product);

        arsort($warehouse_count_by_address);

        // Step 2 : Group product by warehouse
        $grouped_by_warehouse = array();
        foreach ($product_list as &$product)
        {
            if (!isset($grouped_by_warehouse[$product->address_delivery_id]))
                $grouped_by_warehouse[$product->address_delivery_id] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );

            $product->carrier_list = array();
            $warehouse_id = 0;
            foreach ($warehouse_count_by_address[$product->address_delivery_id] as $war_id => $val)
            {
                if (in_array((int)$war_id, $product->warehouse_list))
                {
                    $product->carrier_list = array_merge($product->carrier_list, Carrier::getAvailableCarrierList(new Product($product->product_id), $war_id, $product->address_delivery_id, null, $this));
                    if (!$warehouse_id)
                        $warehouse_id = (int)$war_id;
                }
            }

            if (!isset($grouped_by_warehouse[$product->address_delivery_id]['in_stock'][$warehouse_id])) {
                $grouped_by_warehouse[$product->address_delivery_id]['in_stock'][$warehouse_id] = array();
                $grouped_by_warehouse[$product->address_delivery_id]['out_of_stock'][$warehouse_id] = array();
            }

            if (!$this->allow_separated_package)
                $key = 'in_stock';
            else
                $key = $product->in_stock ? 'in_stock' : 'out_of_stock';

            if (empty($product->carrier_list))
                $product->carrier_list = array(0);

            $grouped_by_warehouse[$product->address_delivery_id][$key][$warehouse_id][] = $product;
        }
        unset($product);

        // Step 3 : grouped product from grouped_by_warehouse by available carriers
        $grouped_by_carriers = array();
        foreach ($grouped_by_warehouse as $address_delivery_id => $products_in_stock_list)
        {
            if (!isset($grouped_by_carriers[$address_delivery_id]))
                $grouped_by_carriers[$address_delivery_id] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );
            foreach ($products_in_stock_list as $key => $warehouse_list)
            {
                if (!isset($grouped_by_carriers[$address_delivery_id][$key]))
                    $grouped_by_carriers[$address_delivery_id][$key] = array();
                foreach ($warehouse_list as $warehouse_id => $product_list)
                {
                    if (!isset($grouped_by_carriers[$address_delivery_id][$key][$warehouse_id]))
                        $grouped_by_carriers[$address_delivery_id][$key][$warehouse_id] = array();
                    foreach ($product_list as $product)
                    {
                        $package_carriers_key = implode(',', $product->carrier_list);

                        if (!isset($grouped_by_carriers[$address_delivery_id][$key][$warehouse_id][$package_carriers_key]))
                            $grouped_by_carriers[$address_delivery_id][$key][$warehouse_id][$package_carriers_key] = array(
                                'product_list' => array(),
                                'carrier_list' => $product->carrier_list,
                                'warehouse_list' => $product->warehouse_list
                            );

                        $grouped_by_carriers[$address_delivery_id][$key][$warehouse_id][$package_carriers_key]['product_list'][] = $product;
                    }
                }
            }
        }

        $package_list = array();
        // Step 4 : merge product from grouped_by_carriers into $package to minimize the number of package
        foreach ($grouped_by_carriers as $address_delivery_id => $products_in_stock_list){
            if (!isset($package_list[$address_delivery_id]))
                $package_list[$address_delivery_id] = array(
                    'in_stock' => array(),
                    'out_of_stock' => array(),
                );

            foreach ($products_in_stock_list as $key => $warehouse_list)
            {
                if (!isset($package_list[$address_delivery_id][$key]))
                    $package_list[$address_delivery_id][$key] = array();
                // Count occurrence of each carriers to minimize the number of packages
                $carrier_count = array();
                foreach ($warehouse_list as $warehouse_id => $products_grouped_by_carriers)
                {
                    foreach ($products_grouped_by_carriers as $data)
                    {
                        foreach ($data['carrier_list'] as $carrier_id)
                        {
                            if (!isset($carrier_count[$carrier_id]))
                                $carrier_count[$carrier_id] = 0;
                            $carrier_count[$carrier_id]++;
                        }
                    }
                }
                arsort($carrier_count);
                foreach ($warehouse_list as $warehouse_id => $products_grouped_by_carriers)
                {
                    if (!isset($package_list[$address_delivery_id][$key][$warehouse_id]))
                        $package_list[$address_delivery_id][$key][$warehouse_id] = array();
                    foreach ($products_grouped_by_carriers as $data)
                    {
                        foreach ($carrier_count as $carrier_id => $rate)
                        {
                            if (in_array($carrier_id, $data['carrier_list']))
                            {
                                if (!isset($package_list[$address_delivery_id][$key][$warehouse_id][$carrier_id]))
                                    $package_list[$address_delivery_id][$key][$warehouse_id][$carrier_id] = array(
                                        'carrier_list' => $data['carrier_list'],
                                        'warehouse_list' => $data['warehouse_list'],
                                        'product_list' => array(),
                                    );
                                $package_list[$address_delivery_id][$key][$warehouse_id][$carrier_id]['carrier_list'] =
                                    array_intersect($package_list[$address_delivery_id][$key][$warehouse_id][$carrier_id]['carrier_list'], $data['carrier_list']);
                                $package_list[$address_delivery_id][$key][$warehouse_id][$carrier_id]['product_list'] =
                                    array_merge($package_list[$address_delivery_id][$key][$warehouse_id][$carrier_id]['product_list'], $data['product_list']);

                                break;
                            }
                        }
                    }
                }
            }
        }

        // Step 5 : Reduce depth of $package_list
        $final_package_list = array();
        foreach ($package_list as $address_delivery_id => $products_in_stock_list){
            if (!isset($final_package_list[$address_delivery_id])){
                $final_package_list[$address_delivery_id] = array();
            }

            foreach ($products_in_stock_list as $key => $warehouse_list){
                foreach ($warehouse_list as $warehouse_id => $products_grouped_by_carriers){
                    foreach ($products_grouped_by_carriers as $data){
                        $final_package_list[$address_delivery_id][] = array(
                            'product_list' => $data['product_list'],
                            'carrier_list' => $data['carrier_list'],
                            'warehouse_list' => $data['warehouse_list'],
                            'warehouse_id' => $warehouse_id,
                        );
                    }
                }
            }
        }
        $cache[(int)$this->cart_id] = $final_package_list;
        return $final_package_list;
    }

    /**
     * Get the delivery option selected, or if no delivery option was selected, the cheapest option for each address
     * @param null $default_country
     * @param bool $doNotAutoSelectOptions
     * @param bool $use_cache
     * @return array delivery option
     */
    public function getDeliveryOption($default_country = null, $doNotAutoSelectOptions = false, $use_cache = true){
        static $cache = array();
        $cache_id = (int)(is_object($default_country) ? $default_country->country_id : 0).'_'.(int)$doNotAutoSelectOptions;
        if (isset($cache[$cache_id]) && $use_cache){
            return $cache[$cache_id];
        }
        $delivery_option_list = $this->getDeliveryOptionList($default_country);

        // The delivery option was selected
        if (isset($this->delivery_option) && $this->delivery_option != '') {
            $delivery_option = Tools::unSerialize($this->delivery_option);
            $validated = true;
            foreach ($delivery_option as $address_id => $key) {
                if (!isset($delivery_option_list[$address_id][$key])) {
                    $validated = false;
                    break;
                }
            }

            if ($validated){
                $cache[$cache_id] = $delivery_option;
                return $delivery_option;
            }
        }

        if ($doNotAutoSelectOptions){ return false; }

        // No delivery option selected or delivery option selected is not valid, get the better for all options
        $delivery_option = array();
        foreach ($delivery_option_list as $address_id => $options)
        {
            foreach ($options as $key => $option) {
                if (JeproshopSettingModelSeting::getValue('default_carrier') == -1 && $option['is_best_price']) {
                    $delivery_option[$address_id] = $key;
                    break;
                } elseif (JeproshopSettingModelSeting::getValue('default_carrier') == -2 && $option['is_best_grade']) {
                    $delivery_option[$address_id] = $key;
                    break;
                } elseif ($option['unique_carrier'] && in_array(JeproshopSettingModelSeting::getValue('default_carrier'), array_keys($option['carrier_list']))) {
                    $delivery_option[$address_id] = $key;
                    break;
                }
            }

            reset($options);
            if (!isset($delivery_option[$address_id]))
                $delivery_option[$address_id] = key($options);
        }

        $cache[$cache_id] = $delivery_option;

        return $delivery_option;
    }

    /**
     * Get all delivery addresses object for the current cart
     */
    public function getAddressCollection(){
        $collection = array();
        $cache_id = 'jeproshop_cart_get_address_collection'.(int)$this->cart_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT DISTINCT " . $db->quoteName('address_delivery_id') . " FROM " . $db->quoteName('#__jeproshop_cart_product');
            $query .= " WHERE cart_id = " . (int)$this->cart_id;

            $db->setQuery($query);
            $result = $db->loadObjectList();
            JeproshopCache::store($cache_id, $result);
        }
        $result = JeproshopCache::retrieve($cache_id);

        //$result[] = array('address_delivery_id' => (int)$this->address_delivery_id);

        foreach ($result as $row)
            if ((int)$row->address_delivery_id != 0)
                $collection[(int)$row->address_delivery_id] = new JeproshopAddressModelAddress((int)$row->address_delivery_id);
        return $collection;
    }

    /**
     *
     * Execute hook displayCarrierList (extraCarrier) and merge theme to the $array
     * @param array $array
     */
    public static function addExtraCarriers(&$array){
        $first = true;
        $hook_extra_carrier_address = array();
        foreach (JeproshopContext::getContext()->cart->getAddressCollection() as $address){
            $hook = ""; //Hook::exec('displayCarrierList', array('address' => $address));
            $hook_extra_carrier_address[$address->address_id] = $hook;

            if ($first){
                $array = array_merge(
                    $array,
                    array('HOOK_EXTRACARRIER' => $hook)
                );
                $first = false;
            }
            $array = array_merge(
                $array,
                array('HOOK_EXTRACARRIER_ADDR' => $hook_extra_carrier_address)
            );
        }
    }

    /**
     * Get all the ids of the delivery addresses without carriers
     *
     * @param bool $return_collection Return a collection
     *
     * @return array Array of address id or of address object
     */
    public function getDeliveryAddressesWithoutCarriers($return_collection = false){
        $addresses_without_carriers = array();
        foreach ($this->getProducts() as $product){
            if (!in_array($product->address_delivery_id, $addresses_without_carriers)
                && !count(JeproshopCarrierModelCarrier::getAvailableCarrierList(new JeproshopProductModelProduct($product->product_id), null, $product->address_delivery_id)))
                $addresses_without_carriers[] = $product->address_delivery_id;
        }
        if (!$return_collection)
            return $addresses_without_carriers;
        else {
            $addresses_instance_without_carriers = array();
            foreach ($addresses_without_carriers as $address_id){
                $addresses_instance_without_carriers[] = new JeproshopAddressModelAddress($address_id);
            }
            return $addresses_instance_without_carriers;
        }
    }

    /**
     * Return shipping total for the cart
     *
     * @param array $delivery_option Array of the delivery option for each address
     * @param bool $use_tax
     * @param JeproshopCountryModelCountry $default_country
     * @return float Shipping total
     */
    public function getTotalShippingCost($delivery_option = null, $use_tax = true, JeproshopCountryModelCountry $default_country = null){
        if(isset(JeproshopContext::getContext()->cookie->country_id)){
            $default_country = new JeproshopCountryModelCountry(JeproshopContext::getContext()->cookie->country_id);
        }
        if (is_null($delivery_option)){
            $delivery_option = $this->getDeliveryOption($default_country, false, false);
        }
        $total_shipping = 0;
        $delivery_option_list = $this->getDeliveryOptionList($default_country);
        foreach ($delivery_option as $address_id => $key){
            if (!isset($delivery_option_list[$address_id]) || !isset($delivery_option_list[$address_id][$key]))
                continue;
            if ($use_tax)
                $total_shipping += $delivery_option_list[$address_id][$key]->total_price_with_tax;
            else
                $total_shipping += $delivery_option_list[$address_id][$key]->total_price_without_tax;
        }

        return $total_shipping;
    }

    /**
     * Does the cart use multiple address
     * @return boolean
     */
    public function isMultiAddressDelivery(){
        static $cache = null;

        if (is_null($cache)) {
            $db = JFactory::getDBO();

            $query = "SELECT count(distinct address_delivery_id) FROM " . $db->quoteName('#__jeproshop_cart_product');
            $query .= " AS cart_product WHERE cart_product.cart_id = " . (int)$this->cart_id;

            $db->setQuery($query);
            $cache = (bool)($db->loadResult() > 1);
        }
        return $cache;
    }

    /**
     * @param bool $ignore_virtual Ignore virtual product
     * @param bool $exclusive If true, the validation is exclusive : it must be present product in stock and out of stock
     * @since 1.5.0
     *
     * @return bool false is some products from the cart are out of stock
     */
    public function isAllProductsInStock($ignore_virtual = false, $exclusive = false){
        $product_out_of_stock = 0;
        $product_in_stock = 0;
        foreach ($this->getProducts() as $product){
            if (!$exclusive){
                if ((int)$product->quantity_available <= 0
                    && (!$ignore_virtual || !$product->is_virtual))
                    return false;
            } else {
                if ((int)$product->quantity_available <= 0
                    && (!$ignore_virtual || !$product->is_virtual))
                    $product_out_of_stock++;
                if ((int)$product->quantity_available > 0
                    && (!$ignore_virtual || !$product->is_virtual))
                    $product_in_stock++;

                if ($product_in_stock > 0 && $product_out_of_stock > 0)
                    return false;
            }
        }
        return true;
    }

    /**
     * Return useful informations for cart
     *
     * @param null $lang_id
     * @param bool $refresh
     * @return array Cart details
     */
    public function getSummaryDetails($lang_id = null, $refresh = false){
        $context = JeproshopContext::getContext();
        $app = JFactory::getApplication();
        if (!$lang_id)
            $lang_id = $context->language->lang_id;

        $delivery = new JeproshopAddressModelAddress((int)$this->address_delivery_id);
        $invoice = new JeproshopAddressModelAddress((int)$this->address_invoice_id);

        // New layout system with personalization fields
        $formatted_addresses = array(
            'delivery' => JeproshopAddressFormatModelAddressFormat::getFormattedLayoutData($delivery),
            'invoice' => JeproshopAddressFormatModelAddressFormat::getFormattedLayoutData($invoice)
        );

        $base_total_tax_inc = $this->getOrderTotal(true);
        $base_total_tax_exc = $this->getOrderTotal(false);

        $total_tax = $base_total_tax_inc - $base_total_tax_exc;

        if ($total_tax < 0)
            $total_tax = 0;

        $currency = new JeproshopCurrencyModelCurrency($this->currency_id);

        $products = $this->getProducts($refresh);
        $gift_products = array();
        $cart_rules = $this->getCartRules();
        $total_shipping = $this->getTotalShippingCost();
        $total_shipping_tax_exc = $this->getTotalShippingCost(null, false);
        $total_products_wt = $this->getOrderTotal(true, JeproshopCartModelCart::ONLY_PRODUCTS);
        $total_products = $this->getOrderTotal(false, JeproshopCartModelCart::ONLY_PRODUCTS);
        $total_discounts = $this->getOrderTotal(true, JeproshopCartModelCart::ONLY_DISCOUNTS);
        $total_discounts_tax_exc = $this->getOrderTotal(false, JeproshopCartModelCart::ONLY_DISCOUNTS);

        // The cart content is altered for display
        foreach ($cart_rules as &$cart_rule){
            // If the cart rule is automatic (without any code) and include free shipping, it should not be displayed as a cart rule but only set the shipping cost to 0
            if ($cart_rule->free_shipping && (empty($cart_rule->code) || preg_match('/^'. JeproshopCartRuleModelCartRule::JEPROSHOP_BO_ORDER_CODE_PREFIX.'[0-9]+/', $cart_rule->code))){
                $cart_rule->value_real -= $total_shipping;
                $cart_rule->value_tax_exc -= $total_shipping_tax_exc;
                $cart_rule->value_real = JeproshopValidator::roundPrice($cart_rule->value_real, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                $cart_rule->value_tax_exc = JeproshopValidator::roundPrice($cart_rule->value_tax_exc, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                if ($total_discounts > $cart_rule->value_real)
                    $total_discounts -= $total_shipping;
                if ($total_discounts_tax_exc > $cart_rule->value_tax_exc)
                    $total_discounts_tax_exc -= $total_shipping_tax_exc;

                // Update total shipping
                $total_shipping = 0;
                $total_shipping_tax_exc = 0;
            }

            if ($cart_rule->gift_product) {
                foreach ($products as $key => &$product) {
                    if (empty($product->gift) && $product->product_id == $cart_rule->gift_product && $product->product_attribute_id == $cart_rule->gift_product_attribute) {
                        // Update total products
                        $total_products_wt = JeproshopValidator::roundPrice($total_products_wt - $product->price_wt, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $total_products = JeproshopValidator::roundPrice($total_products - $product->price, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);

                        // Update total discounts
                        $total_discounts = JeproshopValidator::roundPrice($total_discounts - $product->price_wt, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $total_discounts_tax_exc = JeproshopValidator::roundPrice($total_discounts_tax_exc - $product->price, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);

                        // Update cart rule value
                        $cart_rule->value_real = JeproshopValidator::roundPrice($cart_rule->value_real - $product->price_wt, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $cart_rule->value_tax_exc = JeproshopValidator::roundPrice($cart_rule->value_tax_exc - $product->price, (int)$context->currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);

                        // Update product quantity
                        $product->total_wt = JeproshopValidator::roundPrice($product->total_wt - $product->price_wt, (int)$currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $product->total = JeproshopValidator::roundPrice($product->total - $product->price, (int)$currency->decimals * COM_JEPROSHOP_PRICE_DISPLAY_PRECISION);
                        $product->cart_quantity--;

                        if (!$product->cart_quantity)
                            unset($products[$key]);

                        // Add a new product line
                        $gift_product = $product;
                        $gift_product->cart_quantity = 1;
                        $gift_product->price = 0;
                        $gift_product->price_wt = 0;
                        $gift_product->total_wt = 0;
                        $gift_product->total = 0;
                        $gift_product->gift = true;
                        $gift_products[] = $gift_product;

                        break; // One gift product per cart rule
                    }
                }
            }
        }

        foreach ($cart_rules as $key => &$cart_rule)
            if ($cart_rule->value_real == 0)
                unset($cart_rules[$key]);

        return array(
            'delivery' => $delivery,
            'delivery_state' => JeproshopStateModelState::getNameById($delivery->state_id),
            'invoice' => $invoice,
            'invoice_state' => JeproshopStateModelState::getNameById($invoice->state_id),
            'formattedAddresses' => $formatted_addresses,
            'products' => array_values($products),
            'gift_products' => $gift_products,
            'discounts' => array_values($cart_rules),
            'is_virtual_cart' => (int)$this->isVirtualCart(),
            'total_discounts' => $total_discounts,
            'total_discounts_tax_exc' => $total_discounts_tax_exc,
            'total_wrapping' => $this->getOrderTotal(true, JeproshopCartModelCart::ONLY_WRAPPING),
            'total_wrapping_tax_exc' => $this->getOrderTotal(false, JeproshopCartModelCart::ONLY_WRAPPING),
            'total_shipping' => $total_shipping,
            'total_shipping_tax_exc' => $total_shipping_tax_exc,
            'total_products_wt' => $total_products_wt,
            'total_products' => $total_products,
            'total_price' => $base_total_tax_inc,
            'total_tax' => $total_tax,
            'total_price_without_tax' => $base_total_tax_exc,
            'is_multi_address_delivery' => $this->isMultiAddressDelivery() || ((int)$app->input->get('multi-shipping') == 1),
            'free_ship' => $total_shipping ? 0 : 1,
            'carrier' => new JeproshopCarrierModelCarrier($this->carrier_id, $lang_id),
        );
    }
}


class JeproshopCartRuleModelCartRule extends JModelLegacy
{
    /* Filters used when retrieving the cart rules applied to a cart of when calculating the value of a reduction */
    const JEPROSHOP_FILTER_ACTION_ALL = 1;
    const JEPROSHOP_FILTER_ACTION_SHIPPING = 2;
    const JEPROSHOP_FILTER_ACTION_REDUCTION = 3;
    const JEPROSHOP_FILTER_ACTION_GIFT = 4;
    const JEPROSHOP_FILTER_ACTION_ALL_NO_CAP = 5;

    const JEPROSHOP_BO_ORDER_CODE_PREFIX = 'BO_ORDER_';

    public $cart_rule_id;
    public $name;
    public $customer_id;
    public $date_from;
    public $date_to;
    public $description;
    public $quantity = 1;
    public $quantity_per_user = 1;
    public $priority = 1;
    public $partial_use = 1;
    public $code;
    public $minimum_amount;
    public $minimum_amount_tax;
    public $minimum_amount_currency;
    public $minimum_amount_shipping;
    public $country_restriction;
    public $carrier_restriction;
    public $group_restriction;
    public $cart_rule_restriction;
    public $product_restriction;
    public $shop_restriction;
    public $free_shipping;
    public $reduction_percent;
    public $reduction_amount;
    public $reduction_tax;
    public $reduction_currency;
    public $reduction_product;
    public $gift_product;
    public $gift_product_attribute;
    public $high_light;
    public $published = 1;
    public $date_add;
    public $date_upd;

    /* This variable controls that a free gift is offered only once, even when multi-shippping is activated and the same product is delivered in both addresses */
    protected static $only_one_gift = array();

    /**
     * @static
     * @param JeproshopContext|null $context
     * @return mixed
     */
    public static function autoAddToCart(JeproshopContext $context = null){
        if ($context === null){ $context = JeproshopContext::getContext(); }
        if (!JeproshopCartRuleModelCartRule::isFeaturePublished() || !JeproshopValidator::isLoadedObject($context->cart, 'cart_id')){
            return;
        }

        $db = JFactory::getDBO();
        $query = "SELECT cart_rule.* FROM " . $db->quoteName('#__jeproshop_cart_rule') . " AS cart_rule LEFT JOIN " . $db->quoteName('#__jeproshop_cart_rule_shop');
        $query .= " AS cart_rule_shop ON cart_rule.cart_rule_id = cart_rule_shop.cart_rule_id ";
        $group_query = " LEFT JOIN " . $db->quoteName('#__jeproshop_cart_rule_group') . " AS cart_rule_group ON cart_rule.cart_rule_id = cart_rule_group.cart_rule_id ";
        $query .= (!$context->customer->customer_id && JeproshopGroupModelGroup::isFeaturePublished() ? $group_query : "") . " LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_cart_rule_carrier') . " AS cart_rule_carrier ON cart_rule.cart_rule_id = cart_rule_carrier.cart_rule_id ";
        $query .= ($context->cart->carrier_id ? " LEFT JOIN " . $db->quoteName('#__jeproshop_carrier') . " AS carrier ON (carrier.reference_id = cart_rule_carrier.carrier_id AND carrier.deleted = 0)" : "");
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_cart_rule_country') . " AS cart_rule_country ON cart_rule.cart_rule_id = cart_rule_country.";
        $query .= "cart_rule_id WHERE cart_rule.published = 1 AND cart_rule.code = '' AND cart_rule.quantity > 0 AND cart_rule.date_from < '" . date('Y-m-d H:i:s')."'	AND cart_rule.date_to > '";
        $query .= date('Y-m-d H:i:s') . "' AND ( cart_rule.customer_id = 0 " . ($context->customer->customer_id ? " OR cart_rule.customer_id = " . (int)$context->cart->customer_id : "");
        $query .= ") AND ( cart_rule." . $db->quoteName('carrier_restriction') . " = 0 " .($context->cart->carrier_id ? " OR carrier.carrier_id = " . (int)$context->cart->carrier_id : "");
        $query .= " ) AND ( cart_rule." . $db->quoteName('shop_restriction') . " = 0 " . ((JeproshopShopModelShop::isFeaturePublished() && $context->shop->shop_id) ? " OR cart_rule_shop.shop_id = " .(int)$context->shop->shop_id : "");
        $query .= " ) AND ( cart_rule." . $db->quoteName('group_restriction') . " = 0 ";
        if($context->customer->customer_id){
            $query = " OR 0 < ( SELECT customer_group." . $db->quoteName('group_id') . " FROM " . $db->quoteName('#__jeproshop_customer_group') . " AS customer_group INNER JOIN " . $db->quoteName('#__jeproshop_cart_rule_group');
            $query .=" AS cart_rule_group ON customer_group.group_id = cart_rule_group.group_id WHERE cart_rule." . $db->quoteName('cart_rule_id') . " = cart_rule_group." . $db->quoteName('cart_rule_id') . " AND customer_group.";
            $query .= $db->quoteName('customer_id') . " = " . (int)$context->customer->customer_id . " LIMIT 1	)";
        }else{
			if(JeproshopGroupModelGroup::isFeaturePublished()){
                $query .= " OR cart_rule_group." . $db->quoteName('group_id') ." = " . (int)JeproshopSettingModelSetting::getValue('unidentified_group');
            }
        }
        $query .= " ) AND ( cart_rule." . $db->quoteName('`reduction_product') . " <= 0 OR cart_rule." . $db->quoteName('reduction_product') . " IN ( SELECT " . $db->quoteName('product_id') . " FROM ";
        $query .= $db->quoteName('#__jeproshop_cart_product') . " WHERE " . $db->quoteName('cart_id') . " = " . (int)$context->cart->cart_id . " ) ) AND cart_rule.cart_rule_id NOT IN (SELECT cart_rule_id FROM ";
        $query .= $db->quoteName('#__jeproshop_cart_cart_rule') . " WHERE car_id = " . (int)$context->cart->cart_id . ") ORDER BY priority";

        $db->setQuery($query);
        $result = $db->loadObjectList();
        if ($result){
            $cart_rules = ObjectModel::hydrateCollection('CartRule', $result);
            if ($cart_rules){
                foreach ($cart_rules as $cart_rule){
                    if ($cart_rule->checkValidity($context, false, false)){
                        $context->cart->addCartRule($cart_rule->cart_rule_id);
                    }
                }
            }
        }
    }

    public static function autoRemoveFromCart($context = null){
        if (!$context){
            $context = JeproshopContext::getContext();
        }
        if (!JeproshopCartRuleModelCartRule::isFeaturePublished() || !JeproshopValidator::isLoadedObject($context->cart, 'cart_id'))
            return array();

        static $errors = array();
        foreach ($context->cart->getCartRules() as $cart_rule){
            if ($error = $cart_rule['obj']->checkValidity($context, true))
            {
                $context->cart->removeCartRule($cart_rule['obj']->cart_rule_id);
                $context->cart->update();
                $errors[] = $error;
            }
        }
        return $errors;
    }

    /**
     * @static
     * @param $lang_id
     * @param $customer_id
     * @param bool $published
     * @param bool $includeGeneric
     * @param bool $inStock
     * @param JeproshopCartModelCart|null $cart
     * @return array
     */
    public static function getCustomerCartRules($lang_id, $customer_id, $published = false, $includeGeneric = true, $inStock = false, JeproshopCartModelCart $cart = null){
        if (!JeproshopCartRuleModelCartRule::isFeaturePublished()){ return array(); }

        $db = JFactory::getDBO();

        $query = "SELECT * FROM " . $db->quoteName('#_jeproshop_cart_rule') . " AS cart_rule LEFT JOIN " . $db->quoteName('#__jeproshop_cart_rule_lang') . " AS cart_ruler_lang";
        $query .= " ON (cart_rule." . $db->quoteName('cart_rule_id') . " = cart_rule_lang." . $db->quoteName('cart_rule_id') . " AND cart_rule_lang." . $db->quoteName('lang_id');
        $query .= " = " . (int)$lang_id . ") WHERE ( cart_rule." . $db->quoteName('customer_id') . " = " . (int)$customer_id . " OR cart_rule.group_restriction = 1 ";
        $query .= ($includeGeneric ? " OR cart_rule." . $db->quoteName('customer_id') . " = 0"  : "") . ") AND cart_rule.date_from < '" . date('Y-m-d H:i:s') . "' AND cart_rule.date_to > '";
        $query .= date('Y-m-d H:i:s') . "' " . ($published ? "AND cart_rule." . $db->quoteName('published') . " = 1" : "") . ($inStock ? " AND cart_rule." . $db->quoteName('quantity') . " > 0" : "");
        $db->setQuery($query);
        $result = $db->loadObjectList();

        // Remove cart rule that does not match the customer groups
        $customerGroups = JeproshopCustomerModelCustomer::getStaticGroups($customer_id);

        foreach ($result as $key => $cart_rule) {
            if ($cart_rule->group_restriction) {
                $cartRuleGroups = Db::getInstance()->executeS('SELECT id_group FROM ' . _DB_PREFIX_ . 'cart_rule_group WHERE id_cart_rule = ' . (int)$cart_rule['id_cart_rule']);
                foreach ($cartRuleGroups as $cartRuleGroup) {
                    if (in_array($cartRuleGroup->group_id, $customerGroups)) {
                        continue 2;
                    }
                }
                unset($result[$key]);
            }
        }

        foreach ($result as &$cart_rule) {
            if ($cart_rule->quantity_per_user){
                $quantity_used = Order::getDiscountsCustomer((int)$customer_id, (int)$cart_rule->cart_rule_id);
                if (isset($cart) && isset($cart)) {
                    $quantity_used += $cart->getDiscountsCustomer((int)$cart_rule->cart_rule_id);
                }
                $cart_rule->quantity_for_user = $cart_rule->quantity_per_user - $quantity_used;
            } else {
                $cart_rule->quantity_for_user = 0;
            }
        }
        unset($cart_rule);

        foreach ($result as $key => $cart_rule)
            if ($cart_rule->shop_restriction)
            {
                $cartRuleShops = Db::getInstance()->executeS('SELECT id_shop FROM '._DB_PREFIX_.'cart_rule_shop WHERE id_cart_rule = '.(int)$cart_rule['id_cart_rule']);
                foreach ($cartRuleShops as $cartRuleShop)
                    if (Shop::isFeatureActive() && ($cartRuleShop->shop_id == JeproshopContext::getContext()->shop->shop_id))
                        continue 2;
                unset($result[$key]);
            }

        if (isset($cart) && isset($cart->cart_id)){
            foreach ($result as $key => $cart_rule){
                if ($cart_rule->product_restriction){
                    $cr = new JeproshopCartRuleModelCartRule((int)$cart_rule->cart_rule_id);
                    $restriction = $cr->checkProductRestrictions(JeproshopContext::getContext(), false, false);
                    if ($restriction !== false)
                        continue;
                    unset($result[$key]);
                }
            }
        }

        foreach ($result as $key => $cart_rule)
            if ($cart_rule['country_restriction'])
            {
                $countries = Db::getInstance()->ExecuteS('
					SELECT `id_country`
					FROM `'._DB_PREFIX_.'address`
					WHERE `customer_id` = '.(int)$customer_id.'
					AND `deleted` = 0'
                );

                if (is_array($countries) && count($countries))
                    foreach($countries as $country)
                    {
                        $id_cart_rule = (bool)Db::getInstance()->getValue('
							SELECT crc.id_cart_rule
							FROM '._DB_PREFIX_.'cart_rule_country crc
							WHERE crc.id_cart_rule = '.(int)$cart_rule['id_cart_rule'].'
							AND crc.id_country = '.(int)$country['id_country']);
                        if (!$id_cart_rule)
                            unset($result[$key]);
                    }
            }

        // Retro-compatibility with 1.4 discounts
        foreach ($result as &$cart_rule) {
            $cart_rule['value'] = 0;
            $cart_rule['minimal'] = Tools::convertPriceFull($cart_rule['minimum_amount'], new Currency($cart_rule['minimum_amount_currency']), Context::getContext()->currency);
            $cart_rule['cumulable'] = !$cart_rule['cart_rule_restriction'];
            $cart_rule['id_discount_type'] = false;
            if ($cart_rule['free_shipping'])
                $cart_rule['id_discount_type'] = Discount::FREE_SHIPPING;
            elseif ($cart_rule['reduction_percent'] > 0)
            {
                $cart_rule['id_discount_type'] = Discount::PERCENT;
                $cart_rule['value'] = $cart_rule['reduction_percent'];
            }
            elseif ($cart_rule['reduction_amount'] > 0)
            {
                $cart_rule->discount_type_id = Discount::AMOUNT;
                $cart_rule['value'] = $cart_rule['reduction_amount'];
            }
        }
        unset($cart_rule);

        return $result;
    }

    /**
     * @static
     * @return bool
     */
    public static function isFeaturePublished(){
        static $is_feature_active = null;
        if ($is_feature_active === null)
            $is_feature_active = (bool)JeproshopSettingModelSetting::getValue('cart_rule_feature_active');
        return $is_feature_active;
    }
}