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

class plgAcysmseventbooking extends JPlugin{

	var $debug = false;
	var $messages = array();

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_eventbooking')) return;
		parent:: __construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'eventbooking');
			$this->params = new acysmsParameter($plugin->params);
		}

		$lang = JFactory::getLanguage();
		$lang->load('com_eventbooking', JPATH_SITE);
		$lang->load('com_eventbooking', JPATH_ADMINISTRATOR);
	}

	private function loadScript($tagName, $nexteventBookingParameter){
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

				for(i = 0; i < window.parent.document.getElementsByName("typeofinsert_eventBooking").length; i++){
					if(window.parent.document.getElementsByName("typeofinsert_eventBooking").item(i).checked){
						type += window.parent.document.getElementsByName("typeofinsert_eventBooking").item(i).value + ",";
					}
				}
				if(type) otherinfo += "| type:" + type;
				var wrapNumber;

				<?php if($tagName != 'eventBooking') echo ' wrapNumber = window.parent.document.getElementById("nbEvent_eventBooking").value;'; ?>

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i)){
						if(wrapNumber)
							tag = tag + "{" + "<?php echo $tagName; ?>:" + wrapNumber + "| " + "<?php echo $nexteventBookingParameter; ?>:" + selectedContents[i] + otherinfo + "}";else
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

		$tags['eventTags']['eventBooking'] = new stdClass();
		$tags['eventTags']['eventBooking']->name = JText::_('SMS_EVENTBOOKING');

		$nbEvent = array();
		for($i = 1; $i <= 25; $i += 1){
			$nbEvent[] = JHTML::_('select.option', $i, $i);
		}
		$nbEventSelection = JHTML::_('select.genericlist', $nbEvent, "name", "style=width:45px;", 'value', 'text', 'all', 'nbEvent_eventBooking');


		if(!$app->isAdmin()){
			$ctrl = 'fronttag';
		}else    $ctrl = 'tag';

		$articleSelection = '<div>'.JText::_('SMS_SELECT_EVENT').' <a class="modal" style="cursor:pointer" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=tag&fctplug=chooseEvents_eventBooking\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></div><br />';
		$resultCategory = '<div>'.JText::sprintf('SMS_SELECT_X_EVENTS_FILTERED_X', $nbEventSelection, JText::_('SMS_CATEGORY')).' <a id="listCatLoc_eventBooking" class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=tag&fctplug=chooseCategoryEvents_eventBooking&context=tags\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></div><br />';
		$buttonInsertTag = '<input type="button" id="insertTagButton_eventBooking" onclick="insertNextEvent_eventBooking();" value="'.JText::_('SMS_INSERT_TAG').'" style="display:none"/>';

		$typeOfInsert = array();
		$typeOfInsert[] = "<input type='checkbox' value='title' id='eventname_eventBooking' name='typeofinsert_eventBooking'><label for='eventname_eventBooking'>".JText::_('SMS_NAME')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='event_date' id='startdate_eventBooking' name='typeofinsert_eventBooking'><label for='startdate_eventBooking'>".JText::_('EB_EVENT_START_DATE')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='event_end_date' id='enddate_eventBooking' name='typeofinsert_eventBooking'><label for='enddate_eventBooking'>".JText::_('EB_EVENT_END_DATE')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='location' id='location_eventBooking'  name='typeofinsert_eventBooking'><label for='location_eventBooking'>".JText::_('EB_LOCATION')."</label>";

		$tags['eventTags']['eventBooking']->content = "<table class='acysms_blocktable' cellpadding='1' width='100%'><tbody>";
		$tags['eventTags']['eventBooking']->content .= '<tr><td align="left" style="font-weight: bold">'.JText::_('SMS_INSERT_INFORMATION').'</th></tr>';
		$tags['eventTags']['eventBooking']->content .= "<tr><td>".$typeOfInsert[0].$typeOfInsert[1].$typeOfInsert[2].$typeOfInsert[3]."</td></tr>";
		$tags['eventTags']['eventBooking']->content .= '<tr><td align="left" style="font-weight: bold">'.JText::_('SMS_EVENT_SELECTION').'</th></tr>';
		$tags['eventTags']['eventBooking']->content .= '<tr><td align="left">'.$articleSelection.'</td></tr>'; // select one event
		$tags['eventTags']['eventBooking']->content .= '<tr><td align="left">'.$resultCategory.$buttonInsertTag; //select multiple events (the next XX)+button
		$tags['eventTags']['eventBooking']->content .= '</td></tr></tbody></table>';



		$tags['eventTags']['eventBookingauto'] = new stdClass();
		$tags['eventTags']['eventBookingauto']->name = JText::sprintf('SMS_EVENTS_AUTOMESSAGE_TAGS_X', 'Event Booking');

		$tableField = array();
		$tableField['title'] = JText::_('EB_TITLE');
		$tableField['event_end_date'] = JText::_('EB_EVENT_END_DATE');
		$tableField['event_date'] = JText::_('EB_EVENT_START_DATE');
		$tableField['category'] = JText::_('EB_EVENT_CATEGORY');
		$tableField['location'] = JText::_('EB_LOCATION');
		$tableField['short_description'] = JText::_('EB_SHORT_DESCRIPTION');
		$tableField['description'] = JText::_('EB_DESCRIPTION');


		$tags['eventTags']['eventBookingauto']->content = "<table class='adminlist table table-striped table-hover' cellpadding='1' width='100%'><tbody>";
		$k = 0;
		foreach($tableField as $oneValue => $oneField){
			$tags['eventTags']['eventBookingauto']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{eventBookingAuto:'.$oneValue.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['eventTags']['eventBookingauto']->content .= '</tbody></table>';


		?>
		<script language="javascript" type="text/javascript">

			function insertNextEvent_eventBooking(){
				if(document.getElementById('selectionType_eventBooking').value == 'all'){
					var type = "";
					var nbEvents = document.getElementById('nbEvent_eventBooking').value;
					var tag = 'nexteventBooking:' + nbEvents
					for(i = 0; i < document.getElementsByName("typeofinsert_eventBooking").length; i++){
						if(document.getElementsByName("typeofinsert_eventBooking").item(i).checked){
							type += document.getElementsByName("typeofinsert_eventBooking").item(i).value + ",";
						}
					}
					if(type) tag += "| type:" + type;
					insertTag("{" + tag + "}");
				}
			}
		</script>

	<?php }


	public function onACYSMSchooseEvents_eventBooking(){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$this->loadScript('eventBooking', '');

		$paramBase = ACYSMS_COMPONENT.'eventBooking';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'events.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');


		$searchFields = array("events.title", "categories.name");
		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		if($this->params->get('hidepastevents', 'yes') == 'yes'){
			$filters[] = 'events.`event_date` >= '.$db->Quote(date('Y-m-d', time() - 86400));
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS events.id AS id, events.*, events.title as title, categories.name AS category
					FROM #__eb_events AS events
					JOIN #__eb_event_categories AS eventcategories
					ON events.id = eventcategories.event_id
					JOIN #__eb_categories AS categories
					ON categories.id = eventcategories.category_id';

		if(!empty($filters)) $request .= ' WHERE('.implode(') AND (', $filters).')';

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
					<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('EB_EVENT_START_DATE'), 'event_date', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('EB_EVENT_END_DATE'), 'event_end_date', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('EB_EVENT_CATEGORY'), 'category', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
				?>
				<tr id="content<?php echo $row->id ?>" class="<?php echo "row$k"; ?>"
					onclick="addEventTag(<?php echo $row->id.",'row$k'" ?>);" style="cursor:pointer;">
					<td class="acysmstdcheckbox"></td>
					<td>
						<?php
						echo $row->title;
						?>
					</td>
					<td align="center" id="">
						<?php echo $row->event_date; ?>
					</td>
					<td align="center">
						<?php echo $row->event_end_date; ?>
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
		<?php
	}

	public function onACYSMSchooseCategoryEvents_eventBooking(){
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$this->loadScript('nexteventBooking', 'cat');

		$paramBase = ACYSMS_COMPONENT.'categoryEventBooking';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'categories.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$searchFields[] = "categories.id";
		$searchFields[] = "categories.name";

		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS categories.id AS id, categories.name FROM #__eb_categories AS categories';
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
					<?php echo JHTML::_('grid.sort', JText::_('EB_EVENT_CATEGORY'), 'categories.name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
					$onClick = 'addSelectedCategory('.$row->id.',\''.$row->name.'\',\''.$idToUpdate.'\');';
				}else if($context == 'tags') $onClick = 'addEventTag('.$row->id.',\'row'.$k.'\')';
				?>
				<tr id="content<?php echo $row->id ?>" class="<?php echo "row$k"; ?>"
					onclick="<?php echo $onClick; ?>" style="cursor:pointer;">
					<td class="acysmstdcheckbox"></td>
					<td>
						<?php
						echo $row->name;
						?>
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
	<?php }




	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();

		if(!$app->isAdmin()){
			$helperPlugin = ACYSMS::get('helper.plugins');
			if(!$helperPlugin->allowSendByGroups('eventBooking')) return;
		}

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_X_SUBSCRIBERS', 'Event Booking');
		$filters['eventFilters']['eventBooking'] = $newFilter;
	}

	function onACYSMSDisplayFilterParams_eventBooking($message){

		$eventSelection = array();
		$eventSelection[] = JHTML::_('select.option', 'AND', JText::_('SMS_ALL_THESE_EVENTS'));
		$eventSelection[] = JHTML::_('select.option', 'OR', JText::_('SMS_AT_LEAST_ONE_OF_THESE_EVENTS'));
		$nbEventSelectionDropDown = JHTML::_('select.genericlist', $eventSelection, "data[message][message_receiver][standard][eventBooking][events][eventSelection]", 'onclick="document.getElementById(\'sms_eventFilterEvent\').checked = \'checked\'"', 'value', 'text', 'AND', 'nbEvent');

		$conditionsData = array();
		$conditionsData[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$conditionsData[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$subscriptionData[] = JHTML::_('select.option', '', '- - - -');
		$subscriptionData[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('EB_REGISTRATION_STATUS'));
		$subscriptionData[] = JHTML::_('select.option', 'published:1', JText::_('EB_PUBLISHED'));
		$subscriptionData[] = JHTML::_('select.option', 'published:0', JText::_('EB_UNPUBLISHED'));
		$subscriptionData[] = JHTML::_('select.option', '</OPTGROUP>');
		$subscriptionData[] = JHTML::_('select.option', '<OPTGROUP>', JText::_('EB_PAYMENT_INFORMATION'));
		$subscriptionData[] = JHTML::_('select.option', 'payment_status:0', JText::_('EB_PENDING'));
		$subscriptionData[] = JHTML::_('select.option', 'payment_status:1', JText::_('EB_PAID'));
		$subscriptionData[] = JHTML::_('select.option', 'payment_status:2', JText::_('EB_CANCELLED'));
		$subscriptionData[] = JHTML::_('select.option', '</OPTGROUP>');


		$eventName = '';
		if(!empty($message->message_receiver['standard']['eventBooking']['events']['eventsName'])) $eventName = $message->message_receiver['standard']['eventBooking']['events']['eventsName'];

		$categoryName = '';
		if(!empty($message->message_receiver['standard']['eventBooking']['category']['categoryName'])) $categoryName = $message->message_receiver['standard']['eventBooking']['category']['categoryName'];

		$filterString = '';
		for($i = 0; $i < 3; $i++){
			$subscriptionStatusDropDown = JHTML::_('select.genericlist', $subscriptionData, 'data[message][message_receiver][standard][eventBooking][subscriptionFilters]['.$i.'][status]', 'style="width:auto;"', 'value', 'text', '');
			$conditionDropDown = JHTML::_('select.genericlist', $conditionsData, 'data[message][message_receiver][standard][eventBooking][subscriptionFilters]['.$i.'][condition]', 'style="width:auto;"', 'value', 'text', 'AND', 'nbEvent');
			$filterString .= $subscriptionStatusDropDown;
			if($i < 2) $filterString .= $conditionDropDown;
		}

		$eventChecked = '';
		$categoryChecked = '';
		$autoMsgChecked = '';
		if(empty($message->message_receiver['standard']['eventBooking']['eventFilter']) || $message->message_receiver['standard']['eventBooking']['eventFilter'] == 'events') $eventChecked = 'checked="checked"';
		if(!empty($message->message_receiver['standard']['eventBooking']['eventFilter']) && $message->message_receiver['standard']['eventBooking']['eventFilter'] == 'category') $categoryChecked = 'checked="checked"';
		if(!empty($message->message_receiver['standard']['eventBooking']['eventFilter']) && $message->message_receiver['standard']['eventBooking']['eventFilter'] == 'autoMsg') $autoMsgChecked = 'checked="checked"';

		echo JText::sprintf('SMS_SEND_TO_USERS_WHICH_ARE', $filterString);
		echo '<br /><input type="radio" name="data[message][message_receiver][standard][eventBooking][eventFilter]" value="events" id="sms_eventFilterEvent_eventBooking" '.$eventChecked.'/>
		<label for="sms_eventFilterEvent_eventBooking">'.JText::sprintf('SMS_ASSIGNED_TO', '</label>'.$nbEventSelectionDropDown);
		echo JText::_('SMS_SELECT_EVENT').' :  <span id="displayedeventBooking">'.$eventName.'</span><a class="modal" style="cursor:pointer" onclick="document.getElementById(\'sms_eventFilterEvent_eventBooking\').checked = \'checked\';window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=eventbooking&fctName=chooseEvent_eventBooking&tmpl=component&idToUpdate=eventBooking\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';
		echo '<input type="radio" name="data[message][message_receiver][standard][eventBooking][eventFilter]" value="category" id="sms_eventFilterCategory_eventBooking" '.$categoryChecked.'/> <label for="sms_eventFilterCategory_eventBooking">'.JText::_('SMS_ASSIGNED_TO_EVENT_FROM_CATEGORY').' : <span id="displayedeventBookingCategory"/>'.$categoryName.'</span><a class="modal" style="cursor:pointer" onclick="document.getElementById(\'sms_eventFilterCategory_eventBooking\').checked = \'checked\'; window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=eventbooking&fctName=chooseCategoryEvents_eventBooking&tmpl=component&context=filters&idToUpdate=eventBookingCategory\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';
		echo '<input type="radio" name="data[message][message_receiver][standard][eventBooking][eventFilter]" value="autoMsg" id="sms_eventFilterAutoMsg_eventBooking" '.$autoMsgChecked.'/> <label for="sms_eventFilterAutoMsg_eventBooking">'.JText::_('SMS_ASSIGNED_TO_EVENT_CHOSEN_AUTO_MESSAGE_OPTIONS').'</label><br />';

		echo '<input type="hidden" name="data[message][message_receiver][standard][eventBooking][category][categoryId]" id="selectedeventBookingCategory"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][eventBooking][category][categoryName]" id="hiddeneventBookingCategory"/>';
		echo '<input type="hidden" name="data[message][message_receiver][standard][eventBooking][events][eventsId]" id="selectedeventBooking"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][eventBooking][events][eventsName]" id="hiddeneventBooking"/>';
	}

	public function onACYSMSchooseEvent_eventBooking(){
		$idToUpdate = JRequest::getCmd('idToUpdate');

		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$db = JFactory::getDBO();

		$this->loadScript('eventBooking', '');

		$paramBase = ACYSMS_COMPONENT.'eventBooking';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'events.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');


		$searchFields = array("events.title", "categories.name");
		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		if($this->params->get('hidepastevents', 'yes') == 'yes'){
			$filters[] = 'events.`event_date` >= '.$db->Quote(date('Y-m-d', time() - 86400));
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS events.id AS id, events.*, events.title as title, categories.name AS category
					FROM #__eb_events AS events
					JOIN #__eb_event_categories AS eventcategories
					ON events.id = eventcategories.event_id
					JOIN #__eb_categories AS categories
					ON categories.id = eventcategories.category_id';

		if(!empty($filters)) $request .= ' WHERE('.implode(') AND (', $filters).')';

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
		<form action="#" method="post" name="adminForm" id="adminForm" autocomplete="off">
			<table class="acysms_table_options">
				<tr>
					<td>
						<input type="hidden" id="eventSelected"/>
						<input type="textbox" size="30" id="eventDisplayed" readonly value=""/>
						<input type="button" onclick="confirmEventSelection('<?php echo $idToUpdate; ?>')"
							   value="<?php echo JText::_('SMS_VALIDATE') ?>"/>
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
						<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('EB_EVENT_START_DATE'), 'event_date', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('EB_EVENT_END_DATE'), 'event_end_date', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('EB_EVENT_CATEGORY'), 'category', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'events.id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
					?>
					<tr id="content<?php echo $row->id ?>" class="<?php echo "row$k"; ?>">
						<td align="center">
							<input type="checkbox" value="<?php echo $row->id ?>" id="eb<?php echo $i; ?>"
								   onclick="addSelectedEventAutoMessage();" style="cursor:pointer;">
						</td>
						<td align="center" id="eventNameeb<?php echo $i; ?>">
							<?php
							echo $row->title;
							?>
						</td>
						<td align="center" id="">
							<?php echo $row->event_date; ?>
						</td>
						<td align="center">
							<?php echo $row->event_end_date; ?>
						</td>
						<td>
							<?php echo $row->category; ?>
						</td>
						<td align="center" id="eventIdeb<?php echo $i; ?>">
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
		<?php
	}

	function onACYSMSSelectData_eventBooking(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['eventBooking'])) return;
		if(empty($message->message_receiver['standard']['eventBooking']['eventFilter'])) return;
		if(empty($message->message_receiver['standard']['eventBooking']['subscriptionFilters'])) return;

		$eventFilter = $message->message_receiver['standard']['eventBooking']['eventFilter'];

		$whereCondition = array();
		$catId = '';

		$acyquery->join['eventbookingregistrants'] = 'JOIN `#__eb_registrants` AS eventbookingregistrants ON (eventbookingregistrants.user_id = joomusers.id OR eventbookingregistrants.email = joomusers.email)';


		foreach($message->message_receiver['standard']['eventBooking']['subscriptionFilters'] as $oneFilterNumber => $oneFilter){

			if(empty($oneFilter['status'])) continue;
			$status = $oneFilter['status'];

			if(strpos($status, ':') !== false){
				$informations = explode(':', $status);
				$acyquery->where[] = 'eventbookingregistrants.'.$informations[0].' = '.$informations[1];
			}
		}

		if(!empty($whereCondition)) $acyquery->where[] = implode(' ', $whereCondition);

		if($eventFilter == 'events'){
			$eventConditions = array();
			$eventSelection = '';
			if(empty($message->message_receiver['standard']['eventBooking'][$eventFilter]['eventsId'])){
				$acyquery->where[] = '1=0';
				return;
			}
			$eventsId = explode(',', $message->message_receiver['standard']['eventBooking'][$eventFilter]['eventsId']);

			foreach($eventsId as $oneEventId){
				if(!empty($eventSelection)) $eventConditions[] = $message->message_receiver['standard']['eventBooking']['events']['eventSelection'];
				$eventConditions[] = 'eventbookingregistrants.event_id = '.intval($oneEventId);
				$eventSelection = $message->message_receiver['standard']['eventBooking']['events']['eventSelection'];
			}
			$acyquery->where[] = implode(' ', $eventConditions);
		}else if($eventFilter == 'category'){
			if(empty($message->message_receiver['standard']['eventBooking'][$eventFilter]['categoryId'])){
				$acyquery->where[] = '1=0';
				return;
			}else    $catId = $message->message_receiver['standard']['eventBooking'][$eventFilter]['categoryId'];
		}else if($eventFilter == 'autoMsg' && $message->message_receiver['auto']['eventBooking']['typeselectionradio'] == 'category'){
			if($message->message_type == 'auto' && empty($message->message_receiver['auto']['eventBooking']['idcat'])){
				$acyquery->where[] = '1=0';
				return;
			}else $catId = $message->message_receiver['auto']['eventBooking']['idcat'];
		}

		if(!empty($catId)){
			$acyquery->join['eventcategories'] = 'JOIN #__eb_event_categories eventcategories ON eventbookingregistrants.event_id = eventcategories.event_id';
			$acyquery->join['eventBookingCategories'] = 'JOIN #__eb_categories AS eventBookingCategories ON eventBookingCategories.id = eventcategories.category_id';
			$acyquery->where[] = 'eventBookingCategories.id = '.intval($catId);
		}
	}


	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_BASED_ON_EVENT_X', 'Event Booking');
		$types['eventBooking'] = $newType;
	}

	function onACYSMSDisplayParamsAutoMessage_eventBooking($message){
		$result = '';

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i += 5) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$eventTime = new stdClass();
		$eventTime->hourField = JHTML::_('select.genericlist', $hours, 'data[message][message_receiver][auto][eventBooking][hour]', 'style="width:50px;" class="inputbox"', 'value', 'text', '08');
		$eventTime->minField = JHTML::_('select.genericlist', $min, 'data[message][message_receiver][auto][eventBooking][min]', 'style="width:50px;" class="inputbox"', 'value', 'text', '00');

		$delay_birthday = '<input type="text" name="data[message][message_receiver][auto][eventBooking][daybefore]" class="inputbox" style="width:50px" value="0">';

		$timeValues = array();
		$timeValues[] = JHTML::_('select.option', 'before', JText::_('SMS_BEFORE'));
		$timeValues[] = JHTML::_('select.option', 'after', JText::_('SMS_AFTER'));
		$timeValueDropDown = JHTML::_('select.genericlist', $timeValues, "data[message][message_receiver][auto][eventBooking][time]", 'style="width:auto" size="1" class="chzn-done"', 'value', 'text');

		$catName = '';
		if(!empty($message->message_receiver['auto']['eventBooking']['namecat'])) $catName = $message->message_receiver['auto']['eventBooking']['namecat'];

		$radioList = '<input type="radio" name="data[message][message_receiver][auto][eventBooking][typeselectionradio]" value="eachEvent" checked="checked" id="eventSelectionType_eachEvent"/> <label for="eventSelectionType_eachEvent">'.JText::_('SMS_EACH_EVENT').'</label>';
		$radioList .= '<input type="radio" name="data[message][message_receiver][auto][eventBooking][typeselectionradio]" value="category" id="eventSelectionType_category"/> <label for="eventSelectionType_category">'.JText::_('SMS_BASED_ON_CATEGORY').'</label> : <span id="displayedeventBookingAutoCategory"/>'.$catName.'</span><a class="modal" style="cursor:pointer" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=eventbooking&fctName=chooseCategoryEvents_eventBooking&tmpl=component&context=filters&idToUpdate=eventBookingAutoCategory\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';

		$result .= JText::sprintf('SMS_SEND_EVENTS_TIME', $delay_birthday, $timeValueDropDown, $eventTime->hourField.' : '.$eventTime->minField);
		$result .= '<br />'.$radioList;

		echo $result;

		echo '<input type="hidden" name="data[message][message_receiver][auto][eventBooking][idcat]" id="selectedeventBookingAutoCategory"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][auto][eventBooking][namecat]" id="hiddeneventBookingAutoCategory"/>';
	}


	function onACYSMSDailyCron(){
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$config = ACYSMS::config();
		$allMessages = $messageClass->getAutoMessage('eventBooking');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No auto message configured for eventBooking, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create an eventBooking auto message</a>';
			return;
		}

		foreach($allMessages as $oneMessage){
			$time = empty($oneMessage->message_receiver['auto']['eventBooking']['time']) ? 'before' : $oneMessage->message_receiver['auto']['eventBooking']['time'];

			if($time == 'before'){
				$sendingTime = time() + 86400 + (intval($oneMessage->message_receiver['auto']['eventBooking']['daybefore']) * 86400);
			}else if($time == 'after') $sendingTime = time() + 86400 - (intval($oneMessage->message_receiver['auto']['eventBooking']['daybefore']) * 86400);

			$sendingDay = date('d', $sendingTime);
			$sendingMonth = date('m', $sendingTime);
			if($time == 'before'){
				$senddate = ACYSMS::getTime(date('Y').'-'.$sendingMonth.'-'.$sendingDay.' '.$oneMessage->message_receiver['auto']['eventBooking']['hour'].':'.$oneMessage->message_receiver['auto']['eventBooking']['min']) - (intval($oneMessage->message_receiver['auto']['eventBooking']['daybefore']) * 86400);
			}else if($time == 'after') $senddate = ACYSMS::getTime(date('Y').'-'.$sendingMonth.'-'.$sendingDay.' '.$oneMessage->message_receiver['auto']['eventBooking']['hour'].':'.$oneMessage->message_receiver['auto']['eventBooking']['min']) + (intval($oneMessage->message_receiver['auto']['eventBooking']['daybefore']) * 86400);


			$newDate = date('Y-m-d', $sendingTime);
			$messageInfo = $oneMessage->message_receiver['auto']['eventBooking'];
			$selectionType = $messageInfo['typeselectionradio'];
			$idCat = $messageInfo['idcat'];
			switch($selectionType){
				case 'every':
					break;
				case 'category':
					$whereClause[] = 'categories.id = '.intval($idCat);
			}
			switch($messageInfo['time']){
				case 'before':
					$whereClause[] = ' DATE_FORMAT(event_date, "%Y-%m-%d") = '.$db->Quote($newDate);
					break;
				case 'after':
					$whereClause[] = ' DATE_FORMAT(event_end_date, "%Y-%m-%d") = '.$db->Quote($newDate);
					break;
			}

			$queryUsers = 'SELECT events.id AS eventId
					FROM #__eb_events AS events
					JOIN #__eb_event_categories AS eventcategories
					ON events.id = eventcategories.event_id
					JOIN #__eb_categories AS categories
					ON categories.id = eventcategories.category_id
					LEFT JOIN #__eb_locations AS location
					ON location.id = events.location_id';

			if(!empty($whereClause)) $queryUsers .= ' WHERE ('.implode(') AND (', $whereClause).')';
			$db->setQuery($queryUsers);
			$res = $db->loadObjectList();

			$event = array();
			foreach($res as $oneResult) $event[] = intval($oneResult->eventId);

			$paramQueue = new stdClass();
			$paramQueue->eventsList = $event;

			if(empty($event)){
				$this->messages[] = 'eventBooking plugin: 0 automatic SMS inserted in the queue for '.$sendingDay.'-'.$sendingMonth.' for the SMS '.$oneMessage->message_id;
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
			$this->messages[] = 'eventBooking plugin: '.$nbInserted.' automatic SMS inserted in the queue for '.$sendingDay.'-'.$sendingMonth.' for the SMS '.$oneMessage->message_subject;
		}
	}

	function onACYSMSTestPlugin(){
		$this->debug = true;
		$this->onACYSMSDailyCron();
		ACYSMS::display($this->messages);
	}




	function onACYSMSReplaceTags(&$message, $send = true){
		$display = array('event_date' => JText::_('EB_EVENT_START_DATE').': ', 'event_end_date' => JText::_('EB_EVENT_END_DATE').': ', 'title' => JText::_('EB_TITLE').': ', 'location' => JText::_('EB_LOCATION').': ');
		$this->_replaceNextEventsTag($message, $display);
		$this->_replaceEventTag($message, $display);
	}

	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$this->_replaceEventsAutoTag($message, $user);
	}


	private function _replaceEventTag(&$message, $display){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'eventBooking');
		if(empty($tags)) return;


		$db = JFactory::getDBO();
		foreach($tags as $tagname => $tag){
			if(empty($tag->type)){
				$message->message_body = str_replace($tagname, 'Please select an information to display', $message->message_body);
				continue;
			}
			$id = $tag->id;

			$query = 'SELECT events.*, categories.name AS category, location.*
					FROM #__eb_events AS events
					JOIN #__eb_event_categories AS eventcategories
					ON events.id = eventcategories.event_id
					JOIN #__eb_categories AS categories
					ON categories.id = eventcategories.category_id
					LEFT JOIN #__eb_locations AS location
					ON location.id = events.location_id
					WHERE events.id = '.intval($id);
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
		$tags = $helperPlugin->extractTags($message, 'nexteventBooking');
		if(empty($tags)) return;

		$db = JFactory::getDBO();
		foreach($tags as $tagname => $tag){
			if(empty($tag->type)){
				$message->message_body = str_replace($tagname, 'Please select an information to display', $message->message_body);
				continue;
			}
			$lim = $tag->id;
			$where = array();


			if(empty($tag->from)) $tag->from = date('Y-m-d H:i:s', time());
			if(empty($tag->order)) $tag->order = 'events.event_date ASC';

			$where[] = 'events.`event_date` >= '.$db->Quote($tag->from);
			if(!empty($tag->cat)) $where[] = ' events.category_id = '.intval($tag->cat);

			if(!empty($tag->upcomingdays)) $where[] = 'events.`event_date` <= '.$db->Quote(date('Y-m-d H:i:s', (time() + $tag->upcomingdays * 3600)));

			$where[] = 'events.`published` = 1';

			$query = 'SELECT events.id AS eventId
					FROM #__eb_events AS events
					WHERE ('.implode(') AND (', $where).')
					ORDER BY '.$tag->order.'
					LIMIT '.intval($lim);
			$db->setQuery($query);
			$result = $db->loadObjectList();

			$value = "";
			foreach($result as $oneResult){
				$value .= '{eventBooking:'.$oneResult->eventId.'|type:'.$tag->type.'}';
				$value .= "\n";
			}
			$message->message_body = str_replace($tagname, $value, $message->message_body);
		}
	}

	private function _replaceEventsAutoTag(&$message, $user){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'eventBookingAuto');
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

			$query = 'SELECT events.*, categories.name AS category, location.address as location
					FROM #__eb_events AS events
					JOIN #__eb_event_categories AS eventcategories
					ON events.id = eventcategories.event_id
					JOIN #__eb_categories AS categories
					ON categories.id = eventcategories.category_id
					LEFT JOIN #__eb_locations AS location
					ON location.id = events.location_id
					WHERE events.id IN('.implode(",", $paramQueue).')';
			$db->setQuery($query);
			$events = $db->loadObjectList();

			foreach($events as $oneEvent){
				if($tag->id == 'description') //if the tag is {eventBooking:::description}
				{
					$oneEvent->{$tag->id} = $this->_transformDescription($oneEvent->{$tag->id});
				}
				$message->message_body = str_replace($tagname, $oneEvent->{$tag->id}."\n", $message->message_body);
			}
		}
	}

	private function _transformTag($explodedTag, $display, $oneResult){
		$value = '';
		foreach($explodedTag as $oneType){
			if(!empty($display[$oneType])){
				$value .= $display[$oneType];
			}else continue;

			if($oneType == 'event_end_date' && $oneResult->event_end_date == '0000-00-00 00:00:00'){
				$value .= JText::_('JEV_EVENT_NOENDTIME');
			}else if($oneType == 'location'){
				$value .= $oneResult->address;
			}else{
				$value .= $oneResult->$oneType;
			}
			$value .= "\n";
		}
		return $value;
	}

	private function _transformDescription($text){
		$text = str_replace('<br />', "\n", $text);
		$text = strip_tags($text);
		return $text;
	}



	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_EVENTBOOKING');
		$authorizedFilters['eventBooking'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_eventBooking(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}
}//endclass
