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

class JeproshopViewDefault extends JViewLegacy
{
    protected $context;
    protected static $cache_products = null;

    public function display($tpl = null){
        if(!isset($this->context) || $this->context == null){ $this->context = JeproshopContext::getContext(); }
        if(!$this->context->controller->isInitialized()){ $this->context->controller->initialize();  }
        $app = JFactory::getApplication();

        $useSSL = ((isset($this->context->controller->ssl_enabled) && $this->context->controller->ssl_enabled && $app->input->get('enable_ssl')) || JeproshopTools::usingSecureMode()) ? true : false;
        $protocol_content = ($useSSL) ? 'https://' : 'http://';
        /*$contextParams = $this->context->controller->getContextParams();
        foreach ($contextParams as $assign_key => $assign_value){
            if (!is_array($assign_value) && mb_substr($assign_value, 0, 1, 'utf-8') == '/' || $protocol_content == 'https://'){
                $this->assignRef($assign_key, $protocol_content.JeproshopTools::getMediaServer($assign_value).$assign_value);
            }else{
                $this->assignRef($assign_key, $assign_value);
            }
        }¨*/
        if (!isset(self::$cache_products)) {
            $category = new JeproshopCategoryModelCategory(JeproshopContext::getContext()->shop->getCategoryId(), (int)JeproshopContext::getContext()->language->lang_id);
            $nb = (int)JeproshopSettingModelSetting::getValue('number_of_products_on_page');
            self::$cache_products = JeproshopDefaultModelDefault::getProducts((int)JeproshopContext::getContext()->language->lang_id, 0, ($nb ? $nb : 8), 'position');
        }

        if (self::$cache_products === false || empty(self::$cache_products)){
            self::$cache_products = false;
        }

        $catalog_mode = (bool)(JeproshopSettingModelSetting::getValue('catalog_mode') || !JeproshopGroupModelGroup::getCurrent()->show_prices);
        $this->assignRef('catalog_mode', $catalog_mode);
        $stock_management = (bool)(JeproshopSettingModelSetting::getValue("stock_management"));
        $this->assignRef('stock_management', $stock_management);
        $this->assignRef('products', self::$cache_products);
        $display_add_product = JeproshopSettingModelSetting::getValue('display_category_attribute');
        $this->assignRef('display_add_product', $display_add_product);
        $homeSize = JeproshopImageModelImage::getSize(JeproshopImageTypeModelImageType::getFormatName('home'));
        $this->assignRef('homeSize',  $homeSize);
        $this->assignRef('pagination', JeproshopDefaultModelDefault::$_pagination);
        parent::display($tpl);
    }
}