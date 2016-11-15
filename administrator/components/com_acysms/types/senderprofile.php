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

class ACYSMSsenderprofileType{

	var $allSenderProfileOptions = false;

	var $onlyDefaultSenderProfile = false;

	var $displayDefaultSenderProfileOption = false;

	var $includeMMSJS = false;

	var $customer;

	var $multiple = false;

	var $js;

	function display($map, $values){

		$db = JFactory::getDBO();
		$onChangeFunction = '';
		$config = ACYSMS::config();
		$app = JFactory::getApplication();

		if(is_array($values)) JArrayHelper::toInteger($values);

		$my = JFactory::getUser();
		if(!ACYSMS_J16){
			$groups = $my->gid;
			$condGroup = ' OR senderprofile_access LIKE (\'%,'.$groups.',%\')';
			$condGroup .= ' OR senderprofile_userid = '.intval($my->id);
		}else{
			jimport('joomla.access.access');
			$groups = JAccess::getGroupsByUser($my->id, false);
			$condGroup = '';
			foreach($groups as $group){
				$condGroup .= ' OR senderprofile_access LIKE (\'%,'.$group.',%\')';
				$condGroup .= ' OR senderprofile_userid = '.intval($my->id);
			}
		}

		$where = array();
		$where[] = '(senderprofile_access = \'all\' '.$condGroup.')';


		$allowCustomerManagement = $config->get('allowCustomersManagement');


		if(!$app->isAdmin() && $allowCustomerManagement){

			$my = JFactory::getUser();
			$customerClass = ACYSMS::get('class.customer');
			$customer = $customerClass->getCustomerByJoomID($my->id);

			if(empty($customer)) die('Only customers ccan access this page. Please contact the website administrator and ask him to assign your Joomla user to a customer.');

			if(!empty($customer->customer_senderprofile_id) && is_string($customer->customer_senderprofile_id)){
				$allowedGateways = explode(',', $customer->customer_senderprofile_id);
			}
			if($allowCustomerManagement && empty($allowedGateways)) die('Please contact your administrator and ask him to define a default sender profile for your profile so that you will be able to send SMS.');


			if(is_string($customer->customer_senderprofile_id)) $allowedGateways = explode(',', $customer->customer_senderprofile_id);
			JArrayHelper::toInteger($allowedGateways);
			$where[] = 'senderprofile_id IN ('.implode(',', $allowedGateways).')';
		}

		$query = 'SELECT senderprofile_id, senderprofile_name, senderprofile_default
		FROM '.ACYSMS::table('senderprofile').'
		WHERE '.implode(' AND ', $where).'
		ORDER BY senderprofile_default DESC, senderprofile_name ASC';

		$db->setQuery($query);
		$this->values = $db->loadObjectList();

		if(empty($this->values)){
			echo JText::_('SMS_NO_SENDER_PROFILE_ACCESS');
			return;
		}

		if($this->allSenderProfileOptions){
			$newElement = new stdClass();
			$newElement->senderprofile_id = 0;
			$newElement->senderprofile_name = JText::_('SMS_ALL_SENDER_PROFILE');
			array_unshift($this->values, $newElement);
		}

		if($this->displayDefaultSenderProfileOption){
			$newElement = new stdClass();
			$newElement->senderprofile_id = 0;
			$newElement->senderprofile_name = JText::_('SMS_DEFAULT_SENDER_PROFILE');
			array_unshift($this->values, $newElement);
		}

		if($this->includeMMSJS){
			$senderProfileClass = ACYSMS::get("class.senderprofile");
			$senderProfileHandleMMS = '';
			foreach($this->values as $oneSenderProfile){
				if($senderProfileClass->getGateway($oneSenderProfile->senderprofile_id)->handleMMS){
					$senderProfileHandleMMS .= $oneSenderProfile->senderprofile_id.',';
				}
			}
			$script = '
			window.addEvent("domready", function() {
				isHandlingMMS(document.getElementById("'.str_replace(array('[', ']'), '', $map).'"));
			});

			function isHandlingMMS(dropdownGateway) {
				var gatewayForMMS = ['.trim($senderProfileHandleMMS, ',').']
				for(var i=0; i<gatewayForMMS.length; i++) {
					if(dropdownGateway.value == gatewayForMMS[i]) {
						displayHideMMS(false);
						return;
					}
				}
				displayHideMMS(true);
				return;
			}

			function displayHideMMS(hide) {
				var divMMS = document.getElementsByClassName("sms_mms_upload");
				for(var i=0; i<divMMS.length; i++) {
					if(hide)
						divMMS[i].style.display = "none";
					else
						divMMS[i].style.display = "block";
				}
			}
			';

			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($script);
			$onChangeFunction = 'isHandlingMMS(this)';
		}

		$multiple = (($this->multiple) ? 'multiple="multiple"' : '');

		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" '.$this->js.' onchange="'.$onChangeFunction.'" style="width:150px;" '.$multiple, 'senderprofile_id', 'senderprofile_name', $values);
	}
}
