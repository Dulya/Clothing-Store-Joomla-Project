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

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
define('ACYSMS_COMPONENT', 'com_acysms');
define('ACYSMS_ROOT', rtrim(JPATH_ROOT, DS).DS);
define('ACYSMS_FRONT', rtrim(JPATH_SITE, DS).DS.'components'.DS.ACYSMS_COMPONENT.DS);
define('ACYSMS_BACK', rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.ACYSMS_COMPONENT.DS);
define('ACYSMS_HELPER', ACYSMS_BACK.'helpers'.DS);
define('ACYSMS_BUTTON', ACYSMS_BACK.'buttons');
define('ACYSMS_CLASS', ACYSMS_BACK.'classes'.DS);
define('ACYSMS_TYPE', ACYSMS_BACK.'types'.DS);
define('ACYSMS_CONTROLLER', ACYSMS_BACK.'controllers'.DS);
define('ACYSMS_CONTROLLER_FRONT', ACYSMS_FRONT.'controllers'.DS);
define('ACYSMS_GATEWAY', ACYSMS_BACK.'gateway'.DS);
define('ACYSMS_INTEGRATION', ACYSMS_BACK.'integration'.DS);
include_once(ACYSMS_GATEWAY.'default'.DS.'gateway.php');
include_once(ACYSMS_INTEGRATION.'default'.DS.'integration.php');


$app = JFactory::getApplication();
if($app->isAdmin()){
	define('ACYSMS_IMAGES', '../media/'.ACYSMS_COMPONENT.'/images/');
	define('ACYSMS_CSS', '../media/'.ACYSMS_COMPONENT.'/css/');
	define('ACYSMS_JS', '../media/'.ACYSMS_COMPONENT.'/js/');
}else{
	define('ACYSMS_IMAGES', JURI::base(true).'/media/'.ACYSMS_COMPONENT.'/images/');
	define('ACYSMS_CSS', JURI::base(true).'/media/'.ACYSMS_COMPONENT.'/css/');
	define('ACYSMS_JS', JURI::base(true).'/media/'.ACYSMS_COMPONENT.'/js/');
}


define('ACYSMS_J16', version_compare(JVERSION, '1.6.0', '>=') ? true : false);
define('ACYSMS_J30', version_compare(JVERSION, '3.0.0', '>=') ? true : false);


$compatPath = ACYSMS_BACK.'compat'.DS.'compat';
if(file_exists($compatPath.substr(str_replace('.', '', JVERSION), 0, 2).'.php')){
	require($compatPath.substr(str_replace('.', '', JVERSION), 0, 2).'.php');
}elseif(file_exists($compatPath.substr(str_replace('.', '', JVERSION), 0, 1).'.php')) require($compatPath.substr(str_replace('.', '', JVERSION), 0, 1).'.php');
else{
	echo 'AcySMS: Could not load compat file for J'.JVERSION;
	return;
}

define('ACYSMS_DBPREFIX', '#__acysms_');
define('ACYSMS_NAME', 'AcySMS');
define('ACYSMS_MEDIA', ACYSMS_ROOT.'media'.DS.ACYSMS_COMPONENT.DS);
define('ACYSMS_TEMPLATE', ACYSMS_MEDIA.'templates'.DS);
define('ACYSMS_UPDATEURL', 'https://www.acyba.com/index.php?option=com_updateme&ctrl=update&task=');
define('ACYSMS_HELPURL', 'https://www.acyba.com/index.php?option=com_updateme&ctrl=doc&component='.ACYSMS_NAME.'&page=');
define('ACYSMS_REDIRECT', 'http://www.acyba.com/index.php?option=com_updateme&ctrl=redirect&page=');


if(is_callable("date_default_timezone_set")) date_default_timezone_set(@date_default_timezone_get());

class ACYSMS{

	static public function footer(){
		$config = ACYSMS::config();
		$text = '<div class="acysms_footer"><a href="http://www.acyba.com" target="_blank" title="AcySMS - Joomla! SMS Extension">AcySMS v.'.$config->get('version').' - Joomla! SMS Extension</a></div>';
		return $text;
	}



