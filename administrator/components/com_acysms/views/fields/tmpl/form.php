<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysms_content">
	<div id="iframedoc"></div>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=fields" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<div style="float: left; width: 48%;">
			<div class="acysmsonelineblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('EXTRA_FIELDS'); ?></span>
				<table class="acysms_blocktable">
					<tr>
						<td class="acykey">
							<label for="name">
								<?php echo JText::_('SMS_FIELD_LABEL'); ?>
							</label>
						</td>
						<td>
							<input type="text" name="data[fields][fields_fieldname]" id="name" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->field->fields_fieldname); ?>"/>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="published">
								<?php echo JText::_('SMS_PUBLISHED'); ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('acysmsselect.booleanlist', "data[fields][fields_published]", '', @$this->field->fields_published); ?>
						</td>
					</tr>
					<tr class="columnname" style="display:none">
						<td class="key">
							<label for="namekey">
								<?php echo JText::_('SMS_FIELD_COLUMN'); ?>
							</label>
						</td>
						<td>
							<?php if(empty($this->field->fields_fieldid)){ ?>
								<input type="text" name="data[fields][fields_namekey]" id="namekey" class="inputbox" style="width:200px" value=""/>
							<?php }else echo $this->escape($this->field->fields_namekey); ?>
						</td>
					</tr>
					<tr <?php if(!empty($this->field->fields_fieldid) AND substr($this->field->fields_namekey, 0, 11) == 'customtext_') echo 'style="display:none"'; ?>>
						<td class="key">
							<label for="fieldtype">
								<?php echo JText::_('SMS_FIELD_TYPE'); ?>
							</label>
						</td>
						<td>
							<?php echo $this->fieldtype->display('data[fields][fields_type]', $this->field->fields_type); ?>
						</td>
					</tr>
					<?php if(empty($this->field->fields_core)){ ?>
						<tr class="required" style="display:none">
							<td class="key">
								<label for="required">
									<?php echo JText::_('SMS_REQUIRED'); ?>
								</label>
							</td>
							<td>
								<?php echo JHTML::_('acysmsselect.booleanlist', "data[fields][fields_required]", '', @$this->field->fields_required); ?>
							</td>
						</tr>
						<tr class="required" style="display:none">
							<td class="key">
								<label for="errormessage">
									<?php echo JText::_('SMS_FIELD_ERROR'); ?>
								</label>
							</td>
							<td>
								<input type="text" id="errormessage" size="80" name="fieldsoptions[errormessage]" value="<?php echo $this->escape(@$this->field->fields_options['errormessage']); ?>"/>
							</td>
						</tr>
					<?php } ?>
					<tr class="checkcontent" style="display:none">
						<td class="key">
							<label for="checkcontent">
								<?php echo JText::_('SMS_FIELD_AUTHORIZED_CONTENT'); ?>
							</label>
						</td>
						<td>
							<?php echo $this->fieldCheckContent; ?>
						</td>
					</tr>
					<tr class="checkcontent" style="display:none">
						<td class="key">
							<label for="errormessagecheckcontent">
								<?php echo JText::_('SMS_FIELD_ERROR_AUTHORIZED_CONTENT'); ?>
							</label>
						</td>
						<td>
							<input type="text" id="errormessagecheckcontent" size="80" name="fieldsoptions[errormessagecheckcontent]" value="<?php echo $this->escape(@$this->field->fields_options['errormessagecheckcontent']); ?>"/>
						</td>
					</tr>
					<tr class="default" style="display:none">
						<td class="key">
							<label for="default">
								<?php echo JText::_('SMS_FIELD_DEFAULT'); ?>
							</label>
						</td>
						<td>
							<?php echo $this->fieldsClass->display($this->field, @$this->field->fields_default, 'data[fields][fields_default]', false, '', true); ?>
						</td>
					</tr>
					<tr class="cols" style="display:none">
						<td class="key">
							<label for="cols">
								<?php echo JText::_('SMS_FIELD_COLUMNS'); ?>
							</label>
						</td>
						<td>
							<input type="text" style="width:50px" name="fieldsoptions[cols]" id="cols" class="inputbox" value="<?php echo $this->escape(@$this->field->fields_options['cols']); ?>"/>
						</td>
					</tr>
					<tr class="rows" style="display:none">
						<td class="key">
							<label for="rows">
								<?php echo JText::_('SMS_FIELD_ROWS'); ?>
							</label>
						</td>
						<td>
							<input type="text" style="width:50px" name="fieldsoptions[rows]" id="rows" class="inputbox" value="<?php echo $this->escape(@$this->field->fields_options['rows']); ?>"/>
						</td>
					</tr>
					<tr class="size" style="display:none">
						<td class="key">
							<label for="size">
								<?php echo JText::_('SMS_FIELD_SIZE'); ?>
							</label>
						</td>
						<td>
							<input type="text" id="size" style="width:50px" name="fieldsoptions[size]" value="<?php echo $this->escape(@$this->field->fields_options['size']); ?>"/>
						</td>
					</tr>
					<tr class="format" style="display:none">
						<td class="key">
							<label for="format">
								<?php echo JText::_('SMS_FORMAT'); ?>
							</label>
						</td>
						<td>
							<input type="text" id="format" name="fieldsoptions[format]" value="<?php echo $this->escape(@$this->field->fields_options['format']); ?>"/>
						</td>
					</tr>
					<tr class="customtext" style="display:none">
						<td class="key">
							<label for="size">
								<?php echo JText::_('SMS_CUSTOM_TEXT'); ?>
							</label>
						</td>
						<td>
							<textarea cols="50" rows="10" name="fieldcustomtext"><?php echo $this->escape(@$this->field->fields_options['customtext']); ?></textarea>
						</td>
					</tr>
					<tr class="multivalues" style="display:none">
						<td class="key" valign="top">
							<label for="value">
								<?php echo JText::_('SMS_FIELD_VALUES'); ?>
							</label>
						</td>
						<td>
							<table>
								<tbody id="tablevalues">
								<tr>
									<td><?php echo JText::_('SMS_FIELD_VALUE') ?></td>
									<td><?php echo JText::_('SMS_FIELD_TITLE'); ?></td>
									<td><?php echo JText::_('SMS_DISABLED'); ?></td>
									<td></td>
								</tr>
								<?php $optionid = 0;
								if(!empty($this->field->fields_value) AND is_array($this->field->fields_value)){
									foreach($this->field->fields_value as $title => $onevalue){ ?>
										<tr>
											<td><input style="width:150px;" id="option<?php echo $optionid; ?>title" type="text" name="fieldvalues[title][]" value="<?php echo $this->escape($title); ?>"/></td>
											<td><input style="width:180px;" id="option<?php echo $optionid; ?>value" type="text" name="fieldvalues[value][]" value="<?php echo $this->escape($onevalue->value); ?>"/></td>
											<td><select class="chzn-done" style="width:80px;" id="option<?php echo $optionid; ?>disabled" name="fieldvalues[disabled][]" class="inputbox">
													<option value="0"><?php echo JText::_('SMS_NO'); ?></option>
													<option <?php if(!empty($onevalue->fields_disabled)) echo 'selected="selected"'; ?> value="1"><?php echo JText::_('SMS_YES'); ?></option>
												</select></td>
											<td><a onclick="acysmsmove(<?php echo $optionid; ?>,1);return false;" href="#"><img src="<?php echo ACYSMS_IMAGES; ?>movedown.png" alt=" ˇ "/></a><a onclick="acysmsmove(<?php echo $optionid; ?>,-1);return false;" href="#"><img src="<?php echo ACYSMS_IMAGES; ?>moveup.png" alt=" ˆ "/></a></td>
										</tr>
										<?php $optionid++;
									}
								} ?>
								<tr>
									<td><input style="width:150px;" id="option<?php echo $optionid; ?>title" type="text" name="fieldvalues[title][]" value=""/></td>
									<td><input style="width:180px;" id="option<?php echo $optionid; ?>value" type="text" name="fieldvalues[value][]" value=""/></td>
									<td><select class="chzn-done" style="width:80px;" id="option<?php echo $optionid; ?>disabled" name="fieldvalues[disabled][]" class="inputbox">
											<option value="0"><?php echo JText::_('SMS_NO'); ?></option>
											<option value="1"><?php echo JText::_('SMS_YES'); ?></option>
										</select></td>
									<td><a onclick="acysmsmove(<?php echo $optionid; ?>,1);return false;" href="#"><img src="<?php echo ACYSMS_IMAGES; ?>movedown.png" alt=" ˇ "/></a><a onclick="acysmsmove(<?php echo $optionid; ?>,-1);return false;" href="#"><img src="<?php echo ACYSMS_IMAGES; ?>moveup.png" alt=" ˆ "/></a></td>
								</tr>
								</tbody>
							</table>
							<a class="acysms_button" onclick="addLine();return false;" href='#' title="<?php echo $this->escape(JText::_('SMS_FIELD_ADDVALUE')); ?>"><?php echo JText::_('SMS_FIELD_ADDVALUE'); ?></a>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div style="float: left; width: 48%">
			<div class="acysmsonelineblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('FRONTEND'); ?></span>
				<table class="acysms_blocktable">
					<tr>
						<td class="key">
							<label for="frontcomp">
								<?php echo JText::_('SMS_DISPLAY_FRONTCOMP'); ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('acysmsselect.booleanlist', "data[fields][fields_frontcomp]", '', @$this->field->fields_frontcomp); ?>
						</td>
					</tr>

				</table>
			</div>
			<div class="acysmsonelineblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('BACKEND'); ?></span>
				<table class="acysms_blocktable">
					<tr>
						<td class="key">
							<label for="backend">
								<?php echo JText::_('SMS_DISPLAY_BACKEND'); ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('acysmsselect.booleanlist', "data[fields][fields_backend]", '', @$this->field->fields_backend); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="backend">
								<?php echo JText::_('SMS_DISPLAY_LISTING'); ?>
							</label>
						</td>
						<td>
							<?php echo JHTML::_('acysmsselect.booleanlist', "data[fields][fields_listing]", '', @$this->field->fields_listing); ?>
						</td>
					</tr>
				</table>
			</div>
			<?php if(!empty($this->field->fields_fieldid)){ ?>
				<div class="acysmsonelineblockoptions">
					<span class="acysmsblocktitle"><?php echo JText::_('SMS_PREVIEW'); ?></span>
					<table class="acysms_blocktable">
						<tr>
							<td class="key"><?php $this->fieldsClass->suffix = 'preview';
								echo $this->fieldsClass->getFieldName($this->field); ?></td>
							<td><?php echo $this->fieldsClass->display($this->field, $this->field->fields_default, 'data[user]['.$this->field->fields_namekey.']'); ?></td>
						</tr>
					</table>
				</div>
				<div class="acysmsonelineblockoptions">
					<span class="acysmsblocktitle">HTML</span>
					<textarea style="width:95%" rows="5"><?php echo htmlentities($this->fieldsClass->display($this->field, $this->field->fields_default, 'user['.$this->field->fields_namekey.']')); ?></textarea>
				</div>
				<?php
			} ?>
		</div>

		<?php if(!empty($this->field->fields_fieldid) AND in_array($this->field->fields_type, array('radio', 'singledropdown', 'checkbox', 'multipledropdown'))){
			$this->fieldsClass->chart('user', $this->field);
		}
		?>
		<div class="clr"></div>

		<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->field->fields_fieldid); ?>"/>
		<input type="hidden" name="option" value="com_acysms"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="fields"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
