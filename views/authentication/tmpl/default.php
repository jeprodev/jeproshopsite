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

$availableData = JRequest::get('post');
$fieldData = isset($availableData['jform']) ? $availableData['jform'] : null;

$path = JURI::base() . 'components/com_jeproshop/assets/';
/*if (!$this->useMobileTheme()) {
    //$this->addCSS(_THEME_CSS_DIR_ . 'authentication.css');
} */
//$this->addJqueryPlugin('typewatch');
foreach(array('jeprovat.js', 'jeprostate.js', 'jeproauthentication.js',  'jeprotools.js') as $js){
    $document->addScript($path . 'javascript/script/' . $js);
}
$script = 'jQuery(document).ready(function(){jQuery("#create_account_form").JeproshopAuthentication({}); })';
$document->addScriptDeclaration($script);
?>
<div  id="jform_authentication" >
    <?php
    if(!isset($this->email_create)){
        //echo JText::_('COM_JEPROSHOP_AUTHENTICATION_LABEL');
    }else{ ?>
        <a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=authentication', true, 1); ?>" rel="nofollow" title="<?php echo JText::_('COM_JEPROSHOP_AUTHENTICATION_TITLE_DESC'); ?>" ><?php echo JText::_('COM_JEPROSHOP_AUTHENTICATION_LABEL'); ?></a>
        <span class="navigation-pipe">{$navigationPipe}</span><?php echo JText::_('COM_JEPROSHOP_CREATE_YOUR_ACCOUNT_LABEL');
    } ?>

    <h1 class="page-heading"><?php if(!isset($this->email_create)){ echo JText::_('COM_JEPROSHOP_AUTHENTICATION_LABEL'); }else{ echo JText::_('COM_JEPROSHOP_CREATE_YOUR_ACCOUNT_LABEL'); } ?></h1>
