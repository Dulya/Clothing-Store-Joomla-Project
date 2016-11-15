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
if(!$config->get('allowFrontEndManagement') || !ACYSMS::isAllowed($config->get('acl_receiver_manage', 'all'))) die('You are not allowed to access this page, please check the front end management option in the AcySMS configuration');

$frontHelper = ACYSMS::get('helper.acysmsfront');
include(ACYSMS_BACK.'controllers'.DS.'receiver.php');

class frontReceiverController extends ReceiverController{
	function __construct($config = array()){
		parent::__construct($config);
		$app = JFactory::getApplication();

		$groupid = checkGroupId();
		JRequest::setVar('filter_group', $groupid);
		JRequest::setVar('group_id', $groupid);

		if(!acysmsCheckAccessGroup()){
			ACYSMS::enqueueMessage('You can not have access to this group', 'error');
			$app->redirect('index.php?option=com_acysms');
			return false;
		}
	}

	public function form(){
		return parent::add();
	}
}