	static public function getDate($time = 0, $format = '%d %B %Y %H:%M'){

		if(empty($time)) return '';

		if(is_numeric($format)) $format = JText::_('DATE_FORMAT_LC'.$format);

		if(version_compare(JVERSION, '1.6.0', '>=')){
			$format = str_replace(array('%A', '%d', '%B', '%m', '%Y', '%y', '%H', '%M', '%S', '%a'), array('l', 'd', 'F', 'm', 'Y', 'y', 'H', 'i', 's', 'D'), $format);
			return JHTML::_('date', $time, $format, false);
		}else{
			static $timeoffset = null;
			if($timeoffset === null){
				$config = JFactory::getConfig();
				$timeoffset = $config->getValue('config.offset');
			}
			return JHTML::_('date', $time - date('Z'), $format, $timeoffset);
		}
	}

	static function listingSearch($escapedSearch){
		$app = JFactory::getApplication();
		if($app->isAdmin()){ ?>
			<div class="filter-search">
				<input type="text" name="search" id="search" value="<?php echo htmlspecialchars($escapedSearch, ENT_COMPAT, 'UTF-8'); ?>" class="text_area" placeholder="<?php echo JText::_('SMS_SEARCH'); ?>" title="<?php echo JText::_('SMS_SEARCH'); ?>"/>
				<button style="float:none;" onclick="document.adminForm.limitstart.value=0;this.form.submit();" class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('SMS_SEARCH'); ?>"><i class="smsicon-viewmore"></i></button>
				<button style="float:none;" onclick="document.adminForm.limitstart.value=0;document.getElementById('search').value='';this.form.submit();" class="btn tip hasTooltip" type="button" title="<?php echo JText::_('SMS_RESET'); ?>"><i class="smsicon-cancel"></i></button>
			</div>
		<?php }else{ ?>
			<input placeholder="<?php echo JText::_('SMS_SEARCH'); ?>" type="text" name="search" id="search" value="<?php echo htmlspecialchars($escapedSearch, ENT_COMPAT, 'UTF-8'); ?>" class="text_area" style="margin-bottom:0;"/>
			<button class="btn" onclick="document.adminForm.limitstart.value=0;document.adminForm.task.value='';this.form.submit();" title="<?php echo JText::_('SMS_GO'); ?>"><i class="smsicon-viewmore"></i></button>
			<button class="btn" onclick="document.adminForm.limitstart.value=0;document.adminForm.task.value='';document.getElementById('search').value='';this.form.submit();" title="<?php echo JText::_('SMS_RESET'); ?>"><i class="smsicon-cancel"></i></button>
			<?php
		}
	}

	static public function getTime($date){
		static $timeoffset = null;
		if($timeoffset === null){
			$config = JFactory::getConfig();
			if(ACYSMS_J30){
				$timeoffset = $config->get('offset');
			}else{
				$timeoffset = $config->getValue('config.offset');
			}

			if(ACYSMS_J16){
				$dateC = JFactory::getDate($date, $timeoffset);
				$timeoffset = $dateC->getOffsetFromGMT(true);
			}
		}

		return strtotime($date) - $timeoffset * 60 * 60 + date('Z');
	}

	static public function loadLanguage(){
		$lang = JFactory::getLanguage();
		$lang->load(ACYSMS_COMPONENT, JPATH_SITE);
		$lang->load(ACYSMS_COMPONENT.'_custom', JPATH_SITE);
	}

	static public function createDir($dir, $report = true){
		if(is_dir($dir)) return true;

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$indexhtml = '<html><body bgcolor="#FFFFFF"></body></html>';

		if(!JFolder::create($dir)){
			if($report) self::display('Could not create the directory '.$dir, 'error');
			return false;
		}
		if(!JFile::write($dir.DS.'index.html', $indexhtml)){
			if($report) self::display('Could not create the file '.$dir.DS.'index.html', 'error');
		}

		return true;
	}

