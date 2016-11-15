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
class CronController extends acysmsController{

	function __construct($config = array()){
		parent::__construct($config);
		$this->registerDefaultTask('cron');
		JRequest::setVar('tmpl','component');
	}

	function cron(){
		header("Content-type:text/html; charset=utf-8");

		echo '<html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" /><title>Cron</title></head><body>';
		$cronHelper = ACYSMS::get('helper.cron');
		$cronHelper->report = true;
		$cronHelper->cron();
		$cronHelper->report();
		echo '</body></html>';
		exit;
	}


}
