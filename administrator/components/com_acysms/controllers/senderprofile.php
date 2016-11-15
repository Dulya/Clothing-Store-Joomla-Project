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

class SenderprofileController extends ACYSMSController{

	var $aclCat = 'sender_profiles';

	function copy(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('sender_profiles', 'copy')) return;

		$cids = JRequest::getVar('cid', array(), '', 'array');
		$db = JFactory::getDBO();

		$my = JFactory::getUser();
		$creatorId = intval($my->id);

		foreach($cids as $oneSenderProfileid){
			$query = 'INSERT INTO `#__acysms_senderprofile` (`senderprofile_name`,`senderprofile_gateway`,`senderprofile_userid`,`senderprofile_params`)';
			$query .= " SELECT CONCAT('copy_',`senderprofile_name`), `senderprofile_gateway`,".intval($creatorId).",`senderprofile_params`  FROM `#__acysms_senderprofile` WHERE `senderprofile_id` = ".intval($oneSenderProfileid);
			$db->setQuery($query);
			$db->query();
		}

		return $this->listing();
	}

	function gatewayparams(){
		$gateway = JRequest::getCmd('gateway');

		if(!empty($gateway)){
			$class = ACYSMS::get('class.senderprofile');
			$gateway = $class->getGateway($gateway);
			$gateway->displayConfig();
		}
		exit;
	}

	function store(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('sender_profiles', 'manage')) return;

		$class = ACYSMS::get('class.senderprofile');

		$status = $class->saveForm();
		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'message');
		}else{
			ACYSMS::enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					ACYSMS::enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function sendtest(){
		if(!$this->isAllowed('sender_profiles', 'sendtest')) return;

		$senderprofileClass = ACYSMS::get('class.senderprofile');
		$messageClass = ACYSMS::get('class.message');

		$this->store();

		$gatewayId = ACYSMS::getCID('senderprofile_id');
		if(empty($gatewayId)) return;
		$gateway = $senderprofileClass->getGateway($gatewayId);
		$message = new stdClass();
		$message->message_body = JRequest::getString('message_body');

		$messageClass->sendtest($message, $gateway);

		return $this->edit();
	}

	function remove(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('sender_profiles', 'delete')) return;

		$cids = JRequest::getVar('cid', array(), '', 'array');
		$class = ACYSMS::get('class.senderprofile');
		$num = $class->delete($cids);

		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');

		return $this->listing();
	}

	function checkBalance(){
		$menuHelper = ACYSMS::get('helper.menu');
		$myBalArea = $menuHelper->myBalanceArea();
		echo $myBalArea;
		exit;
	}
}
