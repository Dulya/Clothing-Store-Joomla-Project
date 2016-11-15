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

class ACYSMSstatsClass extends ACYSMSClass{

	var $tables = array('statsdetails' => 'statsdetails_message_id', 'stats' => 'stats_message_id');
	var $pkey = 'stats_message_id';

	function addDeliveryInformations($deliveryInformations){

		if(is_array($deliveryInformations)){
			foreach($deliveryInformations as $oneDeliveryInformation) $this->_addDeliveryEntry($oneDeliveryInformation);
		}
		else{
			$this->_addDeliveryEntry($deliveryInformations);
		}
	}

	private function _addDeliveryEntry($oneDeliveryInformation){
		if(empty($oneDeliveryInformation->statsdetails_sms_id)){
			$cronHelper = ACYSMS::get('helper.cron');
			$cronHelper->messages = array('DELIVERY REQUEST : NO ID');
			$cronHelper->detailMessages = array(print_r($_REQUEST, true));
			$cronHelper->saveReport();
			return false;
		}
		if(!empty($oneDeliveryInformation->statsdetails_error) && is_array($oneDeliveryInformation->statsdetails_error)) $oneDeliveryInformation->statsdetails_error = implode(',', $oneDeliveryInformation->statsdetails_error);
		$db = JFactory::getDBO();

		$db->updateObject(ACYSMS::table('statsdetails'), $oneDeliveryInformation, 'statsdetails_sms_id');

		if($db->getAffectedRows() == 0){

			sleep(10);
			$db->updateObject(ACYSMS::table('statsdetails'), $oneDeliveryInformation, 'statsdetails_sms_id');
			if($db->getAffectedRows() == 0){
				$cronHelper = ACYSMS::get('helper.cron');
				$cronHelper->messages = array('DELIVERY REQUEST ISSUE : No stats details information were updated => '.print_r($oneDeliveryInformation, true));
				$cronHelper->detailMessages = array(print_r($_REQUEST, true));
				$cronHelper->saveReport();
			}
		}
	}
}
