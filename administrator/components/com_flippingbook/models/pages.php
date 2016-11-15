<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class FlippingBookModelPages extends JModelList {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'file', 'a.file',
				'state', 'a.state',
				'zoom_url', 'a.zoom_url',
				'ordering', 'a.ordering',
				'link_url', 'a.link_url',
				'book_title', 'a.book_title',
				'book_id', 'a.book_id',
				'id', 'a.id'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		if ($layout = JRequest::getVar('layout', 'default')) {
			$this->context .= '.'.$layout;
		}

		$search = $app->getUserStateFromRequest($this->context.'.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.state', 'filter_published', '');
		$this->setState('filter.state', $published);

		$bookId = $app->getUserStateFromRequest($this->context.'.book_id', 'filter_book_id');
		$this->setState('filter.book_id', $bookId);

		parent::populateState('a.ordering', 'asc');
	}

	protected function getListQuery() {
	
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.id, 
				a.file, 
				a.zoom_url, 
				a.link_url, 
				a.description, 
				a.checked_out, 
				a.checked_out_time, 
				a.state AS state, 
				a.ordering, 
				a.book_id AS catid')
		);
		$query->from('#__flippingbook_pages AS a');

		$query->select('c.title AS book_title');
		$query->join('LEFT', '#__flippingbook_books AS c ON c.id = a.book_id');

		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = '.(int) $published);
		} elseif ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

		$bookId = $this->getState('filter.book_id');
		if (is_numeric($bookId)) {
			$query->where('a.book_id = ' . (int) $bookId);
		}

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('a.file LIKE '.$search);
		}

		$orderCol	= $this->state->get('list.ordering', 'a.file');
		$orderDirn	= $this->state->get('list.direction');
		
		if ($orderCol == 'a.ordering' || $orderCol == 'book_title') {
			$orderCol = 'book_title '.$orderDirn.', a.ordering';
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}

	protected function getStoreId($id = '') {

		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');
		$id.= ':' . $this->getState('filter.book_id');

		return parent::getStoreId($id);
	}
}