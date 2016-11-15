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
<div id="sms_global">
	<?php
		$countType = ACYSMS::get('type.countcharacters');
		echo $countType->countCaracters('message_body','');
	?>
	<div id="sms_body">
		<div onclick="countCharacters();" onkeyup="countCharacters();" style="width:241px !important;overflow: auto; height:318px;" rows="20" name="data[message][message_body]" id="message_body" ><?php echo nl2br(@$this->message->message_body); ?></div>
	</div>
	<div id="sms_bottom">
	</div>
</div>
</div>
