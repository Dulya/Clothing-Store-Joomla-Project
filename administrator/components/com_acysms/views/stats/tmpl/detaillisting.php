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
	<?php if(JRequest::getString('tmpl') == 'component') include(dirname(__FILE__).DS.'menu.detaillisting.php') ?>
	<div id="iframedoc"></div>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=stats" method="post" name="adminForm"
		  id="adminForm">
		<table class="acysms_table_options">
			<tr>
				<td id="statsdetailsfilter">
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
				</td>
				<td align="right">
					<?php echo $this->filters->messageStatus; ?>
				</td>
			</tr>
		</table>
		<table class="acysms_table">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_('SMS_NUM'); ?>
				</th>
				<th class="title titlesubject">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SUBJECT'), 'message.message_subject', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, JRequest::getCmd('task')); ?>
				</th>
				<th class="title titlesenddate">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SEND_DATE'), 'statsdetails_sentdate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, JRequest::getCmd('task')); ?>
				</th>
				<th class="title titlereceptiondate">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_RECEPTION_DATE'), 'statsdetails_received_date', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, JRequest::getCmd('task')); ?>
				</th>
				<th class="title titleemail">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_EMAIL'), 'receiver_email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, JRequest::getCmd('task')); ?>
				</th>
				<th class="title titlesender">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_USER'), 'receiver_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, JRequest::getCmd('task')); ?>
				</th>
				<th class="title titlestatus">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_STATUS'), 'statsdetails_status', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, JRequest::getCmd('task')); ?>
				</th>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'message_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, JRequest::getCmd('task')); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="8">
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
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo ACYSMS::dispSearch($row->message_subject, $this->pageInfo->search); ?>
					</td>
					<td align="center">
						<?php echo ACYSMS::getDate($row->message_sentdate); ?>
					</td>
					<td align="center">
						<?php if(!empty($row->statsdetails_received_date)) echo ACYSMS::getDate($row->statsdetails_received_date); ?>
					</td>
					<td align="center">
						<?php echo ACYSMS::dispSearch($row->receiver_email, $this->pageInfo->search); ?>
					</td>
					<td align="center">
						<?php if(!empty($row->receiver_name) || $row->receiver_phone) echo ACYSMS::dispSearch($this->escape($row->receiver_name).' ('.$this->helperPhone->getValidNum($row->receiver_phone).')', $this->pageInfo->search); ?>
					</td>
					<td align="center">
						<div id="message_status_<?php echo $i; ?>">

							<?php
							if($row->message_status == '0' || $row->message_status == '-1' || $row->message_status == '-2' || $row->message_status == '-3' || $row->message_status == '-99'){
								$messageStatus = str_replace('-', 'M', $row->message_status);
								echo '<span style="text-decoration: underline; cursor:pointer;" onClick="document.getElementById(\'errors_'.$i.'\').style.display=\'block\';" >'.JText::_('SMS_STATUS_'.$messageStatus).'</span>';
							}else echo JText::_('SMS_STATUS_'.$row->message_status);
							?>
						</div>
						<div id="errors_<?php echo $i; ?>" style="display:none">
							<?php
							if(!empty($row->statsdetails_error)) echo $this->escape($row->statsdetails_error);
							?>
						</div>
					</td>
					<td align="center">
						<?php echo $row->message_id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value="detaillisting"/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>"/>
		<?php
		echo (JRequest::getString('tmpl') == 'component') ? '<input type="hidden" name="tmpl" value="component" />' : '';
		echo JHTML::_('form.token'); ?>
	</form>
</div>
