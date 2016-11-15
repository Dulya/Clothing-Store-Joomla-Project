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

class ACYSMStestReceiverType{
	private function _getAttributesHTML($attributes){
		$attributesHTML = '';

		foreach($attributes as $key => $attribute){
			$attributesHTML .= $key.'="'.trim($attribute).'" ';
		}

		return $attributesHTML;
	}

	private function _addMultipleReceiverJS($isFront, $receiverInput, $listReceiversTest){
		$js = "
		window.addEventListener('load', function() {";

		if($isFront){
			$js .= "	
			var countryCodeInput = document.querySelector('.phoneNumberField[name=\"testReceiverInput[phone_num]\"]');

			countryCodeInput.addEventListener('keypress', function(event) {
				var countryCode = document.querySelector('input[name=\"testReceiverInput[phone_country]\"]');
				if(countryCode.value != '' && this.value != '') acysmsValidateTestReceiver(event, this, countryCode.value + this.value);
			});

			countryCodeInput.addEventListener('blur', function(event) {
				var countryCode = document.querySelector('input[name=\"testReceiverInput[phone_country]\"]');
				if(countryCode.value != '' && this.value != '') addReceiver(countryCode.value + this.value, this);
			});
			";
		}

		if(!empty($listReceiversTest)){
			$js .= "
			var input = document.getElementsByClassName('test-receiver-input')[0];";
			foreach($listReceiversTest as $aReceiver){
				$js .= "
				addReceiver('".$aReceiver[1]."',input,'".$aReceiver[0]."');";
			}
		}

		$js .= "
			manageKeyEvent = function(event, el, value, integration) {
				var keycode = event.which || event.keyCode;
				if(keycode == 38 || keycode == 40 || keycode == 13){
					selectUser(keycode,event,el,value);
				}else searchUser(el,integration);

			}


			function selectUser(keycode, event, el, value){
				var containerReceiver = el.parentNode.getElementsByClassName('test-receiver-proposal')[0];
				var phoneActive = containerReceiver.getElementsByClassName('active')[0];
				if(keycode == 38 || keycode == 40) {
					var newActive;
					if(!phoneActive) {
						phoneActive = containerReceiver.firstChild;
						phoneActive.classList.add('active');
						return true;
					}

					phoneActive.classList.remove('active');

					if(keycode == 40) newActive = phoneActive.nextSibling;
					else newActive = phoneActive.previousSibling;

					if(!newActive) newActive = phoneActive;

					newActive.classList.add('active');
				} else if(keycode == 13) {
					containerReceiver.innerHTML = '';
					if(phoneActive) addReceiver(phoneActive.getElementsByClassName('proposal-phone')[0].innerHTML, el,phoneActive.getElementsByClassName('proposal-name')[0].innerHTML);
					else addReceiver(value, el,'');
				}
			}
			";

		if(!$isFront){
			$js .= "


			function searchUser(el, integration){

				var userSearched = el.value;
				if(userSearched.length > 2) {
					var currentValue = userSearched;
					var elementToAppend = el.parentNode.getElementsByClassName('test-receiver-proposal')[0];
					var receiverLists = document.getElementsByClassName('test-receiver-list');
					var receiver = receiverLists[0].getElementsByClassName('test-receiver-one');
					var arrayReceiver = [];
					for(var j = 0;j < receiver.length;j++)
					{
						if(receiver[j] != 'undefined')
						{
							var number = receiver[j].getElementsByClassName('valueUser')[0].innerText;
							arrayReceiver.push(number);
						}
					}
					try{
						var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=getReceiversPhone&integration='+integration+'&value='+currentValue,
						{
							method: 'get',
							onSuccess: function(responseText, responseXML) {
								var results = JSON.parse(responseText);
								elementToAppend.innerHTML = '';
								for(var i = 0; i < results.length; i++) {
									if(arrayReceiver.indexOf(results[i].phone) == -1)
									{
										elementToAppend.style.display = 'block';
										var newElement = document.createElement('li');
										newElement.innerHTML = '<span class=\'proposal-name\'>'+results[i].name+'</span><span class=\'proposal-phone\'>'+results[i].phone+'</span>';
										newElement.addEventListener('click', function() {
											addReceiver(this.getElementsByClassName('proposal-phone')[0].innerHTML, el, this.getElementsByClassName('proposal-name')[0].innerHTML);
											newElement.parentNode.style.display = 'none';
										});
										elementToAppend.appendChild(newElement);
									}
								}
							}
						}).request();
					}catch(err){
						new Request({
							url:'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=getReceiversPhone&integration='+integration+'&value='+currentValue,
							method: 'get',
							onSuccess: function(responseText, responseXML) {
								var results = JSON.parse(responseText);
								elementToAppend.innerHTML = '';
								for(var i = 0; i < results.length; i++) {
									if(arrayReceiver.indexOf(results[i].phone) == -1)
									{
										elementToAppend.style.display = 'block';
										var newElement = document.createElement('li');
										newElement.innerHTML = '<span class=\'proposal-name\'>'+results[i].name+'</span><span class=\'proposal-phone\'>'+results[i].phone+'</span>';
										newElement.addEventListener('click', function() {
											addReceiver(this.getElementsByClassName('proposal-phone')[0].innerHTML, el,this.getElementsByClassName('proposal-name')[0].innerHTML);
											newElement.parentNode.style.display = 'none';
										});
										elementToAppend.appendChild(newElement);
									}
								}
							}
						}).send();
					}
				}
			}";
		}

		$js .= "

			disableEnterKey = function(event) {
				var keycode = event.keyCode;
				if(keycode == 13) {
					event.preventDefault();
				}
			}

			addReceiverInput = function(){
				var cont = document.getElementsByClassName('test-receiver-container'); 
				var input = cont[0].getElementsByTagName('input');
				addReceiver(input[0].value, input[0],'')
			}




			function addReceiver(valueReceiver, input, name) {
				if(typeOf(name) == undefined || !name) name = ' ';
				var containerReceiver = input.parentNode;
				while(containerReceiver.className != 'test-receiver-container') {
					containerReceiver = containerReceiver.parentNode;
				}
				input = containerReceiver.getElementsByClassName('test-receiver-input')[0];
				if(input.value) input.value = '';	

				if(!valueReceiver || valueReceiver.replace(' ', '').length < 4) return false;

				var receiverLists = containerReceiver.getElementsByClassName('test-receiver-list')[0];
				var newUser = document.createElement('span');
				newUser.className = 'test-receiver-one';
				newUser.innerHTML = '<span class=\"nameUser\">' + name + ' </span><span class=\"valueUser\">' + valueReceiver + '</span><span class=\"removeUser\"></span>';
				receiverLists.appendChild(newUser);

				newUser.addEventListener('click', function() { removeReceiver(newUser) });
		";

		if(!empty($receiverInput)){
			$js .= "
				var receivers = document.getElementById('".$receiverInput."').value.split(',');
				if(receivers[0] == '') receivers = [];
				receivers.push(valueReceiver);
				document.getElementById('".$receiverInput."').value = receivers.join(',');
			}

			removeReceiver = function(el) {
				el.parentNode.removeChild(el);
				var receivers = document.getElementById('".$receiverInput."').value.split(',');
				for(var i = 0; i < receivers.length; i++) {
					if(receivers[i] == el.getElementsByClassName('valueUser')[0].innerHTML) {
						receivers.splice(i, 1);
						break;
					}
				}
				document.getElementById('".$receiverInput."').value = receivers.join(',');
			}
		});
		";
		}else{
			$js .= "
			}
			removeReceiver = function(el) {
				el.parentNode.removeChild(el);
			}
		});";
		}

		echo '<script type="text/javascript">'.$js.'</script>';
	}


	public function display($isFront = false, $integration, $listReceiversTest){

		$attributes = array();
		$className = 'test-receiver-input';
		$attributes['class'] = $className;

		$attributesHTML = $this->_getAttributesHTML($attributes);

		$this->_addMultipleReceiverJS($isFront, 'testNumberReceiver', $listReceiversTest);

		echo '<div class="test-receiver-container">';
		if(!$isFront){
			echo '<input onkeydown="disableEnterKey(event)" onkeyup="manageKeyEvent(event, this, this.value,\''.$integration.'\')" type="text" '.$attributesHTML.' placeholder="'.JText::_('SMS_SENDTEST_DESCRIPTION').'"></input>';
			echo '<ul style="display:none" class="test-receiver-proposal"></ul>';
		}else{
			$countryType = ACYSMS::get('type.country');
			echo $countryType->displayPhone('', 'testReceiverInput');
		}
		echo '<div class="test-receiver-list"></div>';
		echo '</div>';
	}

}
