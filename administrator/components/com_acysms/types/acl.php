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

class  ACYSMSaclType{
	function  __construct(){

		$acl = JFactory::getACL();
		if(!ACYSMS_J16){
			$this->groups = $acl->get_group_children_tree( null, 'USERS', false );
		}else{
			$db = JFactory::getDBO();
			$db->setQuery('SELECT a.*, a.title as text, a.id as value  FROM #__usergroups AS a ORDER BY a.lft ASC');
			$this->groups = $db->loadObjectList('id');
			foreach($this->groups as $id => $group){
				if(isset($this->groups[$group->parent_id])){
					$this->groups[$id]->level = intval(@$this->groups[$group->parent_id]->level) + 1;
					$this->groups[$id]->text = str_repeat('- - ',$this->groups[$id]->level).$this->groups[$id]->text;
				}
			}
		}
		$this->choice = array();
		$this->choice[] = JHTML::_('select.option','none',JText::_('SMS_NONE'));
		$this->choice[] = JHTML::_('select.option','all',JText::_('SMS_ALL'));
		$this->choice[] = JHTML::_('select.option','special',JText::_('SMS_CUSTOM'));

		$js = "function updateACL(map){
			choice = eval('document.adminForm.choice_'+map);
			choiceValue = 'special';
			for (var i=0; i < choice.length; i++){
				 if(choice[i].checked){
					 choiceValue = choice[i].value;
				}
			}

			hiddenVar = document.getElementById('hidden_'+map);
			if(choiceValue != 'special'){
				hiddenVar.value = choiceValue;
				document.getElementById('div_'+map).style.display = 'none';
			}else{
				document.getElementById('div_'+map).style.display = 'block';
				specialVar = eval('document.adminForm.special_'+map);
				finalValue = ',';
				for (var i=0; i < specialVar.length; i++){
					if(specialVar[i].checked){
							 finalValue += specialVar[i].value+',';
					}
				}
				hiddenVar.value = finalValue;
			}

		}";

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );

	}

	function display($map,$values){
		$simplifiedmap = str_replace(array('[',']'),'_',$map);
		$js ='window.addEvent(\'domready\', function(){ updateACL(\''.$simplifiedmap.'\'); });';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration( $js );


		$choiceValue = ($values == 'none' OR $values == 'all') ?  $values : 'special';
		$return = JHTML::_('acysmsselect.radiolist',   $this->choice, "choice_".$simplifiedmap, 'onclick="updateACL(\''.$simplifiedmap.'\');"', 'value', 'text',$choiceValue);
		$return .= '<input type="hidden" name="'.$map.'" id="hidden_'.$simplifiedmap.'" value="'.htmlspecialchars($values,ENT_COMPAT, 'UTF-8').'"/>';
		$valuesArray = explode(',',$values);
		$listAccess = '<div style="display:none" id="div_'.$simplifiedmap.'"><table class="table">';
		foreach($this->groups as $oneGroup){
			$listAccess .= '<tr><td style="width:20px;">';
			if(ACYSMS_J16 || !in_array($oneGroup->value,array(29,30))) $listAccess .= '<input type="checkbox" onclick="updateACL(\''.$simplifiedmap.'\');" value="'.$oneGroup->value.'" '.(in_array($oneGroup->value,$valuesArray) ? 'checked' : '').' name="special_'.$simplifiedmap.'" id="special_'.$simplifiedmap.'_'.$oneGroup->value.'"/>';
			$listAccess .= '</td><td><label for="special_'.$simplifiedmap.'_'.$oneGroup->value.'">'.$oneGroup->text.'</label></td></tr>';
		}
		$listAccess .= '</table></div>';
		$return .= $listAccess;
		return $return;
	}
}
