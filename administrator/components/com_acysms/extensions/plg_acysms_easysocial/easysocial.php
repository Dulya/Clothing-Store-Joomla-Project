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

class plgAcysmsEasysocial extends JPlugin{

	var $sendervalues = array();

	var $debug = false;

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_easysocial')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'easysocial');
			$this->params = new acysmsParameter($plugin->params);
		}
		$lang = JFactory::getLanguage();
		$lang->load('com_easysocial', JPATH_SITE);
		$lang->load('com_easysocial', JPATH_ADMINISTRATOR);
	}


	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();

		$js = "function displayFieldsFilter(fct, element, num, extra){";
		$ctrl = 'message';
		if(!$app->isAdmin()) $ctrl = 'frontmessage';
		$js .= "
					try{
						var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$ctrl."&task=displayFieldsFilter&fct='+fct+'&num='+num+'&'+extra,{
							method: 'get',
							update: document.getElementById(element)
						}).request();

					}catch(err){
						new Request({
							url:'index.php?option=com_acysms&tmpl=component&ctrl=".$ctrl."&task=displayFieldsFilter&fct='+fct+'&num='+num+'&'+extra,
							method: 'get',
							onSuccess: function(responseText, responseXML) {
								document.getElementById(element).innerHTML = responseText;
							}
						}).send();
					}
				}";
		$ctrl = 'message';
		if(!$app->isAdmin()) $ctrl = 'frontmessage';

		$js .= "function displayFieldsFilterValues(num, map){
					var operator = document.getElementById('easysocialoperator_'+num).value;
					try{
						var ajaxCall = new Ajax('index.php?option=com_acysms&tmpl=component&ctrl=".$ctrl."&task=displayFieldsFilterValues&map='+map+'&num='+num+'&operator='+operator+'&fieldsIntegration=easysocialField',{
							method: 'get',
							update: document.getElementById('valueToChange_'+num+'_value')
						}).request();

					}catch(err){
						new Request({
							url:'index.php?option=com_acysms&tmpl=component&ctrl=".$ctrl."&task=displayFieldsFilterValues&map='+map+'&num='+num+'&operator='+operator+'&fieldsIntegration=easysocialField',
							method: 'get',
							onSuccess: function(responseText, responseXML) {
								document.getElementById('valueToChange_'+num+'_value').innerHTML = responseText;
							}
						}).send();
					}
				}";
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$helperPlugin = ACYSMS::get('helper.plugins');
		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'EasySocial');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('easysocialfields'))) $filters['communityFilters']['easysocialfield'] = $newFilter;

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_X_GROUPS', 'EasySocial');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('easysocialgroups'))) $filters['communityFilters']['easysocialgroups'] = $newFilter;

		if(!$app->isAdmin()){
			$helperPlugin = ACYSMS::get('helper.plugins');
			if(!$helperPlugin->allowSendByGroups('easysocial')) return;
		}

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_X_SUBSCRIBERS', 'EasySocial Events');
		$filters['eventFilters']['easysocialevents'] = $newFilter;
	}





	function onACYSMSGetTags(&$tags){

		$lang = JFactory::getLanguage();
		$return = $lang->load('com_easysocial', JPATH_ADMINISTRATOR);
		$easyProfiles = array();
		$easyProfileData = array();
		$db = JFactory::getDBO();
		$previousProfile = '';
		$finalContent = '';
		$content = array();

		$tags['communityTags']['easysocial'] = new stdClass();
		$tags['communityTags']['easysocial']->name = JText::sprintf('SMS_X_USER_INFO', 'EasySocial');

		$query = 'SELECT socialfields.title AS "name", socialfields.id AS "id", socialprofiles.id AS "profileid", socialprofiles.title AS "profile"
				FROM #__social_fields AS socialfields
				JOIN #__social_fields_steps AS socialfieldssteps ON socialfields.step_id = socialfieldssteps.id
				JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
				WHERE socialfields.unique_key NOT LIKE "'.implode('%" AND socialfields.unique_key NOT LIKE "', array("JOOMLA_", "HEADER", "SEPARATOR", "TERMS", "COVER", "AVATAR")).'%"
				ORDER BY socialprofiles.title
				';

		$db->setQuery($query);
		$easySocialFields = $db->loadObjectList();
		$k = 0;


		foreach($easySocialFields as $oneField){
			if(empty($oneField->profile) || empty($oneField->profileid)) continue;
			if(!in_array($oneField->profile, $easyProfiles)) $easyProfiles[$oneField->profileid] = $oneField->profile;

			$style = 'style="display:none"';
			if(empty($previousProfile)){
				$previousProfile = $oneField->profileid;
				$style = 'style="display:table"';
			}

			if(empty($content[$oneField->profileid])) $content[$oneField->profileid] = '<table class="adminlist table table-striped table-hover" id="easySocialId_'.$oneField->profileid.'" cellpadding="1" width="100%" '.$style.'><tbody>';
			$content[$oneField->profileid] .= '<tr style="cursor:pointer" onclick="insertTag(\'{easysocial:'.JText::_($oneField->id).'}\')" class="row'.$k.'"><td>'.JText::_($oneField->name).'</td></tr>';

			if($oneField->profileid != $previousProfile){
				if(!empty($content[$oneField->profileid])) $content[$previousProfile] .= '</tbody></table>';
				$previousProfile = $oneField->profileid;
			}
			$k = 1 - $k;
		}

		$content[$previousProfile] .= '</tbody></table>';

		foreach($easyProfiles as $oneProfileId => $oneProfileTitle) $easyProfileData[] = JHTML::_('select.option', $oneProfileId, $oneProfileTitle);

		$profileSelection = JHTML::_('select.genericlist', $easyProfileData, '', 'onchange="(displayTags(this.value))"', 'value', 'text');

		$script = '
				function displayTags(profileId){
					tables = document.getElementById("easySocialDiv").getElementsByTagName("table");
					for(var i=0; i < tables.length; i++) {
						document.getElementById(tables[i].id).style.display = "none";
					}
					document.getElementById("easySocialId_"+profileId).style.display = "table";
				}';
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($script);

		$tags['communityTags']['easysocial']->content = $profileSelection;
		$tags['communityTags']['easysocial']->content .= '<div id="easySocialDiv">'.implode($content, '').'</div>';
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$helperPlugin = ACYSMS::get('helper.plugins');
		if(empty($user)) return;

		if(!file_exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_easysocial'.DS.'includes'.DS.'foundry.php')){
			ACYSMS::enqueueMessage('You must update your EasySocial component to include user fields', 'warning');
			return;
		}

		include_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_easysocial'.DS.'includes'.DS.'foundry.php');

		if(empty($user->joomla)) return;
		$receiver = Foundry::user($user->joomla->id);

		$db = JFactory::getDBO();
		$lang = JFactory::getLanguage();
		$lang->load('com_easysocial', JPATH_SITE);

		$messageTags = $helperPlugin->extractTags($message, 'easysocial');
		if(empty($messageTags)) return;

		if(empty($user->joomla->id)){
			ACYSMS::enqueueMessage('No user id found', 'warning');
			return;
		}

		foreach($messageTags as $oneTag => $parameter){
			$db->setQuery('SELECT unique_key FROM #__social_fields WHERE id = '.intval($parameter->id));
			$uniqueKey = $db->loadResult();
			$fieldValue = $receiver->getFieldValue($uniqueKey);

			if(empty($fieldValue)){
				$tags[$oneTag] = '';
				continue;
			}

			if(is_string($fieldValue)){
				$tags[$oneTag] = $fieldValue;
				continue;
			}

			if(!empty($fieldValue->value) && is_string($fieldValue->value)){
				if(strstr($fieldValue->unique_key, 'BOOLEAN')){
					$tags[$oneTag] = JText::_(empty($fieldValue->value) ? 'JOOMEXT_NO' : 'JOOMEXT_YES');
				}elseif(strstr($fieldValue->unique_key, 'RELATIONSHIP')){
					$tags[$oneTag] = json_decode($fieldValue->value)->type;
				}elseif(strstr($fieldValue->unique_key, 'COUNTRY')){
					$tags[$oneTag] = implode(', ', json_decode($fieldValue->value));
				}else{
					$tags[$oneTag] = $fieldValue->value;
				}
			}elseif(is_object($fieldValue->value)){
				$arrayValue = (array)$fieldValue->value;
				if(!empty($arrayValue['day'])){
					$tags[$oneTag] = ACYSMS::getDate(ACYSMS::getTime($arrayValue['year'].'-'.$arrayValue['month'].'-'.$arrayValue['day'].' 00:00:00'), JText::_('DATE_FORMAT_LC'));
				}elseif(!empty($arrayValue['address1'])){
					$tags[$oneTag] = $arrayValue['address1'].', '.$arrayValue['zip'].' '.$arrayValue['city'].', '.$arrayValue['country'];
				}elseif(!empty($arrayValue['text'])){
					$tags[$oneTag] = JText::_($arrayValue['text']);
				}else{
					$tags[$oneTag] = implode(', ', $arrayValue);
				}
			}elseif(is_array($fieldValue->value)){
				$tags[$oneTag] = implode(', ', $fieldValue->value);
			}else{
				$tags[$oneTag] = '';
			}
		}
		if(!empty($tags)){
			$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
		}
	}







	function onACYSMSDisplayFilterParams_easysocialField($message){
		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, title FROM #__social_profiles');
		$allProfiles = $db->loadObjectList();
		$profiles = array();
		$profiles[] = JHTML::_('select.option', 0, '- - -');
		foreach($allProfiles as $oneProfile){
			$profiles[] = JHTML::_('select.option', $oneProfile->id, JText::_($oneProfile->title));
		}

		$relation = array();
		$relation[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');
		?>
		<span id="countresult_easysocialField"></span>
		<?php

		for($i = 0; $i < 5; $i++){
			$jsOnChange = "displayFieldsFilter('displayFields_easysocialField', 'maptoChange_".$i."', $i,'profile='+document.getElementById('selectedProfile_".$i."').value); ";
			$return = '<div id="filter_'.$i.'_easysocialfields">'.JHTML::_('select.genericlist', $profiles, "data[message][message_receiver][standard][easysocial][easysocialfield][".$i."][profile]", 'onchange="'.$jsOnChange.'"countresults(\'easysocialField\')" class="inputbox" size="1"', 'value', 'text', '', 'selectedProfile_'.$i);
			$return .= '<span id="maptoChange_'.$i.'"><input onchange="countresults(\'easysocialField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easysocial][easysocialfield]['.$i.'][map]" style="width:200px" value="" id="filter'.$i.'easysocialfieldsmap"/></span>';
			$operators->extra = 'onchange="displayFieldsFilterValues('.$i.', document.getElementById(\'easysocialmap_'.$i.'\').value);setTimeout(function(){countresults(\'easysocialField\');}, 1000);"';
			$operators->id = 'easysocialoperator_'.$i;
			$return .= ' '.$operators->display("data[message][message_receiver][standard][easysocial][easysocialfield][".$i."][operator]").' <span id="valueToChange_'.$i.'_value"><input onchange="countresults(\'easysocialField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easysocial][easysocialfield]['.$i.'][value]" style="width:200px" value="" id="filter'.$i.'easysocialfieldsvalue"></span></div>';
			if($i != 4) $return .= JHTML::_('select.genericlist', $relation, "data[message][message_receiver][standard][easysocial][easysocialfield][".$i."][relation]", 'onchange="countresults(\'easysocialField\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
			echo $return;
		}
	}

	function onAcySMSDisplayFields_easysocialField(){
		$num = JRequest::getInt('num');
		$profile = JRequest::getString('profile');

		if(empty($profile)) return '<input onchange="countresults(\'easysocialField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easysocial][easysocialfield]['.$num.'][map]" style="width:200px" value="" id="filter'.$num.'easysocialfieldsmap"/>';

		$lang = JFactory::getLanguage();
		$lang->load('com_easysocial', JPATH_ADMINISTRATOR);

		$db = JFactory::getDBO();
		$db->setQuery('SELECT socialfields.id, socialfields.title FROM #__social_fields AS socialfields JOIN #__social_fields_steps AS socialfieldssteps ON socialfields.step_id = socialfieldssteps.id WHERE socialfieldssteps.uid = '.intval($profile).' AND socialfields.unique_key NOT LIKE "'.implode('%" AND socialfields.unique_key NOT LIKE "', array("JOOMLA_", "HEADER", "SEPARATOR", "TERMS", "COVER", "AVATAR")).'%"');
		$fields = $db->loadObjectList();
		$list = array();
		$list[] = JHTML::_('select.option', 0, '- - -');
		foreach($fields as $field){
			$list[] = JHTML::_('select.option', $field->id, JText::_($field->title));
		}

		return JHTML::_('select.genericlist', $list, "data[message][message_receiver][standard][easysocial][easysocialfield][".$num."][map] ", 'onchange="displayFieldsFilterValues('.$num.', this.value);setTimeout(function(){countresults(\'easysocialField\');}, 1000);" class="inputbox" size="1"', 'value', 'text', '', 'easysocialmap_'.$num);
	}

	function onAcySMSdisplayFieldsFilterValues_easysocialField(){
		$num = JRequest::getInt('num');
		$map = JRequest::getString('map');
		$cond = JRequest::getString('operator');
		$value = JRequest::getString('value');

		$emptyInputReturn = '<input onchange="countresults(\'easysocialField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][easysocial][easysocialfield]['.$num.'][value]" id="filter'.$num.'acymailingfieldvalue" style="width:200px" value="'.$value.'">';

		if(empty($map) || !in_array($cond, array('=', '!='))) return $emptyInputReturn;

		$db = JFactory::getDBO();
		$query = 'SELECT DISTINCT `raw` AS value
				FROM #__social_fields_data
				WHERE field_id = '.intval($map).'
				LIMIT 100';

		$db->setQuery($query);
		$prop = $db->loadObjectList();

		if(empty($prop) || count($prop) >= 100 || (count($prop) == 1 && (empty($prop[0]->value) || $prop[0]->value == '-'))) return $emptyInputReturn;

		return JHTML::_('select.genericlist', $prop, "data[message][message_receiver][standard][easysocial][easysocialfield][".$num."][value]", 'onchange="countresults(\'easysocialField\')" class="inputbox" size="1" style="width:200px"', 'value', 'value', $value, 'filter'.$num.'acysmsfieldvalue');
	}

	function onACYSMSSelectData_easysocialField(&$acyquery, $message){

		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['easysocial']['easysocialfield'])) return;
		if(!isset($acyquery->join['socialfieldsvalue']) && $integration->componentName != 'easysocial') $acyquery->join['socialfieldsvalue'] = 'LEFT JOIN #__social_fields_data AS socialfieldsvalue ON socialfieldsvalue.uid = joomusers.id';
		$addCondition = '';
		$whereConditions = '';
		$i = 0;
		foreach($message->message_receiver['standard']['easysocial']['easysocialfield'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			$i++;
			$acyquery->join['socialfieldsvalue'.$i] = 'JOIN #__social_fields_data AS socialfieldsvalue_'.$i.' ON socialfieldsvalue_'.$i.'.uid = joomusers.id';
			if(!empty($addCondition)) $whereConditions = '  ('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])){
				$addCondition = $oneFilter['relation'];
			}else  $addCondition = 'AND';
			$whereConditions .= $acyquery->convertQuery('socialfieldsvalue_'.$i, 'raw', $oneFilter['operator'], $oneFilter['value']).' AND '.$acyquery->convertQuery('socialfieldsvalue_'.$i, 'field_id', '=', $oneFilter['map']);
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}


	function onACYSMSDisplayFilterParams_easysocialgroups($message){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		$config = ACYSMS::config();

		$my = JFactory::getUser();
		if(!ACYSMS_J16){
			$myJoomlaGroups = array($my->gid);
		}else{
			jimport('joomla.access.access');
			$myJoomlaGroups = JAccess::getGroupsByUser($my->id, false);
		}

		$allowCustomerManagement = $config->get('allowCustomerManagement');

		if(!$app->isAdmin()){
			$frontEndFilters = $config->get('frontEndFilters');
			if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

			$availableLists = array();
			foreach($frontEndFilters as $oneCondition){
				if($oneCondition['filters'] != 'easysocialgroups') continue;
				if(empty($oneCondition['filterDetails']) || empty($oneCondition['filterDetails']['easysocialgroups'])) continue;
				if($oneCondition['typeDetails'] != 'all' && !in_array($oneCondition['typeDetails'], $myJoomlaGroups)) continue;
				$availableLists = array_merge($availableLists, $oneCondition['filterDetails']['easysocialgroups']);
			}
			if(empty($availableLists)) return;

			if(in_array('userownlists', $availableLists)){
				$currentUser = JFactory::getUser();
				$db->setQuery('SELECT id FROM #__social_clusters WHERE cluster_type ="group" AND creator_uid ='.intval($currentUser->id));
				$groupsForCurrentUser = $db->loadObjectList();
				foreach($groupsForCurrentUser as $oneGroup){
					$availableLists[] = $oneGroup->id;
				}
			}
		}


		$db->setQuery('SELECT id, title FROM #__social_clusters WHERE cluster_type ="group"');
		$groups = $db->loadObjectList();

		echo JText::sprintf('SMS_SEND_X_GROUPS', 'EasySocial').' : <br />';
		foreach($groups as $oneGroup){
			if(!$app->isAdmin()){
				if(!in_array($oneGroup->id, $availableLists)) continue;
			} ?>
			<label><input type="checkbox" name="data[message][message_receiver][standard][acysms][easysocialgroups][<?php echo $oneGroup->id; ?>]" value="<?php echo $oneGroup->id ?>" title="<?php echo $oneGroup->title ?>"/> <?php echo $oneGroup->title ?></label><br/>
		<?php }
	}

	function onACYSMSSelectData_easysocialGroups(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['acysms']['easysocialgroups'])) return;
		if(!isset($acyquery->join['easysocialgroups']) && $message->message_receiver_table != 'easysocial') $acyquery->join['easysocialgroups'] = 'LEFT JOIN #__social_clusters_nodes AS socialclustersnodes ON socialclustersnodes.uid = joomusers.id';

		JArrayHelper::toInteger($message->message_receiver['standard']['acysms']['easysocialgroups']);

		$acyquery->where[] = ' socialclustersnodes.cluster_id IN ('.implode(',', ($message->message_receiver['standard']['acysms']['easysocialgroups'])).') AND socialclustersnodes.state = 1';
	}



	private function loadScript($tagName, $nextEasySocialEventsParameter){
		?>
		<script language="javascript" type="text/javascript">
			var selectedContents = new Array();

			function addEventTag(contentid, rowClass){
				var tag = "";
				var otherinfo = "";
				var type = "";

				var tmp = selectedContents.indexOf(contentid)
				if(tmp != -1){
					window.document.getElementById("content" + contentid).className = rowClass;
					delete selectedContents[tmp];
				}else{
					window.document.getElementById("content" + contentid).className = "selectedrow";
					selectedContents.push(contentid);
				}

				for(i = 0; i < window.parent.document.getElementsByName("typeofinsert_easysocialevents").length; i++){
					if(window.parent.document.getElementsByName("typeofinsert_easysocialevents").item(i).checked){
						type += window.parent.document.getElementsByName("typeofinsert_easysocialevents").item(i).value + ",";
					}
				}
				if(type) otherinfo += "| type:" + type;
				var wrapNumber;

				<?php if($tagName != 'EasySocialEvents') echo ' wrapNumber = window.parent.document.getElementById("nbEvent_easysocialevents").value;'; ?>

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i)){
						if(wrapNumber)
							tag = tag + "{" + "<?php echo $tagName; ?>:" + wrapNumber + "| " + "<?php echo $nextEasySocialEventsParameter; ?>:" + selectedContents[i] + otherinfo + "}";else
							tag = tag + "{" + "<?php echo $tagName; ?>:" + selectedContents[i] + otherinfo + "}";
					}
				}
				window.document.getElementById("tagstring").value = tag;
			}

			var selectedContents = new Array();
			var selectedContentsName = new Array();
			function addSelectedEventAutoMessage(){
				var selectedEvent = "";
				var selectedEventId = "";
				var form = document.adminForm;
				for(i = 0; i <= form.length - 1; i++){
					if(form[i].type == 'checkbox'){

						if(!document.getElementById("eventId" + form[i].id)) continue;
						if(document.getElementById("eventId" + form[i].id).innerHTML.length == 0) continue;
						oneEventId = document.getElementById("eventId" + form[i].id).innerHTML.trim();

						productId = "eventId" + form[i].id
						if(!document.getElementById("eventName" + form[i].id)) continue;
						if(document.getElementById("eventName" + form[i].id).innerHTML.length == 0) continue;
						oneEvent = document.getElementById("eventName" + form[i].id).innerHTML;

						var tmp = selectedContents.indexOf(oneEventId);
						if(tmp != -1 && form[i].checked == false){
							delete selectedContents[tmp];
							delete selectedContentsName[tmp];
						}else if(tmp == -1 && form[i].checked == true){
							selectedContents.push(oneEventId);
							selectedContentsName.push(oneEvent);
						}
					}
				}

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i))    selectedEventId += selectedContents[i].trim() + ",";
					if(selectedContentsName[i] && !isNaN(i))    selectedEvent += " " + selectedContentsName[i].trim() + " , ";
				}

				window.document.getElementById("eventSelected").value = selectedEventId;
				window.document.getElementById("eventDisplayed").value = selectedEvent;
			}

			function addSelectedCategory(categoryId, categoryTitle, idToUpdate){
				parent.window.document.getElementById("selected" + idToUpdate).value = categoryId;
				parent.window.document.getElementById("displayed" + idToUpdate).innerHTML = categoryTitle;
				parent.window.document.getElementById("hidden" + idToUpdate).value = categoryTitle;

				acysms_js.closeBox(true);
			}

			function confirmEventSelection(idToUpdate){

				selected = window.document.getElementById("eventSelected").value;
				displayed = window.document.getElementById("eventDisplayed").value;

				parent.window.document.getElementById("selected" + idToUpdate).value = selected.substring(0, selected.length - 1);

				parent.window.document.getElementById("displayed" + idToUpdate).innerHTML = displayed.substring(1, displayed.length - 3);
				parent.window.document.getElementById("hidden" + idToUpdate).value = displayed.substring(1, displayed.length - 3);


				acysms_js.closeBox(true);
			}
		</script>

	<?php }


	function onACYSMSDisplayFilterParams_easySocialEvents($message){

		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$ctrl = 'fronttag';
		}else    $ctrl = 'tag';

		$eventSelection = array();
		$eventSelection[] = JHTML::_('select.option', 'AND', JText::_('SMS_ALL_THESE_EVENTS'));
		$eventSelection[] = JHTML::_('select.option', 'OR', JText::_('SMS_AT_LEAST_ONE_OF_THESE_EVENTS'));
		$nbEventSelectionDropDown = JHTML::_('select.genericlist', $eventSelection, "data[message][message_receiver][standard][easysocialevents][events][eventSelection]", 'onclick="document.getElementById(\'sms_eventFilterEvent\').checked = \'checked\'"', 'value', 'text', 'AND', 'nbEvent');

		$conditionsData = array();
		$conditionsData[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$conditionsData[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$subscriptionStatus = array('' => ' - - - ', '0' => 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_INVITED', '1' => 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_GOING', '2' => 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_PENDING', '3' => 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_MAYBE', '4' => 'COM_EASYSOCIAL_PAGE_TITLE_EVENTS_FILTER_NOTGOING');

		$subscriptionData = array();
		foreach($subscriptionStatus as $oneSubscription => $oneJText) $subscriptionData[] = JHTML::_('select.option', $oneSubscription, JText::_($oneJText));

		$eventName = '';
		if(!empty($message->message_receiver['standard']['easysocialevents']['events']['eventsName'])) $eventName = $message->message_receiver['standard']['easysocialevents']['events']['eventsName'];

		$categoryName = '';
		if(!empty($message->message_receiver['standard']['easysocialevents']['category']['categoryName'])) $categoryName = $message->message_receiver['standard']['easysocialevents']['category']['categoryName'];

		$filterString = '';
		for($i = 0; $i < 3; $i++){
			$subscriptionStatusDropDown = JHTML::_('select.genericlist', $subscriptionData, 'data[message][message_receiver][standard][easysocialevents][subscriptionFilters]['.$i.'][status]', '', 'value', 'text', '');
			$conditionDropDown = JHTML::_('select.genericlist', $conditionsData, 'data[message][message_receiver][standard][easysocialevents][subscriptionFilters]['.$i.'][condition]', '', 'value', 'text', 'AND', 'nbEvent');
			$filterString .= $subscriptionStatusDropDown;
			if($i < 2) $filterString .= $conditionDropDown;
		}

		$eventChecked = '';
		$categoryChecked = '';
		$autoMsgChecked = '';
		if(empty($message->message_receiver['standard']['easysocialevents']['eventFilter']) || $message->message_receiver['standard']['easysocialevents']['eventFilter'] == 'events') $eventChecked = 'checked="checked"';
		if(!empty($message->message_receiver['standard']['easysocialevents']['eventFilter']) && $message->message_receiver['standard']['easysocialevents']['eventFilter'] == 'category') $categoryChecked = 'checked="checked"';
		if(!empty($message->message_receiver['standard']['easysocialevents']['eventFilter']) && $message->message_receiver['standard']['easysocialevents']['eventFilter'] == 'autoMsg') $autoMsgChecked = 'checked="checked"';

		echo JText::sprintf('SMS_SEND_TO_USERS_WHICH_ARE', $filterString);
		echo '<br /><input type="radio" name="data[message][message_receiver][standard][easysocialevents][eventFilter]" value="events" id="sms_eventFilterEvent_easySocialEvents" '.$eventChecked.'/>
		<label for="sms_eventFilterEvent_easySocialEvents">'.JText::sprintf('SMS_ASSIGNED_TO', '</label>'.$nbEventSelectionDropDown);

		echo JText::_('SMS_SELECT_EVENT').' :  <span id="displayedeasySocialEvents">'.$eventName.'</span><a class="modal" style="cursor:pointer" onclick="document.getElementById(\'sms_eventFilterEvent_easySocialEvents\').checked = \'checked\';window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=easysocial&fctName=chooseEvent_easySocialEvents&tmpl=component&idToUpdate=easySocialEvents\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';
		echo '<input type="radio" name="data[message][message_receiver][standard][easysocialevents][eventFilter]" value="category" id="sms_eventFilterCategory_easySocialEvents" '.$categoryChecked.'/> <label for="sms_eventFilterCategory_easySocialEvents">'.JText::_('SMS_ASSIGNED_TO_EVENT_FROM_CATEGORY').' : <span id="displayedeasySocialEventsCategory"/>'.$categoryName.'</span><a class="modal" style="cursor:pointer" onclick="document.getElementById(\'sms_eventFilterCategory_easySocialEvents\').checked = \'checked\'; window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=easysocial&fctName=chooseCategoryEvents_easysocialevents&tmpl=component&context=filters&idToUpdate=easySocialEventsCategory\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';
		echo '<input type="radio" name="data[message][message_receiver][standard][easysocialevents][eventFilter]" value="autoMsg" id="sms_eventFilterAutoMsg_easySocialEvents" '.$autoMsgChecked.'/> <label for="sms_eventFilterAutoMsg_easySocialEvents">'.JText::_('SMS_ASSIGNED_TO_EVENT_CHOSEN_AUTO_MESSAGE_OPTIONS').'</label><br />';

		echo '<input type="hidden" name="data[message][message_receiver][standard][easysocialevents][category][categoryId]" id="selectedeasySocialEventsCategory"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][easysocialevents][category][categoryName]" id="hiddeneasySocialEventsCategory"/>';
		echo '<input type="hidden" name="data[message][message_receiver][standard][easysocialevents][events][eventsId]" id="selectedeasySocialEvents"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][easysocialevents][events][eventsName]" id="hiddeneasySocialEvents"/>';
	}

	function onACYSMSSelectData_easySocialEvents(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['easysocialevents'])) return;
		if(empty($message->message_receiver['standard']['easysocialevents']['eventFilter'])) return;
		if(empty($message->message_receiver['standard']['easysocialevents']['subscriptionFilters'])) return;

		$eventFilter = $message->message_receiver['standard']['easysocialevents']['eventFilter'];

		$whereCondition = array();
		$filterCondition = '';
		$catId = '';

		$acyquery->join['easysocialeventsattendees'] = 'JOIN #__social_clusters_nodes AS easysocialeventsattendees ON easysocialeventsattendees.uid = joomusers.id';

		foreach($message->message_receiver['standard']['easysocialevents']['subscriptionFilters'] as $oneFilterNumber => $oneFilter){
			if(empty($oneFilter['status'])) continue;
			$status = $oneFilter['status'];

			$acyquery->where[] = 'easysocialeventsattendees.state = '.intval($status);
		}

		if($eventFilter == 'events'){
			$eventConditions = array();
			$eventSelection = '';
			if(empty($message->message_receiver['standard']['easysocialevents'][$eventFilter]['eventsId'])){
				$acyquery->where[] = '1=0';
				return;
			}
			$eventsId = explode(',', $message->message_receiver['standard']['easysocialevents'][$eventFilter]['eventsId']);

			foreach($eventsId as $oneEventId){
				if(!empty($eventSelection)) $eventConditions[] = $message->message_receiver['standard']['easysocialevents']['events']['eventSelection'];
				$eventConditions[] = 'easysocialeventsattendees.cluster_id = '.intval($oneEventId);
				$eventSelection = $message->message_receiver['standard']['easysocialevents']['events']['eventSelection'];
			}
			$acyquery->where[] = implode(' ', $eventConditions);
		}else if($eventFilter == 'category'){
			if(empty($message->message_receiver['standard']['easysocialevents'][$eventFilter]['categoryId'])){
				$acyquery->where[] = '1=0';
				return;
			}else    $catId = $message->message_receiver['standard']['easysocialevents'][$eventFilter]['categoryId'];
		}else if($eventFilter == 'autoMsg' && $message->message_receiver['auto']['easysocialevents']['typeselectionradio'] == 'category'){
			if($message->message_type == 'auto' && empty($message->message_receiver['auto']['easysocialevents']['idcat'])){
				$acyquery->where[] = '1=0';
				return;
			}else $catId = $message->message_receiver['auto']['easysocialevents']['idcat'];
		}
		if(!empty($catId)){
			$acyquery->join['easysocialevents'] = 'JOIN #__social_clusters AS easysocialevents ON easysocialevents.id = easysocialeventsattendees.cluster_id';
			$acyquery->where[] = 'easysocialevents.category_id = '.intval($catId);
		}
	}


	public function onACYSMSchooseEvent_easysocialevents(){
		$idToUpdate = JRequest::getCmd('idToUpdate');

		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$db = JFactory::getDBO();

		$this->loadScript('easysocialevents', '');

		$paramBase = ACYSMS_COMPONENT.'easysocialevents';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'eventinformations.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');


		$searchFields = array("eventinformations.description", "categories.title");
		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$filters[] = 'eventinformations.cluster_type = "event"';

		if($this->params->get('hidepastevents', 'yes') == 'yes'){
			$filters[] = 'eventDate.start >= '.$db->Quote(date('Y-m-d', time() - 86400));
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS eventinformations.id AS id, eventinformations.*, eventinformations.title AS title, categories.title AS category, eventinformations.address AS location, eventDate.*
					FROM #__social_clusters_nodes
					JOIN #__social_clusters AS eventinformations
					JOIN #__social_clusters_categories AS categories ON eventinformations.category_id = categories.id
					JOIN #__social_events_meta AS eventDate ON eventinformations.id = eventDate.cluster_id';

		if(!empty($filters)) $request .= ' WHERE ('.implode(') AND (', $filters).')';

		$request .= ' GROUP BY eventinformations.id';

		if(!empty ($pageInfo->filter->order->value)){
			$request .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db = JFactory::getDBO();
		$db->setQuery($request, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
		?>
		<div id="acysms_content">
			<form action="#" method="post" name="adminForm" id="adminForm" autocomplete="off">
				<table class="acysms_table_options">
					<tr>
						<td>
							<input type="hidden" id="eventSelected"/>
							<input type="textbox" size="30" id="eventDisplayed" readonly value=""/>
							<input type="button" onclick="confirmEventSelection('<?php echo $idToUpdate; ?>')" value="<?php echo JText::_('SMS_VALIDATE') ?>"/>
						</td>
					</tr>
					<tr>
						<td>
							<?php ACYSMS::listingSearch($pageInfo->search); ?>
						</td>
					</tr>
				</table>

				<table class="acysms_table">
					<thead>
					<tr>
						<th class="title">
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'eventdetails.description', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('APP_CALENDAR_CREATE_NEW_SCHEDULE_STARTDATE'), 'start', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('APP_CALENDAR_CREATE_NEW_SCHEDULE_ENDDATE'), 'end', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_LOCATION'), 'location', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('COM_EASYSOCIAL_EMAILS_EVENT_CATEGORY'), 'categories.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="7">
							<?php echo $pagination->getListFooter(); ?>
							<?php echo $pagination->getResultsCounter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody>
					<?php

					$k = 0;
					for($i = 0, $a = count($rows); $i < $a; $i++){
						$row = $rows[$i];
						if(!empty($row->data)) $data = unserialize($row->data);
						?>
						<tr id="content<?php echo $row->id ?>" class="<?php echo "row$k"; ?>">
							<td align="center">
								<input type="checkbox" value="<?php echo $row->id ?>" id="cb<?php echo $i; ?>" onclick="addSelectedEventAutoMessage();" style="cursor:pointer;">
							</td>
							<td align="center" id="eventNamecb<?php echo $i; ?>">
								<?php echo $row->title; ?>
							</td>
							<td align="center" id="">
								<?php echo ACYSMS::getDate(ACYSMS::getTime($row->start), JText::_('DATE_FORMAT_LC')); ?>
							</td>
							<td align="center">
								<?php echo((!empty($row->all_day)) ? ACYSMS::getDate(ACYSMS::getTime($row->end), JText::_('DATE_FORMAT_LC')) : JText::_('APP_CALENDAR_ALL_DAY')); ?>
							</td>
							<td align="center">
								<?php if(!empty($row->location)) echo $row->location; ?>
							</td>
							<td align="center">
								<?php echo $row->category; ?>
							</td>
							<td align="center" id="eventIdcb<?php echo $i; ?>">
								<?php echo $row->id; ?>
							</td>
						</tr>
						<?php
						$k = 1 - $k;
					}
					?>
					</tbody>
				</table>
				<input type="hidden" name="boxchecked" value="0"/>
				<input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>"/>
				<input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>"/>
			</form>
		</div>
		<?php
	}

	public function onAcySMSchooseCategoryEvents_easysocialevents(){
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$this->loadScript('nextEasySocialEvents', 'cat');

		$paramBase = ACYSMS_COMPONENT.'categoryEasySocialevents';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'categories.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$rows = array();

		$searchFields[] = "categories.id";
		$searchFields[] = "categories.title";
		$searchFields[] = "categories.alias";

		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS categories.id AS id, categories.title, categories.alias FROM #__social_clusters_categories AS categories';

		$filters[] = ' (categories.type="event")';
		if(!empty ($filters)){
			$request .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		$request .= ' GROUP BY categories.id';
		if(!empty ($pageInfo->filter->order->value)){
			$request .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db = JFactory::getDBO();
		$db->setQuery($request, $pageInfo->limit->start, $pageInfo->limit->value);
		$rows = $db->loadObjectList();

		$db->setQuery('SELECT FOUND_ROWS()');
		$pageInfo->elements->total = $db->loadResult();
		$pageInfo->elements->page = count($rows);

		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$context = JRequest::getCmd('context', '');
		$idToUpdate = JRequest::getCmd('idToUpdate', '');
		?>
		<div id="acysms_content">
			<div id="acysms_table_options">
				<tr>
					<td>
						<?php ACYSMS::listingSearch($pageInfo->search); ?>
					</td>
				</tr>
			</div>
			<table class="acysms_table">
				<thead>
				<tr>
					<th class="title">
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('COM_EASYSOCIAL_EMAILS_EVENT_CATEGORY'), 'categories.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_ALIAS'), 'categories.alias', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'categories.id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="4">
						<?php echo $pagination->getListFooter(); ?>
						<?php echo $pagination->getResultsCounter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php

				$k = 0;
				for($i = 0, $a = count($rows); $i < $a; $i++){
					$row = $rows[$i];


					if($context == 'filters'){
						$onClick = 'addSelectedCategory('.$row->id.',\''.$row->title.'\',\''.$idToUpdate.'\');';
					}else if($context == 'tags') $onClick = 'addEventTag('.$row->id.',\'row'.$k.'\')';
					?>
					<tr id="content<?php echo $row->id ?>" class="<?php echo "row$k"; ?>" onclick="<?php echo $onClick; ?>" style="cursor:pointer;">
						<td class="acysmstdcheckbox"></td>
						<td>
							<?php
							echo $row->title;
							?>
						</td>
						<td>
							<?php echo $row->alias; ?>
						</td>
						<td>
							<?php echo $row->id; ?>
						</td>
					</tr>
					<?php

					$k = 1 - $k;
				}
				?>
				</tbody>
			</table>
			<input type="hidden" name="boxchecked" value="0"/>
			<input type="hidden" name="filter_order" value="<?php echo $pageInfo->filter->order->value; ?>"/>
			<input type="hidden" name="filter_order_Dir" value="<?php echo $pageInfo->filter->order->dir; ?>"/>
			<input type="hidden" name="context" value="<?php echo $context; ?>"/>
		</div>
		<?php
	}

	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_BASED_ON_EVENT_X', 'Easy Social Events');
		$types['easysocialevents'] = $newType;
	}

	function onACYSMSDisplayParamsAutoMessage_easysocialevents($message){
		$result = '';

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i += 5) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$birthdayautotime = new stdClass();
		$birthdayautotime->hourField = JHTML::_('select.genericlist', $hours, 'data[message][message_receiver][auto][easysocialevents][hour]', 'style="width:50px;" class="inputbox"', 'value', 'text', '08');
		$birthdayautotime->minField = JHTML::_('select.genericlist', $min, 'data[message][message_receiver][auto][easysocialevents][min]', 'style="width:50px;" class="inputbox"', 'value', 'text', '00');

		$delay_birthday = '<input type="text" name="data[message][message_receiver][auto][easysocialevents][daybefore]" class="inputbox" style="width:50px" value="0">';

		$timeValues = array();
		$timeValues[] = JHTML::_('select.option', 'before', JText::_('SMS_BEFORE'));
		$timeValues[] = JHTML::_('select.option', 'after', JText::_('SMS_AFTER'));
		$timeValueDropDown = JHTML::_('select.genericlist', $timeValues, "data[message][message_receiver][auto][easysocialevents][time]", 'style="width:auto" size="1" class="chzn-done"', 'value', 'text');

		$catName = '';
		if(!empty($message->message_receiver['auto']['easysocialevents']['namecat'])) $catName = $message->message_receiver['auto']['easysocialevents']['namecat'];

		$radioList = '<input type="radio" name="data[message][message_receiver][auto][easysocialevents][typeselectionradio]" value="eachEvent" checked="checked" id="eventSelectionType_eachEvent"/> <label for="eventSelectionType_eachEvent">'.JText::_('SMS_EACH_EVENT').'</label>';
		$radioList .= '<input type="radio" name="data[message][message_receiver][auto][easysocialevents][typeselectionradio]" value="category" id="eventSelectionType_category"/> <label for="eventSelectionType_category">'.JText::_('SMS_BASED_ON_CATEGORY').'</label> : <span id="displayedeasySocialEventsAutoCategory"/>'.$catName.'</span><a class="modal" style="cursor:pointer" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=easysocial&fctName=chooseCategoryEvents_easysocialevents&tmpl=component&context=filters&idToUpdate=easySocialEventsAutoCategory\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';

		$result .= JText::sprintf('SMS_SEND_EVENTS_TIME', $delay_birthday, $timeValueDropDown, $birthdayautotime->hourField.' : '.$birthdayautotime->minField);
		$result .= '<br />'.$radioList;

		echo $result;

		echo '<input type="hidden" name="data[message][message_receiver][auto][easysocialevents][idcat]" id="selectedeasySocialEventsAutoCategory"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][auto][easysocialevents][namecat]" id="hiddeneasySocialEventsAutoCategory"/>';
	}

	function onACYSMSDailyCron(){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$messageClass = ACYSMS::get('class.message');

		$allMessages = $messageClass->getAutoMessage('easysocialevents');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No auto message configured for EasySocial, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create an EasySocial auto message</a>';
			return;
		}

		foreach($allMessages as $oneMessage){
			$time = empty($oneMessage->message_receiver['auto']['easysocialevents']['time']) ? 'before' : $oneMessage->message_receiver['auto']['easysocialevents']['time'];

			if($time == 'before'){
				$sendingTime = time() + 86400 + (intval($oneMessage->message_receiver['auto']['easysocialevents']['daybefore']) * 86400);
			}else if($time == 'after') $sendingTime = time() + 86400 - (intval($oneMessage->message_receiver['auto']['easysocialevents']['daybefore']) * 86400);

			$sendingDay = date('d', $sendingTime);
			$sendingMonth = date('m', $sendingTime);

			if($time == 'before'){
				$senddate = ACYSMS::getTime(date('Y').'-'.$sendingMonth.'-'.$sendingDay.' '.$oneMessage->message_receiver['auto']['easysocialevents']['hour'].':'.$oneMessage->message_receiver['auto']['easysocialevents']['min']) - (intval($oneMessage->message_receiver['auto']['easysocialevents']['daybefore']) * 86400);
			}else if($time == 'after') $senddate = ACYSMS::getTime(date('Y').'-'.$sendingMonth.'-'.$sendingDay.' '.$oneMessage->message_receiver['auto']['easysocialevents']['hour'].':'.$oneMessage->message_receiver['auto']['easysocialevents']['min']) + (intval($oneMessage->message_receiver['auto']['easysocialevents']['daybefore']) * 86400);


			$newDate = date('Y-m-d', $sendingTime);
			$messageInfo = $oneMessage->message_receiver['auto']['easysocialevents'];
			$selectionType = $messageInfo['typeselectionradio'];
			$idCat = $messageInfo['idcat'];
			switch($selectionType){
				case 'every':
					break;
				case 'category':
					$whereClause[] = 'clusters.category_id = '.intval($idCat);
			}
			switch($messageInfo['time']){
				case 'before':
					$whereClause[] = 'start LIKE "'.$newDate.'%"';
					break;
				case 'after':
					$whereClause[] = 'end LIKE "'.$newDate.'%"';
					break;
			}

			$whereClause[] = ' clusters.cluster_type = "event"';

			$queryEvents = 'SELECT DISTINCT clusters.id AS eventId
							FROM #__social_clusters AS clusters
							JOIN #__social_events_meta AS eventDate ON clusters.id = eventDate.cluster_id';

			if(!empty($whereClause)) $queryEvents .= ' WHERE ('.implode(') AND (', $whereClause).')';
			$db->setQuery($queryEvents);
			$res = $db->loadObjectList();
			$event = array();
			foreach($res as $oneResult) $event[] = intval($oneResult->eventId);

			$paramQueue = new stdClass();
			$paramQueue->eventsList = $event;

			if(empty($event)){
				$this->messages[] = 'EasySocial plugin: 0 automatic SMS inserted in the queue for '.$sendingDay.'-'.$sendingMonth.' for the SMS '.$oneMessage->message_id;
				continue;
			}

			$acyquery = ACYSMS::get('class.acyquery');
			$integration = ACYSMS::getIntegration($oneMessage->message_receiver_table);
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);

			$serializedParamQueue = serialize($paramQueue);
			$querySelect = $acyquery->getQuery(array('DISTINCT '.$oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.' , "'.$integration->componentName.'", '.$senddate.', '.$config->get('priority_message', 3).', '.$db->Quote($serializedParamQueue)));
			$finalQuery = 'INSERT IGNORE INTO '.ACYSMS::table('queue').' (queue_message_id,queue_receiver_id,queue_receiver_table,queue_senddate,queue_priority,queue_paramqueue) '.$querySelect;
			$this->success = true;
			$db->setQuery($finalQuery);
			$db->query();
			$nbInserted = $db->getAffectedRows();
			$this->messages[] = 'EasySocial plugin: '.$nbInserted.' automatic SMS inserted in the queue for '.$sendingDay.'-'.$sendingMonth.' for the SMS '.$oneMessage->message_subject;
		}
	}

	function onACYSMSTestPlugin(){
		$this->debug = true;
		$this->onACYSMSDailyCron();
		ACYSMS::display($this->messages);
	}


	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_INTEGRATION_FIELDS', 'EasySocial');
		$authorizedFilters['easysocialfields'] = $newType;

		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_X_GROUPS', 'EasySocial');
		$authorizedFilters['easysocialgroups'] = $newType;

		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_X_SUBSCRIBERS', 'EasySocial Events');
		$authorizedFilters['easysocialevents'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_easysocialfields(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

	public function onACYSMSdisplayAuthorizedFilters_easysocialgroups(&$authorizedFiltersSelection, $conditionNumber){

		$db = JFactory::getDBO();
		$db->setQuery('SELECT id, title FROM #__social_clusters WHERE cluster_type = "group"');
		$easySocialGroups = $db->loadObjectList();
		if(empty($easySocialGroups)) return;

		$ownListsObject = new stdClass();
		$ownListsObject->id = 'userownlists';
		$ownListsObject->title = JText::_('SMS_USER_OWN_GROUPS');
		array_unshift($easySocialGroups, $ownListsObject);

		$config = ACYSMS::config();
		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

		$result = '<br />';
		foreach($easySocialGroups as $oneGroup){
			if(!empty($frontEndFilters[$conditionNumber]['filterDetails']['easysocialgroups']) && in_array($oneGroup->id, $frontEndFilters[$conditionNumber]['filterDetails']['easysocialgroups'])){
				$checked = 'checked="checked"';
			}else $checked = '';
			$result .= '<label><input type="checkbox" name="config[frontEndFilters]['.$conditionNumber.'][filterDetails][easysocialgroups]['.$oneGroup->id.']" value="'.$oneGroup->id.'" '.$checked.' title= "'.$oneGroup->title.'"/> '.$oneGroup->title.'</label><br />';
		}
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails">'.$result.'</span>';

		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

	public function onACYSMSdisplayAuthorizedFilters_easysocialevents(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

}//endclass
