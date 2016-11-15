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

class actionsController extends acysmsController{

	var $gateway;

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('stop');

		$this->_checkPass();
		$this->_loadGateway();
	}

	function stop(){

		$phoneNumber = $this->gateway->getNumber();
		if(empty($phoneNumber)) return;

		$phoneClass = ACYSMS::get('class.phone');
		$phoneClass->manageStatus($phoneNumber, 0);

		exit;
	}

	private function _checkPass(){
		$config = ACYSMS::config();
		$pass = JRequest::getCmd("pass", JRequest::getCmd("p", ''));
		if(empty($pass) || $pass != $config->get('pass')) die('Pass not valid');
	}

	private function _loadGateway(){
		$gatewayName = JRequest::getCmd("gateway", '');
		if(empty($gatewayName)) die('No gateway specified');

		$gatewayClass = ACYSMS::get('class.senderprofile');
		$gateway = $gatewayClass->getGateway($gatewayName);

		if(empty($gateway)) die("Can't load gateway");

		$this->gateway = $gateway;
	}
}
