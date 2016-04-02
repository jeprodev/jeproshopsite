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

class JeproshopCategoryViewCategory extends JViewLegacy
{
    public $orderBy;
    public $orderWay;

    protected $category;
    protected $context ;
    protected $customer_access = true;
    protected $errors = array();

    public function display($tpl = null){
        if(!isset($this->context) && $this->context == null){ $this->context = JeproshopContext::getContext(); }
        /*if($this->context->controller->isInitialized()){*/ $this->init(); //}
        if(!$this->customer_access){ return; }

        if(isset($this->context->cookie->compare_id)){
            $this->assignRef('compare_products', JeproshopCompareProductModelCompareProduct::getCompareProducts((int)$this->context->cookie->compare_id));
        }

        $this->assignScenes();
        $this->sortProducts();
        $subCategories = $this->category->getSubCategories($this->context->language->lang_id);
        if(count($subCategories)){
            $this->assignRef('sub_categories', $subCategories);
            $this->assignRef('sub_categories_total', count($subCategories));
            $this->assignRef('sub_categories_half', ceil(count($subCategories)/2));
        }
        $this->assignProducts();
        $catalog_mode = (bool)(JeproshopSettingModelSetting::getValue('catalog_mode') || !JeproshopGroupModelGroup::getCurrent()->show_prices);
        $this->assignRef('catalog_mode', $catalog_mode);
        $this->assignRef('allow_out_of_stock_ordering', JeproshopSettingModelSetting::getValue('allow_out_of_stock_ordering'));
        $comparator_max_item = (int)JeproshopSettingModelSetting::getValue('comparator_max_item');
        $this->assignRef('comparator_max_item', $comparator_max_item);
        $compared_products = array();
        if (JeproshopSettingModelSetting::getValue('comparator_max_item') && isset($this->context->cookie->compare_id)){
            $compared_products = JeproshopProductCompareModelProductCompare::getCompareProducts($this->context->cookie->compare_id);
        }
        $this->assignRef('compared_products', is_array($compared_products) ? $compared_products : array());
        $this->assignRef('display_price', JeproshopSettingModelSetting::getValue('display_price'));
        parent::display($tpl);
    }

    protected function sortProducts(){
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');
        $stock_management = (int)JeproshopSettingModelSetting::getValue('stock_management');
        $order_by_values = array(0 => 'name', 1 => 'price', 2 => 'date_add', 3 => 'date_upd', 4 => 'position', 5 => 'manufacturer_name', 6 => 'quantity', 7 => 'reference');
        $order_way_values = array(0 => 'asc', 1 => 'desc');
        $this->orderBy = $app->getUserStateFromRequest($option, $view, 'order_by', 'order_by', JeproshopSettingModelSetting::getValue('default_order_by'), 'string');
        $this->orderWay = $app->getUserStateFromRequest($option, $view, 'order_way', 'order_way', JeproshopSettingModelSetting::getValue('default_order_way'), 'string');

        if (!in_array($this->orderBy, $order_by_values))
            $this->orderBy = $order_by_values[0];
        if (!in_array($this->orderWay, $order_way_values))
            $this->orderWay = $order_way_values[0];

        $this->assignRef('order_by_default', $order_by_values[(int)JeproshopSettingModelSetting::getValue('default_order_way')]);
        $this->assignRef('order_way_position', $order_way_values[(int)JeproshopSettingModelSetting::getValue('default_order_way')]); // Deprecated: orderwayposition
        $this->assignRef('default_order_way', $order_way_values[(int)JeproshopSettingModelSetting::getValue('default_order_way')]);
        $this->assignRef('stock_management', $stock_management);
    }

    /**
     * Assign scenes template vars
     */
    protected function assignScenes(){
        // Scenes (could be externalised to another controller if you need them)
        $scenes = JeproshopSceneModelScene::getScenes($this->category->category_id, $this->context->language->lang_id, true, false);
        $this->assignRef('scenes', $scenes);

        // Scenes images formats
        if ($scenes && ($sceneImageTypes = JeproshopImageTypeModelImageType::getImagesTypes('scenes'))){
            foreach ($sceneImageTypes as $sceneImageType){
                if ($sceneImageType->name == JeproshopImageTypeModelImageType::getFormatedName('m_scene')){
                    $thumbSceneImageType = $sceneImageType;
                }elseif ($sceneImageType->name == JeproshopImageTypeModelImageType::getFormatedName('scene')){
                    $largeSceneImageType = $sceneImageType;
                }
            }

            $this->assignRef('thumbSceneImageType', isset($thumbSceneImageType) ? $thumbSceneImageType : null);
            $this->assignRef('largeSceneImageType', isset($largeSceneImageType) ? $largeSceneImageType : null);
        }
    }

    protected function assignProducts(){
        $app = JFactory::getApplication();
        $option = $app->input->get('option');
        $view = $app->input->get('view');
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limit_start = $app->getUserStateFromRequest($option. $view. '.limitstart', 'limitstart', 0, 'int');
        $numberProducts = $this->category->getProducts(null, null, null, $this->orderBy, $this->orderWay, true);
        $categoryProducts = $this->category->getProducts($this->context->language->lang_id, $limit_start, $limit, $this->orderBy, $this->orderWay);

        foreach($categoryProducts as &$product){
            if($product->product_attribute_id && isset($product->product_attribute_minimal_quantity)){
                $product->minimal_quantity = $product->product_attribute_minimal_quantity;
            }
        }
        $this->context->controller->addColorsToProductList($categoryProducts);
        $this->assignRef('number_products', $numberProducts);
        $this->assignRef('category_products', $categoryProducts);
    }

    private function init(){
        $app = JFactory::getApplication();
        $category_id = $app->input->get('category_id');
        if(!$category_id || !JeproshopValidator::isUnsignedInt($category_id)){

        }

        $this->category = new JeproshopCategoryModelCategory($category_id, $this->context->language->lang_id);
        $this->context->controller->init();
        if(!$this->category->published){
            header('HTTP/1.1 4O4 Not Found');
            header('Status: 404 Not Found');
        }

        //check if category can be accessible by current customer and return 403 if not
        if (!$this->category->checkAccess($this->context->customer->customer_id)){
            header('HTTP/1.1 403 Forbidden');
            header('Status: 403 Forbidden');
            $this->errors[] = JText::_('You do not have access to this category.');
            $this->customer_access = false;
        }
        $this->context->controller->init();
    }
}