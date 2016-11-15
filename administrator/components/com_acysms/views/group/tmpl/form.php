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
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT.'&ctrl='.JRequest::getCmd('ctrl'); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<?php $divClass = $this->app->isAdmin() ? 'acysmsblockoptions' : 'acysmsonelineblockoptions'; ?>
		<div class="<?php echo $divClass; ?>">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_INFORMATION'); ?></span>
			<table class="acysms_blocktable">
				<tr>
					<td class="key">
						<label for="name">
							<?php echo JText::_('SMS_GROUP_NAME'); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[group][group_name]" id="name" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->group->group_name); ?>"/>
					</td>
				</tr>
				<tr>
					<td class="key">
						<label for="enabled">
							<?php echo JText::_('SMS_ENABLED'); ?>
						</label>
					</td>
					<td>
						<?php echo JHTML::_('acysmsselect.booleanlist', "data[group][group_published]", '', $this->group->group_published); ?>
					</td>
				</tr>
				<tr>
					<td>
						<label for="alias">
							<?php echo JText::_('SMS_ALIAS'); ?>
						</label>
					</td>
					<td>
						<input type="text" name="data[group][group_alias]" id="alias" class="inputbox" style="width:200px" value="<?php echo $this->escape(@$this->group->group_alias); ?>"/>
					</td>

				</tr>
				<tr>
					<td class="key">
						<label for="creator">
							<?php echo JText::_('SMS_CREATOR'); ?>
						</label>
					</td>
					<td>
						<input type="hidden" id="groupcreator" name="data[group][group_user_id]" value="<?php echo $this->escape(@$this->group->group_user_id); ?>"/>
						<?php echo '<span id="creatorname">'.@$this->group->group_creatorname.'</span>';
						if($this->app->isAdmin()){
							echo ' <a class="modal" title="'.JText::_('SMS_EDIT', true).'"  href="index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=user&amp;task=choosejoomuser&currentIntegration=acysms" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
						} ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('SMS_COLOUR'); ?>
					</td>
					<td>
						<?php echo $this->colorBox->displayAll('', 'data[group][group_color]', @$this->group->group_color); ?>
					</td>
				</tr>
			</table>
		</div>
		<div class="<?php echo $divClass; ?>">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_DESCRIPTION'); ?></span>
			<?php echo $this->editor->display(); ?>
		</div>

		<?php if($this->app->isAdmin()){ ?>
			<div class="acysmsblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_ACCESS_LEVEL'); ?></span>
				<?php echo $this->acltype->display('data[group][group_access_manage]', @$this->group->group_access_manage); ?>
			</div>
		<?php } ?>

		<div class="clr"></div>

		<input type="hidden" name="cid[]" value="<?php echo @$this->group->group_id; ?>"/>
		<input type="hidden" name="option" value="com_acysms"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
