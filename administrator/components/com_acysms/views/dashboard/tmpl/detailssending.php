<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(!empty($this->detailsSending)){ ?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load("visualization", "1", {packages: ["corechart"]});
		google.setOnLoadCallback(drawChart);

		function drawChart(){
			var data = google.visualization.arrayToDataTable([[<?php echo '\''.JText::_('SMS_MESSAGE').'\'' ?>, <?php echo '\''.JText::_('SMS_STATUS_1').'\'' ?>, <?php echo '\''.JText::_('SMS_STATUS_5').'\'' ?>, <?php echo '\''.JText::_('SMS_ANSWERS').'\'' ?>],
																 <?php
																 foreach($this->detailsSending as $messageId => $sending){
																	 echo '['.'\''.$sending->name.'\''.','.$sending->sent.','.$sending->received.','.$sending->answer.'],';
																 }
																 ?>
															 ]);

			var container = document.getElementsByClassName('acygraph')[0];
			var width = container.getBoundingClientRect().width;

			var view = new google.visualization.DataView(data);
			var options = {
				height: 450, width: width, backgroundColor: 'transparent', colors: ['#80AE57', '#E0BA53', '#51ADD2'], hAxis: {slantedText: true, slantedTextAngle: 40, textStyle: {fontSize: 13}}
			};
			var chart = new google.visualization.LineChart(document.getElementById("detailsSendingChart"));
			chart.draw(view, options);
		}
	</script>
	<h1 class="acysms_graphtitle"> <?php echo JText::_('SMS_SENDING_STATS') ?> </h1>
	<div class="acychart" id="detailsSendingChart"></div>
<?php }else echo JText::_("SMS_NO_STATISTICS"); ?>
