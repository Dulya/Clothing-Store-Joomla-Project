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

class ACYSMSphoneHelper{

	public $error = '';
	public $errorCode = '';
	public $configPage = false;


	public function getValidNum($phoneNum){
		$this->error = '';

		if(empty($phoneNum)){
			$this->error = JText::_('SMS_NO_PHONE');
			return false;
		}

		$phoneNum = preg_replace(array('#[^0-9+]#i', '#^00#i'), array('', '+'), $phoneNum);

		if(strpos($phoneNum, '+') !== 0){
			$config = ACYSMS::config();
			$defaultCountry = $config->get('country');
			if(strpos($defaultCountry, '+') === 0 && $this->getValidNum($defaultCountry.$phoneNum)){
				$phoneNum = $defaultCountry.$phoneNum;
			}elseif($this->getValidNum('+'.$phoneNum)){
				$phoneNum = '+'.$phoneNum;
			}else{
				$phoneNum = $defaultCountry.$phoneNum;
			}
		}


		$countrycode = trim($this->getCountryCode($phoneNum));
		if(!$countrycode){
			$this->error = JText::_('SMS_NO_COUNTRYCODE_FOUND');
			return false;
		}


		if(!in_array($countrycode, array(225))) $phoneNum = str_replace('+'.$countrycode.'0', '+'.$countrycode, $phoneNum);

		$regex = array();
		$regex[1] = '#^\+1[0-9]{10}$#';
		$regex[20] = '#^\+201[0-9]{9}$#';
		$regex[225] = '#^\+225[0-9]{8}$#';
		$regex[27] = '#^\+27[0-9]{9}$#';
		$regex[30] = '#^\+3069[0-9]{8}$#';
		$regex[31] = '#^\+31[1-9][0-9]{8}$#';
		$regex[32] = '#^\+32[1-9][0-9]{7,8}$#';
		$regex[33] = '#^\+33[1-9][0-9]{8}$#';
		$regex[357] = '#^\+357[0-9]{8}$#';
		$regex[39] = '#^\+39[0-9]{9,10}$#';
		$regex[40] = '#^\+407[0-9]{8}$#';
		$regex[41] = '#^\+41[1-9][0-9]{8}$#';
		$regex[44] = '#^\+44[0-9]{9,10}$#';
		$regex[45] = '#^\+45[1-9][0-9]{7}$#';
		$regex[47] = '#^\+47[0-9]{8}$#';
		$regex[48] = '#^\+48[1-9][0-9]{8}$#';
		$regex[49] = '#^\+49[0-9]{7,12}$#';
		$regex[503] = '#^\+503[0-9]{8}$#';
		$regex[56] = '#^\+56[0-9]{9}$#';
		$regex[57] = '#^\+57[0-9]{10}$#';
		$regex[58] = '#^\+58[0-9]{10}$#';
		$regex[593] = '#^\+593[0-9]{9}$#';
		$regex[61] = '#^\+614[0-9]{8}$#';
		$regex[7] = '#^\+7[0-9]{10}$#';
		$regex[91] = '#^\+91[0-9]{10}$#';
		$regex[94] = '#^\+94[0-9]{9}$#';
		$regex[98] = '#^\+98[0-9]{10}$#';
		$regex[256] = '#^\+256[0-9]{9}$#';
		$regex[353] = '#^\+353[0-9]{9}$#';
		$regex[380] = '#^\+380[0-9]{9}$#';
		$regex[387] = '#^\+387[1-9][0-9]{7}$#';
		$regex[502] = '#^\+502[0-9]{8}$#';
		$regex[503] = '#^\+503[0-9]{8}$#';
		$regex[876] = '#^\+876[0-9]{7}$#';
		$regex[886] = '#^\+886[0-9]{9}$#';
		$regex[966] = '#^\+966[0-9]{9}$#';
		$regex[961] = '#^\+961[1-9][0-9]{7,8}$#';
		$regex[972] = '#^\+972[1-9][0-9]{8}$#';


		$regex[34] = '#^\+34[5-9][0-9]{8}$#';
		$regex[36] = '#^\+36[1-9][0-9]{7,8}$#';
		$regex[43] = '#^\+43[1-9][0-9]{3,12}$#';
		$regex[46] = '#^\+46[1-9][0-9]{5,9}$#';
		$regex[51] = '#^\+51[14-9][0-9]{7,8}$#';
		$regex[52] = '#^\+52[1-9][0-9]{9,10}$#';
		$regex[521] = '#^\+521[0-9]{9,10}$#';
		$regex[53] = '#^\+53[2-57][0-9]{5,7}$#';
		$regex[60] = '#^\+60[13-9][0-9]{7,9}$#';
		$regex[62] = '#^\+62[1-9][0-9]{6,10}$#';
		$regex[93] = '#^\+93[2-7][0-9]{8}$#';
		$regex[212] = '#^\+212[0-9]{9}$#';
		$regex[216] = '#^\+216[2-57-9][0-9]{7}$#';
		$regex[218] = '#^\+218[25679][0-9]{8}$#';
		$regex[220] = '#^\+220[2-9][0-9]{6}$#';
		$regex[221] = '#^\+221[378][0-9]{8}$#';
		$regex[222] = '#^\+222[2-48][0-9]{7}$#';
		$regex[223] = '#^\+223[246-9][0-9]{7}$#';
		$regex[224] = '#^\+224[367][0-9]{7,8}$#';
		$regex[226] = '#^\+226[24-7][0-9]{7}$#';
		$regex[227] = '#^\+227[0289][0-9]{7}$#';
		$regex[228] = '#^\+228[29][0-9]{7}$#';
		$regex[230] = '#^\+230[2-9][0-9]{6,7}$#';
		$regex[232] = '#^\+232[2-578][0-9]{7}$#';
		$regex[234] = '#^\+234[[0-9]{10}$#';
		$regex[235] = '#^\+235[2679][0-9]{7}$#';
		$regex[236] = '#^\+236[278][0-9]{7}$#';
		$regex[237] = '#^\+237[0-9]{9}$#';
		$regex[238] = '#^\+238[259][0-9]{6}$#';
		$regex[239] = '#^\+239[29][0-9]{6}$#';
		$regex[240] = '#^\+240[23589][0-9]{8}$#';
		$regex[241] = '#^\+2410?[0-9]{7}$#';
		$regex[242] = '#^\+242[0-9]{9}$#';
		$regex[244] = '#^\+244[29][0-9]{8}$#';
		$regex[245] = '#^\+245[3-79][0-9]{6}$#';
		$regex[247] = '#^\+247[2-467][0-9]{3}$#';
		$regex[248] = '#^\+248[24689][0-9]{5,6}$#';
		$regex[249] = '#^\+249[19][0-9]{8}$#';
		$regex[250] = '#^\+250[027-9][0-9]{7,8}$#';
		$regex[251] = '#^\+251[1-59][0-9]{8}$#';
		$regex[252] = '#^\+252[1-79][0-9]{6,8}$#';
		$regex[253] = '#^\+253[27][0-9]{7}$#';
		$regex[255] = '#^\+255[0-9]{9}$#';
		$regex[257] = '#^\+257[27][0-9]{7}$#';
		$regex[258] = '#^\+258[28][0-9]{7,8}$#';
		$regex[260] = '#^\+260[289][0-9]{8}$#';
		$regex[261] = '#^\+261[23][0-9]{8}$#';
		$regex[264] = '#^\+264[68][0-9]{7,8}$#';
		$regex[266] = '#^\+266[2568][0-9]{7}$#';
		$regex[267] = '#^\+267[2-79][0-9]{6,7}$#';
		$regex[268] = '#^\+268[027][0-9]{7}$#';
		$regex[269] = '#^\+269[379][0-9]{6}$#';
		$regex[291] = '#^\+291[178][0-9]{6}$#';
		$regex[297] = '#^\+297[25-9][0-9]{6}$#';
		$regex[298] = '#^\+298[2-9][0-9]{5}$#';
		$regex[299] = '#^\+299[1-689][0-9]{5}$#';
		$regex[350] = '#^\+350[2568][0-9]{7}$#';
		$regex[351] = '#^\+351[2-46-9][0-9]{8}$#';
		$regex[356] = '#^\+356[2357-9][0-9]{7}$#';
		$regex[358] = '#^\+358[0-9]{9}$#';
		$regex[370] = '#^\+370[3-9][0-9]{7}$#';
		$regex[371] = '#^\+371[2689][0-9]{7}$#';
		$regex[373] = '#^\+373[235-9][0-9]{7}$#';
		$regex[374] = '#^\+374[1-9][0-9]{7}$#';
		$regex[377] = '#^\+377[4689][0-9]{7,8}$#';
		$regex[378] = '#^\+378[05-7][0-9]{7,9}$#';
		$regex[382] = '#^\+382[2-9][0-9]{7,8}$#';
		$regex[389] = '#^\+389[2-578][0-9]{7}$#';
		$regex[421] = '#^\+421[2-689][0-9]{8}$#';
		$regex[500] = '#^\+500[2-7][0-9]{4}$#';
		$regex[504] = '#^\+504[237-9][0-9]{7}$#';
		$regex[505] = '#^\+505[12578][0-9]{7}$#';
		$regex[506] = '#^\+506[24-9][0-9]{7,9}$#';
		$regex[507] = '#^\+507[1-9][0-9]{6,7}$#';
		$regex[508] = '#^\+508[45][0-9]{5}$#';
		$regex[509] = '#^\+509[2-489][0-9]{7}$#';
		$regex[591] = '#^\+591[23467][0-9]{7}$#';
		$regex[592] = '#^\+592[2-4679][0-9]{6}$#';
		$regex[594] = '#^\+594[56][0-9]{8}$#';
		$regex[596] = '#^\+596[56][0-9]{8}$#';
		$regex[597] = '#^\+597[2-8][0-9]{5,6}$#';
		$regex[598] = '#^\+598[2489][0-9]{6,7}$#';
		$regex[672] = '#^\+672[13][0-9]{5}$#';
		$regex[673] = '#^\+673[2-578][0-9]{6}$#';
		$regex[674] = '#^\+674[458][0-9]{6}$#';
		$regex[675] = '#^\+675[1-9][0-9]{6,7}$#';
		$regex[676] = '#^\+676[02-8][0-9]{4,6}$#';
		$regex[677] = '#^\+677[1-9][0-9]{4,6}$#';
		$regex[678] = '#^\+678[2-57-9][0-9]{4,6}$#';
		$regex[680] = '#^\+680[2-8][0-9]{6}$#';
		$regex[681] = '#^\+681[5-7][0-9]{5}$#';
		$regex[682] = '#^\+682[2-57][0-9]{4}$#';
		$regex[683] = '#^\+683[1-5][0-9]{3}$#';
		$regex[685] = '#^\+685[2-8][0-9]{4,6}$#';
		$regex[687] = '#^\+687[2-57-9][0-9]{5}$#';
		$regex[688] = '#^\+688[29][0-9]{4,5}$#';
		$regex[690] = '#^\+690[2-9][0-9]{3}$#';
		$regex[691] = '#^\+691[39][0-9]{6}$#';
		$regex[692] = '#^\+692[2-6][0-9]{6}$#';
		$regex[853] = '#^\+853[268][0-9]{7}$#';
		$regex[855] = '#^\+855[1-9][0-9]{7,9}$#';
		$regex[856] = '#^\+856[2-8][0-9]{7,9}$#';
		$regex[962] = '#^\+962[235-9][0-9]{7,8}$#';
		$regex[963] = '#^\+963[1-59][0-9]{7,8}$#';
		$regex[964] = '#^\+964[1-7][0-9]{7,9}$#';
		$regex[965] = '#^\+965[12569][0-9]{6,7}$#';
		$regex[967] = '#^\+967[1-7][0-9]{6,8}$#';
		$regex[967] = '#^\+971[0-9]{9}$#';
		$regex[973] = '#^\+973[136-9][0-9]{7}$#';
		$regex[974] = '#^\+974[2-8][0-9]{6,7}$#';
		$regex[975] = '#^\+975[1-8][0-9]{6,7}$#';
		$regex[992] = '#^\+992[3-59][0-9]{8}$#';
		$regex[993] = '#^\+993[1-6][0-9]{7}$#';
		$regex[994] = '#^\+994[1-9][0-9]{8}$#';
		$regex[995] = '#^\+995[34578][0-9]{8}$#';
		$regex[996] = '#^\+996[35-8][0-9]{8,9}$#';
		$regex[998] = '#^\+998[679][0-9]{8}$#';


		if(empty($regex[$countrycode]) && $this->configPage){
			ACYSMS::display("We don't know what's the right phone number format for your country (+".$countrycode."), <a target=\"_blank\" href=\"mailto:acysms@acyba.com?subject=[ACYSMS%20PHONE%20FORMAT]%20Phone%20format%20%2B".$countrycode."&body=The%20phone%20%2B".$countrycode."%20always%20starts%20with%20%2B".$countrycode."%20and%20then%20contains%20??%20digits.%20Example:%20%2B".$countrycode."%20123456789\">please contact us at acysms@acyba.com </a>so we can improve your phone validation", "warning");
			return;
		}

		if(!empty($regex[$countrycode]) && !preg_match($regex[$countrycode], $phoneNum)){
			$this->error = JText::sprintf('SMS_WRONG_FORMAT', $phoneNum);
			return false;
		}
		return $phoneNum;
	}

