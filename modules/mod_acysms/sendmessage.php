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

JSession::checkToken() or die('Invalid Token');

class acySMSsendmessageClass{

	function sendSMS(){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$currentPage = $_SERVER['HTTP_REFERER'];

		$moduleId = JRequest::getInt('module_id');
		if(empty($moduleId)) return;
		$db->setQuery('SELECT params FROM #__modules WHERE id = '.intval($moduleId).' AND `module` LIKE \'%acysms%\' LIMIT 1');
		$dbParams = $db->loadResult();
		if(empty($dbParams)) return;

		$moduleParameter = new acysmsParameter($dbParams);
		$senderprofile = $moduleParameter->get('senderprofile');

		$phoneHelper = ACYSMS::get('helper.phone');
		$class = ACYSMS::get('class.senderprofile');

		$numbers = JRequest::getVar('module_'.$moduleId.'_numbers', '');
		if(empty($numbers[0]['phone_num'])){
			ACYSMS::enqueueMessage(JText::_('SMS_NO_PHONE'), 'error');
			$app->redirect($currentPage);
		}

		if(empty($senderprofile)){
			ACYSMS::enqueueMessage(JText::_('SMS_NO_SENDERPROFILE'), 'error');
			$app->redirect($currentPage);
		}

		$message_body = JRequest::getVar("message_body", '');
		if(empty($message_body)){
			ACYSMS::enqueueMessage(JText::_('SMS_ENTER_BODY'), 'error');
			$app->redirect($currentPage);
		}

		$receivers = array();
		foreach($numbers as $oneNumber){
			$validPhoneNumber = $phoneHelper->getValidNum(implode(',', $oneNumber));
			if(!$validPhoneNumber){
				ACYSMS::enqueueMessage(JText::sprintf('SMS_INVALID_PHONE_NUMBER', implode(',', $oneNumber)), 'warning');
			}else $receivers[] = acysms_getEscaped(strip_tags($validPhoneNumber));
		}


		if(empty($receivers)) $app->redirect($currentPage);
		$query = 'SELECT phone_number FROM '.ACYSMS::table("phone").' WHERE phone_number IN ("'.implode('","', $receivers).'")';
		$db->setQuery($query);
		$blockedPhones = $db->loadObjectList();


		$gateway = $class->getGateway($senderprofile);
		if(!$gateway->open()){
			ACYSMS::enqueueMessage(implode('<br />', $gateway->errors), 'error');
			return $this->preview();
		}

		foreach($receivers as $oneReceiver => $onePhoneNumber){

			if(!empty($blockedPhones[$onePhoneNumber])){
				ACYSMS::enqueueMessage(JText::sprintf('SMS_ERROR_SENT', '', '<b><i>'.$onePhoneNumber.'</i></b>').'<br />'.JText::sprintf('SMS_USER_BLOCKED', $onePhoneNumber), 'error');
			}else{
				if(!empty($gateway->waittosend)) sleep($gateway->waittosend);
				$status = $gateway->send($message_body, $onePhoneNumber);

				$replace = array('{user_name}', '{user_phone_number}', '{message_subject}');
				$replaceby = array('', $onePhoneNumber, '');

				if(!$status){
					ACYSMS::enqueueMessage(JText::sprintf('SMS_ERROR_SENT', '', '<b><i>'.$onePhoneNumber.'</i></b>').'<br />'.implode('<br />', $gateway->errors), 'error');
				}else{
					$config = ACYSMS::config();
					$user = JFactory::getUser();
					$allowCustomerManagement = $config->get('allowCustomersManagement');

					if(!$app->isAdmin() && $allowCustomerManagement && !empty($user->id)){
						$messageClass = ACYSMS::get('class.message');
						$nbParts = $messageClass->countMessageParts($message_body)->nbParts;

						$customerClass = ACYSMS::get('class.customer');
						$nbCreditsLeft = $customerClass->getCredits($user->id);

						if($nbCreditsLeft - $nbParts <= 0){
							ACYSMS::enqueueMessage(JText::_('SMS_NOT_ENOUGH_CREDITS'), 'error');
							$customerClass->sendLowCreditsNotification($user->id);
							$gateway->close();
							$app->redirect($currentPage);
						}
						$customer = $customerClass->getCustomerByJoomID($user->id);
						$customer->customer_credits -= $nbParts;
						$customerClass->save($customer);
					}
					ACYSMS::enqueueMessage(str_replace($replace, $replaceby, JText::_('SMS_SUCC_SENT')), 'message');
				}
			}
		}
		$gateway->close();
		$app->redirect($currentPage);
	}
}
