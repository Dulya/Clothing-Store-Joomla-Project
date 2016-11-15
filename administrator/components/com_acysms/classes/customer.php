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

class ACYSMScustomerClass extends ACYSMSClass{
	var $tables = array('customer' => 'customer_id');
	var $pkey = 'customer_id';
	var $namekey = 'customer_name';
	var $allowedFields = array('customer_name', 'customer_email');


	function get($id, $default = null){
		$column = is_numeric($id) ? 'customer_id' : 'customer_name';
		$this->database->setQuery('SELECT * FROM #__acysms_customer WHERE '.$column.' = '.$this->database->Quote(trim($id)).' LIMIT 1');
		return $this->database->loadObject();
	}


	function saveForm(){
		$formData = JRequest::getVar('data', array(), '', 'array');

		$customer = new stdClass();
		$customer->customer_id = ACYSMS::getCID('customer_id');

		JArrayHelper::toInteger($formData['customer']['customer_senderprofile_id']);

		if(is_array($formData['customer']['customer_senderprofile_id'])) $formData['customer']['customer_senderprofile_id'] = implode(',', $formData['customer']['customer_senderprofile_id']);

		foreach($formData['customer'] as $column => $value){
			ACYSMS::secureField($column);
			$customer->$column = strip_tags($value);
		}

		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__acysms_customer WHERE customer_joomid = '.intval($customer->customer_joomid));
		$alreadyExistCustomer = $db->loadObject();

		if(!empty($alreadyExistCustomer) && $alreadyExistCustomer->customer_id != $customer->customer_id){
			$this->errors[] = JText::_('SMS_JOOMLA_USER_ALREADY_USED');
			$this->errors[] = '<a href="'.ACYSMS::completeLink('customer&task=edit&cid[]='.$alreadyExistCustomer->customer_id).'" >'.JText::_('SMS_CLICK_EDIT_USER').'</a>';
			return false;
		}

		$customer_id = $this->save($customer);
		if(!$customer_id) return false;
		JRequest::setVar('customer_id', $customer_id);

		if(!empty($alreadyExistCustomer) && $customer->customer_credits > $alreadyExistCustomer->customer_credits){
			$customerClass = ACYSMS::get('class.customer');
			$customerClass->enableMessagesByCustomer($customer->customer_joomid);
		}
		return true;
	}


	function getCredits($customerJoomIds){
		$db = JFactory::getDBO();

		if(is_array($customerJoomIds)){
			JArrayHelper::toInteger($customerJoomIds);
			$query = 'SELECT customer_credits FROM '.ACYSMS::table('customer').' WHERE customer_joomid IN ('.implode(',', $customerJoomIds).')';
			$db->setQuery($query);
			return $db->loadObjectList();
		}else if(is_numeric($customerJoomIds)){
			$query = 'SELECT customer_credits FROM '.ACYSMS::table('customer').' WHERE customer_joomid = '.intval($customerJoomIds);
			$db->setQuery($query);
			return $db->loadResult();
		}
	}

	function getCustomerByJoomID($joomlaId){
		$db = JFactory::getDBO();
		$query = 'SELECT * FROM '.ACYSMS::table('customer').' WHERE customer_joomid = '.intval($joomlaId);
		$db->setQuery($query);
		return $db->loadObject();
	}

	function sendLowCreditsNotification($user_id){
		$user = JFactory::getUser($user_id);

		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);

		$config = JFactory::getConfig();
		if(ACYSMS_J30){
			$sender = array($config->get('config.mailfrom'), $config->get('config.fromname'));
		}else $sender = array($config->getValue('config.mailfrom'), $config->getValue('config.fromname'));

		if(!empty($sender[0]) && !empty($sender[1])) $mailer->setSender($sender);

		$mailer->addRecipient($user->email);
		$subject = JText::_('SMS_LOW_CREDIT_NOTIFICATION_SUBJECT');
		$mailer->setSubject($subject);
		$body = JText::_('SMS_LOW_CREDIT_NOTIFICATION_BODY');
		$mailer->setBody($body);
		jimport('joomla.filesystem.file');
		$send = $mailer->Send();
		if($send !== true){
			$cronHelper = ACYSMS::get('helper.cron');
			$cronHelper->messages = array('Error while sending the low credits notification');

			$messageToDisplay = (is_object($send) && method_exists($send, '__toString')) ? $send->__toString() : JText::_('JLIB_MAIL_FUNCTION_OFFLINE');

			$cronHelper->detailMessages = array($messageToDisplay);
			$cronHelper->saveReport();
		}
	}

	public function enableMessagesByCustomer($customer_id){
		$db = JFactory::getDBO();
		$query = 'UPDATE #__acysms_message SET message_status = "notsent" WHERE message_senderid = '.intval($customer_id);
		$db->setQuery($query);
		$db->Query();
	}


	public function changeCredits($joomlaUserId, $nbCredits, $action = 'set'){
		if(empty($joomlaUserId) || empty($nbCredits)) return;
		$customerClass = ACYSMS::get('class.customer');
		$customer = $customerClass->getCustomerByJoomID($joomlaUserId);

		if(empty($customer)){
			$customer = new stdClass();
			$customer->customer_joomid = $joomlaUserId;
		}

		if($action == 'add'){
			$customer->customer_credits += intval($nbCredits);
		}else if($action == 'set'){
			$customer->customer_credits = intval($nbCredits);
		}else return;

		$customerClass->save($customer);
	}
}
