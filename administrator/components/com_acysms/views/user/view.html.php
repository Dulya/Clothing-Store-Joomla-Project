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

class userViewuser extends acysmsView{
	var $ctrl = 'user';

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}


	function form(){

		JHTML::_('behavior.modal', 'a.modal');

		$userId = ACYSMS::getCID('user_id');
		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		acysms_loadMootools();
		$countryType = ACYSMS::get('type.country');

		$db = JFactory::getDBO();
		$groupClass = ACYSMS::get('class.group');

		if(!empty($userId)){
			$userClass = ACYSMS::get('class.user');
			$user = $userClass->get($userId);
			$subscription = $app->isAdmin() ? $userClass->getSubscription($userId) : $userClass->getFrontendSubscription($userId);
		}else{
			$subscription = $app->isAdmin() ? $groupClass->getGroups() : $groupClass->getFrontendGroups();
			$user = new stdClass();
			$user->user_joomid = '';
			$user->user_firstname = '';
			$user->user_lastname = '';
			$user->user_phone_number = '';
			$user->user_birthdate = '';
			$user->user_email = '';
		}

		$days = array();
		$months = array();
		$years = array();


		$days[] = $months[] = $years[] = JHTML::_('select.option', '', JText::_(' - - - '));

		for($i = 1; $i < 32; $i++) $days[] = JHTML::_('select.option', (strlen($i) == 1) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 1900; $i <= date('Y'); $i++) $years[] = JHTML::_('select.option', $i, $i);

		$months[] = JHTML::_('select.option', '01', JText::_('JANUARY'));
		$months[] = JHTML::_('select.option', '02', JText::_('FEBRUARY'));
		$months[] = JHTML::_('select.option', '03', JText::_('MARCH'));
		$months[] = JHTML::_('select.option', '04', JText::_('APRIL'));
		$months[] = JHTML::_('select.option', '05', JText::_('MAY'));
		$months[] = JHTML::_('select.option', '06', JText::_('JUNE'));
		$months[] = JHTML::_('select.option', '07', JText::_('JULY'));
		$months[] = JHTML::_('select.option', '08', JText::_('AUGUST'));
		$months[] = JHTML::_('select.option', '09', JText::_('SEPTEMBER'));
		$months[] = JHTML::_('select.option', '10', JText::_('OCTOBER'));
		$months[] = JHTML::_('select.option', '11', JText::_('NOVEMBER'));
		$months[] = JHTML::_('select.option', '12', JText::_('DECEMBER'));


		if(!empty($user->user_birthdate)) $fields = explode('-', $user->user_birthdate);

		$dayField = JHTML::_('select.genericlist', $days, 'data[user][user_birthdate][day]', 'style="width:50px;" class="inputbox"', 'value', 'text', !empty($fields[2]) ? $fields[2] : '');
		$monthField = JHTML::_('select.genericlist', $months, 'data[user][user_birthdate][month]', 'style="width:100px;" class="inputbox"', 'value', 'text', !empty($fields[1]) ? $fields[1] : '');
		$yearField = JHTML::_('select.genericlist', $years, 'data[user][user_birthdate][year]', 'style="width:70px;" class="inputbox"', 'value', 'text', !empty($fields[0]) ? $fields[0] : '');

		$timeField = array($dayField, $monthField, $yearField);

		if(!empty($user->user_joomid)){
			$query = "SELECT id, name, username, email FROM #__users WHERE id = ".intval($user->user_joomid);
			$db->setQuery($query);
			$joomUser = $db->loadObject();
		}


		$fieldsClass = ACYSMS::get('class.fields');
		$this->assignRef('fieldsClass', $fieldsClass);

		$coreFields = $fieldsClass->getFields('core', $user);
		if($app->isAdmin()){
			$extraFields = $fieldsClass->getFields('backend', $user);
		}else $extraFields = $fieldsClass->getFields('frontcomp', $user);


		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_USER'), $this->ctrl.'&task=edit&user_id='.$userId);


			$acyToolbar->addButtonOption('apply', JText::_('SMS_APPLY'), 'apply', false);
			$acyToolbar->save();
			$acyToolbar->cancel();

			$acyToolbar->divider();
			$acyToolbar->help('receivers');

			$acyToolbar->display();
		}

		$tabs = ACYSMS::get('helper.tabs');
		$tabs->setOptions(array('useCookie' => true));
		$this->assignRef('tabs', $tabs);

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
		if(version_compare(JVERSION, '1.6.0', '<')){
			$script .= 'submitform( pressbutton );} ';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}

		$script .= "function affectUser(id,name, email){
					window.document.getElementById('user_joomid').value = id;
					window.document.getElementById('joomuser').innerHTML = name + ' ('+email+')';
				}";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);


		$filters = new stdClass();
		$quickstatusType = ACYSMS::get('type.statusquick');
		$filters->statusquick = $quickstatusType->display('statusquick');


		$statusType = ACYSMS::get('type.status');

		$this->assignRef('statusType', $statusType);
		$this->assignRef('filters', $filters);
		$this->assignRef('subscription', $subscription);
		$this->assignRef('user', $user);
		$this->assignRef('config', $config);
		$this->assignRef('timeField', $timeField);
		$this->assignRef('countryType', $countryType);
		$this->assignRef('joomUser', $joomUser);
		$this->assignRef('extraFields', $extraFields);
		$this->assignRef('coreFields', $coreFields);
		$this->assignRef('app', $app);
	}

	public function choosejoomuser(){

		$pageInfo = new stdClass();
		$pageInfo->elements = new stdClass();
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		if($pageInfo->filter->order->dir != "asc") $pageInfo->filter->order->dir = 'desc';

		$searchMap = array('name', 'username', 'email');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}



		$query = "SELECT id, name, username, email FROM #__users";
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$queryCount = 'SELECT COUNT(id) FROM #__users';
		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		if($pageInfo->limit->value > $pageInfo->elements->page){
			$pageInfo->elements->total = $pageInfo->limit->start + $pageInfo->elements->page;
		}

		if(empty($pageInfo->limit->value)){
			if($pageInfo->elements->total > 500){
				ACYSMS::display('We do not want you to crash your server so we displayed only the first 500 users', 'warning');
			}
			$pageInfo->limit->value = 100;
		}
		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
	}
}