	static public function replaceDate($mydate){

		if(strpos($mydate, '{time}') === false) return $mydate;

		$mydate = str_replace('{time}', time(), $mydate);
		$operators = array('+', '-');
		foreach($operators as $oneOperator){
			if(!strpos($mydate, $oneOperator)) continue;
			list($part1, $part2) = explode($oneOperator, $mydate);
			if($oneOperator == '+'){
				$mydate = trim($part1) + trim($part2);
			}elseif($oneOperator == '-'){
				$mydate = trim($part1) - trim($part2);
			}
		}

		return $mydate;
	}

	static public function display($messages, $type = 'success', $close = false){
		if(empty($messages)) return;
		if(!is_array($messages)) $messages = array($messages);
		if(!ACYSMS_J30){
			echo '<div id="acysms_messages_'.$type.'" class="acysms_messages acysms_'.$type.'"><ul><li>'.implode('</li><li>', $messages).'</li></ul></div>';
		}else{
			echo '<div id="acysms_messages_'.$type.'" class="alert alert-'.$type.' alert-block">';
			if($close) echo '<button type="button" class="close" data-dismiss="alert">×</button>';
			echo '<p>'.implode('</p><p>', $messages).'</p></div>';
		}
	}

	static function enqueueMessage($message, $type = 'success'){
		$result = is_array($message) ? implode('<br/>', $message) : $message;

		$app = JFactory::getApplication();
		if($app->isAdmin()){
			if(ACYSMS_J30){
				$type = str_replace(array('notice', 'message'), array('info', 'success'), $type);
			}else{
				$type = str_replace(array('message', 'notice', 'warning'), array('info', 'warning', 'error'), $type);
			}
			$_SESSION['acysmsmessage'.$type][] = $result;
		}else{
			if(ACYSMS_J30){
				$type = str_replace(array('success', 'info'), array('message', 'notice'), $type);
			}else{
				$type = str_replace(array('success', 'error', 'warning', 'info'), array('message', 'warning', 'notice', 'message'), $type);
			}
			$app->enqueueMessage($result, $type);
		}
	}

	static public function completeLink($link, $popup = false, $redirect = false){
		if($popup) $link .= '&tmpl=component';
		return JRoute::_('index.php?option='.ACYSMS_COMPONENT.'&ctrl='.$link, !$redirect);
	}

	static public function table($name, $component = true){
		$prefix = $component ? ACYSMS_DBPREFIX : '#__';
		return $prefix.$name;
	}


	static public function secureField($fieldName){
		if(!is_string($fieldName) OR preg_match('|[^a-z0-9#_.-]|i', $fieldName) !== 0){
			die('field "'.htmlspecialchars($fieldName, ENT_COMPAT, 'UTF-8').'" not secured');
		}
		return $fieldName;
	}

	static public function setTitle($name, $picture, $link){
		$extra = '';
		$style = '';
		$before = '';
		$after = '';
		if(!JRequest::getInt('hidemainmenu')){
			$config = ACYSMS::config();
			if($config->get('menu_position', 'under') == 'under'){

				$app = JFactory::getApplication();
				$currentTemplate = $app->getTemplate();
				if(ACYSMS_J30 || in_array($currentTemplate, array('rt_missioncontrol', 'aplite', 'adminpraise3'))){
					$newConfig = new stdClass();
					$newConfig->menu_position = 'above';
					$config->save($newConfig);
				}

				$menuHelper = ACYSMS::get('helper.menu');
				$extra = $menuHelper->display($link);
				$style = 'style="line-height:30px;"';
				$before = '<div style="min-height:48px">';
				$after = '</div>';
			}
		}
		JToolBarHelper::title($before.'<a '.$style.' href="'.ACYSMS::completeLink($link).'">'.$name.'</a>'.$extra.$after, $picture.'.png');

		$doc = JFactory::getDocument();
		$doc->setTitle($name);
	}

	static public function displayErrors(){
		error_reporting(E_ALL);
		@ini_set("display_errors", 1);
	}

