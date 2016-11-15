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

class ACYSMSmessagestatusType{

	public $messageId;

	function load(){

		$query = 'SELECT count(statsdetails_message_id) as totalmsg, statsdetails_status FROM '.ACYSMS::table('statsdetails').' WHERE statsdetails_message_id = '.intval($this->messageId);
		$query .= ' GROUP BY statsdetails_status ORDER BY statsdetails_message_id ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$status = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '', JText::_('SMS_ALL_STATUS'));
		foreach($status as $oneStatus){
			if($oneStatus->statsdetails_status == '-1' || $oneStatus->statsdetails_status == '-2' || $oneStatus->statsdetails_status == '-3' || $oneStatus->statsdetails_status == '-99'){
				$messageStatus = JText::_('SMS_STATUS_'.str_replace('-', 'M', $oneStatus->statsdetails_status));
			}else $messageStatus = JText::_('SMS_STATUS_'.$oneStatus->statsdetails_status);
			$this->values[] = JHTML::_('select.option', $oneStatus->statsdetails_status, $messageStatus.' ( '.$oneStatus->totalmsg.' )');
		}
	}

	function display($map, $value){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" style="width:auto" size="1" onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"', 'value', 'text', $value);
	}
}
