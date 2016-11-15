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

class ACYSMSIntegration_seblod_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__users';

	var $componentName = 'seblod';

	var $displayedName = 'Seblod';

	var $primaryField = 'id';

	var $nameField = 'name';

	var $emailField = 'email';

	var $joomidField = 'id';

	var $editUserURL = 'index.php?option=com_users&task=user.edit&id=';

	var $addUserURL = 'index.php?option=com_users&view=user&layout=edit';

	var $tableAlias = 'seblodusers';

	var $useJoomlaName = 1;

	var $integrationType = 'communityIntegration';

	public function getPhoneField(){

		$db = JFactory::getDBO();

		$query = 'SELECT DISTINCT seblodfields.storage_field AS "column", seblodfields.title AS name
				FROM #__cck_core_fields AS seblodfields
				JOIN #__cck_core_type_field AS seblodtypefield
				ON seblodfields.id = seblodtypefield.fieldid
				JOIN #__cck_core_types AS seblodtype
				ON seblodtype.id = seblodtypefield.typeid
				WHERE `type` = "text" AND seblodtype.storage_location = "joomla_user" AND seblodfields.name = "user_phone"';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('joomusers.name', 'joomusers.email', 'joomusers.id', 'seblodusers.`'.ACYSMS::secureField($config->get('seblod_field')).'`');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT joomusers.*, joomusers.id AS receiver_id, joomusers.name AS receiver_name, joomusers.email AS receiver_email, seblodusers.`'.ACYSMS::secureField($config->get('seblod_field')).'` AS receiver_phone
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id';

		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(joomusers.id)
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id';
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

		$queryConditions->where[] = 'statsdetails_receiver_table = "seblod"';

		$searchFields = array('joomusers.name', 'joomusers.email', 'seblodusers.'.$integrationField, 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, joomusers.name AS receiver_name, joomusers.email AS receiver_email, seblodusers.'.$integrationField.' AS receiver_phone, stats.statsdetails_status as message_status, joomusers.id as receiver_id
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						JOIN '.ACYSMS::table('users', false).' AS joomusers ON stats.statsdetails_receiver_id = joomusers.id
						JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id';


		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id) FROM '.ACYSMS::table('statsdetails').' as stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						JOIN '.ACYSMS::table('users', false).' AS joomusers ON stats.statsdetails_receiver_id = joomusers.id';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = ACYSMS::table('users', false).' AS joomusers ';
		$acyquery->join['seblodusers'] = 'JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id';
		$acyquery->where[] = 'joomusers.block=0 AND CHAR_LENGTH(seblodusers.`'.ACYSMS::secureField($config->get('seblod_field')).'`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_cck')) return true;
		return false;
	}


	public function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$userId = array();
		$juserid = array();
		$acyUser = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.*, seblodusers.*, seblodcckstoreform.*, joomusers.id AS receiver_id, joomusers.name AS receiver_name, joomusers.email AS receiver_email, seblodusers.`'.ACYSMS::secureField($config->get('seblod_field')).'` AS receiver_phone
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id
				JOIN #__cck_store_form_user AS seblodcckstoreform ON seblodcckstoreform.id = seblodusers.id
				WHERE joomusers.id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($joomuserArray[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->seblod = $joomuserArray[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->joomla = $joomuserArray[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->seblod->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->seblod->name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->seblod->email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;
		}
	}

	public function getQueueListingQuery($filters, $order){
		$result = new stdClass();
		$config = ACYSMS::config();

		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$filters[] = ' message.message_userid = '.intval($my->id);
		}

		$query = 'SELECT queue.*, queue.queue_priority as queue_priority, queue.queue_try as queue_try, queue.queue_senddate as queue_senddate, message.message_subject as message_subject, joomusers.id as receiver_id, joomusers.name as receiver_name, seblodusers.`'.ACYSMS::secureField($config->get('seblod_field')).'` AS receiver_phone
				FROM '.ACYSMS::table('queue').' AS queue
				LEFT JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON queue.queue_receiver_id = joomusers.id
				JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';


		$queryCount = 'SELECT COUNT(queue.queue_message_id)
				FROM '.ACYSMS::table('queue').' AS queue
				LEFT JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON queue.queue_receiver_id = joomusers.id
				JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id';
		if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';


		$result->query = $query;
		$result->queryCount = $queryCount;

		return $result;
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT id FROM #__users WHERE id IN ('.implode(',', $userIDs).') AND id > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();

		return $userIDs;
	}

	public function getInformationsByPhoneNumber($phoneNumber){
		$config = ACYSMS::config();
		$phoneHelper = ACYSMS::get('helper.phone');
		$db = JFactory::getDBO();

		$integrationPhoneField = $config->get($this->componentName.'_field');

		$countryCode = $phoneHelper->getCountryCode($phoneNumber);

		$phoneNumberToSearch = str_replace('+'.$countryCode, '', $phoneNumber);

		if(!empty($integrationPhoneField)){
			$db->setQuery('SELECT joomusers.id as receiver_id, joomusers.name as receiver_name, seblodusers.`'.ACYSMS::secureField($config->get('seblod_field')).'` AS receiver_phone
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id
				WHERE  seblodusers.`'.ACYSMS::secureField($config->get('seblod_field')).'` = '.$db->Quote($phoneNumberToSearch).' OR seblodusers.`'.ACYSMS::secureField($config->get('seblod_field')).' LIKE '.$db->Quote('%'.$phoneNumberToSearch));
			$informations = $db->loadObject();
			return $informations;
		}
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT '.$this->nameField.' AS name, joomusers.'.$this->primaryField.' AS receiverId
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id
				WHERE '.$this->nameField.' LIKE '.$db->Quote('%'.$name.'%').' LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
