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
jimport( 'joomla.html.parameter' );

class acysmsView extends JView{

}

class acysmsControllerCompat extends JController{

}

function acysms_loadResultArray(&$db){
	return $db->loadResultArray();
}

function acysms_loadMootools(){
	JHTML::_('behavior.mootools');
}

function acysms_getColumns($table){
	$db = JFactory::getDBO();
	$allfields = $db->getTableFields($table);
	return reset($allfields);
}

function acysms_getEscaped($value, $extra = false) {
	$db = JFactory::getDBO();
	return $db->getEscaped($value, $extra);
}

function acysms_getFormToken() {
	return JUtility::getToken();
}

if(!class_exists('acysmsParameter')){
	class acysmsParameter extends JParameter{}
}
