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

class DeliveryReportController extends acysmsController{

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('deliveryReport');
	}

	function deliveryReport(){
		$config = ACYSMS::config();

		$gatewayName = JRequest::getCmd("gateway", JRequest::getCmd('g'));
		if(empty($gatewayName)) die('No gateway specified');

		$pass = JRequest::getCmd("pass", JRequest::getCmd("p", ''));
		if(empty($pass) || $pass != $config->get('pass')) die('Pass not valid');


		$gatewayClass = ACYSMS::get('class.senderprofile');
		$gateway = $gatewayClass->getGateway($gatewayName);
		if(empty($gateway)) die("Can't load gateway");

		$apiAnswer = $gateway->deliveryReport();

		$statsClass = ACYSMS::get('class.stats');
		$statsClass->addDeliveryInformations($apiAnswer);

		$gateway->closeRequest();

		exit;
	}
}
