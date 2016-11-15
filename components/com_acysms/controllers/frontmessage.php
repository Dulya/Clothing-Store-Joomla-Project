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
if(!$config->get('allowFrontEndManagement') || !ACYSMS::isAllowed($config->get('acl_messages_manage', 'all'))) die('You are not allowed to access this page, please check the front end management option in the AcySMS configuration');


include(ACYSMS_BACK.'controllers'.DS.'message.php');


class FrontmessageController extends MessageController{

	public function form(){
		return $this->edit();
	}

	public function edit(){
		$my = JFactory::getUser();
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$config = ACYSMS::config();

		$msgId = ACYSMS::getCID('message_id');
		$result = '';
		$messageClass = ACYSMS::get('class.message');

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$resultArray = $dispatcher->trigger('acysmsCheckCustomAccessMessage');
		$result = reset($resultArray);
		if(!empty($result)) return parent::edit();

		if(!empty($msgId) && !$messageClass->checkMsgAccess($msgId, $my)) $app->redirect('index.php', 'You are not allowed to see this message !', 'error');

		if(ACYSMS::isAllowed($config->get('acl_messages_manage_all', 'all'))) return parent::edit();

		if(!empty($msgId) && ACYSMS::isAllowed($config->get('acl_messages_manage_own', 'all'))){
			$query = 'SELECT message_id FROM #__acysms_message WHERE message_id = '.intval($msgId).' AND message_userid = '.intval($my->id);
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		if(empty($result)){
			$app->redirect('index.php', 'It appears that you don\'t have access to this page');
		}else return parent::edit();
	}
}
