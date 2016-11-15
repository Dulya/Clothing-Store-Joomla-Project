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
class ACYSMSdetailstatmessageType{

	function __construct(){
		$query = 'SELECT b.message_subject, a.statsdetails_message_id FROM '.ACYSMS::table('statsdetails').' as a';
		$query .= ' JOIN '.ACYSMS::table('message').' as b on a.statsdetails_message_id = b.message_id GROUP BY a.statsdetails_message_id ORDER BY b.message_subject ASC';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_MESSAGES') );
		foreach($messages as $oneMessage){
			$this->values[] = JHTML::_('select.option', $oneMessage->statsdetails_message_id, $oneMessage->message_subject );
		}
	}
	function display($map,$value ){
		return JHTML::_('select.genericlist',   $this->values, $map, 'class="inputbox" style="max-width:500px" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $value );
	}
}