<?php
$stateExist = false;
$postCodeExist = false;
$dniExist = false;
if(!isset($this->email_create)){
    if(isset($authentication_error)){ ?>
        <div class="alert alert-danger">
            <?php if(count($authentication_error) == 1){ ?>
                <p><?php echo JText::_('COM_JEPROSHOP_THERE_IS_AT_LEAST_ONE_ERROR_LABEL'); ?> :</p>
            <?php }else{ ?>
                <p><?php echo JText::_('COM_JEPROSHOP_THERE_ARE_LABEL') . ' ' . count($this->account_error) . ' ' . JText::_('COM_JEPROSHOP_ERRORS_LABEL'); ?> :</p>
            <?php } ?>
            <ol>
                <?php foreach($authentication_error as $error){ ?><li>{<?php echo $error; ?></li><?php } ?>
            </ol>
        </div>
    <?php } ?>
<div class="form-wrapper" id="jform_checker_login_wrapper" >
    <div class="half-wrapper-left">
		<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=authentication', true, 1); ?>" method="post" id="create_account_form" class="form-horizontal">
			<div class="panel well">
                    <div class="panel-title"> <h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_CREATE_ACCOUNT_LABEL'); ?></h3></div>
				    <div class="panel-content clearfix" >
                        <p ><?php echo JText::_('COM_JEPROSHOP_PLEASE_ENTER_YOUR_EMAIL_ADDRESS_TO_CREATE_AN_ACCOUNT_LABEL'); ?></p>
                        <div class="alert alert-danger" id="jform_create_account_error" style="display:none"></div>
					    <div class="control-group" >
                            <div class="control-label"><label for="jform_email_create"><?php echo JText::_('COM_JEPROSHOP_EMAIL_ADDRESS_LABEL'); ?></label></div>
						    <div class="controls"><input type="text" class="validate account_input" data-validate="isEmail" id="jform_email_create" name="jform[email_create]" value="<?php if(isset($this->email_create)){ echo stripslashes($this->email_create); } ?>" required="required" /></div>
					    </div>
					    <div class="submit">
                            <?php if(isset($return)){ ?><input type="hidden" name="return" value="<?php echo $return; ?>" /><?php } ?>
                            <button class="btn btn-default button button-medium exclusive" type="submit" id="jform_create" name="create"  style="float:right; margin-right: 30px;">
                                <span>
                                    <i class="icon-user left"></i>
                                    <?php echo JText::_('COM_JEPROSHOP_CREATE_AN_ACCOUNT_LABEL'); ?>
                                </span>
                            </button>
                            <input type="hidden" class="hidden" name="SubmitCreate" value="<?php echo JText::_('COM_JEPROSHOP_CREATE_AN_ACCOUNT_LABEL'); ?>" />
                            <div style="clear: both;" ></div>
					    </div>

                    </div>
			</div>
            <input type="hidden" name="option" value="com_jeproshop" />
            <input type="hidden" name="task" value="create" />
            <?php echo JHtml::_('form.token'); ?>
		</form>
	</div>
	<div class="half-wrapper-right">
		<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=authentication', true, 1); ?>" method="post" id="login_form" class="form-horizontal">
                <div class="panel well" >
                    <div class="panel-title" ><h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_ALREADY_REGISTERED_LABEL'); ?> ? </h3></div>
				    <div class="panel-content clearfix">
                        <div class="control-group">
                            <div class="control-label" ><label for="jform_email"><?php echo JText::_('COM_JEPROSHOP_EMAIL_ADDRESS_LABEL'); ?></label></div>
                            <div class="controls"><input class="validate account_input" data-validate="isEmail" type="text" id="jform_email" name="jform[email]" value="<?php if(isset($this->email)){ echo stripslashes($this->email); } ?>" required="required" /></div>
                        </div>
                        <div class="control-group">
                            <div class="control-label" ><label for="jform_passwd"><?php echo JText::_('COM_JEPROSHOP_PASSWORD_LABEL'); ?></label></div>
                            <div class="controls"><input class="validate account_input" type="password" data-validate="isPasswd" id="jform_passwd" name="jform{passwd]" value="<?php if(isset($this->passwd)){ echo stripslashes($this->passwd); } ?>" required="required" /></div>
                        </div>
                        <div class="submit control-group">
                            <?php if(isset($return)){ ?><input type="hidden" name="return" value="<?php echo $return; ?>" /><?php } ?>
                            <span class="lost_password control-group" style="float:left; "><a href="<?php echo JRoute::_('index.php?option=com_jeproshop&view=authentication&task=resetpwd', true, 1); ?>" title="<?php echo JText::_('COM_JEPROSHOP_RECOVER_YOUR_FORGOTTEN_PASSWORD_LABEL'); ?>" rel="nofollow"><?php echo JText::_('COM_JEPROSHOP_FORGOT_YOUR_PASSWORD_LABEL'); ?></a></span>

                            <button type="submit" id="SubmitLogin" name="SubmitLogin" class="button btn btn-default button-medium pull-right" style="float:right; margin-right: 30px;">
                                <i class="icon-lock left" ></i> <?php echo JText::_('COM_JEPROSHOP_SIGN_IN_LABEL'); ?>
                            </button>
                        </div>
                    </div>
				</div>
		</form>
	</div>
    <div style="clear:both" ></div>
</div>
    <?php //if(isset($inOrderProcess) && $inOrderProcess && $PS_GUEST_CHECKOUT_ENABLED){ ?>
<form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=authentication', true, 1); ?>" method="post" id="new_account_form" class="std clearfix form-horizontal" >
    <div class="well" >
        <div class="panel">
            <div class="panel-title" ><h3 class="page-heading bottom-indent"><?php echo JText::_('COM_JEPROSHOP_INSTANT_CHECKOUT_LABEL'); ?></h3></div>
            <div class="panel-content" id="opc_account_form" style="display:none; " >
                <div class="control-group">
                    <div class="control-label" ><label for="jform_guest_email" ><?php echo JText::_('COM_JEPROSHOP_EMAIL_ADDRESS_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls" ><input type="text" class="validate" data-validate="isEmail" id="jform_guest_email" name="jform[guest_email]" value="<?php if(isset($this->guest_email)){ echo $this->guest_email; } ?>" required="required" /></div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label for="jform_title"><?php echo JText::_('COM_JEPROSHOP_TITLE_LABEL'); ?></label></div>
                    <div class="controls">
                        <select name="jform[customer_title]" id="jform_customer_title" >
                            <option value="mr" <?php if($this->context->customer->title == 'mr'){ ?>selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MR_LABEL'); ?></option>
                            <option value="mrs" <?php if($this->context->customer->title == 'mrs'){ ?>selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MRS_LABEL'); ?></option>
                            <option value="miss" <?php if($this->context->customer->title == 'miss'){ ?>selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MISS_LABEL'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="required control-group">
                    <div class="control-label" ><label for="jform_firstname"><?php echo JText::_('COM_JEPROSHOP_FIRST_NAME_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls" ><input type="text" class=" validate" data-validate="isName" id="jform_firstname" name="jform[firstname]" value="<?php if(isset($this->firstname)){ echo $this->firstname; } ?>" required="required" /></div>
                </div>
                <div class="required control-group">
                    <div class="control-label" ><label for="jform_lastname"><?php echo JText::_('COM_JEPROSHOP_LAST_NAME_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls"><input type="text" class="validate" data-validate="isName" id="jform_lastname" name="jform[lastname]" value="<?php if(isset($this->lastname)){ echo $this->lastname; }?>" required="required" /></div>
                </div>
                <div class="control-group date-select">
                    <div class="control-label" ><label><?php echo JText::_('COM_JEPROSHOP_DATE_OF_BIRTH_LABEL'); ?></label></div>
                    <div class="controls date-container"><label for="jform_days" ></label>
                        <select id="jform_days" name="days" >
                            <option value="1" <?php if($this->selected_day == 1){ ?> selected="selected"<?php } ?> >1&nbsp;&nbsp;</option>
                            <option value="2" <?php if($this->selected_day == 2){ ?> selected="selected"<?php } ?> >2&nbsp;&nbsp;</option>
                            <option value="3" <?php if($this->selected_day == 3){ ?> selected="selected"<?php } ?> >3&nbsp;&nbsp;</option>
                            <option value="4" <?php if($this->selected_day == 4){ ?> selected="selected"<?php } ?> >4&nbsp;&nbsp;</option>
                            <option value="5" <?php if($this->selected_day == 5){ ?> selected="selected"<?php } ?> >5&nbsp;&nbsp;</option>
                            <option value="6" <?php if($this->selected_day == 6){ ?> selected="selected"<?php } ?> >6&nbsp;&nbsp;</option>
                            <option value="7" <?php if($this->selected_day == 7){ ?> selected="selected"<?php } ?> >7&nbsp;&nbsp;</option>
                            <option value="8" <?php if($this->selected_day == 8){ ?> selected="selected"<?php } ?> >8&nbsp;&nbsp;</option>
                            <option value="9" <?php if($this->selected_day == 9){ ?> selected="selected"<?php } ?> >9&nbsp;&nbsp;</option>
                            <option value="10" <?php if($this->selected_day == 10){ ?> selected="selected"<?php } ?> >10&nbsp;&nbsp;</option>
                            <option value="11" <?php if($this->selected_day == 11){ ?> selected="selected"<?php } ?> >11&nbsp;&nbsp;</option>
                            <option value="12" <?php if($this->selected_day == 12){ ?> selected="selected"<?php } ?> >12&nbsp;&nbsp;</option>
                            <option value="13" <?php if($this->selected_day == 13){ ?> selected="selected"<?php } ?> >13&nbsp;&nbsp;</option>
                            <option value="14" <?php if($this->selected_day == 14){ ?> selected="selected"<?php } ?> >14&nbsp;&nbsp;</option>
                            <option value="15" <?php if($this->selected_day == 15){ ?> selected="selected"<?php } ?> >15&nbsp;&nbsp;</option>
                            <option value="16" <?php if($this->selected_day == 16){ ?> selected="selected"<?php } ?> >16&nbsp;&nbsp;</option>
                            <option value="17" <?php if($this->selected_day == 17){ ?> selected="selected"<?php } ?> >17&nbsp;&nbsp;</option>
                            <option value="18" <?php if($this->selected_day == 18){ ?> selected="selected"<?php } ?> >18&nbsp;&nbsp;</option>
                            <option value="19" <?php if($this->selected_day == 19){ ?> selected="selected"<?php } ?> >19&nbsp;&nbsp;</option>
                            <option value="20" <?php if($this->selected_day == 20){ ?> selected="selected"<?php } ?> >20&nbsp;&nbsp;</option>
                            <option value="21" <?php if($this->selected_day == 21){ ?> selected="selected"<?php } ?> >21&nbsp;&nbsp;</option>
                            <option value="22" <?php if($this->selected_day == 22){ ?> selected="selected"<?php } ?> >22&nbsp;&nbsp;</option>
                            <option value="23" <?php if($this->selected_day == 23){ ?> selected="selected"<?php } ?> >23&nbsp;&nbsp;</option>
                            <option value="24" <?php if($this->selected_day == 24){ ?> selected="selected"<?php } ?> >24&nbsp;&nbsp;</option>
                            <option value="25" <?php if($this->selected_day == 25){ ?> selected="selected"<?php } ?> >25&nbsp;&nbsp;</option>
                            <option value="26" <?php if($this->selected_day == 26){ ?> selected="selected"<?php } ?> >26&nbsp;&nbsp;</option>
                            <option value="27" <?php if($this->selected_day == 27){ ?> selected="selected"<?php } ?> >27&nbsp;&nbsp;</option>
                            <option value="28" <?php if($this->selected_day == 28){ ?> selected="selected"<?php } ?> >28&nbsp;&nbsp;</option>
                            <option value="29" <?php if($this->selected_day == 29){ ?> selected="selected"<?php } ?> >29&nbsp;&nbsp;</option>
                            <option value="30" <?php if($this->selected_day == 30){ ?> selected="selected"<?php } ?> >30&nbsp;&nbsp;</option>
                            <option value="31" <?php if($this->selected_day == 31){ ?> selected="selected"<?php } ?> >31&nbsp;&nbsp;</option>
                        </select>&nbsp;/&nbsp;<label for="jform_months" ></label>
                        <select id="jform_months" name="months" >
                            <option value="1" <?php if($this->selected_month == 1){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_JANUARY_LABEL'); ?></option>
                            <option value="2" <?php if($this->selected_month == 2){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_FEBRUARY_LABEL'); ?></option>
                            <option value="3" <?php if($this->selected_month == 3){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MARCH_LABEL'); ?></option>
                            <option value="4" <?php if($this->selected_month == 4){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_APRIL_LABEL'); ?></option>
                            <option value="5" <?php if($this->selected_month == 5){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MAY_LABEL'); ?></option>
                            <option value="6" <?php if($this->selected_month == 6){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_JUNE_LABEL'); ?></option>
                            <option value="7" <?php if($this->selected_month == 7){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_JULY_LABEL'); ?></option>
                            <option value="8" <?php if($this->selected_month == 8){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_AUGUST_LABEL'); ?></option>
                            <option value="9" <?php if($this->selected_month == 9){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_SEPTEMBER_LABEL'); ?></option>
                            <option value="10" <?php if($this->selected_month == 10){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_OCTOBER_LABEL'); ?></option>
                            <option value="11" <?php if($this->selected_month == 11){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_NOVEMBER_LABEL'); ?></option>
                            <option value="12" <?php if($this->selected_month == 12){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_DECEMBER_LABEL'); ?></option>
                        </select>&nbsp;/&nbsp;<label for="jform_years" ></label>
                        <select id="jform_years" name="years" >
                            <option value="">-</option>
                            <?php foreach($this->years as $year){ ?>
                                <option value="<?php echo $year; ?>" <?php if($this->selected_year == $year){ ?>selected="selected"<?php } ?>><?php echo $year; ?>&nbsp;&nbsp;</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <?php if(isset($this->news_letter) && $this->news_letter){ ?>
                    <div class="control-group checkbox">
                        <div class="controls" ><label for="jform_newsletter" >
                                <input type="checkbox" name="jform[newsletter]" id="jform_newsletter" value="1" <?php if(isset($this->newsletter) && $this->newsletter == '1'){ ?>checked="checked"<?php } ?> />
                                <?php echo JText::_('COM_JEPROSHOP_SIGN_UP_FOR_OUR_NEWS_LETTER_LABEL'); ?></label>
                        </div>
                    </div>
                    <div class="control-group checkbox">
                        <div class="controls" >
                            <label for="jform_optin">
                                <input type="checkbox" name="jform[optin]" id="jform_optin" value="1" <?php if(isset($this->optin) && $this->optin == '1'){ ?>checked="checked"<?php } ?> />
                                <?php echo JText::_('COM_JEPROSHOP_RECEIVE_SPECIAL_OFFERS_FROM_OUR_PARTNERS_LABEL'); ?></label>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="panel" >
            <div class="panel-title" ><h3 class="page-heading bottom-indent top-indent"><?php echo JText::_('COM_JEPROSHOP_DELIVERY_ADDRESS_LABEL'); ?></h3></div>
            <div class="panel-content form-horizontal" >
    <?php /*foreach($this->delivery_all_fields as $field_name) {
        if ($field_name == "company" && $this->enable_b2b_mode) { */?>
            <div class="control-group">
                <div class="control-label"><label
                        for="jform_company"><?php echo JText::_('COM_JEPROSHOP_COMPANY_LABEL'); ?></label></div>
                <div class="controls"><input type="text" id="jform_company" name="jform[company]"
                                             value="<?php if (isset($this->company)){
                                                 echo $this->company;
                                             } ?>"/>
                </div>
            </div>
        <?php //} elseif ($field_name == "vat_number") { ?>
            <div id="vat_number" style="display:none;" class="control-group">
                <div class="control-label"><label for="jform_vat_number"><?php echo JText::_('COM_JEPROSHOP_VAT_NUMBER_LABEL'); ?></label></div>
                <div class="controls">
                    <input id="jform_vat_number" type="text" name="jform[vat_number]"
                           value="<?php if(isset($this->vat_number)){ echo $this->vat_number; } ?>" />
                </div>
            </div>
        <?php //}elseif($field_name == "dni"){
            $dniExist = true; ?>
            <div class="required dni control-group">
                <div class="control-label"><label for="jform_dni"><?php echo JText::_('COM_JEPROSHOP_IDENTIFICATION_NUMBER_LABEL'); ?>
                        <span>*</span></label></div>
                <div class="controls">
                    <input type="text" name="jform[dni]" id="jform_dni"
                           value="<?php if(isset($this->dni)){ echo $this->dni; } ?>"/>
                    <p class="form_info"><?php echo JText::_('COM_JEPROSHOP_DNI_NIF_NIE_LABEL'); ?></p>
                </div>
            </div>
        <?php //}elseif($field_name == "address1"){ ?>
            <div class="required control-group">
                <div class="control-label"><label for="jform_address1"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?> <span>*</span></label>
                </div>
                <div class="controls"><input type="text" name="jform[address1]" id="jform_address1"
                                             value="<?php if(isset($this->address1)){ echo $this->address1; } ?>"/>
                </div>
            </div>
        <?php //}elseif($field_name == "address2"){ ?>
            <div class="control-group is_customer_param">
                <div class="control-label"><label
                        for="jform_address2"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LINE_2_LABEL'); ?> <span>*</span></label>
                </div>
                <div class="controls">
                    <input type="text" name="jform[address2]" id="jform_address2"  value="<?php if(isset($this->address2)){ echo $this->address2; } ?>" />
                </div>
            </div>
        <?php //}elseif($field_name == "postcode"){
            $postCodeExist = true; ?>
            <div class="required postcode control-group">
                <div class="control-label"><label
                        for="jform_postcode"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?>
                        <span>*</span></label></div>
                <div class="controls">
                    <input type="text" name="jform[postcode]" id="jform_postcode"
                           value="<?php if(isset($this->postcode)){ echo $this->postcode; } ?>"
                           onkeyup=" var postCode = $('#jform_postcode'); postCode.val(postCode.val().toUpperCase());"/>
                </div>
            </div>
        <?php //}elseif($field_name == "city"){ ?>
            <div class="required control-group">
                <div class="control-label"><label
                        for="jform_city"><?php echo JText::_('COM_JEPROSHOP_CITY_LABEL'); ?>
                        <span>*</span></label></div>
                <div class="controls"><input type="text" name="jform[city]" id="jform_city"
                                             value="<?php if(isset($this->city)){ echo $this->city; } ?>" />
                </div>
            </div>
            <!-- if customer hasn't update his layout address, country has to be verified but it's deprecated -->
        <?php //}elseif($field_name == "Country:name" || $field_name == "country"){ ?>
            <div class="required select control-group">
                <div class="control-label"><label for="jform_zone_id" ><?php echo JText::_('COM_JEPROSHOP_GEOGRAPHICAL_ZONE_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls">
                        <select name="jform[zone_id]" id="jform_zone_id">
                            <?php foreach ($this->zones as $zone) { ?>
                                <option value="<?php echo $zone->zone_id; ?>" <?php if((isset($this->zone_id) AND  $this->zone_id == $zone->zone_id) OR (!isset($this->zone_id) && $this->selected_country == $zone->zone_id)){ ?> selected="selected"<?php } ?>><?php echo $zone->name; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            <div class="required select control-group">
                <div class="control-label"><label
                        for="jform_country_id" ><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?>
                        <span>*</span></label></div>
                <div class="controls">
                    <select name="jform[country_id]" id="jform_country_id">
                        <?php foreach ($this->countries as $country) { ?>
                            <option value="<?php echo $country->country_id; ?>" <?php if((isset($this->country_id) AND  $this->country_id == $country->country_id) OR (!isset($this->country_id) && $this->selected_country == $country->country_id)){ ?> selected="selected"<?php } ?>><?php echo $country->name; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php //}elseif($field_name == "State:name"){
            $stateExist =true; ?>
            <div class="required state_id select control-group">
                <div class="control-label"><label
                        for="jform_state_id"><?php echo JText::_('COM_JEPROSHOP_STATE_LABEL'); ?>
                        <span>*</span></label></div>
                <div class="controls">
                    <select name="jform[state_id]" id="jform_state_id">
                        <option value="">-</option>
                    </select>
                </div>
            </div>
        <?php //}
    //}
    if($stateExist == false){ ?>
        <div class="required id_state select invisible control-group">
            <div class="control-label" ><label for="jform_state_id"><?php echo JText::_('COM_JEPROSHOP_STATE_LABEL'); ?> <span>*</span></label></div>
            <div class="controls" >
                <select name="jform[state_id]" id="jform_state_id"><option value="">-</option></select>
            </div>
        </div>
    <?php }
    if($postCodeExist == false){ ?>
        <div class="required postcode invisible control-group">
            <div class="control-label" ><label for="jform_postcode"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?> <span>*</span></label></div>
            <div class="controls" ><input type="text" name="jform[postcode]" id="jform_postcode" value="<?php if(isset($this->postcode)){ echo $this->postcode; } ?>" onkeyup=" var postCode = $('#jform_postcode'); postCode.val(postCode.val().toUpperCase());" /></div>
        </div>
    <?php }
    if($dniExist == false){ ?>
        <div class="required control-group dni_invoice">
            <div class="control-label" ><label for="jform_invoice_dni"><?php echo JText::_('COM_JEPROSHOP_IDENTIFICATION_NUMBER_LABEL'); ?> <span>*</span></label></div>
            <div class="controls" >
                <input type="text" class="text form-control" name="jform[invoice_dni]" id="jform_invoice_dni" value="<?php if(isset($guestInformations) && $guestInformations->dni_invoice){ echo $guestInformations->dni_invoice; } ?>" />
                <p class="form_info"><?php echo JText::_('COM_JEPROSHOP_DNI_NIF_NIE_LABEL'); ?></p>
            </div>
        </div>
    <?php } ?>
                <div class="<?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?>required <?php } ?> control-group">
                    <div class="control-label" ><label for="jform_phone_mobile"><?php echo JText::_('COM_JEPROSHOP_MOBILE_PHONE_LABEL'); ?><?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?> <span>*</span><?php } ?></label></div>
                    <div class="controls" ><input type="text"  name="jform[phone_mobile]" id="jform_phone_mobile" value="<?php if(isset($this->phone_mobile)){ echo $this->phone_mobile; } ?>" /></div>
                </div>
                <input type="hidden" name="alias" id="alias" value="<?php echo JText::_('COM_JEPROSHOP_MY_ADDRESS_LABEL'); ?>" />
                <input type="hidden" name="is_new_customer" id="is_new_customer" value="0" />
                <div class="checkbox control-group">
                    <div class="controls" >
                        <label for="jform_invoice_address">
                            <input type="checkbox" name="jform[invoice_address]" id="jform_invoice_address" <?php if((isset($this->invoice_address) && $this->invoice_address) || (isset($guestInformations) && $guestInformations->invoice_address)){ ?> checked="checked"<?php } ?>  autocomplete="off"/>
                            <?php echo JText::_('COM_JEPROSHOP_PLEASE_USE_ANOTHER_ADDRESS_FOR_INVOICE_LABEL'); ?></label>
                    </div>
                </div>
            </div>
        </div>
        <div id="opc_invoice_address"  class="invisible panel">
            <?php $stateExist = false;
            $postCodeExist = false; ?>
            <div class="panel-title" ><h3 class="page-subheading top-indent"><?php echo JText::_('COM_JEPROSHOP_INVOICE_ADDRESS_LABEL'); ?></h3></div>
            <div class="panel-content" >
                <?php //foreach($this->invoice_all_fields as $field_name){
                    //if($field_name == "company" && $this->enable_b2b_mode){ ?>
                        <div class="control-group">
                            <div class="control-label" ><label for="jform_company_invoice"><?php echo JText::_('COM_JEPROSHOP_COMPANY_LABEL'); ?></label></div>
                            <div class="controls"><input type="text"  id="jform_company_invoice" name="jform[company_invoice]" value="" /></div>
                        </div>
                    <?php //}elseif($field_name == "vat_number"){  ?>
                        <div id="vat_number_block_invoice" class="is_customer_param control-group" style="display:none;">
                            <div class="control-label" ><label for="vat_number_invoice"><?php echo JText::_('COM_JEPROSHOP_VAT_NUMBER_LABEL'); ?></label></div>
                            <div class="controls"><input type="text"  id="vat_number_invoice" name="vat_number_invoice" value="" /></div>
                        </div>
                    <?php //}elseif($field_name == "dni"){
                        $dniExist =true; ?>
                        <div class="required control-group dni_invoice">
                            <div class="control-label" ><label for="jform_invoice_dni"><?php echo JText::_('COM_JEPROSHOP_IDENTIFICATION_NUMBER_LABEL'); ?> <span>*</span></label></div>
                            <div class="controls">
                                <input type="text" class="text" name="jform[invoice_dni]" id="jform_invoice_dni" value="<?php if(isset($guestInformations) && $guestInformations->dni_invoice){ echo $guestInformations->dni_invoice; }?>" />
                                <p class="form_info"><?php echo JText::_('COM_JEPROSHOP_DNI_NIF_NIE_LABEL'); ?></p>
                            </div>
                        </div>
                    <?php //}elseif($field_name == "firstname"){ ?>
                        <div class="required control-group">
                            <div class="control-label" ><label for="jform_firstname_invoice"><?php echo JText::_('COM_JEPROSHOP_FIRST_NAME_LABEL'); ?> <span>*</span></label></div>
                            <div class="controls"><input type="text"  id="jform_firstname_invoice" name="jform[firstname_invoice]" value="<?php if(isset($guestInformations) && $guestInformations->firstname_invoice){ echo $guestInformations->firstname_invoice; } ?>" /></div>
                        </div>
                    <?php //}elseif($field_name == "lastname"){ ?>
                        <div class="required control-group">
                            <div class="control-label" ><label for="lastname_invoice"><?php echo JText::_('COM_JEPROSHOP_LAST_NAME_LABEL'); ?> <span>*</span></label></div>
                            <div class="controls"><input type="text"  id="lastname_invoice" name="jform[lastname_invoice]" value="<?php if(isset($guestInformations) && $guestInformations->lastname_invoice){ echo $guestInformations->lastname_invoice; } ?>" /></div>
                        </div>
                    <?php //}elseif($field_name == "address1"){ ?>
                        <div class="required control-group">
                            <div class="control-label" ><label for="jform_address1_invoice"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?> <span>*</span></label></div>
                            <div class="controls"><input type="text"  id="jform_address1_invoice" name="jform[address1_invoice]" value="<?php if(isset($guestInformations) && $guestInformations->address1_invoice){$guestInformations->address1_invoice; } ?> " /></div>
                        </div>
                    <?php //}elseif($field_name == "address2"){ ?>
                        <div class="control-group is_customer_param">
                            <div class="control-label" ><label for="address2_invoice"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LINE_2_LABEL'); ?></label></div>
                            <div class="controls"><input type="text"  name="address2_invoice" id="address2_invoice" value="<?php if(isset($guestInformations) && $guestInformations->address2_invoice){$guestInformations->address2_invoice; } ?>" /></div>
                        </div>
                    <?php //}elseif($field_name == "postcode"){
                        //$postCodeExist = true; ?>
                        <div class="required postcode_invoice control-group">
                            <div class="control-label" ><label for="jform_postcode_invoice"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?> <span>*</span></label></div>
                            <div class="controls"><input type="text"  name="postcode_invoice" id="jform_postcode_invoice" value="<?php if(isset($guestInformations) && $guestInformations->postcode_invoice){ echo $guestInformations->postcode_invoice; } ?>" onkeyup="var invoicePostCode = $('#postcode_invoice'); invoicePostCode.val(invoicePostCode.val().toUpperCase());" /></div>
                        </div>
                    <?php //}elseif($field_name == "city"){ ?>
                        <div class="required control-group">
                            <div class="control-label" ><label for="jform_invoice_city"><?php echo JText::_('COM_JEPROSHOP_CITY_LABEL'); ?> <span>*</span></label></div>
                            <div class="controls"><input type="text"  name="jform[invoice_city]" id="jform_invoice_city" value="<?php if(isset($guestInformations) && $guestInformations->city_invoice){$guestInformations->city_invoice; } ?>" /></div>
                        </div>
                    <?php //}elseif($field_name == "country" || $field_name == "Country:name"){ ?>
                        <div class="required control-group">
                            <div class="control-label" ><label for="jform_invoice_country_id"><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?> <span>*</span></label></div>
                            <div class="controls">
                                <select name="jform[invoice_country_id]" id="jform_invoice_country_id" >
                                    <option value="">-</option>
                                    <?php foreach($this->countries as $country){ ?>
                                        <option value="<?php echo $country->country_id; ?>"<?php if(isset($guestInformations) AND $guestInformations->id_country_invoice == $country->country_id OR (!isset($guestInformations) && $this->selected_country_id == $country->country_id)){ ?> selected="selected"<?php } ?>><?php echo ucfirst($country->name); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php //}elseif($field_name ==  "state" || $field_name == 'State:name'){
                        $stateExist = true; ?>
                        <div class="required id_state_invoice control-group" style="display:none;">
                            <div class="control-label" ><label for="id_state_invoice"><?php echo JText::_('COM_JEPROSHOP_STATE_LABEL'); ?> <span>*</span></label></div>
                            <div class="controls">
                                <select name="id_state_invoice" id="id_state_invoice" >
                                    <option value="">-</option>
                                </select>
                            </div>
                        </div>
                    <?php // }
                //}
                if(!$postCodeExist){ ?>
                    <div class="required postcode_invoice control-group invisible">
                        <div class="control-label" ><label for="jform_postcode_invoice"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?> <span>*</span></label></div>
                        <div class="controls"><input type="text"  name="jform[postcode_invoice]" id="jform_postcode_invoice" value="" onkeyup="$('#jform_postcode_ivoice').val($('#jform_postcode_invoice').val().toUpperCase());" /></div>
                    </div>
                <?php }
                if(!$stateExist){ ?>
                    <div class="required id_state_invoice control-group invisible">
                        <div class="control-label" ><label for="id_state_invoice"><?php echo JText::_('COM_JEPROSHOP_STATE_LABEL'); ?> <span>*</span></label></div>
                        <div class="controls">
                            <select name="jform[invoice_state_id]" id="id_state_invoice" >
                                <option value="">-</option>
                            </select>
                        </div>
                    </div>
                <?php } ?>
                <div class="control-group is_customer_param">
                    <div class="control-label" ><label for="jform_other_invoice"><?php echo JText::_('COM_JEPROSHOP_ADDITIONAL_INFORMATION_LABEL'); ?></label></div>
                    <div class="controls"><textarea  name="jform[other_invoice]" id="jform_other_invoice" cols="26" rows="3"></textarea></div>
                </div>
                <?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?>
                    <div class="control-group" >
                        <div class="controls">
                    <p class="inline-infos required is_customer_param"><?php echo JText::_('COM_JEPROSHOP_YOU_MUST_REGISTER_AT_LEAST_ONE_PHONE_NUMBER_LABEL'); ?></p></div></div>
                <?php } ?>
                <div class="control-group is_customer_param">
                    <div class="control-label" ><label for="phone_invoice"><?php echo JText::_('COM_JEPROSHOP_HOME_PHONE_LABEL'); ?></label></div>
                    <div class="controls"><input type="text"  name="phone_invoice" id="phone_invoice" value="<?php if(isset($guestInformations) && $guestInformations->phone_invoice){ echo $guestInformations->phone_invoice; } ?>" /></div>
                </div>
                <div class="<?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?>required <?php } ?>control-group">
                    <div class="control-label" ><label for="jform_phone_mobile_invoice"><?php echo JText::_('COM_JEPROSHOP_MOBILE_PHONE_LABEL'); ?><?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?> <span>*</span><?php } ?></label></div>
                    <div class="controls"><input type="text"  name="jform[phone_mobile_invoice]" id="jform_phone_mobile_invoice" value="<?php if(isset($guestInformations) && $guestInformations->phone_mobile_invoice){ echo $guestInformations->phone_mobile_invoice; } ?>" /></div>
                </div>
                <input type="hidden" name="jform[alias_invoice]" id="jform_alias_invoice" value="<?php echo JText::_('COM_JEPROSHOP_MY_INVOICE_ADDRESS_LABEL'); ?>" />
            </div>
        </div>
        <?php if(isset($return)){ ?><input type="hidden" name="return" value="<?php echo $return; ?>" /><?php } ?>
        <!-- END Account -->
        {$HOOK_CREATE_ACCOUNT_FORM}
        <p class="cart_navigation required submit clearfix">
            <span><span>*</span>&nbsp;<?php echo JText::_('COM_JEPROSHOP_REQUIRED_FIELD_LABEL'); ?></span>
            <input type="hidden" name="display_guest_checkout" value="1" />
            <button type="submit" class="button btn btn-default button-medium" name="submitGuestAccount" id="submitGuestAccount">
			    <span>
				    <?php echo JText::_('COM_JEPROSHOP_PROCEED_TO_CHECKOUT_LABEL'); ?> <i class="icon-chevron-right right"></i>
                </span>
            </button>
        </p>
    </div>
</form>
    <?php //}else{ ?>
    <form action="<?php echo JRoute::_('index.php?option=com_jeproshop&view=authentication', true, 1); ?>" method="post" id="jform_account_creation_form" class="std box" style="display: none;">
        <div class="well" >
            {$HOOK_CREATE_ACCOUNT_TOP}
            <div class="account_creation form-horizontal">
                <h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_YOUR_PERSONAL_INFORMATION_LABEL'); ?></h3>
                <div class="clearfix control-group">
                    <div class="control-label"><label for="jform_customer_title"><?php echo JText::_('COM_JEPROSHOP_TITLE_LABEL'); ?></label></div>
                    <div class="controls">
                        <select name="jform[customer_title]" id="jform_customer_title" >
                            <option value="mr" <?php if($this->context->customer->title == 'mr'){ ?>selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MR_LABEL'); ?></option>
                            <option value="mrs" <?php if($this->context->customer->title == 'mrs'){ ?>selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MRS_LABEL'); ?></option>
                            <option value="miss" <?php if($this->context->customer->title == 'miss'){ ?>selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MISS_LABEL'); ?></option>
                        </select>
                    </div>
                </div>
                <div class="required control-group">
                    <div class="control-label"><label for="jform_customer_firstname"><?php echo JText::_('COM_JEPROSHOP_FIRST_NAME_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls"><input onkeyup="$('#jform_customer_firstname').val(this.value);" type="text" data-validate="isName" id="jform_customer_firstname" name="jform[customer_firstname]" value="<?php if(isset($this->customer_firstname)){ echo $this->customer_firstname; } ?>" /></div>
                </div>
                <div class="required control-group">
                    <div class="control-label"><label for="jform_customer_lastname" ><?php echo JText::_('COM_JEPROSHOP_LAST_NAME_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls"><input onkeyup="$('#jform_customer_lastname').val(this.value);" type="text" data-validate="isName" id="jform_customer_lastname" name="jform[customer_lastname]" value="<?php if(isset($this->customer_lastname)){$this->customer_lastname; } ?>" /></div>
                </div>
                <div class="required control-group">
                    <div class="control-label"><label for="jform_customer_email"><?php echo JText::_('COM_JEPROSHOP_EMAIL_ADDRESS_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls"><input type="text" data-validate="isEmail" id="jform_customer_email" name="jform[customer_email]" value="<?php if(isset($this->email)){$this->email; } ?>" /></div>
                </div>
                <div class="required password control-group">
                    <div class="control-label"><label for="jform_customer_passwd"><?php echo JText::_('COM_JEPROSHOP_PASSWORD_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls">
                        <input type="password" required="required" data-validate="isPasswd" name="jform[customer_passwd]" id="jform_customer_passwd" />
                        <p class="form_info"><?php echo JText::_('COM_JEPROSHOP_HEIGHT_CHARACTERS_MINIMUM_LABEL'); ?></>
                    </div>
                </div>
                <div class="control-group">
                    <div class="control-label"><label ><?php echo JText::_('COM_JEPROSHOP_DATE_OF_BIRTH_LABEL'); ?></label></div>
                    <div class="controls date-container"">
                        <select id="jform_customer_days" name="jform[customer_days]" >
                            <option value="1" <?php if($this->selected_day == 1){ ?> selected="selected" <?php } ?> >1&nbsp;&nbsp;</option>
                            <option value="2" <?php if($this->selected_day == 2){ ?> selected="selected" <?php } ?> >2&nbsp;&nbsp;</option>
                            <option value="3" <?php if($this->selected_day == 3){ ?> selected="selected" <?php } ?> >3&nbsp;&nbsp;</option>
                            <option value="4" <?php if($this->selected_day == 4){ ?> selected="selected" <?php } ?> >4&nbsp;&nbsp;</option>
                            <option value="5" <?php if($this->selected_day == 5){ ?> selected="selected" <?php } ?> >5&nbsp;&nbsp;</option>
                            <option value="6" <?php if($this->selected_day == 6){ ?> selected="selected" <?php } ?> >6&nbsp;&nbsp;</option>
                            <option value="7" <?php if($this->selected_day == 7){ ?> selected="selected" <?php } ?> >7&nbsp;&nbsp;</option>
                            <option value="8" <?php if($this->selected_day == 8){ ?> selected="selected" <?php } ?> >8&nbsp;&nbsp;</option>
                            <option value="9" <?php if($this->selected_day == 9){ ?> selected="selected" <?php } ?> >9&nbsp;&nbsp;</option>
                            <option value="10" <?php if($this->selected_day == 10){ ?> selected="selected" <?php } ?> >10&nbsp;&nbsp;</option>
                            <option value="11" <?php if($this->selected_day == 11){ ?> selected="selected" <?php } ?> >11&nbsp;&nbsp;</option>
                            <option value="12" <?php if($this->selected_day == 1){ ?> selected="selected" <?php } ?> >12&nbsp;&nbsp;</option>
                            <option value="13" <?php if($this->selected_day == 1){ ?> selected="selected" <?php } ?> >13&nbsp;&nbsp;</option>
                            <option value="14" <?php if($this->selected_day == 1){ ?> selected="selected" <?php } ?> >14&nbsp;&nbsp;</option>
                            <option value="15" <?php if($this->selected_day == 1){ ?> selected="selected" <?php } ?> >15&nbsp;&nbsp;</option>
                            <option value="16" <?php if($this->selected_day == 1){ ?> selected="selected" <?php } ?> >16&nbsp;&nbsp;</option>
                            <option value="17" <?php if($this->selected_day == 1){ ?> selected="selected" <?php } ?> >17&nbsp;&nbsp;</option>
                            <option value="18" <?php if($this->selected_day == 18){ ?> selected="selected" <?php } ?> >18&nbsp;&nbsp;</option>
                            <option value="19" <?php if($this->selected_day == 19){ ?> selected="selected" <?php } ?> >19&nbsp;&nbsp;</option>
                            <option value="20" <?php if($this->selected_day == 20){ ?> selected="selected" <?php } ?> >20&nbsp;&nbsp;</option>
                            <option value="21" <?php if($this->selected_day == 21){ ?> selected="selected" <?php } ?> >21&nbsp;&nbsp;</option>
                            <option value="22" <?php if($this->selected_day == 22){ ?> selected="selected" <?php } ?> >22&nbsp;&nbsp;</option>
                            <option value="23" <?php if($this->selected_day == 23){ ?> selected="selected" <?php } ?> >23&nbsp;&nbsp;</option>
                            <option value="24" <?php if($this->selected_day == 24){ ?> selected="selected" <?php } ?> >24&nbsp;&nbsp;</option>
                            <option value="25" <?php if($this->selected_day == 25){ ?> selected="selected" <?php } ?> >25&nbsp;&nbsp;</option>
                            <option value="26" <?php if($this->selected_day == 26){ ?> selected="selected" <?php } ?> >26&nbsp;&nbsp;</option>
                            <option value="27" <?php if($this->selected_day == 27){ ?> selected="selected" <?php } ?> >27&nbsp;&nbsp;</option>
                            <option value="28" <?php if($this->selected_day == 28){ ?> selected="selected" <?php } ?> >28&nbsp;&nbsp;</option>
                            <option value="29" <?php if($this->selected_day == 29){ ?> selected="selected" <?php } ?> >29&nbsp;&nbsp;</option>
                            <option value="30" <?php if($this->selected_day == 30){ ?> selected="selected" <?php } ?> >30&nbsp;&nbsp;</option>
                            <option value="31" <?php if($this->selected_day == 31){ ?> selected="selected" <?php } ?> >31&nbsp;&nbsp;</option>
                        </select>&nbsp;/&nbsp;
                        <select id="jform_customer_months" name="jform[customer_months]" >
                            <option value="1" <?php if($this->selected_month == 1){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_JANUARY_LABEL'); ?></option>
                            <option value="2" <?php if($this->selected_month == 2){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_FEBRUARY_LABEL'); ?></option>
                            <option value="3" <?php if($this->selected_month == 3){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MARCH_LABEL'); ?></option>
                            <option value="4" <?php if($this->selected_month == 4){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_APRIL_LABEL'); ?></option>
                            <option value="5" <?php if($this->selected_month == 5){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_MAY_LABEL'); ?></option>
                            <option value="6" <?php if($this->selected_month == 6){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_JUNE_LABEL'); ?></option>
                            <option value="7" <?php if($this->selected_month == 7){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_JULY_LABEL'); ?></option>
                            <option value="8" <?php if($this->selected_month == 8){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_AUGUST_LABEL'); ?></option>
                            <option value="9" <?php if($this->selected_month == 9){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_SEPTEMBER_LABEL'); ?></option>
                            <option value="10" <?php if($this->selected_month == 10){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_OCTOBER_LABEL'); ?></option>
                            <option value="11" <?php if($this->selected_month == 11){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_NOVEMBER_LABEL'); ?></option>
                            <option value="12" <?php if($this->selected_month == 12){ ?> selected="selected" <?php } ?> ><?php echo JText::_('COM_JEPROSHOP_DECEMBER_LABEL'); ?></option>
                        </select>&nbsp;/&nbsp;
                        <select id="jform_customer_years" name="jform[customer_years]" >
                            <option value="">-</option>
                            <?php foreach($this->years as $year){ ?>
                                <option value="<?php echo $year; ?>" <?php if($this->selected_year == $year){ ?> selected="selected"<?php } ?> ><?php echo $year; ?>&nbsp;&nbsp;</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            <?php if($this->news_letter){ ?>
                <div class="checkbox control-group">
                    <div class="controls" >
                        <input type="checkbox" name="jform[customer_newsletter]" id="jform_customer_newsletter" value="1" <?php if(isset($this->newsletter) AND $this->newsletter == 1){ ?> checked="checked"<?php } ?> />
                        <label for="customer_newsletter"><?php echo JText::_('COM_JEPROSHOP_SIGN_UP_FOR_OUR_NEWS_LETTER_LABEL'); ?></label>
                    </div>
                </div>
                <div class="checkbox control-group">
                    <div class="controls" >
                        <input type="checkbox" name="jform[customer_optin]" id="jform_customer_optin" value="1" <?php if(isset($this->optin) AND $this->optin == 1){ ?> checked="checked"<?php } ?> />
                        <label for="jform_customer_optin"><?php echo JText::_('COM_JEPROSHOP_RECEIVE_SPECIAL_OFFERS_FROM_OUR_PARTNERS_LABEL'); ?></label>
                    </div>
                </div>
            <?php } ?>
    <?php if($this->enable_b2b_mode){ ?>
        <div class="account_creation form-horizontal">
            <h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_YOUR_COMPANY_INFORMATION_LABEL'); ?></h3>
            <div class="control-group">
                <div class="control-label"><label for="jform_customer_company" ><?php echo JText::_('COM_JEPROSHOP_COMPANY_LABEL'); ?></label></div>
                <div class="controls" ><input type="text"  id="jform_customer_company" name="jform[customer_company]" value="<?php if(isset($this->company)){ echo $this->company; } ?>" /></div>
            </div>
            <div class="control-group">
                <div class="control-label"><label for="jform_customer_siret"><?php echo JText::_('COM_JEPROSHOP_SIRET_LABEL'); ?></label></div>
                <div class="controls" ><input type="text"  id="jform_customer_siret" name="jform[customer_siret]" value="<?php if(isset($this->siret)){ echo $this->siret; } ?>" /></div>
            </div>
            <div class="control-group">
                <div class="control-label"><label for="jform_customer_ape"><?php echo JText::_('COM_JEPROSHOP_APE_LABEL'); ?></label></div>
                <div class="controls" ><input type="text" id="jform_customer_ape" name="jform[customer_ape]" value="<?php if(isset($this->ape)){ echo $this->ape; } ?>" /></div>
            </div>
            <div class="control-group">
                <div class="control-label"><label for="jform_customer_website" ><?php echo JText::_('COM_JEPROSHOP_WEBSITE_LABEL'); ?></label></div>
                <div class="controls" ><input type="text"  id="jform_customer_website" name="jform[customer_website]" value="<?php if(isset($this->website)){ echo $this->website; } ?>" /></div>
            </div>
        </div>
    <?php }
    if(isset($PS_REGISTRATION_PROCESS_TYPE) && $PS_REGISTRATION_PROCESS_TYPE){ ?>
        <div class="panel-title"><h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_YOUR_ADDRESS_LABEL'); ?></h3></div>
        <div class="panel-content" >
                <?php foreach($this->delivery_all_fields as $field_name){
        if($field_name == "company"){
            if (!$this->enable_b2b_mode){ ?>
                <div class="control-group">
                    <div class="control-label"><label
                            for="jform_company" ><?php echo JText::_('COM_JEPROSHOP_COMPANY_LABEL'); ?></label></div>
                    <div class="controls"><input type="text" id="jform_company" name="jform[company]"
                                                 value="<?php if(isset($this->company)){ echo $this->company; } ?>" />
                    </div>
                </div>
                <?php }
        }elseif($field_name == "vat_number"){ ?>
            <div id="vat_number" style="display:none;" class="control-group">
                <div class="control-label" ><label for="jform_vat_number"><?php echo JText::_('COM_JEPROSHOP_VAT_NUMBER_LABEL'); ?></label></div>
                <div class="controls" ><input type="text" id="jform_vat_number" name="jform[vat_number]" value="<?php if(isset($this->vat_number)){ echo $this->vat_number; } ?>" /></div>
            </div>
        <?php }elseif($field_name == "firstname"){ ?>
            <div class="required control-group">
                <div class="control-label" ><label for="jform_firstname"><?php echo JText::_('COM_JEPROSHOP_FIRST_NAME_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" ><input type="text" id="jform_firstname" name="jform[firstname]" value="<?php if(isset($this->firstname)){ echo $this->firstname; } ?>" /></div>
            </div>
        <?php }elseif($field_name ==  "lastname"){ ?>
            <div class="required control-group">
                <div class="control-label" ><label for="jform_lastname"><?php echo JText::_('COM_JEPROSHOP_LAST_NAME_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" ><input type="text" id="jform_lastname" name="jform[lastname]" value="<?php if(isset($this->lastname)){ echo $this->lastname; } ?>" />
            </div>
        <?php }elseif($field_name ==  "address1"){ ?>
            <div class="required control-group">
                <div class="control-label" ><label for="jform_address1"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" >
                    <input type="text"  name="jform[address1]" id="jform_address1" value="<?php if(isset($this->address1)){ echo $this->address1; } ?>" />
                    <p class="description-preference"><?php echo JText::_('COM_JEPROSHOP_STREET_ADDRESS_PO_BOX_COMPANY_NAME_LABEL'); ?></p>
                </div>
            </div>
        <?php }elseif($field_name == "address2"){ ?>
            <div class="control-group is_customer_param">
                <div class="control-label" ><label for="jform_address2"><?php echo JText::_('COM_JEPROSHOP_ADDRESS_LINE_2_LABEL'); ?></label></div>
                <div class="controls" >
                    <input type="text" name="jform[address2]" id="jform_address2" value="<?php if(isset($this->address2)){ echo $this->address2; } ?>" />
                    <p class="description-preference"><?php echo JText::_('COM_JEPROSHOP_APARTMENT_SUITE_UNIT_BUILDING_FLOOR_LABEL'); ?></p>
                </div>
            </div>
        <?php }elseif($field_name == "postcode"){
            $postCodeExist = true; ?>
            <div class="required postcode control-group">
                <div class="control-label" ><label for="jform_postcode"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" ><input type="text"  name="jform[postcode]" id="jform_postcode" value="<?php if(isset($this->postcode)){ echo $this->postcode; } ?>" onkeyup="var postalCode = $('#jform_postcode'); postalCode.val(postalCode.val().toUpperCase());" /></div>
            </div>
        <?php }elseif($field_name == "city"){ ?>
            <div class="required control-group">
                <div class="control-label" ><label for="jform_city"><?php echo JText::_('COM_JEPROSHOP_CITY_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" ><input type="text"  name="jform[city]" id="jform_city" value="<?php if(isset($this->city)){ echo $this->city; } ?>" /></div>
            </div>
            <!-- if customer hasn't update his layout address, country has to be verified but it's deprecated -->
        <?php }elseif($field_name ==  "Country:name" || $field_name == "country"){ ?>
            <div class="required select control-group">
                <div class="control-label" ><label for="jform_country_id"><?php echo JText::_('COM_JEPROSHOP_COUNTRY_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" >
                    <select name="jform[country_id]" id="jform_country_id" >
                        <option value="">-</option>
                        <?php foreach($this->countries as $country){ ?>
                            <option value="<?php echo $country->country_id; ?>" <?php if((isset($this->country_id) AND $this->country_id == $country->country_id) OR (!isset($this->country_id) && $this->selected_country_id == $country->country_id)){ ?> selected="selected"<?php } ?> ><?php echo $country->name; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php }elseif($field_name == "State:name" || $field_name == 'state'){
            $stateExist = true; ?>
            <div class="required id_state select control-group">
                <div class="control-label" ><label for="jform_state_id"><?php echo JText::_('COM_JEPROSHOP_STATE_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" >
                    <select name="jform[state_id]" id="jform_state_id" >
                        <option value="">-</option>
                    </select>
                </div>
            </div>
            <?php } }
        if($postCodeExist == false){ ?>
            <div class="required postcode control-group invisible">
                <div class="control-label" ><label for="jform_postcode"><?php echo JText::_('COM_JEPROSHOP_ZIP_POSTAL_CODE_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" ><input type="text"  name="jform[postcode]" id="jform_postcode" value="<?php if(isset($this->postcode)){ echo $this->postcode; } ?>" onkeyup="var postalCode = $('#jform_postcode'); postalCode.val(postalCode.val().toUpperCase());" /></div>
            </div>
        <?php }
        if($stateExist == false){ ?>
            <div class="required id_state select invisible control-group">
                <div class="control-label" ><label for="jform_state_id"><?php echo JText::_('COM_JEPROSHOP_STATE_LABEL'); ?> <span>*</span></label></div>
                <div class="controls" >
                    <select name="jform[state_id]" id="jform_state_id" >
                        <option value="">-</option>
                    </select>
                </div>
            </div>
        <?php } ?>
                <div class="textarea control-group">
                    <div class="control-label" ><label for="jform_other"><?php echo JText::_('COM_JEPROSHOP_ADDITIONAL_INFORMATION_LABEL'); ?></label></div>
                    <div class="controls" ><textarea  name="jform[other]" id="jform_other" cols="26" rows="3"><?php if(isset($this->other)){ echo $this->other; } ?></textarea></div>
                </div>
                <?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?>
                    <div class="control-group"><div class="controls"><p class="inline-infos"><?php echo JText::_('COM_JEPROSHOP_YOU_MUST_REGISTER_AT_LEAST_ONE_PHONE_NUMBER_LABEL'); ?></p></div></div>
                <?php } ?>
                <div class="control-group">
                    <div class="control-label" ><label for="jform_phone"><?php echo JText::_('COM_JEPROSHOP_HOME_PHONE_LABEL'); ?></label></div>
                    <div class="controls" ><input type="text"  name="jform[phone]" id="jform_phone" value="<?php if(isset($this->phone)){ echo $this->phone; } ?>" /></div>
                </div>
                <div class="<?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?>required <?php } ?> control-group">
                    <div class="control-label" ><label for="jform_phone_mobile"><?php echo JText::_('COM_JEPROSHOP_MOBILE_PHONE_LABEL'); ?><?php if(isset($this->one_phone_at_least) && $this->one_phone_at_least){ ?> <span>*</span><?php } ?></label></div>
                    <div class="controls" ><input type="text"  name="jform[phone_mobile]" id="jform_phone_mobile" value="<?php if(isset($this->phone_mobile)){ echo $this->phone_mobile; } ?>" /></div>
                </div>
                <div class="required control-group" id="jform_address_alias">
                    <div class="control-label" ><label for="jform_alias"><?php echo JText::_('COM_JEPROSHOP_ASSIGN_AN_ADDRESS_ALIAS_FOR_FUTURE_REFERENCE_LABEL'); ?> <span>*</span></label></div>
                    <div class="controls" ><input type="text"  name="alias" id="jform_alias" value="<?php if(isset($this->alias)){ echo $this->alias; }else{ echo JText::_('COM_JEPROSHOP_MY_ADDRESS_LABEL'); } ?>" /></div>
                </div>
                <div class="account_creation dni">
                    <h3 class="page-subheading"><?php echo JText::_('COM_JEPROSHOP_TAX_IDENTIFICATION_LABEL'); ?></h3>
                    <div class="required control-group">
                        <div class="control-label" ><label for="jform_dni"><?php echo JText::_('COM_JEPROSHOP_IDENTIFICATION_NUMBER_LABEL'); ?> <span>*</span></label></div>
                        <div class="controls" >
                            <input type="text"  name="dni" id="jform_dni" value="<?php if(isset($this->dni)){ echo $this->dni; } ?>" />
                            <span class="form_info"><?php echo JText::_('COM_JEPROSHOP_DNI_NIF_NIE_LABEL'); ?></span>
                        </div>
                    </div>
                </div>
    <?php } ?>
                {$HOOK_CREATE_ACCOUNT_FORM}
                <div class="submit clearfix">
                    <input type="hidden" name="email_create" value="1" />
                    <input type="hidden" name="is_new_customer" value="1" />
                    <?php if(isset($return)){ ?><input type="hidden" class="hidden" name="return" value="<?php echo $return; ?>" /><?php } ?>
                    <button type="submit" name="submitAccount" id="submitAccount" class="btn btn-default button button-medium pull-right">
                        <span><?php echo JText::_('COM_JEPROSHOP_REGISTER_LABEL'); ?><i class="icon-chevron-right"></i></span>
                    </button>
                    <p class="required"><span class=""><span>*</span>&nbsp;<?php echo JText::_('COM_JEPROSHOP_REQUIRED_FIELD_LABEL'); ?></span></p>
                </div>
            </div>
        </div>
    </form>
    <?php //} ?>
<?php } ?>
</div>