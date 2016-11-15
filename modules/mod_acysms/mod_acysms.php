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

if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	echo 'This module can not work without the AcySMS Component';
	return;
};

if(defined('JDEBUG') AND JDEBUG) ACYSMS::displayErrors();

$moduleId = JRequest::getInt('module_id','');
$numbers = JRequest::getVar('module_'.$moduleId.'_numbers','');
$message_body = JRequest::getVar("message_body",'');

if(!empty($moduleId) && !empty($numbers)){
	include_once(dirname(__FILE__).DS.'sendmessage.php');
	$sendMessageClass = new acySMSsendmessageClass();
	$sendMessageClass->sendSMS();

}
$doc = JFactory::getDocument();
$config = ACYSMS::config();
$phoneHelper = ACYSMS::get('helper.phone');

acysms_loadMootools();
$countryType = ACYSMS::get('type.country');


$introText = $params->get('introtext');
$finaltext = $params->get('finaltext');
$sendtext = $params->get('sendtext');
$defaultMessage = $params->get('defaultmessage');
$defaultNumber = $phoneHelper->getValidNum($params->get('defaultnumber'));
$blockModification = $params->get('blockModification');
$messageMaxChar = $params->get('maxNumberMessage');



require(JModuleHelper::getLayoutPath('mod_acysms'));


