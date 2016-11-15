<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-queue">
	<?php echo JHTML::_('form.token'); ?>

	<div style="float: left; width:48%">
		<div class="acysmsonelineblockoptions">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_CRON'); ?></span>
			<table class="acysms_blocktable">
				<tr>
					<td colspan="2">
						<?php
						if($this->config->get('cron_last', 0) < (time() - 43200)){
							ACYSMS::display(JText::_('SMS_CREATE_CRON_REMINDER').'<br />'.$this->elements->cron_edit, 'warning');
						}else{
							echo $this->elements->cron_edit;
						}
						?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo ACYSMS::tooltip(JText::_('SMS_CRON_URL_DESC'), JText::_('SMS_CRON_URL'), '', JText::_('SMS_CRON_URL')); ?>
					</td>
					<td>
						<a href="<?php echo $this->escape($this->elements->cron_url); ?>" target="_blank"><?php echo $this->elements->cron_url; ?></a>
					</td>
				</tr>
			</table>
		</div>


		<div class="acysmsonelineblockoptions">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_LAST_CRON'); ?></span>
			<table class="acysms_blocktable">
				<tr>
					<td class="key">
						<?php echo ACYSMS::tooltip(JText::_('SMS_LAST_RUN_DESC'), JText::_('SMS_LAST_RUN'), '', JText::_('SMS_LAST_RUN')); ?>
					</td>
					<td>
						<?php $diff = intval((time() - $this->config->get('cron_last', 0)) / 60);
						if($diff > 500){
							echo ACYSMS::getDate($this->config->get('cron_last'));
							echo ' <span style="font-size:10px">(Your current time is '.ACYSMS::getDate(time()).')</span>';
						}else{
							echo JText::sprintf('SMS_MINUTES_AGO', $diff);
						} ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php echo $this->config->get('cron_fromip'); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo ACYSMS::tooltip(JText::_('SMS_REPORT_DESC'), JText::_('SMS_REPORT'), '', JText::_('SMS_REPORT')); ?>
					</td>
					<td>
						<?php echo nl2br($this->config->get('cron_report')); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>


	<div style="float: left; width:48%">
		<div class="acysmsonelineblockoptions"  id="queue_processing">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_QUEUE_PROCESS'); ?></span>
			<table class="acysms_blocktable">
				<tr>
					<td colspan="2">
						<?php echo JText::sprintf('SMS_SEND_X_USING_Y', $this->sendXsmsParams, $this->parallelThreadsParams); ?>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<?php echo JText::sprintf('SMS_AUTO_SEND_PROCESS_LIMIT', $this->cronFrequency); ?>
					</td>
				</tr>
			</table>
		</div>

		<div class="acysmsonelineblockoptions">
			<table class="acysms_blocktable" cellspacing="1" width="100%">
				<tr>
					<td>
						<?php echo ACYSMS::tooltip(JText::_('SMS_REPORT_SEND_DESC'), JText::_('SMS_REPORT_SEND'), '', JText::_('SMS_REPORT_SEND')); ?>
					</td>
					<td>
						<?php echo $this->elements->cron_sendreport; ?>
					</td>
				</tr>
				<tr>
					<td valign="top" id="cronreportdetail">
						<?php echo ACYSMS::tooltip(JText::_('SMS_REPORT_SEND_TO_DESC'), JText::_('SMS_REPORT_SEND_TO'), '', JText::_('SMS_REPORT_SEND_TO')); ?>
					</td>
					<td>
						<input class="inputbox" type="text" name="config[cron_sendto]" style="width:200px" value="<?php echo $this->escape($this->config->get('cron_sendto')); ?>">
					</td>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo ACYSMS::tooltip(JText::_('SMS_REPORT_SAVE_DESC'), JText::_('SMS_REPORT_SAVE'), '', JText::_('SMS_REPORT_SAVE')); ?>
					</td>
					<td>
						<?php echo $this->elements->cron_savereport; ?>
					</td>
				</tr>
				<tr id="cronreportsave">
					<td valign="top" class="key">
						<?php echo ACYSMS::tooltip(JText::_('SMS_REPORT_SAVE_TO_DESC'), JText::_('SMS_REPORT_SAVE_TO'), '', JText::_('SMS_REPORT_SAVE_TO')); ?>
					</td>
					<td>
						<input class="inputbox" type="text" name="config[cron_savepath]" style="width:300px" value="<?php echo $this->escape($this->config->get('cron_savepath')); ?>">
						<?php echo $this->elements->seeReport; ?>
						<?php echo $this->elements->deleteReport; ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
