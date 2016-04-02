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

class JeproshopManufacturerModelManufacturer extends JModelLegacy
{
    protected static $_cache_name = array();


    public function __construct($manufacturer_id = null, $lang_id = null){
        parent::__construct();
        $db = JFactory::getDBO();

        if($lang_id !== NULL){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($lang_id) ? (int)$lang_id : JeproshopSettingModelSetting::getValue('default_lang'));
        }

        if($manufacturer_id){
            $cache_id = 'jeproshop_manufacturer_model_' . $manufacturer_id . '_' . $lang_id ;
            if(!JeproshopCache::isStored($cache_id)){
                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_manufacturer') . " AS j_manufacturer ";
                $where = "";
                /** get language information **/
                if($lang_id){
                    $query .= "LEFT JOIN " . $db->quoteName('#__jeproshop_manufacturer_lang') . " AS manufacturer_lang ";
                    $query .= "ON (j_manufacturer.manufacturer_id = manufacturer_lang.manufacturer_id AND manufacturer_lang.lang_id = " . (int)$lang_id . ") ";
                    /* if($this->shop_id && !(empty($this->multiLangShop))){
                         $where = " AND manufacturer_lang.shop_id = " . $this->shop_id;
                     }*/
                }

                /** Get shop informations ** /
                if(JeproshopShopModelShop::isTableAssociated('manufacturer')){
                $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_manufacturer_shop') . " AS manufacturer_shop ON (";
                $query .= "j_manufacturer.manufacturer_id = manufacturer_shop.manufacturer_id AND manufacturer_shop.shop_id = " . (int)  $this->shop_id . ")";
                }*/
                $query .= " WHERE j_manufacturer.manufacturer_id = " . (int)$manufacturer_id . $where;

                $db->setQuery($query);
                $manufacturer_data = $db->loadObject();

                if($manufacturer_data){
                    if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_manufacturer_lang');
                        $query .= " WHERE manufacturer_id = " . (int)$manufacturer_id;

                        $db->setQuery($query);
                        $manufacturer_lang_data = $db->loadObjectList();
                        if($manufacturer_lang_data){
                            foreach ($manufacturer_lang_data as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'manufacturer_id'){
                                        if(!isset($manufacturer_data->{$key}) || !is_array($manufacturer_data->{$key})){
                                            $manufacturer_data->{$key} = array();
                                        }
                                        $manufacturer_data->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                    }
                    JeproshopCache::store($cache_id, $manufacturer_data);
                }
            }else{
                $manufacturer_data = JeproshopCache::retrieve($cache_id);
            }

            if($manufacturer_data){
                $manufacturer_data->manufacturer_id = $manufacturer_id;
                foreach($manufacturer_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }

        $this->link_rewrite = $this->getLink();
        $this->image_dir = COM_JEPROSHOP_MANUFACTURER_IMAGE_DIRECTORY;
    }

    /**
     * Return name from id
     *
     * @param int $manufacturer_id
     * @return string name
     */
    public static function getNameById($manufacturer_id){
        if(!isset(self::$_cache_name[$manufacturer_id])){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_manufacturer') . " WHERE " ;
            $query .= $db->quoteName('manufacturer_id') . " = " . (int)$manufacturer_id . " AND " . $db->quoteName('published') . " = 1";

            $db->setQuery($query);
            self::$_cache_name[$manufacturer_id] = $db->loadResult();
        }
        return self::$_cache_name[$manufacturer_id];
    }

    public function getLink(){
        return JeproshopTools::str2url($this->name);
    }

}