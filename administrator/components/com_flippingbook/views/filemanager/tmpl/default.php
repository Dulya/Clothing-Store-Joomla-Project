<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

		jimport('joomla.filesystem.folder');
		JHTML::_('behavior.modal', 'a.modal');

		$document = JFactory::getDocument();
		$document->addStyleSheet('../administrator/components/com_flippingbook/css/common.css');
?>

<?php
	$base_images_folder = DIRECTORY_SEPARATOR . 'images';
	$folder = JRequest::getVar( 'folder', '', '', 'string' );
	if ($folder == '') 
		$folder = $base_images_folder;
	// protection from watching files in another folders
	if ( substr( $folder, 0, 7 ) != $base_images_folder )
		$folder = $base_images_folder;
	
	$path = JPATH_ROOT . $folder;
	$filter = '.';
	$recurse = false;
	$fullpath = false;
	$files = JFolder::files($path, $filter, $recurse, $fullpath);
	$folders = JFolder::folders($path, $filter, $recurse, $fullpath);
?>
	<a href="index.php?option=com_flippingbook&view=filemanager&layout=create_folder&folder=<?php echo $folder; ?>" id="new_folder_link" class="fb_button"><?php echo JText::_( 'COM_FLIPPINGBOOK_CREATE_A_NEW_FOLDER' ); ?></a>
	<a href="index.php?option=com_flippingbook&view=filemanager&layout=upload_files&folder=<?php echo $folder; ?>" id="upload_files_link" class="fb_button"><?php echo JText::_( 'COM_FLIPPINGBOOK_UPLOAD_FILES' ); ?></a>
	<div id="fb_current_folder">
<?php 	echo JText::_( 'COM_FLIPPINGBOOK_CURRENT_FOLDER' );
			$current_folder_array = explode( "\\", $folder );
			if ( count($current_folder_array) > 0) {
				$path_for_link = "";
				foreach ( $current_folder_array as $current_folder_part ) {
					$path_for_link .= $current_folder_part;
					echo '<a href="index.php?option=com_flippingbook&view=filemanager&folder=' . urlencode( $path_for_link ) . '">' . $current_folder_part . '</a>&nbsp;' . DIRECTORY_SEPARATOR . '&nbsp;';
					$path_for_link .= DIRECTORY_SEPARATOR;
				}
			}
?>
		</a>
	</div>
	<table class="table table-striped" id="articleList">
		<tr>
			<th align="left"><?php echo JText::_( 'COM_FLIPPINGBOOK_NAME' ); ?></th>
			<th align="center" width="32" colspan="2"><?php echo JText::_( 'COM_FLIPPINGBOOK_ACTION' ); ?></th>
			<th align="center" width="90"><?php echo JText::_( 'COM_FLIPPINGBOOK_SIZE' ); ?></th>
			<th align="center" width="150"><?php echo JText::_( 'COM_FLIPPINGBOOK_MODIFIED' ); ?></th>
		</tr>
