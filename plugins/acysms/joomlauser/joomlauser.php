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

class plgAcysmsJoomlauser extends JPlugin{
	var $sendervalues = array();

	var $lastName = '';
	var $firstName = '';
	var $email = '';
	var $phoneNumber = '';

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'joomlauser');
			$this->params = new JParameter($plugin->params);
		}
	}

	function onACYSMSGetTags(&$tags){
		$tags['communityTags']['joomlauser'] = new stdClass();
		$tags['communityTags']['joomlauser']->name = JText::sprintf('SMS_X_USER_INFO', 'Joomla');

		$tableFields = acysms_getColumns('#__users');
		$tags['communityTags']['joomlauser']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['communityTags']['joomlauser']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{joomlauser:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['communityTags']['joomlauser']->content .= '</tbody></table>';
	}

	function onACYSMSReplaceUserTags(&$message, &$juser, $send = true){

		$db = JFactory::getDBO();

		$query = 'SELECT queue_paramqueue FROM '.ACYSMS::table('queue').' WHERE queue_message_id = '.intval($message->message_id);
		$db->setQuery($query);
		$paramQueue = $db->loadResult();
		if(!empty($paramQueue)) $paramQueue = unserialize($paramQueue);

		$match = '#(?:{|%7B)joomlauser:(.*)(?:}|%7D)#Ui';
		$variables = array('message_body');
		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;
		$tags = array();
		foreach($results[0] as $i => $oneTag){
			if(isset($tags[$oneTag])) continue;
			$arguments = explode('|', strip_tags($results[1][$i]));
			$field = $arguments[0];
			unset($arguments[0]);
			$mytag = new stdClass();
			$mytag->default = '';
			if(!empty($arguments)){
				foreach($arguments as $onearg){
					$args = explode(':', $onearg);
					if(isset($args[1])){
						$mytag->$args[0] = $args[1];
					}else{
						$mytag->$args[0] = 1;
					}
				}
			}
			if($field == 'password' && !empty($paramQueue->password)){
				$tags[$oneTag] = base64_decode($paramQueue->password);
			}else $tags[$oneTag] = (isset($juser->joomla->$field) && strlen($juser->joomla->$field) > 0) ? $juser->joomla->$field : $mytag->default;
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}


	public function fillViewComponent(&$view_compo){
		$firstOption = 'com_user';
		$secondOption = 'com_users';
		$firstView = 'register';
		$secondView = 'registration';
		$view_compo[$firstOption] = $firstView;
		$view_compo[$secondOption] = $secondView;
	}

	public function getNeededIntegration($integration){
		$config = ACYSMS::config();
		if($integration == 'joomlasub'){
			return $config->get('require_confirmation_joomla_subscription');
		}
	}

	public function displayConfirmationError($informations){
		$task = $informations['task'];
		$option = $informations['option'];
		if(($task == 'register_save' && $option == 'com_user') || ($task == 'registration.register' && $option == 'com_users')){
			if($this->getNeededIntegration('joomlasub')){
				echo '<script>alert("Phonenumber confirmation didn t process");history.back();</script>';
				exit;
			}
		}
	}

	public function displayConfirmationArea($infoUrl){
		$option = $infoUrl['option'];
		$view = $infoUrl['view'];

		$config = ACYSMS::config();
		$joomlaPhoneField = $config->get('joomlausers_field');
		if(!$this->getNeededIntegration('joomlasub') || empty($joomlaPhoneField)) return;

		if($option == 'com_users' && $view == 'registration'){
			$newField = $this->displayPhoneField('joomlasub');
			$this->_replaceConfirmButtonJoomlaSub($newField);
		}else if($option == 'com_user' && $view == 'register'){
			$newField = $this->displayPhoneField('joomlasub15');
			$this->_replaceConfirmButtonJoomlaSub15($newField);
		}
	}


	private function _replaceConfirmButtonJoomlaSub($newField){
		$body = JResponse::getBody();
		$body = preg_replace("#(<form.*id=\"member-registration\".*>.*)<button.*type=\"submit\".*>.*<\/button>(.*<\/form>)#sU", '$1'.$newField.'$2', $body);
		JResponse::setBody($body);
	}

	private function _replaceConfirmButtonJoomlaSub15($newField){
		$body = JResponse::getBody();
		$body = preg_replace("#(<form.*id=\"josForm	\".*>.*)<button.*type=\"submit\".*>.*<\/button>(.*<\/form>)#sU", '$1'.$newField.'$2', $body);
		JResponse::setBody($body);
	}

	public function displayPhoneField($integration, $extraInformations = null){
		$ajaxURLForCodeRequest = '';
		$idElementCodeRequest = '';
		$phoneFieldToDisplay = '';
		$additionalTreatmentForCodeRequest = '';
		$actionToAddFormCodeRequest = '';
		$ajaxURLForSendCode = '';
		$additionalTreatmentForSendCode = '';
		$jtextInstruction = 'SMS_VERIFICATION_CODE_SELECT';

		$config = ACYSMS::config();

		if($integration == 'joomlasub'){
			$jtextInstruction = 'SMS_VERIFICATION_CODE_CONFIRM';
			$ajaxURLForCodeRequest = '"?verificationcode="+verificationCode+"&phonenumber="+phonenumber';
			$ajaxURLForSendCode = '"?sendCode=1&lastname="+name+"&email="+email+"&phonenumber="+phonenumber';
			$additionalTreatmentForCodeRequest = 'phonenumber = document.getElementById("field'.$extraInformations['fieldid'].'").value;';
			$additionalTreatmentForCodeRequest = 'if(document.getElementById("jform_profile_phone") == undefined) phonenumber = document.getElementById("sms_sent_to").value; else phonenumber = document.getElementById("jform_profile_phone").value;';
			$actionToAddFormCodeRequest = '"phonenumber="+phonenumber+"&verificationcodesubmited="+verificationCode';
			$idElementCodeRequest = 'member-registration';
			$additionalTreatmentForSendCode = '
					form = document.getElementById("member-registration");
					if(!document.formvalidator.isValid(form)) return;
					if(document.getElementById("jform_profile_phone") == undefined){
								if(document.getElementById("sms_sent_to")) phonenumber = document.getElementById("sms_sent_to").value; 
							else 
								phonenumber = document.getElementsByName("phonenumber_verification[phone_country]")[0].value+document.getElementsByName("phonenumber_verification[phone_num]")[0].value;
					}else
							phonenumber = document.getElementById("jform_profile_phone").value;
					name = document.getElementById("jform_name").value;
					email = document.getElementById("jform_email1").value;
			';

			$body = JResponse::getBody();
			if(!preg_match("#<input type=\"tel|text\".*id=\"jform_profile_phone\".*>#", $body)){
				$countryType = ACYSMS::get('type.country');
				$countryType = new ACYSMScountryType();
				$countryType->phonewidth = 20;
				$phoneFieldToDisplay = $countryType->displayPhone('', 'phonenumber_verification');
			}
		}else if($integration == 'joomlasub15'){
			$jtextInstruction = 'SMS_VERIFICATION_CODE_CONFIRM';
			$ajaxURLForCodeRequest = '';
			$idElementCodeRequest = 'josForm';
			$phoneFieldToDisplay = '';
			$actionToAddFormCodeRequest = '"phonenumber="+phonenumber+"&verificationcodesubmited="+verificationCode';
			$additionalTreatmentForCodeRequest = 'phonenumber = document.getElementById("sms_sent_to").value;';
			$ajaxURLForSendCode = '"?sendCode=1&lastname="+name+"&phonenumber="+phonenumber';
			$additionalTreatmentForSendCode = 'form = document.getElementById("josForm");
				if(!document.formvalidator.isValid(form)) return;
				if(document.getElementById("sms_sent_to")) phonenumber = document.getElementById("sms_sent_to").value; 
				else
					phonenumber = document.getElementsByName("phonenumber_verification[phone_country]")[0].value+document.getElementsByName("phonenumber_verification[phone_num]")[0].value;
				name = document.getElementById("name").value;';
			$countryType = ACYSMS::get('type.country');
			$countryType = new ACYSMScountryType();
			$countryType->phonewidth = 20;
			$phoneFieldToDisplay = $countryType->displayPhone('', 'phonenumber_verification');
		}else return;

		$script = '';

		$script .= '
		<script>

			codeRequest = function(){
				verificationCode = document.getElementById("verification_code").value;
				if(!verificationCode){ alert("'.JText::_('SMS_PLEASE_ENTER_CODE').'"); return;}
				document.getElementById("spinner_button").innerHTML = \'<span id=\"ajaxSpan\" class=\"onload\"></span>\';
				'.$additionalTreatmentForCodeRequest.'
				try{
					new Ajax('.$ajaxURLForCodeRequest.', {
						method: "post",
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.verify) {
									signParameter = (document.getElementById("'.$idElementCodeRequest.'").action.contains("?")) ? "&" : "?";
									document.getElementById("'.$idElementCodeRequest.'").action+=signParameter+'.$actionToAddFormCodeRequest.';
									document.getElementById("'.$idElementCodeRequest.'").submit();
							}else{
								document.getElementById("spinner_button").innerHTML = \'<button type="button" onclick="codeRequest();">'.JText::_('SMS_VERIFY_CODE').'</button>\';
								document.getElementById("validation_result").innerHTML = response.errorMessage;
								document.getElementById("validation_result").style.color="red";
							}
					}
					}).request();
				}catch(err){
					new Request({
						method: "post",
						url: '.$ajaxURLForCodeRequest.',
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.verify){
								signParameter = (document.getElementById("'.$idElementCodeRequest.'").action.contains("?")) ? "&" : "?";
								document.getElementById("'.$idElementCodeRequest.'").action+=signParameter+'.$actionToAddFormCodeRequest.';
								document.getElementById("'.$idElementCodeRequest.'").submit();
							}else{
								document.getElementById("spinner_button").innerHTML = \'<button type="button" onclick="codeRequest();">'.JText::_('SMS_VERIFY_CODE').'</button>\';
								document.getElementById("validation_result").innerHTML = response.errorMessage;
								document.getElementById("validation_result").style.color="red";
							}
						}
					}).send();
				}
			};
			sendCode = function(){
				'.$additionalTreatmentForSendCode.'
				document.getElementById("spinner_button").innerHTML = "<span id=\"ajaxSpan\" class=\"onload\"></span>";
				try{
					new Ajax('.$ajaxURLForSendCode.', {
						method: "post",
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.sendingResult)
								document.getElementById("acysms_button_send").innerHTML = response.display;
							else {
								document.getElementById("spinner_button").innerHTML = 	\'<button id="send_code" type="button" onclick="sendCode();">'.str_replace("'", "\'", JText::_('SMS_SEND_CODE')).'</button>\';
								document.getElementById("sending_result").innerHTML = response.display;
							}
						}
					}).request();
				}catch(err){
					new Request({
						method: "post",
						url: '.$ajaxURLForSendCode.',
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.sendingResult)
								document.getElementById("acysms_button_send").innerHTML = response.display;
							else {
								document.getElementById("spinner_button").innerHTML = 	\'<button id="send_code" type="button" onclick="sendCode();">'.str_replace("'", "\'", JText::_('SMS_SEND_CODE')).'</button>\';
								document.getElementById("sending_result").innerHTML = response.display;
							}
						}
					}).send();
				}
			};

			var divVerifNumber = document.getElementsByClassName("input-append")[0];
			divVerifNumber.style.fontSize = "inherit";
			divVerifNumber.style.whiteSpace = "normal";
		</script>
		<div style="font-size:inherit; white-space:normal;" id="acysms_button_send">
				<span style="color:#1EA0FC">'.str_replace("'", "\'", JText::_($jtextInstruction)).'</span>
				'.$phoneFieldToDisplay.'
				<div id="spinner_button"><button id="send_code" type="button" onclick="sendCode();">'.JText::_('SMS_SEND_CODE').'</button></div>
				<span style="color:red" id="sending_result"></span>
		</div>';
		return $script;
	}
}//endclass
