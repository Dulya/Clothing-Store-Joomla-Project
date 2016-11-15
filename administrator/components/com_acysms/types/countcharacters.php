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

class ACYSMScountcharactersType{

	function countCaracters($id, $moduleNumber = ''){

		$this->_addScript($id, $moduleNumber);
		return '<div class="sms_top"><div class="sms_count" id="sms_count'.$moduleNumber.'" ></div><div class="sms_info"> '.JText::sprintf('SMS_MESSAGE_PARTS', '<span id="sms_part'.$moduleNumber.'"></span>').'</div></div>';
	}


	private function _addScript($id, $moduleNumber){
		static $done = false;
		?>
		<script type="text/javascript">

			window.addEvent('load', function(){
				countCharacters(<?php echo $moduleNumber; ?>)
			});

			<?php
			if($done){
				echo '</script>';
				return;
			}
			$done = true;
			?>


			function countCharacters(moduleNumber){

				if(typeof moduleNumber == 'undefined') moduleNumber = '';
				message_size = 0;
				message_limit = 160;
				message_cut = 153;

				if(typeof document.getElementById("<?php echo $id ?>" + moduleNumber).value != 'undefined'){
					message_body = document.getElementById("<?php echo $id ?>" + moduleNumber).value;
				}else{
					message_body = document.getElementById("<?php echo $id ?>" + moduleNumber).innerHTML;
				}

				message_body = message_body.replace(/<br.*?>/g, '');

				var gsm7bitChars = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
				var gsm7bitExChar = "^{}\\[~]|€";
				var gsm7bitUnits = 0;
				var utf16codeUnits = 0;

				for(var i = 0, len = message_body.length; i < len; i++){
					if(gsm7bitUnits != null){
						if(message_body.charCodeAt(i) != 13){
							if(gsm7bitChars.indexOf(message_body.charAt(i)) > -1){
								gsm7bitUnits++;
							}else if(gsm7bitExChar.indexOf(message_body.charAt(i)) > -1){
								gsm7bitUnits += 2;
							}else{
								gsm7bitUnits = null;
							}
						}
					}
					utf16codeUnits += message_body.charCodeAt(i) < 0x10000 ? 1 : 2;
				}


				if(typeof gsm7bitUnits != 'undefined' && gsm7bitUnits){
					message_size = gsm7bitUnits;
					message_limit = 160;
					message_cut = 153;
				}else if(typeof gsm7bitUnits == 'undefined' || !gsm7bitUnits && utf16codeUnits && typeof gsm7bitUnits != 'undefined' && message_body.length > 0){
					message_size = utf16codeUnits;
					message_limit = 70;
					message_cut = 67;
				}
				if(message_size > message_limit){
					sms_part = (message_size / message_cut) + 1;
				}else{
					sms_part = 1;
				}
				document.getElementById("sms_count" + moduleNumber).innerHTML = message_size + '/' + message_limit;
				document.getElementById("sms_part" + moduleNumber).innerHTML = Math.floor(sms_part);
			}


		</script>
		<?php
	}
}
