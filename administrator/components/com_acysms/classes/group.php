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

class ACYSMSgroupClass extends ACYSMSClass{
	var $tables = array('group' => 'group_id');
	var $pkey = 'group_id';

	var $newgroup = false;

	function get($groupid, $default = null){
		$query = 'SELECT acysmsgroup.*, acysmsusers.*, joomuser.name AS group_creatorname, acysmsusers.user_email AS email
		FROM '.ACYSMS::table('group').' as acysmsgroup
		LEFT JOIN #__acysms_user as acysmsusers
		ON acysmsgroup.group_user_id = acysmsusers.user_joomid
		LEFT JOIN #__users AS joomuser
		ON acysmsusers.user_joomid = joomuser.id
		WHERE group_id = '.intval($groupid).' LIMIT 1';

		$this->database->setQuery($query);
		return $this->database->loadObject();
	}

	function saveForm(){
		$app = JFactory::getApplication();

		$group = new stdClass();
		$group->group_id = ACYSMS::getCID('group_id');

		$formData = JRequest::getVar('data', array(), '', 'array');

		foreach($formData['group'] as $column => $value){
			ACYSMS::secureField($column);
			$group->$column = strip_tags($value);
		}

		$group->group_description = JRequest::getVar('editor_description', '', '', 'string', JREQUEST_ALLOWHTML);

		$groupid = $this->save($group);
		if(!$groupid) return false;

		if(empty($group->group_ordering)){
			$orderHelper = ACYSMS::get('helper.order');
			$orderHelper->pkey = 'group_id';
			$orderHelper->table = 'group';
			$orderHelper->orderingColumnName = 'group_ordering';
			$orderHelper->reOrder();

			$this->newgroup = true;
		}

		JRequest::setVar('group_id', $groupid);

		return true;
	}

	function save($group){
		if(empty($group->group_id)){
			if(empty($group->group_user_id)){
				$user = JFactory::getUser();
				$group->group_user_id = $user->id;
			}
			if(empty($group->group_alias)) $group->group_alias = $group->group_name;
		}

		if(isset($group->group_alias)){
			if(empty($group->group_alias)) $group->group_alias = $group->group_name;
			$group->group_alias = JFilterOutput::stringURLSafe(trim($group->group_alias));
		}

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		if(empty($group->group_id)){
			$dispatcher->trigger('onAcySMSBeforeGroupCreate', array(&$group));
			$status = $this->database->insertObject(ACYSMS::table('group'), $group);
		}else{
			$dispatcher->trigger('onAcySMSBeforeGroupModify', array(&$group));
			$status = $this->database->updateObject(ACYSMS::table('group'), $group, 'group_id');
		}


		if($status) return empty($group->group_id) ? $this->database->insertid() : $group->group_id;
		return false;
	}

	function onlyCurrentLanguage($groups){
		$currentLanguage = JFactory::getLanguage();
		$currentLang = strtolower($currentLanguage->getTag());

		$newGroups = array();
		foreach($groups as $id => $oneList){
			if($oneList->languages == 'all' OR in_array($currentLang, explode(',', $oneList->languages))){
				$newGroups[$id] = $oneList;
			}
		}

		return $newGroups;
	}

	function getGroups($index = '', $groupIds = 'all'){
		$onlyGroupIds = array();
		if(strtolower($groupIds) != 'all'){
			$onlyGroupIds = explode(',', $groupIds);
			JArrayHelper::toInteger($onlyGroupIds);
		}

		$query = 'SELECT * FROM '.ACYSMS::table('group').(empty($onlyGroupIds) ? '' : ' WHERE group_id IN ('.implode(',', $onlyGroupIds).')').' ORDER BY group_ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList($index);
	}

	function getFrontendGroups($index = ''){
		$my = JFactory::getUser();
		if(empty($my->id)) return array();

		if(!ACYSMS_J16){
			$groups = array($my->gid);
		}else{
			jimport('joomla.access.access');
			$groups = JAccess::getGroupsByUser($my->id, false);
		}

		$possibleValues = array();
		$possibleValues[] = 'group_access_manage = \'all\'';
		$possibleValues[] = 'group_user_id = '.intval($my->id);
		foreach($groups as $oneGroup){
			$possibleValues[] = 'group_access_manage LIKE \'%,'.intval($oneGroup).',%\'';
		}

		$query = 'SELECT * FROM '.ACYSMS::table('group').' WHERE group_published = 1 AND ('.implode(' OR ', $possibleValues).') ORDER BY group_ordering ASC';
		$this->database->setQuery($query);
		return $this->database->loadObjectList($index);
	}

	function toggleArchiveGroup($groupIds){

		$db = JFactory::getDBO();

		JArrayHelper::toInteger($groupIds);
		$query = 'UPDATE `#__acysms_group` SET group_published = group_published * (-1) WHERE group_id IN ('.implode(',', $groupIds).')';
		$db->setQuery($query);
		$db->query();

		return $db->getAffectedRows();
	}

}
