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
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=customer" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

	<div class="acysmsblockoptions">
		<span class="acysmsblocktitle"><?php echo JText::_('SMS_CUSTOMERS'); ?></span>
		<table class="acysms_blocktable" width="100%">
			<tr>
				<td class="key">
					<label for="user_joomid">
						<?php echo JText::_('SMS_ASSIGNED_JOOMUSER'); ?>
					</label>
				</td>
				<td>
				<span id="joomuser">
				<?php
				if(!empty($this->joomUser->name) && !empty($this->joomUser->email)){
					echo $this->joomUser->name.' ('.$this->joomUser->email.')';
				}else  echo JText::_('SMS_SELECT_JOOMUSER');
				echo "</span>";
				$app = JFactory::getApplication();
				if($app->isAdmin()){
					echo ' <a class="modal"  href="index.php?option=com_acysms&tmpl=component&ctrl=user&task=choosejoomuser" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
				}
				?>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="customer_credits">
						<?php echo JText::_('SMS_CREDITS_LEFT'); ?>
					</label>
				</td>
				<td>
					<input type="text" name="data[customer][customer_credits]" id="customer_credits" class="inputbox" style="width:200px" value="<?php echo $this->escape($this->customer->customer_credits); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="customer_credits_url">
						<?php echo JText::_('SMS_DEFAULT_CREDITS_URL'); ?>
					</label>
				</td>
				<td>
					<?php
					if(empty($this->customer->customer_credits_url)){
						$URL = $this->config->get('default_credits_url');
					}else $URL = $this->customer->customer_credits_url;
					?>
					<input type="text" name="data[customer][customer_credits_url]" id="customer_credits_url" class="inputbox" style="width:200px" value="<?php echo $this->escape($URL); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="customer_credits">
						<?php echo JText::_('SMS_SENDER_PROFILE'); ?>
					</label>
				</td>
				<td>
					<?php

					$values = array();
					if(!empty($this->customer->customer_senderprofile_id) && is_string($this->customer->customer_senderprofile_id)) $values = explode(',', $this->customer->customer_senderprofile_id);

					echo $this->senderprofile->display('data[customer][customer_senderprofile_id][]', $values);
					?>
				</td>
			</tr>
		</table>
	</div>	



		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->customer->customer_id); ?>"/>
		<input type="hidden" id="user_joomid" name="data[customer][customer_joomid]" value="<?php echo $this->escape(@$this->joomUser->id); ?>"/>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value="save"/>
		<input type="hidden" name="ctrl" value="customer"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
