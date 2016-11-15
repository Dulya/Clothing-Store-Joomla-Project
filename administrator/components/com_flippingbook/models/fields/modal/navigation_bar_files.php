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

class JFormFieldModal_Navigation_Bar_Files extends JFormField {
	
	protected $type = 'Modal_Navigation_Bar_Files';
	
	protected function getInput() {
	
		$navigationBarFolder = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_flippingbook' . DIRECTORY_SEPARATOR . 'navigationbars';
		$navigationBarFiles = JFolder::files( $navigationBarFolder, '.swf$' );
		if ( count( $navigationBarFiles ) > 0 ) {
			foreach ( $navigationBarFiles as $file )
				$navigationBarFile[] = JHTML::_( 'select.option', $file, $file );
		}
		$navigationBarFile[] = JHTML::_( 'select.option', "", JText::_( 'COM_FLIPPINGBOOK_NONE' ) );
		return JHTML::_( 'select.genericlist',  $navigationBarFile, 'jform[navigation_bar]', 'class="inputbox" size="1"', 'value', 'text', $this->value );
	}
}