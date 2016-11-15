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

class ACYSMSphoneClass extends ACYSMSClass{

	function manageStatus($numbers, $status){
		$phoneHelper = ACYSMS::get('helper.phone');
		$db = JFactory::getDBO();

		if(!is_array($numbers)) $numbers = array($numbers);

		foreach($numbers as $oneNumber){
			$validPhone = $phoneHelper->getValidNum(substr($oneNumber, strpos($oneNumber, '_')));
			if($validPhone != false) $phoneNumbers[] = $db->Quote($validPhone);
		}
		if(empty($phoneNumbers)) return;

		if(empty($status)){
			$query = 'INSERT IGNORE INTO '.ACYSMS::table("phone").' (`phone_number`)  VALUES ('.implode('),(', $phoneNumbers).')';
			$db->setQuery($query);
			$db->query();
			return;
		}

		$query = 'DELETE FROM '.ACYSMS::table("phone").' WHERE phone_number IN ('.implode(',', $phoneNumbers).')';
		$db->setQuery($query);
		$db->query();
	}

	function block($phoneNumbers = array()){
		$db = JFactory::getDBO();

		foreach($phoneNumbers as $onePhoneNumber) $onePhoneNumber = $db->Quote($onePhoneNumber);

		$query = 'INSERT IGNORE INTO '.ACYSMS::table("phone").' (`phone_number`)  VALUES ("'.implode('"),("', $phoneNumbers).'")';
		$db->setQuery($query);
		$db->query();
	}

	function unblock($phoneNumbers = array()){
		$db = JFactory::getDBO();

		foreach($phoneNumbers as $onePhoneNumber) $onePhoneNumber = $db->Quote($onePhoneNumber);

		$query = 'DELETE FROM '.ACYSMS::table("phone").' WHERE phone_number IN ("'.implode('","', $phoneNumbers).'")';
		$db->setQuery($query);
		$db->query();
	}

	function isBlocked($phoneNumber){
		$db = JFactory::getDBO();
		$query = 'SELECT phone_id FROM '.ACYSMS::table("phone").' WHERE phone_number ='.$db->Quote($phoneNumber).' LIMIT 1';
		$db->setQuery($query);
		$result = $db->loadResult();
		if(empty($result)) {
			return false;
		}

		return true;
	}
}
