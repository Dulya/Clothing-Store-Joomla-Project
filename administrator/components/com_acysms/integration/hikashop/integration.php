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

class ACYSMSIntegration_hikashop_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__hikashop_address';

	var $componentName = 'hikashop';

	var $displayedName = 'HikaShop';

	var $primaryField = 'address_user_id';

	var $nameField = 'address_firstname';

	var $emailField = 'user_email';

	var $joomidField = 'user_cms_id';

	var $editUserURL = 'index.php?option=com_hikashop&ctrl=user&task=edit&cid[]=';

	var $addUserURL = 'index.php?option=com_users&task=user.add';

	var $tableAlias = 'hikaaddress';

	var $useJoomlaName = 0;

	var $integrationType = 'ecommerceIntegration';


	public function getPhoneField(){

		$db = JFactory::getDBO();
		$tableFields = array();
		$hikashopDefaultField = array('address_id', 'address_user_id', 'address_title', 'address_firstname', 'address_middle_name', 'address_lastname', 'address_company', 'address_street', 'address_street2', 'address_post_code', 'address_city', 'address_fax', 'address_state', 'address_country', 'address_published', 'address_vat', 'address_default');
		$hikaFields = array_keys(acysms_getColumns('#__hikashop_address'));
		foreach($hikaFields as $field){
			if(in_array($field, $hikashopDefaultField)) continue;
			$oneField = new stdClass();
			$oneField->name = $oneField->column = $field;
			$tableFields[] = $oneField;
		}

		return $tableFields;
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('hikaaddress.address_firstname', 'hikaaddress.address_lastname', 'hikausers.user_email', 'hikaaddress.address_user_id', 'hikaaddress.`'.ACYSMS::secureField($config->get('hikashop_field')).'`');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT hikausers.*, hikaaddress.*, hikaaddress.address_user_id as receiver_id, hikausers.user_email as receiver_email,
					 hikaaddress.`'.ACYSMS::secureField($config->get('hikashop_field')).'` as receiver_phone, CONCAT_WS(" ",hikaaddress.address_firstname,hikaaddress.address_lastname) as receiver_name
					 FROM #__hikashop_address AS hikaaddress
					 LEFT JOIN #__hikashop_user as hikausers
					 ON hikaaddress.address_user_id = hikausers.user_id
					 WHERE hikaaddress.address_default = "1"';
		if(!empty($filters)){
			$query .= ' AND ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT COUNT(hikausers.user_id) FROM #__hikashop_user as hikausers
											LEFT JOIN #__hikashop_address as hikaaddress
											ON hikaaddress.address_user_id = hikausers.user_id
											';
		if(!empty($filters)){
			$queryCount .= ' LEFT JOIN '.ACYSMS::table('users', false).' as receiver
													ON hikausers.user_cms_id = receiver.id
													WHERE hikaaddress.address_default = "1"';

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

		$queryConditions->where[] = 'statsdetails_receiver_table = "hikashop"';

		$searchFields = array('CONCAT_WS(" ",hikaaddress.address_firstname,hikaaddress.address_lastname)', 'hikausers.user_email', 'hikaaddress.`'.ACYSMS::secureField($config->get('hikashop_field')).'`', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*,  message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, stats.statsdetails_status as message_status, hikausers.user_email as receiver_email,
							 	hikaaddress.`'.ACYSMS::secureField($config->get('hikashop_field')).'` as receiver_phone, CONCAT_WS(" ",hikaaddress.address_firstname,hikaaddress.address_lastname) as receiver_name, hikaaddress.address_user_id as receiver_id
								FROM '.ACYSMS::table('statsdetails').' AS stats
								LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
								JOIN #__hikashop_user as hikausers ON stats.statsdetails_receiver_id = hikausers.user_id
								JOIN #__users as joomusers ON hikausers.user_cms_id = joomusers.id
								LEFT JOIN #__hikashop_address AS hikaaddress ON hikaaddress.address_user_id = hikausers.user_id';

		$query .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;

		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					JOIN #__hikashop_user as hikausers ON stats.statsdetails_receiver_id = hikausers.user_id
					JOIN #__users as joomusers ON hikausers.user_cms_id = joomusers.id
					LEFT JOIN #__hikashop_address AS hikaaddress ON hikaaddress.address_user_id = hikausers.user_id';

		$queryCount .= ' WHERE ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	public function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = ' #__hikashop_address as hikaaddress ';
		$acyquery->join['hikausers'] = ' JOIN #__hikashop_user as hikausers ON hikaaddress.address_user_id = hikausers.user_id ';
		$acyquery->join['joomusers'] = ' LEFT JOIN #__users as joomusers ON hikausers.user_cms_id = joomusers.id ';
		$acyquery->where[] = 'CHAR_LENGTH(hikaaddress.`'.ACYSMS::secureField($config->get('hikashop_field')).'`) > 3 AND hikaaddress.address_default = 1';
		return $acyquery;
	}

	public function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_hikashop')) return true;
		return false;
	}

	public function addUsersInformations(&$queueMessage){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$userId = array();
		$addressId = array();
		$hikaUserAddress = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);

			if(!empty($oneMessage->queue_paramqueue)){
				$address = $oneMessage->queue_paramqueue;
				if(empty($address->address_id)) continue;
				$addressId[$address->address_id] = intval($address->address_id);
			}
		}

		if(empty($userId)) return;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT *, `'.ACYSMS::secureField($config->get('hikashop_field')).'` as receiver_phone, CONCAT_WS(" ",address_firstname,address_lastname) as receiver_name
						FROM #__hikashop_address as hikaaddress
						JOIN #__hikashop_user as hikausers ON hikausers.user_id = hikaaddress.address_user_id
						WHERE address_user_id IN ('.implode(',', $userId).')
						ORDER BY address_default ASC';
		$db->setQuery($query);
		$hikaUser = $db->loadObjectList('address_user_id');

		if(empty($hikaUser)) return false;

		JArrayHelper::toInteger($addressId);

		if(!empty($addressId)){
			$query = 'SELECT *, `'.ACYSMS::secureField($config->get('hikashop_field')).'` as receiver_phone, CONCAT_WS(" ",address_firstname,address_lastname) as receiver_name
					FROM #__hikashop_address as hikaaddress
					JOIN #__hikashop_user as hikausers ON hikausers.user_id = hikaaddress.address_user_id
					WHERE address_id IN ('.implode(',', $addressId).')';
			$db->setQuery($query);
			$hikaUserAddress = $db->loadObjectList('address_id');
		}

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.*
						FROM #__hikashop_user as hikausers
						JOIN #__users as joomusers
						 ON joomusers.id = hikausers.user_cms_id
						WHERE hikausers.user_id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');
		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($hikaUser[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->hikashop = $hikaUser[$oneMessage->queue_receiver_id];
			if(!empty($address->address_id) && !empty($hikaUserAddress[$address->address_id])){
				$queueMessage[$messageID]->hikashop = $hikaUserAddress[$address->address_id];
			}
			$queueMessage[$messageID]->receiver_phone = $queueMessage[$messageID]->hikashop->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $queueMessage[$messageID]->hikashop->receiver_name;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->hikashop->user_cms_id) && !empty($joomuserArray[$queueMessage[$messageID]->hikashop->user_cms_id])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->hikashop->user_cms_id];
				$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomla->email;
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
						JOIN #__hikashop_address as hikaaddress
						ON hikausers.user_id = address_user_id
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
						JOIN #__hikashop_address as hikaaddress
						ON hikausers.user_id = address_user_id
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
		$query = 'SELECT CONCAT_WS(" ",address_firstname,address_lastname) AS name, address_user_id AS receiverId
				FROM #__hikashop_address
				WHERE address_firstname LIKE '.$db->Quote('%'.$name.'%').'
				OR address_lastname LIKE '.$db->Quote('%'.$name.'%').'
				LIMIT 10';
		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
