<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class FlippingBookModelPage extends JModelAdmin {

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
			$table->reorder('book_id = '.(int) $table->book_id);
		}
	}

	public function getTable($type = 'Page', $prefix = 'PagesTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getItem($pk = null) {
		$item = parent::getItem($pk);
		return $item;
	}

	protected function getReorderConditions($table) {
		$condition = array();
		$condition[] = 'book_id = '.(int) $table->book_id;
		return $condition;
	}

	public function getForm($data = array(), $loadData = true) {	
		$form = $this->loadForm('com_flippingbook.page', 'page', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState('com_flippingbook.edit.page.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}
}