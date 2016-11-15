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

class StatsController extends ACYSMSController{

	var $aclCat = 'stats';

	function detaillisting(){
		if(!$this->isAllowed('stats', 'manage_details')) return;
		JRequest::setVar('layout', 'detaillisting');
		return parent::display();
	}

	function diagram(){
		JRequest::setVar('layout', 'diagram');
		return parent::display();
	}

	function remove(){
		if(!$this->isAllowed('stats', 'delete')) return;

		JRequest::checkToken() or die('Invalid Token');
		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();

		$statsClass = ACYSMS::get('class.stats');
		$num = $statsClass->delete($cids);

		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');
		return $this->listing();
	}

	function exportGlobal(){
		if(!$this->isAllowed('stats', 'export')) return;

		$db = JFactory::getDBO();

		$exportHelper = ACYSMS::get('helper.export');
		$config = ACYSMS::config();
		$encodingClass = ACYSMS::get('helper.encoding');
		$exportHelper->addHeaders('globalStatistics_'.date('m_d_y'));


		$message_id = JRequest::getVar('cid');
		$extraConditions = '';

		JArrayHelper::toInteger($message_id);

		if(!empty($message_id)) $extraConditions = ' WHERE stats.stats_message_id IN ('.implode(', ', $message_id).') ';

		$query = 'SELECT message.*, stats.*
				FROM #__acysms_stats AS stats
				JOIN #__acysms_message AS message
				ON stats.stats_message_id = message.message_id
				'.$extraConditions;
		$db->setQuery($query);
		$mydata = $db->loadObjectList();

		$eol = "\r\n";
		$before = '"';
		$separator = '"'.str_replace(array('semicolon', 'comma'), array(';', ','), $config->get('export_separator', ';')).'"';
		$exportFormat = $config->get('export_format', 'UTF-8');
		$after = '"';

		$titles = array(JText::_('SMS_SUBJECT'), JText::_('SMS_STATS_SEND'), JText::_('SMS_STATS_FAILED'), JText::_('SMS_TYPE'), JText::_('SMS_ID'));
		$titleLine = $before.implode($separator, $titles).$after.$eol;
		echo $titleLine;

		foreach($mydata as $oneStat){
			$line = $oneStat->message_subject.$separator;
			$line .= $oneStat->stats_nbsent.$separator;
			$line .= $oneStat->stats_nbfailed.$separator;
			$line .= $oneStat->message_type.$separator;
			$line .= $oneStat->stats_message_id.$separator;

			$line = $before.$encodingClass->change($line, 'UTF-8', $exportFormat).$after.$eol;
			echo $line;
		}
		exit;
	}

	function exportStatsDetails(){
		if(!$this->isAllowed('stats', 'export')) return;

		$app = JFactory::getApplication();
		$paramBase = ACYSMS_COMPONENT.'.d'.$this->getName();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$messageClass = ACYSMS::get('class.message');

		$message_id = JRequest::getVar('message_id');

		$message = $messageClass->get($message_id);
		$integration = ACYSMS::getIntegration($message->message_receiver_table);

		$selectedStatus = $app->getUserStateFromRequest($paramBase."messageStatus", 'messageStatus', '', 'string');

		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'message_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));


		$queryFilters = array();

		if($selectedStatus !== '') $queryFilters[] = 'stats.statsdetails_status = '.intval($selectedStatus);


		$exportHelper = ACYSMS::get('helper.export');
		$config = ACYSMS::config();
		$encodingClass = ACYSMS::get('helper.encoding');
		$helperPhone = ACYSMS::get('helper.phone');
		$exportHelper->addHeaders('detailledStatistics_'.date('m_d_y'));
		$db = JFactory::getDBO();

		$queryFilters[] = 'stats.statsdetails_message_id = '.intval($message_id);

		$nbExport = 5000;

		$queryConditions = new stdClass();
		$queryConditions->where = $queryFilters;
		$queryConditions->order = $pageInfo->filter->order;
		$queryConditions->limit = $nbExport;
		$queryConditions->offset = 0;

		$rows = array();
		do{
			$queryUser = $integration->getStatDetailsQuery($queryConditions, $pageInfo->search);
			$db->setQuery($queryUser->query);
			$result = $db->loadObjectList();
			$queryConditions->offset = $queryConditions->offset + $nbExport;
			$rows = array_merge($result, $rows);
		}while(count($result) > 0);


		$eol = "\r\n";
		$before = '"';
		$separator = '"'.str_replace(array('semicolon', 'comma'), array(';', ','), $config->get('export_separator', ';')).'"';
		$exportFormat = $config->get('export_format', 'UTF-8');
		$after = '"';

		$titles = array(JText::_('SMS_SUBJECT'), JText::_('SMS_SEND_DATE'), JText::_('SMS_RECEPTION_DATE'), JText::_('SMS_EMAIL'), JText::_('SMS_USER'), JText::_('SMS_PHONE'), JText::_('SMS_STATUS'), JText::_('SMS_ID'), JText::_('SMS_EXTRA_INFORMATION'));
		$titleLine = $before.implode($separator, $titles).$after.$eol;
		echo $titleLine;

		foreach($rows as $oneStat){
			$line = $oneStat->message_subject.$separator;
			$line .= ACYSMS::getDate($oneStat->statsdetails_sentdate).$separator;
			$line .= ACYSMS::getDate($oneStat->statsdetails_received_date).$separator;
			$line .= $oneStat->receiver_email.$separator;
			$line .= $oneStat->receiver_name.$separator;
			$line .= $helperPhone->getValidNum($oneStat->receiver_phone).$separator;
			$line .= $oneStat->statsdetails_status.$separator;
			$line .= $oneStat->statsdetails_sms_id.$separator;
			$line .= $oneStat->statsdetails_error.$separator;

			$line = $before.$encodingClass->change($line, 'UTF-8', $exportFormat).$after.$eol;
			echo $line;
		}
		exit;
	}
}
