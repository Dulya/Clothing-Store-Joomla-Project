<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="config_interface">
	<div class="acysmsonelineblockoptions">
		<span class="acysmsblocktitle">CSS</span>
		<table class="acysms_blocktable" cellspacing="1">
			<tr>
				<td class="key">
					<?php echo ACYSMS::tooltip(JText::_('SMS_CSS_MODULE_DESC'), JText::_('SMS_CSS_MODULE'), '', JText::_('SMS_CSS_MODULE')); ?>
				</td>
				<td>
					<?php echo $this->elements->css_module; ?>
				</td>
			</tr>
			<?php if(ACYSMS_J30){ ?>
				<tr>
					<td class="key">
						<?php echo JText::_('SMS_USE_BOOTSTRAP_FRONTEND'); ?>
					</td>
					<td>
						<?php echo $this->elements->bootstrap_frontend; ?>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
	<div class="acysmsonelineblockoptions">
		<span class="acysmsblocktitle"> <?php echo JText::_('SMS_SUBSCRIPTION'); ?></span>
		<table class="acysms_blocktable" cellspacing="1">
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_REQUIRE_CONFIRM'); ?>
				</td>
				<td>
					<?php
					echo $this->confirmationOptions.' '.$this->confirmationMessage;
					?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_UNIQUE_PHONE_SUBSCRIPTION'); ?>
				</td>
				<td>
					<?php
					echo $this->uniquePhoneSubscriptionOption;
					?>
				</td>

			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_NOTIF_CREATE'); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[admin_address]" style="width:200px" value="<?php echo $this->escape($this->config->get('admin_address')); ?>">
				</td>
			</tr>
		</table>
	</div>
	<div class="acysmsonelineblockoptions">
		<span class="acysmsblocktitle"> <?php echo JText::_('SMS_FRONTEND'); ?></span>
		<table class="acysms_blocktable" cellspacing="1">
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_ALLOW_FRONTEND_MANAGEMENT'); ?>
				</td>
				<td>
					<div id="frontEndManagement">
						<?php
						echo $this->frontEndManagementOption;
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="key" valign="top">
					<?php echo JText::_('SMS_FRONTEND_FILTERS'); ?>
				</td>
				<td>
					<div id="frontEndFilters">
						<?php
						echo $this->conditionsToDisplay;
						?>
					</div>
					<button type="button" class="acysms_button" onclick="addCondition();return false;"><?php echo JText::_('SMS_ADD_CONDITION'); ?></button>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_REQUIRED_FILTER'); ?>
				</td>
				<td>
					<?php echo $this->requiredFilterString; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo ACYSMS::tooltip(JText::_('SMS_FE_DELETE_BUTTON_DESC'), JText::_('SMS_FE_DELETE_BUTTON'), '', JText::_('SMS_FE_DELETE_BUTTON')); ?>
				</td>
				<td>
					<?php $deleteButton = array(JHTML::_('select.option', "delete", JText::_('SMS_DELETE_USER')), JHTML::_('select.option', "unsub", JText::_('SMS_UNSUB_USER')));
					echo JHTML::_('acysmsselect.radiolist', $deleteButton, 'config[frontend_delete_button]', '', 'value', 'text', $this->config->get('frontend_delete_button', 'delete')); ?>
				</td>
			</tr>
		</table>
	</div>

	<div class="acysmsonelineblockoptions">
		<span class="acysmsblocktitle"> <?php echo JText::_('SMS_CUSTOMERS'); ?></span>
		<table class="acysms_blocktable" cellspacing="1">
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_REMOVE_CREDITS_SEND_FRONT'); ?>
				</td>
				<td>
					<div id="customersManagement">
						<?php
						echo $this->customerManagementOption;
						?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_DEFAULT_CREDITS_URL'); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[default_credits_url]" style="width:200px" value="<?php echo $this->escape($this->config->get('default_credits_url')); ?>">
				</td>
			</tr>
		</table>
	</div>
</div>
