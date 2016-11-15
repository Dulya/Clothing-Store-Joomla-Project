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
('Joomla!','core','3.0.2','<=','3.0.0','>='),
('com_jnews','component','8.0.1','<=','3.0.0','>='),
('com_attachments','component','---','==','3.0.0','>='),
('Joomla!','core','3.1.4','<=','3.0.0','>=');