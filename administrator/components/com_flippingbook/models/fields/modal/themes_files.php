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

class JFormFieldModal_Themes_Files extends JFormField {
	
	protected $type = 'Modal_Themes_Files';
	
	protected function getInput() {
	
		$themesFolder = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_flippingbook' . DIRECTORY_SEPARATOR . 'css';
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$themesFiles = JFolder::files( $themesFolder, '.css$' );
		$themeFile[] = JHTML::_( 'select.option', "", JText::_( 'COM_FLIPPINGBOOK_NONE' ) );
		if ( count( $themesFiles ) > 0 ) {
			foreach ( $themesFiles as $file )
				$themeFile[] = JHTML::_( 'select.option', $file, $file );
		}
		return JHTML::_( 'select.genericlist',  $themeFile, 'jform[theme]', 'class="inputbox" size="1"', 'value', 'text', $this->value );
	}
}