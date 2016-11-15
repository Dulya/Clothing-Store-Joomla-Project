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

class plgAcysmsJevents extends JPlugin{

	var $debug = false;
	var $messages = array();

	var $state = array();

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_jevents')) return;
		parent:: __construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'jevents');
			$this->params = new acysmsParameter($plugin->params);
		}
		$lang = JFactory::getLanguage();
		$lang->load('com_jevents', JPATH_SITE);
		$lang->load('com_jevents', JPATH_ADMINISTRATOR);

		$lang->load('com_rsvppro', JPATH_SITE);
		$lang->load('com_rsvppro', JPATH_ADMINISTRATOR);
	}

	private function loadScript($tagName, $nextJeventsParameter){
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

				for(i = 0; i < window.parent.document.getElementsByName("typeofinsert_jevents").length; i++){
					if(window.parent.document.getElementsByName("typeofinsert_jevents").item(i).checked){
						type += window.parent.document.getElementsByName("typeofinsert_jevents").item(i).value + ",";
					}
				}
				if(type) otherinfo += "| type:" + type;
				var wrapNumber;

				<?php if($tagName != 'Jevents') echo ' wrapNumber = window.parent.document.getElementById("nbEvent_jevents").value;'; ?>

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i)){
						if(wrapNumber)
							tag = tag + "{" + "<?php echo $tagName; ?>:" + wrapNumber + "| " + "<?php echo $nextJeventsParameter; ?>:" + selectedContents[i] + otherinfo + "}";else
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



	function onACYSMSGetTags(&$tags){
		$app = JFactory::getApplication();

		$tags['eventTags']['jevents'] = new stdClass();
		$tags['eventTags']['jevents']->name = JText::_('SMS_JEVENTS');

		$nbEvent = array();
		for($i = 1; $i <= 25; $i += 1){
			$nbEvent[] = JHTML::_('select.option', $i, $i);
		}
		$nbEventSelection = JHTML::_('select.genericlist', $nbEvent, "name", 'style="width:55px"', 'value', 'text', 'all', 'nbEvent_jevents');


		if(!$app->isAdmin()){
			$ctrl = 'fronttag';
		}else{
			$ctrl = 'tag';
		}

		$articleSelection = '<div>'.JText::_('SMS_SELECT_EVENT').' <a class="modal" style="cursor:pointer" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=tag&fctplug=chooseEvents_jevents\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></div>';
		$resultCategory = '<div>'.JText::sprintf('SMS_SELECT_X_EVENTS_FILTERED_X', $nbEventSelection, JText::_('JEV_EVENT_CATEGORY')).' <a id="listCatLoc_jevents" class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=tag&fctplug=chooseCategoryEvents_jevents&context=tags\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></div><br />';
		$buttonInsertTag = '<input type="button" id="insertTagButton_jevents" onclick="insertNextEvent_jevents();" value="'.JText::_('SMS_INSERT_TAG').'" style="display:none"/>';

		$typeOfInsert = array();
		$typeOfInsert[] = "<input type='checkbox' value='eventname' id='eventname_jevents' name='typeofinsert_jevents'><label for='eventname_jevents'>".JText::_('SMS_NAME')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='startdate' id='startdate_jevents' name='typeofinsert_jevents'><label for='startdate_jevents'>".JText::_('JEV_EVENT_STARTDATE')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='enddate' id='enddate_jevents' name='typeofinsert_jevents'><label for='enddate_jevents'>".JText::_('JEV_EVENT_ENDDATE')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='location' id='location_jevents'  name='typeofinsert_jevents'><label for='location_jevents'>".JText::_('JEV_EVENT_ADRESSE')."</label>";

		$tags['eventTags']['jevents']->content = "<table class='acysms_blocktable' cellpadding='1' width='100%'><tbody>";
		$tags['eventTags']['jevents']->content .= '<tr><td style="font-weight: bold" align="left">'.JText::_('SMS_INSERT_INFORMATION').'</th></tr>';
		$tags['eventTags']['jevents']->content .= "<tr><td>".$typeOfInsert[0].$typeOfInsert[1].$typeOfInsert[2].$typeOfInsert[3]."</td></tr>";
		$tags['eventTags']['jevents']->content .= '<tr><td style="font-weight: bold" align="left">'.JText::_('SMS_EVENT_SELECTION').'</th></tr>';
		$tags['eventTags']['jevents']->content .= '<tr><td align="left">'.$articleSelection.'</td></tr>'; // select one event
		$tags['eventTags']['jevents']->content .= '<tr><td align="left">'.$resultCategory.$buttonInsertTag; //select multiple events (the next XX)+button
		$tags['eventTags']['jevents']->content .= '</td></tr></tbody></table>';



		$tags['eventTags']['jeventsauto'] = new stdClass();
		$tags['eventTags']['jeventsauto']->name = JText::sprintf('SMS_EVENTS_AUTOMESSAGE_TAGS_X', 'JEvents');

		$tableFields = array();
		$tableField['eventname'] = JText::_('JEV_TITLE');
		$tableField['enddate'] = JText::_('JEV_FIELD_ENDDATE');
		$tableField['startdate'] = JText::_('JEV_FIELD_STARTDATE');
		$tableField['category'] = JText::_('JEV_DEF_EC_CATEGORY');
		$tableField['location'] = JText::_('JEV_FIELD_LOCATION');
		$tableField['description'] = JText::_('JEV_DESCRIPTION');
		$tableField['contact'] = JText::_('JEV_FIELD_CONTACT');
		$tableField['url'] = JText::_('JEV_AUTOREFRESH_LINK');
		$tableField['extra_info'] = JText::_('JEV_FIELD_EXTRAINFO');


		$tags['eventTags']['jeventsauto']->content = "<table class='adminlist table table-striped table-hover' cellpadding='1' width='100%'><tbody>";
		$k = 0;
		foreach($tableField as $oneValue => $oneField){
			$tags['eventTags']['jeventsauto']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{JeventsAuto:'.$oneValue.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['eventTags']['jeventsauto']->content .= '</tbody></table>';


		?>
		<script language="javascript" type="text/javascript">

			function insertNextEvent_jevents(){
				if(document.getElementById('selectionType_jevents').value == 'all'){
					var type = "";
					var nbEvents = document.getElementById('nbEvent_jevents').value;
					var tag = 'nextJevents:' + nbEvents
					for(i = 0; i < document.getElementsByName("typeofinsert_jevents").length; i++){
						if(document.getElementsByName("typeofinsert_jevents").item(i).checked){
							type += document.getElementsByName("typeofinsert_jevents").item(i).value + ",";
						}
					}
					if(type) tag += "| type:" + type;
					insertTag("{" + tag + "}");
				}
			}
		</script>

	<?php }


	public function onACYSMSchooseEvents_jevents(){
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$this->loadScript('Jevents', '');

		$paramBase = ACYSMS_COMPONENT.'jevents';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'events.ev_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');


		$searchFields = array("eventdetails.summary", "categories.title");
		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS events.ev_id AS id, eventdetails.*, eventdetails.summary AS title, rpt.*, categories.title AS category,  eventdetails.location AS location
					FROM #__jevents_repetition AS rpt
 					JOIN #__jevents_vevent AS events ON rpt.eventid = events.ev_id 
					JOIN #__jevents_vevdetail AS eventdetails ON events.detail_id = eventdetails.evdet_id
					LEFT JOIN #__categories as categories ON events.catid = categories.id ';

		if(!empty($filters)) $request .= ' WHERE ('.implode(') AND (', $filters).')';

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
			<table class="acysms_table_options">
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
						<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'eventdetails.summary', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('JEV_EVENT_STARTDATE'), 'dtstart', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('JEV_EVENT_ENDDATE'), 'dtend', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('JEV_FIELD_LOCATION'), 'location', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('JEV_EVENT_CATEGORY'), 'categories.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="6">
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
					<tr id="content<?php echo $row->id ?>" class="<?php echo "row$k"; ?>" onclick="addEventTag(<?php echo $row->id.",'row$k'" ?>);" style="cursor:pointer;">
						<td class="acysmstdcheckbox"></td>
						<td>
							<?php
							echo $row->title;
							?>
						</td>
						<td align="center" id="">
							<?php echo ACYSMS::getDate(ACYSMS::getTime($row->startrepeat), JText::_('DATE_FORMAT_LC')); ?>
						</td>
						<td align="center">
							<?php echo((!empty($row->noendtime)) ? ACYSMS::getDate(ACYSMS::getTime($row->endrepeat), JText::_('DATE_FORMAT_LC')) : JText::_('JEV_EVENT_NOENDTIME')); ?>
						</td>
						<td>
							<?php if(!empty($row->location)) echo $row->location; ?>
						</td>
						<td>
							<?php echo $row->category; ?>
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
		</div>
		<?php
	}

	public function onACYSMSchooseCategoryEvents_jevents(){
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$this->loadScript('nextJevents', 'cat');

		$paramBase = ACYSMS_COMPONENT.'categoryJevents';
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

		$request = 'SELECT SQL_CALC_FOUND_ROWS categories.id AS id, categories.title, categories.alias FROM #__categories AS categories';

		$filters[] = ' (categories.extension=\'com_jevents\')';
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
			<table class="acysms_table_options">
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
						<?php echo JHTML::_('grid.sort', JText::_('JEV_EVENT_CATEGORY'), 'categories.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
					<td style="font-weight: bold">
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




	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_rsvppro')) return;
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();

		if(!$app->isAdmin()){
			$helperPlugin = ACYSMS::get('helper.plugins');
			if(!$helperPlugin->allowSendByGroups('jevents')) return;
		}

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_X_SUBSCRIBERS', 'Jevents');
		$filters['eventFilters']['Jevents'] = $newFilter;
	}

	function onACYSMSDisplayFilterParams_Jevents($message){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_rsvppro')) return;

		$eventSelection = array();
		$eventSelection[] = JHTML::_('select.option', 'AND', JText::_('SMS_ALL_THESE_EVENTS'));
		$eventSelection[] = JHTML::_('select.option', 'OR', JText::_('SMS_AT_LEAST_ONE_OF_THESE_EVENTS'));
		$nbEventSelectionDropDown = JHTML::_('select.genericlist', $eventSelection, "data[message][message_receiver][standard][jevents][events][eventSelection]", 'onclick="document.getElementById(\'sms_eventFilterEvent\').checked = \'checked\'"', 'value', 'text', 'AND', 'nbEvent');

		$conditionsData = array();
		$conditionsData[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$conditionsData[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$subscriptionStatus = array('' => ' - - - ', 'confirmed' => 'RSVP_IS_CONFIRMED', 'pending' => 'RSVP_PENDING', 'waiting' => 'RSVP_WAITING', 'notwaiting' => 'RSVP_NOT_WAITING', 'notattending' => 'RSVP_NOT_ATTENDING', 'attending' => 'RSVP_ATTENDING', 'maybeattending' => 'RSVP_MAYBE_ATTENDING', 'pendingapproval' => 'RSVP_PENDING_APPROVAL');

		$subscriptionData = array();
		foreach($subscriptionStatus as $oneSubscription => $oneJText) $subscriptionData[] = JHTML::_('select.option', $oneSubscription, JText::_($oneJText));

		$eventName = '';
		if(!empty($message->message_receiver['standard']['jevents']['events']['eventsName'])) $eventName = $message->message_receiver['standard']['jevents']['events']['eventsName'];

		$categoryName = '';
		if(!empty($message->message_receiver['standard']['jevents']['category']['categoryName'])) $categoryName = $message->message_receiver['standard']['jevents']['category']['categoryName'];

		$filterString = '';
		for($i = 0; $i < 3; $i++){
			$subscriptionStatusDropDown = JHTML::_('select.genericlist', $subscriptionData, 'data[message][message_receiver][standard][jevents][subscriptionFilters]['.$i.'][status]', '', 'value', 'text', '');
			$conditionDropDown = JHTML::_('select.genericlist', $conditionsData, 'data[message][message_receiver][standard][jevents][subscriptionFilters]['.$i.'][condition]', '', 'value', 'text', 'AND', 'nbEvent');
			$filterString .= $subscriptionStatusDropDown;
			if($i < 2) $filterString .= $conditionDropDown;
		}

		$eventChecked = '';
		$categoryChecked = '';
		$autoMsgChecked = '';
		if(empty($message->message_receiver['standard']['jevents']['eventFilter']) || $message->message_receiver['standard']['jevents']['eventFilter'] == 'events') $eventChecked = 'checked="checked"';
		if(!empty($message->message_receiver['standard']['jevents']['eventFilter']) && $message->message_receiver['standard']['jevents']['eventFilter'] == 'category') $categoryChecked = 'checked="checked"';
		if(!empty($message->message_receiver['standard']['jevents']['eventFilter']) && $message->message_receiver['standard']['jevents']['eventFilter'] == 'autoMsg') $autoMsgChecked = 'checked="checked"';

		echo JText::sprintf('SMS_SEND_TO_USERS_WHICH_ARE', $filterString);
		echo '<br /><input type="radio" name="data[message][message_receiver][standard][jevents][eventFilter]" value="events" id="sms_eventFilterEvent_Jevents" '.$eventChecked.'/>
		<label for="sms_eventFilterEvent_Jevents">'.JText::sprintf('SMS_ASSIGNED_TO', '</label>'.$nbEventSelectionDropDown);
		echo JText::_('SMS_SELECT_EVENT').' :  <span id="displayedJevents">'.$eventName.'</span><a class="modal" style="cursor:pointer" onclick="document.getElementById(\'sms_eventFilterEvent_Jevents\').checked = \'checked\';window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=jevents&fctName=chooseEvent_jevents&tmpl=component&idToUpdate=Jevents\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';
		echo '<input type="radio" name="data[message][message_receiver][standard][jevents][eventFilter]" value="category" id="sms_eventFilterCategory_Jevents" '.$categoryChecked.'/> <label for="sms_eventFilterCategory_Jevents">'.JText::_('SMS_ASSIGNED_TO_EVENT_FROM_CATEGORY').' : <span id="displayedJeventsCategory"/>'.$categoryName.'</span><a class="modal" style="cursor:pointer" onclick="document.getElementById(\'sms_eventFilterCategory_Jevents\').checked = \'checked\'; window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=jevents&fctName=chooseCategoryEvents_jevents&tmpl=component&context=filters&idToUpdate=JeventsCategory\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';
		echo '<input type="radio" name="data[message][message_receiver][standard][jevents][eventFilter]" value="autoMsg" id="sms_eventFilterAutoMsg_Jevents" '.$autoMsgChecked.'/> <label for="sms_eventFilterAutoMsg_Jevents">'.JText::_('SMS_ASSIGNED_TO_EVENT_CHOSEN_AUTO_MESSAGE_OPTIONS').'</label><br />';

		echo '<input type="hidden" name="data[message][message_receiver][standard][jevents][category][categoryId]" id="selectedJeventsCategory"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][jevents][category][categoryName]" id="hiddenJeventsCategory"/>';
		echo '<input type="hidden" name="data[message][message_receiver][standard][jevents][events][eventsId]" id="selectedJevents"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][jevents][events][eventsName]" id="hiddenJevents"/>';
	}

	public function onACYSMSchooseEvent_jevents(){
		$idToUpdate = JRequest::getCmd('idToUpdate');

		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$my = JFactory::getUser();
		$db = JFactory::getDBO();

		$this->loadScript('Jevents', '');

		$paramBase = ACYSMS_COMPONENT.'jevents';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'events.ev_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');


		$searchFields = array('rpt.rp_id', 'eventdetails.evdet_id', 'eventdetails.description', 'eventdetails.summary', 'eventdetails.contact', 'eventdetails.location');
		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		if($this->params->get('hidepastevents', 'yes') == 'yes'){
			$filters[] = 'rpt.`endrepeat` >= '.$db->Quote(date('Y-m-d', time() - 86400));
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS events.ev_id AS id, eventdetails.*, eventdetails.summary AS title, rpt.*, categories.title AS category,  eventdetails.location AS location
					FROM #__jevents_repetition AS rpt
 					JOIN #__jevents_vevent AS events ON rpt.eventid = events.ev_id 
					JOIN #__jevents_vevdetail AS eventdetails ON events.detail_id = eventdetails.evdet_id
					LEFT JOIN #__categories as categories ON events.catid = categories.id ';

		if(!empty($filters)) $request .= ' WHERE ('.implode(') AND (', $filters).')';

		if(!empty ($pageInfo->filter->order->value)){
			$request .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}
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
							<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'eventdetails.summary', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('JEV_EVENT_STARTDATE'), 'rpt.startrepeat', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('JEV_EVENT_ENDDATE'), 'rpt.endrepeat', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('JEV_FIELD_LOCATION'), 'location', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('JEV_EVENT_CATEGORY'), 'categories.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'events.ev_id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
								<?php
								echo $row->title;
								?>
							</td>
							<td align="center" id="">
								<?php echo ACYSMS::getDate(ACYSMS::getTime($row->startrepeat), JText::_('DATE_FORMAT_LC')); ?>
							</td>
							<td align="center">
								<?php echo((!empty($row->noendtime)) ? ACYSMS::getDate(ACYSMS::getTime($row->endrepeat), JText::_('DATE_FORMAT_LC')) : JText::_('JEV_EVENT_NOENDTIME')); ?>
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

	function onACYSMSSelectData_Jevents(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['jevents'])) return;
		if(empty($message->message_receiver['standard']['jevents']['eventFilter'])) return;
		if(empty($message->message_receiver['standard']['jevents']['subscriptionFilters'])) return;

		$eventFilter = $message->message_receiver['standard']['jevents']['eventFilter'];

		$whereCondition = array();
		$filterCondition = '';
		$catId = '';

		$acyquery->join['jeventsattendees'] = 'JOIN #__jev_attendees AS jeventsattendees ON jeventsattendees.user_id = joomusers.id';
		$acyquery->join['jeventsattendance'] = 'JOIN #__jev_attendance AS jeventsattendance ON jeventsattendees.at_id = jeventsattendance.id';

		foreach($message->message_receiver['standard']['jevents']['subscriptionFilters'] as $oneFilterNumber => $oneFilter){
			if(empty($oneFilter['status'])) continue;
			$status = $oneFilter['status'];

			if(!empty($filterCondition)) $whereCondition[] = $filterCondition;
			if($status == 'confirmed') $whereCondition[] = 'jeventsattendees.confirmed = 1';
			if($status == 'pending') $whereCondition[] = 'jeventsattendees.confirmed = 0';

			if($status == 'waiting') $whereCondition[] = 'jeventsattendees.waiting = 1';
			if($status == 'notwaiting') $whereCondition[] = 'jeventsattendees.waiting = 0';

			if($status == 'notattending') $whereCondition[] = 'jeventsattendees.attendstate = 0';
			if($status == 'attending') $whereCondition[] = 'jeventsattendees.attendstate = 1';
			if($status == 'maybeattending') $whereCondition[] = 'jeventsattendees.attendstate = 2';
			if($status == 'pendingapproval') $whereCondition[] = 'jeventsattendees.attendstate = 3';

			if(!empty($oneFilter['condition'])) $filterCondition = $oneFilter['condition'];
		}
		if(!empty($whereCondition)) $acyquery->where[] = implode(' ', $whereCondition);

		if($eventFilter == 'events'){
			$eventConditions = array();
			$eventSelection = '';
			if(empty($message->message_receiver['standard']['jevents'][$eventFilter]['eventsId'])){
				$acyquery->where[] = '1=0';
				return;
			}
			$eventsId = explode(',', $message->message_receiver['standard']['jevents'][$eventFilter]['eventsId']);

			foreach($eventsId as $oneEventId){
				if(!empty($eventSelection)) $eventConditions[] = $message->message_receiver['standard']['jevents']['events']['eventSelection'];
				$eventConditions[] = 'jeventsattendance.ev_id = '.intval($oneEventId);
				$eventSelection = $message->message_receiver['standard']['jevents']['events']['eventSelection'];
			}
			$acyquery->where[] = implode(' ', $eventConditions);
		}else if($eventFilter == 'category'){
			if(empty($message->message_receiver['standard']['jevents'][$eventFilter]['categoryId'])){
				$acyquery->where[] = '1=0';
				return;
			}else    $catId = $message->message_receiver['standard']['jevents'][$eventFilter]['categoryId'];
		}else if($eventFilter == 'autoMsg' && $message->message_receiver['auto']['jevents']['typeselectionradio'] == 'category'){
			if($message->message_type == 'auto' && empty($message->message_receiver['auto']['jevents']['idcat'])){
				$acyquery->where[] = '1=0';
				return;
			}else $catId = $message->message_receiver['auto']['jevents']['idcat'];
		}
		if(!empty($catId)){
			$acyquery->join['jeventsvevent'] = 'JOIN #__jevents_vevent AS jeventsvevent ON jeventsattendance.ev_id = jeventsvevent.ev_id';
			$acyquery->where[] = 'jeventsvevent.catid = '.intval($catId);
		}
	}

	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_BASED_ON_EVENT_X', 'JEvents');
		$types['jevents'] = $newType;
	}

	function onACYSMSDisplayParamsAutoMessage_jevents($message){
		$result = '';

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i += 5) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$birthdayautotime = new stdClass();
		$birthdayautotime->hourField = JHTML::_('select.genericlist', $hours, 'data[message][message_receiver][auto][jevents][hour]', 'style="width:50px;" class="inputbox"', 'value', 'text', '08');
		$birthdayautotime->minField = JHTML::_('select.genericlist', $min, 'data[message][message_receiver][auto][jevents][min]', 'style="width:50px;" class="inputbox"', 'value', 'text', '00');

		$delay_birthday = '<input type="text" name="data[message][message_receiver][auto][jevents][daybefore]" class="inputbox" style="width:50px" value="0">';

		$timeValues = array();
		$timeValues[] = JHTML::_('select.option', 'before', JText::_('SMS_BEFORE'));
		$timeValues[] = JHTML::_('select.option', 'after', JText::_('SMS_AFTER'));
		$timeValueDropDown = JHTML::_('select.genericlist', $timeValues, "data[message][message_receiver][auto][jevents][time]", 'style="width:auto" size="1" class="chzn-done"', 'value', 'text');

		$catName = '';
		if(!empty($message->message_receiver['auto']['jevents']['namecat'])) $catName = $message->message_receiver['auto']['jevents']['namecat'];

		$radioList = '<input type="radio" name="data[message][message_receiver][auto][jevents][typeselectionradio]" value="eachEvent" checked="checked" id="eventSelectionType_eachEvent"/> <label for="eventSelectionType_eachEvent">'.JText::_('SMS_EACH_EVENT').'</label>';
		$radioList .= '<input type="radio" name="data[message][message_receiver][auto][jevents][typeselectionradio]" value="category" id="eventSelectionType_category"/> <label for="eventSelectionType_category">'.JText::_('SMS_BASED_ON_CATEGORY').'</label> : <span id="displayedJeventsAutoCategory"/>'.$catName.'</span><a class="modal" style="cursor:pointer" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=jevents&fctName=chooseCategoryEvents_jevents&tmpl=component&context=filters&idToUpdate=JeventsAutoCategory\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label>';

		$result .= JText::sprintf('SMS_SEND_EVENTS_TIME', $delay_birthday, $timeValueDropDown, $birthdayautotime->hourField.' : '.$birthdayautotime->minField);
		$result .= '<br />'.$radioList;

		echo $result;

		echo '<input type="hidden" name="data[message][message_receiver][auto][jevents][idcat]" id="selectedJeventsAutoCategory"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][auto][jevents][namecat]" id="hiddenJeventsAutoCategory"/>';
	}



	function onACYSMSDailyCron(){
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$config = ACYSMS::config();
		$allMessages = $messageClass->getAutoMessage('jevents');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No auto message configured for JEvents, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create an JEvents auto message</a>';
			return;
		}

		foreach($allMessages as $oneMessage){
			$time = empty($oneMessage->message_receiver['auto']['jevents']['time']) ? 'before' : $oneMessage->message_receiver['auto']['jevents']['time'];

			if($time == 'before'){
				$sendingTime = time() + 86400 + (intval($oneMessage->message_receiver['auto']['jevents']['daybefore']) * 86400);
			}else if($time == 'after') $sendingTime = time() + 86400 - (intval($oneMessage->message_receiver['auto']['jevents']['daybefore']) * 86400);

			$sendingDay = date('d', $sendingTime);
			$sendingMonth = date('m', $sendingTime);
			if($time == 'before'){
				$senddate = ACYSMS::getTime(date('Y').'-'.$sendingMonth.'-'.$sendingDay.' '.$oneMessage->message_receiver['auto']['jevents']['hour'].':'.$oneMessage->message_receiver['auto']['jevents']['min']) - (intval($oneMessage->message_receiver['auto']['jevents']['daybefore']) * 86400);
			}else if($time == 'after') $senddate = ACYSMS::getTime(date('Y').'-'.$sendingMonth.'-'.$sendingDay.' '.$oneMessage->message_receiver['auto']['jevents']['hour'].':'.$oneMessage->message_receiver['auto']['jevents']['min']) + (intval($oneMessage->message_receiver['auto']['jevents']['daybefore']) * 86400);


			$newDate = date('Y-m-d', $sendingTime);
			$messageInfo = $oneMessage->message_receiver['auto']['jevents'];
			$selectionType = $messageInfo['typeselectionradio'];
			$idCat = $messageInfo['idcat'];
			switch($selectionType){
				case 'every':
					break;
				case 'category':
					$whereClause[] = 'catid = '.intval($idCat);
			}
			switch($messageInfo['time']){
				case 'before':
					$whereClause[] = ' FROM_UNIXTIME(dtstart, "%Y-%m-%d") = '.$db->Quote($newDate);
					break;
				case 'after':
					$whereClause[] = ' FROM_UNIXTIME(dtend, "%Y-%m-%d") = '.$db->Quote($newDate);
					break;
			}

			$queryUsers = 'SELECT DISTINCT jeventsattendees.at_id AS eventId
							FROM #__jev_attendees AS jeventsattendees
						INNER JOIN #__jev_attendance jeventsattendance
						ON jeventsattendance.id = jeventsattendees.at_id
						INNER JOIN #__jevents_vevent AS jeventsvevent
						ON jeventsattendance.ev_id = jeventsvevent.ev_id
						INNER JOIN #__jevents_vevdetail AS jeventvevdetail
						ON jeventvevdetail.evdet_id = jeventsvevent.ev_id';

			if(!empty($whereClause)) $queryUsers .= ' WHERE ('.implode(') AND (', $whereClause).')';
			$db->setQuery($queryUsers);
			$res = $db->loadObjectList();

			$event = array();
			foreach($res as $oneResult) $event[] = intval($oneResult->eventId);

			$paramQueue = new stdClass();
			$paramQueue->eventsList = $event;

			if(empty($event)){
				$this->messages[] = 'JEvents plugin: 0 automatic SMS inserted in the queue for '.$sendingDay.'-'.$sendingMonth.' for the SMS '.$oneMessage->message_id;
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
			$this->messages[] = 'JEvents plugin: '.$nbInserted.' automatic SMS inserted in the queue for '.$sendingDay.'-'.$sendingMonth.' for the SMS '.$oneMessage->message_subject;
		}
	}

	function onACYSMSTestPlugin(){
		$this->debug = true;
		$this->onACYSMSDailyCron();
		ACYSMS::display($this->messages);
	}




	function onACYSMSReplaceTags(&$message, $send = true){
		$display = array('startdate' => JText::_('JEV_EVENT_STARTDATE').': ', 'enddate' => JText::_('JEV_EVENT_ENDDATE').': ', 'eventname' => JText::_('SMS_NAME').': ', 'location' => JText::_('JEV_EVENT_ADRESSE').': ');
		$this->_replaceNextEventsTag($message, $display);
		$this->_replaceEventTag($message, $display);
	}

	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$this->_replaceEventsAutoTag($message, $user);
	}


	private function _replaceEventTag(&$message, $display){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'Jevents');
		if(empty($tags)) return;


		$db = JFactory::getDBO();
		foreach($tags as $tagname => $tag){
			if(empty($tag->type)){
				$message->message_body = str_replace($tagname, 'Please select an information to display', $message->message_body);
				continue;
			}
			$id = $tag->id;

			$query = 'SELECT eventdetails.summary AS eventname, FROM_UNIXTIME(eventdetails.dtstart) AS startdate, FROM_UNIXTIME(eventdetails.dtend) AS enddate, eventdetails.noendtime AS noendtime, eventdetails.location AS location
					FROM `#__jevents_repetition` AS eventrepetition
					JOIN `#__jevents_vevent` AS event
					ON eventrepetition.eventid = event.ev_id
					JOIN `#__jevents_vevdetail` AS eventdetails
					ON eventrepetition.eventdetail_id = eventdetails.evdet_id
					LEFT JOIN `#__categories` as cat
					ON cat.id = event.catid
					WHERE eventrepetition.rp_id = '.intval($id);
			$db->setQuery($query);
			$result = $db->loadObject();
			$tag->type = rtrim($tag->type, ','); //we delete last ,
			$explodedTag = explode(",", $tag->type);
			$value = '';
			$value .= $this->_transformTag($explodedTag, $display, $result);
			$message->message_body = str_replace($tagname, $value, $message->message_body);
		}
	}

	private function _replaceNextEventsTag(&$message, $display){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'nextJevents');
		if(empty($tags)) return;

		$db = JFactory::getDBO();
		foreach($tags as $tagname => $tag){
			if(empty($tag->type)){
				$message->message_body = str_replace($tagname, 'Please select an information to display', $message->message_body);
				continue;
			}
			$lim = $tag->id;
			$where = array();

			if(empty($tag->order)){
				$tag->order = 'jeventrepetition.startrepeat ASC';
			}
			if(empty($tag->from)){
				$tag->from = date('Y-m-d H:i:s', time());
			}

			if(!empty($tag->cat)){
				$where[] = ' jeventevents.catid = '.intval($tag->cat);
			}

			if(!empty($tag->addcurrent)){
				$where[] = 'jeventrepetition.`endrepeat` >= '.$db->Quote($tag->from);
			}else{
				$where[] = 'jeventrepetition.`startrepeat` >= '.$db->Quote($tag->from);
			}

			if(!empty($tag->upcomingdays)){
				$where[] = 'jeventrepetition.`startrepeat` <= '.$db->Quote(date('Y-m-d H:i:s', (time() + $tag->upcomingdays * 3600)));
			}

			$where[] = 'jeventevents.`state` = 1';

			$query = 'SELECT jeventrepetition.rp_id AS eventId
					FROM `#__jevents_repetition` AS jeventrepetition
					JOIN `#__jevents_vevent` AS jeventevents ON jeventrepetition.eventid = jeventevents.ev_id
					WHERE ('.implode(') AND (', $where).')
					ORDER BY '.$tag->order.'
					LIMIT '.intval($lim);
			$db->setQuery($query);
			$result = $db->loadObjectList();

			$value = "";
			foreach($result as $oneResult){
				$value .= '{Jevents:'.$oneResult->eventId.'|type:'.$tag->type.'}';
				$value .= "\n";
			}
			$message->message_body = str_replace($tagname, $value, $message->message_body);
		}
	}

	private function _replaceEventsAutoTag(&$message, $user){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'JeventsAuto');
		if(empty($tags)) return;
		$db = JFactory::getDBO();
		foreach($tags as $tagname => $tag){
			if(empty($tag->id)){
				$message->message_body = str_replace($tagname, '', $message->message_body);
				continue;
			}

			if(empty($user->queue_paramqueue) || empty($user->queue_paramqueue->eventsList)){
				$message->message_body = str_replace($tagname, "EVENT-".mb_strtoupper($tag->id)." ", $message->message_body);
				continue;
			}
			$paramQueue = $user->queue_paramqueue->eventsList;

			JArrayHelper::toInteger($paramQueue);

			$query = 'SELECT *, eventdetails.summary AS eventname, FROM_UNIXTIME(eventdetails.dtstart) AS startdate, FROM_UNIXTIME(eventdetails.dtend) AS enddate, events.rawdata AS data, events.ev_id as eventId
					FROM #__jevents_vevent AS events
					JOIN #__jevents_vevdetail AS eventdetails ON events.ev_id = eventdetails.evdet_id
					JOIN #__categories AS categories ON events.catid = categories.id
					WHERE events.ev_id IN('.implode(",", $paramQueue).')';
			$db->setQuery($query);
			$events = $db->loadObjectList();

			foreach($events as $oneEvent){
				if($tag->id == 'description') //if the tag is {JEvents:::description}
				{
					$oneEvent->{$tag->id} = $this->_transformDescription($oneEvent->{$tag->id});
				}
				$message->message_body = str_replace($tagname, $oneEvent->{$tag->id}."\n", $message->message_body);
			}
		}
	}

	private function _transformDescription($text){
		$text = str_replace('<br />', "\n", $text);
		$text = strip_tags($text);
		return $text;
	}

	private function _transformTag($explodedTag, $display, $oneResult){
		$value = '';
		foreach($explodedTag as $oneType){
			if(!empty($display[$oneType])){
				$value .= $display[$oneType];
			}else continue;

			if($oneType == 'enddate'){
				$value .= ((!empty($oneResult->noendtime)) ? $oneResult->enddate : JText::_('JEV_EVENT_NOENDTIME'));
			}else{
				$value .= $oneResult->$oneType;
			}
			$value .= "\n";
		}
		return $value;
	}



	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_X_SUBSCRIBERS', 'Jevents');
		$authorizedFilters['jevents'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_jevents(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

}//endclass
