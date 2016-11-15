<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class FlippingBookViewMain extends JViewLegacy {

	function display($tpl = null) {
		$this->_setToolbar();
		parent::display($tpl);
	}

	protected function _setToolbar() {
		JToolBarHelper::title( JText::_( 'COM_FLIPPINGBOOK_MAIN_TITLE' ) );
		JToolBarHelper::preferences('com_flippingbook');
		JToolBarHelper::help( '../help.html', true );
	}
}