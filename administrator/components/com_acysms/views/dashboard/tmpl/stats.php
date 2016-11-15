<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="dashboard_mainstat">
	<div class="acysmsdashboard_content">
		<div class="acysmscircles">
			<div class="circle stat_subscribers" onclick="displayDetails('userStatisticDetails');">

				<!-- circle animation 1 -->
				<div class="progressdiv" data-percent="<?php echo $this->userStats->confirmedPercent; ?>" data-title="<?php echo $this->userStats->total; ?>">
					<svg class="acyprogress" width="178" height="178" viewport="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<circle r="80" cx="89" cy="89" fill="#fff" stroke-dasharray="502.4" stroke-dashoffset="0" stroke="#51add2"></circle>
						<circle class="bar" r="80" cx="89" cy="89" fill="transparent" stroke-dasharray="502.4" stroke-dashoffset="0"></circle>
					</svg>
				</div>
				<span class="circle_title"><?php echo JText::_('SMS_USERS'); ?></span>
				<span class="circle_informations">
					<span class="stats_blue_point"></span> <?php echo JText::_('SMS_ENABLED'); ?>
					<span class="stats_grey_point"></span> <?php echo JText::_('SMS_DISABLED'); ?>
				</span>
				<br/>
				<button class="acysms_button"><?php echo JText::sprintf("SMS_MORE_USER_STATISTICS", JText::_('USERS')) ?></button>
			</div>

			<div class="circle stat_campaign" onclick="displayDetails('campaignStatisticDetails');">

				<!-- circle animation 2 -->
				<div class="progressdiv" data-percent="<?php echo $this->campaignStats->standardPercent; ?>" data-title="<?php echo $this->campaignStats->total; ?>">
					<svg class="acyprogress" width="178" height="178" viewport="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<circle r="80" cx="89" cy="89" fill="#fff" stroke-dasharray="502.4" stroke-dashoffset="0" stroke="#80AE57"></circle>
						<circle class="bar" r="80" cx="89" cy="89" fill="transparent" stroke-dasharray="502.4" stroke-dashoffset="0"></circle>
					</svg>
				</div>
				<span class="circle_title"><?php echo JText::_('SMS_MESSAGES'); ?></span>
				<span class="circle_informations">
					<span class="stats_green_point"></span> <?php echo JText::_('SMS_STANDARD'); ?>
					<span class="stats_grey_point"></span> <?php echo JText::_('SMS_AUTO'); ?>
				</span>
				<br/>
				<button class="acysms_button"><?php echo JText::sprintf("SMS_MORE_CAMPAIGN_STATISTICS", JText::_('LISTS')) ?></button>

			</div>
			<div class="circle stat_sending" onclick="displayDetails('sendingStatisticDetails');">
				<!-- circle animation 3 -->
				<div class="progressdiv" data-percent="<?php echo $this->sendingStats->successPercent; ?>" data-title="<?php echo $this->sendingStats->total; ?>">
					<svg class="acyprogress" width="178" height="178" viewport="0 0 100 100" version="1.1" xmlns="http://www.w3.org/2000/svg">
						<circle r="80" cx="89" cy="89" fill="#fff" stroke-dasharray="502.4" stroke-dashoffset="0" stroke="#D16D78"></circle>
						<circle class="bar" r="80" cx="89" cy="89" fill="transparent" stroke-dasharray="502.4" stroke-dashoffset="0"></circle>
					</svg>
				</div>
				<span class="circle_title"><?php echo JText::_('SMS_SENDING_MONTH'); ?></span>
				<span class="circle_informations">
					<span class="stats_darkblue_point"></span> <?php echo JText::_('SMS_STATUS_1'); ?>
					<span class="stats_grey_point"></span> <?php echo JText::_('SMS_STATUS_FAILED'); ?>
				</span>
				<br/>
				<button class="acysms_button"><?php echo JText::sprintf("SMS_MORE_SENDING_STATISTICS", JText::_('NEWSLETTER')) ?></button>
			</div>
		</div>
		<div class="acygraph">
			<div id="userStatisticDetails" style="display: none;">
				<?php
				if(ACYSMS::isAllowed($this->config->get('acl_receivers_view', 'all'))){
					echo '<div id="latestUsers">';
					include(dirname(__FILE__).DS.'latestusers.php');
					echo '</div>';
				}

				if(ACYSMS::isAllowed($this->config->get('acl_receivers_view', 'all'))){
				   echo '<div id="chronoUsers">';
				   include(dirname(__FILE__).DS.'chronousers.php');
				   echo '</div>';
			   }
			   ?>
		   </div>
		   <div id="campaignStatisticDetails" style="display: none;">
			   <?php
			   if(ACYSMS::isAllowed($this->config->get('acl_messages_manage_all', 'all'))){
				   echo '<div id="campaignPerMonth">';
				   include(dirname(__FILE__).DS.'campaignpermonth.php');
				   echo '</div>';
			   }

			   if(ACYSMS::isAllowed($this->config->get('acl_messages_manage_all', 'all'))){
				   echo '<div id="latestCampaigns">';
				   include(dirname(__FILE__).DS.'latestcampaigns.php');
				   echo '</div>';
			   }
			   ?>

		   </div>
		   <div id="sendingStatisticDetails" style="display: none;">
			   <?php
			   if(ACYSMS::isAllowed($this->config->get('acl_messages_manage_all', 'all'))){
				   echo '<div id="detailsSending">';
				   include(dirname(__FILE__).DS.'detailssending.php');
				   echo '</div>';
			   }
				?>
			</div>
		</div>
	</div>
</div>

