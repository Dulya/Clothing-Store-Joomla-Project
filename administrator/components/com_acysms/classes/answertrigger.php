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

class ACYSMSanswerTriggerClass extends ACYSMSClass{

	var $tables = array('answertrigger' => 'answertrigger_id');
	var $pkey = 'answertrigger_id';


	function get($id, $default = null){

		$column = is_numeric($id) ? 'answertrigger_id' : 'answertrigger_name';
		$this->database->setQuery('SELECT * FROM #__acysms_answertrigger WHERE '.$column.' = '.$this->database->Quote(trim($id)).' LIMIT 1');
		$answerTrigger = $this->database->loadObject();

		if(!empty($answerTrigger->answertrigger_actions)) $answerTrigger->answertrigger_actions = unserialize($answerTrigger->answertrigger_actions);
		if(!empty($answerTrigger->answertrigger_triggers)) $answerTrigger->answertrigger_triggers = unserialize($answerTrigger->answertrigger_triggers);

		return $answerTrigger;
	}


	function saveForm(){
		$app = JFactory::getApplication();
		$answerTrigger = new stdClass();

		$answerTrigger->answertrigger_id = ACYSMS::getCID('answertrigger_id');
		$formData = JRequest::getVar('data', array(), '', 'array');

		if(!empty($formData['answertrigger']['answertrigger_triggers']) && is_array($formData['answertrigger']['answertrigger_triggers'])) $formData['answertrigger']['answertrigger_triggers'] = serialize($formData['answertrigger']['answertrigger_triggers']);
		if(!empty($formData['answertrigger']['answertrigger_actions']) && is_array($formData['answertrigger']['answertrigger_actions'])) $formData['answertrigger']['answertrigger_actions'] = serialize($formData['answertrigger']['answertrigger_actions']);


		foreach($formData['answertrigger'] as $column => $value){
			if($app->isAdmin() OR in_array($column, $this->allowedFields)){
				if($column == 'params'){
					$answerTrigger->$column = $value;
				}
				else{
					$answerTrigger->$column = strip_tags($value);
				}
			}
		}

		if(empty($answerTrigger->ordering)){
			$helperClass = ACYSMS::get('helper.order');
			$helperClass->pkey = 'answertrigger_id';
			$helperClass->table = 'answertrigger';
			$helperClass->orderingColumnName = 'answertrigger_ordering';
			$helperClass->reOrder();
		}

		$answerTriggerid = $this->save($answerTrigger);
		if(!$answerTriggerid) return false;
		JRequest::setVar('answertrigger_id', $answerTriggerid);
		return true;
	}
}
