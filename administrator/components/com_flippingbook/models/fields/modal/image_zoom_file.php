<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.form.formfield');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

class JFormFieldModal_Image_Zoom_File extends JFormField {
	
	protected $type = 'Modal_Image_Zoom_File';
	
	protected function getInput() {
		$files[] = JHTML::_('select.option', '', '- '. JText::_( 'COM_FLIPPINGBOOK_SELECT_FILE' ) .' -');
		$files[] = JHTML::_( 'select.option',  '<OPTGROUP>', DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR );
		$image_files_root = JFolder::files( JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR, '.(jpg|jpeg|gif|png|swf|JPG|JPEG|GIF|PNG|SWF)$' );
			if ( count( $image_files_root ) > 0 ) {
				foreach ( $image_files_root as $file ) {
					$path_for_db = strtr($file, "\\", "/");
					$path_for_db = preg_replace ('/^\/images\//', '', $path_for_db);
					$files[] = JHTML::_('select.option', $path_for_db, $file);
				}
			}
		$files[] = JHTML::_( 'select.option',  '</OPTGROUP>' );
		$folders = JFolder::listFolderTree (JPATH_ROOT . DIRECTORY_SEPARATOR . 'images', '', 10);
		if ( count( $folders ) > 0) {
			foreach ( $folders as $folder ) {
				$files[] = JHTML::_( 'select.option',  '<OPTGROUP>', $folder["relname"] . DIRECTORY_SEPARATOR );
				$image_files = JFolder::files( JPATH_SITE.$folder["relname"], '.(jpg|jpeg|gif|png|swf|JPG|JPEG|GIF|PNG|SWF)$' );
				if ( count( $image_files ) > 0 ) {
					foreach ( $image_files as $file ) {
						$path_for_db = strtr( $folder["relname"] . DIRECTORY_SEPARATOR . $file, "\\", "/" );
						$path_for_db = preg_replace ( '/^\/images\//', '', $path_for_db );
						$files[] = JHTML::_( 'select.option', $path_for_db, $file );
					}
				}
				$files[] = JHTML::_('select.option',  '</OPTGROUP>' );
			}
		}
		return JHTML::_('select.genericlist', $files, 'jform[zoom_url]', 'class="inputbox" size="1" onchange="update_fields_state()"', 'value', 'text', $this->value);
	}
}