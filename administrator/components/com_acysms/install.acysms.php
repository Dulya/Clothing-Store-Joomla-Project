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


if(version_compare(PHP_VERSION, '5.0.0', '<')){
	echo '<p style="color:red">This version of AcySMS does not support PHP4, it is time to upgrade your server to PHP5!</p>';
	exit;
}

if(!function_exists('com_install')){
	function com_install(){
		return installAcySMS();
	}
}

if(!function_exists('com_uninstall')){
	function com_uninstall(){
		return uninstallAcySMS();
	}
}

class com_acysmsInstallerScript{
	function install($parent){
		installAcySMS();
	}

	function update($parent){
		installAcySMS();
	}

	function preflight($type, $parent){
		return true;
	}

	function postflight($type, $parent){
		return true;
	}
}

function uninstallAcySMS(){
	$uninstallClass = new acysmsUninstall();
	$uninstallClass->unpublishModules();
	$uninstallClass->unpublishSystemPlugins();
	$uninstallClass->message();
}


function installAcySMS(){
	include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php');

	ACYSMS::increasePerf();

	$installClass = new acysmsInstall();
	$installClass->addPref();
	$installClass->updatePref();
	$installClass->updateSQL();
	$installClass->displayInfo();
	$installClass->secureRequests();
}


class acysmsInstall{

	var $level = 'express';
	var $version = '3.1.0';
	var $fromVersion = '';
	var $fromLevel = '';
	var $db;
	var $update = false;

	function __construct(){
		$this->db = JFactory::getDBO();
	}

	function displayInfo(){
		echo '<h1>'.JText::_('SMS_PLEASE_WAIT').'</h1>';
		echo '<h2>'.JText::_('SMS_INSTALL_NOT_FINISHED').'</h2>';
		$url = 'index.php?option=com_acysms&ctrl=update&task=install&fromversion='.$this->fromVersion;
		echo '<a href="'.$url.'">'.JText::_('SMS_CLICK_REDIRECTION').'</a>';
		echo "<script language=\"javascript\" type=\"text/javascript\">document.location.href='$url';</script>\n";
	}

	function addPref(){
		$allPref = array();

		$this->level = ucfirst($this->level);

		$allPref['level'] = $this->level;
		$allPref['version'] = $this->version;

		$allPref['cron_frequency'] = 900;
		$allPref['cron_plugins_next'] = time() + 86400;
		$allPref['cron_savepath'] = 'media/com_acysms/logs/report'.rand(0, 999999999).'.log';
		$allPref['version'] = $this->version;
		$allPref['cron_savepath'] = 'media/com_acysms/logs/report'.rand(0, 999999999).'.log';
		$allPref['menu_position'] = 'above';
		$allPref['allowedfiles'] = 'zip,doc,docx,pdf,xls,txt,gzip,rar,jpg,gif,xlsx,pps,csv,bmp,ico,odg,odp,ods,odt,png,ppt,swf,xcf,mp3,wma';
		$allPref['uploadfolder'] = 'media/com_acysms/upload';
		$allPref['default_integration'] = 'acysms';
		$allPref['acysms_field'] = 'user_phone_number';
		$allPref['queue_nbmsg'] = '200';
		$allPref['parallel_threads'] = '10';


		$app = JFactory::getApplication();
		$currentTemplate = $app->getTemplate();
		if(ACYSMS_J30 || in_array($currentTemplate, array('rt_missioncontrol', 'aplite', 'adminpraise3'))){
			$allPref['menu_position'] = 'above';
		}
		$query = "INSERT IGNORE INTO `#__acysms_config` (`namekey`,`value`) VALUES ";
		foreach($allPref as $namekey => $value){
			$query .= '('.$this->db->Quote($namekey).','.$this->db->Quote($value).'),';
		}
		$query = rtrim($query, ',');
		$this->db->setQuery($query);
		$this->db->query();
	}

