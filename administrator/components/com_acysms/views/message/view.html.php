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

class MessageViewMessage extends acysmsView{
	var $ctrl = 'message';
	var $nameListing = 'SMS_MESSAGES';
	var $nameForm = 'SMS';
	var $icon = 'message';
	var $defaultSize = 160;

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		JHTML::_('behavior.modal', 'a.modal');
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

		$my = JFactory::getUser();
		$customerClass = ACYSMS::get('class.customer');
		$customer = $customerClass->getCustomerByJoomID($my->id);
		$allowCustomersManagement = $config->get('allowCustomersManagement');
		if(!$app->isAdmin() && $allowCustomersManagement && empty($customer->customer_id)) die('You are not allowed to access this page, please check the customer management option in the AcySMS configuration');


		$selectedCategory = $app->getUserStateFromRequest($paramBase."filter_category", 'filter_category', 0, 'int');
		$selectedType = $app->getUserStateFromRequest($paramBase."filter_type", 'filter_type', '', 'string');
		$selectedCreator = $app->getUserStateFromRequest($paramBase."filter_creator", 'filter_creator', 0, 'int');
		$selectedSenderProfile = $app->getUserStateFromRequest($paramBase."filter_senderprofile", 'filter_senderprofile', 0, 'int');
		$selectedPublishedStatus = $app->getUserStateFromRequest($paramBase."filter_messagepublished", 'filter_messagepublished', '1', 'int');


		if(!$app->isAdmin()){
			if(!ACYSMS::isAllowed($config->get('acl_messages_manage_all', 'all')) && !ACYSMS::isAllowed($config->get('acl_messages_manage_own', 'all'))){
				echo JText::_('SMS_NO_MESSAGE_ACCESS');
				exit;
			}
			if(ACYSMS::isAllowed($config->get('acl_messages_manage_own', 'all'))){
				$my = JFactory::getUser();
				$selectedCreator = intval($my->id);
			}
		}

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'message.message_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		if($pageInfo->filter->order->dir != "asc") $pageInfo->filter->order->dir = 'desc';

		$listCategory = ACYSMS::get('type.category');
		$listCategory->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$dropdownFilters->category = $listCategory->display('filter_category', $selectedCategory);

		$listMessageType = ACYSMS::get('type.messagetype');
		$listMessageType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$dropdownFilters->type = $listMessageType->display('filter_type', $selectedType);

		$listMessageCreator = ACYSMS::get('type.messagecreator');
		$listMessageCreator->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$dropdownFilters->creator = $listMessageCreator->display('filter_creator', $selectedCreator);

		$listSenderProfile = ACYSMS::get('type.senderprofile');
		$listSenderProfile->allSenderProfileOptions = true;
		$listSenderProfile->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$dropdownFilters->senderprofile = $listSenderProfile->display('filter_senderprofile', $selectedSenderProfile);

		$dropdownFilters->publishedStatus = '';
		$listPublishedStatus = ACYSMS::get('type.messagepublished');
		if(!empty($listPublishedStatus->values)) $dropdownFilters->publishedStatus = $listPublishedStatus->display('filter_messagepublished', $selectedPublishedStatus);

