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

class ACYSMSIntegration_communitybuilder_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__comprofiler';

	var $componentName = 'communitybuilder';

	var $displayedName = 'Community Builder';

	var $primaryField = 'id';

	var $nameField = 'CONCAT_WS(" ",firstname, lastname)';

	var $emailField = 'email';

	var $joomidField = 'user_id';

	var $editUserURL = 'index.php?option=com_comprofiler&task=edit&cid[]=';

	var $addUserURL = 'index.php?option=com_comprofiler&task=new';

	var $tableAlias = 'comprofiler';

	var $useJoomlaName = 1;

	var $integrationType = 'communityIntegration';

	public function getPhoneField(){

		$db = JFactory::getDBO();

		try{
			$query = 'SELECT  name as "name", name as "column" FROM `#__comprofiler_fields` WHERE `type` = "text" OR `type` ="integer"';
			$db->setQuery($query);
			$res = $db->loadObjectList();
		}catch(Exception $e){
			$res = null;
		}

		if($res === null){
			ACYSMS::display(isset($e) ? print_r($e, true) : substr(strip_tags($this->db->getErrorMsg()), 0, 200).'...', 'error');
		}
		return $res;
	}


	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$result = new stdClass();

		$searchFields = array('comprofiler.firstname', 'comprofiler.lastname', 'comprofiler.middlename', 'joomusers.id', 'comprofiler.`'.ACYSMS::secureField($config->get('communitybuilder_field')).'`');

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT comprofiler.*, joomusers.id as receiver_id, joomusers.name as receiver_name, joomusers.email as receiver_email, comprofiler.`'.ACYSMS::secureField($config->get('communitybuilder_field')).'` as receiver_phone
				FROM #__comprofiler as comprofiler
				JOIN '.ACYSMS::table('users', false).' as joomusers ON joomusers.id = comprofiler.user_id';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(comprofiler.user_id) FROM #__comprofiler as comprofiler';
		$queryCount .= ' JOIN '.ACYSMS::table('users', false).' as joomusers ON comprofiler.user_id = joomusers.id';
		if(!empty($filters)){
			$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;

		return $result;
	}

	function getStatDetailsQuery($queryConditions, $search){
		$db = JFactory::getDBO();
		$result = new stdClass();
		$config = ACYSMS::config();

		$queryConditions->where[] = 'statsdetails_receiver_table = "communitybuilder"';

		$searchFields = array('joomusers.name', 'joomusers.email', 'comprofiler.`'.ACYSMS::secureField($config->get('communitybuilder_field')).'`', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, joomusers.name as receiver_name, joomusers.email as receiver_email, joomusers.id as receiver_id, comprofiler.`'.ACYSMS::secureField($config->get('communitybuilder_field')).'` as receiver_phone, stats.statsdetails_status as message_status
				FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
				LEFT JOIN '.ACYSMS::table('users', false).' AS joomusers ON stats.statsdetails_receiver_id = joomusers.id
				JOIN  #__comprofiler as comprofiler ON joomusers.id = comprofiler.user_id';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;


		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN '.ACYSMS::table('users', false).' AS joomusers ON stats.statsdetails_receiver_id = joomusers.id
					JOIN  #__comprofiler as comprofiler ON joomusers.id = comprofiler.user_id';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__users as joomusers ';
		$acyquery->join['comprofiler'] = ' LEFT JOIN #__comprofiler as comprofiler ON joomusers.id = comprofiler.id ';
		$acyquery->where[] = 'joomusers.block=0 AND CHAR_LENGTH(comprofiler.`'.ACYSMS::secureField($config->get('communitybuilder_field')).'`) > 3';
		return $acyquery;
	}

	function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_comprofiler')) return true;
		return false;
	}

	function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$userId = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `'.ACYSMS::secureField($config->get('communitybuilder_field')).'` as receiver_phone,  CONCAT_WS(" ",firstname, lastname) as receiver_name FROM #__comprofiler WHERE id IN ("'.implode('","', $userId).'")';
		$db->setQuery($query);
		$cbUser = $db->loadObjectList('id');

		if(empty($cbUser)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.*
				FROM #__users as joomusers
				WHERE id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($cbUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->communityBuilder = $cbUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->communityBuilder->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->communityBuilder->receiver_name;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->communityBuilder->id) && !empty($joomuserArray[$queueMessage[$messageID]->communityBuilder->id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->communityBuilder->id];
				$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomla->email;
			}
		}
	}

	function getQueueListingQuery($filters, $order){
		$result = new stdClass();
		$config = ACYSMS::config();
		$integrationField = $config->get($this->componentName.'_field');

		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$filters[] = ' message.message_userid = '.intval($my->id);
		}

		$query = 'SELECT queue.*, queue.queue_priority as queue_priority, queue.queue_try as queue_try, queue.queue_senddate as queue_senddate, message.message_subject as message_subject, CONCAT_WS(" ",receiver.firstname, receiver.lastname) as receiver_name, receiver.'.$integrationField.' as receiver_phone, receiver.id as receiver_id';
		$query .= ' FROM '.ACYSMS::table('queue').' AS queue';
		$query .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';
		$query .= ' JOIN #__comprofiler AS receiver ON receiver.id = queue.queue_receiver_id';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		$query .= ' ORDER BY '.$order->value.' '.$order->dir.', queue.`queue_receiver_id` ASC';


		$queryCount = 'SELECT COUNT(queue.queue_message_id)';
		$queryCount .= ' FROM '.ACYSMS::table('queue').' AS queue';
		$queryCount .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';
		if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
		$queryCount .= ' ORDER BY '.$order->value.' '.$order->dir.', queue.`queue_receiver_id` ASC';

		$result->query = $query;
		$result->queryCount = $queryCount;
		return $result;
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT joomusers.name AS name, joomusers.'.$this->primaryField.' AS receiverId
				FROM #__comprofiler as comprofiler
				JOIN '.ACYSMS::table('users', false).' as joomusers ON joomusers.id = comprofiler.user_id
				WHERE firstname LIKE '.$db->Quote('%'.$name.'%').'
				OR lastname LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
