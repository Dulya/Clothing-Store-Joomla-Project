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
include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php');


if(defined('JDEBUG') AND JDEBUG) ACYSMS::displayErrors();

$taskGroup = JRequest::getCmd('ctrl', 'dashboard');
$doc = JFactory::getDocument();
$app = JFactory::getApplication();
$config = ACYSMS::config();
$doc->addStyleSheet(ACYSMS_CSS.'component.css?v='.str_replace('.', '', $config->get('version')));
JHTML::_('behavior.tooltip');

if($taskGroup != 'update' && !$config->get('installcomplete')){
	$url = ACYSMS::completeLink('update&task=install', false, true);
	echo "<script>document.location.href='".$url."';</script>\n";
	echo JText::_('SMS_INSTALL_NOT_FINISHED').'<br />';
	echo '<a href="'.$url.'">'.JText::_('SMS_CLICK_REDIRECTION').'</a>';
	return;
}

$currentuser = JFactory::getUser();
if($taskGroup != 'update' && ACYSMS_J16 && !$currentuser->authorise('core.manage', 'com_acysms')){
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}


$action = JRequest::getCmd('task', 'listing');
if(empty($action)){
	$action = JRequest::getCmd('defaulttask', 'listing');
	JRequest::setVar('task', $action);
}

if(($taskGroup == 'cpanel' || ($taskGroup == 'update' && $action == 'listing')) && ACYSMS_J16 && !$currentuser->authorise('core.admin', 'com_acysms')){
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$menuDisplayed = false;
if($taskGroup !== 'toggle' && JRequest::getString('tmpl') !== 'component' && !JRequest::getInt('hidemainmenu') && $config->get('menu_position', 'under') == 'above' && !in_array($taskGroup, array('filter', 'file'))){
	$menuHelper = ACYSMS::get('helper.menu');
	echo '<div id="acysmsallcontent" class="acysmsallcontent">';
	echo $menuHelper->display($taskGroup);

	echo '<div id="acysmsmainarea" class="acysmsmaincontent_'.$taskGroup.'">';
	$menuDisplayed = true;
}

if(!include(ACYSMS_CONTROLLER.$taskGroup.'.php')){
	$app->redirect('index.php?option=com_acysms');
	return;
}
$doc->addScript(ACYSMS_JS.'acysms_compat.js');

$className = ucfirst($taskGroup).'Controller';
$classGroup = new $className();

JRequest::setVar('view', $classGroup->getName());
$classGroup->execute(JRequest::getCmd('task', 'listing'));
$classGroup->redirect();


if($menuDisplayed){
	echo '</div></div>';
}
