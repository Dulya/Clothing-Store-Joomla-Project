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

class AnswertriggerViewAnswerTrigger extends acysmsView{
	var $ctrl = 'answertrigger';
	var $nameListing = 'SMS_ANSWERS_TRIGGER';
	var $nameForm = 'answertrigger';
	var $icon = 'answertrigger';

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$config = ACYSMS::config();
		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$dropdownFilters = new stdClass();
		$db = JFactory::getDBO();
		$filters = array();
		$toggleHelper = ACYSMS::get('helper.toggle');



		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'answertrigger.answertrigger_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));


		$searchMap = array('answertrigger.answertrigger_id', 'answertrigger.answertrigger_actions', 'answertrigger.answertrigger_name', 'answertrigger.answertrigger_triggers');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}


		$order = new stdClass();
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'answertrigger.answertrigger_ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$plgactions = array();
		$answerTrigger = new stdClass();
		$dispatcher->trigger('onACYSMSDisplayActionsAnswersTrigger', array(&$plgactions, $answerTrigger));



		$queryCount = 'SELECT COUNT(answertrigger.answertrigger_id) FROM '.ACYSMS::table('answertrigger').' as answertrigger ';

		$query = 'SELECT * FROM '.ACYSMS::table('answertrigger').' as answertrigger ';
		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
			$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();
		$pageInfo->elements->page = count($rows);

		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();

		if(!empty($rows)){
			foreach($rows as $oneRow){
				if(!empty($oneRow->answertrigger_actions)){
					$actions = unserialize($oneRow->answertrigger_actions);
					$oneRow->answertrigger_actions = "";
					if(empty($actions['selected'])) continue;
					if(!is_array($actions['selected'])){
						$oneRow->answertrigger_actions = '- '.$plgactions[$actions['selected']]->name;
						continue;
					}
					foreach($actions['selected'] as $oneAction){
						$oneRow->answertrigger_actions .= '- '.$plgactions[$oneAction]->name;
						if(!empty($actions[$oneAction])) $oneRow->answertrigger_actions .= implode(',', $actions[$oneAction]);
						$oneRow->answertrigger_actions .= '<br />';
					}
				}
			}
		}


		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$acyToolbar = ACYSMS::get('helper.toolbar');

		$acyToolbar->setTitle(JText::_($this->nameListing), $this->ctrl);


		$acyToolbar->add();
		$acyToolbar->edit();
		if(ACYSMS::isAllowed($config->get('acl_answers_trigger_copy', 'all'))){
			$acyToolbar->custom('copy', JText::_('SMS_COPY'), 'copy', false);
		}
		if(ACYSMS::isAllowed($config->get('acl_answers_trigger_delete', 'all'))) $acyToolbar->delete();

		$acyToolbar->divider();

		$acyToolbar->help('answerTrigger');
		$acyToolbar->display();

		$this->assignRef('dropdownFilters', $dropdownFilters);
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('config', $config);
		$this->assignRef('order', $order);
		$this->assignRef('toggleHelper', $toggleHelper);
	}


	function form(){
		$answerTriggerid = ACYSMS::getCID('answertrigger_id');
		$config = ACYSMS::config();

		if(!empty($answerTriggerid)){
			$answerTriggerClass = ACYSMS::get('class.answertrigger');
			$answerTrigger = $answerTriggerClass->get($answerTriggerid);
		}else{
			$answerTrigger = new stdClass();
			$answerTrigger->answertrigger_name = '';
			$answerTrigger->answertrigger_description = '';
			$answerTrigger->answertrigger_actions = '';
			$answerTrigger->answertrigger_trigger = '';
			$answerTrigger->answertrigger_publish = '1';
		}

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();

		$actions = array();
		$dispatcher->trigger('onACYSMSDisplayActionsAnswersTrigger', array(&$actions, $answerTrigger));

		$radioListActions = '';
		foreach($actions as $value => $newOption){
			$radioListActions .= '<input type="checkbox" name="data[answertrigger][answertrigger_actions][selected][]" id="action_'.$value.'" value="'.$value.'" '.@(in_array($value, @$answerTrigger->answertrigger_actions['selected']) ? "checked" : "").'/> ';
			$radioListActions .= '<label for="action_'.$value.'" >'.$newOption->name.'</label>';
			if(!empty($newOption->extra)) $radioListActions .= $newOption->extra;
			$radioListActions .= '<br />';
		}

		$inputRegex = '#<input type="text" name="data[answertrigger][answertrigger_triggers][regex]" onChange="document.getElementById(\'answertrigger_regex\').checked=true;" value="'.@$answerTrigger->answertrigger_triggers['regex'].'" />#is';
		$inputWord = '<input type="text" name="data[answertrigger][answertrigger_triggers][word]" onChange="document.getElementById(\'answertrigger_word\').checked=true;" value="'.@$answerTrigger->answertrigger_triggers['word'].'" />';

		$triggerWhen = '<label for="answertrigger_regex"><input type="radio" id="answertrigger_regex" name="data[answertrigger][answertrigger_triggers][selected]" value="regex" '.(!empty($answerTrigger->answertrigger_triggers['selected']) && $answerTrigger->answertrigger_triggers['selected'] == "regex" ? "checked" : "").'/> ';
		$triggerWhen .= JText::_('SMS_MESSAGE_MATCHES_REGEX').' : </label>'.$inputRegex.'<br />';
		$triggerWhen .= '<label for="answertrigger_word"><input type="radio" id="answertrigger_word" name="data[answertrigger][answertrigger_triggers][selected]" value="word" '.(!empty($answerTrigger->answertrigger_triggers['selected']) && $answerTrigger->answertrigger_triggers['selected'] == "word" ? "checked" : "").'/> ';
		$triggerWhen .= JText::_('SMS_MESSAGE_CONTAINS_ONLY').' : </label>'.$inputWord.'<br />';
		$triggerWhen .= '<label for="answertrigger_attachment"><input type="radio" id="answertrigger_attachment" name="data[answertrigger][answertrigger_triggers][selected]" value="contains" '.(!empty($answerTrigger->answertrigger_triggers['selected']) && $answerTrigger->answertrigger_triggers['selected'] == "contains" ? "checked" : "").'/> ';
		$triggerWhen .= JText::_('SMS_MESSAGE_CONTAINS_ATTACHMENT').'</label><br/>';
		$triggerWhen .= '<label for="answertrigger_groupname"><input type="radio" id="answertrigger_groupname" name="data[answertrigger][answertrigger_triggers][selected]" value="groupname" '.(!empty($answerTrigger->answertrigger_triggers['selected']) && $answerTrigger->answertrigger_triggers['selected'] == "groupname" ? "checked" : "").'/> ';
		$triggerWhen .= JText::_('SMS_MESSAGE_CONTAINS_GROUP_NAME').'</label><br/>';



		$acyToolbar = ACYSMS::get('helper.toolbar');

		$acyToolbar->setTitle(JText::_('SMS_ANSWERS_TRIGGER'), $this->ctrl.'&task=edit&answertrigger_id='.$answerTriggerid);

		$acyToolbar->addButtonOption('apply', JText::_('SMS_APPLY'), 'apply', false);
		$acyToolbar->save();
		$acyToolbar->cancel();
		$acyToolbar->divider();

		$acyToolbar->help('answerTrigger');
		$acyToolbar->display();

		$tabs = ACYSMS::get('helper.tabs');
		$tabs->setOptions(array('useCookie' => true));

		if(version_compare(JVERSION, '1.6.0', '<')){
			$script = 'function submitbutton(pressbutton){
						if (pressbutton == \'cancel\') {
							submitform( pressbutton );
							return;
						}';
		}else{
			$script = 'Joomla.submitbutton = function(pressbutton) {
						if (pressbutton == \'cancel\') {
							Joomla.submitform(pressbutton,document.adminForm);
							return;
						}';
		}
		$script .= 'if(window.document.getElementById("answertrigger_name").value.length < 1){alert(\''.JText::_('SMS_ENTER_NAME', true).'\'); return false;}';
		if(version_compare(JVERSION, '1.6.0', '<')){
			$script .= 'submitform( pressbutton );} ';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}


		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
		$this->assignRef('config', $config);
		$this->assignRef('answertrigger', $answerTrigger);
		$this->assignRef('radioListActions', $radioListActions);
		$this->assignRef('triggerWhen', $triggerWhen);
	}
}