		$searchMap = array('message.message_id', 'message.message_subject', 'message.message_userid', 'message.message_senderid', 'message.message_status', 'joomuser.name', 'joomuser.username', 'joomuser.email', 'category.category_name');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}
		if(!empty($selectedCategory)) $filters[] = 'message.message_category_id = '.intval($selectedCategory);
		if(!empty($selectedCreator)) $filters[] = 'message.message_userid = '.intval($selectedCreator);
		if(!empty($selectedType)) $filters[] = 'message.message_type = '.$db->quote($selectedType);
		if(!empty($selectedSenderProfile)) $filters[] = 'senderprofile.senderprofile_id = '.$db->quote($selectedSenderProfile);
		if(!empty($selectedPublishedStatus)) $filters[] = 'message.message_published = '.$db->quote($selectedPublishedStatus);

		$filters[] = 'message.message_type <> "answer" AND message.message_type <> "activation_optin" AND message.message_type <> "conversation"';
		if(!$app->isAdmin()) $filters[] = 'message.message_type <> "auto"';



		$queryCount = 'SELECT COUNT(message.message_id) FROM '.ACYSMS::table('message').' as message';
		$queryCount .= ' LEFT JOIN #__users as joomuser on message.message_userid = joomuser.id ';
		$queryCount .= ' LEFT JOIN #__acysms_senderprofile AS senderprofile on message.message_senderprofile_id = senderprofile.senderprofile_id ';
		$queryCount .= 'LEFT JOIN '.ACYSMS::table('category').' AS category ON message.message_category_id = category.category_id';

		$query = 'SELECT * FROM '.ACYSMS::table('message').' as message';
		$query .= ' LEFT JOIN #__users as joomuser on message.message_userid = joomuser.id ';
		$query .= ' LEFT JOIN #__acysms_senderprofile AS senderprofile on message.message_senderprofile_id = senderprofile.senderprofile_id ';
		$query .= 'LEFT JOIN '.ACYSMS::table('category').' AS category ON message.message_category_id = category.category_id';

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

		global $Itemid;
		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;
		$this->assignRef('itemId', $myItem);



		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_($this->nameListing), $this->ctrl);

			$acyToolbar->custom('preview', JText::_('SMS_PREVIEW_SEND'), 'viewmore', true);
			$acyToolbar->custom('archive', JText::_('SMS_ARCHIVE').' / '.JText::_('SMS_UNARCHIVE'), 'archive', true);

			$acyToolbar->divider();
			$acyToolbar->add();
			$acyToolbar->edit();
			if(ACYSMS::isAllowed($config->get('acl_messages_copy', 'all'))) $acyToolbar->custom('copy', JText::_('SMS_COPY'), 'copy', true);
			if(ACYSMS::isAllowed($config->get('acl_messages_delete', 'all'))) $acyToolbar->delete();

			$acyToolbar->divider();
			$acyToolbar->help('messages');

			$acyToolbar->display();
		}

		$this->assignRef('dropdownFilters', $dropdownFilters);
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('config', $config);
		$this->assignRef('app', $app);
	}

	function form(){
		$config = ACYSMS::config();
		$app = JFactory::getApplication();


		$senderProfileType = ACYSMS::get('type.senderprofile');
		$senderProfileType->includeMMSJS = true;

		$categoryType = ACYSMS::get('type.category');
		$categoryType->js = 'onchange = "if(this.value == -1){document.getElementById(\'newcategory\').style.display = \'\';}else{document.getElementById(\'newcategory\').style.display = \'none\';}" size = "1" style = "width:208px;font-size:12px;"';

		$message_id = ACYSMS::getCID('message_id');
		$messageMaxChar = $config->get('messageMaxChar');

		if(!empty($message_id)){
			$messageClass = ACYSMS::get('class.message');
			$message = $messageClass->get($message_id);
		}else{
			$message = new stdClass();
			$message->message_userid = '';
			$message->message_subject = '';
			$message->message_body = str_replace("<br>", "\n", JText::_('SMS_DEFAULT_MESSAGE'));
			$message->message_created = time();
			$message->message_type = 'draft';
			$message->message_senddate = '';
			$message->message_status = 'notsent';
			$message->message_recipients = '';
		}
		$senderProfileDropdown = $senderProfileType->display('data[message][message_senderprofile_id]', @$message->message_senderprofile_id);


		if(empty($senderProfileType->values)){
			ACYSMS::enqueueMessage('Please create a sender profile before trying to send SMS', 'warning');

			if($app->isAdmin()){
				$url = 'index.php?option=com_acysms&ctrl=senderprofile';
			}else $url = 'index.php';
			$app->redirect($url);
		}

		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_MESSAGE'), $this->ctrl.'&task=edit&message_id='.$message_id);

			$acyToolbar->custom('preview', JText::_('SMS_PREVIEW_SEND'), 'viewmore', false);
			$acyToolbar->addButtonOption('apply', JText::_('SMS_APPLY'), 'apply', false);
			$acyToolbar->save();
			$acyToolbar->cancel();

			$acyToolbar->divider();
			$acyToolbar->help('messages');
			$acyToolbar->display();
		}

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$tags = array();
		$dispatcher->trigger('onACYSMSGetTags', array(&$tags));
		if(version_compare(JVERSION, '1.6.0', ' < ')){
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
		$script .= 'if(window.document.getElementById("message_subject").value.length < 2  ){alert(\''.JText::_('SMS_ENTER_SUBJECT', true).'\'); return false;}';
		$script .= 'if(window.document.getElementById("message_body").value.length < 2  ){alert(\''.JText::_('SMS_ENTER_BODY', true).'\'); return false;}';
		if(version_compare(JVERSION, '1.6.0', '<')){
			$script .= 'submitform( pressbutton );} ';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}

		$script .= "function insertTag(tag){ try{jInsertEditorText(tag,'editor_body'); return true;} catch(err){alert('Your editor does not enable AcySMS to automatically insert the tag, please copy/paste it manually in your Newsletter'); return false;}}";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
		$this->assign('senderprofile', $senderProfileDropdown);
		$this->assign('category', $categoryType);
		$this->assignRef('message', $message);
		$this->assignRef('tags', $tags);
		$this->assignRef('messageMaxChar', $messageMaxChar);
		$this->assignRef('app', $app);
	}

	function preview(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		JHTML::_('behavior.modal', 'a.modal');
		$messageClass = ACYSMS::get('class.message');
		$message_id = ACYSMS::getCID('message_id');
		$message = $messageClass->get($message_id);

		$doc = JFactory::getDocument();
		$currentIntegration = $app->getUserStateFromRequest("currentIntegration", 'currentIntegration', '', 'string');

		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration($currentIntegration);

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();



		$allowCustomersManagement = $config->get('allowCustomersManagement');

		$my = JFactory::getUser();
		$customerClass = ACYSMS::get('class.customer');
		$customer = $customerClass->getCustomerByJoomID($my->id);
		if(!$app->isAdmin() && $allowCustomersManagement && empty($customer->customer_id)) die('You are not allowed to access this page, please check the customer management option in the AcySMS configuration');

		$integrations = new stdClass();
		$integrationType = ACYSMS::get('type.integration');
		$integrationType->js = 'onchange="changeIntegration(this.value);"';
		$integrationType->hideIfOnlyOneEntry = true;
		if(!$app->isAdmin() && $allowCustomersManagement) $integrationType->defaultIntegrationOnly = true;
		$integrationType->load();
		$integrations->display = $integrationType->display('data[message][message_receiver_table]', (empty($this->message->message_receiver_table) ? $integration->componentName : $this->message->message_receiver_table));
		$integrations->nbIntegrations = count($integrationType->integration);

		$message_types = array();
		$message_types[] = JHTML::_('select.option', 'draft', JText::_('SMS_DRAFT'));
		$message_types[] = JHTML::_('select.option', 'standard', JText::_('SMS_ONETIME'));
		if($app->isAdmin()) $message_types[] = JHTML::_('select.option', 'auto', JText::_('SMS_AUTOMATIC'));

		$message_senddate[] = JHTML::_('select.option', 'notsent', JText::_('SMS_SEND_NOW'));
		$message_senddate[] = JHTML::_('select.option', 'scheduled', JText::_('SMS_SEND_AT'));

		$days = array();
		for($i = 1; $i < 32; $i++) $days[] = JHTML::_('select.option', (strlen($i) == 1) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$years = array();
		for($i = date('Y'); $i <= date('Y') + 5; $i++) $years[] = JHTML::_('select.option', $i, $i);
		$months = array();
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

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i += 5) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);

		$dayField = JHTML::_('select.genericlist', $days, 'data[scheduleddate][day]', 'style="width:50px;" class="inputbox"', 'value', 'text', !empty($message->message_senddate) ? ACYSMS::getDate($message->message_senddate, 'd') : ACYSMS::getDate(time(), 'd'));
		$monthField = JHTML::_('select.genericlist', $months, 'data[scheduleddate][month]', 'style="width:100px;" class="inputbox"', 'value', 'text', !empty($message->message_senddate) ? ACYSMS::getDate($message->message_senddate, 'm') : ACYSMS::getDate(time(), 'm'));
		$yearField = JHTML::_('select.genericlist', $years, 'data[scheduleddate][year]', 'style="width:70px;" class="inputbox"', 'value', 'text', !empty($message->message_senddate) ? ACYSMS::getDate($message->message_senddate, 'Y') : ACYSMS::getDate(time(), 'Y'));
		$hourField = JHTML::_('select.genericlist', $hours, 'data[scheduleddate][hour]', 'style="width:50px;" class="inputbox"', 'value', 'text', !empty($message->message_senddate) ? ACYSMS::getDate($message->message_senddate, 'H') : ACYSMS::getDate(time(), 'H'));
		$minField = JHTML::_('select.genericlist', $min, 'data[scheduleddate][min]', 'style="width:50px;" class="inputbox"', 'value', 'text', !empty($message->message_senddate) ? ACYSMS::getDate($message->message_senddate, 'i') : ACYSMS::getDate(time(), 'i'));
		$timeField = array($dayField, $monthField, $yearField, $hourField.' : ', $minField);


		$filters = array();
		$dispatcher->trigger('onACYSMSDisplayFiltersSimpleMessage', array($integration->componentName, &$filters));


		$autotypes = array();
		$messageReceiverTable = !empty($this->message->message_receiver_table) ? $this->message->message_receiver_table : $integration->componentName;
		$dispatcher->trigger('onACYSMSGetMessageType', array(&$autotypes, $messageReceiverTable));
		$messageBasedOn[] = JHTML::_('select.option', '', JText::_('SMS_SELECT_MESSAGE_TYPE'));
		foreach($autotypes as $type => $object){
			$messageBasedOn[] = JHTML::_('select.option', $type, $object->name);
		}
		$messageBasedOn = JText::sprintf('SMS_START_ON', JHTML::_('select.genericlist', $messageBasedOn, 'data[message][message_autotype]', 'onchange="loadAutoParams(this.value)" class="inputbox" style="width:auto"', 'value', 'text', $message->message_autotype));

		$toggleHelper = ACYSMS::get('helper.toggle');
		$toggleHelper->callFunction();

		acysms_loadMootools();
		$script = "
		function changeIntegration(integration){
			generateDropDown(integration);
		}

		function generateDropDown(integration){
			if(document.getElementById('autoSendParameters')){
				element = document.getElementById('autoSendParameters');
				while (element.firstChild) {
					element.removeChild(element.firstChild);
				}
			}
			if(!document.getElementById('sendBasedOn')){
				var newElement = document.createElement('div');
				newElement.id = 'sendBasedOn';
				document.getElementById('autoSendParameters').appendChild(newElement);
				document.getElementById('sendBasedOn').innerHTML = '<span id=\"ajaxSpan\" class=\"onload\"></span>';
			}

			if(!document.getElementById('autosms_params')){
				var newElement = document.createElement('div');
				newElement.id = 'autosms_params';
				document.getElementById('autoSendParameters').appendChild(newElement);
			}
			try{
				new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl."&task=addDropdownEntryAutoMessage&integration='+integration,{ method: 'post', update: document.getElementById('sendBasedOn')}).request();
			}catch(err){
				new Request({
				method: 'post',
				url: 'index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl."&task=addDropdownEntryAutoMessage&integration='+integration,
				onSuccess: function(responseText, responseXML) {
					document.getElementById('sendBasedOn').innerHTML = responseText;
				}
				}).send();
			}
		}
		function loadAutoParams(value){
			if(document.getElementById('selectedIntegration') && document.getElementById('selectedIntegration').value) currentIntegration = document.getElementById('selectedIntegration').value;
			document.getElementById('autosms_params').innerHTML = '<div id=\"'+'DisplayParamsAutoMessage_'+value+'\"><span id=\"ajaxSpan\" class=\"onload\"></span></div>';
			try{
				new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl."&task=displayParamsAutoMessage&message_id=".$message_id."&value='+value+'&integration='+currentIntegration,{ method: 'post', update: document.getElementById('DisplayParamsAutoMessage_'+value)}).request();
			}catch(err){
				new Request({
				method: 'post',
				url: 'index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl."&task=displayParamsAutoMessage&message_id=".$message_id."&value='+value+'&integration='+currentIntegration,
				onSuccess: function(responseText, responseXML) {
					document.getElementById('DisplayParamsAutoMessage_'+value).innerHTML = responseText;
				}
				}).send();
			}
		}
		function loadFilterParams(checked, integration){
			if(!checked){
				document.getElementById(integration+'_filters').style.display = 'none';
				document.getElementById('displayfilterparams_'+integration).innerHTML = '';
				return;
			}

			document.getElementById(integration+'_filters').style.display = 'block';
			document.getElementById('displayfilterparams_'+integration).className += 'opened';
			document.getElementById('displayfilterparams_'+integration).innerHTML = '<div id=\"displayfilterparams_'+integration+'\" ><span id = \"ajaxSpan\" class=\"onload\"></span></div>';

			try{
				new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl."&task=displayFilterParams&integration='+integration,{ method: 'post', update: document.getElementById('DisplayFilterParams_'+integration)}).request();
			}catch(err){
				new Request({
				method: 'post',
				url: 'index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl."&task=displayFilterParams&integration='+integration,
				onSuccess: function(responseText, responseXML) {
					document.getElementById('displayfilterparams_'+integration).innerHTML = responseText;
				}
			}).send();
			}
		}


		function affectTestUser(idcreator,name,phoneNumber,htmlID){
			window.document.getElementById(htmlID+'_phone').innerHTML = name+' ('+phoneNumber+')';
			window.document.getElementById(htmlID+'_id').value = idcreator;
		}

		function applyAutoParamsValue(params,value){
			var myparam = document.adminForm.elements['data[message][message_receiver][auto][".$message->message_autotype."]'+params];
			if(myparam){
				myparam.value = value;
				if(myparam.type && myparam.type == 'checkbox'){ myparam.checked = 'checked'; } ;
				if(myparam instanceof NodeList){
					for(var i=0;i<myparam.length;i++)
					{
						if(myparam[i].value == value) myparam[i].checked = true;
					}
				}
			}
		}
		function applyParamsValue(params,value){
			var myparam = document.adminForm.elements['data[message][message_receiver][standard]'+params];

			if(myparam){
				myparam.value = value;
				if(myparam.type && myparam.type == 'checkbox'){ myparam.checked = 'checked'; } ;
			}
		}
		function countresults(integration){
			document.getElementById('countresult_'+integration).innerHTML = '<span class=\"onload\"></span>';
			var form = $('adminForm');
			var data = form.toQueryString();
			data += '&task=countresults&ctrl=".$this->ctrl."';
			try{
				new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl."&task=countresults&integration='+integration,{
					method: 'post',
					data: data,
					update: document.getElementById('countresult_'+integration)
				}).request();
			}catch(err){
				new Request({
					method: 'post',
					data: data,
					url: 'index.php?option=com_acysms&tmpl=component&ctrl=".$this->ctrl."&task=countresults&integration='+integration,
					onSuccess: function(responseText, responseXML) {
						document.getElementById('countresult_'+integration).innerHTML = responseText;
					}
				}).send();
			}
		}";



		$script2 = 'window.addEvent("domready", function() {';
		if(!empty($message->message_receiver['standard'])){
			foreach($message->message_receiver['standard'] as $oneParam => $details){
				if(!is_array($details)){
					$script2 .= "applyParamsValue('[$oneParam]','$details');";
				}else{
					foreach($details as $oneDetail => $value){
						if(!is_array($value)){
							$script2 .= "applyParamsValue('[$oneParam][$oneDetail]','$value');";
						}else{
							foreach($value as $oneValue => $value2){
								if(!is_array($value2)){
									$script2 .= "applyParamsValue('[".$oneParam."][".$oneDetail."][".$oneValue."]','$value2');";
								}else{
									foreach($value2 as $oneValue2 => $value3){
										$script2 .= "applyParamsValue('[".$oneParam."][".$oneDetail."][".$oneValue."][".$oneValue2."]','$value3');";
									}
								}
							}
						}
					}
				}
			}
		}

		if(!empty($message->message_receiver['auto'][$message->message_autotype])){
			foreach($message->message_receiver['auto'][$message->message_autotype] as $oneParam => $details){
				if(!is_array($details)){
					$script2 .= "applyAutoParamsValue('[$oneParam]','$details');";
				}else{
					foreach($details as $oneDetail => $value){
						if(!is_array($value)){
							$script2 .= "applyAutoParamsValue('[".$oneParam."][".$oneDetail."]','$value');";
						}else{
							foreach($value as $oneValue => $value2){
								$script2 .= "applyAutoParamsValue('[".$oneParam."][".$oneDetail."][".$oneValue."]','$value2');";
							}
						}
					}
				}
			}
		}
		$script2 .= '});';
		$doc->addScriptDeclaration($script.$script2);




		$testNumberReceiver = $app->getUserStateFromRequest($integration->componentName."_testNumberReceiver", $integration->componentName."_testNumberReceiver", '', 'string');
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

		if(empty($userInformations)) $userInformations = array();
		$dispatcher->trigger('onACYSMSReplaceTags', array(&$message, false));
		$dispatcher->trigger('onACYSMSReplaceUserTags', array(&$message, &$userInformations, false));



		if(ACYSMS_J30){
			$js = ' function removeChosen(){
						jQuery("#acysms_content .chzn-container").remove();
						jQuery("#acysms_content .chzn-done").removeClass("chzn-done").show();
				}
				window.addEvent("domready", function(){removeChosen();
						setTimeout(function(){
								removeChosen();
				}, 100);});';

			$doc->addScriptDeclaration($js);
		}



		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_PREVIEW').' : '.$message->message_subject, $this->ctrl.'&task=preview&message_id='.$message_id);

			$acyToolbar->custom('edit', JText::_('SMS_EDIT'), 'edit', false);
			$acyToolbar->cancel();

			$acyToolbar->divider();
			$acyToolbar->help('messages');
			$acyToolbar->display();
		}

		$this->assignRef('integrations', $integrations);
		$this->assignRef('dispatcher', $dispatcher);
		$this->assignRef('config', $config);
		$this->assignRef('messageBasedOn', $messageBasedOn);
		$this->assignRef('message_types', $message_types);
		$this->assignRef('message', $message);
		$this->assignRef('message_senddate', $message_senddate);
		$this->assignRef('timeField', $timeField);
		$this->assignRef('currentIntegration', $integration->componentName);
		$this->assignRef('filters', $filters);
		$this->assignRef('app', $app);
		$this->assignRef('listReceiversTest', $listReceiversTest);


		$this->setLayout('preview');
	}


	function summarybeforesend(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		JHTML::_('behavior.modal', 'a.modal');
		$queueClass = ACYSMS::get('class.queue');
		$messageClass = ACYSMS::get('class.message');

		$message_id = ACYSMS::getCID('message_id');
		$message = $messageClass->get($message_id);
		$numberUser = $queueClass->getNbReceivers($message);

		$query = "SELECT queue_message_id FROM ".ACYSMS::table('queue')." WHERE queue_message_id = ".intval($message_id);
		$db->setQuery($query);
		$alreadyInQueue = $db->loadResult();
		if(!empty($alreadyInQueue)){
			ACYSMS::enqueueMessage(JText::_('SMS_ALREADY_IN_QUEUE'), 'notice');
		}
		if(empty($numberUser)){
			ACYSMS::enqueueMessage(JText::_('SMS_NO_RECEIVERS'), 'notice');
		}

		$allowCustomerManagement = $config->get('allowCustomersManagement', '1');

		$my = JFactory::getUser();
		$customerClass = ACYSMS::get('class.customer');
		$customer = $customerClass->getCustomerByJoomID($my->id);
		if(!$app->isAdmin() && $allowCustomerManagement && empty($customer->customer_id)) die('You are not allowed to access this page, please check the customer management option in the AcySMS configuration');

		if(!$app->isAdmin() && $allowCustomerManagement){
			$customerClass = ACYSMS::get('class.customer');
			$my = JFactory::getUser();

			$credits = $customerClass->getCredits($my->id);
			$this->assignRef('credits', $credits);

			$customer = $customerClass->getCustomerByJoomID($my->id);
			$this->assignRef('customer', $customer);

			if($credits > 0){
				ACYSMS::enqueueMessage(JText::sprintf('SMS_X_CREDITS_LEFT', $credits), 'success');
			}else ACYSMS::enqueueMessage(JText::sprintf('SMS_X_CREDITS_LEFT', $credits), 'notice');

			$msgClass = ACYSMS::get('class.message');
			$partInformations = $msgClass->countMessageParts($message->message_body);

			$this->assignRef('partInformations', $partInformations);

			$msgToDisplay = '<br />'.JText::sprintf('SMS_CONTAINS_PARTS_COSTS_CREDITS', $partInformations->nbParts, ($numberUser * $partInformations->nbParts)).'<br />';
			$errorMsg = '';
			if(($numberUser * $partInformations->nbParts) > $credits){
				$URL = empty($customer->customer_credits_url) ? $config->get('default_credits_url', '') : $customer->customer_credits_url;

				if(empty($URL)){
					$errorMsg = JText::_('SMS_NOT_ENOUGH_CREDITS').' '.JText::_('SMS_CONTACT_ADMIN');
				}else $errorMsg = JText::_('SMS_NOT_ENOUGH_CREDITS').' <a href="'.htmlentities($URL).'">'.JText::_('SMS_CREDITS_URL').'</a>';
			}
		}


		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_PREVIEW').' : '.$message->message_subject, $this->ctrl.'&task=preview&message_id='.$message_id);

			$acyToolbar->custom('edit', JText::_('SMS_EDIT'), 'edit', false);
			$acyToolbar->cancel();
			$acyToolbar->display();
		}

		$this->assignRef('query', $query);
		$this->assignRef('numberUser', $numberUser);
		$this->assignRef('message', $message);
		$this->assignRef('alreadyInQueue', $alreadyInQueue);
		$this->assignRef('app', $app);
		$this->assignRef('config', $config);
		$this->assignRef('errorMsg', $errorMsg);
		$this->assignRef('msgToDisplay', $msgToDisplay);
		$this->assignRef('allowCustomerManagement', $allowCustomerManagement);
	}

	function answermessage(){
		$doc = JFactory::getDocument();
		$app = JFactory::getApplication();
		$doc->addStyleSheet(ACYSMS_CSS.'frontendedition.css');


		$message_id = ACYSMS::getCID('message_id');
		$config = ACYSMS::config();

		$messageMaxChar = $config->get('messageMaxChar');

		if(!empty($message_id)){
			$messageClass = ACYSMS::get('class.message');
			$message = $messageClass->get($message_id);
		}else{
			$message = new stdClass();
			$message->message_userid = '';
			$message->message_subject = '';
			$message->message_body = '';
			$message->message_created = time();
			$message->message_type = 'answer';
			$message->message_senddate = '';
			$message->message_status = 'notsent';
			$message->message_recipients = '';
		}

		$sliders = ACYSMS::get('helper.sliders');
		$sliders->setOptions(array('useCookie' => true));

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$tags = array();
		$dispatcher->trigger('onACYSMSGetTags', array(&$tags));
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
		$script .= 'if(window.document.getElementById("message_subject").value.length < 2  ){alert(\''.JText::_('SMS_ENTER_SUBJECT', true).'\'); return false;}';
		$script .= 'if(window.document.getElementById("message_body").value.length < 2  ){alert(\''.JText::_('SMS_ENTER_BODY', true).'\'); return false;}';
		if(version_compare(JVERSION, '1.6.0', '<')){
			$script .= 'submitform( pressbutton );} ';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}

		$script .= "function insertTag(tag){ try{jInsertEditorText(tag,'editor_body'); return true;} catch(err){alert('Your editor does not enable AcySMS to automatically insert the tag, please copy/paste it manually in your Message'); return false;}}";


		$toolbarHelper = ACYSMS::get('helper.toolbar');
		$toolbarHelper->setTitle(JText::_('SMS_MESSAGE'));
		$toolbarHelper->custom('answermessage', JText::_('SMS_APPLY'), 'save', false);
		$toolbarHelper->topfixed = false;
		$toolbarHelper->display();

		$doc->addScriptDeclaration($script);
		$this->assign('senderprofile', ACYSMS::get('type.senderprofile'));
		$this->assignRef('message', $message);
		$this->assignRef('sliders', $sliders);
		$this->assignRef('tags', $tags);
		$this->assignRef('app', $app);
		$this->assignRef('messageMaxChar', $messageMaxChar);
	}
}
