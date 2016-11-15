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

class ACYSMSintegrationType{

	var $js = '';
	var $allIntegration = false;
	var $integration = array();
	var $hideIfOnlyOneEntry = false;

	var $defaultIntegrationOnly = false;

	function load(){
		jimport('joomla.filesystem.folder');
		$dirs = JFolder::folders(ACYSMS_INTEGRATION);

		if($this->defaultIntegrationOnly){
			$integration = ACYSMS::getIntegration();
			$integrationNames = $integration->getNames();
			$this->integration[] = JHTML::_('select.option', $integrationNames[0]->value, $integrationNames[0]->text, 'value', 'text');
			return;
		}

		foreach($dirs as $oneDir){
			if($oneDir == 'default') continue;

			$oneIntegration = ACYSMS::getIntegration($oneDir);
			if(!($oneIntegration->isPresent())) continue;

			$integrationNames = $oneIntegration->getNames();

			foreach($integrationNames as $oneIntegrationName){
				$this->integration[] = JHTML::_('select.option', $oneIntegrationName->value, $oneIntegrationName->text, 'value', 'text');
			}
		}
	}

	function display($map, $value){
		$style = '';
		if(count($this->integration) == 1 && $this->hideIfOnlyOneEntry) $style = 'display:none';
		if($this->allIntegration == true) array_unshift($this->integration, JHTML::_('select.option', '', JText::_('SMS_ALL_INTEGRATIONS')));
		return JHTML::_('select.genericlist', $this->integration, $map, ' class="inputbox" style="width:auto;min-width:100px;'.$style.'" size="1" '.$this->js, 'value', 'text', $value, 'selectedIntegration');
	}
}
