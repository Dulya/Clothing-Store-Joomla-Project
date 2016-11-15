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

class ACYSMStoolbarHelper{
	var $buttons = array();
	var $buttonOptions = array();
	var $title = '';
	var $titleLink = '';

	var $topfixed = true;

	var $htmlclass = '';

	function setTitle($name, $link = ''){
		$this->title = $name;
		$this->titleLink = $link;
		ACYSMS::setPageTitle($name);
	}

	function custom($task, $text, $class, $listSelect = true, $onClick = ''){

		$confirm = !ACYSMS_J16 ? JText::sprintf('PLEASE MAKE A SELECTION FROM THE LIST TO', strtolower(JText::_('SMS_'.strtoupper($task)))) : JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
		$submit = !ACYSMS_J16 ? "submitbutton('".$task."')" : "Joomla.submitbutton('".$task."')";
		$js = !empty($listSelect) ? "if(document.adminForm.boxchecked.value==0){alert('".str_replace(array("'", '"'), array("\'", '\"'), $confirm)."');return false;}else{".$submit."}" : $submit;

		$onClick = !empty($onClick) ? $onClick : $js;


		if(empty($this->buttonOptions)){
			$this->buttons[] = '<button id="toolbar-'.$class.'" onclick="'.$onClick.'" class="acysmstoolbar_'.$class.'" title="'.$text.'"><i class="smsicon-'.$class.'"></i><span>'.$text.'</span></button>';
			return;
		}

		$dropdownOptions = '<ul class="buttonOptions" style="padding: 0px; margin: 0px; text-align: left;">';
		foreach($this->buttonOptions as $oneOption){
			$dropdownOptions .= '<li>'.$oneOption.'</li>';
		}
		$dropdownOptions .= '</ul>';

		$buttonArea = '<button onclick="'.$onClick.'" class="acysmstoolbar_'.$class.'" title="'.$text.'"><i class="smsicon-'.$class.'"></i><span>'.$text.'</span></button>';


		$this->buttons[] = '<div style="display:inline;" class="subbuttonactions">'.$buttonArea.'<span class="acysmstoolbar_hover"><span style="vertical-align: top; display:inline-block; padding-top:10px;" class="smsicon-down"></span><span class="acysmstoolbar_hover_display">'.$dropdownOptions.'</span></span></div>';

		$this->buttonOptions = array();
	}

	function display(){
		$classCtrl = JRequest::getCmd('ctrl', '');
		echo '<div '.(empty($this->topfixed) ? '' : 'id="acysmsmenu_top"').' class="acysmstoolbarmenu donotprint '.(empty($this->topfixed) ? '' : 'acysmsaffix-top ').(!empty($classCtrl) ? 'acysmstopmenu_'.$classCtrl.' ' : '').$this->htmlclass.'" >';
		if(!empty($this->title)){
			$title = htmlspecialchars($this->title, ENT_COMPAT, 'UTF-8');
			if(!empty($this->titleLink)) $title = '<a style="color:white;" href="'.ACYSMS::completeLink($this->titleLink).'">'.$title.'</a>';
			echo '<span class="acysmstoolbartitle">'.$title.'</span>';
		}
		echo '<div class="acysmstoolbarmenu_menu">';
		echo implode(' ', $this->buttons);
		echo '</div></div>';

		$types = array('acysmsmessagesuccess' => 'success', 'acysmsmessageinfo' => 'info', 'acysmsmessagewarning' => 'warning', 'acysmsmessageerror' => 'error', 'acysmsmessagenotice' => 'notice', 'acysmsmessagemessage' => 'message');
		foreach($types as $key => $type){
			if(empty($_SESSION[$key])) continue;
			ACYSMS::display($_SESSION[$key], $type);
			unset($_SESSION[$key]);
		}
	}

	function add(){
		$this->custom('add', JText::_('SMS_NEW'), 'new', false);
	}

	function edit(){
		$this->custom('edit', JText::_('SMS_EDIT'), 'edit', true);
	}

