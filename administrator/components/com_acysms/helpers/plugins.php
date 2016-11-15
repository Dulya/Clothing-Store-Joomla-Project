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

class ACYSMSpluginsHelper{

	function formatString(&$replaceme, $mytag){
		if(!empty($mytag->part)){
			$parts = explode(' ', $replaceme);
			if($mytag->part == 'last'){
				$replaceme = count($parts) > 1 ? end($parts) : '';
			}else{
				$replaceme = reset($parts);
			}
		}

		if(!empty($mytag->type)){
			if(empty($mytag->format)) $mytag->format = JText::_('DATE_FORMAT_LC3');
			if($mytag->type == 'date'){
				$replaceme = ACYSMS::getDate(ACYSMS::getTime($replaceme), $mytag->format);
			}elseif($mytag->type == 'time'){
				$replaceme = ACYSMS::getDate($replaceme, $mytag->format);
			}elseif($mytag->type == 'diff'){
				try{
					$date = $replaceme;
					if(is_numeric($date)) $date = ACYSMS::getDate($replaceme, '%Y-%m-%d %H:%M:%S');
					$dateObj = new DateTime($date);
					$nowObj = new DateTime();
					$diff = $dateObj->diff($nowObj);
					$replaceme = $diff->format($mytag->format);
				}catch(Exception $e){
					$replaceme = 'Error using the "diff" parameter in your tag. Please make sure the DateTime() and diff() functions are available on your server.';
				}
			}
		}

		if(!empty($mytag->lower)) $replaceme = strtolower($replaceme);
		if(!empty($mytag->upper)) $replaceme = strtoupper($replaceme);
		if(!empty($mytag->ucwords)) $replaceme = ucwords($replaceme);
		if(!empty($mytag->ucfirst)) $replaceme = ucfirst($replaceme);
		if(!empty($mytag->urlencode)) $replaceme = urlencode($replaceme);
		if(!empty($replaceme)){
			if(!empty($mytag->nb_format) && is_numeric($mytag->nb_format)){
				$replaceme = number_format($replaceme, $mytag->nb_format);
			}else if(!empty($mytag->nb_format)) $replaceme = number_format($replaceme, 2);
		}
	}

	function extractTags(&$message, $tagfamily){

		$match = '#(?:{|%7B)'.$tagfamily.':(.*)(?:}|%7D)#Ui';

		if(empty($message->message_body)) return array();
		if(!preg_match_all($match, $message->message_body, $results)) return array();

		$tags = array();

		foreach($results[0] as $i => $oneTag){
			if(isset($tags[$oneTag])) continue;
			$tags[$oneTag] = $this->extractTag($results[1][$i]);
		}

		return $tags;
	}

	function extractTag($oneTag){
		$arguments = explode('|', strip_tags($oneTag));
		$tag = new stdClass();
		$tag->id = $arguments[0];
		$tag->default = '';
		for($i = 1, $a = count($arguments); $i < $a; $i++){
			$args = explode(':', $arguments[$i]);
			$arg0 = trim($args[0]);
			if(empty($arg0)) continue;
			if(isset($args[1])){
				$tag->$arg0 = $args[1];
				if(isset($args[2])) $tag->$args[0] .= ':'.$args[2];
			}else{
				$tag->$arg0 = true;
			}
		}
		return $tag;
	}



	public function allowSendByGroups($filterType){
		$ableToSend = false;
		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

		if(empty($frontEndFilters)) return false;

		foreach($frontEndFilters as $oneCondition){
			if($oneCondition['filters'] != $filterType) continue;

			if(empty($oneCondition['typeDetails'])) continue;

			if($oneCondition['typeDetails'] == 'all') return true;

			$my = JFactory::getUser();
			$query = 'SELECT user_id FROM #__user_usergroup_map WHERE group_id = '.intval($oneCondition['typeDetails']).' AND user_id = '.intval($my->id);
			$db->setQuery($query);
			$result = $db->loadResult();
			if(empty($result)) continue;
			$ableToSend = true;
			break;
		}
		return $ableToSend;
	}
}
