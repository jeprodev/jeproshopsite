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

class JeproshopTaxModelTax extends JModelLegacy
{
    public $name;

    public $rate;

    public $published;

    public $deleted = 0;

    public static function taxExcludedOption(){
        static $use_tax = null;
        if($use_tax === NULL){
            $use_tax = JeproshopSettingModelSetting::getValue('use_tax');
        }
        return !$use_tax;
    }
    /**
     * Returns the ecotax tax rate
     *
     * @param int $address_id
     * @return float tax rate
     */
    public static function getProductEcotaxRate($address_id = NULL){
        $address = new JeproshopAddressModelAddress($address_id);

        $taxManager = JeproshopTaxManagerFactory::getManager($address, (int)JeproshopSettingModelSetting::getValue('ecotax_tax_rules_group_id'));
        $taxCalculator = $taxManager->getTaxCalculator();

        return $taxCalculator->getTotalRate();
    }
}


class JeproshopTaxRulesGroupModelTaxRulesGroup extends JModelLegacy
{
    public $name;

    public $published;

    public static function getTaxRulesGroups($published = TRUE){
        $db = JFactory::getDBO();

        $query = "SELECT DISTINCT tax_rules_group.tax_rules_group_id, tax_rules_group.name, tax_rules_group.published FROM ";
        $query .= $db->quoteName('#__jeproshop_tax_rules_group') . " AS tax_rules_group ". JeproshopShopModelShop::addSqlAssociation('tax_rules_group');
        $query .= ($published ?  " WHERE tax_rules_group." . $db->quoteName('published') . " =  1" : "") . " ORDER BY name ASC";

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getAssociatedTaxRatesByCountryId($country_id){
        $db = JFactory::getDBO();

        $query = "SELECT tax_rules_group." . $db->quoteName('tax_rules_group_id') . ", tax." . $db->quoteName('rate') . " FROM ";
        $query .= $db->quoteName('#__jeproshop_tax_rules_group') . " AS tax_rules_group LEFT JOIN " . $db->quoteName('#__jeproshop_tax_rule');
        $query .= " AS tax_rule ON(tax_rule." . $db->quoteName('tax_rules_group_id') . " = tax_rules_group." . $db->quoteName('tax_rules_group_id');
        $query .= ") LEFT JOIN " . $db->quoteName('#__jeproshop_tax') . " AS tax ON(tax." . $db->quoteName('tax_id') . " = tax_rule.";
        $query .= $db->quoteName('tax_id') . ") WHERE tax_rule." . $db->quoteName('country_id') . " = " . (int)$country_id . " AND tax_rule.";
        $query .= $db->quoteName('state_id') . " = 0 AND 0 between " . $db->quoteName('zipcode_from') . " AND " . $db->quoteName('zipcode_to');

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}


class JeproshopTaxManagerFactory
{
    protected static $cache_tax_manager;

    /**
     *
     * @param JeproshopAddressModelAddress $address
     * @param $type
     */
    public static function getManager(JeproshopAddressModelAddress $address, $type){
        $cache_id = JeproshopTaxManagerFactory::getCacheKey($address);
        if(!isset(JeproshopTaxManagerFactory::$cache_tax_manager[$cache_id])){
            $tax_manager = JeproshopTaxManagerFactory::getTaxManager($address, $type);
            if(!($tax_manager instanceof JeproshopTaxManagerInterface)){
                $tax_manager = new JeproshopTaxRulesTaxManager($address, $type);
            }
            JeproshopTaxManagerFactory::$cache_tax_manager[$cache_id] = $tax_manager;
        }
        return JeproshopTaxManagerFactory::$cache_tax_manager[$cache_id];
    }

    public static function getTaxManager(JeproshopAddressModelAddress $address, $type){
        return NULL;
    }

    /**
     *
     * Create a unique identifier for the address
     * @param JeproshopAddressModelAddress $address
     * @return string
     */
    protected static function getCacheKey(JeproshopAddressModelAddress $address){
        return $address->country_id . '_' . (int)$address->state_id . '_' . $address->postcode . '_'
        . $address->vat_number . '_' . $address->dni;
    }
}

class JeproshopTaxRulesTaxManager implements JeproshopTaxManagerInterface
{
    public $address;

    public $type;

    public $tax_calculator;

    /**
     *
     * @param JeproshopAddressModelAddress $address
     * @param mixed An additional parameter for the tax manager (ex: tax rules id for JeproshopTaxRuleTaxManager)
     */
    public function __construct(JeproshopAddressModelAddress $address, $type) {
        $this->address = $address;
        $this->type = $type;
    }

    /**
     * Returns true if this tax manager is available for this address
     *
     * @param JeproshopAddressModelAddress $address
     * @return boolean
     */
    public static function isAvailableForThisAddress(JeproshopAddressModelAddress $address){
        $address = NULL;
        return true; // default manager, available for all addresses
    }

    /**
     * Return the tax calculator associated to this address
     *
     * @return JeproshopTaxCalculator
     */
    public function getTaxCalculator(){
        static $tax_enabled = null;
        if (isset($this->tax_calculator)){
            return $this->tax_calculator;
        }

        if ($tax_enabled === null){
            $tax_enabled = JeproshopSettingModelSetting::getValue('use_tax');
        }

        if (!$tax_enabled){
            return new JeproshopTaxCalculator(array());
        }

        $taxes = array();
        $postcode = 0;
        if (!empty($this->address->postcode)){
            $postcode = $this->address->postcode;
        }

        $cache_id = (int)$this->address->country_id . '_' . (int)$this->address->state_id .'_' . $postcode . '_' . (int)$this->type;
        if (!JeproshopCache::isStored($cache_id)){
            $db = JFactory::getDBO();

            $query = "SELECT * FROM " . $db->quoteName('#__jeproshop_tax_rule') . " WHERE " . $db->quoteName('country_id') . " = " . (int)$this->address->country_id;
            $query .= " AND " . $db->quoteName('tax_rules_group_id') . " = " . (int)$this->type . " AND " . $db->quoteName('state_id') . " IN (0, " . (int)$this->address->state_id;
            $query .= ") AND ( " . $db->quote($db->escape($postcode)) . " BETWEEN " . $db->quoteName('zipcode_from') . " AND " . $db->quoteName('zipcode_to') . " OR (";
            $query .= $db->quote('zipcode_to') . " = 0 AND " . $db->quoteName('zipcode_from') . " IN(0, " . $db->quote($db->escape($postcode)) . "))) ORDER BY ";
            $query .= $db->quoteName('zipcode_from') . " DESC, " . $db->quoteName('zipcode_to') . " DESC, " . $db->quoteName('state_id') . " DESC, " . $db->quoteName('country_id') . " DESC" ;

            $db->setQuery($query);
            $results = $db->loadObjectList();

            $behavior = 0;
            $first_row = true;

            foreach ($results as $result){
                $tax = new JeproshopTaxModelTax((int)$result->tax_id);

                $taxes[] = $tax;

                // the applied behavior correspond to the most specific rules
                if ($first_row){
                    $behavior = $result->behavior;
                    $first_row = false;
                }

                if ($result->behavior == 0){ break; }
            }
            JeproshopCache::store($cache_id, new JeproshopTaxCalculator($taxes, $behavior));
        }
        return JeproshopCache::retrieve($cache_id);
    }
}


class JeproshopTaxCalculator
{
    const COMBINE_METHOD = 1;
    const ONE_AFTER_ANOTHER_METHOD = 2;

    public $taxes;

    public $computation_method;

    public function __construct(array $taxes = array(), $computation_method = JeproshopTaxCalculator::COMBINE_METHOD) {
        // sanity check
        foreach($taxes as $tax){
            if(!($tax instanceof JeproshopTaxModelTax)){
                JError::raiseError(500, JText::_('COM_JEPROSHOP_INVALID_TAX_OBJECT_MESSAGE'));
            }
        }
        $this->taxes = $taxes;
        $this->computation_method = (int)$computation_method;
    }

    public function getTotalRate(){
        $taxes = 0;
        if($this->computation_method == JeproshopTaxCalculator::ONE_AFTER_ANOTHER_METHOD){
            $taxes = 1;
            foreach($this->taxes as $tax){
                $taxes *= (1 + (abs($tax->rate) / 100));
            }
            $taxe = $taxes -1;
            $taxes = $taxe * 100;
        }else{
            foreach($this->taxes as $tax){
                $taxes += abs($tax->rate);
            }
        }
        return (float)$taxes;
    }

    /**
     * Compute and add the taxes to the specified price
     *
     * @param float $price_te price tax excluded
     * @return float price with taxes
     */
    public function addTaxes($price_te)	{
        return $price_te * (1 + ($this->getTotalRate() / 100));
    }

    public function getTaxesName() {
        $name = '';
        foreach ($this->taxes as $tax){
            $name .= $tax->name[(int)JeproshopContext::getContext()->language->lang_id].' - ';
        }
        $name = rtrim($name, ' - ');

        return $name;
    }
}


interface JeproshopTaxManagerInterface
{
    /**
     * This method determine if the tax manager is available for the specified address
     * @param JeproshopAddressModelAddress $address
     *
     * @return JeproshopTaxManager Description
     */
    public static function isAvailableForThisAddress(JeproshopAddressModelAddress $address);

    /**
     * Return the tax calculator associated to this address
     *
     * @return JeproshopTaxCalculator
     */
    public function getTaxCalculator();
}