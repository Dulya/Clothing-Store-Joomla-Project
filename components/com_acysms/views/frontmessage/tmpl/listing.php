<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset id="acysms_message_listing_menu">
	<div class="toolbar" id="acysmstoolbar" style="float: right;">
		<table>
			<tr>
				<?php if(ACYSMS::isAllowed($this->config->get('acl_messages_create_edit', 'all'))){ ?>
					<td id="acysmsbutton_message_preview">
						<a onclick="javascript:if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SMS_PLEASE_SELECT', true); ?>');}else{  submitbutton('preview')} return false;" href="#"><span title="<?php echo JText::_('SMS_PREVIEW').'/'.JText::_('SMS_SEND'); ?>"><i class="smsicon-viewmore"></i></span><?php echo JText::_('SMS_PREVIEW').'/'.JText::_('SMS_SEND'); ?></a>
					</td>
					<td id="acysmsbuttondivider"><span class="divider"></span></td>
					<td id="acysmsbutton_message_add">
						<a onclick="javascript:submitbutton('form'); return false;" href="#"><span title="<?php echo JText::_('SMS_NEW'); ?>"><i class="smsicon-new"></i></span><?php echo JText::_('SMS_NEW'); ?></a>
					</td>
					<td id="acysmsbutton_subscriber_edit"><a onclick="javascript:if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SMS_PLEASE_SELECT', true); ?>');}else{  submitbutton('edit')} return false;" href="#">
							<span title="<?php echo JText::_('SMS_EDIT'); ?>"><i class="smsicon-edit"></i></span><?php echo JText::_('SMS_EDIT'); ?></a>
					</td>
				<?php } ?>
				<?php if(ACYSMS::isAllowed($this->config->get('acl_message_delete', 'all'))){ ?>
					<td id="acysmsbutton_message_delete">
						<a onclick="javascript:if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SMS_PLEASE_SELECT', true); ?>');}else{if(confirm('<?php echo JText::_('SMS_VALIDDELETEITEMS', true); ?>')){submitbutton('remove');}} return false;" href="#"><span title="<?php echo JText::_('SMS_DELETE'); ?>"><i class="smsicon-delete"></i></span><?php echo JText::_('SMS_DELETE'); ?></a>
					</td>
				<?php } ?>
				<?php if(ACYSMS::isAllowed($this->config->get('acl_message_copy', 'all'))){ ?>
					<td id="acysmsbutton_message_copy"><a onclick="javascript:if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SMS_PLEASE_SELECT', true); ?>');}else{  submitbutton('copy')} return false;" href="#">
							<span title="<?php echo JText::_('SMS_COPY'); ?>"><i class="smsicon-copy"></i></span><?php echo JText::_('SMS_COPY'); ?></a>
					</td>
				<?php } ?>
				<?php if(ACYSMS::isAllowed($this->config->get('acl_message_manage_own', 'all'))){ ?>
					<td id="acysmsbutton_message_archive"><a onclick="javascript:if(document.adminForm.boxchecked.value==0){alert('<?php echo JText::_('SMS_PLEASE_SELECT', true); ?>');}else{  submitbutton('archive')} return false;" href="#">
							<span title="<?php echo JText::_('SMS_ARCHIVE').' / '.JText::_('SMS_UNARCHIVE'); ?>"><i class="smsicon-archive"></i></span><?php echo JText::_('SMS_ARCHIVE').' / '.JText::_('SMS_UNARCHIVE'); ?></a>
					</td>
				<?php } ?>
			</tr>
		</table>
	</div>
	<div class="acysmsheader" style="float: left;"><h1><?php echo JText::_('SMS_MESSAGE'); ?></h1></div>
</fieldset>
<?php
include(ACYSMS_BACK.'views'.DS.'message'.DS.'tmpl'.DS.'listing.php');
