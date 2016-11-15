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
$my = JFactory::getUser();
if(empty($my->id)){
	$usercomp = !ACYSMS_J16 ? 'com_user' : 'com_users';
	$uri = JFactory::getURI();
	$url = 'index.php?option='.$usercomp.'&view=login&return='.base64_encode($uri->toString());
	$app = JFactory::getApplication();
	$app->redirect($url, JText::_('SMS_NOTALLOWED'));
	return false;
}

$config = ACYSMS::config();
if(!ACYSMS::isAllowed($config->get('acl_queue_manage', 'all'))) die('You are not allowed to access this page, please check the front end management option in the AcySMS configuration');


include(ACYSMS_BACK.'controllers'.DS.'queue.php');

class FrontQueueController extends QueueController{

}
