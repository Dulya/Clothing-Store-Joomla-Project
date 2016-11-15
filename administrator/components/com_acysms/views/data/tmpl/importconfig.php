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
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>" method="post" name="adminForm" enctype="multipart/form-data" id="adminForm">
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value="importConfig"/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<input type="hidden" name="filename" value="<?php echo $this->filename; ?>"/>
		<?php
		echo JHTML::_('form.token');
		$app = JFactory::getApplication();
		if(!$app->isAdmin()) echo '<div style="overflow-x:scroll">';
		?>


		<div class="acysmsblockoptions">
		<table class="acysms_table import_table">
					<?php
					echo '<thead><tr>';
					for($i = 0; $i < $this->nbColumns; $i++){
						echo '<th>'.JHTML::_('select.genericlist', $this->columns, "importColumn[]", 'size="1" class="chzn-done"', 'value', 'text', isset($this->importObject->importcolumn[$i]) ? $this->importObject->importcolumn[$i] : '').'</th>';
					}
					echo '</tr></thead>';
					$k = 0;
					foreach($this->lines as $oneLine){
						echo '<tbody><tr class="row'.$k.'">';
						foreach($oneLine as $lineInfo){
							echo '<td align="center">'.$oneColumn = htmlspecialchars($lineInfo, ENT_COMPAT | ENT_IGNORE, 'UTF-8').'</td>';
						}
						echo '</tr></tbody>';
						$k = 1 - $k;
					} ?>
		</table>
		</div>

		<div class="acysmsblockoptions">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_PARAMETERS'); ?></span>
			<div>
				<table class="acysms_blocktable" cellspacing="1" style="margin-top:30px">
					<tr id="trfilecharset">
						<td class="key">
							<?php echo JText::_('SMS_CHARSET_DATA'); ?>
						</td>
						<td>
							<?php $charsetType = ACYSMS::get('type.charset');
							$charsetType->js = 'onchange="this.form.submit();"';
							echo $charsetType->display('charsetconvert', $this->importObject->charsetconvert, ''); ?>
						</td>
					</tr>
					<tr id="importFirstLine">
						<td class="key"> <?php echo JText::_('SMS_IMPORT_FIRST_LINE'); ?></td>
						<td> <?php echo JHTML::_('acysmsselect.booleanlist', "importFirstLine", '', $this->importObject->importFirstLine, JText::_('SMS_YES'), JTEXT::_('SMS_NO')); ?> </td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_('SMS_OVERWRITE_EXISTING'); ?>
						</td>
						<td>
							<?php echo JHTML::_('acysmsselect.booleanlist', "overwriteExisting", '', $this->importObject->overwriteExisting, JText::_('SMS_YES'), JTEXT::_('SMS_NO')); ?>
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_('SMS_IMPORT_BLOCKED'); ?>
						</td>
						<td>
							<?php echo JHTML::_('acysmsselect.booleanlist', "importBlocked", '', $this->importObject->importBlocked, JText::_('SMS_YES'), JTEXT::_('SMS_NO')); ?>
						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php if(!$app->isAdmin()) echo '</div>'; ?>
		<?php
		if(!empty($this->groups)){
			?>
			<div class="acysmsblockoptions" id="importgroups">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_IMPORT_SUBSCRIBE'); ?></span>
				<table class="acysms_blocktable" cellpadding="1">
					<?php
					$currentValues = JRequest::getVar('importgroups');
					$groupid = JRequest::getInt('group_id');
					$k = 0;
					foreach($this->groups as $row){
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td>
								<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->group_color.'"></div>'; ?>
								<?php
								$text = '<b>'.JText::_('SMS_ID').' : </b>'.$row->group_id.'<br />'.$row->group_description;
								echo ACYSMS::tooltip($text, $row->group_name, 'tooltip.png', $row->group_name);
								?>
							</td>
							<td align="left">
								<?php
								echo JHTML::_('acysmsselect.booleanlist', "importgroups[".$row->group_id."]", '', !empty($currentValues[$row->group_id]) || $groupid == $row->group_id, JText::_('SMS_YES'), JTEXT::_('SMS_NO'), "importgroups".$row->group_id."-");
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					} ?>
					<tr class="<?php echo "row$k"; ?>" id="importcreatelist">
						<td colspan="2">
							<?php echo JText::_('SMS_IMPORT_SUBSCRIBE_CREATE').' : <input type="text" name="creategroup" placeholder="'.JText::_('SMS_GROUP_NAME').'" />'; ?>
						</td>
					</tr>
				</table>
			</div>
			<?php
		}
		?>
	</form>
</div>
