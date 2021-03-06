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

class JeproshopTagModelTag extends JModelLegacy
{
    public $tag_id;

    public $lang_id;

    public $name;

    public static function getProductTags($produt_id){
        $db = JFactory::getDBO();
        $query = "SELECT tag." . $db->quoteName('lang_id') . ", tag." . $db->quoteName('name') . " FROM " . $db->quoteName('#__jeproshop_tag');
        $query .= " AS tag LEFT JOIN " . $db->quoteName('#__jeproshop_product_tag') . " AS product_tag ON (product_tag." . $db->quoteName('tag_id');
        $query .= " = tag." . $db->quoteName('tag_id') . ") WHERE product_tag." . $db->quoteName('product_id') . " = " . (int)$produt_id;
        $db->setQuery($query);
        $tmp = $db->loadObjectList();
        if(!$tmp ){
            return FALSE;
        }
        return $tmp;
    }
}