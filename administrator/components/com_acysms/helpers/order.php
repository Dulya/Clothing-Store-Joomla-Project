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

class ACYSMSorderHelper{
	var $table = '';
	var $pkey = '';
	var $groupMap = '';
	var $groupVal = '';
	var $orderingColumnName = '';

	function order($down = true){
		$orderingColumnName = $this->orderingColumnName;
		if($down){
			$sign = '>';
			$dir = 'ASC';
		}else{
			$sign = '<';
			$dir = 'DESC';
		}
		$ids = JRequest::getVar('cid', array(), '', 'array');
		$id = (int)$ids[0];
		$pkey = $this->pkey;
		$db = JFactory::getDBO();
		$query = 'SELECT a.'.$orderingColumnName.',a.'.$pkey.' FROM '.ACYSMS::table($this->table).' as b, '.ACYSMS::table($this->table).' as a';
		$query .= ' WHERE a.'.$orderingColumnName.' '.$sign.' b.'.$orderingColumnName.' AND b.'.$pkey.' = '.intval($id);
		if(!empty($this->groupMap)) $query .= ' AND a.'.$this->groupMap.' = '.$db->Quote($this->groupVal);
		$query .= ' ORDER BY a.'.$orderingColumnName.' '.$dir.' LIMIT 1';


		$db->setQuery($query);
		$secondElement = $db->loadObject();
		if(empty($secondElement)) return false;
		$firstElement = new stdClass();
		$firstElement->$pkey = $id;
		$firstElement->$orderingColumnName = $secondElement->$orderingColumnName;
		if($down){
			$secondElement->$orderingColumnName--;
		}else $secondElement->$orderingColumnName++;
		$status1 = $db->updateObject(ACYSMS::table($this->table), $firstElement, $pkey);
		$status2 = $db->updateObject(ACYSMS::table($this->table), $secondElement, $pkey);
		$status = $status1 && $status2;
		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_MOVED'), 'message');
		}
		return $status;
	}

	function save(){
		$orderingColumnName = $this->orderingColumnName;
		$pkey = $this->pkey;
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$order = JRequest::getVar('order', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		$db = JFactory::getDBO();
		if(!empty($cid)) $whereClause = ' WHERE `'.$pkey.'` NOT IN ('.implode(',', $cid).') ';
		$query = 'SELECT `'.$orderingColumnName.'`,`'.$pkey.'` FROM '.ACYSMS::table($this->table).$whereClause;
		if(!empty($this->groupMap)) $query .= ' AND '.$this->groupMap.' = '.$db->Quote($this->groupVal);
		$query .= ' ORDER BY `'.$orderingColumnName.'` ASC';
		$db->setQuery($query);
		$results = $db->loadObjectList($pkey);
		$oldResults = $results;
		asort($order);
		$newOrder = array();
		while(!empty($order) OR !empty($results)){
			$dbElement = reset($results);
			if(empty($dbElement->$orderingColumnName) OR (!empty($order) AND reset($order) <= $dbElement->$orderingColumnName)){
				$newOrder[] = $cid[(int)key($order)];
				unset($order[key($order)]);
			}else{
				$newOrder[] = $dbElement->$pkey;
				unset($results[$dbElement->$pkey]);
			}
		}
		$i = 1;
		$status = true;
		$element = new stdClass();
		foreach($newOrder as $val){
			$element->$pkey = $val;
			$element->$orderingColumnName = $i;
			if(!isset($oldResults[$val]) OR $oldResults[$val]->$orderingColumnName != $i){
				$status = $db->updateObject(ACYSMS::table($this->table), $element, $pkey) && $status;
			}
			$i++;
		}
		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_NEW_ORDERING_SAVED'), 'message');
		}else{
			ACYSMS::enqueueMessage(JText::_('SMS_ERROR_ORDERING'), 'error');
		}
		return $status;
	}

	function reOrder(){
		$orderingColumnName = $this->orderingColumnName;
		$db = JFactory::getDBO();
		$query = 'UPDATE '.ACYSMS::table($this->table).' SET `'.$orderingColumnName.'` = `'.$orderingColumnName.'`+1';
		if(!empty($this->groupMap)) $query .= ' WHERE '.$this->groupMap.' = '.$db->Quote($this->groupVal);
		$db->setQuery($query);
		$db->query();
		$query = 'SELECT `'.$orderingColumnName.'`,`'.$this->pkey.'` FROM '.ACYSMS::table($this->table);
		if(!empty($this->groupMap)) $query .= ' WHERE '.$this->groupMap.' = '.$db->Quote($this->groupVal);
		$query .= ' ORDER BY `'.$orderingColumnName.'` ASC';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		$i = 1;
		foreach($results as $oneResult){
			if($oneResult->$orderingColumnName != $i){
				$oneResult->$orderingColumnName = $i;
				$db->updateObject(ACYSMS::table($this->table), $oneResult, $this->pkey);
			}
			$i++;
		}
	}
}
