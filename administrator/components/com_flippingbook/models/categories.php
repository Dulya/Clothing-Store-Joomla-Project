<?php

defined('_JEXEC') or die;

class FlippingBookModelCategories extends JModelList {

	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();

		if ($layout = JRequest::getVar('layout', 'default')) {
			$this->context .= '.'.$layout;
		}

		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '');
		$this->setState('filter.state', $published);

		$categoryId = $this->getUserStateFromRequest($this->context.'.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		parent::populateState('a.title', 'asc');
	}

	protected function getStoreId($id = '') {
	
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.published');
		$id.= ':' . $this->getState('filter.category_id');

		return parent::getStoreId($id);
	}

	protected function getListQuery($resolveFKs = true) {
	
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'a.id, 
				a.title, 
				a.alias, 
				a.checked_out, 
				a.checked_out_time, 
				a.description, 
				a.state, 
				a.ordering, 
				a.publish_up, 
				a.publish_down')
		);
		$query->from($db->quoteName('#__flippingbook_categories'). ' AS a');

		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.state = ' . (int) $published);
		} else if ($published === '') {
			$query->where('(a.state IN (0, 1))');
		}

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%'.$db->escape($search, true).'%');
			$query->where('a.title LIKE '.$search.' OR a.alias LIKE '.$search);
		}

		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
}