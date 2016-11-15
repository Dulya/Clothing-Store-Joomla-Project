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
if(!$config->get('allowFrontEndManagement') || !ACYSMS::isAllowed($config->get('acl_receivers_manage', 'all'))) die('You are not allowed to access this page');


include(ACYSMS_BACK.'controllers'.DS.'user.php');
$frontHelper = ACYSMS::get('helper.acysmsfront');

class FrontUserController extends UserController{
	function __construct($config = array()){
		parent::__construct($config);

		$groupid = checkGroupId();
		JRequest::setVar('filter_group', $groupid);
		JRequest::setVar('group_id', $groupid);

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$resultArray = $dispatcher->trigger('acysmsCheckCustomAccessGroup');
		$result = reset($resultArray);
		if(!empty($result)) return true;

		$app = JFactory::getApplication();
		if(in_array(JRequest::getCmd('task'), array('edit', 'add')) && !acysmsCheckEditUser()){
			ACYSMS::enqueueMessage('This user does not belong to your group', 'error');
			$app->redirect('index.php?option=com_acysms');
			return false;
		}
	}

	function conversation(){
		die('Conversation is not available on the front end');
	}
}
