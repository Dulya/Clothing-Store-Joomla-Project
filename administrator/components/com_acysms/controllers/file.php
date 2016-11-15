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

class FileController extends ACYSMSController{
	function language(){
		JRequest::setVar('layout', 'language');
		return parent::display();
	}

	function save(){
		JRequest::checkToken() or die('Invalid Token');
		$this->_savelanguage();
		return $this->language();
	}

	function savecss(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('configuration', 'manage')) return;

		$file = JRequest::getCmd('file');
		if(!preg_match('#^([-a-z0-9]*)_([-_a-z0-9]*)$#i', $file, $result)){
			ACYSMS::enqueueMessage('Could not load the file '.$file.' properly');
			exit;
		}
		$type = $result[1];
		$fileName = $result[2];
		jimport('joomla.filesystem.file');
		$path = ACYSMS_MEDIA.'css'.DS.$type.'_'.$fileName.'.css';
		$csscontent = JRequest::getString('csscontent');
		$alreadyExists = file_exists($path);
		if(JFile::write($path, $csscontent)){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'success');
			$varName = JRequest::getCmd('var');
			if(!$alreadyExists){
				$js = "var optn = document.createElement(\"OPTION\");
						optn.text = '$fileName'; optn.value = '$fileName';
						mydrop = window.top.document.getElementById('".$varName."_choice');
						mydrop.options.add(optn);
						lastid = 0; while(mydrop.options[lastid+1]){lastid = lastid+1;} mydrop.selectedIndex = lastid;
						window.top.updateCSSLink('".$varName."','$type','$fileName');";
				$doc = JFactory::getDocument();
				$doc->addScriptDeclaration($js);
			}
			$config = ACYSMS::config();
			$newConfig = new stdClass();
			$newConfig->$varName = $fileName;
			$config->save($newConfig);
		}else{
			ACYSMS::enqueueMessage(JText::sprintf('SMS_FAIL_SAVE', $path), 'error');
		}
		return $this->css();
	}

	function css(){
		JRequest::setVar('layout', 'css');
		return parent::display();
	}

	function latest(){
		return $this->language();
	}

	function share(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('configuration', 'manage')) return;
		if($this->_savelanguage()){
			JRequest::setVar('layout', 'share');
			return parent::display();
		}else{
			return $this->language();
		}
	}

	function send(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('configuration', 'manage')) return;

		$bodyMessage = JRequest::getString('msgbody');
		$config = ACYSMS::config();
		$code = JRequest::getString('code');
		JRequest::setVar('code', $code);
		if(empty($code)) return;
		$mailer = JFactory::getMailer();
		$mailer->isHTML(true);
		$user = JFactory::getUser();
		$sender = array($user->email, $user->username);
		$mailer->setSender($sender);
		$mailer->addRecipient(array('translate@acyba.com', $user->email));
		$subject = '[ACYSMS LANGUAGE FILE] '.$code;
		$mailer->setSubject($subject);
		$body = 'The website '.ACYSMS_LIVE.' using AcySMS '.$config->get('version').' sent a language file : '.$code;
		$body .= "\n"."\n"."\n".$bodyMessage;

		$extrafile = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acysms_custom.ini';

		if(file_exists($extrafile)){
			$mailer->Body .= "\n"."\n"."\n".'Custom content:'."\n".file_get_contents($extrafile);
		}

		$mailer->setBody($body);
		jimport('joomla.filesystem.file');
		$path = JPath::clean(JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acysms.ini');
		$mailer->AddAttachment($path);
		$send = $mailer->Send();
		if($send == true){
			ACYSMS::display(JText::_('SMS_THANK_YOU_SHARING'), 'success');
		}else{
			ACYSMS::display(JText::_('SMS_ERROR_SENDING_LANGUAGE'), 'error');
		}
	}

	function _savelanguage(){
		if(!$this->isAllowed('configuration', 'manage')) return;

		jimport('joomla.filesystem.file');
		$code = JRequest::getString('code');
		JRequest::setVar('code', $code);
		$content = JRequest::getVar('content', '', '', 'string', JREQUEST_ALLOWHTML);
		if(empty($code) OR empty($content)) return;
		$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acysms.ini';
		$result = JFile::write($path, $content);
		if($result){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'success');
			$js = "window.top.document.getElementById('image$code').className = 'acyicon-edit'";
			$doc = JFactory::getDocument();
			$doc->addScriptDeclaration($js);
			$updateHelper = ACYSMS::get('helper.update');
			$updateHelper->installMenu($code);
		}else{
			ACYSMS::enqueueMessage(JText::sprintf('SMS_FAIL_SAVE', $path), 'error');
		}
		$customcontent = JRequest::getVar('customcontent', '', '', 'string', JREQUEST_ALLOWHTML);
		$custompath = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acysms_custom.ini';
		$customresult = JFile::write($custompath, $customcontent);
		if(!$customresult) ACYSMS::enqueueMessage(JText::sprintf('SMS_FAIL_SAVE', $custompath), 'error');

		return $result;
	}
}
