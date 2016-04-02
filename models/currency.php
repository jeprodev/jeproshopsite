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

class JeproshopCurrencyModelCurrency extends JModelLegacy
{
    public $currency_id;

    /** @var string name */
    public $name;

    /** @var string Iso code */
    public $iso_code;

    /** @var  string Iso code numeric */
    public $iso_code_num;

    /** @var string symbol for short display */
    public $sign;

    /** @var int bool used for displaying blank between sign and price */
    public $blank;

    /**
     * contains the sign to display before price, according to its format
     * @var string
     */
    public $prefix;

    /**
     * contains the sign to display after price, according to its format
     * @var string
     */
    public $suffix;

    /** @var double conversion rate  */
    public $conversion_rate;

    /** @var int ID used for displaying prices */
    public $format;

    /** @var boolean True if currency has been deleted(staying in database as deleted) */
    public $deleted;

    /** @var int bool Display decimals on prices */
    public $decimals;

    /** @var int bool published  */
    public $published;

    public $shop_id;

    static protected $currencies = array();


    public function __construct($currency_id = null, $shop_id = null){

        $db = JFactory::getDBO();


        if($shop_id && $this->isMultiShop()){
            $this->shop_id = (int)$shop_id;
            $this->get_shop_from_context = false;
        }

        if($this->isMultiShop() && !$this->shop_id){
            $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
        }

        if($currency_id){
            //load object from the  database if the currency id is provided
            $cache_id = 'jeproshop_currency_model_' . (int)$currency_id . '_' . (int)$shop_id;
            if(!JeproshopCache::isStored($cache_id)){
				$db = JFactory::getDBO();
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_currency') . " AS currency ";

                if(JeproshopShopModelShop::isTableAssociated('currency')){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_currency_shop') ." shop ON( currency.currency_id";
                    $query .= " = shop.currency_id AND shop.shop_id = " . (int)$this->shop_id . ")";
                }
                $query .= " WHERE currency.currency_id = " . (int)$currency_id ;

                $db->setQuery($query);
                $currency_data = $db->loadObject();
                if($currency_data){
                    JeproshopCache::store($cache_id, $currency_data);
                }
            }else{
                $currency_data = JeproshopCache::retrieve($cache_id);
            }

            if($currency_data){
                foreach($currency_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
                $this->currency_id = (int)$currency_id;
            }
        }

        /* prefix and suffix are convenient short cut for displaying price sign before or after the price number */
        $this->prefix = $this->format % 2 != 0 ? $this->sign . " " : " ";
        $this->suffix = $this->format % 2 == 0 ? " " . $this->sign : " ";
    }

    public function isMultiShop(){
        return JeproshopShopModelShop::isTableAssociated('currency') || !empty($this->multiLangShop);
    }

    public static function getCurrencyInstance($currency_id) { 
        if (!isset(self::$currencies[$currency_id])){
            self::$currencies[(int)($currency_id)] = new JeproshopCurrencyModelCurrency($currency_id);
        } 
        return self::$currencies[(int)($currency_id)];
    }

    public function isAssociatedToShop($shop_id = null){
        if ($shop_id === null){
            $shop_id = JeproshopContext::getContext()->shop->shop_id;
        }
        $cache_id = 'jeproshop_model_shop_currency_'. (int)$this->currency_id.'-'.(int)$shop_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT shop_id FROM " . $db->quoteName('#__jeproshop_currency_shop') . " WHERE ";
            $query .= $db->quoteName('currency_id') . " = " . $this->currency_id . " AND shop_id = " .(int)$shop_id;

            $db->setQuery($query);
            JeproshopCache::store($cache_id, (bool)$db->loadResult());
        }
        return JeproshopCache::retrieve($cache_id);
    }

    public static function getCurrencies($published = true){
        $db = JFactory::getDBO();
        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_currency') . " AS currency " . JeproshopShopModelShop::addSqlAssociation('currency');
        $query .= " WHERE currency." . $db->quoteName('deleted') . " = 0 " . ($published ? " AND currency.". $db->quoteName('published') . " = 1" : "") ;
        $query .= " GROUP BY currency.currency_id ORDER BY " . $db->quoteName('name') . " ASC ";
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    public static function getCurrency($currency_id){
        $db = JFactory::getDBO();
        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_currency')  . " WHERE " . $db->quoteName('deleted');
        $query .= " = 0 AND " . $db->quoteName('currency_id') . " = " . (int)$currency_id;
        $db->setQuery($query);
        return $db->loadObject();
    }
}