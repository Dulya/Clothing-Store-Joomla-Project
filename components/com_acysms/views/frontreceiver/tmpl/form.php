<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset id="acysms_receiver_form_menu">
	<div class="toolbar" id="acysmstoolbar" style="float: right;">
		<table>
			<tr>
				<td id="acysmsbuttonsave"><a onclick="javascript:<?php if(ACYSMS_J16) echo "Joomla."; ?>submitbutton('save'); return false;" href="#"><span title="<?php echo JText::_('SMS_SAVE'); ?>"><i class="smsicon-save"></i></span><?php echo JText::_('SMS_SAVE'); ?></a></td>
				<td id="acysmsbuttonapply"><a onclick="javascript:<?php if(ACYSMS_J16) echo "Joomla."; ?>submitbutton('apply'); return false;" href="#"><span title="<?php echo JText::_('SMS_APPLY'); ?>"><i class="smsicon-apply"></i></span><?php echo JText::_('SMS_APPLY'); ?></a></td>
				<td id="acysmsbuttoncancel"><a onclick="javascript:<?php if(ACYSMS_J16) echo "Joomla."; ?>submitbutton('cancel'); return false;" href="#"><span title="<?php echo JText::_('SMS_CANCEL'); ?>"><i class="smsicon-cancel"></i></span><?php echo JText::_('SMS_CANCEL'); ?></a></td>
			</tr>
		</table>
	</div>
	<div class="acyheader" style="float: left;"><h1><?php echo JText::_('SMS_RECEIVER').' : '.@$this->joomUser->name; ?></h1></div>
</fieldset>
<?php
if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
include(ACYSMS_BACK.'views'.DS.'receiver'.DS.'tmpl'.DS.'form.php'); ?>
