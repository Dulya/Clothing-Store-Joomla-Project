<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');
jimport('joomla.filesystem.folder');
jimport( 'joomla.filesystem.file' );

class FlippingBookControllerBatchaddingpages extends JControllerForm {
	function __construct() {
		parent::__construct();
		$this->registerTask( 'apply', 'saveFiles' );
		$this->registerTask( 'save', 'saveFiles' );
	}
	
	function saveFiles () {
		JRequest::checkToken() or jexit( JText::_( 'JInvalid_Token' ) );
		$db = JFactory::getDbo();
		$data = JRequest::getVar( 'jform', array(), 'post', 'array' );
		$book_id = $data['request']['id'];

		// Get the files from the selected folder
		$path = JPATH_SITE . $data['folder'];
		$filter = '.jpg$|.jpeg$|.png$|.gif$|.swf$|.JPG$|.JPEG$|.PNG$|.GIF$|.SWF$|.Jpg$|.Jpeg$|.Png$|.Gif$|.Swf$';
		$recurse = false;
		$fullpath = false;
		$files = JFolder::files( JPATH_SITE.$data['folder'], $filter, $recurse, $fullpath );
		if ( count( $files ) == 0 )  {
			$message = JText::_( 'COM_FLIPPINGBOOK_THE_FOLDER_DOESN_T_CONTAIN_IMAGES' );
			$this->setRedirect( 'index.php?option=com_flippingbook&view=batchaddingpages', $message);
			return;
		}

		//Get the last page number in the selected book
		$query = 'SELECT MAX(ordering)' . ' FROM #__flippingbook_pages WHERE book_id=' . $book_id;
		$db->setQuery( $query );
		$last_page_number = $db->loadResult();

		//Adding all jpg and swf files into the book
		if ( $data['mode'] == 'simple' ) {
			$i = $last_page_number + 1;
			foreach ( $files as $file ) {
				$path_for_db = $data['folder'] . $file;
				$path_for_db = strtr($path_for_db, "\\", "/");
				$path_for_db = preg_replace ('/^\/images\//', '', $path_for_db);
				$query = "INSERT INTO #__flippingbook_pages (file, book_id, ordering, state) VALUES('" . $path_for_db . "', " . $book_id . ", " . $i . ", 1);";
				$db->setQuery( $query );
				if (!$db->query()) {
					return JError::raiseWarning( 500, $row->getError() );
				}
				$i++;
			}
		}

		//Advanced adding files to the book
		if ( $data['mode'] == 'advanced') {
			$i = $last_page_number + 1;
			$allowed_extensions = array ('jpg', 'jpeg', 'png', 'gif', 'swf', 'JPG', 'JPEG', 'PNG', 'GIF' , 'SWF' , 'Jpg', 'Jpeg' ,'Png' ,'Gif' ,'Swf');
			$message = '';
			foreach ($files as $file) {
				if (preg_match ("/^" . $data['prefix_page'] . "[0-9]+[.jpg$|.jpeg$|.png$|.gif$|.swf$]/i", $file)) {
					preg_match ("/^" . $data['prefix_page'] . "([0-9]+).(jpg$|jpeg$|png$|gif$|swf$)/i", $file, $matches);
					$name_after_prefix = $matches[1];
					$file_extension = $matches[2];

					if (JFile::exists(JPATH_ROOT . $data['folder'] . $file)) { // file for normal page size
						$zoom_path_for_db = '';
						$path_for_db = $data['folder'] . $file;
						$path_for_db = strtr($path_for_db, "\\", "/");
						$path_for_db = preg_replace ('/^\/images\//', '', $path_for_db);
						$zoom_file_name = $data['folder'] . $data['prefix_page'] . $data['prefix_zoom'] . $name_after_prefix . 	"." . $file_extension ;
						if (JFile::exists(JPATH_ROOT . $zoom_file_name)) { // file for zoomed state
							$zoom_path_for_db = $zoom_file_name;
							$zoom_path_for_db = strtr($zoom_path_for_db, "\\", "/");
							$zoom_path_for_db = preg_replace ('/^\/images\//', '', $zoom_path_for_db);
							} else {
								$message .= $zoom_file_name . " " . JText::_('COM_FLIPPINGBOOK_FILE_NOT_FOUND') . "<br />";
						}
						$query = "INSERT INTO #__flippingbook_pages (file, book_id, ordering, state, zoom_url) VALUES('" . $path_for_db . "', " . $book_id . ", " . $i . ", 1, '" . $zoom_path_for_db . "');";
						$db->setQuery( $query );
						if (!$db->query()) {
							return JError::raiseWarning( 500, $row->getError() );
						}
						$i++;
					} else {
						$message .= $data['folder'] . $file . " " . JText::_('COM_FLIPPINGBOOK_FILE_NOT_FOUND') . "<br />";
					}
				}
			}
		}
		
		$total_created_pages = $i - $last_page_number - 1;
		$message .= $total_created_pages . " " . JText::_('COM_FLIPPINGBOOK_PAGES_SUCCESSFULLY_CREATED');

		if ( $this->task == 'apply' ) {
			$this->setRedirect( 'index.php?option=com_flippingbook&view=batchaddingpages', $message);
		} else {
			$this->setRedirect( 'index.php?option=com_flippingbook', $message);
		}
	}
}