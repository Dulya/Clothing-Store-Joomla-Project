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

class JFormFieldModal_Page_Flip_Sound extends JFormField {
	
	protected $type = 'Modal_Page_Flip_Sound';
	
	protected function getInput() {
	
		$Folder = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_flippingbook' . DIRECTORY_SEPARATOR . 'sounds';
		$files = JFolder::files( $Folder, '.mp3$' );
		$page_flip_soundFile[] = JHTML::_( 'select.option', "", JText::_( 'COM_FLIPPINGBOOK_NONE' ) );
		if ( count( $files ) > 0 ) {
			foreach ( $files as $file )
				$page_flip_soundFile[] = JHTML::_( 'select.option', $file, $file );
		}
		return JHTML::_( 'select.genericlist',  $page_flip_soundFile, 'jform[flipSound]', 'class="inputbox" size="1"', 'value', 'text', $this->value );
	}
}