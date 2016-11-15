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

class ACYSMSmessageType{

	var $js = '';

	function __construct(){
		$app = JFactory::getApplication();


		$where = array();
		$where[] = ' ';

		if($app->isAdmin()){
			$query = 'SELECT count(distinct answer.answer_id) as totalmsg, message_subject, message_id  FROM '.ACYSMS::table('message').' AS message';
			$query .= ' INNER JOIN '.ACYSMS::table('answer').' AS answer on answer.answer_message_id = message.message_id';
			$query .= ' WHERE message_type <> "activation_optin" AND message_type <> "answer" AND message_type <> "conversation"';
			$query .= ' GROUP BY message_id ORDER BY message_id ASC';
		}else{
			$my = JFactory::getUser();
			$query = 'SELECT count(distinct answer.answer_id) as totalmsg, message_subject, message_id  FROM '.ACYSMS::table('message').' AS message';
			$query .= ' INNER JOIN '.ACYSMS::table('answer').' AS answer on answer.answer_message_id = message.message_id';
			$query .= ' WHERE  message_type <> "activation_optin" AND message_type <> "answer" AND message_type <> "conversation" AND (message_userid = '.intval($my->id).')';
			$query .= ' GROUP BY message_id ORDER BY message_id ASC';
		}

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_MESSAGES'));
		foreach($messages as $oneMessage){
			$this->values[] = JHTML::_('select.option', $oneMessage->message_id, $oneMessage->message_subject.' ( '.$oneMessage->totalmsg.' )');
		}
	}

	function display($map, $value){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" style="max-width:500px;width:auto;" size="1" '.$this->js, 'value', 'text', $value);
	}
}
