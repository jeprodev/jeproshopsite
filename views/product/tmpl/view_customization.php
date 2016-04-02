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
?>
<!--Customization -->
<div class="page_product_box">
    <form method="post" action="<?php echo $customizationFormTarget; ?>" enctype="multipart/form-data" id="jform_customization_form" class="clearfix" >
        <p class="info_customizable">
            <?php  JText::_('COM_JEPROSHOP_AFTER_SAVING_YOUR_CUSTOMIZED_PRODUCT_REMEMBER_TO_ADD_IT_TO_YOUR_CART_LABEL');
            if($this->product->uploadable_files) { echo '<br />' . JText::_('COM_JEPROSHOP_ALLOWED_FILE_FORMATS_ARE_GIF_JPG_PNG_LABEL'); } ?>
        </p>
        <?php if($this->product->uploadable_files){ ?>
            <div class="customizable_products_file">
                <h5 class="product_heading_h5"><?php echo JText::_('COM_JEPROSHOP_PICTURES_LABEL'); ?></h5>
                <ul id="uploadable_files" class="clearfix">
                    <?php $start=0;
                    foreach($this->customizationFields as $field){
                        if($field->type == 0){?>
                            <li class="customization_upload_line<?php if($field->required){ ?> required<?php } ?>">
                                <?php $key = 'pictures_' . $this->product->product_id . '_' . $field->customization_field_id;
                                if(isset($this->pictures->key)){ ?>
                                    <div class="customization_upload_browse">
                                        <img src="<?php echo $pic_dir . $pictures.$key . '_small'; ?>" alt="" />
                                        <a href="<?php echo $this->context->controller->getProductDeletePictureLink($this->product, $field->customization_field_id); ?>" title="<?php echo JText::_('COM_JEPROSHOP_DELETE_LABEL'); ?>" >
                                            <img src="<?php echo $img_dir . 'icon/delete.gif'; ?>" alt="<?php echo JText::_('COM_JEPROSHOP_DELETE_LABEL'); ?>" class="customization_delete_icon" width="11" height="13" />
                                        </a>
                                    </div>
                                <?php } ?>
                                <div class="customization_upload_browse control-group">
                                    <div class="control-label" >
                                        <label class="customization_upload_browse_description">
                                            <?php if(!empty($field->name)){ echo $field->name; }else{ echo JText::_('COM_JEPROSHOP_PLEASE_SELECT_AN_IMAGE_FILE_FROM_YOUR_COMPUTER_LABEL'); } ?>
                                            <?php if($field->required){ ?><sup>*</sup><?php } ?>
                                        </label>
                                    </div>
                                    <div class="controls" ><?php $picturesKey = $pictures.$key; ?>
                                        <input type="file" name="jform[file_<?php $field->customization_field_id; ?>]" id="jform_img_<?php echo $customization_field; ?>" class="form-control customization_block_input <?php if(isset($picturesKey)){ ?>filled<?php } ?>" />
                                    </div>
                            </li>
                            <?php $start++;
                        }
                    } ?>
                </ul>
            </div>
        <?php }
        if($this->product->text_fields){ ?>
            <div class="customizable_products_text">
                <h5 class="product_heading_h5"><?php echo JText::_('COM_JEPROSHOP_TEXT_LABEL'); ?></h5>
                <ul id="text_fields">
                    <?php $start=0 ; //assign='customizationField'}
                    foreach($customizationFields as $field){ //} ' name='customizationFields'}
                        if($field->type == 1){ ?>
                            <li class="customization_upload_line<?php if($field->required){ ?> required<?php } ?>">
                                <div class="control-group" >
                                    <div class="control-label" >
                                        <label for ="jform_text_Field_<?php echo $customizationField; ?>">
                                            <?php $key =' textFields_' . $this->product->product_id . '_' . $field->customization_field_id;
                                            if(!empty($field->name)){ echo $field->name; }
                                            if($field->required){ ?><sup>*</sup><?php } ?>
                                        </label>
                                    </div>
                                    <div class="controls" >
                                        <textarea name="jform[text_field_<?php echo $field->customization_field_id; ?>]" class=" customization_block_input" id="jform_text_field_<?php $customizationField; ?>" rows="3" cols="20">{strip}
                                            <?php $textFieldsKey = $textFields.$key;
                                            if(isset($textFieldsKey)){ echo stripslashes($textFields.$key); } ?>
                                            {/strip}
                                        </textarea>
                                    </div>
                                </div>
                            </li>
                            <?php $start++;
                        }
                    } ?>
                </ul>
            </div>
        <?php } ?>
        <p id="customizedDatas">
            <input type="hidden" name="quantityBackup" id="quantityBackup" value="" />
            <input type="hidden" name="submitCustomizedDatas" value="1" />
            <button class="button btn btn-default button button-small" name="saveCustomization">
                <span><?php echo JText::_('COM_JEPROSHOP_SAVE_LABEL'); ?></span>
            </button>
            <span id="ajax-loader" class="invisible"><img src="<?php echo $this->jeproshop_image_dir. 'loader.gif'; ?>" alt="loader" /></span>
        </p>
    </form>
    <p class="clear required"><sup>*</sup> <?php echo JText::_('COM_JEPROSHOP_REQUIRED_FIELD_LABEL'); ?></p>
</div>
<!--end Customization -->