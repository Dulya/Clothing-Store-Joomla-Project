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

class statsViewstats extends acysmsView{
	var $ctrl = 'stats';
	var $icon = 'stat';

	function display($tpl = null){
		$doc = JFactory::getDocument();
		$doc->addScript("https://www.google.com/jsapi");

		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'message_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');
		$selectedType = $app->getUserStateFromRequest($paramBase."filter_type", 'filter_type', '0', 'string');

		if($pageInfo->filter->order->dir != "asc") $pageInfo->filter->order->dir = 'desc';


		$dropdownFilters = new stdClass();
		$listMessageType = ACYSMS::get('type.messagetype');
		$listMessageType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$dropdownFilters->type = $listMessageType->display('filter_type', $selectedType);

		$searchMap = array('stats.stats_message_id', 'stats.stats_nbsent', 'stats.stats_nbfailed', 'message.message_subject', 'message.message_type');
		$filters = array();

		if(!empty($selectedType)) $filters[] = 'message.message_type = '.$db->quote($selectedType);

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.* FROM '.ACYSMS::table('stats').' AS stats LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.stats_message_id = message.message_id';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$queryCount = 'SELECT COUNT(stats.stats_message_id) FROM '.ACYSMS::table('stats').' AS stats';
		$db->setQuery($queryCount);

		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$acyToolbar = ACYSMS::get('helper.toolbar');
		$acyToolbar->setTitle(JText::_('SMS_STATS'), $this->ctrl);

		if(ACYSMS::isAllowed($config->get('acl_stats_export', 'all'))) $acyToolbar->custom('exportGlobal', JText::_('SMS_EXPORT'), 'export', false);
		$acyToolbar->divider();
		if(ACYSMS::isAllowed($config->get('acl_stats_delete', 'all'))) $acyToolbar->delete();

		$acyToolbar->divider();
		$acyToolbar->help('statistics');

		$acyToolbar->display();

