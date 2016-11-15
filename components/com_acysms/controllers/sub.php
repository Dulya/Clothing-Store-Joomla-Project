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

class SubController extends acySMSController{

	function notask(){
		$redirectUrl = urldecode(JRequest::getVar('redirect', '', '', 'string'));
		$this->_checkRedirectUrl($redirectUrl);
		$this->setRedirect($redirectUrl, 'Please enable the Javascript to be able to subscribe', 'notice');
		return false;
	}

	function display($dummy1 = false, $dummy2 = false){
		$moduleId = JRequest::getInt('formid');
		if(empty($moduleId)) return;

		if(JRequest::getInt('interval') > 0) setcookie('acysmsSubscriptionState', true, time() + JRequest::getInt('interval'), '/');

		$db = JFactory::getDBO();
		$db->setQuery('SELECT * FROM #__modules WHERE id = '.intval($moduleId).' AND `module` LIKE \'%acysms%\' LIMIT 1');
		$module = $db->loadObject();
		if(empty($module)){
			echo 'No module found';
			exit;
		}

		$module->user = substr($module->module, 0, 4) == 'mod_' ? 0 : 1;
		$module->name = $module->user ? $module->title : substr($module->module, 4);
		$module->style = null;
		$module->module = preg_replace('/[^A-Z0-9_\.-]/i', '', $module->module);

		$params = array();

		echo JModuleHelper::renderModule($module, $params);
	}

