<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

		jimport('joomla.filesystem.folder');

		$document = JFactory::getDocument();
		$document->addStyleSheet('../administrator/components/com_flippingbook/css/common.css');
		
		$file_to_rename = urldecode(JRequest::getVar( 'file_to_rename', '', 'get', 'string' ));
		$current_folder = JRequest::getVar( 'folder', '', 'get', 'string' );
?><fieldset class="adminform"><legend><?php echo JText::_( 'COM_FLIPPINGBOOK_RENAME_FILE' ); ?></legend>
	<script language="JavaScript">
		function submit_this_form() {
			if (document.getElementById('new_file_name').value == '')
				alert ('<?php echo JText::_( 'COM_FLIPPINGBOOK_ENTER_A_NEW_NAME'); ?>');
			else
				document.renamefile.submit();
		}
	</script>

	<form action="index.php?option=com_flippingbook&task=filemanager.rename_file&folder=<?php echo urlencode($current_folder); ?>" method="post" name="renamefile">
		<?php echo JText::_( 'COM_FLIPPINGBOOK_NEW_NAME' ); ?><br />
		<input name="new_file_name" id="new_file_name" type="text" value="<?php echo $file_to_rename; ?>" class="fb_rename_field" />
		<input name="old_file_name" type="hidden" value="<?php echo $file_to_rename; ?>" />
		<input type="hidden" name="current_folder" value="<?php echo urlencode($current_folder); ?>" />
		<a id="saveButton" class="fb_button" href="javascript:submit_this_form();"><?php echo JText::_( 'COM_FLIPPINGBOOK_SAVE' ); ?></a>
		<a id="cancelButton" class="fb_button" href="index.php?option=com_flippingbook&view=filemanager&folder=<?php echo urlencode($current_folder); ?>"><?php echo JText::_( 'COM_FLIPPINGBOOK_CANCEL' ); ?></a>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</fieldset>
FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a>