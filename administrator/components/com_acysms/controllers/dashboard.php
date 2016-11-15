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
class DashboardController extends acysmsController{

	var $aclCat = 'cpanel';


	function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('listing','display');
		$this->registerDefaultTask('listing');
	}
	function display($cachable = false, $urlparams = false){
		return parent::display($cachable, $urlparams);
	}
}