	function optin(){
		ACYSMS::checkRobots();
		$my = JFactory::getUser();
		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		$userClass = ACYSMS::get('class.user');
		$db = JFactory::getDBO();

		$userClass->allowModif = false;

		$ajax = JRequest::getInt('ajax', 0);
		if($ajax) header("Content-type:text/html; charset=utf-8");

		if((int)$config->get('allow_visitor', 1) != 1 && empty($my->id)){
			if($ajax){
				echo '{"message":"'.JText::_('SMS_ONLY_LOGGED', true).'","type":"error","code":"0"}';
				exit;
			}else{
				ACYSMS::enqueueMessage(JText::_('SMS_ONLY_LOGGED'), 'error');
				$usercomp = !ACYSMS_J16 ? 'com_user' : 'com_users';
				$app->redirect('index.php?option='.$usercomp.'&view=login');
				return;
			}
		}

		if(empty($my->id)){
			if($config->get('captcha_enabled')){
				$seckey = JRequest::getString('seckey');
				if(!empty($seckey)){
					if($config->get('security_key') !== $seckey){
						if($ajax){
							echo '{"message":"'.JText::_('SMS_ERROR_SECURE_KEY', true).'","type":"error","code":"0"}';
						}else{
							echo JText::_('SMS_ERROR_SECURE_KEY', true);
						}
						exit;
					}
				}else{
					$captchaClass = ACYSMS::get('class.acycaptcha');
					$captchaClass->state = 'acycaptchamodule'.JRequest::getCmd('acyformname');
					if(!$captchaClass->check(JRequest::getString('acycaptcha'))){
						if($ajax){
							echo '{"message":"'.JText::_('SMS_ERROR_CAPTCHA', true).'","type":"error","code":"0"}';
						}else{
							$captchaClass->returnError();
						}
						exit;
					}
				}
			}
		}

		$user = new stdClass();
		$formData = JRequest::getVar('user', array(), '', 'array');

		if(!empty($formData)){
			$userClass->checkFields($formData, $user);
		}

		$currentUser = JFactory::getUser();

		if(!empty($currentUser->id)) $user->user_joomid = $currentUser->id;

		if(empty($user->user_phone_number)){
			if(!empty($currentUser->id)){
				$loggedUser = $userClass->getByJoomid($currentUser->id);
				if(!empty($loggedUser->user_id)){
					$user->user_id = $loggedUser->user_id;
					$user->user_phone_number = $loggedUser->user_phone_number;
				}
			}
		}
		$user->user_phone_number = trim($user->user_phone_number);

		$phoneHelper = ACYSMS::get('helper.phone');
		$validPhone = $phoneHelper->getValidNum($user->user_phone_number, true);

		if(empty($user->user_phone_number) || !$validPhone){
			if($ajax){
				echo '{"message":"'.JText::_('SMS_VALID_PHONE').'","type":"error","code":"0"}';
			}else echo "<script>alert('".JText::_('SMS_VALID_PHONE', true)."'); window.history.go(-1);</script>";
			exit;
		}

		$user->user_phone_number = $validPhone;

		$alreadyExists = $userClass->getByPhone($validPhone);

		if(!empty($alreadyExists->user_id)){
			$user->user_id = $alreadyExists->user_id;
			$currentSubscription = $userClass->getSubscriptionStatus($alreadyExists->user_id);
		}else{
			$currentSubscription = array();
		}

		$user->user_id = $userClass->save($user);

		if(!empty($alreadyExists->user_id) && !empty($alreadyExists->user_phone_number)){
			$query = 'SELECT phone_id FROM #__acysms_phone WHERE phone_number = '.$db->Quote($alreadyExists->user_phone_number);
			$db->setQuery($query);
			$phoneId = $db->loadResult();
		}

		if(($config->get('require_confirmation') && empty($alreadyExists)) || ($config->get('require_confirmation') && !empty($alreadyExists->user_id) && !empty($phoneId))){
			$confirmationRequired = true;
		}else $confirmationRequired = false;

		if($confirmationRequired){
			$phoneClass = ACYSMS::get('class.phone');
			$phoneClass->block(array($user->user_phone_number));
		}

		$myuser = $userClass->get($user->user_id);
		if(empty($myuser->user_id)){
			if($ajax){
				echo '{"message":"Could not save the user","type":"error","code":"1"}';
			}else echo "<script>alert('Could not save the user'); window.history.go(-1);</script>";
			exit;
		}

		$hiddengroupsstring = JRequest::getVar('hiddengroups', '', '', 'string');
		if(!empty($hiddengroupsstring)){

			$hiddengroups = explode(',', $hiddengroupsstring);

			JArrayHelper::toInteger($hiddengroups);

			foreach($hiddengroups as $id => $idOneGroup){
				if(!isset($currentSubscription[$idOneGroup])){

					if($confirmationRequired){
						$addgroups[2][] = $idOneGroup;
					}else $addgroups[1][] = $idOneGroup;
					continue;
				}

				if($currentSubscription[$idOneGroup]->groupuser_status == 1 || $currentSubscription[$idOneGroup]->groupuser_status == 1) continue;

				$updategroups[1][] = $idOneGroup;
			}
		}

		$visibleSubscription = JRequest::getVar('subscription', '', '', 'array');

		if(!empty($visibleSubscription)){
			foreach($visibleSubscription as $idOneGroup){
				if(empty($idOneGroup)) continue;

				if(!isset($currentSubscription[$idOneGroup])){
					if($confirmationRequired){
						$addgroups[2][] = $idOneGroup;
					}else $addgroups[1][] = $idOneGroup;
					continue;
				}

				if($currentSubscription[$idOneGroup]->groupuser_status == 1) continue;

				$updategroups[1][] = $idOneGroup;
			}
		}

		$visiblegroupsstring = JRequest::getVar('visiblegroups', '', '', 'string');
		if(!empty($visiblegroupsstring)){

			$visiblegroup = explode(',', $visiblegroupsstring);
			JArrayHelper::toInteger($visiblegroup);

			foreach($visiblegroup as $idGroup){
				if(!in_array($idGroup, $visibleSubscription) AND !empty($currentSubscription[$idGroup]) AND $currentSubscription[$idGroup]->groupuser_status != '-1'){
					$updategroups['-1'][] = $idGroup;
				}
			}
		}

		$groupUserClass = ACYSMS::get('class.groupuser');
		$status = true;
		$updateMessage = false;
		$insertMessage = false;

		if(!empty($updategroups)){
			$status = $groupUserClass->updateSubscription($myuser->user_id, $updategroups) && $status;
			$updateMessage = true;
		}
		if(!empty($addgroups)){
			$status = $groupUserClass->addSubscription($myuser->user_id, $addgroups) && $status;
			$insertMessage = true;
		}

		if($config->get('subscription_message', 1) || $ajax){
			if($insertMessage){
				$msg = JText::_('SMS_SUBSCRIPTION_OK');
				$code = 3;
				$msgtype = 'success';
			}elseif($updateMessage){

				$msg = JText::_('SMS_SUBSCRIPTION_UPDATED_OK');
				$code = 4;
				$msgtype = 'success';
			}else{
				$msg = JText::_('SMS_ALREADY_SUBSCRIBED');
				$code = 5;
				$msgtype = 'success';
			}
		}

		$replace = array();
		$replace['{group:group_name}'] = '';
		foreach($myuser as $oneProp => $oneVal){
			$replace['{user:'.$oneProp.'}'] = $oneVal;
		}
		$msg = str_replace(array_keys($replace), $replace, $msg);


		$redirectUrl = urldecode(JRequest::getVar('redirect', '', '', 'string'));
		$redirectUrl = str_replace(array_keys($replace), $replace, $redirectUrl);

		if($ajax){
			ob_clean();
			$msg = str_replace(array("\n", "\r", '"', '\\'), array(' ', ' ', "'", '\\\\'), $msg);
			echo '{"message":"'.$msg.'","type":"'.($msgtype == 'warning' ? 'success' : $msgtype).'","code":"'.$code.'"}';
		}elseif(empty($redirectUrl)){
			ACYSMS::display($msg, $msgtype == 'success' ? 'info' : $msgtype);
		}else{
			if($msgtype == 'success'){
				ACYSMS::enqueueMessage($msg, 'success');
			}elseif($msgtype == 'warning') ACYSMS::enqueueMessage($msg, 'notice');
			else ACYSMS::enqueueMessage($msg, 'error');
		}

		if($confirmationRequired){
			$phoneHelper = ACYSMS::get('helper.phone');
			$sendResult = $phoneHelper->sendVerificationCode($validPhone, 'activation_optin');
			if(!$sendResult) ACYSMS::enqueueMessage('Error while sending the AcySMS confirmation code. Please contact the administrator of the website for more information.', 'notice');
		}

		if($ajax) exit;

		$this->_closepop($redirectUrl);
		$moduleId = JRequest::getInt('moduleId', 0);
		if($confirmationRequired){
			$this->setRedirect(ACYSMS::completeLink('activate&moduleId='.$moduleId.'&userId='.$user->user_id, false, false));
		}else $this->setRedirect($redirectUrl);
		return true;
	}

