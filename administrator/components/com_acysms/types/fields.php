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

class ACYSMSfieldsType{
	var $allValues;
	function __construct(){
		$this->allValues = array();
		$this->allValues["text"] = JText::_('SMS_FIELD_TEXT');
		$this->allValues["textarea"] = JText::_('SMS_FIELD_TEXTAREA');
		$this->allValues["radio"] = JText::_('SMS_FIELD_RADIO');
		$this->allValues["checkbox"] = JText::_('SMS_FIELD_CHECKBOX');
		$this->allValues["singledropdown"] = JText::_('SMS_FIELD_SINGLEDROPDOWN');
		$this->allValues["multipledropdown"] = JText::_('SMS_FIELD_MULTIPLEDROPDOWN');
		$this->allValues["date"] = JText::_('SMS_FIELD_DATE');
		$this->allValues["birthday"] = JText::_('SMS_FIELD_BIRTHDAY');
		$this->allValues["file"] = JText::_('SMS_FIELD_FILE');
		$this->allValues["phone"] = JText::_('SMS_FIELD_PHONE');
		$this->allValues["customtext"] = JText::_('SMS_CUSTOM_TEXT');
	}

	function display($map,$value){
		$js = "function updateFieldType(){
			newType = document.getElementById('fieldtype').value;
			hiddenAll = new Array('multivalues','cols','rows','size','required','format','default','customtext','columnname','checkcontent');
			allTypes = new Array();
			allTypes['text'] = new Array('size','required','default','columnname','checkcontent');
			allTypes['textarea'] = new Array('cols','rows','required','default','columnname');
			allTypes['radio'] = new Array('multivalues','required','default','columnname');
			allTypes['checkbox'] = new Array('multivalues','required','default','columnname');
			allTypes['singledropdown'] = new Array('multivalues','required','default','columnname','size');
			allTypes['multipledropdown'] = new Array('multivalues','required','size','default','columnname');
			allTypes['date'] = new Array('required','format','size','default','columnname');
			allTypes['birthday'] = new Array('required','format','default','columnname');
			allTypes['file'] = new Array('columnname','required','size');
			allTypes['phone'] = new Array('columnname','required','size','default');
			allTypes['customtext'] = new Array('customtext');

			for (var i=0; i < hiddenAll.length; i++){
				$$('tr[class='+hiddenAll[i]+']').each(function(el) {
					el.style.display = 'none';
				});
			}

			for (var i=0; i < allTypes[newType].length; i++){
				$$('tr[class='+allTypes[newType][i]+']').each(function(el) {
					el.style.display = '';
				});
			}
		}
		window.addEvent('domready', function(){ updateFieldType(); });";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );

		$this->values = array();
		foreach($this->allValues as $oneType => $oneVal){
			$this->values[] = JHTML::_('select.option', $oneType,$oneVal);
		}


		return JHTML::_('select.genericlist', $this->values, $map , 'size="1" onchange="updateFieldType();"', 'value', 'text', (string) $value,'fieldtype');
	}
}
