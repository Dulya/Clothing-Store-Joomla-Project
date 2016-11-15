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

class ACYSMSmenuHelper{
	function display($selected = ''){
		$this->_addJS();

		$doc = JFactory::getDocument();
		if(version_compare(JVERSION, '1.6.0', '<')){
			$doc->addStyleDeclaration(" #submenu-box{display:none !important;} ");
		}

		$js = "function acysmsToggleClass(id,myclass){
			elem = document.getElementById(id);
			if(elem.className.search(myclass) < 0){

				var elements = document.querySelectorAll('.mainelement');

				for(var i = 0; i < elements.length;i++){
					elements[i].className = elements[i].className.replace('opened','');
				}
				elem.className += ' '+myclass;
				if(myclass == 'iconsonly') sessionStorage.setItem('acysmsclosedmenu', '1');
			}else{
				elem.className = elem.className.replace(' '+myclass,'');
				if(myclass == 'iconsonly') sessionStorage.setItem('acysmsclosedmenu', '0');
			}
		}

		window.addEvent('domready', function(){
			var isClosed = sessionStorage.getItem('acysmsclosedmenu');
			if(isClosed == 1) acysmsToggleClass('acysmsallcontent', 'iconsonly');
			setTimeout(function () {
				document.getElementById('acysmsmainarea').style.transition = 'margin 0.4s cubic-bezier(0.00, 0.00, 1, 1.00)';
				document.getElementById('acysmsmenu_leftside').style.transition = 'width 0.4s cubic-bezier(0.00, 0.00, 1, 1.00)';
			}, 1000);
		});

		function acysmsAddClass(id,myclass){
			elem = document.getElementById(id);
			if(elem.className.search(myclass)>=0) return;
			elem.className += ' '+myclass;
		}

		function acysmsRemoveClass(id,myclass){
			elem = document.getElementById(id);
			elem.className = elem.className.replace(' '+myclass,'');
		}
		";


		$app = JFactory::getApplication();
		if($app->isAdmin()){
			$doc->addScript(ACYSMS_JS.'acysmstoolbar.js?v='.filemtime(ACYSMS_MEDIA.'js'.DS.'acysmstoolbar.js'));
		}

		$doc->addScriptDeclaration($js);

		$selected = substr($selected, 0, 5);

		if($selected == 'data' || $selected == 'data&' || $selected == 'field' || $selected == 'group') $selected = 'recei';
		if($selected == 'messa' || $selected == 'categ') $selected = 'messa';
		if($selected == 'sende' || $selected == 'custo' || $selected == 'cpane') $selected = 'confi';

		$config = ACYSMS::config();
		$integration = ACYSMS::getIntegration();
		$mainmenu = array();
		$submenu = array();

		if(ACYSMS::isAllowed($config->get('acl_cpanel_manage', 'all'))){
			$mainmenu['dashboard'] = array(JText::_('SMS_CPANEL'), 'index.php?option=com_acysms', 'smsicon-dashboard');
		}

		if(ACYSMS::isAllowed($config->get('acl_receivers_manage', 'all'))){
			$mainmenu['receiver'] = array(JText::_('SMS_RECEIVERS'), 'index.php?option=com_acysms&ctrl=receiver', 'smsicon-receiver');
			$submenu['receiver'] = array();
			$submenu['receiver'][] = array(JText::_('SMS_RECEIVERS'), 'index.php?option=com_acysms&ctrl=receiver', 'smsicon-receiver');

			if(ACYSMS::isAllowed($config->get('acl_receivers_manage', 'all')) && (!ACYSMS_J16 || JFactory::getUser()->authorise('core.admin', 'com_acysms') && $integration->componentName == 'acysms')) $submenu['receiver'][] = array(JText::_('SMS_EXTRA_FIELDS'), 'index.php?option=com_acysms&ctrl=fields', 'smsicon-fields');
			if(ACYSMS::isAllowed($config->get('acl_groups_manage', 'all')) && $integration->componentName == 'acysms') $submenu['receiver'][] = array(JText::_('SMS_GROUPS'), 'index.php?option=com_acysms&ctrl=group', 'smsicon-group');
			if(ACYSMS::isAllowed($config->get('acl_receivers_import', 'all')) && $integration->componentName == 'acysms') $submenu['receiver'][] = array(JText::_('SMS_IMPORT'), 'index.php?option=com_acysms&ctrl=data&task=import', 'smsicon-import');
			if(ACYSMS::isAllowed($config->get('acl_receivers_export', 'all')) && $integration->componentName == 'acysms') $submenu['receiver'][] = array(JText::_('SMS_EXPORT'), 'index.php?option=com_acysms&ctrl=data&task=export', 'smsicon-export');
		}

