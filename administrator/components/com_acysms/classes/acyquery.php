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

class ACYSMSacyqueryClass extends ACYSMSClass{
	var $join = array();
	var $where = array();
	var $group = array();
	var $from = '';

	function count($integration){
		$this->db = JFactory::getDBO();
		$integration = ACYSMS::getIntegration($integration);
		$myquery = $this->getQuery(array('COUNT(DISTINCT '.$integration->tableAlias.'.'.$integration->primaryField.')'));
		$this->db->setQuery($myquery);
		return $this->db->loadResult();
	}

	function getQuery($select = array()){
		$query = '';
		if(!empty($select)) $query .= ' SELECT '.implode(',', $select);
		if(!empty($this->from)) $query .= ' FROM '.$this->from;
		if(!empty($this->join)) $query .= ' '.implode(' ', $this->join);
		if(!empty($this->where)) $query .= ' WHERE ('.implode(') AND (', $this->where).')';
		if(!empty($this->group)) $query .= ' GROUP BY ('.implode(') , (', $this->group).')';

		return $query;
	}

	function convertQuery($as, $column, $operator, $value, $type = ''){
		$this->db = JFactory::getDBO();

		$operator = str_replace(array('&lt;', '&gt;'), array('<', '>'), $operator);

		if($operator == 'CONTAINS'){
			$operator = 'LIKE';
			$value = '%'.$value.'%';
		}elseif($operator == 'BEGINS'){
			$operator = 'LIKE';
			$value = $value.'%';
		}elseif($operator == 'END'){
			$operator = 'LIKE';
			$value = '%'.$value;
		}elseif($operator == 'NOTCONTAINS'){
			$operator = 'NOT LIKE';
			$value = '%'.$value.'%';
		}elseif(!in_array($operator, array('REGEXP', 'NOT REGEXP', 'IS NULL', 'IS NOT NULL', 'NOT LIKE', 'LIKE', '=', '!=', '>', '<', '>=', '<='))){
			die('Operator not safe : '.$operator);
		}

		if(strpos($value, '{time}') !== false){
			$value = ACYSMS::replaceDate($value);
			$value = strftime('%Y-%m-%d %H:%M:%S', $value);
		}

		$replace = array('{year}', '{month}', '{day}');
		$replaceBy = array(date('Y'), date('m'), date('d'));
		$value = str_replace($replace, $replaceBy, $value);

		if(!is_numeric($value) OR in_array($operator, array('REGEXP', 'NOT REGEXP', 'NOT LIKE', 'LIKE', '=', '!='))){
			$value = $this->db->Quote($value);
		}

		if(in_array($operator, array('IS NULL', 'IS NOT NULL'))){
			$value = '';
		}

		if($type == 'datetime' && in_array($operator, array('=', '!='))){
			return 'DATE_FORMAT('.$as.'.`'.ACYSMS::secureField($column).'`, "%Y-%m-%d") '.$operator.' '.'DATE_FORMAT('.$value.', "%Y-%m-%d")';
		}
		if($type == 'timestamp' && in_array($operator, array('=', '!='))){
			return 'FROM_UNIXTIME('.$as.'.`'.ACYSMS::secureField($column).'`, "%Y-%m-%d") '.$operator.' '.'FROM_UNIXTIME('.$value.', "%Y-%m-%d")';
		}
		return $as.'.`'.ACYSMS::secureField($column).'` '.$operator.' '.$value;
	}

	public function addMessageFilters($message){
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();

		if(!empty($message->message_receiver['standard']['type'])){
			foreach($message->message_receiver['standard']['type'] as $oneType){
				$dispatcher->trigger('onACYSMSSelectData_'.$oneType, array(&$this, $message));
			}
		}
	}

	public function addUserFilters($users = array(), $integrationFrom, $integrationTo){
		$config = ACYSMS::config();

		if(!empty($integrationTo) && $integrationTo == $integrationFrom){
			$integration = ACYSMS::getIntegration($integrationTo);
		}else{
			$integration = ACYSMS::getIntegration($integrationFrom);
			$joomIDs = $integration->getJoomUserId($users);

			$integration = ACYSMS::getIntegration($integrationTo);
			$users = $integration->getReceiverIDs($joomIDs);
			if(empty($users)) return;
		}

		$receiverField = $config->get($integration->componentName.'_field');
		if(empty($receiverField)) return;
		JArrayHelper::toInteger($users);
		$this->where[] = $integration->tableAlias.'.'.$integration->primaryField.' IN ('.implode(',', $users).')';
	}
}
