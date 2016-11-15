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

class ACYSMSoperatorsType{
	var $extra = '';
	var $id = '';
	function __construct(){

		$this->values = array();

		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('SMS_NUMERIC'));
		$this->values[] = JHTML::_('select.option', '=','=');
		$this->values[] = JHTML::_('select.option', '!=','!=');
		$this->values[] = JHTML::_('select.option', '>','>');
		$this->values[] = JHTML::_('select.option', '<','<');
		$this->values[] = JHTML::_('select.option', '>=','>=');
		$this->values[] = JHTML::_('select.option', '<=','<=');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('SMS_STRING'));
		$this->values[] = JHTML::_('select.option', 'BEGINS',JText::_('SMS_BEGINS_WITH'));
		$this->values[] = JHTML::_('select.option', 'END',JText::_('SMS_ENDS_WITH'));
		$this->values[] = JHTML::_('select.option', 'CONTAINS',JText::_('SMS_CONTAINS'));
		$this->values[] = JHTML::_('select.option', 'NOTCONTAINS',JText::_('SMS_NOT_CONTAINS'));
		$this->values[] = JHTML::_('select.option', 'LIKE','LIKE');
		$this->values[] = JHTML::_('select.option', 'NOT LIKE','NOT LIKE');
		$this->values[] = JHTML::_('select.option', 'REGEXP','REGEXP');
		$this->values[] = JHTML::_('select.option', 'NOT REGEXP','NOT REGEXP');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');
		$this->values[] = JHTML::_('select.option', '<OPTGROUP>',JText::_('OTHER'));
		$this->values[] = JHTML::_('select.option', 'IS NULL','IS NULL');
		$this->values[] = JHTML::_('select.option', 'IS NOT NULL','IS NOT NULL');
		$this->values[] = JHTML::_('select.option', '</OPTGROUP>');

	}

	function display($map){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" size="1" style="width:120px;" '.$this->extra, 'value', 'text', '', $this->id);
	}

}
