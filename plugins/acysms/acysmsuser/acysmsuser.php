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

class plgAcysmsAcysmsUser extends JPlugin{

	var $sendervalues = array();

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_acysms')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'acysmsuser');
			$this->params = new acysmsParameter($plugin->params);
		}
	}




	function onACYSMSGetTags(&$tags){

		$tags['communityTags']['acysms'] = new stdClass();
		$tags['communityTags']['acysms']->name = JText::sprintf('SMS_X_USER_INFO', 'AcySMS');
		$tableFields = acysms_getColumns('#__acysms_user');

		$tags['communityTags']['acysms']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['communityTags']['acysms']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{acysms:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['communityTags']['acysms']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{acysms:message_id}\')" class="row'.$k.'"><td>'.JText::_('SMS_MESSAGE_ID').'</td></tr>';
		$tags['communityTags']['acysms']->content .= '</tbody></table>';
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$tagKey = JText::_('SMS_USER_TAG_KEY');
		if($tagKey == 'SMS_USER_TAG_KEY') $tagKey = 'acysms';

		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, $tagKey);


		if(!isset($user->acysms)){
			$db = JFactory::getDBO();
			if(!empty($user->joomla->id)){
				$query = 'SELECT * FROM #__acysms_user WHERE user_joomid = '.intval($user->joomla->id);
				$db->setQuery($query);
				$user->acysms = $db->loadObject();
			}
		}

		foreach($tags as $oneString => $oneObject){
			if(isset($user->acysms->{$oneObject->id}) && strlen($user->acysms->{$oneObject->id}) > 0){
				$tags[$oneString] = $user->acysms->{$oneObject->id};
			}else if((isset($message->{$oneObject->id}) && strlen($message->{$oneObject->id}) > 0)){
				$tags[$oneString] = $message->{$oneObject->id};
			}else $tags[$oneString] = $oneObject->default;

			if($oneObject->id == 'user_activationcode'){
				if(!empty($oneObject->subType)){
					if(is_string($tags[$oneString])) $tags[$oneString] = unserialize($tags[$oneString]);
					if(!empty($tags[$oneString][$oneObject->subType])){
						$tags[$oneString] = $tags[$oneString][$oneObject->subType];
					}else $tags[$oneString] = 'Error subType '.$oneObject->subType.'. Use '.implode(' or ', array_keys($tags[$oneString]));
				}else{
					if(is_string($tags[$oneString])) $tags[$oneString] = unserialize($tags[$oneString]);
					if(is_array($tags[$oneString])) $tags[$oneString] = reset($tags[$oneString]);
				}
			}
			$helperPlugin->formatString($tags[$oneString], $oneObject);
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}





	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$db = JFactory::getDBO();

		$helperPlugin = ACYSMS::get('helper.plugins');

		$filter = new stdClass();
		$filter->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'AcySMS');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('acysmsfields'))) $filters['communityFilters']['acysmsField'] = $filter;

		$secondFilter = new stdClass();
		$secondFilter->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'AcySMS Statistics');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('acysmsstatisticsfields'))) $filters['communityFilters']['acysmsStatisticsField'] = $secondFilter;
	}


	function onACYSMSDisplayFilterParams_acysmsStatisticsfield($message){

		$field = array();
		$field[] = JHTML::_('select.option', '', ' - - - ');


		$field[] = JHTML::_('select.option', '0', JText::_('SMS_STATUS_FAILED'));
		$field[] = JHTML::_('select.option', '1', JText::_('SMS_STATUS_1'));
		$field[] = JHTML::_('select.option', 'nevertried', JText::_('SMS_NEVER_TRIED_TO_SEND'));
		$field[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('SMS_DELIVERY_STATUS'));
		$field[] = JHTML::_('select.option', '2', JText::_('SMS_STATUS_2'));
		$field[] = JHTML::_('select.option', '3', JText::_('SMS_STATUS_3'));
		$field[] = JHTML::_('select.option', '4', JText::_('SMS_STATUS_4'));
		$field[] = JHTML::_('select.option', '5', JText::_('SMS_STATUS_5'));
		$field[] = JHTML::_('select.option', '</OPTGROUP>');
		$field[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('SMS_DELIVERY_FAILURE_STATUS'));
		$field[] = JHTML::_('select.option', '-1', JText::_('SMS_STATUS_M1'));
		$field[] = JHTML::_('select.option', '-2', JText::_('SMS_STATUS_M2'));
		$field[] = JHTML::_('select.option', '-3', JText::_('SMS_STATUS_M3'));
		$field[] = JHTML::_('select.option', '-99', JText::_('SMS_STATUS_M99'));
		$field[] = JHTML::_('select.option', '</OPTGROUP>');

		$db = JFactory::getDBO();
		$db->setQuery("SELECT `message_id`,CONCAT(`message_subject`,' ( ID : ',`message_id`,' )') AS value FROM `#__acysms_message` WHERE `message_type` NOT IN ('answer') ORDER BY `message_subject` ASC ");
		$allMessages = $db->loadObjectList();
		$element = new stdClass();
		$element->message_id = 0;
		$element->value = JText::_('SMS_AT_LEAST_ONE_MESSAGE');
		array_unshift($allMessages, $element);

		$operators = ACYSMS::get('type.operators');

		$relation = array();
		$relation[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		?>
		<span id="countresult_acysmsStatisticsfield"></span>
		<?php
		for($i = 0; $i < 5; $i++){
			$operators->extra = 'onchange="countresults(\'acysmsStatisticsfield\')"';
			$return = '<div id="filter'.$i.'acysmsStatisticsfield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][acysms][acysmsStatisticsfield][".$i."][status]", 'onchange="countresults(\'acysmsStatisticsfield\')" class="inputbox" size="1" style=""', 'value', 'text');
			$return .= JHTML::_('select.genericlist', $allMessages, "data[message][message_receiver][standard][acysms][acysmsStatisticsfield][".$i."][message_id]", 'onchange="countresults(\'acysmsStatisticsfield\')" class="inputbox" style="width:auto;" size="1"', 'message_id', 'value').'<br />';
			if($i != 4) $return .= JHTML::_('select.genericlist', $relation, "data[message][message_receiver][standard][acysms][acysmsStatisticsfield][".$i."][relation]", 'onchange="countresults(\'acysmsStatisticsfield\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
			echo $return;
		}
	}

	function onACYSMSSelectData_acysmsStatisticsfield(&$acyquery, $message){
		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['acysms']['acysmsStatisticsfield'])) return;


		$addCondition = '';
		$whereConditions = '';
		$nbJoin = 0;

		foreach($message->message_receiver['standard']['acysms']['acysmsStatisticsfield'] as $filterNumber => $oneFilter){
			if($oneFilter['status'] == '') continue;
			if(!empty($addCondition)) $whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])){
				$addCondition = $oneFilter['relation'];
			}else  $addCondition = 'AND';

			$acyquery->join['acysmsstatistics'.$nbJoin] = 'LEFT JOIN #__acysms_statsdetails AS acysmsstatsdetails'.$nbJoin.' ON '.$integration->tableAlias.'.'.$integration->primaryField.' = acysmsstatsdetails'.$nbJoin.'.statsdetails_receiver_id ';

			if($oneFilter['status'] == 'nevertried'){
				$acyquery->join['acysmsstatistics'.$nbJoin] .= ' AND acysmsstatsdetails'.$nbJoin.'.statsdetails_message_id = '.intval($oneFilter['message_id']);
				$whereConditions .= 'acysmsstatsdetails'.$nbJoin.'.statsdetails_status IS NULL';
			}else if($oneFilter['status'] == '0'){
				$whereConditions .= 'acysmsstatsdetails'.$nbJoin.'.statsdetails_status <= 0';
			}else    $whereConditions .= 'acysmsstatsdetails'.$nbJoin.'.statsdetails_status = '.intval($oneFilter['status']);

			if(!empty($oneFilter['message_id']) && $oneFilter['status'] != 'nevertried') $whereConditions .= ' AND acysmsstatsdetails'.$nbJoin.'.statsdetails_message_id = '.intval($oneFilter['message_id']);
			$nbJoin++;
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}


	function onACYSMSDisplayFilterParams_acysmsField($message){
		$fields = acysms_getColumns('#__acysms_user');
		if(empty($fields)) return;

		$field = array();
		$field[] = JHTML::_('select.option', '', ' - - - ');
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option', $oneField, $oneField);
		}

		$relation = array();
		$relation[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');

		?>
		<span id="countresult_acysmsField"></span>
		<?php
		for($i = 0; $i < 5; $i++){
			$operators->extra = 'onchange="countresults(\'acysmsField\')"';
			$return = '<div id="filter'.$i.'acyfield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][acysms][acysmsField][".$i."][map]", 'onchange="countresults(\'acysmsField\')" class="inputbox" size="1"', 'value', 'text');
			$return .= ' '.$operators->display("data[message][message_receiver][standard][acysms][acysmsField][".$i."][operator]").' <input onchange="countresults(\'acysmsField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][acysms][acysmsField]['.$i.'][value]" style="width:200px" value=""></div>';
			if($i != 4) $return .= JHTML::_('select.genericlist', $relation, "data[message][message_receiver][standard][acysms][acysmsField][".$i."][relation]", 'onchange="countresults(\'acysmsField\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
			echo $return;
		}
	}

	function onACYSMSSelectData_acysmsField(&$acyquery, $message){

		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['acysms']['acysmsField'])) return;
		if(!isset($acyquery->join['acysmsusers']) && $integration->componentName != 'acysms') $acyquery->join['acysmsusers'] = 'LEFT JOIN #__acysms_user AS acysmsusers ON joomusers.id = acysmsusers.user_joomid ';
		$addCondition = '';
		$whereConditions = '';
		foreach($message->message_receiver['standard']['acysms']['acysmsField'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			if(!empty($addCondition)) $whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])){
				$addCondition = $oneFilter['relation'];
			}else  $addCondition = 'AND';

			$timeTag = false;
			$replace = array('{year}', '{month}', '{weekday}', '{day}');
			foreach($replace as $oneReplace){
				if(strpos($oneFilter['value'], $oneReplace) !== false) $timeTag = true;
			}

			if(strpos($oneFilter['value'], '{time}') !== false) $oneFilter['value'] = ACYSMS::replaceDate($oneFilter['value']);
			if($timeTag){
				$replaceBy = array(date('Y'), date('m'), date('N'), date('d'));
				$oneFilter['value'] = str_replace($replace, $replaceBy, $oneFilter['value']);
			}

			$whereConditions .= $acyquery->convertQuery('acysmsusers', $oneFilter['map'], $oneFilter['operator'], $oneFilter['value'], '');
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}

	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'AcySMS');
		$authorizedFilters['acysmsfields'] = $newType;

		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'AcySMS Statistics');
		$authorizedFilters['acysmsstatisticsfields'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_acysmsfields(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

}//endclass
