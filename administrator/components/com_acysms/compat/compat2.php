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
class acysmsParameter extends JRegistry {

	function get($path, $default = null){
		$value = parent::get($path, 'noval');
		if($value === 'noval') $value = parent::get('data.'.$path,$default);
		return $value;
	}
}
require(dirname(__FILE__).DS.'compat1.php');
