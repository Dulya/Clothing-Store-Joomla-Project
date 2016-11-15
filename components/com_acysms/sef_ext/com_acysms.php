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


defined( '_JEXEC' ) or die( 'Direct Access to this location is not allowed.' );

if(!class_exists('Sh404sefFactory')){
	$dosef = false;
	return;
}

global $sh_LANG;
$sefConfig = & Sh404sefFactory::getConfig();
$shLangName = '';
$shLangIso = '';
$shItemidString = '';

$dosef =  false;
	return;
