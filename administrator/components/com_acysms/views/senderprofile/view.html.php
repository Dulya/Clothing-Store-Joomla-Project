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

class senderprofileViewsenderprofile extends acysmsView{
	var $ctrl = 'senderprofile';
	var $nameForm = 'senderprofile';
	var $icon = 'sender';

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)){
			$this->$function();
		}

		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();



		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'a.senderprofile_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		if($pageInfo->filter->order->dir != "asc") $pageInfo->filter->order->dir = 'desc';


		$searchMap = array('a.senderprofile_id', 'a.senderprofile_name', 'a.senderprofile_gateway', 'a.senderprofile_userid', 'b.username');
		$filters = array();
		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}

		$query = 'SELECT b.*,a.* FROM '.ACYSMS::table('senderprofile').' AS a LEFT JOIN #__users AS b ON a.senderprofile_userid=b.id';
		$queryCount = 'SELECT COUNT(a.senderprofile_id) FROM '.ACYSMS::table('senderprofile').' as a';

		if(!empty($filters)){
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);



		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$acyToolbar = ACYSMS::get('helper.toolbar');
		$acyToolbar->setTitle(JText::_('SMS_SENDER_PROFILES'), $this->ctrl);


		$acyToolbar->add();
		$acyToolbar->edit();
		if(ACYSMS::isAllowed($config->get('acl_sender_profiles_copy', 'all'))) $acyToolbar->custom('copy', JText::_('SMS_COPY'), 'copy', true);
		if(ACYSMS::isAllowed($config->get('acl_sender_profiles_delete', 'all'))) $acyToolbar->delete();

		$acyToolbar->divider();
		$acyToolbar->help('senderprofiles');

		$acyToolbar->display();

		$toggleHelper = ACYSMS::get('helper.toggle');
		$this->assignRef('toggleHelper', $toggleHelper);
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
	}

	function form(){
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();

		JHTML::_('behavior.modal', 'a.modal');
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
		acysms_loadMootools();

		$acltype = ACYSMS::get('type.acl');


		$senderprofile_id = ACYSMS::getCID('senderprofile_id');
		$senderprofileClass = ACYSMS::get('class.senderprofile');


		if(!empty($senderprofile_id)){
			$senderprofile = $senderprofileClass->get($senderprofile_id);
			$user = JFactory::getUser($senderprofile->senderprofile_userid);
			$senderprofile->senderprofile_username = $user->name;
		}else{
			$senderprofile = new stdClass();
			$senderprofile->senderprofile_name = '';
			$user = JFactory::getUser();
			$senderprofile->senderprofile_userid = $user->id;
			$senderprofile->senderprofile_username = $user->name;
			$senderprofile->senderprofile_gateway = '';
			$senderprofile->senderprofile_access = 'none';
		}

		$gateways = array();
		$gateways[] = JHTML::_('select.option', '', JText::_('SMS_SELECT_GATEWAY'));
		$dirs = JFolder::folders(ACYSMS_GATEWAY);
		foreach($dirs as $oneDir){
			if($oneDir == 'default'){
				continue;
			}
			$oneGateway = $senderprofileClass->getGateway($oneDir);
			$gateways[] = JHTML::_('select.option', $oneDir, $oneGateway->name);
		}
		$gatewaydropdown = JHTML::_('select.genericlist', $gateways, "data[senderprofile][senderprofile_gateway]", 'size="1" onchange="loadGateway(this.value);"', 'value', 'text', $senderprofile->senderprofile_gateway);


		if(version_compare(JVERSION, '1.6.0', '<')){
			$script = 'function submitbutton(pressbutton){
									if(pressbutton == \'cancel\') {
										submitform( pressbutton );
										return;
									}';
		}else{
			$script = 'Joomla.submitbutton = function(pressbutton) {
									if(pressbutton == \'cancel\') {
										Joomla.submitform(pressbutton,document.adminForm);
										return;
									}';
		}
		$script .= 'if(window.document.getElementById("senderprofile_name").value.length < 2){alert(\''.JText::_('SMS_ENTER_NAME', true).'\'); return false;}';
		$script .= 'if(window.document.getElementById("datasenderprofilesenderprofile_gateway").selectedIndex==0){alert(\''.JText::_('SMS_PLEASE_SELECT_GATEWAY', true).'\'); return false;}';
		if(version_compare(JVERSION, '1.6.0', '<')){
			$script .= 'submitform( pressbutton );} ';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}

		$script .= "function loadGateway(gateway){
						document.getElementById('gateway_params').innerHTML = '<span class=\"onload\"></span>';
						try{
							new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=senderprofile&task=gatewayparams&gateway='+gateway,{ method: 'post', update: document.getElementById('gateway_params')}).request();
						}catch(err){
							new Request({
							method: 'post',
							url: 'index.php?option=com_acysms&tmpl=component&ctrl=senderprofile&task=gatewayparams&gateway='+gateway,
							onSuccess: function(responseText, responseXML) {
								document.getElementById('gateway_params').innerHTML = responseText;
							}
							}).send();
						}
				}";




		$message_body = JRequest::getString('message_body');
		if(empty($message_body)) $message_body = JText::_('SMS_TEST_MESSAGE');


		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');
		$integration = ACYSMS::getIntegration($currentIntegration);

		$currentIntegration = $integration->componentName;
		$testNumberReceiver = $app->getUserStateFromRequest($currentIntegration."_testNumberReceiver", $currentIntegration."_testNumberReceiver", '', 'string');

		if(empty($testNumberReceiver)){
			$user = JFactory::getUser();
			$testNumberReceivers = $integration->getReceiverIDs($user->id);
			if(!empty($testNumberReceivers)) $testNumberReceiver = reset($testNumberReceivers);
		}


		if(!empty($testNumberReceiver)){
			$listNumber = explode(',', $testNumberReceiver);
			$listReceiversTest = array();

			foreach($listNumber as $number){
				$receiverTest = $integration->getInformationsByPhoneNumber($number);
				if(!empty($receiverTest)){
					$userFirstname = $receiverTest->user_firstname;
					$userNumber = $receiverTest->user_phone_number;
				}else{
					$userFirstname = ' ';
					$userNumber = $number;
				}
				$oneReceiver = array();
				array_push($oneReceiver, $userFirstname);
				array_push($oneReceiver, $userNumber);
				array_push($listReceiversTest, $oneReceiver);
			}
		}

		$script .= 'function affectUser(idcreator,name,email){
			window.document.getElementById("creatorname").innerHTML = name;
			window.document.getElementById("creatorid").value = idcreator;
		}';


		$acyToolbar = ACYSMS::get('helper.toolbar');
		$acyToolbar->setTitle(JText::_('SMS_SENDER_PROFILE'), $this->ctrl.'&task=edit&senderprofile_id='.$senderprofile_id);

		$acyToolbar->addButtonOption('apply', JText::_('SMS_APPLY'), 'apply', false);
		$acyToolbar->save();
		$acyToolbar->cancel();

		$acyToolbar->divider();
		$acyToolbar->help('senderprofiles');

		$acyToolbar->display();

		$doc->addScriptDeclaration($script);
		$this->assignRef('message_body', $message_body);
		$this->assignRef('senderprofile', $senderprofile);
		$this->assignRef('gatewaydropdown', $gatewaydropdown);
		$this->assignRef('currentIntegration', $currentIntegration);
		$this->assignRef('acltype', $acltype);
		$this->assignRef('listReceiversTest', $listReceiversTest);
	}
}
