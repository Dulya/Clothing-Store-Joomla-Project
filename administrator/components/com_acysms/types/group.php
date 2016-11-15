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

class ACYSMSgroupType{

	var $addNoGroupOption = true;
	var $js = '';
	var $groupsInMessageOption = false;

	function load(){
		$db = JFactory::getDBO();

		$query = 'SELECT *,group_id, group_name FROM '.ACYSMS::table('group').' ORDER BY group_ordering ASC';
		$db->setQuery($query);
		$this->values = $db->loadObjectList('group_id');

		if($this->addNoGroupOption){
			$newElement = new stdClass();
			$newElement->group_id = 0;
			$newElement->group_name = JText::_('SMS_NO_GROUP');
			array_unshift($this->values, $newElement);
		}
	}

	function display($map, $value){
		$this->load();
		if($this->groupsInMessageOption == true) $this->values[0]->group_name = JText::_('SMS_GROUP_IN_MESSAGE');
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" style="width:auto;min-width:120px;max-width:200px" size="1" '.$this->js, 'group_id', 'group_name', (int)$value);
	}

	function getData(){
		$this->addNoGroupOption = false;
		$this->load();
		return $this->values;
	}

}
