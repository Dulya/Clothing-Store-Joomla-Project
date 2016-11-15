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
if(empty($my->id)) die('You can not have access to this page');

include(ACYSMS_BACK.'controllers'.DS.'cpanel.php');

class FrontCpanelController extends CpanelController{}
