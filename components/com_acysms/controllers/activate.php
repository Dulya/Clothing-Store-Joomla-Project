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

class ActivateController extends acysmsController{

	var $errors = array();

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('activate');
	}

	function activate(){
		JRequest::setVar('layout', 'activate');
		return parent::display();
	}

	function saveActivation(){

		$phoneNumber = JRequest::getVar("phoneNumber", array(), '', 'array');
		$phoneNumber = trim(strip_tags($phoneNumber['phone_country'].$phoneNumber['phone_num']));

		$activationCode = JRequest::getString("activationCode", '');
		$moduleId = JRequest::getInt("moduleId", '');
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		if(empty($moduleId)) return;
		$db->setQuery('SELECT params FROM #__modules WHERE id = '.intval($moduleId).' AND `module` LIKE \'%acysms%\' LIMIT 1');
		$dbParams = $db->loadResult();

		$moduleParameter = new acysmsParameter($dbParams);
		$redirectLink = $moduleParameter->get('redirectlink');

		$phoneHelper = ACYSMS::get('helper.phone');
		$result = $phoneHelper->verifyActivation($phoneNumber, $activationCode, 'activation_optin');


		if($result){

			$validPhone = $phoneHelper->getValidNum($phoneNumber);
			$userClass = ACYSMS::get('class.user');
			$user = $userClass->getByPhone($validPhone);

			$query = 'UPDATE #__acysms_groupuser SET groupuser_status = 1 WHERE groupuser_status = 2 && groupuser_user_id = '.intval($user->user_id);
			$db->setQuery($query);
			$db->query();

			$phoneClass = ACYSMS::get('class.phone');
			$phoneClass->unblock(array($validPhone));

			ACYSMS::enqueueMessage(JText::_('SMS_SUCCESSFULLY_ACTIVATED'), 'success');
			if($redirectLink) $app->redirect($redirectLink);
			$app->redirect(ACYSMS_LIVE);
			exit;
		}
		ACYSMS::enqueueMessage($phoneHelper->error, 'error');
		JRequest::setVar('layout', 'activate');
		return parent::display();
	}
}
