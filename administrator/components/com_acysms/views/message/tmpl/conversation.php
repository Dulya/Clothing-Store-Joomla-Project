<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysms_content" >
<script>
var idSelected = new Array();

function addNewReceiver(currentValue){
	try{
		var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=message&task=getReceiversByName&nameSearched='+currentValue,{
			method: 'get',
			onSuccess: function() {
				receiversList = document.getElementById('acysms_receiversTable');
				if(receiversList.getElementsByClassName("row_user").length==0)
					document.getElementById("acysms_divSelectReceiver").style.display= "none";
				}
		}).request();

	}catch(err){
		new Request({
			url:'index.php?option=com_acysms&tmpl=component&ctrl=message&task=getReceiversByName&nameSearched='+currentValue,
			method: 'get',
			onSuccess: function(responseText, responseXML) {
				receiversList = document.getElementById('acysms_receiversTable');
				document.getElementById('acysms_receiversTable').innerHTML = responseText;
				document.getElementById('acysms_divSelectReceiver').style.display = "block";
				if(receiversList.getElementsByClassName("row_user").length==0)
					document.getElementById("acysms_divSelectReceiver").style.display= "none";
			}
		}).send();
	}
}

function setUser(userName, receiverId){
	document.getElementById('usersSelected').innerHTML += '<span class="selectedUsers">'+userName+'<span class="removeUser" onclick="removeUser(this, '+receiverId+');"></span></span>';
	document.getElementById('message_receivers').value = '';
	document.getElementById('acysms_divSelectReceiver').style.display = "none";

	idSelected.push(parseInt(receiverId));

	loadConversation();
}


function removeUser(element, receiverId){
	element.parentElement.remove();
	var index = idSelected.indexOf(receiverId);
	if (index > -1) {
		idSelected.splice(index, 1);
	}

	loadConversation();
}

function loadConversation(){
	receiverid = idSelected.join('-');
	try{
		var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=conversation&receiverid='+receiverid+'&tmpl=component',{
			method: 'get',
			update: document.getElementById('sms_body')
		}).request();

	}catch(err){
		new Request({
			url:'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=conversation&receiverid='+receiverid+'&tmpl=component',
			method: 'get',
			onSuccess: function(responseText, responseXML) {
				document.getElementById('sms_body').innerHTML = responseText;
			}
		}).send();
	}

}

</script>
<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm"  id="adminForm" >
	<table class="adminform" width="100%">
		<tr>
			<td class="key" id="subjectkey">
				<label for="message_subject">
					<?php echo JText::_( 'SMS_RECEIVERS' ); ?>
				</label>
			</td>
			<td id="subjectinput">
			<div id="userSelection">
				<span id="usersSelected"></span>
				<input type="text" name="data[message][message_receivers]" id="message_receivers" onkeyup="addNewReceiver(this.value)" class="inputbox" style="width:100%" value="<?php echo $this->escape(@$this->message->message_receivers); ?>" />
				<div id="acysms_divSelectReceiver" style="display:none;">
					<div id="acysms_receiversTable"></div>
				</div>
			</div>
			</td>
		</tr>
	</table>
	<table width="100%">
		<tr>
			<td valign="top">
				<div id="sms_global">
					<?php
						$countType = ACYSMS::get('type.countcharacters');
						echo $countType->countCaracters('message_body','');
					?>
					<div id="sms_body">
						<textarea <?php echo empty($this->messageMaxChar) ? "" : 'maxlength="'.$this->messageMaxChar.'"'; ?>" onclick="countCharacters();" onkeyup="countCharacters();" rows="20" name="data[message][message_body]" id="message_body" ><?php echo @$this->message->message_body; ?></textarea>
					</div>
					<div id="sms_bottom">
					</div>
				</div>
			</td>
		</tr>
		</table>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
</form>
</div>
