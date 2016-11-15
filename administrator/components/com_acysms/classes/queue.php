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

class ACYSMSqueueClass extends ACYSMSClass{
	var $onlynew = false;
	var $mindelay = 0;

	function delete($filters){

		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$filters[] = ' message.message_userid = '.intval($my->id);
		}

		$filter_messagequeue = $app->getUserStateFromRequest("filter_messagequeue", 'filter_messagequeue', '', 'string');
		$filterArray = explode('.', $filter_messagequeue);
		if(!empty($filterArray[0])) $filters[] = ' queue.queue_receiver_table = '.$this->database->Quote($filterArray[0]);
		if(!empty($filterArray[1]) && $filterArray[1] != 'all') $filters[] = ' queue.queue_message_id = '.intval($filterArray[1]);


		$query = 'DELETE queue.* FROM '.ACYSMS::table('queue').' AS queue';
		if(!empty($filters)){
			$query .= ' JOIN '.ACYSMS::table('message').' AS message on queue.queue_message_id = message.message_id';
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}

		$this->database->setQuery($query);
		$this->database->query();
		$nbRecords = $this->database->getAffectedRows();
		if(empty($filters)){
			$this->database->setQuery('TRUNCATE TABLE '.ACYSMS::table('queue'));
			$this->database->query();
		}
		return $nbRecords;
	}


	function queue($message_id, $time){
		$config = ACYSMS::config();
		$messageClass = ACYSMS::get('class.message');
		$acyquery = ACYSMS::get('class.acyquery');

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();

		$message_id = intval($message_id);
		if(empty($message_id)) return false;
		$message = $messageClass->get($message_id);

		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = $integration = ACYSMS::getIntegration($config->get('default_integration'));
		$integration->initQuery($acyquery);

		if(!empty($message->message_receiver['standard']['type'])){
			foreach($message->message_receiver['standard']['type'] as $oneType){
				$dispatcher->trigger('onACYSMSSelectData_'.$oneType, array(&$acyquery, $message));
			}
		}
		$querySelect = $acyquery->getQuery(array('DISTINCT '.$message_id.','.$integration->tableAlias.'.'.$integration->primaryField.' , "'.$integration->componentName.'", '.$time.', '.$config->get('priority_message', 3)));
		$query = 'INSERT IGNORE INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority) '.$querySelect;
		$this->database->setQuery($query);
		if(!$this->database->query()){
			ACYSMS::display($this->database->getErrorMsg(), 'error');
		}
		$totalinserted = $this->database->getAffectedRows();

		return $totalinserted;
	}

	function getNbReceivers($message){
		$integration = ACYSMS::getIntegration($message->message_receiver_table);
		if(empty($message)) return;
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$acyquery = ACYSMS::get('class.acyquery');
		$acyquery = $integration->initQuery($acyquery);
		if(!empty($message->message_receiver['standard']) && isset($message->message_receiver['standard']['type'])){
			foreach($message->message_receiver['standard']['type'] as $oneType){
				$dispatcher->trigger('onACYSMSSelectData_'.$oneType, array(&$acyquery, $message));
			}
		}
		$querySelect = $acyquery->getQuery(array('COUNT(DISTINCT('.$integration->tableAlias.'.'.$integration->primaryField.'))'));
		$this->database->setQuery($querySelect);
		return $this->database->loadResult();
	}


	function getReady($limit, $message_id = 0){
		$query = 'SELECT queue.* FROM #__acysms_queue as queue';
		$query .= ' JOIN #__acysms_message AS message ON queue.queue_message_id = message.message_id';
		$query .= ' WHERE queue.`queue_senddate` <= '.time();
		$query .= ' AND message.`message_type` <>  "draft" AND message.`message_status` <>  "waitingcredits"';
		if(!empty($message_id)) $query .= ' AND queue.`queue_message_id` = '.intval($message_id);
		$query .= ' ORDER BY queue.`queue_priority` ASC, queue.`queue_senddate` ASC, queue.`queue_receiver_id` ASC';
		if(empty($limit)) $limit = 50;
		$query .= ' LIMIT '.JRequest::getInt('startqueue', 0).','.intval($limit);
		$this->database->setQuery($query);
		$results = $this->database->loadObjectList();
		if(empty($results)) return false;
		if($results === null){
			$this->database->setQuery('REPAIR TABLE #__acysms_queue, #__acysms_phone, #__acysms_message');
			$this->database->query();
		}
		$resultsIntegration = array();
		foreach($results as $oneResult){
			if(empty($oneResult->queue_paramqueue)){
				$oneResult->queue_paramqueue = new stdClass();
			}else $oneResult->queue_paramqueue = unserialize($oneResult->queue_paramqueue);
			if(!empty($oneResult->queue_receiver_table)) $resultsIntegration[$oneResult->queue_receiver_table][] = $oneResult;
		}
		$returnInformations = array();
		foreach($resultsIntegration as $oneIntegration => $oneResultIntegration){
			$integration = ACYSMS::getIntegration($oneIntegration);
			$integration->addUsersInformations($resultsIntegration[$oneIntegration]);
			$returnInformations = array_merge($returnInformations, $resultsIntegration[$oneIntegration]);
		}
		return $returnInformations;
	}

	function queueStatus($message_id, $all = false){

		$filters = array();

		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$filters[] = ' message.message_userid = '.intval($my->id);
		}
		if(!$all){
			$filters[] = ' queue.queue_senddate < '.time();
			if(!empty($message_id)) $filters[] = ' queue.queue_message_id = '.intval($message_id);
		}

		$query = 'SELECT queue.queue_message_id, count(queue.queue_receiver_id) as nbsub, min(queue.queue_senddate) as senddate, message.*
					FROM '.ACYSMS::table('queue').' AS queue';
		$query .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		$query .= ' GROUP BY queue.queue_message_id';
		$this->database->setQuery($query);
		$queueStatus = $this->database->loadObjectList('queue_message_id');
		return $queueStatus;
	}

	function getScheduled(){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$query = 'SELECT * FROM '.ACYSMS::table('message').' AS message WHERE message_senddate > 0 AND message_status = "scheduled"';

		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$query .= ' AND message.message_userid = '.intval($my->id);
		}
		$query .= ' ORDER BY  message_senddate ASC ';

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	private function getReadyMessage(){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$query = 'SELECT message_id, message_senddate, message_subject FROM '.ACYSMS::table('message').' AS message
		WHERE message_senddate > 0 AND message_status="scheduled" AND message_senddate <= '.(time() + 1200);

		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			if(!empty($my->id)) $query .= ' AND message.message_userid = '.intval($my->id);
		}

		$query .= ' ORDER BY message_senddate ASC';
		$db->setQuery($query);

		return $db->loadObjectList('message_id');
	}

	function queueScheduled(){
		$this->messages = array();
		$messageReady = $this->getReadyMessage();
		if(empty($messageReady)){
			$this->messages[] = JText::_('SMS_NO_SCHED');
			return false;
		}

		$this->messages[] = JText::sprintf('SMS_NB_SCHED_NEWS', count($messageReady));
		foreach($messageReady as $message_id => $message){
			$nbQueue = $this->queue($message_id, $message->message_senddate);
			$this->messages[] = JText::sprintf('SMS_ADDED_QUEUE_SCHEDULE', $nbQueue, $message_id, '<b><i>'.$message->message_subject.'</i></b>');
		}
		$arrayKeys = array_keys($messageReady);
		JArrayHelper::toInteger($arrayKeys);

		$db = JFactory::getDBO();
		$db->setQuery('UPDATE '.ACYSMS::table('message').' SET message_status = "sent" WHERE message_id IN ('.implode(',', $arrayKeys).')');
		$db->query();
		return true;
	}

	public function plgQueueUpdateSenddate($messageId, $diff){

		$messageClass = ACYSMS::get('class.message');
		$messageUpdated = $messageClass->get($messageId);
		if(empty($messageUpdated->message_id)){
			echo 'Could not load message id '.$messageId;
			exit;
		}
		$db = JFactory::getDBO();
		$query = 'UPDATE #__acysms_queue AS queue ';
		$query .= ' SET queue.`queue_senddate` = queue.`queue_senddate` + '.intval($diff);
		$query .= ' WHERE queue.queue_message_id = '.intval($messageId);
		$db->setQuery($query);
		$db->query();
		$nbupdated = $db->getAffectedRows();
		echo JText::sprintf('SMS_NB_MESSAGES_UPDATED', $nbupdated);
		exit;
	}
}
