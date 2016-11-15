<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

switch ( getFBComponentVersion() ) {
	case '3.1.0':
		// component has been updated, do nothing
	break;
	case '3.0.0':
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '2.5.0':
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.6.4':
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.6.3':
		updateDatabaseStructure_163_to_164 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.6.2':
		updateDatabaseStructure_162_to_163 ();
		updateDatabaseStructure_163_to_164 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.6.1':
		updateDatabaseStructure_161_to_162 ();
		updateDatabaseStructure_162_to_163 ();
		updateDatabaseStructure_163_to_164 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.6.0':
		updateDatabaseStructure_160_to_161 ();
		updateDatabaseStructure_161_to_162 ();
		updateDatabaseStructure_162_to_163 ();
		updateDatabaseStructure_163_to_164 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.5.13':
		updateDatabaseStructure_1510_to_160 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.5.12':
		updateDatabaseStructure_1510_to_160 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.5.11':
		updateDatabaseStructure_1510_to_160 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.5.10':
		updateDatabaseStructure_1510_to_160 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.5.9':
		updateDatabaseStructure_159_to_1510 ();
		updateDatabaseStructure_1510_to_160 ();
		updateDatabaseStructure_160_to_161 ();
		updateDatabaseStructure_161_to_162 ();
		updateDatabaseStructure_162_to_163 ();
		updateDatabaseStructure_163_to_164 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.5.8':
		updateDatabaseStructure_158_to_159 ();
		updateDatabaseStructure_159_to_1510 ();
		updateDatabaseStructure_1510_to_160 ();
		updateDatabaseStructure_160_to_161 ();
		updateDatabaseStructure_161_to_162 ();
		updateDatabaseStructure_162_to_163 ();
		updateDatabaseStructure_163_to_164 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	case '1.5.7':
		updateDatabaseStructure_157_to_158 ();
		updateDatabaseStructure_158_to_159 ();
		updateDatabaseStructure_159_to_1510 ();
		updateDatabaseStructure_1510_to_160 ();
		updateDatabaseStructure_160_to_161 ();
		updateDatabaseStructure_161_to_162 ();
		updateDatabaseStructure_162_to_163 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
	default: // 1.5.6  
		updateDatabaseStructure_156_to_157 ();
		updateDatabaseStructure_157_to_158 ();
		updateDatabaseStructure_158_to_159 ();
		updateDatabaseStructure_159_to_1510 ();
		updateDatabaseStructure_1510_to_160 ();
		updateDatabaseStructure_160_to_161 ();
		updateDatabaseStructure_161_to_162 ();
		updateDatabaseStructure_162_to_163 ();
		updateDatabaseStructure_163_to_164 ();
		updateDatabaseStructure_164_to_250 ();
		updateDatabaseStructure_250_to_300 ();
		updateDatabaseStructure_300_to_310 ();
		showUpdateMessage ( 'FlippingBook was updated to 3.1.0 version' );
	break;
}


function updateDatabaseStructure_300_to_310 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '3.1.0' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}
function updateDatabaseStructure_250_to_300 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '3.0.0' WHERE `name` = 'version'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_books` CHANGE `published` `state` tinyint(1)";
	$query[] = "ALTER TABLE `#__flippingbook_books` MODIFY `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_categories` ADD `publish_up` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_categories` ADD `publish_down` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_categories` CHANGE `published` `state` tinyint(1)";
	$query[] = "ALTER TABLE `#__flippingbook_categories` MODIFY `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_pages` CHANGE `published` `state` tinyint(1)";
	$query[] = "ALTER TABLE `#__flippingbook_pages` MODIFY `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}

function updateDatabaseStructure_164_to_250 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '2.5.0' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}

function updateDatabaseStructure_163_to_164 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `book_size` int(4) NOT NULL DEFAULT '90'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `dynamic_scaling` tinyint(1) NOT NULL DEFAULT '1'";
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '1.6.4' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}


function updateDatabaseStructure_162_to_163 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '1.6.3' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}


function updateDatabaseStructure_161_to_162 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '1.6.2' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}

function updateDatabaseStructure_160_to_161 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '1.6.1' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}

