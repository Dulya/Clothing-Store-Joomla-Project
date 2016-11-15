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

class UpdateController extends acysmsController{

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('update');
	}

	function listing(){
		return $this->update();
	}

	function install(){
		ACYSMS::increasePerf();

		$newConfig = new stdClass();
		$newConfig->installcomplete = 1;
		$config = ACYSMS::config();
		$config->save($newConfig);
		if(!$config->save($newConfig)){

			echo '<h2>The installation failed, some tables are missing, we will try to create them now...</h2>';

			$queries = file_get_contents(ACYSMS_BACK.'tables.sql');
			$queriesTable = explode("CREATE TABLE", $queries);

			$db = JFactory::getDBO();
			$success = true;
			foreach($queriesTable as $oneQuery){
				$oneQuery = trim($oneQuery);
				if(empty($oneQuery)) continue;
				$db->setQuery("CREATE TABLE ".$oneQuery);
				if(!$db->query()){
					echo '<br /><br /><span style="color:red">Error creating table : '.$db->getErrorMsg().'</span><br />';
					$success = false;
				}else{
					echo '<br /><span style="color:green">Table successfully created</span>';
				}
			}

			if($success){
				echo '<h2>Please install again AcySMS via the Joomla Extensions manager, the tables are now created so the installation will work</h2>';
			}else{
				echo '<h2>Some tables could not be created, please fix the above issues and then install again AcySMS.</h2>';
			}
			return;
		}

		$updateHelper = ACYSMS::get('helper.update');
		$updateHelper->installMenu();
		$updateHelper->installExtensions();
		$updateHelper->fixDoubleExtension();
		$updateHelper->addUpdateSite();
		$updateHelper->fixMenu();
		$updateHelper->installDefaultSenderProfile();
		$updateHelper->installDefaultAnswerTrigger();
		$updateHelper->installDefaultCustomFields();
		$updateHelper->installDefaultOptinMessage();

		$acyToolbar = ACYSMS::get('helper.toolbar');
		$acyToolbar->setTitle('AcySMS', 'dashboard');


		$this->_iframe(ACYSMS_UPDATEURL.'install&fromversion='.JRequest::getCmd('fromversion'));
	}

	function update(){
		$acyToolbar = ACYSMS::get('helper.toolbar');

		$acyToolbar->setTitle(JText::_('SMS_UPDATE_ABOUT'), 'update');

		$acyToolbar->link(ACYSMS::completeLink('dashboard'), JText::_('SMS_CLOSE'), 'cancel');
		$acyToolbar->display();

		return $this->_iframe(ACYSMS_UPDATEURL.'update');
	}

	function _iframe($url){

		$config = ACYSMS::config();
		$url .= '&version='.$config->get('version').'&component=acysms&level='.strtolower($config->get('level'));
		?>
		<div id="acysms_div">
			<iframe allowtransparency="true" scrolling="auto" height="800px" frameborder="0" width="100%" name="acysms_frame" id="acysms_frame" src="<?php echo $url; ?>">
			</iframe>
		</div>
		<?php
	}

	function checkForNewVersion(){

		$config = ACYSMS::config();
		ob_start();
		$url = ACYSMS_UPDATEURL.'loadUserInformation&component=acysms&level='.strtolower($config->get('level', 'express'));
		if($config->get('level', 'express') != 'express') $url .= '&domain='.urlencode(rtrim(ACYSMS_LIVE, '/'));

		$userInformation = ACYSMS::fileGetContent($url);
		$warnings = ob_get_clean();
		$result = (!empty($warnings) && defined('JDEBUG') && JDEBUG) ? $warnings : '';


		if(empty($userInformation)){
			echo json_encode(array('content' => '<br/><span style="color:#C10000;">Could not load your information from our server</span><br/>'.$result));
			exit;
		}

		$decodedInformation = json_decode($userInformation, true);


		$newConfig = new stdClass();
		$newConfig->latestversion = $decodedInformation['latestversion'];
		$newConfig->expirationdate = $decodedInformation['expiration'];
		$newConfig->lastlicensecheck = time();
		$config->save($newConfig);

		$menuHelper = ACYSMS::get('helper.menu');
		$myAcyArea = $menuHelper->myacysmsarea();

		echo json_encode(array('content' => $myAcyArea));
		exit;
	}
}