		if(ACYSMS::isAllowed($config->get('acl_messages_manage', 'all'))){
			$mainmenu['message'] = array(JText::_('SMS_MESSAGES'), 'index.php?option=com_acysms&ctrl=message', 'smsicon-message');
			$submenu['message'] = array();
			$submenu['message'][] = array(JText::_('SMS_MESSAGES'), 'index.php?option=com_acysms&ctrl=message', 'smsicon-message');

			if(ACYSMS::isAllowed($config->get('acl_categories_manage', 'all'))) $submenu['message'][] = array(JText::_('SMS_CATEGORIES'), 'index.php?option=com_acysms&ctrl=category', 'smsicon-categories');
		}

		if(ACYSMS::isAllowed($config->get('acl_answers_manage', 'all'))){
			$mainmenu['answer'] = array(JText::_('SMS_ANSWERS'), 'index.php?option=com_acysms&ctrl=answer', 'smsicon-answer');
			$submenu['answer'] = array();

			$submenu['answer'][] = array(JText::_('SMS_ANSWERS'), 'index.php?option=com_acysms&ctrl=answer', 'smsicon-answer');
			if(ACYSMS::isAllowed($config->get('acl_answers_trigger_manage', 'all'))) $submenu['answer'][] = array(JText::_('SMS_ANSWERS_TRIGGER'), 'index.php?option=com_acysms&ctrl=answertrigger', 'smsicon-answertrigger');
		}

		if(ACYSMS::isAllowed($config->get('acl_queue_manage', 'all'))) $mainmenu['queue'] = array(JText::_('SMS_QUEUE'), 'index.php?option=com_acysms&ctrl=queue', 'smsicon-queue');


		if(ACYSMS::isAllowed($config->get('acl_stats_manage', 'all'))) $mainmenu['stats'] = array(JText::_('SMS_STATS'), 'index.php?option=com_acysms&ctrl=stats', 'smsicon-stats');


