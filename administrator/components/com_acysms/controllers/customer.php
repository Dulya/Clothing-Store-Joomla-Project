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

class CustomerController extends ACYSMSController{

	var $aclCat = 'customer';


	function store(){
		JRequest::checkToken() or die('Invalid Token');
		$customerClass = ACYSMS::get('class.customer');

		$status = $customerClass->saveForm();
		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'message');
		}else{
			ACYSMS::enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!empty($customerClass->errors)){
				foreach($customerClass->errors as $oneError){
					ACYSMS::enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() or die('Invalid Token');
		$customerClass = ACYSMS::get('class.customer');

		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();
		$num = $customerClass->delete($cids);

		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');

		return $this->listing();
	}
}
