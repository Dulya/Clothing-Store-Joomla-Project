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

class ACYSMSupdateHelper{

	var $db;
	var $errors = array();

	function __construct(){
		$this->db = JFactory::getDBO();
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');
	}

	function fixDoubleExtension(){

		if(!ACYSMS_J16) return;

		$this->db->setQuery("SELECT extension_id FROM #__extensions WHERE type='component' AND element = 'com_acysms' AND extension_id > 0 ORDER BY client_id ASC, extension_id ASC");
		$results = $this->db->loadObjectList();
		if(empty($results) || count($results) == 1) return;

		$validExtension = reset($results)->extension_id;

		$toDelete = array();
		for($i = 1; $i < count($results); $i++){
			$toDelete[] = $results[$i]->extension_id;
		}

		$tablesToUpdate = array('#__menu' => 'component_id');
		foreach($tablesToUpdate as $table => $field){
			$this->db->setQuery("UPDATE ".$table." SET ".$field." = ".intval($validExtension)." WHERE ".$field." IN (".implode(',', $toDelete).")");
			$this->db->query();
		}
		$tablesToCheck = array('#__updates' => 'extension_id', '#__update_sites_extensions' => 'extension_id', '#__extensions' => 'extension_id');
		foreach($tablesToCheck as $table => $field){
			$this->db->setQuery("DELETE FROM ".$table." WHERE ".$field." IN (".implode(',', $toDelete).")");
			$this->db->query();
		}
	}

	function installDefaultSenderProfile(){

		$this->db->setQuery("SELECT senderprofile_id FROM #__acysms_senderprofile LIMIT 1");
		$extensionid = $this->db->loadResult();
		if(!empty($extensionid)) return;

		$user = JFactory::getUser();

		$this->db->setQuery("INSERT INTO #__acysms_senderprofile (senderprofile_id, senderprofile_userid, senderprofile_name, senderprofile_gateway, senderprofile_params, senderprofile_default) VALUES ('1','".$user->id."', 'test', 'test', '',1)");
		$this->db->query();
	}


	public function installDefaultAnswerTrigger(){

		$this->db->setQuery("SELECT answertrigger_id FROM #__acysms_answertrigger LIMIT 1");
		$extensionid = $this->db->loadResult();
		if(!empty($extensionid)) return;

		$answerTrigger = new stdClass();
		$answerTrigger->answertrigger_name = 'Stop';
		$answerTrigger->answertrigger_description = '';
		$answerTrigger->answertrigger_triggers = serialize(array('selected' => 'word', 'word' => 'stop'));
		$answerTrigger->answertrigger_actions = serialize(array('selected' => array('unsubscribe')));
		$answerTrigger->answertrigger_publish = 1;

		$answerTriggerClass = ACYSMS::get('class.answertrigger');
		$answerTriggerClass->save($answerTrigger);
	}

