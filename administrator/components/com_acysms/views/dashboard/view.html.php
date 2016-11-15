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

class dashboardViewDashboard extends acysmsView{
	function display($tpl = null){

		$acyToolbar = ACYSMS::get('helper.toolbar');
		$acyToolbar->setTitle(ACYSMS_NAME, 'dashboard');
		$acyToolbar->help('dashboard');
		$acyToolbar->display();

		$app = JFactory::getApplication();
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');
		$integration = ACYSMS::getIntegration($currentIntegration);

		$references = array();

		$references['userStats'] = $this->_getUserStats($integration);
		$references['campaignStats'] = $this->_getCampaignStats();
		$references['sendingStats'] = $this->_getSendingStats();

		$references['users'] = $this->_getLatestUsers($integration);
		$references['chronoUsers'] = $this->_getChronologicalUsers();
		$references['campaignPerMonth'] = $this->_getCampaignPerMonth();
		$references['campaigns'] = $this->_getLatestCampaigns();
		$references['detailsSending'] = $this->_getDetailsSending();

		$references['progressBar'] = $this->_getProgressBar($references['userStats']);

		$references['config'] = ACYSMS::config();
		$references['integration'] = $integration;
		foreach($references as $key => $reference){
			$this->assignRef($key, $references[$key]);
		}

		parent::display($tpl);
	}

	private function _getUserStats($integration){
		$db = JFactory::getDBO();

		$db->setQuery('SELECT COUNT(phone_id) FROM #__acysms_phone');
		$numberPhoneBlocked = $db->loadResult();

		$queryTotalUser = $integration->getQueryUsers('', '', '');
		$totalUser = $queryTotalUser->count;

		$result = new stdClass();
		if(empty($totalUser)){
			$result->confirmedPercent = 0;
			$result->total = 0;
		}else{
			$result->confirmedPercent = (($totalUser - $numberPhoneBlocked) / $totalUser) * 100;
			$result->total = $totalUser;
		}

		return $result;
	}

	private function _getCampaignStats(){
		$db = JFactory::getDBO();

		$db->setQuery('SELECT COUNT(message_id) FROM #__acysms_message WHERE message_type = "auto"');
		$numberAutoCampaign = $db->loadResult();

		$db->setQuery('SELECT COUNT(*) FROM #__acysms_message WHERE message_type = "standard"');
		$numberStandardCampaign = $db->loadResult();

		$result = new stdClass();
		if(empty($numberAutoCampaign) && empty($numberStandardCampaign)){
			$result->standardPercent = 0;
			$result->total = 0;
		}else{
			$result->standardPercent = ($numberStandardCampaign / ($numberAutoCampaign + $numberStandardCampaign)) * 100;
			$result->total = $numberStandardCampaign + $numberAutoCampaign;
		}

		return $result;
	}

	private function _getSendingStats(){
		$db = JFactory::getDBO();

		$db->setQuery('SELECT SUM(stats_nbsent) AS nbsent, SUM(stats_nbfailed) as nbfailed FROM #__acysms_stats JOIN #__acysms_message ON stats_message_id = message_id WHERE message_senddate > (UNIX_TIMESTAMP() - (86400 * 30))');
		$stats = $db->loadObject();

		$result = new stdClass();
		if(empty($stats->nbsent)){
			$result->successPercent = 0;
			$result->total = 0;
		}else{
			$result->successPercent = ((($stats->nbsent + $stats->nbfailed) - $stats->nbfailed) / ($stats->nbsent + $stats->nbfailed)) * 100;
			$result->total = $stats->nbsent + $stats->nbfailed;
		}

		return $result;
	}

	private function _getLatestUsers($integration){
		$db = JFactory::getDBO();

		$order = new stdClass();
		$order->dir = 'DESC';
		$order->value = $integration->primaryField;

		$query = $integration->getQueryUsers('', $order, '');
		$db->setQuery($query->query, 0, 10);
		$users = $db->loadObjectList();

		return array_slice($users, 0, 10);
	}

	private function _getChronologicalUsers(){
		$db = JFactory::getDBO();

		$db->setQuery('SELECT COUNT(user_id) AS nbuser, FROM_UNIXTIME(`user_created`, \'%Y-%m-%d\') AS subday FROM #__acysms_user GROUP BY subday');

		return $db->loadObjectList();
	}

	private function _getCampaignPerMonth(){
		$db = JFactory::getDBO();

		$db->setQuery('SELECT COUNT(message_id) AS number_campaign, DATE_FORMAT(FROM_UNIXTIME(`message_created`),\'%Y-%m\') AS date_campaign FROM #__acysms_message WHERE message_type = "standard" OR message_type = "auto" GROUP BY date_campaign;');

		return $db->loadObjectList();
	}

	private function _getLatestCampaigns(){
		$db = JFactory::getDBO();

		$db->setQuery('SELECT * FROM #__acysms_message WHERE message_type = "standard" OR message_type = "auto" ORDER BY message_created DESC LIMIT 10');

		return $db->loadObjectList();
	}

	private function _getDetailsSending(){
		$db = JFactory::getDBO();

		$db->setQuery('SELECT statsdetails_message_id AS messageid, COUNT(*) AS nbreceived FROM #__acysms_statsdetails WHERE statsdetails_status = 5 GROUP BY statsdetails_message_id');
		$receiveds = $db->loadObjectList('messageid');

		$db->setQuery('SELECT message_subject AS name, stats_message_id AS messageid, stats_nbsent AS nbsent FROM #__acysms_stats JOIN #__acysms_message ON stats_message_id = message_id GROUP BY stats_message_id');
		$sents = $db->loadObjectList('messageid');

		$db->setQuery('SELECT answer_message_id AS messageid, COUNT(*) AS nbanswer FROM #__acysms_answer GROUP BY answer_message_id;');
		$answers = $db->loadObjectList('messageid');

		$results = array();
		foreach($sents as $messageid => $sent){
			$result = new stdClass();
			$result->sent = $sent->nbsent;
			$result->answer = 0;
			$result->received = 0;
			$result->name = $sent->name;

			if(!empty($answers[$messageid])){
				$result->answer = $answers[$messageid]->nbanswer;
			}

			if(!empty($receiveds[$messageid])){
				$result->received = $receiveds[$messageid]->nbreceived;
			}

			$results[$messageid] = $result;
		}

		ksort($results);

		return $results;
	}

	private function _getProgressBar($userStats){
		$db = JFactory::getDBO();
		$progress = new stdClass();

		$progress->selectIntegration = true;

		$db->setQuery('SELECT COUNT(senderprofile_id) AS nbsenderprofile FROM #__acysms_senderprofile WHERE senderprofile_gateway != "test"');
		$result = $db->loadResult();
		$progress->createSender = !empty($result);

		$progress->addUser = !empty($userStats->total);

		$db->setQuery('SELECT stats_nbsent FROM #__acysms_stats WHERE stats_nbsent > 0 LIMIT 1');
		$result = $db->loadResult();
		$progress->sendMessage = !empty($result);

		return $progress;
	}
}
