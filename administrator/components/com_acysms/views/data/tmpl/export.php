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
	<?php
	$divClass = $this->app->isAdmin() ? 'acysmsblockoptions' : 'acysmsonelineblockoptions';

	if(JRequest::getString('tmpl') == 'component'){
		?>
		<div class="acysmsblockoptions">
			<div class="acyheader 48_smsexport" style="float: left;"><?php echo JText::_('SMS_EXPORT'); ?></div>
			<div class="toolbar" id="toolbar" style="float: right;">
				<a onclick="javascript:submitbutton('doexport')" href="#"><span class="32_acyexport" title="<?php echo JText::_('SMS_EXPORT', true); ?>"></span><?php echo JText::_('SMS_EXPORT'); ?></a>
			</div>
		</div>
	<?php } ?>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=data" method="post" name="adminForm" id="adminForm">

		<div class="<?php echo $divClass; ?>">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_FIELD_EXPORT'); ?></span>
			<table class="acysms_blocktable" cellpadding="1">
				<?php
				if(!empty($this->fields)){
					foreach($this->fields as $fieldName => $fieldType){
						?>
						<tr>
							<td class="key">
								<?php echo $fieldName ?>
							</td>
							<td align="center">
								<?php echo JHTML::_('acysmsselect.booleanlist', "exportdata[".$fieldName."]", '', in_array($fieldName, $this->selectedfields) ? 1 : 0); ?>
							</td>
						</tr>
						<?php
					}
				}
				?>
			</table>
		</div>
		<div class="<?php echo $divClass; ?>">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_PARAMETERS'); ?></span>
			<table class="acysms_blocktable" cellpadding="1">
				<tbody>
				<tr>
					<td>
						<?php echo JText::_('SMS_EXPORT_FORMAT'); ?>
					</td>
					<td align="center">
						<?php echo $this->charset->display('exportformat', $this->config->get('export_format', 'UTF-8')); ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo JText::_('SMS_SEPARATOR'); ?>
					</td>
					<td align="center" nowrap="nowrap">
						<?php
						$values = array(JHTML::_('select.option', 'semicolon', JText::_('SMS_SEPARATOR_SEMICOLON')), JHTML::_('select.option', 'comma', JText::_('SMS_SEPARATOR_COMMA')));
						$data = str_replace(array(';', ','), array('semicolon', 'comma'), $this->config->get('export_separator', ';'));
						if($data == 'colon') $data = 'comma';
						echo JHTML::_('acysmsselect.radiolist', $values, 'exportseparator', '', 'value', 'text', $data);
						?>
					</td>
				</tr>
				</tbody>
			</table>
		</div>

		<?php if(empty($this->users)){ ?>
			<div class="<?php echo $divClass; ?>">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_FILTERS'); ?></span>
				<table class="acysms_blocktable" cellpadding="1">
					<tr>
						<td class="key" style="width: 225px; white-space: normal">
							<?php echo JText::_('SMS_EXPORT_SUB_GROUPS'); ?>
						</td>
						<td>
							<?php echo JHTML::_('acysmsselect.booleanlist', "exportfilter[subscribed]", 'onchange="if(this.value == 1){document.getElementById(\'exportgroups\').style.display = \'block\'; }else{document.getElementById(\'exportgroups\').style.display = \'none\'; }"', @$this->selectedFilters->subscribed, JText::_('SMS_YES'), JText::_('SMS_NO').' : '.JText::_('SMS_ALL_USERS')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_('SMS_USER_STATUS'); ?>
						</td>
						<td>
							<?php echo JHTML::_('acysmsselect.radiolist', $this->userStatus, "exportfilter[userStatus]", '', 'value', 'text', @$this->selectedFilters->userStatus); ?>
						</td>
					</tr>

				</table>
			</div>
		<?php } ?>
		<div class="<?php echo $divClass; ?>" id="exportgroups" <?php echo @$this->selectedFilters->subscribed ? '' : 'style="display:none"' ?> >
			<?php if(empty($this->users)){ ?>
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_GROUPS'); ?></span>
				<table class="acysms_blocktable" cellpadding="1">
					<tbody>
					<?php
					foreach($this->groups as $row){
						?>
						<tr>
							<td class="key">
								<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->group_color.'"></div>';
								$text = '<b>'.JText::_('SMS_ID').' : </b>'.$row->group_id.'<br />'.$row->group_description;
								echo ACYSMS::tooltip($text, $row->group_name, 'tooltip.png', $row->group_name);
								?>
							</td>
							<td align="center" nowrap="nowrap">
								<?php echo JHTML::_('acysmsselect.booleanlist', "exportgroups[".$row->group_id."]", '', in_array($row->group_id, $this->selectedgroups) ? 1 : 0, JText::_('SMS_YES'), JText::_('SMS_NO'), 'exportgroups'.$row->group_id.'_'); ?>
							</td>
						</tr>
						<?php
					}
					if(count($this->groups) > 3){ ?>
						<tr>
							<td>
							</td>
							<td align="center" nowrap="nowrap">
								<script language="javascript" type="text/javascript">
									function updateStatus(selection){
										<?php foreach($this->groups as $row){
										$languages['all'][$row->group_id] = $row->group_id;
										if($row->group_languages == 'all') continue;
										$lang = explode(',', trim($row->group_languages, ','));
										foreach($lang as $oneLang){
											$languages[strtolower($oneLang)][$row->group_id] = $row->group_id;
										}
									} ?>
										var selectedGroups = new Array();
										<?php
										foreach($languages as $val => $group_ids){
											echo "selectedGroups['$val'] = new Array('".implode("','", $group_ids)."'); ";
										}
										?>
										for(var i = 0; i < selectedGroups['all'].length; i++){
											<?php
											if(ACYSMS_J30){
												echo 'jQuery("label[for=exportgroups"+selectedGroups["all"][i]+"_0]").click();';
											}
											?>
											window.document.getElementById('exportgroups' + selectedGroups['all'][i] + '_0').checked = true;
										}
										if(!selectedGroups[selection]) return;
										for(var i = 0; i < selectedGroups[selection].length; i++){
											<?php
											if(ACYSMS_J30){
												echo 'jQuery("label[for=exportgroups"+selectedGroups[selection][i]+"_1]").click();';
											}
											?>
											window.document.getElementById('exportgroups' + selectedGroups[selection][i] + '_1').checked = true;
										}
									}
								</script>
								<?php
								$selectGroup = array();
								$selectGroup[] = JHTML::_('select.option', 'none', JText::_('SMS_NONE'));
								$selectGroup[] = JHTML::_('select.option', 'all', JText::_('SMS_ALL'));
								echo JHTML::_('acysmsselect.radiolist', $selectGroup, "selectgroups", 'onclick="updateStatus(this.value);"', 'value', 'text');
								?>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
			<?php } ?>

			<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
			<?php echo JHTML::_('form.token'); ?>
		</div>
	</form>
</div>
