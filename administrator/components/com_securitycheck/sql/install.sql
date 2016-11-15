DROP TABLE IF EXISTS `#__securitycheck`;
CREATE TABLE `#__securitycheck` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`Product` VARCHAR(35) NOT NULL,
`Type` VARCHAR(35),
`Installedversion` VARCHAR(30) DEFAULT '---',
`Vulnerable` VARCHAR(10) NOT NULL DEFAULT 'No',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__securitycheck_db`;
CREATE TABLE `#__securitycheck_db` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`Product` VARCHAR(35) NOT NULL,
`Type` VARCHAR(35),
`Vulnerableversion` VARCHAR(10) DEFAULT '---',
`modvulnversion` VARCHAR(2) DEFAULT '==',
`Joomlaversion` VARCHAR(30) DEFAULT 'Notdefined',
`modvulnjoomla` VARCHAR(20) DEFAULT '==',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
INSERT INTO `#__securitycheck_db` (`product`,`type`,`vulnerableversion`,`modvulnversion`,`Joomlaversion`,`modvulnjoomla`) VALUES 
('Joomla!','core','3.0.0','==','3.0.0','=='),
('com_fss','component','1.9.1.1447','<=','3.0.0','>='),
('com_commedia','component','3.1','<=','3.0.0','>='),
('Joomla!','core','3.0.1','<=','3.0.1','<='),
('com_jnews','component','7.9.1','<','3.0.0','>='),
('com_bch','component','---','==','3.0.0','>='),
('com_aclassif','component','---','==','3.0.0','>='),
('com_rsfiles','component','1.0.0 Rev 11','==','3.0.0','>='),
('Joomla!','core','3.0.2','<=','3.0.0','>='),
('com_jnews','component','8.0.1','<=','3.0.0','>='),
('com_attachments','component','3.1.1','<','3.0.0','>='),
('Joomla!','core','3.1.4','<=','3.0.0','>='),
('com_sectionex','component','2.5.96','<=','3.0.0','>='),
('com_joomsport','component','1.7.1','<','3.0.0','>='),
('Joomla!','core','3.1.5','<=','3.0.0','>='),
('com_flexicontent','component','2.1.3','<=','3.0.0','>='),
('com_mijosearch','component','2.0.1','<=','3.0.0','>='),
('com_acesearch','component','3.0','==','3.0.0','>='),
('com_melody','component','1.6.25','<=','3.0.0','>='),
('com_sexypolling','component','1.0.8','<=','3.0.0','>='),
('com_komento','component','1.7.2','<=','3.0.0','>='),
('com_community','component','2.6','==','3.0.0','>='),
('Joomla!','core','3.2.2','<=','3.0.0','>='),
('com_youtubegallery','component','3.4.0','==','3.0.0','>='),
('com_pbbooking','component','2.4','==','3.0.0','>='),
('com_extplorer','component','2.1.3','==','3.0.0','>='),
('com_freichat','component','3.5','<=','3.0.0','>='),
('com_multicalendar','component','4.0.2','==','3.0.0','>='),
('com_kunena','component','3.0.4','==','3.0.0','>='),
('com_jchat','component','2.2','==','3.0.0','>='),
('com_youtubegallery','component','4.1.7','<=','3.0.0','>='),
('com_kunena','component','3.0.5','==','3.0.0','>='),
('com_spidervideoplayer','component','2.8.3','==','3.0.0','>='),
('com_akeeba','component','3.11.4','<','3.0.0','>='),
('com_spidercalendar','component','3.2.6','<=','3.0.0','>='),
('com_spidercontacts','component','1.3.6','<=','3.0.0','>='),
('com_formmaker','component','3.4.1','<','3,0.0','>='),
('com_facegallery','component','1.0','==','3.0.0','>='),
('com_macgallery','component','1.5','<=','3.0.0','>='),
('Joomla!','core','3.3.4','<','3.0.0','>='),
('Joomla!','core','3.3.4','<=','3.0.0','>='),
('com_creativecontactform','component','2.0.0','<=','3.0.0','>='),
('com_xcloner-backupandrestore','component','3.5.1','==','3.0.0','>='),
('com_eventbooking','component','---','==','3.0.0','>='),
('com_hdflvplayer','component','2.1.0.1','<=','3.0.0','>='),
('com_jclassifiedsmanager','component','2.0.0','<','3.0.0','>='),
('com_simplephotogallery','component','1.0','==','3.0.0','>='),
('com_ecommercewd','component','1.2.5','==','3.0.0','>='),
('com_spiderfaq','component','1.1','==','3.0.0','>='),
('com_rand','component','1.5','==','3.0.0','>='),
('com_gallery_wd','component','1.2.5','==','3.0.0','>='),
('com_contactformmaker','component','1.0.1','==','3.0.0','>='),
('com_osproperty','component','2.8.0','<','3.0.0','>='),
('com_eqfullevent','component','1.0.0','<=','3.0.0','>='),
('Joomla!','core','3.4.1','<=','3.0.0','>='),
('com_kunena','component','4.0.2','==','3.0.0','>='),
('com_j2store','component','3.1.6','==','2.5.0,3.0.0','>=,>='),
('com_kunena','component','4.0.3','<=','2.5.0,3.0.0','>=,>='),
('com_helpdeskpro','component','1.4.0','<','2.5.0,3.0.0','>=,>='),
('mod_jshopping_products_wfl','module','4.10.4','<=','3.0.0','>='),
('Joomla!','core','3.4.3','<=','3.0.0','>='),
('com_komento','component','2.0.5','<','3.0.0','>='),
('Joomla!','core','3.4.4','<=','3.0.0','>='),
('com_rpl','component','8.9.2','==','3.0.0','>='),
('com_jnews','component','8.5.1','<=','2.5.0,3.0.0','>=,>='),
('Joomla!','core','3.4.5','<=','3.0.0','>='),
('Joomla!','core','3.4.6','<=','3.0.0','>='),
('com_pricelist','component','2.3.1','==','3.0.0','>='),
('com_poweradmin','component','2.3.0','<=','3.0.0','>='),
('com_easy_youtube_gallery','component','1.0.2','==','3.0.0','>='),
('com_icagenda','component','3.5.15','<=','2.5.0,3.0.0','>=,>='),
('com_jem','component','2.1.15','<=','2.5.0,3.0.0','>=,>='),
('com_extplorer','component','2.1.9','==','2.5.0,3.0.0','>=,>='),
('com_securitycheck','component','2.8.10','<','2.5.0,3.0.0','>=,>='),
('com_jumi','component','3.0.5','==','2.5.0,3.0.0','>=,>='),
('com_jobgrokapp','component','3.1-1.2.55','==','3.0.0','>='),
('com_joomdoc','component','4.0.3','==','2.5.0,3.0.0','>=,>='),
('com_payplans','component','3.3.6','==','3.0.0','>='),
('com_affiliate','component','1.0.3','==','2.5.0','>='),
('com_maqmahelpdesk','component','4.2.3','==','3.0.0','>='),
('com_affiliatetracker','component','2.0.3','==','2.5.0,3.0.0','>=,>='),
('com_enmasse','component','6.4','<=','3.0.0','>='),
('com_bt_media','component','1.0','==','2.5.0,3.0.0','>=,>='),
('com_publisher','component','3.0.11','==','2.5.0,3.0.0','>=,>='),
('com_services','component','---','==','3.0.0','>='),
('com_branch','component','3.0','==','3.0.0','>='),
('com_zhgooglemap','component','8.1.2.0','==','2.5.0,3.0.0','>=,>='),
('com_guru','component','5.0.1','<=','2.5.0,3.0.0','>=,>='),
('com_weblinks','component','---','==','2.5.0,3.0.0','>=,>='),
('com_gallery','component','1.1.5','==','3.0.0','>='),
('com_catalog','component','1.0.4','==','3.0.0','>='),
('com_slider','component','1.0.9','==','3.0.0','>='),
('Joomla!','core','3.6.0','==','3.0.0','>='),
('com_videoflow','component','1.1.5','<=','2.5.0,3.0.0','>=,>='),
('com_k2','component','2.7.1','<','2.5.0,3.0.0','>=,>='),
('com_registrationpro','component','3.2.12','==','3.0.0','>='),
('com_videogallerylite','component','1.0.9','<=','3.0.0','>='),
('com_eventbooking','component','2.10.1','==','3.0.0','>='),
('com_videogallerylite','component','1.1.1','==','3.0.0','>='),
('com_catalog','component','1.0.7','==','3.0.0','>='),
('com_portfoliogallery','component','1.0.6','==','3.0.0','>='),
('mod_dvfoldercontent','module','1.0.2','==','3.0.0','>='),
('com_googlemaps','component','1.0.9','==','3.0.0','>='),
('com_slider','component','1.1.0','==','3.0.0','>=');

