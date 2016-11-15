<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset id="acysms_answer_listing_menu">
	<div class="toolbar" id="acysmstoolbar" style="float: right;">
		<table>
			<tr>
				<?php if(ACYSMS::isAllowed($this->config->get('acl_answers_export', 'all'))){ ?>
					<td id="acysmsbutton_answers_export" class="pull-left">
						<a href="<?php echo ACYSMS::completeLink('frontanswer&task=exportGlobal'); ?>"><span title="<?php echo JText::_('SMS_EXPORT'); ?>"><i class="smsicon-export"></i></span><?php echo JText::_('SMS_EXPORT'); ?></a>
					</td>
				<?php }
				if(ACYSMS::isAllowed($this->config->get('acl_answers_delete', 'all'))){ ?>
					<td id="acysmsbutton_answer_delete">
						<a onclick="javascript:if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SMS_PLEASE_SELECT', true); ?>');}else{if(confirm('<?php echo JText::_('SMS_VALIDDELETEITEMS', true); ?>')){submitbutton('remove');}} return false;" href="#"><span title="<?php echo JText::_('SMS_DELETE'); ?>"><i class="smsicon-delete"></i></span><?php echo JText::_('SMS_DELETE'); ?>
						</a>
					</td>
				<?php } ?>
			</tr>
		</table>
	</div>
	<div class="acysmsheader" style="float: left;"><h1><?php echo JText::_('SMS_ANSWERS'); ?></h1></div>
</fieldset>
<?php
include(ACYSMS_BACK.'views'.DS.'answer'.DS.'tmpl'.DS.'listing.php');
