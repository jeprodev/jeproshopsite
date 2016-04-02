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

class JeproshopGroupModelGroup extends JModelLegacy
{
    public $group_id;

    public $shop_id;

    public $name;

    public $reduction;

    public $show_prices = 1;

    public $price_display_method;

    public $date_add;
    public $date_upd;

    protected static $cache_reduction = array();
    protected static $group_price_display_method;

    public function __construct($group_id = NULL, $lang_id = NULL, $shop_id = NULL){
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

        if($group_id){
            $cache_id = 'jeproshop_group_model_' . $group_id . '_' . $lang_id . '_' . $shop_id;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_group') . " AS j_group ";
                $where = "";
                /** get language information **/
                if($lang_id){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_group_lang') . " AS group_lang ";
                    $query .= "ON (j_group.group_id = group_lang.group_id AND group_lang.lang_id = " . (int)$lang_id . ") ";
                    if($this->shop_id && !(empty($this->multiLangShop))){
                        $where = " AND group_lang.shop_id = " . $this->shop_id;
                    }
                }

                /** Get shop informations **/
                if(JeproshopShopModelShop::isTableAssociated('group')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_group_shop') . " AS group_shop ON (";
                    $query .= "j_group.group_id = group_shop.group_id AND group_shop.shop_id = " . (int)  $this->shop_id . ")";
                }
                $query .= " WHERE j_group.group_id = " . (int)$group_id . $where;

                $db->setQuery($query);
                $group_data = $db->loadObject();

                if($group_data){
                    if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_group_lang');
                        $query .= " WHERE group_id = " . (int)$group_id;

                        $db->setQuery($query);
                        $group_lang_data = $db->loadObjectList();
                        if($group_lang_data){
                            foreach ($group_lang_data as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'group_id'){
                                        if(!isset($group_data->{$key}) || !is_array($group_data->{$key})){
                                            $group_data->{$key} = array();
                                        }
                                        $group_data->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cache_id, $group_data);
                }
            }else{
                $group_data = JeproshopCache::retrieve($cache_id);
            }

            if($group_data){
                $group_data->group_id = $group_id;
                foreach($group_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        if($this->group_id && !isset(JeproshopGroupModelGroup::$group_price_display_method[$this->group_id])){
            self::$group_price_display_method[$this->group_id] = $this->price_display_method;
        }
    }

    public static function getPriceDisplayMethod($group_id) {
        if(!isset($group_id) || !$group_id){ $group_id = (int)JeproshopSettingModelSetting::getValue('unidentified_group'); }
        if (!isset(JeproshopGroupModelGroup::$group_price_display_method[$group_id])){
            $db = JFactory::getDBO();

            $query = " SELECT " . $db->quoteName('price_display_method') . " FROM " . $db->quoteName('#__jeproshop_group');
            $query .= " WHERE " . $db->quoteName('group_id') . " = " .(int)$group_id;

            $db->setQuery($query);
            self::$group_price_display_method[$group_id] = $db->loadResult();
        }
        return self::$group_price_display_method[$group_id];
    }

    /**
     * Return current group object
     * Use context
     * @static
     * @return Group Group object
     */
    public static function getCurrent(){
        static $groups = array();

        $customer = JeproshopContext::getContext()->customer;
        if (JeproshopTools::isLoadedObject($customer, 'customer_id')){
            $group_id = (int)$customer->default_group_id;
        } else
            $group_id = (int)JeproshopSettingModelSetting::getValue('unidentified_group');

        if (!isset($groups[$group_id])){
            $groups[$group_id] = new JeproshopGroupModelGroup($group_id);
        }
        if (!$groups[$group_id]->isAssociatedToShop(JeproshopContext::getContext()->shop->shop_id)){
            $group_id = (int)JeproshopSettingModelSetting::getValue('customer_group');
            if (!isset($groups[$group_id]))
                $groups[$group_id] = new JeproshopGroupModelGroup($group_id);
        }

        return $groups[$group_id];
    }

    public function isMultiShop(){
        return (JeproshopShopModelShop::isTableAssociated('group') || !empty($this->multiLangShop));
    }

    public function isAssociatedToShop($shop_id = null){
        if ($shop_id === null){
            $shop_id = JeproshopContext::getContext()->shop->shop_id;
        }
        $cache_id = 'jeproshop_model_shop_group_'. (int)$this->group_id . '_' .(int)$shop_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT shop_id FROM " . $db->quoteName('#__jeproshop_group_shop') . " WHERE ";
            $query .= $db->quoteName('group_id') . " = " . $this->group_id . " AND shop_id = " .(int)$shop_id;

            $db->setQuery($query);
            JeproshopCache::store($cache_id, (bool)$db->loadResult());
        }
        return JeproshopCache::retrieve($cache_id);
    }


    /**
     * This method is allow to know if a feature is used or active
     * @since 1.5.0.1
     * @return bool
     */
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('group_feature_active');
    }
}


Class JeproshopGroupReductionModelGroupReduction extends JModelLegacy
{
    public	$group_id;
    public	$category_id;
    public	$reduction;

    protected static $reduction_cache = array();

    public static function getValueForProduct($product_id, $group_id){
        if (!JeproshopGroupModelGroup::isFeaturePublished()){ return 0; }

        if (!isset(self::$reduction_cache[$product_id . '_' . $group_id])){
            $db = JFactory::getDBO();
            $query = "SELECT " . $db->quoteName('reduction') . " FROM " . $db->quoteName('#__jeproshop_product_group_reduction_cache') . " WHERE ";
            $query .= $db->quoteName('product_id') . " = " .(int)$product_id . " AND " . $db->quoteName('group_id') . " = ".(int)$group_id;
            $db->setQuery($query);

            self::$reduction_cache[$product_id . '_' .$group_id] = $db->loadResult();

        }
        // Should return string (decimal in database) and not a float
        return self::$reduction_cache[$product_id.'_'.$group_id];
    }
}