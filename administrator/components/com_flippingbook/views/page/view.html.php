<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class FlippingBookViewPage extends JViewLegacy {

	protected $form;
	protected $item;
	protected $state;

	function display($tpl = null) {
	
		$this->form		= $this->get('form');
		$this->item		= $this->get('item');
		$this->state	= $this->get('state');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_FLIPPINGBOOK_EDIT_PAGE'));

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()	{

		JRequest::setVar('hidemainmenu', true);

		$isNew = ($this->item->id == 0);
		$canDo = FlippingBookHelper::getActions();

		JToolBarHelper::title( JText::_( 'COM_FLIPPINGBOOK_MAIN_TITLE') .': '.JText::_( 'COM_FLIPPINGBOOK_EDIT_PAGE' ) );

		if ($canDo->get('core.edit')) {
			JToolBarHelper::apply('page.apply','JTOOLBAR_APPLY');
			JToolBarHelper::save('page.save','JTOOLBAR_SAVE');
		}
		if (!$isNew) {
			JToolBarHelper::custom('page.save2copy','save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY',false );
		}
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('page.cancel','JTOOLBAR_CANCEL');
		} else {
			JToolBarHelper::cancel('page.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}