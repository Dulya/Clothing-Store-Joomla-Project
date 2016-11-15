<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class FlippingBookViewCategory extends JViewLegacy {

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
		$document->setTitle(JText::_('COM_FLIPPINGBOOK_EDIT_CATEGORY'));

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar()	{

		JFactory::getApplication()->input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);
		$canDo = FlippingBookHelper::getActions();

		JToolBarHelper::title( JText::_( 'COM_FLIPPINGBOOK_MAIN_TITLE') .': ' . JText::_( 'COM_FLIPPINGBOOK_EDIT_CATEGORY' ) );
		
		if ($canDo->get('core.edit')) {
			JToolBarHelper::apply('category.apply');
			JToolBarHelper::save('category.save');
		}
		if (!$isNew) {
				JToolbarHelper::save2copy('category.save2copy');
		}
		if (empty($this->item->id))  {
			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CANCEL');
		} else {
			JToolBarHelper::cancel('category.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}