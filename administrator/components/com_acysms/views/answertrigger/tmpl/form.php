<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysms_content" >
	<div id="iframedoc"></div>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=answertrigger" method="post" name="adminForm"  id="adminForm" enctype="multipart/form-data">
		<div class="acysmsblockoptions">
		<span class="acysmsblocktitle"><?php echo JText::_('SMS_ANSWERS_TRIGGER'); ?></span>
		<table class="acysms_blocktable" width="100%">
			<tr>
				<td class="key" >
					<label for="answertrigger_name" >
						<?php echo JText::_( 'SMS_NAME' ); ?>
					</label>
				</td>
				<td>
					<input type="text" name="data[answertrigger][answertrigger_name]" id="answertrigger_name" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->answertrigger->answertrigger_name);?>" />
				</td>
			</tr>
			<tr>
				<td class="key" valign="top">
					<label for="answertrigger_description" >
						<?php echo JText::_( 'SMS_DESCRIPTION' ); ?>
					</label>
				</td>
				<td>
					<textarea  name="data[answertrigger][answertrigger_description]" id="answertrigger_description" class="inputbox" style="width:300px;height:100px;" ><?php echo $this->escape(@$this->answertrigger->answertrigger_description);?></textarea>
				</td>
			</tr>
			<tr>
				<td class="key" valign="top">
					<label>
						<?php echo JText::_( 'SMS_TRIGGER_ACTION_WHEN' ); ?>
					</label>
				</td>
				<td style="padding-bottom: 35px">
					<?php echo $this->triggerWhen ; ?>
				</td>
			</tr>
			<tr>
				<td class="key" valign="top">
					<label>
						<?php echo JText::_( 'SMS_ACTIONS' ); ?>
					</label>
				</td>
				<td>
					<?php echo '<div id="triggerActionList">'.$this->radioListActions.'</div>' ; ?>
				</td>
			</tr>
		</table>
		</div>
		<div class="clr"></div>
		<input type="hidden" name="data[answertrigger][answertrigger_publish]" value="<?php echo $this->escape(@$this->answertrigger->answertrigger_publish); ?>" />
		<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->answertrigger->answertrigger_id); ?>" />
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
		<input type="hidden" name="task" value="save" />
		<input type="hidden" name="ctrl" value="answertrigger" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
</div>