	public function getCountryCode($phoneNum){
		$countryType = ACYSMS::get('type.country');
		krsort($countryType->country);
		foreach($countryType->country as $code => $country){
			$pos = strpos($phoneNum, '+'.$code);
			if($pos === 0){
				return $code;
			}
		}
		return false;
	}

	function sendVerificationCode($validPhone, $activationType = 'activation_optin'){

		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$userClass = ACYSMS::get('class.user');
		$user = $userClass->getByPhone($validPhone);
		if(!$user) return false;


		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randstring = '';
		$max = strlen($characters) - 1;
		for($i = 0; $i < 5; $i++){
			$randstring .= $characters[mt_rand(0, $max)];
		}

		if(is_string($user->user_activationcode)) $user->user_activationcode = unserialize($user->user_activationcode);

		$userClass = ACYSMS::get('class.user');
		if(empty($user->user_activationcode[$activationType])) $user->user_activationcode[$activationType] = $randstring;
		$userClass->save($user);

		$query = 'SELECT *
				FROM #__acysms_message AS message
				WHERE message_subject = '.$db->Quote($activationType).' OR message_type = '.$db->Quote($activationType).'
				ORDER BY message_id DESC
				LIMIT 1';
		$db->setQuery($query);
		$message = $db->loadObject();
		$messageId = $message->message_id;

		$senderProfileId = $message->message_senderprofile_id;
		if($senderProfileId == 0){
			$querySenderProfile = 'SELECT senderprofile_id FROM '.ACYSMS::table('senderprofile').' WHERE senderprofile_default = 1';
			$db->setQuery($querySenderProfile);
			$senderProfileId = $db->loadResult();

			$message->message_senderprofile_id = $senderProfileId;
			$messageClass = ACYSMS::get('class.message');
			$messageObject = $messageClass->save($message);
		}

		if(empty($messageId)){
			$this->errorCode = 'noMessageForThisType';
			$this->error = 'There is no SMS with the subject or the type : '.$activationType;
			return false;
		}

		$acyquery = ACYSMS::get('class.acyquery');
		$integrationTo = 'acysms';
		$integrationFrom = 'acysms';
		$integration = ACYSMS::getIntegration($integrationTo);
		$integration->initQuery($acyquery);
		$querySelect = $acyquery->getQuery(array('DISTINCT '.intval($messageId).', '.intval($user->user_id).', "acysms", '.time().', '.$config->get('priority_message', 3)));
		$finalQuery = 'INSERT INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority) '.$querySelect.' ON DUPLICATE KEY UPDATE queue_senddate ='.time();
		$db->setQuery($finalQuery);
		$db->query();

		$helperQueue = ACYSMS::get('helper.queue');
		$helperQueue->message_id = $messageId;
		$helperQueue->report = false;
		$helperQueue->displayNextTry = false;
		$helperQueue->process();
		if($helperQueue->successSend < 1){
			$this->errorCode = 'errorWhileSendingMessage';
			if(!empty($helperQueue->messages)) $this->error = implode(',', $helperQueue->messages);
			return false;
		}
		return true;
	}


