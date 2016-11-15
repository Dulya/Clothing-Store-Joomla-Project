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

class plgAcysmsAcymailing extends JPlugin{

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acymailing')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'acymailing');
			$this->params = new acysmsParameter($plugin->params);
		}
	}





	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$helperPlugin = ACYSMS::get('helper.plugins');

		$newFilter = new stdClass();
		$newFilter->name = JText::_('SMS_ACYMAILING_LIST');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('acymailinglist'))) $filters['communityFilters']['acymailingList'] = $newFilter;

		$secondFilter = new stdClass();
		$secondFilter->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'AcyMailing');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('acymailingfield'))) $filters['communityFilters']['acymailingField'] = $secondFilter;
	}




	function onACYSMSDisplayFilterParams_acymailingList($message){
		if(!include_once(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
			echo 'This code can not work without the AcyMailing Component';
			return false;
		}

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

		$query = 'SELECT name, listid FROM #__acymailing_list WHERE type="list" ORDER BY ordering ASC';
		$db->setQuery($query);
		$lists = $db->loadObjectList();


		if(!$app->isAdmin()){
			$frontEndFilters = $config->get('frontEndFilters');
			if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

			$availableFrontListFilters = array();
			foreach($frontEndFilters as $oneCondition){
				if($oneCondition['filters'] != 'acymailinglist') continue;
				if(empty($oneCondition['filterDetails']) || empty($oneCondition['filterDetails']['acymailinglist'])) continue;
				if($oneCondition['typeDetails'] != 'all' && !in_array($oneCondition['typeDetails'], $myJoomlaGroups)) continue;
				$availableFrontListFilters += $oneCondition['filterDetails']['acymailinglist'];
			}
			if(empty($availableFrontListFilters)) return;

			if(in_array('userownlists', $availableFrontListFilters)){
				$groupClass = acymailing_get('class.list');
				$allCustomerLists = $groupClass->getFrontendLists('listid');
				$availableFrontListFilters += $allCustomerLists;
			}
		}

		echo JText::_('SMS_SEND_MAILINGLIST').' : <br />';

		foreach($lists as $oneList){
			if(!$app->isAdmin()){
				if(!array_key_exists($oneList->listid, $availableFrontListFilters)) continue;
			} ?>
			<label><input type="checkbox" name="data[message][message_receiver][standard][acymailing][acymailinglist][<?php echo $oneList->listid; ?>]" value="<?php echo $oneList->listid ?>" title="<?php echo $oneList->name ?>"/> <?php echo $oneList->name ?></label><br/>
		<?php }
	}

	function onACYSMSSelectData_acymailingList(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['acymailing']['acymailinglist'])) return;
		if(!isset($acyquery->join['acymailingsubscribers']) && $message->message_receiver_table != 'acymailing') $acyquery->join['acymailingsubscribers'] = 'JOIN #__acymailing_subscriber AS acymailingsubscribers ON acymailingsubscribers.userid = joomusers.id';
		$acyquery->join['acymailinglistsub'] = 'JOIN #__acymailing_listsub as acymailinglistsub ON acymailinglistsub.subid = acymailingsubscribers.subid ';

		JArrayHelper::toInteger($message->message_receiver['standard']['acymailing']['acymailinglist']);

		$acyquery->where[] = ' acymailinglistsub.listid IN ('.implode(',', ($message->message_receiver['standard']['acymailing']['acymailinglist'])).') AND acymailinglistsub.status = 1';
	}



	function onACYSMSDisplayFilterParams_acymailingField($message){
		$fields = acysms_getColumns('#__acymailing_subscriber');
		if(empty($fields)) return;

		$field = array();
		$field[] = JHTML::_('select.option', '', ' - - - ');
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option', $oneField, $oneField);
		}

		$relation = array();
		$relation[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');

		?>
		<span id="countresult_acymailingField"></span>
		<?php
		for($i = 0; $i < 5; $i++){
			$operators->extra = 'onchange="countresults(\'acymailingField\')"';
			$return = '<div id="filter'.$i.'acyfield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][acymailing][acymailingfield][".$i."][map]", 'onchange="countresults(\'acymailingField\')" class="inputbox" size="1"', 'value', 'text');
			$return .= ' '.$operators->display("data[message][message_receiver][standard][acymailing][acymailingfield][".$i."][operator]").' <input onchange="countresults(\'acymailingField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][acymailing][acymailingfield]['.$i.'][value]" style="width:200px" value=""></div>';
			if($i != 4) $return .= JHTML::_('select.genericlist', $relation, "data[message][message_receiver][standard][acymailing][acymailingfield][".$i."][relation]", 'onchange="countresults(\'acymailingField\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
			echo $return;
		}
	}

	function onACYSMSSelectData_acymailingField(&$acyquery, $message){
		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['acymailing']['acymailingfield'])) return;
		if(!isset($acyquery->join['acymailingsubscribers']) && $integration->componentName != 'acymailing') $acyquery->join['acymailingsubscribers'] = 'LEFT JOIN #__acymailing_subscriber as acymailingsubscribers ON joomusers.id = acymailingsubscribers.userid ';
		$addCondition = '';
		$whereConditions = '';

		foreach($message->message_receiver['standard']['acymailing']['acymailingfield'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			if(!empty($addCondition)) $whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])){
				$addCondition = $oneFilter['relation'];
			}else  $addCondition = 'AND';

			$type = '';
			$value = ACYSMS::replaceDate($oneFilter['value']);

			if(strpos($oneFilter['value'], '{time}') !== false && !in_array($oneFilter['map'], array('created', 'confirmed_date', 'lastclick_date', 'lastopen_date', 'lastsent_date'))){
				$value = strftime('%Y-%m-%d', $value);
			}

			if(in_array($oneFilter['map'], array('created', 'confirmed_date', 'lastclick_date', 'lastopen_date', 'lastsent_date'))){
				if(!is_numeric($value)) $value = strtotime($value);
				$type = 'timestamp';
			}
			$whereConditions .= $acyquery->convertQuery('acymailingsubscribers', $oneFilter['map'], $oneFilter['operator'], $value, $type);
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}






	function onACYSMSGetMessageType(&$types, $integration){
		if($integration != 'acymailing' || empty($integration)) return;
		$newType = new stdClass();
		$newType->name = JText::_('SMS_AUTO_SUBSCRIPTION');
		$types['acymailingsubscription'] = $newType;
	}

	function onACYSMSdisplayParamsAutoMessage_acymailingsubscription(){
		$db = JFactory::getDBO();
		$result = '';

		$query = 'SELECT name, listid FROM #__acymailing_list WHERE type="list" ORDER BY ordering ASC';
		$db->setQuery($query);
		$lists = $db->loadObjectList();

		$timevalue = array();
		$timevalue[] = JHTML::_('select.option', 'hours', JText::_('SMS_HOURS'));
		$timevalue[] = JHTML::_('select.option', 'days', JText::_('SMS_DAYS'));
		$timevalue[] = JHTML::_('select.option', 'weeks', JText::_('SMS_WEEKS'));
		$timevalue[] = JHTML::_('select.option', 'months', JText::_('SMS_MONTHS'));
		$delay = JHTML::_('select.genericlist', $timevalue, "data[message][message_receiver][auto][acymailingsubscription][delay][timevalue]", 'size="1" style="width:auto"', 'value', 'text', '0');

		$timeNumber = '<input type="text" name="data[message][message_receiver][auto][acymailingsubscription][delay][duration]" class="inputbox" style="width:30px" value="0">';
		echo JText::sprintf('SMS_SEND_MAILINGLIST_AUTO', $timeNumber.' '.$delay).'<br />';
		foreach($lists as $oneList){ ?>
			<label><input type="checkbox" name="data[message][message_receiver][auto][acymailingsubscription][acymailinglist][<?php echo $oneList->listid; ?>]" value="<?php echo $oneList->listid; ?>" title="<?php echo $oneList->name; ?>"/><?php echo $oneList->name ?></label> <br/>
		<?php }
	}




	public function onAcySMSSaveMessage($oldMsg, $newMsg){
		if(empty($newMsg['message_type']) || $newMsg['message_type'] != 'auto' || empty($newMsg['message_autotype'])) return;
		if($newMsg['message_autotype'] != 'acymailingsubscription') return;
		$toggleHelper = ACYSMS::get('helper.toggle');
		$db = JFactory::getDBO();

		if(!isset($newMsg['message_receiver']['auto']['acymailingsubscription']['delay'])) return;
		$newMsgDelay = $newMsg['message_receiver']['auto']['acymailingsubscription']['delay'];

		if(empty($newMsg['message_receiver']['auto']['acymailingsubscription']['acymailinglist'])) return;

		if(empty($oldMsg->message_receiver['auto']['acymailingsubscription'])){

			$delay = strtotime($newMsgDelay['duration'].' '.$newMsgDelay['timevalue']) - time();

			$acyquery = $this->_getAcyQuery($oldMsg->message_id, $delay);
			$nbUser = $acyquery->count($newMsg['message_receiver_table']);
			if(empty($nbUser)) return;

			$timeDisplayed = ACYSMS::getDate(time() - $delay);

			$acyquery = $this->_getAcyQuery($oldMsg->message_id, '');
			$nbUserAll = $acyquery->count($newMsg['message_receiver_table']);

			$text = $toggleHelper->toggleText('plgtrigger', $oldMsg->message_id.'_delay', '', '&function=onAcySMSAutoMsgAdd&plg=acymailing&plgtype=acysms&delay=1&msgId='.$oldMsg->message_id, JText::sprintf('SMS_ADD_NEW_MESSAGE_QUEUE_SUBSCRIBED_AFTER', $nbUser, $timeDisplayed));
			$text .= '<br />';
			$text .= $toggleHelper->toggleText('plgtrigger', $oldMsg->message_id, '', '&function=onAcySMSAutoMsgAdd&plg=acymailing&plgtype=acysms&msgId='.$oldMsg->message_id, JText::sprintf('SMS_ADD_NEW_MESSAGE_QUEUE_SUBSCRIBED_ALL', $nbUserAll));

			ACYSMS::enqueueMessage($text, 'notice');
			return;
		}

		if(!isset($oldMsg->message_receiver['auto']['acymailingsubscription']['delay'])) return;
		$oldMsgDelay = $oldMsg->message_receiver['auto']['acymailingsubscription']['delay'];

		if(($oldMsgDelay['duration'] !== $newMsgDelay['duration']) || ($oldMsgDelay['timevalue'] !== $newMsgDelay['timevalue'])){
			$difference = strtotime($newMsgDelay['duration'].' '.$newMsgDelay['timevalue']) - strtotime($oldMsgDelay['duration'].' '.$oldMsgDelay['timevalue']);
			$text = JText::_('SMS_MESSAGE_CHANGED_DELAY_INFORMED');
			$text .= ' '.$toggleHelper->toggleText('plgtrigger', $oldMsg->message_id, '', '&function=onAcySMSAutoMsgUpdate&plg=acymailing&plgtype=acysms&difference='.$difference.'&msgId='.$oldMsg->message_id, JText::_('SMS_MESSAGE_CHANGED_DELAY'));

			ACYSMS::enqueueMessage($text, 'notice');
		}
	}


	private function _getAcyQuery($messageId, $msgDelay){
		$messageClass = ACYSMS::get('class.message');
		$message = $messageClass->get($messageId);

		$delayIsPresent = JRequest::getInt('delay', '');

		$integration = ACYSMS::getIntegration($message->message_receiver_table);

		$acyquery = ACYSMS::get('class.acyquery');
		$integrationFrom = $message->message_receiver_table;
		$integrationTo = $message->message_receiver_table;
		$integration->initQuery($acyquery);
		$acyquery->addMessageFilters($message);
		$acyquery->join['acymailingsubscription'] = ' JOIN #__acymailing_listsub AS listsub ON listsub.subid = acymailingsubscribers.subid';

		JArrayHelper::toInteger($message->message_receiver['auto']['acymailingsubscription']['acymailinglist']);

		$acyquery->where[] = ' listsub.status = 1 AND listsub.listid IN ('.implode(',', $message->message_receiver['auto']['acymailingsubscription']['acymailinglist']).')';
		if($delayIsPresent) $acyquery->where[] = ' listsub.subdate > '.(time() - $msgDelay);
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

		$msgDelay = strtotime($newMessage->message_receiver['auto']['acymailingsubscription']['delay']['duration'].' '.$newMessage->message_receiver['auto']['acymailingsubscription']['delay']['timevalue']) - time();

		$integration = ACYSMS::getIntegration($newMessage->message_receiver_table);
		$acyquery = $this->_getAcyQuery($messageId, $msgDelay);

		$querySelect = $acyquery->getQuery(array($newMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($newMessage->message_receiver_table).', listsub.`subdate` +'.$msgDelay.',0,2'));
		$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`) '.$querySelect;
		$db->setQuery($finalQuery);
		$db->query();
		$nbinserted = $db->getAffectedRows();

		echo JText::sprintf('SMS_ADDED_QUEUE', $nbinserted);
		exit;
	}






	function onACYSMSGetTags(&$tags){

		$tags['communityTags']['acymailing'] = new stdClass();
		$tags['communityTags']['acymailing']->name = JText::sprintf('SMS_X_USER_INFO', 'AcyMailing');
		$tableFields = acysms_getColumns('#__acymailing_subscriber');

		$tags['communityTags']['acymailing']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['communityTags']['acymailing']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{acymailing:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['communityTags']['acymailing']->content .= '</tbody></table>';
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$match = '#(?:{|%7B)acymailing:(.*)(?:}|%7D)#Ui';
		$helperPlugin = ACYSMS::get('helper.plugins');

		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;

		if(!isset($user->acymailing)){
			$db = JFactory::getDBO();
			if(!empty($user->joomla->id)){
				$query = 'SELECT * FROM #__acymailing_subscriber WHERE userid = '.intval($user->joomla->id);
				$db->setQuery($query);
				$user->acymailing = $db->loadObject();
			}
		}
		$tags = array();
		foreach($results[0] as $i => $oneTag){
			if(isset($tags[$oneTag])) continue;
			$arguments = explode('|', strip_tags($results[1][$i]));
			$field = $arguments[0];
			unset($arguments[0]);
			$mytag = new stdClass();
			$mytag->default = '';
			if(!empty($arguments)){
				foreach($arguments as $onearg){
					$args = explode(':', $onearg);
					if(isset($args[1])){
						$mytag->$args[0] = $args[1];
					}else{
						$mytag->$args[0] = 1;
					}
				}
			}
			$tags[$oneTag] = (isset($user->acymailing->$field) && strlen($user->acymailing->$field) > 0) ? $user->acymailing->$field : $mytag->default;
			$helperPlugin->formatString($tags[$oneTag], $mytag);
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}




	function onACYSMSDisplayActionsAnswersTrigger(&$actions, $answerTrigger){

		$query = 'SELECT `subject`, `mailid` FROM #__acymailing_mail WHERE type IN ("news","followup","welcome","unsub")';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();

		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_NO_NEWSLETTER'));
		foreach($messages as $oneMessage){
			$this->values[] = JHTML::_('select.option', $oneMessage->mailid, '['.$oneMessage->mailid.'] '.$oneMessage->subject);
		}

		$newActionSendNewsletter = new stdClass();
		$newActionSendNewsletter->name = JText::_('SMS_ACTION_TRIGGER_SEND_NEWSLETTER').' : ';
		$emailAddress = '';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('sendnewsletter', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['sendnewsletter']) && !empty($answerTrigger->answertrigger_actions['sendnewsletter']['newsletter_id'])){
			$newsletter_id = $answerTrigger->answertrigger_actions['sendnewsletter']['newsletter_id'];
		}
		$newActionSendNewsletter->extra = JHTML::_('select.genericlist', $this->values, 'data[answertrigger][answertrigger_actions][sendnewsletter][newsletter_id]', 'class="inputbox" size="1" onchange="changeMessage(\'unsub\',this.value);"', 'value', 'text', (int)@$newsletter_id);
		$actions['sendnewsletter'] = $newActionSendNewsletter;


		$query = 'SELECT `name`, `listid` FROM #__acymailing_list';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();

		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_NO_LIST'));
		foreach($messages as $oneMessage){
			$this->values[] = JHTML::_('select.option', $oneMessage->listid, $oneMessage->name);
		}

		$newActionSubscribeList = new stdClass();
		$newActionSubscribeList->name = JText::_('SMS_ACTION_TRIGGER_SUBSCRIBE_LIST').' : ';
		$emailAddress = '';
		if(!empty($answerTrigger->answertrigger_actions) && !empty($answerTrigger->answertrigger_actions['selected']) && is_array($answerTrigger->answertrigger_actions['selected']) && in_array('subscribelist', $answerTrigger->answertrigger_actions['selected']) && !empty($answerTrigger->answertrigger_actions['subscribelist']) && !empty($answerTrigger->answertrigger_actions['subscribelist']['listid'])){
			$listid = $answerTrigger->answertrigger_actions['subscribelist']['listid'];
		}
		$newActionSubscribeList->extra = JHTML::_('select.genericlist', $this->values, 'data[answertrigger][answertrigger_actions][subscribelist][listid]', 'class="inputbox" size="1" onchange="changeMessage(\'unsub\',this.value);"', 'value', 'text', (int)@$listid);
		$actions['subscribelist'] = $newActionSubscribeList;
	}

	public function onACYSMSTriggerActions_sendnewsletter($actionsParams, $answer){

		if(!include_once(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
			echo 'This code can not work without the AcyMailing Component';
			return false;
		}

		$subscriberClass = acymailing_get('class.subscriber');
		$potentialEmail = array();
		$user = new stdClass();


		if(empty($actionsParams['sendnewsletter']['newsletter_id'])){
			$this->_saveReport('SEND NEWSLETTER ERROR', 'No newsletter id selected in the answer action configuration page.');
			return false;
		}

		preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@([a-z0-9\-]+\.)+[a-z0-9]{2,8}/i', $answer->answer_body, $potentialEmail);

		if(empty($potentialEmail) && empty($answer->answer_receiver_id)){
			$this->_saveReport('SEND NEWSLETTER ERROR', 'No potential email found and no receiver_id related to the sender number.');
			return false;
		}

		if(empty($potentialEmail)){
			$integration = ACYSMS::getIntegration($answer->answer_receiver_table);
			$sender = new stdClass();
			$sender->queue_receiver_id = intval($answer->answer_receiver_id);
			$senderArray = array($sender);

			$integration->addUsersInformations($senderArray);
			$senderInformations = reset($senderArray);

			if(!empty($senderInformations->receiver_email)){
				$user->email = $senderInformations->receiver_email;
				$user->name = $senderInformations->receiver_name;
			}else{
				$this->_saveReport('SEND NEWSLETTER ERROR', 'No receiver email found after the addUserInformation() function => User may have an empty email address.');
				return false;
			}
		}else{
			$user->email = reset($potentialEmail);
		}

		$subid = $subscriberClass->subid($user->email);
		if(empty($subid)) $subid = $subscriberClass->save($user);

		if(empty($subid)) return false;

		$db = JFactory::getDBO();
		$db->setQuery('INSERT IGNORE INTO #__acymailing_queue (`subid`,`mailid`,`senddate`,`priority`) VALUES ('.$db->Quote($subid).','.$db->Quote($actionsParams['sendnewsletter']['newsletter_id']).','.time().',1)');
		$db->query();
	}

	public function onACYSMSTriggerActions_subscribelist($actionsParams, $answer){

		if(!include_once(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acymailing'.DS.'helpers'.DS.'helper.php')){
			echo 'This code can not work without the AcyMailing Component';
			return false;
		}

		$subscriberClass = acymailing_get('class.subscriber');
		$potentialEmail = array();
		$user = new stdClass();


		if(empty($actionsParams['subscribelist']['listid'])){
			$this->_saveReport('SUBSCRIBE LIST ERROR', 'No list id selected in the answer action configuration page.');
			return false;
		}

		preg_match('/[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@([a-z0-9\-]+\.)+[a-z0-9]{2,8}/i', $answer->answer_body, $potentialEmail);

		if(empty($potentialEmail) && empty($answer->answer_receiver_id)){
			$this->_saveReport('SUBSCRIBE LIST ERROR', 'No potential email found and no receiver_id related to the sender number.');
			return false;
		}

		if(empty($potentialEmail)){
			$integration = ACYSMS::getIntegration($answer->answer_receiver_table);
			$sender = new stdClass();
			$sender->queue_receiver_id = intval($answer->answer_receiver_id);
			$senderArray = array($sender);

			$integration->addUsersInformations($senderArray);
			$senderInformations = reset($senderArray);

			if(!empty($senderInformations->receiver_email)){
				$user->email = $senderInformations->receiver_email;
				$user->name = $senderInformations->receiver_name;
			}else{
				$this->_saveReport('SUBSCRIBE LIST ERROR', 'No receiver email found after the addUserInformation() function => User may have an empty email address.');
				return false;
			}
		}else{
			$user->email = reset($potentialEmail);
		}

		$subid = $subscriberClass->subid($user->email);
		if(empty($subid)) $subid = $subscriberClass->save($user);

		if(empty($subid)) return false;

		$newList['status'] = 1;
		$newSubscription[$actionsParams['subscribelist']['listid']] = $newList;
		$subscriberClass->saveSubscription($subid, $newSubscription);
	}

	private function _saveReport($message, $detailMessages){
		$cronHelper = ACYSMS::get('helper.cron');
		$cronHelper->messages = array($message);
		$cronHelper->detailMessages = array($detailMessages);
		$cronHelper->saveReport();
		return;
	}




	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_ACYMAILING_LIST');
		$authorizedFilters['acymailinglist'] = $newType;

		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'AcyMailing');
		$authorizedFilters['acymailingfield'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_acymailinglist(&$authorizedFiltersSelection, $conditionNumber){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT listid, name FROM #__acymailing_list WHERE type = "list"');
		$acyLists = $db->loadObjectList();

		if(empty($acyLists)) return;

		$ownListsObject = new stdClass();
		$ownListsObject->listid = 'userownlists';
		$ownListsObject->name = JText::_('SMS_USER_OWN_LISTS');
		array_unshift($acyLists, $ownListsObject);

		$config = ACYSMS::config();
		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

		$result = '<br />';
		foreach($acyLists as $oneList){
			if(!empty($frontEndFilters[$conditionNumber]['filterDetails']['acymailinglist']) && in_array($oneList->listid, $frontEndFilters[$conditionNumber]['filterDetails']['acymailinglist'])){
				$checked = 'checked="checked"';
			}else $checked = '';
			$result .= '<label><input type="checkbox" name="config[frontEndFilters]['.$conditionNumber.'][filterDetails][acymailinglist]['.$oneList->listid.']" value="'.$oneList->listid.'" '.$checked.' title= "'.$oneList->name.'"/> '.$oneList->name.'</label><br />';
		}
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails">'.$result.'</span>';
	}

	public function onACYSMSdisplayAuthorizedFilters_acymailingfield(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

	public function onACYSMSdisplayRequiredFilters(&$requiredFilters){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_ACYMAILING_LIST');
		$requiredFilters['acymailinglist'] = $newType;
	}

	public function onAcySMSAllowSend_acymailinglist($messageReceiver, &$answer){
		if(empty($messageReceiver['standard']['acymailing']['acymailinglist'])){
			$answer->msg = JText::_('SMS_PLEASE_SELECT_GROUP');
			$answer->result = false;
			return;
		}
		$answer->result = true;
	}
}//endclass
