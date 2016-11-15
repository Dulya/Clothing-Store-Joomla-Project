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

class ACYSMSdelayType{
	var $values = array();
	var $num = 0;
	var $onChange = '';

	function __construct(){

		static $i = 0;
		$i++;
		$this->num = $i;

		$js = "function updateDelay".$this->num."(){";
			$js .= "delayvar = window.document.getElementById('delayvar".$this->num."');";
			$js .= "delaytype = window.document.getElementById('delaytype".$this->num."').value;";
			$js .= "delayvalue = window.document.getElementById('delayvalue".$this->num."');";
			$js .= "realValue = delayvalue.value;";
			$js .= "if(delaytype == 'minute'){realValue = realValue*60; }";
			$js .= "if(delaytype == 'hour'){realValue = realValue*3600; }";
			$js .= "if(delaytype == 'day'){realValue = realValue*86400; }";
			$js .= "if(delaytype == 'week'){realValue = realValue*604800; }";
			$js .= "if(delaytype == 'month'){realValue = realValue*2592000; }";
			$js .= "delayvar.value = realValue;";
		$js .= '}';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );

	}

	function display($map,$value,$type = 1){
		if($type == 0){
			$this->values[] = JHTML::_('select.option', 'second',JText::_('SMS_SECONDS'));
			$this->values[] = JHTML::_('select.option', 'minute',JText::_('SMS_MINUTES'));
		}elseif($type == 1){
			$this->values[] = JHTML::_('select.option', 'minute',JText::_('SMS_MINUTES'));
			$this->values[] = JHTML::_('select.option', 'hour',JText::_('SMS_HOURS'));
			$this->values[] = JHTML::_('select.option', 'day',JText::_('SMS_DAYS'));
			$this->values[] = JHTML::_('select.option', 'week',JText::_('SMS_WEEKS'));
		}elseif($type == 2){
			$this->values[] = JHTML::_('select.option', 'minute',JText::_('SMS_MINUTES'));
			$this->values[] = JHTML::_('select.option', 'hour',JText::_('SMS_HOURS'));
		}elseif($type == 3){
			$this->values[] = JHTML::_('select.option', 'hour',JText::_('SMS_HOURS'));
			$this->values[] = JHTML::_('select.option', 'day',JText::_('SMS_DAYS'));
			$this->values[] = JHTML::_('select.option', 'week',JText::_('SMS_WEEKS'));
			$this->values[] = JHTML::_('select.option', 'month',JText::_('SMS_MONTHS'));
		}elseif($type == 4){
			$this->values[] = JHTML::_('select.option', 'week',JText::_('SMS_WEEKS'));
			$this->values[] = JHTML::_('select.option', 'month',JText::_('SMS_MONTHS'));
		}

		$return = $this->get($value,$type);
		$delayValue = '<input class="inputbox" onchange="updateDelay'.$this->num.'();'.$this->onChange.'" type="text" id="delayvalue'.$this->num.'" style="width:50px" value="'.htmlspecialchars($return->value,ENT_COMPAT, 'UTF-8').'" /> ';
		$delayVar = '<input type="hidden" name="'.htmlspecialchars($map,ENT_COMPAT, 'UTF-8').'" id="delayvar'.$this->num.'" value="'.htmlspecialchars($value,ENT_COMPAT, 'UTF-8').'"/>';
		return $delayValue.JHTML::_('select.genericlist',   $this->values, 'delaytype'.$this->num, 'class="inputbox" size="1" style="width:100px" onchange="updateDelay'.$this->num.'();'.$this->onChange.'"', 'value', 'text', $return->type ,'delaytype'.$this->num).$delayVar;
	}

	function get($value,$type){

		$return = new stdClass();

		$return->value = $value;
		if($type == 0){
			$return->type = 'second';
		}else{
			$return->type = 'minute';
		}

		if($return->value >= 60  AND $return->value%60 == 0){
			$return->value = (int) $return->value / 60;
			$return->type = 'minute';
			if($type != 0 AND $return->value >=60 AND $return->value%60 == 0){
				$return->type = 'hour';
				$return->value = $return->value / 60;
				if($type != 2 AND $return->value >=24 AND $return->value%24 == 0){
					$return->type = 'day';
					$return->value = $return->value / 24;
					if($type >= 3 AND $return->value >=30 AND $return->value%30 == 0){
						$return->type = 'month';
						$return->value = $return->value / 30;
					}elseif($return->value >=7 AND $return->value%7 == 0){
						$return->type = 'week';
						$return->value = $return->value / 7;
					}
				}
			}
		}

		return $return;

	}

}