	function verifyActivation($validPhone, $activationCode, $activationType = 'activation_optin', $removeActivationCode = 'true'){

		$userClass = ACYSMS::get('class.user');
		$user = $userClass->getByPhone($validPhone);
		if(!$user) return false;

		if(is_string($user->user_activationcode)) $user->user_activationcode = unserialize($user->user_activationcode);
		if(!isset($user->user_activationcode[$activationType])){
			$this->errorCode = 'KeyNotGenerated';
			$this->error = JText::_('SMS_KEY_NOT_GENERATED');
			return false;
		}

		if(empty($user->user_activationcode[$activationType])){
			return true;
		}

		if($activationCode != $user->user_activationcode[$activationType]){
			$this->errorCode = 'wrongActivationCode';
			$this->error = JText::_('SMS_WRONG_ACTIVATION_CODE');
			return false;
		}
		if($removeActivationCode){
			$userClass = ACYSMS::get('class.user');
			$user->user_activationcode[$activationType] = '';
			$userClass->save($user);
		}

		return true;
	}

	public function displayMMS($message, $isForm){
		if(!$isForm){
			$senderProfileClass = ACYSMS::get("class.senderprofile");
			$gateway = $senderProfileClass->getGateway($message->message->message_senderprofile_id);
			if(empty($gateway)) return;

			if(!$gateway->handleMMS) return;
		}
		$html = '
		<script>
		function addFile(file,id){
			idToChange = id-1;
			if(document.getElementById("sms_upload_text_"+idToChange).value == "") {
				idForFunction = id+1;
				divUpload = document.getElementById("sms_mms_upload_"+idToChange);
				divToAdd = divUpload.cloneNode(true);
				divToAdd.id = "sms_mms_upload_"+id;
				divToAdd.getElementsByClassName("sms_upload_text")[0].id = "sms_upload_text_"+id;
				divToAdd.getElementsByClassName("sms_upload_text")[0].value = "";
				divToAdd.getElementsByClassName("importfile")[0].value="";
				divToAdd.getElementsByClassName("importfile")[0].setAttribute("onchange", "addFile(this,"+idForFunction+")");
				divUpload.getParent().insertBefore(divToAdd,divUpload.nextSibling);
				document.getElementById("sms_mms_upload_"+idToChange).getElementsByClassName("sms_remove_media")[0].setAttribute("onclick","deleteMedia(this.parentNode)");
			}
			document.getElementById("sms_upload_text_"+idToChange).value=file.files[0].name;
		}

		function deleteMedia(fileDiv){
			if(!confirm("'.JText::_("SMS_CONFIRM_DELETE").'"))return;
			var filenameonDB = fileDiv.getElementsByClassName("sms_upload_text")[0].value;
			fileDiv.getElementsByClassName("sms_upload_text")[0].value = "";
			document.getElementById("media_to_delete").value += filenameonDB+",";
			fileDiv.parentNode.removeChild(fileDiv);
		}
		</script>';
		$files = array();
		if(!empty($message->message->message_attachment)) $files = explode(',', $message->message->message_attachment);
		$id = 0;
		foreach($files as $oneFile){
			if(empty($oneFile)) continue;
			$idIncremented = $id + 1;
			$importHelper = ACYSMS::get('helper.import');
			$uploadPath = $importHelper->getUploadDirectory();
			$imageLink = str_replace(ACYSMS_ROOT, ACYSMS_LIVE, $uploadPath);
			$imageLink = str_replace(DS, "/", $imageLink);
			$fileExtension = explode('.', $oneFile);
			$fileExtension = $fileExtension[1];
			$imageExtension = array('png', 'jpeg', 'jpg', 'gif', 'bmp', 'ico');
			$styleOther = "";
			$styleDiv = "";
			if(!$isForm){
				$styleOther .= " width:225px";
				$styleDiv .= "background:initial";
			}
			if(in_array($fileExtension, $imageExtension)){
				$html .= '<div style="background:initial;" id="sms_mms_upload_'.$id.'" class="sms_mms_upload">';
				$html .= '<img style="width:95%; margin-bottom:15px;" src="'.$imageLink.$oneFile.'"/>';
				if($isForm) $html .= '<span style="position:absolute; bottom:30px; left:10px" onclick="deleteMedia(this.parentNode)" class="sms_remove_media"></span>';
				$html .= '<input style="display: none;" type="text" readonly class="sms_upload_text" id="sms_upload_text_'.$id.'" value="'.$oneFile.'">';
				$html .= '</div>';
			}else{
				$html .= '<div style="'.$styleDiv.'" id="sms_mms_upload_'.$id.'" class="sms_mms_upload" style="cursor:pointer">';
				if($isForm) $html .= '<span onclick="deleteMedia(this.parentNode)" class="sms_remove_media" style="cursor:pointer"></span>';
				$html .= '<input type="text" style="'.$styleOther.'" readonly class="sms_upload_text" id="sms_upload_text_'.$id.'" value="'.$oneFile.'">';
				if($isForm) $html .= '<input type="file" class="importfile" name="importfile[]" onchange="addFile(this,'.$idIncremented.')" style="cursor:pointer">';
				$html .= '</div>';
			}
			$id++;
		}
		if(!$isForm) return $html;

		$idIncremented = $id + 1;
		$html .= '<div id="sms_mms_upload_'.$id.'" class="sms_mms_upload">
					<span onclick="" class="sms_remove_media"></span>
					<input type="text" readonly class="sms_upload_text" id="sms_upload_text_'.$id.'">
					<input type="file" class="importfile" name="importfile[]" onchange="addFile(this,'.$idIncremented.')" style="cursor:pointer"">
			</div>
			<input type="hidden" id="media_to_delete" name="message_attachment_delete">';

		return $html;
	}
}