CREATE TABLE IF NOT EXISTS `#__securitycheck_logs` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`ip` VARCHAR(35) NOT NULL,
`time` DATETIME NOT NULL,
`tag_description` VARCHAR(50),
`description` VARCHAR(300) NOT NULL,
`type` VARCHAR(50),
`uri` VARCHAR(100),
`component` VARCHAR(150) DEFAULT '---',
`marked` TINYINT(1) DEFAULT 0,
`original_string` VARCHAR(300),
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__securitycheck_file_permissions`;

DROP TABLE IF EXISTS `#__securitycheck_file_manager`;
CREATE TABLE IF NOT EXISTS `#__securitycheck_file_manager` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`last_check` DATETIME,
`files_scanned` INT(10) DEFAULT 0,
`files_with_incorrect_permissions` INT(10) DEFAULT 0,
`estado` VARCHAR(40) DEFAULT 'IN_PROGRESS',
`estado_clear_data` VARCHAR(40) DEFAULT 'DELETING_ENTRIES',
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
INSERT INTO `#__securitycheck_file_manager` (`estado`,`estado_clear_data`) VALUES 
('ENDED','DELETING_ENTRIES');

DROP TABLE IF EXISTS `#__securitycheck_storage`;
CREATE TABLE IF NOT EXISTS `#__securitycheck_storage` (
`storage_key` varchar(255) NOT NULL,
`storage_value` longtext NOT NULL,
PRIMARY KEY (`storage_key`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `#__securitycheckpro_update_database`;
CREATE TABLE IF NOT EXISTS `#__securitycheckpro_update_database` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`version` VARCHAR(10),
`last_check` DATETIME,
`message` VARCHAR(300),
PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
INSERT INTO `#__securitycheckpro_update_database` (`version`) VALUES ('1.0.59');