DROP TABLE IF EXISTS `#__securitycheck_db`;
CREATE TABLE `#__securitycheck_db` (
`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`Product` VARCHAR(35) NOT NULL,
`Type` VARCHAR(35),
`Vulnerableversion` VARCHAR(10) DEFAULT '---',
`modvulnversion` VARCHAR(2) DEFAULT '==',
`Joomlaversion` VARCHAR(10) DEFAULT 'Notdefined',
`modvulnjoomla` VARCHAR(2) DEFAULT '==',
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
('Joomla!','core','3.0.2','<=','3.0.2','<=');

DROP TABLE IF EXISTS `#__securitycheck_file_permissions`;

ALTER TABLE `#__securitycheck_storage` CHANGE `key` `storage_key` varchar(255) NOT NULL;
ALTER TABLE `#__securitycheck_storage` CHANGE `value` `storage_value` longtext NOT NULL;

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