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

class ACYSMSIntegration_eventbooking_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__eb_registrants';

	var $componentName = 'eventbooking';

	var $displayedName = 'EventBooking';

	var $primaryField = 'id';

	var $nameField = 'first_name';

	var $emailField = 'email';

	var $joomidField = 'user_id';

	var $editUserURL = 'index.php?option=com_eventbooking&view=registrant&id=';

	var $editUserFrontURL = 'index.php/en/event-booking/edit-registrant?cid[0]=919';

	var $addUserURL = 'index.php?option=com_eventbooking&view=registrants';

	var $addUserFrontURL = 'index.php?option=com_eventbooking&view=registrants';

	var $tableAlias = 'eventbookingusers';

	var $useJoomlaName = 0;

	var $integrationType = 'eventIntegration';

	public function getPhoneField(){

		$db = JFactory::getDBO();

		$query = 'SELECT  title AS "name", name AS "column" FROM `#__eb_fields`';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$searchFields = array('eventbookingusers.first_name', 'eventbookingusers.last_name', 'eventbookingusers.email', 'eventbookingusers.id', 'eventbookingusers.`'.ACYSMS::secureField($config->get('eventbooking_field')).'`');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$filters[] = 'eventbookingusers.published >= 1';

		$query = 'SELECT DISTINCT(eventbookingusers.id) as receiver_id, eventbookingusers.*, CONCAT_WS(" ",eventbookingusers.first_name, eventbookingusers.last_name) as receiver_name, eventbookingusers.email as receiver_email, eventbookingusers.`'.ACYSMS::secureField($config->get('eventbooking_field')).'` as receiver_phone
				FROM #__eb_registrants as eventbookingusers';

		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(eventbookingusers.id) FROM #__eb_registrants as eventbookingusers';
		if(!empty($filters)){
			$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;

		return $result;
	}

	public function getStatDetailsQuery($queryConditions, $search){
		$db = JFactory::getDBO();
		$result = new stdClass();
		$config = ACYSMS::config();
		$integrationField = $config->get($this->componentName.'_field');

		$queryConditions->where[] = 'statsdetails_receiver_table = "eventbooking"';

		$searchFields = array('eventbookingusers.first_name', 'eventbookingusers.last_name', 'eventbookingusers.email', 'eventbookingusers.id', 'eventbookingusers.`'.ACYSMS::secureField($config->get('eventbooking_field')).'`');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, eventbookingusers.email as receiver_email, CONCAT_WS(" ",eventbookingusers.first_name, eventbookingusers.last_name) as receiver_name, eventbookingusers.id as receiver_id, eventbookingusers.'.$integrationField.' as receiver_phone, stats.statsdetails_status as message_status
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						LEFT JOIN #__eb_registrants as eventbookingusers ON stats.statsdetails_receiver_id = eventbookingusers.id';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id) FROM '.ACYSMS::table('statsdetails').' as stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						LEFT JOIN #__eb_registrants as eventbookingusers ON stats.statsdetails_receiver_id = eventbookingusers.id';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__eb_registrants AS eventbookingusers ';
		$acyquery->join['joomusers'] = 'LEFT JOIN #__users AS joomusers ON eventbookingusers.user_id = joomusers.id';
		$acyquery->where[] = ' eventbookingusers.published = 1 AND CHAR_LENGTH(eventbookingusers.`'.ACYSMS::secureField($config->get('eventbooking_field')).'`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_eventbooking')) return true;
		return false;
	}


	public function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$userId = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `'.ACYSMS::secureField($config->get('eventbooking_field')).'` as receiver_phone, eventbookingusers.email as email, CONCAT_WS(" ",eventbookingusers.first_name, eventbookingusers.last_name) as name, user_id as userid FROM #__eb_registrants AS eventbookingusers WHERE id IN ("'.implode('","', $userId).'")';
		$db->setQuery($query);
		$acyUser = $db->loadObjectList('id');

		if(empty($acyUser)) return false;

		$query = 'SELECT joomusers.* FROM #__eb_registrants as eventbookingusers
									 JOIN #__users as joomusers
									 ON joomusers.id = eventbookingusers.user_id
									 WHERE eventbookingusers.id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($acyUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->eventbooking = $acyUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->eventbooking->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->eventbooking->name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->eventbooking->email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->eventbooking->userid) && !empty($joomuserArray[$queueMessage[$messageID]->eventbooking->userid])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->eventbooking->userid];
			}
		}
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT id FROM #__eb_registrants WHERE user_id IN ('.implode(',', $userIDs).') AND id > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT user_id FROM #__eb_registrants WHERE id IN ('.implode(',', $userIDs).')';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();


		$query = 'SELECT CONCAT_WS(" ",eventbookingusers.first_name, eventbookingusers.last_name) AS name, id AS receiverId
				FROM #__eb_registrants AS eventbookingusers
				WHERE last_name LIKE '.$db->Quote('%'.$name.'%').' 
				OR first_name LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';

		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
