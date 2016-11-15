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

class plgAcySMSFreestyleSupport extends JPlugin{
	var $messages = array();
	var $debug = false;

	function __construct(&$subject, $config){

		parent::__construct($subject, $config);
		if(!$this->init()) return;
	}

	function init(){
		if(!file_exists(rtrim(JPATH_SITE, DS).DS.'components'.DS.'com_fss')) return;

		if(file_exists(rtrim(JPATH_SITE, DS).DS.'components'.DS.'com_fss'.DS.'plugins'.DS.'tickets'.DS.'aycsms_freestylesupport.php')){
			unlink(rtrim(JPATH_SITE, DS).DS.'components'.DS.'com_fss'.DS.'plugins'.DS.'tickets'.DS.'aycsms_freestylesupport.php');
		}

		if(!copy(dirname(__FILE__).DS.'acysms_freestylesupport.php', rtrim(JPATH_SITE, DS).DS.'components'.DS.'com_fss'.DS.'plugins'.DS.'tickets'.DS.'acysms_freestylesupport.php')){
			ACYSMS::enqueueMessage('The Freestyle support plugin for AcySMS can\'t copy the file acysms_freestylesupport.php in the Freestyle Support directory. Please share this message with your admin.');
		}

		if(defined('ACYSMS_COMPONENT')) return true;
		$acySmsHelper = rtrim(JPATH_ROOT, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper)){
			include_once $acySmsHelper;
		}else return false;
		return defined('ACYSMS_COMPONENT');
	}

	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_BASED_ON_X_NOTIFICATION', 'Freestyle Support');
		$types['fsnotification'] = $newType;

		$secondType = new stdClass();
		$secondType->name = JText::sprintf('SMS_BASED_ON_FREESTYLE_TICKET_UPDATE');
		$types['fsticketupdatereminder'] = $secondType;
	}

	function onACYSMSdisplayParamsAutoMessage_fsnotification($message){

		$triggerValues = array();
		$triggerValues[] = JHTML::_('select.option', 'ticketCreated', JText::_('SMS_TICKET_CREATED'));
		$triggerValues[] = JHTML::_('select.option', 'ticketReplied', JText::_('SMS_TICKET_REPLIED'));
		$triggerDropdown = JHTML::_('select.genericlist', $triggerValues, "data[message][message_receiver][auto][fsnotification][trigger]", 'size="1" style="width:auto"', 'value', 'text');

		echo JText::sprintf('SMS_BASED_ON_X_NOTIFICATION', $triggerDropdown).'<br />';

		$receiverType = '';
		if(!empty($message->message_receiver['auto']['fsnotification']['receiverType'])) $receiverType = $message->message_receiver['auto']['fsnotification']['receiverType'];

		$oneUserSelected = '';
		if(empty($receiverType) || $receiverType == 'oneUser') $oneUserSelected = 'checked';

		$threadSubscribersSelected = '';
		if(!empty($receiverType) && $receiverType == 'threadSubscribers') $threadSubscribersSelected = 'checked';

		$userAssignedSelected = '';
		if(!empty($receiverType) && $receiverType == 'userAssigned') $userAssignedSelected = 'checked';


		$receiverSelection = '<input type="radio" name="data[message][message_receiver][auto][fsnotification][receiverType]" '.$oneUserSelected.' value="oneUser" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'block\';" id="fs_oneReceiver"/> <label for="fs_oneReceiver">'.JText::_('SMS_SPECIFIC_USER').'</label>';
		$receiverSelection .= '<input type="radio" name="data[message][message_receiver][auto][fsnotification][receiverType]" '.$threadSubscribersSelected.' value="threadSubscribers" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'none\';" id="fs_threadSubscribers"/> <label for="fs_threadSubscribers">'.JText::_('SMS_THREAD_SUBSCRIBERS').'</label>';
		$receiverSelection .= '<input type="radio" name="data[message][message_receiver][auto][fsnotification][receiverType]" '.$userAssignedSelected.' value="userAssigned" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'none\';" id="fs_userAssigned"/> <label for="fs_userAssigned">'.JText::_('SMS_USER_ASSIGNED_TO_TICKET').'</label>';

		echo JText::sprintf('SMS_SEND_MESSAGE_TO', $receiverSelection);


		$userName = '';
		if(!empty($message->message_receiver['auto']['fsnotification']['fsNotification_receiverName'])) $userName = $message->message_receiver['auto']['fsnotification']['fsNotification_receiverName'];

		echo '<br/>';

		$style = '';
		if(!empty($threadSubscribersSelected) || !empty($userAssignedSelected)) $style = 'style="display:none"';
		echo '<div id="oneReceiverParameters" '.$style.'>';
		echo '<input type="hidden" id="fsNotification_id" name="data[message][message_receiver][auto][fsnotification][fsNotification_receiverid]"/>';
		echo JText::sprintf('SMS_SELECT_USER', '<span id="fsNotification_phone"/>'.$userName.'</span><a class="modal" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=choose&&jsFct=affectTestUser&htmlID=fsNotification&currentIntegration='.$message->message_receiver_table.'\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>');
		echo '</div>';
	}

	function onACYSMSdisplayParamsAutoMessage_fsticketupdatereminder($message){

		$ticketStatus = array();
		$ticketPriority = array();
		$db = JFactory::getDBO();

		$query = 'SELECT title, id FROM #__fss_ticket_status';
		$db->setQuery($query);
		$status = $db->loadObjectList();

		$ticketStatus[] = JHTML::_('select.option', '', JText::_('SMS_ALL_STATUS'));
		foreach($status as $oneStatus){
			$ticketStatus[] = JHTML::_('select.option', $oneStatus->id, $oneStatus->title);
		}
		$ticketStatusDropdown = JHTML::_('select.genericlist', $ticketStatus, "data[message][message_receiver][auto][fsticketupdatereminder][status]", 'size="1" style="width:auto"', 'value', 'text');

		$relationValue = array();
		$relationValue[] = JHTML::_('select.option', 'and', JText::_('SMS_AND'));
		$relationValue[] = JHTML::_('select.option', 'or', JText::_('SMS_OR'));
		$relation = JHTML::_('select.genericlist', $relationValue, "data[message][message_receiver][auto][fsticketupdatereminder][relation]", 'size="1" style="width:auto"', 'value', 'text', '0');

		$query = 'SELECT title, id FROM #__fss_ticket_pri';
		$db->setQuery($query);
		$priority = $db->loadObjectList();

		$ticketPriority[] = JHTML::_('select.option', '', JText::_('SMS_ALL_PRIORITIES'));
		foreach($priority as $onePriority){
			$ticketPriority[] = JHTML::_('select.option', $onePriority->id, $onePriority->title);
		}
		$ticketPriorityDropdown = JHTML::_('select.genericlist', $ticketPriority, "data[message][message_receiver][auto][fsticketupdatereminder][priority]", 'size="1" style="width:auto"', 'value', 'text');


		$timevalues = array();
		$timevalues[] = JHTML::_('select.option', 'HOUR', JText::_('SMS_HOURS'));
		$timevalues[] = JHTML::_('select.option', 'DAY', JText::_('SMS_DAYS'));
		$timevalues[] = JHTML::_('select.option', 'WEEK', JText::_('SMS_WEEKS'));
		$timevalues[] = JHTML::_('select.option', 'MONTH', JText::_('SMS_MONTHS'));

		$timeValue = JHTML::_('select.genericlist', $timevalues, "data[message][message_receiver][auto][fsticketupdatereminder][timeUnit]", 'size="1" style="width:auto"', 'value', 'text', '0');
		$timeUnit = '<input type="text" name="data[message][message_receiver][auto][fsticketupdatereminder][timeValue]" class="inputbox" style="width:30px" value="0">';


		echo JText::sprintf('SMS_FREESTYLE_TICKET_REMINDER', $ticketStatusDropdown.$relation, $ticketPriorityDropdown, $timeUnit.$timeValue).'<br />';

		$receiverType = '';
		if(!empty($message->message_receiver['auto']['fsticketupdatereminder']['receiverType'])) $receiverType = $message->message_receiver['auto']['fsticketupdatereminder']['receiverType'];

		$oneUserSelected = '';
		if(empty($receiverType) || $receiverType == 'oneUser') $oneUserSelected = 'checked';

		$threadSubscribersSelected = '';
		if(!empty($receiverType) && $receiverType == 'threadSubscribers') $threadSubscribersSelected = 'checked';

		$userAssignedSelected = '';
		if(!empty($receiverType) && $receiverType == 'userAssigned') $userAssignedSelected = 'checked';


		$receiverSelection = '<input type="radio" name="data[message][message_receiver][auto][fsticketupdatereminder][receiverType]" '.$oneUserSelected.' value="oneUser" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'block\';" id="fs_oneReceiver"/> <label for="fs_oneReceiver">'.JText::_('SMS_SPECIFIC_USER').'</label>';
		$receiverSelection .= '<input type="radio" name="data[message][message_receiver][auto][fsticketupdatereminder][receiverType]" '.$threadSubscribersSelected.' value="threadSubscribers" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'none\';" id="fs_threadSubscribers"/> <label for="fs_threadSubscribers">'.JText::_('SMS_THREAD_SUBSCRIBERS').'</label>';
		$receiverSelection .= '<input type="radio" name="data[message][message_receiver][auto][fsticketupdatereminder][receiverType]" '.$userAssignedSelected.' value="userAssigned" onclick="document.getElementById(\'oneReceiverParameters\').style.display = \'none\';" id="fs_userAssigned"/> <label for="fs_userAssigned">'.JText::_('SMS_USER_ASSIGNED_TO_TICKET').'</label>';

		echo JText::sprintf('SMS_SEND_MESSAGE_TO', $receiverSelection);

		echo '<br/>';

		$style = '';
		if(!empty($threadSubscribersSelected) || !empty($userAssignedSelected)) $style = 'style="display:none"';
		echo '<div id="oneReceiverParameters" '.$style.'>';
		echo '<input type="hidden" id="fsticketupdatereminder_id" name="data[message][message_receiver][auto][fsticketupdatereminder][fsticketupdatereminder_receiverid]"/>';


		$receiverId = '';
		if(!empty($message->message_receiver['auto']['fsticketupdatereminder']['fsticketupdatereminder_receiverid'])) $receiverId = $message->message_receiver['auto']['fsticketupdatereminder']['fsticketupdatereminder_receiverid'];

		$integration = ACYSMS::getIntegration($message->message_receiver_table);
		$newObject = new stdClass();
		$newObject->queue_receiver_id = $receiverId;
		$arrayInformation = array($newObject);
		$integration->addUsersInformations($arrayInformation);

		$userInformation = reset($arrayInformation);
		$receiverInfo = '';

		if(!empty($userInformation) && !empty($userInformation->receiver_name) && !empty($userInformation->receiver_phone)) $receiverInfo = $userInformation->receiver_name.' ('.$userInformation->receiver_phone.')';

		echo JText::sprintf('SMS_SELECT_USER', '<span id="fsticketupdatereminder_phone"/>'.$receiverInfo.'</span><a class="modal" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=choose&&jsFct=affectTestUser&htmlID=fsticketupdatereminder&currentIntegration='.$message->message_receiver_table.'\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><img class="icon16" src="'.ACYSMS_IMAGES.'icons/icon-16-edit.png"/></a>');
		echo '</div>';
	}

	function onAcySMS_FreestyleSupportSendNotification($ticket, $params, $status){

		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$messageClass = ACYSMS::get('class.message');
		$allMessages = $messageClass->getAutoMessage('fsnotification');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No Freestyle Support notification message configured in AcySMS, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create a message</a> using the type : Automatic Based on Freestyle Support Notification';
			return false;
		}

		foreach($allMessages as $oneMessage){

			if($oneMessage->message_receiver['auto']['fsnotification']['trigger'] != $status) continue;

			$integration = ACYSMS::getIntegration($oneMessage->message_receiver_table);
			$receivers = array();

			if($oneMessage->message_receiver['auto']['fsnotification']['receiverType'] == 'oneUser'){

				if(empty($oneMessage->message_receiver['auto']['fsnotification']['fsNotification_receiverid'])){
					$this->messages[] = "Please select the user who will receive the notifications for the SMS ".$oneMessage->message_id;
					continue;
				}
				$receivers = array($oneMessage->message_receiver['auto']['fsnotification']['fsNotification_receiverid']);
			}else if($oneMessage->message_receiver['auto']['fsnotification']['receiverType'] == 'threadSubscribers'){

				$subscribedUserQuery = 'SELECT DISTINCT user_id
											FROM #__fss_ticket_messages AS messages
											JOIN rjapw_users AS users
											ON messages.user_id = users.id
											WHERE messages.ticket_ticket_id = '.intval($ticket->id);

				$db->setQuery($subscribedUserQuery);
				$subscribedUsers = $db->loadResultArray();
				if(empty($subscribedUsers)) continue;

				if(!empty($ticket->user_id) && in_array($ticket->user_id, $subscribedUsers)) unset($subscribedUsers[array_search($ticket->user_id, $subscribedUsers)]);


				$receivers = $integration->getReceiverIDs($subscribedUsers);
				if(empty($receivers)) continue;
			}else if($oneMessage->message_receiver['auto']['fsnotification']['receiverType'] == 'userAssigned'){
				if(empty($ticket->user_id)) continue;
				$receivers = $integration->getReceiverIDs(array($ticket->user_id));
				if(empty($receivers)) continue;
			}else continue;


			$config = ACYSMS::config();
			$db = JFactory::getDBO();
			$acyquery = ACYSMS::get('class.acyquery');
			$integrationTo = $oneMessage->message_receiver_table;
			$integrationFrom = $integration->componentName;
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);
			if(!empty($receivers)) $acyquery->addUserFilters($receivers, $integrationFrom, $integrationTo);
			$querySelect = $acyquery->getQuery(array('DISTINCT '.$oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.' , "'.$integration->componentName.'", '.time().', '.$config->get('priority_message', 3)));
			$finalQuery = 'INSERT IGNORE INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority) '.$querySelect;
			$db->setQuery($finalQuery);
			$db->query();

			$queueHelper = ACYSMS::get('helper.queue');
			$queueHelper->report = false;
			$queueHelper->message_id = $oneMessage->message_id;
			$queueHelper->process();
		}
	}

	function onAcySMSTestPlugin(){
		$this->debug = true;
		$this->onACYSMSCron();
		ACYSMS::display($this->messages);
	}

	function onACYSMSCron(){
		$db = JFactory::getDBO();

		$messageClass = ACYSMS::get('class.message');
		$allMessages = $messageClass->getAutoMessage('fsticketupdatereminder');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No Freestyle Support automatic message based on  Freestyle support ticket last update configured in AcySMS, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create a message</a> using the type : Automatic Based on Freestyle support ticket last update';
			return false;
		}

		$timeValuesAllowed = array('HOUR', 'DAY', 'WEEK', 'MONTH');
		$relationsAllowed = array('and', 'or');

		foreach($allMessages as $oneMessage){

			if(empty($oneMessage->message_receiver['auto']['fsticketupdatereminder']['status']) && empty($oneMessage->message_receiver['auto']['fsticketupdatereminder']['priority'])){
				$this->messages[] = 'Date plugin: 0 SMS inserted in the queue for the SMS '.$oneMessage->message_subject;
				continue;
			}

			if(empty($oneMessage->message_receiver['auto']['fsticketupdatereminder']['timeValue']) && empty($oneMessage->message_receiver['auto']['fsticketupdatereminder']['timeUnit'])){
				$this->messages[] = 'Date plugin: 0 SMS inserted in the queue for the SMS '.$oneMessage->message_subject;
				continue;
			}

			$conditions = array();
			$where = array();

			$priority = $oneMessage->message_receiver['auto']['fsticketupdatereminder']['priority'];
			$ticketStatus = $oneMessage->message_receiver['auto']['fsticketupdatereminder']['status'];
			$relation = $oneMessage->message_receiver['auto']['fsticketupdatereminder']['relation'];
			$timeValue = $oneMessage->message_receiver['auto']['fsticketupdatereminder']['timeValue'];
			$timeUnit = $oneMessage->message_receiver['auto']['fsticketupdatereminder']['timeUnit'];

			if(!empty($priority)) $conditions[] = 'ticket_pri_id = '.intval($priority);
			if(!empty($ticketStatus)) $conditions[] = 'ticket_status_id = '.intval($ticketStatus);

			if(count($conditions) > 1){
				if(!in_array($relation, $relationsAllowed)){
					$this->messages[] = 'Date plugin: 0 SMS inserted in the queue for the SMS '.$oneMessage->message_subject;
					continue;
				}

				$where[] = implode(' '.$relation.' ', $conditions);
			}else $where[] = reset($conditions);

			if(!in_array($timeUnit, $timeValuesAllowed)){
				$this->messages[] = 'Date plugin: 0 SMS inserted in the queue for the SMS '.$oneMessage->message_subject;
				continue;
			}

			$date = ACYSMS::getDate(strtotime("-".intval($timeValue)." ".strtolower($timeUnit)), "Y-m-d G:i:s");
			$cronDate = ACYSMS::getDate(strtotime("-".intval($timeValue)." ".strtolower($timeUnit)." + 15 minute"), "Y-m-d G:i:s");

			$cronDate = date("Y-m-d G:i:s", strtotime("-".intval($timeValue)." ".strtolower($timeUnit)." + 15 minute"));

			$where[] = "lastupdate > ".$db->Quote($date);
			$where[] = "lastupdate < ".$db->Quote($cronDate);


			$query = 'SELECT * FROM #__fss_ticket_ticket
 						WHERE '.implode(' AND ', $where);
			$db->setQuery($query);
			$fsTickets = $db->loadObjectList();

			if(empty($fsTickets)){
				$this->messages[] = 'Date plugin: 0 SMS inserted in the queue for the SMS '.$oneMessage->message_subject;
				continue;
			}

			$integration = ACYSMS::getIntegration($oneMessage->message_receiver_table);

			foreach($fsTickets as $ticket){
				$receivers = array();

				if($oneMessage->message_receiver['auto']['fsticketupdatereminder']['receiverType'] == 'oneUser'){

					if(empty($oneMessage->message_receiver['auto']['fsticketupdatereminder']['fsticketupdatereminder_receiverid'])){
						$this->messages[] = "Please select the user who will receive the notifications for the SMS ".$oneMessage->message_id;
						continue;
					}
					$receivers = array($oneMessage->message_receiver['auto']['fsticketupdatereminder']['fsticketupdatereminder_receiverid']);
				}else if($oneMessage->message_receiver['auto']['fsticketupdatereminder']['receiverType'] == 'threadSubscribers'){

					$subscribedUserQuery = 'SELECT DISTINCT user_id
											FROM #__fss_ticket_messages AS messages
											JOIN rjapw_users AS users
											ON messages.user_id = users.id
											WHERE messages.ticket_ticket_id = '.intval($ticket->id);

					$db->setQuery($subscribedUserQuery);
					$subscribedUsers = $db->loadResultArray();
					if(empty($subscribedUsers)) continue;

					if(!empty($ticket->user_id) && in_array($ticket->user_id, $subscribedUsers)) unset($subscribedUsers[array_search($ticket->user_id, $subscribedUsers)]);


					$receivers = $integration->getReceiverIDs($subscribedUsers);
					if(empty($receivers)) continue;
				}else if($oneMessage->message_receiver['auto']['fsticketupdatereminder']['receiverType'] == 'userAssigned'){
					if(empty($ticket->user_id)) continue;
					$receivers = $integration->getReceiverIDs(array($ticket->user_id));
					if(empty($receivers)) continue;
				}else continue;


				$config = ACYSMS::config();
				$db = JFactory::getDBO();
				$acyquery = ACYSMS::get('class.acyquery');
				$integrationTo = $oneMessage->message_receiver_table;
				$integrationFrom = $integration->componentName;
				$integration->initQuery($acyquery);
				$acyquery->addMessageFilters($oneMessage);
				if(!empty($receivers)) $acyquery->addUserFilters($receivers, $integrationFrom, $integrationTo);

				$querySelect = $acyquery->getQuery(array('DISTINCT '.$oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.' , "'.$integration->componentName.'", '.(time() + 900).', '.$config->get('priority_message', 3)));
				$finalQuery = 'INSERT IGNORE INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority) '.$querySelect;
				$db->setQuery($finalQuery);
				$db->query();
			}

			$nbInserted = $db->getAffectedRows();
			$this->messages[] = 'Date plugin: '.$nbInserted.' SMS inserted in the queue for the SMS '.$oneMessage->message_subject;

			$queueHelper = ACYSMS::get('helper.queue');
			$queueHelper->report = false;
			$queueHelper->message_id = $oneMessage->message_id;
			$queueHelper->process();
		}
	}
}//endclasss
