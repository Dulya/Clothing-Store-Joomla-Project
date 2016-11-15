<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-message">
	<div class="acysmsblockoptions" style="width:400px">
		<span class="acysmsblockbluetitle"><i class="smsicon-pricetag"></i><?php echo JText::_('SMS_INCREASE_YOUR_SALES'); ?></span>
		<?php echo JText::_('SMS_INCREASE_YOUR_SALES_DESCRIPTION'); ?>
		<br/><br/>
		<div class="extensions_choice">
			<span class="extensions_choice_intro"><?php echo JText::sprintf('SMS_PLUG_ACYSMS_EXTENSION', '<span class="extension_detail">'.JText::_('SMS_ECOMMERCE_MANAGEMENT_EXTENSION').'</span>'); ?></span>
			<br/><br/>

			<div>
				<table class="acysms_blocktable" cellspacing="1">
					<tr>
						<td>
							<?php echo $this->integrationList->ecommerceIntegration; ?>
						</td>
					</tr>

				</table>
			</div>
		</div>
	</div>
	<div class="acysmsblockoptions" style="width:400px">
		<span class="acysmsblockbluetitle"><i class="smsicon-megaphone"></i><?php echo JText::_('SMS_GROW_YOUR_COMMUNITY'); ?></span>
		<?php echo JText::_('SMS_GROW_YOUR_COMMUNITY_DESCRIPTION'); ?>
		<br/><br/>
		<div class="extensions_choice">
			<span class="extensions_choice_intro"><?php echo JText::sprintf('SMS_PLUG_ACYSMS_EXTENSION', '<span class="extension_detail">'.JText::_('SMS_USER_MANAGEMENT_EXTENSION').'</span>'); ?></span>
			<br/><br/>
			<div>
				<table class="acysms_blocktable" cellspacing="1">
					<tr>
						<td>
							<?php echo $this->integrationList->communityIntegration; ?>
						</td>
					</tr>

				</table>
			</div>
		</div>
	</div>
	<?php if(!empty($this->integrationList->eventIntegration)){ ?>
		<div class="acysmsblockoptions" style="width:400px">
			<span class="acysmsblockbluetitle"><i class="smsicon-calendar"></i><?php echo JText::_('SMS_MAKE_EVENTS_SUCCESS'); ?></span>
			<?php echo JText::_('SMS_MAKE_EVENTS_SUCCESS_DESCRIPTION'); ?>

			<br/><br/>
			<div class="extensions_choice">
				<span class="extensions_choice_intro"><?php echo JText::sprintf('SMS_PLUG_ACYSMS_EXTENSION', '<span class="extension_detail">'.JText::_('SMS_EVENT_MANAGEMENT_EXTENSION').'</span>'); ?></span>
				<br/><br/>
				<div>
					<table class="acysms_blocktable" cellspacing="1">
						<tr>
							<td>
								<?php echo $this->integrationList->eventIntegration; ?>
							</td>
						</tr>

					</table>
				</div>
			</div>
		</div>
	<?php } ?>

	<div class="acysmsblockoptions" id="default_config">
		<span class="acysmsblocktitle"><?php echo JText::_('SMS_DEFAULT_VALUES'); ?></span>

		<table class="acysms_blocktable" cellspacing="1">
			<tr>
				<td>
					<?php
					echo JText::sprintf('SMS_INTEGRATION_DEFAULT', $this->integrationType);
					?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_MESSAGE_MAX_CHARACTERS'); ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[messageMaxChar]" style="width:50px" value="<?php echo empty($this->messageMaxChar) ? 0 : $this->escape($this->messageMaxChar); ?>">
				</td>
			</tr>

			<tr>
				<td class="key">
					<?php echo JText::_('SMS_DEFAULT_COUNTRY'); ?>
				</td>
				<td>
					<?php
					echo $this->countryPrefix;
					?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<?php
					$link = ACYSMS_LIVE.'administrator/index.php?option=com_acysms&ctrl=fields';
					if(!empty($this->idPhoneField)) $link .= '&task=edit&fields_fieldid='.$this->idPhoneField;
					echo '<a href="'.$link.'">'.JText::_('SMS_CHANGE_DEFAULT_COUNTRY_CUSTOM_FIELD').'</a>';
					?>
				</td>
			</tr>
		</table>
	</div>

	<div class="acysmsblockoptions" id="miscellaneous">
		<span class="acysmsblocktitle"><?php echo JText::_('SMS_MISCELLANEOUS'); ?></span>

		<table class="acysms_blocktable" cellspacing="1">
			<tr>
				<td class="key">
					<?php echo ACYSMS::tooltip(JText::_('SMS_USE_HTTPS_DESC'), JText::_('SMS_USE_HTTPS'), '', JText::_('SMS_USE_HTTPS')); ?>
				</td>
				<td>
					<?php echo $this->useHTTPS; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo ACYSMS::tooltip(JText::_('SMS_USE_SHORT_URL_DESC'), JText::_('SMS_USE_SHORT_URL'), '', JText::_('SMS_USE_SHORT_URL')); ?>
				</td>
				<td>
					<?php echo $this->useShortUrl; ?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('SMS_API_KEY_SHORT_URL').' <a href="http://www.acyba.com/acysms/528-how-to-configure-the-shortened-urls-option.html" target="_blank">('.JText::_('SMS_EXTRA_INFORMATION').')</a>'; ?>
				</td>
				<td>
					<input class="inputbox" type="text" name="config[api_key_short_url]" value="<?php echo $this->apiKeyShortUrl; ?>">
				</td>
			</tr>
		</table>
	</div>

</div>
<script type="text/javascript">
	function hideApiKeyField(){
		var misc = document.getElementById('miscellaneous');
		var tab = misc.getElementsByTagName('table')[0];
		var field = tab.getElementsByTagName('tr')[2];
		field.style.display = 'none';
	}

	function showApiKeyField(){
		var misc = document.getElementById('miscellaneous');
		var tab = misc.getElementsByTagName('table')[0];
		var field = tab.getElementsByTagName('tr')[2];
		field.style.display = '';
	}

	var switchoff = document.getElementById('config_use_short_url0');
	switchoff.addEventListener('click', hideApiKeyField);

	var switchon = document.getElementById('config_use_short_url1');
	switchon.addEventListener('click', showApiKeyField);
</script>
