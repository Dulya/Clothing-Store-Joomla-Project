<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php ACYSMS::display(JText::sprintf('SMS_QUEUE_STATUS', ACYSMS::getDate(time())), 'info');

$app = JFactory::getApplication();

$messageCtrl = ($app->isAdmin() ? 'message' : 'frontmessage');
$queueCtrl = ($app->isAdmin() ? 'queue' : 'frontqueue');

?>
<form action="index.php?tmpl=component&option=com_acysms&ctrl=<?php echo $queueCtrl; ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
	<div>
		<?php if(!empty($this->queue)){ ?>
			<div class="acysmsblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_QUEUE_READY'); ?></span>
				<table class="adminlist table table-striped table-hover" cellspacing="1" align="center">
					<tbody>
					<?php $k = 0;
					$total = 0;
					foreach($this->queue as $messageid => $row){
						$total += $row->nbsub;
						?>
						<tr class="<?php echo "row$k"; ?>">
							<td>
								<?php
								echo JText::sprintf('SMS_READY', $row->queue_message_id, $row->message_subject, $row->nbsub);
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					} ?>
					</tbody>
				</table>
				<br/>
				<input type="hidden" name="totalsend" value="<?php echo $total; ?>"/>
				<input type="submit" onclick="document.adminForm.task.value = 'processQueue';" value="<?php echo JText::_('SMS_SEND', true); ?>">
			</div>
		<?php } ?>
		<?php if(!empty($this->schedMsgs)){ ?>
			<div class="acysmsblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_SCHEDULE_MESSAGE'); ?></span>
				<table class="adminlist table table-striped table-hover" cellspacing="1" align="center">
					<tbody>
					<?php $k = 0;
					$sendButton = false;
					foreach($this->schedMsgs as $row){

						if($row->message_senddate < time()) $sendButton = true; ?>
						<tr class="<?php echo "row$k"; ?>">
							<td>
								<?php
								echo JText::sprintf('SMS_QUEUE_SCHED', $row->message_id, $row->message_subject, ACYSMS::getDate($row->message_senddate));
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					} ?>
					</tbody>
				</table>
				<?php if($sendButton){ ?><br/><input onclick="document.adminForm.task.value = 'genschedule';" type="submit" value="<?php echo JText::_('SMS_SEND', true); ?>"><?php } ?>
			</div>
		<?php } ?>
		<?php if(!empty($this->nextqueue)){ ?>
			<div class="acysmsblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::sprintf('SMS_QUEUE_STATUS', ACYSMS::getDate(time())); ?></span>
				<table class="adminlist table table-striped table-hover" cellspacing="1" align="center">
					<tbody>
					<?php $k = 0;
					foreach($this->nextqueue as $message_id => $row){ ?>
						<tr class="<?php echo "row$k"; ?>">
							<td>
								<?php
								echo JText::sprintf('SMS_READY', $row->queue_message_id, $row->message_subject, $row->nbsub);
								echo '<br />'.JText::sprintf('SMS_QUEUE_NEXT_SCHEDULE', ACYSMS::getDate($row->senddate));
								?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					} ?>
					</tbody>
				</table>
			</div>
		<?php } ?>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="message_id" value="<?php echo $this->infos->message_id; ?>"/>
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="processQueue"/>
	<input type="hidden" name="ctrl" value="<?php echo $messageCtrl; ?>"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
