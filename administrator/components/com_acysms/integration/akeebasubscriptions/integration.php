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

class ACYSMSIntegration_akeebasubscriptions_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__akeebasubs_users';

	var $componentName = 'akeebasubscriptions';

	var $displayedName = 'Akeeba Subscription';

	var $primaryField = 'akeebasubs_user_id';

	var $nameField = 'name';

	var $emailField = 'email';

	var $joomidField = 'user_id';

	var $editUserURL = 'index.php?option=com_users&task=user.edit&id=';

	var $addUserURL = 'index.php?option=com_akeebasubs&view=users&task=add';

	var $tableAlias = 'akeebasubscriptions';

	var $useJoomlaName = 0;

	var $integrationType = 'communityIntegration';

	public function getPhoneField(){

		$db = JFactory::getDBO();
		$query = 'SELECT  title as "name", slug as "column" FROM `#__akeebasubs_customfields` WHERE `type` = "text"';
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('joomusers.name', 'joomusers.email', 'akeebasubscriptions.akeebasubs_user_id', 'joomuserprofile.profile_value');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT akeebasubscriptions.*, akeebasubscriptions.akeebasubs_user_id as receiver_id, joomusers.name as receiver_name, joomusers.email as receiver_email, joomuserprofile.profile_value as receiver_phone
				FROM #__akeebasubs_users AS akeebasubscriptions
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
				JOIN #__user_profiles as joomuserprofile ON joomusers.id = joomuserprofile.user_id
				WHERE joomuserprofile.profile_key = "akeebasubs.`'.ACYSMS::secureField($config->get('akeebasubscriptions_field')).'`"';
		if(!empty($filters)){
			$query .= ' AND ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(akeebasubscriptions.akeebasubs_user_id) FROM #__akeebasubs_users as akeebasubscriptions
					JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
					JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
					WHERE joomuserprofile.profile_key = "akeebasubs.`'.ACYSMS::secureField($config->get('akeebasubscriptions_field')).'`"';
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

		$queryConditions->where[] = 'statsdetails_receiver_table = "akeebasubscriptions"';

		$searchFields = array('joomusers.name', 'joomusers.email', 'joomuserprofile.profile_key', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, joomusers.email as receiver_email, akeebasubscriptions.akeebasubs_user_id as receiver_id, joomusers.name as receiver_name, joomuserprofile.profile_key as receiver_phone, stats.statsdetails_status as message_status
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						LEFT JOIN #__akeebasubs_users as akeebasubscriptions ON stats.statsdetails_receiver_id = akeebasubscriptions.akeebasubs_user_id
						JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
						JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
						WHERE joomuserprofile.profile_key = "akeebasubs.`'.ACYSMS::secureField($config->get('akeebasubscriptions_field')).'`"';


		$query .= ' AND ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
						FROM '.ACYSMS::table('statsdetails').' AS stats
						LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
						LEFT JOIN #__akeebasubs_users as akeebasubscriptions ON stats.statsdetails_receiver_id = akeebasubscriptions.akeebasubs_user_id
						JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
						JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
						WHERE joomuserprofile.profile_key = "akeebasubs.`'.ACYSMS::secureField($config->get('akeebasubscriptions_field')).'`"';

		$queryCount .= ' AND ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__akeebasubs_users AS akeebasubscriptions ';
		$acyquery->join['joomusers'] = 'LEFT JOIN #__users as joomusers ON akeebasubscriptions.user_id = joomusers.id';
		$acyquery->join['joomuserprofile'] = 'LEFT JOIN #__user_profiles AS joomuserprofile ON joomusers.id = joomuserprofile.user_id';
		$acyquery->where[] = 'joomuserprofile.profile_key = "akeebasubs.`'.ACYSMS::secureField($config->get('akeebasubscriptions_field')).'`"';
		$acyquery->where[] = 'CHAR_LENGTH(joomuserprofile.profile_key) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_akeebasubs')) return true;
		return false;
	}


	public function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$userId = array();
		$juserid = array();
		$akeebaUser = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT akeebasubscriptions.akeebasubs_user_id as receiver_id, joomuserprofile.profile_value as receiver_phone, akeebasubscriptions.*, joomuserprofile.profile_key
				FROM #__akeebasubs_users as akeebasubscriptions
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id
				WHERE profile_key = "akeebasubs.`'.ACYSMS::secureField($config->get('akeebasubscriptions_field')).'`" AND akeebasubscriptions.akeebasubs_user_id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$akeebaUser = $db->loadObjectList('akeebasubs_user_id');

		if(empty($akeebaUser)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.* FROM #__akeebasubs_users as akeebasubscriptions
									 JOIN #__users as joomusers
									 ON joomusers.id = akeebasubscriptions.user_id
									 WHERE akeebasubscriptions.akeebasubs_user_id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($akeebaUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->akeebasubscriptions = $akeebaUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->akeebasubscriptions->receiver_phone;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->akeebasubscriptions->user_id) && !empty($joomuserArray[$queueMessage[$messageID]->akeebasubscriptions->user_id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->akeebasubscriptions->user_id];
				$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomla->email;
				$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->joomla->name;
			}
		}
	}

	public function getQueueListingQuery($filters, $order){
		$result = new stdClass();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$filters[] = 'joomuserprofile.profile_key = "akeebasubs.'.ACYSMS::secureField($config->get('akeebasubscriptions_field')).'"';

		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$filters[] = ' message.message_userid = '.intval($my->id);
		}

		$query = 'SELECT queue.*, queue.queue_priority as queue_priority, queue.queue_try as queue_try, queue.queue_senddate as queue_senddate, message.message_subject as message_subject, joomusers.name as receiver_name, joomuserprofile.profile_value as receiver_phone, akeebasubscriptions.akeebasubs_user_id as receiver_id
				FROM '.ACYSMS::table('queue').' AS queue
				LEFT JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id
				LEFT JOIN #__akeebasubs_users as akeebasubscriptions ON queue.queue_receiver_id = akeebasubscriptions.akeebasubs_user_id
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';


		$queryCount = 'SELECT COUNT(queue.queue_message_id)
				FROM '.ACYSMS::table('queue').' AS queue
				LEFT JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id
				LEFT JOIN #__akeebasubs_users as akeebasubscriptions ON queue.queue_receiver_id = akeebasubscriptions.akeebasubs_user_id
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
				JOIN #__user_profiles as joomuserprofile	ON joomusers.id = joomuserprofile.user_id';
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

		$query = 'SELECT akeebasubs_user_id FROM #__akeebasubs_users WHERE user_id IN ('.implode(',', $userIDs).') AND akeebasubs_user_id > 0';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT user_id FROM #__akeebasubs_users WHERE akeebasubs_user_id IN ('.implode(',', $userIDs).')';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}

	public function getInformationsByPhoneNumber($phoneNumber){
		$config = ACYSMS::config();
		$phoneHelper = ACYSMS::get('helper.phone');
		$db = JFactory::getDBO();

		$integrationPhoneField = $config->get($this->componentName.'_field');

		$countryCode = $phoneHelper->getCountryCode($phoneNumber);

		$phoneNumberToSearch = str_replace('+'.$countryCode, '', $phoneNumber);

		if(!empty($integrationPhoneField)){
			$db->setQuery('SELECT akeebasubscriptions.akeebasubs_user_id as receiver_id, joomusers.name as receiver_name, joomuserprofile.profile_value as receiver_phone
				FROM #__akeebasubs_users as akeebasubscriptions
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
				JOIN #__user_profiles AS joomuserprofile ON joomusers.id = joomuserprofile.user_id
				WHERE joomuserprofile.profile_key = "akeebasubs.'.$integrationPhoneField.'"
				AND joomuserprofile.profile_value = '.$db->Quote($phoneNumberToSearch).' OR joomuserprofile.profile_value LIKE '.$db->Quote('%'.$phoneNumberToSearch));
			$informations = $db->loadObject();
			return $informations;
		}
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT '.$this->nameField.' AS name, '.$this->primaryField.' AS receiverId
				FROM #__akeebasubs_users as akeebasubscriptions
				JOIN '.ACYSMS::table('users', false).' AS joomusers	ON akeebasubscriptions.user_id = joomusers.id
				WHERE '.$this->nameField.' LIKE '.$db->Quote('%'.$name.'%').' LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
