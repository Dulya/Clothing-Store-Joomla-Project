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

class ACYSMSstatusType{
	function __construct(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '-1', JText::_('SMS_UNSUBSCRIBED') );
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_NO_SUBSCRIPTION') );
		$this->values[] = JHTML::_('select.option', '1', JText::_('SMS_SUBSCRIBED') );
	}

	function display($map,$value){
		static $i = 0;
		if($value == 2){
			$this->values[] = JHTML::_('select.option', '2', JText::_('SMS_WAITING_FOR_ACTIVATION') );
		}
		return JHTML::_('acysmsselect.radiolist', $this->values, $map , 'class="radiobox" size="1"', 'value', 'text', (int) $value,'status'.$i++);
	}

}
