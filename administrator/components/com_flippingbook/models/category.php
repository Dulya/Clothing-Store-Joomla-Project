<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class FlippingBookModelCategory extends JModelAdmin {

	protected function canDelete($record) {
		$user = JFactory::getUser();
		return $user->authorise('core.delete', 'com_flippingbook');
	}

	protected function canEditState($record) {
		$user = JFactory::getUser();
		return $user->authorise('core.edit.state', 'com_flippingbook');
	}
	
	protected function prepareTable($table)	{
		if (empty($table->id)) {
			$table->reorder();
		}
	}
	
	public function getTable($type = 'Category', $prefix = 'CategoriesTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null) {
		$item = parent::getItem($pk);
		return $item;
	}
	
	protected function getReorderConditions($table = null) {		
		$condition = array();
		return $condition;
	}

	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_flippingbook.category', 'category', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData() {
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
}