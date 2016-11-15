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

if(!include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	echo 'This module can not work without the AcySMS Component';
	return;
};

$doc = JFactory::getDocument();
$config = ACYSMS::config();

switch($params->get('redirectmode', '0')){
	case 1 :
		$redirectUrl = ACYSMS::completeLink('groups', false, true);
		$redirectUrlUnsub = $redirectUrl;
		break;
	case 2 :
		$redirectUrl = $params->get('redirectlink');
		$redirectUrlUnsub = $params->get('redirectlinkunsub');
		break;
	default :
		if(isset($_SERVER["REQUEST_URI"])){
			$requestUri = $_SERVER["REQUEST_URI"];
		}else{
			$requestUri = $_SERVER['PHP_SELF'];
			if(!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri, '/').'?'.$_SERVER['QUERY_STRING'];
		}
		$redirectUrl = (((!empty($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == "on") || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://').$_SERVER["HTTP_HOST"].$requestUri;
		$redirectUrlUnsub = $redirectUrl;
		if($params->get('effect', 'normal') == 'mootools-box') $redirectUrlUnsub = $redirectUrl = '';
}
$regex = trim(preg_replace('#[^a-z0-9\|\.]#i', '', $config->get('module_redirect')), '|');
if($regex != 'all'){
	preg_match('#^(https?://)?(www.)?([^/]*)#i', $redirectUrl, $resultsurl);
	$domainredirect = preg_replace('#[^a-z0-9\.]#i', '', @$resultsurl[3]);
	preg_match('#^(https?://)?(www.)?([^/]*)#i', $redirectUrlUnsub, $resultsurl);
	$domainredirectunsub = preg_replace('#[^a-z0-9\.]#i', '', @$resultsurl[3]);
	$saveRedir = false;
	if(!empty($domainredirect) && !preg_match('#^'.$regex.'$#i', $domainredirect)){
		$regex .= '|'.$domainredirect;
		$saveRedir = true;
	}
	if(!empty($domainredirectunsub) && !preg_match('#^'.$regex.'$#i', $domainredirectunsub)){
		$regex .= '|'.$domainredirectunsub;
		$saveRedir = true;
	}
	if($saveRedir){
		$newConfig = new stdClass();
		$newConfig->module_redirect = $regex;
		$config->save($newConfig);
	}
}

$formName = ACYSMS::getModuleFormName();
$overridedesign = preg_replace('#[^a-z_]#i', '', JRequest::getCmd('design'));
if(!empty($overridedesign)){
	$params->set('includejs', 'module');
}

$introText = $params->get('introtext');
$postText = $params->get('finaltext');
$mootoolsIntro = $params->get('mootoolsintro', '');
if(!empty($introText) && preg_match('#^[A-Z_]*$#', $introText)){
	$introText = JText::_('SMS_'.$introText);
}
if(!empty($postText) && preg_match('#^[A-Z_]*$#', $postText)){
	$postText = JText::_('SMS_'.$postText);
}
if(!empty($mootoolsIntro) && preg_match('#^[A-Z_]*$#', $mootoolsIntro)){
	$mootoolsIntro = JText::_('SMS_'.$mootoolsIntro);
}


if($params->get('effect') == 'mootools-box' AND JRequest::getString('tmpl') != 'component'){

	$mootoolsButton = $params->get('mootoolsbutton', '');
	if(empty($mootoolsButton)){
		$mootoolsButton = JText::_('SMS_SUBSCRIBE');
	}else{
		if(!empty($mootoolsButton) && preg_match('#^[A-Z_]*$#', $mootoolsButton)){
			$mootoolsButton = JText::_('SMS_'.$mootoolsButton);
		}
	}

	$moduleCSS = $config->get('css_module', 'default');
	if(!empty($moduleCSS)){
		$doc->addStyleSheet(ACYSMS_CSS.'module_'.$moduleCSS.'.css');
	}
	JHTML::_('behavior.modal', 'a.modal');
	require(JModuleHelper::getLayoutPath('mod_acysms_subscription', 'popup'));
	return;
}
ACYSMS::initModule($params->get('includejs', 'header'), $params);

$userClass = ACYSMS::get('class.user');
$identifiedUser = null;
$connectedUser = JFactory::getUser();
if($params->get('loggedin', 1) && !empty($connectedUser->id)){
	$identifiedUser = $userClass->getByJoomid($connectedUser->id);
}

$visibleGroups = trim($params->get('groups', 'None'));
$hiddenGroups = trim($params->get('hiddengroups', 'All'));
$visibleGroupsArray = array();
$hiddenGroupsArray = array();
$groupClass = ACYSMS::get('class.group');
if(empty($identifiedUser->user_id)){
	$allGroups = $groupClass->getGroups('group_id');
}else{
	$allGroups = $userClass->getSubscription($identifiedUser->user_id, 'group_id');
}

if(strpos($visibleGroups, ',') OR is_numeric($visibleGroups)){
	$allvisiblegroups = explode(',', $visibleGroups);
	foreach($allGroups as $oneGroup){
		if($oneGroup->group_published AND in_array($oneGroup->group_id, $allvisiblegroups)) $visibleGroupsArray[] = $oneGroup->group_id;
	}
}elseif(strtolower($visibleGroups) == 'all'){
	foreach($allGroups as $oneGroup){
		if($oneGroup->group_published){
			$visibleGroupsArray[] = $oneGroup->group_id;
		}
	}
}

if(strpos($hiddenGroups, ',') OR is_numeric($hiddenGroups)){
	$allhiddengroups = explode(',', $hiddenGroups);
	foreach($allGroups as $oneGroup){
		if($oneGroup->group_published AND in_array($oneGroup->group_id, $allhiddengroups)) $hiddenGroupsArray[] = $oneGroup->group_id;
	}
}elseif(strtolower($hiddenGroups) == 'all'){
	$visibleGroupsArray = array();
	foreach($allGroups as $oneGroup){
		if(!empty($oneGroup->group_published)){
			$hiddenGroupsArray[] = $oneGroup->group_id;
		}
	}
}

if(!empty($visibleGroupsArray) AND !empty($hiddenGroupsArray)){
	$visibleGroupsArray = array_diff($visibleGroupsArray, $hiddenGroupsArray);
}

$visibleGroups = $params->get('dropdown', 0) ? '' : implode(',', $visibleGroupsArray);
$hiddenGroups = implode(',', $hiddenGroupsArray);

if(!$params->get('dropdown', 0) && empty($hiddenGroups) && empty($visibleGroups)){
}

if(!empty($identifiedUser->user_id)){
	$countSub = 0;
	$countUnsub = 0;
	foreach($visibleGroupsArray as $idOneGroup){
		if($allGroups[$idOneGroup]->groupuser_status == 0){
			$countSub++;
		}elseif($allGroups[$idOneGroup]->groupuser_status == 1) $countUnsub++;
	}
	foreach($hiddenGroupsArray as $idOneGroup){
		if($allGroups[$idOneGroup]->groupuser_status == 0){
			$countSub++;
		}elseif($allGroups[$idOneGroup]->groupuser_status == 1) $countUnsub++;
	}
}

$checkedGroups = $params->get('groupschecked', 'All');
if(strtolower($checkedGroups) == 'all'){
	$checkedGroupsArray = $visibleGroupsArray;
}elseif(strpos($checkedGroups, ',') OR is_numeric($checkedGroups)){
	$checkedGroupsArray = explode(',', $checkedGroups);
}else{
	$checkedGroupsArray = array();
}

$captions = array();
$captions['user_firstname'] = $params->get('firstnametext', JText::_('SMS_FIRSTNAMECAPTION'));
$captions['user_lastname'] = $params->get('lastnametext', JText::_('SMS_LASTNAMECAPTION'));
$captions['user_phone_number'] = $params->get('phonetext', JText::_('SMS_PHONECAPTION'));
$displayOutside = $params->get('displayfields', 0);
$displayInline = ($params->get('displaymode', 'vertical') == 'vertical') ? false : true;

$displayedFields = $params->get('customfields', 'user_phone_number');
$fieldsToDisplay = explode(',', $displayedFields);
$extraFields = array();

$fieldsize = $params->get('fieldsize');
if(is_numeric($fieldsize)) $fieldsize .= 'px';

$fieldsClass = ACYSMS::get('class.fields');
$fieldsClass->prefix = 'user_';
$fieldsClass->suffix = '_'.$formName;
$extraFields = $fieldsClass->getFields('module', $identifiedUser);

$newOrdering = array();
$requiredFields = array();
$validMessages = array();
$checkFields = array();
$checkFieldsType = array();
$checkFieldsRegexp = array();
$validCheckMsg = array();

foreach($extraFields as $fieldnamekey => $oneField){
	if(in_array($fieldnamekey, $fieldsToDisplay)) $newOrdering[] = $fieldnamekey;
	if(in_array($oneField->fields_type, array('text', 'date', 'phone')) AND $params->get('fieldsize') AND (empty($extraFields[$fieldnamekey]->fields_options['size']) || $params->get('fieldsize') < $extraFields[$fieldnamekey]->fields_options['size'])){
		$extraFields[$fieldnamekey]->fields_options['size'] = $params->get('fieldsize');
	}

	if(!empty($captions[$fieldnamekey]) && strlen($captions[$fieldnamekey]) > 1){

		$extraFields[$fieldnamekey]->fields_fieldname = $captions[$fieldnamekey];
	}
	if(!empty($oneField->fields_required)){
		$requiredFields[] = $fieldnamekey;
		if(!empty($oneField->fields_options['errormessage'])){
			$validMessages[] = addslashes($fieldsClass->trans($oneField->fields_options['errormessage']));
		}else{
			$validMessages[] = addslashes(JText::sprintf('SMS_FIELD_VALID', $fieldsClass->trans($oneField->fields_fieldname)));
		}
	}
	if($oneField->fields_type == 'text' && !empty($oneField->fields_options['checkcontent']) && in_array($fieldnamekey, explode(',', $params->get('customfields')))){
		$checkFields[] = $fieldnamekey;
		$checkFieldsType[] = $oneField->fields_options['checkcontent'];
		if($oneField->fields_options['checkcontent'] == 'regexp') $checkFieldsRegexp[] = $oneField->fields_options['regexp'];
		if(!empty($oneField->fields_options['errormessagecheckcontent'])){
			$validCheckMsg[] = addslashes($fieldsClass->trans($oneField->fields_options['errormessagecheckcontent']));
		}elseif(!empty($oneField->fields_options['errormessage'])){
			$validCheckMsg[] = addslashes($fieldsClass->trans($oneField->fields_options['errormessage']));
		}else{
			$validCheckMsg[] = addslashes(JText::sprintf('SMS_FIELD_CONTENT_VALID', $fieldsClass->trans($oneField->fieldname)));
		}
	}
}
$fieldsToDisplay = $newOrdering;

if(!empty($requiredFields)){
	$js = "acysms['reqFields".$formName."'] = Array('".implode("','", $requiredFields)."');
	acysms['validFields".$formName."'] = Array('".implode("','", $validMessages)."');";

	if($params->get('includejs', 'header') == 'header'){
		$doc->addScriptDeclaration($js);
	}else{
		echo "<script type=\"text/javascript\">
			<!--
				$js
			//-->
				</script>";
	}
	if(!empty($checkFields)){
		$js = "acysms['checkFields".$formName."'] = Array('".implode("','", $checkFields)."');
		acysms['checkFieldsType".$formName."'] = Array('".implode("','", $checkFieldsType)."');
		acysms['validCheckFields".$formName."'] = Array('".implode("','", $validCheckMsg)."');";
		if(!empty($checkFieldsRegexp)) $js .= "acysms['checkFieldsRegexp".$formName."'] = Array('".implode("','", $checkFieldsRegexp)."');";

		if($params->get('includejs', 'header') == 'header'){
			$doc->addScriptDeclaration($js);
		}else{
			echo "<script type=\"text/javascript\">
					$js
					</script>";
		}
	}
}

if(!in_array('user_phone_number', $fieldsToDisplay) && empty($connectedUser->user_id)) $fieldsToDisplay[] = 'user_phone_number';

if($params->get('effect') == 'mootools-slide' || $params->get('redirectmode', 0) == '3'){
	acysms_loadMootools($params->get('effect') == 'mootools-slide');
}

if($params->get('effect') == 'mootools-slide'){
	$mootoolsButton = $params->get('mootoolsbutton', '');
	if(empty($mootoolsButton)) $mootoolsButton = JText::_('SMS_SUBSCRIBE');

	$js = "window.addEvent('domready', function(){
				var myAcySMSSlide = new Fx.Slide('acysms_fulldiv_$formName');
				myAcySMSSlide.hide();
				try{
					var acysmstogglemodule = document.id('acysms_togglemodule_$formName');
				}catch(err){
					var acysmstogglemodule = $('acysms_togglemodule_$formName');
				}

				acysmstogglemodule.addEvent('click', function(e){
					if(myAcySMSSlide.wrapper.offsetHeight == 0){
						acysmstogglemodule.className = 'acysms_togglemodule acyactive';
					}else{
						acysmstogglemodule.className = 'acysms_togglemodule';
					}
					myAcySMSSlide.toggle();
					try {
						var acySMSevt = new Event(e);
						acySMSevt.stop();
					} catch(err) {
						e.stop();
					}
				});
			});";
	if($params->get('includejs', 'header') == 'header'){
		$doc->addScriptDeclaration($js);
	}else{
		echo "<script type=\"text/javascript\">
			<!--
				$js
			//-->
				</script>";
	}
}

if($params->get('overlay', 0)){
	JHTML::_('behavior.tooltip');
}

if($params->get('showterms', false)){
	require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
	$termsIdContent = $params->get('termscontent', 0);
	if(empty($termsIdContent)){
		$termslink = JText::_('SMS_TERMS');
	}else{
		if(is_numeric($termsIdContent)){
			$db = JFactory::getDBO();
			if(!ACYSMS_J16){
				$query = 'SELECT a.id,a.alias,a.catid,a.sectionid, c.alias as catalias, s.alias as secalias FROM #__content as a ';
				$query .= ' LEFT JOIN #__categories AS c ON c.id = a.catid ';
				$query .= ' LEFT JOIN #__sections AS s ON s.id = a.sectionid ';
				$query .= 'WHERE a.id = '.intval($termsIdContent).' LIMIT 1';
				$db->setQuery($query);
				$article = $db->loadObject();

				$section = $article->sectionid.(!empty($article->secalias) ? ':'.$article->secalias : '');
				$category = $article->catid.(!empty($article->catalias) ? ':'.$article->catalias : '');
				$articleid = $article->id.(!empty($article->alias) ? ':'.$article->alias : '');
				$url = ContentHelperRoute::getArticleRoute($articleid, $category, $section);
			}else{
				$query = 'SELECT a.id,a.alias,a.catid, c.alias as catalias FROM #__content as a ';
				$query .= ' LEFT JOIN #__categories AS c ON c.id = a.catid ';
				$query .= 'WHERE a.id = '.intval($termsIdContent).' LIMIT 1';
				$db->setQuery($query);
				$article = $db->loadObject();

				$category = $article->catid.(!empty($article->catalias) ? ':'.$article->catalias : '');
				$articleid = $article->id.(!empty($article->alias) ? ':'.$article->alias : '');

				$url = ContentHelperRoute::getArticleRoute($articleid, $category);
			}
			$url .= (strpos($url, '?') ? '&' : '?').'tmpl=component';
		}else{
			$url = $termsIdContent;
		}

		if($params->get('showtermspopup', 1) == 1){
			JHTML::_('behavior.modal', 'a.modal');
			$termslink = '<a class="modal" title="'.JText::_('SMS_TERMS', true).'"  href="'.$url.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}">'.JText::_('SMS_TERMS').'</a>';
		}else{
			$termslink = '<a title="'.JText::_('SMS_TERMS', true).'"  href="'.$url.'" target="_blank">'.JText::_('SMS_TERMS').'</a>';
		}
	}
}

if(!empty($overridedesign)){
	ob_start();
}

if($params->get('displaymode') == 'tableless'){
	require(JModuleHelper::getLayoutPath('mod_acysms_subscription', 'tableless'));
}else{
	require(JModuleHelper::getLayoutPath('mod_acysms_subscription'));
}

if(!empty($overridedesign)){
	$moduleDisplay = ob_get_clean();
	$file = ACYSMS_MEDIA.'plugins'.DS.'squeezepage'.DS.$overridedesign.'.php';
	if(file_exists($file)){
		ob_start();
		require($file);
		$squeezePage = ob_get_clean();
		$squeezePage = str_replace('{module}', $moduleDisplay, $squeezePage);
		echo $squeezePage;
		exit;
	}else{
		echo $moduleDisplay;
	}
}
acysms_loadMootools();
