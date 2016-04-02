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

class JeproshopSceneModelScene extends JModelLegacy
{
    /** @var string Name */
    public $name;

    /** @var boolean Active Scene */
    public $published = true;

    /** @var array Zone for image map */
    public $zones = array();

    /** @var array list of category where this scene is available */
    public $categories = array();

    /** @var array Products */
    public $products;

    /**
     * Get all scenes of a category
     *
     * @param $category_id
     * @param null $lang_id
     * @param bool $only_published
     * @param bool $lite_result
     * @param bool $hide_scene_position
     * @param JeproshopContext $context
     * @return array Products
     */
    public static function getScenes($category_id, $lang_id = null, $only_published = true, $lite_result = true, $hide_scene_position = true, JeproshopContext $context = null){
        if (!JeproshopSceneModelScene::isFeaturePublished()){ return array(); }

        $cache_key = 'jeproshop_scene_get_scenes_'.$category_id . '_' .(int)$lite_result;
        if (!JeproshopCache::isStored($cache_key)){
            if (!$context)
                $context = JeproshopContext::getContext();
            $lang_id = is_null($lang_id) ? $context->language->lang_id : $lang_id;

            $db = JFactory::getDBO();
            $query = "SELECT scene.* FROM " . $db->quoteName('#__jeproshop_scene_category') . " scene_category LEFT JOIN " . $db->quoteName('#__jeproshop_scene');
            $query .= " AS scene ON (scene_category.scene_id = scene.scene_id) " . JeproshopShopModelShop::addSqlAssociation('scene') . " LEFT JOIN ";
            $query .= $db->quoteName('#__jeproshop_scene_lang') . " AS scene_lang ON (scene_lang.scene_id = scene.scene_id) WHERE scene_category.category_id = ";
            $query .= (int)$category_id . "	AND scene_lang.lang_id = " .(int)$lang_id . ($only_published ? " AND scene.published = 1" : ""). " ORDER BY scene_lang.name ASC";

            $db->setQuery($query);
            $scenes = $db->loadObjectList();

            if (!$lite_result && $scenes){
                foreach ($scenes as &$scene){
                    $scene = new Scene($scene->scene_id, $lang_id, false, $hide_scene_position);
                }
            }
            JeproshopCache::store($cache_key, $scenes);
        }
        $scenes = JeproshopCache::retrieve($cache_key);
        return $scenes;
    }

    /**
     * This method is allow to know if a feature is used or active
     *
     * @return bool
     */
    public static function isFeaturePublished(){
        return JeproshopSettingModelSetting::getValue('scene_feature_active');
    }
}