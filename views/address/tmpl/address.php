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


$document = JFactory::getDocument();
$app = JFactory::getApplication();
$css_dir = JeproshopContext::getContext()->shop->theme_directory;
$document->addStyleSheet(JURI::base() .'components/com_jeproshop/assets/themes/' . $css_dir . '/css/jeproshop.css');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('jquery.framework');


?>
{capture name=path}<?php echo JText::_('COM_JEPROSHOP_YOUR_ADDRESSES_LABEL'); ?>{/capture}
<div class="form-wrapper">
    <h1 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_YOUR_ADDRESSES_LABEL'); ?></h1>
    <p class="info-title">
        <?php /*if(isset($this->address_id) && (isset($smarty.post.alias) || isset($this->address->alias))){
            echo JText::_('COM_JEPROSHOP_MODIFY_ADDRESS_LABEL');
            if (isset($smarty.post . alias)){
                echo '"' . $smarty . post . alias . '"';
            }else{
                if (isset($this->address->alias)) {
                    echo '"' . $this->address->alias . '"';
                }
            }
        }else{
            echo JText::_('COM_JEPROSHOP_TO_ADD_A_NEW_ADDRESS_PLEASE_FILL_OUT_THE_FORM_BELOW_LABEL');
        } */ ?>
    </p>
    {include file="$tpl_dir./errors.tpl"}
    <p class="required"><sup>*</sup><?php echo JText::_('COM_JEPROSHOP_REQUIRED_FIELD_LABEL'); ?></p>
    <form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=address', true, 1); ?>" method="post" class="std form-horizontal" id="add_address">
        <div class="panel" >
            <div class="panel-content well" >
                <!--h3 class="page-subheading"><?php if(isset($this->address_id)){ echo JText::_('COM_JEPROSHOP_YOUR_ADDRESS_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_NEW_ADDRESS_LABEL'); ?></h3-->
                <?php
                $stateExist = false;
                $postCodeExist = false;
                $dniExist = false;
                $homePhoneExist = false;
                $mobilePhoneExist = false;
                $atLeastOneExists = false;
                foreach($this->ordered_address_fields as $field_name){
                    if($field_name == 'company'){ ?>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_company"><?php echo JText::_('COM_JEPROSHOP_COMPANY_LABEL'); ?></label></div>
                    <div class="controls" ><input class="validate" data-validate="{$address_validation.$field_name.validate}" type="text" id="jform_company" name="jform[company]" value="{if isset($smarty.post.company)}{$smarty.post.company}{else}{if isset($address->company)}{$address->company|escape:'html':'UTF-8'}{/if}{/if}" /></div>
                </div>
                    <?php }
                    if($field_name == 'vat_number'){ ?>
                <div id="jform_vat_number_wrapper" class="vat-area control-group" >
                    <div class="control-label" ><label for="jform_vat_number" ><?php echo JText::_('COM_JEPROSHOP_VAT_NUMBER_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text" class="validate" data-validate="{$address_validation.$field_name.validate}" id="jform_vat_number" name="jform[vat_number]" value="{if isset($smarty.post.vat_number)}{$smarty.post.vat_number}{else}{if isset($address->vat_number)}{$address->vat_number|escape:'html':'UTF-8'}{/if}{/if}" /></div>
                </div>
                    <?php }
                    if($field_name == 'dni'){
                        $dniExist = true; ?>
                <div class="required control-group dni">
                    <div class="control-label" ><label for="jform_dni"><?php echo JText::_('COM_JEPROSHOP_IDENTIFICATION_NUMBER_LABEL'); ?> <sup>*</sup></label></div>
                    <div class="controls" >
                        <input data-validate="{$address_validation.$field_name.validate}" type="text" name="jform[dni]" id="jform_dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{else}{if isset($address->dni)}{$address->dni|escape:'html':'UTF-8'}{/if}{/if}" />
                        <span class="form_info"><?php echo JText::_('COM_JEPROSHOP_DNI_NIF_NIE_LABEL'); ?></span>
                    </div>
                </div>
                    <?php }
                    if($field_name == 'firstname'){ ?>
                <div class="required control-group">
                    <div class="control-label" ><label for="jform_firstname"><?php echo JText::_('COM_JEPROSHOP_FIRST_NAME_LABEL'); ?> <sup>*</sup></label></div>
                    <div class="controls" ><input class="is_required validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" name="jform[firstname]" id="jform_firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{else}{if isset($address->firstname)}{$address->firstname|escape:'html':'UTF-8'}{/if}{/if}" /></div>
                </div>
                    <?php }
                    if($field_name == 'lastname'){ ?>
                <div class="required control-group">
                    <div class="control-label" ><label for="jform_lastname"><?php echo JText::_('COM_JEPROSHOP_LAST_NAME_LABEL'); ?> <sup>*</sup></label></div>
                    <div class="controls" ><input class="is_required validate" data-validate="{$address_validation.$field_name.validate}" type="text" id="jform_lastname" name="jform[lastname]" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{else}{if isset($address->lastname)}{$address->lastname|escape:'html':'UTF-8'}{/if}{/if}" /></div>
                </div>
                    <?php }
                    if($field_name == 'address1'){ ?>
                <div class="required control-group">
                    <div class="control-label" ><label for="jform_address1"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?> <sup>*</sup></label></div>
                    <div class="controls" ><input class="is_required validate" data-validate="{$address_validation.$field_name.validate}" type="text" id="jform_address1" name="jorm[address1]" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{else}{if isset($address->address1)}{$address->address1|escape:'html':'UTF-8'}{/if}{/if}" /></div>
                </div>
                    <?php }
                    if($field_name == 'address2'){ ?>
                <div class="required control-group">
                    <div class="control-label" ><label for="jform_address2"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LINE_2_LABEL'); ?></label></div>
                    <div class="controls" ><input class="validate form-control" data-validate="{$address_validation.$field_name.validate}" type="text" id="jform_address2" name="jform[address2]" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{else}{if isset($address->address2)}{$address->address2|escape:'html':'UTF-8'}{/if}{/if}" /></div>
                </div>
                    <?php }
                    if($field_name == 'postcode'){
                        $postCodeExist = true; ?>
                <div class="required postcode control-group invisible">
                    <div class="control-label" ><label for="jform_postcode"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?> <sup>*</sup></label></div>
                    <div class="controls" ><input class="is_required validate " data-validate="{$address_validation.$field_name.validate}" type="text" id="jform_postcode" name="jform[postcode]" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{else}{if isset($address->postcode)}{$address->postcode|escape:'html':'UTF-8'}{/if}{/if}" /></div>
                </div>
                    <?php }
                    if($field_name == 'city') { ?>
                        <div class="required control-group">
                            <div class="control-label"><label for="jform_city"><?php echo JText::_('COM_JEPROSHOP_CITY_LABEL'); ?>
                                    <sup>*</sup></label></div>
                            <div class="controls"><input class="is_required validate"
                                                        data-validate="{$address_validation.$field_name.validate}"
                                                        type="text" name="jform[city]" id="jform_city"
                                                        value="<?php if(isset($this->city)){ echo $this->city; }else{ if(isset($this->address->city)){ echo $this->address->city; }} ?>"
                                                        maxlength="64"/></div>
                        </div>
                        <?php /** if customer hasn't update his layout address, country has to be verified but it's deprecated **/
                    }
                    if($field_name == 'Country:name' || $field_name == 'country'){ ?>
                <div class="required control-group">
                    <div class="control-label" ><label for="jform_country_id"><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?> <sup>*</sup></label></div>
                    <div class="controls" ><select id="jform_country_id"  name="jform[country_id]"><?php echo $this->countries_list; ?></select></div>
                </div>
                    <?php }
                    if($field_name == 'State:name'){
                        $stateExist = true; ?>
                <div class="required state_id control-group">
                    <div class="control-label" ><label for="jform_state_id"><?php echo JText::_('COM_JEPROSHOP_STATE_LABEL'); ?> <sup>*</sup></label></div>
                    <div class="controls" >
                        <select name="jform[state_id]" id="jform_state_id" >
                            <option value="">-</option>
                        </select>
                    </div>
                </div>
                    <?php }
                    if($field_name == 'phone'){
                        $homePhoneExist = true; ?>
                <div class="control-group phone-number">
                    <div class="control-label" ><label for="jform_phone"><?php echo JText::_('COM_JEPROSHOP_HOME_PHONE_LABEL'); if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?> <sup>**</sup><?php } ?></label></div>
                    <div class="controls" >
                        <input class="validate" <?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?> required="required" <?php } ?> data-validate="{$address_validation.phone.validate}" type="tel" id="jform_phone" name="jform[phone]" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{else}{if isset($address->phone)}{$address->phone|escape:'html':'UTF-8'}{/if}{/if}"  />
                        <?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){
                            $atLeastOneExists = true; ?>
                            <p class="inline-infos required">** <?php echo JText::_('COM_JEPROSHOP_YOU_MUST_REGISTER_AT_LEAST_ONE_PHONE_NUMBER_LABEL'); ?></p>
                        <?php } ?>
                    </div>
                </div>
                    <?php }
                    if($field_name == 'phone_mobile'){
                        $mobilePhoneExist = true; ?>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_phone_mobile"><?php echo JText::_('COM_JEPROSHOP_MOBILE_PHONE_LABEL'); if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?><sup>**</sup><?php } ?></label></div>
                    <div class="controls" ><input class="validate" <?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?> required="required" <?php } ?> data-validate="{$address_validation.phone_mobile.validate}" type="tel" id="jform_phone_mobile" name="jform[phone_mobile]" value="{if isset($smarty.post.phone_mobile)}{$smarty.post.phone_mobile}{else}{if isset($address->phone_mobile)}{$address->phone_mobile|escape:'html':'UTF-8'}{/if}{/if}" /></div>
                </div>
                <?php }
                }
                if(!$postCodeExist){ ?>
                <div class="required postcode form-group invisible" >
                    <label for="postcode"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CµODE_LABEL'); ?> <sup>*</sup></label>
                    <input class="is_required validate" data-validate="{$address_validation.postcode.validate}" type="text" id="postcode" name="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{else}{if isset($address->postcode)}{$address->postcode|escape:'html':'UTF-8'}{/if}{/if}" />
                </div>
                <?php }
                if(!$stateExist){ ?>
                <div class="required state_id control-group invisible">
                    <label for="jform_state_id"><?php echo JText::_('COM_JEPROSHOP_STATE_LABEL'); ?> <sup>*</sup></label>
                    <select name="id_state" id="jform_state_id">
                        <option value="">-</option>
                    </select>
                </div>
                <?php }
                if(!$dniExist){ ?>
            <div class="required dni control-group invisible">
                <div class="control-label" ><label for="jform_dni"><?php echo JText::_('COM_JEPROSHOP_IDENTIFICATION_NUMBER_LABEL'); ?> <sup>*</sup></label>
                <div class="controls" >
                    <input class="is_required form-control" data-validate="{$address_validation.dni.validate}" type="text" name="jform[dni]" id="jform_dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{else}{if isset($address->dni)}{$address->dni|escape:'html':'UTF-8'}{/if}{/if}" />
                    <span class="form_info"><?php echo JText::_('COM_JEPROSHOP_DNI_NIF_NIE_LABEL'); ?></span>
                </div>
            </div>
            <?php } ?>
            <div class="control-group">
                <div class="control-label" ><label for="jform_other"><?php echo JText::_('COM_JEPROSHOP_ADDITIONAL_INFORMATION_LABEL'); ?></label></div>
                <div class="controls" ><textarea class="validate" data-validate="{$address_validation.other.validate}" id="jform_other" name="jform[other]" cols="26" rows="3" >{if isset($smarty.post.other)}{$smarty.post.other}{else}{if isset($address->other)}{$address->other|escape:'html':'UTF-8'}{/if}{/if}</textarea></div>
            </div>
            <?php if(!$homePhoneExist){ ?>
            <div class="control-group phone-number">
                <div class="control-label" ><label for="jform_phone"><?php echo JText::_('COM_JEPROSHOP_HOME_PHONE_LABEL'); ?></label></div>
                <div class="controls" ><input class="{if isset($one_phone_at_least) && $one_phone_at_least}is_required{/if} validate form-control" data-validate="{$address_validation.phone.validate}" type="tel" id="jform_phone" name="jform[phone]" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{else}{if isset($address->phone)}{$address->phone|escape:'html':'UTF-8'}{/if}{/if}"  /></div>
            </div>
            <?php }
            if(isset($this->one_phone_at_least) && $this->one_phone_at_least && !$atLeastOneExists){ ?>
            <p class="inline-infos required"><?php echo JText::_('COM_JEPROSHOP_YOU_MUST_REGISTER_AT_LEAST_ONE_PHONE_NUMBER_LABEL'); ?></p>
            <?php } ?>
            <div class="clearfix"></div>
            <?php if(!$mobilePhoneExist){ ?>
            <div class="{if isset($one_phone_at_least) && $one_phone_at_least}required {/if}form-group">
                <label for="phone_mobile"><?php echo JText::_('COM_JEPROSHOP_MOBILE_PHONE_LABEL'); if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?> <sup>**</sup> <?php } ?></label>
                <input class="validate form-control" data-validate="{$address_validation.phone_mobile.validate}" type="tel" id="phone_mobile" name="phone_mobile" value="{if isset($smarty.post.phone_mobile)}{$smarty.post.phone_mobile}{else}{if isset($address->phone_mobile)}{$address->phone_mobile|escape:'html':'UTF-8'}{/if}{/if}" />
            </div>
            <?php } ?>
            <div class="required form-group" id="address_alias">
                <div class="control-label" ><label for="alias"><?php echo JText::_('COM_JEPROSHOP_PLEASE_ASSIGN_AN_ADDRESS_TITLE_FOR_FUTURE_REFERENCE_LABEL'); ?> <sup>*</sup></label></div>
                <input type="text" id="alias" class="is_required validate form-control" data-validate="{$address_validation.alias.validate}" name="alias" value="{if isset($smarty.post.alias)}{$smarty.post.alias}{else if isset($address->alias)}{$address->alias|escape:'html':'UTF-8'}{elseif !$select_address}<?php echo JText::_('COM_JEPROSHOP_MY_ADDRESS_LABEL'); } ?>" /></div>
            </div>
                <p class="submit2">
                    {if isset($id_address)}<input type="hidden" name="id_address" value="{$id_address|intval}" />{/if}
                    {if isset($back)}<input type="hidden" name="back" value="{$back}" />{/if}
                    {if isset($mod)}<input type="hidden" name="mod" value="{$mod}" />{/if}
                    {if isset($select_address)}<input type="hidden" name="select_address" value="{$select_address|intval}" />{/if}
                    <input type="hidden" name="token" value="{$token}" />
                    <button type="submit" name="submitAddress" id="submitAddress" class="btn btn-default button button-medium">
                        <span>
                            <?php echo JText::_('COM_JEPROSHOP_SAVE_LABEL'); ?>
                            <i class="icon-chevron-right right"></i>
                        </span>
                    </button>
                </p>
            </div>
        </div>
    </form>