	function updatePref(){
		$this->db->setQuery("SELECT `namekey`, `value` FROM `#__acysms_config` WHERE `namekey` IN ('version','level') LIMIT 2");
		try{
			$results = $this->db->loadObjectList('namekey');
		}catch(Exception $e){
			$results = null;
		}

		if($results === null){
			ACYSMS::display(isset($e) ? $e->getMessage() : substr(strip_tags($this->db->getErrorMsg()), 0, 200).'...', 'error');
			return false;
		}

		if($results['version']->value == $this->version AND $results['level']->value == $this->level) return true;

		$this->update = true;
		$this->fromLevel = $results['level']->value;
		$this->fromVersion = $results['version']->value;


		$query = "REPLACE INTO `#__acysms_config` (`namekey`,`value`) VALUES ('level',".$this->db->Quote($this->level)."),('version',".$this->db->Quote($this->version)."),('installcomplete','0')";
		$this->db->setQuery($query);
		$this->db->query();

		return true;
	}

	function updateSQL(){
		if(!$this->update) return true;

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');


		if(version_compare($this->fromVersion, '0.0.3', '<')){
			$this->updatequery("CREATE TABLE IF NOT EXISTS `#__acysms_phone` (
					`phone_id` int unsigned NOT NULL AUTO_INCREMENT,
					`phone_number` varchar(30) NOT NULL,
					PRIMARY KEY (`phone_id`),
					UNIQUE KEY `phone_number` (`phone_number`)
					) ENGINE=MyISAM  DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
			$this->updatequery("DROP TABLE #__acysms_receiver");

			$this->updatequery("ALTER TABLE `#__acysms_statsdetails` CHANGE `statsdatails_receiver_table` `statsdetails_receiver_table` VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL");
		}

		if(version_compare($this->fromVersion, '1.0.0', '<')){
			if(!file_exists(ACYSMS_INTEGRATION.'communityBuilder'.DS.'install.html') && file_exists(ACYSMS_INTEGRATION.'communityBuilder')){
				JFolder::delete(ACYSMS_INTEGRATION.'communityBuilder');
			}

			$this->updatequery("CREATE TABLE IF NOT EXISTS `#__acysms_answer` (
				`answer_id` int unsigned NOT NULL AUTO_INCREMENT,
				`answer_from` varchar(30) DEFAULT NULL,
				`answer_to` varchar(30) DEFAULT NULL,
				`answer_date` int unsigned DEFAULT NULL,
				`answer_body` text,
				`answer_sms_id` varchar(250) DEFAULT NULL,
				`answer_receiver_id` int unsigned DEFAULT NULL,
				`answer_receiver_table` varchar(30) DEFAULT NULL,
				`answer_message_id` mediumint DEFAULT NULL,
				PRIMARY KEY (`answer_id`)
			) ENGINE=MyISAM ;");

			$this->updatequery("ALTER TABLE `#__acysms_senderprofile` ADD `senderprofile_default` TINYINT NOT NULL DEFAULT 0");

			$this->updatequery("ALTER TABLE `#__acysms_message` ADD `message_receiver_table` VARCHAR( 250 ) NOT NULL ");

			$this->updatequery("ALTER TABLE `#__acysms_statsdetails` ADD `statsdetails_sms_id` VARCHAR( 250 ) NOT NULL , ADD `statsdetails_error` VARCHAR( 750 ) NOT NULL, ADD `statsdetails_received_date` INT UNSIGNED NOT NULL");

			$this->updatequery("ALTER TABLE `#__acysms_message` ADD `message_receiver_table` VARCHAR( 250 ) NOT NULL");

			$this->updatequery("CREATE TABLE IF NOT EXISTS `#__acysms_user` (
				`user_id` int unsigned NOT NULL AUTO_INCREMENT,
				`user_joomid` int unsigned NOT NULL,
				`user_firstname` varchar(250) NOT NULL,
				`user_lastname` varchar(250) NOT NULL,
				`user_phone_number` varchar(30) NOT NULL,
				`user_birthdate` varchar(250) NOT NULL,
				`user_email` varchar(250) DEFAULT NULL,
				PRIMARY KEY (`user_id`),
				UNIQUE KEY `user_id` (`user_id`),
				UNIQUE KEY `user_phone_number` (`user_phone_number`)
				) ENGINE=MyISAM ");
		}
		if(version_compare($this->fromVersion, '1.0.1', '<')){
			$this->updatequery("UPDATE `#__acysms_statsdetails` SET `statsdetails_sms_id` = CONCAT(`statsdetails_message_id`,'_',`statsdetails_receiver_id`) WHERE `statsdetails_sms_id` IS NULL OR `statsdetails_sms_id` = ''");

			$this->updatequery("ALTER TABLE `#__acysms_statsdetails`  DROP PRIMARY KEY,   ADD PRIMARY KEY(`statsdetails_sms_id`)");

			$this->updatequery("ALTER TABLE `#__acysms_statsdetails` ADD INDEX `message_index` ( `statsdetails_message_id` , `statsdetails_sentdate` )");

			$this->updatequery("ALTER TABLE `#__acysms_statsdetails` ADD INDEX `receiver_index` ( `statsdetails_receiver_table` , `statsdetails_receiver_id` )");
		}
		if(version_compare($this->fromVersion, '1.0.3', '<')){
			$this->updatequery("CREATE TABLE IF NOT EXISTS `#__acysms_answertrigger` (
				`answertrigger_id` int unsigned NOT NULL AUTO_INCREMENT,
				`answertrigger_name` text NOT NULL,
				`answertrigger_description` text NOT NULL,
				`answertrigger_actions` text NOT NULL,
				`answertrigger_triggers` text NOT NULL,
				`answertrigger_ordering` int unsigned NOT NULL,
				`answertrigger_publish` int NOT NULL,
				PRIMARY KEY (`answertrigger_id`)
			) ENGINE=MyISAM ");
		}
		if(version_compare($this->fromVersion, '1.1.2', '<')){
			$this->updatequery("ALTER TABLE `#__acysms_config` CHANGE `namekey` `namekey` VARCHAR( 50 )  NOT NULL ;");
		}
		if(version_compare($this->fromVersion, '1.1.5', '<')){
			$this->updatequery("CREATE TABLE IF NOT EXISTS `#__acysms_fields` (
					`fields_fieldid` smallint unsigned NOT NULL AUTO_INCREMENT,
					`fields_fieldname` varchar(250) NOT NULL,
					`fields_namekey` varchar(50) NOT NULL,
					`fields_type` varchar(50) DEFAULT NULL,
					`fields_value` text NOT NULL,
					`fields_published` tinyint unsigned NOT NULL DEFAULT '1',
					`fields_ordering` smallint unsigned DEFAULT '99',
					`fields_options` text,
					`fields_core` tinyint unsigned NOT NULL DEFAULT '0',
					`fields_required` tinyint unsigned NOT NULL DEFAULT '0',
					`fields_backend` tinyint unsigned NOT NULL DEFAULT '1',
					`fields_frontcomp` tinyint unsigned NOT NULL DEFAULT '0',
					`fields_default` varchar(250) DEFAULT NULL,
					`fields_listing` tinyint unsigned DEFAULT NULL,
					PRIMARY KEY (`fields_fieldid`),
					UNIQUE KEY `fields_namekey` (`fields_namekey`),
					KEY `fields_orderingindex` (`fields_published`,`fields_ordering`)
				) ENGINE=MyISAM ");
		}
		if(version_compare($this->fromVersion, '1.2.1', '<')){

			$this->updatequery("CREATE TABLE IF NOT EXISTS `#__acysms_group` (
					`group_name` varchar(250) NOT NULL,
					`group_description` text,
					`group_ordering` smallint unsigned NULL DEFAULT '0',
					`group_id` smallint unsigned NOT NULL AUTO_INCREMENT,
					`group_published` tinyint DEFAULT NULL,
					`group_user_id` int unsigned DEFAULT NULL,
					`group_alias` varchar(250) DEFAULT NULL,
					`group_color` varchar(30) DEFAULT NULL,
					`group_visible` tinyint NOT NULL DEFAULT '1',
					`group_access_sub` varchar(250) DEFAULT 'all',
					`group_access_manage` varchar(250) NOT NULL DEFAULT 'none',
					`group_languages` varchar(250) NOT NULL DEFAULT 'all',
					PRIMARY KEY (`group_id`),
					KEY `orderingindex` (`group_ordering`)
				) ENGINE=MyISAM ;");

			$this->updatequery("CREATE TABLE IF NOT EXISTS `#__acysms_groupuser` (
					`groupuser_group_id` smallint unsigned NOT NULL,
					`groupuser_user_id` int unsigned NOT NULL,
					`groupuser_subdate` int unsigned DEFAULT NULL,
					`groupuser_unsubdate` int unsigned DEFAULT NULL,
					`groupuser_status` tinyint NOT NULL,
					PRIMARY KEY (`groupuser_group_id`,`groupuser_user_id`),
					KEY `useridindex` (`groupuser_user_id`),
					KEY `groupidstatusindex` (`groupuser_group_id`,`groupuser_status`)
				) ENGINE=MyISAM ;");

			$this->updatequery("INSERT IGNORE INTO `#__acysms_fields` (`fields_fieldname`, `fields_namekey`, `fields_type`, `fields_value`, `fields_published`, `fields_ordering`, `fields_options`, `fields_core`, `fields_required`, `fields_backend`, `fields_frontcomp`, `fields_default`, `fields_listing`) VALUES
						('SMS_FIRSTNAMECAPTION', 'user_firstname', 'text', '', 1, 1, NULL, 1, 1, 1, 1, NULL, 1),
						('SMS_LASTNAMECAPTION', 'user_lastname', 'text', '', 1, 2, NULL, 1, 1, 1, 1, NULL, 1),
						('SMS_PHONECAPTION', 'user_phone_number', 'phone', '', 1, 3, 'a:4:{s:4:\"cols\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"size\";s:0:\"\";s:6:\"format\";s:0:\"\";}', 1, 1, 1, 1, '', 1),
						('SMS_BIRTHDAYCAPTION', 'user_birthdate', 'birthday', '', 1, 5, 'a:5:{s:12:\"errormessage\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"size\";s:0:\"\";s:6:\"format\";s:0:\"\";}', 1, 0, 1, 0, '', 0),
						('SMS_EMAILCAPTION', 'user_email', 'text', '', 1, 4, 'a:7:{s:12:\"checkcontent\";s:5:\"email\";s:6:\"regexp\";s:0:\"\";s:24:\"errormessagecheckcontent\";s:0:\"\";s:4:\"cols\";s:0:\"\";s:4:\"rows\";s:0:\"\";s:4:\"size\";s:0:\"\";s:6:\"format\";s:0:\"\";}', 1, 0, 1, 1, NULL, 1);");

			$this->updatequery("DELETE FROM #__acysms_phone WHERE `phone_status` = 1");

			$this->updatequery("ALTER TABLE `#__acysms_phone` DROP `phone_status`");
		}
		if(version_compare($this->fromVersion, '1.3.1', '<')){
			$this->updatequery("UPDATE `#__acysms_fields`
						SET `fields_listing` = 1
						WHERE `fields_namekey` IN ('user_firstname', 'user_lastname', 'user_phone_number', 'user_email')
						AND (`fields_listing` IS NULL OR `fields_namekey` = 'user_phone_number')");
		}

		if(version_compare($this->fromVersion, '1.3.2', '<')){
			$this->updatequery("ALTER TABLE `#__acysms_user` ADD `user_activationcode` VARCHAR( 250 ) DEFAULT NULL ;");
		}


		if(version_compare($this->fromVersion, '1.5.0', '<')){
			$this->updatequery("UPDATE `#__acysms_user` SET user_phone_number = REPLACE(user_phone_number, ',0', '')");
			$this->updatequery("UPDATE `#__acysms_user` SET user_phone_number = REPLACE(user_phone_number, ',', '')");
			$this->updatequery("UPDATE `#__acysms_user` SET user_phone_number = REPLACE(user_phone_number, ' ', '')");
		}

		if(version_compare($this->fromVersion, '1.6.0', '<')){
			$this->updatequery("ALTER TABLE `#__acysms_senderprofile` ADD `senderprofile_access` VARCHAR( 250 ) NOT NULL DEFAULT 'all'");
		}

		if(version_compare($this->fromVersion, '1.6.2', '<')){
			$this->updatequery("ALTER TABLE `#__acysms_answertrigger` CHANGE `answertrigger_actions` `answertrigger_actions` TEXT");
		}
		if(version_compare($this->fromVersion, '1.7.5', '<')){
			$this->updatequery("ALTER TABLE `#__acysms_message` ADD `message_attachment` TEXT NULL");
			$this->updatequery("ALTER TABLE `#__acysms_answer` ADD `answer_attachment` TEXT NULL");
			$this->updatequery("ALTER TABLE `#__acysms_message` ADD `message_usecredits` TEXT NULL");
			$this->updatequery("CREATE TABLE IF NOT EXISTS `#__acysms_customer` (
									`customer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
									`customer_joomid` int(11) unsigned NOT NULL,
									`customer_senderprofile_id` int(11) NOT NULL,
									`customer_credits` int(11) unsigned NOT NULL,
									`customer_credits_url` varchar(250) NOT NULL,
									PRIMARY KEY (`customer_id`),
									UNIQUE KEY `customer_joomid` (`customer_joomid`)
								) ENGINE=MyISAM ;");
		}
		if(version_compare($this->fromVersion, '2.1.1', '<')){
			$this->updatequery("ALTER TABLE `#__acysms_message` ADD `message_published` TINYINT NOT null DEFAULT '1'");

			$this->updatequery("ALTER TABLE `#__acysms_customer` CHANGE `customer_senderprofile_id` `customer_senderprofile_id` VARCHAR(250) NOT NULL;");
		}

		if(version_compare($this->fromVersion, '2.5.0', '<')){
			$this->updatequery('ALTER TABLE `#__acysms_user` ADD `user_created` INT(10) UNSIGNED');
			$this->updatequery('UPDATE `#__acysms_user` AS acyusers SET `user_created` = (SELECT UNIX_TIMESTAMP(registerDate) FROM `#__users` AS jusers WHERE acyusers.user_joomid = jusers.id)');
			$this->updatequery('UPDATE `#__acysms_user` SET `user_created` = '.time().' WHERE `user_created` IS NULL OR `user_created` = 0');
			$this->updatequery('UPDATE `#__acysms_config` SET `value` = "above" WHERE `namekey` = "menu_position"');
		}
	}


	function updatequery($query){
		try{
			$this->db->setQuery($query);
			$res = $this->db->query();
		}catch(Exception $e){
			$res = null;
		}

		if($res === null) ACYSMS::display(isset($e) ? print_r($e, true) : substr(strip_tags($this->db->getErrorMsg()), 0, 200).'...', 'error');
	}

	function secureRequests(){
		$config = ACYSMS::config();
		$pass = $config->get('pass');
		if(!empty($pass)) return;
		jimport('joomla.user.helper');
		$pass = JUserHelper::genRandomPassword(30);

		$newConfig = new stdClass();
		$newConfig->pass = $pass;
		$config->save($newConfig);
	}
}

class acysmsUninstall{
	var $db;

	function __construct(){
		$this->db = JFactory::getDBO();
	}

	function message(){
		?>
		You uninstalled the AcySMS component.<br/>
		AcySMS also unpublished the modules attached to the component.<br/><br/>
		If you want to completely uninstall AcySMS, please select all the AcySMS modules and plugins and uninstall them from the Joomla Extensions Manager.<br/>
		Then execute this query via phpMyAdmin to remove all AcySMS data:<br/><br/>
		DROP TABLE <?php
		$this->db->setQuery("SHOW TABLES LIKE '".$this->db->getPrefix()."acysms%' ");
		if(version_compare(JVERSION, '3.0.0', '>=')){
			echo implode(' , ', $this->db->loadColumn());
		}else{
			echo implode(' , ', $this->db->loadResultArray());
		}

		?>;<br/><br/>
		If you DO NOT execute the query, you will be able to install AcySMS again without losing data.<br/>
		Please note that you don't have to uninstall AcySMS to install a new version, simply install the new one without uninstalling your current version.
		<?php
	}

	function unpublishModules(){
		$this->db->setQuery("UPDATE `#__modules` SET `published` = 0 WHERE `module` LIKE '%acysms%'");
		$this->db->query();
	}

	function unpublishSystemPlugins(){
		$this->db->setQuery('UPDATE #__extensions SET published = 0 WHERE type = \'plugin\' AND folder = \'system\' AND name LIKE \'%acysms%\'');
		$this->db->query();
	}
}