	function installDefaultCustomFields(){

		$this->db->setQuery("SELECT fields_fieldid FROM #__acysms_fields LIMIT 1");
		$extensionid = $this->db->loadResult();
		if(!empty($extensionid)) return;

		$user = JFactory::getUser();

		$this->db->setQuery("INSERT IGNORE INTO `#__acysms_fields` (`fields_fieldname`, `fields_namekey`, `fields_type`, `fields_value`, `fields_published`, `fields_ordering`, `fields_options`, `fields_core`, `fields_required`, `fields_backend`, `fields_frontcomp`, `fields_default`, `fields_listing`) VALUES
						('SMS_FIRSTNAMECAPTION', 'user_firstname', 'text', '', 1, 1, NULL, 1, 1, 1, 1, NULL, 1),
						('SMS_LASTNAMECAPTION', 'user_lastname', 'text', '', 1, 2, NULL, 1, 1, 1, 1, NULL, 1),
						('SMS_PHONECAPTION', 'user_phone_number', 'phone', '', 1, 3, 'a:4:{s:4:\"cols\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"size\";s:0:\"\";s:6:\"format\";s:0:\"\";}', 1, 1, 1, 1, '', 1),
						('SMS_BIRTHDAYCAPTION', 'user_birthdate', 'birthday', '', 1, 5, 'a:5:{s:12:\"errormessage\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"size\";s:0:\"\";s:6:\"format\";s:0:\"\";}', 1, 0, 1, 0, '', 0),
						('SMS_EMAILCAPTION', 'user_email', 'text', '', 1, 4, 'a:7:{s:12:\"checkcontent\";s:5:\"email\";s:6:\"regexp\";s:0:\"\";s:24:\"errormessagecheckcontent\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"size\";s:0:\"\";s:6:\"format\";s:0:\"\";}', 1, 0, 1, 1, NULL, 1);");
		$this->db->query();
	}

	function installDefaultOptinMessage(){

		$this->db->setQuery("SELECT message_id FROM #__acysms_message WHERE message_type = 'activation_optin' LIMIT 1");
		$extensionid = $this->db->loadResult();
		if(!empty($extensionid)) return;

		$user = JFactory::getUser();

		$this->db->setQuery("
					INSERT IGNORE INTO `#__acysms_message`
					(`message_userid`, `message_receiver_table`, `message_subject`, `message_body`, `message_type`, `message_autotype`, `message_senddate`, `message_status`, `message_receiver`, `message_category_id`, `message_senderid`, `message_senderprofile_id`, `message_created`)
					VALUES
					(0, 'acysms', 'activation_optin', '{acysms:user_activationcode}', 'activation_optin', '', NULL, 'notsent', NULL, 0, NULL, 0, ".time().")");
		$this->db->query();
	}

	function fixMenu(){

		if(!ACYSMS_J16) return;

		$this->db->setQuery("SELECT extension_id FROM #__extensions WHERE type='component' AND element LIKE '%acysms' LIMIT 1");
		$extensionid = $this->db->loadResult();
		if(empty($extensionid)) return;

		$this->db->setQuery("UPDATE #__menu SET component_id = ".intval($extensionid).",published = 1 WHERE link LIKE '%com_acysms%' AND component_id = 0 AND client_id = 1");
		$this->db->query();
	}

	function addUpdateSite(){
		$config = ACYSMS::config();

		$newconfig = new stdClass();
		$newconfig->website = ACYSMS_LIVE;
		$config->save($newconfig);

		if(!ACYSMS_J16) return false;

		$this->db->setQuery("DELETE FROM #__updates WHERE element = 'com_acysms'");
		$this->db->query();

		$query = "SELECT update_site_id FROM #__update_sites WHERE location LIKE '%acysms%' AND type LIKE 'extension'";
		$this->db->setQuery($query);
		$update_site_id = $this->db->loadResult();

		$object = new stdClass();
		$object->name = 'AcySMS';
		$object->type = 'extension';
		$object->location = 'http://www.acyba.com/component/updateme/updatexml/component-acysms/version-'.$config->get('version').'/level-'.$config->get('level').'/li-'.urlencode(base64_encode(ACYSMS_LIVE)).'/file-extension.xml';
		$object->enabled = 1;

		if(empty($update_site_id)){
			$this->db->insertObject("#__update_sites", $object);
			$update_site_id = $this->db->insertid();
		}else{
			$object->update_site_id = $update_site_id;
			$this->db->updateObject("#__update_sites", $object, 'update_site_id');
		}

		$query = "SELECT extension_id FROM #__extensions WHERE `name` LIKE 'acysms' AND type LIKE 'component'";
		$this->db->setQuery($query);
		$extension_id = $this->db->loadResult();
		if(empty($update_site_id) OR empty($extension_id)) return false;

		$query = 'INSERT IGNORE INTO #__update_sites_extensions (update_site_id, extension_id) values ('.$update_site_id.','.$extension_id.')';
		$this->db->setQuery($query);
		$this->db->query();
		return true;
	}

	function installMenu($code = ''){
		if(empty($code)){
			$lang = JFactory::getLanguage();
			$code = $lang->getTag();
		}
		$path = JLanguage::getLanguagePath(JPATH_ROOT).DS.$code.DS.$code.'.com_acysms.ini';
		if(!file_exists($path)) return;
		$content = file_get_contents($path);
		if(empty($content)) return;

		$menuFileContent = 'COM_ACYSMS="AcySMS"'."\r\n";
		$menuFileContent .= 'ACYSMS="AcySMS"'."\r\n";
		$menuFileContent .= 'COM_ACYSMS_CONFIGURATION="AcySMS"'."\r\n";
		$menuStrings = array('RECEIVERS', 'GROUPS', 'MESSAGES', 'CATEGORIES', 'QUEUE', 'CONFIGURATION', 'SENDER_PROFILES', 'STATS', 'ANSWERS');
		foreach($menuStrings as $oneString){
			preg_match('#(\n|\r)(SMS_)?'.$oneString.'="(.*)"#i', $content, $matches);
			if(empty($matches[3])) continue;
			if(version_compare(JVERSION, '1.6.0', '<')){
				$menuFileContent .= 'COM_ACYSMS.'.$oneString.'="'.$matches[3].'"'."\r\n";
			}else{
				$menuFileContent .= $oneString.'="'.$matches[3].'"'."\r\n";
			}
		}
		if(version_compare(JVERSION, '1.6.0', '<')){
			$menuPath = ACYSMS_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.com_acysms.menu.ini';
		}else{
			$menuPath = ACYSMS_ROOT.'administrator'.DS.'language'.DS.$code.DS.$code.'.com_acysms.sys.ini';
		}
		if(!JFile::write($menuPath, $menuFileContent)){
			ACYSMS::display(JText::sprintf('SMS_FAIL_SAVE', $menuPath), 'error');
		}
	}

	function installExtensions(){
		$path = ACYSMS_BACK.'extensions';
		$dirs = JFolder::folders($path);

		if(version_compare(JVERSION, '1.6.0', '<')){
			$query = "SELECT CONCAT(`folder`,`element`) FROM #__plugins WHERE `folder` = 'acysms' OR `element` LIKE '%acysms%'";
			$query .= " UNION SELECT `module` FROM #__modules WHERE `module` LIKE '%acysms%'";
			$this->db->setQuery($query);
			$existingExtensions = acysms_loadResultArray($this->db);
		}else{

			$this->db->setQuery("SELECT CONCAT(`folder`,`element`) FROM #__extensions WHERE `folder` = 'acysms' OR `element` LIKE '%acysms%'");
			$existingExtensions = acysms_loadResultArray($this->db);
		}


		$plugins = array();
		$modules = array();
		$extensioninfo = array(); //array('name','ordering','required table or published')
		$extensioninfo['plg_acymailing_acysmsfollowup'] = array('AcySMS : Follow Up SMS', 29, '#__acymailing_subscriber');
		$extensioninfo['plg_acysms_acymailing'] = array('AcySMS : AcyMailing integration', 1, '#__acymailing_subscriber');
		$extensioninfo['plg_acysms_acysms'] = array('AcySMS : General plugin for AcySMS', 2, 1);
		$extensioninfo['plg_acysms_acysmsuser'] = array('AcySMS : AcySMS users informations', 3, 1);
		$extensioninfo['plg_acysms_akeebasubs'] = array('AcySMS : Akeeba Subscriptions', 4, '#__akeebasubs_levels');
		$extensioninfo['plg_acysms_answertrigger'] = array('AcySMS : Automatics Actions based on answers', 5, 1);
		$extensioninfo['plg_acysms_birthdaysms'] = array('AcySMS : Birthday SMS', 6, '#__acymailing_subscriber');
		$extensioninfo['plg_acysms_cb'] = array('AcySMS : Community Builder integration', 7, '#__comprofiler');
		$extensioninfo['plg_acysms_datesms'] = array('AcySMS : Date SMS', 8, 1);
		$extensioninfo['plg_acysms_easyprofile'] = array('AcySMS : EasyProfile User Informations', 30, '#__jsn_users');
		$extensioninfo['plg_acysms_easysocial'] = array('AcySMS : EasySocial User Informations', 29, '#__social_fields');
		$extensioninfo['plg_acysms_eventbooking'] = array('AcySMS : Event Booking Integration', 29, '#__eb_events');
		$extensioninfo['plg_acysms_freestylesupport'] = array('AcySMS : Freestyle Support plugin for AcySMS', 9, '#__fss_data');
		$extensioninfo['plg_acysms_frequencysms'] = array('AcySMS : Frequency SMS plugin for AcySMS', 10, 1);
		$extensioninfo['plg_acysms_hikashop'] = array('AcySMS : HikaShop plugin for AcySMS', 11, '#__hikashop_user');
		$extensioninfo['plg_acysms_jevents'] = array('AcySMS : JEvents integration plugin', 12, '#__jevents_vevent');
		$extensioninfo['plg_acysms_jomsocial'] = array('AcySMS : JomSocial user integration', 12, '#__community_users');
		$extensioninfo['plg_acysms_joomlacontent'] = array('AcySMS : Joomla Content', 13, 1);
		$extensioninfo['plg_acysms_joomlagroups'] = array('AcySMS : Joomla Groups User', 14, 1);
		$extensioninfo['plg_acysms_joomlauser'] = array('AcySMS : Joomla users integration', 15, 1);
		$extensioninfo['plg_acysms_k2content'] = array('AcySMS : K2 Content', 16, '#__k2_items');
		$extensioninfo['plg_acysms_managetext'] = array('AcySMS : Manage text', 17, 0);
		$extensioninfo['plg_acysms_mijoshop'] = array('AcySMS : MijoShop integration plugin', 18, '#__mijoshop_customer');
		$extensioninfo['plg_acysms_redshop'] = array('AcySMS : RedShop integration plugin', 19, '#__redshop_users_info');
		$extensioninfo['plg_acysms_rsevent'] = array('AcySMS : RSEvents!Pro integration plugin', 20, '#__rseventspro_events');
		$extensioninfo['plg_acysms_seblod'] = array('AcySMS : Seblod User Informations', 21, '#__cck_store_item_users');
		$extensioninfo['plg_acysms_virtuemart'] = array('AcySMS : VirtueMart User', 22, '#__virtuemart_userinfos');
		$extensioninfo['plg_hikashop_acysmsorders'] = array('AcySMS : HikaShop order update', 23, '#__hikashop_order');
		$extensioninfo['plg_system_acysmscontent'] = array('AcySMS : Content Manager', 24, 1);
		$extensioninfo['plg_system_acysmsmijoshoporders'] = array('AcySMS : Mijoshop Orders', 25, '#__mijoshop_customer');
		$extensioninfo['plg_system_acysmsusercreation'] = array('AcySMS : User Creation', 26, 1);
		$extensioninfo['plg_system_acysmsverifnumber'] = array('AcySMS : number verification plugin', 27, 1);
		$extensioninfo['plg_system_acysmsvmorders'] = array('AcySMS : VirtueMart order update', 28, '#__virtuemart_orders');
		$extensioninfo['plg_system_acysmsjoomshopping'] = array('AcySMS : JoomShopping order update', 28, '#__jshopping_products');

		$extensioninfo['mod_acysms'] = array('AcySMS : Send SMS Module');
		$extensioninfo['mod_acysms_subscription'] = array('AcySMS : subscription Module');

		$listTables = $this->db->getTableList();

		foreach($dirs as $oneDir){
			$arguments = explode('_', $oneDir);
			if(!isset($extensioninfo[$oneDir])) continue;
			if($arguments[0] == 'plg'){
				$newPlugin = new stdClass();
				$newPlugin->name = $oneDir;
				if(isset($extensioninfo[$oneDir][0])) $newPlugin->name = $extensioninfo[$oneDir][0];
				$newPlugin->type = 'plugin';
				$newPlugin->folder = $arguments[1];
				$newPlugin->element = $arguments[2];
				$newPlugin->enabled = 1;
				if(isset($extensioninfo[$oneDir][2])){
					if(is_numeric($extensioninfo[$oneDir][2])){
						$newPlugin->enabled = $extensioninfo[$oneDir][2];
					}elseif(!in_array(str_replace('#__', $this->db->getPrefix(), $extensioninfo[$oneDir][2]), $listTables)) $newPlugin->enabled = 0;
				}
				$newPlugin->params = '{}';
				$newPlugin->ordering = 0;
				if(isset($extensioninfo[$oneDir][1])) $newPlugin->ordering = $extensioninfo[$oneDir][1];

				if(!ACYSMS::createDir(ACYSMS_ROOT.'plugins'.DS.$newPlugin->folder)) continue;

				if(!ACYSMS_J16){
					$destinationFolder = ACYSMS_ROOT.'plugins'.DS.$newPlugin->folder;
				}else{
					$destinationFolder = ACYSMS_ROOT.'plugins'.DS.$newPlugin->folder.DS.$newPlugin->element;
					if(!ACYSMS::createDir($destinationFolder)) continue;
				}

				if(!$this->copyFolder($path.DS.$oneDir, $destinationFolder)) continue;

				if(in_array($newPlugin->folder.$newPlugin->element, $existingExtensions)) continue;

				$plugins[] = $newPlugin;
			}elseif($arguments[0] == 'mod'){
				$newModule = new stdClass();
				$newModule->name = $oneDir;
				if(isset($extensioninfo[$oneDir][0])) $newModule->name = $extensioninfo[$oneDir][0];
				$newModule->type = 'module';
				$newModule->folder = '';
				$newModule->element = $oneDir;
				$newModule->enabled = 1;
				$newModule->params = '{}';
				$newModule->ordering = 0;
				if(isset($extensioninfo[$oneDir][1])) $newModule->ordering = $extensioninfo[$oneDir][1];

				$destinationFolder = ACYSMS_ROOT.'modules'.DS.$oneDir;

				if(!ACYSMS::createDir($destinationFolder)) continue;

				if(!$this->copyFolder($path.DS.$oneDir, $destinationFolder)) continue;

				if(in_array($newModule->element, $existingExtensions)) continue;
				$modules[] = $newModule;
			}else{
				ACYSMS::display('Could not handle : '.$oneDir, 'error');
			}
		}

		if(!empty($this->errors)) ACYSMS::display($this->errors, 'error');

		if(!ACYSMS_J16){
			$extensions = $plugins;
		}else{
			$extensions = array_merge($plugins, $modules);
		}

		$success = array();
		if(!empty($extensions)){
			if(!ACYSMS_J16){
				$queryExtensions = 'INSERT INTO `#__plugins` (`name`,`element`,`folder`,`published`,`ordering`) VALUES ';
			}else{
				$queryExtensions = 'INSERT INTO `#__extensions` (`name`,`element`,`folder`,`enabled`,`ordering`,`type`,`access`) VALUES ';
			}

			foreach($extensions as $oneExt){
				$queryExtensions .= '('.$this->db->Quote($oneExt->name).','.$this->db->Quote($oneExt->element).','.$this->db->Quote($oneExt->folder).','.$oneExt->enabled.','.$oneExt->ordering;
				if(ACYSMS_J16) $queryExtensions .= ','.$this->db->Quote($oneExt->type).',1';
				$queryExtensions .= '),';
				if($oneExt->type != 'module') $success[] = JText::sprintf('PLUG_INSTALLED', $oneExt->name);
			}
			$queryExtensions = trim($queryExtensions, ',');

			$this->db->setQuery($queryExtensions);
			$this->db->query();
		}

		if(!empty($modules)){
			foreach($modules as $oneModule){
				if(!ACYSMS_J16){
					$queryModule = 'INSERT INTO `#__modules` (`title`,`position`,`published`,`module`) VALUES ';
					$queryModule .= '('.$this->db->Quote($oneModule->name).",'left',0,".$this->db->Quote($oneModule->element).")";
				}else{
					$queryModule = 'INSERT INTO `#__modules` (`title`,`position`,`published`,`module`,`access`,`language`) VALUES ';
					$queryModule .= '('.$this->db->Quote($oneModule->name).",'position-7',0,".$this->db->Quote($oneModule->element).",1,'*')";
				}
				$this->db->setQuery($queryModule);
				$this->db->query();
				$moduleId = $this->db->insertid();

				$this->db->setQuery('INSERT IGNORE INTO `#__modules_menu` (`moduleid`,`menuid`) VALUES ('.$moduleId.',0)');
				$this->db->query();

				$success[] = JText::sprintf('SMS_MODULE_INSTALLED', $oneModule->name);
			}
		}

		if(ACYSMS_J16){
			$this->db->setQuery("UPDATE `#__extensions` SET `access` = 1 WHERE ( `folder` = 'acysms' OR `element` LIKE '%acysms%' ) AND `type` = 'plugin'");
			$this->db->query();
		}

		if(!empty($success)) ACYSMS::display($success, 'success');
	}

	function copyFolder($from, $to){
		$return = true;

		$allFiles = JFolder::files($from);
		foreach($allFiles as $oneFile){
			if(file_exists($to.DS.'index.html') AND $oneFile == 'index.html') continue;
			if(JFile::copy($from.DS.$oneFile, $to.DS.$oneFile) !== true){
				$this->errors[] = 'Could not copy the file from '.$from.DS.$oneFile.' to '.$to.DS.$oneFile;
				$return = false;
			}
			if(ACYSMS_J30 && substr($oneFile, -4) == '.xml'){
				$data = file_get_contents($to.DS.$oneFile);
				if(strpos($data, '<install ') !== false){
					$data = str_replace(array('<install ', '</install>', 'version="1.5"', '<!DOCTYPE install SYSTEM "http://dev.joomla.org/xml/1.5/plugin-install.dtd">'), array('<extension ', '</extension>', 'version="2.5"', ''), $data);
					JFile::write($to.DS.$oneFile, $data);
				}
			}
		}
		$allFolders = JFolder::folders($from);
		if(!empty($allFolders)){
			foreach($allFolders as $oneFolder){
				if(!ACYSMS::createDir($to.DS.$oneFolder)) continue;
				if(!$this->copyFolder($from.DS.$oneFolder, $to.DS.$oneFolder)) $return = false;
			}
		}
		return $return;
	}
}
