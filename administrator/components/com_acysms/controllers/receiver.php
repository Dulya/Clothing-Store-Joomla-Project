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

class ReceiverController extends ACYSMSController{
	var $aclCat = 'receivers';

	function choose(){
		if(!$this->isAllowed('receivers', 'manage')) return;
		JRequest::setVar('layout', 'choose');
		return parent::display();
	}

	function add(){
		if(!$this->isAllowed('receivers', 'manage')) return;

		$app = JFactory::getApplication();
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');

		$integration = ACYSMS::getIntegration($currentIntegration);

		global $Itemid;
		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;

		if(!$app->isAdmin()){
			$integration = ACYSMS::getIntegration('acysms');
			$url = $integration->addUserFrontURL.$myItem;
		}else $url = $integration->addUserURL.$myItem;

		$this->setRedirect($url);
	}

	function edit(){
		if(!$this->isAllowed('receivers', 'manage')) return;
		$app = JFactory::getApplication();

		$cid = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cid)) return;
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');
		$integration = ACYSMS::getIntegration($currentIntegration);

		if(!$app->isAdmin()){
			$integration = ACYSMS::getIntegration('acysms');
			$url = $integration->editUserFrontURL.substr($cid[0], 0, strpos($cid[0], '_'));
		}else $url = $integration->editUserURL.substr($cid[0], 0, strpos($cid[0], '_'));;


		$this->setRedirect($url);
	}

	function remove(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('receivers', 'delete')) return;
		$app = JFactory::getApplication();
		$config = ACYSMS::config();


		$deleteBehaviour = $config->get('frontend_delete_button', 'delete');
		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();

		if($app->isAdmin() || $deleteBehaviour == 'delete'){
			$class = ACYSMS::get('class.user');
			JArrayHelper::toInteger($cids);
			$num = $class->delete($cids);
			ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');
		}else{
			if(!$this->isAllowed('receivers', 'manage')) return;

			$groupId = JRequest::getInt('filter_group', 0);
			if(empty($groupId)){
				ACYSMS::enqueueMessage('Group not found', 'error');
			}else{
				$groupUserClass = ACYSMS::get('class.groupuser');
				foreach($cids as $oneUserId){
					$groupUserClass->removeSubscription($oneUserId, array($groupId));
				}

				$groupClass = ACYSMS::get('class.group');
				$group = $groupClass->get($groupId);

				ACYSMS::enqueueMessage(JText::sprintf('SMS_IMPORT_REMOVE', count($cids), $group->group_name), 'message');
			}
		}

		return $this->listing();
	}

	function block(){
		if(!$this->isAllowed('receivers', 'manage')) return;

		$cid = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cid)) return;
		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($cid, 0);
		return $this->listing();
	}

	function unblock(){
		if(!$this->isAllowed('receivers', 'manage')) return;

		$cid = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cid)) return;
		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($cid, 1);
		return $this->listing();
	}

	function conversation(){
		JRequest::setVar('layout', 'conversation');
		return parent::display();
	}

	function getReceiversByName(){
		JRequest::setVar('layout', 'conversationreceivers');
		return parent::display();
	}

	function sendOneShotSMS(){
		$receiverIdsString = JRequest::getCmd('receiverIds');
		$senderProfile = JRequest::getCmd('senderProfile_id');

		$isAjax = JRequest::getCmd('isAjax', '');

		$messageBody = JRequest::getString('messageBody');
		if(empty($messageBody)){
			ACYSMS::display(JText::_('SMS_ENTER_BODY'), 'error');
			exit;
		}

		if(empty($receiverIdsString)){
			ACYSMS::display(JText::_('SMS_SELECT_RECEIVER'), 'error');
			exit;
		}

		if(empty($senderProfile)){
			ACYSMS::display('Please select a sender profile', 'error');
			exit;
		}

		$receiverIdArray = explode('-', $receiverIdsString);

		$message = new stdClass();
		$message->message_senderprofile_id = $senderProfile;
		$message->message_body = $messageBody;
		$message->message_type = 'conversation';

		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$messageClass = ACYSMS::get('class.message');

		if(!$app->isAdmin() && $allowCustomerManagement){
			$nbParts = $messageClass->countMessageParts($messageBody)->nbParts;
			$user = JFactory::getUser();
			$customerClass = ACYSMS::get('class.customer');
			$nbCreditsLeft = $customerClass->getCredits($user->id);

			if($nbCreditsLeft - $nbParts <= 0){
				ACYSMS::display(JText::_('SMS_NOT_ENOUGH_CREDITS'), 'error');
				$customerClass->sendLowCreditsNotification($user->id);
				if($isAjax) exit;
				return $this->edit();
			}
			$customer = $customerClass->getCustomerByJoomID($user->id);
			$customer->customer_credits -= $nbParts;
			$customerClass->save($customer);
		}

		$messageClass->sendOneShotSMS($message, $receiverIdArray);


		if($isAjax) exit;
		return $this->conversation();
	}

	function getReceiversPhone(){
		$integration = JRequest::getCmd('integration', '');
		$value = JRequest::getString('value', '');

		if(empty($value) || empty($integration)){
			echo json_encode(array());
			exit;
		}

		$config = ACYSMS::config();
		$phoneHelper = ACYSMS::get('helper.phone');

		$integrationObj = ACYSMS::getIntegration($integration);
		$integrationPhoneField = $config->get($integrationObj->componentName.'_field');

		$db = JFactory::getDBO();

		if($integrationObj->useJoomlaName){
			$columnName = 'joomUsers.name';
		}else $columnName = $integrationObj->tableAlias.'.'.$integrationObj->nameField;

		$query = 'SELECT '.$columnName.' AS name, '.$integrationObj->tableAlias.'.'.$integrationPhoneField.' AS phone
				FROM '.$integrationObj->tableName.' AS '.$integrationObj->tableAlias.'
				LEFT JOIN #__users AS joomUsers
				ON joomUsers.id = '.$integrationObj->tableAlias.'.'.$integrationObj->joomidField.'
				WHERE '.$columnName.' LIKE '.$db->Quote('%'.$value.'%').'
				OR '.$integrationObj->tableAlias.'.'.$integrationPhoneField.' LIKE '.$db->Quote('%'.$value.'%');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		foreach($result as $oneResult){
			$oneResult->phone = $phoneHelper->getValidNum($oneResult->phone);
		}

		echo json_encode($result);
		exit;
	}
}
