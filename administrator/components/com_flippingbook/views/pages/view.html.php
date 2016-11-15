<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

class FlippingBookViewPages extends JViewLegacy {

	protected $items;
	protected $pagination;
	protected $state;
	
	protected $books_list;

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
		JToolBarHelper::title( JText::_( 'COM_FLIPPINGBOOK_MAIN_TITLE') .': '.JText::_( 'COM_FLIPPINGBOOK_PAGES' ) );
		
		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('page.add','JTOOLBAR_NEW');
		}
		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('page.edit','JTOOLBAR_EDIT');
			JToolBarHelper::divider();
			JToolBarHelper::custom('pages.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			JToolBarHelper::custom('pages.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			JToolBarHelper::divider();
			JToolBarHelper::archiveList('pages.archive','JTOOLBAR_ARCHIVE');
		}
		
		if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'pages.delete','JTOOLBAR_EMPTY_TRASH');
		} else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('pages.trash','JTOOLBAR_TRASH');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help( '../help.html', true );
		

		JSubMenuHelper::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.state'), true)
		);
		
		// Create the list of books
		$db = JFactory::getDBO();
		$query = 'SELECT id, title FROM #__flippingbook_books ORDER BY title';
		$db->setQuery( $query );
		$rows2 = $db->loadObjectList();
		foreach ( $rows2 as $row ) {
			$book_filter[] = JHTML::_('select.option', $row->id, $row->title );
		}
		$this->books_list = JHTML::_('select.options', $book_filter, 'value', 'text', $this->state->get('filter.book_id'), true );
		
		JSubMenuHelper::addFilter(
			'- '.JText::_('COM_FLIPPINGBOOK_SELECT_BOOK').' -',
			'filter_book_id',
			$this->books_list
		);
	}
	
	protected function getSortFields() {
		return array(
			'a.ordering' => JText::_('JGRID_HEADING_ORDERING'),
			'a.file' => JText::_('COM_FLIPPINGBOOK_PAGE_FILE'),
			'a.zoom_url' => JText::_('COM_FLIPPINGBOOK_ZOOMED_PAGE_FILE'),
			'a.state' => JText::_('JSTATUS'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
