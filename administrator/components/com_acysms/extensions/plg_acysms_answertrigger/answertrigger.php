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

class plgAcysmsAnswerTrigger extends JPlugin{
	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms');
			$this->params = new JParameter($plugin->params);
		}
	}

	function onACYSMSDisplayActionsAnswersTrigger(&$actions, $answerTrigger){
		$newActionSubscribe = new stdClass();
		$newActionSubscribe->name = JText::_('SMS_ANSWER_TRIGGER_SUBSCRIBE');
		$actions['subscribe'] = $newActionSubscribe;

		$newActionUnsubscribe = new stdClass();
		$newActionUnsubscribe->name = JText::_('SMS_ANSWER_TRIGGER_UNSUBSCRIBE');
		$actions['unsubscribe'] = $newActionUnsubscribe;

		$groupType = ACYSMS::get('type.group');
		$newActionSubscribeGroup = new stdClass();
		$newActionSubscribeGroup->name = JText::_('SMS_ACTION_TRIGGER_SUBSCRIBE_GROUP').' : ';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('subscribegroup', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['subscribegroup']) && !empty($answerTrigger->answertrigger_actions['subscribegroup']['group_id'])) $group_id = $answerTrigger->answertrigger_actions['subscribegroup']['group_id'];
		$newActionSubscribeGroup->extra = $groupType->display("data[answertrigger][answertrigger_actions][subscribegroup][group_id]", @$group_id);
		$actions['subscribegroup'] = $newActionSubscribeGroup;

		$newActionUnsubscribeGroup = new stdClass();
		$newActionUnsubscribeGroup->name = JText::_('SMS_ACTION_TRIGGER_UNSUBSCRIBE_GROUP').' : ';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('unsubscribegroup', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['subscribegroup']) && !empty($answerTrigger->answertrigger_actions['unsubscribegroup']['group_id'])) $group_id = $answerTrigger->answertrigger_actions['unsubscribegroup']['group_id'];
		$newActionUnsubscribeGroup->extra = $groupType->display("data[answertrigger][answertrigger_actions][unsubscribegroup][group_id]", @$group_id);
		$actions['unsubscribegroup'] = $newActionUnsubscribeGroup;

		$newActionDeleteAnswer = new stdClass();
		$newActionDeleteAnswer->name = JText::_('SMS_ANSWER_TRIGGER_DELETEANSWER');
		$actions['deleteanswer'] = $newActionDeleteAnswer;

		$newActionForward = new stdClass();
		$newActionForward->name = JText::_('SMS_ANSWER_TRIGGER_FORWARDEMAIL').' : ';
		$emailAddress = '';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('forwardemail', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['forwardemail']) && !empty($answerTrigger->answertrigger_actions['forwardemail']['emailAddress'])) $emailAddress = $answerTrigger->answertrigger_actions['forwardemail']['emailAddress'];
		$newActionForward->extra = '<input type="text" name="data[answertrigger][answertrigger_actions][forwardemail][emailAddress]" value="'.$emailAddress.'"/>';
		$actions['forwardemail'] = $newActionForward;

		$answerMessageType = ACYSMS::get('type.answermessage');
		$newActionAnswerViaSMS = new stdClass();
		$newActionAnswerViaSMS->name = JText::_('SMS_ACTION_TRIGGER_ANSWER_MESSAGE').' : ';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('sendmessage', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['sendmessage']) && !empty($answerTrigger->answertrigger_actions['sendmessage']['message_id'])) $message_id = $answerTrigger->answertrigger_actions['sendmessage']['message_id'];
		$newActionAnswerViaSMS->extra = $answerMessageType->display(@$message_id);
		$actions['sendmessage'] = $newActionAnswerViaSMS;

		$newActionForwardToGroup = new stdClass();
		$newActionForwardToGroup->name = JText::_('SMS_FORWARD_MESSAGE_TO_GROUP').' : ';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('forwardtogroup', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['forwardtogroup']) && !empty($answerTrigger->answertrigger_actions['forwardtogroup']['group_id'])) $group_id = $answerTrigger->answertrigger_actions['forwardtogroup']['group_id'];
		$groupType->groupsInMessageOption = true;
		$newActionForwardToGroup->extra = $groupType->display("data[answertrigger][answertrigger_actions][forwardtogroup][group_id]", @$group_id);

		$actions['forwardtogroup'] = $newActionForwardToGroup;
	}


	public function onACYSMSTriggerActions_forwardemail($actionsParams, $answer){
		if(empty($actionsParams['forwardemail']['emailAddress'])) return;
		$emailAddress = $actionsParams['forwardemail']['emailAddress'];

		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);
		$mailer->addRecipient($emailAddress);
		$subject = JText::sprintf('SMS_ACTION_TRIGGER_FORWARDEMAIL_SUBJECT', $answer->answer_from);
		$mailer->setSubject($subject);
		$body = JText::_('SMS_FROM').' : '.$answer->answer_from.'<br />';
		$body .= JText::_('SMS_TO').' : '.$answer->answer_to.'<br />';
		$body .= JText::_('SMS_RECEPTION_DATE').' : '.date(JText::_('DATE_FORMAT_LC2'), $answer->answer_date).'<br />';
		$body .= JText::_('SMS_SMS_BODY').' : '.$answer->answer_body.'<br />';

		$stringToReplace = array('{answer_body}', '{answer_to}', '{answer_date}', '{answer_from}');
		$values = array($answer->answer_body, $answer->answer_to, $answer->answer_date, $answer->answer_from);

		if(file_exists(ACYSMS_MEDIA.'plugins'.DS.'answer.php')){
			ob_start();
			require(ACYSMS_MEDIA.'plugins'.DS.'answer.php');
			$result = ob_get_clean();
			$body = str_replace($stringToReplace, $values, $result);
		}
		$mailer->setBody(nl2br($body));
		$send = $mailer->Send();
	}

	public function onACYSMSTriggerActions_deleteanswer($actionsParams, $answer){
		if(empty($answer->answer_id)) return;

		$answerClass = ACYSMS::get('class.answer');
		$answerClass->delete($answer->answer_id);
	}

	public function onACYSMSTriggerActions_unsubscribe($actionsParams, $answer){
		if(empty($answer->answer_from)) return;

		$phoneHelper = ACYSMS::get('helper.phone');
		$validNum = $phoneHelper->getValidNum($answer->answer_from);

		if(!$validNum) return;

		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($validNum, 0);


		$userClass = ACYSMS::get('class.user');
		$user = $userClass->getByPhone($validNum);
		if(empty($user)) return;

		$groupUserClass = ACYSMS::get('class.groupuser');
		$groupUserClass->removeAllSubscriptions($user->user_id);
	}

	public function onACYSMSTriggerActions_subscribe($actionsParams, $answer){
		if(empty($answer->answer_from)) return;

		$phoneHelper = ACYSMS::get('helper.phone');

		$validNum = $phoneHelper->getValidNum($answer->answer_from);
		if(!$validNum) return;

		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($validNum, 1);
	}

	public function onACYSMSTriggerActions_subscribegroup($actionsParams, $answer){
		if(empty($answer->answer_from)) return;
		if(empty($actionsParams['subscribegroup']['group_id'])) return;

		$phoneHelper = ACYSMS::get('helper.phone');
		$validNum = $phoneHelper->getValidNum($answer->answer_from);

		if(!$validNum) return;

		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($validNum, 1);

		$userClass = ACYSMS::get('class.user');
		$user = $userClass->getByPhone($validNum);
		if(empty($user)){
			$user = new stdClass();
			$user->user_phone_number = $validNum;
			$user->user_id = $userClass->save($user);
		}

		$groupUserClass = ACYSMS::get('class.groupuser');
		$groupUserClass->addSubscription($user->user_id, array('1' => array($actionsParams['subscribegroup']['group_id'])));
	}

	public function onACYSMSTriggerActions_unsubscribegroup($actionsParams, $answer){
		if(empty($answer->answer_from)) return;
		if(empty($actionsParams['unsubscribegroup']['group_id'])) return;

		$phoneHelper = ACYSMS::get('helper.phone');
		$validNum = $phoneHelper->getValidNum($answer->answer_from);

		if(!$validNum) return;

		$userClass = ACYSMS::get('class.user');
		$user = $userClass->getByPhone($validNum);
		if(empty($user)) return;

		$groupUserClass = ACYSMS::get('class.groupuser');
		$groupUserClass->updateSubscription($user->user_id, array('-1' => array($actionsParams['unsubscribegroup']['group_id'])));
	}

	public function onACYSMSTriggerActions_sendmessage($actionsParams, $answer){

		if(empty($actionsParams['sendmessage']['message_id'])) return;

		$db = JFactory::getDBO();
		$phoneHelper = ACYSMS::get('helper.phone');
		$queueHelper = ACYSMS::get('helper.queue');

		$integration = ACYSMS::getIntegration();
		$validPhone = $phoneHelper->getValidNum($answer->answer_from);

		if(!$validPhone) return false;

		$receiverId = '';

		if(!empty($answer->answer_from)){
			$informations = $integration->getInformationsByPhoneNumber($validPhone);
			if(!empty($informations->receiver_id)) $receiverId = $informations->receiver_id;
		}

		if(empty($informations) && $integration->componentName == 'acysms'){
			$userClass = ACYSMS::get('class.user');
			$newUser = new stdClass();

			$newUser->user_phone_number = $validPhone;
			$receiverId = $userClass->save($newUser);
		}

		if(!empty($receiverId)){
			$query = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`) VALUES ('.intval($actionsParams['sendmessage']['message_id']).','.intval($receiverId).','.$db->Quote($integration->componentName).','.time().',0,2)';
			$db->setQuery($query);
			$db->query();

			$queueHelper->report = false;
			$queueHelper->message_id = $actionsParams['sendmessage']['message_id'];
			$queueHelper->process();
		}else{
			$dispatcher = JDispatcher::getInstance();
			$user = new stdClass();

			$messageClass = ACYSMS::get('class.message');
			$senderProfileClass = ACYSMS::get('class.senderprofile');

			$message = $messageClass->get($actionsParams['sendmessage']['message_id']);

			$dispatcher->trigger('onACYSMSReplaceUserTags', array(&$message, &$user, true));

			$gateway = $senderProfileClass->getGateway($message->message_senderprofile_id);
			$gateway->openSend($message->message_body, $validPhone);
		}
	}

	public function onACYSMSTriggerActions_forwardToGroup($actionsParams, $answer){
		if(empty($answer->answer_from)) return false;

		$messageClass = ACYSMS::get('class.message');
		$groupuserClass = ACYSMS::get('class.groupuser');
		$groupClass = ACYSMS::get('class.group');
		$userClass = ACYSMS::get('class.user');

		$phoneHelper = ACYSMS::get('helper.phone');
		$queueHelper = ACYSMS::get('helper.queue');

		$db = JFactory::getDBO();

		$validPhone = $phoneHelper->getValidNum($answer->answer_from);

		$integration = ACYSMS::getIntegration();
		$sender = $integration->getInformationsByPhoneNumber($validPhone);
		if(empty($sender)) return false;

		$idField = $integration->primaryField;
		$senderId = $sender->$idField;

		$groupsUser = $userClass->getSubscriptionStatus($senderId);

		$groups = '';

		$groupsToSend = array();

		$groupsInMessage = $actionsParams['groupsdetected'];

		if(empty($groupsInMessage)) return false;

		if(!empty($groupsUser)){

			if(empty($actionsParams['forwardtogroup']['group_id'])){
				foreach($groupsInMessage as $oneGroupID){
					$groupsList = $groupClass->getGroups('', $oneGroupID);
					$answer->answer_body = preg_replace('#'.$groupsList[0]->group_name.'#is', '', $answer->answer_body);
					if(array_key_exists($oneGroupID, $groupsUser) && $groupsUser[$oneGroupID]->groupuser_status == 1 && !in_array($oneGroupID, $groupsToSend)){
						array_push($groupsToSend, $oneGroupID);
						$groups .= '.'.$groupsList[0]->group_name;
					}
				}
			}else{
				$groupID = $actionsParams['forwardtogroup']['group_id'];
				if(array_key_exists($groupID, $groupsUser) && $groupsUser[$groupID]->groupuser_status == 1){
					$groupsList = $groupClass->getGroups('', $groupID);
					array_push($groupsToSend, $groupID);
					$groups .= '.'.$groupsList[0]->group_name;
				}
			}

			if(empty($groupsToSend)) return false;

			$msg = new stdClass();
			$msg->message_body = $answer->answer_body;
			$msg->message_subject = 'Forward.SMS.'.time().$groups;
			$msg->message_type = 'standard';
			$msg->message_senddate = time();
			$msg->message_status = 'sent';

			$msgID = $messageClass->save($msg);

			$query = 'INSERT INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`) VALUES ';

			$receivers = array();

			foreach($groupsToSend as $oneGroup){
				$usersGroup = $groupuserClass->getUsers($oneGroup);
				if(empty($usersGroup)) continue;
				foreach($usersGroup as $oneUser){
					if(in_array($oneUser, $receivers)) continue;
					$query .= '('.$msgID.','.$oneUser.','.$db->Quote($integration->componentName).','.time().'),';
					array_push($receivers, $oneUser);
				}
			}
			$query = rtrim($query, ',');


			$db->setQuery($query);
			$db->query();

			$queueHelper->report = false;
			$queueHelper->message_id = $msgID;
			$queueHelper->process();
		}else{
			return false;
		}
	}
}//endclass
