<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(!empty($this->campaignPerMonth)){ ?>
	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
	<script type="text/javascript">
		google.load("visualization", "1", {packages: ["corechart"]});
		google.setOnLoadCallback(drawChart);

		function drawChart(){
			var data = google.visualization.arrayToDataTable([[<?php echo '\''.JText::_('SMS_PERIOD').'\'' ?>, <?php echo '\''.JText::_('SMS_CAMPAIGN_NUMBER').'\'' ?>],
																 <?php
																 foreach($this->campaignPerMonth as $campaign){
																	 echo '['.'\''.$campaign->date_campaign.'\''.','.$campaign->number_campaign.'],';
																 }
																 ?>
															 ]);

			var container = document.getElementsByClassName('acygraph')[0];
			var width = container.getBoundingClientRect().width;

			var view = new google.visualization.DataView(data);
			var options = {
				height: 450, width: width, isStacked: true, backgroundColor: 'transparent', colors: ['#4AA8CE'], legend: {position: 'none'}, hAxis: {slantedText: true, slantedTextAngle: 40, textStyle: {fontSize: 13}}
			};
			var chart = new google.visualization.ColumnChart(document.getElementById("campaignPerMonthChart"));
			chart.draw(view, options);
		}
	</script>
	<h1 class="acysms_graphtitle"> <?php echo JText::_('SMS_CAMPAIGN_MONTH') ?> </h1>
	<div class="acychart" id="campaignPerMonthChart"></div>
<?php }else echo JText::_("SMS_NO_STATISTICS"); ?>
