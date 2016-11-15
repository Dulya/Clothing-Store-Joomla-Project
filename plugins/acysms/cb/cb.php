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

class plgAcysmsCb extends JPlugin{

	var $sendervalues = array();

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_comprofiler')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'cb');
			$this->params = new acysmsParameter($plugin->params);
		}
	}



	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();

		if(!$app->isAdmin()){
			$helperPlugin = ACYSMS::get('helper.plugins');
			if(!$helperPlugin->allowSendByGroups('cb')) return;
		}

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'Community Builder');
		$filters['communityFilters']['cb'] = $newFilter;
	}


	function onACYSMSDisplayFilterParams_cb($message){
		$db = JFactory::getDBO();
		$fields = acysms_getColumns('#__comprofiler');
		if(empty($fields)) return;

		$field = array();
		$field[] = JHTML::_('select.option', '', ' - - - ');
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option', $oneField, $oneField);
		}

		$relation = array();
		$relation[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');

		?>
		<span id="countresult_cb"></span>
		<?php
		for($i = 0; $i < 5; $i++){
			$operators->extra = 'onchange="countresults(\'cb\')"';
			$return = '<div id="filter'.$i.'cbfield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][cb][cbfield][".$i."][map]", 'onchange="countresults(\'cb\')" class="inputbox" size="1"', 'value', 'text');
			$return .= ' '.$operators->display("data[message][message_receiver][standard][cb][cbfield][".$i."][operator]").' <input onchange="countresults(\'cb\')" class="inputbox" type="text" name="data[message][message_receiver][standard][cb][cbfield]['.$i.'][value]" style="width:200px" value=""></div>';
			if($i != 4) $return .= JHTML::_('select.genericlist', $relation, "data[message][message_receiver][standard][cb][cbfield][".$i."][relation]", 'onchange="countresults(\'cb\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
			echo $return;
		}
	}

	function onACYSMSSelectData_cb(&$acyquery, $message){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['cb']['cbfield'])) return;
		if(!isset($acyquery->join['comprofiler'])) $acyquery->join['comprofiler'] = 'LEFT JOIN #__comprofiler as comprofiler ON joomusers.id = comprofiler.id ';
		$addCondition = '';
		$whereConditions = '';

		foreach($message->message_receiver['standard']['cb']['cbfield'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			if(!empty($addCondition)) $whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])){
				$addCondition = $oneFilter['relation'];
			}else  $addCondition = 'AND';
			$whereConditions .= $acyquery->convertQuery('comprofiler', $oneFilter['map'], $oneFilter['operator'], $oneFilter['value']);
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}






	function onACYSMSGetTags(&$tags){

		$tags['communityTags']['cbuser'] = new stdClass();
		$tags['communityTags']['cbuser']->name = JText::sprintf('SMS_X_USER_INFO', 'Community Builder');
		$db = JFactory::getDBO();
		$tableFields = acysms_getColumns('#__comprofiler');

		$tags['communityTags']['cbuser']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['communityTags']['cbuser']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{cbuser:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['communityTags']['cbuser']->content .= '</tbody></table>';
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$match = '#(?:{|%7B)cbuser:(.*)(?:}|%7D)#Ui';
		$variables = array('message_body');
		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;

		if(!isset($user->cbuser)){
			$db = JFactory::getDBO();
			if(!empty($user->joomla->id)){
				$db->setQuery('SELECT * FROM #__comprofiler WHERE user_id = '.intval($user->joomla->id).' LIMIT 1');
				$user->cbuser = $db->loadObject();
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
			$tags[$oneTag] = (isset($user->cbuser->$field) && strlen($user->cbuser->$field) > 0) ? $user->cbuser->$field : $mytag->default;
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}


	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'Community Builder');
		$authorizedFilters['cb'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_cb(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}


	public function verificationCodeIntegration(&$integrationVerificationCode){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_comprofiler')) $integrationVerificationCode['cb'] = true;
	}

	public function fillViewComponent(&$view_compo){
		$option = 'com_comprofiler';
		$view = 'registers';
		$view_compo[$option] = $view;
	}

	public function getNeededIntegration($integration){
		$config = ACYSMS::config();
		if($integration == 'cb') return $config->get('require_confirmation_cb');
	}

	public function displayConfirmationError($informations){
		$view = $informations['view'];
		$option = $informations['option'];
		if(($view == 'saveregisters' && $option == 'com_comprofiler')){
			if($this->getNeededIntegration('cb')){
				echo '<script>alert("Phonenumber confirmation didn\'t process");history.back();</script>';
				exit;
			}
		}
	}

	public function displayConfirmationArea($infoUrl){
		$option = $infoUrl['option'];
		$task = $infoUrl['task'];
		if($option == 'com_comprofiler' && $task == 'registers'){

			$config = ACYSMS::config();
			$cbPhoneField = $config->get('communitybuilder_field');
			if(!$this->getNeededIntegration('cb') || empty($cbPhoneField)) return;

			$newField = $this->displayPhoneField('cb');
			$this->_replaceConfirmButtonCB($newField);
		}
	}

	private function _replaceConfirmButtonCB($newField){
		$body = JResponse::getBody();
		$body = preg_replace("#(<form.*id=\"cbcheckedadminForm\".*>.*)<input type=\"submit\".*>.*(.*<\/form>)#sU", '$1'.$newField.'$3', $body);
		JResponse::setBody($body);
	}

	public function displayPhoneField($integration, $extraInformations = null){
		$config = ACYSMS::config();
		$phoneField = $config->get('communitybuilder_field');

		$jtextInstruction = 'SMS_VERIFICATION_CODE_CONFIRM';
		$ajaxURLForCodeRequest = '"?integration=cb&verificationcode="+verificationCode+"&phonenumber="+phonenumber';
		$ajaxURLForSendCode = '"?sendCode=1&lastname="+name+"&email="+email+"&phonenumber="+phonenumber';
		$idElementCodeRequest = 'cbcheckedadminForm';
		$actionToAddFormCodeRequest = '"phonenumber="+phonenumber+"&verificationcodesubmited="+verificationCode';

		$additionalTreatmentForCodeRequest = '';
		$phoneFieldToDisplay = '';

		$body = JResponse::getBody();
		if(!preg_match('#<input type="text".*id="'.$phoneField.'".*>#', $body)){
			ACYSMS::get('type.country');
			$countryType = new ACYSMScountryType();
			$countryType->phonewidth = 20;
			$phoneFieldToDisplay = $countryType->displayPhone('', 'phonenumber_verification');
		}

		$additionalTreatmentForSendCode = '							
					if(document.getElementById("'.$phoneField.'") == undefined)
							phonenumber = document.getElementsByName("phonenumber_verification[phone_country]")[0].value+document.getElementsByName("phonenumber_verification[phone_num]")[0].value;
					else
							phonenumber = document.getElementById("'.$phoneField.'").value;
					name = document.getElementById("name").value;
					email = document.getElementById("email").value;
			';

		$script = '';

		$script .= '
			<script>
			var element = document.getElementById("'.$idElementCodeRequest.'");
			element.addEventListener("submit", function(event) {
			  event.preventDefault();
			});


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
							if(response.verify) {
								signParameter = (document.getElementById("'.$idElementCodeRequest.'").action.contains("?")) ? "&" : "?";
								document.getElementById("'.$idElementCodeRequest.'").action+=signParameter+'.$actionToAddFormCodeRequest.';
								document.getElementById("'.$idElementCodeRequest.'").submit();
							}else {
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

				document.getElementById("spinner_button").innerHTML = " < span id = \"ajaxSpan\" class=\"onload\"></span>";
				try{
					new Ajax('.$ajaxURLForSendCode.', {
						method:
						"post",
						onSuccess: function (responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.sendingResult){
								document.getElementById("acysms_button_send").innerHTML = response.display;
							}else{
								document.getElementById("spinner_button").innerHTML = \'<button id="send_code" type="button" onclick="sendCode();">'.str_replace("'", "\'", JText::_('SMS_SEND_CODE')).'</button>\';
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
				<div id="spinner_button"><button id="send_code" type="button" onclick="sendCode();">Send a Code</button></div>
				<span style="color:red" id="sending_result"></span>
			</div>';
		return $script;
	}
}//endclass
