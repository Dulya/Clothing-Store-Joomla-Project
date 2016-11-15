<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="acysms_module<?php echo $params->get('moduleclass_sfx') ?>" id="acysms_module_<?php echo $module->id; ?>">
	<script langage="javascript">
		function addPhoneNumber(moduleID){

			moduleDiv = document.getElementById("module_" + moduleID + "_numbers");
			totalDiv = document.getElementsByClassName("acysms_buttonCountryCode").length;
			var newElement = document.createElement('div');
			newElement.className = "acysms_phonenumber_div";
			var firstPhoneField = moduleDiv.getElementsByClassName("acysms_buttonCountryCode")[0].value;

			newElement.innerHTML = document.getElementById("module_" + moduleID + "_phone").innerHTML.replace(/numbers\[0\]/g, "numbers[" + totalDiv + "]");
			totalDiv++;
			newElement.innerHTML = newElement.innerHTML.replace(new RegExp("acysms_displayDivCountryCode\\(" + firstPhoneField, "g"), "acysms_displayDivCountryCode(" + totalDiv);
			newElement.innerHTML = newElement.innerHTML.replace(new RegExp("acysms_valueSelectedCountryCode" + firstPhoneField, "g"), "acysms_valueSelectedCountryCode" + totalDiv);
			newElement.innerHTML = newElement.innerHTML.replace(new RegExp("acysms_buttonCountryCodeImage" + firstPhoneField, "g"), "acysms_buttonCountryCodeImage" + totalDiv);
			newElement.innerHTML = newElement.innerHTML.replace(new RegExp("acysms_divCountryCode" + firstPhoneField, "g"), "acysms_divCountryCode" + totalDiv);
			newElement.innerHTML = newElement.innerHTML.replace(new RegExp("acysms_searchACountry" + firstPhoneField, "g"), "acysms_searchACountry" + totalDiv);
			newElement.innerHTML = newElement.innerHTML.replace(new RegExp("acysms_searchACountry\\(" + firstPhoneField, "g"), "acysms_searchACountry(" + totalDiv);


			document.getElementById("module_" + moduleID + "_numbers").appendChild(newElement);

			acysms_searchACountry(totalDiv);
		}
	</script>
	<form action="<?php echo JURI::getInstance(); ?>" method="post" name="sendsms">
		<span class="acysms_introtext">
			<?php if(!empty($introText)) echo ($introText).'<br />'; ?>
		</span>
		<?php
		echo '<div class="acysms_numbers" id="module_'.$module->id.'_numbers">';
		echo '<div class="acysms_phonenumber_div" id="module_'.$module->id.'_phone">';
		if($blockModification) $countryType->readOnly = true;
		$countryType->placeholder = JText::_('SMS_PHONECAPTION');
		echo $countryType->displayPhone((!empty($defaultNumber) ? $defaultNumber : $config->get('country')), 'module_'.$module->id.'_numbers[0]'); ?>
</div>
</div>
<?php

if(!$blockModification) echo '<button style="margin:5px 0 10px 0" type="button" onclick="addPhoneNumber('.$module->id.');">'.JText::_('SMS_ADD_PHONE_NUMBER').'</button>';

?>
<div class="acysms_messageBody">
	<textarea rows="5" <?php echo empty($messageMaxChar) ? "" : 'maxlength="'.$messageMaxChar.'"'; ?> id="message_body<?php echo $module->id; ?>" name="message_body" class="message_body" style="width:90%; max-width:400px" onclick="countCharacters(<?php echo $module->id; ?>);" onkeyup="countCharacters(<?php echo $module->id; ?>);" <?php echo ($blockModification) ? 'readonly' : "" ?> ><?php echo(!empty($defaultMessage) ? $defaultMessage : JText::_('SMS_TEST_MESSAGE')); ?></textarea>
</div>
<div class="acysms_countCharacters" style="text-align:right;width:90%; max-width:400px">
	<?php
	$countType = ACYSMS::get('type.countcharacters');
	echo $countType->countCaracters('message_body', $module->id);
	?>
</div>
<span class="acysms_finaltext">
			<?php if(!empty($finaltext)) echo ($finaltext).'<br />'; ?>
		</span>
<input type="hidden" name="module_id" value="<?php echo $module->id; ?>"/>
<div class="sendMessageDiv">
	<input class="acysms_button" type="submit" value="<?php echo empty($sendtext) ? JText::_('SMS_SEND') : ($sendtext); ?>"/>
</div>
<?php echo JHTML::_('form.token'); ?>
</form>
</div>
