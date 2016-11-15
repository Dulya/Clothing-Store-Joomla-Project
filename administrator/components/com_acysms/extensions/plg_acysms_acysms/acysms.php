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

class plgAcysmsAcySMS extends JPlugin{
	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
	}



	function onACYSMSGetMessageType(&$types, $integration){
		if($integration == 'acysms'){
			$newType = new stdClass();
			$newType->name = JText::_('SMS_AUTO_SUBSCRIPTION');
			$types['acysmssubscription'] = $newType;
		}
	}

	function onACYSMSdisplayParamsAutoMessage_acysmssubscription(){
		$db = JFactory::getDBO();

		$query = 'SELECT group_name, group_id FROM #__acysms_group ORDER BY group_ordering ASC';
		$db->setQuery($query);
		$groups = $db->loadObjectList();

		$timevalue = array();
		$timevalue[] = JHTML::_('select.option', 'hours', JText::_('SMS_HOURS'));
		$timevalue[] = JHTML::_('select.option', 'days', JText::_('SMS_DAYS'));
		$timevalue[] = JHTML::_('select.option', 'weeks', JText::_('SMS_WEEKS'));
		$timevalue[] = JHTML::_('select.option', 'months', JText::_('SMS_MONTHS'));
		$delay = JHTML::_('select.genericlist', $timevalue, "data[message][message_receiver][auto][acysmssubscription][delay][timevalue]", 'size="1" style="width:auto"', 'value', 'text', '0');

		$timeNumber = '<input type="text" name="data[message][message_receiver][auto][acysmssubscription][delay][duration]" class="inputbox" style="width:30px" value="0">';
		echo JText::sprintf('SMS_SEND_GROUP_AUTO', $timeNumber.' '.$delay).'<br />';
		foreach($groups as $oneGroup){ ?>
			<label><input type="checkbox" name="data[message][message_receiver][auto][acysmssubscription][acysmsgroups][<?php echo $oneGroup->group_id; ?>]" value="<?php echo $oneGroup->group_id; ?>" title="<?php echo $oneGroup->group_name ?>"/><?php echo $oneGroup->group_name ?></label> <br/>
		<?php }
	}

	function onAcySMSUserCreate($user){
		$this->_sendEmailToAdmin($user);
	}

	private function _sendEmailToAdmin($user){
		$config = ACYSMS::config();
		$adminAddress = $config->get('admin_address');
		if(empty($adminAddress)) return;

		if(strpos($adminAddress, ',')){
			$recipient = explode(',', $adminAddress);
		}else $recipient = array($adminAddress);

		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);

		if(ACYSMS_J30){
			$sender = array($config->get('config.mailfrom'), $config->get('config.fromname'));
		}else $sender = array($config->getValue('config.mailfrom'), $config->getValue('config.fromname'));
		if(!empty($sender[0]) && !empty($sender[1])) $mailer->setSender($sender);

		$mailer->addRecipient($recipient);
		$subject = JText::_('SMS_ADMIN_NOTIFICATION_EMAIL_SUBJECT');
		$mailer->setSubject($subject);
		$body = JText::_('SMS_ADMIN_NOTIFICATION_EMAIL_BODY');

		foreach($user as $oneUserInformations => $value){
			if(strpos($body, '{'.$oneUserInformations.'}') !== false) $body = str_replace('{'.$oneUserInformations.'}', $value, $body);
		}

		$mailer->setBody($body);
		$send = $mailer->Send();
	}

	function onAcySMSSubscribe($user_id, $groups){
		if(empty($groups[1])) return;
		$groups = $groups[1];

		$integrationFrom = 'acysms';
		$integrationTo = '';

		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');

		if(empty($groups)) return false;

		$receiverField = $config->get('acysms_field');
		if(empty($receiverField)) return;

		$allMessages = $messageClass->getAutoMessage('acysmssubscription');
		if(empty($allMessages)) return false;

		$sendNow = 0;
		foreach($allMessages as $oneMessage){
			$commonLists = array_intersect($groups, $oneMessage->message_receiver['auto']['acysmssubscription']['acysmsgroups']);
			if(empty($commonLists)) continue;


			$acyquery = ACYSMS::get('class.acyquery');
			$integrationTo = $oneMessage->message_receiver_table;
			$integration = ACYSMS::getIntegration($integrationTo);
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);
			$acyquery->addUserFilters(array($user_id), $integrationFrom, $integrationTo);

			$senddate = strtotime('+'.intval($oneMessage->message_receiver['auto']['acysmssubscription']['delay']['duration']).' '.$oneMessage->message_receiver['auto']['acysmssubscription']['delay']['timevalue'], time());
			$querySelect = $acyquery->getQuery(array($oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($oneMessage->message_receiver_table).','.$senddate.',0,2'));


			$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`) '.$querySelect;
			$db->setQuery($finalQuery);
			$db->query();

			if(empty($oneMessage->message_receiver['auto']['acysmssubscription']['delay']['duration'])) $sendNow = $oneMessage->message_id;
		}

		if(!empty($sendNow)){
			$queueHelper = ACYSMS::get('helper.queue');
			$queueHelper->report = false;
			$queueHelper->message_id = $sendNow;
			$queueHelper->process();
		}
	}

	function onAcySMSUnsubscribe($user_id, $groups){
		if(empty($user_id) || empty($groups[-1])) return;

		$UnsubscribeGroups = $groups[-1];
		$messageToDelete = array();
		$db = JFactory::getDBO();

		$messageClass = ACYSMS::get('class.message');
		$allMessages = $messageClass->getAutoMessage('acysmssubscription');
		if(empty($allMessages)) return;

		foreach($allMessages as $oneMessage){
			if(!empty($oneMessage->message_receiver['auto']['acysmssubscription']['acysmsgroups'])){
				foreach($oneMessage->message_receiver['auto']['acysmssubscription']['acysmsgroups'] as $oneGroup){
					if(in_array($oneGroup, $UnsubscribeGroups)) $messageToDelete[$oneMessage->message_id] = $oneMessage->message_id;
				}
			}
		}
		if(empty($messageToDelete)) return;

		JArrayHelper::toInteger($messageToDelete);

		$db->setQuery('DELETE  FROM '.ACYSMS::table('queue').' WHERE `queue_receiver_id` = '.intval($user_id).' AND `queue_message_id` IN ('.implode(',', $messageToDelete).')');
		$db->query();
	}



	public function onAcySMSSaveMessage($oldMsg, $newMsg){
		if(empty($newMsg['message_type']) || $newMsg['message_type'] != 'auto' || empty($newMsg['message_autotype'])) return;
		if($newMsg['message_autotype'] != 'acysmssubscription') return;
		$toggleHelper = ACYSMS::get('helper.toggle');
		$db = JFactory::getDBO();

		if(!isset($newMsg['message_receiver']['auto']['acysmssubscription']['delay'])) return;
		$newMsgDelay = $newMsg['message_receiver']['auto']['acysmssubscription']['delay'];

		if(empty($newMsg['message_receiver']['auto']['acysmssubscription']['acysmsgroups'])) return;


		if(empty($oldMsg->message_receiver['auto']['acysmssubscription'])){

			$delay = strtotime($newMsgDelay['duration'].' '.$newMsgDelay['timevalue']) - time();

			$acyquery = $this->_getAcyQuery($oldMsg->message_id, $delay);
			$nbUser = $acyquery->count($newMsg['message_receiver_table']);
			if(empty($nbUser)) return;

			$timeDisplayed = ACYSMS::getDate(time() - $delay);

			$acyquery = $this->_getAcyQuery($oldMsg->message_id, '');
			$nbUserAll = $acyquery->count($newMsg['message_receiver_table']);

			$text = $toggleHelper->toggleText('plgtrigger', $oldMsg->message_id.'_delay', '', '&function=onAcySMSAutoMsgAdd&plg=acysms&plgtype=acysms&delay=1&msgId='.$oldMsg->message_id, JText::sprintf('SMS_ADD_NEW_MESSAGE_QUEUE_SUBSCRIBED_AFTER', $nbUser, $timeDisplayed));
			$text .= '<br />';
			$text .= $toggleHelper->toggleText('plgtrigger', $oldMsg->message_id, '', '&function=onAcySMSAutoMsgAdd&plg=acysms&plgtype=acysms&msgId='.$oldMsg->message_id, JText::sprintf('SMS_ADD_NEW_MESSAGE_QUEUE_SUBSCRIBED_ALL', $nbUserAll));

			ACYSMS::enqueueMessage($text, 'notice');
			return;
		}

		if(!isset($oldMsg->message_receiver['auto']['acysmssubscription']['delay'])) return;
		$oldMsgDelay = $oldMsg->message_receiver['auto']['acysmssubscription']['delay'];

		if(($oldMsgDelay['duration'] !== $newMsgDelay['duration']) || ($oldMsgDelay['timevalue'] !== $newMsgDelay['timevalue'])){
			$difference = strtotime($newMsgDelay['duration'].' '.$newMsgDelay['timevalue']) - strtotime($oldMsgDelay['duration'].' '.$oldMsgDelay['timevalue']);
			$text = JText::_('SMS_MESSAGE_CHANGED_DELAY_INFORMED');
			$text .= ' '.$toggleHelper->toggleText('plgtrigger', $oldMsg->message_id, '', '&function=onAcySMSAutoMsgUpdate&plg=acysms&plgtype=acysms&difference='.$difference.'&msgId='.$oldMsg->message_id, JText::_('SMS_MESSAGE_CHANGED_DELAY'));

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
		$acyquery->join['groupuser'] = ' JOIN #__acysms_groupuser as groupuser ON acysmsusers.user_id = groupuser.groupuser_user_id';

		JArrayHelper::toInteger($message->message_receiver['auto']['acysmssubscription']['acysmsgroups']);

		$acyquery->where[] = ' groupuser.groupuser_status = 1 AND groupuser_group_id IN ('.implode(',', $message->message_receiver['auto']['acysmssubscription']['acysmsgroups']).')';
		if($delayIsPresent) $acyquery->where[] = 'groupuser.`groupuser_subdate` >'.(time() - $msgDelay);
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
		$delay = JRequest::getInt('delay', '');

		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$newMessage = $messageClass->get($messageId);
		if(empty($newMessage->message_id)){
			echo 'Could not load messageId '.$messageId;
			exit;
		}

		$integrationName = "";
		$integration = ACYSMS::getIntegration($newMessage->message_receiver_table);


		$senddate = strtotime($newMessage->message_receiver['auto']['acysmssubscription']['delay']['duration'].' '.$newMessage->message_receiver['auto']['acysmssubscription']['delay']['timevalue']) - time();

		$integration = ACYSMS::getIntegration($newMessage->message_receiver_table);
		$acyquery = $this->_getAcyQuery($messageId, $senddate);
		$querySelect = $acyquery->getQuery(array($newMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($newMessage->message_receiver_table).', groupuser.`groupuser_subdate` +'.$senddate.',0,2'));

		$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`) '.$querySelect;
		$db->setQuery($finalQuery);
		$db->query();
		$nbinserted = $db->getAffectedRows();

		echo JText::sprintf('SMS_ADDED_QUEUE', $nbinserted);
		exit;
	}


	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){

		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		if(!$app->isAdmin()){
			$helperPlugin = ACYSMS::get('helper.plugins');
			if(!$helperPlugin->allowSendByGroups('acysmsgroup')) return;
		}

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_X_GROUPS', 'AcySMS');
		$filters['communityFilters']['acysmsGroups'] = $newFilter;
	}

	function onACYSMSDisplayFilterParams_acysmsGroups($message){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$my = JFactory::getUser();
		if(!ACYSMS_J16){
			$myJoomlaGroups = array($my->gid);
		}else{
			jimport('joomla.access.access');
			$myJoomlaGroups = JAccess::getGroupsByUser($my->id, false);
		}

		$extraCondition = '';

		if(!$app->isAdmin()){
			$frontEndFilters = $config->get('frontEndFilters');
			if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

			$availableFrontGroupFilters = array();
			foreach($frontEndFilters as $oneCondition){
				if($oneCondition['filters'] != 'acysmsgroup') continue;
				if(empty($oneCondition['filterDetails']) || empty($oneCondition['filterDetails']['acysmsgroup'])) continue;
				if($oneCondition['typeDetails'] != 'all' && !in_array($oneCondition['typeDetails'], $myJoomlaGroups)) continue;
				$availableFrontGroupFilters += $oneCondition['filterDetails']['acysmsgroup'];
			}
			if(empty($availableFrontGroupFilters)) return;

			if(in_array('userowngroups', $availableFrontGroupFilters)){
				$groupClass = ACYSMS::get('class.group');
				$myOwnGroups = $groupClass->getFrontendGroups('group_id');
				if(!empty($myOwnGroups)){
					foreach($myOwnGroups as $oneGroup){
						$availableFrontGroupFilters[] = $oneGroup->group_id;
					}
				}
			}
			JArrayHelper::toInteger($availableFrontGroupFilters);
			$extraCondition = ' WHERE group_id IN ('.implode(',', $availableFrontGroupFilters).')';
		}

		$query = 'SELECT group_name, group_id FROM #__acysms_group '.$extraCondition.' ORDER BY group_ordering ASC';
		$db->setQuery($query);
		$groups = $db->loadObjectList();

		echo JText::sprintf('SMS_SEND_X_GROUPS', 'AcySMS').' : <br />';
		foreach($groups as $oneGroup){
			if(!$app->isAdmin()){
				if(!in_array($oneGroup->group_id, $availableFrontGroupFilters)) continue;
			} ?>
			<label><input type="checkbox" name="data[message][message_receiver][standard][acysms][acysmsGroups][<?php echo $oneGroup->group_id; ?>]" value="<?php echo $oneGroup->group_id ?>" title="<?php echo $oneGroup->group_name ?>"/> <?php echo $oneGroup->group_name ?></label><br/>
		<?php }
	}

	function onACYSMSSelectData_acysmsGroups(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['acysms']['acysmsGroups'])) return;
		if(!isset($acyquery->join['acysmsusers']) && $message->message_receiver_table != 'acysms') $acyquery->join['acysmsusers'] = 'JOIN #__acysms_user AS acysmsusers ON acysmsusers.user_joomid = joomusers.id';
		$acyquery->join['acysmsgroupuser'] = 'JOIN #__acysms_groupuser as acysmsgroupuser ON acysmsgroupuser.groupuser_user_id = acysmsusers.user_id ';

		JArrayHelper::toInteger($message->message_receiver['standard']['acysms']['acysmsGroups']);

		$acyquery->where[] = ' acysmsgroupuser.groupuser_group_id IN ('.implode(',', ($message->message_receiver['standard']['acysms']['acysmsGroups'])).') AND acysmsgroupuser.groupuser_status = 1';
	}

	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_ACYSMS_GROUP');
		$authorizedFilters['acysmsgroup'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_acysmsgroup(&$authorizedFiltersSelection, $conditionNumber){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT group_id, group_name FROM #__acysms_group');
		$acySMSGroups = $db->loadObjectList();

		if(empty($acySMSGroups)) return;

		$ownGroupsObject = new stdClass();
		$ownGroupsObject->group_id = 'userowngroups';
		$ownGroupsObject->group_name = JText::_('SMS_USER_OWN_GROUPS');
		array_unshift($acySMSGroups, $ownGroupsObject);

		$config = ACYSMS::config();
		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

		$result = '<br />';
		foreach($acySMSGroups as $oneGroup){
			if(!empty($frontEndFilters[$conditionNumber]['filterDetails']['acysmsgroup']) && in_array($oneGroup->group_id, $frontEndFilters[$conditionNumber]['filterDetails']['acysmsgroup'])){
				$checked = 'checked="checked"';
			}else $checked = '';
			$result .= '<label><input type="checkbox" name="config[frontEndFilters]['.$conditionNumber.'][filterDetails][acysmsgroup]['.$oneGroup->group_id.']" value="'.$oneGroup->group_id.'" '.$checked.' title= "'.$oneGroup->group_name.'"/> '.$oneGroup->group_name.'</label><br />';
		}
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails">'.$result.'</span>';
	}

	public function onACYSMSdisplayRequiredFilters(&$requiredFilters){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_ACYSMS_GROUP');
		$requiredFilters['acysmsgroup'] = $newType;
	}

	public function onAcySMSAllowSend_acysmsgroup($messageReceiver, &$answer){
		if(empty($messageReceiver['standard']['acysms']['acysmsGroups'])){
			$answer->msg = JText::_('SMS_PLEASE_SELECT_GROUP');
			$answer->result = false;
			return;
		}
		$answer->result = true;
	}
}//endclass
