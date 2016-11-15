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

class ACYSMSgrouppublishedType{
	public $values;

	function __construct(){
		$this->values[] = JHTML::_('select.option', '1', JText::_('SMS_PUBLISHED'));
		$this->values[] = JHTML::_('select.option', '-1', JText::_('SMS_ARCHIVED'));
	}

	function display($map, $value){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" style="width:100px;" size="1" onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"', 'value', 'text', $value);
	}
}
