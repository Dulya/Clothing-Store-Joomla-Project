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

class plgAcysmsRsevent extends JPlugin{

	var $debug = false;

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_rseventspro')) return;
		parent:: __construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'rsevent');
			$this->params = new acysmsParameter($plugin->params);
		}
		$lang = JFactory::getLanguage();
		$lang->load('com_rseventspro', JPATH_ADMINISTRATOR);
	}

	private function loadScript($tagName, $nextRsEventsParameter){
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

				for(i = 0; i < window.parent.document.getElementsByName("typeofinsert_RsEventsPro").length; i++){
					if(window.parent.document.getElementsByName("typeofinsert_RsEventsPro").item(i).checked){
						type += window.parent.document.getElementsByName("typeofinsert_RsEventsPro").item(i).value + ",";
					}
				}
				if(type) otherinfo += "| type:" + type;
				var wrapNumber;

				<?php if($tagName != 'rsEvents') echo ' wrapNumber = window.parent.document.getElementById("nbEvent_RsEventsPro").value;'; ?>

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i)){
						if(wrapNumber)
							tag = tag + "{" + "<?php echo $tagName; ?>:" + wrapNumber + "| " + "<?php echo $nextRsEventsParameter; ?>:" + selectedContents[i] + otherinfo + "}";else
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

	public function onACYSMSchooseCategoryEvents_RsEventsPro(){
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$my = JFactory::getUser();

		$this->loadScript('nextRsEvents', 'cat');

		$paramBase = ACYSMS_COMPONENT.'categoryRsEventsPro';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'cat.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$contentToTrigger = $app->getUserStateFromRequest(ACYSMS_COMPONENT.".content", 'content', 'cmd');

		$rows = array();
		$searchFields[] = "cat.id";
		$searchFields[] = "title";
		$searchFields[] = "alias";

		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS cat.id AS id, title, alias FROM #__categories AS cat LEFT JOIN #__rseventspro_taxonomy AS taxo ON cat.id = taxo.id WHERE taxo.type = "category"';
		if(!empty ($filters)){
			$request .= ' AND ('.implode(') AND (', $filters).')';
		}
		$request .= ' GROUP BY cat.id';
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
						<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_FILTER_CATEGORY'), 'cat.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_ALIAS'), 'cat.alias', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_GLOBAL_SORT_ID'), 'cat.id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
							$text = '<b>'.JText::_('COM_RSEVENTSPRO_SUBMENU_CATEGORIES').': </b>'.$row->title;
							echo ACYSMS::tooltip($text, $row->title, '', $row->alias);
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



	function onACYSMSGetTags(&$tags){
		$app = JFactory::getApplication();

		$tags['eventTags']['rsevent'] = new stdClass();
		$tags['eventTags']['rsevent']->name = JText::_('SMS_RSEVENTSPRO');

		$nbEvent = array();
		for($i = 1; $i <= 25; $i += 1){
			$nbEvent[] = JHTML::_('select.option', $i, $i);
		}
		$nbEventSelection = JHTML::_('select.genericlist', $nbEvent, "name", "style=\"width:auto;\"", 'value', 'text', 'all', 'nbEvent_RsEventsPro');


		if(!$app->isAdmin()){
			$ctrl = 'fronttag';
		}else{
			$ctrl = 'tag';
		}

		$eventSelection = '<div>'.JText::_('SMS_SELECT_EVENT').' <a class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=tag&fctplug=chooseEvents_RsEventsPro\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></div><br />';
		$resultCategory = '<div>'.JText::sprintf('SMS_SELECT_X_EVENTS_FILTERED_X', $nbEventSelection, JText::_('COM_RSEVENTSPRO_SUBMENU_CATEGORIES')).' <a id="listCatLoc_RsEventsPro" class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=tag&fctplug=chooseCategoryEvents_RsEventsPro&context=tags\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></div><br />';
		$buttonInsertTag = '<input type="button" id="insertTagButton_RsEventsPro" onclick="insertNextEvent_RsEventsPro();" value="'.JText::_('SMS_INSERT_TAG').'" style="display:none"/>';

		$typeOfInsert = array();
		$typeOfInsert[] = "<input type='checkbox' value='eventname' id='eventname_RsEventsPro' name='typeofinsert_RsEventsPro'><label for='eventname_RsEventsPro'>".JText::_('COM_RSEVENTSPRO_ORDERING_NAME')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='startdate' id='startdate_RsEventsPro' name='typeofinsert_RsEventsPro'><label for='startdate_RsEventsPro'>".JText::_('COM_RSEVENTSPRO_ORDERING_START_DATE')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='enddate' id='enddate_RsEventsPro' name='typeofinsert_RsEventsPro'><label for='enddate_RsEventsPro'>".JText::_('COM_RSEVENTSPRO_ORDERING_END_DATE')."</label>";
		$typeOfInsert[] = "<input type='checkbox' value='location' id='location_RsEventsPro' name='typeofinsert_RsEventsPro'><label for='location_RsEventsPro'>".JText::_('COM_RSEVENTSPRO_SUBMENU_LOCATIONS')."</label>";

		$tags['eventTags']['rsevent']->content = "<table class='acysms_blocktable' cellpadding='1' width='100%'><tbody>";
		$tags['eventTags']['rsevent']->content .= '<tr><td style="font-weight: bold" align="left">'.JText::_('SMS_INSERT_INFORMATION').'</th></tr>';
		$tags['eventTags']['rsevent']->content .= "<tr><td>".$typeOfInsert[0].$typeOfInsert[1].$typeOfInsert[2].$typeOfInsert[3]."</td></tr>";
		$tags['eventTags']['rsevent']->content .= '<tr><td style="font-weight: bold" align="left">'.JText::_('SMS_EVENT_SELECTION').'</th></tr>';
		$tags['eventTags']['rsevent']->content .= '<tr><td align="left">'.$eventSelection.'</td></tr>'; // select one event
		$tags['eventTags']['rsevent']->content .= '<tr><td align="left">'.$resultCategory.$buttonInsertTag; //select multiple events (the next XX)+button
		$tags['eventTags']['rsevent']->content .= '</td></tr></tbody></table>';



		$tags['eventTags']['rseventauto'] = new stdClass();
		$tags['eventTags']['rseventauto']->name = JText::sprintf('SMS_EVENTS_AUTOMESSAGE_TAGS_X', 'RsEventsPro');

		$tableField = array();
		$tableField['eventname'] = JText::_('COM_RSEVENTSPRO_ORDERING_NAME');
		$tableField['startdate'] = JText::_('COM_RSEVENTSPRO_ORDERING_START_DATE');
		$tableField['enddate'] = JText::_('COM_RSEVENTSPRO_ORDERING_END_DATE');
		$tableField['location'] = JText::_('COM_RSEVENTSPRO_ORDERING_LOCATION');
		$tableField['description'] = JText::_('COM_RSEVENTSPRO_FILTER_DESCRIPTION');
		$tableField['owner'] = JText::_('COM_RSEVENTSPRO_ORDERING_OWNER');
		$tableField['url'] = JText::_('COM_RSEVENTSPRO_LOCATION_URL');
		$tableField['email'] = JText::_('COM_RSEVENTSPRO_EVENT_EMAIL');
		$tableField['phone'] = JText::_('COM_RSEVENTSPRO_EVENT_PHONE');


		$tags['eventTags']['rseventauto']->content = "<table class='adminlist table table-striped table-hover' cellpadding='1' width='100%'><tbody>";
		$k = 0;
		foreach($tableField as $oneField => $title){
			$tags['eventTags']['rseventauto']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{rsEventsAuto:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['eventTags']['rseventauto']->content .= '</tbody></table>';


		?>
		<script language="javascript" type="text/javascript">

			function insertNextEvent_RsEventsPro(){
				if(document.getElementById('selectionType_RsEventsPro').value == 'all'){
					var type = "";
					var nbEvents = document.getElementById('nbEvent_RsEventsPro').value;
					var tag = 'nextRsEvents:' + nbEvents
					for(i = 0; i < document.getElementsByName("typeofinsert_RsEventsPro").length; i++){
						if(document.getElementsByName("typeofinsert_RsEventsPro").item(i).checked){
							type += document.getElementsByName("typeofinsert_RsEventsPro").item(i).value + ",";
						}
					}
					if(type) tag += "| type:" + type;
					insertTag("{" + tag + "}");
				}
			}
		</script>

	<?php }


	public function onACYSMSchooseEvent_RsEventsPro(){
		$idToUpdate = JRequest::getCmd('idToUpdate');

		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$my = JFactory::getUser();

		$this->loadScript('rsEvents', '');

		$paramBase = ACYSMS_COMPONENT.'rsevents';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'ev.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$contentToTrigger = $app->getUserStateFromRequest(ACYSMS_COMPONENT.".content", 'content', 'cmd');


		$searchFields = array();
		$searchFields[] = "ev.name";
		$searchFields[] = "lo.address";
		$searchFields[] = "cat.title";

		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS ev.id as id, ev.name AS title, start, end, lo.address AS location, allday, GROUP_CONCAT(distinct cat.title) AS category
					FROM #__rseventspro_events AS ev INNER JOIN #__rseventspro_locations lo ON ev.location = lo.id
					INNER JOIN #__rseventspro_taxonomy AS taxo on taxo.ide = ev.id
					INNER JOIN #__categories AS cat on taxo.id = cat.id
					WHERE taxo.type="category"';
		if(!empty ($filters)){
			$request .= ' AND ('.implode(') AND (', $filters).')';
		}
		$request .= ' GROUP BY ev.id';
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
							<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_ORDERING_NAME'), 'ev.name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_ORDERING_START_DATE'), 'start', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_ORDERING_END_DATE'), 'end', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_SUBMENU_LOCATIONS'), 'lo.address', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_SUBMENU_CATEGORIES'), 'cat.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'ev.id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
								<input type="checkbox" value="<?php echo $row->id ?>" id="cb<?php echo $i; ?>" onclick="addSelectedEventAutoMessage();" style="cursor:pointer;">
							</td>
							<td align="center" id="eventNamecb<?php echo $i; ?>">
								<?php echo $row->title; ?>
							</td>
							<td align="center">
								<?php echo $row->start; ?>
							</td>
							<td align="center">
								<?php echo (empty($row->allday)) ? $row->end : JText::_('COM_RSEVENTSPRO_EVENT_ALL_DAY'); ?>
							</td>
							<td align="center">
								<?php echo $row->location; ?>
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

	public function onACYSMSchooseEvents_RsEventsPro(){
		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$my = JFactory::getUser();

		$this->loadScript('rsEvents', '');

		$paramBase = ACYSMS_COMPONENT.'rsevents';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'ev.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower($pageInfo->search);
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$contentToTrigger = $app->getUserStateFromRequest(ACYSMS_COMPONENT.".content", 'content', 'cmd');


		$searchFields = array();
		$searchFields[] = "ev.name";
		$searchFields[] = "lo.address";
		$searchFields[] = "cat.title";

		if(!empty ($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$request = 'SELECT SQL_CALC_FOUND_ROWS ev.id as id, ev.name AS title, start, end, lo.address AS location, allday, GROUP_CONCAT(distinct cat.title) AS category
					FROM #__rseventspro_events AS ev INNER JOIN #__rseventspro_locations lo ON ev.location = lo.id
					INNER JOIN #__rseventspro_taxonomy AS taxo on taxo.ide = ev.id
					INNER JOIN #__categories AS cat on taxo.id = cat.id
					WHERE taxo.type="category"';
		if(!empty ($filters)){
			$request .= ' AND ('.implode(') AND (', $filters).')';
		}
		$request .= ' GROUP BY ev.id';
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
						<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_EVENT_STARTING'), 'dtstart', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_EVENT_ENDING'), 'dtend', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_FILTER_LOCATION'), '', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title">
						<?php echo JHTML::_('grid.sort', JText::_('COM_RSEVENTSPRO_FILTER_CATEGORY'), 'categories.title', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
					<tr id="content<?php echo $row->id ?>" class="<?php echo "row$k"; ?>" onclick="addEventTag(<?php echo $row->id.",'row$k'" ?>);" style="cursor:pointer;">
						<td class="acysmstdcheckbox"></td>
						<td>
							<?php
							echo $row->title;
							?>
						</td>
						<td>
							<?php echo $row->start; ?>
						</td>
						<td>
							<?php echo $row->end; ?>
						</td>
						<td>
							<?php echo $row->location; ?>
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



	function onACYSMSReplaceTags(&$message, $send = true){
		$display = array('startdate' => JText::_('COM_RSEVENTSPRO_ORDERING_START_DATE').': ', 'enddate' => JText::_('COM_RSEVENTSPRO_ORDERING_END_DATE').': ', 'eventname' => JText::_('COM_RSEVENTSPRO_ORDERING_NAME').': ', 'location' => JText::_('COM_RSEVENTSPRO_ORDERING_LOCATION').': ');
		$this->_replaceNextEventsTag($message, $display);
		$this->_replaceEventTag($message, $display);
	}

	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$this->_replaceEventsAutoTag($message, $user);
	}

	private function _replaceEventTag(&$message, $display){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'rsEvents');
		if(empty($tags)) return;


		$db = JFactory::getDBO();
		foreach($tags as $tagname => $tag){
			if(empty($tag->type)){
				$message->message_body = str_replace($tagname, 'Please select an information to display', $message->message_body);
				continue;
			}
			$id = $tag->id;
			$query = 'SELECT *,ev.email AS email,ev.description AS description, lo.address AS location, ev.name AS eventname, start AS startdate, end AS enddate FROM #__rseventspro_events AS ev INNER JOIN #__rseventspro_locations AS lo ON ev.location = lo.id WHERE ev.id='.intval($id);
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
		$tags = $helperPlugin->extractTags($message, 'nextRsEvents');
		if(empty($tags)) return;

		$db = JFactory::getDBO();
		foreach($tags as $tagname => $tag){
			if(empty($tag->type)){
				$message->message_body = str_replace($tagname, 'Please select an information to display', $message->message_body);
				continue;
			}

			$lim = $tag->id;
			$whereClause = '';
			if(!empty($tag->cat)){
				$whereClause .= ' AND cat.id = '.intval($tag->cat);
			}

			$query = 'SELECT DISTINCT ev.id as eventId
			FROM #__rseventspro_events AS ev
			INNER JOIN #__rseventspro_locations AS lo ON ev.location = lo.id
			INNER JOIN #__rseventspro_taxonomy AS taxo ON taxo.ide = ev.id
			INNER JOIN #__categories AS cat ON taxo.id = cat.id
			WHERE taxo.type="category" AND start >= NOW() '.$whereClause.'
			ORDER BY start ASC LIMIT '.intval($lim);
			$db->setQuery($query);
			$result = $db->loadObjectList();

			$value = "";
			foreach($result as $oneResult){
				$value .= '{rsEvents:'.$oneResult->eventId.'|type:'.$tag->type.'}';
				$value .= "\n";
			}
			$message->message_body = str_replace($tagname, $value, $message->message_body);
		}
	}

	private function _transformDescription($text){
		$text = str_replace('<br />', "\n", $text);
		$text = strip_tags($text);
		return $text;
	}

	private function _replaceEventsAutoTag(&$message, $user){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'rsEventsAuto');
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

			$query = 'SELECT *,us.name AS owner,ev.email AS email,ev.description AS description, lo.address AS location, ev.name AS eventname, start AS startdate, end AS enddate, ev.URL as URL FROM #__rseventspro_events AS ev INNER JOIN #__rseventspro_locations AS lo ON ev.location = lo.id INNER JOIN #__users AS us ON us.id = ev.owner WHERE ev.id IN('.implode(",", $paramQueue).')';
			$db->setQuery($query);
			$events = $db->loadObjectList();
			foreach($events as $oneEvent){
				if($tag->id == 'description') //if the tag is {rsEventsAuto:description}
				{
					$oneEvent->{$tag->id} = $this->_transformDescription($oneEvent->{$tag->id});
				}else if($tag->id == 'enddate' && $oneEvent->allday){
					$oneEvent->{$tag->id} = JText::_('COM_RSEVENTSPRO_EVENT_ALL_DAY');
				}else if($tag->id == 'startdate' && $oneEvent->allday){
					$oneEvent->{$tag->id} = date("Y-m-d", strtotime($oneEvent->{$tag->id}));
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
			if($oneType == 'enddate' && $oneResult->allday == '1'){
				$value .= JText::_('COM_RSEVENTSPRO_EVENT_ALL_DAY');
			}else if($oneType == 'startdate' && $oneResult->allday == '1'){
				$value .= date("Y-m-d", strtotime($oneResult->$oneType));
			}else{
				$value .= $oneResult->$oneType;
			}
			$value .= "\n";
		}
		return $value;
	}

	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();

		if(!$app->isAdmin()){
			$helperPlugin = ACYSMS::get('helper.plugins');
			if(!$helperPlugin->allowSendByGroups('rseventspro')) return;
		}

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_X_SUBSCRIBERS', 'RsEvents');
		$filters['eventFilters']['rseventspro'] = $newFilter;
	}

	function onACYSMSDisplayFilterParams_RsEventsPro($message){
		$lang = JFactory::getLanguage();
		$lang->load('com_rseventspro', JPATH_ADMINISTRATOR);
		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$ctrl = 'fronttag';
		}else    $ctrl = 'tag';

		$eventSelection = array();
		$eventSelection[] = JHTML::_('select.option', 'AND', JText::_('SMS_ALL_THESE_EVENTS'));
		$eventSelection[] = JHTML::_('select.option', 'OR', JText::_('SMS_AT_LEAST_ONE_OF_THESE_EVENTS'));
		$nbEventSelectionDropDown = JHTML::_('select.genericlist', $eventSelection, "data[message][message_receiver][standard][rseventspro][events][eventSelection]", 'onclick="document.getElementById(\'sms_eventFilterEvent\').checked = \'checked\'"', 'value', 'text', 'AND', 'nbEvent_RsEventsPro');

		$conditionsData = array();
		$conditionsData[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$conditionsData[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$subscriptionStatus = array('' => ' - - - ', 'incomplete' => 'COM_RSEVENTSPRO_RULE_STATUS_INCOMPLETE', 'complete' => 'COM_RSEVENTSPRO_RULE_STATUS_COMPLETE', 'denied' => 'COM_RSEVENTSPRO_RULE_STATUS_DENIED');

		$subscriptionData = array();
		foreach($subscriptionStatus as $oneSubscription => $oneJText) $subscriptionData[] = JHTML::_('select.option', $oneSubscription, JText::_($oneJText));

		$eventName = '';
		if(!empty($message->message_receiver['standard']['rseventspro']['events']['eventsName'])) $eventName = $message->message_receiver['standard']['rseventspro']['events']['eventsName'];

		$categoryName = '';
		if(!empty($message->message_receiver['standard']['rseventspro']['category']['categoryName'])) $categoryName = $message->message_receiver['standard']['rseventspro']['category']['categoryName'];

		$filterString = '';
		for($i = 0; $i < 3; $i++){
			$subscriptionStatusDropDown = JHTML::_('select.genericlist', $subscriptionData, 'data[message][message_receiver][standard][rseventspro][subscriptionFilters]['.$i.'][status]', '', 'value', 'text', '');
			$conditionDropDown = JHTML::_('select.genericlist', $conditionsData, 'data[message][message_receiver][standard][rseventspro][subscriptionFilters]['.$i.'][condition]', '', 'value', 'text', 'AND', 'nbEvent');
			$filterString .= $subscriptionStatusDropDown;
			if($i < 2) $filterString .= $conditionDropDown;
		}

		$eventChecked = '';
		$categoryChecked = '';
		$autoMsgChecked = '';

		$ctrl = 'cpanel';
		if(!$app->isAdmin()) $ctrl = 'frontcpanel';

		if(empty($message->message_receiver['standard']['rseventspro']['eventFilter']) || $message->message_receiver['standard']['rseventspro']['eventFilter'] == 'events') $eventChecked = 'checked="checked"';
		if(!empty($message->message_receiver['standard']['rseventspro']['eventFilter']) && $message->message_receiver['standard']['rseventspro']['eventFilter'] == 'category') $categoryChecked = 'checked="checked"';
		if(!empty($message->message_receiver['standard']['rseventspro']['eventFilter']) && $message->message_receiver['standard']['rseventspro']['eventFilter'] == 'autoMsg') $autoMsgChecked = 'checked="checked"';

		echo JText::sprintf('SMS_SEND_TO_USERS_WHICH_ARE', $filterString);
		echo '<br /><input type="radio" name="data[message][message_receiver][standard][rseventspro][eventFilter]" value="events" id="sms_eventFilterEvent_RsEventsPro" '.$eventChecked.'/>
		<label for="sms_eventFilterEvent_RsEventsPro">'.JText::sprintf('SMS_ASSIGNED_TO', '</label>'.$nbEventSelectionDropDown);
		echo JText::_('SMS_SELECT_EVENT').' :  <span id="displayedRsEventsPro">'.$eventName.'</span><a class="modal" style="cursor:pointer" onclick="document.getElementById(\'sms_eventFilterEvent_RsEventsPro\').checked = \'checked\';window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl='.$ctrl.'&task=plgtrigger&plg=rsevent&fctName=chooseEvent_RsEventsPro&tmpl=component&idToUpdate=RsEventsPro\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label>';
		echo '<input type="radio" name="data[message][message_receiver][standard][rseventspro][eventFilter]" value="category" id="sms_eventFilterCategory_RsEventsPro" '.$categoryChecked.'/> <label for="sms_eventFilterCategory_RsEventsPro">'.JText::_('SMS_ASSIGNED_TO_EVENT_FROM_CATEGORY').' : <span id="displayedRsEventsProCategory"/>'.$categoryName.'</span><a class="modal" style="cursor:pointer" onclick="document.getElementById(\'sms_eventFilterCategory_RsEventsPro\').checked = \'checked\'; window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl='.$ctrl.'&task=plgtrigger&plg=rsevent&fctName=chooseCategoryEvents_RsEventsPro&tmpl=component&context=filters&idToUpdate=RsEventsProCategory\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';
		echo '<input type="radio" name="data[message][message_receiver][standard][rseventspro][eventFilter]" value="autoMsg" id="sms_eventFilterAutoMsg_RsEventsPro" '.$autoMsgChecked.'/> <label for="sms_eventFilterAutoMsg_RsEventsPro">'.JText::_('SMS_ASSIGNED_TO_EVENT_CHOSEN_AUTO_MESSAGE_OPTIONS').'</label><br />';

		echo '<input type="hidden" name="data[message][message_receiver][standard][rseventspro][category][categoryId]" id="selectedRsEventsProCategory"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][rseventspro][category][categoryName]" id="hiddenRsEventsProCategory"/>';
		echo '<input type="hidden" name="data[message][message_receiver][standard][rseventspro][events][eventsId]" id="selectedRsEventsPro"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][rseventspro][events][eventsName]" id="hiddenRsEventsPro"/>';
	}

	function onACYSMSSelectData_RsEventsPro(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['rseventspro'])) return;
		if(empty($message->message_receiver['standard']['rseventspro']['eventFilter'])) return;
		if(empty($message->message_receiver['standard']['rseventspro']['subscriptionFilters'])) return;

		$eventFilter = $message->message_receiver['standard']['rseventspro']['eventFilter'];

		$whereCondition = array();
		$filterCondition = '';
		$catId = '';

		$acyquery->join['rseventsprouser'] = 'JOIN #__rseventspro_users AS rsUser ON rsUser.idu = joomusers.id';
		$acyquery->join['rseventspro'] = 'JOIN #__rseventspro_events as rsEvents ON rsEvents.id = rsUser.ide ';


		foreach($message->message_receiver['standard']['rseventspro']['subscriptionFilters'] as $oneFilterNumber => $oneFilter){
			if(empty($oneFilter['status'])) continue;
			$status = $oneFilter['status'];

			if(!empty($filterCondition)) $whereCondition[] = $filterCondition;
			if($status == 'complete'){
				$whereCondition[] = 'rsUser.state = 1';
			}else if($status == 'incomplete'){
				$whereCondition[] = 'rsUser.state = 0';
			}else if($status == 'denied') $whereCondition[] = 'rsUser.state = 2';

			if(!empty($oneFilter['condition'])) $filterCondition = $oneFilter['condition'];
		}
		if(!empty($whereCondition)) $acyquery->where[] = implode(' ', $whereCondition);

		if($eventFilter == 'events'){
			$eventConditions = array();
			$eventSelection = '';
			if(empty($message->message_receiver['standard']['rseventspro'][$eventFilter]['eventsId'])){
				$acyquery->where[] = '1=0';
				return;
			}
			$eventsId = explode(',', $message->message_receiver['standard']['rseventspro'][$eventFilter]['eventsId']);

			foreach($eventsId as $oneEventId){
				if(!empty($eventSelection)) $eventConditions[] = $message->message_receiver['standard']['rseventspro']['events']['eventSelection'];
				$eventConditions[] = 'rsEvents.id = '.intval($oneEventId);
				$eventSelection = $message->message_receiver['standard']['rseventspro']['events']['eventSelection'];
			}
			$acyquery->where[] = implode(' ', $eventConditions);
		}else if($eventFilter == 'category'){
			if(empty($message->message_receiver['standard']['rseventspro'][$eventFilter]['categoryId'])){
				$acyquery->where[] = '1=0';
				return;
			}else    $catId = $message->message_receiver['standard']['rseventspro'][$eventFilter]['categoryId'];
		}else if($eventFilter == 'autoMsg' && $message->message_receiver['auto']['rseventspro']['typeselectionradio'] == 'category'){
			if($message->message_type == 'auto' && empty($message->message_receiver['auto']['rseventspro']['idcat'])){
				$acyquery->where[] = '1=0';
				return;
			}else $catId = $message->message_receiver['auto']['rseventspro']['idcat'];
		}
		if(!empty($catId)){
			$acyquery->join['taxo'] = 'JOIN #__rseventspro_taxonomy AS taxo ON taxo.ide = rsEvents.id';
			$acyquery->where[] = 'taxo.id = '.intval($catId);
			$acyquery->where[] = 'taxo.type = "category"';
		}
	}


	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_BASED_ON_EVENT_X', 'RsEventsPro');
		$types['rseventspro'] = $newType;
	}

	function onACYSMSDisplayParamsAutoMessage_RsEventsPro(){
		$result = '';
		$db = JFactory::getDBO();

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i += 5) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$birthdayautotime = new stdClass();
		$birthdayautotime->hourField = JHTML::_('select.genericlist', $hours, 'data[message][message_receiver][auto][rseventspro][hour]', 'style="width:50px;" class="inputbox"', 'value', 'text', '08');
		$birthdayautotime->minField = JHTML::_('select.genericlist', $min, 'data[message][message_receiver][auto][rseventspro][min]', 'style="width:50px;" class="inputbox"', 'value', 'text', '00');


		$importData = array();
		$importvalues = array();

		$delay_birthday = '<input type="text" name="data[message][message_receiver][auto][rseventspro][daybefore]" class="inputbox" style="width:50px" value="0">';

		$timeValues = array();
		$timeValues[] = JHTML::_('select.option', 'before', JText::_('SMS_BEFORE'));
		$timeValues[] = JHTML::_('select.option', 'after', JText::_('SMS_AFTER'));
		$timeValueDropDown = JHTML::_('select.genericlist', $timeValues, "data[message][message_receiver][auto][rseventspro][time]", 'style="width:auto" size="1" class="chzn-done"', 'value', 'text');

		$catName = '';
		if(!empty($message->message_receiver['auto']['rseventspro']['namecat'])) $catName = $message->message_receiver['auto']['rseventspro']['namecat'];

		$radioList = '<input type="radio" name="data[message][message_receiver][auto][rseventspro][typeselectionradio]" value="eachEvent" checked="checked" id="eventSelectionType_eachEvent"/> <label for="eventSelectionType_eachEvent">'.JText::_('SMS_EACH_EVENT').'</label>';
		$radioList .= '<input type="radio" name="data[message][message_receiver][auto][rseventspro][typeselectionradio]" value="category" id="eventSelectionType_category"/> <label for="eventSelectionType_category">'.JText::_('SMS_BASED_ON_CATEGORY').'</label> : <span id="displayedRsEventsProAutoCategory"/>'.$catName.'</span><a class="modal" style="cursor:pointer" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&ctrl=cpanel&task=plgtrigger&plg=rsevent&fctName=chooseCategoryEvents_rseventspro&tmpl=component&context=filters&idToUpdate=RsEventsProAutoCategory\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></label><br />';

		$result .= JText::sprintf('SMS_SEND_EVENTS_TIME', $delay_birthday, $timeValueDropDown, $birthdayautotime->hourField.' : '.$birthdayautotime->minField);
		$result .= '<br />'.$radioList;

		echo $result;

		echo '<input type="hidden" name="data[message][message_receiver][auto][rseventspro][idcat]" id="selectedRsEventsProAutoCategory"/><br />';
		echo '<input type="hidden" name="data[message][message_receiver][auto][rseventspro][namecat]" id="hiddenRsEventsProAutoCategory"/>';
	}


	function onACYSMSDailyCron(){
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$config = ACYSMS::config();
		$allMessages = $messageClass->getAutoMessage('rseventspro');
		if(empty($allMessages)){
			if($this->debug) $this->messages[] = 'No auto message configured with RSEvents, you should first <a href="index.php?option=com_acysms&ctrl=message&task=add" target="_blank">create a message</a> using the type : Automatic and RsEvents ';
			return;
		}

		foreach($allMessages as $oneMessage){
			$time = empty($oneMessage->message_receiver['auto']['rseventspro']['time']) ? 'before' : $oneMessage->message_receiver['auto']['rseventspro']['time'];

			if($time == 'before'){
				$sendingTime = time() + 86400 + (intval($oneMessage->message_receiver['auto']['rseventspro']['daybefore']) * 86400);
			}else if($time == 'after') $sendingTime = time() + 86400 - (intval($oneMessage->message_receiver['auto']['rseventspro']['daybefore']) * 86400);

			$sendingDay = date('d', $sendingTime);
			$sendingMonth = date('m', $sendingTime);
			if($time == 'before'){
				$senddate = ACYSMS::getTime(date('Y').'-'.$sendingMonth.'-'.$sendingDay.' '.$oneMessage->message_receiver['auto']['rseventspro']['hour'].':'.$oneMessage->message_receiver['auto']['rseventspro']['min']) - (intval($oneMessage->message_receiver['auto']['rseventspro']['daybefore']) * 86400);
			}else if($time == 'after') $senddate = ACYSMS::getTime(date('Y').'-'.$sendingMonth.'-'.$sendingDay.' '.$oneMessage->message_receiver['auto']['rseventspro']['hour'].':'.$oneMessage->message_receiver['auto']['rseventspro']['min']) + (intval($oneMessage->message_receiver['auto']['rseventspro']['daybefore']) * 86400);


			$newDate = date('Y-m-d', $sendingTime);
			$messageInfo = $oneMessage->message_receiver['auto']['rseventspro'];
			$selectionType = $messageInfo['typeselectionradio'];
			$idCat = $messageInfo['idcat'];
			$whereClause = array();
			$whereClause[] = 'taxo.type="category"';
			switch($selectionType){
				case 'every':
					break;
				case 'category':
					$whereClause[] = "cat.id = ".intval($idCat);
			}
			switch($messageInfo['time']){
				case 'before':
					$whereClause[] = 'DATE_FORMAT(start, "%Y-%m-%d") = '.$db->Quote($newDate);
					break;
				case 'after':
					$whereClause[] = 'DATE_FORMAT(end, "%Y-%m-%d") = '.$db->Quote($newDate).' OR (allday = 1 AND DATE_FORMAT(start, "%Y-%m-%d") = '.$db->Quote($newDate).')';
					break;
			}

			$queryEvent = 'SELECT DISTINCT rsEvents.id AS eventId
			FROM #__rseventspro_events AS rsEvents
			INNER JOIN #__rseventspro_taxonomy AS taxo ON rsEvents.id = taxo.ide
			INNER JOIN #__categories AS cat ON cat.id = taxo.id';
			if(!empty($whereClause)) $queryEvent .= ' WHERE ('.implode(') AND (', $whereClause).')';
			$db->setQuery($queryEvent);
			$res = $db->loadObjectList();

			$event = array();
			foreach($res as $oneResult){
				$event[] = intval($oneResult->eventId);
			}
			$paramQueue = new stdClass();
			$paramQueue->eventsList = $event;

			if(empty($event)){
				$this->messages[] = 'RsEventsPro plugin: 0 automatic SMS inserted in the queue for '.$sendingDay.'-'.$sendingMonth.' for the SMS '.$oneMessage->message_id;
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
			$this->messages[] = 'RsEventsPro plugin: '.$nbInserted.' automatic SMS inserted in the queue for '.$sendingDay.'-'.$sendingMonth.' for the SMS '.$oneMessage->message_subject;
		}
	}

	function onACYSMSTestPlugin(){
		$this->debug = true;
		$this->onACYSMSDailyCron();
		ACYSMS::display($this->messages);
	}



	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_RSEVENTSPRO');
		$authorizedFilters['rseventspro'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_RsEventsPro(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

}//endclass
