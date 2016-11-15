<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
if($this->app->isAdmin()){
	$receiverCtrl = 'receiver';
}else $receiverCtrl = 'frontreceiver';

?>
	<table class="acysms_table_options">
		<tr>
			<td>
				<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
			</td>
			<td align="right">
				<?php if($this->app->isAdmin()) echo $this->dropdownFilters->integration; ?>
				<?php echo $this->dropdownFilters->message; ?>
				<?php echo $this->dropdownFilters->answerreceiver; ?>
			</td>

		</tr>
	</table>
	<table class="acysms_table">
		<thead>
		<tr>
			<th class="title titlenum">
				<?php echo JText::_('SMS_NUM'); ?>
			</th>
			<th class="title titlebox">
				<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this);"/>
			</th>
			<th class="title titlebody">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_SMS_BODY'), 'answer.answer_body', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title titlefrom">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_FROM'), 'answer.answer_from', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title titlename">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), '', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title titleto">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_TO'), 'answer.answer_to', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title titledate">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_RECEPTION_DATE'), 'answer.answer_date', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title titleid">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'answer.answer_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title titleconversation">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_CONVERSATION'), '', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="9">
				<?php echo $this->pagination->getListFooter(); ?>
				<?php echo $this->pagination->getResultsCounter();
				if(ACYSMS_J30) echo '<br />'.$this->pagination->getLimitBox(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		for($i = 0, $a = count($this->rows); $i < $a; $i++){
			$row =& $this->rows[$i];
			$attachments = explode(',', $row->answer_attachment);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td>
					<?php echo JHTML::_('grid.id', $i, $row->answer_id); ?>
				</td>
				<td>
					<?php echo nl2br(ACYSMS::dispSearch($row->answer_body, $this->pageInfo->search)); ?><br/>
					<?php
					foreach($attachments as $index => $oneAttachment){
						if(empty($oneAttachment)) continue;
						if($index == 0) echo '<fieldset style="padding: 3px; float:left;"> <span style="vertical-align: top; font-size:10px;">'.JText::_('SMS_ATTACHMENTS').' : </span>';
						echo '<a href="'.$oneAttachment.'" target="_blank" class="answer_attachment"></a>';
						if($index == 0) echo '</fieldset>';
					}
					?>
				</td>
				<td>
					<?php echo ACYSMS::dispSearch($row->answer_from, $this->pageInfo->search); ?>
				</td>
				<td>
					<?php if(!empty($row->integrationNameField)){
						echo ACYSMS::dispSearch($row->integrationNameField, $this->pageInfo->search);
					}else if(!empty($row->joomlaUserNameField)) echo ACYSMS::dispSearch($row->joomlaUserNameField, $this->pageInfo->search);
					?>
				</td>
				<td>
					<?php echo ACYSMS::dispSearch($row->answer_to, $this->pageInfo->search); ?>
				</td>
				<td>
					<?php echo ACYSMS::getDate($row->answer_date); ?>
				</td>
				<td width="1%">
					<?php echo $this->escape($row->answer_id); ?>
				</td>
				<td>
					<?php
					if(isset($this->receivers[$row->answer_receiver_table][$this->phoneHelper->getValidNum($row->answer_from)]) && !empty($this->receivers[$row->answer_receiver_table][$this->phoneHelper->getValidNum($row->answer_from)]->queue_receiver_id)){
						echo '<a class="modal" href="index.php?option=com_acysms&tmpl=component&ctrl='.$receiverCtrl.'&task=conversation&receiverid='.$this->escape($this->receivers[$row->answer_receiver_table][$this->phoneHelper->getValidNum($row->answer_from)]->queue_receiver_id).'" rel="{handler: \'iframe\', size: {x: 500, y: 600}}"><i class="smsicon-conversation"></i></a>';
					}
					?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
	</table>
<?php if($this->isAjax) exit; ?>
