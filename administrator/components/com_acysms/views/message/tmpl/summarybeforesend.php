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
	<div id="iframedoc"></div>
	<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm"  id="adminForm" autocomplete="off">
		<div class="acysmsblockoptions" width="100%" id="finalSend">
			<span class="acysmsblocktitle"><?php echo JText::_( 'SMS_SEND' ); ?></span>

		<?php
		if(!empty($this->numberUser)) echo JText::sprintf('SMS_SEND_TOTAL', $this->numberUser, ACYSMS::getDate(time()));

		echo $this->msgToDisplay;
		if(!empty($this->errorMsg)) echo '<font color="red"><b>'.$this->errorMsg.'</b></font>';

		if((!$this->app->isAdmin() && $this->allowCustomerManagement && ($this->credits <= 0 || ($this->numberUser * $this->partInformations->nbParts) > $this->credits)) || $this->app->isAdmin() && empty($this->numberUser) || !empty($this->alreadyInQueue)){
			?>
				<div>
					<button class="acysms_button" type="submit" onclick="<?php  if(ACYSMS_J30) echo "Joomla.submitbutton('preview')"; else echo "submitbutton('preview')"; ?> "><?php echo JText::_('SMS_CANCEL')?></button>
				</div>
			<?php
		}else{
			?>
				<div>
					<button class="acysms_button" type="submit" onclick="<?php  if(ACYSMS_J30) echo "Joomla.submitbutton('send')"; else echo "submitbutton('send')"; ?> "><?php echo JText::_('SMS_SEND')?></button>
				</div>
			<?php
		}
		?>
		</div>

		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo $this->message->message_id; ?>" />
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>" />
		<?php
			if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		 	echo JHTML::_( 'form.token' ); 
		 ?>
	</form>
</div>
