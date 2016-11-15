<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class FlippingBookModelConfiguration extends JModelAdmin {

	public function getForm($data = array(), $loadData = true) {
	
		$form = $this->loadForm('com_flippingbook.configuration', 'configuration', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	public function getData ($pk = null) { 
		//Load config data
		$item = new JObject;
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('name, value');
		$query->from('#__flippingbook_config');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}
		foreach ($rows as $row) {
			$item->set($row->name, $row->value);
		}
		
		return $item;
	}
}