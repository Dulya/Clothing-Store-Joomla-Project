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
	<script type="text/javascript">
		function displayDetails(detailsDivID){

			var oldDisplay = document.getElementById(detailsDivID).style.display;

			document.getElementById('userStatisticDetails').style.display = "none";
			document.getElementById('campaignStatisticDetails').style.display = "none";
			document.getElementById('sendingStatisticDetails').style.display = "none";

			if(oldDisplay == 'block'){
				document.getElementById(detailsDivID).style.display = 'none';
			}else{
				document.getElementById(detailsDivID).style.display = 'block';
			}
		}

		(function(){
			window.onload = function(){
				var circles = document.querySelectorAll('.acyprogress');
				for(var i = 0; i < 3; i++){
					var totalProgress = circles[i].querySelector('circle').getAttribute('stroke-dasharray');
					var progress = circles[i].parentNode.getAttribute('data-percent');
					circles[i].querySelector('.bar').style['stroke-dashoffset'] = totalProgress * progress / 100;
				}
			}
		})();
	</script>

	<div id="iframedoc"></div>
	<div id="dashboard_mainview">
		<?php include(dirname(__FILE__).DS.'stats.php'); ?>
	</div>
	<!-- progress bar -->
	<div id="acysmsdashboard_progress">
		<div class="acysmsdashboard_progressbar">
			<table width="100%">
				<tr>
					<td><svg class="green-icon mail-icon <?php echo(!empty($this->progressBar->createSender) ? 'acystepdone' : ''); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 126.1 78.2"><title>acysms logo SVG</title><polygon points="7.7 0 116.7 0 62.2 45.1 7.7 0"/><polygon points="0 5.6 0 74.2 40.5 39.1 0 5.6"/><polygon points="45.8 45.1 7.7 78.2 116.7 78.2 78.6 45.1 62.2 58.2 45.8 45.1"/><polygon points="126.1 5.6 86.3 38.5 126.1 74.2 126.1 5.6"/></svg></td>
					<td><svg class="blue-icon mail-icon <?php echo(!empty($this->progressBar->selectIntegration) ? 'acystepdone' : ''); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 126.1 78.2"><title>acysms logo SVG</title><polygon points="7.7 0 116.7 0 62.2 45.1 7.7 0"/><polygon points="0 5.6 0 74.2 40.5 39.1 0 5.6"/><polygon points="45.8 45.1 7.7 78.2 116.7 78.2 78.6 45.1 62.2 58.2 45.8 45.1"/><polygon points="126.1 5.6 86.3 38.5 126.1 74.2 126.1 5.6"/></svg></td>
					<td><svg class="orange-icon mail-icon <?php echo(!empty($this->progressBar->addUser) ? 'acystepdone' : ''); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 126.1 78.2"><title>acysms logo SVG</title><polygon points="7.7 0 116.7 0 62.2 45.1 7.7 0"/><polygon points="0 5.6 0 74.2 40.5 39.1 0 5.6"/><polygon points="45.8 45.1 7.7 78.2 116.7 78.2 78.6 45.1 62.2 58.2 45.8 45.1"/><polygon points="126.1 5.6 86.3 38.5 126.1 74.2 126.1 5.6"/></svg></td>
					<td><svg class="red-icon mail-icon <?php echo(!empty($this->progressBar->sendMessage) ? 'acystepdone' : ''); ?>" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 126.1 78.2"><title>acysms logo SVG</title><polygon points="7.7 0 116.7 0 62.2 45.1 7.7 0"/><polygon points="0 5.6 0 74.2 40.5 39.1 0 5.6"/><polygon points="45.8 45.1 7.7 78.2 116.7 78.2 78.6 45.1 62.2 58.2 45.8 45.1"/><polygon points="126.1 5.6 86.3 38.5 126.1 74.2 126.1 5.6"/></svg></td>
				</tr>
				<tr class="acysmsdashboard_progressbar_colors">
					<td width="25%" height="3" class="acysmsdashboard_progress1"><span class="<?php echo(!empty($this->progressBar->createSender) ? 'acystepdone' : ''); ?>"></span></td>
					<td width="25%" height="3" class="acysmsdashboard_progress2"><span class="<?php echo(!empty($this->progressBar->selectIntegration) ? 'acystepdone' : ''); ?>"></span></td>
					<td width="25%" height="3" class="acysmsdashboard_progress3"><span class="<?php echo(!empty($this->progressBar->addUser) ? 'acystepdone' : ''); ?>"></span></td>
					<td width="25%" height="3" class="acysmsdashboard_progress4"><span class="<?php echo(!empty($this->progressBar->sendMessage) ? 'acystepdone' : ''); ?>"></span></td>
				</tr>
			</table>
		</div>

		<!-- progress steps -->
		<div class="acysmsdashboard_progress_steps clearfix">
			<a href="index.php?option=com_acysms&ctrl=senderprofile">
				<div class="acysmsdashboard_progress_block acysmsdashboard_step1">
					<div class="step_image"></div>
					<div class="step_info"><span class="step_title"><?php echo JText::_('SMS_DASHBOARD_STEP1_TITLE'); ?></span><?php echo JText::_('SMS_DASHBOARD_STEP1_DESC'); ?></div>
				</div>
			</a>

			<a href="index.php?option=com_acysms&ctrl=cpanel">
				<div class="acysmsdashboard_progress_block acysmsdashboard_step2">
					<div class="step_image"></div>
					<div class="step_info"><span class="step_title"><?php echo JText::_('SMS_DASHBOARD_STEP2_TITLE'); ?></span><?php echo JText::_('SMS_DASHBOARD_STEP2_DESC'); ?></div>
				</div>
			</a>

			<a href="index.php?option=com_acysms&ctrl=message">
				<div class="acysmsdashboard_progress_block acysmsdashboard_step3">
					<div class="step_image"></div>
					<div class="step_info"><span class="step_title"><?php echo JText::_('SMS_DASHBOARD_STEP3_TITLE'); ?></span><?php echo JText::_('SMS_DASHBOARD_STEP3_DESC'); ?></div>
				</div>
			</a>

			<a href="index.php?option=com_acysms&ctrl=queue">
				<div class="acysmsdashboard_progress_block acysmsdashboard_step4">
					<div class="step_image"></div>
					<div class="step_info"><span class="step_title"><?php echo JText::_('SMS_DASHBOARD_STEP4_TITLE'); ?></span><?php echo JText::_('SMS_DASHBOARD_STEP4_DESC'); ?></div>
				</div>
			</a>
		</div>
	</div>
</div>
