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

class plgSystemAcysmsContent extends JPlugin{

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) return false;
		parent::__construct($subject, $config);
	}




	function onACYSMSGetTags(&$tags){
		$app = JFactory::getApplication();
		JHTML::_('behavior.modal', 'a.modal');
		$checkbox = '';
		$js = '<script type="text/javascript">
					function displayWrap(){
						if(document.getElementById("body").checked == true) document.getElementById("wrap").style.display = "block";
						else document.getElementById("wrap").style.display = "none";
					}

					function getContentDropwDownValue(){
						var selectElmt = document.getElementById("dropdowContent");
						return selectElmt.options[selectElmt.selectedIndex].value;
					}
				</script>';

		$tags['communityTags']['joomlacontent'] = new stdClass();
		$tags['communityTags']['joomlacontent']->name = JText::_('SMS_INSERT_CONTENT');

		$checkbox .= '<div style="margin:15px 0 "><input type="checkbox" id="title" name="checkboxInsert" value="title" checked="checked"><label for="title">'.JText::_('SMS_TITLE').'</label> ';
		$checkbox .= '<input type="checkbox" id="body" name="checkboxInsert" 	value="body" onclick="displayWrap()"><label for="body">'.JText::_('SMS_CONTENT_BODY').'</label> ';
		$checkbox .= '<input type="checkbox" id="link" name="checkboxInsert" value="link"><label for="link">'.JText::_('SMS_LINK').'</label> </div>';
		$wrap = '<div id="wrap" style="display:none">'.JText::sprintf('SMS_ONLY_FIRST_X_CHARACTERS', '<input type="text" id="wrapCharacterNumber" class="inputbox" style="width:20px;"/>').'</div>';

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dropdownContentData = array();
		$dispatcher->trigger('onACYSMSDisplayTagDropdown', array(&$dropdownContentData));
		$dropdownNbArticles = JHTML::_('select.genericlist', $dropdownContentData, 'dropdowContent', 'style="width:100px;" class="inputbox"', 'value', 'text', '');

		$contentSelection = '<div id="contentSelection">'.JText::_('SMS_INSERT_CONTENT_FROM').' : '.$dropdownNbArticles.'</div>';


		if(!$app->isAdmin()){
			$ctrl = 'fronttag';
		}else $ctrl = 'tag';

		$articleSelection = '<div id="articleSelection">'.JText::_('SMS_SELECT_ARTICLE').' : <a class="modal"  onclick="var contentToTrigger = getContentDropwDownValue(); window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=tag&fctplug=chooseArticle&content=\'+contentToTrigger);return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a></div><br />';

		$dropdownLastFirst = array();
		$dropdownLastFirstData[] = JHTML::_('select.option', 'id,DESC', JText::_('SMS_LAST'));
		$dropdownLastFirstData[] = JHTML::_('select.option', 'id,ASC', JText::_('SMS_FIRST'));
		$dropdownLastFirst = JHTML::_('select.genericlist', $dropdownLastFirstData, 'dropdownLastFirst', 'style="width:100px;" class="inputbox"', 'value', 'text', '');

		$dropdownNbArticlesData = array();
		for($i = 1; $i < 11; $i++){
			$dropdownNbArticlesData[] = JHTML::_('select.option', $i, $i);
		}
		$dropdownNbArticles = JHTML::_('select.genericlist', $dropdownNbArticlesData, 'dropdownNbArticles', 'style="width:50px;" class="inputbox"', 'value', 'text', '');

		$articleFromCategory = JText::sprintf('SMS_THE_X_Y_ARTICLES_FROM_CATEGORY', $dropdownLastFirst, $dropdownNbArticles).' : <a class="modal"  onclick="var contentToTrigger = getContentDropwDownValue(); window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=tag&fctplug=chooseCategory&content=\'+contentToTrigger);return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';

		$selectFrom = JText::_('SMS_FROM').'<ul><li>'.$articleSelection.'</li><li>'.$articleFromCategory.'</li></ul>';

		$tags['communityTags']['joomlacontent']->content = $js.$contentSelection.$checkbox.$wrap.$selectFrom;
	}

	public function onACYSMSchooseArticle(){

		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYSMS_COMPONENT.'content';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'article.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->filter_cat = $app->getUserStateFromRequest($paramBase.".filter_cat", 'filter_cat', '', 'int');
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');


		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$contentToTrigger = $app->getUserStateFromRequest($paramBase, 'content', 'cmd');

		$rows = array();
		$categoriesValues = array();

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onACYSMSchooseArticle_'.$contentToTrigger, array(&$pageInfo, &$rows, &$categoriesValues));

		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		?>
		<script language="javascript" type="text/javascript">
			var selectedContents = new Array();
			function addArticle(contentid, rowClass){
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

				for(i = 0; i < window.parent.document.getElementsByName("checkboxInsert").length; i++){
					if(window.parent.document.getElementsByName("checkboxInsert").item(i).checked){
						type += window.parent.document.getElementsByName("checkboxInsert").item(i).id + ",";
					}
				}
				if(type) otherinfo += "| type:" + type;

				var wrapNumber = window.parent.document.getElementById("wrapCharacterNumber").value;
				if(wrapNumber) otherinfo += "| wrap:" + wrapNumber;

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i)){
						tag = tag + "{" + window.parent.getContentDropwDownValue() + ":" + selectedContents[i] + otherinfo + "}";
					}
				}
				window.document.getElementById("tagstring").value = tag;
			}
		</script>
		<table class="acysms_table_options">
			<tr>
				<td>
					<?php ACYSMS::listingSearch($pageInfo->search); ?>
				</td>
				<td nowrap="nowrap">
					<?php echo JHTML::_('select.genericlist', $categoriesValues, 'filter_cat', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int)$pageInfo->filter_cat); ?>
				</td>
			</tr>
		</table>

		<table class="acysms_table">
			<thead>
			<tr>
				<th class="title">
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_TITLE'), 'listingTitle', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_AUTHOR'), 'listingUsername', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
				</th>
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_CREATED'), 'listingCreatedDate', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'listingId', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="5">
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
				<tr id="content<?php echo $row->listingId ?>" class="<?php echo "row$k"; ?>"
					onclick="addArticle(<?php echo $row->listingId.",'row$k'" ?>);" style="cursor:pointer;">
					<td class="acysmstdcheckbox"></td>
					<td>
						<?php
						$text = '<b>'.JText::_('SMS_ALIAS').': </b>'.$row->alias;
						echo ACYSMS::tooltip($text, $row->listingTitle, '', $row->listingTitle);
						?>
					</td>
					<td>
						<?php
						if(!empty($row->name)){
							$text = '<b>'.JText::_('SMS_NAME').' : </b>'.$row->name;
							$text .= '<br /><b>'.JText::_('SMS_USERNAME').' : </b>'.$row->username;
							$text .= '<br /><b>'.JText::_('SMS_ID').' : </b>'.$row->created_by;
							echo ACYSMS::tooltip($text, $row->name, '', $row->name);
						}
						?>
					</td>
					<td align="center">
						<?php if(!empty($row->created)) echo JHTML::_('date', strip_tags($row->created), JText::_('DATE_FORMAT_LC4')); ?>
					</td>
					<td align="center">
						<?php echo $row->listingId; ?>
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


	public function onACYSMSchooseCategory(){

		$app = JFactory::getApplication();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYSMS_COMPONENT.'content';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'article.id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));
		$pageInfo->filter_cat = $app->getUserStateFromRequest($paramBase.".filter_cat", 'filter_cat', '', 'int');
		$pageInfo->lang = $app->getUserStateFromRequest($paramBase.".lang", 'lang', '', 'string');


		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$contentToTrigger = $app->getUserStateFromRequest(ACYSMS_COMPONENT.".content", 'content', 'cmd');

		$rows = array();
		$contentListing = array();

		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onACYSMSchooseCategory_'.$contentToTrigger, array(&$pageInfo, &$contentListing));

		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		?>

		<script language="javascript" type="text/javascript">
			<!--
			var selectedCategories = new Array();


			function insertCategories(secid, catid){
				var tag = '';
				var otherinfo = '';
				var type = ''

				if(catid != 0){
					if(selectedCategories[secid] && selectedCategories[secid][catid]){
						window.document.getElementById('content_cat' + catid).className = window.document.getElementById('content_cat' + catid).className.replace(" selectedrow", "");
						delete selectedCategories[secid][catid];
					}else{
						if(!selectedCategories[secid]) selectedCategories[secid] = new Array();
						window.document.getElementById('content_cat' + catid).className += ' selectedrow';
						selectedCategories[secid][catid] = 'content';

						if(selectedCategories[secid][0]){
							delete selectedCategories[secid][0];
							window.document.getElementById('content_sec' + secid).className = window.document.getElementById('content_sec' + secid).className.replace(" selectedrow", "");
						}
						window.document.getElementById('content_sec0_cat0').className = window.document.getElementById('content_sec0_cat0').className.replace(" selectedrow", "");
					}
				}else if(secid != 0){
					if(selectedCategories[secid] && selectedCategories[secid][catid]){
						window.document.getElementById('content_sec' + secid).className = window.document.getElementById('content_sec' + secid).className.replace(" selectedrow", "");
						if(selectedCategories[secid][catid]) delete selectedCategories[secid];
					}else{
						if(!selectedCategories[secid]) selectedCategories[secid] = new Array();

						window.document.getElementById('content_sec0_cat0').className = window.document.getElementById('content_sec0_cat0').className.replace(" selectedrow", "");

						for(var i  in selectedCategories[secid]){
							if(isNaN(i)) continue;
							if(selectedCategories[secid][i]) delete selectedCategories[secid][i];
							window.document.getElementById('content_cat' + i).className = window.document.getElementById('content_cat' + i).className.replace(" selectedrow", "");
						}
						window.document.getElementById('content_sec' + secid).className += ' selectedrow';
						selectedCategories[secid][catid] = 'content';
					}
				}else{
					if(selectedCategories[secid] && selectedCategories[secid][catid]){
						window.document.getElementById('content_sec' + secid + '_cat' + catid).className = window.document.getElementById('content_sec' + secid + '_cat' + catid).className.replace(" selectedrow", "");
					}else{
						window.document.getElementById('content_sec' + secid + '_cat' + catid).className += ' selectedrow';


						for(var i in selectedCategories){
							for(var y in selectedCategories[i]){
								if(window.document.getElementById('content_cat' + y) != undefined)    window.document.getElementById('content_cat' + y).className = window.document.getElementById('content_cat' + y).className.replace(" selectedrow", "");
							}
							if(window.document.getElementById('content_sec' + i) != undefined)    window.document.getElementById('content_sec' + i).className = window.document.getElementById('content_sec' + i).className.replace(" selectedrow", "");
						}
					}
					selectedCategories = new Array();
				}
				updateTag();
			}

			function updateTag(){
				var tag = '';
				var categories = '';
				sections = '';
				var otherinfo = '';
				var type = '';
				tag = '{' + window.parent.getContentDropwDownValue() + 'auto:';
				for(var i in selectedCategories){
					for(var y in selectedCategories[i]){
						if(selectedCategories[i][y] == 'content'){
							if(y != 0)    categories += y + '-';else if(i != 0) sections += i + '-';
						}
					}

				}
				if(categories) tag += '| cat:' + categories;
				if(sections) tag += '| sec:' + sections;
				for(i = 0; i < window.parent.document.getElementsByName("checkboxInsert").length; i++){
					if(window.parent.document.getElementsByName("checkboxInsert").item(i).checked){
						type += window.parent.document.getElementsByName("checkboxInsert").item(i).id + ',';
					}
				}
				if(type) otherinfo += "| type:" + type;

				var wrapNumber = window.parent.document.getElementById("wrapCharacterNumber").value;
				if(wrapNumber) otherinfo += "| wrap:" + wrapNumber;

				var dropdownLastFirst = window.parent.document.getElementById("dropdownLastFirst");
				otherinfo += '| order:' + dropdownLastFirst.options[dropdownLastFirst.selectedIndex].value;

				var dropdownNbArticles = window.parent.document.getElementById("dropdownNbArticles");
				otherinfo += '| max:' + dropdownNbArticles.options[dropdownNbArticles.selectedIndex].value;

				tag += otherinfo + '}';

				window.document.getElementById('tagstring').value = tag;
			}
			function insertTag(){
				window.parent.insertTag(window.document.getElementById('tagstring').value);
				acysms_js.closeBox(true);
			}
			//-->
		</script>
		<table class="acysms_table">
			<thead>
			<tr>
				<th class="title"></th>
				<th class="title">
					<?php echo JText::_('SMS_CATEGORIES'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<tr id="content_sec0_cat0" class="<?php echo "row1"; ?>" onclick="insertCategories(0,0);" style="cursor:pointer;">
				<td class="acysmstdcheckbox"></td>
				<td>
					<?php
					echo JText::_('SMS_ALL');
					?>
				</td>
			</tr>
			<?php
			$k = 0;
			$currentSection = '';
			foreach($contentListing as $row){
				if(empty($row->catid)){ ?>
					<tr id="content_sec<?php echo $row->secid ?>" class="<?php echo "row$k"; ?>" onclick="insertCategories(<?php echo $row->secid ?>,<?php echo $row->catid ?>);" style="cursor:pointer;">
					<?php
				}else{ ?>
					<tr id="content_cat<?php echo $row->catid ?>" class="<?php echo "row$k"; ?>" onclick="insertCategories(<?php echo $row->secid ?>,<?php echo $row->catid ?>);" style="cursor:pointer;">
					<?php
				}
				?>
				<td class="acysmstdcheckbox"></td>
				<td <?php if(empty($row->catid)) echo 'class="acysmsSection"' ?> >
					<?php
					echo $row->title;
					?>
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



	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::_('SMS_INSERTED_TAGS');
		$types['content'] = $newType;
	}

	function onACYSMSdisplayParamsAutoMessage_content(){
		$triggerData = array();
		$triggerData[] = JHTML::_('select.option', 'creation', JText::_('SMS_CREATION'));
		$triggerData[] = JHTML::_('select.option', 'modification', JText::_('SMS_MODIFICATION'));

		$dropdownTrigger = JHTML::_('select.genericlist', $triggerData, 'data[message][message_receiver][auto][content][trigger]', 'class="inputbox"', 'value', 'text', '');

		$days = array();
		for($i = 1; $i < 32; $i++) $days[] = JHTML::_('select.option', (strlen($i) == 1) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		$years = array();
		for($i = date('Y'); $i <= date('Y') + 5; $i++) $years[] = JHTML::_('select.option', $i, $i);
		$months = array();
		$months[] = JHTML::_('select.option', '01', JText::_('JANUARY'));
		$months[] = JHTML::_('select.option', '02', JText::_('FEBRUARY'));
		$months[] = JHTML::_('select.option', '03', JText::_('MARCH'));
		$months[] = JHTML::_('select.option', '04', JText::_('APRIL'));
		$months[] = JHTML::_('select.option', '05', JText::_('MAY'));
		$months[] = JHTML::_('select.option', '06', JText::_('JUNE'));
		$months[] = JHTML::_('select.option', '07', JText::_('JULY'));
		$months[] = JHTML::_('select.option', '08', JText::_('AUGUST'));
		$months[] = JHTML::_('select.option', '09', JText::_('SEPTEMBER'));
		$months[] = JHTML::_('select.option', '10', JText::_('OCTOBER'));
		$months[] = JHTML::_('select.option', '11', JText::_('NOVEMBER'));
		$months[] = JHTML::_('select.option', '12', JText::_('DECEMBER'));

		for($i = 0; $i < 24; $i++) $hours[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);
		for($i = 0; $i < 60; $i++) $min[] = JHTML::_('select.option', (($i) < 10) ? '0'.$i : $i, (strlen($i) == 1) ? '0'.$i : $i);

		$dayField = JHTML::_('select.genericlist', $days, 'data[message][message_receiver][auto][content][generatingdate][day]', 'class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'd'));
		$monthField = JHTML::_('select.genericlist', $months, 'data[message][message_receiver][auto][content][generatingdate][month]', 'style="width:100px;" class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'm'));
		$yearField = JHTML::_('select.genericlist', $years, 'data[message][message_receiver][auto][content][generatingdate][year]', 'style="width:70px;" class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'Y'));
		$hourField = JHTML::_('select.genericlist', $hours, 'data[message][message_receiver][auto][content][generatingdate][hour]', 'class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'H'));
		$minField = JHTML::_('select.genericlist', $min, 'data[message][message_receiver][auto][content][generatingdate][min]', 'class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'i'));
		$timeField = array($dayField, $monthField, $yearField, $hourField.' : ', $minField);

		echo JText::sprintf('SMS_SEND_CONTENT_MODIFICATION', $dropdownTrigger).'<br />'.JText::_('SMS_STARTING_AT').' : ';
		foreach($timeField as $oneField) echo $oneField.' ';
	}

	public function onContentAfterSave($context, $article, $isNew){
		$this->_articleModification($article, $isNew);
	}

	private function init(){
		if(defined('ACYSMS_COMPONENT')) return true;
		$acySmsHelper = rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper)){
			include_once $acySmsHelper;
		}else return false;
		return defined('ACYSMS_COMPONENT');
	}

	private function _articleModification($article, $isNew){
		JPluginHelper::importPlugin('acysms');
		$dispatcher = JDispatcher::getInstance();

		if(!$this->init()) return;

		$messageClass = ACYSMS::get('class.message');
		$allMessages = $messageClass->getAutoMessage('content');

		foreach($allMessages as $oneMessage){
			$generate = true;
			$trigger = $oneMessage->message_receiver['auto']['content']['trigger'];
			if(empty($trigger) || $isNew && $trigger == 'modification' || !$isNew && $trigger == 'creation') continue;
			$newMessage = clone($oneMessage);
			$pluginReturns = $dispatcher->trigger('onACYSMSReplaceTags', array(&$newMessage, false));
			unset($newMessage->message_id);

			foreach($pluginReturns as $onePluginReturn){
				if(!is_object($onePluginReturn)) continue;
				if(isset($onePluginReturn->generateNewOne) && !$onePluginReturn->generateNewOne){
					$generate = false;
					$cronHelper = ACYSMS::get('helper.cron');
					$cronHelper->messages = array('Message Generation blocked by a plugin');
					if(!empty($onePluginReturn->message)) $cronHelper->detailMessages = array($onePluginReturn->message);
					$cronHelper->saveReport();
					continue;
				}
				if($generate){
					$newMessage->message_subject = JText::_('SMS_GENERATED_SMS').' : '.$newMessage->message_subject;
					$newMessage->message_created = time();
					$newMessage->message_type = 'standard';
					$newMessage->message_senddate = time();
					$newMessage->message_status = 'scheduled';
					$newMessage->message_receiver = $newMessage->message_receiver;
					$newMessage->message_receiver_table = $newMessage->message_receiver_table;
					$messageClass->save($newMessage);


					$oneMessage->message_receiver['auto']['content']['generatingdate']['day'] = ACYSMS::getDate(time(), 'd');
					$oneMessage->message_receiver['auto']['content']['generatingdate']['month'] = ACYSMS::getDate(time(), 'm');
					$oneMessage->message_receiver['auto']['content']['generatingdate']['year'] = ACYSMS::getDate(time(), 'Y');
					$oneMessage->message_receiver['auto']['content']['generatingdate']['hour'] = ACYSMS::getDate(time(), 'H');
					$oneMessage->message_receiver['auto']['content']['generatingdate']['min'] = ACYSMS::getDate(time() + 60, 'i');
					$messageClass->save($oneMessage);
				}
			}
		}
	}
}
