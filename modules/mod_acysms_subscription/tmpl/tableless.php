<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="acysms_module<?php echo $params->get('moduleclass_sfx') ?>" id="acysms_module_<?php echo $formName; ?>">
	<?php
	$style = array();
	if($params->get('effect', 'normal') == 'mootools-slide'){
	if(!empty($mootoolsIntro)) echo '<p class="acysms_mootoolsintro">'.$mootoolsIntro.'</p>'; ?>
	<div class="acysms_mootoolsbutton" id="acysms_toggle_<?php echo $formName; ?>">
		<p><a class="acysms_togglemodule" id="acysms_togglemodule_<?php echo $formName; ?>" href="#subscribe"><?php echo $mootoolsButton ?></a></p>
		<?php
		}
		if($params->get('textalign', 'none') != 'none') $style[] .= 'text-align:'.$params->get('textalign');
		$styleString = empty($style) ? '' : 'style="'.implode(';', $style).'"';
		?>
		<div class="acysms_fulldiv" id="acysms_fulldiv_<?php echo $formName; ?>" <?php echo $styleString; ?> >
			<form id="<?php echo $formName; ?>" action="<?php echo JRoute::_('index.php'); ?>" onsubmit="return submitacysmsform('optin','<?php echo $formName; ?>')" method="post" name="<?php echo $formName ?>" <?php if(!empty($fieldsClass->formoption)) echo $fieldsClass->formoption; ?> >
				<div class="acysms_module_form">
					<?php if(!empty($introText)) echo '<span class="acysms_introtext">'.$introText.'</span>'; ?>
					<?php if(!empty($visibleGroupsArray)){
						if($params->get('dropdown', 0)){
							?>
							<select name="subscription[1]">
								<?php foreach($visibleGroupsArray as $myGroupId){ ?>
									<option value="<?php echo $myGroupId ?>"><?php echo $allGroups[$myGroupId]->name; ?></option>
								<?php } ?>
							</select>
						<?php }else{
							?>
							<div class="acysms_groups">
								<?php foreach($visibleGroupsArray as $myGroupId){
									$check = in_array($myGroupId, $checkedGroupsArray) ? 'checked="checked"' : '';

									if($params->get('checkmode', 0) == '0' AND !empty($identifiedUser->user_phone_number)){
										if(empty($allGroups[$myGroupId]->groupuser_status)){
											$check = '';
										}else{
											$check = $allGroups[$myGroupId]->groupuser_status == '-1' ? '' : 'checked="checked"';
										}
									}
									?>
									<p class="onegroup">
										<label for="acygroup_<?php echo $myGroupId; ?>">
											<input type="checkbox" class="acysms_checkbox" name="subscription[]" id="acygroup_<?php echo $myGroupId; ?>" <?php echo $check; ?> value="<?php echo $myGroupId; ?>"/>
											<?php
											if($params->get('overlay', 0)){
												echo ACYSMS::tooltip($allGroups[$myGroupId]->group_description, $allGroups[$myGroupId]->group_name, '', $allGroups[$myGroupId]->group_name, '');
											}else{
												echo $allGroups[$myGroupId]->group_name;
											}
											?>
										</label>
									</p>
								<?php } ?>
							</div>
						<?php }//endif dropdown
					}//endif visiblegroups ?>
					<div class="acysms_form">
						<?php foreach($fieldsToDisplay as $oneField){
							if(!empty($extraFields[$oneField])){
								if($displayOutside){
									echo '<td><label '.((strpos($extraFields[$oneField]->fields_type, 'text') !== false) ? 'for="user_'.$oneField.'_'.$formName.'"' : '').'>'.$fieldsClass->trans($extraFields[$oneField]->fields_fieldname).'</label></td>';
								}
								$sizestyle = '';
								if(!empty($extraFields[$oneField]->fields_options['size'])){
									$sizestyle = 'style="width:'.(is_numeric($extraFields[$oneField]->fields_options['size']) ? ($extraFields[$oneField]->fields_options['size'].'px') : $extraFields[$oneField]->fields_options['size']).'"';
								}
								?>

							<td class="acyfield_<?php echo $oneField; ?>">
								<?php
								if(!empty($identifiedUser->user_id) AND in_array($oneField, array('user_firstname', 'user_lastname', 'user_phone_number'))){ ?>
									<input id="user_<?php echo $oneField; ?>_<?php echo $formName; ?>" disabled="disabled" class="inputbox" type="text" name="user[<?php echo $oneField; ?>]" <?php echo $sizestyle; ?> value="<?php echo empty($identifiedUser->$oneField) ? $captions[$oneField] : @$identifiedUser->$oneField; ?>"/>
								<?php }else{
									echo $fieldsClass->display($extraFields[$oneField], @$identifiedUser->$oneField, 'user['.$oneField.']', !$displayOutside);
								} ?>
								</td><?php
								if(!$displayInline) echo '</tr><tr>';
							}else continue;
						}

						if($params->get('showterms', false)){
							?>
							<td class="acyterms" <?php if($displayOutside AND !$displayInline) echo 'colspan="2"'; ?> >
								<input id="mailingdata_terms_<?php echo $formName; ?>" class="checkbox" type="checkbox" name="terms"/> <?php echo $termslink; ?>
							</td>
							<?php if(!$displayInline) echo '</tr><tr>';
						} ?>

						<p class="acysubbuttons">
							<?php if($params->get('showsubscribe', true)){ ?>
								<input class="button subbutton acysms_button" type="submit" value="<?php $subtext = $params->get('subscribetextreg');
								if(empty($identifiedUser->userid) OR empty($subtext)){
									$subtext = $params->get('subscribetext', JText::_('SMS_SUBSCRIBE'));
								}
								echo $subtext; ?>" name="Submit" onclick="try{ return submitacysmsform('optin','<?php echo $formName; ?>'); }catch(err){alert('The form could not be submitted '+err);return false;}"/>
							<?php }
							if($params->get('showunsubscribe', false) AND (!$params->get('showsubscribe', true) OR empty($identifiedUser->userid) OR !empty($countUnsub))){ ?>
								<input class="button unsubbutton btn btn-inverse" type="button" value="<?php echo $params->get('unsubscribetext', JText::_('SMS_UNSUBSCRIBE')); ?>" name="Submit" onclick="return submitacysmsform('optout','<?php echo $formName; ?>')"/>
							<?php } ?>
						</p>
					</div>
					<?php
					if(!empty($fieldsClass->excludeValue)){
						$js = "\n"."acysms['excludeValues".$formName."'] = Array();";
						foreach($fieldsClass->excludeValue as $namekey => $value){
							$js .= "\n"."acysms['excludeValues".$formName."']['".$namekey."'] = '".$value."';";
						}
						$js .= "\n";
						$doc = JFactory::getDocument();
						if($params->get('includejs', 'header') == 'header'){
							$doc->addScriptDeclaration($js);
						}else{
							echo "<script type=\"text/javascript\">
							<!--
							$js
							//-->
							</script>";
						}
					}
					if(!empty($postText)) echo '<span class="acysms_finaltext">'.$postText.'</span>';
					$ajax = ($params->get('redirectmode') == '3') ? 1 : 0; ?>
					<input type="hidden" name="moduleId" value="<?php echo $module->id; ?>"/>
					<input type="hidden" name="ajax" value="<?php echo $ajax; ?>"/>
					<input type="hidden" name="ctrl" value="sub"/>
					<input type="hidden" name="task" value="notask"/>
					<input type="hidden" name="redirect" value="<?php echo urlencode($redirectUrl); ?>"/>
					<input type="hidden" name="redirectunsub" value="<?php echo urlencode($redirectUrlUnsub); ?>"/>
					<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT ?>"/>
					<?php if(!empty($identifiedUser->user_id)){ ?><input type="hidden" name="visiblegroups" value="<?php echo $visibleGroups; ?>"/><?php } ?>
					<input type="hidden" name="hiddengroups" value="<?php echo $hiddenGroups; ?>"/>
					<input type="hidden" name="acyformname" value="<?php echo $formName; ?>"/>
					<?php if(JRequest::getCmd('tmpl') == 'component'){ ?>
						<input type="hidden" name="tmpl" value="component"/>
						<?php if($params->get('effect', 'normal') == 'mootools-box' AND !empty($redirectUrl)){ ?>
							<input type="hidden" name="closepop" value="1"/>
						<?php }
					} ?>
					<?php $myItemId = $config->get('itemid', 0);
					if(empty($myItemId)){
						global $Itemid;
						$myItemId = $Itemid;
					}
					if(!empty($myItemId)){ ?><input type="hidden" name="Itemid" value="<?php echo $myItemId; ?>"/><?php } ?>
				</div>
			</form>
		</div>
		<?php if($params->get('effect', 'normal') == 'mootools-slide'){ ?> </div> <?php } ?>
</div>