	static public function &config($reload = false){
		static $configClass = null;
		if($configClass === null || $reload){
			$configClass = ACYSMS::get('class.config');
			$configClass->load();
		}
		return $configClass;
	}

	static public function search($searchString, $object){

		if(empty($object) OR is_numeric($object)) return $object;

		if(is_string($object) OR is_numeric($object)){
			return preg_replace('#('.str_replace('#', '\#', $searchString).')#i', '<span class="searchtext">$1</span>', $object);
		}

		if(is_array($object)){
			foreach($object as $key => $element){
				$object[$key] = ACYSMS::search($searchString, $element);
			}
		}elseif(is_object($object)){
			foreach($object as $key => $element){
				$object->$key = ACYSMS::search($searchString, $element);
			}
		}

		return $object;
	}

	static public function get($path){
		list($group, $class) = explode('.', $path);
		if($class == 'config') $class = 'cpanel';
		$className = 'ACYSMS'.$class.ucfirst($group);
		if(!class_exists($className)) include(constant(strtoupper('ACYSMS_'.$group)).$class.'.php');

		if(!class_exists($className)) return null;
		return new $className();
	}

	static public function getCID($field = ''){
		$oneResult = JRequest::getVar('cid', array(), '', 'array');
		$oneResult = intval(reset($oneResult));
		if(!empty($oneResult) OR empty($field)) return $oneResult;

		$oneResult = JRequest::getVar($field, 0, '', 'int');
		return intval($oneResult);
	}

	static function tooltip($desc, $title = ' ', $image = 'tooltip.png', $name = '', $href = '', $link = 1){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$bootstrap = $config->get('bootstrap_frontend');
		if(ACYSMS_J30 && ($app->isAdmin() || !empty($bootstrap))){
			$class = 'hasTooltip';
			JHtml::_('bootstrap.tooltip');
		}else{
			$class = 'hasTip';
		}
		return JHTML::_('tooltip', str_replace(array("'", "::"), array("&#039;", ": : "), $desc.' '), str_replace(array("'", '::'), array("&#039;", ': : '), $title), $image, str_replace(array("'", '::'), array("&#039;", ': : '), $name.' '), $href, $link, $class);
	}

	static public function increasePerf(){
		@ini_set('max_execution_time', 0);
		@ini_set('pcre.backtrack_limit', 1000000);
	}

	static public function getIntegration($integration = ''){
		$config = ACYSMS::config();
		$idsubmodule = '';


		if(empty($integration)) $integration = $config->get('default_integration');

		if(empty($integration)) return false;

		$fileName = explode('-', $integration);
		if(!is_string($fileName[0]) OR preg_match('|[^a-z0-9#_.-]|i', $fileName[0]) !== 0) return;
		$file = ACYSMS_INTEGRATION.$fileName[0].DIRECTORY_SEPARATOR.'integration.php';
		$className = 'ACYSMSIntegration_'.$fileName[0].'_integration';
		if(!include_once($file)){
			ACYSMS::display('Could not load the integration : '.$file, 'error');
			return false;
		}

		if(!empty($fileName[1])) $idsubmodule = $fileName[1];
		$oneIntegration = new $className($idsubmodule);

		if(empty($oneIntegration)){
			echo 'The integration could not be loaded';
			exit;
		}
		return $oneIntegration;
	}

