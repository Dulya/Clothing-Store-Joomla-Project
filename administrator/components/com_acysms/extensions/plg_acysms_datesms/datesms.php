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

class plgAcysmsDatesms extends JPlugin{

	var $sendervalues = array();
	var $messages = array();
	var $debug = false;

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
	}

	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_AUTO_DATE');
		$types['dateField'] = $newType;
		return;
	}

	function onACYSMSDisplayParamsAutoMessage_dateField(){
		$result = '';
		$db = JFactory::getDBO();

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i += 5) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$dateautotime = new stdClass();
		$dateautotime->hourField = JHTML::_('select.genericlist', $hours, 'data[message][message_receiver][auto][dateField][hour]', 'style="width:50px;" class="inputbox"', 'value', 'text', '08');
		$dateautotime->minField = JHTML::_('select.genericlist', $min, 'data[message][message_receiver][auto][dateField][min]', 'style="width:50px;" class="inputbox"', 'value', 'text', '00');


		$importData = array();
		$importvalues = array();

		$possibleImport = array();
		$possibleImport[$db->getPrefix().'comprofiler'] = array('communitybuilder', 'Community Builder');
		$possibleImport[$db->getPrefix().'acymailing_listsub'] = array('acymailing_listsub', 'AcyMailing Subscription');
		$possibleImport[$db->getPrefix().'acymailing_subscriber'] = array('acymailing', 'AcyMailing');
		$possibleImport[$db->getPrefix().'hikashop_address'] = array('hikashop', 'HikaShop');
		$possibleImport[$db->getPrefix().'redshop_users_info'] = array('redshop', 'RedShop');
		$possibleImport[$db->getPrefix().'virtuemart_userinfos'] = array('virtuemart_2', 'VirtueMart 2');
		$possibleImport[$db->getPrefix().'community_users'] = array('jomsocial', 'JomSocial');
		$possibleImport[$db->getPrefix().'vm_user_info'] = array('virtuemart_1', 'VirtueMart 1');
		$possibleImport[$db->getPrefix().'acysms_user'] = array('acysms', 'AcySMS');
		$possibleImport[$db->getPrefix().'social_profiles'] = array('easyprofile', 'Easy Profile');


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

		$timeValues = array();
		$timeValues[] = JHTML::_('select.option', 'before', JText::_('SMS_BEFORE'));
		$timeValues[] = JHTML::_('select.option', 'after', JText::_('SMS_AFTER'));
		$timeValueDropDown = JHTML::_('select.genericlist', $timeValues, "data[message][message_receiver][auto][dateField][time]", 'style="width:auto" size="1" class="chzn-done"', 'value', 'text');

		$presentIntegrations = JHTML::_('select.genericlist', $importvalues, "data[message][message_receiver][auto][dateField][table]", 'size="1" class="chzn-done" onchange="callFunction(\'plg=datesms&function=getdatefields&table=\'+this.value,\'dateField\');"', 'value', 'text');
		$delay_date = '<input type="text" name="data[message][message_receiver][auto][dateField][daybefore]" class="inputbox" style="width:50px;" value="0">';

		$result .= JText::sprintf('SMS_SEND_DATEFIELD', $delay_date, $timeValueDropDown, $dateautotime->hourField.' : '.$dateautotime->minField);
		$result .= '<br />'.JText::_('SMS_TABLE').' : '.$presentIntegrations;
		$result .= '<div id="dateField">'.JText::_('SMS_FIELD').' : <input readonly="readonly" type="text" value="" name="data[message][message_receiver][auto][dateField][field]"/></div>';
		echo $result;
	}

	function onAcySMSTestPlugin(){
		$this->debug = true;
		$this->onACYSMSDailyCron();
		ACYSMS::display($this->messages);
	}

	function onACYSMSDailyCron(){
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$config = ACYSMS::config();
		$allMessages = $messageClass->getAutoMessage('dateField');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No message configured in AcySMS for a special date, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create a message</a> using the type : Automatic -> '.JText::_('SMS_AUTO_DATE');
			return;
		}

		foreach($allMessages as $oneMessage){
			$integration = ACYSMS::getIntegration($oneMessage->message_receiver_table);
			if(empty($oneMessage->message_receiver['auto']['dateField']['field'])){
				$this->messages[] = "Please select a date field for the SMS ".$oneMessage->message_id;
				continue;
			}
			$field = $oneMessage->message_receiver['auto']['dateField']['field'];
			$time = $oneMessage->message_receiver['auto']['dateField']['time'];
			if($time == 'before') $date = time() + 86400 + (intval($oneMessage->message_receiver['auto']['dateField']['daybefore']) * 86400);
			else if($time == 'after') $date = time() + 86400 - (intval($oneMessage->message_receiver['auto']['dateField']['daybefore']) * 86400);

			$dateDay = date('d', $date);
			$dateMonth = date('m', $date);
			$dateYear = date('Y', $date);

			if($time == 'before') $senddate = ACYSMS::getTime($dateYear.'-'.$dateMonth.'-'.$dateDay.' '.$oneMessage->message_receiver['auto']['dateField']['hour'].':'.$oneMessage->message_receiver['auto']['dateField']['min']) - (intval($oneMessage->message_receiver['auto']['dateField']['daybefore']) * 86400);
			else if($time == 'after') $senddate = ACYSMS::getTime($dateYear.'-'.$dateMonth.'-'.$dateDay.' '.$oneMessage->message_receiver['auto']['dateField']['hour'].':'.$oneMessage->message_receiver['auto']['dateField']['min']) + (intval($oneMessage->message_receiver['auto']['dateField']['daybefore']) * 86400);

			if(empty($field)){
				$this->messages[] = "Please select a date field for the SMS ".$oneMessage->message_id;
				continue;
			}

			$receiverId = false;

			switch($oneMessage->message_receiver['auto']['dateField']['table']){
				case 'acymailing':
					$db->setQuery("SELECT fieldid AS id, namekey AS name FROM `#__acymailing_fields` WHERE  `namekey` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
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
											FROM `#__acymailing_subscriber` as acymailingsubscribers
											WHERE acymailingsubscribers.enabled = 1
											AND acymailingsubscribers.accept = 1
											AND DAY(acymailingsubscribers.`'.$fieldObject->name.'`) = '.$db->Quote($dateDay).'
											AND MONTH(acymailingsubscribers.`'.$fieldObject->name.'`)='.$db->Quote($dateMonth).'
											AND YEAR(acymailingsubscribers.`'.$fieldObject->name.'`)='.$db->Quote($dateYear);
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
											ON acysmsusers.user_phone_number = acysmsphone.phone_number
											WHERE acysmsphone.phone_number IS NULL
											AND DAY(acysmsusers.`'.$fieldObject->name.'`) = '.$db->Quote($dateDay).'
											AND MONTH(acysmsusers.`'.$fieldObject->name.'`)='.$db->Quote($dateMonth).'
											AND YEAR(acysmsusers.`'.$fieldObject->name.'`)='.$db->Quote($dateYear);
					break;

				case 'acymailing_listsub':
					$fieldObject = new stdClass();
					$fieldObject->name = 'subdate';

					if($integration->componentName == 'acymailing'){
						$receiver = 'subid';
						$receiverId = true;
					}
					$queryUsers = 'SELECT acy_listsub.subid
											FROM `#__acymailing_listsub` as acy_listsub
											WHERE DAY(acy_listsub.`'.$fieldObject->name.'`) = '.$db->Quote($dateDay).'
											AND MONTH(acy_listsub.`'.$fieldObject->name.'`)='.$db->Quote($dateMonth).'
											AND YEAR(acy_listsub.`'.$fieldObject->name.'`)='.$db->Quote($dateYear);
					break;
				case 'communitybuilder':
					$db->setQuery("SELECT fieldid AS id, name AS name FROM `#__comprofiler_fields` WHERE  `fieldid` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
					$fieldObject = $db->loadObject();
					if(empty($fieldObject->name)) continue;
					$receiver = 'id';
					$receiverId = false;
					$queryUsers = 'SELECT comprofiler.id
											FROM #__comprofiler as comprofiler
											WHERE DAY(comprofiler.`'.$fieldObject->name.'`) = '.$db->Quote($dateDay).'
											AND MONTH(comprofiler.`'.$fieldObject->name.'`)='.$db->Quote($dateMonth).'
											AND YEAR(comprofiler.`'.$fieldObject->name.'`)='.$db->Quote($dateYear);
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
											ON hikausers.user_id = address_user_id
											WHERE DAY(hikaaddress.`'.$fieldObject->name.'`) = '.$db->Quote($dateDay).'
											AND MONTH(hikaaddress.`'.$fieldObject->name.'`)= '.$db->Quote($dateMonth).'
											AND YEAR(hikaaddress.`'.$fieldObject->name.'`)= '.$db->Quote($dateYear);
					break;
				case 'jomsocial':
					$db->setQuery("SELECT id AS id, name AS name FROM `#__community_fields` WHERE  `id` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
					$fieldObject = $db->loadObject();
					if(empty($fieldObject->name)) continue;
					$receiverId = false;
					$queryUsers = 'SELECT `user_id`
											FROM `#__community_fields_values`
											WHERE `field_id` = '.intval($fieldObject->id).'
											AND DAY(`value`) = '.$db->Quote($dateDay).'
											AND MONTH(`value`)= '.$db->Quote($dateMonth).'
											AND YEAR(`value`)= '.$db->Quote($dateYear);
					break;
				case 'redshop':
					$db->setQuery("SELECT field_id AS id, field_name AS name FROM `#__redshop_fields` WHERE  `field_id` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
					$fieldObject = $db->loadObject();

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
											ON redshopusers.users_info_id = redshopdata.itemid
											WHERE redshopdata.`fieldid` = '.intval($fieldObject->id).'
											AND redshopdata.`data_txt` LIKE "'.$dateDay.'-'.$dateMonth.'-'.$dateYear.'"';
					break;
				case 'virtuemart_2':
					$db->setQuery("SELECT virtuemart_userfield_id AS id, name AS name FROM `#__virtuemart_userfields` WHERE  `virtuemart_userfield_id` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
					$fieldObject = $db->loadObject();
					$receiver = 'virtuemart_userinfo_id';
					$receiverId = true;
					$tableInfos = acysms_getColumns('#__virtuemart_userinfos');
					$fields = array_keys($tableInfos);
					if(in_array($fieldObject->name, $fields)){
						$queryUsers = 'SELECT vm.'.$receiver.'
										FROM `#__virtuemart_userinfos` as vm
										WHERE DAY(vm.`'.$fieldObject->name.'`) = '.$db->Quote($dateDay).'
										AND MONTH(vm.`'.$fieldObject->name.'`)= '.$db->Quote($dateMonth).'
										AND YEAR(vm.`'.$fieldObject->name.'`)= '.$db->Quote($dateYear);
					}
					break;
				case 'virtuemart_1':
					$db->setQuery("SELECT fieldid AS id, name AS name FROM `#__vm_userfield` WHERE  `fieldid` =  ".$db->Quote($oneMessage->message_receiver['auto']['dateField']['field'])."  LIMIT 1");
					$fieldObject = $db->loadObject();
					$receiver = 'user_id';
					$receiverId = true;


					$tableInfos = acysms_getColumns('#__vm_user_info');
					$fields = array_keys($tableInfos);
					if(in_array($fieldObject->name, $fields)){
						$queryUsers = 'SELECT vm.'.$receiver.'
									FROM `#__vm_user_info` as vm
									WHERE DAY(vm.`'.$fieldObject->name.'`) = '.$db->Quote($dateDay).'
									AND MONTH(vm.`'.$fieldObject->name.'`)= '.$db->Quote($dateMonth).'
									AND YEAR(vm.`'.$fieldObject->name.'`)= '.$db->Quote($dateYear);
					}
					break;

				case 'easyprofile':
					$db->setQuery("SELECT  id AS id, alias AS name FROM #__jsn_fields WHERE `id` =  ".$db->Quote($oneMessage->message_receiver['auto']['birthday']['field'])."  LIMIT 1");
					$fieldObject = $db->loadObject();
					$receiver = 'id';
					$receiverId = true;

					$queryUsers = 'SELECT easyprofileUser.'.$receiver.'
											FROM `#__jsn_users` as easyprofileUser
											WHERE DAY(easyprofileUser.`'.$fieldObject->name.'`) = '.$db->Quote($dateDay).'
											AND MONTH(easyprofileUser.`'.$fieldObject->name.'`) ='.$db->Quote($dateMonth).'
											AND YEAR(easyprofileUser.`'.$fieldObject->name.'`)= '.$db->Quote($dateYear);;
					break;
			}
			if(!empty($queryUsers)){
				$db->setQuery($queryUsers);
				$users = acysms_loadResultArray($db);
			}
			if(!$receiverId){
				$users = $integration->getReceiverIDs($users);
			}
			if(empty($users)){
				$this->messages[] = 'Date plugin: 0 SMS inserted in the queue for '.$dateDay.'-'.$dateMonth.'-'.$dateYear.' for the SMS '.$oneMessage->message_id;
				continue;
			}

			$acyquery = ACYSMS::get('class.acyquery');
			$integrationTo = $oneMessage->message_receiver_table;
			$integrationFrom = $integration->componentName;
			$integration = ACYSMS::getIntegration($integrationTo);
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);
			$acyquery->addUserFilters($users, $integrationFrom, $integrationTo);
			$querySelect = $acyquery->getQuery(array('DISTINCT '.$oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.' , "'.$integration->componentName.'", '.$senddate.', '.$config->get('priority_message', 3)));
			$finalQuery = 'INSERT IGNORE INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority) '.$querySelect;

			$this->success = true;
			$db->setQuery($finalQuery);
			$db->query();
			$nbInserted = $db->getAffectedRows();
			$this->messages[] = 'Date plugin: '.$nbInserted.' SMS inserted in the queue for '.$dateDay.'-'.$dateMonth.'-'.$dateYear.' for the SMS '.$oneMessage->message_subject;
		}
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
			case 'easyprofile':
				$query = 'SELECT  id AS id, title AS name FROM #__jsn_fields WHERE type = "date"';
				break;
		}
		if(!empty($query)){
			$db->setQuery($query);
			$tableFields = $db->loadObjectList();
		}
		if(!empty($tableFields)){
			$dateField = JHTML::_('select.genericlist', $tableFields, "data[message][message_receiver][auto][dateField][field]", 'style="width:auto;" size="1"', 'id', 'name');
		}
		else $dateField = ' No date field found.';
		echo JText::_('SMS_FIELD').' : '.$dateField;
	}
}//endclass
