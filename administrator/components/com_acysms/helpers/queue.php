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

class ACYSMSqueueHelper{

	var $message_id = 0;

	var $report = true;

	var $send_limit = 50;

	var $finish = false;

	var $error = false;
	var $nbprocess = 0;

	var $start = 0;
	var $stoptime = 0;
	var $successSend = 0;
	var $errorSend = 0;

	var $messages = array();

	var $detailedReport = true;
	var $detailedMessageLogs = array();

	var $nbthreads = 10;

	private $queueDelete = array();

	private $queueUpdate = array();

	private $statsAdd = array();

	private $sms = array();

	var $pause = 5;
	var $config;
	var $listsubClass;
	var $subClass;

	var $senderProfiles = array();

	var $openSend = array();
	var $mod_security2 = false;
	var $obend = 0;

	private $senderCredits = array();

	var $nbParts = 0;

	var $displayNextTry = true;

	function __construct(){
		$this->config = ACYSMS::config();
		$this->send_limit = (int)$this->config->get('queue_nbmsg', 50);
		$this->nbthreads = (int)$this->config->get('parallel_threads', 10);


		ACYSMS::increasePerf();
		@ini_set('default_socket_timeout', 10);
		@ignore_user_abort(true);

		$timelimit = ini_get('max_execution_time');
		if(!empty($timelimit)){
			$this->stoptime = time() + $timelimit - 4;
		}
		$this->db = JFactory::getDBO();
		$this->begin = microtime(true);
	}