		$filters = new stdClass();
		$this->assignRef('filters', $filters);
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('app', $app);
		$this->assignRef('dropdownFilters', $dropdownFilters);
	}

	function detaillisting(){

		JHTML::_('behavior.modal', 'a.modal');
		$doc = JFactory::getDocument();


		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$helperPhone = ACYSMS::get('helper.phone');
		$filters = new stdClass();
		$paramBase = ACYSMS_COMPONENT.'.d'.$this->getName();




		$selectedStatus = $app->getUserStateFromRequest($paramBase."messageStatus", 'messageStatus', '', 'string');

		$selectedMessage = $app->getUserStateFromRequest($paramBase."filter_message", 'filter_message', 0, 'int');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'message_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$messageClass = ACYSMS::get('class.message');
		$message_id = intval($selectedMessage);
		if(empty($message_id)) return false;


		$message = $messageClass->get($message_id);

		$integration = ACYSMS::getIntegration($message->message_receiver_table);

		$integrationType = ACYSMS::get('type.messagestatus');
		$integrationType->messageId = $message_id;
		$integrationType->load();
		$filters->messageStatus = $integrationType->display('messageStatus', $selectedStatus);

		if(JRequest::getString('tmpl') == 'component'){
			$messageClass = ACYSMS::get('class.message');
			$this->assign('selectedMessage', $messageClass->get($message_id));
		}



		$queryFilters = array();
		if($selectedStatus !== '') $queryFilters[] = 'stats.statsdetails_status = '.intval($selectedStatus);

		if(!empty($message_id)) $queryFilters[] = 'stats.statsdetails_message_id = '.intval($message_id);

		$queryConditions = new stdClass();
		$queryConditions->where = $queryFilters;
		$queryConditions->order = $pageInfo->filter->order;
		$queryConditions->limit = empty($pageInfo->limit->value) ? 500 : $pageInfo->limit->value;
		$queryConditions->offset = $pageInfo->limit->start;

		$queryUser = $integration->getStatDetailsQuery($queryConditions, $pageInfo->search);
		$db->setQuery($queryUser->query);
		$rows = $db->loadObjectList();


		$pageInfo->elements->total = $queryUser->count;
		$pageInfo->elements->page = count($rows);
		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);


		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_STATS_DETAILED'), $this->icon, $this->ctrl);

			$acyToolbar->link(ACYSMS::completeLink('stats'), JText::_('SMS_STATS'), 'stats');
			$acyToolbar->link(ACYSMS::completeLink('stats&task=exportStatsDetails&message_id='.$message_id), JText::_('SMS_EXPORT'), 'export');

			$acyToolbar->divider();
			$acyToolbar->help('statistics');

			$acyToolbar->display();
		}

		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('helperPhone', $helperPhone);
		$this->assignRef('integration', $integration);
		$this->assignRef('filters', $filters);
	}

	function diagram(){

		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'frontendedition.css');

		$messageId = JRequest::getInt('message_id');
		if(empty($messageId)) return;

		$db = JFactory::getDBO();
		$db->setQuery('SELECT COUNT(*) AS total, statsdetails_status FROM '.ACYSMS::table('statsdetails').' WHERE statsdetails_message_id = '.intval($messageId).' GROUP BY `statsdetails_status`');
		$messageStats = $db->loadObjectList('statsdetails_status');

		$db->setQuery('SELECT min(statsdetails_sentdate) AS minval, max(statsdetails_sentdate) AS maxval FROM '.ACYSMS::table('statsdetails').' WHERE statsdetails_sentdate > 0');
		$datesInterval = $db->loadObject();

		$minDate = $datesInterval->minval;
		$maxDate = $datesInterval->maxval;

		$delay = ($maxDate - $minDate);

		if($delay < 3600){
			$groupingdate = "DATE_FORMAT(FROM_UNIXTIME(statsdetails_sentdate),'%Y-%m-%d %H:%i')";
			$dateformat = '%d %B %Y %H:%M';
		}//Less than 72h ? We dsplay it in hours
		else if($delay < 259200){
			$groupingdate = "DATE_FORMAT(FROM_UNIXTIME(statsdetails_sentdate),'%Y-%m-%d %H:00')";
			$dateformat = '%d %B %Y %H:00';
		}//Less than two months ? We display it in days
		else if($delay < 5259600){
			$groupingdate = "DATE_FORMAT(FROM_UNIXTIME(statsdetails_sentdate),'%Y-%m-%d')";
			$dateformat = '%d %B %Y';
		}//Less than 6 months ? We display it in months
		else if($delay < 15778800){
			$groupingdate = "DATE_FORMAT(FROM_UNIXTIME(statsdetails_sentdate),'%Y-%m-01')";
			$dateformat = '%B %Y';
		}else{
			$groupingdate = "DATE_FORMAT(FROM_UNIXTIME(statsdetails_sentdate),'%Y-01-01')";
			$dateformat = '%Y';
		}

		$groupingtype[] = "DATE_FORMAT(FROM_UNIXTIME(statsdetails_sentdate),'%Y')";
		$dateformat = str_replace('%Y', '', $dateformat);
		$groupingdate = str_replace('%Y', '2000', $groupingdate);

		$fieldtype = empty($groupingtype) ? "'Total'" : "CONCAT('Total - ',".implode(", ' - ' ,", $groupingtype).")";

		$query = "SELECT COUNT(*) AS total, ".$groupingdate." AS groupingdate, ".$fieldtype." AS groupingtype FROM ".ACYSMS::table('statsdetails')." WHERE statsdetails_message_id = ".intval($messageId)." GROUP BY groupingdate ORDER BY groupingdate ASC";
		$db->setQuery($query);
		$msgSentByDate = $db->loadObjectList();


		$messageStatus = array(0 => 'nbFailed', 1 => 'nbSent', 2 => 'nbAcceptedByTheGateway', 3 => 'nbSentToOperator', 4 => 'nbBuffered', 5 => 'nbDelivered', -1 => 'nbNotDelivered', -2 => 'nbTimedOut', -3 => 'nbBlocked', -99 => 'nbUnknowError');

		$messageStat = new stdClass();

		$messageClass = ACYSMS::get('class.message');
		$message = $messageClass->get($messageId);
		$messageStat->message = $message;

		$messageStat->totalSent = 0;
		foreach($messageStatus as $oneStatus => $oneDescription){
			$messageStat->$oneDescription = empty($messageStats[$oneStatus]) ? 0 : $messageStats[$oneStatus]->total;
			$messageStat->totalSent += $messageStat->$oneDescription;
		}

		ACYSMS::setPageTitle($message->message_subject);

		$db->setQuery('SELECT COUNT(*) FROM `#__acysms_queue` WHERE `queue_message_id` = '.intval($messageId).' GROUP BY `queue_message_id`');
		$messageStat->queue = $db->loadResult();

		$this->assignRef('ctrl', $this->ctrl);
		$this->assignRef('config', ACYSMS::config());
		$this->assignRef('messageStat', $messageStat);
		$this->assignRef('msgSentByDate', $msgSentByDate);
		$this->assignRef('dateformat', $dateformat);
	}
}
