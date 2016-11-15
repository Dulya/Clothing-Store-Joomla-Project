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

class GroupController extends acysmsController{

	var $pkey = 'group_id';
	var $table = 'group';
	var $orderingColumnName = 'group_ordering';
	var $aclCat = 'groups';

	function store(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('groups', 'manage')) return;

		$groupClass = ACYSMS::get('class.group');
		$status = $groupClass->saveForm();
		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'message');
		}else{
			ACYSMS::enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!empty($groupClass->errors)){
				foreach($groupClass->errors as $oneError){
					ACYSMS::enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('groups', 'delete')) return;

		$group_ids = JRequest::getVar('cid', array(), '', 'array');

		$groupClass = ACYSMS::get('class.group');
		$num = $groupClass->delete($group_ids);

		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');

		JRequest::setVar('layout', 'listing');
		return parent::display();
	}

	function choose(){
		if(!$this->isAllowed('groups', 'manage')) return;
		JRequest::setVar('layout', 'choose');
		return parent::display();
	}

	function archive(){
		JRequest::checkToken() || JRequest::checkToken('get') || JSession::checkToken('get') || die('Invalid Token');

		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();

		$groupClass = ACYSMS::get('class.group');

		$affectedRows = $groupClass->toggleArchiveGroup($cids);

		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_UPDATED_ELEMENTS', $affectedRows), 'message');
		return $this->listing();
	}
}
