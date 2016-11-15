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

class ACYSMSIntegration_virtuemart_2_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__virtuemart_userinfos';

	var $componentName = 'virtuemart_2';

	var $displayedName = 'VirtueMart 2';

	var $primaryField = 'virtuemart_userinfo_id';

	var $nameField = 'first_name';

	var $emailField = 'email';

	var $joomidField = 'virtuemart_user_id';

	var $editUserURL = 'index.php?option=com_virtuemart&view=user&task=edit&virtuemart_user_id[]=';

	var $addUserURL = 'index.php?option=com_users&task=user.add';

	var $tableAlias = 'virtuemartusers_2';

	var $useJoomlaName = 0;

	var $integrationType = 'ecommerceIntegration';

	public function getPhoneField(){
		$tableFields = array();

		$oneField = new stdClass();
		$oneField->name = $oneField->column = 'phone_1';
		$tableFields[] = $oneField;

		$oneField = new stdClass();
		$oneField->name = $oneField->column = 'phone_2';
		$tableFields[] = $oneField;

		return $tableFields;
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$user = JFactory::getUser();
		$searchFields = array('virtuemartusers_2.first_name', 'virtuemartusers_2.last_name', 'joomusers.email', 'virtuemartusers_2.virtuemart_userinfo_id', 'virtuemartusers_2.'.$config->get('virtuemart_2_field'));
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT virtuemartusers_2.*, virtuemartusers_2.virtuemart_userinfo_id as receiver_id, CONCAT_WS(" ",virtuemartusers_2.first_name,virtuemartusers_2.last_name) as receiver_name, joomusers.email as receiver_email, virtuemartusers_2.'.$config->get('virtuemart_2_field').' as receiver_phone
				FROM #__virtuemart_userinfos as virtuemartusers_2
				JOIN '.ACYSMS::table('users', false).' as joomusers ON joomusers.id = virtuemartusers_2.virtuemart_user_id';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(virtuemartusers_2.virtuemart_userinfo_id) FROM #__virtuemart_userinfos as virtuemartusers_2';
		if(!empty($filters)){
			$queryCount .= ' JOIN '.ACYSMS::table('users', false).' as joomusers ON virtuemartusers_2.virtuemart_userinfo_id = joomusers.id';
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

		$queryConditions->where[] = 'statsdetails_receiver_table = "virtuemart_2"';

		$searchFields = array('CONCAT_WS(" ",virtuemartusers_2.first_name,virtuemartusers_2.last_name)', 'joomusers.email', 'virtuemartusers_2.'.$config->get('virtuemart_2_field'), 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, CONCAT_WS(" ",virtuemartusers_2.first_name,virtuemartusers_2.last_name) as receiver_name, joomusers.email as receiver_email, virtuemartusers_2.'.$config->get('virtuemart_2_field').' as receiver_phone, stats.statsdetails_status as message_status, virtuemartusers_2.virtuemart_userinfo_id as receiver_id
				FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
				LEFT JOIN #__virtuemart_userinfos as virtuemartusers_2 ON stats.statsdetails_receiver_id = virtuemartusers_2.virtuemart_userinfo_id
				JOIN '.ACYSMS::table('users', false).' AS joomusers ON virtuemartusers_2.virtuemart_user_id = joomusers.id ';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
				FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
				LEFT JOIN #__virtuemart_userinfos as virtuemartusers_2 ON stats.statsdetails_receiver_id = virtuemartusers_2.virtuemart_userinfo_id
				JOIN '.ACYSMS::table('users', false).' AS joomusers ON virtuemartusers_2.virtuemart_user_id = joomusers.id ';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__users as joomusers ';
		$acyquery->join['virtuemartusers_2'] = 'JOIN #__virtuemart_userinfos as virtuemartusers_2 ON joomusers.id = virtuemartusers_2.virtuemart_user_id ';
		$acyquery->where[] = 'joomusers.block=0 AND CHAR_LENGTH(virtuemartusers_2.`'.$config->get('virtuemart_2_field').'`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		$file = ACYSMS_ROOT.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'version.php';
		if(!file_exists($file)) return false;
		include_once($file);
		$vmversion = new vmVersion();
		if(empty($vmversion->RELEASE)) return true;
		return false;
	}

	public function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$userId = array();
		$juserid = array();
		$virtueMartUser = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `'.$config->get('virtuemart_2_field').'` as receiver_phone,  CONCAT_WS(" ",first_name, last_name) as receiver_name FROM #__virtuemart_userinfos WHERE virtuemart_userinfo_id IN ("'.implode('","', $userId).'")';
		$db->setQuery($query);
		$virtueMartUser = $db->loadObjectList('virtuemart_userinfo_id');

		if(empty($virtueMartUser)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.* FROM #__virtuemart_userinfos as virtuemartusers_2
									 JOIN #__users as joomusers
									 ON joomusers.id = virtuemartusers_2.virtuemart_user_id
									WHERE virtuemartusers_2.virtuemart_userinfo_id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;

			if(empty($virtueMartUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->virtueMart = $virtueMartUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->virtueMart->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->virtueMart->receiver_name;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->virtueMart->virtuemart_user_id) && !empty($joomuserArray[$queueMessage[$messageID]->virtueMart->virtuemart_user_id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->virtueMart->virtuemart_user_id];
				$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomla->email;
			}
		}
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT virtuemart_userinfo_id FROM #__virtuemart_userinfos WHERE virtuemart_user_id IN ('.implode(',', $userIDs).') AND virtuemart_userinfo_id > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT virtuemart_user_id FROM #__virtuemart_userinfos WHERE virtuemart_userinfo_id IN ('.implode(',', $userIDs).')';
		$db->setQuery($query);

		return acysms_loadResultArray($db);
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT CONCAT_WS(" ",virtuemartusers_2.first_name, virtuemartusers_2.last_name) AS name,  virtuemartusers_2.virtuemart_userinfo_id AS receiverId
				FROM #__virtuemart_userinfos AS virtuemartusers_2
				JOIN '.ACYSMS::table('users', false).' as joomusers
				ON virtuemartusers_2.virtuemart_user_id = joomusers.id
				WHERE virtuemartusers_2.first_name LIKE '.$db->Quote('%'.$name.'%').'
				OR virtuemartusers_2.last_name LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
