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
if(!empty($this->isAjax)){
	echo $this->conversation;
	exit;
}

if(!$this->app->isAdmin()){
	$isFront = 1;
	$ctrl = 'frontreceiver';
}else{
	$isFront = 0;
	$ctrl = 'receiver';
}
?>

<script>
	(function(){

		try{
			var progressSupport = ('onprogress' in new Browser.Request());

			Request.File = new Class({

				Extends: Request,

				options: {
					emulation: false, urlEncoded: false
				},

				initialize: function(options){
					this.xhr = new Browser.Request();
					this.formData = new FormData();
					this.setOptions(options);
					this.headers = this.options.headers;
				},

				append: function(key, value){
					this.formData.append(key, value);
					return this.formData;
				},

				reset: function(){
					this.formData = new FormData();
				},

				send: function(options){
					if(!this.check(options)) return this;

					this.options.isSuccess = this.options.isSuccess || this.isSuccess;
					this.running = true;

					var xhr = this.xhr;
					if(progressSupport){
						xhr.onloadstart = this.loadstart.bind(this);
						xhr.onprogress = this.progress.bind(this);
						xhr.upload.onprogress = this.progress.bind(this);
					}

					xhr.open('POST', this.options.url, true);
					xhr.onreadystatechange = this.onStateChange.bind(this);

					Object.each(this.headers, function(value, key){
						try{
							xhr.setRequestHeader(key, value);
						}catch(e){
							this.fireEvent('exception', [key, value]);
						}
					}, this);

					this.fireEvent('request');
					xhr.send(this.formData);

					if(!this.options.async) this.onStateChange();
					if(this.options.timeout) this.timer = this.timeout.delay(this.options.timeout, this);
					return this;
				}

			});
		}catch(err){
			console.log("You cant send media from conversation with Joomla 1.5. Please Upgrade");
		}

	})();


	var idSelected = new Array();

	function sendProcess_withImage(receiverid, senderProfile, messageBody, fileInputs){
		var request = new Request.File({
			url: 'index.php?option=com_acysms&tmpl=component&ctrl=<?php echo $ctrl; ?>&task=sendOneShotSMS&receiverIds=' + receiverid + '&isAjax=1&senderProfile_id=' + senderProfile + '&messageBody=' + messageBody, onSuccess: function(responseText, responseXML){
				document.getElementById('message_body').value = '';
				document.getElementById('acysms_errors').innerHTML = responseText;
				document.getElementById('sendOneShotSMSButton').innerHTML = '<button class="acysms_button" type="button" onclick="sendOneShotSMS();"><span class="buttonText"> <?php echo JText::_('SMS_SEND')?></span></button>';
				loadConversation();
			}
		});

		for(var i = 0; i < fileInputs.length; i++){
			if(fileInputs[i].files.length > 0){
				request.append("importfile[]", fileInputs[i].files[0]);
			}
		}

		request.send();
	}

	function sendProcess_withoutImage(receiverid, senderProfile, messageBody){
		for(var i = 0; i < fileInputs.length; i++){
			if(fileInputs[i].files.length > 0){
				alert("You cant send media from conversation with Joomla 1.5. Please Upgrade. Your media files will be ignored.");
				break;
			}
		}
		try{
			var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=<?php echo $ctrl; ?>&task=sendOneShotSMS&receiverIds=' + receiverid + '&isAjax=1&senderProfile_id=' + senderProfile + '&messageBody=' + messageBody, {
				method: 'post', onComplete: function(responseText, responseXML){
					document.getElementById('message_body').value = '';
					document.getElementById('acysms_errors').innerHTML = responseText;
					document.getElementById('sendOneShotSMSButton').innerHTML = '<button class="acysms_button" type="button" onclick="sendOneShotSMS();"><span class="buttonText"> <?php echo JText::_('SMS_SEND')?></span></button>';
					loadConversation();
				}
			}).request();

		}catch(err){
			new Request({
				url: 'index.php?option=com_acysms&tmpl=component&ctrl=<?php echo $ctrl; ?>&task=sendOneShotSMS&receiverIds=' + receiverid + '&isAjax=1&senderProfile_id=' + senderProfile + '&messageBody=' + messageBody, method: 'post', onSuccess: function(responseText, responseXML){
					document.getElementById('message_body').value = '';
					document.getElementById('acysms_errors').innerHTML = responseText;
					document.getElementById('sendOneShotSMSButton').innerHTML = '<button class="acysms_button" type="button" onclick="sendOneShotSMS();"><span class="buttonText"> <?php echo JText::_('SMS_SEND')?></span></button>';
					loadConversation();
				}
			}).send();
		}
	}

	function sendProcess(receiverid, senderProfile, messageBody, fileInputs){
		var mtVersion = MooTools.version.split('.');
		mtVersion = mtVersion[1].substr(0, 1);
		if(mtVersion < 3){
			sendProcess_withoutImage(receiverid, senderProfile, messageBody);
		}else{
			sendProcess_withImage(receiverid, senderProfile, messageBody, fileInputs);
		}
	}


	function addNewReceiver(currentValue){
		if(currentValue.length < 2) return;
		try{
			var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=<?php echo $ctrl; ?>&task=getReceiversByName&nameSearched=' + currentValue + '&isFront=<?php echo $isFront;?>', {
				method: 'get', onComplete: function(responseText, responseXML){
					document.getElementById('acysms_divSelectReceiver').style.display = "block";
					document.getElementById('acysms_receiversTable').innerHTML = responseText;
					receiversList = document.getElementById('acysms_receiversTable');
					if(receiversList.getElementsByClassName("row_user").length == 0){
						document.getElementById("acysms_divSelectReceiver").style.display = "none";
					}
				}
			}).request();

		}catch(err){
			new Request({
				url: 'index.php?option=com_acysms&tmpl=component&ctrl=<?php echo $ctrl; ?>&task=getReceiversByName&nameSearched=' + currentValue + '&isFront=<?php echo $isFront;?>', method: 'get', onSuccess: function(responseText, responseXML){
					document.getElementById('acysms_divSelectReceiver').style.display = "block";
					document.getElementById('acysms_receiversTable').innerHTML = responseText;
					receiversList = document.getElementById('acysms_receiversTable');
					if(receiversList.getElementsByClassName("row_user").length == 0){
						document.getElementById("acysms_divSelectReceiver").style.display = "none";
					}
				}
			}).send();
		}
	}

	function setUser(userName, receiverId){
		document.getElementById('usersSelected').innerHTML += '<span class="selectedUsers">' + userName + '<span class="removeUser" onclick="removeUser(this, ' + receiverId + ');"></span></span>';
		document.getElementById('message_receivers').value = '';
		document.getElementById('acysms_divSelectReceiver').style.display = "none";

		if(!idSelected){
			idSelected = new Array();
		}
		idSelected.push(parseInt(receiverId));

		loadConversation();
	}


	function removeUser(element, receiverId){
		element.parentElement.remove();
		var index = idSelected.indexOf(receiverId);
		if(index > -1){
			idSelected.splice(index, 1);
		}

		loadConversation();
	}

	function loadConversation(){
		receiverid = idSelected.join('-');
		try{
			var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=<?php echo $ctrl; ?>&task=conversation&receiverid=' + receiverid + '&tmpl=component&isAjax=1', {
				method: 'get', onComplete: function(responseText, responseXML){
					document.getElementById('sms_conversation').innerHTML = responseText;
					document.getElementById('sms_conversation').scrollTop = document.getElementById('sms_conversation').scrollHeight;
				}
			}).request();

		}catch(err){
			new Request({
				url: 'index.php?option=com_acysms&tmpl=component&ctrl=<?php echo $ctrl; ?>&task=conversation&receiverid=' + receiverid + '&tmpl=component&isAjax=1', method: 'get', onSuccess: function(responseText, responseXML){
					document.getElementById('sms_conversation').innerHTML = responseText;
					document.getElementById('sms_conversation').scrollTop = document.getElementById('sms_conversation').scrollHeight;
				}
			}).send();
		}
	}

	function reloadConversation(){
		loadConversation();
		window.setTimeout(reloadConversation, 60000);
	}

	function sendOneShotSMS(){
		receiverid = idSelected.join('-');
		senderProfile = document.getElementById('senderProfile_id').value;
		messageBody = document.getElementById('message_body').value;
		fileInputs = document.getElementsByClassName('importfile');
		document.getElementById('sendOneShotSMSButton').innerHTML = "<span id=\"ajaxSpan\" class=\"onload\"></span>";
		sendProcess(receiverid, senderProfile, messageBody, fileInputs);
	}

	window.addEvent("load", function(){
		listeningDiv = document.getElementById("message_receivers");
		listeningDiv.addEventListener("keyup", function(e){
			var previousSelected = document.getElementsByClassName("acysms_lineselected");
			var results = document.getElementsByClassName("row_user");
			var listenedKey = [38, 9, 40, 13];
			var kCode = e.keyCode ? e.keyCode : e.charCode;

			if(previousSelected.length != 0) previousSelected[0].className = previousSelected[0].className.replace(" acysms_lineselected", "");

			if(kCode == 38){
				e.preventDefault();
				if(acysms_indexSelected) acysms_indexSelected--;
			}
			if(kCode == 9 || kCode == 40){
				e.preventDefault();
				if(acysms_indexSelected < results.length - 1) acysms_indexSelected++;else acysms_indexSelected = 0;
			}
			if(kCode == 13){
				e.preventDefault();
				results[acysms_indexSelected].click();
			}

			if(listenedKey.indexOf(kCode) == -1){
				addNewReceiver(listeningDiv.value);
				acysms_indexSelected = 0;
			}

			if(results[acysms_indexSelected] != undefined)results[acysms_indexSelected].className += " acysms_lineselected";
		}, false);

		reloadConversation();
	});


