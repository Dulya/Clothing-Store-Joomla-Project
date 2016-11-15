<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="unsubpage">
<div class="introtext"><?php echo empty($this->introtext) ? JText::_('SMS_UNSUB_INTRO') : ($this->introtext); ?></div>
<form action="<?php echo JRoute::_( 'index.php' );?>" method="post" name="adminForm" id="adminForm" >
	<div id="phoneNumber">
		<?php echo $this->countryType->displayPhone($this->config->get('country'),'number'); ?>
	</div>
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="task" value="saveunsub" />
	<input type="hidden" name="ctrl" value="<?php echo $this->ctrl; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<div id="unsubbutton_div" class="unsubdiv">
		<input class="button acysms_button" type="submit" value="<?php echo empty($this->unsubscribetext) ? JText::_('SMS_UNSUBSCRIBE') : ($this->unsubscribetext);?>"/>
	</div>
</form>
<div class="finaltext"><?php echo empty($this->finaltext) ? '' : ($this->finaltext); ?></div>
</div>
<?php
if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
