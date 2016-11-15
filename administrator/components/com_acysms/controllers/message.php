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

class MessageController extends ACYSMSController{

	var $aclCat = 'messages';

	function copy(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('messages', 'copy')) return;

		$db = JFactory::getDBO();
		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();

		$user = JFactory::getUser();
		foreach($cids as $oneMessageid){
			$query = 'INSERT INTO `#__acysms_message`  (`message_userid`,`message_receiver_table`,`message_subject`,`message_body`,`message_type`,`message_status`,`message_category_id`,`message_senderid`,`message_senderprofile_id`,`message_created`)';
			$query .= " SELECT ".$user->id.",`message_receiver_table`,CONCAT('copy_',`message_subject`),`message_body`, 'draft','notsent',`message_category_id`,`message_senderid`,`message_senderprofile_id`,".time()." FROM `#__acysms_message` WHERE `message_id` = ".intval($oneMessageid);
			$db->setQuery($query);
			$db->query();
		}
		return $this->listing();
	}

	function store(){
		JRequest::checkToken() or die('Invalid Token');

		if(!$this->isAllowed('messages', 'manage')) return;
		$app = JFactory::getApplication();
		$messageClass = ACYSMS::get('class.message');
		$oldMsgid = ACYSMS::getCID('message_id');
		$oldMsg = $messageClass->get($oldMsgid);


		$data = JRequest::getVar('data');

		$newMessage = $data['message'];

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$resultArray = $dispatcher->trigger('acysmsCheckCustomAccessMessage');
		$result = reset($resultArray);

		if(!$app->isAdmin() && !empty($oldMsgid) && !$messageClass->checkMsgAccess($oldMsgid, JFactory::getUser()) && !$result) $app->redirect('index.php', 'You are not allowed to save this message !', 'error');

		$status = $messageClass->saveForm();

		if(!empty($newMessage['message_type']) && $newMessage['message_type'] == 'answer'){
			$subject = addslashes($data['message']['message_subject']);
			$message_id = JRequest::getInt('message_id');

			$js = "var mydrop = window.top.document.getElementById('dataanswertriggeranswertrigger_actionssendmessagemessage_id'); ";
			$js .= "var type = 'answer';";

			if(empty($oldMsgid)){
				$js .= 'var optn = document.createElement("OPTION");';
				$js .= "optn.text = '[$message_id] $subject'; optn.value = '$message_id';";
				$js .= 'mydrop.options.add(optn);';
				$js .= 'lastid = 0; while(mydrop.options[lastid+1]){lastid = lastid+1;} mydrop.selectedIndex = lastid;';
				$js .= 'window.top.changeMessage(type,'.$message_id.');';
			}else{
				$js .= "lastid = 0; notfound = true; while(notfound && mydrop.options[lastid]){if(mydrop.options[lastid].value == $message_id){mydrop.options[lastid].text = '[$message_id] $subject';notfound = false;} lastid = lastid+1;}";
			}
			if(ACYSMS_J30) $js .= 'window.top.jQuery("#dataanswertriggeranswertrigger_actionssendmessagemessage_id").trigger("liszt:updated");';
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
		}


		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAcySMSSaveMessage', array($oldMsg, $newMessage));

		if(!$status){
			ACYSMS::enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!empty($messageClass->errors)){
				foreach($messageClass->errors as $oneError){
					ACYSMS::enqueueMessage($oneError, 'error');
				}
			}
		}else{
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'success');
		}
	}

	function remove(){
		JRequest::checkToken() || JRequest::checkToken('get') || JSession::checkToken('get') || die('Invalid Token');
		if(!$this->isAllowed('messages', 'delete')) return;

		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();
		$messageClass = ACYSMS::get('class.message');
		$num = $messageClass->delete($cids);

		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');
		return $this->listing();
	}

	function archive(){
		JRequest::checkToken() || JRequest::checkToken('get') || JSession::checkToken('get') || die('Invalid Token');

		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();

		$messageClass = ACYSMS::get('class.message');

		$affectedRows = $messageClass->toggleArchive($cids);

		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_UPDATED_ELEMENTS', $affectedRows), 'message');
		return $this->listing();
	}

	function preview(){

		$formData = JRequest::getVar('data', array(), '', 'array');
		if(!empty($formData)) $this->store();
		JRequest::setVar('layout', 'preview');
		return parent::display();
	}

	function send(){
		if(!$this->isAllowed('messages', 'send')) return;
		$app = JFactory::getApplication();
		JHTML::_('behavior.modal', 'a.modal');
		$messageClass = ACYSMS::get('class.message');
		$message_id = ACYSMS::getCID('message_id');
		$user = JFactory::getUser();
		$queueClass = ACYSMS::get('class.queue');
		$time = time();
		$db = JFactory::getDBO();

		if(!$app->isAdmin() && !empty($message_id) && !$messageClass->checkMsgAccess($message_id, $user)) $app->redirect('index.php', 'You are not allowed to send this message !', 'error');

		if(empty($message_id)) return $this->listing();
		$totalSub = $queueClass->queue($message_id, $time);
		if(empty($totalSub)){
			ACYSMS::enqueueMessage(JText::_('SMS_NO_RECEIVERS'), 'warning');
			return $this->listing();
		}
		$messageObject = new stdClass();
		$messageObject->message_senddate = $time;
		$messageObject->message_id = $message_id;
		$messageObject->message_status = 'sent';
		$messageObject->message_senderid = $user->id;
		$db->updateObject(ACYSMS::table('message'), $messageObject, 'message_id');
		$messages = JText::sprintf('SMS_ADDED_QUEUE', $totalSub);
		$controller = JRequest::getVar('ctrl');

		if($app->isAdmin()){
			$messages .= '<br /><a class="modal" rel="{handler: \'iframe\', size: {x: 640, y: 480}}" href="'.ACYSMS::completeLink($controller."&task=processQueue&message_id=$message_id&totalsend=$totalSub", true, true).'">'.JText::_('SMS_CONTINUE_SEND').'</a>';
		}else{
			$messages .= '<br />'.JText::_('SMS_MESSAGES_SENT_ASAP');
		}

		$sendNow = JRequest::getInt('sendNow');

		if($sendNow){
			$message_id = ACYSMS::getCID('message_id');
			$queueHelper = ACYSMS::get('helper.queue');
			$queueHelper->report = false;
			$queueHelper->message_id = $message_id;
			$queueHelper->process();
		}else{
			ACYSMS::enqueueMessage($messages, 'message');
		}
		return $this->listing();
	}

	function processQueue(){
		if(!$this->isAllowed('queue', 'process')) return;

		$config = ACYSMS::config();
		$helperQueue = ACYSMS::get('helper.queue');

		$newcrontime = time() + 120;
		if($config->get('cron_next') < $newcrontime){
			$newValue = new stdClass();
			$newValue->cron_next = $newcrontime;
			$config->save($newValue);
		}

		$helperQueue->message_id = ACYSMS::getCID('message_id', 0);
		$helperQueue->report = true;
		$helperQueue->total = JRequest::getVar('totalsend', 0, '', 'int');
		$helperQueue->start = JRequest::getVar('alreadysent', 0, '', 'int');
		$helperQueue->process();
	}

	function sendtest(){
		if(!$this->isAllowed('messages', 'sendtest')) return;

		$messageClass = ACYSMS::get('class.message');
		$resultSending = $messageClass->sendtest();

		$this->preview();
	}

	function genschedule(){
		$queueClass = ACYSMS::get('class.queue');
		$queueClass->queueScheduled();

		ACYSMS::enqueueMessage(implode('<br />', $queueClass->messages));
	}

	function summaryBeforeSend(){
		if(!$this->isAllowed('messages', 'manage')) return;

		$formData = JRequest::getVar('data', array(), '', 'array');
		$app = JFactory::getApplication();

		$messageClass = ACYSMS::get('class.message');
		$message_id = ACYSMS::getCID('message_id');
		if(empty($message_id)) return $this->listing();

		$config = ACYSMS::config();

		if(!$app->isAdmin() && !$messageClass->checkMsgAccess($message_id, JFactory::getUser())) $app->redirect('index.php', 'You are not allowed to send this message !', 'error');

		$message = $messageClass->get(intval($message_id));

		if(empty($message->message_senderprofile_id)){
			ACYSMS::enqueueMessage(JText::_('SMS_SELECT_SENDERPROFILE'), 'warning');
			return $this->edit();
		}

		if(!$app->isAdmin()){
			$frontEndFiltersMinimumSelection = $config->get('frontEndRequiredFilters');
			if(!empty($frontEndFiltersMinimumSelection)){
				JPluginHelper::importPlugin('acysms');
				$dispatcher = JDispatcher::getInstance();
				$answer = new stdClass();
				$answer->result = true;
				$answer->msg = '';
				empty($formData['message']['message_receiver']) ? $receivers = array() : $receivers = $formData['message']['message_receiver'];

				$dispatcher->trigger('onAcySMSAllowSend_'.$frontEndFiltersMinimumSelection, array($receivers, &$answer));
				if(!$answer->result){
					ACYSMS::enqueueMessage($answer->msg, 'notice');
					return $this->preview();
				}
			}
		}


		if($formData['message']['message_status'] == 'scheduled'){

			$sendDate = ACYSMS::getTime($formData['scheduleddate']['year'].'-'.$formData['scheduleddate']['month'].'-'.$formData['scheduleddate']['day'].' '.$formData['scheduleddate']['hour'].':'.$formData['scheduleddate']['min']);
			if(!$messageClass->saveForm()) ACYSMS::enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!$messageClass->scheduleMessage($message->message_id, $sendDate)){
				ACYSMS::enqueueMessage(implode(',', $messageClass->errors), 'warning');
				return $this->preview();
			}else{
				$messages = JText::sprintf('SMS_QUEUE_SCHED', $message->message_id, $message->message_subject, ACYSMS::getDate($sendDate));
				ACYSMS::enqueueMessage($messages, 'message');
				return $this->listing();
			}
		}

		$this->store();
		JRequest::setVar('layout', 'summaryBeforeSend');
		return parent::display();
	}

	function displayParamsAutoMessage(){
		$value = JRequest::getCmd('value');
		if(empty($value)) exit;
		JPluginHelper::importPlugin('acysms');

		$messageClass = ACYSMS::get('class.message');
		$message_id = ACYSMS::getCID('message_id');
		$message = $messageClass->get($message_id);

		$message->message_receiver_table = JRequest::getCmd('integration', '');

		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onACYSMSDisplayParamsAutoMessage_'.$value, array($message));
		exit;
	}

	function addDropdownEntryAutoMessage(){
		JPluginHelper::importPlugin('acysms');
		$integration = JRequest::getCmd('integration');
		if(empty($integration)) return;

		$autotypes = array();
		$messageBasedOn[] = JHTML::_('select.option', '', JText::_('SMS_SELECT_MESSAGE_TYPE'));
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onACYSMSGetMessageType', array(&$autotypes, $integration));
		foreach($autotypes as $type => $object){
			$messageBasedOn[] = JHTML::_('select.option', $type, $object->name);
		}
		echo JText::sprintf('SMS_START_ON', JHTML::_('select.genericlist', $messageBasedOn, 'data[message][message_autotype]', 'onchange="loadAutoParams(this.value)" class="inputbox" style="width:auto"', 'value', 'text', ''));
		exit;
	}

	function displayFilterParams(){
		$integration = JRequest::getCmd('integration');
		if(empty($integration)) exit;
		JPluginHelper::importPlugin('acysms');
		JPluginHelper::importPlugin('user');
		$messageClass = ACYSMS::get('class.message');
		$message_id = ACYSMS::getCID('message_id');
		$message = $messageClass->get($message_id);

		$dispatcher = JDispatcher::getInstance();
		ob_clean();
		$dispatcher->trigger('onACYSMSDisplayFilterParams_'.$integration, array($message));
		exit;
	}

	function countresults(){
		$integration = JRequest::getVar('integration');
		$data = JRequest::getVar('data');
		$query = ACYSMS::get('class.acyquery');

		if(empty($data['message'])) return;

		$message = new stdClass();
		$message->message_receiver = $data['message']['message_receiver'];
		$message->message_receiver_table = $data['message']['message_receiver_table'];
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onACYSMSSelectData_'.$integration, array(&$query, $message));
		echo JText::sprintf('SMS_SELECTED_USERS', $query->count($message->message_receiver_table));
		exit;
	}

	function answermessage(){
		$data = JRequest::getVar('data');
		if(!empty($data)) $this->store();

		JRequest::setVar('layout', 'answermessage');
		return parent::display();
	}

	function savesend(){

		$data = JRequest::getVar('data');

		if($data['message']['message_type'] != 'standard'){
			return $this->save();
		}else{
			return $this->summaryBeforeSend();
		}
	}

	function displayFieldsFilter(){
		JPluginHelper::importPlugin('acysms');
		$fct = JRequest::getVar('fct');

		$dispatcher = JDispatcher::getInstance();
		$message = $dispatcher->trigger('onAcySMS'.$fct);
		echo implode(' | ', $message);
		exit;
	}

	function displayFieldsFilterValues(){
		JPluginHelper::importPlugin('acysms');
		$fieldsIntegration = JRequest::getVar('fieldsIntegration');

		$dispatcher = JDispatcher::getInstance();
		$message = $dispatcher->trigger('onAcySMSdisplayFieldsFilterValues_'.$fieldsIntegration);
		echo implode(' | ', $message);
		exit;
	}
}
