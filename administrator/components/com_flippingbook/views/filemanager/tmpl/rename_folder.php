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
		
		$folder_to_rename = urldecode(JRequest::getVar( 'folder_to_rename', '', 'get', 'string' ));
		$current_folder = JRequest::getVar( 'folder', '', 'get', 'string' );
?><fieldset class="adminform"><legend><?php echo JText::_( 'COM_FLIPPINGBOOK_RENAME_FOLDER' ); ?></legend>
	<script language="JavaScript">
		function submit_this_form() {
			if (document.getElementById('new_folder_name').value == '')
				alert ('<?php echo JText::_( 'COM_FLIPPINGBOOK_ENTER_A_NEW_NAME'); ?>');
			else
				document.renamefolder.submit();
		}
	</script>

	<form action="index.php?option=com_flippingbook&task=filemanager.rename_folder&folder=<?php echo urlencode($current_folder); ?>" method="post" name="renamefolder">
		<?php echo JText::_( 'COM_FLIPPINGBOOK_NEW_NAME' ); ?><br />
		<?php 
			$folder_name = urldecode( $folder_to_rename );
			if ( strrchr( $folder_name, '\\' ) != false ) {
				$folder_name = stripslashes( strrchr( $folder_name, '\\' ));
			}
		?>
		<input name="new_folder_name" id="new_folder_name" type="text" value="<?php echo $folder_name; ?>" class="fb_rename_field" />
		<input name="old_folder_name" type="hidden" value="<?php echo $folder_to_rename; ?>" />
		<input type="hidden" name="current_folder" value="<?php echo urlencode($current_folder); ?>" />
		<a id="saveButton" class="fb_button" href="javascript:submit_this_form();"><?php echo JText::_( 'COM_FLIPPINGBOOK_SAVE' ); ?></a>
		<a id="cancelButton" class="fb_button" href="index.php?option=com_flippingbook&view=filemanager&folder=<?php echo urlencode($current_folder); ?>"><?php echo JText::_( 'COM_FLIPPINGBOOK_CANCEL' ); ?></a>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</fieldset>
FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a>