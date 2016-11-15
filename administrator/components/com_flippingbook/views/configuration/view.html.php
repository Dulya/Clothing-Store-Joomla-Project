<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class FlippingBookViewConfiguration extends JViewLegacy {

	protected $form;
	protected $item;

	function display($tpl = null) {
		$form	= $this->get('Form');
		$data	= $this->get('Data');
		
		if ($form && $data) {
			$form->bind($data);
		}
		$this->form = &$form;
		$this->data = &$data;
		
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
	
		JToolBarHelper::title( JText::_( 'COM_FLIPPINGBOOK_MAIN_TITLE') .': '.JText::_( 'COM_FLIPPINGBOOK_CONFIGURATION' ) );
		
		JToolBarHelper::apply('configuration.apply','JTOOLBAR_APPLY');
		JToolBarHelper::save('configuration.save','JTOOLBAR_SAVE');
		JToolBarHelper::cancel('configuration.cancel', 'JTOOLBAR_CANCEL');
		JToolBarHelper::help( '../help.html', true );
	}
}