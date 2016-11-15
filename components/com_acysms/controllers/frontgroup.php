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
if(!ACYSMS::isAllowed($config->get('acl_groups_manage', 'all'))) die('You are not allowed to access this page, please check the front end management option in the AcySMS configuration');


include(ACYSMS_BACK.'controllers'.DS.'group.php');
$frontHelper = ACYSMS::get('helper.acysmsfront');

class FrontGroupController extends GroupController{


	function __construct($config = array()){
		parent::__construct($config);

		$app = JFactory::getApplication();

		$groupid = JRequest::getInt('group_id');
		if(empty($groupid)) return;

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$resultArray = $dispatcher->trigger('acysmsCheckCustomAccessGroup');
		$result = reset($resultArray);
		if(!empty($result)) return true;


		if(!acysmsCheckAccessGroup()){
			ACYSMS::enqueueMessage('You can not have access to this group', 'error');
			$app->redirect('index.php');
			return false;
		}
	}

	public function form(){
		return $this->edit();
	}
}
