<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysms_content">
	<div id="iframedoc"></div>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=senderprofile" method="post" name="adminForm" class="clearfix" id="adminForm" autocomplete="off" enctype="multipart/form-data">
		<div style="width:48%; float: left; display: inline-block;">
			<div class="acysmsonelineblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_SENDER_PROFILE'); ?></span>
				<table class="acysms_blocktable">
					<tr>
						<td class="key">
							<label for="senderprofile_name"><?php echo JText::_('SMS_SENDER_PROFILE_NAME'); ?></label>
						</td>
						<td>
							<input type="text" name="data[senderprofile][senderprofile_name]" id="senderprofile_name" class="inputbox" style="width:200px;" value="<?php echo $this->escape(@$this->senderprofile->senderprofile_name); ?>"/>
						</td>
					</tr>
					<tr>
						<td class="key">
							<label for="senderprofile_gateway"><?php echo JText::_('SMS_GATEWAY'); ?></label>
						</td>
						<td>
							<?php echo $this->gatewaydropdown; ?>
						</td>
					</tr>
					<tr>
						<td>
							<label for="creator">
								<?php echo JText::_('SMS_CREATOR'); ?>
							</label>
						</td>
						<td>
							<input type="hidden" id="creatorid" name="data[senderprofile][senderprofile_userid]" value="<?php echo $this->escape(@$this->senderprofile->senderprofile_userid); ?>"/>
							<?php echo '<span id="creatorname">'.@$this->senderprofile->senderprofile_username.'</span>';
							echo ' <a class="modal" title="'.JText::_('SMS_EDIT', true).'"  href="index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=user&amp;task=choosejoomuser&currentIntegration=acysms" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
							?>
						</td>
					</tr>
				</table>
			</div>
			<div class="acysmsonelineblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_PARAMETERS'); ?></span>
				<div id="gateway_params">
					<?php if(!empty($this->senderprofile->senderprofile_gateway)){
						$senderprofileClass = ACYSMS::get('class.senderprofile');
						$gateway = $senderprofileClass->getGateway($this->senderprofile->senderprofile_gateway, $this->senderprofile->senderprofile_params);
						if(method_exists($gateway, 'displayConfig')) $gateway->displayConfig();
					} ?>
				</div>
			</div>
			<div class="acysmsonelineblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_ACCESS_LEVEL'); ?></span>
				<?php echo $this->acltype->display('data[senderprofile][senderprofile_access]', @$this->senderprofile->senderprofile_access); ?>
			</div>
		</div>
		<div style="width: 48%; float: left">
			<div class="acysmsblockoptions" style="float:none; width:226px">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_SEND_TEST'); ?></span>
				<div id="message-test">
					<?php
					$testReceiverType = ACYSMS::get('type.testReceiver');
					$testReceiverType->display(false, $this->currentIntegration, $this->listReceiversTest);
					?>
					<button class="acysms_button" type="submit" onclick="addReceiverInput();if(document.getElementById('testNumberReceiver').value=='' || undefined){window.acysms_js.openBox(document.getElementById('selectreceiver'),'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=choose');return false;}else{ <?php if(ACYSMS_J30) echo "Joomla.submitbutton('sendTest');}";else echo "submitbutton('sendTest');}"; ?> "><?php echo JText::_('SMS_SEND_TEST') ?></button>
				</div>
			</div>
			<div id="sms_global" style="margin:10px 0 0 16px">
				<?php
				$countType = ACYSMS::get('type.countcharacters');
				echo $countType->countCaracters('message_body', '');
				?>
				<div id="sms_body">
					<textarea onclick="countCharacters();" onkeyup="countCharacters();" style="width:98%" rows="20" name="message_body" id="message_body"><?php echo @$this->message_body; ?></textarea>
				</div>
				<div id="sms_bottom"></div>
			</div>

		</div>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->senderprofile->senderprofile_id); ?>"/>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="senderprofile"/>
		<input type="hidden" name="currentIntegration" value="<?php echo $this->currentIntegration; ?>"/>
		<input type="hidden" name="<?php echo $this->currentIntegration.'_testNumberReceiver' ?>" id="testNumberReceiver" value=""/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
