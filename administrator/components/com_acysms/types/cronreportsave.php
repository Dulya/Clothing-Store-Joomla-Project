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
class ACYSMScronreportsaveType{
	function __construct(){
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0',JText::_('SMS_NO'));
		$this->values[] = JHTML::_('select.option', '1',JText::_('SMS_SIMPLIFIED_REPORT'));
		$this->values[] = JHTML::_('select.option', '2',JText::_('SMS_DETAILED_REPORT'));
		$js = "function updateCronReportSave(){";
			$js .= "cronsavereport = window.document.getElementById('cronsavereport').value;";
			$js .= "if(cronsavereport != 0) {window.document.getElementById('cronreportsave').style.display = 'block';}else{window.document.getElementById('cronreportsave').style.display = 'none';}";
		$js .= '}';
		$js .='window.addEvent(\'domready\', function(){ updateCronReportSave(); });';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );
	}
	function display($map,$value){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" size="1" onchange="updateCronReportSave();"', 'value', 'text', (int) $value ,'cronsavereport');
	}
}
