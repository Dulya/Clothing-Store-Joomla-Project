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

class ACYSMSIntegration_virtuemart_1_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__vm_user_info';

	var $componentName = 'virtuemart_1';

	var $displayedName = 'VirtueMart 1';

	var $primaryField = 'user_id';

	var $nameField = 'first_name';

	var $emailField = 'email';

	var $joomidField = 'user_id';

	var $editUserURL = 'index.php?page=admin.user_form&option=com_virtuemart&user_id=';

	var $addUserURL = 'index.php?option=com_users&task=user.add';

	var $tableAlias = 'virtuemartusers_1';

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
		$searchFields = array('virtuemartusers_1.first_name', 'virtuemartusers_1.last_name', 'joomusers.email', 'virtuemartusers_1.user_id', 'virtuemartusers_1.'.$config->get('virtuemart_1_field'));
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT virtuemartusers_1.*, virtuemartusers_1.user_id as receiver_id, CONCAT_WS(" ",virtuemartusers_1.first_name,virtuemartusers_1.last_name) as receiver_name, joomusers.email as receiver_email, virtuemartusers_1.'.$config->get('virtuemart_1_field').' as receiver_phone
				FROM #__vm_user_info as virtuemartusers_1
				JOIN '.ACYSMS::table('users', false).' as joomusers
				ON joomusers.id = virtuemartusers_1.user_id';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(virtuemartusers_1.user_id) FROM #__vm_user_info as virtuemartusers_1';
		if(!empty($filters)){
			$queryCount .= ' LEFT JOIN '.ACYSMS::table('users', false).' as joomusers ON virtuemartusers_1.user_id = joomusers.id';
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

		$queryConditions->where[] = 'statsdetails_receiver_table = "virtuemart_1"';

		$searchFields = array('CONCAT_WS(" ",virtuemartusers_1.first_name,virtuemartusers_1.last_name)', 'joomusers.email', 'virtuemartusers_1.'.$config->get('virtuemart_1_field'), 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT statsdetails.*, message.message_id as message_id, statsdetails.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, CONCAT_WS(" ",virtuemartusers_1.first_name,virtuemartusers_1.last_name) as receiver_name, joomusers.email as receiver_email, virtuemartusers_1.'.$config->get('virtuemart_1_field').' as receiver_phone, statsdetails.statsdetails_status as message_status, virtuemartusers_1.user_id as receiver_id
				FROM '.ACYSMS::table('statsdetails').' AS statsdetails
				LEFT JOIN '.ACYSMS::table('message').' AS message ON statsdetails.statsdetails_message_id = message.message_id
				LEFT JOIN #__vm_user_info as virtuemartusers_1 ON statsdetails.statsdetails_receiver_id = virtuemartusers_1.user_id
				LEFT JOIN '.ACYSMS::table('users', false).' AS joomusers ON virtuemartusers_1.user_id = joomusers.id ';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(statsdetails.statsdetails_message_id)
				FROM '.ACYSMS::table('statsdetails').' AS statsdetails
				LEFT JOIN '.ACYSMS::table('message').' AS message ON statsdetails.statsdetails_message_id = message.message_id
				LEFT JOIN #__vm_user_info as virtuemartusers_1 ON statsdetails.statsdetails_receiver_id = virtuemartusers_1.user_id
				LEFT JOIN '.ACYSMS::table('users', false).' AS joomusers ON virtuemartusers_1.user_id = joomusers.id ';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__users as joomusers ';
		$acyquery->join['virtuemartusers_1'] = 'JOIN #__vm_user_info as virtuemartusers_1 ON joomusers.id = virtuemartusers_1.user_id ';
		$acyquery->where[] = 'joomusers.block=0 AND CHAR_LENGTH(virtuemartusers_1.`'.$config->get('virtuemart_1_field').'`) > 3';
		return $acyquery;
	}

	function isPresent(){
		$file = ACYSMS_ROOT.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'version.php';
		if(!file_exists($file)) return false;
		include_once($file);
		$vmversion = new vmVersion();
		if(empty($vmversion->RELEASE)) return false;
		return true;
	}

	function addUsersInformations(&$queueMessage){

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

		$query = 'SELECT *, `'.$config->get('virtuemart_1_field').'` as receiver_phone,  CONCAT_WS(" ",first_name, last_name) as receiver_name FROM #__vm_user_info WHERE user_id IN ("'.implode('","', $userId).'")';
		$db->setQuery($query);
		$virtueMartUser = $db->loadObjectList('user_id');

		if(empty($virtueMartUser)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.* FROM #__vm_user_info as virtuemartusers_1
									 JOIN #__users as joomusers
									 ON joomusers.id = virtuemartusers_1.user_id
									WHERE virtuemartusers_1.user_id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;

			if(empty($virtueMartUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->virtueMart_1 = $virtueMartUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->virtueMart_1->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->virtueMart_1->receiver_name;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->virtueMart_1->user_id) && !empty($joomuserArray[$queueMessage[$messageID]->virtueMart_1->user_id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->virtueMart_1->user_id];
				$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomla->email;
			}
		}
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT CONCAT_WS(" ",virtuemartusers_1.first_name, virtuemartusers_1.last_name) AS name, joomusers.'.$this->primaryField.' AS receiverId
				FROM #__vm_user_info AS virtuemartusers_1
				JOIN '.ACYSMS::table('users', false).' as joomusers
				ON joomusers.id = virtuemartusers_1.user_id
				WHERE virtuemartusers_1.first_name LIKE '.$db->Quote('%'.$name.'%').'
				OR virtuemartusers_1.last_name LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
