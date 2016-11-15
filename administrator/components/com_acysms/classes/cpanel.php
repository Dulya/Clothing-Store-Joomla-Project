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

class ACYSMScpanelClass extends ACYSMSClass{

	function load(){
		$query = 'SELECT * FROM '.ACYSMS::table('config');
		$this->database->setQuery($query);
		$this->values = $this->database->loadObjectList('namekey');
	}

	function get($namekey, $default = ''){
		if(isset($this->values[$namekey])) return $this->values[$namekey]->value;
		return $default;
	}

	function save($configObject){

		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onACYSMSbeforeSaveConfig', array($configObject));

		$query = 'REPLACE INTO '.ACYSMS::table('config').' (namekey,value) VALUES ';
		$params = array();
		$i = 0;
		foreach($configObject as $namekey => $value){
			$i++;
			if($i > 100){
				$query .= implode(',', $params);
				$this->database->setQuery($query);
				if(!$this->database->query()) return false;
				$i = 0;
				$query = 'REPLACE INTO '.ACYSMS::table('config').' (namekey,value) VALUES ';
				$params = array();
			}
			if(empty($this->values[$namekey])) $this->values[$namekey] = new stdClass();
			$this->values[$namekey]->value = $value;

			if(is_array($value)) $value = implode(',', $value);
			$params[] = '('.$this->database->Quote(strip_tags($namekey)).','.$this->database->Quote(strip_tags($value, '<br />')).')';
		}

		if(empty($params)) return true;
		$query .= implode(',', $params);
		$this->database->setQuery($query);

		return $this->database->query();
	}

}
