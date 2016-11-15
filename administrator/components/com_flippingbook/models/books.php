<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

class FlippingBookModelBooks extends JModelList {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'title', 'a.title',
				'state', 'a.state',
				'hits', 'a.hits',
				'ordering', 'a.ordering',
				'category_title', 'a.category_title',
				'category_id', 'a.category_id',
				'id', 'a.id'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		if ($layout = JRequest::getVar('layout')) {
			$this->context .= '.'.$layout;
		}

		$search = $app->getUserStateFromRequest($this->context.'.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->context.'.state', 'filter_published', '');
		$this->setState('filter.state', $state);

		$categoryId = $app->getUserStateFromRequest($this->context.'.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		parent::populateState('a.ordering', 'asc');
	}
	
	protected function getListQuery() {
	
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.id, 
				a.title, 
				a.description, 
				a.alias, 
				a.checked_out, 
				a.checked_out_time, 
				a.hits, 
				a.state AS state, 
				a.access, 
				a.ordering, 
				a.category_id AS catid')
		);
		$query->from('#__flippingbook_books AS a');

		$query->select('c.title AS category_title');
		$query->join('LEFT', '#__flippingbook_categories AS c ON c.id = a.category_id');

		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = '.(int) $published);
		} elseif ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$query->where('a.category_id = ' . (int) $categoryId);
		}

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('a.title LIKE '.$search.' OR a.alias LIKE '.$search);
		}

		$orderCol	= $this->state->get('list.ordering', 'a.title');
		$orderDirn	= $this->state->get('list.direction');

		if ($orderCol == 'a.ordering' || $orderCol == 'category_title') {
			$orderCol = 'category_title '.$orderDirn.', a.ordering';
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));
		return $query;
	}

	protected function getStoreId($id = '')	{

		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');
		$id.= ':' . $this->getState('filter.category_id');

		return parent::getStoreId($id);
	}
}