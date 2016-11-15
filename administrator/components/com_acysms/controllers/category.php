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

class CategoryController extends ACYSMSController{

	var $pkey = 'category_id';
	var $table = 'category';
	var $orderingColumnName = 'category_ordering';
	var $aclCat = 'categories';


	function copy(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('categories', 'copy')) return;

		$db = JFactory::getDBO();
		$time = time();
		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();
		foreach($cids as $oneCategoryid){
			$query = 'INSERT INTO `#__acysms_category` (`category_name`, `category_ordering`)';
			$query .= " SELECT CONCAT('copy_',`category_name`), `category_ordering` FROM `#__acysms_category` WHERE `category_id` = ".intval($oneCategoryid);
			$db->setQuery($query);
			$db->query();
		}
		return $this->listing();
	}

	function store(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('categories', 'manage')) return;
		$categoryClass = ACYSMS::get('class.category');

		$status = $categoryClass->saveForm();
		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'message');
		}else{
			ACYSMS::enqueueMessage(JText::_('SMS_ERROR_SAVING'), 'error');
			if(!empty($categoryClass->errors)){
				foreach($categoryClass->errors as $oneError){
					ACYSMS::enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function remove(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('categories', 'delete')) return;
		$categoryClass = ACYSMS::get('class.category');

		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();
		$num = $categoryClass->delete($cids);

		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');

		return $this->listing();
	}
}
