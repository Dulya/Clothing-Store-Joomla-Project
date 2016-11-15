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

class plgAcysmsJoomlagroups extends JPlugin{

	var $sendervalues = array();

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'joomlagroups');
			$this->params = new acysmsParameter($plugin->params);
		}
	}





	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		if(!$app->isAdmin()){
			$helperPlugin = ACYSMS::get('helper.plugins');
			if(!$helperPlugin->allowSendByGroups('joomlagroup')) return;
		}

		$newFilter = new stdClass();
		$newFilter->name = JText::_('SMS_JOOMLA_GROUPS');
		$filters['communityFilters']['joomlagroups'] = $newFilter;
	}

	function onACYSMSDisplayFilterParams_joomlagroups($message){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$my = JFactory::getUser();
		if(!ACYSMS_J16){
			$myJoomlaGroups = array($my->gid);
		}else{
			jimport('joomla.access.access');
			$myJoomlaGroups = JAccess::getGroupsByUser($my->id, false);
		}

		if(!$app->isAdmin()){
			$frontEndFilters = $config->get('frontEndFilters');
			if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

			$availableLists = array();
			foreach($frontEndFilters as $oneCondition){
				if($oneCondition['filters'] != 'joomlagroup') continue;
				if(empty($oneCondition['filterDetails']) || empty($oneCondition['filterDetails']['joomlagroup'])) continue;
				if($oneCondition['typeDetails'] != 'all' && !in_array($oneCondition['typeDetails'], $myJoomlaGroups)) continue;
				$availableLists = array_merge($availableLists, $oneCondition['filterDetails']['joomlagroup']);
			}
			if(empty($availableLists)) return;
		}

		if(ACYSMS_J16){
			$query = 'SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC';
			$db->setQuery($query);
			$groups = $db->loadObjectList('id');
		}else{
			$acl = JFactory::getACL();
			$groups = $acl->get_group_children_tree(null, 'USERS', false);
		}
		foreach($groups as $id => $group){
			if(isset($groups[$group->parent_id])){
				$groups[$id]->level = intval(@$groups[$group->parent_id]->level) + 1;
				$groups[$id]->text = str_repeat('- - ', $groups[$id]->level).$groups[$id]->text;
			}
		}

		echo JText::_('SMS_SEND_JOOMLAGROUPS').' : <br />';
		foreach($groups as $oneGroup){
			if(!$app->isAdmin()){
				if(!in_array($oneGroup->id, $availableLists)) continue;
			} ?>
			<label><input type="checkbox" name="data[message][message_receiver][standard][joomla][groups][<?php echo $oneGroup->id; ?>]" value="<?php echo $oneGroup->id ?>" title="<?php echo $oneGroup->title ?>"/> <?php echo $oneGroup->text ?></label><br/>
		<?php }
	}

	function onACYSMSDisplayResults_joomlagroups($message){
		if(empty($message['joomla']) || empty($message['joomla']['groups'])) return;

		$db = JFactory::getDBO();

		JArrayHelper::toInteger($message['joomla']['groups']);

		if(ACYSMS_J16){
			$query = 'SELECT title FROM #__usergroups WHERE id IN ('.implode(',', $message['joomla']['groups']).')';
		}else{
			$query = 'SELECT name FROM #__groups WHERE id IN ('.implode(',', $message['joomla']['groups']).')';
		}
		$db->setQuery($query);
		$lists = acysms_loadResultArray($db);
		if(empty($lists)) return;

		echo JText::_('SMS_SEND_JOOMLAGROUPS').' : '.implode(', ', $lists).'<br />';
	}

	function onACYSMSSelectData_joomlagroups(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['joomla']['groups'])) return;
		$acyquery->join['joomusergroup'] = 'JOIN #__user_usergroup_map as joomusergroup  ON joomusergroup.user_id = joomusers.id';

		JArrayHelper::toInteger($message->message_receiver['standard']['joomla']['groups']);

		$acyquery->where[] = ' joomusergroup.group_id IN ('.implode(',', ($message->message_receiver['standard']['joomla']['groups'])).') ';
	}



	public function onACYSMSdisplayAuthorizedType(&$authorizedTypes){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_JOOMLA_GROUP');
		$authorizedTypes['joomlagroup'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedType_joomlagroup(&$authorizedTypesSelection, $conditionNumber){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		if(version_compare(JVERSION, '1.6.0', '<')){
			$db->setQuery('SELECT id, name as title FROM #__groups');
		}else{
			$db->setQuery('SELECT id, title FROM #__usergroups');
		}
		$jGroups = $db->loadObjectList();

		if(empty($jGroups)) return;

		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

		if(!isset($frontEndFilters[$conditionNumber]['typeDetails'])) $frontEndFilters[$conditionNumber]['typeDetails'] = '';

		$joomlaGroupsToDisplay = array();
		$joomlaGroupsToDisplay[] = JHTML::_('select.option', 'all', JText::_('SMS_ALL_GROUPS'));
		foreach($jGroups as $oneGroup){
			$joomlaGroupsToDisplay[] = JHTML::_('select.option', $oneGroup->id, $oneGroup->title);
		}
		$authorizedTypesSelection .= '<span id="'.$conditionNumber.'_acysmsTypeDetails">'.JHTML::_('acysmsselect.genericlist', $joomlaGroupsToDisplay, 'config[frontEndFilters]['.$conditionNumber.'][typeDetails]', '', 'value', 'text', $frontEndFilters[$conditionNumber]['typeDetails']).'</span>';
	}

	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_JOOMLA_GROUP');
		$authorizedFilters['joomlagroup'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_joomlagroup(&$authorizedFiltersSelection, $conditionNumber){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, title FROM #__usergroups');
		$jGroups = $db->loadObjectList();

		if(empty($jGroups)) return;

		$config = ACYSMS::config();
		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

		$result = '<br />';
		foreach($jGroups as $oneGroup){
			if(!empty($frontEndFilters[$conditionNumber]['filterDetails']['joomlagroup']) && in_array($oneGroup->id, $frontEndFilters[$conditionNumber]['filterDetails']['joomlagroup'])){
				$checked = 'checked="checked"';
			}else $checked = '';
			$result .= '<label><input type="checkbox" name="config[frontEndFilters]['.$conditionNumber.'][filterDetails][joomlagroup]['.$oneGroup->id.']" value="'.$oneGroup->id.'" '.$checked.' title= "'.$oneGroup->title.'"/> '.$oneGroup->title.'</label><br />';
		}
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails">'.$result.'</span>';
	}

}//endclass
