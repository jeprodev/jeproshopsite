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

class JeproshopProductModelProduct extends JModelLegacy
{
    /** @var int product id */
    public $product_id;

    public $product_redirected_id;

    /** @var int default category id */
    public $default_category_id;

    /** @var int default shop id */
    public $default_shop_id;

    /** @var int manufacturer  */
    public $manufacturer_id;

    /** @var int supplier  */
    public $supplier_id;

    public $lang_id ;

    /** @var int  developer id*/
    public $developer_id;

    /** @var array shop list id */
    public $shop_list_id;

    public $shop_id;

    public $name = array();

    public $ecotax;

    public $unity = null;

    public $tax_rules_group_id = 1;

    /**
     * We keep this variable for retro_compatibility for themes
     * @deprecated 1.5.0
     */
    public $default_color_id = 0;

    public $meta_title = array();
    public $meta_keywords = array();
    public $meta_description = array();

    /** @var string Friendly URL */
    public $link_rewrite;

    /**
     * @since 1.5.0
     * @var boolean Tells if the product uses the advanced stock management
     */
    public $advanced_stock_management = 0;
    public $out_of_stock;
    public $depends_on_stock;

    public $isFullyLoaded = false;
    public $cache_is_pack;
    public $cache_has_attachments;
    public $is_virtual;
    public $cache_default_attribute;

    /**
     * @var string If product is populated, this property contain the rewrite link of the default category
     */
    public $category;

    /** @var string Tax name */
    public $tax_name;

    /** @var string Tax rate */
    public $tax_rate;

    /** @var DateTime date_add */
    public $date_add;

    /** @var DateTime date_upd */
    public $date_upd;

    public $manufacturer_name;

    public $supplier_name;

    public $developer_name;

    /** @var string Long description */
    public $description;

    /** @var string Short description */
    public $short_description;

    /** @var float Price in euros */
    public $price = 0;
    public $base_price = 0;

    /** @var float price for product's unity */
    public $unit_price;

    /** @var float price for product's unity ratio */
    public $unit_price_ratio = 0;

    /** @var float Additional shipping cost */
    public $additional_shipping_cost = 0;

    /** @var float Wholesale Price in euros */
    public $wholesale_price = 0;

    /** @var boolean on_sale */
    public $on_sale = false;

    /** @var boolean online_only */
    public $online_only = false;

    /** @var integer Quantity available */
    public $quantity = 0;

    /** @var integer Minimal quantity for add to cart */
    public $minimal_quantity = 1;

    /** @var string available_now */
    public $available_now;

    /** @var string available_later */
    public $available_later;

    /** @var string Reference */
    public $reference;

    /** @var string Supplier Reference */
    public $supplier_reference;

    /** @var string Location */
    public $location;

    /** @var string Width in default width unit */
    public $width = 0;

    /** @var string Height in default height unit */
    public $height = 0;

    /** @var string Depth in default depth unit */
    public $depth = 0;

    /** @var string Weight in default weight unit */
    public $weight = 0;

    /** @var string Ean-13 barcode */
    public $ean13;

    /** @var string Upc barcode */
    public $upc;

    /** @var boolean Product status */
    public $quantity_discount = 0;

    /** @var boolean Product customization */
    public $customizable;

    /** @var boolean Product is new */
    public $is_new = null;

    public $uploadable_files;

    /** @var int Number of text fields */
    public $text_fields;

    /** @var boolean Product status */
    public $published = true;

    /** @var boolean Product status */
    public $redirect_type = '';

    /** @var boolean Product available for order */
    public $available_for_order = true;

    /** @var enum Product condition (new, used, refurbished) */
    public $condition;

    /** @var boolean Show price of Product */
    public $show_price = true;

    /** @var boolean is the product indexed in the search index? */
    public $indexed = 0;

    /** @var string Object available order date */
    public $available_date = '0000-00-00';

    /** @var string ENUM('both', 'catalog', 'search', 'none') front office visibility */
    public $visibility;

    /*** @var array Tags */
    public $tags;

    public $specific_price;

    /**
     * Note:  prefix is "PRODUCT_TYPE" because TYPE_ is used in ObjectModel (definition)
     */
    const SIMPLE_PRODUCT = 1;
    const PACKAGE_PRODUCT = 2;
    const VIRTUAL_PRODUCT = 3;

    const CUSTOMIZE_FILE = 0;
    const CUSTOMIZE_TEXT_FIELD = 1;

    public $product_type = self::SIMPLE_PRODUCT;

    public static $_taxCalculationMethod = null;
    protected static $_prices = array();
    protected static $_pricesLevel2 = array();
    protected static $_in_category = array();
    protected static $_cart_quantity = array();
    protected static $_tax_rules_group = array();
    protected static $_cacheFeatures = array();
    protected static $_frontFeaturesCache = array();
    protected static $_productPropertiesCache = array();

    /** @var array cache stock data in getStock() method */
    protected static $cacheStock = array();

    /** definition element */
    private $multiLangShop = true;

    private $multiLang = true;

    private $pagination;

    public function __construct($product_id = NULL, $full = FALSE, $lang_id = NULL, $shop_id = NULL, JeproshopContext $context = NULL){
        $db = JFactory::getDBO();

        if($lang_id !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($lang_id) ? (int)$lang_id : JeproshopSettingModelSetting::getValue('default_lang'));
        }

        if($shop_id && $this->isMultiShop()){
            $this->shop_id = (int)$shop_id;
            $this->getShopFromContext = FALSE;
        }