	private function _closepop($redirectUrl){
		$this->_checkRedirectUrl($redirectUrl);

		if(empty($redirectUrl) OR !JRequest::getInt('closepop')) return;

		echo '<script type="text/javascript" language="javascript">
					window.parent.document.location.href=\''.str_replace('&amp;', '&', $redirectUrl).'\';
					d = window.parent.document;
					w = window.parent;
					var e = d.getElementById(\'sbox-window\');
					if(e && typeof(e.close) != "undefined") {
						e.close();
					}else if(typeof(w.jQuery) != "undefined" && w.jQuery(\'div.modal.in\') && w.jQuery(\'div.modal.in\').hasClass(\'in\')){
						w.jQuery(\'div.modal.in\').modal(\'hide\');
					}else if(w.SqueezeBox !== undefined) {
						w.SqueezeBox.close();
					}
				</script>';

		$app = JFactory::getApplication();
		$messages = $app->getMessageQueue();
		if(!empty($messages)){
			$session = JFactory::getSession();
			$session->set('application.queue', $messages);
		}

		exit;
	}

	function optout(){
		ACYSMS::checkRobots();
		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		$myJoomla = JFactory::getUser();

		$userClass = ACYSMS::get('class.user');

		$redirectUrl = urldecode(JRequest::getString('redirectunsub'));
		if(!empty($redirectUrl)) $this->setRedirect($redirectUrl);

		$formData = JRequest::getVar('user', array(), '', 'array');

		$phoneNumber = trim(strip_tags(@$formData['user_phone_number']['phone_country'].@$formData['user_phone_number']['phone_num']));

		if(empty($phoneNumber) && !empty($myJoomla->id)){
			$user = $userClass->getByJoomid($myJoomla->id);
			$phoneNumber = $user->user_phone_number;
		}

		$ajax = JRequest::getInt('ajax', 0);
		if($ajax) header("Content-type:text/html; charset=utf-8");

		$phoneHelper = ACYSMS::get('helper.phone');
		$validPhone = $phoneHelper->getValidNum($phoneNumber, true);

		if(empty($phoneNumber) || !$validPhone){
			if($ajax){
				echo '{"message":"'.JText::_('SMS_VALID_PHONE').'","type":"error","code":"7"}';
			}else echo "<script>alert('".JText::_('SMS_VALID_PHONE', true)."'); window.history.go(-1);</script>";
			exit;
		}

		$alreadyExists = $userClass->getByPhone($validPhone);

		if(empty($alreadyExists->user_id)){
			if($ajax){
				echo '{"message":"'.JText::sprintf('SMS_NOT_IN_GROUP', '<b><i>'.$validPhone.'</i></b>').'","type":"error","code":"8"}';
				exit;
			}
			if(empty($redirectUrl)){
				ACYSMS::display(JText::sprintf('SMS_NOT_IN_GROUP', '<b><i>'.$validPhone.'</i></b>'), 'warning');
			}else ACYSMS::enqueueMessage(JText::sprintf('SMS_NOT_IN_GROUP', '<b><i>'.$validPhone.'</i></b>'), 'notice');
			return $this->_closepop($redirectUrl);
		}

		$visibleSubscription = JRequest::getVar('subscription', '', '', 'array');
		$currentSubscription = $userClass->getSubscriptionStatus($alreadyExists->user_id);
		$hiddenSubscription = explode(',', JRequest::getVar('hiddengroups', '', '', 'string'));

		$updategroups = array();
		$removeSubscription = array_merge($visibleSubscription, $hiddenSubscription);
		foreach($removeSubscription as $idGroup){
			if(!empty($currentSubscription[$idGroup]) AND $currentSubscription[$idGroup]->groupuser_status != '-1'){
				$updategroups[-1][] = $idGroup;
			}
		}

		if(!empty($updategroups)){
			$groupUserClass = ACYSMS::get('class.groupuser');
			$groupUserClass->updateSubscription($alreadyExists->user_id, $updategroups);
			if($config->get('unsubscription_message', 1)){
				if($ajax){
					echo '{"message":"'.JText::_('SMS_UNSUBSCRIPTION_OK').'","type":"success","code":"10"}';
					exit;
				}
				if(empty($redirectUrl)){
					ACYSMS::display(JText::_('SMS_UNSUBSCRIPTION_OK'), 'info');
				}else ACYSMS::enqueueMessage(JText::_('SMS_UNSUBSCRIPTION_OK'));
			}
		}elseif($config->get('unsubscription_message', 1) || $ajax){
			if($ajax){
				echo '{"message":"'.JText::_('SMS_UNSUBSCRIPTION_NOT_IN_GROUP').'","type":"success","code":"11"}';
				exit;
			}
			if(empty($redirectUrl)){
				ACYSMS::display(JText::_('SMS_UNSUBSCRIPTION_NOT_IN_GROUP'), 'info');
			}else ACYSMS::enqueueMessage(JText::_('SMS_UNSUBSCRIPTION_NOT_IN_GROUP'));
		}

		if($ajax) exit;

		return $this->_closepop($redirectUrl);
	}

	private function _checkRedirectUrl($redirectUrl){
		$config = ACYSMS::config();
		$regex = trim(preg_replace('#[^a-z0-9\|\.]#i', '', $config->get('module_redirect')), '|');
		if($regex != 'all' && !empty($redirectUrl)){
			preg_match('#^(https?://)?(www.)?([^/]*)#i', $redirectUrl, $resultsurl);
			$domainredirect = preg_replace('#[^a-z0-9\.]#i', '', @$resultsurl[3]);
			if(!preg_match('#^'.$regex.'$#i', $domainredirect)){
				$regex .= '|'.$domainredirect;
				echo "<script>alert('This redirect url is not allowed, you should change the \"".JText::_('SMS_REDIRECTION_MODULE', true)."\" parameter from the AcySMS configuration page to \"".$regex."\" to allow it or set it to \"all\" to allow all urls'); window.history.go(-1);</script>";
				exit;
			}
		}
	}

	function listing(){
		$errorMsg = "You shouldn't see this page. If you come from an external subscription form, maybe the URL in the form action is not valid.";
		if(!empty($_SERVER['HTTP_HOST'])) $errorMsg .= "<br />Host: ".htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8');
		if(!empty($_SERVER['REQUEST_URI'])) $errorMsg .= "<br />URI: ".htmlspecialchars($_SERVER['REQUEST_URI'], ENT_COMPAT, 'UTF-8');
		if(!empty($_SERVER['HTTP_REFERER'])) $errorMsg .= "<br />Referer: ".htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_COMPAT, 'UTF-8');
		ACYSMS::display($errorMsg, 'error');
	}
}