</script>

<div id="acysms_errors"></div>
<div id="acysms_content">
	<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.$ctrl); ?>" method="post" name="adminForm" id="adminForm">
		<div class="acysmsblockoptions">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_CONVERSATION'); ?></span>

			<table class="acysms_blocktable" width="100%">
				<tr>
					<td class="key" id="subjectkey">
						<label for="message_subject">
							<?php echo JText::_('SMS_RECEIVERS'); ?>
						</label>
					</td>
					<td id="subjectinput">
						<div id="userSelection">
							<span id="usersSelected"></span>
							<input type="text" id="message_receivers" class="inputbox" style="width:100%" value="<?php echo $this->escape(@$this->message->message_receivers); ?>" autocomplete="off"/>

							<div id="acysms_divSelectReceiver" style="display:none; overflow-y:scroll !important;">
								<div id="acysms_receiversTable"></div>
							</div>
						</div>
					</td>
				</tr>
				<tr>
					<td class="key" id="senderkey">
						<label for="status">
							<?php echo JText::_('SMS_SENDER_PROFILE'); ?>
						</label>
					</td>
					<td>
						<?php $this->senderprofile->includeMMSJS = true;
						echo $this->senderprofile->display('senderProfile_id', @$this->message->message_senderprofile_id); ?>
					</td>
				</tr>
			</table>
		</div>
		<div style="float:left">
			<table width="100%">
				<tr>
					<td valign="top">
						<div id="sms_global">
							<?php
							$countType = ACYSMS::get('type.countcharacters');
							echo $countType->countCaracters('message_body', '');
							?>
							<div id="sms_body">
								<div id="sms_conversation" class="conversation">
									<?php echo $this->conversation; ?>
								</div>
								<div id="answerArea">
									<textarea <?php echo empty($this->messageMaxChar) ? "" : 'maxlength="'.$this->messageMaxChar.'"'; ?> onclick="countCharacters();" onkeyup="countCharacters();" rows="20" name="messageBody" id="message_body"><?php echo $this->escape(@$this->message->message_body); ?></textarea>
							<span id="sendOneShotSMSButton">
								<button class="acysms_button" type="button" onclick="sendOneShotSMS();"><span class="buttonText"> <?php echo JText::_('SMS_SEND') ?></span></button>
							</span>
									<?php $phoneType = ACYSMS::get('helper.phone');
									echo $phoneType->displayMMS($this, true); ?>
								</div>
							</div>
							<div id="sms_bottom"></div>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class="clr"></div>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<input type="hidden" name="task" value=""/>
		<?php echo JHTML::_('form.token'); ?>
		<div/>
