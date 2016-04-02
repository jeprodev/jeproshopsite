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

class JeproshopEmployeeModelEmployee extends JModelLegacy
{
    public $employee_id;

    public $customer_id;

    public $profile_id;

    public $lang_id;

    public $shop_id;

    public $theme = 'default';

    public $stats_date_from;
    public $stats_date_to;

    /** @var datetime Password **/
    public $last_passwd_gen;
    public $stats_compare_from;
    public $stats_compare_to;
    public $stats_compare_option = 1;

    public $preselect_date_range;

    protected $associated_shops = array();


    public function __construct($employee_id = NULL, $lang_id = NULL, $shop_id = null) {
        parent::__construct();

        $db = JFactory::getDBO();

        if($lang_id !== null){
            $this->lang_id = (JeproshopLanguageModelLanguage::getLanguage($lang_id) !== false)  ? (int)$lang_id : JeproshopSettingModelSetting::getValue('default_lang');
        }

        if($shop_id && $this->isMultiShop()){
            $this->shop_id = (int)$shop_id;
            $this->getShopFromContext = false;
        }
        /*
                if($this->isMultiShop() && !$this->shop_id){
                    $this->shop_id = JeproshopContext::getContext()->shop->shop_id;
                }

                if($employee_id){
                    /** load employee from database if employee * /
                    $cache_id = 'jeproshop_employee_model_' . (int)$employee_id . (($lang_id) ? '_lang_' . (int)$lang_id : '') .(($shop_id) ? '_shop_' .(int)$shop_id : '');
                    if(!JeproshopCache::isStored($cache_id)){
                        $query = "SELECT * FROM " . $db->quoteName('#__users') . " AS employee LEFT JOIN " . $db->quoteName('#__jeproshop_employee');
                        $query .= " AS alias ON(employee.id = alias.employee_id) ";
                        //add language filter
                        $where = "";
                        if($lang_id){
                            $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_employee_lang') . " employee_lang ON(alias.";
                            $query .= $db->quoteName('employee_id') . " = employee_lang." . $db->quoteName('employee_id') ;
                            $query .= " AND employee_lang." . $db->quoteName('lang_id') . " = " . (int)$lang_id . ")";
                            if($this->shop_id && !empty($this->multiLangShop)){
                                $where = " AND employee_lang." . $db->quoteName('shop_id') . " = " . $this->shop_id;
                            }
                        }

                        /** get shop information * /
                        if(JeproshopShopModelShop::isTableAssociated('employee')){
                            $query .= " LEFT JOIN " . $db->quoteName('#__jeproshop_employee_shop') . " AS shop ON(alias.employee_id";
                            $query .= " = shop.employee_id AND shop.shop_id = " .  (int)$this->shop_id . ")";
                        }

                        $query .= " WHERE employee." . $db->quoteName('id') . " = " . (int)$employee_id . $where;
                        $db->setQuery($query);

                        $employee_data = $db->loadObject();
                        if($employee_data){
                            if(!$lang_id && isset($this->multiLang) && $this->multiLang){
                                $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_employee_lang') . " WHERE " . $db->quoteName('employee_id');
                                $query .= " = " . (int)$employee_id . (($this->shop_id && $this->isLangMultiShop()) ? " AND " . $db->quoteName('shop_id') . " = " . $this->shop_id : "");

                                $db->setQuery($query);
                                $employee_lang_data = $db->loadObjectList();
                                if($employee_lang_data){
                                    foreach($employee_lang_data as $row){
                                        foreach($row as $key => $value){
                                            if(array_key_exists($key, $this) && $key != 'employee_id'){
                                                if(!isset($employee_data->{$key}) || !is_array($employee_data->{$key})){
                                                    $employee_data->{$key} = array();
                                                }
                                                $employee_data->{$key}[$row->lang_id] =$value;
                                            }
                                        }
                                    }
                                }
                            }
                            JeproshopCache::store($cache_id, $employee_data);
                        }
                    } else{
                        $employee_data = JeproshopCache::retrieve($cache_id);
                    }

                    if($employee_data){
                        $employee_data->employee_id = (int)$employee_id;
                        foreach($employee_data as $key => $value){
                            if(array_key_exists($key, $this)){
                                $this->{$key} = $value;
                            }
                        }
                    }
                }

                if($this->employee_id){
                    $this->associated_shops = $this->getAssociatedShops();
                }
                */
        $this->image_dir = COM_JEPROSHOP_EMPLOYEE_IMAGE_DIRECTORY;
    }
}