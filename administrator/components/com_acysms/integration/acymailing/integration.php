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

class ACYSMSIntegration_acymailing_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__acymailing_subscriber';

	var $componentName = 'acymailing';

	var $displayedName = 'AcyMailing';

	var $primaryField = 'subid';

	var $nameField = 'name';

	var $emailField = 'email';

	var $joomidField = 'userid';

	var $editUserURL = 'index.php?option=com_acymailing&ctrl=subscriber&task=edit&cid[]=';

	var $addUserURL = 'index.php?option=com_acymailing&ctrl=subscriber&task=add';

	var $tableAlias = 'acymailingsubscribers';

	var $useJoomlaName = 0;

	var $integrationType = 'communityIntegration';

	public function getPhoneField(){

		$db = JFactory::getDBO();

		$query = 'SELECT  namekey as "column", fieldname as "name" FROM `#__acymailing_fields` WHERE `type` IN ("phone","text") AND namekey NOT IN ("name","email")';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('acymailingsubscribers.name', 'acymailingsubscribers.email', 'acymailingsubscribers.subid', 'acymailingsubscribers.`'.ACYSMS::secureField($config->get('acymailing_field')).'`');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT acymailingsubscribers.*, acymailingsubscribers.subid as receiver_id, acymailingsubscribers.name as receiver_name, acymailingsubscribers.email as receiver_email, acymailingsubscribers.'.ACYSMS::secureField($config->get('acymailing_field')).' as receiver_phone
				FROM #__acymailing_subscriber as acymailingsubscribers';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(acymailingsubscribers.subid) FROM #__acymailing_subscriber as acymailingsubscribers';
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

		$queryConditions->where[] = 'statsdetails_receiver_table = "acymailing"';

		$searchFields = array('acymailingsubscribers.name', 'acymailingsubscribers.email', 'acymailingsubscribers.'.$integrationField, 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, acymailingsubscribers.email as receiver_email, acymailingsubscribers.name as receiver_name, acymailingsubscribers.subid as receiver_id, acymailingsubscribers.'.$integrationField.' as receiver_phone, stats.statsdetails_status as message_status
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						LEFT JOIN #__acymailing_subscriber as acymailingsubscribers ON stats.statsdetails_receiver_id = acymailingsubscribers.subid';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id) FROM '.ACYSMS::table('statsdetails').' as stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						LEFT JOIN #__acymailing_subscriber as acymailingsubscribers ON stats.statsdetails_receiver_id = acymailingsubscribers.subid';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__acymailing_subscriber AS acymailingsubscribers ';
		$acyquery->join['joomusers'] = 'LEFT JOIN #__users AS joomusers ON acymailingsubscribers.userid = joomusers.id';
		$acyquery->where[] = ' acymailingsubscribers.enabled = 1 AND acymailingsubscribers.accept = 1 AND CHAR_LENGTH(acymailingsubscribers.`'.ACYSMS::secureField($config->get('acymailing_field')).'`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acymailing')) return true;
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

		$query = 'SELECT *, `'.ACYSMS::secureField($config->get('acymailing_field')).'` as receiver_phone FROM #__acymailing_subscriber WHERE subid IN ("'.implode('","', $userId).'")';
		$db->setQuery($query);
		$acyUser = $db->loadObjectList('subid');

		if(empty($acyUser)) return false;

		$query = 'SELECT joomusers.* FROM #__acymailing_subscriber as acymailingsubscribers
									 JOIN #__users as joomusers
									 ON joomusers.id = acymailingsubscribers.userid
									 WHERE acymailingsubscribers.subid IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($acyUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->acymailing = $acyUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->acymailing->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->acymailing->name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->acymailing->email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->acymailing->userid) && !empty($joomuserArray[$queueMessage[$messageID]->acymailing->userid])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->acymailing->userid];
			}
		}
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT subid FROM #__acymailing_subscriber WHERE userid IN ('.implode(',', $userIDs).') AND subid > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT userid FROM #__acymailing_subscriber WHERE subid IN ('.implode(',', $userIDs).')';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();

		if(!$isFront){
			$query = 'SELECT name , subid AS receiverId
				FROM #__acymailing_subscriber
				WHERE name LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		}else{

			$query = 'SELECT DISTINCT acymailingUser2.name, acymailingUser2.subid AS receiverId

				FROM #__acymailing_subscriber AS acymailingUser
				JOIN #__acymailing_list AS acymailingList
				ON acymailingUser.userid = acymailingList.userid

				JOIN #__acymailing_listsub AS acymailingListSub
				ON acymailingListSub.listid = acymailingList.listid

				JOIN #__acymailing_subscriber AS acymailingUser2
				ON acymailingUser2.subid = acymailingListSub.subid

				WHERE acymailingUser2.name LIKE '.$db->Quote('%'.$name.'%').'
				AND acymailingList.userid = '.intval($receiverId).'
				LIMIT 10';
		}
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
