<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

?>
<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function(task)
	{
			Joomla.submitform(task,document.getElementById('category-form'));
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_flippingbook'); ?>" method="post" name="adminForm" id="category-form" class="form-validate form-horizontal>
	<div class="row-fluid">
		<div class="span12">
			<fieldset class="form-horizontal">
				<legend><?php echo empty($this->item->id) ? JText::_('COM_FLIPPINGBOOK_NEW_CATEGORY') : JText::sprintf('COM_FLIPPINGBOOK_EDIT_CATEGORY', $this->item->id); ?></legend>
				<?php foreach ($this->form->getFieldset('category_edit') as $field) { ?>
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