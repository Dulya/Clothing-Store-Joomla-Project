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

class ACYSMSIntegration_easyprofile_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__jsn_users';

	var $componentName = 'easyprofile';

	var $displayedName = 'Easy Profile';

	var $primaryField = 'id';

	var $nameField = 'firstname';

	var $emailField = 'email';

	var $joomidField = 'id';

	var $editUserURL = 'index.php?option=com_users&task=user.edit&id=';

	var $addUserURL = 'index.php?option=com_users&view=user&layout=edit';

	var $tableAlias = 'easyProfileUsers';

	var $useJoomlaName = 0;

	var $integrationType = 'communityIntegration';

	public function getPhoneField(){

		$db = JFactory::getDBO();

		$query = 'SELECT  title AS "name", alias AS "column" FROM `#__jsn_fields` WHERE `type`= "phone" OR `type`= "text"';
		$db->setQuery($query);
		$fields = $db->loadObjectList();

		$lang = JFactory::getLanguage();
		$lang->load('com_jsn', JPATH_SITE);

		foreach($fields as $oneField){
			$oneField->name = JText::_($oneField->name);
		}
		return $fields;
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$searchFields = array('easyProfileUsers.firstname', 'easyProfileUsers.lastname', 'joomusers.email', 'easyProfileUsers.id', 'easyProfileUsers.`'.ACYSMS::secureField($config->get('easyprofile_field')).'`');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT easyProfileUsers.*, easyProfileUsers.id AS receiver_id, CONCAT_WS(" ",easyProfileUsers.firstname,easyProfileUsers.lastname) AS receiver_name, joomusers.email AS receiver_email, easyProfileUsers.`'.ACYSMS::secureField($config->get('easyprofile_field')).'` AS receiver_phone
				FROM #__jsn_users AS easyProfileUsers
				JOIN #__users AS joomusers
				ON easyProfileUsers.id = joomusers.id';

		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(easyProfileUsers.id)
						FROM #__jsn_users AS easyProfileUsers
						JOIN #__users AS joomusers
						ON easyProfileUsers.id = joomusers.id';

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
		$config = ACYSMS::config();
		$result = new stdClass();

		$queryConditions->where[] = 'statsdetails_receiver_table = "easyprofile"';

		$searchFields = array('CONCAT_WS(" ",easyProfileUsers.firstname,easyProfileUsers.lastname)', 'joomusers.email', 'easyProfileUsers.`'.ACYSMS::secureField($config->get('easyprofile_field')).'`', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, joomusers.email as receiver_email, easyProfileUsers.id as receiver_id, easyProfileUsers.`'.ACYSMS::secureField($config->get('easyprofile_field')).'` as receiver_phone, CONCAT_WS(" ",easyProfileUsers.firstname,easyProfileUsers.lastname) as receiver_name, stats.statsdetails_status as message_status
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__jsn_users AS easyProfileUsers ON stats.statsdetails_receiver_id = easyProfileUsers.id
					JOIN #__users AS joomusers
					ON easyProfileUsers.id = joomusers.id';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
					FROM '.ACYSMS::table('statsdetails').' as stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__jsn_users AS easyProfileUsers ON stats.statsdetails_receiver_id = easyProfileUsers.id
					JOIN #__users AS joomusers
					ON easyProfileUsers.id = joomusers.id';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__jsn_users AS easyProfileUsers ';
		$acyquery->join['joomusers'] = 'LEFT JOIN #__users as joomusers ON easyProfileUsers.id = joomusers.id';
		$acyquery->where[] = ' CHAR_LENGTH(easyProfileUsers.`'.ACYSMS::secureField($config->get('easyprofile_field')).'`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_jsn')) return true;
		return false;
	}


	public function addUsersInformations(&$queueMessage){

		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$userId = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `'.ACYSMS::secureField($config->get('easyprofile_field')).'` AS receiver_phone, CONCAT_WS(" ", firstname, lastname) AS receiver_name, email AS receiver_email
					FROM #__users as joomusers
					JOIN #__jsn_users AS easyProfileUsers
					ON joomusers.id = easyProfileUsers.id
					WHERE easyProfileUsers.id
					IN ("'.implode('","', $userId).'")';

		$db->setQuery($query);
		$easyProfileUser = $db->loadObjectList('id');

		if(empty($easyProfileUser)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.* FROM #__users as joomusers
									 JOIN #__jsn_users AS easyProfileUsers
									 ON joomusers.id = easyProfileUsers.id
									WHERE easyProfileUsers.id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($easyProfileUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->easyprofile = $easyProfileUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->easyprofile->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->easyprofile->receiver_name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->easyprofile->receiver_email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->easyprofile->id) && !empty($joomuserArray[$queueMessage[$messageID]->easyprofile->id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->easyprofile->id];
			}
		}
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT CONCAT_WS(" ",firstname, lastname) AS name, id AS receiverId
				FROM #__jsn_users
				WHERE firstname LIKE '.$db->Quote('%'.$name.'%').'
				OR  lastname LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
