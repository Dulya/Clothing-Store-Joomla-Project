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

class ACYSMSGroupCreatorType{
	function __construct(){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		if($app->isAdmin()){
			$query = 'SELECT COUNT(*) as total, group_user_id FROM #__acysms_group WHERE `group_user_id` > 0 GROUP BY group_user_id';
		}else{

			$my = JFactory::getUser();
			if(!ACYSMS_J16){
				$groups = $my->gid;
				$condGroup = ' OR group_access_manage LIKE (\'%,'.$groups.',%\')';
			}else{
				jimport('joomla.access.access');
				$groups = JAccess::getGroupsByUser($my->id, false);
				$condGroup = '';
				foreach($groups as $group){
					$condGroup .= ' OR group_access_manage LIKE (\'%,'.$group.',%\')';
				}
			}
			$condGroup = ' OR (group_access_manage = \'all\''.$condGroup.')';


			$query = 'SELECT COUNT(*) as total, group_user_id FROM #__acysms_group';
			$query .= ' WHERE (`group_user_id` = '.intval($my->id).$condGroup.')';
			$query .= ' GROUP BY group_user_id';
		}
		$db->setQuery($query);
		$allusers = $db->loadObjectList('group_user_id');


		$allnames = array();
		if(!empty($allusers)){

			$arrayKeys = array_keys($allusers);
			JArrayHelper::toInteger($arrayKeys);

			$db->setQuery('SELECT name, id FROM #__users WHERE id IN ('.implode(',', $arrayKeys).') ORDER BY name ASC');
			$allnames = $db->loadObjectList('id');
		}
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_CREATORS'));
		foreach($allnames as $userid => $oneCreator){
			$this->values[] = JHTML::_('select.option', $userid, $oneCreator->name.' ( '.$allusers[$userid]->total.' )');
		}
	}

	function display($map, $value){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" style="width:100px;max-width:200px" size="1" onchange="document.adminForm.submit();"', 'value', 'text', (int)$value);
	}
}
