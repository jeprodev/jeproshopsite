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

class JeproshopProductController extends JeproshopController
{
    /*public function initialize(){
        parent::initialize();
        $app = JFactory::getApplication();
        $context = JeproshopContext::getContext();
        $view = $app->input->get('view', 'product');
        $layout = $app->input->get('layout', 'product');
        $viewClass = $this->getView($view, JFactory::getDocument()->getType());
        $product_id = (int)$app->input->get('product_id');

        if($product_id){
            $product = new JeproshopProductModelProduct($product_id, true, $context->language->lang_id, $context->shop->shop_id);
        }else{ $product = null; }

        if(!JeproshopTools::isLoadedObject($product, 'product_id')){
            header('HTTP/1.1 404 Not Found');
            header('Status: 404 Not Found');
            $this->has_errors = true;
            JError::raiseError(500, JText::_('COM_JEPROSHOP_PRODUCT_NOT_FOUND_MESSAGE'));
        }else{
            $this->canonicalRedirection();

            if(!$product->isAssociatedToShop() || !$product->published){
                if (Tools::getValue('adtoken') == Tools::getAdminToken('AdminProducts'.(int)Tab::getIdFromClassName('AdminProducts').(int)Tools::getValue('id_employee')) && $this->product->isAssociatedToShop())
                {
                    // If the product is not active, it's the admin preview mode
                    $this->context->smarty->assign('adminActionDisplay', true);
                }
                else
                {
                    $this->context->smarty->assign('adminActionDisplay', false);
                    if ($this->product->id_product_redirected == $this->product->id)
                        $this->product->redirect_type = '404';

                    switch ($product->redirect_type)
                    {
                        case '301':
                            header('HTTP/1.1 301 Moved Permanently');
                            header('Location: '.$this->context->link->getProductLink($product->product_redirected_id));
                            break;
                        case '302':
                            header('HTTP/1.1 302 Moved Temporarily');
                            header('Cache-Control: no-cache');
                            header('Location: '. $context->link->getProductLink($product->product_redirected_id));
                            break;
                        case '404':
                        default:
                            header('HTTP/1.1 404 Not Found');
                            header('Status: 404 Not Found');
                            $this->errors[] = Tools::displayError('This product is no longer available.');
                            break;
                    }
                }
            }elseif(!$product->checkAccess(isset($context->customer) ? $context->customer->customer_id : 0)){
                header('HTTP/1.1 403 Forbidden');
                header('Status: 403 Forbidden');
                $this->has_errors = true;
                JError::raiseError(500, JText::_('You do not have access to this product.'));
            }else{
                // Load category
                $id_category = false;
                if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == Tools::secureReferrer($_SERVER['HTTP_REFERER']) // Assure us the previous page was one of the shop
                    && preg_match('~^.*(?<!\/content)\/([0-9]+)\-(.*[^\.])|(.*)id_(category|product)=([0-9]+)(.*)$~', $_SERVER['HTTP_REFERER'], $regs))
                {
                    // If the previous page was a category and is a parent category of the product use this category as parent category
                    $id_object = false;
                    if (isset($regs[1]) && is_numeric($regs[1]))
                        $id_object = (int)$regs[1];
                    elseif (isset($regs[5]) && is_numeric($regs[5]))
                        $id_object = (int)$regs[5];
                    if ($id_object)
                    {
                        $referrers = array($_SERVER['HTTP_REFERER'],urldecode($_SERVER['HTTP_REFERER']));
                        if (in_array($context->link->getCategoryLink($id_object), $referrers))
                            $id_category = (int)$id_object;
                        elseif (isset($context->cookie->last_visited_category) && (int)$context->cookie->last_visited_category && in_array($context->link->getProductLink($id_object), $referrers))
                            $id_category = (int)$context->cookie->last_visited_category;
                    }

                }
                if (!$id_category || !JeproshopCategoryModelCategory::inShopStatic($id_category, $context->shop) || !Product::idIsOnCategoryId((int)$product->id, array('0' => array('id_category' => $id_category))))
                    $id_category = (int)$product->default_category_id;
                $this->category = new JeproshopCategoryModelCategory((int)$id_category, (int)$context->cookie->lang_id);
                if (isset($context->cookie) && isset($this->category->category_id) && !(Module::isInstalled('blockcategories') && Module::isEnabled('blockcategories')))
                    $context->cookie->last_visited_category = (int)$this->category->id_category;
            }
        }
    }*/

    public function initContent(){
        parent::initContent();
        if(!$this->has_errors){

        }
    }
}