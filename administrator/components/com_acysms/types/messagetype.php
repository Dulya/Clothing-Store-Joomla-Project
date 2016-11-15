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

class ACYSMSmessagetypeType{

	var $js = '';

	function __construct(){
		$query = 'SELECT message_type
		FROM '.ACYSMS::table('message').'
		WHERE message_type <> "activation_optin"
		AND message_type <> "answer"
		AND message_type <> "conversation"';

		$query .= ' GROUP BY message_type ASC ';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();


		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_TYPES'));

		$messageStandardPresent = false;
		foreach($messages as $oneMessage){
			if($oneMessage->message_type == 'standard') $messageStandardPresent = true;
			$this->values[] = JHTML::_('select.option', $oneMessage->message_type, JText::_('SMS_'.strtoupper($oneMessage->message_type)));
		}

		if(!$messageStandardPresent) $this->values[] = JHTML::_('select.option', 'standard', JText::_('SMS_STANDARD'));
	}

	function display($map, $value){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" style="width:115px" size="1" '.$this->js, 'value', 'text', $value);
	}
}
