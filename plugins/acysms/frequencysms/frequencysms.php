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


class plgAcysmsFrequencySms extends JPlugin{

	var $debug = false;
	var $messages = array();


	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
	}

	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_AUTO_FREQUENCY');
		$types['frequencysms'] = $newType;
		return;
	}

	function onACYSMSDisplayParamsAutoMessage_frequencysms($message){
		$result = '';
		$db = JFactory::getDBO();


		$msgId = ACYSMS::getCID('message_id');
		$messageClass = ACYSMS::get('class.message');
		$message = $messageClass->get($msgId);

		$intervalData[] = JHTML::_('select.option', 'day', JText::_('SMS_DAYS'));
		$intervalData[] = JHTML::_('select.option', 'week', JText::_('SMS_WEEKS'));
		$intervalData[] = JHTML::_('select.option', 'month', JText::_('SMS_MONTHS'));
		$intervalData[] = JHTML::_('select.option', 'year', JText::_('SMS_YEARS'));
		$intervalDropDown = JHTML::_('select.genericlist', $intervalData, 'data[message][message_receiver][auto][frequencysms][interval]', 'style="width:70px;" class="inputbox"', 'value', 'text', '00');

		$defaultValue = empty($message->message_receiver['auto']['frequencysms']['nbInterval']) ? 1 : $message->message_receiver['auto']['frequencysms']['nbInterval'];

		$nbInterval = '<input type="text" value="'.$defaultValue.'" style="width:50px" name="data[message][message_receiver][auto][frequencysms][nbInterval]"/>';

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i += 5) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$startTime = new stdClass();
		$startTime->hourField = JHTML::_('select.genericlist', $hours, 'data[message][message_receiver][auto][frequencysms][hour]', 'style="width:50px;" class="inputbox"', 'value', 'text', '08');
		$startTime->minField = JHTML::_('select.genericlist', $min, 'data[message][message_receiver][auto][frequencysms][min]', 'style="width:50px;" class="inputbox"', 'value', 'text', '00');

		$result .= JText::sprintf('SMS_SEND_EVERY_X_Y_AT_Z', $nbInterval, $intervalDropDown, $startTime->hourField.' : '.$startTime->minField).'<br />';

		$options = array();
		$options[] = JHTML::_('select.option', 'startDate', JText::_('SMS_START_DATE'));
		$options[] = JHTML::_('select.option', 'field', JText::_('SMS_FIELD'));

		$radioList = '<input type="radio" name="data[message][message_receiver][auto][frequencysms][type]" value="startDate" onclick="document.getElementById(\'startDateParams\').style.display = \'none\'; document.getElementById(\'fieldParams\').style.display = \'none\'; document.getElementById(this.value+\'Params\').style.display = \'block\';" id="acysms_startdate"/> <label for="acysms_startdate">'.JText::_('SMS_START_DATE').'</label>';
		$radioList .= '<input type="radio" name="data[message][message_receiver][auto][frequencysms][type]" value="field" onclick="document.getElementById(\'startDateParams\').style.display = \'none\'; document.getElementById(\'fieldParams\').style.display = \'none\'; document.getElementById(this.value+\'Params\').style.display = \'block\';" id="acysms_field"/> <label for="acysms_field">'.JText::_('SMS_FIELD').'</label>';

		$result .= JText::sprintf('SMS_BASED_ON', $radioList);

		if(empty($field->options['format'])) $field->options['format'] = "%d %m %Y";
		$days = array();
		for($i = 1; $i < 32; $i++) $days[] = JHTML::_('select.option', (strlen($i) == 1) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$years = array();
		for($i = date('Y') - 5; $i <= date('Y') + 5; $i++) $years[] = JHTML::_('select.option', $i, $i);
		$months = array();
		$months[] = JHTML::_('select.option', '01', JText::_('JANUARY'));
		$months[] = JHTML::_('select.option', '02', JText::_('FEBRUARY'));
		$months[] = JHTML::_('select.option', '03', JText::_('MARCH'));
		$months[] = JHTML::_('select.option', '04', JText::_('APRIL'));
		$months[] = JHTML::_('select.option', '05', JText::_('MAY'));
		$months[] = JHTML::_('select.option', '06', JText::_('JUNE'));
		$months[] = JHTML::_('select.option', '07', JText::_('JULY'));
		$months[] = JHTML::_('select.option', '08', JText::_('AUGUST'));
		$months[] = JHTML::_('select.option', '09', JText::_('SEPTEMBER'));
		$months[] = JHTML::_('select.option', '10', JText::_('OCTOBER'));
		$months[] = JHTML::_('select.option', '11', JText::_('NOVEMBER'));
		$months[] = JHTML::_('select.option', '12', JText::_('DECEMBER'));

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i += 5) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);

		$dayField = JHTML::_('select.genericlist', $days, 'data[message][message_receiver][auto][frequencysms][startDate][day]', 'style="width:50px;" class="inputbox"', 'value', 'text', !empty($message->message_senddate) ? ACYSMS::getDate($message->message_senddate, 'd') : ACYSMS::getDate(time(), 'd'));
		$monthField = JHTML::_('select.genericlist', $months, 'data[message][message_receiver][auto][frequencysms][startDate][month]', 'style="width:100px;" class="inputbox"', 'value', 'text', !empty($message->message_senddate) ? ACYSMS::getDate($message->message_senddate, 'm') : ACYSMS::getDate(time(), 'm'));
		$yearField = JHTML::_('select.genericlist', $years, 'data[message][message_receiver][auto][frequencysms][startDate][year]', 'style="width:70px;" class="inputbox"', 'value', 'text', !empty($message->message_senddate) ? ACYSMS::getDate($message->message_senddate, 'Y') : ACYSMS::getDate(time(), 'Y'));


		$style = (!empty($message->message_receiver['auto']['frequencysms']['type']) && $message->message_receiver['auto']['frequencysms']['type'] == 'startDate') ? 'style="display:block"' : 'style="display:none"';
		$result .= '<div id="startDateParams" '.$style.'>'.JText::_('SMS_START_DATE').' : '.$dayField.' '.$monthField.' '.$yearField.'</div>';


		$importData = array();
		$importvalues = array();

		$possibleImport = array();
		$possibleImport[$db->getPrefix().'comprofiler'] = array('communitybuilder', 'Community Builder');
		$possibleImport[$db->getPrefix().'acymailing_subscriber'] = array('acymailing', 'AcyMailing');
		$possibleImport[$db->getPrefix().'hikashop_address'] = array('hikashop', 'HikaShop');
		$possibleImport[$db->getPrefix().'redshop_users_info'] = array('redshop', 'RedShop');
		$possibleImport[$db->getPrefix().'virtuemart_userinfos'] = array('virtuemart_2', 'VirtueMart 2');
		$possibleImport[$db->getPrefix().'community_users'] = array('jomsocial', 'JomSocial');
		$possibleImport[$db->getPrefix().'vm_user_info'] = array('virtuemart_1', 'VirtueMart 1');
		$possibleImport[$db->getPrefix().'acysms_user'] = array('acysms', 'AcySMS');

		$tables = $db->getTableList();
		foreach($tables as $mytable){
			if(isset($possibleImport[$mytable])){
				$importData[$possibleImport[$mytable][0]] = $possibleImport[$mytable][1];
			}
		}
		$importvalues[] = JHTML::_('select.option', '', ' - - - ');
		foreach($importData as $div => $name){
			$importvalues[] = JHTML::_('select.option', $div, $name);
		}
		$presentIntegrations = JHTML::_('select.genericlist', $importvalues, "data[message][message_receiver][auto][frequencysms][field][table]", 'size="1" class="chzn-done" onchange="callFunction(\'plg=frequencysms&function=getdatefields&table=\'+this.value,\'dateField\');"', 'value', 'text');


		$style = (!empty($message->message_receiver['auto']['frequencysms']['type']) && $message->message_receiver['auto']['frequencysms']['type'] == 'field') ? 'style="display:block"' : 'style="display:none"';

		$result .= '<br /><div id="fieldParams" '.$style.'>'.JText::_('SMS_TABLE').' : '.$presentIntegrations;
		$result .= '<div id="dateField">'.JText::_('SMS_FIELD').' : <input readonly="readonly" type="text" value="" name="data[message][message_receiver][auto][frequencysms][field][column]"/></div></div>';
		echo $result;
	}

	function onAcySMSTestPlugin(){
		$this->debug = true;
		$this->onACYSMSCron();
		ACYSMS::display($this->messages);
	}

	function onACYSMSCron(){

		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$config = ACYSMS::config();

		$saveInConfig = array();

		$allMessages = $messageClass->getAutoMessage('frequencysms');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No message configured in AcySMS for a specific frequency, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create a message</a> using the type : Automatic -> '.JText::_('SMS_AUTO_FREQUENCY');
			return;
		}

		$msgAlreadySentSerialized = $config->get('FrequencyMsgSentToday');
		if(empty($msgAlreadySentSerialized)) $msgAlreadySent = array();
		else $msgAlreadySent = unserialize($msgAlreadySentSerialized);

		foreach($allMessages as $oneMessage){

			$sendDate = ACYSMS::getDate(time(), 'Y-m-d');
			$informationDate = ACYSMS::getDate(time(), 'Y-m-d G:i');
			$queueSendDate = ACYSMS::getTime($sendDate.' '.$oneMessage->message_receiver['auto']['frequencysms']['hour'].':'.$oneMessage->message_receiver['auto']['frequencysms']['min']);

			if(!empty($msgAlreadySent[$oneMessage->message_id]) && $msgAlreadySent[$oneMessage->message_id] >= $sendDate){
				if($this->debug) $this->messages[] = 'Frequency plugin: 0 frequency SMS inserted in the queue for '.$informationDate.' for the SMS '.$oneMessage->message_subject.' (Message already sent today)';
				continue;
			}

			if(time() < ($queueSendDate - 900)){
				if($this->debug) $this->messages[] = "It's too early to send this message. The message will be sent at ".$oneMessage->message_receiver['auto']['frequencysms']['hour'].':'.$oneMessage->message_receiver['auto']['frequencysms']['min'];
				continue;
			}

			$integration = ACYSMS::getIntegration($oneMessage->message_receiver_table);
			$whereCondition = array();
			$frequencyConditions = array();

			if(empty($oneMessage->message_receiver['auto']['frequencysms']['nbInterval'])){
				$this->messages[] = 'No value number defined before the interval (X days for example) for the message : '.$oneMessage->message_id;
				continue;
			}

			$type = $oneMessage->message_receiver['auto']['frequencysms']['type'];

			if(empty($oneMessage->message_receiver['auto']['frequencysms'][$type])){
				$this->messages[] = "Please select a start date or a field for the SMS ".$oneMessage->message_id;
				continue;
			}

			if($type == 'field'){

				$field = $oneMessage->message_receiver['auto']['frequencysms'][$type];
				if(empty($field)){
					$this->messages[] = "Please select a date field for the SMS ".$oneMessage->message_id;
					continue;
				}

				$receiverId = false;
				switch($oneMessage->message_receiver['auto']['frequencysms']['field']['table']){
					case 'acymailing':
						$db->setQuery("SELECT fieldid AS id, namekey AS name FROM `#__acymailing_fields` WHERE  `namekey` =  ".$db->Quote($oneMessage->message_receiver['auto']['frequencysms']['field']['column'])."  LIMIT 1");
						$fieldObject = $db->loadObject();
						if(empty($fieldObject->name)) continue;

						if($integration->componentName == 'acymailing'){
							$receiver = 'subid';
							$receiverId = true;
						}
						else{
							$receiver = 'userid';
							$receiverId = false;
						}
						$queryUsers = 'SELECT acymailingsubscribers.'.$receiver.'
												FROM `#__acymailing_subscriber` as acymailingsubscribers';

						$whereCondition[] = 'acymailingsubscribers.enabled = 1';
						$whereCondition[] = 'acymailingsubscribers.accept = 1';
						$frequencyConditions = $this->_addFrequencyConditions('acymailingsubscribers', $oneMessage, $fieldObject);

						break;
					case 'acysms':
						$db->setQuery("SELECT fields_fieldid AS id, fields_namekey AS name FROM `#__acysms_fields` WHERE  `fields_namekey` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
						$fieldObject = $db->loadObject();
						if(empty($fieldObject->name)) continue;

						if($integration->componentName == 'acysms'){
							$receiver = 'user_id';
							$receiverId = true;
						}
						else{
							$receiver = 'user_joomid';
							$receiverId = false;
						}
						$queryUsers = 'SELECT acysmsusers.'.$receiver.'
												FROM `#__acysms_user` as acysmsusers
												LEFT JOIN `#__acysms_phone` AS acysmsphone
												ON acysmsusers.user_phone_number = acysmsphone.phone_number';

						$whereCondition[] = 'acysmsphone.phone_number IS NULL';
						$frequencyConditions = $this->_addFrequencyConditions('acysmsusers', $oneMessage, $fieldObject);

						break;

					case 'acymailing_listsub':
						$fieldObject = new stdClass();
						$fieldObject->name = 'subdate';

						if($integration->componentName == 'acymailing'){
							$receiver = 'subid';
							$receiverId = true;
						}

						$queryUsers = 'SELECT acy_listsub.subid
											FROM `#__acymailing_listsub` as acy_listsub';
						$frequencyConditions = $this->_addFrequencyConditions('acy_listsub', $oneMessage, $fieldObject);

						break;
					case 'communitybuilder':
						$db->setQuery("SELECT fieldid AS id, name AS name FROM `#__comprofiler_fields` WHERE  `fieldid` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
						$fieldObject = $db->loadObject();
						if(empty($fieldObject->name)) continue;
						$receiver = 'id';
						$receiverId = false;
						$queryUsers = 'SELECT comprofiler.id
												FROM #__comprofiler as comprofiler';

						$frequencyConditions = $this->_addFrequencyConditions('comprofiler', $oneMessage, $fieldObject);

						break;
					case 'hikashop':
						$db->setQuery("SELECT field_id AS id, field_realname AS name FROM `#__hikashop_field` WHERE  `field_id` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
						$fieldObject = $db->loadObject();
						if(empty($fieldObject->name)) continue;

						if($integration->componentName == 'hikashop'){
							$receiver = 'user_id';
							$receiverId = true;
						}
						else{
							$receiver = 'user_cms_id';
							$receiverId = false;
						}
						$queryUsers = 'SELECT hikausers.'.$receiver.'
												FROM #__hikashop_user as hikausers
												JOIN #__hikashop_address as hikaaddress
												ON hikausers.user_id = address_user_id';

						$frequencyConditions = $this->_addFrequencyConditions('hikaaddress', $oneMessage, $fieldObject);

						break;
					case 'jomsocial':
						$db->setQuery("SELECT id AS id, name AS name FROM `#__community_fields` WHERE  `id` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
						$fieldObject = $db->loadObject();
						if(empty($fieldObject->name)) continue;
						$receiverId = false;
						$queryUsers = 'SELECT `user_id`
												FROM `#__community_fields_values`';

						$whereCondition[] = '`field_id` = '.intval($fieldObject->id);
						$frequencyConditions = $this->_addFrequencyConditions('value', $oneMessage, $fieldObject);

						break;
					case 'redshop':
						$db->setQuery("SELECT field_id AS id, field_name AS name FROM `#__redshop_fields` WHERE  `field_id` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
						$fieldObject = $db->loadObject();
						if(empty($fieldObject->name)) continue;

						if($integration->componentName == 'redshop'){
							$receiver = 'users_info_id';
							$receiverId = true;
						}
						else{
							$receiver = 'user_id';
							$receiverId = false;
						}
						$queryUsers = 'SELECT '.$receiver.'
												FROM `#__redshop_users_info` as redshopusers
												JOIN `#__redshop_fields_data` as redshopdata
												ON redshopusers.users_info_id = redshopdata.itemid';

						$frequencyConditions = $this->_addFrequencyConditions('value', $oneMessage, $fieldObject);

						break;
					case 'virtuemart_2':
						$db->setQuery("SELECT virtuemart_userfield_id AS id, name AS name FROM `#__virtuemart_userfields` WHERE  `virtuemart_userfield_id` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
						$fieldObject = $db->loadObject();
						if(empty($fieldObject->name)) continue;

						$receiver = 'virtuemart_userinfo_id';
						$receiverId = true;
						$tableInfos = acysms_getColumns('#__virtuemart_userinfos');
						$fields = array_keys($tableInfos);
						if(in_array($fieldObject->name, $fields)){
							$queryUsers = 'SELECT vm.'.$receiver.'
										FROM `#__virtuemart_userinfos` AS vm';

							$frequencyConditions = $this->_addFrequencyConditions('vm', $oneMessage, $fieldObject);
						}
						break;
					case 'virtuemart_1':
						$db->setQuery("SELECT fieldid AS id, name AS name FROM `#__vm_userfield` WHERE  `fieldid` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
						$fieldObject = $db->loadObject();
						if(empty($fieldObject->name)) continue;

						$receiver = 'user_id';
						$receiverId = true;


						$tableInfos = acysms_getColumns('#__vm_user_info');
						$fields = array_keys($tableInfos);
						if(in_array($fieldObject->name, $fields)){
							$queryUsers = 'SELECT vm.'.$receiver.'
									FROM `#__vm_user_info` AS vm';

							$frequencyConditions = $this->_addFrequencyConditions('vm', $oneMessage, $fieldObject);
						}
						break;
				}
				if(empty($queryUsers)){
					$this->messages[] = 'No user query available for this SMS '.$oneMessage->message_id;
					continue;
				}

				$whereConditions = array_merge($whereCondition, $frequencyConditions);

				if(!empty($whereConditions)) $queryUsers .= ' WHERE ('.implode(') AND (', $whereConditions).')';
				$db->setQuery($queryUsers);
				$users = acysms_loadResultArray($db);

				if(!$receiverId){
					$users = $integration->getReceiverIDs($users);
				}

				if(empty($users)){
					$this->messages[] = 'Frequency plugin: 0 receivers found for '.$informationDate.'  for the SMS '.$oneMessage->message_id;
					continue;
				}

				$acyquery = ACYSMS::get('class.acyquery');
				$integrationTo = $oneMessage->message_receiver_table;
				$integrationFrom = $integration->componentName;
				$integration = ACYSMS::getIntegration($integrationTo);
				$integration->initQuery($acyquery);
				$acyquery->addMessageFilters($oneMessage);
				$acyquery->addUserFilters($users, $integrationFrom, $integrationTo);
				$querySelect = $acyquery->getQuery(array('DISTINCT '.$oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.' , "'.$integration->componentName.'", '.$queueSendDate.', '.$config->get('priority_message', 3)));
				$finalQuery = 'INSERT IGNORE INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority) '.$querySelect;

				$db->setQuery($finalQuery);
				$db->query();
				$nbInserted = $db->getAffectedRows();
				$this->messages[] = 'Frequency plugin: '.$nbInserted.' frequency SMS inserted in the queue for '.$informationDate.' for the SMS '.$oneMessage->message_subject;
			}
			else if($type == 'startDate'){

				$startDay = $oneMessage->message_receiver['auto']['frequencysms']['startDate']['day'];
				$startMonth = $oneMessage->message_receiver['auto']['frequencysms']['startDate']['month'];
				$startYear = $oneMessage->message_receiver['auto']['frequencysms']['startDate']['year'];
				$startDate = $startYear.'-'.$startMonth.'-'.$startDay;

				list($sendYear, $sendMonth, $sendDay) = explode("-", ACYSMS::getDate(time(), 'Y-m-d'));

				$nbInterval = $oneMessage->message_receiver['auto']['frequencysms']['nbInterval'];
				$interval = $oneMessage->message_receiver['auto']['frequencysms']['interval'];

				$sendMsg = false;

				if($startDate > $sendDate) continue;

				if($interval == 'day'){
					if((((date('z', strtotime($sendDate)) + 365 * $sendYear) - (date('z', strtotime($startDate)) + (365 * $startYear))) % $nbInterval) != 0) continue;
				}
				else if($interval == 'week'){
					if((date('w', strtotime($sendDate)) != date('w', strtotime($startDate))) || (((date('W', strtotime($sendDate)) + 52 * $sendYear) - (date('W', strtotime($startDate)) + (52 * $startYear))) % $nbInterval) != 0) continue;
				}
				else if($interval == 'month'){
					$nothingToSendAfterMaxDay = false;
					if($sendDay == cal_days_in_month(CAL_GREGORIAN, $sendMonth, $sendYear) && ($startDay != $sendDay + 1) && ($startDay != $sendDay + 2) && ($startDay != $sendDay + 3)) $nothingToSendAfterMaxDay = true;
					if(($startDay != $sendDay) && $nothingToSendAfterMaxDay || ((($startMonth + 12 * $startMonth) - ($sendMonth + 12 * $sendMonth)) % $nbInterval != 0)) continue;
				}
				else if($interval == 'year'){
					$nothingToSendAfterMaxDay = false;
					if($sendDay == cal_days_in_month(CAL_GREGORIAN, $sendMonth, $sendYear) && ($startDay != $sendDay + 1) && ($startDay != $sendDay + 2) && ($startDay != $sendDay + 3)) $nothingToSendAfterMaxDay = true;
					if(($startDay != $sendDay) && $nothingToSendAfterMaxDay || ($startMonth == $sendMonth) && (($sendYear - $sendYear) % $nbInterval != 0)) continue;
				}

				JPluginHelper::importPlugin('acysms');
				$dispatcher = JDispatcher::getInstance();
				$newMessage = clone($oneMessage);
				$pluginReturns = $dispatcher->trigger('onACYSMSReplaceTags', array(&$newMessage));
				unset($newMessage->message_id);

				$stopProcess = false;
				foreach($pluginReturns as $onePluginReturn){
					if(!is_object($onePluginReturn)) continue;
					if(isset($onePluginReturn->generateNewOne) && !$onePluginReturn->generateNewOne){

						$cronHelper = ACYSMS::get('helper.cron');
						$cronHelper->messages = array('Message Generation blocked by a plugin');
						if(!empty($onePluginReturn->message)) $cronHelper->detailMessages = array($onePluginReturn->message);
						$cronHelper->saveReport();
						$stopProcess = true;
						break;
					}
				}
				if($stopProcess) continue;

				$newMessage->message_subject = JText::_('SMS_GENERATED_SMS').' : '.$newMessage->message_subject;
				$newMessage->message_created = time();
				$newMessage->message_type = 'standard';
				$newMessage->message_senddate = time();
				$newMessage->message_status = 'sent';
				$newMessage->message_receiver = $newMessage->message_receiver;
				$newMessage->message_receiver_table = $newMessage->message_receiver_table;
				$newMessage->message_id = $messageClass->save($newMessage);

				$acyquery = ACYSMS::get('class.acyquery');
				$integrationTo = $oneMessage->message_receiver_table;
				$integration = ACYSMS::getIntegration($integrationTo);
				$integration->initQuery($acyquery);
				$acyquery->addMessageFilters($oneMessage);
				$querySelect = $acyquery->getQuery(array('DISTINCT '.$newMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.' , "'.$integration->componentName.'", '.$queueSendDate.', '.$config->get('priority_message', 3)));
				$finalQuery = 'INSERT IGNORE INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority) '.$querySelect;

				$db->setQuery($finalQuery);
				$db->query();
				$nbInserted = $db->getAffectedRows();
				$this->messages[] = 'Frequency plugin: '.$nbInserted.' frequency SMS inserted in the queue for '.$informationDate.' for the SMS '.$oneMessage->message_subject;
			}
			$saveInConfig[$oneMessage->message_id] = $sendDate;
		}
		if(empty($nbInserted)) $this->messages[] = 'Frequency plugin: 0 frequency SMS inserted in the queue';
		$newConfig = new stdClass();
		$newConfig->FrequencyMsgSentToday = serialize(($saveInConfig + $msgAlreadySent));
		$config->save($newConfig);
	}

	function ajax_getdatefields(){
		$db = JFactory::getDBO();
		$table = JRequest::getVar('table', '', '', 'string');
		$tableFields = array();

		switch($table){
			case 'acymailing':
				$query = 'SELECT  namekey AS id, fieldname AS name FROM #__acymailing_fields WHERE type = "date"';
				break;
			case 'acysms':
				$query = 'SELECT  fields_namekey AS id, fields_fieldname AS name FROM #__acysms_fields WHERE fields_type = "date"';
				break;
			case 'acymailing_listsub':
				$field = new stdClass();
				$field->name = 'subdate';
				$field->id = 'subdate';
				$tableFields[] = $field;
				break;
			case 'communitybuilder':
				$query = 'SELECT  fieldid AS id, name AS name, title FROM #__comprofiler_fields WHERE type = "date"';
				break;
			case 'hikashop':
				$query = 'SELECT  field_id AS id, field_namekey AS name, field_realname FROM #__hikashop_field WHERE field_type = "date"';
				break;
			case 'jomsocial':
				$query = 'SELECT  id AS id, fieldcode, name AS name FROM #__community_fields WHERE type = "date"';
				break;
			case 'redshop':
				$query = 'SELECT  field_id AS id, field_title AS name FROM #__redshop_fields WHERE field_type = "12"';
				break;
			case 'virtuemart_2':
				$query = 'SELECT  virtuemart_userfield_id AS id, name AS name FROM #__virtuemart_userfields WHERE type = "date"';
				break;
			case 'virtuemart_1':
				$query = 'SELECT  fieldid AS id, name AS name FROM #__vm_userfield  WHERE type = "date"';
				break;
		}
		if(!empty($query)){
			$db->setQuery($query);
			$tableFields = $db->loadObjectList();
		}
		if(!empty($tableFields)){
			$dateField = JHTML::_('select.genericlist', $tableFields, "data[message][message_receiver][auto][frequencysms][field][column]", 'style="width:auto;" size="1"', 'id', 'name');
		}
		else $dateField = ' No date field found.';
		echo JText::_('SMS_FIELD').' : '.$dateField;
	}

	private function _addFrequencyConditions($prefix, $message, $fieldObject){

		$db = JFactory::getDBO();
		$nbInterval = $message->message_receiver['auto']['frequencysms']['nbInterval'];
		$interval = $message->message_receiver['auto']['frequencysms']['interval'];

		$sendDate = date('Y-m-d', time());
		list($sendYear, $sendMonth, $sendDay) = explode('-', $sendDate);

		$conditions = array();
		$conditions[] = $prefix.'.`'.$fieldObject->name.'` <= '.$db->Quote($sendDate);

		if($interval == 'day'){
			$conditions[] = '((DATE_FORMAT("'.$sendDate.'", "%j") +  '.(365 * $sendYear).') - (DATE_FORMAT('.$prefix.'.`'.$fieldObject->name.'`, "%j") + (365 * DATE_FORMAT('.$prefix.'.`'.$fieldObject->name.'`, "%Y"))))%'.$nbInterval.' = 0';
		}
		else if($interval == 'week'){
			$conditions[] = '( DATE_FORMAT("'.$sendDate.'", "%w")  = DATE_FORMAT('.$prefix.'.`'.$fieldObject->name.'`, "%w"))';
			$conditions[] = '( WEEK("'.$sendDate.'")  + '.(52 * $sendYear).' - (WEEK('.$prefix.'.`'.$fieldObject->name.'`) + (52 * YEAR('.$prefix.'.`'.$fieldObject->name.'`))))%'.$nbInterval.' = 0';
		}
		else if($interval == 'month'){
			$dayCondition = '';
			if($sendDay == cal_days_in_month(CAL_GREGORIAN, $sendMonth, $sendYear)) $dayCondition .= ' OR ( DAY("'.$sendDate.'") = (DAY('.$prefix.'.`'.$fieldObject->name.'`)+ INTERVAL 1 DAY)) OR ( DAY("'.$sendDate.'") = (DAY('.$prefix.'.`'.$fieldObject->name.'`)+ INTERVAL 2 DAY)) OR ( DAY("'.$sendDate.'") = (DAY('.$prefix.'.`'.$fieldObject->name.'`)+ INTERVAL 3 DAY)))';

			$conditions[] = '(( DAY("'.$sendDate.'") = DAY('.$prefix.'.`'.$fieldObject->name.'`)) '.$dayCondition;
			$conditions[] = '(MONTH("'.$sendDate.'") + '.(12 * $sendYear).' - (MONTH('.$prefix.'.`'.$fieldObject->name.'`) + (12 * YEAR('.$prefix.'.`'.$fieldObject->name.'`))))%'.$nbInterval.' = 0';
		}
		else if($interval == 'year'){
			$dayCondition = '';
			if($sendDay == cal_days_in_month(CAL_GREGORIAN, $sendMonth, $sendYear)) $dayCondition .= ' OR ( DAY("'.$sendDate.'") = (DAY('.$prefix.'.`'.$fieldObject->name.'`)+ INTERVAL 1 DAY)) OR ( DAY("'.$sendDate.'") = (DAY('.$prefix.'.`'.$fieldObject->name.'`)+ INTERVAL 2 DAY)) OR ( DAY("'.$sendDate.'") = (DAY('.$prefix.'.`'.$fieldObject->name.'`)+ INTERVAL 3 DAY)))';

			$conditions[] = '(( DAY("'.$sendDate.'") = DAY('.$prefix.'.`'.$fieldObject->name.'`)) '.$dayCondition;
			$conditions[] = '( MONTH("'.$sendDate.'") = MONTH('.$prefix.'.`'.$fieldObject->name.'`))';
			$conditions[] = '( YEAR("'.$sendDate.'") - YEAR('.$prefix.'.`'.$fieldObject->name.'`))%'.$nbInterval.' = 0';
		}

		return $conditions;
	}
}

