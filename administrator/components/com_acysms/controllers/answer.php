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

class AnswerController extends ACYSMSController{

	var $pkey = 'answer_id';
	var $table = 'answer';
	var $aclCat = 'answers';


	function remove(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('answers', 'delete')) return;

		$answerClass = ACYSMS::get('class.answer');

		$cids = JRequest::getVar('cid', array(), '', 'array');
		if(empty($cids)) return $this->listing();
		$num = $answerClass->delete($cids);
		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $num), 'message');

		return $this->listing();
	}

	function exportGlobal(){
		if(!$this->isAllowed('answers', 'export')) return;

		$exportHelper = ACYSMS::get('helper.export');
		$config = ACYSMS::config();
		$encodingClass = ACYSMS::get('helper.encoding');
		$phoneHelper = ACYSMS::get('helper.phone');
		$exportHelper->addHeaders('globalAnswers_'.date('m_d_y'));
		$db = JFactory::getDBO();
		$paramBase = ACYSMS_COMPONENT.'.answer';
		$filters = array();
		$app = JFactory::getApplication();

		$selectedIntegration = $app->getUserStateFromRequest($paramBase."filter_integration", 'filter_integration', 0, 'string');
		$selectedMessage = $app->getUserStateFromRequest($paramBase."filter_message", 'filter_message', 0, 'int');
		$selectedAnswerReceiver = $app->getUserStateFromRequest($paramBase."filter_answerreceiver", 'filter_answerreceiver', 0, 'string');

		if(!empty($selectedIntegration)) $filters[] = 'answer.answer_receiver_table = '.$db->quote($selectedIntegration);
		if(!empty($selectedMessage)) $filters[] = 'answer.answer_message_id = '.intval($selectedMessage);
		if(!empty($selectedAnswerReceiver)) $filters[] = 'answer.answer_to = '.$db->quote($selectedAnswerReceiver);


		$message_id = JRequest::getVar('cid');
		JArrayHelper::toInteger($message_id);

		if(!empty($message_id)) $filters[] = ' answers.answers_id IN ('.implode(', ', $message_id).') ';

		$query = 'SELECT * FROM '.ACYSMS::table('answer').' as answer ';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$receiverInformations = array();

		foreach($rows as $oneRow){
			if(empty($oneRow->answer_receiver_table)) continue;
			$user = new stdClass();
			$user->queue_receiver_id = $oneRow->answer_receiver_id;
			$receiverInformations[$oneRow->answer_receiver_table][] = $user;
		}
		$receivers = array();
		$receiverNames = array();
		foreach($receiverInformations as $integrationName => $receiverdId){
			$integration = ACYSMS::getIntegration($integrationName);
			$integration->addUsersInformations($receiverdId);
			$receiversInfo = $receiverdId;
			$receivers[$integrationName] = $receiversInfo;
			foreach($receiversInfo as $oneReceiverInfo){
				if(!empty($oneReceiverInfo->receiver_phone) && !empty($oneReceiverInfo->receiver_name)) $receiverNames[$integrationName][$phoneHelper->getValidNum($oneReceiverInfo->receiver_phone)] = $oneReceiverInfo->receiver_name;
			}
		}

		$eol = "\r\n";
		$before = '"';
		$separator = '"'.str_replace(array('semicolon', 'comma'), array(';', ','), $config->get('export_separator', ';')).'"';
		$exportFormat = $config->get('export_format', 'UTF-8');
		$after = '"';

		$titles = array(JText::_('SMS_SMS_BODY'), JText::_('SMS_FROM'), JText::_('SMS_NAME'), JText::_('SMS_TO'), JText::_('SMS_RECEPTION_DATE'), JText::_('SMS_ID'));
		$titleLine = $before.implode($separator, $titles).$after.$eol;
		echo $titleLine;

		foreach($rows as $oneAnswer){
			$line = $oneAnswer->answer_body.$separator;
			$line .= $oneAnswer->answer_from.$separator;
			isset($this->receiverNames[$oneAnswer->answer_receiver_table][$phoneHelper->getValidNum($oneAnswer->answer_from)]) ? $receiverNames[$oneAnswer->answer_receiver_table][$phoneHelper->getValidNum($oneAnswer->answer_from)] : '';
			$line .= $separator;
			$line .= $oneAnswer->answer_to.$separator;
			$line .= $oneAnswer->answer_date.$separator;
			$line .= $oneAnswer->answer_id.$separator;

			$line = $before.$encodingClass->change($line, 'UTF-8', $exportFormat).$after.$eol;
			echo $line;
		}
		exit;
	}

	function answerDataListing(){
		JRequest::setVar('layout', 'answerdatalisting');
		return parent::display();
	}
}
