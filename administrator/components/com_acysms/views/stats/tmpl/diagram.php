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
$toolbarHelper = ACYSMS::get('helper.toolbar');
$toolbarHelper->setTitle(JText::_('SMS_MESSAGE'));
$toolbarHelper->directPrint();
$toolbarHelper->topfixed = false;
$toolbarHelper->display();
?>

<div id="acysms_content">
	<?php $app = JFactory::getApplication();
	$adminPath = $app->isAdmin() ? '../' : JURI::base(true)."/";
	if(empty($this->messageStat->totalSent)) die('No statistics recorded yet');
	?>
	<script language="JavaScript" type="text/javascript">

		function drawChartSendProcess(){
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn('string');
			dataTable.addColumn('number');
			dataTable.addRows(10);

			dataTable.setValue(0, 0, '<?php echo intval($this->messageStat->nbFailed).' '.JText::_('SMS_STATUS_0', true); ?>');
			dataTable.setValue(1, 0, '<?php echo intval($this->messageStat->nbSent).' '.JText::_('SMS_STATUS_1', true); ?>');
			dataTable.setValue(2, 0, '<?php echo intval($this->messageStat->nbAcceptedByTheGateway).' '.JText::_('SMS_STATUS_2', true); ?>');
			dataTable.setValue(3, 0, '<?php echo intval($this->messageStat->nbSentToOperator).' '.JText::_('SMS_STATUS_3', true); ?>');
			dataTable.setValue(4, 0, '<?php echo intval($this->messageStat->nbBuffered).' '.JText::_('SMS_STATUS_4', true); ?>');
			dataTable.setValue(5, 0, '<?php echo intval($this->messageStat->nbDelivered).' '.JText::_('SMS_STATUS_5', true); ?>');
			dataTable.setValue(6, 0, '<?php echo intval($this->messageStat->nbNotDelivered).' '.JText::_('SMS_STATUS_M1', true); ?>');
			dataTable.setValue(7, 0, '<?php echo intval($this->messageStat->nbTimedOut).' '.JText::_('SMS_STATUS_M2', true); ?>');
			dataTable.setValue(8, 0, '<?php echo intval($this->messageStat->nbBlocked).' '.JText::_('SMS_STATUS_M3', true); ?>');
			dataTable.setValue(9, 0, '<?php echo intval($this->messageStat->nbUnknowError).' '.JText::_('SMS_STATUS_M99', true); ?>');

			dataTable.setValue(0, 1, <?php echo intval($this->messageStat->nbFailed); ?>);
			dataTable.setValue(1, 1, <?php echo intval($this->messageStat->nbSent); ?>);
			dataTable.setValue(2, 1, <?php echo intval($this->messageStat->nbAcceptedByTheGateway); ?>);
			dataTable.setValue(3, 1, <?php echo intval($this->messageStat->nbSentToOperator); ?>);
			dataTable.setValue(4, 1, <?php echo intval($this->messageStat->nbBuffered); ?>);
			dataTable.setValue(5, 1, <?php echo intval($this->messageStat->nbDelivered); ?>);
			dataTable.setValue(6, 1, <?php echo intval($this->messageStat->nbNotDelivered); ?>);
			dataTable.setValue(7, 1, <?php echo intval($this->messageStat->nbTimedOut); ?>);
			dataTable.setValue(8, 1, <?php echo intval($this->messageStat->nbBlocked); ?>);
			dataTable.setValue(9, 1, <?php echo intval($this->messageStat->nbUnknowError); ?>);

			var vis = new google.visualization.PieChart(document.getElementById('sendprocess'));
			var options = {
				height: 200, colors: ['#40A640', '#5F78B5', '#A42B37'], title: '<?php echo JText::_('SMS_SEND_PROCESS', true);?>', is3D: true, legendTextStyle: {color: '#333333'}
			};
			vis.draw(dataTable, options);
		}

		function drawChartMsgSentByDate(){
			var dataTable = new google.visualization.DataTable();
			dataTable.addColumn('string');
			<?php
			$dates = array();
			$types = array();
			$i = 0;
			$a = 1;
			foreach($this->msgSentByDate as $oneResult){
				if(!isset($dates[$oneResult->groupingdate])){
					$dates[$oneResult->groupingdate] = $i;
					$i++;
					echo "dataTable.addRows(1);"."\n";
					$grpDate = JFactory::getDate(strtotime($oneResult->groupingdate));
					if(ACYSMS_J30){
						echo "dataTable.setValue(".$dates[$oneResult->groupingdate].", 0, '".ACYSMS::getDate($oneResult->groupingdate, $this->dateformat)."');";
					}else{
						echo "dataTable.setValue(".$dates[$oneResult->groupingdate].", 0, '".$grpDate->toFormat($this->dateformat)."');";
					}
				}
				if(!isset($types[$oneResult->groupingtype])){
					$types[$oneResult->groupingtype] = $a;
					echo "dataTable.addColumn('number','".$oneResult->groupingtype."');"."\n";
					$a++;
				}
				echo "dataTable.setValue(".$dates[$oneResult->groupingdate].", ".$types[$oneResult->groupingtype].", ".$oneResult->total.");";
			}
			?>

			var vis = new google.visualization.LineChart(document.getElementById('lineChart'));
			var options = {
				height: 500, legend: 'none', is3D: true, title: '<?php echo JText::_('SMS_MESSAGES_SENT', true)?>', legendTextStyle: {color: '#333333'}
			};
			vis.draw(dataTable, options);
		}

		google.load("visualization", "1", {packages: ["corechart"]});
		google.setOnLoadCallback(drawChartSendProcess);
		google.setOnLoadCallback(drawChartMsgSentByDate);
	</script>
	<h1 class="onlyprint"><?php echo $this->messageStat->message->message_subject; ?></h1>

	<div style="width:100%;">
		<table width="60%" style="float:left">
			<tr>
				<td width="45%">

					<?php
					$sent = '<span class="statnumber">'.((int)$this->messageStat->totalSent).'</span>';
					$sent = JText::sprintf('SMS_TOTAL_SMS_SENT', $sent);
					if(ACYSMS:: isAllowed($this->config->get('acl_receivers_view', 'all'))){
						$link = 'stats&task=detaillisting';
						if(!$app->isAdmin()) $link = 'frontstats&task=detaillisting&filter_message='.$this->messageStat->message->message_id;
						$text = '<a href="'.ACYSMS::completeLink($link.'&filter_message='.$this->messageStat->message->message_id, true).'">'.$sent.'</a>';
						echo $text;
					}
					?>
					<br/>
					<?php if(!empty($this->messageStat->queue)){ ?>
						<?php echo JText::sprintf('SMS_NB_PENDING_SMS', $this->messageStat->queue, '<b><i>'.$this->messageStat->message->message_subject.'</i></b>'); ?>
					<?php } ?>
					<br/>
					<?php if(!empty($this->messageStat->message->message_senddate)){ ?>
						<?php echo JText::_('SMS_SEND_DATE').' : <span class="statnumber">'.ACYSMS::getDate($this->messageStat->message->message_senddate); ?></span>
					<?php } ?>
				</td>
			</tr>
		</table>
		<div style="width:35%; float:right;" id="sendprocess" class="acychart"></div>
	</div>
	<div style="width:100%; float:right;" id="lineChart" class="acychart"></div>
</div>
