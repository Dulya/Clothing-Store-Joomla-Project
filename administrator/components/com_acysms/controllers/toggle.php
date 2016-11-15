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

class ToggleController extends acysmsController{
	var $allowedTablesColumn = array();
	var $deleteColumns = array();

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('toggle');
		$this->allowedTablesColumn['phone'] = array('phone_number' => 'phone_status');
		$this->allowedTablesColumn['plugins'] = array('published' => 'id');
		$this->allowedTablesColumn['fields'] = array('fields_published' => 'fields_fieldid', 'fields_required' => 'fields_fieldid', 'fields_frontcomp' => 'fields_fieldid', 'fields_backend' => 'fields_fieldid', 'fields_listing' => 'fields_fieldid');
		$this->allowedTablesColumn['answertrigger'] = array('answertrigger_publish' => 'answertrigger_id');
		$this->allowedTablesColumn['senderprofile'] = array('senderprofile_id' => 'senderprofile_default');
		$this->allowedTablesColumn['group'] = array('group_published' => 'group_id');
		$this->allowedTablesColumn['queueAutoMsg'] = array('add' => 'message_id', 'update' => 'message_id');
		$this->allowedTablesColumn['groupsub'] = array('status' => 'groupid,subid');
		$this->deleteColumns['queue'] = array('queue_receiver_id ', 'queue_message_id');

		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', false);
		header('Pragma: no-cache');
	}

	function toggle(){
		$completeTask = JRequest::getCmd('task');
		$task = substr($completeTask, 0, strpos($completeTask, '-'));
		$elementId = substr($completeTask, strpos($completeTask, '-') + 1);


		$value = JRequest::getVar('value', '0', '', 'int');
		$table = JRequest::getVar('table', '', '', 'word');

		if(!isset($this->allowedTablesColumn[$table][$task])) exit;

		$pkey = $this->allowedTablesColumn[$table][$task];
		if(empty($pkey)) exit;
		$function = $table.$task;

		if(method_exists($this, $function)){
			$this->$function($elementId, $value);
		}else{
			$db = JFactory::getDBO();
			$db->setQuery('UPDATE '.ACYSMS::table($table).' SET '.$task.' = '.intval($value).' WHERE '.$pkey.' = '.intval($elementId).' LIMIT 1');
			$db->query();
		}
		$toggleClass = ACYSMS::get('helper.toggle');
		$extra = JRequest::getVar('extra', array(), '', 'array');
		if(!empty($extra)){
			foreach($extra as $key => $val){
				$extra[$key] = urldecode($val);
			}
		}
		echo $toggleClass->toggle(JRequest::getCmd('task', ''), $value, $table, $extra);
		exit;
	}

	function phonephone_number($elementId, $value){
		JSession::checkToken('get') or die('Invalid Token');
		$receiverClass = ACYSMS::get('class.phone');
		$receiverClass->manageStatus($elementId, $value);
	}

	function senderprofilesenderprofile_id($elementId, $value){
		$db = JFactory::getDBO();

		$db->setQuery('UPDATE '.ACYSMS::table('senderprofile').' SET `senderprofile_default` = 1 WHERE `senderprofile_id` = '.intval($elementId).' LIMIT 1');
		$db->query();

		$db->setQuery('UPDATE '.ACYSMS::table('senderprofile').' SET `senderprofile_default` = 0 WHERE `senderprofile_id` != '.intval($elementId));
		$db->query();
	}

	function delete(){
		list($value1, $value2) = explode('_', JRequest::getCmd('value'));
		$table = JRequest::getVar('table', '', '', 'word');
		if(empty($table)) exit;
		list($key1, $key2) = $this->deleteColumns[$table];
		if(empty($key1) OR empty($key2) OR empty($value1) OR empty($value2)) exit;
		$db = JFactory::getDBO();
		$db->setQuery('DELETE FROM '.ACYSMS::table($table).' WHERE '.$key1.' = '.intval($value1).' AND '.$key2.' = '.intval($value2));
		$db->query();
		exit;
	}

	function pluginspublished($id, $publish){
		$db = JFactory::getDBO();
		if(!ACYSMS_J16){
			$db->setQuery('UPDATE '.ACYSMS::table('plugins', false).' SET `published` = '.intval($publish).' WHERE `id` = '.intval($id).' AND (`folder` = \'acysms\' OR `name` LIKE \'%acysms%\' OR `element` LIKE \'%acysms%\') LIMIT 1');
		}else{
			$db->setQuery('UPDATE `#__extensions` SET `enabled` = '.intval($publish).' WHERE `extension_id` = '.intval($id).' AND (`folder` = \'acysms\' OR `name` LIKE \'%acysms%\' OR `element` LIKE \'%acysms%\') LIMIT 1');
		}
		$db->query();
	}

	function plgtrigger(){
		$pluginToTrigger = JRequest::getCmd('plg');
		$functionToTrigger = 'ajax_'.JRequest::getCmd('function', 'onTestPlugin');
		$pluginType = JRequest::getCmd('plgtype', 'acysms');
		if(version_compare(JVERSION, '1.6.0', '<')){
			$path = JPATH_PLUGINS.DS.$pluginType.DS.$pluginToTrigger.'.php';
		}else{
			$path = JPATH_PLUGINS.DS.$pluginType.DS.$pluginToTrigger.DS.$pluginToTrigger.'.php';
		}
		if(!file_exists($path)){
			ACYSMS::display('Plugin not found: '.$path, 'error');
			exit;
		}
		require_once($path);
		$className = 'plg'.$pluginType.$pluginToTrigger;
		if(!class_exists($className)){
			ACYSMS::display('Class not found: '.$className, 'error');
			exit;
		}
		$dispatcher = JDispatcher::getInstance();
		$instance = new $className($dispatcher, array('name' => $pluginToTrigger, 'type' => $pluginType));
		if(!method_exists($instance, $functionToTrigger)){
			ACYSMS::display('Method "'.$functionToTrigger.'" not found: '.$className, 'error');
			exit;
		}
		$instance->$functionToTrigger();
		exit;
	}

	function groupsubstatus($ids, $status){

		list($groupid, $subid) = explode('_', $ids);
		$groupid = (int)$groupid;
		$subid = (int)$subid;

		if(empty($subid) OR empty($groupid)) exit;
		$groupUserClass = ACYSMS::get('class.groupuser');
		$groups = array();
		$groups[$status] = array($groupid);
		if($groupUserClass->updateSubscription($subid, $groups)) return;

		echo 'error while updating the subscription';
	}
}
