<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class FlippingBookViewBatchaddingpages extends JViewLegacy {

	protected $form;

	function display($tpl = null) {
		$form	= $this->get('Form');
		$this->form = &$form;
		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {

		$canDo = FlippingBookHelper::getActions();

		JToolBarHelper::title( JText::_( 'COM_FLIPPINGBOOK_MAIN_TITLE') .': '.JText::_( 'COM_FLIPPINGBOOK_BATCH_ADDING_PAGES' ) );
		
		if ($canDo->get('core.create')) {
			JToolBarHelper::apply('batchaddingpages.apply','JTOOLBAR_APPLY');
			JToolBarHelper::save('batchaddingpages.save','JTOOLBAR_SAVE');
		}
		JToolBarHelper::help( '../help.html', true );
	}
}