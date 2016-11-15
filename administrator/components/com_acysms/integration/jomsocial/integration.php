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

class ACYSMSIntegration_jomsocial_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__community_users';

	var $componentName = 'jomsocial';

	var $displayedName = 'JomSocial';

	var $primaryField = 'userid';

	var $nameField = 'name';

	var $emailField = 'email';

	var $joomidField = 'userid';

	var $editUserURL = 'index.php?option=com_community&view=users&layout=edit&id=';

	var $addUserURL = 'index.php?option=com_users&task=user.add';

	var $tableAlias = 'jomsocialusers';

	var $useJoomlaName = 1;

	var $integrationType = 'communityIntegration';


	public function getPhoneField(){

		$db = JFactory::getDBO();

		$query = 'SELECT  name as "name", id as "column"
				FROM `#__community_fields`
				WHERE type="text"
				AND name <> "Basic Information"
				AND name <> "Gender"
				AND name <> "Birthdate"
				AND name <> "About me"
				AND name <> "Contact Information"
				AND name <> "Address"
				AND name <> "State"
				AND name <> "City / Town"
				AND name <> "Country"
				AND name <> "Website"
				AND name <> "Education"
				AND name <> "College / University"
				AND name <> "Graduation Year"';

		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('joomusers.name', 'joomusers.email', 'jomsocialusers.userid', 'fieldsvalue.value');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT jomsocialusers.*, jomsocialusers.userid as receiver_id, joomusers.name as receiver_name, joomusers.email as receiver_email, fieldsvalue.value as receiver_phone
				FROM #__community_users as jomsocialusers
				JOIN #__users as joomusers ON jomsocialusers.userid = joomusers.id
				LEFT JOIN #__community_fields_values fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid
				WHERE fieldsvalue.field_id = '.intval($config->get('jomsocial_field'));

		if(!empty($filters)){
			$query .= ' AND ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(jomsocialusers.userid)
				FROM #__community_users as jomsocialusers
				JOIN #__users as joomusers ON jomsocialusers.userid = joomusers.id
				LEFT JOIN #__community_fields_values fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid
				WHERE fieldsvalue.field_id = '.intval($config->get('jomsocial_field'));
		if(!empty($filters)){
			$queryCount .= ' AND ('.implode(') AND (', $filters).')';
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

		$queryConditions->where[] = 'statsdetails_receiver_table = "jomsocial"';

		$searchFields = array('joomusers.name', 'joomusers.email', 'fieldsvalue.value', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}


		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, joomusers.name as receiver_name, joomusers.email as receiver_email, fieldsvalue.value as receiver_phone, stats.statsdetails_status as message_status, jomsocialusers.userid as receiver_id
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__community_users as jomsocialusers ON stats.statsdetails_receiver_id = jomsocialusers.userid
					LEFT JOIN #__community_fields_values fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid
					JOIN #__users as joomusers ON jomsocialusers.userid = joomusers.id
					WHERE fieldsvalue.field_id = '.intval($config->get('jomsocial_field'));

		$query .= ' AND ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__community_users as jomsocialusers ON stats.statsdetails_receiver_id = jomsocialusers.userid
					LEFT JOIN #__community_fields_values fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid
					JOIN #__users as joomusers ON jomsocialusers.userid = joomusers.id
					WHERE fieldsvalue.field_id = '.intval($config->get('jomsocial_field'));

		$queryCount .= ' AND ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__community_users as jomsocialusers ';
		$acyquery->join['joomusers'] = 'JOIN #__users as joomusers ON jomsocialusers.userid = joomusers.id';
		$acyquery->join['fieldsvalue'] = 'JOIN #__community_fields_values fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid';
		$acyquery->where[] = 'fieldsvalue.field_id = '.intval($config->get('jomsocial_field')).' AND joomusers.block=0 AND CHAR_LENGTH(fieldsvalue.value) > 3';
		return $acyquery;
	}

	function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_community')) return true;
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

		$phoneField = intval($config->get('jomsocial_field'));

		JArrayHelper::toInteger($userId);

		$queryPhone = 'SELECT value as receiver_phone, user_id FROM #__community_fields_values WHERE field_id = '.intval($phoneField).' AND user_id IN ('.implode(',', $userId).')';
		$db->setQuery($queryPhone);
		$phoneNumbers = $db->loadObjectList('user_id');

		JArrayHelper::toInteger($userId);

		$query = 'SELECT jomsocialusers.*, jomsocialusers.userid as receiver_id, joomusers.name as receiver_name
			FROM #__community_users as jomsocialusers
			JOIN #__users as joomusers ON jomsocialusers.userid = joomusers.id
			AND joomusers.id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$jomsocialusers = $db->loadObjectList('receiver_id');

		if(empty($jomsocialusers)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.*
			FROM #__community_users as jomsocialusers
			JOIN #__users as joomusers ON jomsocialusers.userid = joomusers.id
			AND joomusers.id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;

			if(empty($jomsocialusers[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->jomsocial = $jomsocialusers[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $phoneNumbers[$oneMessage->queue_receiver_id]->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $jomsocialusers[$oneMessage->queue_receiver_id]->receiver_name;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->jomsocial->userid) && !empty($joomuserArray[$queueMessage[$messageID]->jomsocial->userid])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->jomsocial->userid];
				$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomla->email;
			}
		}
	}

	function getQueueListingQuery($filters, $order){
		$result = new stdClass();
		$config = ACYSMS::config();

		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$filters[] = ' message.message_userid = '.intval($my->id);
		}


		$query = 'SELECT queue.*, queue.queue_priority as queue_priority, queue.queue_try as queue_try, queue.queue_senddate as queue_senddate, message.message_subject as message_subject, joomusers.'.$this->nameField.' as receiver_name, fieldsvalue.value as receiver_phone, jomsocialusers.userid as receiver_id';
		$query .= ' FROM '.ACYSMS::table('queue').' AS queue';
		$query .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';
		$query .= ' JOIN #__community_users AS jomsocialusers  ON jomsocialusers.userid = queue.queue_receiver_id';
		$query .= ' JOIN #__users AS joomusers ON jomsocialusers.userid = joomusers.id';
		$query .= ' JOIN #__community_fields_values AS fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid ';
		$query .= ' WHERE fieldsvalue.field_id = '.intval($config->get('jomsocial_field'));

		if(!empty($filters)) $query .= ' AND  ('.implode(') AND (', $filters).')';
		$query .= ' ORDER BY '.$order->value.' '.$order->dir.', queue.`queue_receiver_id` ASC';

		$queryCount = 'SELECT COUNT(queue.queue_message_id), joomusers.'.$this->nameField.' as receiver_name';
		$queryCount .= ' FROM '.ACYSMS::table('queue').' AS queue';
		$queryCount .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';
		$queryCount .= ' JOIN #__community_users AS jomsocialusers  ON jomsocialusers.userid = queue.queue_receiver_id';
		$queryCount .= ' JOIN #__users AS joomusers ON jomsocialusers.userid = joomusers.id';
		$queryCount .= ' JOIN #__community_fields_values AS fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid ';
		$queryCount .= ' WHERE fieldsvalue.field_id = '.intval($config->get('jomsocial_field'));

		if(!empty($filters)) $queryCount .= ' AND ('.implode(') AND (', $filters).')';
		$queryCount .= ' ORDER BY '.$order->value.' '.$order->dir.', queue.`queue_receiver_id` ASC';

		$result->query = $query;
		$result->queryCount = $queryCount;

		return $result;
	}

	function getInformationsByPhoneNumber($phoneNumber){
		$config = ACYSMS::config();
		$phoneHelper = ACYSMS::get('helper.phone');
		$db = JFactory::getDBO();

		$integrationPhoneField = $config->get($this->componentName.'_field');

		$countryCode = $phoneHelper->getCountryCode($phoneNumber);

		$phoneNumberToSearch = str_replace('+'.$countryCode, '', $phoneNumber);

		if(!empty($integrationPhoneField)){
			$db->setQuery('SELECT jomsocialusers.*, jomsocialusers.userid as receiver_id, fieldsvalue.value as receiver_phone
				FROM #__community_users as jomsocialusers
				JOIN #__community_fields_values fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid
				WHERE fieldsvalue.field_id = '.intval($integrationPhoneField).'
				AND fieldsvalue.value = '.$db->Quote($phoneNumberToSearch).' OR fieldsvalue.value LIKE '.$db->Quote('%'.$phoneNumberToSearch));
			$informations = $db->loadObject();
			return $informations;
		}
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;

		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$query = 'SELECT '.$this->nameField.' AS name, '.$this->primaryField.' AS receiverId
				FROM #__community_users as jomsocialusers
				JOIN #__users as joomusers ON jomsocialusers.userid = joomusers.id
				LEFT JOIN #__community_fields_values fieldsvalue ON fieldsvalue.user_id = jomsocialusers.userid
				WHERE fieldsvalue.field_id = '.intval($config->get('jomsocial_field')).'
				AND '.$this->nameField.' LIKE '.$db->Quote('%'.$name.'%').' LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