</div>
<ul class="footer_links clearfix">
    <li>
        <a class="btn btn-default button button-small" href="{$link->getPageLink('addresses', true)|escape:'html':'UTF-8'}">
            <span><i class="icon-chevron-left"></i> <?php echo JText::_('COM_JEPROSHOP_BACK_TO_YOUR_ADDRESSES_LABEL'); ?></span>
        </a>
    </li>
</ul>
{strip}
{if isset($smarty.post.id_state) && $smarty.post.id_state}
{addJsDef idSelectedState=$smarty.post.id_state|intval}
{else if isset($address->id_state) && $address->id_state}
{addJsDef idSelectedState=$address->id_state|intval}
{else}
{addJsDef idSelectedState=false}
{/if}
{if isset($smarty.post.id_country) && $smarty.post.id_country}
{addJsDef idSelectedCountry=$smarty.post.id_country|intval}
{else if isset($address->id_country) && $address->id_country}
{addJsDef idSelectedCountry=$address->id_country|intval}
{else}
{addJsDef idSelectedCountry=false}
{/if}
{if isset($countries)}
{addJsDef countries=$countries}
{/if}
{if isset($vatnumber_ajax_call) && $vatnumber_ajax_call}
{addJsDef vatnumber_ajax_call=$vatnumber_ajax_call}
{/if}
{/strip}
