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
			Joomla.submitform(task,document.getElementById('page-form'));
	}
</script>

<form action="<?php JRoute::_('index.php?option=com_flippingbook'); ?>" method="post" name="adminForm" id="page-form" class="form-validate">
	<div class="row-fluid">
		<div class="span6">
			<fieldset class="form-horizontal">
				<legend><?php echo empty($this->item->id) ? JText::_('COM_FLIPPINGBOOK_NEW_PAGE') : JText::sprintf('COM_FLIPPINGBOOK_EDIT_PAGE', $this->item->id); ?></legend>
				<?php foreach ($this->form->getFieldset('book_page') as $field) { ?>
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
<script language="javascript" type="text/javascript">
	var jform_zoom_width_lbl = document.getElementById("jform_zoom_width-lbl");
	var jform_zoom_height_lbl = document.getElementById("jform_zoom_height-lbl");
	var jformzoom_url = document.getElementById("jformzoom_url");
	
	function update_fields_state() {	
	var file_ext = jformzoom_url.value.substring( jformzoom_url.value.length-3, jformzoom_url.value.length );
 	if ( ( file_ext == 'swf' ) || ( file_ext == 'SWF') ) {
			jform_zoom_width_lbl.parentNode.parentNode.style.display = "block";
			jform_zoom_height_lbl.parentNode.parentNode.style.display = "block";
		} else {
			jform_zoom_width_lbl.parentNode.parentNode.style.display = "none";
			jform_zoom_height_lbl.parentNode.parentNode.style.display = "none";
		}
	}
	
	update_fields_state();
</script>
<div class="span12" style="font-size: 80%; line-height: 120%; margin: 20px 0 0 0;">
	FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
	<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/faqs/" target="_blank">FAQ</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a><br />
	<div style="padding: 10px 0 20px 0;">Copyright &copy; 2012 Mediaparts Interactive. All rights reserved.</div>
</div>