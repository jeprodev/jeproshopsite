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

class JeproshopSettingModelSetting extends JModelLegacy
{
    public $setting_id;

    /** @var string value **/
    public $value;

    /** @var string Object creation date Description **/
    public $date_add;

    /** @var string Object last modification date Description **/
    public $date_upd;

    /** @var array Setting cache **/
    protected static $_SETTINGS;

    /** @var array Vars types **/
    protected static $types = array();

    /**
     * Load all setting data
     */
    public static function loadSettings(){
        self::$_SETTINGS = array();

        $db = JFactory::getDBO();

        $query = "SELECT setting." . $db->quoteName('name') . ", setting." . $db->quoteName('value') . " FROM " . $db->quoteName('#__jeproshop_setting') . " AS setting";

        $db->setQuery($query);
        if(!$settings = $db->loadObjectList()){ return; }

        foreach($settings as $setting){
            if(!isset(self::$_SETTINGS)){
                self::$_SETTINGS = array('global' => array(), 'group' => array(), 'shop' => array());
            }

            if(isset($setting->shop_id)){
                self::$_SETTINGS['shop'][$setting->shop_id][$setting->name] = $setting->value;
            }elseif(isset($setting->shop_group_id)){
                self::$_SETTINGS['group'][$setting->shop_group_id][$setting->name] = $setting->value;
            }else{
                self::$_SETTINGS['global'][$setting->name] = $setting->value ;
            }
        }
    }

    public static function getValue($key, $shop_group_id = NULL, $shop_id = NULL){
        /** If setting is not initialized, try manual query **/
        if(!self::$_SETTINGS){
            JeproshopSettingModelSetting::loadSettings();

            if(!self::$_SETTINGS){
                $db = JFactory::getDBO();
                $query = "SELECT " . $db->quoteName('value') . " FROM " . $db->quoteName('#__jeproshop_setting');
                $query .= " WHERE " . $db->quoteName('name') . " = " . $db->quote($db->escape($key));

                $db->setQuery($query);
                $settingValue = $db->loadResult();
                return ($settingValue ? $settingValue : $key);
            }
        }

        if($shop_id && JeproshopSettingModelSetting::hasKey($key, NULL, $shop_id)){
            return self::$_SETTINGS['shop'][$shop_id][$key];
        }elseif($shop_group_id && JeproshopSettingModelSetting::hasKey($key)){
            return self::$_SETTINGS['group'][$shop_group_id][$key];
        }elseif(JeproshopSettingModelSetting::hasKey($key)){
            return self::$_SETTINGS['global'][$key];
        }else {     echo $key;     exit();  }
        return FALSE;
    }

    /**
     *
     * @param String $key the setting key to retrieve data
     * @param int $shop_group_id
     * @param int $shop_id
     * @internal param \type $lang_id
     * @return type
     */
    public static function hasKey($key, $shop_group_id = NULL, $shop_id = NULL){
        if($shop_id){
            return isset(self::$_SETTINGS['shop'][$shop_id]) && array_key_exists($key, self::$_SETTINGS['shop'][$shop_id]);
        }elseif($shop_group_id){
            return isset(self::$_SETTINGS['group'][$shop_group_id]) && array_key_exists($key, self::$_SETTINGS['group'][$shop_group_id]);
        }
        return isset(self::$_SETTINGS['global']) && array_key_exists($key, self::$_SETTINGS['global']);
    }
}