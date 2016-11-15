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

class TagViewTag extends acysmsView{

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function tag(){

		JPluginHelper::importPlugin('acysms');

		$dispatcher = JDispatcher::getInstance();
		$app = JFactory::getApplication();
		$fctplug = $app->getUserStateFromRequest(ACYSMS_COMPONENT.".content", 'fctplug', 'cmd');

		$functionToTrigger = 'onACYSMS'.$fctplug;
		ob_start();
		$dispatcher->trigger($functionToTrigger);
		$defaultContent = ob_get_clean();

		$js = 'function insertTag(){window.parent.insertTag(window.document.getElementById(\'tagstring\').value); acysms_js.closeBox(true);}';
		$js .= 'function setTag(tagvalue){window.document.getElementById(\'tagstring\').value = tagvalue;}';
		$js .= 'function showTagButton(){window.document.getElementById(\'insertButton\').style.display = \'inline\'; window.document.getElementById(\'tagstring\').style.display=\'inline\';}';
		$js .= 'function hideTagButton(){}';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);


		$this->assignRef('fctplug', $fctplug);
		$this->assignRef('defaultContent', $defaultContent);
		$app = JFactory::getApplication();
		$this->assignRef('app', $app);
		$ctrl = JRequest::getString('ctrl');
		$this->assignRef('ctrl', $ctrl);
		$content = JRequest::getString('content');
		$this->assignRef('content', $content);
	}
}
