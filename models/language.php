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

class JeproshopLanguageModelLanguage extends JModelLegacy
{
    public $lang_id;

    public $name;

    public $iso_code;

    public $language_code;

    public $is_default = 0;

    public $published;

    /** @var string date format http://http://php.net/manual/en/function.date.php with the date only */
    public $date_format_lite = 'Y-m-d';

    /** @var string date format http://http://php.net/manual/en/function.date.php with hours and minutes */
    public $date_format_full = 'Y-m-d H:i:s';

    /** @var bool true if this language is right to left language */
    public $is_rtl = false;

    protected static $_LANGUAGES = array();

    public function __construct($lang_id = null){
        parent::__construct();

        $db = JFactory::getDBO();
        $default_language = JFactory::getLanguage();

        if($lang_id){
            /** Load shop from database if  id is supplied */
            $cache_id = 'jeproshop_language_model_' . (($lang_id > 0) ? '_' . $lang_id : '');
            if(!JeproshopCache::isStored($cache_id)){
                $query = " SELECT * FROM " . $db->quoteName('#__languages') . " lang WHERE lang.lang_id = " .  (int)$lang_id;

                $db->setQuery($query);
                $language_data = $db->loadObject();
                if($language_data){
                    JeproshopCache::store($cache_id, $language_data);
                }
            }else{
                $language_data = JeproshopCache::retrieve($cache_id);
            }

            if($language_data){
                $language_data->lang_id = (int)$lang_id;
                foreach($language_data as $key => $value){
                    if(array_key_exists($key, $this)){
                        $this->{$key} = $value;
                    }
                }

                if($this->language_code == $default_language->getTag()){
                    $this->is_default = 1;
                }
            }
        }
    }

    public static function loadLanguages(){
        $db = JFactory::getDBO();
        self::$_LANGUAGES = array();

        $query = " SELECT * FROM " . $db->quoteName('#__languages') ;

        $db->setQuery($query);
        $languages = $db->loadObjectList();

        $default_language = JFactory::getLanguage();

        foreach($languages as $language){
            if(!isset(self::$_LANGUAGES[(int) $language->lang_id])){
                $lang = new JObject();
                $lang->lang_id = $language->lang_id;
                $lang->language_code = $language->lang_code;
                $lang->name = $language->title;
                $lang->iso_code = $language->sef;
                $lang->published = $language->published;
                (($lang->language_code == $default_language->getTag()) ? $lang->is_default = 1 : $lang->is_default = 0);

                self::$_LANGUAGES[(int)$language->lang_id] = $lang;
            }
        }
    }

    public static function getLanguage($lang_id){
        if(!array_key_exists((int)($lang_id), self::$_LANGUAGES)){
            return false;
        }
        return self::$_LANGUAGES[(int)($lang_id)];
    }

    /**
     * Return available languages
     *
     * @param bool $published
     * @param bool $shop_id     *
     * @return array Languages
     */
    public static function getLanguages($published = true, $shop_id = false){
        if(!self::$_LANGUAGES){
            JeproshopLanguageModelLanguage::loadLanguages();
        }

        $default_language = JFactory::getLanguage();

        $languages = array();
        foreach(self::$_LANGUAGES as $language){
            if($published && !$language->published || ($shop_id && !isset($language->shops[(int)$shop_id]))){ continue; }

            if($default_language->getTag() == $language->language_code ){$language->is_default = 1; }

            $languages[] = $language;
        }
        return $languages;
    }

    /**
     * Check if current object is associated to a shop
     *
     * @param int $shop_id
     * @return bool
     */
    public function isAssociatedToShop($shop_id = null){
        if ($shop_id === null){
            $shop_id = JeproshopContext::getContext()->shop->shop_id;
        }
        $cache_id = 'jeproshp_model_shop_language_'. (int)$this->lang_id.'-'.(int)$shop_id;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();
            $query = "SELECT shop_id FROM " . $db->quoteName('#__jeproshop_language_shop') . " WHERE ";
            $query .= $db->quoteName('lang_id') . " = " . $this->lang_id . " AND shop_id = " .(int)$shop_id;

            $db->setQuery($query);
            JeproshopCache::store($cache_id, (bool)$db->loadResult());
        }
        return JeproshopCache::retrieve($cache_id);
    }

    /**
     * Return iso code from id
     *
     * @param $lang_id
     * @return string Iso code
     */
    public static function getIsoById($lang_id){
        if (isset(self::$_LANGUAGES[(int)$lang_id]->iso_code))
            return self::$_LANGUAGES[(int)$lang_id]->iso_code;
        return false;
    }
	
	public static function getLanguageByIETFCode($code){
        if (!JeproshopTools::isLanguageCode($code)) {
            die(sprintf(Tools::displayError('Fatal error: IETF code %s is not correct'), Tools::safeOutput($code)));
        }

        // $code is in the form of 'xx-YY' where xx is the language code
        // and 'YY' a country code identifying a variant of the language.
        $lang_country = explode('-', $code);
        // Get the language component of the code
        $lang = $lang_country[0];

        // Find the id_lang of the language.
        // We look for anything with the correct language code
        // and sort on equality with the exact IETF code wanted.
        // That way using only one query we get either the exact wanted language
        // or a close match.
        $db = JFactory::getDBO();
        $query = "SELECT " . $db->quoteName("lang_id") . ", IF(sef = " . $db->quote($code) . ", 0, LENGTH(language_code)) as found ";
        $query .= " FROM " . $db->quoteName('#_languages') . " WHERE LEFT(`language_code`,2) = ". $db->quote($lang). "	ORDER BY found ASC";
        $db->setQuery($query);

        $lang_id = $db->loadValue();

        // Instantiate the Language object if we found it.
        if ($lang_id) {
            return new JeproshopLanguageModelLanguage($lang_id);
        } else {
            return false;
        }
    }
}