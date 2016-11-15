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

class ACYSMSanswermessageType{

	function __construct(){

		$query = 'SELECT `message_subject`, `message_id` FROM '.ACYSMS::table('message').' WHERE `message_type`= \'answer\'';
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$messages = $db->loadObjectList();

		$this->values = array();
		$this->values[] = JHTML::_('select.option', '0', JText::_('SMS_NO_ANSWER'));
		foreach($messages as $oneMessage){
			$this->values[] = JHTML::_('select.option', $oneMessage->message_id, '['.$oneMessage->message_id.'] '.$oneMessage->message_subject);
		}

		$js = "function changeMessage(idField,value){
			linkEdit = idField+'_edit';
			if(value>0){
				window.document.getElementById(linkEdit).href = 'index.php?option=com_acysms&tmpl=component&ctrl=message&task=answermessage&message_id='+value;
				window.document.getElementById(linkEdit).style.display = 'inline';
			}else{
				window.document.getElementById(linkEdit).style.display = 'none';
			}
		}";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	function display($value){
		JHTML::_('behavior.modal', 'a.modal');
		$linkEdit = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=message&amp;task=answermessage&amp;message_id='.$value;
		$linkAdd = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=message&amp;task=answermessage';
		$style = empty($value) ? 'style="display:none"' : '';
		$text = ' <a '.$style.' class="modal" id="answer_edit" title="'.JText::_('SMS_EDIT_MESSAGE', true).'"  href="'.$linkEdit.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><i class="smsicon-edit"></i></a>';
		$text .= ' <a class="modal" id="answer_add" title="'.JText::_('SMS_CREATE_MESSAGE', true).'"  href="'.$linkAdd.'" rel="{handler: \'iframe\', size:{x:800, y:500}}"><i class="smsicon-new"></i></a>';

		return JHTML::_('select.genericlist', $this->values, 'data[answertrigger][answertrigger_actions][sendmessage][message_id]', 'class="inputbox" style="width:auto;" size="1" onchange="changeMessage(\'answer\',this.value);"', 'value', 'text', (int)$value).$text;
	}
}
