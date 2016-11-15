<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

class FlippingBookModelBatchaddingpages extends JModelAdmin {

	public function getForm($data = array(), $loadData = true) {

		$form = $this->loadForm('com_flippingbook.batchaddingpages', 'batchaddingpages', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}
}