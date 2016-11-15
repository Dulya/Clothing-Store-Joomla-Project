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

class plgAcysmsSeblod extends JPlugin{

	var $sendervalues = array();

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_cck')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'seblod');
			$this->params = new acysmsParameter($plugin->params);
		}
	}




	function onACYSMSGetTags(&$tags){

		$tags['communityTags']['seblod'] = new stdClass();
		$tags['communityTags']['seblod']->name = JText::sprintf('SMS_X_USER_INFO', 'Seblod');
		$db = JFactory::getDBO();
		$tableFields = array_merge(acysms_getColumns('#__cck_store_item_users'), acysms_getColumns('#__cck_store_form_user'));

		$tags['communityTags']['seblod']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['communityTags']['seblod']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{seblod:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['communityTags']['seblod']->content .= '</tbody></table>';
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'seblod');

		foreach($tags as $oneTag){
			if(!isset($user->seblod)){
				$db = JFactory::getDBO();
				if(!empty($user->joomla->id)){
					$query = 'SELECT * FROM #__cck_store_item_users AS sebloditemusers
							JOIN #__cck_store_form_user AS seblodcckstoreform ON seblodcckstoreform.id = sebloditemusers.id
							WHERE id = '.intval($user->joomla->id);
					$db->setQuery($query);
					$user->acysms = $db->loadObject();
				}
			}
		}
		foreach($tags as $oneString => $oneObject){
			$tags[$oneString] = (isset($user->seblod->{$oneObject->id}) && strlen($user->seblod->{$oneObject->id}) > 0) ? $user->seblod->{$oneObject->id} : $oneObject->default;
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

		$app = JFactory::getApplication();

		$helperPlugin = ACYSMS::get('helper.plugins');

		$filter = new stdClass();
		$filter->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'Seblod');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('seblodfield'))) $filters['communityFilters']['seblodField'] = $filter;
	}


	function onACYSMSDisplayFilterParams_seblodField($message){
		$db = JFactory::getDBO();
		$fields = array('seblodusers' => acysms_getColumns('#__cck_store_item_users'), 'seblodcckstoreform' => acysms_getColumns('#__cck_store_form_user'));
		if(empty($fields)) return;

		$field = array();
		$field[] = JHTML::_('select.option', '', ' - - - ');

		foreach($fields as $oneTable => $oneTableFields){
			foreach($oneTableFields as $oneField => $oneType) $field[] = JHTML::_('select.option', $oneTable.'_'.$oneField, $oneField);
		}
		$relation = array();
		$relation[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');

		?>
		<span id="countresult_seblodField"></span>
		<?php
		for($i = 0; $i < 5; $i++){
			$operators->extra = 'onchange="countresults(\'seblodField\')"';
			$return = '<div id="filter'.$i.'acyfield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][seblod][seblodField][".$i."][map]", 'onchange="countresults(\'seblodField\')" class="inputbox" size="1"', 'value', 'text');
			$return .= ' '.$operators->display("data[message][message_receiver][standard][seblod][seblodField][".$i."][operator]").' <input onchange="countresults(\'seblodField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][seblod][seblodField]['.$i.'][value]" style="width:200px" value=""></div>';
			if($i != 4) $return .= JHTML::_('select.genericlist', $relation, "data[message][message_receiver][standard][seblod][seblodField][".$i."][relation]", 'onchange="countresults(\'seblodField\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
			echo $return;
		}
	}

	function onACYSMSSelectData_seblodField(&$acyquery, $message){
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['seblod']['seblodField'])) return;
		if(!isset($acyquery->join['seblodusers']) && $integration->componentName != 'seblod') $acyquery->join['seblodusers'] = 'JOIN #__cck_store_item_users AS seblodusers ON joomusers.id = seblodusers.id';
		if(!isset($acyquery->join['seblodcckstoreform'])) $acyquery->join['seblodcckstoreform'] = 'JOIN #__cck_store_form_user AS seblodcckstoreform ON seblodcckstoreform.id = seblodusers.id';
		$addCondition = '';
		$whereConditions = '';


		foreach($message->message_receiver['standard']['seblod']['seblodField'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			if(!empty($addCondition)) $whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])){
				$addCondition = $oneFilter['relation'];
			}else  $addCondition = 'AND';

			$prefixs = explode('_', $oneFilter['map']);

			$whereConditions .= $acyquery->convertQuery($prefixs[0], $prefixs[1], $oneFilter['operator'], $oneFilter['value']);
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}

	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'Seblod');
		$authorizedFilters['seblodfield'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_acymailingfield(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}
}//endclass
