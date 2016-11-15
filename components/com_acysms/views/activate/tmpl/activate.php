<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysmsactivation">
	<div class="introtext"><?php echo empty($this->introtext) ? JText::_('SMS_ACTIVATION_INTRO') : ($this->introtext); ?></div>
	<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
		<div id="ValidationPhoneNumber">
			<?php echo JText::_('SMS_PHONE').' : '; ?>
			<?php
			$phoneToDisplay = '';
			if(!empty($this->user->user_phone_number)){
				$phoneToDisplay = $this->user->user_phone_number;
			}else $phoneToDisplay = $this->config->get('country');
			echo $this->countryType->displayPhone($phoneToDisplay, 'phoneNumber');
			?>
			</di>
			<br/>

			<div id="validationCode">
				<?php echo JText::_('SMS_ACTIVATION_CODE').' : '; ?>
				<input type="text" name="activationCode"/>
			</div>
			<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
			<input type="hidden" name="task" value="saveactivation"/>
			<input type="hidden" name="ctrl" value="<?php echo $this->ctrl; ?>"/>
			<input type="hidden" name="moduleId" value="<?php echo $this->moduleId; ?>"/>
			<?php echo JHTML::_('form.token'); ?>
			<div id="activatebutton_div" class="activatediv">
				<input class="button acysms_button" type="submit" value="<?php echo empty($this->activatetext) ? JText::_('SMS_ACTIVATE') : ($this->activatetext); ?>"/>
			</div>
		</div>
	</form>
	<div class="finaltext"><?php echo empty($this->finaltext) ? '' : ($this->finaltext); ?></div>
</div>