        if($this->isMultiShop() && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if($product_id){
            $cache_id = 'jeproshop_product_model_' . $product_id . '_' . $lang_id . '_' . $shop_id;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_product') . " AS product ";
                $where = "";
                /** get language information **/
                if($lang_id){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ";
                    $query .= "ON (product.product_id = product_lang.product_id AND product_lang.lang_id = " . (int)$lang_id . ") ";
                    if($this->shop_id && !(empty($this->multiLangShop))){
                        $where = " AND product_lang.shop_id = " . $this->shop_id;
                    }
                }

                /** Get shop informations **/
                if(JeproshopShopModelShop::isTableAssociated('product')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_product_shop') . " AS product_shop ON (";
                    $query .= "product.product_id = product_shop.product_id AND product_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE product.product_id = " . (int)$product_id . $where;

                $db->setQuery($query);
                $product_data = $db->loadObject();

                if($product_data){
                    if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_product_lang');
                        $query .= " WHERE product_id = " . (int)$product_id;

                        $db->setQuery($query);
                        $product_lang_data = $db->loadObjectList();
                        if($product_lang_data){
                            foreach ($product_lang_data as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'product_id'){
                                        if(!isset($product_data->{$key}) || !is_array($product_data->{$key})){
                                            $product_data->{$key} = array();
                                        }
                                        $product_data->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cache_id, $product_data);
                }
            }else{
                $product_data = JeproshopCache::retrieve($cache_id);
            }

            if($product_data){
                $product_data->product_id = $product_id;
                foreach($product_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        if(!$context){
            $context = JeproshopContext::getContext();
        }

        if($full && $this->product_id){
            $this->isFullyLoaded = $full;
            $this->manufacturer_name = JeproshopManufacturerModelManufacturer::getNameById((int)  $this->manufacturer_id);
            $this->supplier_name = JeproshopSupplierModelSupplier::getNameById((int)  $this->supplier_id);
            if($this->getProductType() == self::VIRTUAL_PRODUCT){
                $this->developer_name = JeproshopDeveloperModelDeveloper::getNameById((int)$this->developer_id);
            }
            $address = NULL;
            if(is_object($context->cart) && $context->cart->{JeproshopSettingModelSetting::getValue('tax_address_type')} != null){
                $address = $context->cart->{JeproshopSettingModelSetting::getValue('tax_address_type')};
            }

            $this->tax_rate = $this->getTaxesRate(new JeproshopAddressModelAddress($address));

            $this->is_new = $this->isNew();

            $this->base_price = $this->price;

            $this->price = JeproshopProductModelProduct::getStaticPrice((int)$this->product_id, false, null, 6, null, false, true, 1, false, null, null, null, $this->specific_price);
            $this->unit_price = ($this->unit_price_ratio != 0 ? $this->price / $this->unit_price_ratio : 0);
            if($this->product_id){
                $this->tags = JeproshopTagModelTag::getProductTags((int)$this->product_id);
            }
            $this->loadStockData();
        }

        if($this->default_category_id){
            $this->category = JeproshopCategoryModelCategory::getLinkRewrite((int)$this->default_category_id, (int)$lang_id);
        }
    }

    public function getProductType(){
        if(!$this->product_id){
            return JeproshopProductModelProduct::SIMPLE_PRODUCT;
        }

        if(JeproshopProductPack::isPack($this->product_id)){
            return JeproshopProductModelProduct::PACKAGE_PRODUCT;
        }

        if($this->is_virtual){
            return JeproshopProductModelProduct::VIRTUAL_PRODUCT;
        }
        return JeproshopProductModelProduct::SIMPLE_PRODUCT;
    }

    public static function getTaxCalculationMethod($customer_id = null){
        if (self::$_taxCalculationMethod === null || $customer_id !== null){
            JeproshopProductModelProduct::initPricesComputation($customer_id);
        }
        return (int)self::$_taxCalculationMethod;
    }

    public static function isAvailableWhenOutOfStock($out_of_stock){
        $return = $out_of_stock ? (int)JeproshopSettingModelSetting::getValue('allow_out_of_stock_ordering') : (int)$out_of_stock;
        return true; //!JeproshopSettingModelSetting::getValue('stock_management') ? true : $return;
    }

    public function isAssociatedToShop($shop_id = NULL){
        if($shop_id === NULL){
            $shop_id = (int)JeproshopContext::getContext()->shop->shop_id;
        }

        $cache_id = 'jeproshop_shop_model_product_' . (int)$this->product_id . '_' . (int)$this->shop_id;
        if(!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT shop_id FROM " . $db->quoteName('#__jeproshop_product_shop') . " WHERE " . $db->quoteName('product_id') . " = " . (int)$this->product_id;
            $query .= " AND shop_id = " . (int)$shop_id;

            $db->setQuery($query);
            $result = (bool)$db->loadResult();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    public static function initPricesComputation($customer_id = null){
        if ($customer_id){
            $customer = new JeproshopCustomerModelCustomer((int)$customer_id);
            if (!JeproshopTools::isLoadedObject($customer, 'customer_id')){
                die(Tools::displayError());
            }
            self::$_taxCalculationMethod = JeproshopGroupModelGroup::getPriceDisplayMethod((int)$customer->default_group_id);
            $cur_cart = JeproshopContext::getContext()->cart;
            $address_id = 0;
            if (JeproshopTools::isLoadedObject($cur_cart, 'cart_id')){
                $address_id = (int)$cur_cart->{JeproshopSettingModelSetting::getValue('tax_address_type')};
            }
            $address_info = JeproshopAddressModelAddress::getCountryAndState($address_id);

            if (self::$_taxCalculationMethod != COM_JEPROSHOP_TAX_EXCLUDED && !empty($address_info->vat_number)
                && $address_info->country_id !=JeproshopSettingModelSetting::getValue('country_vat_number') &&JeproshopSettingModelSetting::getValue('vat_number_management')){
                self::$_taxCalculationMethod = COM_JEPROSHOP_TAX_EXCLUDED;
            }
        } else{
            self::$_taxCalculationMethod = JeproshopGroupModelGroup::getPriceDisplayMethod(JeproshopGroupModelGroup::getCurrent()->group_id);
        }
    }

    public function getAttributesGroups($lang_id){
        if(!JeproshopCombinationModelCombination::isFeaturePublished()){ return array(); }

        $db = JFactory::getDBO();
        $query = "SELECT attribute_group." . $db->quoteName('attribute_group_id') . ", attribute_group." . $db->quoteName('is_color_group');
        $query .= ", attribute_group_lang." . $db->quoteName('name') . " AS group_name, attribute_group_lang." . $db->quoteName('public_name');
        $query .= " AS public_group_name, attribute." . $db->quoteName('attribute_id') . ", attribute_lang." . $db->quoteName('name') . " AS ";
        $query .= "attribute_name, attribute." . $db->quoteName('color') . " AS attribute_color, product_attribute_shop." . $db->quoteName('product_attribute_id');
        $query .= ", IFNULL(stock.quantity, 0) AS quantity, product_attribute_shop." . $db->quoteName('price') .  ", product_attribute_shop.";
        $query .= $db->quoteName('ecotax') . ", product_attribute_shop." . $db->quoteName('weight') . ", product_attribute_shop." . $db->quoteName('default_on');
        $query .= ", product_attribute." . $db->quoteName('reference') . ", product_attribute_shop." .  $db->quoteName('unit_price_impact');
        $query .= ", product_attribute_shop." . $db->quoteName('minimal_quantity') . ", product_attribute_shop." .  $db->quoteName('available_date');
        $query .= ", attribute_group." .  $db->quoteName('group_type') . " FROM " .  $db->quoteName('#__jeproshop_product_attribute') . " AS ";
        $query .= " product_attribute " . JeproshopShopModelShop::addSqlAssociation('product_attribute'). JeproshopProductModelProduct::sqlStock('product_attribute');
        $query .= " LEFT JOIN " .  $db->quoteName('#__jeproshop_product_attribute_combination') . " AS  product_attribute_combination ON ( product_attribute_combination.";
        $query .=  $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . ") LEFT JOIN " .  $db->quoteName('#__jeproshop_attribute');
        $query .= " AS attribute ON ( attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination." .  $db->quoteName('attribute_id') . " ) LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group ON ( attribute_group." . $db->quoteName('attribute_group_id') . " = attribute.";
        $query .= $db->quoteName('attribute_group_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang') . " AS attribute_lang ON ( attribute." . $db->quoteName('attribute_id');
        $query .= " = attribute_lang." . $db->quoteName('attribute_id') . " ) LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ON ( attribute_group.";
        $query .= $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') . ") " . JeproshopShopModelShop::addSqlAssociation('attribute');
        $query .= " WHERE product_attribute." . $db->quoteName('product_id') . " = " . (int)$this->product_id . " AND attribute_lang." . $db->quoteName('lang_id') . " = ". (int)$lang_id ;
        $query .= " AND attribute_group_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . " GROUP BY attribute_group_id, product_attribute_id ORDER BY attribute_group.";
        $query .= $db->quoteName('position') . " ASC, attribute." . $db->quoteName('position') . " ASC, attribute_group_lang." . $db->quoteName('name') . " ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getCombinationImages($lang_id){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return false; }

        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('product_attribute_id') . " FROM " . $db->quoteName('#__jeproshop_product_attribute') . " WHERE " . $db->quoteName('product_id') . " = " .(int)$this->product_id;

        $db->setQuery($query);
        $product_attributes = $db->loadObjectList();

        if (!$product_attributes){ return false; }

        $ids = array();

        foreach ($product_attributes as $product_attribute)
            $ids[] = (int)$product_attribute->product_attribute_id;

        $query = "SELECT product_attribute_image." . $db->quoteName('image_id') . ", product_attribute_image." . $db->quoteName('product_attribute_id') . ", image_lang.";
        $query .=  $db->quoteName('legend') . " FROM " . $db->quoteName('#__jeproshop_product_attribute_image') . " AS product_attribute_image LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image_lang." . $db->quoteName('image_id') . " = product_attribute_image.";
        $query .= $db->quoteName('image_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_image') . " AS image ON (image." . $db->quoteName('image_id') ;
        $query .= " = product_attribute_image." . $db->quoteName('image_id') . ") WHERE product_attribute_image." . $db->quoteName('product_attribute_id') ;
        $query .= " IN (" .implode(', ', $ids). ") AND image_lang." . $db->quoteName('lang_id') . " = " .(int)$lang_id . " ORDER by image." . $db->quoteName('position');

        $db->setQuery($query);
        $result = $db->loadObjectList();

        if (!$result){ return false; }


        return $result;
    }

    /**
     * @todo Remove existing module condition
     * @param int $product_id
     * @return array
     */
    public static function getAttributesInformationsByProduct($product_id) {
        $db = JFactory::getDBO();
        $context = JeproshopContext::getContext();
        //todo if block_layered module is installed we check if user has set custom attribute name
        if ($context->controller->isModuleInstalled('mod_block_layered') && $context->controller->isModuleEnabled('mod_block_layered')) {
            $query = " SELECT DISTINCT layered_attribute." . $db->quoteName('attribute_id') . ", layered_attribute." . $db->quoteName('url_name') . " AS attribute_url FROM ";
            $query .= $db->quoteName('#__jeproshop_attribute') . " AS attribute LEFT JOIN " .$db->quoteName('#__jeproshop_product_attribute_combination') . " AS product_attribute_combination ";
            $query .= " ON (attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination." . $db->quoteName('attribute_id') . " ) LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute');
            $query .= " AS product_attribute ON (product_attribute_combination." . $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . ") ";
            $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute') . " LEFT JOIN " . $db->quoteName('#__jeproshop_layered_indexable_attribute_lang_value') . " AS layered_attribute ON (layered_attribute.";
            $query .= $db->quoteName('attribute_id') . " = attribute." . $db->quoteName('attribute_id') . " AND layered_attribute." . $db->quoteName('lang_id') . " = " . (int)JeproshopContext::getContext()->language->lang_id;
            $query .= ") WHERE layered_attribute." . $db->quoteName('url_name') . " IS NOT NULL AND layered_attribute." . $db->quoteName('url_name') . " != '' AND product_attribute." . $db->quoteName('product_id') . " = " .(int)$product_id;

            $db->setQuery($query);
            $nb_custom_values = $db->loadObjectList();

            if (!empty($nb_custom_values)) {
                $tab_attribute_id = array();
                foreach ($nb_custom_values as $attribute) {
                    $tab_attribute_id[] = $attribute->attribute_id;

                    $query = "SELECT layered_attribute_group." . $db->quoteName('attribute_group_id') . ", layered_attribute_group." . $db->quoteName('url_name') . " AS ";
                    $query .= " layered_attribute_url FROM " . $db->quoteName('#__jeproshop_layered_indexable_attribute_group_lang_value') . " AS layered_attribute_group ";
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_attribute') . " AS attribute ON (attribute." . $db->quoteName('attribute_group_id') . " = layered_attribute_group.";
                    $query .= $db->quoteName('attribute_group_id') . ") WHERE attribute." . $db->quoteName('attribute_id') . " = " .(int)$attribute->attribute_id . " AND ";
                    $query .= "layered_attribute_group." . $db->quoteName('lang_id') . " = " .(int)JeproshopContext::getContext()->language->lang_id . " AND layered_attribute_group.";
                    $query .= $db->quoteName('url_name') . " IS NOT NULL AND layered_attribute_group." . $db->quoteName('url_name') . " != '' ";

                    $db->setQuery($query);
                    $group = $db->loadObjectList();

                    if (empty($group)){
                        $query = "SELECT attribute_group_lang." . $db->quoteName('attribute_group_id') . ", attribute_group_lang." . $db->quoteName('name') . " AS group_name";
                        $query .= "	FROM " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang LEFT JOIN " . $db->quoteName('#__jeproshop_attribute');
                        $query .= " AS attribute ON (attribute." . $db->quoteteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') ;
                        $query .= ") WHERE attribute." . $db->quoteName('attribute_id') . " = " .(int)$attribute->attribute_id . " AND attribute_group_lang." . $db->quoteName('lang_id');
                        $query .= " = " .(int)JeproshopContext::getContext()->language->lang_id . " AND attribute_group_lang." . $db->quoteName('name') . " IS NOT NULL";
                        $db->setQuery($query);
                        $group = $db->loadObjectList();
                    }
                    $result[] = array_merge($attribute, $group[0]);
                }

				$query = "SELECT DISTINCT attribute." . $db->quoteName('attribute_id') . ", attribute." . $db->quoteName('attribute_group_id') . ", attribute_lang.";
                $query .= $db->quoteName('name') . " AS attribute, attribute_group_lang." . $db->quoteName('name') . " AS group_name FROM " . $db->quoteName('#__jeproshop_attribute');
                $query .= " AS attribute LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang') . " AS attribute_lang ON (attribute." . $db->quoteName('attribute_id');
                $query .= " = attribute_lang." . $db->quoteName('attribute_id') . " AND attribute_lang." . $db->quoteName('lang_id') . " = " . (int)JeproshopContext::getContext()->language->lang_id;
                $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') .  " AS attribute_group_lang ON (attribute." . $db->quoteName('attribute_group_id');
                $query .= " = attribute_group_lang." . $db->quoteName('attribute_group_id') . " AND attribute_group_lang." . $db->quoteName('lang_id') . " = " . (int)JeproshopContext::getContext()->language->lang_id;
                $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_combination') . " AS product_attribute_combination ON (attribute." . $db->quoteName('attribute_id') . " = ";
                $query .= "product_attribute_combination." . $db->quoteName('attribute_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ON (";
                $query .= "product_attribute_combination." . $db->quoteName('product_attribute_id') . " = product_attribute." . $db->quoteName('product_attribute_id') . ") ";
                $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute'). JeproshopShopModelShop::addSqlAssociation('attribute', 'pac') . "
				WHERE product_attribute" . $db->quoteName('product_id') . " = ".(int)$product_id. "	AND attribute." . $db->quoteName('attribute_id') . " NOT IN(" .implode(', ', $tab_attribute_id).")";
                $db->setQuery($query);
                $values_not_custom = $db->loadObjectList();
                $result = array_merge($values_not_custom, $result);
            } else {
                $query = "SELECT DISTINCT attribute." . $db->quoteName('attribute_id') . ", attribute." . $db->quoteName('attribute_group_id') . ", attribute_lang." . $db->quoteName('name') . " AS attribute_name,";
                $query .= " attribute_group_lang." . $db->quoteName('name') . " AS group_name FROM " . $db->quoteName('#__jeproshop_attribute') . " AS attribute LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_lang');
                $query .= " AS attribute_lang ON (attribute." . $db->quoteName('attribute_id') . " = attribute_lang." . $db->quoteName('attribute_id') . " AND attribute_lang." . $db->quoteName('lang_id') . " = ";
                $query .= (int)JeproshopContext::getContext()->language->lang_id . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang') . " AS attribute_group_lang ON (attribute." . $db->quoteName('attribute_group_id');
                $query .= " = attribute_group_lang." . $db->quoteName('attribute_group_id') . " AND attribute_group_lang." . $db->quoteName('lang_id') . " = ".(int)JeproshopContext::getContext()->language->lang_id;
                $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_combination') . " AS product_attribute_combination ON (attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination.";
                $query .= $db->quoteName('attribute_id') . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ON (product_attribute_combination." . $db->quoteName('product_attribute_id');
                $query .= " = product_attribute." . $db->quoteName('product_attribute_id') . ") " . JeproshopShopModelShop::addSqlAssociation('product_attribute') . " "
				.JeproshopShopModelShop::addSqlAssociation('attribute', 'product_attribute_combination'). " WHERE product_attribute." . $db->quoteName('product_id') . " = ".(int)$product_id;

                $db->setQuery($query);
                $result = $db->loadObjectList();
            }
        } else {
            $query = "SELECT DISTINCT attribute." . $db->quoteName('attribute_id') . ", attribute." . $db->quoteName('attribute_group_id') . ", attribute_lang." . $db->quoteName('name');
            $query .= " AS attribute_name, attribute_group_lang." . $db->quoteName('name') . " AS group_name FROM" . $db->quoteName('#__jeproshop_attribute') . " AS attribute LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_attribute_lang') . " AS attribute_lang ON (attribute." . $db->quoteName('attribute_id') . " = attribute_lang." . $db->quoteName('attribute_id');
            $query .= " AND attribute_lang." . $db->quoteName('lang_id') . " = " . (int)JeproshopContext::getContext()->language->lang_id . ") LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_group_lang');
            $query .= " AS attribute_group_lang ON (attribute." . $db->quoteName('attribute_group_id') . " = attribute_group_lang." . $db->quoteName('attribute_group_id') . " AND attribute_group_lang.";
            $query .= $db->quoteName('lang_id') . " = " .(int)JeproshopContext::getContext()->language->lang_id . ") LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_combination');
            $query .= " AS  product_attribute_combination ON (attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination." . $db->quoteName('attribute_id') . ") LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ON (product_attribute_combination." . $db->quoteName('product_attribute_id') . " = product_attribute.";
            $query .= $db->quoteName('product_attribute_id') . ") " . JeproshopShopModelShop::addSqlAssociation('product_attribute') . " LEFT JOIN " . $db->quoteName('#__jeproshop_attribute_shop');
            $query .= " AS product_attribute_combination_shop ON(product_attribute_combination_shop." . $db->quoteName('attribute_id') . " = product_attribute_combination." . $db->quoteName('attribute_id');
            if(JeproshopShopModelShop::$context_shop_id ){
                $query .= " AND product_attribute_combination_shop." . $db->quoteName('shop_id') . " = " . JeproshopShopModelShop::$context_shop_id;
            }elseif(JeproshopShopModelShop::checkDefaultShopId()){
                $query .= " AND product_attribute_combination_shop." . $db->quoteName('shop_id') . " IN( " . implode(', ', JeproshopShopModelShop::getContextShopListId()) . ") ";
            }
            $query .= ") WHERE product_attribute." . $db->quoteName('product_id') . " = " .(int)$product_id;
            $db->setQuery($query);
            $result = $db->loadObjectList();
        }
        return $result;
    }

    /**
     * Get product accessories
     *
     * @param integer $lang_id Language id
     * @param bool $published
     * @param JeproshopContext $context
     * @return array Product accessories
     */
    public function getAccessories($lang_id, $published = true, JeproshopContext $context = null){
        if (!$context){
            $context = JeproshopContext::getContext();
        }
        $db = JFactory::getDBO();

        $query = "SELECT product.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, product_lang." . $db->quoteName('description');
        $query .= ", product_lang." . $db->quoteName('short_description') . ", product_lang." . $db->quoteName('link_rewrite') . ", product_lang.";
        $query .= $db->quoteName('meta_description') . ", product_lang." . $db->quoteName('meta_keywords') . ", product_lang." . $db->quoteName('meta_title');
        $query .= ", product_lang." . $db->quoteName('name') . ", product_lang." . $db->quoteName('available_now') . ", product_lang." . $db->quoteName('available_later');
        $query .= ", MAX(image_shop." . $db->quoteName('image_id') . ") AS image_id, image_lang." . $db->quoteName('legend') . ", manufacturer." . $db->quoteName('name');
        $query .= " AS manufacturer_name, category_lang." . $db->quoteName('name') . " AS default_category, DATEDIFF( product." . $db->quoteName('date_add');
        $number_days_new_product = JeproshopSettingModelSetting::getValue('number_days_new_product');
        $query .= ", DATE_SUB( NOW(), INTERVAL " .(JeproshopTools::isUnsignedInt($number_days_new_product) ? $number_days_new_product : 20)." DAY ) ) > 0 AS new ";
        $query .= " FROM " . $db->quoteName('#__jeproshop_accessory') . " AS accessory LEFT JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON product.";
        $query .= $db->quoteName('product_id') . " =accessory." . $db->quoteName('product_2_id') . JeproshopShopModelShop::addSqlAssociation('product') . "	LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ON (product." . $db->quoteName('product_id') . " = product_lang." . $db->quoteName('product_id');
        $query .= "	AND product_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang') . " ) LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON (product_shop." . $db->quoteName('default_category_id') . " = category_lang.";
        $query .= $db->quoteName('category_id') . " AND category_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang');
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_image') . " AS image ON (image." . $db->quoteName('product_id') . " = product." . $db->quoteName('product_id');
        $query .= ") " . JeproshopShopModelShop::addSqlAssociation('image', false, 'image_shop.cover=1') . " LEFT JOIN " . $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ";
        $query .= "ON (image." . $db->quoteName('image_id') . " = image_lang." . $db->quoteName('image_id') . " AND image_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id;
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_manufacturer') . " AS manufacturer ON (product." . $db->quoteName('manufacturer_id') . " = manufacturer." . $db->quoteName('manufacturer_id');
        $query .= ") " . JeproshopProductModelProduct::sqlStock('product', 0) . " WHERE " . $db->quoteName('product_1_id') . " = " . (int)$this->product_id ;
        $query .= ($published ? " AND product_shop." . $db->quoteName('published') . " = 1 AND product_shop." . $db->quoteName('visibility') . " != 'none'" : "") . " GROUP BY product_shop.product_id ";

        $db->setQuery($query);
        $result = $db->loadObjectList();
        if (!$result){ return false; }

        foreach ($result as &$row){
            $row->product_attribute_id = JeproshopProductModelProduct::getDefaultAttribute((int)$row->product_id);
        }
        return $this->getProductsProperties($lang_id, $result);
    }

    /**
     * Get product cover image
     *
     * @param $product_id
     * @param JeproshopContext $context
     * @return array Product cover image
     */
    public static function getCover($product_id, JeproshopContext $context = null) {
        if (!$context){ $context = JeproshopContext::getContext();}

        $cache_id = 'jeproshop_product::getOrderStates_'.(int)$product_id.'-'.(int)$context->shop->shop_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT image_shop." . $db->quoteName('image_id') . " FROM " . $db->quoteName('#__jeproshop_image') . " AS image ";
            $query .= JeproshopShopModelShop::addSqlAssociation('image') . " WHERE image." . $db->quoteName('product_id') . " = " . (int)$product_id;
            $query .= "	AND image_shop." . $db->quoteName('cover') . " = 1";
            $result = $db->loadObject();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Return the field value for the specified language if the field is multilang, else the field value.
     *
     * @param $field_name
     * @param null $lang_id
     * @return mixed
     */
    public function getFieldByLang($field_name, $lang_id = null){
        //$definition = ObjectModel::getDefinition($this);
        // Is field in definition
        $field = $this->{$field_name};
        // Is field multilang?
        if (isset($field['lang']) && $field['lang']) {
            if (is_array($this->{$field_name}))
                return $this->{$field_name}[$lang_id ? $lang_id : JeproshopContext::getContext()->language->lang_id];
        }
        return $this->{$field_name};
    }

    /*
     * @param JeproshopAddressModelAddress $address
     * @return the total taxes rate applied to the product
     */
    public function getTaxesRate(JeproshopAddressModelAddress $address = null){
        if(!$address || $address->country_id){
            $address = JeproshopAddressModelAddress::initialize();
        }

        $tax_manager = JeproshopTaxManagerFactory::getManager($address, $this->tax_rules_group_id);
        $tax_calculator = $tax_manager->getTaxCalculator();

        return $tax_calculator->getTotalRate();
    }

    public function isMultiShop(){
        return (JeproshopShopModelShop::isTableAssociated('product') || !empty($this->multiLangShop));
    }

    public function isNew(){
        $db = JFactory::getDBO();
        $query = "SELECT product.product_id FROM " . $db->quoteName('#__jeproshop_product') . " AS product ";
        $query .= JeproshopShopModelShop::addSqlAssociation('product') . " WHERE product.product_id = " . (int)$this->product_id;
        $query .= " AND DATEDIFF(product_shop." . $db->quoteName('date_add') . ", DATE_SUB(NOW(), INTERVAL " ;
        $query .= (JeproshopTools::isUnsignedInt(JeproshopSettingModelSetting::getValue('number_days_new_product')) ? JeproshopSettingModelSetting::getValue('number_days_new_product') : 20);
        $query .= " DAY) ) > 0";

        $db->setQuery($query);
        $result = $db->loadObjectList();
        return count($result) > 0;
    }

    public function checkAccess($customer_id){
        if (!JeproshopGroupModelGroup::isFeaturePublished()){ return true; }

        $cache_id = 'jeproshop_product_check_access_' . (int)$this->product_id . '_' . (int)$customer_id .(!$customer_id ? '_'.(int)JeproshopGroupModelGroup::getCurrent()->group_id : '');
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            if (!$customer_id){
                $query = "SELECT category_group."  . $db->quoteName('group_id') . " FROM " . $db->quoteName('#__jeproshop_product_category');
                $query .= " AS product_category INNER JOIN " . $db->quoteName('#__jeproshop_category_group') . " AS category_group ON (category_group.";
                $query .= $db->quoteName('category_id') . " = product_category." . $db->quoteName('category_id') . ") WHERE product_category.";
                $query .= $db->quoteName('product_id') . " = " . (int)$this->product_id . " AND category_group." . $db->quoteName('group_id') . " = ";
                $query .= (int)JeproshopGroupModelGroup::getCurrent()->group_id;
            }else{
                $query = "SELECT customer_group." . $db->quoteName('group_id') . " FROM " . $db->quoteName('#__jeproshop_product_category');
                $query .= " AS product_category INNER JOIN " . $db->quoteName('#__jeproshop_category_group') . " AS category_group ON (category_group.";
                $query .= $db->quoteName('category_id') . " = product_category." . $db->quoteName('category_id') . ") INNER JOIN " ;
                $query .= $db->quoteName('#__jeproshop_customer_group') . " AS customer_group ON (customer_group." . $db->quoteName('group_id') ;
                $query .= " = category_group."  . $db->quoteName('group_id') . ") WHERE product_category." . $db->quoteName('product_id') . " = ";
                $query .= (int)$this->product_id . " AND customer_group." . $db->quoteName('customer_id') . " = " .(int)$customer_id;
            }
            $db->setQuery($query);
            $result = $db->loadResult();
            JeproshopCache::store($cache_id, $result);
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Get product price
     * Same as static function getPriceStatic, no need to specify product id
     *
     * @param boolean $tax With taxes or not (optional)
     * @param integer $product_attribute_id Product attribute id (optional)
     * @param integer $decimals Number of decimals (optional)
     * @param integer $divisor Util when paying many time without fees (optional)
     * @param bool $only_reduction
     * @param bool $use_reduction
     * @param int $quantity
     * @return float Product price in euros
     */
    public function getPrice($tax = true, $product_attribute_id = null, $decimals = 6, $divisor = null, $only_reduction = false, $use_reduction = true, $quantity = 1){
        return JeproshopProductModelProduct::getStaticPrice((int)$this->product_id, $tax, $product_attribute_id, $decimals, $divisor, $only_reduction, $use_reduction, $quantity);
    }

    /**
     * Get product price
     *
     * @param integer $product_id Product id
     * @param boolean $use_tax With taxes or not (optional)
     * @param integer $product_attribute_id Product attribute id (optional).
     *    If set to false, do not apply the combination price impact. NULL does apply the default combination price impact.
     * @param integer $decimals Number of decimals (optional)
     * @param boolean $only_reduction Returns only the reduction amount
     * @param boolean $use_reduction Set if the returned amount will include reduction
     * @param integer $quantity Required for quantity discount application (default value: 1)
     * @param integer $customer_id Customer ID (for customer group reduction)
     * @param integer $cart_id Cart ID. Required when the cookie is not accessible (e.g., inside a payment module, a cron task...)
     * @param integer $address_id Customer address ID. Required for price (tax included) calculation regarding the guest localization
     * @param null $specific_price_output
     * @param boolean $with_ecotax insert ecotax in price output.
     * @param bool $use_group_reduction
     * @param JeproshopContext $context
     * @param bool $use_customer_price
     * @internal param int $divisor Useful when paying many time without fees (optional)
     * @internal param \variable_reference $specificPriceOutput .
     *    If a specific price applies regarding the previous parameters, this variable is filled with the corresponding SpecificPrice object
     * @return float Product price
     */
    public static function getStaticPrice($product_id, $use_tax = true, $product_attribute_id = null, $decimals = 6, $only_reduction = false, $use_reduction = true, $quantity = 1, $customer_id = null,
        $cart_id = null, $address_id = null, $specific_price_output = null, $with_ecotax = true, $use_group_reduction = true, JeproshopContext $context = null, $use_customer_price = true){
        if(!$context){
            $context = JeproshopContext::getContext();
        }

        $cur_cart = $context->cart;

        if (!JeproshopTools::isBool($use_tax) || !JeproshopTools::isUnsignedInt($product_id)){
            //die(Tools::displayError());
        }

        // Initializations
        $group_id = (int)JeproshopGroupModelGroup::getCurrent()->group_id;

        // If there is cart in context or if the specified id_cart is different from the context cart id
        if (!is_object($cur_cart) || (JeproshopTools::isUnsignedInt($cart_id) && $cart_id && $cur_cart->cart_id != $cart_id)){
            /*
             * When a user (e.g., guest, customer, Google...) is on Jeproshop, he has already its cart as the global (see /init.php)
             * When a non-user calls directly this method (e.g., payment module...) is on JeproShop, he does not have already it BUT knows the cart ID
             * When called from the back office, cart ID can be inexistant
             */
            if (!$cart_id && !isset($context->employee)){
                JError::raiseError(500, __FILE__ . ' ' . __LINE__);
            }
            $cur_cart = new JeproshopCartModelCart($cart_id);
            // Store cart in context to avoid multiple instantiations in BO
            if (!JeproshopTools::isLoadedObject($context->cart, 'cart_id')){
                $context->cart = $cur_cart;
            }
        }
        $db = JFactory::getDBO();
        $cart_quantity = 0;
        if ((int)$cart_id){
            $cache_id = 'jeproshop_product_model_get_price_static_' . (int)$product_id .'_' . (int)$cart_id;
            $cart_qty = JeproshopCache::retrieve($cache_id);
            if (!JeproshopCache::isStored($cache_id) || ( $cart_qty != (int)$quantity)){
                $query = "SELECT SUM(" . $db->quoteName('quantity') . ") FROM " . $db->quoteName('#__jeproshop_cart_product');
                $query .= " WHERE " . $db->quoteName('product_id') . " = " . (int)$product_id . " AND " . $db->quoteName('cart_id');
                $query .= " = " .(int)$cart_id;
                $db->setQuery($query);
                $cart_quantity = (int)$db->loadResult();
                JeproshopCache::store($cache_id, $cart_quantity);
            }
            $cart_quantity = JeproshopCache::retrieve($cache_id);
        }

        $currency_id = (int)JeproshopTools::isLoadedObject($context->currency, 'currency_id') ? $context->currency->currency_id : JeproshopSettingModelSetting::getValue('default_currency');

        // retrieve address information
        $country_id = (int)$context->country->country_id;
        $state_id = 0;
        $zipcode = 0;

        if (!$address_id && JeproshopTools::isLoadedObject($cur_cart, 'cart_id')){
            $address_id = $cur_cart->{JeproshopSettingModelSetting::getValue('tax_address_type')};
        }

        if ($address_id){
            $address_info = JeproshopAddressModelAddress::getCountryAndState($address_id);
            if ($address_info->country_id){
                $country_id = (int)$address_info->country_id;
                $state_id = (int)$address_info->state_id;
                $zipcode = $address_info->postcode;
            }
        }else if (isset($context->customer->geoloc_country_id)){
            $country_id = (int)$context->customer->geoloc_country_id;
            $state_id = (int)$context->customer->state_id;
            $zipcode = (int)$context->customer->postcode;
        }

        if (JeproshopTaxModelTax::taxExcludedOption()){
            $use_tax = false;
        }

        if ($use_tax != false && !empty($address_info->vat_number)
            && $address_info->country_id != JeproshopSettingModelSetting::getValue('vat_number_country')
            && JeproshopSettingModelSetting::getValue('vat_number_management')){
            $use_tax = false;
        }

        if (is_null($customer_id) && JeproshopTools::isLoadedObject($context->customer, 'customer_id')){
            $customer_id = $context->customer->customer_id;
        }

        return JeproshopProductModelProduct::priceCalculation($context->shop->shop_id, $product_id,
            $product_attribute_id, $country_id, $state_id, $zipcode, $currency_id, $group_id,
            $quantity, $use_tax, $decimals, 	$only_reduction, $use_reduction, $with_ecotax, $specific_price_output,
            $use_group_reduction, $customer_id, $use_customer_price, $cart_id, $cart_quantity
        );
    }

    /**
     * Price calculation / Get product price
     *
     * @param integer $shop_id Shop id
     * @param integer $product_id Product id
     * @param integer $product_attribute_id Product attribute id
     * @param integer $country_id Country id
     * @param integer $state_id State id
     * @param $zipcode
     * @param integer $currency_id Currency id
     * @param integer $group_id Group id
     * @param integer $quantity Quantity Required for Specific prices : quantity discount application
     * @param boolean $use_tax with (1) or without (0) tax
     * @param integer $decimals Number of decimals returned
     * @param boolean $only_reduction Returns only the reduction amount
     * @param boolean $use_reduction Set if the returned amount will include reduction
     * @param boolean $with_ecotax insert ecotax in price output.
     * @param $specific_price
     * @param $use_group_reduction
     * @param int $customer_id
     * @param bool $use_customer_price
     * @param int $cart_id
     * @param int $real_quantity
     * @internal param \variable_reference $specific_price_output If a specific price applies regarding the previous parameters, this variable is filled with the corresponding SpecificPrice object*    If a specific price applies regarding the previous parameters, this variable is filled with the corresponding SpecificPrice object
     * @return float Product price
     */
    public static function priceCalculation($shop_id, $product_id, $product_attribute_id, $country_id, $state_id, $zipcode, $currency_id, $group_id, $quantity, $use_tax,
        $decimals, $only_reduction, $use_reduction, $with_ecotax, &$specific_price, $use_group_reduction, $customer_id = 0, $use_customer_price = true, $cart_id = 0, $real_quantity = 0){
        static $address = null;
        static $context = null;

        if ($address === null){
            $address = new JeproshopAddressModelAddress();
        }

        if ($context == null){
            $context = JeproshopContext::getContext()->cloneContext();
        }

        if ($shop_id !== null && $context->shop->shop_id != (int)$shop_id){
            $context->shop = new JeproshopShopModelShop((int)$shop_id);
        }

        if (!$use_customer_price){
            $customer_id = 0;
        }

        if ($product_attribute_id === null){
            $product_attribute_id = JeproshopProductModelProduct::getDefaultAttribute($product_id);
        }

        $cache_id = $product_id . '_' .$shop_id . '_' . $currency_id . '_' . $country_id . '_' . $state_id . '_' . $zipcode . '_' . $group_id .
            '_' . $quantity . '_' . $product_attribute_id . '_' .($use_tax?'1':'0').'_' . $decimals.'_'. ($only_reduction ? '1' :'0').
            '_'.($use_reduction ?'1':'0') . '_' . $with_ecotax. '_' . $customer_id . '_'.(int)$use_group_reduction.'_'.(int)$cart_id.'-'.(int)$real_quantity;

        // reference parameter is filled before any returns
        $specific_price = JeproshopSpecificPriceModelSpecificPrice::getSpecificPrice((int)$product_id, $shop_id, $currency_id,
            $country_id, $group_id, $quantity, $product_attribute_id, $customer_id, $cart_id, $real_quantity
        );

        if (isset(self::$_prices[$cache_id])){
            return self::$_prices[$cache_id];
        }
        $db = JFactory::getDBO();
        // fetch price & attribute price
        $cache_id_2 = $product_id.'-'.$shop_id;
        if (!isset(self::$_pricesLevel2[$cache_id_2])){
            $select = "SELECT product_shop." . $db->quoteName('price') . ", product_shop." . $db->quoteName('ecotax');
            $from = $db->quoteName('#__jeproshop_product') . " AS product INNER JOIN " . $db->quoteName('#__jeproshop_product_shop');
            $from .= " AS product_shop ON (product_shop.product_id =product.product_id AND product_shop.shop_id = " .(int)$shop_id  . ")";

            if (JeproshopCombinationModelCombination::isFeaturePublished()){
                $select .= ", product_attribute_shop.product_attribute_id, product_attribute_shop." . $db->quoteName('price') . " AS attribute_price, product_attribute_shop.default_on";
                $leftJoin = " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute') .  " AS product_attribute ON product_attribute.";
                $leftJoin .= $db->quoteName('product_id') . " = product." . $db->quoteName('product_id') . " LEFT JOIN " . $db->quoteName('#__jeproshop_product_attribute_shop');
                $leftJoin .= " AS product_attribute_shop ON (product_attribute_shop.product_attribute_id = product_attribute.product_attribute_id AND product_attribute_shop.shop_id = " .(int)$shop_id .")";
            }else{
                $select .= ", 0 as product_attribute_id";
                $leftJoin = "";
            }
            $query = $select . " FROM " . $from . $leftJoin . " WHERE product." . $db->quoteName('product_id') . " = " . (int)$product_id;

            $db->setQuery($query);
            $results = $db->loadObjectList();

            foreach ($results as $row){
                $array_tmp = array(
                    'price' => $row->price, 'ecotax' => $row->ecotax,
                    'attribute_price' => (isset($row->attribute_price) ? $row->attribute_price : null)
                );

                self::$_pricesLevel2[$cache_id_2][(int)$row->product_attribute_id] = $array_tmp;

                if (isset($row->default_on) && $row->default_on == 1){
                    self::$_pricesLevel2[$cache_id_2][0] = $array_tmp;
                }
            }
        }

        if (!isset(self::$_pricesLevel2[$cache_id_2][(int)$product_attribute_id])){
            return;
        }

        $result = self::$_pricesLevel2[$cache_id_2][(int)$product_attribute_id];

        if (!$specific_price || $specific_price->price < 0){
            $price = (float)$result['price'];
        }else{
            $price = (float)$specific_price->price;
        }

        // convert only if the specific price is in the default currency (id_currency = 0)
        if (!$specific_price || !($specific_price->price >= 0 && $specific_price->currency_id)){
            $price = JeproshopTools::convertPrice($price, $currency_id);
        }

        // Attribute price
        if (is_array($result) && (!$specific_price || !$specific_price->product_attribute_id || $specific_price->price < 0)){
            $attribute_price = JeproshopTools::convertPrice($result['attribute_price'] !== null ? (float)$result['attribute_price'] : 0, $currency_id);
            // If you want the default combination, please use NULL value instead
            if ($product_attribute_id !== false){
                $price += $attribute_price;
            }
        }

        // Tax
        $address->country_id = $country_id;
        $address->state_id = $state_id;
        $address->postcode = $zipcode;

        $tax_manager = JeproshopTaxManagerFactory::getManager($address, JeproshopProductModelProduct::getTaxRulesGroupIdByProductId((int)$product_id, $context));
        $product_tax_calculator = $tax_manager->getTaxCalculator();

        // Add Tax
        if ($use_tax){
            $price = $product_tax_calculator->addTaxes($price);
        }

        // Reduction
        $specific_price_reduction = 0;
        if (($only_reduction || $use_reduction) && $specific_price){
            if ($specific_price->reduction_type == 'amount'){
                $reduction_amount = $specific_price->reduction;

                if (!$specific_price->currency_id){
                    $reduction_amount = JeproshopTools::convertPrice($reduction_amount, $currency_id);
                }
                $specific_price_reduction = !$use_tax ? $product_tax_calculator->removeTaxes($reduction_amount) : $reduction_amount;
            }else{
                $specific_price_reduction = $price * $specific_price->reduction;
            }
        }

        if ($use_reduction){
            $price -= $specific_price_reduction;
        }

        // Group reduction
        if($use_group_reduction){
            $reduction_from_category = JeproshopGroupReductionModelGroupReduction::getValueForProduct($product_id, $group_id);
            if ($reduction_from_category !== false){
                $group_reduction = $price * (float)$reduction_from_category;
            }else {
                // apply group reduction if there is no group reduction for this category
                $group_reduction = (($reduction = JeproshopGroupModelGroup::getReductionByGroupId($group_id)) != 0) ? ($price * $reduction / 100) : 0;
            }
        }else{
            $group_reduction = 0;
        }

        if ($only_reduction){
            return JeproshopTools::roundPrice($group_reduction + $specific_price_reduction, $decimals);
        }

        if ($use_reduction){  $price -= $group_reduction;   }

        // Eco Tax
        if (($result['ecotax'] || isset($result['attribute_ecotax'])) && $with_ecotax){
            $ecotax = $result['ecotax'];
            if (isset($result['attribute_ecotax']) && $result['attribute_ecotax'] > 0){
                $ecotax = $result['attribute_ecotax'];
            }
            if ($currency_id){
                $ecotax = JeproshopTools::convertPrice($ecotax, $currency_id);
            }

            if ($use_tax){
                // reinit the tax manager for ecotax handling
                $tax_manager = JeproshopTaxManagerFactory::getManager($address, (int)  JeproshopSettingModelSetting::getValue('ecotax_tax_rules_group_id'));
                $ecotax_tax_calculator = $tax_manager->getTaxCalculator();
                $price += $ecotax_tax_calculator->addTaxes($ecotax);
            }else{
                $price += $ecotax;
            }
        }
        $price = JeproshopTools::roundPrice($price, $decimals);
        if ($price < 0){
            $price = 0;
        }

        self::$_prices[$cache_id] = $price;
        return self::$_prices[$cache_id];
    }

    /**
     * Get the default attribute for a product
     *
     * @param $product_id
     * @param int $minimum_quantity
     * @return int Attributes list
     */
    public static function getDefaultAttribute($product_id, $minimum_quantity = 0){
        static $combinations = array();

        if (!JeproshopCombinationModelCombination::isFeaturePublished()){
            return 0;
        }

        if (!isset($combinations[$product_id])){
            $combinations[$product_id] = array();
        }

        if (isset($combinations[$product_id][$minimum_quantity])){
            return $combinations[$product_id][$minimum_quantity];
        }

        $db = JFactory::getDBO();
        $query = "SELECT product_attribute.product_attribute_id FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
        $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute'). ($minimum_quantity > 0 ? JeproshopProductModelProduct::sqlStock('product_attribute') : "");
        $query .= " WHERE product_attribute_shop.default_on = 1 " .($minimum_quantity > 0 ? " AND IFNULL(stock.quantity, 0) >= " .(int)$minimum_quantity : "");
        $query .= " AND product_attribute.product_id = " . (int)$product_id;

        $db->setQuery($query);
        $result = $db->loadResult();

        if (!$result){
            $query = "SELECT product_attribute.product_attribute_id FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
            $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute'). ($minimum_quantity > 0 ? JeproshopProductModelProduct::sqlStock('product_attribute') : "");
            $query .= " WHERE product_attribute.product_id = " .(int)$product_id .($minimum_quantity > 0 ? " AND IFNULL(stock.quantity, 0) >= ".(int)$minimum_quantity : "");

            $db->setQuery($query);
            $result = $db->loadResult();
        }

        if (!$result){
            $query = "SELECT product_attribute.product_attribute_id FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
            $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute') . " WHERE product_attribute_shop." . $db->quoteName('default_on') . " = 1 AND product_attribute.";
            $query .= "product_id = " .(int)$product_id;

            $db->setQuery($query);
            $result = $db->loadResult();
        }

        if (!$result){
            $query = "SELECT product_attribute.product_attribute_id FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute ";
            $query .= JeproshopShopModelShop::addSqlAssociation('product_attribute'). " WHERE product_attribute.product_id = " .(int)$product_id;

            $db->setQuery($query);
            $result = $db->loadResult();
        }

        $combinations[$product_id][$minimum_quantity] = $result;
        return $result;
    }

    /*
	 ** Customization management
	*/
    public static function getAllCustomizedDatas($cart_id, $lang_id = null, $only_in_cart = true){
        if (!JeproshopCustomization::isFeaturePublished()){ return false; }

        // No need to query if there isn't any real cart!
        if (!$cart_id){ return false; }
        if (!$lang_id){	$lang_id = JeproshopContext::getContext()->language->lang_id; }

        $db = JFactory::getDBO();

        $query = "SELECT customized_data." . $db->quoteName('customization_id') . ", customization." . $db->quoteName('address_delivery_id');
        $query .= ", customization." . $db->quoteName('product_id') . ", customization_field_lang." . $db->quoteName('customization_field_id');
        $query .= ", customization." . $db->quoteName('product_attribute_id') . ", customized_data." . $db->quoteName('type') . ", ";
        $query .= "customized_data." . $db->quoteName('index') . ", customized_data." . $db->quoteName('value') . ", ";
        $query .= "customization_field_lang." . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_customized_data') . " AS ";
        $query .= " customized_data NATURAL JOIN " . $db->quoteName('#__jeproshop_customization') . " AS customization LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_customization_field_lang') . " AS customization_field_lang ON (customization_field_lang.";
        $query .= "customization_field_id = customized_data." . $db->quoteName('index') . " AND lang_id = " .(int)$lang_id . ") WHERE ";
        $query .= "customization." . $db->quoteName('cart_id') . " = " . (int)$cart_id . ($only_in_cart ? " AND customization." .$db->quoteName('in_cart') . " = 1"  : "");
        $query .= " ORDER BY " . $db->quoteName('product_id'). ", " . $db->quoteName('product_attribute_id') . ", " . $db->quoteName('type') . ", " . $db->quoteName('index');

        $db->seQuery($query);
        $result = $db->loadObjectList();

        if (!$result){ return false; }

        $customized_datas = array();

        foreach ($result as $row){
            $customized_datas[(int)$row->product_id][(int)$row->product_attribute_id][(int)$row->address_delivery_id][(int)$row->customization_id]['datas'][(int)$row->type][] = $row;
        }

        $query = "SELECT " . $db->quoteName('product_id') . ", " . $db->quoteName('product_attribute_id') . ", " . $db->quoteName('customization_id');
        $query .= ", " . $db->quoteName('address_delivery_id') . ", " . $db->quoteName('quantity') . ", " . $db->quoteName('quantity_refunded') . ", ";
        $query .= $db->quoteName('quantity_returned') . " FROM " . $db->quoteName('#__jeproshop_customization') . " WHERE " . $db->quoteName('cart_id');
        $query .= " = " . (int)($cart_id) . ($only_in_cart ? " AND " . $db->quoteName('in_cart') . " = 1"  : "");

        $db->seQuery($query);
        $result = $db->loadObjectList();
        if (!$result ){ return false; }

        foreach ($result as $row){
            $customized_datas[(int)$row->product_id][(int)$row->product_attribute_id][(int)$row->address_delivery_id][(int)$row->customization_id]['quantity'] = (int)$row->quantity;
            $customized_datas[(int)$row->product_id][(int)$row->product_attribute_id][(int)$row->address_delivery_id][(int)$row->customization_id]['quantity_refunded'] = (int)$row->quantity_refunded;
            $customized_datas[(int)$row->product_id][(int)$row->product_attribute_id][(int)$row->address_delivery_id][(int)$row->customization_id]['quantity_returned'] = (int)$row->quantity_returned;
        }

        return $customized_datas;
    }

    public static function addCustomizationPrice(&$products, &$customized_datas){
        if (!$customized_datas){ return; }

        foreach ($products as &$product_update){
            if (!JeproshopCustomization::isFeaturePublished())
            {
                $product_update['customizationQuantityTotal'] = 0;
                $product_update['customizationQuantityRefunded'] = 0;
                $product_update['customizationQuantityReturned'] = 0;
            }
            else
            {
                $customization_quantity = 0;
                $customization_quantity_refunded = 0;
                $customization_quantity_returned = 0;

                /* Compatibility */
                $product_id = (int)(isset($product_update['id_product']) ? $product_update['id_product'] : $product_update['product_id']);
                $product_attribute_id = (int)(isset($product_update['id_product_attribute']) ? $product_update['id_product_attribute'] : $product_update['product_attribute_id']);
                $id_address_delivery = (int)$product_update['id_address_delivery'];
                $product_quantity = (int)(isset($product_update['cart_quantity']) ? $product_update['cart_quantity'] : $product_update['product_quantity']);
                $price = isset($product_update['price']) ? $product_update['price'] : $product_update['product_price'];
                if (isset($product_update['price_wt']) && $product_update['price_wt'])
                    $price_wt = $product_update['price_wt'];
                else
                    $price_wt = $price * (1 + ((isset($product_update['tax_rate']) ? $product_update['tax_rate'] : $product_update['rate']) * 0.01));

                if (!isset($customized_datas[$product_id][$product_attribute_id][$id_address_delivery]))
                    $id_address_delivery = 0;
                if (isset($customized_datas[$product_id][$product_attribute_id][$id_address_delivery]))
                {
                    foreach ($customized_datas[$product_id][$product_attribute_id][$id_address_delivery] as $customization)
                    {
                        $customization_quantity += (int)$customization['quantity'];
                        $customization_quantity_refunded += (int)$customization['quantity_refunded'];
                        $customization_quantity_returned += (int)$customization['quantity_returned'];
                    }
                }

                $product_update['customizationQuantityTotal'] = $customization_quantity;
                $product_update['customizationQuantityRefunded'] = $customization_quantity_refunded;
                $product_update['customizationQuantityReturned'] = $customization_quantity_returned;

                if ($customization_quantity)
                {
                    $product_update['total_wt'] = $price_wt * ($product_quantity - $customization_quantity);
                    $product_update['total_customization_wt'] = $price_wt * $customization_quantity;
                    $product_update['total'] = $price * ($product_quantity - $customization_quantity);
                    $product_update['total_customization'] = $price * $customization_quantity;
                }
            }
        }
    }

    public static function getColorsListCacheId($product_id){
        return 'product_colors_list|' . (int)$product_id . '|' . (int)JeproshopContext::getContext()->shop->shop_id . '|' . (int)JeproshopContext::getContext()->cookie->lang_id;
    }

    /**
     * Fill the variables used for stock management
     */
    public function loadStockData(){
        if (JeproshopTools::isLoadedObject($this, 'product_id')){
            // By default, the product quantity correspond to the available quantity to sell in the current shop
            $this->quantity = JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($this->product_id, 0);
            $this->out_of_stock = JeproshopStockAvailableModelStockAvailable::outOfStock($this->product_id);
            $this->depends_on_stock = JeproshopStockAvailableModelStockAvailable::dependsOnStock($this->product_id);
            if (JeproshopContext::getContext()->shop->getShopContext() == JeproshopShopModelShop::CONTEXT_GROUP && JeproshopContext::getContext()->shop->getContextShopGroup()->share_stock == 1){
                $this->advanced_stock_management = $this->useAdvancedStockManagement();
            }
        }
    }

    public function getNoPackPrice(){
        return JeproshopProductPack::noPackPrice($this->product_id);
    }

    public function getPriceWithoutReduction($no_tax = false, $product_attribute_id = false){
        return JeproshopProductModelProduct::getStaticPrice((int)$this->product_id, !$no_tax, $product_attribute_id, 6, null, false, false);
    }

    public static function getAttributeColorList(Array $products, $have_stock = true){
        if (!count($products)){ return array(); }

        $lang_id = JeproshopContext::getContext()->language->lang_id;

        $check_stock = !JeproshopSettingModelSetting::getValue('display_unavailable_attributes');
        $db = JFactory::getDBO();

        $query =  "SELECT product_attribute." . $db->quoteName('product_id') . ", attribute." . $db->quoteName('color') . ", product_attribute_combination.";
        $query .= $db->quoteName('product_attribute_id') . ", " .($check_stock ? "SUM(IF(stock." . $db->quoteName('quantity') . " > 0, 1, 0))" : "0") . " AS quantity,";
        $query .= "attribute." . $db->quoteName('attribute_id') . ", attribute_lang." . $db->quoteName('name') . ", IF(color = '', attribute.attribute_id, color) group_by ";
        $query .= " FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS product_attribute " . JeproshopShopModelShop::addSqlAssociation('product_attribute');
        $query .= ($check_stock ? JeproshopProductModelProduct::sqlStock('product_attribute') : '') . " JOIN " . $db->quoteName('#__jeproshop_product_attribute_combination');
        $query .= " AS product_attribute_combination ON(product_attribute_combination." . $db->quoteName('product_attribute_id') . " = product_attribute_shop." . $db->quoteName('product_attribute_id');
        $query .= ") JOIN " . $db->quoteName('#__jeproshop_attribute') . " AS attribute ON(attribute." . $db->quoteName('attribute_id') . " = product_attribute_combination.";
        $query .= $db->quoteName('attribute_id') . ") JOIN " . $db->quoteName('#__jeproshop_attribute_lang') . " AS attribute_lang ON(attribute." . $db->quoteName('attribute_id');
        $query .= " = attribute_lang." . $db->quoteName('attribute_id') . " AND attribute_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") JOIN ";
        $query .= $db->quoteName('#__jeproshop_attribute_group') . " AS attribute_group ON (attribute." . $db->quoteName('attribute_group_id') . " = attribute_group.";
        $query .= $db->quoteName('attribute_group_id') . ") WHERE product_attribute." . $db->quoteName('product_id'). " IN(" . implode(array_map('intval', $products), ',') ;
        $query .= ") AND attribute_group." . $db->quoteName('is_color_group') . " = 1 GROUP BY product_attribute." . $db->quoteName('product_id') . ", " . $db->quoteName('group_by');
        $query .= ($check_stock ? " HAVING quantity > 0" : "") . " ORDER BY attribute." . $db->quoteName('attribute_id') . " ASC" ;

        $db->setQuery($query);
        $res = $db->loadObjectList();
        if (!$res){ return false; }

        $colors = array();
        foreach ($res as $row)
        {
            if (Tools::isEmpty($row['color']) && !@filemtime(_PS_COL_IMG_DIR_.$row['id_attribute'].'.jpg'))
                continue;

            $colors[(int)$row['id_product']][] = array('product_attribute_id' => (int)$row['id_product_attribute'], 'color' => $row['color'], 'id_product' => $row['id_product'], 'name' => $row['name'], 'id_attribute' => $row['id_attribute']);
        }

        return $colors;
    }

    /**
     * @deprecated 1.5.0 Use Combination::getPrice()
     */
    public static function getProductAttributePrice($product_attribute_id){
        return JeproshopCombinationModelCombination::getPrice($product_attribute_id);
    }

    /**
     * @param type $product_alias
     * @param int|\type $product_attribute
     * @param bool|\type $inner_join
     * @param JeproshopShopModelShop $shop
     * @return string
     */
    public static function sqlStock($product_alias, $product_attribute = 0, $inner_join = FALSE, JeproshopShopModelShop $shop = NULL){
        $db = JFactory::getDBO();
        $shop_id = ($shop !== NULL ? (int)$shop->shop_id : NULL);
        $query = (( $inner_join) ? " INNER " : " LEFT ") . "JOIN " . $db->quoteName('#__jeproshop_stock_available');
        $query .= " AS stock ON(stock.product_id = " . $db->escape($product_alias) . ".product_id";

        if(!is_null($product_attribute)){
            if(!JeproshopCombinationModelCombination::isFeaturePublished()){
                $query .= " AND stock.product_attribute_id = 0";
            }elseif(is_numeric($product_attribute)){
                $query .= " AND stock.product_attribute_id = " . $product_attribute;
            }elseif (is_string($product_attribute)) {
                $query .= " AND stock.product_attribute_id = IFNULL(" . $db->quoteName($db->escape($product_attribute)) . ".product_attribute_id, 0)";
            }
        }
        $query .=  JeproshopStockAvailableModelStockAvailable::addShopRestriction($shop_id, 'stock') . ")";

        return $query;
    }

    public static function getProductsProperties($lang_id, $query_result){
        $results_array = array();

        if (is_array($query_result)){
            foreach ($query_result as $row){
                if ($row2 = JeproshopProductModelProduct::getProductProperties($lang_id, $row)){
                    $results_array[] = $row2;
                }
            }
        }

        return $results_array;
    }

    public static function getProductProperties($lang_id, $row, JeproshopContext $context = null){
        if (!$row->product_id){ return false; }

        if ($context == null){ $context = JeproshopContext::getContext(); }

        // Product::getDefaultAttribute is only called if id_product_attribute is missing from the SQL query at the origin of it:
        // consider adding it in order to avoid unnecessary queries
        $row->allow_out_of_stock_ordering = JeproshopProductModelProduct::isAvailableWhenOutOfStock($row->out_of_stock);
        if (JeproshopCombinationModelCombination::isFeaturePublished() && (!isset($row->product_attribute_id) || !$row->product_attribute_id)
            && ((isset($row->cache_default_attribute) && ($default_product_attribute_id = $row->cache_default_attribute) !== null)
                || ($default_product_attribute_id = JeproshopProductModelProduct::getDefaultAttribute($row->product_id, !$row->allow_out_of_stock_ordering))))
            $row->product_attribute_id = $default_product_attribute_id;
        if (!JeproshopCombinationModelCombination::isFeaturePublished() || !isset($row->product_attribute_id)){ $row->product_attribute_id = 0; }

        // Tax
        $useTax = JeproshopTaxModelTax::taxExcludedOption();

        $cache_key = $row->product_id . '_' . $row->product_attribute_id . '_' . $lang_id . '_'.(int)$useTax;
        if (isset($row->product_pack_id)){ $cache_key .= '_pack_' . $row->product_pack_id; }

        if (isset(self::$_productPropertiesCache[$cache_key]))
            return JeproshopTools::updateObjectData($row, self::$_productPropertiesCache[$cache_key]);

        // Datas
        $row->category = JeproshopCategoryModelCategory::getLinkRewrite((int)$row->default_category_id, (int)$lang_id);
        $row->link = $context->controller->getProductLink((int)$row->product_id, $row->link_rewrite, $row->category, $row->ean13);

        $row->attribute_price = 0;
        if (isset($row->product_attribute_id) && $row->product_attribute_id){
            $row->attribute_price = (float)JeproshopProductModelProduct::getProductAttributePrice($row->product_attribute_id);
        }
        $row->price_tax_exc = JeproshopProductModelProduct::getStaticPrice((int)$row->product_id, false,
            ((isset($row->product_attribute_id) && !empty($row->product_attribute_id)) ? (int)$row->product_attribute_id : null),
            (self::$_taxCalculationMethod == COM_JEPROSHOP_TAX_EXCLUDED ? 2 : 6)
        );

        if (self::$_taxCalculationMethod == COM_JEPROSHOP_TAX_EXCLUDED) {
            $row->price_tax_exc = JeproshopTools::roundPrice($row->price_tax_exc, 2);
            $row->price = JeproshopProductModelProduct::getStaticPrice( (int)$row->product_id, true,
                ((isset($row->product_attribute_id) && !empty($row->product_attribute_id)) ? (int)$row->product_attribute_id : null),
                6
            );
            $row->price_without_reduction = JeproshopProductModelProduct::getStaticPrice((int)$row->product_id, false,
                ((isset($row->product_attribute_id) && !empty($row->product_attribute_id)) ? (int)$row->product_attribute_id : null),
                2, null, false, false
            );
        } else {
            $row->price = JeproshopTools::roundPrice(
                JeproshopProductModelProduct::getStaticPrice((int)$row->product_id, true,
                    ((isset($row->product_attribute_id) && !empty($row->product_attribute_id)) ? (int)$row->product_attribute_id : null),
                    2
                ),
                2
            );

            $row->price_without_reduction = JeproshopProductModelProduct::getStaticPrice( (int)$row->product_id, true,
                ((isset($row->product_attribute_id) && !empty($row->product_attribute_id)) ? (int)$row->product_attribute_id : null),
                6, null, false, false
            );
        }
        $specific_prices = null;
        $row->reduction = JeproshopProductModelProduct::getStaticPrice((int)$row->product_id, (bool)$useTax, (int)$row->product_attribute_id,
            6, null, true, true, 1, true,  null, null, null, $specific_prices
        );

        $row->specific_prices = $specific_prices;

        $row->quantity = JeproshopProductModelProduct::getQuantity((int)$row->product_id, 0, isset($row->cache_is_pack) ? $row->cache_is_pack : null );

        $row->quantity_all_versions = $row->quantity;

        if ($row->product_attribute_id){
            $row->quantity = JeproshopProductModelProduct::getQuantity( (int)$row->product_id, $row->product_attribute_id, isset($row->cache_is_pack) ? $row->cache_is_pack : null );
        }
        $row->image_id = JeproshopProductModelProduct::defineProductImage($row, $lang_id);
        $row->features = JeproshopProductModelProduct::getFrontStaticFeatures((int)$lang_id, $row->product_id);

        $row->attachments = array();
        if (!isset($row->cache_has_attachments) || $row->cache_has_attachments)
            $row->attachments = JeproshopProductModelProduct::getStaticAttachments((int)$lang_id, $row->product_id);

        $row->virtual = ((!isset($row->is_virtual) || $row->is_virtual) ? 1 : 0);

        // Pack management
        $row->pack = (!isset($row->cache_is_pack) ? JeproshopProductPack::isPack($row->product_id) : (int)$row->cache_is_pack);
        $row->packItems = $row->pack ? JeproshopProductPack::getItemTable($row->product_id, $lang_id) : array();
        $row->no_pack_price = $row->pack ? JeproshopProductPack::noPackPrice($row->product_id) : 0;
        if ($row->pack && !JeproshopProductPack::isInStock($row->product_id)){ $row->quantity = 0; }

        $row->customization_required = false;
        if (isset($row->customizable) && $row->customizable && JeproshopCustomization::isFeaturePublished()){
            if (count(JeproshopProductModelProduct::getStaticRequiredCustomizableFields((int)$row->product_id))){
                $row->customization_required = true;
            }
        }

        $row = JeproshopProductModelProduct::getTaxesInformations($row, $context);
        self::$_productPropertiesCache[$cache_key] = $row;
        return self::$_productPropertiesCache[$cache_key];
    }

    public static function getTaxesInformations($row, JeproshopContext $context = null){
        static $address = null;

        if ($context === null){ $context = JeproshopContext::getContext(); }
        if ($address === null){
            $address = new JeproshopAddressModelAddress();
        }

        $address->country_id = (int)$context->country->country_id;
        $address->state_id = 0;
        $address->postcode = 0;

        $tax_manager = JeproshopTaxManagerFactory::getManager($address, JeproshopProductModelProduct::getTaxRulesGroupIdByProductId((int)$row->product_id, $context));
        $row->rate = $tax_manager->getTaxCalculator()->getTotalRate();
        $row->tax_name = $tax_manager->getTaxCalculator()->getTaxesName();

        return $row;
    }

    /**
     * convert price with currency
     */
    public static function convertPriceWithCurrency($price, $currency){
        return JeproshopTools::displayPrice($price, $currency);
    }

    public static function displayWtPrice($price){
        return JeproshopTools::displayPrice($price, JeproshopContext::getContext()->currency);
    }

    /**
     * Display WT price with currency
     *
     * @param $price
     * @param $currency
     * @internal param array $params
     * @internal param \DEPRECATED $object $smarty
     * @return Ambiguous <string, mixed, Ambiguous <number, string>>
     */
    public static function displayWtPriceWithCurrency($price, &$currency){
        return JeproshopTools::displayPrice($price, $currency, false);
    }

    public static function defineProductImage($row, $lang_id) {
        if (isset($row->image_id) && $row->image_id)
            return $row->product_id .'_'.$row->image_id;

        return JeproshopLanguageModelLanguage::getIsoById((int)$lang_id).'_default';
    }

    /**
     * Get available product quantities
     *
     * @param integer $product_id Product id
     * @param integer $product_attribute_id Product attribute id (optional)
     * @param null $cache_is_pack
     * @return integer Available quantities
     */
    public static function getQuantity($product_id, $product_attribute_id = null, $cache_is_pack = null){
        if ((int)$cache_is_pack || ($cache_is_pack === null && JeproshopProductPack::isPack((int)$product_id))) {
            if (!JeproshopProductPack::isInStock((int)$product_id))
                return 0;
        }

        // @since 1.5.0
        return (JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($product_id, $product_attribute_id));
    }

    /*
	* Select all features for a given language
	*
	* @param $id_lang Language id
	* @return array Array with feature's data
	*/
    public static function getFrontStaticFeatures($lang_id, $product_id){
        $db = JFactory::getDBO();
        if (!JeproshopFeatureModelFeature::isFeaturePublished()){ return array(); }
        if (!array_key_exists($product_id . '_' .$lang_id , self::$_frontFeaturesCache)){
            $query = "SELECT name, value, product_feature.feature_id FROM " . $db->quoteName('#__jeproshop_product_feature') . " AS product_feature	LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_feature_lang') . " AS feature_lang ON (feature_lang.feature_id = product_feature.feature_id AND feature_lang.";
            $query .= "lang_id = " .(int)$lang_id . ") LEFT JOIN " . $db->quoteName('#__jeproshop_feature_value_lang') . " AS feature_value_lang ON (feature_value_lang.";
            $query .= "feature_value_id = product_feature.feature_value_id AND feature_value_lang.lang_id = " .(int)$lang_id . ") LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_feature') . " AS feature ON (feature.feature_id = product_feature.feature_id AND feature_lang.lang_id = ";
            $query .= (int)$lang_id . ") " . JeproshopShopModelShop::addSqlAssociation('feature'). " WHERE product_feature.product_id = " .(int)$product_id ;
            $query .= "	ORDER BY feature.position ASC";

            $db->setQuery($query);
            self::$_frontFeaturesCache[$product_id.'_'.$lang_id] = $db->loadObjectList();
        }
        return self::$_frontFeaturesCache[$product_id .'_'.$lang_id];
    }

    public function getFrontFeatures($lang_id) {
        return JeproshopProductModelProduct::getFrontStaticFeatures($lang_id, $this->product_id);
    }

    public static function getStaticAttachments($lang_id, $product_id) {
        $db = JFactory::getDBO();
        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_product_attachment') . " AS product_attachment LEFT JOIN " . $db->quoteName('#__jeproshop_attachment');
        $query .= " AS attachment ON attachment.attachment_id = product_attachment.attachment_id LEFT JOIN " . $db->quoteName('#__jeproshop_attachment_lang') . " AS ";
        $query .= "attachment_lang ON (attachment.attachment_id = attachment_lang.attachment_id AND attachment_lang.lang_id = " .(int)$lang_id . ") WHERE ";
        $query .= "product_attachment.product_id = " . (int)$product_id;

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getAttachments($lang_id) {
        return JeproshopProductModelProduct::getStaticAttachments($lang_id, $this->product_id);
    }

    public function getCustomizationFields($lang_id = false){
        if (!JeproshopCustomization::isFeaturePublished()){ return false; }

        $db = JFactory::getDBO();

        $query = "SELECT customization_field." . $db->quoteName('customization_field_id') . ", customization_field." . $db->quoteName('type') . ", customization_field.";
        $query .= $db->quoteName('required') . ", customization_field_lang." . $db->quoteName('name') . ", customization_field_lang." . $db->quoteName('lang_id') . " FROM ";
        $query .= $db->quoteName('#__jeproshop_customization_field') . " AS customization_field NATURAL JOIN " . $db->quoteName('#__jeproshop_customization_field_lang') ;
        $query .= " AS customization_field_lang WHERE customization_field." . $db->quoteName('product_id') . " = " .(int)$this->product_id;
        $query .= ($lang_id ? " AND customization_field_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id : "")." ORDER BY customization_field." . $db->quoteName('customization_field_id');

        $db->setQuery($query);
        $result = $db->loadObjectList();
        if (!$result){ return false; }

        if ($lang_id)
            return $result;

        $customization_fields = array();
        foreach ($result as $row)
            $customization_fields[(int)$row->type][(int)$row->customization_field_id][(int)$row->lang_id] = $row;

        return $customization_fields;
    }

    public function getCustomizationFieldIds()
    {
        if (!Customization::isFeatureActive())
            return array();
        return Db::getInstance()->executeS('
			SELECT `id_customization_field`, `type`, `required`
			FROM `'._DB_PREFIX_.'customization_field`
			WHERE `id_product` = '.(int)$this->id);
    }


    /**
     * Get product images and legends
     *
     * @param integer $lang_id Language id for multilingual legends
     * @param JeproshopContext $context
     * @return array Product images and legends
     */
    public function getImages($lang_id, JeproshopContext $context = null) {
        if (!$context){
            $context = JeproshopContext::getContext();
        }

        $db = JFactory::getDBO();
        $query = "SELECT image_shop." . $db->quoteName('cover') . ", image." . $db->quoteName('image_id') . ", image_lang." . $db->quoteName('legend');
        $query .= ", image." . $db->quoteName('position') . " FROM "  . $db->quoteName('#__jeproshop_image') . " AS image ";
        $query .= JeproshopShopModelShop::addSqlAssociation('image') . " LEFT JOIN " . $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image.";
        $query .= $db->quoteName('image_id') . " = image_lang." . $db->quoteName('image_id') . " AND image_lang." . $db->quoteName('lang_id') . " = " ;
        $query .= (int)$lang_id . ") WHERE image." . $db->quoteName('product_id') . " = " .(int)$this->product_id . " ORDER BY " . $db->quoteName('position');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public function getTaxRulesGroupId(){
        return $this->tax_rules_group_id;
    }

    public static function getTaxRulesGroupIdByProductId($product_id, JeproshopContext $context = null) {
        if (!$context){
            $context = JeproshopContext::getContext();
        }
        $key = 'product_tax_rules_group_id_'.(int)$product_id .'_'.(int)$context->shop->shop_id;
        if (!JeproshopCache::isStored($key)){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('tax_rules_group_id') . " FROM " . $db->quoteName('#__jeproshop_product_shop') . " WHERE ";
            $query .= $db->quoteName('product_id') . " = " .(int)$product_id . " AND shop_id = " .(int)$context->shop->shop_id;

            $db->setQuery($query);
            $tax_rules_group_id = $db->loadResult();
            JeproshopCache::store($key, $tax_rules_group_id);
        }
        return JeproshopCache::retrieve($key);
    }

    public function hasAllRequiredCustomizableFields(JeproshopContext $context = null){
        if (!JeproshopCustomization::isFeaturePublished())
            return true;
        if (!$context){ $context = JeproshopContext::getContext(); }

        $fields = $context->cart->getProductCustomization($this->product_id, null, true);
        if (($required_fields = $this->getRequiredCustomizableFields()) === false){
            return false;
        }
        $fields_present = array();
        foreach ($fields as $field){ $fields_present[] = array('customization_field_id' => $field['index'], 'type' => $field['type']); }

        if (is_array($required_fields) && count($required_fields)){
            foreach ($required_fields as $required_field){
                if (!in_array($required_field, $fields_present)){ return false; }
            }
        }
        return true;
    }

    public function getRequiredCustomizableFields(){
        if (!JeproshopCustomization::isFeaturePublished()){ return array(); }
        return JeproshopProductModelProduct::getStaticRequiredCustomizableFields($this->product_id);
    }

    public static function getStaticRequiredCustomizableFields($product_id){
        if (!$product_id || !JeproshopCustomization::isFeaturePublished())
            return array();

        $db = JFactory::getDBO();

        $query = "SELECT " . $db->quoteName('customization_field_id') . ", " . $db->quoteName('type') . " FROM " . $db->quoteName('#__jeproshop_customization_field');
        $query .= " WHERE " . $db->quoteName('product_id') . " = " . (int)$product_id . " AND " . $db->quoteName('required') . " = 1";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Check if product has attributes combinations
     *
     * @return integer Attributes combinations number
     */
    public function hasAttributes(){
        if (!JeproshopCombinationModelCombination::isFeaturePublished()){ return 0; }

        $db = JFactory::getDBO();
		$query = "SELECT COUNT(*) FROM " . $db->quoteName('#__jeproshop_product_attribute') . " AS  product_attribute " ;
		$query .= JeproshopShopModelShop::addSqlAssociation('product_attribute') . " WHERE product_attribute." . $db->quoteName('product_id') . " = "  . (int)$this->product_id;

        $db->setQuery($query);
        return $db->loadResult();
    }

    /**
     * Check product availability
     *
     * @param integer $qty Quantity desired
     * @return boolean True if product is available with this quantity
     */
    public function checkQuantity($qty){
        if (JeproshopProductPack::isPack((int)$this->product_id) && !JeproshopProductPack::isInStock((int)$this->product_id))
            return false;

        if ($this->isAvailableWhenOutOfStock(JeproshopStockAvailableModelStockAvailable::outOfStock($this->product_id)))
            return true;

        if (isset($this->product_attribute_id)){
            $product_attribute_id = $this->product_attribute_id;
        }else{
            $product_attribute_id = 0;
        }
        return ($qty <= JeproshopStockAvailableModelStockAvailable::getQuantityAvailableByProduct($this->product_id, $product_attribute_id));
    }
}



class JeproshopProductPack extends JeproshopProductModelProduct
{
    protected static $cachePackItems = array();
    protected static $cacheIsPack = array();
    protected static $cacheIsPacked = array();
    /**
     * Is product a pack
     * @param int $product_id
     * @return boolean
     */
    public static function isPack($product_id){
        if(!JeproshopProductPack::isFeaturePublished()){ return FALSE; }
        if(!$product_id){ return FALSE; }

        if(!array_key_exists($product_id, self::$cacheIsPack)){
            $db = JFactory::getDBO();
            $query = "SELECT COUNT(*) FROM " . $db->quoteName('#__jeproshop_pack') . " WHERE product_pack_id = " . (int)$product_id;
            $db->setQuery($query);
            $result = $db->loadResult();
            self::$cacheIsPack[$product_id] = ($result > 0);
        }
        return self::$cacheIsPack[$product_id];
    }

    /***
     * Check is the product is in a pack
     *
     * @param $product_id
     * @return boolean
     */
    public static function isPacked($product_id){
        if(!JeproshopProductPack::isFeaturePublished()){ return FALSE; }

        if(!array_key_exists($product_id, self::$cacheIsPacked)){
            $db = JFactory::getDBO();

            $query = "SELECT COUNT(*) FROM " . $db->quoteName('#__jeproshop_pack') . " WHERE producct_item_id = " . (int)$product_id;
            $db->setQuery($query);
            $result = $db->loadResult();
            self::$cacheIsPacked[$product_id] = ($result > 0);
        }
        return self::$cacheIsPacked[$product_id];
    }

    public static function getPacksTable($product_id, $lang_id, $full = false, $limit = null){
        if (!JeproshopProductPack::isFeaturePublished()){ return array(); }

        $db = JFactory::getDBO();

        $query = "SELECT GROUP_CONCAT(pack." . $db->quoteName('product_pack_id') .") FROM " . $db->quoteName('#__jeproshop_pack') . " AS pack WHERE pack." . $db->quoteName('product_item_id') ." = ".(int)$product_id;
        $db->setQuery($query);
        $packs = $db->loadResult();

        if (!(int)$packs){ return array(); }

        $query = "SELECT product.*, product_shop.*, product_lang.*, MAX(image_shop." . $db->quoteName('image_id') . ") image_id, image_lang." . $db->quoteName('legend');
        $query .= " FROM " . $db->quoteName('#__jeproshop_product') . " AS product NATURAL LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ";
        $query .= JeproshopShopModelShop::addSqlAssociation('product'). " LEFT JOIN " . $db->quoteName('#__jeproshop_image') . " AS image ON (image." . $db->quoteName('product_id');
        $query .= " = product." . $db->quoteName('product_id') . ")" . JeproshopShopModelShop::addSqlAssociation('image', false, 'image_shop.cover=1') . " LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image." . $db->quoteName('image_id') . " = image_lang." ;
        $query .= $db->quoteName('image_id') . " AND image_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") WHERE product_lang." . $db->quoteName('lang_id');
        $query .= " = " . (int)$lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang') . " AND product." . $db->quoteName('product_id') . " IN (" . $packs;
        $query .= ") GROUP BY product_shop.product_id";

        if ($limit){
            $query .= " LIMIT " . (int)$limit;
        }
        $db->setQuery($query);
        $result = $db->loadObjectList();
        if (!$full){
            return $result;
        }

        $array_result = array();
        foreach ($result as $row){
            if (!JeproshopProductPack::isPacked($row->product_id)){
                $array_result[] = JeproshopProductModelProduct::getProductProperties($lang_id, $row);
            }
        }
        return $array_result;
    }

    public static function isInStock($product_id){
        if (!JeproshopProductPack::isFeaturePublished()){ return true; }

        $items = JeproshopProductPack::getItems((int)$product_id, Configuration::get('default_lang'));

        foreach ($items as $item){
            // Updated for 1.5.0
            if (Product::getQuantity($item->id) < $item->pack_quantity && !$item->isAvailableWhenOutOfStock((int)$item->out_of_stock))
                return false;
        }
        return true;
    }

    public static function getItemTable($product_id, $lang_id, $full = false){
        if (!JeproshopProductPack::isFeaturePublished()){
            return array();
        }

        $db = JFactory::getDBO();

        $query = "SELECT product.*, product_shop.*, product_lang.*, MAX(image_shop." . $db->quoteName('image_id') . ") image_id, image_lang." . $db->quoteName('legend');
        $query .= ", category_lang." . $db->quoteName('name') . " AS default_category, pack.quantity AS pack_quantity, product_shop." . $db->quoteName('default_category_id');
        $query .= ", pack.product_pack_id FROM " . $db->quoteName('#__jeproshop_pack') . " AS pack LEFT JOIN " . $db->quoteName('#__jeproshop_product') . " AS product ON(";
        $query .= "product.product_id = pack.product_item_id) LEFT JOIN " . $db->quoteName('#__jeproshop_product_lang') . " AS product_lang ON(product.product_id = product_lang.";
        $query .= "product_id AND product_lang.lang_id = " . (int)$lang_id . JeproshopShopModelShop::addSqlRestrictionOnLang('product_lang') . ") LEFT JOIN ";
        $query .= $db->quoteName('#__jeproshop_image') . " AS image ON( image.product_id = product.product_id )" . JeproshopShopModelShop::addSqlAssociation('product', FALSE, 'image_shop.cover = 1');
        $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image.image_id = image_lang.image_id AND image_lang.lang_id = " . (int)$lang_id . " ) ";
        $query .= JeproshopShopModelShop::addSqlAssociation('product') . " LEFT JOIN " . $db->quoteName('#__jeproshop_category_lang') . " AS category_lang ON product_shop.";
        $query .= $db->quoteName('default_category_id') . " = category_lang." . $db->quoteName('category_id') . " AND category_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id;
        $query .= JeproshopShopModelShop::addSqlRestrictionOnLang('category_lang') . " WHERE product_shop." . $db->quoteName('shop_id') . " = " . (int)JeproshopContext::getContext()->shop->shop_id;
        $query .= " AND pack." . $db->quoteName('product_pack_id') . " = " . (int)$product_id . " GROUP BY product_shop.product_id";

        $db->setQuery($query);
        $result = $db->loadObjectList();

        foreach ($result as &$line){
            $line = JeproshopProductModelProduct::getTaxesInformations($line);
        }

        if (!$full){
            return $result;
        }
        $array_result = array();
        foreach ($result as $prow){
            if (!JeproshopProductPack::isPack($prow->product_id)){
                $array_result[] = JeproshopProductModelProduct::getProductProperties($lang_id, $prow);
            }
        }
        return $array_result;
    }

    public static function noPackPrice($product_id){
        $sum = 0;
        $price_display_method = !self::$_taxCalculationMethod;
        $items = JeproshopProductPack::getItems($product_id, JeproshopSettingModelSetting::getValue('default_lang'));
        foreach ($items as $item){
            $sum += $item->getPrice($price_display_method) * $item->pack_quantity;
        }
        return $sum;
    }

    public static function getItems($product_id, $lang_id){
        if (!JeproshopProductPack::isFeaturePublished()){ return array(); }

        if (array_key_exists($product_id, self::$cachePackItems)){
            return self::$cachePackItems[$product_id];
        }
        $db = JFactory::getDBO();
        $query = "SELECT product_item_id, quantity FROM " . $db->quoteName('#__jeproshop_pack') . " WHERE product_pack_id = " . (int)$product_id;

        $db->setQuery($query);
        $result = $db->loadObjectList();;
        $array_result = array();
        foreach ($result as $row) {
            $product = new JeproshopProductModelProduct($row->product_item_id, false, $lang_id);
            $product->loadStockData();
            $product->pack_quantity = $row->quantity;
            $array_result[] = $product;
        }
        self::$cachePackItems[$product_id] = $array_result;
        return self::$cachePackItems[$product_id];
    }

    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('pack_feature_active');
    }
}


class JeproshopProductDownloadModelProductDownload extends JModelLegacy
{
    /** @var integer Product id which download belongs */
    public $product_download_id;

    /** @var string DisplayFilename the name which appear */
    public $display_filename;

    /** @var string PhysicallyFilename the name of the file on hard disk */
    public $filename;

    /** @var string DateDeposit when the file is upload */
    public $date_add;

    /** @var string DateExpiration deadline of the file */
    public $date_expiration;

    /** @var string NbDaysAccessible how many days the customer can access to file */
    public $nb_days_accessible;

    /** @var string NbDownloadable how many time the customer can download the file */
    public $nb_downloadable;

    /** @var boolean Active if file is accessible or not */
    public $published = 1;

    /** @var boolean is_shareable indicates whether the product can be shared */
    public $is_shareable = 0;

    protected static $_productIds = array();

    /**
     * Return the id_product_download from an id_product
     *
     * @param int $product_id Product the id
     * @return integer Product the id for this virtual product
     */
    public static function getIdFromProductId($product_id){
        if (!JeproshopProductDownloadModelProductDownload::isFeaturePublished()){
            return false;
        }
        if (array_key_exists((int)$product_id, self::$_productIds)){
            return self::$_productIds[$product_id];
        }
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName('product_download_id') . " FROM " . $db->quoteName('#__jeproshop_product_download');
        $query .= " WHERE " . $db->quoteName('product_id') . " = " .(int)$product_id . " AND " . $db->quoteName('published') . " = 1 ";
        $query .= "	ORDER BY " . $db->quoteName('product_download_id') . " DESC";

        $db->setQuery($query);
        self::$_productIds[$product_id] = (int)$db->loadResult();

        return self::$_productIds[$product_id];
    }


    /**
     * This method is allow to know if a feature is used or active
     * @since 1.5.0.1
     * @return bool
     */
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('virtual_product_feature_active');
    }
}