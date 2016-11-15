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

class DataController extends acysmsController{

	function import(){
		if(!$this->isAllowed('receivers', 'import')) return;
		JRequest::setVar('layout', 'import');
		return parent::display();
	}

	function export(){
		if(!$this->isAllowed('receivers', 'export')) return;
		JRequest::setVar('layout', 'export');
		return parent::display();
	}

	function importConfig(){

		$token = acysms_getFormToken();
		if(!array_key_exists($token, $_REQUEST) && !JRequest::checkToken('get') && !JRequest::checkToken('post')) die('Invalid Token');

		if(!$this->isAllowed('receivers', 'import')) return;

		$importHelper = ACYSMS::get('helper.import');
		$importFrom = JRequest::getCmd('importfrom');

		if(!empty($importFrom)){
			if(!$importHelper->$importFrom()){
				return $this->import();
				if(file_exists($importHelper->getUploadDirectory().DS.$importHelper->filename)) $importContent = file_get_contents($importHelper->getUploadDirectory().DS.$importHelper->filename);
				if(empty($importContent)){
					ACYSMS::enqueueMessage(JText::_('SMS_IMPORT_NO_CONTENT'), 'error');
					return $this->import();
				}
			}
			JRequest::setVar('filename', $importHelper->filename);
		}

		JRequest::setVar('layout', 'importconfig');
		return parent::display();
	}



	function doImport(){
		if(!$this->isAllowed('receivers', 'import')) return;
		$importHelper = ACYSMS::get('helper.import');
		$app = JFactory::getApplication();

		$filename = JRequest::getCmd('filename', '');
		$paramBase = ACYSMS_COMPONENT.'.'.$filename;

		$importHelper->importBlocked = $app->getUserStateFromRequest($paramBase."importBlocked", 'importBlocked', 0, 'string');
		$importHelper->overwriteExisting = $app->getUserStateFromRequest($paramBase."overwriteExisting", 'overwriteExisting', 0, 'string');
		$importHelper->columns = $app->getUserStateFromRequest($paramBase."importColumn", 'importColumn', 0, 'array');
		$importHelper->importFirstLine = $app->getUserStateFromRequest($paramBase."importFirstLine", 'importFirstLine', 0, 'string');
		$importHelper->charsetConvert = $app->getUserStateFromRequest($paramBase."charsetconvert", 'charsetconvert', 0, 'string');

		$importHelper->handleContent($filename);

$ctrl = $app->isAdmin() ? 'receiver' : 'frontreceiver';
		$this->setRedirect(ACYSMS::completeLink($ctrl, false, true));
	}



	function doexport(){
		JRequest::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		if(!$this->isAllowed('receivers', 'export')) return;

		ACYSMS::increasePerf();

		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$encodingClass = ACYSMS::get('helper.encoding');
		$exportHelper = ACYSMS::get('helper.export');


		$fieldsToExport = JRequest::getVar('exportdata');
		$inseparator = JRequest::getString('exportseparator');
		$groupsToExport = JRequest::getVar('exportgroups');
		$filtersExport = JRequest::getVar('exportfilter');

		$exportGroups = array();

		if(!empty($filtersExport['subscribed'])){
			foreach($groupsToExport as $groupid => $checked){
				if(!empty($checked)) $exportGroups[] = (int)$groupid;
			}
		}

		$inseparator = str_replace(array('semicolon', 'comma'), array(';', ','), $inseparator);
		$exportFormat = JRequest::getString('exportformat');
		if(!in_array($inseparator, array(',', ';'))) $inseparator = ';';

		$exportFields = array();
		$selectOthers = '';
		foreach($fieldsToExport as $fieldName => $checked){
			if(!empty($checked)) $exportFields[] = ACYSMS::secureField($fieldName);
		}

		$selectFields = 'acyms_users.`'.implode('`, acyms_users.`', $exportFields).'`';


		$newConfig = new stdClass();
		$newConfig->export_fields = implode(',', $exportFields);
		$newConfig->export_separator = $inseparator;
		$newConfig->export_format = $exportFormat;
		$newConfig->export_filters = serialize((object)$filtersExport);
		$config->save($newConfig);

		$where = array();

		if(empty($exportGroups)){
			$query = 'SELECT '.$selectFields.' FROM '.ACYSMS::table('user').' as acyms_users';
			if(!$app->isAdmin()){
				$user = JFactory::getUser();
				$query .= ' JOIN '.ACYSMS::table('groupuser').' AS groupuser ON acyms_users.user_id = groupuser.groupuser_user_id JOIN '.ACYSMS::table('group').' AS acysms_group ON groupuser.groupuser_group_id = acysms_group.group_id';
				$where[] = 'acysms_group.group_user_id = '.intval($user->get('id'));
			}
		}else{
			JArrayHelper::toInteger($exportGroups);
			$query = 'SELECT '.$selectFields.' FROM '.ACYSMS::table('groupuser').' AS groupuser JOIN #__acysms_user AS acyms_users ON acyms_users.user_id = groupuser.groupuser_user_id';
			$where[] = 'groupuser.groupuser_group_id IN ('.implode(',', $exportGroups).')';
			$where[] = 'groupuser.groupuser_status = 1';
		}


		if(!empty($filtersExport['userStatus'])){

			switch($filtersExport['userStatus']){
				case 'blocked':
					$query .= ' JOIN #__acysms_phone AS acysms_phone ON acyms_users.user_phone_number = acysms_phone.phone_number ';
					$where[] = 'acysms_phone.phone_number IS NOT NULL ';
					break;
				case 'notblocked':
					$query .= ' LEFT JOIN #__acysms_phone AS acysms_phone ON acyms_users.user_phone_number = acysms_phone.phone_number ';
					$where[] = 'acysms_phone.phone_number IS NULL';
					break;
				case 'all':
					$query .= ' LEFT JOIN #__acysms_phone AS acysms_phone ON acyms_users.user_phone_number = acysms_phone.phone_number ';
					break;
			}
		}

		if(!empty($where)) $query .= ' WHERE '.implode(' AND ', $where);
		$db->setQuery($query);
		$allData = $db->loadAssocList();

		$fileName = 'export_'.date('Y-m-d');
		$exportHelper->addHeaders($fileName);

		$eol = "\r\n";
		$before = '"';
		$separator = '"'.$inseparator.'"';
		$after = '"';

		$allFields = $exportFields;

		echo $before.implode($separator, $allFields).$after.$eol;
		for($i = 0, $a = count($allData); $i < $a; $i++){
			echo $before.$encodingClass->change(implode($separator, $allData[$i]), 'UTF-8', $exportFormat).$after.$eol;
		}
		exit;
	}

	function cancel(){
		$filename = JRequest::getCmd('filename');
		$app = JFactory::getApplication();
		if(!empty($filename)){
			jimport('joomla.filesystem.file');
			$importHelper = ACYSMS::get('helper.import');
			$uploadPath = $importHelper->getUploadDirectory();
			if(file_exists($uploadPath.$filename)) JFile::delete($uploadPath.$filename);
		}


		if($app->isAdmin()){
			$link = ACYSMS::completeLink('receiver', false, true);
		}else $link = ACYSMS::completeLink('frontreceiver', false, true);

		$app->redirect($link);
	}
}
