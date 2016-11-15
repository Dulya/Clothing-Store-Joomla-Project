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

class acysmsfrontHelper{

}

function acysmsCheckAccessGroup(){
	$groupid = JRequest::getInt('group_id');
	if(empty($groupid)) return false;
	$my = JFactory::getUser();
	$groupClass = ACYSMS::get('class.group');
	$myGroup = $groupClass->get($groupid);
	if(empty($myGroup->group_id)) die('Invalid Group');
	if(!empty($my->id) AND (int) $my->id == (int) $myGroup->group_user_id) return true;
	if(empty($my->id) OR $myGroup->group_access_manage == 'none') return false;
	if($myGroup->group_access_manage != 'all'){
		if(!ACYSMS::isAllowed($myGroup->group_access_manage)) return false;
	}
	return true;
}

function acysmsCheckEditUser(){
	$groupid = JRequest::getInt('group_id');
	$user_id = ACYSMS::getCID('user_id');

	if(empty($user_id)) return true;

	$db = JFactory::getDBO();
	$db->setQuery('SELECT groupuser_status FROM #__acysms_groupuser WHERE groupuser_user_id ='.intval($user_id).' AND groupuser_group_id = '.intval($groupid));
	$status = $db->loadResult();
	if(empty($status)) return false;

	return true;
}


function checkGroupId(){
	$app = JFactory::getApplication();

	$group_id = JRequest::getInt('group_id');
	if(empty($group_id)){
		$group_id = JRequest::getInt('filter_group');
	}
	if(empty($group_id)){
		$group_id = $app->getUserState("com_acysms.frontmessagefilter_group");
	}
	if(empty($group_id)){
		$listClass = ACYSMS::get('class.group');
		$allAllowedGroups = $listClass->getFrontendGroups();
		if(!empty($allAllowedGroups)){
			$firstGroup = reset($allAllowedGroups);
			$group_id = $firstGroup->group_id;
			JRequest::setVar('group_id', $group_id);
		}
	}
	return $group_id;
}
