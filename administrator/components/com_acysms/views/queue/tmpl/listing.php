<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php $ctrl = JRequest::getCmd('ctrl');

if(empty($this->pageInfo->search) && empty($this->rows)){
	ACYSMS::display(JText::_('SMS_EMPTY_QUEUE'), 'info');
	return;
}
?>
<div id="acysms_content">
	<div id="iframedoc"></div>

	<?php if(!empty($this->messages)){ ?>
		<script language="JavaScript" type="text/javascript">
			function statsqueue(){
				var dataTable = new google.visualization.DataTable();

				dataTable.addColumn('date');
				dataTable.addColumn('number', '<?php echo JText::_('SMS_STATS_SEND', true); ?>');
				dataTable.addColumn('number', '<?php echo JText::_('SMS_STATS_FAILED', true); ?>');

				<?php
				$i = -1;
				$statsdetailsSentDate = '';
				$mindate = 0;
				$maxdate = 0;

				foreach($this->messages as $oneResult){
					$date = strtotime(substr($oneResult->sentdate, 0, 4)."-".intval(substr($oneResult->sentdate, 5, 2))."-".substr($oneResult->sentdate, 8, 2));
					if(empty($mindate) || $date < $mindate) $mindate = $date;
					if(empty($maxdate) || $date > $maxdate) $maxdate = $date;


					if($statsdetailsSentDate != $oneResult->sentdate){
						$i++;
						echo 'dataTable.addRow();';
						echo "dataTable.setValue($i, 0, new Date(".$date."*1000));";
						$statsdetailsSentDate = $oneResult->sentdate;
					}
					if($oneResult->statsdetails_status > 0){
						echo "dataTable.setValue($i, 1, ".intval(@$oneResult->total)."); ";
					}else echo "dataTable.setValue($i, 2, ".intval(@$oneResult->total)."); ";
				}
				?>

				var container = document.getElementById('statsqueue');
				var width = container.getBoundingClientRect().width;

				var vis = new google.visualization.ColumnChart(document.getElementById('statsqueue'));
				var options = {
					height: 200, width: width, legend: 'none', vAxis: {
						minValue: 0
					}, hAxis: {
						format: ' MMM d, y', //Js timestamp is in milliseconds so maxdate*1000
						maxValue: new Date(<?php echo $maxdate + 86400; ?> * 1000
			),
				minValue: new Date(<?php echo $mindate - 86400; ?> *
				1000
			)
			},
				backgroundColor: 'transparent', colors
			:
				['#adccea', 'red'],
			}

				;

				vis.draw(dataTable, options);
			}
			google.load("visualization", "1", {packages: ["corechart"]});
			google.setOnLoadCallback(statsqueue);

		</script>
		<div style="text-align: center">
			<h1 class="acysms_graphtitle"><?php echo JText::_('SMS_STATS_LAST_MONTH'); ?></h1>
		</div>
		<div class="acysmsonelineblockoptions googlechart">
			<div id="statsqueue"></div>
		</div>

	<?php } ?>


	<form action="index.php?option=<?php echo ACYSMS_COMPONENT.'&ctrl='.$ctrl; ?>" method="post" name="adminForm" id="adminForm">
		<table class="acysms_table_options">
			<tr>
				<td>
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
				</td>
				<td align="right">
					<?php if(!empty($this->rows)) echo $this->dropdownFilters->message; ?>
				</td>
			</tr>
		</table>
		<table class="acysms_table">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_('SMS_NUM'); ?>
				</th>
				<th class="title titledate">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SEND_DATE'), 'queue_senddate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesubject">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SUBJECT'), 'message_subject', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesender">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_USER'), 'receiver_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlepriority">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_PRIORITY'), 'queue_priority', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titletry">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_TRY'), 'queue_try', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<?php if($this->app->isAdmin()){ ?>
					<th class="title titletoggle">
						<?php echo JText::_('SMS_DELETE'); ?>
					</th>
				<?php } ?>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="7">
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
				$id = 'queue'.$i;
				?>
				<tr class="<?php echo "row$k"; ?>" id="<?php echo $id; ?>">
					<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo ACYSMS::getDate($row->queue_senddate); ?>
					</td>
					<td align="center">
						<a class="modal" href="<?php echo ACYSMS::completeLink($ctrl.'&task=preview&message_id='.$row->queue_message_id.'&receiver_id='.$row->queue_receiver_id.'&receiver_table='.$row->queue_receiver_table.'&queueMsgId='.$row->queue_message_id, true) ?>" rel="{handler: 'iframe', size: {x: 800, y: 590}}">
							<?php echo ACYSMS::dispSearch($row->message_subject, $this->pageInfo->search); ?>
						</a>
					</td>
					<td align="center">
						<?php echo ACYSMS::dispSearch($row->receiver_name.' ('.$this->helperPhone->getValidNum($row->receiver_phone).')', $this->pageInfo->search); ?>
					</td>
					<td align="center">
						<?php
						echo $row->queue_priority;
						?>
					</td>
					<td align="center">
						<?php
						echo $row->queue_try;
						?>
					</td>
					<?php if($this->app->isAdmin()){ ?>
						<td align="center">
							<?php echo $this->toggleClass->delete($id, $row->queue_receiver_id.'_'.$row->queue_message_id, 'queue'); ?>
						</td>
					<?php } ?>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="<?php echo $ctrl; ?>"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
