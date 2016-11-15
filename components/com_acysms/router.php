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

function AcysmsBuildRoute( &$query )
{
	$segments = array();

	$ctrl = '';
	$task = '';

	if (isset($query['ctrl'])) {
		$ctrl = $query['ctrl'];
		$segments[] = $query['ctrl'];
		unset( $query['ctrl'] );
		if (isset($query['task'])) {
			$task = $query['task'];
			$segments[] = $query['task'];
			unset( $query['task'] );
		}
	}elseif(isset($query['view'])){
		$ctrl = $query['view'];
		$segments[] = $query['view'];
		unset( $query['view'] );
		if(isset($query['layout'])){
			$task = $query['layout'];
			$segments[] = $query['layout'];
			unset( $query['layout'] );
		}
	}

	if(!empty($query)){
		foreach($query as $name => $value){
			if(in_array($name,array('option','Itemid','tmpl'))) continue;

			$segments[] = $name.':'.$value;
			unset($query[$name]);
		}
	}

	return $segments;
}

function AcysmsParseRoute( $segments )
{
	$vars = array();

	if(!empty($segments)){
		$i = 0;
		foreach($segments as $name){
			if(strpos($name,':')){
				list($arg,$val) = explode(':',$name);
				if(is_numeric($arg)) $vars['Itemid'] = $arg;
				else $vars[$arg] = $val;
			}else{
				$i++;
				if($i == 1) $vars['ctrl'] = $name;
				elseif($i == 2) $vars['task'] = $name;
			}
		}
	}

	return $vars;
}
