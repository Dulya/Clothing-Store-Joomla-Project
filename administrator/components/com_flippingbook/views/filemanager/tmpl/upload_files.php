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

		$current_folder = urldecode( JRequest::getVar( 'folder', '', 'get', 'string' ) );
?>
<script language="JavaScript">
	function submit_this_form() {
		document.uploadform.submit();
	}
</script>
<form action="index.php?option=com_flippingbook&task=filemanager.save_uploaded_files&folder=<?php echo urlencode( $current_folder ); ?>" method="post" name="uploadform" enctype="multipart/form-data">
	<?php echo @$report; ?>
	<fieldset class="adminform">
		<legend><?php echo JText::_( 'COM_FLIPPINGBOOK_UPLOAD_FILES' ); ?></legend>
		<?php echo JText::_( 'COM_FLIPPINGBOOK_UPLOAD_FILES_DESC' ); ?>
		<table>
			<tr>
				<td><input type="file" name="upload[]" class="fb_select_file"></td>
			</tr>
			<tr>
				<td><input type="file" name="upload[]" class="fb_select_file"></td>
			</tr>
			<tr>
				<td><input type="file" name="upload[]" class="fb_select_file"></td>
			</tr>
			<tr>
				<td><input type="file" name="upload[]" class="fb_select_file"></td>
			</tr>
			<tr>
				<td><input type="file" name="upload[]" class="fb_select_file"></td>
			</tr>
			<tr>
				<td><label><input type="checkbox" name="upload_more_files" id="upload_more_files" /><?php echo JText::_( 'COM_FLIPPINGBOOK_RETURN_TO_THIS_PAGE_AFTER_UPLOADING' ); ?></label></td>
			</tr>
			<tr>
				<td><a id="uploadButton" class="fb_button" href="javascript:submit_this_form();"><?php echo JText::_( 'COM_FLIPPINGBOOK_UPLOAD' ); ?></a><a id="cancelButton" class="fb_button" href="index.php?option=com_flippingbook&view=filemanager&folder=<?php echo urlencode($current_folder); ?>"><?php echo JText::_( 'COM_FLIPPINGBOOK_CANCEL' ); ?></a></td>
			</tr>
		</table>
	</fieldset>
	<input type="hidden" name="MAX_FILE_SIZE" value="16777216">
	<input type="hidden" name="current_folder" value="<?php echo urlencode($current_folder); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a>