	static public function bytes($val){
		$val = trim($val);
		if(empty($val)){
			return 0;
		}
		$last = strtolower($val[strlen($val) - 1]);
		switch($last){
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return (int)$val;
	}

	static function frontendLink($link, $popup = false){
		if($popup) $link .= '&tmpl=component';

		$config = ACYSMS::config();
		$app = JFactory::getApplication();
		if(!$app->isAdmin() && $config->get('use_sef', 0)){
			$link = ltrim(JRoute::_($link, false), '/');
		}

		static $mainurl = '';
		static $otherarguments = false;
		if(empty($mainurl)){
			$urls = parse_url(ACYSMS_LIVE);
			if(isset($urls['path']) AND strlen($urls['path']) > 0){
				$mainurl = substr(ACYSMS_LIVE, 0, strrpos(ACYSMS_LIVE, $urls['path'])).'/';
				$otherarguments = trim(str_replace($mainurl, '', ACYSMS_LIVE), '/');
				if(strlen($otherarguments) > 0) $otherarguments .= '/';
			}else{
				$mainurl = ACYSMS_LIVE;
			}
		}

		if($otherarguments AND strpos($link, $otherarguments) === false){
			$link = $otherarguments.$link;
		}

		return $mainurl.$link;
	}

	static function getModuleFormName(){
		static $i = 1;
		return 'formAcySMS'.rand(1000, 9999).$i++;
	}

	static function initModule($includejs, $params){

		static $alreadyThere = false;
		if($alreadyThere && $includejs == 'header') return;

		$alreadyThere = true;

		ACYSMS::initJSStrings($includejs, $params);
		$doc = JFactory::getDocument();
		$config = ACYSMS::config();
		if($includejs == 'header'){
			$doc->addScript(ACYSMS_JS.'acysms_module.js?v='.str_replace('.', '', $config->get('version')));
		}else{
			echo "\n".'<script type="text/javascript" src="'.ACYSMS_JS.'acysms_module.js?v='.str_replace('.', '', $config->get('version')).'" ></script>'."\n";
		}

		$moduleCSS = $config->get('css_module', 'default');
		if(!empty($moduleCSS)){
			if($includejs == 'header'){
				$doc->addStyleSheet(ACYSMS_CSS.'module_'.$moduleCSS.'.css');
			}else{
				echo "\n".'<link rel="stylesheet" href="'.ACYSMS_CSS.'module_'.$moduleCSS.'.css" type="text/css" />'."\n";
			}
		}
	}

	static function initJSStrings($includejs = 'header', $params = null){
		static $alreadyThere = false;
		if($alreadyThere && $includejs == 'header') return;

		$alreadyThere = true;

		$doc = JFactory::getDocument();
		if(method_exists($params, 'get')){
			$nameCaption = $params->get('nametext');
			$phoneCaption = $params->get('phonetext');
		}
		if(empty($nameCaption)) $nameCaption = JText::_('SMS_NAME');
		if(empty($phoneCaption)) $phoneCaption = JText::_('SMS_PHONE');
		$js = "	var acysms = Array();
				acysms['FIRSTNAME_MISSING'] = '".str_replace("'", "\'", JText::_('SMS_FIRSTNAME_MISSING'))."';
				acysms['LAST_MISSING'] = '".str_replace("'", "\'", JText::_('SMS_LASTNAME_MISSING'))."';
				acysms['EMAILCAPTION'] = '".str_replace("'", "\'", $phoneCaption)."';
				acysms['SMS_VALID_PHONE'] = '".str_replace("'", "\'", JText::_('SMS_VALID_PHONE'))."';
				acysms['ACCEPT_TERMS'] = '".str_replace("'", "\'", JText::_('SMS_ACCEPT_TERMS'))."';
				acysms['SMS_NO_GROUP_SELECTED'] = '".str_replace("'", "\'", JText::_('SMS_NO_GROUP_SELECTED'))."';
		";
		if($includejs == 'header'){
			$doc->addScriptDeclaration($js);
		}else{
			echo "<script type=\"text/javascript\">
					<!--
					$js
					//-->
				</script>";
		}
	}

	static function checkRobots(){
		if(preg_match('#(libwww-perl|python)#i', @$_SERVER['HTTP_USER_AGENT'])) die('Not allowed for robots. Please contact us if you are not a robot');
	}

	static function isAllowed($allowedGroups, $groups = null){
		if($allowedGroups == 'all') return true;
		if($allowedGroups == 'none') return false;
		$my = JFactory::getUser();
		if(empty($groups) AND empty($my->id)) return false;
		if(empty($groups)){
			if(!ACYSMS_J16){
				$groups = $my->gid;
			}else{
				jimport('joomla.access.access');
				$groups = JAccess::getGroupsByUser($my->id, false);
			}
		}
		if(!is_array($allowedGroups)) $allowedGroups = explode(',', trim($allowedGroups, ','));
		if(is_array($groups)){
			$inter = array_intersect($groups, $allowedGroups);
			if(empty($inter)) return false;
			return true;
		}else{
			return in_array($groups, $allowedGroups);
		}
	}

	static function removeChzn($eltsToClean){
		if(!ACYSMS_J30) return;

		$js = ' function removeChosen(){';
		foreach($eltsToClean as $elt){
			$js .= 'jQuery("#'.$elt.' .chzn-container").remove();
					jQuery("#'.$elt.' .chzn-done").removeClass("chzn-done").show();
					';
		}
		$js .= '}
		window.addEvent("domready", function(){removeChosen();
			setTimeout(function(){
				removeChosen();
		}, 100);});';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
	}

	static function generateKey($length){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randstring = '';
		$max = strlen($characters) - 1;
		for($i = 0; $i < $length; $i++){
			$randstring .= $characters[mt_rand(0, $max)];
		}
		return $randstring;
	}

	static function setPageTitle($title){
		$app = JFactory::getApplication();
		if(empty($title)){
			$title = $app->getCfg('sitename');
		}elseif($app->getCfg('sitename_pagetitles', 0) == 1){
			$title = JText::sprintf('SMS_JPAGETITLE', $app->getCfg('sitename'), $title);
		}elseif($app->getCfg('sitename_pagetitles', 0) == 2){
			$title = JText::sprintf('SMS_JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$document = JFactory::getDocument();
		$document->setTitle($title);
	}

	static function dispSearch($string, $searchString){
		$secString = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
		if(strlen($searchString) == 0) return $secString;
		return preg_replace('#('.preg_quote($searchString, '#').')#i', '<span class="searchtext">$1</span>', $secString);
	}

	static function perf($name){
		static $previoustime = 0;
		static $previousmemory = 0;
		static $file = '';

		if(empty($file)){
			$file = ACYSMS_ROOT.'acydebug_'.rand().'.txt';
			$previoustime = microtime(true);
			$previousmemory = memory_get_usage();
			file_put_contents($file, "\r\n\r\n-- new test : ".$name." -- ".date('d M H:i:s')." from ".@$_SERVER['REMOTE_ADDR'], FILE_APPEND);
			return;
		}

		$nowtime = microtime(true);
		$totaltime = $nowtime - $previoustime;
		$previoustime = $nowtime;

		$nowmemory = memory_get_usage();
		$totalmemory = $nowmemory - $previousmemory;
		$previousmemory = $nowmemory;

		file_put_contents($file, "\r\n".$name.' : '.number_format($totaltime, 2).'s - '.$totalmemory.' / '.memory_get_usage(), FILE_APPEND);
	}

	static function level($level){
		$config = ACYSMS::config();
		if($config->get($config->get('level'), 0) >= $level) return true;
		return false;
	}

	function fileGetContent($url){
		if(function_exists('file_get_contents')){
			$data = file_get_contents($url);
		}

		if(!$data && function_exists('curl_exec')){
			$conn = curl_init($url);
			curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
			curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($conn);
			curl_close($conn);
		}

		if(!$data && function_exists('fopen') && function_exists('stream_get_contents')){
			$handle = fopen($url, "r");
			$data = stream_get_contents($handle);
		}

		return $data;
	}
}


class ACYSMSController extends acysmsControllerCompat{

	var $pkey = '';
	var $table = '';
	var $groupMap = '';
	var $groupVal = '';

	function __construct($config = array()){
		parent::__construct($config);

		$this->registerDefaultTask('listing');
	}

	function getModel($name = '', $prefix = '', $config = array()){
		return false;
	}

	function listing(){
		if(!empty($this->aclCat) AND !$this->isAllowed($this->aclCat, 'manage')) return;
		JRequest::setVar('layout', 'listing');
		return parent::display();
	}

	function edit(){
		JRequest::setVar('layout', JRequest::getCmd('defaultform', 'form'));
		return parent::display();
	}

	function add(){
		JRequest::setVar('cid', array());
		JRequest::setVar('layout', 'form');
		return parent::display();
	}

	function apply(){
		$this->store();
		return $this->edit();
	}

	function save(){
		$this->store();
		return $this->listing();
	}

	function orderdown(){
		JRequest::checkToken() or die('Invalid Token');

		$orderClass = ACYSMS::get('helper.order');
		$orderClass->pkey = $this->pkey;
		$orderClass->table = $this->table;
		$orderClass->groupMap = $this->groupMap;
		$orderClass->groupVal = $this->groupVal;
		$orderClass->orderingColumnName = $this->orderingColumnName;
		$orderClass->order(true);

		return $this->listing();
	}

	function orderup(){
		JRequest::checkToken() or die('Invalid Token');

		$orderClass = ACYSMS::get('helper.order');
		$orderClass->pkey = $this->pkey;
		$orderClass->table = $this->table;
		$orderClass->groupMap = $this->groupMap;
		$orderClass->groupVal = $this->groupVal;
		$orderClass->orderingColumnName = $this->orderingColumnName;
		$orderClass->order(false);

		return $this->listing();
	}

	function saveorder(){
		JRequest::checkToken() or die('Invalid Token');

		$orderClass = ACYSMS::get('helper.order');
		$orderClass->pkey = $this->pkey;
		$orderClass->table = $this->table;
		$orderClass->groupMap = $this->groupMap;
		$orderClass->groupVal = $this->groupVal;
		$orderClass->orderingColumnName = $this->orderingColumnName;
		$orderClass->save();

		return $this->listing();
	}

	function isAllowed($cat, $action){
		$config = ACYSMS::config();
		if(!ACYSMS::isAllowed($config->get('acl_'.$cat.'_'.$action, 'all'))){
			ACYSMS::display(JText::_('SMS_NOTALLOWED'), 'error');
			return false;
		}
		return true;
	}
}


class ACYSMSClass{
	var $tables = array();

	var $pkey = '';

	var $namekey = '';

	var $errors = array();

	function __construct($config = array()){
		$this->database = JFactory::getDBO();
	}


	function save($element){
		$array_keys = array_keys($this->tables);
		$pkey = $this->pkey;
		if(empty($element->$pkey)){
			$status = $this->database->insertObject(ACYSMS::table(end($array_keys)), $element);
		}else{
			if(count((array)$element) > 1){
				$status = $this->database->updateObject(ACYSMS::table(end($array_keys)), $element, $pkey);
			}else{
				$status = true;
			}
		}

		if($status) return empty($element->$pkey) ? $this->database->insertid() : $element->$pkey;
		return false;
	}

	function delete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}

		if(empty($elements)) return 0;

		foreach($elements as $key => $val){
			$elements[$key] = $this->database->Quote($val);
		}

		if(empty($this->tables) OR empty($elements)) return false;

		$result = true;

		foreach($this->tables as $oneTable => $oneField){
			$query = 'DELETE FROM '.ACYSMS::table($oneTable);
			$query .= ' WHERE '.$oneField.' IN ('.implode(',', $elements).')';
			$this->database->setQuery($query);
			$result = $this->database->query() && $result;
		}

		if(!$result) return false;

		return $this->database->getAffectedRows();
	}
}

ACYSMS::loadLanguage();
$config = ACYSMS::config();

JHTML::_('select.booleanlist', 'acysms');

if(!$config->get('use_https', 0)){
	define('ACYSMS_LIVE', rtrim(str_replace('https:', 'http:', JURI::root()), '/').'/');
}else{
	define('ACYSMS_LIVE', rtrim(str_replace('http:', 'https:', JURI::root()), '/').'/');
}


if(ACYSMS_J30 && ($app->isAdmin() || $config->get('bootstrap_frontend', 0))){
	require(ACYSMS_BACK.'compat'.DS.'bootstrap.php');
}else{
	class JHtmlAcysmsselect extends JHTMLSelect{
	}
}
