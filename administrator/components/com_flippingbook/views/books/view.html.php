<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class FlippingBookViewBooks extends JViewLegacy {

	protected $items;
	protected $pagination;
	protected $state;
	
	protected $category_list;

	public function display($tpl = null) {
		
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	protected function addToolbar() {
		
		$state = $this->get('State');
		$canDo = FlippingBookHelper::getActions();
		
		JRequest::setVar('hidemainmenu', false);
		JToolBarHelper::title( JText::_( 'COM_FLIPPINGBOOK_MAIN_TITLE') .': '.JText::_( 'COM_FLIPPINGBOOK_BOOKS' ) );
		
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('book.add','JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('book.edit','JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::divider();
			JToolBarHelper::custom('books.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('books.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('books.archive','JTOOLBAR_ARCHIVE');
		}
		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'books.delete','JTOOLBAR_EMPTY_TRASH');
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('books.trash','JTOOLBAR_TRASH');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help( '../help.html', true );
		

		JSubMenuHelper::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
		);
		
		// Create the list of categories
		$db = JFactory::getDBO();
		$query = 'SELECT id, title FROM #__flippingbook_categories ORDER BY title';
		$db->setQuery( $query );
		$rows2 = $db->loadObjectList();
		foreach ( $rows2 as $row ) {
			$book_filter[] = JHTML::_('select.option', $row->id, $row->title );
		}
		$this->category_list = JHTML::_('select.options', $book_filter, 'value', 'text', $this->state->get('filter.category_id'), true );
		
		JSubMenuHelper::addFilter(
			JText::_('JOPTION_SELECT_CATEGORY'),
			'filter_category_id',
			$this->category_list
		);
	}
	protected function getSortFields() {
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.title' => JText::_('JGLOBAL_TITLE'),
			'category_title' => JText::_('COM_FLIPPINGBOOK_CATEGORY'),
			'category_id' => JText::_('COM_FLIPPINGBOOK_CATEGORY_ID'),
			'a.hits' => JText::_('COM_FLIPPINGBOOK_HITS'),
			'a.state' => JText::_('JSTATUS'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}