	function delete(){
		$selectMessage = ACYSMS_J16 ? JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST') : JText::sprintf('PLEASE MAKE A SELECTION FROM THE LIST TO', strtolower(JText::_('SMS_DELETE')));
		$onClick = 'if(document.adminForm.boxchecked.value==0){
						alert(\''.str_replace("'", "\\'", $selectMessage).'\');
					}else{
						if(confirm(\''.str_replace("'", "\\'", JText::_('SMS_VALIDDELETEITEMS', true)).'\')){
							'.(ACYSMS_J16 ? 'Joomla.' : '').'submitbutton(\'remove\');
						}
					}';
		$this->custom('remove', JText::_('SMS_DELETE'), 'delete', true, $onClick);
	}

	function copy(){
		$this->custom('copy', JTEXT::_('SMS_COPY'), 'copy', true);
	}

	function link($link, $text, $class){
		$onClick = "location.href='".$link."';return false;";
		$this->custom('link', $text, $class, false, $onClick);
	}

	function help($helpname, $anchor = ''){
		$doc = JFactory::getDocument();
		$config = ACYSMS::config();
		$level = $config->get('level');

		$url = ACYSMS_HELPURL.$helpname.'&level='.$level.(!empty($anchor) ? '#'.$anchor : '');
		$iFrame = "'<iframe frameborder=\"0\" src=\'$url\' width=\'100%\' height=\'100%\' scrolling=\'auto\'></iframe>'";

		$js = "var openHelp = true;
				function displayDoc(){
					var box=document.getElementById('iframedoc');
					if(openHelp){
						box.innerHTML = ".$iFrame.";
						box.style.display = 'block';
						box.className = 'help_open';
					}else{
						box.style.display = 'none';
						box.className = 'help_close';
					}
					openHelp = !openHelp;
				}";
		$doc->addScriptDeclaration($js);

		$onClick = 'displayDoc();return false;';

		$this->custom('help', JText::_('SMS_HELP'), 'help', false, $onClick);
	}

	function divider(){
		$this->buttons[] = '<span class="acysmstoolbar_divider"></span>';
	}

	function cancel(){
		$this->custom('cancel', JText::_('SMS_CANCEL'), 'cancel', false);
	}

	function save(){
		$this->custom('save', JText::_('SMS_SAVE'), 'save', false);
	}

	function apply(){
		$this->custom('apply', JText::_('SMS_APPLY'), 'save', false);
	}

	function popup($name = '', $text = '', $url = '', $width = 640, $height = 480){
		$this->buttons[] = $this->_popup($name, $text, $url, $width, $height);
	}

	function directPrint(){
		$this->buttons[] = $this->_directPrint();
	}

	private function _popup($name = '', $text = '', $url = '', $width = 640, $height = 480){
		$params = array();
		$onClick = '';
		if(in_array($name, array('ABtesting', 'action'))){
			$doc = JFactory::getDocument();
			if(empty($doc->_script['text/javascript']) || strpos($doc->_script['text/javascript'], 'getAcyPopupUrl') === false){
				$js = "
				function getAcyPopupUrl(mylink){
					i = 0;
					mymailids = '';
					while(window.document.getElementById('cb'+i)){
						if(window.document.getElementById('cb'+i).checked) mymailids += window.document.getElementById('cb'+i).value+',';
						i++;
					}
					mylink += mymailids.slice(0,-1);
					return mylink;
				}";
				$doc->addScriptDeclaration($js);
			}

			if($name == 'ABtesting'){
				$mylink = 'index.php?option=com_acysms&ctrl=newsletter&task=abtesting&tmpl=component&mailid=';
				$url = JURI::base()."index.php?option=com_acysms&ctrl=newsletter&task=abtesting&tmpl=component";
			}elseif($name == 'action'){
				$mylink = 'index.php?option=com_acysms&ctrl=filter&tmpl=component&subid=';
				$url = JURI::base()."index.php?option=com_acysms&ctrl=filter&tmpl=component";
			}

			$onClick = ' onclick="this.href=getAcyPopupUrl(\''.$mylink.'\');"';
			$params['url'] = '\'+getAcyPopupUrl(\''.$mylink.'\')+\'';
		}else{
			$params['url'] = $url;
		}

		if(!ACYSMS_J30){
			JHTML::_('behavior.modal', 'a.modal');
			$html = '<a'.$onClick.' id="a_'.$name.'" class="modal" href="'.$url.'" rel="{handler: \'iframe\', size: {x: '.$width.', y: '.$height.'}}">';
			$html .= '<button title="'.$text.'"><i class="smsicon-'.$name.'"></i><span>'.$text.'</span></button></a>';
			return $html;
		}

		$html = '<button id="toolbar-'.$name.'" class="acysmstoolbar_'.$name.'" data-toggle="modal" data-target="#modal-'.$name.'" title="'.$text.'"><i class="smsicon-'.$name.'"></i><span>'.$text.'</span></button>';

		$params['height'] = $height;
		$params['width'] = $width;
		$params['title'] = $text;

		$modalHtml = JHtml::_('bootstrap.renderModal', 'modal-'.$name, $params);

		$html .= str_replace(array('id="modal-'.$name.'"', 'class="modal-body"', 'id="modal-'.$name.'-container"', 'class="iframe"'), array('id="modal-'.$name.'" style="width:82%;height:84%;margin-left:9%;left:0;top:0px;margin-top:50px;"', 'class="modal-body" style="height:82%;max-height:none;"', 'id="modal-'.$name.'-container" style="height:100%"', 'class="iframe" style="width:100%"'), $modalHtml);
		$html .= '<script>'."\r\n".'jQuery(document).ready(function(){jQuery("#modal-'.$name.'").appendTo(jQuery(document.body));});'."\r\n".'</script>';
		$html .= '<style type="text/css">#modal-'.$name.' iframe.iframe{ height: 100%; }</style>';

		return $html;
	}

	private function _directPrint(){

		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'acysmsprint.css', 'text/css', 'print');

		$function = "if(document.getElementById('iframepreview')){document.getElementById('iframepreview').contentWindow.focus();document.getElementById('iframepreview').contentWindow.print();}else{window.print();}return false;";

		return '<button class="acysmstoolbar_print" onclick="'.$function.'" title="'.JText::_('SMS_PRINT', true).'"><i class="smsicon-print"></i><span>'.JText::_('SMS_PRINT', true).'</span></button>';
	}

	function addButtonOption($task, $text, $class, $listSelect){

		$confirm = !ACYSMS_J16 ? 'PLEASE MAKE A SELECTION FROM THE LIST TO' : 'JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST';
		$submit = !ACYSMS_J16 ? "submitbutton('".$task."')" : "Joomla.submitbutton('".$task."')";
		$js = !empty($listSelect) ? "if(document.adminForm.boxchecked.value==0){alert('".str_replace(array("'", '"'), array("\'", '\"'), JText::_($confirm))."');return false;}else{".$submit."}" : $submit;

		$onClick = !empty($onClick) ? $onClick : $js;

		$this->buttonOptions[] = '<button onclick="'.$onClick.'" class="acysmstoolbar_'.$class.'" title="'.$text.'"><span class="smsicon-'.$class.'"></span><span>'.$text.'</span></button>';
	}
}
