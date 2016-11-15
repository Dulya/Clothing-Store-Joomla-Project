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

class CustomerViewCustomer extends acysmsView{

	var $ctrl = 'customer';
	var $icon = 'customers';

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$filters = new stdClass();
		$pageInfo = new stdClass();
		$pageInfo->elements = new stdClass();
		JHTML::_('behavior.modal', 'a.modal');




		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();

		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'customer_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');

		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->limit = new stdClass();
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$searchMap = array('joomusers.name', 'joomusers.email', 'acycustomers.customer_id');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}


		$query = 'SELECT joomusers.name AS customer_name, joomusers.email AS customer_email, acycustomers.customer_credits AS customer_credits, acycustomers.customer_joomid AS joomid, acycustomers.customer_id AS customer_id
			FROM #__acysms_customer AS acycustomers
			JOIN #__users AS joomusers
			ON joomusers.id = acycustomers.customer_joomid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$queryCount = 'SELECT COUNT(acycustomers.customer_id)
			FROM #__acysms_customer AS acycustomers
			JOIN #__users AS joomusers
			ON joomusers.id = acycustomers.customer_joomid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		if(empty($pageInfo->limit->value)){
			if($pageInfo->elements->total > 500){
				ACYSMS::display('We do not want you to crash your server so we displayed only the first 500 users', 'warning');
				$pageInfo->limit->value = 100;
			}
		}


		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');

			$acyToolbar->setTitle(JText::_('SMS_CUSTOMERS'), 'customer');

			$acyToolbar->add();
			$acyToolbar->edit();
			$acyToolbar->delete();

			$acyToolbar->divider();

			$acyToolbar->help('customer');
			$acyToolbar->display();
		}
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
	}


	function form(){
		JHTML::_('behavior.modal', 'a.modal');
		$customerID = ACYSMS::getCID('customer_id');
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		if(!empty($customerID)){
			$customerClass = ACYSMS::get('class.customer');
			$customer = $customerClass->get($customerID);

			if(!empty($customer->customer_joomid)){
				$query = "SELECT id, name, username, email FROM #__users WHERE id = ".intval($customer->customer_joomid);
				$db->setQuery($query);
				$joomUser = $db->loadObject();
			}
		}else{
			$customer = new stdClass();
			$customer->customer_joomid = '';
			$customer->customer_id = '';
			$customer->customer_credits = 0;
			$customer->customer_senderprofile_id = 0;
			$customer->customer_credits_url = $config->get('default_credits_url');
		}

		$senderProfileType = ACYSMS::get('type.senderprofile');
		$senderProfileType->multiple = true;
		$senderProfileType->displayDefaultSenderProfileOption = true;


		$acyToolbar = ACYSMS::get('helper.toolbar');

		$acyToolbar->setTitle(JText::_('SMS_CUSTOMERS'), $this->ctrl.'&task=edit&customer_id='.$customerID);

		$acyToolbar->addButtonOption('apply', JText::_('SMS_APPLY'), 'apply', false);
		$acyToolbar->save();
		$acyToolbar->cancel();

		$acyToolbar->divider();

		$acyToolbar->help('customer');
		$acyToolbar->display();

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

		$script .= 'if(window.document.getElementById("user_joomid").value.length < 2){alert(\''.JText::_('SMS_SELECT_JOOMLA_USER', true).'\'); return false;}';

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


		$this->assignRef('customer', $customer);
		$this->assignRef('config', $config);
		$this->assignRef('senderprofile', $senderProfileType);
		if(!empty($joomUser)) $this->assignRef('joomUser', $joomUser);
	}
}
