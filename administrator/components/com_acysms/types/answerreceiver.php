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

class ACYSMSanswerreceiverType{

	var $js = '';

	function __construct(){

		$app = JFactory::getApplication();

		if($app->isAdmin()){
			$query = 'SELECT count(answer_to) as totalmsg, answer_to FROM '.ACYSMS::table('answer');
			$query .= ' WHERE `answer_to` IS NOT NULL OR `answer_to` <> "" GROUP BY answer_to';
		}else{
			$my = JFactory::getUser();

			$query = 'SELECT count(answer_to) as totalmsg, answer_to FROM '.ACYSMS::table('answer').' AS answer';
			$query .= ' JOIN #__acysms_message AS message ON message.message_id = answer.answer_message_id ';
			$query .= ' WHERE (`answer_to` IS NOT NULL OR `answer_to` <> "")';
			$query .= ' AND (message.message_userid = '.intval($my->id).')';
			$query .= ' GROUP BY answer_to';
		}


		$db = JFactory::getDBO();
		$db->setQuery($query);
		$answerReceiverPhoneNumber = $db->loadObjectList();
		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_ALL_RECEIVERS'));

		if(!empty(reset($answerReceiverPhoneNumber)->totalmsg) && !empty(reset($answerReceiverPhoneNumber)->answer_to)){
			foreach($answerReceiverPhoneNumber as $onePhoneNumber){
				$this->values[] = JHTML::_('select.option', $onePhoneNumber->answer_to, $onePhoneNumber->answer_to.' ( '.$onePhoneNumber->totalmsg.' )');
			}
		}
	}

	function display($map, $value){
		return JHTML::_('select.genericlist', $this->values, $map, 'class="inputbox" style="max-width:500px;width:auto;" size="1" '.$this->js, 'value', 'text', $value);
	}
}
