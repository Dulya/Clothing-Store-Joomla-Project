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

class FieldsController extends acysmsController{
	var $pkey = 'fields_fieldid';
	var $table = 'fields';
	var $orderingColumnName = 'fields_ordering';
	var $groupMap = '';
	var $groupVal = '';

	function store(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('configuration', 'manage')) return;

		$class = ACYSMS::get('class.fields');
		$status = $class->saveForm();
		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'message');
		}else{
			ACYSMS::enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!empty($class->errors)){
				foreach($class->errors as $oneError){
					ACYSMS::enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('configuration', 'manage')) return;

		$cids = JRequest::getVar('cid', array(), '', 'array');

		$class = ACYSMS::get('class.fields');
		$num = $class->delete($cids);

		if($num){
			ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');
		}

		return $this->listing();
	}

	function choose(){
		if(!$this->isAllowed('configuration', 'manage')) return;
		JRequest::setVar('layout', 'choose');
		return parent::display();
	}

}
