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
if(version_compare(JVERSION,'1.6.0','<')){
	class JElementHelp extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			JHTML::_('behavior.modal','a.modal');
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
				return 'This module can not work without the AcySMS Component';
			}

			$link = ACYSMS_HELPURL.$value;
			$text = '<a class="modal" title="'.JText::_('SMS_HELP',true).'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><button class="acysms_button" onclick="return false">'.JText::_('SMS_HELP').'</button></a>';
			return $text;
		}
	}
}else{
	class JFormFieldHelp extends JFormField
	{
		var $type = 'help';
		function getInput() {
			 JHTML::_('behavior.modal','a.modal');
			if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
				return 'This module can not work without the AcySMS Component';
			}
			$lang = JFactory::getLanguage();
			$lang->load(ACYSMS_COMPONENT,JPATH_SITE);

			$link = ACYSMS_HELPURL.$this->value;
			$text = '<a class="modal" title="'.JText::_('SMS_HELP',true).'"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><button class="acysms_button" onclick="return false">'.JText::_('SMS_HELP').'</button></a>';
			return $text;
		}
	}
}
