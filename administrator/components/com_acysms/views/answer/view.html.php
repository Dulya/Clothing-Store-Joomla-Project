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

class AnswerViewAnswer extends acysmsView{
	var $ctrl = 'answer';
	var $nameListing = 'SMS_ANSWERS';
	var $nameForm = 'answer';
	var $icon = 'answer';
	var $defaultSize = 160;

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	public function listing(){
		$this->_getListingData();
		$isAjax = 0;
		$this->assignRef('isAjax', $isAjax);
	}

	public function answerdatalisting(){
		$this->_getListingData();
		$isAjax = JRequest::getInt('isAjax', 0);
		$this->assignRef('isAjax', $isAjax);
	}

	private function _getListingData(){
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
		$phoneHelper = ACYSMS::get('helper.phone');
		JHTML::_('behavior.modal', 'a.modal');


		$selectedIntegration = $app->getUserStateFromRequest($paramBase."filter_integration", 'filter_integration', 0, 'string');
		$selectedMessage = $app->getUserStateFromRequest($paramBase."filter_message", 'filter_message', 0, 'int');
		$selectedAnswerReceiver = $app->getUserStateFromRequest($paramBase."filter_answerreceiver", 'filter_answerreceiver', 0, 'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'answer.answer_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		if($pageInfo->filter->order->dir != "asc") $pageInfo->filter->order->dir = 'desc';

		$integrationType = ACYSMS::get('type.integration');
		$integrationType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$integrationType->allIntegration = true;
		$integrationType->load();
		$dropdownFilters->integration = $integrationType->display('filter_integration', $selectedIntegration);

		$listMessageType = ACYSMS::get('type.message');
		$listMessageType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$dropdownFilters->message = $listMessageType->display('filter_message', $selectedMessage);

		$listSender = ACYSMS::get('type.answerreceiver');
		$listMessageType->js = 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"';
		$dropdownFilters->answerreceiver = $listSender->display('filter_answerreceiver', $selectedAnswerReceiver);

		if(!empty($selectedIntegration)) $filters[] = 'answer.answer_receiver_table = '.$db->quote($selectedIntegration);
		if(!empty($selectedMessage)) $filters[] = 'answer.answer_message_id = '.intval($selectedMessage);
		if(!empty($selectedAnswerReceiver)) $filters[] = 'answer.answer_to = '.$db->quote($selectedAnswerReceiver);



		$integration = ACYSMS::getIntegration();

		if($integration->useJoomlaName){
			$columnName = 'joomUsers.name';
		}else $columnName = $integration->tableAlias.'.'.$integration->nameField;

		$query = 'SELECT *, '.$columnName.' AS integrationNameField, joomUsers.name AS joomlaUserNameField  
					FROM '.ACYSMS::table('answer').' AS answer
					LEFT JOIN '.$integration->tableName.' AS '.$integration->tableAlias.'
					ON answer_receiver_id = '.$integration->tableAlias.'.'.$integration->primaryField.'
					LEFT JOIN #__users AS joomUsers ON '.$integration->tableAlias.'.'.$integration->joomidField.' = joomUsers.id';
		$queryCount = 'SELECT COUNT(answer.answer_id) FROM '.ACYSMS::table('answer').' as answer
					LEFT JOIN '.$integration->tableName.' AS '.$integration->tableAlias.'
					ON answer_receiver_id = '.$integration->primaryField;


		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$query .= ' JOIN #__acysms_message AS message ON message.message_id = answer.answer_message_id ';
			$queryCount .= ' JOIN #__acysms_message AS message ON message.message_id = answer.answer_message_id ';
			$filters[] = 'message.message_userid = '.intval($my->id);
		}


		$searchMap = array('answer.answer_id', 'answer.answer_body', 'answer.answer_from', 'answer.answer_to');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';

			$nameFilter = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";

			$nameFilter .= ' OR ('.$integration->tableAlias.'.'.$integration->nameField.' LIKE '.$searchVal.' AND answer.answer_receiver_table = '.$db->Quote($integration->componentName).')';

			$filters[] = $nameFilter;
		}

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


		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$isAjax = JRequest::getInt('isAjax', 0);

		if($app->isAdmin() && !$isAjax){
			$acyToolbar = ACYSMS::get('helper.toolbar');

			$acyToolbar->setTitle(JText::_($this->nameListing), $this->ctrl);

			if(ACYSMS::isAllowed($config->get('acl_answers_export', 'all'))) $acyToolbar->link(ACYSMS::completeLink('answer&task=exportGlobal'), JText::_('SMS_EXPORT'), 'export');
			$acyToolbar->divider();
			if(ACYSMS::isAllowed($config->get('acl_answers_delete', 'all'))) $acyToolbar->delete();

			$acyToolbar->divider();

			$acyToolbar->help('answers');
			$acyToolbar->display();
		}


		$ctrl = ($app->isAdmin() ? 'answer' : 'frontanswer');

		$script = '
		function reloadAnswerDataListing(){
			try{
			var ajaxCall = new Ajax(\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=answerdatalisting&isAjax=1\', {
				method: \'get\', onComplete: function(responseText, responseXML){
					document.getElementById(\'answerlistingdata\').innerHTML = responseText;
					SqueezeBox.assign($$("a.modal"), {
						parse: "rel"
					});
				}
			}).request();

			}catch(err){
				new Request({
					url: \'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=answerdatalisting&isAjax=1\', method: \'get\', onSuccess: function(responseText, responseXML){
						document.getElementById(\'answerlistingdata\').innerHTML = responseText;
						SqueezeBox.assign($$("a.modal"), {
							parse: "rel"
						});
					}
				}).send();
			}
			window.setTimeout(reloadAnswerDataListing, 60000);
		}

		window.addEvent("domready", function() {
			reloadAnswerDataListing();
		})
		';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);

		$this->assignRef('dropdownFilters', $dropdownFilters);
		$this->assignRef('app', $app);
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('config', $config);
		$this->assignRef('phoneHelper', $phoneHelper);
		$this->assignRef('integration', $integration);
	}
}
