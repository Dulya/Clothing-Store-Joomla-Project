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

class CpanelController extends ACYSMSController{

	var $aclCat = 'configuration';

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('display');
	}

	function save(){
		$this->store();
		return $this->cancel();
	}

	function apply(){
		$this->store();
		return $this->display();
	}

	function listing(){
		return $this->display();
	}

	function store(){
		JRequest::checkToken() or die('Invalid Token');
		if(!$this->isAllowed('cpanel', 'manage')) return;

		jimport('joomla.filesystem.folder');

		$source = is_array($_POST['config']) ? 'POST' : 'REQUEST';
		$formData = JRequest::getVar('config', array(), $source, 'array');

		$aclcats = JRequest::getVar('aclcat', array(), 'POST', 'array');

		if(!empty($aclcats)){
			if(JRequest::getString('acl_configuration', 'all') != 'all' && !ACYSMS::isAllowed($formData['acl_configuration_manage'])){
				ACYSMS::enqueueMessage(JText::_('ACL_WRONG_CONFIG'), 'notice');
				unset($formData['acl_configuration_manage']);
			}
			$deleteAclCats = array();
			$unsetVars = array('save', 'create', 'manage', 'delete', 'export', 'copy', 'create_edit', 'send', 'send_test', 'process', 'import', 'manage_details', 'view', 'manage_all', 'manage_own');
			foreach($aclcats as $oneCat){
				if(JRequest::getString('acl_'.$oneCat) == 'all'){
					foreach($unsetVars as $oneVar){
						unset($formData['acl_'.$oneCat.'_'.$oneVar]);
					}
					$deleteAclCats[] = $oneCat;
				}
			}
		}
		if(!empty($deleteAclCats)){
			$db = JFactory::getDBO();
			$db->setQuery("DELETE FROM `#__acysms_config` WHERE `namekey` LIKE 'acl_".implode("%' OR `namekey` LIKE 'acl_", $deleteAclCats)."%'");
			$db->query();
		}

		if(is_array($formData['frontEndFilters'])) $formData['frontEndFilters'] = serialize($formData['frontEndFilters']);

		$country = $formData['country'];
		if(isset($country)){
			$phoneHelper = ACYSMS::get('helper.phone');
			$phoneHelper->configPage = true;
			$phoneHelper->getValidNum($country);
		}

		$useShortUrl = $formData['use_short_url'];
		$apiKeyShortUrl = $formData['api_key_short_url'];

		if(empty($apiKeyShortUrl) && !empty($useShortUrl)){
			$formData['use_short_url'] = 0;
			$formData['api_key_short_url'] = '';
			ACYSMS::enqueueMessage(JText::_('SMS_API_KEY_XOR_OPTION_SHORT_URL'), 'warning');
		}


		$defaultIntegrationFieldName = empty($formData['default_integration']) ? 'acysms' : $formData['default_integration'].'_field';

		if(empty($formData[$defaultIntegrationFieldName])){
			$dirs = array();
			$this->integration = array();
			$dirs = JFolder::folders(ACYSMS_INTEGRATION);
			foreach($dirs as $oneDir){
				if($oneDir == 'default'){
					continue;
				}
				$oneIntegration = ACYSMS::getIntegration($oneDir);
				if(!empty($formData[$oneIntegration->componentName.'_field'])){
					$formData['default_integration'] = $oneIntegration->componentName;
					break;
				}
			}
		}
		if(empty($formData['default_integration'])) $formData['default_integration'] = 'acysms';

		$config = ACYSMS::config();
		$status = $config->save($formData);
		if($status){
			ACYSMS::enqueueMessage(JText::_('SMS_SUCC_SAVED'), 'message');
		}else{
			ACYSMS::enqueueMessage(JText::_('ERROR_SAVING'), 'error');
		}
		$config->load();
	}

	function plgtrigger(){
		$pluginToTrigger = JRequest::getCmd('plg');
		$pluginType = JRequest::getCmd('plgtype', 'acysms');
		$fctName = 'onAcySMS'.JRequest::getCmd('fctName', 'TestPlugin');
		if(version_compare(JVERSION, '1.6.0', '<')){
			$path = JPATH_PLUGINS.DS.$pluginType.DS.$pluginToTrigger.'.php';
		}else{
			$path = JPATH_PLUGINS.DS.$pluginType.DS.$pluginToTrigger.DS.$pluginToTrigger.'.php';
		}
		if(!file_exists($path)){
			ACYSMS::display('Plugin not found: '.$path, 'error');
			return;
		}
		require_once($path);
		$className = 'plg'.$pluginType.$pluginToTrigger;
		if(!class_exists($className)){
			ACYSMS::display('Class not found: '.$className, 'error');
			return;
		}
		$dispatcher = JDispatcher::getInstance();
		$instance = new $className($dispatcher, array('name' => $pluginToTrigger, 'type' => $pluginType));
		if(!method_exists($instance, $fctName)){
			ACYSMS::display('Method '.$fctName.' not found: '.$className, 'error');
			return;
		}
		$instance->$fctName();
		return;
	}

	function seereport(){
		if(!$this->isAllowed('configuration', 'manage')) return;
		$config = ACYSMS::config();

		$path = trim(html_entity_decode($config->get('cron_savepath')));

		if(!preg_match('#^[a-z0-9/_\-]*\.log$#i', $path)){
			ACYSMS::display('The log file must only contain alphanumeric characters and end with .log', 'error');
			return;
		}

		$reportPath = JPath::clean(ACYSMS_ROOT.$path);

		$logFile = @file_get_contents($reportPath);
		if(empty($logFile)){
			ACYSMS::display(JText::_('SMS_EMPTY_LOG'), 'info');
		}else{
			echo nl2br($logFile);
		}
	}

	function cleanreport(){
		if(!$this->isAllowed('configuration', 'manage')) return;
		jimport('joomla.filesystem.file');
		$config = ACYSMS::config();

		$path = trim(html_entity_decode($config->get('cron_savepath')));
		if(!preg_match('#^[a-z0-9/_\-]*\.log$#i', $path)){
			ACYSMS::display('The log file must only contain alphanumeric characters and end with .log', 'error');
			return;
		}

		$reportPath = JPath::clean(ACYSMS_ROOT.$path);

		if(is_file($reportPath)){
			$result = JFile::delete($reportPath);
			if($result){
				ACYSMS::display(JText::_('SMS_SUCC_DELETE_LOG'), 'success');
			}else{
				ACYSMS::display(JText::_('SMS_ERROR_DELETE_LOG'), 'error');
			}
		}else{
			ACYSMS::display(JText::_('SMS_EXIST_LOG'), 'info');
		}
	}

	function cancel(){
		$this->setRedirect(ACYSMS::completeLink('dashboard', false, true));
	}

	function displayAuthorizedTypeDetails(){
		if(!$this->isAllowed('groups', 'manage')) return;
		$type = JRequest::getCmd('type');
		if(empty($type)) exit;

		$conditionNumber = JRequest::getCmd('conditionNumber');
		if(empty($conditionNumber)) exit;
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$result = '';
		$dispatcher->trigger('onACYSMSdisplayAuthorizedType_'.$type, array(&$result, $conditionNumber));
		echo $result;
		exit;
	}


	public function displayAuthorizedFilters(){
		if(!$this->isAllowed('receivers', 'manage')) return;
		$type = JRequest::getCmd('type');
		if(empty($type)) exit;

		$conditionNumber = JRequest::getCmd('conditionNumber');
		if(empty($conditionNumber)) exit;
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$authorizedFilters = array();
		$authorizedFiltersData = array();

		$dispatcher->trigger('onACYSMSdisplayAuthorizedFilters', array(&$authorizedFilters, $type));

		foreach($authorizedFilters as $type => $object){
			$authorizedFiltersData[] = JHTML::_('select.option', $type, $object->name);
		}
		echo JHTML::_('select.genericlist', $authorizedFiltersData, 'config[frontEndFilters]['.$conditionNumber.'][filters]', 'onchange="showAuthorizedFiltersDetails(\''.$conditionNumber.'\')"', 'value', 'text', '', $conditionNumber.'_acysmsAuthorizedFilter');
		exit;
	}

	public function displayAuthorizedFiltersDetails(){
		$filter = JRequest::getCmd('filter');
		if(empty($filter)) exit;
		$conditionNumber = JRequest::getCmd('conditionNumber');
		if(empty($conditionNumber)) exit;
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$result = '';
		$dispatcher->trigger('onACYSMSdisplayAuthorizedFilters_'.$filter, array(&$result, $conditionNumber));
		echo $result;
		exit;
	}
}