	public function process(){
		$phoneHelper = ACYSMS::get('helper.phone');
		$queueClass = ACYSMS::get('class.queue');
		$senderProfileClass = ACYSMS::get('class.senderprofile');
		$messageClass = ACYSMS::get('class.message');
		$customerClass = ACYSMS::get('class.customer');
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$queueElements = $queueClass->getReady($this->send_limit, $this->message_id);
		$app = JFactory::getApplication();

		$ctrl = ($app->isAdmin() ? 'message' : 'frontmessage');

		$nbCreditsLeft = array();

		if(empty($queueElements)){
			$this->finish = true;
			return true;
		}

		$currentMessage = $this->start;
		$this->nbprocess = 0;
		if(count($queueElements) < $this->send_limit){
			$this->finish = true;
		}


		if($this->report){
			if(function_exists('apache_get_modules')){
				$modules = apache_get_modules();
				$this->mod_security2 = in_array('mod_security2', $modules);
			}
			if(!headers_sent()){
				while(ob_get_level() > 0 && $this->obend++ < 3){
					@ob_end_flush();
				}
			}

			$disp = '<html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />';
			$disp .= '<title>'.addslashes(JText::_('SMS_SEND_PROCESS')).'</title>';
			$disp .= '<style>body{font-size:12px;font-family: Arial,Helvetica,sans-serif;}</style></head><body>';
			$disp .= "<div style='position:fixed; top:3px;left:3px;background-color : white;border : 1px solid grey; padding : 3px;font-size:14px'>";
			$disp .= "<span id='divpauseinfo' style='padding:10px;margin:5px;font-size:16px;font-weight:bold;display:none;background-color:black;color:white;'> </span>";
			$disp .= JText::sprintf('SMS_SEND_X_Y', '<span id="counter" >'.$this->start.'</span>', $this->total);
			$disp .= '</div>';
			$disp .= "<div id='divinfo' style='display:none; position:fixed; bottom:3px;left:3px;background-color : white; border : 1px solid grey; padding : 3px;'> </div>";
			$disp .= '<br /><br />';
			$url = JURI::base().'index.php?option=com_acysms&ctrl='.$ctrl.'&tmpl=component&task=processQueue&message_id='.$this->message_id.'&totalsend='.$this->total.'&alreadysent=';
			$disp .= '<script type="text/javascript" language="javascript">';
			$disp .= 'var mycounter = document.getElementById("counter");';
			$disp .= 'var divinfo = document.getElementById("divinfo");
					var divpauseinfo = document.getElementById("divpauseinfo");
					function setInfo(message){ divinfo.style.display = \'block\';divinfo.innerHTML=message; }
					function setPauseInfo(nbpause){ divpauseinfo.style.display = \'\';divpauseinfo.innerHTML=nbpause;}
					function setCounter(val){ mycounter.innerHTML=val;}
					var scriptpause = '.intval($this->pause).';
					function handlePause(){
						setPauseInfo(scriptpause);
						if(scriptpause > 0){
							scriptpause = scriptpause - 1;
							setTimeout(\'handlePause()\',1000);
						}else{
							document.location.href=\''.$url.'\'+mycounter.innerHTML;
						}
					}
					</script>';
			echo $disp;
			if(function_exists('ob_flush')) @ob_flush();
			if(!$this->mod_security2) @flush();
		}//endifreport

		$phoneArray = array();
		foreach($queueElements as $oneQueue){
			if(empty($oneQueue->receiver_phone)) continue;
			$validPhone = $phoneHelper->getValidNum($oneQueue->receiver_phone);
			if($validPhone != false) $phoneArray[] = $this->db->Quote($validPhone);
		}

		$blockedPhoneNumbers = array();
		if(!empty($phoneArray)){
			$query = 'SELECT phone_number FROM #__acysms_phone WHERE phone_number IN ('.implode(',', $phoneArray).')';
			$this->db->setQuery($query);
			$blockedPhoneNumbers = $this->db->loadObjectList('phone_number');
		}

		if(defined('JDEBUG') && JDEBUG && $this->detailedReport) $i = 0;

		foreach($queueElements as $oneQueue){
			$sendit = true;
			$messageOnScreen = '';
			$blockedUser = false;

			$currentMessage++;
			$this->nbprocess++;

			if($this->report){
				echo '<script type="text/javascript" language="javascript">setCounter('.$currentMessage.')</script>';
				if(function_exists('ob_flush')) @ob_flush();
				if(!$this->mod_security2) @flush();
			}

			if(!isset($this->sms[$oneQueue->queue_message_id])){
				$this->sms[$oneQueue->queue_message_id] = $messageClass->get($oneQueue->queue_message_id);
				$dispatcher->trigger('onACYSMSReplaceTags', array(&$this->sms[$oneQueue->queue_message_id], true));
			}

			$myMessage = clone($this->sms[$oneQueue->queue_message_id]);

			$phone = $phoneHelper->getValidNum($oneQueue->receiver_phone);

			if(!$phone){
				$sendit = false;
				$messageOnScreen .= $phoneHelper->error;
			}elseif(isset($blockedPhoneNumbers[$phone]) && $this->sms[$oneQueue->queue_message_id]->message_type != 'activation_optin'){
				$blockedUser = true;
				$sendit = false;
				$messageOnScreen .= JText::sprintf('SMS_USER_BLOCKED', $oneQueue->queue_receiver_id.' ( '.$phone.' )');
			}


			if(!isset($this->senderProfiles[$myMessage->message_senderprofile_id])){
				$gateway = $senderProfileClass->getGateway($myMessage->message_senderprofile_id);

				if(!$gateway){
					$this->finish = true;
					$this->display('Could not load the sender profile with the ID : '.$myMessage->message_senderprofile_id.'. Please edit the message '.$myMessage->message_subject.' and select the sender profile', false, $currentMessage);
					break;
				}
				$this->senderProfiles[$myMessage->message_senderprofile_id] = $gateway;

				if(!$this->senderProfiles[$myMessage->message_senderprofile_id]->open()){
					$this->finish = true;
					$this->display(implode('<br />', $this->senderProfiles[$myMessage->message_senderprofile_id]->errors));
					unset($this->senderProfiles[$myMessage->message_senderprofile_id]);
					break;
				}
			}
			$this->senderProfiles[$myMessage->message_senderprofile_id]->errors = array();
			$dispatcher->trigger('onACYSMSReplaceUserTags', array(&$myMessage, &$oneQueue, true));


			if(!empty($myMessage->message_usecredits)){
				$this->nbParts = $messageClass->countMessageParts($myMessage->message_body)->nbParts;

				if(empty($nbCreditsLeft[$myMessage->message_senderid])) $nbCreditsLeft[$myMessage->message_senderid] = $customerClass->getCredits($myMessage->message_senderid);
				$nbCreditsLeft[$myMessage->message_senderid] = $nbCreditsLeft[$myMessage->message_senderid] - $this->nbParts;

				if(($nbCreditsLeft[$myMessage->message_senderid]) < 0){
					$messageOnScreen = JText::_('SMS_NOT_ENOUGH_CREDITS');
					$myMessage->message_status = 'waitingcredits';
					$messageClass->save($myMessage);
					$this->display($messageOnScreen, false, $currentMessage);
					$customerClass->sendLowCreditsNotification($myMessage->message_senderid);
					continue;
				}
			}

			$myresult = new stdClass();
			$myresult->queue = $oneQueue;
			$myresult->message = $myMessage;
			$myresult->currentMessage = $currentMessage;

			$myresult->smsid = $oneQueue->queue_receiver_id.'_'.$myMessage->message_id.'_'.str_replace('.', '', microtime(true));

			if(!$sendit){
				$this->errorSend++;
				$this->queueDelete[$oneQueue->queue_message_id][$oneQueue->queue_receiver_table][] = $oneQueue->queue_receiver_id;
				if($blockedUser){
					$this->statsAdd[$oneQueue->queue_message_id][$oneQueue->queue_receiver_table][-3][] = array('smsid' => $myresult->smsid, 'receiverid' => $oneQueue->queue_receiver_id, 'errors' => implode(' | ', $this->senderProfiles[$myMessage->message_senderprofile_id]->errors));
				}else $this->statsAdd[$oneQueue->queue_message_id][$oneQueue->queue_receiver_table][0][] = array('smsid' => $myresult->smsid, 'receiverid' => $oneQueue->queue_receiver_id, 'errors' => implode(' | ', $this->senderProfiles[$myMessage->message_senderprofile_id]->errors));

				$messageOnScreen = '['.$oneQueue->queue_message_id.'] '.$messageOnScreen;
				$this->display($messageOnScreen, false, $currentMessage);
				continue;
			}

			if(defined('JDEBUG') && JDEBUG && $this->detailedReport){
				$currenTime = microtime(true);
				$timeCronProcess = $currenTime - $this->begin;

				$messageOnScreen = 'Start opening socket '.$i.' within : '.number_format($timeCronProcess, 2, ',', 3).' ms <br> ';
				$this->begin = $currenTime;
			}

			$this->senderProfiles[$myMessage->message_senderprofile_id]->fullMessage = $myMessage;
			$myresult->openSendId = $this->senderProfiles[$myMessage->message_senderprofile_id]->openSend($myMessage->message_body, $phone);

			if(defined('JDEBUG') && JDEBUG && $this->detailedReport){
				$currenTime = microtime(true);
				$timeCronProcess = $currenTime - $this->begin;
				$messageOnScreen .= 'Socket '.$i.' opened within : '.number_format($timeCronProcess, 2, ',', 3).' ms <br> ';
				$this->begin = $currenTime;
			}
			if($myresult->openSendId === false){
				$messageOnScreen .= $this->_handleErrors($myresult);
				$messageOnScreen = '['.$myresult->queue->queue_message_id.'] '.$messageOnScreen;
				$this->display($messageOnScreen, $myresult->openSendId, $myresult->currentMessage);
				continue;
			}

			$this->openSend[] = $myresult;

			if(!empty($this->senderProfiles[$myMessage->message_senderprofile_id]->waittosend)) sleep(intval($this->senderProfiles[$myMessage->message_senderprofile_id]->waittosend));

			if(defined('JDEBUG') && JDEBUG && $this->detailedReport){
				$messageOnScreen = '['.$myresult->queue->queue_message_id.'] '.$messageOnScreen;
				$this->display($messageOnScreen, $myresult->openSendId, $myresult->currentMessage);
				$i++;
			}

			if($this->nbprocess % $this->nbthreads == 0){
				$this->closeAllConnections();
				$deleteOk = $this->deleteQueue();
				$this->statsAdd();
				$this->queueUpdate();

				if(!$deleteOk) break;
			}

			if(!empty($this->stoptime) AND $this->stoptime < time()){
				$this->display(JText::_('SMS_REFRESH_TIMEOUT'));
				break;
			}
		}

		$this->closeAllConnections();
		$this->deleteQueue();
		$this->statsAdd();
		$this->queueUpdate();


		if(!empty($this->senderCredits)){
			foreach($this->senderCredits as $oneSender => $creditsToRemove){
				$customerClass = ACYSMS::get('class.customer');
				$customer = $customerClass->getCustomerByJoomID($oneSender);
				$customer->customer_credits -= $creditsToRemove;
				$customerClass->save($customer);
			}
		}

		if(!empty($this->senderProfiles)){
			foreach($this->senderProfiles as $gateway){
				$gateway->close();
			}
		}

		if(!empty($this->total) AND $currentMessage >= $this->total){
			$this->finish = true;
		}
		if($this->report && !$this->finish){
			echo '<script type="text/javascript" language="javascript">handlePause();</script>';
		}
		if($this->report){
			echo "</body></html>";
			while($this->obend-- > 0){
				ob_start();
			}
			exit;
		}

		return true;
	}

	private function closeAllConnections(){

		if(empty($this->openSend)) return;

		if(defined('JDEBUG') && JDEBUG && $this->detailedReport) static $Connectioni = 0;

		foreach($this->openSend as $oneConnection){
			$messageOnScreen = '';

			if(defined('JDEBUG') && JDEBUG && $this->detailedReport){
				$currenTime = microtime(true);
				$timeCronProcess = $currenTime - $this->begin;
				$messageOnScreen .= 'Start closing socket '.$Connectioni.' within : '.number_format($timeCronProcess, 2, ',', 3).' ms <br> ';
				$this->begin = $currenTime;
			}

			$oneConnection->smsid = $oneConnection->queue->queue_receiver_id.'_'.$oneConnection->message->message_id.'_'.time();
			$result = $this->senderProfiles[$oneConnection->message->message_senderprofile_id]->closeSend($oneConnection->openSendId);
			if(!empty($this->senderProfiles[$oneConnection->message->message_senderprofile_id]->smsid)) $oneConnection->smsid = $this->senderProfiles[$oneConnection->message->message_senderprofile_id]->smsid;

			if(defined('JDEBUG') && JDEBUG && $this->detailedReport){
				$currenTime = microtime(true);
				$timeCronProcess = $currenTime - $this->begin;
				$messageOnScreen .= 'Socket '.$Connectioni.' closed within : '.number_format($timeCronProcess, 2, ',', 3).' ms <br> ';
				$this->begin = $currenTime;
			}

			if($result){
				$this->successSend++;
				$this->queueDelete[$oneConnection->queue->queue_message_id][$oneConnection->queue->queue_receiver_table][] = $oneConnection->queue->queue_receiver_id;
				$this->statsAdd[$oneConnection->queue->queue_message_id][$oneConnection->queue->queue_receiver_table][1][] = array('smsid' => $oneConnection->smsid, 'receiverid' => $oneConnection->queue->queue_receiver_id, 'errors' => '');

				if($oneConnection->message->message_usecredits) $this->senderCredits[$oneConnection->message->message_senderid] = intval(@$this->senderCredits[$oneConnection->message->message_senderid]) + $this->nbParts;
				$replace = array('{user_name}', '{user_phone_number}', '{message_subject}');
				$replaceby = array($oneConnection->queue->receiver_name, '<b><i>'.$oneConnection->queue->receiver_phone.'</i></b>', '<b><i>'.$oneConnection->message->message_subject.'</i></b>');
				$messageOnScreen .= str_replace($replace, $replaceby, JText::_('SMS_SUCC_SENT'));
			}else{
				$messageOnScreen .= $this->_handleErrors($oneConnection);
			}

			$messageOnScreen = '['.$oneConnection->queue->queue_message_id.'] '.$messageOnScreen;
			$this->display($messageOnScreen, $result, $oneConnection->currentMessage);

			if(defined('JDEBUG') && JDEBUG && $this->detailedReport) $Connectioni++;
		}

		$this->openSend = array();
	}

	private function deleteQueue(){

		if(empty($this->queueDelete)) return true;

		$status = true;
		foreach($this->queueDelete as $message_id => $subinfos){
			foreach($subinfos as $receiver_table => $receiver_ids){
				JArrayHelper::toInteger($receiver_ids);

				$nbsub = count($receiver_ids);
				$query = 'DELETE FROM #__acysms_queue WHERE queue_message_id = '.intval($message_id).' AND queue_receiver_table = '.$this->db->Quote($receiver_table).' AND queue_receiver_id IN ('.implode(',', $receiver_ids).') LIMIT '.$nbsub;
				$this->db->setQuery($query);
				if(!$this->db->query()){
					$status = false;
					$this->finish = true;
					$this->display($this->db->getErrorNum.' : '.$this->db->getErrorMsg());
				}else{
					$nbdeleted = $this->db->getAffectedRows();
					if($nbdeleted != $nbsub){
						$status = false;
						$this->finish = true;
						$this->display(JText::_('SMS_QUEUE_DOUBLE'));
					}
				}
			}
		}
		$this->queueDelete = array();

		return $status;
	}

	private function statsAdd(){
		if(empty($this->statsAdd)) return true;
		$time = time();
		$query = 'INSERT INTO #__acysms_statsdetails (statsdetails_message_id,statsdetails_sentdate,statsdetails_status,statsdetails_receiver_id,statsdetails_receiver_table,statsdetails_sms_id, statsdetails_error) VALUES ';
		foreach($this->statsAdd as $message_id => $subinfos){

			$this->db->setQuery('INSERT IGNORE INTO #__acysms_stats (stats_message_id,stats_nbsent,stats_nbfailed) VALUES ('.$message_id.',0,0)');
			$this->db->query();

			foreach($subinfos as $receiver_table => $infos){
				foreach($infos as $status => $messageInformations){
					$fieldUpdate = ($status > 0) ? 'stats_nbsent' : 'stats_nbfailed';
					$this->db->setQuery('UPDATE #__acysms_stats SET '.$fieldUpdate.' = '.$fieldUpdate.' + '.count($messageInformations).' WHERE stats_message_id = '.intval($message_id));
					$this->db->query();
					foreach($messageInformations as $oneInformation => $informations){
						$query .= '('.$this->db->quote($message_id).','.$this->db->quote($time).','.$this->db->quote($status).','.$this->db->quote($informations['receiverid']).','.$this->db->Quote($receiver_table).','.$this->db->quote($informations['smsid']).','.$this->db->quote($informations['errors']).'),';
					}
				}
			}
		}
		$query = rtrim($query, ',');
		$this->db->setQuery($query);
		$this->db->query();

		$this->statsAdd = array();
	}

	private function queueUpdate(){
		if(empty($this->queueUpdate)) return true;

		$delay = 600;
		foreach($this->queueUpdate as $message_id => $subinfos){
			foreach($subinfos as $receiver_table => $receiver_ids){
				JArrayHelper::toInteger($receiver_ids);
				$query = 'UPDATE #__acysms_queue SET queue_senddate = queue_senddate + '.$delay.', queue_try = queue_try +1 WHERE queue_message_id = '.intval($message_id).' AND queue_receiver_table = '.$this->db->Quote($receiver_table).' AND queue_receiver_id IN ('.implode(',', $receiver_ids).')';
				$this->db->setQuery($query);
				$this->db->query();
			}
		}
		$this->queueUpdate = array();
	}


	private function display($message, $status = '', $num = ''){
		$this->messages[] = strip_tags($message);

		if($this->detailedReport){
			$this->detailedMessageLogs[$status][] = strip_tags($message);
		}

		if(!$this->report) return;
		if(!empty($num)){
			$color = $status ? 'green' : 'red';
			echo '<br />'.$num.' : <font color="'.$color.'">'.$message.'</font>';
		}else{
			echo '<script type="text/javascript" language="javascript">setInfo(\''.addslashes($message).'\')</script>';
		}
		if(function_exists('ob_flush')) @ob_flush();
		@flush();
	}

	private function _handleErrors($oneConnection){

		$maxTry = (int)$this->config->get('queue_try', 3);

		$this->errorSend++;
		$messageOnScreen = JText::sprintf('SMS_ERROR_SENT', '<b><i>'.$oneConnection->message->message_subject.'</i></b>', '<b><i>'.$oneConnection->queue->receiver_phone.'</i></b>');
		if(!empty($this->senderProfiles[$oneConnection->message->message_senderprofile_id]->errors)) $messageOnScreen .= ' : '.implode(' | ', $this->senderProfiles[$oneConnection->message->message_senderprofile_id]->errors);

		if(empty($maxTry) || $oneConnection->queue->queue_try < $maxTry - 1 || $this->senderProfiles[$oneConnection->message->message_senderprofile_id]->keepInQueue){
			if($this->displayNextTry) $messageOnScreen .= ' => '.JText::_('SMS_QUEUE_NEXT_TRY');
			$this->queueUpdate[$oneConnection->queue->queue_message_id][$oneConnection->queue->queue_receiver_table][] = $oneConnection->queue->queue_receiver_id;
		}else{
			$this->queueDelete[$oneConnection->queue->queue_message_id][$oneConnection->queue->queue_receiver_table][] = $oneConnection->queue->queue_receiver_id;
			$this->statsAdd[$oneConnection->queue->queue_message_id][$oneConnection->queue->queue_receiver_table][0][] = array('smsid' => $oneConnection->smsid, 'receiverid' => $oneConnection->queue->queue_receiver_id, 'errors' => implode(' | ', $this->senderProfiles[$oneConnection->message->message_senderprofile_id]->errors));
		}
		$this->senderProfiles[$oneConnection->message->message_senderprofile_id]->errors = array();

		return $messageOnScreen;
	}
}
