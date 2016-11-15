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

class FieldsViewFields extends acysmsView{

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function form(){
		$fieldid = ACYSMS::getCID('fields_fieldid');
		$fieldsClass = ACYSMS::get('class.fields');
		if(!empty($fieldid)){
			$field = $fieldsClass->get($fieldid);
		}else{
			$field = new stdClass();
			$field->fields_published = 1;
			$field->fields_type = 'text';
			$field->fields_backend = 1;
			$field->fields_namekey = '';
		}

		if(!empty($field->fields_fieldid)){
			$fieldTitle = ' : '.$field->fields_namekey;
		}else $fieldTitle = '';

		$start = empty($field->fields_value) ? 0 : count($field->fields_value);
		$script = ' var currentid = '.($start + 1).';
			function addLine(){
			var myTable=window.document.getElementById("tablevalues");
			var newline = document.createElement(\'tr\');
			var column = document.createElement(\'td\');
			var column2 = document.createElement(\'td\');
			var column3 = document.createElement(\'td\');
			var column4 = document.createElement(\'td\');
			column4.innerHTML = \'<a onclick="acymove(\'+currentid+\',1);return false;" href="#"><img src="'.ACYSMS_IMAGES.'movedown.png" alt=" ˇ "/></a><a onclick="acymove(\'+currentid+\',-1);return false;" href="#"><img src="'.ACYSMS_IMAGES.'moveup.png" alt=" ˆ "/></a>\';
			var input = document.createElement(\'input\');
			input.id = "option"+currentid+"title";
			var input2 = document.createElement(\'input\');
			input2.id = "option"+currentid+"value";
			var input3 = document.createElement(\'select\');
			input3.id = "option"+currentid+"disabled";
			var option1 = document.createElement(\'option\');
			var option2 = document.createElement(\'option\');
			input.type = \'text\';
			input2.type = \'text\';
			input.name = \'fieldvalues[title][]\';
			input2.name = \'fieldvalues[value][]\';
			input3.name = \'fieldvalues[disabled][]\';
			input.style.width = \'150px\';
			input2.style.width = \'180px\';
			input3.style.width = \'80px\';
			option1.value= \'0\';
			option2.value= \'1\';
			option1.text= \''.JText::_('SMS_NO', true).'\';
			option2.text= \''.JText::_('SMS_YES', true).'\';
			try { input3.add(option1, null); } catch(ex) { input3.add(option1); }
			try { input3.add(option2, null); } catch(ex) { input3.add(option2); }
			column.appendChild(input);
			column2.appendChild(input2);
			column3.appendChild(input3);
			newline.appendChild(column);
			newline.appendChild(column2);
			newline.appendChild(column3);
			newline.appendChild(column4);
			myTable.appendChild(newline);
			currentid = currentid+1;
		}
		function acymove(myid,diff){
			var previousId = myid + diff;
			if(!document.getElementById(\'option\'+previousId+\'title\')) return;
			var prevtitle = document.getElementById(\'option\'+previousId+\'title\').value;
			var prevvalue = document.getElementById(\'option\'+previousId+\'value\').value;
			var prevdisabled = document.getElementById(\'option\'+previousId+\'disabled\').value;
			document.getElementById(\'option\'+previousId+\'title\').value = document.getElementById(\'option\'+myid+\'title\').value;
			document.getElementById(\'option\'+previousId+\'value\').value = document.getElementById(\'option\'+myid+\'value\').value;
			document.getElementById(\'option\'+previousId+\'disabled\').value = document.getElementById(\'option\'+myid+\'disabled\').value;
			document.getElementById(\'option\'+myid+\'title\').value = prevtitle;
			document.getElementById(\'option\'+myid+\'value\').value = prevvalue;
			document.getElementById(\'option\'+myid+\'disabled\').value = prevdisabled;
		}';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);

		$defaultCC = '';
		if(!empty($field->fields_options['checkcontent'])) $defaultCC = $field->fields_options['checkcontent'];
		$valRegexp = (!empty($field->fields_options['regexp']) ? 'value="'.$field->fields_options['regexp'].'"' : '');
		$fieldCheckContent = '<input type="radio" name="fieldsoptions[checkcontent]" id="fieldsoptions[checkcontent]0" value="0" '.(empty($defaultCC) ? 'checked="checked"' : '').'>';
		$fieldCheckContent .= ' <label for="fieldsoptions[checkcontent]0" id="fieldsoptions[checkcontent]0-lbl class="radiobtn">'.JText::_('SMS_ALL').'</label><br />';

		$fieldCheckContent .= '<input type="radio" name="fieldsoptions[checkcontent]" id="fieldsoptions[checkcontent]1" value="number" '.($defaultCC == 'number' ? 'checked="checked"' : '').'>';
		$fieldCheckContent .= ' <label for="fieldsoptions[checkcontent]1" id="fieldsoptions[checkcontent]1-lbl class="radiobtn">'.JText::_('SMS_ONLY_NUMBER').'</label><br />';

		$fieldCheckContent .= '<input type="radio" name="fieldsoptions[checkcontent]" id="fieldsoptions[checkcontent]2" value="letter" '.($defaultCC == 'letter' ? 'checked="checked"' : '').'>';
		$fieldCheckContent .= ' <label for="fieldsoptions[checkcontent]2" id="fieldsoptions[checkcontent]2-lbl class="radiobtn">'.JText::_('SMS_ONLY_LETTER').'</label><br />';

		$fieldCheckContent .= '<input type="radio" name="fieldsoptions[checkcontent]" id="fieldsoptions[checkcontent]3" value="email" '.($defaultCC == 'email' ? 'checked="checked"' : '').'>';
		$fieldCheckContent .= ' <label for="fieldsoptions[checkcontent]3" id="fieldsoptions[checkcontent]3-lbl class="radiobtn">'.JText::_('SMS_EMAIL_ADDRESS').'</label><br />';

		$fieldCheckContent .= '<input type="radio" name="fieldsoptions[checkcontent]" id="fieldsoptions[checkcontent]4" value="regexp" '.($defaultCC == 'regexp' ? 'checked="checked"' : '').'>';
		$fieldCheckContent .= ' <label for="fieldsoptions[checkcontent]4" id="fieldsoptions[checkcontent]4-lbl class="radiobtn">'.JText::_('SMS_ONLY_NUMBER_LETTER').'</label><br />';

		$fieldCheckContent .= '<input type="radio" name="fieldsoptions[checkcontent]" id="fieldsoptions[checkcontent]5" value="regexp" '.($defaultCC == 'regexp' ? 'checked="checked"' : '').'>';
		$fieldCheckContent .= ' <label for="fieldsoptions[checkcontent]5" id="fieldsoptions[checkcontent]5-lbl class="radiobtn">'.JText::_('SMS_MY_REGEXP').'</label> ';

		$fieldCheckContent .= ' <input type="text" name="fieldsoptions[regexp]" id="fieldsoptions[regexp]" style="width:200px" '.htmlspecialchars($valRegexp, ENT_COMPAT, 'UTF-8').'/>';


		$acyToolbar = ACYSMS::get('helper.toolbar');
		$acyToolbar->setTitle(JText::_('SMS_FIELD').$fieldTitle, 'fields&task=edit&fields_fieldid='.$fieldid);

		$acyToolbar->addButtonOption('apply', JText::_('SMS_APPLY'), 'apply', false);
		$acyToolbar->save();
		$acyToolbar->cancel();

		$acyToolbar->divider();

		$acyToolbar->help('customfields');
		$acyToolbar->display();

		$fieldtype = ACYSMS::get('type.fields');
		$this->assignRef('fieldtype', $fieldtype);
		$this->assignRef('field', $field);
		$this->assignRef('fieldCheckContent', $fieldCheckContent);
		$this->assignRef('fieldsClass', $fieldsClass);
	}

	function listing(){
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();


		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM `#__acysms_fields` ORDER BY `fields_ordering` ASC');
		$rows = $db->loadObjectList();
		$config = ACYSMS::config();

		$acyToolbar = ACYSMS::get('helper.toolbar');
		$acyToolbar->setTitle(JText::_('SMS_EXTRA_FIELDS'), 'fields');

		$acyToolbar->add();
		$acyToolbar->edit();
		$acyToolbar->delete();

		$acyToolbar->divider();

		$acyToolbar->help('customfields');

		$acyToolbar->display();

		jimport('joomla.html.pagination');
		$total = count($rows);
		$pagination = new JPagination($total, 0, $total);

		$this->assignRef('rows', $rows);
		$toggleClass = ACYSMS::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);
		$this->assignRef('pagination', $pagination);
		$fieldtype = ACYSMS::get('type.fields');
		$this->assignRef('fieldtype', $fieldtype);
		$fieldsClass = ACYSMS::get('class.fields');
		$this->assignRef('fieldsClass', $fieldsClass);
	}

	function choose(){

		$fieldsClass = ACYSMS::get('class.fields');
		$fake = null;
		$rows = $fieldsClass->getFields('module', $fake);

		$selected = JRequest::getVar('values', '', '', 'string');
		$selectedvalues = explode(',', $selected);
		foreach($rows as $id => $oneRow){
			if(in_array($oneRow->fields_namekey, $selectedvalues)){
				$rows[$id]->selected = true;
			}
		}

		$this->assignRef('fieldsClass', $fieldsClass);
		$this->assignRef('rows', $rows);
		$controlName = JRequest::getString('control', 'params');
		$this->assignRef('controlName', $controlName);
	}
}
