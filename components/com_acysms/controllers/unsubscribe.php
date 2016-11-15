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

class UnsubscribeController extends acysmsController{

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('unsubscribe');
	}

	function unsubscribe(){
		JRequest::setVar('layout', 'unsubscribe');
		return parent::display();
	}

	function saveunsub(){
		JRequest::checkToken() or die('Invalid Token');
		$db = JFactory::getDBO();

		$phoneHelper = ACYSMS::get('helper.phone');
		$phoneClass = ACYSMS::get('class.phone');

		$validPhoneNumber = $phoneHelper->getValidNum(implode(',', JRequest::getVar("number", '')));
		if(!$validPhoneNumber){
			ACYSMS::enqueueMessage($phoneHelper->error, 'warning');
			JRequest::setVar('layout', 'unsubscribe');
			return parent::display();
		}

		$query = 'SELECT phone_number FROM '.ACYSMS::table("phone").' WHERE phone_number = '.$db->Quote($validPhoneNumber);
		$db->setQuery($query);
		$status = $db->loadResult();

		if(empty($status)){
			ACYSMS::enqueueMessage(JText::_('SMS_ALREADY_UNSUBSCRIBE'), 'success');
			JRequest::setVar('layout', 'saveunsub');
			return parent::display();
		}

		$phoneClass->manageStatus($validPhoneNumber, 0);
		ACYSMS::enqueueMessage(JText::_('SMS_SUCCESSFULLY_UNSUBSCRIBED'), 'success');
		JRequest::setVar('layout', 'saveunsub');
		return parent::display();
	}
}
