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

defined('_JEXEC') or die('Restricted access');

class plgAcySMSManagetext extends JPlugin{
	var $foundtags = array();

	function __construct(&$subject, $config){
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'managetext');
			$this->params = new JParameter($plugin->params);
		}
	}

	function onACYSMSReplaceTags(&$message, $send = true){
		$this->_replaceConstant($message);
	}

	private function _replaceConstant(&$message){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'trans');

		if(!$tags) return;

		$jconfig = JFactory::getConfig();
		foreach($tags as $tagString => $oneTag){
			$tags[$tagString] = JText::_($oneTag->id);
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}
}//endclass
