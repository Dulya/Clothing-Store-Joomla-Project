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

class plgAcymailingAcysmsfollowup extends JPlugin{
	var $sendervalues = array();

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acymailing', 'acysmsfollowup');
			$this->params = new JParameter($plugin->params);
		}
	}

	function init(){
		if(defined('ACYSMS_COMPONENT')) return true;
		$acySmsHelper = rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper)){
			include_once $acySmsHelper;
		}else return false;
		return defined('ACYSMS_COMPONENT');
	}


	function onAcySubscribe($subid, $listids){
		$integrationFrom = 'acymailing';


		if(!$this->init()) return;

		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');

		if(empty($listids)) return false;

		$receiverField = $config->get('acymailing_field');
		if(empty($receiverField)) return;

		$allMessages = $messageClass->getAutoMessage('acymailingsubscription');
		if(empty($allMessages)) return false;

		$sendNow = 0;
		foreach($allMessages as $oneMessage){
			$commonLists = array_intersect($listids, $oneMessage->message_receiver['auto']['acymailingsubscription']['acymailinglist']);
			if(empty($commonLists)) continue;


			$acyquery = ACYSMS::get('class.acyquery');
			$integrationTo = $oneMessage->message_receiver_table;
			$integration = ACYSMS::getIntegration($integrationTo);
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);
			$acyquery->addUserFilters(array($subid), $integrationFrom, $integrationTo);

			$senddate = strtotime('+'.intval($oneMessage->message_receiver['auto']['acymailingsubscription']['delay']['duration']).' '.$oneMessage->message_receiver['auto']['acymailingsubscription']['delay']['timevalue'], time());
			$querySelect = $acyquery->getQuery(array($oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($oneMessage->message_receiver_table).','.$senddate.',0,2'));


			$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`) '.$querySelect;
			$db->setQuery($finalQuery);
			$db->query();

			if(empty($oneMessage->message_receiver['auto']['acymailingsubscription']['delay']['duration'])) $sendNow = $oneMessage->message_id;
		}

		if(!empty($sendNow)){
			$queueHelper = ACYSMS::get('helper.queue');
			$queueHelper->report = false;
			$queueHelper->message_id = $sendNow;
			$queueHelper->process();
		}
	}

	function onAcyUnsubscribe($subid, $listids){
		if(!$this->init()) return;

		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');

		if(empty($listids)) return false;

		$allMessages = $messageClass->getAutoMessage('acymailingsubscription');
		if(empty($allMessages)) return false;


		$messagesToDelete = array();
		foreach($allMessages as $oneMessage){
			$commonLists = array_intersect($listids, $oneMessage->message_receiver['auto']['acymailingsubscription']['acymailinglist']);
			if(empty($commonLists)) continue;
			$messagesToDelete[] = $oneMessage->message_id;
		}
		if(empty($messagesToDelete)) return;

		JArrayHelper::toInteger($messagesToDelete);

		$finalQuery = 'DELETE FROM #__acysms_queue WHERE queue_message_id IN ('.implode(',', $messagesToDelete).') AND queue_receiver_id = '.intval($subid).' AND queue_receiver_table = "acymailing"';
		$db->setQuery($finalQuery);
		$db->query();
	}
}//endclass