function updateDatabaseStructure_1510_to_160 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "DELETE FROM `#__flippingbook_config` WHERE name = 'downloadComplete'";
	$query[] = "DELETE FROM `#__flippingbook_config` WHERE name = 'printTitle'";
	$query[] = "DELETE FROM `#__flippingbook_config` WHERE name = 'zoomHint'";
	$query[] = "ALTER TABLE `#__flippingbook_categories` ADD `access` int(10) unsigned NOT NULL DEFAULT '0'";
	$query[] = "ALTER TABLE `#__flippingbook_categories` ADD `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_categories` ADD `created_by` int(10) unsigned NOT NULL DEFAULT '0'";
	$query[] = "ALTER TABLE `#__flippingbook_categories` ADD `created_by_alias` varchar(255) NOT NULL DEFAULT ''";
	$query[] = "ALTER TABLE `#__flippingbook_categories` ADD `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_categories` ADD `language` char(7) NOT NULL DEFAULT ''";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `access` int(10) unsigned NOT NULL DEFAULT '0'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `created_by` int(10) unsigned NOT NULL DEFAULT '0'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `created_by_alias` varchar(255) NOT NULL DEFAULT ''";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `language` char(7) NOT NULL DEFAULT ''";
	$query[] = "ALTER TABLE `#__flippingbook_pages` ADD `access` int(10) unsigned NOT NULL DEFAULT '0'";
	$query[] = "ALTER TABLE `#__flippingbook_pages` ADD `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_pages` ADD `created_by` int(10) unsigned NOT NULL DEFAULT '0'";
	$query[] = "ALTER TABLE `#__flippingbook_pages` ADD `created_by_alias` varchar(255) NOT NULL DEFAULT ''";
	$query[] = "ALTER TABLE `#__flippingbook_pages` ADD `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `language` char(7) NOT NULL DEFAULT ''";
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '1.6.0' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}
		
function updateDatabaseStructure_159_to_1510 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '1.5.10' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}

function updateDatabaseStructure_158_to_159 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `direction` VARCHAR( 3 ) NOT NULL DEFAULT 'LTR'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `frame_width` INT( 4 ) NOT NULL DEFAULT '0'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `frame_color` VARCHAR (10) NOT NULL DEFAULT 'FFFFFF'";
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '1.5.9' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}

function updateDatabaseStructure_157_to_158 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `sound_control_button` TINYINT( 1 ) NOT NULL DEFAULT '1'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `transparent_pages` TINYINT( 1 ) NOT NULL DEFAULT '1'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `show_zoom_hint` TINYINT( 1 ) NOT NULL DEFAULT '1'";
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `fullscreen_hint` TEXT NOT NULL DEFAULT ''";
	$query[] = "UPDATE `#__flippingbook_config` SET `value` = '1.5.8' WHERE `name` = 'version'";
	foreach ( $query as $query_string ) {
		$db->setQuery ( $query_string );
		$db->query () or die( $db->stderr () );
	}
}

function updateDatabaseStructure_156_to_157 () {
	$db	= JFactory::getDBO ();
	$query = array ();
	$query[] = "ALTER TABLE `#__flippingbook_books` ADD `zooming_method` INT( 1 ) NOT NULL DEFAULT '0'";
	$query[] = "ALTER TABLE `#__flippingbook_pages` ADD `zoom_height` INT( 4 ) NOT NULL DEFAULT '800'";
	$query[] = "ALTER TABLE `#__flippingbook_pages` ADD `zoom_width` INT( 4 ) NOT NULL DEFAULT '600'";
	$query[] = "INSERT INTO `#__flippingbook_config` ( `name` , `value` ) VALUES ( 'version', '1.5.7' )";
	foreach ( $query as $query_string ) {
		$db->setQuery( $query_string );
		$db->query() or die( $db->stderr () );
	}
}

function getFBComponentVersion () {
	$db	= JFactory::getDBO ();
	$query = "SELECT value FROM #__flippingbook_config WHERE name = 'version'";
	$db->setQuery ($query);
	$rows = $db->loadObjectList ();
	define ("FBComponentVersion", $rows[0]->value);
	return $rows[0]->value;
}

function showUpdateMessage ( $updateMessage ) {
	echo ' <dl id="system-message">';
	echo ' <dt class="message">Message</dt>';
	echo $updateMessage;
	echo ' </dl>';
}
?>