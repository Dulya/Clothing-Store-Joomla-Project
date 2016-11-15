<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');
?>
<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function(task)
	{
			Joomla.submitform(task,document.getElementById('batchaddpages-form'));
	}
</script>
<form action="<?php echo JRoute::_('index.php?option=com_flippingbook&task=batchaddpages.save'); ?>" method="post" name="adminForm" id="batchaddpages-form" class="form-validate">
	<div class="span12">
		<fieldset class="form-horizontal">
			<legend><?php echo JText::_('COM_FLIPPINGBOOK_COMPONENT_SETTINGS'); ?></legend>
			<?php foreach ($this->form->getFieldset('batch_add_pages') as $field) { ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
			<?php } ?>
		</fieldset>
		<fieldset class="form-horizontal" id="adv_mode_panel">
			<legend><?php echo JText::_('COM_FLIPPINGBOOK_ADVANCED_MODE_SETTINGS'); ?></legend>
			<?php foreach ($this->form->getFieldset('batch_add_pages_advanced') as $field) { ?>
			<div class="control-group">
				<div class="control-label"><?php echo $field->label; ?></div>
				<div class="controls"><?php echo $field->input; ?></div>
			</div>
			<?php } ?>
			<img src="components/com_flippingbook/images/batch_help.gif" width="740" height="310" />
		</fieldset>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<script language="javascript" type="text/javascript">
	var method_obj = document.getElementById("jform_mode");
	var adv_mode_panel = document.getElementById("adv_mode_panel");
	
	function check_method() {
		if (method_obj.selectedIndex == 0) {
			adv_mode_panel.style.display = "none";
		} else {
			adv_mode_panel.style.display = 'block';
		}
	}
	
	check_method();
</script>
<div class="span12" style="font-size: 80%; line-height: 120%; margin: 20px 0 0 0;">
	FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
	<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/faqs/" target="_blank">FAQ</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a><br />
	<div style="padding: 10px 0 20px 0;">Copyright &copy; 2012 Mediaparts Interactive. All rights reserved.</div>
</div>