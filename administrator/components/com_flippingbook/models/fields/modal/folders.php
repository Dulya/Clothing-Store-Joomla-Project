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

class JFormFieldModal_Folders extends JFormField {
	
	protected $type = 'Modal_Folders';
	
	protected function getInput() {
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		$folders = JFolder::listFolderTree (JPATH_ROOT . DIRECTORY_SEPARATOR . 'images', '', 10);
		$folder_name[] = JHTML::_('select.option', DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR );
		if (count($folders) > 0) {
			foreach ($folders as $folder) {
				$folder_name[] = JHTML::_('select.option', $folder["relname"] . DIRECTORY_SEPARATOR, $folder["relname"] . DIRECTORY_SEPARATOR );
			}
		}
		return JHTML::_('select.genericlist', $folder_name, 'jform[folder]', 'class="inputbox" size="1"', 'value', 'text');
	}
}