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

class ACYSMSIntegration_j2store_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__j2store_order';

	var $componentName = 'j2store';

	var $displayedName = 'J2Store';

	var $primaryField = 'user_id';

	var $nameField = 'first_name';

	var $emailField = 'email';

	var $joomidField = 'user_id';

	var $editUserURL = '';

	var $addUserURL = '';

	var $tableAlias = 'j2storeorders';

	var $useJoomlaName = 0;

	var $integrationType = 'ecommerceIntegration';


	public function getPhoneField(){
		$tableFields = array();
		$j2StoreDefaultField = array('j2store_orderinfo_id', 'order_id', 'user_id', 'first_name', 'middle_name', 'last_name', 'email', 'address_1', 'address_2', 'city', 'zip', 'zone_id', 'zone_name', 'country_id', 'country_name', 'fax', 'type', 'company', 'tax_number', 'all_billing', 'all_shipping', 'all_payment', 'shipping_id');

		$j2storeFields = array_keys(acysms_getColumns('#__j2store_orderinfos'));
		foreach($j2storeFields as $field){
			if(in_array(str_replace('billing_', '', $field), $j2StoreDefaultField) || in_array(str_replace('shipping_', '', $field), $j2StoreDefaultField)) continue;
			$oneField = new stdClass();
			$oneField->name = $oneField->column = $field;
			$tableFields[] = $oneField;
		}

		return $tableFields;
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$j2storeField = ACYSMS::secureField($config->get('j2store_field'));
		$explodedInfo = explode('_', $j2storeField);
		$addressType = $explodedInfo[0];


		$searchFields = array('j2storeorderinfos.'.$addressType.'_first_name', 'j2storeorderinfos.'.$addressType.'_last_name', 'j2storeorders.email', 'j2storeorderinfos.'.$addressType.'`'.ACYSMS::secureField($config->get('j2store_field')).'`');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT j2storeorderinfos.`'.ACYSMS::secureField($config->get('j2store_field')).'` AS receiver_phone,
							j2storeorderinfos.*,
							j2storeorders.user_id as receiver_id,
							j2storeorders.user_email AS receiver_email,
							CONCAT_WS(" ",j2storeorderinfos.'.$addressType.'_first_name,j2storeorderinfos.'.$addressType.'_last_name) AS receiver_name

							FROM #__j2store_orders AS j2storeorders
							JOIN #__j2store_orderinfos AS j2storeorderinfos
							ON j2storeorderinfos.order_id = j2storeorders.order_id';
		if(!empty($filters)){
			$query .= ' AND ('.implode(') AND (', $filters).')';
		}

		$query .= ' GROUP BY receiver_phone';

		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}


		$queryCount = 'SELECT COUNT(DISTINCT j2storeorderinfos.`'.ACYSMS::secureField($config->get('j2store_field')).'`) 
						FROM #__j2store_orders AS j2storeorders
						JOIN #__j2store_orderinfos AS j2storeorderinfos
						ON j2storeorderinfos.order_id = j2storeorders.order_id';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;

		return $result;
	}

	public function getStatDetailsQuery($queryConditions, $search){
		$db = JFactory::getDBO();
		$result = new stdClass();
		$config = ACYSMS::config();

		$j2storeField = ACYSMS::secureField($config->get('j2store_field'));
		$explodedInfo = explode('_', $j2storeField);
		$addressType = $explodedInfo[0];


		$queryConditions->where[] = 'statsdetails_receiver_table = "j2store"';

		$searchFields = array('CONCAT_WS(" ", j2storeorderinfos.'.$addressType.'_first_name,j2storeorderinfos.'.$addressType.'_last_name)', 'j2storeorderinfos.'.$addressType.'_email', 'j2storeorderinfos.'.$addressType.'_`'.ACYSMS::secureField($config->get('j2store_field')).'`', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate AS message_sentdate,
								message.message_subject AS message_subject, 
								stats.statsdetails_status AS message_status, 
								j2storeorders.user_email AS receiver_email,
							 	j2storeorderinfos.'.ACYSMS::secureField($config->get('j2store_field')).'` AS receiver_phone, 
							 	CONCAT_WS(" ",j2storeorderinfos.'.$addressType.'_first_name,j2storeorderinfos.'.$addressType.'_last_name) AS receiver_name, 
							 	j2storeorders.user_id as receiver_id

								FROM '.ACYSMS::table('statsdetails').' AS stats
								LEFT JOIN '.ACYSMS::table('message').' AS message 
								ON stats.statsdetails_message_id = message.message_id

								JOIN #__j2store_orders AS j2storeorders
								ON stats.statsdetails_receiver_id = j2storeorders.order_id

								JOIN #__j2store_orderinfos AS j2storeorderinfos
					 			ON j2storeorderinfos.order_id = j2storeorders.j2store_order_id';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		$query .= ' GROUP BY receiver_phone';

		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}

		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message 
					ON stats.statsdetails_message_id = message.message_id

					JOIN #__j2store_orders AS j2storeorders
					ON stats.statsdetails_receiver_id = j2storeorders.user_id

					JOIN #__j2store_orderinfos AS j2storeorderinfos
					ON j2storeorderinfos.order_id = j2storeorders.j2store_order_id';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		$queryCount .= ' GROUP BY receiver_phone';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = ' #__j2store_orders as j2storeorders ';
		$acyquery->join['j2storeusers'] = ' JOIN #__j2store_orderinfos AS j2storeorderinfos ON j2storeorderinfos.order_id = j2storeorders.order_id ';
		$acyquery->join['joomusers'] = ' LEFT JOIN #__users as joomusers ON j2storeorders.user_id = joomusers.id ';
		$acyquery->where[] = 'CHAR_LENGTH(j2storeorderinfos.`'.ACYSMS::secureField($config->get('j2store_field')).'`) > 3';
		$acyquery->group[] = 'j2storeorderinfos.`'.ACYSMS::secureField($config->get('j2store_field')).'`';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_j2store')) return true;
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

		$j2storeField = ACYSMS::secureField($config->get('j2store_field'));
		$explodedInfo = explode('_', $j2storeField);
		$addressType = $explodedInfo[0];


		$query = 'SELECT *, `'.ACYSMS::secureField($config->get('j2store_field')).'` AS receiver_phone, 
					CONCAT_WS(" ",j2storeorderinfos.'.$addressType.'_first_name,j2storeorderinfos.'.$addressType.'_last_name) AS receiver_name, 
					j2storeorders.user_email AS receiver_email
					FROM #__j2store_orders AS j2storeorders
					JOIN #__j2store_orderinfos AS j2storeorderinfos
					ON j2storeorderinfos.order_id = j2storeorders.order_id
					WHERE j2storeorders.user_id IN ("'.implode('","', $userId).'")
					GROUP BY receiver_phone';

		$db->setQuery($query);
		$j2storeUser = $db->loadObjectList('user_id');

		if(empty($j2storeUser)) return false;

		$query = 'SELECT joomusers.* 
					FROM #__j2store_orders AS j2storeorders
					JOIN #__users AS joomusers
					ON joomusers.id = j2storeorders.user_id
					WHERE j2storeorders.user_id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage AS $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($j2storeUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->j2store = $j2storeUser[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->j2store->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->j2store->receiver_name;
			$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->j2store->receiver_email;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->j2store->user_id) && !empty($joomuserArray[$queueMessage[$messageID]->j2store->user_id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->j2store->user_id];
			}
		}
	}

	public function getReceiverIDs($userIDs = array()){

		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT address_user_id
						FROM #__hikashop_user as hikausers
						JOIN #__j2store_orderinfos as j2storeorderinfos
						ON hikausers.order_id = address_user_id
						WHERE hikausers.user_cms_id IN ('.implode(',', $userIDs).') AND address_user_id > 0';
		$db->setQuery($query);

		return acysms_loadResultArray($db);
	}


	public function getJoomUserId($userIDs = array()){
		if(empty($userIDs)) return array();
		if(!is_array($userIDs)) $userIDs = array($userIDs);

		JArrayHelper::toInteger($userIDs);

		$db = JFactory::getDBO();

		$query = 'SELECT hikausers.user_cms_id
						FROM #__hikashop_user as hikausers
						JOIN #__j2store_orderinfos as j2storeorderinfos
						ON hikausers.order_id = address_user_id
						WHERE address_user_id IN ('.implode(',', $userIDs).')';
		$db->setQuery($query);
		return acysms_loadResultArray($db);
	}

	public function getQueueListingQuery($filters, $order){
		$filters[] = 'receiver.address_default = "1"';
		return parent::getQueueListingQuery($filters, $order);
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$j2storeField = ACYSMS::secureField($config->get('j2store_field'));
		$explodedInfo = explode('_', $j2storeField);
		$addressType = $explodedInfo[0];

		$query = 'SELECT CONCAT_WS(" ",j2storeorderinfos.'.$addressType.'_first_name,j2storeorderinfos.'.$addressType.'_last_name) AS name, j2storeorders.user_id as receiverId
				FROM #__j2store_orders AS j2storeorders
				JOIN #__j2store_orderinfos AS j2storeorderinfos
				ON j2storeorderinfos.order_id = j2storeorders.order_id

				WHERE '.$addressType.'_first_name LIKE '.$db->Quote('%'.$name.'%').'
				OR '.$addressType.'_last_name LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
