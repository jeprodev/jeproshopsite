<?php
/**
 * @version         1.0.3
 * @package         components
 * @sub package      com_jeproshop
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

class JeproshopImageModelImage extends JModelLegacy
{
    public $image_id;

    public $product_id;

    public $position;

    public $cover;

    public $legend;

    public $image_format ='jpg';

    protected static $_cacheGetSize = array();

    public function __construct($image_id = null, $lang_id = null){
        //parent::__construct($id, $id_lang);
        if($lang_id !== null){
            $this->lang_id = (JeproshopSettingModelSetting::getLanguage($lang_id) !== false) ? $lang_id : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($image_id){
            $cache_id = 'jeproshop_image_model_' . (int)$image_id . '_' . (int)$lang_id;
            if(!JeproshopCache::isStored($cache_id)){
                $db = JFactory::getDBO();

                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_image') . " AS image ";
                if($lang_id){
                    $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_image_lang') . " AS image_lang ON (image." . $db->quoteName('image_id');
                    $query .= " = image_lang." . $db->quoteName('image_id') . " AND image." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ") ";
                }
                $query .= "WHERE image." . $db->quoteName('image_id') . " = " . (int)$image_id;

                $db->setQuery($query);
                $image_data = $db->loadObject();

                if($image_data){
                    if(!$lang_id && isset($this->multilang) && $this->multilang){
                        $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_image_lang');
                        $query .= " WHERE image_id = " . (int)$image_id;

                        $db->setQuery($query);
                        $image_lang_data = $db->loadObjectList();
                        if($image_lang_data){
                            foreach ($image_lang_data as $row){
                                foreach($row as $key => $value){
                                    if(array_key_exists($key, $this) && $key != 'image_id'){
                                        if(!isset($image_data->{$key}) || !is_array($image_data->{$key})){
                                            $image_data->{$key} = array();
                                        }
                                        $image_data->{$key}[$row->lang_id] = $value;
                                    }
                                }
                            }
                        }
                        JeproshopCache::store($cache_id, $image_data);
                    }
                }
            }else{
                $image_data = JeproshopCache::retrieve($cache_id);
            }

            if($image_data){
                $image_data->image_id = $image_id;
                foreach($image_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }
            }
        }
        $this->image_dir = COM_JEPROSHOP_PRODUCT_IMAGE_DIRECTORY;
        $this->source_index = COM_JEPROSHOP_PRODUCT_IMAGE_DIRECTORY.'index.php';
    }

    public static function getSize($type){
        if (!isset(self::$_cacheGetSize[$type]) || self::$_cacheGetSize[$type] === null){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('width') . ", " . $db->quoteName('height') . " FROM " . $db->quoteName('#__jeproshop_image_type');
            $query .= " WHERE " . $db->quoteName('name') . " = " . $db->quote($db->escape($type));

            $db->setQuery($query);
            self::$_cacheGetSize[$type] = $db->loadObject();
        }
        return self::$_cacheGetSize[$type];
    }

    /**
     * Returns the path to the folder containing the image in the new filesystem
     *
     * @param integer $image_id
     * @return string path to folder
     */
    public static function getStaticImageFolder($image_id) {
        if (!is_numeric($image_id)){ return false; }
        $folders = str_split((string)$image_id);
        return implode('/', $folders).'/';
    }

}



class JeproshopImageTypeModelImageType extends JModelLegacy
{
    /**
     * @var array Image types cache
     */
    protected static $images_types_cache = array();

    protected static $images_types_name_cache = array();

    public static function getFormatName($name){
        $theme_name = JeproshopContext::getContext()->shop->theme_name;
        $name_without_theme_name = str_replace(array('_'.$theme_name, $theme_name.'_'), '', $name);

        //check if the theme name is already in $name if yes only return $name
        if (strstr($name, $theme_name) && self::getByNameNType($name))
            return $name;
        else if (self::getByNameNType($name_without_theme_name.'_'.$theme_name))
            return $name_without_theme_name.'_'.$theme_name;
        else if (self::getByNameNType($theme_name.'_'.$name_without_theme_name))
            return $theme_name.'_'.$name_without_theme_name;
        else
            return $name_without_theme_name.'_default';
    }

    /**
     * Finds image type definition by name and type
     * @param string $name
     * @param string $type
     * @param null $order
     * @return
     */
    public static function getByNameNType($name, $type = null, $order = null) {
        if (!isset(self::$images_types_name_cache[$name.'_'.$type.'_'.$order])){
            $db = JFactory::getDBO();

            $query = "SELECT " . $db->quoteName('image_type_id') . ", " . $db->quoteNam('name') . ", " . $db->quoteName('width') . ", " . $db->quoteName('height');
            $query .= ", " . $db->quoteName('products') . ", " . $db->quoteName('categories') . ", " . $db->quoteName('manufacturers') . ", " . $db->quoteName('suppliers');
            $query .= ", " . $db->quoteName('scenes') . " FROM " . $db->quoteName('#__jeproshop_image_type') . " WHERE " . $db->quoteName('name') . " LIKE ";
            $query .= $db->quote($db->escape($name)) . (!is_null($type) ? " AND " . $db->quoteName($db->escape($type)) . " = 1" : "");
            $query .= (!is_null($order) ? " ORDER BY " . $db->quoteName($db->escape($order)) . " ASC" : '');
            self::$images_types_name_cache[$name.'_'.$type.'_'.$order] = $db->loadObject();
        }
        return self::$images_types_name_cache[$name.'_'.$type.'_'.$order];
    }
}