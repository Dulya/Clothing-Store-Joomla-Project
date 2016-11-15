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
jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');

include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php');

if(defined('JDEBUG') AND JDEBUG) ACYSMS::displayErrors();

$view = JRequest::getCmd('view');
if(!empty($view) AND !JRequest::getCmd('ctrl')){
	JRequest::setVar('ctrl', $view);
	$layout = JRequest::getCmd('layout');
	if(!empty($layout)){
		JRequest::setVar('task', $layout);
	}
}


$doc = JFactory::getDocument();
$doc->addStyleSheet(ACYSMS_CSS.'frontendedition.css');
$doc->addStyleSheet(ACYSMS_CSS.'component.css');

global $Itemid;
if(empty($Itemid)){
	$urlItemid = JRequest::getInt('Itemid');
	if(!empty($urlItemid)) $Itemid = $urlItemid;
}

$doc = JFactory::getDocument();
$doc->addScript(ACYSMS_JS.'acysms_compat.js');


$taskGroup = JRequest::getCmd('ctrl');
if(empty($taskGroup)){
	$app = JFactory::getApplication();
	$app->redirect('index.php');
}

if($taskGroup == 'd') $taskGroup = 'deliveryreport';
if(!file_exists(ACYSMS_CONTROLLER_FRONT.$taskGroup.'.php') || !include(ACYSMS_CONTROLLER_FRONT.$taskGroup.'.php')){
	return JError::raiseError(404, 'Page not found : '.$taskGroup);
}
$className = ucfirst($taskGroup).'Controller';
$classGroup = new $className();
JRequest::setVar('view', $classGroup->getName());
$classGroup->execute(JRequest::getCmd('task'));
$classGroup->redirect();
