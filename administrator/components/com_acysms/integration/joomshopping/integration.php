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

class ACYSMSIntegration_joomshopping_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__jshopping_users';

	var $componentName = 'joomshopping';

	var $displayedName = 'JoomShopping';

	var $primaryField = 'user_id';

	var $nameField = 'u_name';

	var $emailField = 'email';

	var $joomidField = 'user_id';

	var $editUserURL = 'index.php?option=com_jshopping&controller=users&task=edit&user_id=';

	var $addUserURL = 'index.php?option=com_jshopping&controller=users&task=add';

	var $tableAlias = 'joomshoppinguser';

	var $useJoomlaName = 0;

	var $integrationType = 'ecommerceIntegration';

	public function getPhoneField(){

		$tableFields = array();
		$oneField = new stdClass();
		$oneField->name = $oneField->column = 'number';
		$tableFields[] = $oneField;

		$secondField = new stdClass();
		$secondField->name = $secondField->column = 'phone';
		$tableFields[] = $secondField;

		$thirdField = new stdClass();
		$thirdField->name = $thirdField->column = 'mobil_phone';
		$tableFields[] = $thirdField;

		return $tableFields;
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$config = ACYSMS::config();

		$searchFields = array('joomshoppinguser.u_name', 'joomshoppinguser.f_name', 'joomshoppinguser.l_name', 'joomshoppinguser.user_id', 'joomshoppinguser.email', 'joomshoppinguser.`'.ACYSMS::secureField($config->get('joomshopping_field')).'`');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT joomshoppinguser.*, joomshoppinguser.user_id AS receiver_id, joomshoppinguser.u_name AS receiver_name, joomshoppinguser.email AS receiver_email, joomshoppinguser.`'.ACYSMS::secureField($config->get('joomshopping_field')).'` AS receiver_phone
				FROM #__jshopping_users AS joomshoppinguser';

		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(joomshoppinguser.user_id) FROM #__jshopping_users AS joomshoppinguser';
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

		$queryConditions->where[] = 'statsdetails_receiver_table = "joomshopping"';

		$searchFields = array('joomshoppinguser.u_name', 'joomshoppinguser.user_email', 'joomshoppinguser.`'.ACYSMS::secureField($config->get('joomshopping_field')).'`', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id AS message_id, stats.statsdetails_sentdate AS message_sentdate, message.message_subject AS message_subject, joomshoppinguser.email AS receiver_email,  joomshoppinguser.user_id AS receiver_id,joomshoppinguser.`'.ACYSMS::secureField($config->get('joomshopping_field')).'` AS receiver_phone, joomshoppinguser.u_name AS receiver_name, stats.statsdetails_status AS message_status
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__jshopping_users AS joomshoppinguser ON stats.statsdetails_receiver_id = joomshoppinguser.user_id';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__jshopping_users AS joomshoppinguser ON stats.statsdetails_receiver_id = joomshoppinguser.user_id';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__jshopping_users AS joomshoppinguser ';
		$acyquery->join['joomusers'] = 'LEFT JOIN #__users AS joomusers ON joomshoppinguser.user_id = joomusers.id';
		$acyquery->where[] = ' CHAR_LENGTH(joomshoppinguser.`'.ACYSMS::secureField($config->get('joomshopping_field')).'`) > 3';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_jshopping')) return true;
		return false;
	}


	public function addUsersInformations(&$queueMessage){

		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$userId = array();

		foreach($queueMessage AS $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `'.ACYSMS::secureField($config->get('joomshopping_field')).'` AS receiver_phone, u_name AS receiver_name, email AS receiver_email
					FROM #__jshopping_users
					WHERE user_id
					IN ("'.implode('","', $userId).'")';

		$db->setQuery($query);
		$joomShoppingUser = $db->loadObjectList('user_id');

		if(empty($joomShoppingUser)) return false;

		$query = 'SELECT joomusers.* FROM #__jshopping_users AS joomshoppinguser
									 JOIN #__users AS joomusers
									 ON joomusers.id = joomshoppinguser.user_id
									WHERE joomshoppinguser.user_id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage AS $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($joomShoppingUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->joomshopping = $joomShoppingUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->joomshopping->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->joomshopping->receiver_name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomshopping->receiver_email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->joomshopping->user_id) && !empty($joomuserArray[$queueMessage[$messageID]->joomshopping->user_id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->joomshopping->user_id];
			}
		}
	}

	public function getReceiverIDs($userIDs = array()){
		if(!is_array($userIDs)) $userIDs = array($userIDs);
		return $userIDs;
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT user_id FROM #__jshopping_users WHERE user_id IN ('.implode(',', $userIDs).')';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}

	public function displayFiltersUserListing(){
		$app = JFactory::getApplication();

		$selectedGroup = $app->getUserStateFromRequest('filter_group', 'filter_group', '', 'string');
		$groupType = ACYSMS::get('type.group');
		$groupType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';

		if(!$app->isAdmin()){
			$groupClass = ACYSMS::get('class.group');
			$allGroups = $groupClass->getFrontendGroups();
			if(count($allGroups) > 1){
				$filterGroup = JHTML::_('select.genericlist', $allGroups, "filter_group", 'class="inputbox" size="1" onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"', 'group_id', 'group_name', (int)$selectedGroup, "filter_group");
			}else{
				$filterGroup = '<input type="hidden" name="filter_group" value="'.$selectedGroup.'"/>';
			}
		}else{
			$filterGroup = $groupType->display('filter_group', $selectedGroup);
		}

		return $filterGroup;
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$query = 'SELECT u_name AS name, user_id AS receiverId
				FROM #__jshopping_users
				WHERE u_name LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
