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

class ACYSMScronHelper{
	var $report = false;
	var $messages = array();
	var $detailMessages = array();
	var $processed = false;
	var $executed = false;
	var $mainmessage = '';
	var $begin = 0;
	var $errorDetected = false;

	function __construct(){
		$this->begin = time();
	}


	function cron(){
		$time = time();
		$config = ACYSMS::config();
		$firstMessage = JText::sprintf('SMS_CRON_TRIGGERED', ACYSMS::getDate(time()));
		$this->messages[] = $firstMessage;
		if($this->report){
			ACYSMS::display($firstMessage, 'info');
		}

		if($config->get('cron_next') > $time){
			if($config->get('cron_next') > ($time + $config->get('cron_frequency'))){
				$newConfig = new stdClass();
				$newConfig->cron_next = $time + $config->get('cron_frequency');
				$config->save($newConfig);
			}
			$nottime = JText::sprintf('SMS_CRON_NEXT', ACYSMS::getDate($config->get('cron_next')));
			$this->messages[] = $nottime;
			if($this->report){
				ACYSMS::display($nottime, 'info');
			}
			return false;
		}

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();


		$queueHelper = ACYSMS::get('helper.queue');
		$this->executed = true;
		$newConfig = new stdClass();
		$newConfig->cron_next = $config->get('cron_next') + $config->get('cron_frequency');
		if($newConfig->cron_next <= $time OR $newConfig->cron_next > $time + $config->get('cron_frequency')) $newConfig->cron_next = $time + $config->get('cron_frequency');
		$newConfig->cron_last = $time;
		$config->save($newConfig);
		$queueHelper->report = false;
		$queueHelper->process();
		if(!empty($queueHelper->messages)){
			$this->detailMessages = array_merge($this->detailMessages, $queueHelper->messages);
		}
		if(!empty($queueHelper->nbprocess)) $this->processed = true;
		$this->mainmessage = JText::sprintf('SMS_CRON_PROCESS', $queueHelper->nbprocess, $queueHelper->successSend, $queueHelper->errorSend);
		if(!empty($queueHelper->errorSend)) $this->errorDetected = true;
		$this->messages[] = $this->mainmessage;
		if(!empty($queueHelper->stoptime) AND time() > $queueHelper->stoptime) return true;

		$queueClass = ACYSMS::get('class.queue');
		$resultSchedule = $queueClass->queueScheduled();
		if($resultSchedule){
			$this->detailMessages = array_merge($this->detailMessages, $queueClass->messages);
			$this->processed = true;
		}

		if($config->get('cron_daily_trigger') < $time){
			$newConfig = new stdClass();
			$newConfig->cron_daily_trigger = $config->get('cron_daily_trigger', 0) + 86400;
			if($newConfig->cron_daily_trigger <= $time) $newConfig->cron_daily_trigger = $time + 86400;
			$config->save($newConfig);
			$resultsTrigger = $dispatcher->trigger('onACYSMSDailyCron');
			if(!empty($resultsTrigger)){
				$this->processed = true;
				$this->messages = array_merge($this->messages, $resultsTrigger);
			}

			if(!empty($queueHelper->stoptime) AND time() > $queueHelper->stoptime) return true;
		}

		$resultsTrigger = $dispatcher->trigger('onACYSMSCron');
		if(!empty($resultsTrigger)){
			$this->processed = true;
			$this->messages = array_merge($this->messages, $resultsTrigger);
		}

		return true;
	}

	function report(){
		$config = ACYSMS::config();

		$sendreport = $config->get('cron_sendreport');


		if(($sendreport == 2 && $this->processed) || $sendreport == 1 || ($sendreport == 3 && $this->errorDetected)){
			$config = ACYSMS::config();
			$receiverString = $config->get('cron_sendto');

			$mailer = JFactory::getMailer();
			$mailer->isHTML(true);
			$subject = 'AcySMS Report : '.$this->mainmessage;
			$mailer->setSubject($subject);
			$body = implode('<br />', $this->detailMessages);
			$mailer->setBody($body);
			if(substr_count($receiverString, '@') > 1){
				$receivers = explode(' ', trim(preg_replace('# +#', ' ', str_replace(array(';', ','), ' ', $receiverString))));
			}else{
				$receivers[] = trim($receiverString);
			}
			if(!empty($receivers)){
				foreach($receivers as $oneReceiver){
					$mailer->addRecipient($oneReceiver);
					$send = $mailer->Send();
				}
			}
		}

		$newConfig = new stdClass();
		$newConfig->cron_report = implode("\n", $this->messages);
		if(strlen($newConfig->cron_report) > 800) $newConfig->cron_report = substr($newConfig->cron_report, 0, 795).'...';
		$config->save($newConfig);
		if($this->processed) $this->saveReport();
	}

	function saveReport(){
		$config = ACYSMS::config();
		$reportPath = JPath::clean(ACYSMS_ROOT.trim(html_entity_decode($config->get('cron_savepath'))));
		if(!is_dir(dirname($reportPath))){
			ACYSMS::createDir(dirname($reportPath));
			$htaccess = 'Order deny,allow'."\r\n".'Deny from all';
			JFile::write(dirname($reportPath).DS.'.htaccess', $htaccess);
		}
		file_put_contents($reportPath, "\r\n"."\r\n".str_repeat('*', 150)."\r\n".str_repeat('*', 20).str_repeat(' ', 5).ACYSMS::getDate(time()).str_repeat(' ', 5).str_repeat('*', 20)."\r\n", FILE_APPEND);
		@file_put_contents($reportPath, implode("\r\n", $this->messages), FILE_APPEND);
		@file_put_contents($reportPath, "\r\n"."---- Details ----"."\r\n", FILE_APPEND);
		@file_put_contents($reportPath, implode("\r\n", $this->detailMessages), FILE_APPEND);

		$currenTime = time();
		$timeCronProcess = $currenTime - $this->begin;
		@file_put_contents($reportPath, "\r\n The cron process took ".$timeCronProcess." seconds", FILE_APPEND);
	}
}//endclass