<?php
	$folders = JFolder::listFolderTree (JPATH_ROOT . $folder, '', 1);
	$i = 0;
	if ( count($folders) > 0 ) {
		foreach ($folders as $folders_) {
			$relname = str_replace ($base_images_folder . DIRECTORY_SEPARATOR, '', $folders_["relname"]);
			$folder_name = urldecode( $relname );
			if ( strrchr( $folder_name, DIRECTORY_SEPARATOR ) != false ) {
				$folder_name = stripslashes( strrchr( $folder_name, DIRECTORY_SEPARATOR ));
			}
?>
	<tr class="row<?php if ( $i%2 == 0 ) echo "0"; else echo "1"; ?>">
		<td nowrap="nowrap" class="folder_td">
			<a href="index.php?option=com_flippingbook&view=filemanager&folder=<?php echo urlencode ( $folders_["relname"] ); ?>" class="folder_row"><?php echo $folder_name; ?></a>
		</td>
		<td align="center" width="16">
			<a href="index.php?option=com_flippingbook&view=filemanager&layout=rename_folder&folder_to_rename=<?php echo urlencode ( $folder_name ); ?>&folder=<?php echo urlencode ( $folder ); ?>" title="<?php echo JText::_( 'COM_FLIPPINGBOOK_RENAME' ); ?>"><img src="components/com_flippingbook/images/rename.png" alt="<?php echo JText::_( 'COM_FLIPPINGBOOK_RENAME' ); ?>"></a>
		</td>
		<td align="center" width="16">
			<a href="javascript:void(0)" onclick="fb_delete_folder('<?php echo urlencode ( $folder_name ); ?>', '<?php echo urlencode ( $folder ); ?>')" title="<?php echo JText::_( 'COM_FLIPPINGBOOK_DELETE' ); ?>"><img src="components/com_flippingbook/images/remove.png" alt="<?php echo JText::_( 'COM_FLIPPINGBOOK_DELETE' ); ?>"></a>
		</td>
		<td align="center"></td>
		<td align="center"></td>
	</tr>
<?php
			$i++;
		}
	}
	if ( count( $files ) > 0 ) {
		foreach ( $files as $file ) {
?>
		<tr class="row<?php if ( $i%2 == 0 ) echo "0"; else echo "1"; ?>">
			<td nowrap="nowrap">
				<a class="modal" href="index.php?option=com_flippingbook&amp;view=filemanager&amp;layout=preview_image&amp;tmpl=component&amp;file=<?php echo urlencode ( $folder . DIRECTORY_SEPARATOR . $file ); ?>" rel="{handler: 'iframe', size: {x: 800, y: 600}}"><?php echo $file; ?></a>
			</td>
			<td align="center" width="16">
				<a href="index.php?option=com_flippingbook&view=filemanager&layout=rename_file&file_to_rename=<?php echo urlencode ( $file ); ?>&folder=<?php echo urlencode ( $folder ); ?>" title="<?php echo JText::_( 'COM_FLIPPINGBOOK_RENAME' ); ?>"><img src="components/com_flippingbook/images/rename.png" alt="<?php echo JText::_( 'COM_FLIPPINGBOOK_RENAME' ); ?>"></a>
			</td>
			<td align="center" width="16">
				<a href="javascript:void(0)" onclick="fb_delete_file('<?php echo urlencode ( $file ); ?>', '<?php echo urlencode ( $folder ); ?>')" title="<?php echo JText::_( 'COM_FLIPPINGBOOK_DELETE' ); ?>"><img src="components/com_flippingbook/images/remove.png" alt="<?php echo JText::_( 'COM_FLIPPINGBOOK_DELETE' ); ?>"></a>
			</td>
			<td align="center">
				<?php echo number_format ( filesize ($path . DIRECTORY_SEPARATOR . $file), 0, ' ', ' ' ); ?>
			</td>
			<td align="center">
				<?php echo date ( "Y-m-d H:i:s", filemtime ($path . DIRECTORY_SEPARATOR . $file)); ?>
			</td>
		</tr>
<?php
			$i++;
		}
	}
?>
	</table>
	<script type= "text/javascript">
		function fb_delete_folder ( folder_to_delete, folder ) {
			if( confirm( '<?php echo JText::_( 'COM_FLIPPINGBOOK_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_FOLDER' ); ?>' ) ) {
				document.forms["delete_folder"].folder_to_delete.value = folder_to_delete;
				document.forms["delete_folder"].folder.value = folder;
				document.forms["delete_folder"].submit();
			}
		}
		function fb_delete_file ( file, folder ) {
			if( confirm( '<?php echo JText::_( 'COM_FLIPPINGBOOK_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_FILE' ); ?>' ) ) {
				document.forms["delete_file"].file_to_delete.value = file;
				document.forms["delete_file"].folder.value = folder;
				document.forms["delete_file"].submit();
			}
		}
	</script>
	<form action="index.php?option=com_flippingbook&task=filemanager.delete_folder" method="post" name="delete_folder">
		<input name="folder_to_delete" type="hidden" value="" />
		<input name="folder" type="hidden" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
	<form action="index.php?option=com_flippingbook&task=filemanager.delete_file" method="post" name="delete_file">
		<input name="file_to_delete" type="hidden" value="" />
		<input name="folder" type="hidden" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
<div class="span12" style="font-size: 80%; line-height: 120%; margin: 20px 0 0 0;">
	FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
	<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/faqs/" target="_blank">FAQ</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a><br />
	<div style="padding: 10px 0 20px 0;">Copyright &copy; 2012 Mediaparts Interactive. All rights reserved.</div>
</div>