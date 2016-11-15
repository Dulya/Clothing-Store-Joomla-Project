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

class ACYSMSsenderprofileClass extends ACYSMSClass{
	var $tables = array('senderprofile' => 'senderprofile_id');
	var $pkey = 'senderprofile_id';
	var $namekey = 'senderprofile_name';

	function get($id){
		$column = is_numeric($id) ? 'senderprofile_id' : 'senderprofile_name';
		$this->database->setQuery('SELECT * FROM #__acysms_senderprofile WHERE '.$column.' = '.$this->database->Quote(trim($id)).' LIMIT 1');
		$object = $this->database->loadObject();

		if(empty($object)) return false;

		$object->senderprofile_params = unserialize($object->senderprofile_params);
		return $object;
	}

	function getDefaultSenderProfileId(){
		$this->database->setQuery('SELECT senderprofile_id FROM #__acysms_senderprofile WHERE senderprofile_default = 1 LIMIT 1');
		$senderProfileId = $this->database->loadResult();
		return $senderProfileId;
	}

	function getGateway($gateway, $params = array()){

		if(empty($gateway)){
			$gateway = $this->getDefaultSenderProfileId();
			if(empty($gateway)) return false;
		}

		if(is_numeric($gateway)){
			$object = $this->get($gateway);
			if(!$object){
				return false;
			}
			$gateway = $object->senderprofile_gateway;
			$params = $object->senderprofile_params;
		}

		$file = ACYSMS_GATEWAY.$gateway.DS.'gateway.php';
		$className = 'ACYSMSGateway_'.$gateway.'_gateway';
		if(!include_once($file)){
			ACYSMS::display('Could not load the gateway : '.$file, 'error');
			return false;
		}
		$oneGateway = new $className();

		if(!empty($object)){
			foreach($object as $fieldName => $value){
				$oneGateway->$fieldName = $value;
			}
		}

		if(!empty($params)){
			foreach($params as $fieldName => $value){
				$oneGateway->$fieldName = $value;
			}
		}
		return $oneGateway;
	}


	function saveForm(){
		$formData = JRequest::getVar('data', array(), '', 'array');
		$senderprofile = new stdClass();
		$senderprofile->senderprofile_id = ACYSMS::getCID('senderprofile_id');

		if(isset($formData['senderprofile']['senderprofile_params'])){
			$senderprofile->senderprofile_params = serialize($formData['senderprofile']['senderprofile_params']);
			unset($formData['senderprofile']['senderprofile_params']);
		}


		foreach($formData['senderprofile'] as $column => $value){
			ACYSMS::secureField($column);
			$senderprofile->$column = strip_tags($value);
		}
		$senderprofile_id = $this->save($senderprofile);

		if(!$senderprofile_id) return false;
		JRequest::setVar('senderprofile_id', $senderprofile_id);


		return true;
	}


	function save($senderprofile){

		if(empty($senderprofile->senderprofile_id)){
			if(empty($senderprofile->userid)){
				$user = JFactory::getUser();
				$senderprofile->senderprofile_userid = $user->id;
			}
		}

		if(!empty($senderprofile->senderprofile_gateway)){
			if(isset($senderprofile->senderprofile_params) && is_string($senderprofile->senderprofile_params)){
				$senderprofile->senderprofile_params = unserialize($senderprofile->senderprofile_params);
			}
			if(empty($senderprofile->senderprofile_params)) $senderprofile->senderprofile_params = array();
			$gateway = $this->getGateway($senderprofile->senderprofile_gateway, $senderprofile->senderprofile_params);
			$gateway->beforeSaveConfig($senderprofile);
		}

		if(!empty($senderprofile->senderprofile_params) && !is_string($senderprofile->senderprofile_params)){
			$senderprofile->senderprofile_params = serialize($senderprofile->senderprofile_params);
		}

		if(empty($senderprofile->senderprofile_id)){
			$status = $this->database->insertObject(ACYSMS::table('senderprofile'), $senderprofile);
		}else{
			$status = $this->database->updateObject(ACYSMS::table('senderprofile'), $senderprofile, 'senderprofile_id');
		}
		if(!$status) return false;

		if(empty($senderprofile->senderprofile_id)) $senderprofile->senderprofile_id = $this->database->insertid();

		if(!empty($gateway)){
			if(!empty($senderprofile->senderprofile_params)) $senderprofile->senderprofile_params = unserialize($senderprofile->senderprofile_params);
			$gateway->afterSaveConfig($senderprofile);
		}

		return $senderprofile->senderprofile_id;
	}
}
