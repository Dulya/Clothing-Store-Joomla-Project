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

class ACYSMSIntegration_joomlausers_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__users';

	var $componentName = 'joomlausers';

	var $displayedName = 'Joomla User';

	var $primaryField = 'id';

	var $nameField = 'name';

	var $emailField = 'email';

	var $joomidField = 'id';

	var $editUserURL = 'index.php?option=com_users&task=user.edit&id=';

	var $addUserURL = 'index.php?option=com_users&view=user&layout=edit';

	var $tableAlias = 'joomusers';

	var $useJoomlaName = 0;

	var $integrationType = 'communityIntegration';

	public function getPhoneField(){

		$db = JFactory::getDBO();

		$query = 'SELECT enabled FROM #__extensions WHERE type="plugin"	 AND element="profile" AND folder="user"';
		$db->setQuery($query);
		$result = $db->loadResult();
		if(!$result){
			return array();
		}else return array('phone' => 'phone');
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('joomusers.name', 'joomusers.email', 'joomusers.id', 'joomuserprofile.profile_value');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT joomusers.*, joomusers.id as receiver_id, joomusers.name as receiver_name, joomusers.email as receiver_email, joomuserprofile.profile_value as receiver_phone
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__user_profiles as joomuserprofile ON joomusers.id = joomuserprofile.user_id
				WHERE joomuserprofile.profile_key = "profile.'.ACYSMS::secureField($config->get('joomlausers_field')).'"';
		if(!empty($filters)){
			$query .= ' AND ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(joomusers.id)
						FROM '.ACYSMS::table('users', false).' AS joomusers
						JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
						WHERE joomuserprofile.profile_key = "profile.'.ACYSMS::secureField($config->get('joomlausers_field')).'"';
		if(!empty($filters)){
			$queryCount .= ' AND ('.implode(') AND (', $filters).')';
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

		$queryConditions->where[] = 'statsdetails_receiver_table = "joomlausers"';

		$searchFields = array('joomusers.name', 'joomusers.email', 'joomuserprofile.profile_key', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, joomusers.email as receiver_email, joomusers.name as receiver_name, joomuserprofile.profile_key as receiver_phone, stats.statsdetails_status as message_status, joomusers.id as receiver_id
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						JOIN '.ACYSMS::table('users', false).' AS joomusers	ON stats.statsdetails_receiver_id = joomusers.id
						JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
						WHERE joomuserprofile.profile_key = "profile.'.ACYSMS::secureField($config->get('joomlausers_field')).'"';


		$query .= ' AND ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						JOIN '.ACYSMS::table('users', false).' AS joomusers	ON stats.statsdetails_receiver_id = joomusers.id
						JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
						WHERE joomuserprofile.profile_key = "profile.'.ACYSMS::secureField($config->get('joomlausers_field')).'"';

		$queryCount .= ' AND ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__users AS joomusers';
		$acyquery->join['joomuserprofile'] = 'LEFT JOIN #__user_profiles AS joomuserprofile ON joomusers.id = joomuserprofile.user_id';
		$acyquery->where[] = 'joomuserprofile.profile_key = "profile.'.ACYSMS::secureField($config->get('joomlausers_field')).'"';
		$acyquery->where[] = 'joomusers.block=0 AND CHAR_LENGTH(joomuserprofile.profile_key) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(!ACYSMS_J16){
			return false;
		}
		return true;
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

		$query = 'SELECT joomusers.id as receiver_id, joomuserprofile.profile_value as receiver_phone, joomusers.*, joomuserprofile.profile_key
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE profile_key = "profile.'.ACYSMS::secureField($config->get('joomlausers_field')).'" AND joomusers.id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');

		if(empty($joomuserArray)) return false;


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($joomuserArray[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->joomlauser = $joomuserArray[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->joomla = $joomuserArray[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->joomlauser->receiver_phone;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomlauser->email;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->joomlauser->name;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;
		}
	}

	public function getQueueListingQuery($filters, $order){
		$result = new stdClass();
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$filters[] = 'joomuserprofile.profile_key = "profile.'.ACYSMS::secureField($config->get('joomlausers_field')).'"';


		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$filters[] = ' message.message_userid = '.intval($my->id);
		}

		$query = 'SELECT queue.*, queue.queue_priority as queue_priority, queue.queue_try as queue_try, queue.queue_senddate as queue_senddate, message.message_subject as message_subject, joomusers.name as receiver_name, joomuserprofile.profile_value as receiver_phone, joomusers.id as receiver_id
				FROM '.ACYSMS::table('queue').' AS queue
				LEFT JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON queue.queue_receiver_id = joomusers.id
				JOIN #__user_profiles AS joomuserprofile	ON joomusers.id = joomuserprofile.user_id';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';


		$queryCount = 'SELECT COUNT(queue.queue_message_id)
				FROM '.ACYSMS::table('queue').' AS queue
				LEFT JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON queue.queue_receiver_id = joomusers.id
				JOIN #__user_profiles AS joomuserprofile	ON joomusers.id = joomuserprofile.user_id';
		if(!empty($filters)) $queryCount .= ' WHERE('.implode(') AND (', $filters).')';

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
			$db->setQuery('SELECT joomusers.id as receiver_id, joomusers.name as receiver_name, joomuserprofile.profile_value as receiver_phone
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE joomuserprofile.profile_key = "profile.'.$integrationPhoneField.'"
				AND joomuserprofile.profile_value = '.$db->Quote($phoneNumberToSearch).' OR joomuserprofile.profile_value LIKE '.$db->Quote('%'.$phoneNumberToSearch));

			$informations = $db->loadObject();
			return $informations;
		}
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$query = 'SELECT joomusers.name AS name, joomusers.id AS receiverId
				FROM '.ACYSMS::table('users', false).' AS joomusers
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE name LIKE '.$db->Quote('%'.$name.'%').'
				AND joomuserprofile.profile_key = "profile.'.ACYSMS::secureField($config->get('joomlausers_field')).'"
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
