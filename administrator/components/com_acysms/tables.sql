CREATE TABLE IF NOT EXISTS `#__acysms_answer` (
	`answer_id` int unsigned NOT NULL AUTO_INCREMENT,
	`answer_from` varchar(30) DEFAULT NULL,
	`answer_to` varchar(30) DEFAULT NULL,
	`answer_date` int unsigned DEFAULT NULL,
	`answer_body` text,
	`answer_sms_id` varchar(250) DEFAULT NULL,
	`answer_receiver_id` int unsigned DEFAULT NULL,
	`answer_receiver_table` varchar(30) DEFAULT NULL,
	`answer_message_id` mediumint DEFAULT NULL,
	`answer_attachment` text NULL,
	PRIMARY KEY (`answer_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_answertrigger` (
	`answertrigger_id` int unsigned NOT NULL AUTO_INCREMENT,
	`answertrigger_name` text NOT NULL,
	`answertrigger_description` text NOT NULL,
	`answertrigger_actions` text NOT NULL,
	`answertrigger_triggers` text NOT NULL,
	`answertrigger_ordering` int unsigned NOT NULL,
	`answertrigger_publish` int NOT NULL,
	PRIMARY KEY (`answertrigger_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;


CREATE TABLE IF NOT EXISTS `#__acysms_category` (
	`category_id` mediumint unsigned NOT NULL AUTO_INCREMENT,
	`category_name` varchar(250) NOT NULL,
	`category_ordering` mediumint unsigned DEFAULT NULL,
	PRIMARY KEY (`category_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_config` (
	`namekey` varchar(50) NOT NULL,
	`value` text,
	PRIMARY KEY (`namekey`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_message` (
	`message_id` mediumint unsigned NOT NULL AUTO_INCREMENT,
	`message_userid` int unsigned NOT NULL,
	`message_receiver_table` varchar(30) NOT NULL,
	`message_subject` varchar(250) NOT NULL,
	`message_body` text NOT NULL,
	`message_type` varchar(50) NOT NULL DEFAULT 'draft',
	`message_autotype` varchar(50) DEFAULT NULL,
	`message_senddate` int unsigned DEFAULT NULL,
	`message_status` varchar(50) NOT NULL DEFAULT 'notsent',
	`message_receiver` text,
	`message_category_id` mediumint unsigned DEFAULT NULL,
	`message_senderid` int unsigned DEFAULT NULL,
	`message_senderprofile_id` mediumint unsigned NOT NULL,
	`message_created` int unsigned NOT NULL,
	`message_attachment` text NULL,
	`message_usecredits` tinyint(1) NOT NULL,
	`message_published` tinyint(4) NOT NULL DEFAULT '1',
	PRIMARY KEY (`message_id`),
	KEY `category` (`message_category_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_user` (
	`user_id` int unsigned NOT NULL AUTO_INCREMENT,
	`user_joomid` int unsigned NOT NULL,
	`user_firstname` varchar(250) NOT NULL,
	`user_lastname` varchar(250) NOT NULL,
	`user_phone_number` varchar(30) NOT NULL,
	`user_birthdate` date NOT NULL,
	`user_email` varchar(250) DEFAULT NULL,
	`user_activationcode` varchar(250) DEFAULT NULL,
	`user_created` INT(10) UNSIGNED DEFAULT NULL,
	PRIMARY KEY (`user_id`),
	UNIQUE KEY `user_id` (`user_id`),
	UNIQUE KEY `user_phone_number` (`user_phone_number`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_queue` (
	`queue_message_id` mediumint unsigned NOT NULL,
	`queue_receiver_id` int unsigned NOT NULL,
	`queue_receiver_table` varchar(30) NOT NULL,
	`queue_senddate` int unsigned NOT NULL,
	`queue_try` tinyint unsigned NOT NULL,
	`queue_priority` tinyint unsigned DEFAULT NULL,
	`queue_paramqueue` varchar(250) DEFAULT NULL,
	PRIMARY KEY (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_phone` (
	`phone_id` int unsigned NOT NULL AUTO_INCREMENT,
	`phone_number` varchar(30) NOT NULL,
	PRIMARY KEY (`phone_id`),
	UNIQUE KEY `phone_number` (`phone_number`)
) ENGINE=MyISAM  /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_senderprofile` (
	`senderprofile_id` mediumint unsigned NOT NULL AUTO_INCREMENT,
	`senderprofile_userid` int unsigned NOT NULL,
	`senderprofile_name` varchar(250) NOT NULL,
	`senderprofile_gateway` varchar(250) DEFAULT NULL,
	`senderprofile_params` text,
	`senderprofile_default` tinyint NOT NULL DEFAULT 0,
	`senderprofile_access` varchar(250) NOT NULL DEFAULT 'all',
	PRIMARY KEY (`senderprofile_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_stats` (
	`stats_message_id` mediumint unsigned NOT NULL,
	`stats_nbsent` int unsigned NOT NULL,
	`stats_nbfailed` int unsigned NOT NULL,
	PRIMARY KEY (`stats_message_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_statsdetails` (
	`statsdetails_sms_id` varchar(250) NOT NULL,
	`statsdetails_message_id` mediumint unsigned NOT NULL,
	`statsdetails_sentdate` int unsigned NOT NULL,
	`statsdetails_status` tinyint NOT NULL,
	`statsdetails_receiver_id` int unsigned NOT NULL,
	`statsdetails_receiver_table` varchar(50) NOT NULL,
	`statsdetails_details` varchar(250) DEFAULT NULL,
	`statsdetails_error` text NOT NULL,
	`statsdetails_received_date` int unsigned NOT NULL,
	PRIMARY KEY (`statsdetails_sms_id`),
	KEY `message_index` (`statsdetails_message_id`,`statsdetails_sentdate`),
	KEY `receiver_index` (`statsdetails_receiver_table`,`statsdetails_receiver_id`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_fields` (
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
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_group` (
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
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_groupuser` (
	`groupuser_group_id` smallint unsigned NOT NULL,
	`groupuser_user_id` int unsigned NOT NULL,
	`groupuser_subdate` int unsigned DEFAULT NULL,
	`groupuser_unsubdate` int unsigned DEFAULT NULL,
	`groupuser_status` tinyint NOT NULL,
	PRIMARY KEY (`groupuser_group_id`,`groupuser_user_id`),
	KEY `useridindex` (`groupuser_user_id`),
	KEY `groupidstatusindex` (`groupuser_group_id`,`groupuser_status`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;

CREATE TABLE IF NOT EXISTS `#__acysms_customer` (
	`customer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	`customer_joomid` int(11) unsigned NOT NULL,
	`customer_senderprofile_id` varchar(250) NOT NULL,
	`customer_credits` int(11) unsigned NOT NULL,
	`customer_credits_url` varchar(250) NOT NULL,
	PRIMARY KEY (`customer_id`),
	UNIQUE KEY `customer_joomid` (`customer_joomid`)
) ENGINE=MyISAM /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci*/;