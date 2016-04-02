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

jimport('joomla.filesystem.folder');

if(!defined('COM_JEPROSHOP_SSL_PORT')){
    define('COM_JEPROSHOP_SSL_PORT', 443);
}

/* Debug only */
define('COM_JEPROSHOP_MODE_DEV', false);
/* Compatibility warning */
define('COM_JEPROSHOP_DISPLAY_COMPATIBILITY_WARNING', false);

define('COM_JEPROSHOP_THEME_DIR', JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'themes');

if(!defined('COM_JEPROSHOP_IMAGE_DIR')){
    $imageDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images';
    if(!JFolder::exists($imageDirectory)){
        JFolder::create($imageDirectory);
    }
    define('COM_JEPROSHOP_IMAGE_DIR',  'media/com_jeproshop/images/');
}

if(!defined('COM_JEPROSHOP_PRODUCT_IMAGE_DIRECTORY')){
    $productDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($productDirectory)){
        JFolder::create($productDirectory);
    }
    define('COM_JEPROSHOP_PRODUCT_IMAGE_DIRECTORY',  '/media/com_jeproshop/images/products/');
}

if(!defined('COM_JEPROSHOP_COLOR_IMAGE_DIRECTORY')){
    $productDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'colors' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($productDirectory)){
        JFolder::create($productDirectory);
    }
    define('COM_JEPROSHOP_COLOR_IMAGE_DIRECTORY',  '/media/com_jeproshop/images/colors/');
}

if(!defined('COM_JEPROSHOP_CATEGORY_IMAGE_DIRECTORY')){
    $categoryImageDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'categories' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($categoryImageDirectory)){
        JFolder::create($categoryImageDirectory);
    }
    define('COM_JEPROSHOP_CATEGORY_IMAGE_DIRECTORY', '/media/com_jeproshop/categories/colors/');
}

if(!defined('COM_JEPROSHOP_MANUFACTURER_IMAGE_DIRECTORY')){
    $manufacturerImageDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'manufacturers' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($categoryImageDirectory)){
        JFolder::create($categoryImageDirectory);
    }
    define('COM_JEPROSHOP_MANUFACTURER_IMAGE_DIRECTORY', '/media/com_jeproshop/images/manufacturers/');
}

if(!defined('COM_JEPROSHOP_EMPLOYEE_IMAGE_DIRECTORY')){
    $employeeImageDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'employee' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($employeeImageDirectory)){ JFolder::create($employeeImageDirectory); }
    define('COM_JEPROSHOP_EMPLOYEE_IMAGE_DIRECTORY', $employeeImageDirectory);
}

if(!defined('COM_JEPROSHOP_CARRIER_IMAGE_DIRECTORY')){
    $shippingImageDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($shippingImageDirectory)){
        JFolder::create($shippingImageDirectory);
    }
    define('COM_JEPROSHOP_CARRIER_IMAGE_DIRECTORY', $shippingImageDirectory);
}
/*
if(!defined('COM_JEPROSHOP_COLOR_IMAGE_DIRECTORY')){
    $colorImageDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'color' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($colorImageDirectory)){
        JFolder::create($colorImageDirectory);
    }
    define('COM_JEPROSHOP_COLOR_IMAGE_DIRECTORY', $colorImageDirectory);
}
*/
if(!defined('COM_JEPROSHOP_STORE_IMAGE_DIR')){
    jimport('joomla.filesystem.folder');
    $storeImageDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'stores' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($storeImageDirectory)){
        JFolder::create($storeImageDirectory);
    }
    define('COM_JEPROSHOP_STORE_IMAGE_DIR', $storeImageDirectory);
}

if(!defined('COM_JEPROSHOP_DEVELOPER_IMAGE_DIR')){
    jimport('joomla.filesystem.folder');
    $developerImageDirectory = JPATH_SITE . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'com_jeproshop' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'developers' . DIRECTORY_SEPARATOR;
    if(!JFolder::exists($developerImageDirectory)){
        JFolder::create($developerImageDirectory);
    }
    define('COM_JEPROSHOP_DEVELOPER_IMAGE_DIR', $developerImageDirectory);
}

/* Tax behavior */
define('COM_JEPROSHOP_PRODUCT_TAX', 0);
define('COM_JEPROSHOP_STATE_TAX', 1);
define('COM_JEPROSHOP_BOTH_TAX', 2);

define('COM_JEPROSHOP_PRICE_DISPLAY_PRECISION', 2);
define('COM_JEPROSHOP_TAX_EXCLUDED', 1);
define('COM_JEPROSHOP_TAX_INCLUDED', 0);

define('COM_JEPROSHOP_ROUND_UP_PRICE', 0);
define('COM_JEPROSHOP_ROUND_DOWN_PRICE', 1);
define('COM_JEPROSHOP_ROUND_HALF_PRICE', 2);
