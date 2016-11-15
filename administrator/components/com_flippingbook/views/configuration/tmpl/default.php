<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function(task)
	{
			Joomla.submitform(task,document.getElementById('configuration-form'));
	}
	
	function resetConfiguration() {
		var rigidPageSpeed = document.getElementById('jform_rigidPageSpeed');
		rigidPageSpeed.value = '5';
		var closeSpeed = document.getElementById('jform_closeSpeed');
		closeSpeed.value = '3';
		var moveSpeed = document.getElementById('jform_moveSpeed');
		moveSpeed.value = '2';
		var gotoSpeed = document.getElementById('jform_gotoSpeed');
		gotoSpeed.value = '3';
		var gotoSpeed = document.getElementById('jform_columns');
		gotoSpeed.value = '2';
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_flippingbook&task=configuration.save'); ?>" method="post" name="adminForm" id="configuration-form" class="form-validate">
	<div class="row-fluid">
		<div class="span6">
			<fieldset class="form-horizontal">
				<legend><?php echo JText::_('COM_FLIPPINGBOOK_COMPONENT_SETTINGS'); ?></legend>
				<?php foreach ($this->form->getFieldset('component_settings') as $field) { ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
				<?php } ?>
				<a class="btn btn-small" onclick="javascript:resetConfiguration();return void(0);"><i class="icon-refresh"></i> <?php echo JText::_( 'COM_FLIPPINGBOOK_RESTORE_DEFAULT_SETTINGS' );?></a>
			</fieldset>
		</div>
		<div class="span6">
			<fieldset class="form-horizontal">
				<legend><?php echo JText::_('COM_FLIPPINGBOOK_CATEGORIES_LIST_SETTINGS'); ?></legend>
				<?php foreach ($this->form->getFieldset('categories_list_settings') as $field) { ?>
				<div class="control-group">
					<div class="control-label"><?php echo $field->label; ?></div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
				<?php } ?>
			</fieldset>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="span12" style="font-size: 80%; line-height: 120%; margin: 20px 0 0 0;">
	FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
	<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/faqs/" target="_blank">FAQ</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a><br />
	<div style="padding: 10px 0 20px 0;">Copyright &copy; 2012 Mediaparts Interactive. All rights reserved.</div>
</div>