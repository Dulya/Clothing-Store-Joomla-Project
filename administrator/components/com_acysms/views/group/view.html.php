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

class GroupViewGroup extends acysmsView{
	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYSMS_COMPONENT.'.'.$this->getName();

		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'acysmsgroup.group_ordering', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'asc', 'word');

		if($pageInfo->filter->order->dir != "asc") $pageInfo->filter->order->dir = 'desc';

		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.group_limit', 'limit', $app->getCfg('group_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$listingFilters = new stdClass();

		$selectedCreator = $app->getUserStateFromRequest($paramBase."filter_creator", 'filter_creator', 0, 'int');
		$groupCreatorType = ACYSMS::get('type.groupcreator');
		$listingFilters->creator = $groupCreatorType->display('filter_creator', $selectedCreator);

		$listingFilters->publishedStatus = '';
		$selectedPublishedStatus = $app->getUserStateFromRequest($paramBase."filter_grouppublished", 'filter_grouppublished', '1', 'int');
		$listPublishedStatus = ACYSMS::get('type.grouppublished');
		if(!empty($listPublishedStatus->values)) $listingFilters->publishedStatus = $listPublishedStatus->display('filter_grouppublished', $selectedPublishedStatus);


		$db = JFactory::getDBO();

		$searchMap = array('acysmsgroup.group_name', 'acysmsgroup.group_description', 'acysmsgroup.group_id');
		$filters = array();

		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			if(!ACYSMS_J16){
				$groups = $my->gid;
				$condGroup = ' OR group_access_manage LIKE (\'%,'.$groups.',%\')';
				$condGroup .= ' OR group_user_id = '.intval($my->id);
			}else{
				jimport('joomla.access.access');
				$groups = JAccess::getGroupsByUser($my->id, false);
				$condGroup = '';
				foreach($groups as $group){
					$condGroup .= ' OR group_access_manage LIKE (\'%,'.$group.',%\')';
					$condGroup .= ' OR group_user_id = '.intval($my->id);
				}
			}
			$filters[] = '(group_access_manage = \'all\' '.$condGroup.')';
		}


		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}

		if(!empty($selectedCreator)) $filters[] = 'acysmsgroup.group_user_id = '.intval($selectedCreator);
		if(!empty($selectedPublishedStatus)) $filters[] = 'acysmsgroup.group_published = '.$db->quote($selectedPublishedStatus);

		$query = 'SELECT acysmsgroup.*, joomusers.name AS creatorname, joomusers.username, joomusers.email AS email';
		$query .= ' FROM '.ACYSMS::table('group').' AS acysmsgroup';
		$query .= ' LEFT JOIN #__users AS joomusers on acysmsgroup.group_user_id = joomusers.id';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db->setQuery($query, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$queryCount = 'SELECT COUNT(acysmsgroup.group_id) FROM  '.ACYSMS::table('group').' AS acysmsgroup';
		if(!empty($pageInfo->search)) $queryCount .= ' LEFT JOIN '.ACYSMS::table('users', false).' AS joomusers on acysmsgroup.group_user_id = joomusers.id';

		if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';

		$db->setQuery($queryCount);
		$pageInfo->elements->total = $db->loadResult();

		$groupsids = array();
		foreach($rows as $oneRow){
			$groupsids[] = intval($oneRow->group_id);
		}

		JArrayHelper::toInteger($groupsids);

		$subscriptionresults = array();
		if(!empty($groupsids)){
			$querySubscription = 'SELECT count(groupuser_group_id) AS total, groupuser_group_id, groupuser_status FROM '.ACYSMS::table('groupuser').' WHERE groupuser_group_id IN ('.implode(',', $groupsids).') GROUP BY groupuser_group_id, groupuser_status';
			$db->setQuery($querySubscription);
			$countresults = $db->loadObjectList();
			foreach($countresults as $oneResult){
				$subscriptionresults[$oneResult->groupuser_group_id][intval($oneResult->groupuser_status)] = $oneResult->total;
			}
		}

		foreach($rows as $i => $oneRow){
			$rows[$i]->nbsub = intval(@$subscriptionresults[$oneRow->group_id][1]);
		}

		if(!empty($pageInfo->search)){
			$rows = ACYSMS::search($pageInfo->search, $rows);
		}

		$pageInfo->elements->page = count($rows);

		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_GROUPS'), 'group');


			$acyToolbar->custom('archive', JText::_('SMS_ARCHIVE').' / '.JText::_('SMS_UNARCHIVE'), 'archive', true);

			$acyToolbar->divider();

			$acyToolbar->add();
			$acyToolbar->edit();
			if(ACYSMS::isAllowed($config->get('acl_groups_delete', 'all'))) $acyToolbar->delete();

			$acyToolbar->divider();
			$acyToolbar->help('groups');

			$acyToolbar->display();
		}
		$order = new stdClass();
		$order->ordering = false;
		$order->orderUp = 'orderup';
		$order->orderDown = 'orderdown';
		$order->reverse = false;
		if($pageInfo->filter->order->value == 'acysmsgroup.group_ordering'){
			$order->ordering = true;
			if($pageInfo->filter->order->dir == 'desc'){
				$order->orderUp = 'orderdown';
				$order->orderDown = 'orderup';
				$order->reverse = true;
			}
		}

		$this->assignRef('filters', $listingFilters);
		$this->assignRef('order', $order);
		$toggleClass = ACYSMS::get('helper.toggle');
		$this->assignRef('toggleClass', $toggleClass);
		$this->assignRef('rows', $rows);
		$this->assignRef('pageInfo', $pageInfo);
		$this->assignRef('pagination', $pagination);
		$this->assignRef('app', $app);
		$this->assignRef('config', $config);
	}

	function form(){
		$app = JFactory::getApplication();
		JHTML::_('behavior.modal', 'a.modal');

		$group_id = ACYSMS::getCID('group_id');

		$groupClass = ACYSMS::get('class.group');
		if(!empty($group_id)){
			$group = $groupClass->get($group_id);
			if(!empty($group->group_user_id)){
				$user = JFactory::getUser($group->group_user_id);
				$group->group_creatorname = $user->name;
			}
		}else{
			$group = new stdClass();
			$group->group_published = 0;
			$group->group_description = '';
			$group->group_published = 1;
			$group->group_access_manage = 'none';
			$user = JFactory::getUser();
			$group->group_creatorname = $user->name;
			$colors = array('#3366ff', '#7240A4', '#7A157D', '#157D69', '#ECE649');
			$group->group_color = $colors[rand(0, count($colors) - 1)];
		}

		$acltype = ACYSMS::get('type.acl');

		$editor = ACYSMS::get('helper.editor');
		$editor->name = 'editor_description';
		$editor->content = $group->group_description;
		$editor->setDescription();

		if(!ACYSMS_J16){
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
		$script .= 'if(window.document.getElementById("name").value.length < 2){alert(\''.JText::_('ENTER_TITLE', true).'\'); return false;}';
		$script .= $editor->jsCode();
		if(!ACYSMS_J16){
			$script .= 'submitform( pressbutton );}';
		}else{
			$script .= 'Joomla.submitform(pressbutton,document.adminForm);}; ';
		}
		$script .= 'function affectUser(idcreator,name,email){
			window.document.getElementById("creatorname").innerHTML = name;
			window.document.getElementById("groupcreator").value = idcreator;
		}';


		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);

		$colorBox = ACYSMS::get('type.color');


		if($app->isAdmin()){
			$acyToolbar = ACYSMS::get('helper.toolbar');
			$acyToolbar->setTitle(JText::_('SMS_GROUP'), 'group&task=edit&group_id='.$group_id);

			$acyToolbar->addButtonOption('apply', JText::_('SMS_APPLY'), 'apply', false);
			$acyToolbar->save();
			$acyToolbar->cancel();

			$acyToolbar->divider();

			$acyToolbar->help('groups');
			$acyToolbar->display();
		}
		$this->assignRef('colorBox', $colorBox);


		$this->assignRef('group', $group);
		$this->assignRef('editor', $editor);
		$this->assignRef('acltype', $acltype);
		$this->assignRef('app', $app);
	}

	public function choose(){
		$app = JFactory::getApplication();
		$groupClass = ACYSMS::get('class.group');
		$rows = $app->isAdmin() ? $groupClass->getGroups() : $groupClass->getFrontendGroups();

		$selectedGroups = JRequest::getVar('values', '', '', 'string');

		if(strtolower($selectedGroups) == 'all'){
			foreach($rows as $id => $oneRow){
				$rows[$id]->selected = true;
			}
		}elseif(!empty($selectedGroups)){
			$selectedGroups = explode(',', $selectedGroups);
			foreach($rows as $id => $oneRow){
				if(in_array($oneRow->group_id, $selectedGroups)){
					$rows[$id]->selected = true;
				}
			}
		}

		$fieldName = JRequest::getString('task');
		$controlName = JRequest::getString('control', 'params');
		$nbDisplay = JRequest::getInt('nb_display', -1);

		if($nbDisplay != -1) $this->assignRef('nbDisplay', $nbDisplay);

		$this->assignRef('rows', $rows);
		$this->assignRef('selectedGroups', $selectedGroups);
		$this->assignRef('fieldName', $fieldName);
		$this->assignRef('controlName', $controlName);
	}
}
