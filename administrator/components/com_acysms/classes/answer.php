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

class ACYSMSanswerClass extends ACYSMSClass{

	var $tables = array('answer' => 'answer_id');
	var $pkey = 'answer_id';


	function get($id, $default = null){
		$this->database->setQuery('SELECT * FROM #__acysms_answer WHERE answer_id = '.intval($id).' LIMIT 1');
		$answerTrigger = $this->database->loadObject();
		return $answerTrigger;
	}


	function addAnswer($answerInformations){
		if(is_array($answerInformations)){
			$answerId = array();
			foreach($answerInformations as $oneAnswerInformation) $answerId[] = $this->_addAnswerEntry($oneAnswerInformation);
			return $answerId;
		}else{
			return $this->_addAnswerEntry($answerInformations);
		}
	}

	private function _addAnswerEntry($answerEntry){
		$integration = ACYSMS::getIntegration();
		$db = JFactory::getDBO();
		$phoneHelper = ACYSMS::get('helper.phone');

		if(empty($answerEntry->answer_body)) return;

		if(empty($answerEntry->answer_date)) $answerEntry->answer_date = time();
		if(!is_numeric($answerEntry->answer_date)) $answerEntry->answer_date = strtotime($answerEntry->answer_date);

		if(!empty($answerEntry->concat)){

			$query = 'SELECT answer_id FROM #__acysms_answer WHERE answer_from = '.$db->Quote($answerEntry->answer_from).' AND answer_to = '.$db->Quote($answerEntry->answer_to).' ORDER BY answer_date DESC LIMIT 1';
			$db->setQuery($query);
			$answerId = $db->loadResult();

			$query = 'UPDATE #__acysms_answer SET answer_body = CONCAT(answer_body ,'.$db->Quote(' '.$answerEntry->answer_body).') WHERE answer_id = '.intval($answerId);
			$db->setQuery($query);
			$db->query();

			return $answerId;
		}


		if(!empty($answerEntry->answer_sms_id) && empty($answerEntry->answer_receiver_id)){
			$query = 'SELECT statsdetails_message_id, statsdetails_receiver_id, statsdetails_receiver_table FROM #__acysms_statsdetails WHERE statsdetails_sms_id = '.$db->Quote($answerEntry->answer_sms_id);
			$db->setQuery($query);
			$receiver = $db->loadObject();

			if(!empty($receiver)){
				$answerEntry->answer_message_id = $receiver->statsdetails_message_id;
				$answerEntry->answer_receiver_id = $receiver->statsdetails_receiver_id;
				$answerEntry->answer_receiver_table = $receiver->statsdetails_receiver_table;
			}
		}
		if(!empty($answerEntry->answer_from) && empty($answerEntry->answer_message_id) && $phoneHelper->getValidNum($answerEntry->answer_from) != false){
			$informations = $integration->getInformationsByPhoneNumber($phoneHelper->getValidNum($answerEntry->answer_from));

			if(empty($informations->receiver_id)) return $this->save($answerEntry);

			$query = 'SELECT statsdetails_message_id FROM #__acysms_statsdetails WHERE statsdetails_receiver_id = '.intval($informations->receiver_id).' LIMIT 1';
			$db->setQuery($query);
			$msgId = $db->loadResult();


			if(!empty($informations)){
				if(!empty($msgId)) $answerEntry->answer_message_id = $msgId;
				$answerEntry->answer_receiver_id = $informations->receiver_id;
				$answerEntry->answer_receiver_table = $integration->componentName;
			}
		}
		return $this->save($answerEntry);
	}

	function processAnswerTriggers($answer_id){

		if(empty($answer_id)) return;

		$db = JFactory::getDBO();

		$answer = $this->get($answer_id);
		$queryForTriggerList = 'SELECT * FROM '.ACYSMS::table('answertrigger').' WHERE answertrigger_publish = 1 ORDER BY answertrigger_ordering ';
		$db->setQuery($queryForTriggerList);
		$answerTriggerList = $db->loadObjectList();
		if(empty($answerTriggerList)) return;

		$groupClass = ACYSMS::get('class.group');
		$groupList = $groupClass->getGroups();


		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();


		foreach($answerTriggerList as $oneAnswerTrigger){
			$triggers = unserialize($oneAnswerTrigger->answertrigger_triggers);
			if(!empty($triggers['selected'])){
				$selectedTrigger = $triggers['selected'];
			}else if(empty($triggers['attachment'])) continue;

			if(!empty($selectedTrigger) && !empty($triggers[$selectedTrigger])){
				if($selectedTrigger == 'regex'){
					$regex = '#'.$triggers[$selectedTrigger].'#is';
				}else if($selectedTrigger == 'word') $regex = '#^'.preg_quote($triggers[$selectedTrigger], '#').'$#is';

				if(!preg_match($regex, $answer->answer_body, $result)) continue;
			}

			$actions = unserialize($oneAnswerTrigger->answertrigger_actions);

			if($selectedTrigger == 'groupname'){
				$actions['groupsdetected'] = array();
				foreach($groupList as $oneGroup){
					if(preg_match('#'.$oneGroup->group_name.'#is', $answer->answer_body)){
						array_push($actions['groupsdetected'], $oneGroup->group_id);
					}
				}
				if(empty($actions['groupsdetected'])) continue;
			}


			if(!empty($triggers['attachment']) && $triggers['attachment'] == 'contains' && empty($answer->answer_attachment)) continue;

			if(empty($actions['selected'])) break;


			foreach($actions['selected'] as $oneAction) $dispatcher->trigger('onACYSMSTriggerActions_'.$oneAction, array($actions, $answer));
		}
	}
}
