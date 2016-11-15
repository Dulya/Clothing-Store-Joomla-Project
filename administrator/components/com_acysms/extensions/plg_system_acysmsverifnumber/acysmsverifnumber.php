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

class plgSystemAcySMSVerifNumber extends JPlugin{
	var $option = '';
	var $view = '';
	var $phoneNumber = '';
	var $firstName = '';
	var $lastName = '';
	var $email = '';


	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) return false;
		parent::__construct($subject, $config);
		if(!$this->init()) return;
		JPluginHelper::importPlugin('acysms');
	}

	private function init(){
		if(defined('ACYSMS_COMPONENT')) return true;
		$acySmsHelper = rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper)){
			include_once $acySmsHelper;
		}else return false;
		return defined('ACYSMS_COMPONENT');
	}

	private function verifyFileIncluded($requestParams){
		$dispatcher = JDispatcher::getInstance();
		$view_component = array();
		$dispatcher->trigger('fillViewComponent', array(&$view_component));


		if(isset($view_component[$requestParams['option']]) && ($view_component[$requestParams['option']] == $requestParams['task'] || $view_component[$requestParams['option']] == $requestParams['view'])){
			$doc = JFactory::getDocument();
			$doc->addStyleSheet(ACYSMS_CSS.'component.css');
		}else return;
	}

	function onAfterRoute(){
		if(!$this->init()) return;

		acysms_loadMootools();
		$this->phoneNumber = JRequest::getCmd('phonenumber', '');
		$verificationCodeSubmited = JRequest::getCmd('verificationcodesubmited', '');
		$task = JRequest::getCmd('task', '');
		$option = JRequest::getCmd('option', '');
		$view = JRequest::getCmd('view', '');
		$ctrl = JRequest::getCmd('ctrl', '');

		$requestParams = array();
		$requestParams['task'] = $task;
		$requestParams['view'] = $view;
		$requestParams['option'] = $option;

		$this->verifyFileIncluded($requestParams);


		if(!empty($verificationCodeSubmited)){
			$result = $this->_verifyCode($verificationCodeSubmited, true, true);
			$result = json_decode($result);
			if($result->verify === false) echo "<script>alert('".$result->errorMessage."');history.back();</script>";
		}else{
			$dispatcher = JDispatcher::getInstance();
			$informations = array();
			$informations['option'] = $option;
			$informations['task'] = $task;
			$informations['view'] = $view;
			$informations['ctrl'] = $ctrl;
			$dispatcher->trigger('displayConfirmationError', array($informations));
		}
	}

	function onAfterRender(){
		acysms_loadMootools();

		$app = JFactory::getApplication();
		if($app->isAdmin()) return;

		$option = JRequest::getCmd('option', '');
		$view = JRequest::getCmd('view', '');
		$task = JRequest::getCmd('task', '');

		$verificationCode = JRequest::getCmd('verificationcode', '');
		$sendCode = JRequest::getCmd('sendCode', '0');
		$this->lastName = JRequest::getString('lastname', '');
		$this->firstName = JRequest::getString('firstname', '');
		$this->email = JRequest::getString('email', '');

		if(!empty($verificationCode)){
			$integration = JRequest::getCmd('integration', '');
			$dispatcher = JDispatcher::getInstance();
			$delete = $dispatcher->trigger('deleteTheCode', array($integration));
			$deleteCodeInDB = $delete ? true : false;
			$this->_verifyCode($verificationCode, $deleteCodeInDB);
			return;
		}

		if($sendCode == 1){
			$this->_sendCode();
		}

		if($this->_checkIfUserLogged()) return;
		if(empty($option) || empty($view)) return;
		$infoUrl = array();
		$infoUrl['option'] = $option;
		$infoUrl['view'] = $view;
		$infoUrl['task'] = $task;
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('displayConfirmationArea', array($infoUrl));
	}

	public function onACYSMSgetVerificationCodeIntegrations(&$integrationVerificationCode){

		$integrationVerificationCode['joomla_subscription'] = true;
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('verificationCodeIntegration', array(&$integrationVerificationCode));
	}


	public function isIntegrationNeeded($integration){
		$dispatcher = JDispatcher::getInstance();
		return $dispatcher->trigger('getNeededIntegration', array($integration));
	}

	private function _sendCode(){
		$config = ACYSMS::config();

		$std_result = new stdClass();
		$std_result->sendingResult = false;
		$std_result->display = '';

		if(empty($this->phoneNumber)){
			$std_result->display = JText::_('SMS_NO_PHONE');
			echo json_encode($std_result);
			exit;
		}
		$phoneHelper = ACYSMS::get('helper.phone');
		$validPhoneNumber = $phoneHelper->getValidNum($this->phoneNumber);

		if($validPhoneNumber == false){
			$std_result->display = $phoneHelper->error;
			echo json_encode($std_result);
			exit;
		}else{
			$this->phoneNumber = $validPhoneNumber;
		}

		if($config->get('uniquePhoneSubscription')){
			$userClass = ACYSMS::get('class.user');
			$user = $userClass->getByPhone($validPhoneNumber);
			if(isset($user->user_activationcode)){
				if(is_string($user->user_activationcode)) $user->user_activationcode = unserialize($user->user_activationcode);

				if(isset($user->user_activationcode['activation_optin']) && empty($user->user_activationcode['activation_optin'])){
					$std_result->display = JText::_('SMS_PHONE_ALREADY_USED_FOR_ACTIVATION');
					echo json_encode($std_result);
					exit;
				}
			}
		}

		if(empty($this->firstName)) $this->firstName = " "; //if we have only one field who contains name

		$classUser = ACYSMS::get('class.user');
		$user = $classUser->getByPhone($validPhoneNumber);

		if(empty($user)) $this->_createAcySMSUser($this->phoneNumber, $this->lastName, $this->firstName, $this->email);

		$std_result->sendingResult = $phoneHelper->sendVerificationCode($this->phoneNumber);
		if(!$std_result->sendingResult){
			$std_result->display = $phoneHelper->error.' '.JText::_('SMS_CONTACT_ADMIN');
			echo json_encode($std_result);
			exit;
		}else{

			$std_result->sendingResult = true;
			$std_result->display = '<div id="acysms_phoneverification">
					<span style="color:#1EA0FC">'.JText::_('SMS_VERIFICATION_CODE_ENTER').'</span><br />
					<input type="hidden" id="sms_sent_to" value="'.$this->phoneNumber.'">
					<label for="verification_code">'.JText::_('SMS_VERIFICATION_CODE').'</label>
					<input type="text" name="verification_code" id="verification_code">
					<br />
					<a onclick="sendCode();">'.JText::_('SMS_SEND_AGAIN_CODE').'</a>
					<div id="spinner_button">
						<button type="button" onclick="codeRequest();">'.JText::_('SMS_VERIFY_CODE').'</button>
					</div>
			</div>
			<span id="validation_result"></span>';
			echo json_encode($std_result);
			exit;
		}
	}

	private function _verifyCode($verificationCode, $deleteCodeInDB = false, $afterUserSubmited = false){
		$std_result = new stdClass();
		$std_result->verify = false;
		$std_result->errorMessage = '';

		$phoneHelper = ACYSMS::get('helper.phone');
		$this->phoneNumber = $phoneHelper->getValidNum($this->phoneNumber);
		if($this->phoneNumber == false){
			$std_result->errorMessage = $phoneHelper->error;
			$string_result = json_encode($std_result); //we encode in json to access the result in js later
			if($afterUserSubmited) return $string_result;
			echo $string_result;
			exit;
		}

		$result = $phoneHelper->verifyActivation($this->phoneNumber, $verificationCode, 'activation_optin', $deleteCodeInDB);
		$std_result->verify = $result;
		$std_result->errorMessage = $phoneHelper->error;
		$string_result = json_encode($std_result); //we encode in json to access the result in js later
		if($afterUserSubmited) return $string_result;
		echo $string_result;
		exit;
	}


	private function _createAcySMSUser($phoneNumber, $lastName = '', $firstName = '', $email = ''){
		$userClass = ACYSMS::get('class.user');
		$user = new stdClass();
		$user->user_firstname = $firstName;
		$user->user_lastname = $lastName;
		$user->user_phone_number = $phoneNumber;
		$user->user_email = $email;
		$userClass->save($user);
	}

	private function _checkIfUserLogged(){
		$my = JFactory::getUser();
		if(empty($my->id)) return false;
		return true;
	}

}//endclass
