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
defined('_JEXEC') or die('Restricted access');
?>
<?php

class FileViewFile extends acysmsView{
	function display($tpl = null){
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'frontendedition.css');
		JRequest::setVar('tmpl', 'component');
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();
		parent::display($tpl);
	}

	function css(){
		$file = JRequest::getCmd('file');
		if(!preg_match('#^([-A-Z0-9]*)_([-_A-Z0-9]*)$#i', $file, $result)){
			ACYSMS::display('Could not load the file '.$file.' properly');
			exit;
		}
		$type = $result[1];
		$fileName = $result[2];
		$content = JRequest::getString('csscontent');
		if(empty($content)) $content = file_get_contents(ACYSMS_MEDIA.'css'.DS.$type.'_'.$fileName.'.css');
		if(strpos($fileName, 'default') !== false){
			$fileName = 'custom'.str_replace('default', '', $fileName);
			$i = 1;
			while(file_exists(ACYSMS_MEDIA.'css'.DS.$type.'_'.$fileName.'.css')){
				$fileName = 'custom'.$i;
				$i++;
			}
		}

		if(JRequest::getString('tmpl') == 'component'){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->custom('savecss', JText::_('SMS_SAVE'), 'save', false);
			$acyToolbar->setTitle($type.'_'.$fileName.'.css');
			$acyToolbar->topfixed = false;
			$acyToolbar->display();
		}

		$this->assignRef('content', $content);
		$this->assignRef('fileName', $fileName);
		$this->assignRef('type', $type);
	}

	function language(){
		$this->setLayout('default');

		$code = JRequest::getCmd('code');
		if(empty($code)){
			ACYSMS::display('Code not specified', 'error');
			return;
		}

		$file = new stdClass();
		$file->name = $code;
		$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acysms.ini';
		$file->path = $path;

		jimport('joomla.filesystem.file');
		$showLatest = true;
		$loadLatest = false;

		if(JFile::exists($path)){
			$file->content = JFile::read($path);
			if(empty($file->content)){
				ACYSMS::display('File not found : '.$path, 'error');
			}
		}else{
			$loadLatest = true;
			ACYSMS::display(JText::_('SMS_LOAD_ENGLISH_1').'<br />'.JText::_('SMS_LOAD_ENGLISH_2').'<br />'.JText::_('SMS_LOAD_ENGLISH_3'), 'info');
			$file->content = JFile::read(JLanguage::getLanguagePath(JPATH_ROOT).DS.'en-GB'.DS.'en-GB.com_acysms.ini');
		}
		$custompath = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acysms_custom.ini';
		if(JFile::exists($custompath)){
			$file->customcontent = JFile::read($custompath);
		}

		if($loadLatest OR JRequest::getCmd('task') == 'latest'){
			if(file_exists(JPATH_ROOT.DS.'language'.DS.$code)){
				$doc = JFactory::getDocument();
				$doc->addScript(ACYSMS_UPDATEURL.'languageload&component=acysms&code='.JRequest::getCmd('code'));
			}else{
				ACYSMS::enqueueMessage('The specified language "'.htmlspecialchars($code, ENT_COMPAT, 'UTF-8').'" is not installed on your site', 'warning');
			}
			$showLatest = false;
		}elseif(JRequest::getCmd('task') == 'save'){
			$showLatest = false;
		}

		if(JRequest::getString('tmpl') == 'component'){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->save();
			$acyToolbar->custom('share', JText::_('SMS_SHARE'), 'share', false);
			$acyToolbar->setTitle(JText::_('SMS_FILE').' : '.$this->escape($file->name));
			$acyToolbar->topfixed = false;
			$acyToolbar->display();
		}

		$this->assignRef('showLatest', $showLatest);
		$this->assignRef('file', $file);
	}

	function share(){
		$file = new stdClass();
		$file->name = JRequest::getCmd('code');

		$acyToolbar = ACYSMS::get('helper.toolbar');
		$acyToolbar->custom('share', JText::_('SMS_SHARE'), 'share', false, "if(confirm('".JText::_('SMS_CONFIRM_SHARE_TRANS', true)."')){ javascript:submitbutton('send');} return false;");
		$acyToolbar->setTitle(JText::_('SMS_SHARE').' : '.$this->escape($file->name));
		$acyToolbar->topfixed = false;
		$acyToolbar->display();

		$this->assignRef('file', $file);
	}
}
