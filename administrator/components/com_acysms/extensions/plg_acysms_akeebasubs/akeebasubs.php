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

class plgAcysmsAkeebaSubs extends JPlugin{

	var $sendervalues = array();

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_akeebasubs')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'akeebasubs');
			$this->params = new acysmsParameter($plugin->params);
		}
	}





	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		if(!$app->isAdmin()){
			$helperPlugin = ACYSMS::get('helper.plugins');
			if(!$helperPlugin->allowSendByGroups('akeebasubs')) return;
		}

		$newFilter = new stdClass();
		$newFilter->name = JText::_('SMS_AKEEBA_SUBSCRIPTIONS_LEVELS');
		$filters['communityFilters']['akeebasubs'] = $newFilter;
	}

	function onACYSMSDisplayFilterParams_akeebasubs($message){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		if(!$app->isAdmin()){
			$frontEndFilters = $config->get('frontEndFilters');
			if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

			$availableLists = array();
			foreach($frontEndFilters as $oneCondition){
				if($oneCondition['filters'] != 'virtuemart_vendors') continue;

				if(empty($oneCondition['filterDetails']) || empty($oneCondition['filterDetails']['virtuemart_vendors'])) continue;
				$availableLists = array_merge($availableLists, $oneCondition['filterDetails']['virtuemart_vendors']);
			}
			if(empty($availableLists)) return;
		}

		$query = 'SELECT title, akeebasubs_level_id FROM #__akeebasubs_levels';
		$db->setQuery($query);
		$levels = $db->loadObjectList();

		$userState = array();
		$userState[] = JHTML::_('select.option', '0', '---');
		$userState[] = JHTML::_('select.option', '1', JText::_('SMS_ONLY_SUBSCRIBED_USERS'));
		$userState[] = JHTML::_('select.option', '-1', JText::_('SMS_ONLY_UNSUBSCRIBED_USERS'));
		$userState[] = JHTML::_('select.option', '-2', JText::_('SMS_NO_SUBSCRIPTIONS'));

		$relation = array();
		$relation[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$akeebaLevel = array();
		foreach($levels as $oneLevel){
			$akeebaLevel[] = JHTML::_('select.option', $oneLevel->akeebasubs_level_id, $oneLevel->title);
		}
		?><span id="countresult_akeebasubs"></span><br/><?php
		echo JText::_('SMS_SEND_IT_TO_USERS_MATCHING_CONDITIONS').'<br />';

		for($i = 0; $i < 5; $i++){

			$listingUserState = JHTML::_('select.genericlist', $userState, 'data[message][message_receiver][standard][akeebasubs][condition_'.$i.'][userState]', 'onchange="countresults(\'akeebasubs\')" class="inputbox" style="max-width:500px"', 'value', 'text', '');
			$listingLevel = JHTML::_('select.genericlist', $akeebaLevel, 'data[message][message_receiver][standard][akeebasubs][condition_'.$i.'][level]', 'onchange="countresults(\'akeebasubs\')" class="inputbox" style="max-width:500px"', 'value', 'text', '');
			echo $listingUserState.' '.$listingLevel;
			echo '<br />';
			if($i < 4) echo JHTML::_('select.genericlist', $relation, 'data[message][message_receiver][standard][akeebasubs][condition_'.$i.'][relation]', 'onchange="countresults(\'akeebasubs\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text').'<br />';
		}
	}

	function onACYSMSSelectData_akeebasubs(&$acyquery, $message){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['akeebasubs'])) return;
		if(!isset($acyquery->join['akeebausers'])) $acyquery->join['akeebausers'] = 'LEFT JOIN #__akeebasubs_users AS akeebausers ON akeebausers.user_id = joomusers.id ';
		$addCondition = '';
		$whereConditions = '';
		$i = 0;
		foreach($message->message_receiver['standard']['akeebasubs'] as $oneCondition){
			if(empty($oneCondition['userState'])) continue;else $enable = $oneCondition['userState'];
			if(empty($oneCondition['level'])) continue;else $level = $oneCondition['level'];
			if($enable == -2){
				$acyquery->join[] = 'LEFT JOIN #__akeebasubs_subscriptions AS akeebasubscriptions_'.$i.' ON akeebasubscriptions_'.$i.'.user_id = akeebausers.user_id AND akeebasubscriptions_'.$i.'.akeebasubs_level_id = '.intval($level);
				$tmpWhereCondition = '(akeebasubscriptions_'.$i.'.akeebasubs_level_id IS NULL)';
				$i++;
			}else if(!empty($level) && !empty($enable)){
				if($enable == -1) $enable = 0;
				$acyquery->join[] = 'LEFT JOIN #__akeebasubs_subscriptions AS akeebasubscriptions_'.$i.' ON akeebasubscriptions_'.$i.'.user_id = akeebausers.user_id';
				$tmpWhereCondition = '(akeebasubscriptions_'.$i.'.enabled = '.intval($enable).' AND akeebasubscriptions_'.$i.'.akeebasubs_level_id = '.intval($level).')';
			}
			if(!empty($addCondition)){
				$whereConditions = '('.$whereConditions.' '.$addCondition.' '.$tmpWhereCondition.') ';
			}else $whereConditions = ' '.$tmpWhereCondition;
			if(!empty($oneCondition['relation'])){
				$addCondition = $oneCondition['relation'];
			}else  $addCondition = ' AND ';
			$i++;
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}


	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_AKEEBA_SUBSCRIPTIONS_LEVELS');
		$authorizedFilters['akeebasubs'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_akeebasubs(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

}//endclass
