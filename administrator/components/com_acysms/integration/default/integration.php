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

class ACYSMSIntegration_default_integration{


	var $tableName;

	var $componentName;

	var $displayedName;

	var $primaryField;

	var $nameField;

	var $emailField;

	var $joomidField;

	var $editUserURL;

	var $editUserFrontURL;

	var $addUserURL;

	var $addUserFrontURL;

	var $tableAlias;

	var $useJoomlaName;

	var $submoduleId;


	public function __construct($moduleid = ''){
		$this->submoduleId = $moduleid;
	}

	public function getPhoneField(){
		$db = JFactory::getDBO();
		$tableFields = $db->getTableFields($this->tableName);
		foreach($tableFields as $field => $type){
			$oneField = new stdClass();
			$oneField->column = $field;
			$oneField->name = $field;
			$tableFields[] = $oneField;
		}
		return $tableFields;
	}


	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('users.username', 'integrationTable.'.$this->emailField);
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT integrationTable.*, firstname as receiver_name, receivers.email as receiver_email, integrationTable.'.$this->primaryField.' as receiver_id, integrationTable.`'.ACYSMS::secureField($config->get('receiver_field')).'` as receiver_phone
				FROM '.$this->tableName.' as integrationTable LEFT JOIN '.ACYSMS::table('users', false).' as users ON integrationTable.'.$this->primaryField.' = users.id';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(integrationTable.subid) FROM '.$this->tableName.' as integrationTable';
		if(!empty($filters)){
			$queryCount .= ' LEFT JOIN '.ACYSMS::table('users', false).' as users ON integrationTable.userid = users.id';
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
		$integrationField = $config->get($this->componentName.'_field');

		$searchFields = array($this->nameField, $this->emailField, 'receiver.`'.$integrationField.'`', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, receiver.'.$this->emailField.' as receiver_email, receiver.`'.$integrationField.'` as receiver_phone, stats.statsdetails_status as message_status
				FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id';

		if(!$this->useJoomlaName){
			$query .= ' JOIN '.$this->tableName.' AS receiver ON receiver.'.$this->primaryField.' = stats.statsdetails_receiver_id';
		}else  $query .= ' JOIN  #__users as receiver ON stats.statsdetails_receiver_id = receiver.id';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id) FROM '.ACYSMS::table('statsdetails').' AS stats
				LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id';

		if(!$this->useJoomlaName){
			$queryCount .= ' JOIN '.$this->tableName.' AS receiver ON receiver.'.$this->primaryField.' = queue.queue_receiver_id';
		}else  $queryCount .= ' JOIN  #__users as receiver ON queue.queue_receiver_id = receiver.id';

		if(!empty($queryConditions->where)) $queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		$queryCount .= ' AND statsdetails_receiver_table = '.$this->componentName;

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	function getQuery(&$acyquery, $message){
		$config = ACYSMS::config();
		$acyquery->from = $this->tableName.' as integrationTable';
		$acyquery->where[] = 'integrationTable.enabled = 1 AND integrationTable.accept = 1 AND CHAR_LENGTH(integrationTable.`'.ACYSMS::secureField($config->get('receiver_field')).'`) > 3 ';
		return $acyquery;
	}

	function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.$this->tableName)) return true;
		return false;
	}

	function addUsersInformations(&$idArray){

		$users = array();
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		JArrayHelper::toInteger($idArray);

		$query = 'SELECT * FROM '.$this->tableName.' WHERE '.$this->primaryField.' IN ("'.implode('","', $idArray).'")';
		$db->setQuery($query);
		$userArray = $db->loadObjectList($this->primaryField);

		foreach($userArray as $receiverID => $acyUser){

			$users[$receiverID]['joomla'] = $acyUser;

			$phoneField = $config->get('receiver_field');
			$users[$receiverID]->receiver_phone = $acyUser->{$phoneField};
			$nameField = $this->nameField;
			$users[$receiverID]->receiver_name = $acyUser->$nameField;
			$users[$receiverID]->receiver_id = $receiverID;
		}

		return $users;
	}

	function getReceiverIDs($userIDs = array()){
		if(!is_array($userIDs)) $userIDs = array($userIDs);
		return $userIDs;
	}


	public function getJoomUserId($userIDs = array()){
		if(!is_array($userIDs)) $userIDs = array($userIDs);
		return $userIDs;
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

		$query = 'SELECT queue.*, queue.queue_priority as queue_priority, queue.queue_try as queue_try, queue.queue_senddate as queue_senddate, message.message_subject as message_subject, receiver.'.$this->nameField.' AS receiver_name, receiver.`'.$integrationField.'` AS receiver_phone, receiver.'.$this->primaryField.' as receiver_id';
		$query .= ' FROM '.ACYSMS::table('queue').' AS queue';
		$query .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';

		if(!$this->useJoomlaName){
			$query .= ' JOIN '.$this->tableName.' AS receiver ON receiver.`'.$this->primaryField.'` = queue.queue_receiver_id';
		}else  $query .= ' JOIN  #__users as receiver ON queue.queue_receiver_id = receiver.id';

		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		$query .= ' ORDER BY '.$order->value.' '.$order->dir.', queue.`queue_receiver_id` ASC';


		$queryCount = 'SELECT COUNT(queue.queue_message_id), receiver.'.$this->nameField.' as receiver_name';
		$queryCount .= ' FROM '.ACYSMS::table('queue').' AS queue';
		$queryCount .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';
		if(!$this->useJoomlaName){
			$queryCount .= ' JOIN '.$this->tableName.' AS receiver ON receiver.`'.$this->primaryField.'` = queue.queue_receiver_id';
		}else  $queryCount .= ' JOIN  #__users as receiver ON queue.queue_receiver_id = receiver.id';
		if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
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

		if($this->useJoomlaName){
			$columnName = 'joomUsers.name';
		}else $columnName = $this->tableAlias.'.'.$this->nameField;


		if(!empty($integrationPhoneField) && !empty($phoneNumberToSearch)){
			$query = 'SELECT *, '.$this->tableAlias.'.'.$this->primaryField.' as receiver_id, '.$columnName.' as receiver_name
					FROM '.$this->tableName.' AS '.$this->tableAlias.'
					LEFT JOIN #__users AS joomUsers
					ON joomUsers.id = '.$this->tableAlias.'.'.$this->joomidField.'
					WHERE `'.$integrationPhoneField.'` = '.$db->Quote($phoneNumberToSearch).' OR REPLACE(`'.$integrationPhoneField.'`, "-", "") LIKE '.$db->Quote('%'.$phoneNumberToSearch);
			$db->setQuery($query);
			$informations = $db->loadObject();

			return $informations;
		}
	}

	public function displayFiltersUserListing(){
		return;
	}

	function getNames(){
		$config = ACYSMS::config();
		$result = array();
		$row = new stdClass();
		$row->value = $this->componentName;
		$row->text = $this->displayedName;

		$phoneFieldSubIntegration = $config->get($this->componentName.'_field');
		if(empty($phoneFieldSubIntegration)) return array();

		$result[] = $row;
		return $result;
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT '.$this->nameField.' AS name, '.$this->primaryField.' AS receiverId  FROM '.$this->tableName.' WHERE '.$this->nameField.' LIKE '.$db->Quote('%'.$name.'%').' LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
