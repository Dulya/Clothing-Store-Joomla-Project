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

class ReceiverViewReceiver extends acysmsView{
	var $ctrl = 'receiver';
	var $icon = 'receiver';

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$phoneHelper = ACYSMS::get('helper.phone');
		$fieldsClass = ACYSMS::get('class.fields');
		$filters = new stdClass();
		$pageInfo = new stdClass();
		$UserQueryFilters = array();
		$pageInfo->elements = new stdClass();
		JHTML::_('behavior.modal', 'a.modal');

		$selectedIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');
		$integration = ACYSMS::getIntegration($selectedIntegration);
		if(empty($selectedIntegration)) $selectedIntegration = $integration->componentName;

		$integrationType = ACYSMS::get('type.integration');
		$integrationType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$integrationType->load();
		$filters->integration = $integrationType->display('currentIntegration', $selectedIntegration);

		$phoneArray = array();

		$displayFields = array();
		if($integration->componentName == "acysms"){
			$fakeUser = new stdClass();
			if($app->isAdmin()){
				$displayFields = $fieldsClass->getFields('backlisting', $fakeUser);
			}else $displayFields = $fieldsClass->getFields('frontlisting', $fakeUser);
		}





		$paramBase = $integration->componentName.'.'.ACYSMS_COMPONENT.'.'.$this->getName();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$sessionIntegration = $app->getUserState("acysms_selected_integration");
		$app->setUserState("acysms_selected_integration", $integration->componentName);

		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'receiver_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');

		if($sessionIntegration != $integration->componentName) $pageInfo->filter->order->value = 'receiver_id';

		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$queryUser = $integration->getQueryUsers($pageInfo->search, $pageInfo->filter->order, $UserQueryFilters);
		$db->setQuery($queryUser->query, $pageInfo->limit->start, empty($pageInfo->limit->value) ? 500 : $pageInfo->limit->value);
		$rows = $db->loadObjectList($integration->primaryField);

		if(!empty($rows) && $integration->componentName == 'acysms'){
			$db->setQuery('SELECT * FROM `#__acysms_groupuser` WHERE `groupuser_user_id` IN (\''.implode('\',\'', array_keys($rows)).'\')');
			$subscriptions = $db->loadObjectList();
			if(!empty($subscriptions)){
				foreach($subscriptions as $onesub){
					$subgroupid = $onesub->groupuser_group_id;
					if(empty($rows[$onesub->groupuser_user_id]->subscription)) $rows[$onesub->groupuser_user_id]->subscription = new stdClass();
					$rows[$onesub->groupuser_user_id]->subscription->$subgroupid = $onesub;
				}
			}
		}

		if(!empty($rows)){
			foreach($rows as $oneUser){
				$phone = $phoneHelper->getValidNum($oneUser->receiver_phone);
				if(!$phone){
					continue;
				}else $phoneArray[] = $db->Quote($phone);
			}
		}

		if(!empty($phoneArray)){
			$query = 'SELECT phone_number FROM #__acysms_phone WHERE phone_number IN ('.implode(',', $phoneArray).')';
			$db->setQuery($query);
			$phones = $db->loadObjectList('phone_number');
		}
		$pageInfo->elements->page = count($rows);
		if($pageInfo->limit->value > $pageInfo->elements->page){
			$pageInfo->elements->total = $pageInfo->limit->start + $pageInfo->elements->page;
		}else{
			$pageInfo->elements->total = $queryUser->count;
		}

		if(empty($pageInfo->limit->value)){
			if($pageInfo->elements->total > 500){
				ACYSMS::display('We do not want you to crash your server so we displayed only the first 500 users', 'warning');
				$pageInfo->limit->value = 100;
			}
		}

		$allowCustomerManagement = $config->get('allowCustomersManagement');


		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_RECEIVERS'), 'receiver');

			$acyToolbar->custom('conversation', JText::_('SMS_CONVERSATION'), 'conversation', false);

			if($integration->componentName == 'acysms'){
				if(ACYSMS::isAllowed($config->get('acl_receivers_import', 'all'))) $acyToolbar->link(ACYSMS::completeLink('data&task=import'), JText::_('SMS_IMPORT'), 'import');
				if(ACYSMS::isAllowed($config->get('acl_receivers_export', 'all'))) $acyToolbar->link(ACYSMS::completeLink('data&task=export'), JText::_('SMS_EXPORT'), 'export');
			}

			if(ACYSMS::isAllowed($config->get('acl_receivers_unblock', 'all'))) $acyToolbar->custom('unblock', JText::_('SMS_UNBLOCK'), 'unblock_contact', true);
			if(ACYSMS::isAllowed($config->get('acl_receivers_block', 'all'))) $acyToolbar->custom('block', JText::_('SMS_BLOCK'), 'block_contact', true);

			$acyToolbar->divider();

			$acyToolbar->add();
			$acyToolbar->edit();
			if($integration->componentName == 'acysms'){
				if(ACYSMS::isAllowed($config->get('acl_receivers_delete', 'all'))) $acyToolbar->delete();
			}

			$acyToolbar->divider();
			$acyToolbar->help('receivers');

			$acyToolbar->display();
		}

		$groupsType = ACYSMS::get('type.group');
		$groups = $groupsType->getData();
		$this->assignRef('groups', $groups);

		$showStatusColumn = true;
		$this->assignRef('showStatusColumn', $showStatusColumn);
		$toggleClass = ACYSMS::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);
		$this->assignRef('phoneHelper', $phoneHelper);
		$this->assignRef('receiver_table', $integration->tableName);
		$this->assignRef('rows', $rows);
		$this->assignRef('phones', $phones);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('config', $config);
		$this->assignRef('integration', $integration);
		$this->assignRef('filters', $filters);
		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('displayFields', $displayFields);
		$this->assignRef('app', $app);
		$this->assignRef('allowCustomerManagement', $allowCustomerManagement);
	}

	function choose(){
		$pageInfo = new stdClass();
		$pageInfo->elements = new stdClass();
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$userQueryFilters = array();


		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');
		$integration = ACYSMS::getIntegration($currentIntegration);

		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();

		$sessionIntegration = $app->getUserState("acysms_selected_integration");
		$app->setUserState("acysms_selected_integration", $integration->componentName);

		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', $integration->tableAlias.'.'.$integration->primaryField, 'cmd');
		if($sessionIntegration != $integration->componentName) $pageInfo->filter->order->value = 'receiver_id';

		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$queryUser = $integration->getQueryUsers($pageInfo->search, $pageInfo->filter->order, $userQueryFilters);

		$db->setQuery($queryUser->query, $pageInfo->limit->start, empty($pageInfo->limit->value) ? 500 : $pageInfo->limit->value);
		$rows = $db->loadObjectList();
		$pageInfo->elements->page = count($rows);
		if($pageInfo->limit->value > $pageInfo->elements->page){
			$pageInfo->elements->total = $pageInfo->limit->start + $pageInfo->elements->page;
		}else{
			$pageInfo->elements->total = $queryUser->count;
		}

		if(empty($pageInfo->limit->value)){
			if($pageInfo->elements->total > 500){
				ACYSMS::display('We do not want you to crash your server so we displayed only the first 500 users', 'warning');
			}
			$pageInfo->limit->value = 100;
		}
		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$jsFct = JRequest::getCmd('jsFct', 'affectTestUser');

		$htmlID = JRequest::getCmd('htmlID', 'testID');
		$this->assignRef('htmlID', $htmlID);
		$this->assignRef('receiver_table', $integration->tableName);
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('jsFct', $jsFct);
		$this->assignRef('integration', $integration);
	}

	function conversation(){
		$app = JFactory::getApplication();

		$receiverIdsViaListing = JRequest::getVar('cid', array(), '', 'array');
		$receiverIdsViaAjax = JRequest::getCmd('receiverid', '');
		$isAjax = JRequest::getCmd('isAjax', '');

		if(!empty($receiverIdsViaAjax)) $receiverArray = explode('-', $receiverIdsViaAjax);
		if(!empty($receiverIdsViaListing)) $receiverArray = $receiverIdsViaListing;

		JArrayHelper::toInteger($receiverArray);

		if(!empty($receiverArray)){
			$conversation = $this->_loadConversation($receiverArray);

			$scriptToInsert = '';
			$userClass = ACYSMS::get('class.user');
			$receiverArray = $userClass->getUsersInformationsById($receiverArray);
			foreach($receiverArray as $oneReceiver){
				if(!empty($oneReceiver->receiver_name)) $scriptToInsert .= 'setUser("'.str_replace('"', '\"', $oneReceiver->receiver_name).'","'.str_replace('"', '\"', $oneReceiver->receiver_id).'");';
			}

			$script = 'window.addEvent("domready", function() {';
			$script .= $scriptToInsert.'});';

			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($script);
		}else{
			$conversation = new stdClass();
			$conversation->name = '';
			$conversation->conversationHTML = '<div class="empty-message">'.JText::_('SMS_EMPTY_CONVERSATION').'</div>';
		}

		if($app->isAdmin() && !$isAjax && empty($receiverIdsViaAjax)){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_CONVERSATION'), 'receiver&task=conversation');
			$acyToolbar->cancel();

			$acyToolbar->divider();
			$acyToolbar->help('receivers');

			$acyToolbar->display();
		}

		$this->assignRef('app', $app);
		$this->assignRef('isAjax', $isAjax);
		$this->assignRef('name', $conversation->name);
		$this->assignRef('conversation', $conversation->conversationHTML);
		$this->assignRef('integration', $conversation->integration);
		$this->assignRef('receiverArray', $receiverArray);
		$this->assign('senderprofile', ACYSMS::get('type.senderprofile'));
	}

	private function _loadConversation($receiverIds){

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();

		$importHelper = ACYSMS::get('helper.import');
		$uploadPath = $importHelper->getUploadDirectory();
		$uploadPath = str_replace(ACYSMS_ROOT, ACYSMS_LIVE, $uploadPath);
		$uploadPath = str_replace(DS, '/', $uploadPath);

		$conversationObject = new stdClass();
		$conversationObject->conversationHTML = '';

		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');
		$integration = ACYSMS::getIntegration($currentIntegration);

		JArrayHelper::toInteger($receiverIds);
		$userInformationsArray = array();

		foreach($receiverIds as $oneReceiverId){
			$newUser = new stdClass();
			$newUser->queue_receiver_id = $oneReceiverId;
			$userInformationsArray[$oneReceiverId] = $newUser;
		}
		$integration->addUsersInformations($userInformationsArray);

		$conversationObject->title = '<div id="conversationTitle">';
		foreach($userInformationsArray as $oneUserInformations){
			if(!empty($oneUserInformations->receiver_name)) $conversationObject->title .= '<span class="conversationUsersName">'.$oneUserInformations->receiver_name.'</span>';
		}
		$conversationObject->title .= '</div>';

		$whereConditionsQueryMessages[] = 'statsdetails.statsdetails_receiver_id IN ('.implode(',', $receiverIds).')';
		$whereConditionsQueryAnswers[] = 'answer_receiver_id IN ('.implode($receiverIds, ',').')';

		$whereConditionsQueryMessages[] = 'statsdetails.statsdetails_receiver_table = '.$db->Quote($integration->componentName);
		$whereConditionsQueryAnswers[] = 'answer_receiver_table = '.$db->Quote($integration->componentName);

		$whereConditionsQueryMessages[] = 'message_type <> \'activation_optin\'';

		$queryMessage = 'SELECT DISTINCT message.*, statsdetails.statsdetails_receiver_id, statsdetails.statsdetails_receiver_table, statsdetails.statsdetails_sentdate
					FROM #__acysms_statsdetails as statsdetails
					JOIN #__acysms_message as message
					ON statsdetails.statsdetails_message_id = message.message_id
					WHERE '.implode(' AND ', $whereConditionsQueryMessages).'
					ORDER BY statsdetails.statsdetails_sentdate';
		$db->setQuery($queryMessage);
		$messagesList = $db->loadObjectList();
		$messageInformations = array();
		foreach($messagesList as $oneMessageSent){
			if(empty($messageInformations[$oneMessageSent->message_id])){
				$messageInformations[$oneMessageSent->message_id] = $oneMessageSent;
				$messageInformations[$oneMessageSent->message_id]->names = array();
			}

			$name = $userInformationsArray[$oneMessageSent->statsdetails_receiver_id]->receiver_name;
			if(!in_array($name, $messageInformations[$oneMessageSent->message_id]->names)) $messageInformations[$oneMessageSent->message_id]->names[] = $userInformationsArray[$oneMessageSent->statsdetails_receiver_id]->receiver_name;
		}


		$queryAnswer = 'SELECT *
						FROM #__acysms_answer
						WHERE '.implode(' AND ', $whereConditionsQueryAnswers).'
						ORDER by answer_date';
		$db->setQuery($queryAnswer);
		$answerList = $db->loadObjectList();

		foreach($answerList as $oneAnswer){
			$oneAnswer->names = array();
			if(!empty($userInformationsArray[$oneAnswer->answer_receiver_id])) $oneAnswer->names[] = $userInformationsArray[$oneAnswer->answer_receiver_id]->receiver_name;
		}

		$lenghtSum = count($answerList) + count($messageInformations);
		$i = 0;
		$conversation = array();

		while($i < $lenghtSum){
			$firstAnswer = reset($answerList);
			$firstMessage = reset($messageInformations);

			if(empty($firstAnswer)){
				$conversation[] = array_shift($messageInformations);
				$i++;
				continue;
			}
			if(!empty($firstMessage) && $firstMessage->statsdetails_sentdate < $firstAnswer->answer_date){
				$conversation[] = array_shift($messageInformations);
			}//We add the next answer from the list
			else $conversation[] = array_shift($answerList);
			$i++;
		}

		$currentDate = ACYSMS::getDate(time(), '%d %B %Y');
		$dateFields = array('message' => 'statsdetails_sentdate', 'answer' => 'answer_date');

		foreach($conversation as $oneMessage){

			$oneType = empty($oneMessage->answer_date) ? 'message' : 'answer';

			if($oneType == 'message'){
				$dispatcher->trigger('onACYSMSReplaceTags', array(&$oneMessage, false));
				$dispatcher->trigger('onACYSMSReplaceUserTags', array(&$oneMessage, &$userInformationsArray[$oneMessage->statsdetails_receiver_id], false));
			}

			$newDate = ACYSMS::getDate($oneMessage->{$dateFields[$oneType]}, '%d %B %Y');
			if($newDate != $currentDate) $conversationObject->conversationHTML .= '<div class="newday">'.$newDate.'</div>';
			$currentDate = $newDate;

			$conversationObject->conversationHTML .= '<div class="conversationItem_'.$oneType.'">';
			$title = 'title=""';

			if(count($userInformationsArray) <= 1){
				$textToDisplay = '';
			}else{
				if(count($oneMessage->names) <= 2){
					$textToDisplay = implode(', ', $oneMessage->names);
				}else{
					$title = 'title="'.JText::_('SMS_RECEIVERS').' : '.implode(',', $oneMessage->names).'"';
					$textToDisplay = JText::sprintf('SMS_X_USERS', $oneMessage->names[0], $oneMessage->names[1], count($oneMessage->names) - 2);
				}
			}
			$conversationObject->conversationHTML .= '<div class="acysms_'.$oneType.'"><span class="acysms_text">'.$this->escape($oneMessage->{$oneType.'_body'}).'</span></div>';
			$attachments = explode(',', $oneMessage->{$oneType.'_attachment'});
			foreach($attachments as $oneAttachment){
				if(empty($oneAttachment)) continue;
				$path = ($oneType == 'answer') ? '' : $uploadPath;
				$conversationObject->conversationHTML .= '<a href="'.$path.$oneAttachment.'" target="_blank" class="'.$oneType.'_conversation_attachment conversation_attachment"></a>';
			}
			$conversationObject->conversationHTML .= '<div class="'.$oneType.'_info">';
			$conversationObject->conversationHTML .= '<span class="senderName" '.$title.' >'.$textToDisplay.'</span>';
			$conversationObject->conversationHTML .= '<span class="'.$oneType.'_date">'.ACYSMS::getDate($oneMessage->{$dateFields[$oneType]}, '%H:%M').'</span>';
			$conversationObject->conversationHTML .= '</div>';
			$conversationObject->conversationHTML .= '</div>';
		}

		if(empty($conversationObject->conversationHTML)) $conversationObject->conversationHTML = '<div class="empty-message">'.JText::_('SMS_EMPTY_CONVERSATION').'</div>';

		$conversationObject->integration = $integration;

		return $conversationObject;
	}

	function conversationreceivers(){
		$app = JFactory::getApplication();
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');

		$integration = ACYSMS::getIntegration($currentIntegration);

		$NameSearched = JRequest::getVar('nameSearched', '');
		if(empty($NameSearched)) exit;

		$isFront = JRequest::getInt('isFront', 0);
		$my = JFactory::getUser();

		$users = $integration->getReceiversByName($NameSearched, $isFront, $my->id);
		$this->assignref('receivers', $users);
	}
}
