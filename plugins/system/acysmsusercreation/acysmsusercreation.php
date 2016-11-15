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

class plgSystemAcysmsUserCreation extends JPlugin{


	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) return false;
		parent::__construct($subject, $config);
		if(!$this->init()) return;
	}

	private function init(){
		if(defined('ACYSMS_COMPONENT')) return true;
		$acySmsHelper = rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper)){
			include_once $acySmsHelper;
		}else return false;
		return defined('ACYSMS_COMPONENT');
	}

	function onACYSMSGetMessageType(&$types, $integration){
		if(!$this->init()) return;

		if($integration == 'acymailing' || $integration == 'acysms' || empty($integration)) return;

		$newType = new stdClass();
		$newType->name = JText::_('SMS_AUTO_USER_CREATION');
		$types['usercreation'] = $newType;
		return;
	}

	function onACYSMSDisplayParamsAutoMessage_usercreation(){
		$timevalue = array();
		$timevalue[] = JHTML::_('select.option', 'hours', JText::_('SMS_HOURS'));
		$timevalue[] = JHTML::_('select.option', 'days', JText::_('SMS_DAYS'));
		$timevalue[] = JHTML::_('select.option', 'weeks', JText::_('SMS_WEEKS'));
		$timevalue[] = JHTML::_('select.option', 'months', JText::_('SMS_MONTHS'));
		$delay = JHTML::_('select.genericlist', $timevalue, "data[message][message_receiver][auto][usercreation][delay][timevalue]", 'size="1"  style="width:auto;"', 'value', 'text', '0');

		$sentTrigger = array();
		$sentTrigger[] = JHTML::_('select.option', 'creation', JText::_('SMS_USER_CREATED'));
		$sentTrigger[] = JHTML::_('select.option', 'confirmation', JText::_('SMS_USER_CONFIRMED'));
		$sentOption = JHTML::_('select.genericlist', $sentTrigger, "data[message][message_receiver][auto][usercreation][sentTrigger]", 'size="1" style="width:auto;"', 'value', 'text', '0');

		$timeNumber = '<input type="text" name="data[message][message_receiver][auto][usercreation][delay][duration]" class="inputbox" style="width:30px" value="0">';
		echo JText::sprintf('SMS_SEND_CREATION_AUTO', $timeNumber.' '.$delay, $sentOption).'<br />';
		echo '<div id="loadField"></div>';
	}

	function onUserAfterSave($user, $isnew, $success, $msg){

		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement', '0');
		if($allowCustomerManagement && $isnew) $this->_createCustomer($user);

		return $this->onAfterStoreUser($user, $isnew, $success, $msg);
	}

	function onUserBeforeSave($user, $isnew, $new){
		return $this->onBeforeStoreUser($user, $isnew);
	}


	function onBeforeStoreUser($user, $isnew){

		if(is_object($user)) $user = get_object_vars($user);

		$this->oldUser = $user;

		return true;
	}

	function onAfterStoreUser($user, $isnew, $success, $msg){
		$mainframe = JFactory::getApplication();

		if(is_object($user)) $user = get_object_vars($user);

		if($success === false) return false;
		if(empty($user['id'])) return false;

		$mainframe->setUserState("ACYSMS_userCreationId", intval($user['id']));
		if(!empty($user['password_clear'])) $mainframe->setUserState("ACYSMS_userPwd", base64_encode($user['password_clear']));

		if($isnew){
			$mainframe->setUserState("ACYSMS_action", 'creation');
		}else{            //Confirm the user if the status changed so we moved from blocked to non blocked
			if(!empty($this->oldUser['block']) && empty($user['block'])) $mainframe->setUserState("ACYSMS_action", 'confirmation');
		}

		return true;
	}

	public function onAfterDispatch(){
		if(!$this->init()) return;
		$db = JFactory::getDBO();
		$mainframe = JFactory::getApplication();

		$userCreationId = $mainframe->getUserStateFromRequest("ACYSMS_userCreationId", "ACYSMS_userCreationId", "0");
		if(empty($userCreationId)) return;

		$messageClass = ACYSMS::get('class.message');
		$allMessages = $messageClass->getAutoMessage('usercreation');
		if(empty($allMessages)) return false;

		$paramqueue = '';
		$userPwd = $mainframe->getUserStateFromRequest("ACYSMS_userPwd", "ACYSMS_userPwd", "0");
		if(!empty($userPwd)){
			$params = new stdClass();
			$params->password = $userPwd;
			$paramqueue = serialize($params);
		}

		$sentTrigger = $mainframe->getUserStateFromRequest("ACYSMS_action", "ACYSMS_action", "0");

		$mainframe->setUserState("ACYSMS_userCreationId", 0);
		$mainframe->setUserState("ACYSMS_userPwd", 0);
		$mainframe->setUserState("ACYSMS_action", 0);

		foreach($allMessages as $oneMessage){
			if(empty($sentTrigger) || $sentTrigger != $oneMessage->message_receiver['auto']['usercreation']['sentTrigger']) continue;

			$senddate = strtotime('+'.intval($oneMessage->message_receiver['auto']['usercreation']['delay']['duration']).' '.$oneMessage->message_receiver['auto']['usercreation']['delay']['timevalue'], time());

			$integration = ACYSMS::getIntegration($oneMessage->message_receiver_table);
			$receiverId = $integration->getReceiverIDs($userCreationId);


			$acyquery = ACYSMS::get('class.acyquery');
			$integrationFrom = $oneMessage->message_receiver_table;
			$integrationTo = $oneMessage->message_receiver_table;
			$integration = ACYSMS::getIntegration($integrationTo);
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);
			$acyquery->addUserFilters($receiverId, $integrationFrom, $integrationTo);
			$querySelect = $acyquery->getQuery(array($oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($oneMessage->message_receiver_table).','.$senddate.',0,2,'.$db->Quote($paramqueue)));

			$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`, `queue_paramqueue`) '.$querySelect;
			$db->setQuery($finalQuery);
			$db->query();
		}
	}




	public function onAcySMSSaveMessage($oldMsg, $newMsg){
		if(empty($newMsg['message_type']) || $newMsg['message_type'] != 'auto' || empty($newMsg['message_autotype'])) return;
		if($newMsg['message_autotype'] != 'usercreation') return;
		$toggleHelper = ACYSMS::get('helper.toggle');

		if(!isset($newMsg['message_receiver']['auto']['usercreation']['delay'])) return;
		$newMsgDelay = $newMsg['message_receiver']['auto']['usercreation']['delay'];

		if(empty($oldMsg->message_receiver['auto']['usercreation'])){

			$delay = strtotime($newMsgDelay['duration'].' '.$newMsgDelay['timevalue']) - time();

			$acyquery = $this->_getAcyQuery($oldMsg->message_id, $delay);
			$nbUser = $acyquery->count($newMsg['message_receiver_table']);
			if(empty($nbUser)) return;

			$timeDisplayed = ACYSMS::getDate(time() - $delay);

			$acyquery = $this->_getAcyQuery($oldMsg->message_id, '');
			$nbUserAll = $acyquery->count($newMsg['message_receiver_table']);

			$text = $toggleHelper->toggleText('plgtrigger', $oldMsg->message_id.'_delay', '', '&function=onAcySMSAutoMsgAdd&plg=acysmsusercreation&plgtype=system&delay=1&msgId='.$oldMsg->message_id, JText::sprintf('SMS_ADD_NEW_MESSAGE_QUEUE_SUBSCRIBED_AFTER', $nbUser, $timeDisplayed));
			$text .= '<br />';
			$text .= $toggleHelper->toggleText('plgtrigger', $oldMsg->message_id, '', '&function=onAcySMSAutoMsgAdd&plg=acysmsusercreation&plgtype=system&msgId='.$oldMsg->message_id, JText::sprintf('SMS_ADD_NEW_MESSAGE_QUEUE_SUBSCRIBED_ALL', $nbUserAll));

			ACYSMS::enqueueMessage($text, 'notice');
			return;
		}

		if(!isset($oldMsg->message_receiver['auto']['usercreation']['delay'])) return;
		$oldMsgDelay = $oldMsg->message_receiver['auto']['usercreation']['delay'];

		if(($oldMsgDelay['duration'] !== $newMsgDelay['duration']) || ($oldMsgDelay['timevalue'] !== $newMsgDelay['timevalue'])){
			$difference = strtotime($newMsgDelay['duration'].' '.$newMsgDelay['timevalue']) - strtotime($oldMsgDelay['duration'].' '.$oldMsgDelay['timevalue']);
			$text = JText::_('SMS_MESSAGE_CHANGED_DELAY_INFORMED');
			$text .= ' '.$toggleHelper->toggleText('plgtrigger', $oldMsg->message_id, '', '&function=onAcySMSAutoMsgUpdate&plg=acysmsusercreation&plgtype=system&difference='.$difference.'&msgId='.$oldMsg->message_id, JText::_('SMS_MESSAGE_CHANGED_DELAY'));

			ACYSMS::enqueueMessage($text, 'notice');
		}
	}

	private function _getAcyQuery($messageId, $msgDelay){
		$messageClass = ACYSMS::get('class.message');
		$message = $messageClass->get($messageId);
		$delayIsPresent = JRequest::getInt('delay', '');

		$integration = ACYSMS::getIntegration($message->message_receiver_table);

		$acyquery = ACYSMS::get('class.acyquery');
		$integration->initQuery($acyquery);
		$acyquery->addMessageFilters($message);
		if($delayIsPresent) $acyquery->where[] = 'UNIX_TIMESTAMP(joomusers.`registerDate`) >'.(time() - $msgDelay);
		return $acyquery;
	}

	public function ajax_onAcySMSAutoMsgUpdate(){

		$messageId = JRequest::getInt('msgId', '');
		if(empty($messageId)) return;

		$diff = JRequest::getInt('difference', '');
		if(empty($diff)) return;

		$queueClass = ACYSMS::get('class.queue');
		$queueClass->plgQueueUpdateSenddate($messageId, $diff);
	}

	public function ajax_onAcySMSAutoMsgAdd(){

		$messageId = JRequest::getInt('msgId', '');
		if(empty($messageId)) return;

		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$newMessage = $messageClass->get($messageId);
		if(empty($newMessage->message_id)){
			echo 'Could not load messageId '.$messageId;
			exit;
		}

		$senddate = strtotime($newMessage->message_receiver['auto']['usercreation']['delay']['duration'].' '.$newMessage->message_receiver['auto']['usercreation']['delay']['timevalue']) - time();

		$integration = ACYSMS::getIntegration($newMessage->message_receiver_table);
		$acyquery = $this->_getAcyQuery($messageId, $senddate);

		$querySelect = $acyquery->getQuery(array($newMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($newMessage->message_receiver_table).', UNIX_TIMESTAMP(joomusers.`registerDate`) +'.$senddate.',0,2'));
		$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`) '.$querySelect;
		$db->setQuery($finalQuery);
		$db->query();
		$nbinserted = $db->getAffectedRows();

		echo JText::sprintf('SMS_ADDED_QUEUE', $nbinserted);
		exit;
	}



	function _createCustomer($user){

		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$defaultCreditsURL = $config->get('default_credits_url', '');

		$senderProfileClass = ACYSMS::get('class.senderprofile');
		$defaultSenderProfileId = $senderProfileClass->getDefaultSenderProfileId();

		$query = 'INSERT INTO #__acysms_customer (customer_joomid, customer_senderprofile_id, customer_credits, customer_credits_url) VALUES ('.intval($user['id']).', '.intval($defaultSenderProfileId).', 0, '.$db->quote($defaultCreditsURL).')';

		$db->setQuery($query);
		$db->query();
	}
}//endclass
