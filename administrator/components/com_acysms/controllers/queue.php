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

class QueueController extends ACYSMSController{
	var $aclCat = 'queue';

	function remove(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('queue', 'delete')) return;

		$app = JFactory::getApplication();
		$filter_messagequeue = $app->getUserStateFromRequest("filter_messagequeue", 'filter_messagequeue', '', 'string');

		$queueMessageFilterIntegration = '';

		$filterArray = explode('.', $filter_messagequeue);
		if(!empty($filterArray[0])){
			$queueMessageFilterIntegration = $filterArray[0];
			$message_id = $filterArray[1];
		}

		if(empty($queueMessageFilterIntegration)){
			$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');
		}else $currentIntegration = $queueMessageFilterIntegration;

		$queueClass = ACYSMS::get('class.queue');
		$search = JRequest::getString('search');
		$integration = $currentIntegration;
		$filters = array();
		$db = JFactory::getDBO();
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$searchFields = array('message.message_subject', 'queue.queue_message_id', 'queue.queue_receiver_id', 'queue.queue_receiver_table');
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		if(!empty($message_id) && $message_id != 'all'){
			$filters[] = 'queue.queue_message_id = '.intval($message_id);
		}
		if(!empty($integration)){
			$filters[] = 'queue.queue_receiver_table = '.$db->Quote($integration);
		}
		$total = $queueClass->delete($filters);
		ACYSMS::enqueueMessage(JText::sprintf('SMS_SUCC_DELETE_ELEMENTS', $total), 'message');
		JRequest::setVar('filter_sms', 0, 'post');
		JRequest::setVar('search', '', 'post');
		return $this->listing();
	}

	function process(){
		if(!$this->isAllowed('queue', 'process')) return;
		JRequest::setVar('layout', 'process');
		return parent::display();
	}

	function preview(){
		JRequest::setVar('layout', 'preview');
		return parent::display();
	}
}