		if(ACYSMS::isAllowed($config->get('acl_configuration_manage', 'all')) && (!ACYSMS_J16 || JFactory::getUser()->authorise('core.admin', 'com_acysms'))){
			$mainmenu['config'] = array(JText::_('SMS_CONFIGURATION'), 'index.php?option=com_acysms&ctrl=cpanel', 'smsicon-config');
			$submenu['config'] = array();
			$submenu['config'][] = array(JText::_('SMS_CONFIGURATION'), 'index.php?option=com_acysms&ctrl=cpanel', 'smsicon-config');

			if(ACYSMS::isAllowed($config->get('acl_sender_profiles_manage', 'all'))) $submenu['config'][] = array(JText::_('SMS_SENDER_PROFILES'), 'index.php?option=com_acysms&ctrl=senderprofile', 'smsicon-sender');
			$submenu['config'][] = array(JText::_('SMS_CUSTOMERS'), 'index.php?option=com_acysms&ctrl=customer', 'smsicon-customers');
		}


		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'acysmsmenu.css?v='.filemtime(ACYSMS_MEDIA.'css'.DS.'acysmsmenu.css'));

		$menu = '<div id="acysmsmenu_leftside" class="donotprint acysmsaffix-top">';
		$menu .= '<div class="acysmsmenu_slide"><span onclick="acysmsToggleClass(\'acysmsallcontent\',\'iconsonly\');"><i class="smsicon-open-close"></i></span></div>';
		$menu .= '<div class="acysmsmenu_mainmenus">';
		$menu .= '<ul>';
		foreach($mainmenu as $id => $oneMenu){
			$sel = '';
			if($selected == substr($id, 0, 5)) $sel = ' sel opened';
			$menu .= '<li class="mainelement'.$sel.'" id="mainelement'.$id.'"><span onclick="acysmsToggleClass(\'mainelement'.$id.'\',\'opened\');"><a '.(!empty($submenu[$id]) ? 'href="#" onclick="return false;"' : 'href="'.$oneMenu[1].'"').' ><i class="'.$oneMenu[2].'"></i><span class="subtitle">'.$oneMenu[0].'</span>'.(!empty($submenu[$id]) ? '<i class="smsicon-down"></i>' : '').'</a></span>';
			if(!empty($submenu[$id])){
				$menu .= '<ul>';
				foreach($submenu[$id] as $subelement){
					$menu .= '<li class="acysmssubmenu" ><a class="acysmssubmenulink" href="'.$subelement[1].'" title="'.$subelement[0].'"><i class="'.$subelement[2].'"></i><span>'.$subelement[0].'</span></a></li>';
				}
				$menu .= '</ul>';
			}
			$menu .= '</li>';
		}

		$menu .= '<li class="mainelement" id="mainelementbalancechecker">';
		$menu .= '<div id="mybalancechecker">';
		$menu .= $this->myBalanceArea();
		$menu .= '</div>';
		$menu .= '</li>';

		$menu .= '<li class="mainelement" id="mainelementmyacysms">';
		$menu .= '<div id="myacysmsarea">'; //DO NOT CHANGE THIS ID! we use it for ajax things...
		$menu .= $this->myacysmsarea();
		$menu .= '</div>'; //End of acysms myacysmsarea
		$menu .= '</li>';


		$menu .= '</ul>';
		$menu .= '</div>'; //end of acysmsmenu_mainmenus
		$menu .= '</div>'; //end of acysmsmenu_leftside

		return $menu;
	}


	public function myBalanceArea(){

		$menu = '';

		$senderProfileClass = ACYSMS::get('class.senderprofile');
		$defaultGateway = $senderProfileClass->getGateway('');
		if(empty($defaultGateway)){
			$menu .= '<div id="nogateway">'.JText::_('SMS_UNDEFINED_DEFAULT_GATEWAY').'</div>';
			$menu .= '<div><button id="refreshBalance"  onclick="checkBalance()"><i class="smsicon-refresh"></i>'.JText::_('SMS_REFRESH').'</button></div>';
			return $menu;
		}


		$balance = $defaultGateway->getBalance();
		$url = $defaultGateway->creditsUrl;
		if(empty($balance)) return $menu;


		$creditTypeName = '';

		$balanceMax = 0;

		foreach($balance as $type => $value){
			if($value > $balanceMax) $balanceMax = $value;
			if($type != 'default') $creditTypeName = $type.' : ';
			$menu .= '<div class="mybalance_left">'.$creditTypeName.'<b>'.$value.'</b> '.JText::_('SMS_CREDITS_LEFT').'</div>';
		}

		if($balanceMax <= 25){
			$menu .= '<progress id="low_balance" value="'.$balanceMax.'" max="100"></progress>';
		}else $menu .= '<progress id="high_balance" value="'.$balanceMax.'" max="100"></progress>';


		$menu .= '<div class="myacysmsbuttons">';
		if(!empty($url)) $menu .= '<div><a  target ="_blank" href="'.$url.'"><button id="newCredits"><i class="smsicon-new"></i>'.JText::_('SMS_PURCHASE_CREDITS').'</button></a></div><br/>';
		$menu .= '<div><button id="refreshBalance"  onclick="checkBalance()"><i class="smsicon-refresh"></i>'.JText::_('SMS_REFRESH').'</button></div></div>';
		return $menu;
	}

	private function _addJS(){
		$script = "function checkBalance(){
			document.getElementById('mybalancechecker').innerHTML = '<span class=\"onload spinner2\"></span>';
			try{
				new Ajax('index.php?&option=com_acysms&ctrl=senderprofile&task=checkBalance&tmpl=component',
				{
					method: 'post',
					onSuccess: function(responseText, responseXML) {

						document.getElementById('mybalancechecker').innerHTML = responseText;
					}
				}).request();
			}catch(err){
				new Request({
					method: 'post',
					url: 'index.php?&option=com_acysms&ctrl=senderprofile&task=checkBalance&tmpl=component',
					onSuccess: function(responseText, responseXML) {
						document.getElementById('mybalancechecker').innerHTML = responseText;
					}
				}).send();
			}
		}";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
	}


	public function myacysmsarea(){

		$this->_addAjaxScript();

		$config = ACYSMS::config();
		$menu = '<div id="myacysms_level">'.ACYSMS_NAME.' '.$config->get('level').' : '.$config->get('version').'</div><div id="myacysms_version">';

		$currentVersion = $config->get('version', '');
		$latestVersion = $config->get('latestversion', '');

		if(($currentVersion >= $latestVersion)){
			$menu .= '<div class="acysmsversion_uptodate myacysmsbuttons"><i class="smsicon-import"></i>'.JText::_('SMS_LATEST_VERSION_OK').'</div>';
		}elseif(!empty($latestVersion)){
			$menu .= '<div class="acysmsversion_needtoupdate myacysmsbuttons"><a class="acysms_updateversion" href="'.ACYSMS_REDIRECT.'update-acysms-'.$config->get('level').'" target="_blank"><i class="smsicon-import"></i>'.JText::sprintf('SMS_UPDATE_NOW', $latestVersion).'</a></div>';
		}


		$menu .= '</div>';

		if(ACYSMS::level(1)){
			$expirationDate = $config->get('expirationdate', '');

			if(empty($expirationDate) || $expirationDate == -1){
				$menu .= '<div id="myacysms_expiration"></div>';
			}elseif($expirationDate == -2){
				$menu .= '<div id="myacysms_expiration"><div class="acylicence_expired"><span style="color:#c2d5f3; line-height: 16px;">'.JText::_('SMS_ATTACH_LICENCE').' :</span><div><a class="acy_attachlicence myacysmsbuttons" href="'.ACYSMS_REDIRECT.'acysms-assign" target="_blank"><i class="smsicon-attach"></i>'.JText::_('SMS_ATTACH_LICENCE_BUTTON').'</a></div></div></div>';
			}elseif($expirationDate < time()){
				$menu .= '<div id="myacysms_expiration"><div class="acylicence_expired"><span class="acylicenceinfo">'.JText::_('SMS_SUBSCRIPTION_EXPIRED').'</span><a class="acy_subscriptionexpired myacysmsbuttons" href="'.ACYSMS_REDIRECT.'renew-acysms-'.$config->get('level').'" target="_blank"><i class="smsicon-renew"></i>'.JText::_('SMS_SUBSCRIPTION_EXPIRED_LINK').'</a></div></div>';
			}else{
				$menu .= '<div id="myacysms_expiration"><div class="acylicence_valid myacysmsbuttons"><span class="acy_subscriptionok">'.JText::_('SMS_VALID_UNTIL').' : '.ACYSMS::getDate($expirationDate, 'DATE_FORMAT_LC4').'</span></div></div>';
			}
		}

		$menu .= '<div class="myacysmsbuttons"><button onclick="checkForNewVersion()"><i class="smsicon-viewmore"></i>'.JText::_('SMS_CHECK_MY_VERSION').'</button></div>';

		return $menu;
	}

	private function _addAjaxScript(){

		$script = "function checkForNewVersion(){
			document.getElementById('myacysmsarea').innerHTML = '<span class=\"onload spinner2\"></span>';
			try{
				new Ajax('index.php?&option=com_acysms&ctrl=update&task=checkForNewVersion&tmpl=component',
				{
					method: 'post',
					onSuccess: function(responseText, responseXML) {
						response = JSON.parse(responseText);
						document.getElementById('myacysmsarea').innerHTML = response.content;
					}
				}).request();
			}catch(err){
				new Request({
					method: 'post',
					url: 'index.php?&option=com_acysms&ctrl=update&task=checkForNewVersion&tmpl=component',
					onSuccess: function(responseText, responseXML) {
						response = JSON.parse(responseText);
						document.getElementById('myacysmsarea').innerHTML = response.content;
					}
				}).send();
			}
		}";

		$config =& ACYSMS::config();
		$lastlicensecheck = $config->get('lastlicensecheck', '');
		if(empty($lastlicensecheck) || $lastlicensecheck < (time() - 604800)){
			$script .= 'window.addEvent("load", function(){
				checkForNewVersion();
			});';
		}

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);
	}
}
