<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class FlippingBookModelBook extends JModelAdmin {

	protected $text_prefix = 'COM_FLIPPINGBOOOK';

	protected function canDelete($record) {
		$user = JFactory::getUser();
		if ( $user->authorise('core.delete', 'com_flippingbook' ) ) {
			// Delete pages
			$db = JFactory::getDBO();
			$query = 'DELETE FROM `#__flippingbook_pages` WHERE `book_id` = '.intval( $record->id );
			$db->setQuery( $query );
			if (!$db->query()) {
				JError::raiseError(500, $db->getErrorMsg());
			}
		}
		return $user->authorise('core.delete', 'com_flippingbook');
	}

	protected function canEditState($record) {
		$user = JFactory::getUser();
		return $user->authorise('core.edit.state', 'com_flippingbook');
	}
	
	protected function prepareTable($table)	{
		if (empty($table->id)) {
			$table->reorder('category_id = '.(int) $table->category_id);
		}
	}

	public function getTable($type = 'Book', $prefix = 'BooksTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	public function getItem($pk = null) {
		$item = parent::getItem($pk);
		return $item;
	}
	
	protected function getReorderConditions($table) {
		$condition = array();	
		$condition[] = 'category_id = '. (int) $table->category_id;
		return $condition;
	}

	public function getForm($data = array(), $loadData = true) {	
		$form = $this->loadForm('com_flippingbook.book', 'book', array('control' => 'jform', 'load_data' => $loadData));
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