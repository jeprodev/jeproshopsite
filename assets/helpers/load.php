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

/** requiring helpers class in order to avoid multiple inclusion */ //JPATH_ADMINISTRATOR
$rootPath = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . DIRECTORY_SEPARATOR . 'administrator';
$adminPath = $rootPath . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'helpers';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'defines.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'helper.php';
//require_once $adminPath. DIRECTORY_SEPARATOR . 'defines.inc.php';
require_once $adminPath. DIRECTORY_SEPARATOR . 'customization.php';
require_once $adminPath . DIRECTORY_SEPARATOR . 'settings.inc.php';
require_once $adminPath . DIRECTORY_SEPARATOR . 'uploader.php';
require_once $adminPath . DIRECTORY_SEPARATOR . 'cookie.php';
require_once $adminPath . DIRECTORY_SEPARATOR . 'cache.php';
require_once $adminPath . DIRECTORY_SEPARATOR . 'context.php';
require_once $adminPath . DIRECTORY_SEPARATOR . 'tools.php';

/** require models */
$modelDirectoryPath = dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'models' . DIRECTORY_SEPARATOR;
require_once($modelDirectoryPath . 'attachment.php');
require_once($modelDirectoryPath . 'attribute.php');
require_once($modelDirectoryPath . 'address.php');
require_once($modelDirectoryPath . 'carrier.php');
require_once($modelDirectoryPath . 'cart.php');
require_once($modelDirectoryPath . 'category.php');
require_once($modelDirectoryPath . 'combination.php');
require_once($modelDirectoryPath . 'country.php');
require_once($modelDirectoryPath . 'customer.php');
require_once($modelDirectoryPath . 'currency.php');
require_once($modelDirectoryPath . 'default.php');
require_once($modelDirectoryPath . 'employee.php');
require_once($modelDirectoryPath . 'feature.php');
require_once($modelDirectoryPath . 'group.php');
require_once($modelDirectoryPath . 'image.php');
require_once($modelDirectoryPath . 'language.php');
require_once($modelDirectoryPath . 'manufacturer.php');
require_once($modelDirectoryPath . 'order.php');
require_once($modelDirectoryPath . 'price.php');
require_once($modelDirectoryPath . 'product.php');
require_once($modelDirectoryPath . 'scene.php');
require_once($modelDirectoryPath . 'setting.php');
require_once($modelDirectoryPath . 'shop.php');
require_once($modelDirectoryPath . 'stock.php');
require_once($modelDirectoryPath . 'supplier.php');
require_once($modelDirectoryPath . 'tag.php');
require_once($modelDirectoryPath . 'tax.php');