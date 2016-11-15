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

class UserController extends ACYSMSController{

	var $pkey = 'user_id';
	var $table = 'user';

	function store(){
		if(!$this->isAllowed('receivers', 'manage')) return;
		JRequest::checkToken() or die('Invalid Token');

		$userClass = ACYSMS::get('class.user');
		$status = $userClass->saveForm();

		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'message');
		}else{
			ACYSMS::enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!empty($userClass->errors)){
				foreach($userClass->errors as $oneError){
					ACYSMS::enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function save(){
		$this->store();
		return $this->cancel();
	}

	function cancel(){
		$app = JFactory::getApplication();
		if($app->isAdmin()){
			$redirectURL = ACYSMS::completeLink('receiver', false, true);
		}else $redirectURL = ACYSMS::completeLink('frontreceiver', false, true);

		$this->setRedirect($redirectURL);
	}

	function choosejoomuser(){
		JHTML::_('behavior.modal', 'a.modal');
		JRequest::setVar('layout', 'choosejoomuser');
		return parent::display();
	}
}
