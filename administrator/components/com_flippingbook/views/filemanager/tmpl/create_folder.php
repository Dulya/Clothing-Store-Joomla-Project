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

		$current_folder = JRequest::getVar( 'folder', '', 'get', 'string' );
?><fieldset class="adminform"><legend><?php echo JText::_( 'COM_FLIPPINGBOOK_CREATE_A_NEW_FOLDER' ); ?></legend>
	<script language="JavaScript">
		function submit_this_form() {
			if (document.getElementById('name_of_new_folder').value == '')
				alert ('<?php echo JText::_( 'COM_FLIPPINGBOOK_ENTER_THE_NAME_OF_THE_NEW_FOLDER'); ?>');
			else
				document.createfolder.submit();
		}
	</script>

	<form action="index.php?option=com_flippingbook&task=filemanager.create_folder&folder=<?php echo urlencode($current_folder); ?>" method="post" name="createfolder">
		<?php echo JText::_( 'COM_FLIPPINGBOOK_ENTER_THE_NAME_OF_THE_NEW_FOLDER' ); ?><br />
		<input name="name_of_new_folder" id="name_of_new_folder" type="text" value="new_folder" class="fb_rename_field" />
		<input type="hidden" name="current_folder" value="<?php echo urlencode($current_folder); ?>" />
		<a id="saveButton" class="fb_button" href="javascript:submit_this_form();"><?php echo JText::_( 'COM_FLIPPINGBOOK_CREATE' ); ?></a>
		<a id="cancelButton" class="fb_button" href="index.php?option=com_flippingbook&view=filemanager&folder=<?php echo urlencode($current_folder); ?>"><?php echo JText::_( 'COM_FLIPPINGBOOK_CANCEL' ); ?></a>
		<?php echo JHtml::_('form.token'); ?>
	</form>
</fieldset>
FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a>