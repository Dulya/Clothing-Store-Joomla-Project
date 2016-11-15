<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class FlippingBookControllerFilemanager extends JControllerLegacy {

	function __construct() {
		parent::__construct();
		$this->registerTask( 'save_uploaded_files', 'saveUploadedFiles' );
		$this->registerTask( 'rename_file', 'renameFile' );
		$this->registerTask( 'delete_file', 'deleteFile' );
		$this->registerTask( 'rename_folder', 'renameFolder' );
		$this->registerTask( 'delete_folder', 'deleteFolder' );
		$this->registerTask( 'create_folder', 'createFolder' );
	}

	function display() {
		$this->setRedirect(JRoute::_('index.php?option=com_flippingbook', false));
	}

	function &getModel() {
		return parent::getModel('FlippingBook', '', array('ignore_request' => true));
	}
	
	function renameFile () {
		JRequest::checkToken() or jexit( JText::_( 'JInvalid_Token' ) );
		$current_folder = urldecode( JRequest::getVar( 'folder', '', 'get', 'string' ) );
		$absolute_path = JPATH_SITE . urldecode( JRequest::getVar( 'folder', '', 'get', 'string' ) );
		$old_file_name = JPath::clean( $absolute_path . DIRECTORY_SEPARATOR . urldecode( JRequest::getVar( 'old_file_name', '', 'post', 'string' ) ) );
		$new_file_name = JPath::clean( $absolute_path . DIRECTORY_SEPARATOR . urldecode( JRequest::getVar( 'new_file_name', '', 'post', 'string' ) ) );
		$file_type = strtolower( substr( $new_file_name, -3 ) );
		$permitted_file_type = array ('jpg', 'peg', 'png', 'gif', 'bmp', 'swf');
		if ( in_array( $file_type, $permitted_file_type ) && $this->checkPath( $old_file_name ) && $this->checkPath( $new_file_name ) ) {
			$message = JFile::move( $old_file_name, $new_file_name );
			if ($message) {
				$message = JText::_( 'COM_FLIPPINGBOOK_THE_FILE_WAS_SUCCESSFULLY_RENAMED' );
			}
		} else {
			$message = JText::_( 'COM_FLIPPINGBOOK_THE_FILE_CAN_T_BE_RENAMED' );
		}
		$this->setRedirect('index.php?option=com_flippingbook&view=filemanager&folder='.$current_folder, $message);
	}
	
	function renameFolder () {
		JRequest::checkToken() or jexit( JText::_( 'JInvalid_Token' ) );
		$current_folder = urldecode( JRequest::getVar( 'folder', '', 'get', 'string' ) );
		$absolute_path = JPATH_SITE . urldecode( JRequest::getVar( 'folder', '', 'get', 'string' ) );
		$old_folder_name = JPath::clean( $absolute_path . DIRECTORY_SEPARATOR . urldecode( JRequest::getVar( 'old_folder_name', '', 'post', 'string' ) ) );
		$new_folder_name = JPath::clean( $absolute_path . DIRECTORY_SEPARATOR . urldecode( JRequest::getVar( 'new_folder_name', '', 'post', 'string' ) ) );
		if ( $this->checkPath( $old_folder_name ) && $this->checkPath( $new_folder_name ) ) {
			$message = JFolder::move( $old_folder_name, $new_folder_name );
			if ( $message )
				$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_WAS_SUCCESSFULLY_RENAMED' );
		} else
			$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_CAN_T_BE_RENAMED' );
		$this->setRedirect('index.php?option=com_flippingbook&view=filemanager&folder='.$current_folder, $message);
	}
	
	function deleteFolder () {
		JRequest::checkToken() or jexit( JText::_( 'JInvalid_Token' ) );
		$current_folder = urldecode( JRequest::getVar( 'folder', '', 'post', 'string' ) );
		$absolute_path = JPATH_SITE . urldecode( JRequest::getVar( 'folder', '', 'post', 'string' ));
		$folder_to_delete = JPath::clean( $absolute_path . DIRECTORY_SEPARATOR . urldecode( JRequest::getVar( 'folder_to_delete', '', 'post', 'string' ) ) );
		if ( $this->checkPath( $folder_to_delete ) ) {
			if (JFolder::delete ( $folder_to_delete ) ) {
				$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_WAS_SUCCESSFULLY_REMOVED' );
			} else
				$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_CAN_T_BE_DELETED' );
		} else
			$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_CAN_T_BE_DELETED' );
		$this->setRedirect('index.php?option=com_flippingbook&view=filemanager&folder='.$current_folder, $message);
	}
	
	function deleteFile () {
		JRequest::checkToken() or jexit( JText::_( 'JInvalid_Token' ) );
		$current_folder = urldecode( JRequest::getVar( 'folder', '', 'post', 'string' ) );
		$absolute_path = JPATH_SITE . urldecode(JRequest::getVar( 'folder', '', 'post', 'string' ) );
		$file_to_delete = JPath::clean( $absolute_path . DIRECTORY_SEPARATOR . urldecode( JRequest::getVar( 'file_to_delete', '', 'post', 'string' ) ) );
		if ( $this->checkPath( $file_to_delete ) ) {
			if ( JFile::delete ( $file_to_delete ) ) {
				$message = JText::_( 'COM_FLIPPINGBOOK_THE_FILE_WAS_SUCCESSFULLY_REMOVED' );
			} else {
				$message = JText::_( 'COM_FLIPPINGBOOK_THE_FILE_CAN_T_BE_REMOVED' );
			}
		} else {
			$message = JText::_( 'COM_FLIPPINGBOOK_THE_FILE_CAN_T_BE_REMOVED' );
		}
		$this->setRedirect('index.php?option=com_flippingbook&view=filemanager&folder='.$current_folder, $message);
	}
	
	function createFolder () {
		JRequest::checkToken() or jexit( JText::_( 'JInvalid_Token' ) );
		$current_folder = urldecode( JRequest::getVar( 'folder', '', 'get', 'string' ) );
		$absolute_path = JPATH_SITE . urldecode( JRequest::getVar( 'folder', '', 'get', 'string' ) );
		$name_of_new_folder = urldecode( JRequest::getVar( 'name_of_new_folder', '', 'post', 'string' ) );
		$folder_to_create = JPath::clean( $absolute_path . DIRECTORY_SEPARATOR . $name_of_new_folder );

		if ( $this->checkPath( $folder_to_create ) ) {
			if ( JFolder::create ( $folder_to_create ) ) {
				$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_WAS_SUCCESSFULLY_CREATED' );
				// create blank index.html file to prevent listing of files
				$data = '<html><body bgcolor="#FFFFFF"></body></html>';
				JFile::write( $folder_to_create.DIRECTORY_SEPARATOR."index.html", $data );
			} else
				$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_CAN_T_BE_CREATED' );
		} else
			$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_CAN_T_BE_CREATED' );
		$this->setRedirect( 'index.php?option=com_flippingbook&view=filemanager&folder='.$current_folder, $message );
	}
	
	function saveUploadedFiles () {
		JRequest::checkToken() or jexit( JText::_( 'JInvalid_Token' ) );
		$current_folder = urldecode( JRequest::getVar( 'folder', '', 'get', 'string' ) );
		$absolute_path = JPATH_SITE . $current_folder;
		if ( $this->checkPath( $absolute_path ) === false ) {
			$current_folder = $absolute_path = JPATH_SITE . DIRECTORY_SEPARATOR . 'images';
		}
		$uploaded_files = JRequest::getVar( 'upload', null, 'files', 'array' );
		
		for ($i = 0; $i < count( $uploaded_files ); $i++) {
				if ( @$uploaded_files['name'][$i] != '' ) {
				$destination_file_path = $absolute_path . DIRECTORY_SEPARATOR . $uploaded_files['name'][$i];
				$temporary_name = $uploaded_files['tmp_name'][$i];
				$file_type = strtolower( substr( $uploaded_files['name'][$i], -3 ) );
				$permitted_file_type = array ( 'jpg', 'peg', 'png', 'gif', 'bmp', 'swf' );
				if ( in_array( $file_type, $permitted_file_type ) ) {
					if ( !JFile::upload( $temporary_name, $destination_file_path ) ) {
						$message .= $uploaded_files['name'][$i] . " - " . WARNFS_ERR02 . "<br />";
					}
				} else {
					$message .= $uploaded_files['name'][$i] . " - " . JText::_( 'COM_FLIPPINGBOOK_UNALLOWED_FILE_TYPE' ) . "<br />";
				}
			}
		}
		if ( $message != "" ) {
			$message = '<table style="margin-left: 30px;"><tr><td>' . $message . '</td></tr></table>';
		}
		if ( JRequest::getVar( 'upload_more_files', '', 'post', '' ) ) {
			$this->setRedirect('index.php?option=com_flippingbook&view=filemanager&layout=upload_files&folder='.$current_folder, $message);
		} else {
			$this->setRedirect('index.php?option=com_flippingbook&view=filemanager&folder='.$current_folder, $message);
		}
	}
	
	// operate with [joomla root]/images/ folder and subfolders only
	function checkPath ($path) {
		$base_images_folder = DIRECTORY_SEPARATOR . 'images';
		$permitted_path = JPath::clean( JPATH_SITE . $base_images_folder );
		if ( ( substr( $path, 0, strlen( $permitted_path ) ) == $permitted_path ) && ( strpos( $path, '..' ) === false ) ) {
			return true;
		} else {
			return false;
		}
	}
}