<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class ACYSMScategoryClass extends ACYSMSClass{
	var $tables = array('category'=>'category_id');
	var $pkey = 'category_id';
	var $namekey = 'category_name';
	var $allowedFields = array('category_name','category_ordering');



	function get($id,$default = null){
		$column = is_numeric($id) ? 'category_id' : 'category_name';
		$this->database->setQuery('SELECT * FROM #__acysms_category WHERE '.$column.' = '.$this->database->Quote(trim($id)).' LIMIT 1');
		return $this->database->loadObject();
	}


	function saveForm(){
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		$category = new stdClass();
		$category->category_id = ACYSMS::getCID('category_id');
		foreach($formData['category'] as $column => $value){
			ACYSMS::secureField($column);
			$category->$column = strip_tags($value);
		}
		$category_id= $this->save($category);
		if(empty($category->category_ordering)){
			$helperClass = ACYSMS::get('helper.order');
			$helperClass->pkey = 'category_id';
			$helperClass->table = 'category';
			$helperClass->orderingColumnName = 'category_ordering';
			$helperClass->reOrder();
		}

		if(!$category_id) return false;
		JRequest::setVar( 'category_id', $category_id);

		return true;
	}
}
