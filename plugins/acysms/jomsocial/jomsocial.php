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

class plgAcysmsJomSocial extends JPlugin{

	var $lastName = '';
	var $firstName = '';
	var $email = '';
	var $phoneNumber = '';


	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_community')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'jomsocial');
			$this->params = new acysmsParameter($plugin->params);
		}
	}




	function onACYSMSGetTags(&$tags){

		$tags['communityTags']['jomsocial'] = new stdClass();
		$tags['communityTags']['jomsocial']->name = JText::sprintf('SMS_X_USER_INFO', 'JomSocial');
		$db = JFactory::getDBO();
		$query = 'SELECT name, id
				FROM #__community_fields';
		$db->setQuery($query);
		$jomSocialFields = $db->loadObjectList();

		$tags['communityTags']['jomsocial']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($jomSocialFields as $oneField){
			$tags['communityTags']['jomsocial']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{jomsocial:'.$oneField->id.'}\')" class="row'.$k.'"><td>'.$oneField->name.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['communityTags']['jomsocial']->content .= '</tbody></table>';
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$match = '#(?:{|%7B)jomsocial:(.*)(?:}|%7D)#Ui';
		$helperPlugin = ACYSMS::get('helper.plugins');

		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;

		if(!isset($user->jomsocial)){
			$db = JFactory::getDBO();
			if(!empty($user->joomla->id)){
				$query = 'SELECT fieldsValues.field_id, fieldsValues.value
				FROM #__community_users AS jomSocialUsers
				JOIN #__community_fields_values AS fieldsValues
				ON fieldsValues.user_id = jomSocialUsers.userid
				WHERE user_id = '.intval($user->joomla->id);
				$db->setQuery($query);
				$information = $db->loadObjectList();


				$user->jomsocial = new stdClass();
				foreach($information as $oneFieldValue){
					$user->jomsocial->{$oneFieldValue->field_id} = $oneFieldValue->value;
				}
			}
		}
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
			$tags[$oneTag] = (isset($user->jomsocial->$field) && strlen($user->jomsocial->$field) > 0) ? $user->jomsocial->$field : $mytag->default;
			$helperPlugin->formatString($tags[$oneTag], $mytag);
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}


	public function fillViewComponent(&$view_compo){
		$option = 'com_community';
		$view = 'register';
		$view_compo[$option] = $view;
	}

	public function getNeededIntegration($integration){
		$config = ACYSMS::config();
		if($integration == 'jomsocial'){
			return $config->get('require_confirmation_jomsocial');
		}
	}

	public function displayConfirmationError($informations){
		$task = $informations['task'];
		$option = $informations['option'];
		$onRegister = JRequest::getCmd('onRegisterForm', '0');
		if($option == 'com_community' && ($task == 'registerUpdateProfile' || $task == 'registerProfile') && $onRegister == 1){
			if($this->getNeededIntegration('jomsocial')){
				echo '<script>alert("Phonenumber confirmation didn\'t process");history.back();</script>';
				exit;
			}
		}
	}


	public function displayConfirmationArea($infoUrl){
		$option = $infoUrl['option'];
		$view = $infoUrl['view'];
		$task = $infoUrl['task'];
		if($option == 'com_community' && $view == 'register' && $task == 'registerProfile'){
			$config = ACYSMS::config();
			$jomSocialPhoneField = $config->get('jomsocial_field');
			if(!$this->getNeededIntegration('jomsocial') || empty($jomSocialPhoneField)) return;

			$data = $this->_loadDBinformation();
			$newField = $this->displayPhoneField('jomsocial', $data);
			$this->_replaceConfirmButton($newField);
		}
	}

	private function _replaceConfirmButton($newField){
		$body = JResponse::getBody();
		$body = preg_replace("#(<form.*id=\"jomsForm\"|onsubmit=\"return joms_validate_form.*>.*)(<input[ a-zA-Z0-9\"\'_=-]*type=\"submit\"[ a-zA-Z0-9\"\'_=-]*>)(.*<\/form>)#sU", '$1'.'$2'.$newField.'$3', $body);
		$body = preg_replace('#(<form.*action=")(.*)(" .*;">)#sU', '$1'.'$2'.'&onRegisterForm=1'.'$3', $body);
		JResponse::setBody($body);
	}

	public function verificationCodeIntegration(&$integrationVerificationCode){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_community')) $integrationVerificationCode['jomsocial'] = true;
	}


	private function _loadDBinformation(){
		$data = array();
		$db = JFactory::getDBO();
		$db->setQuery('SELECT value FROM #__acysms_config WHERE namekey = \'jomsocial_field\'');
		$data['fieldid'] = $db->loadResult();
		if(empty($data['fieldid'])){
			$db->setQuery('SELECT id FROM #__community_fields WHERE fieldcode = \'FIELD_MOBILE\'');
			$data['fieldid'] = $db->loadResult();
		}
		$currentSession = JFactory::getSession();
		$session_id = $currentSession->get('session.token');
		$db->setQuery('SELECT name FROM #__community_register WHERE token = '.$db->quote($session_id).' ORDER BY created DESC LIMIT 1');
		$data['name'] = $db->loadResult();

		return $data;
	}

	public function displayPhoneField($integration, $extraInformations = null){
		$jtextInstruction = 'SMS_VERIFICATION_CODE_SELECT';
		$ajaxURLForCodeRequest = '"?verificationcode="+verificationCode+"&phonenumber="+phonenumber';
		$idElementCodeRequest = array('jomsForm', 'joms-page');
		$additionalTreatmentForCodeRequest = '';
		$actionToAddFormCodeRequest = '"phonenumber="+phonenumber+"&verificationcodesubmited="+verificationCode';
		$ajaxURLForSendCode = '"?sendCode=1&lastname='.$extraInformations['name'].'&phonenumber="+phonenumber';

		$phoneFieldToDisplay = '';

		$body = JResponse::getBody();
		if(!preg_match('#<input type="text".*id="field'.$extraInformations['fieldid'].'".*>#', $body)){
			ACYSMS::get('type.country');
			$countryType = new ACYSMScountryType();
			$countryType->phonewidth = 20;
			$phoneFieldToDisplay = $countryType->displayPhone('', 'phonenumber_verification');

			$additionalTreatmentForSendCode = 'phonenumber = document.getElementById("acysms_button_send").getElementsByClassName("phoneNumberField")[0].value;';
		}else{
			$additionalTreatmentForSendCode = 'phonenumber = document.getElementById("field'.$extraInformations['fieldid'].'").value;';
		}


		$script = '
		<script>
			codeRequest = function(){
				var IDElementPossibleValues = ["'.implode('","', $idElementCodeRequest).'"];
				verificationCode = document.getElementById("verification_code").value;
				if(!verificationCode){ alert("'.JText::_('SMS_PLEASE_ENTER_CODE').'"); return;}
				document.getElementById("spinner_button").innerHTML = \'<span id=\"ajaxSpan\" class=\"onload\"></span>\';
				'.$additionalTreatmentForCodeRequest.'
				try{
					new Ajax('.$ajaxURLForCodeRequest.', {
						method: "post",
						onSuccess: function(responseText, responseXML){
							response = JSON.parse(responseText);
							if(response.verify) {
								if(document.getElementById(IDElementPossibleValues[0]) != null){
									var element = document.getElementById(IDElementPossibleValues[0]);
									var signParameter = (element.action.contains("?")) ? "&" : "?";
								}else if(document.getElementsByClassName(IDElementPossibleValues[1])){
									var element = document.getElementsByClassName(IDElementPossibleValues[1])[0].getElementsByTagName("form")[0];										
									var signParameter = (element.action.contains("?")) ? "&" : "?";;
								}
								element.action+=signParameter+'.$actionToAddFormCodeRequest.';
								document.getElementById("validation_result").innerHTML = \''.str_replace("'", "\'", JText::_('SMS_VERIFICATION_CODE_SUCCESS')).'\';
								document.getElementById("validation_result").style.color="green";
								document.getElementById("acysms_phoneverification").style.display="none";
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
						onSuccess: function(responseText, responseXML){
							response = JSON.parse(responseText);
							if(response.verify) {
								if(document.getElementById(IDElementPossibleValues[0]) != null){
									var element = document.getElementById(IDElementPossibleValues[0]);
									var signParameter = (element.action.contains("?")) ? "&" : "?";
								}else if(document.getElementsByClassName(IDElementPossibleValues[1])){
									var element = document.getElementsByClassName(IDElementPossibleValues[1])[0].getElementsByTagName("form")[0];										
									var signParameter = (element.action.contains("?")) ? "&" : "?";;
								}
								element.action+=signParameter+'.$actionToAddFormCodeRequest.';
								document.getElementById("validation_result").innerHTML = \''.str_replace("'", "\'", JText::_('SMS_VERIFICATION_CODE_SUCCESS')).'\';
								document.getElementById("validation_result").style.color="green";
								document.getElementById("acysms_phoneverification").style.display="none";
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
		</script>
		<div id="acysms_button_send">
				<span style="color:#1EA0FC">'.str_replace("'", "\'", JText::_($jtextInstruction)).'</span>
				'.$phoneFieldToDisplay.'
				<div id="spinner_button"><button id="send_code" type="button" onclick="sendCode();">'.JText::_('SMS_SEND_CODE').'</button></div>
				<span style="color:red" id="sending_result"></span>
		</div>';
		return $script;
	}
}//endclass
