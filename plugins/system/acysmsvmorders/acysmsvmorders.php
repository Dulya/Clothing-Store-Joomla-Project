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

class plgSystemAcysmsvmorders extends JPlugin{
	var $version = false;






	function onACYSMSGetMessageType(&$types, $integration){

		if(!$this->init()) return;
		if(empty($integration) || $integration != "virtuemart_2") return;
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_AUTO_ORDER_STATUS', JText::_('SMS_VIRTUEMART'));
		$types['virtuemartorders'] = $newType;
	}


	function onACYSMSdisplayParamsAutoMessage_virtuemartorders($message){

		if(!$this->init()) return;
		$db = JFactory::getDBO();
		$timevalue = array();
		$timevalue[] = JHTML::_('select.option', 'hours', JText::_('SMS_HOURS'));
		$timevalue[] = JHTML::_('select.option', 'days', JText::_('SMS_DAYS'));
		$timevalue[] = JHTML::_('select.option', 'weeks', JText::_('SMS_WEEKS'));
		$timevalue[] = JHTML::_('select.option', 'months', JText::_('SMS_MONTHS'));

		$orderStatus[] = JHTML::_('select.option', '', JText::_(' - - - '));
		$query = 'SELECT `order_status_code` AS value, `order_status_name` AS text
								 FROM `#__virtuemart_orderstates`
								 WHERE `virtuemart_vendor_id` = 1
								 ORDER BY `ordering` ASC';
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		foreach($orders as $oneOrder){
			$orderStatus[] = JHTML::_('select.option', $oneOrder->value, JText::_($oneOrder->text));
		}

		$delay = JHTML::_('select.genericlist', $timevalue, "data[message][message_receiver][auto][virtuemartorders][delay][timevalue]", 'size="1" style="width:auto"', 'value', 'text', '0');
		$status1 = JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][auto][virtuemartorders][status][status1]", 'size="1"  style="width:auto;"', 'value', 'text', '0');
		$status2 = JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][auto][virtuemartorders][status][status2]", 'size="1"  style="width:auto;"', 'value', 'text', '0');


		$timeNumber = '<input type="text" name="data[message][message_receiver][auto][virtuemartorders][delay][duration]" class="inputbox" style="width:30px" value="0">';
		echo JText::sprintf('SMS_AFTER_ORDER_MODIF', $timeNumber.' '.$delay).'<br />';

		$buyerChecked = $vendorChecked = $otherChecked = '';
		if(empty($message->message_receiver['auto']['virtuemartorders']['to']) || $message->message_receiver['auto']['virtuemartorders']['to'] == 'buyer') $buyerChecked = 'checked="checked"';
		if(empty($message->message_receiver['auto']['virtuemartorders']['to']) || $message->message_receiver['auto']['virtuemartorders']['to'] == 'vendor') $vendorChecked = 'checked="checked"';
		if(empty($message->message_receiver['auto']['virtuemartorders']['to']) || $message->message_receiver['auto']['virtuemartorders']['to'] == 'other') $otherChecked = 'checked="checked"';

		$to = '<input id="tobuyer" type="radio" name="data[message][message_receiver][auto][virtuemartorders][to]" value="buyer" '.$buyerChecked.' ><label for="tobuyer">'.JText::_('SMS_BUYER').'</label>
		<input id="tovendor" type="radio" name="data[message][message_receiver][auto][virtuemartorders][to]" value="vendor" '.$vendorChecked.'><label for="tovendor">'.JText::_('SMS_VENDOR').'</label>
		<input id="toselect" type="radio" name="data[message][message_receiver][auto][virtuemartorders][to]" value="other" '.$otherChecked.'><label for="toselect">'.JText::sprintf('SMS_SELECT_USER', '</label><span id="selectedVmUser_phone"></span>').'
		<a class="modal" id="selectUser" onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl=receiver&task=choose&htmlID=selectedVmUser&currentIntegration=virtuemart_2\'); return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
		echo JTEXT::sprintf('SMS_SENDTO_ADDRESS', $to).'<br />';
		echo '<input type="hidden" name="data[message][message_receiver][auto][virtuemartorders][other][phoneNumber]" id="selectedVmUser_id"></input>';

		if(version_compare($this->version, '2.0.0', '<')){
			$db->setQuery('SELECT vmCategory.*,vmCategoryXref.*
							FROM `#__vm_category` AS vmCategory
							LEFT JOIN `#__vm_category_xref` as vmCategoryXref
							ON vmCategory.category_id = vmCategoryXref.category_child_id
							ORDER BY `list_order`');
		}else{
			$db->setQuery('SELECT vmCategory.*, vmCategoryCategories.*, vmCategories.*, vmCategory.virtuemart_category_id AS category_id
							FROM `#__virtuemart_categories` AS vmCategory
							LEFT JOIN `#__virtuemart_category_categories` as vmCategoryCategories
							ON vmCategory.virtuemart_category_id = vmCategoryCategories.category_child_id
							LEFT JOIN `#__virtuemart_categories_'.$this->lang.'` AS vmCategories
							ON vmCategory.virtuemart_category_id = vmCategories.virtuemart_category_id
							ORDER BY vmCategory.`ordering`');
		}
		$VmCategories = $db->loadObjectList();

		if(!empty($VmCategories)){
			$VmCategoriesOptions = array();
			$VmCategoriesOptions[] = JHTML::_('select.option', '', JText::_('SMS_ANY_CATEGORIES'));
			foreach($VmCategories as $oneVmCategory){
				$VmCategoriesOptions[] = JHTML::_('select.option', $oneVmCategory->virtuemart_category_id, $oneVmCategory->category_name);
			}
			$VmCategoryDropDown = JHTML::_('select.genericlist', $VmCategoriesOptions, "data[message][message_receiver][auto][virtuemartorders][other][category]", 'size="1" style="width:auto"', 'value', 'text', '0');
		}

		echo str_replace(array('%s', '%t'), array($status1, $status2), JText::_('SMS_STATUS_CHANGES')).'<br />';
		echo JText::_('SMS_ORDER_CONTAINS_PRODUCT').' : <span id="displayedVmProduct"/></span><a class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl=cpanel&task=plgtrigger&plgtype=system&plg=acysmsvmorders&fctName=displayVmArticles\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
		echo '<input type="hidden" name="data[message][message_receiver][auto][virtuemartorders][other][product]" id="selectedVmProduct"></input><br />';
		if(!empty($VmCategoryDropDown)) echo JText::_('SMS_ONLY_ORDER_CONTAINS_PRODUCT_FROM_CATEGORY').' : '.$VmCategoryDropDown;
	}

	function onAcySMSdisplayVMArticles(){

		if(!$this->init()) return;
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'component.css');


		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYSMS_COMPONENT.'virtuemart';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'vmProductsTrad.virtuemart_product_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		if(version_compare($this->version, '2.0.0', '<')){
			$query = ('SELECT vmProduct.*
							FROM `#__vm_product` AS vmProduct');
		}else{
			$query = ('SELECT vmProductsTrad.virtuemart_product_id, vmProductsTrad.product_name, vmProductsTrad.product_desc, GROUP_CONCAT(distinct vmCategories.category_name) AS category_name
						FROM #__virtuemart_products_'.$this->lang.' AS vmProductsTrad
						LEFT JOIN #__virtuemart_product_categories AS vmProductCategories ON vmProductsTrad.virtuemart_product_id = vmProductCategories.virtuemart_product_id
						LEFT JOIN #__virtuemart_categories_'.$this->lang.' AS vmCategories ON vmProductCategories.virtuemart_category_id = vmCategories.virtuemart_category_id
						');
		}

		$searchMap = array('vmProductsTrad.product_name', 'vmProductsTrad.product_desc', 'vmCategories.category_name');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		$query .= ' GROUP BY vmProductsTrad.virtuemart_product_id';
		if(!empty ($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$pageInfo->elements->total = count($rows);

		jimport('joomla.html.pagination');
		$pagination = new JPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		?>

		<script language="javascript" type="text/javascript">
			var selectedContents = new Array();
			var selectedContentsName = new Array();
			function addProduct(){
				var selectedProduct = "";
				var selectedProductId = "";
				var form = document.adminForm;
				for(i = 0; i <= form.length - 1; i++){
					if(form[i].type == 'checkbox'){

						if(!document.getElementById("productId" + form[i].id)) continue;
						if(document.getElementById("productId" + form[i].id).innerHTML.lentgth == 0) continue;
						oneProductId = document.getElementById("productId" + form[i].id).innerHTML.trim();

						productId = "productId" + form[i].id
						if(!document.getElementById("productName" + form[i].id)) continue;
						if(document.getElementById("productName" + form[i].id).innerHTML.lentgth == 0) continue;
						oneProduct = document.getElementById("productName" + form[i].id).innerHTML;

						var tmp = selectedContents.indexOf(oneProductId);
						if(tmp != -1 && form[i].checked == false){
							delete selectedContents[tmp];
							delete selectedContentsName[tmp];
						}else if(tmp == -1 && form[i].checked == true){
							selectedContents.push(oneProductId);
							selectedContentsName.push(oneProduct);
						}
					}
				}

				for(var i in selectedContents){
					if(selectedContents[i] && !isNaN(i))    selectedProductId += selectedContents[i].trim() + ",";
					if(selectedContentsName[i] && !isNaN(i))    selectedProduct += " " + selectedContentsName[i].trim() + " , ";
				}

				window.document.getElementById("productSelected").value = selectedProductId;
				window.document.getElementById("productDisplayed").value = selectedProduct;
			}

			function confirmProductSelection(){
				selected = window.document.getElementById("productSelected").value;
				displayed = window.document.getElementById("productDisplayed").value;

				parent.window.document.getElementById("selectedVmProduct").value = selected.substring(0, selected.length - 1);

				parent.window.document.getElementById("displayedVmProduct").innerHTML = displayed.substring(1, displayed.length - 3);


				acysms_js.closeBox(true);
			}

		</script>


		<div id="acysms_content">
			<div id="acysms_edit" class="acytagpopup">
				<form action="#" method="post" name="adminForm" id="adminForm" autocomplete="off">
					<table class="acysms_table_options">
						<tr>
							<td>
								<input type="hidden" id="productSelected"/>
								<input type="textbox" size="30" id="productDisplayed" readonly value=""/>
								<input type="button" onclick="confirmProductSelection()" value="<?php echo JText::_('SMS_VALIDATE') ?>"/>
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
						<th class="title titlebox">
							<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this); addProduct();"/>
						</th>
						<th class="title titlename">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'vmProductsTrad.product_name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titledesc">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_DESCRIPTION'), 'vmProductsTrad.product_desc', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titlecat">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_CATEGORY'), 'vmCategories.category_name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titleid">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'vmProductsTrad.virtuemart_product_id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
						$k = 1;
						for($i = 0, $a = count($rows); $i < $a; $i++){
							$row = $rows[$i];
							?>
							<tr class="<?php echo "row$k"; ?>">
								<td align="center">
									<input type="checkbox" value="<?php echo $row->virtuemart_product_id ?>" id="cb<?php echo $i; ?>" onclick="addProduct();">
								</td>
								<td align="center" id="productNamecb<?php echo $i; ?>">
									<?php
									echo $row->product_name
									?>
								</td>
								<td align="center">
									<?php
									if(!empty($row->product_desc)) echo substr(strip_tags(html_entity_decode($row->product_desc), '<br>'), 0, 200).'...';
									?>
								</td>
								<td align="center">
									<?php
									echo $row->category_name;
									?>
								</td>
								<td align="center" id="productIdcb<?php echo $i; ?>">
									<?php
									echo $row->virtuemart_product_id;
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
				</form>
			</div>
		</div>
		<?php
	}


	function onACYSMSGetTags(&$tags){

		if(!$this->init()) return;
		if(empty($this->version) || version_compare($this->version, '2.0.0', '<')) return;

		$tags['ecommerceTags']['virtuemartorders'] = new stdClass();
		$tags['ecommerceTags']['virtuemartorders']->name = JText::sprintf('SMS_X_ORDER_INFO', JText::_('SMS_VIRTUEMART'));
		$db = JFactory::getDBO();

		$tableFields = array();
		$tableFields['order_status_name'] = 'char';
		$tableFields['comments'] = 'char';
		$tableFields += acysms_getColumns('#__virtuemart_orders');

		$tags['ecommerceTags']['virtuemartorders']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['ecommerceTags']['virtuemartorders']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{virtuemartorders:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['ecommerceTags']['virtuemartorders']->content .= '</tbody></table>';
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		if(!$this->init()) return;
		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$helperPlugin = ACYSMS::get('helper.plugins');

		$match = '#(?:{|%7B)virtuemartorders:(.*)(?:}|%7D)#Ui';
		$variables = array('message_body');
		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;

		$integration = ACYSMS::getIntegration($message->message_receiver_table);

		$orders = new stdClass();
		if(!empty($user->queue_paramqueue->virtuemart_order_id)){
			$query = 'SELECT *
					FROM #__virtuemart_orders AS orders
					LEFT JOIN #__virtuemart_orderstates AS orderstates
					ON orderstates.order_status_code = orders.order_status
					LEFT JOIN #__virtuemart_order_histories AS orderHistories
					ON orderHistories.virtuemart_order_id = orders.virtuemart_order_id
					WHERE orders.virtuemart_order_id = '.intval($user->queue_paramqueue->virtuemart_order_id).'
					ORDER BY orderHistories.modified_on DESC
					LIMIT 1';
			$db->setQuery($query);
			$orders = $db->loadObject();
		}

		if(!empty($user->queue_paramqueue->virtuemart_newStatus)){
			$query = 'SELECT `order_status_name` AS text
						FROM `#__virtuemart_orderstates`
						WHERE `virtuemart_vendor_id` = 1
						AND  `order_status_code` = '.$db->Quote($user->queue_paramqueue->virtuemart_newStatus).'
						ORDER BY `ordering` ASC';
			$db->setQuery($query);
			$newStatus = $db->loadResult();
			$orders->order_status_name = JText::_($newStatus);
		}

		$tags = array();
		foreach($results[0] as $i => $oneTag){
			if(isset($tags[$oneTag])) continue;
			$arguments = explode('|', strip_tags($results[1][$i]));
			$field = $arguments[0];
			unset($arguments[0]);
			$mytag = new stdClass();
			$mytag->default = '';
			if(!empty($arguments)){
				foreach($arguments as $onearg){
					$args = explode(':', $onearg);
					if(isset($args[1])){
						$mytag->$args[0] = $args[1];
					}else{
						$mytag->$args[0] = 1;
					}
				}
			}
			$tags[$oneTag] = (isset($orders->$field) && strlen($orders->$field) > 0) ? $orders->$field : $mytag->default;

			if(file_exists(ACYSMS_MEDIA.'plugins'.DS.'acysmsvmorders.php')){
				ob_start();
				require(ACYSMS_MEDIA.'plugins'.DS.'acysmsvmorders.php');
				$result = ob_get_clean();
			}

			$helperPlugin->formatString($tags[$oneTag], $mytag);
		}

		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}

	function init(){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_virtuemart')) return false;

		$file = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_virtuemart'.DS.'version.php';
		if(!file_exists($file)) return false;
		include_once($file);

		$vmversion = new vmVersion();
		if(empty($vmversion->RELEASE)){
			$this->version = vmVersion::$RELEASE;
			$params = JComponentHelper::getParams('com_languages');
			$this->lang = strtolower(str_replace('-', '_', $params->get('site', 'en-GB')));
		}else{
			$this->version = $vmversion->RELEASE;
		}

		if(defined('ACYSMS_COMPONENT')) return true;
		$acySmsHelper = rtrim(JPATH_ROOT, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper)){
			include_once $acySmsHelper;
		}else return false;
		return defined('ACYSMS_COMPONENT');
	}

	function plgVmOnUpdateOrderShipment($data, $old_order_status){
		if(!$this->init()) return;
		$this->checkForAcySMSCredits($data);
		$this->manageMessage($data, $old_order_status);
	}

	public function manageMessage($data, $old_order_status){

		$db = JFactory::getDBO();

		$messageClass = ACYSMS::get('class.message');
		$allMessages = $messageClass->getAutoMessage('virtuemartorders');
		if(empty($allMessages)) return;
		foreach($allMessages as $messageID => $oneMessage){

			if(empty($data->order_status)) continue;
			if(empty($oneMessage->message_receiver['auto']['virtuemartorders']['status'])) continue;
			if(empty($old_order_status) && !empty($oneMessage->message_receiver['auto']['virtuemartorders']['status']['status1'])) continue;
			if(!empty($oneMessage->message_receiver['auto']['virtuemartorders']['status']['status1']) && $old_order_status != $oneMessage->message_receiver['auto']['virtuemartorders']['status']['status1']) continue;
			if(!empty($oneMessage->message_receiver['auto']['virtuemartorders']['status']['status2']) && $data->order_status != $oneMessage->message_receiver['auto']['virtuemartorders']['status']['status2']) continue;


			if(!empty($oneMessage->message_receiver['auto']['virtuemartorders']['other']['product'])){
				$query = 'SELECT virtuemart_product_id FROM #__virtuemart_order_items WHERE virtuemart_order_id = '.intval($data->virtuemart_order_id);
				$db->setQuery($query);
				$productIds = acysms_loadResultArray($db);
				if(!in_array($oneMessage->message_receiver['auto']['virtuemartorders']['other']['product'], $productIds)) continue;
			}
			if(!empty($oneMessage->message_receiver['auto']['virtuemartorders']['other']['category'])){
				$query = 'SELECT VmProductCategory.virtuemart_category_id
					FROM #__virtuemart_order_items AS VmOrderItem
					JOIN #__virtuemart_product_categories AS VmProductCategory
					ON VmOrderItem.virtuemart_product_id = VmProductCategory.virtuemart_product_id
					WHERE virtuemart_order_id = '.intval($data->virtuemart_order_id);
				$db->setQuery($query);
				$categoryIds = acysms_loadResultArray($db);
				if(!in_array($oneMessage->message_receiver['auto']['virtuemartorders']['other']['category'], $categoryIds)) continue;
			}

			$senddate = strtotime('+'.intval($oneMessage->message_receiver['auto']['virtuemartorders']['delay']['duration']).' '.$oneMessage->message_receiver['auto']['virtuemartorders']['delay']['timevalue'], time());



			if(!empty($oneMessage->message_receiver['auto']['virtuemartorders']['other']['phoneNumber']) && $oneMessage->message_receiver['auto']['virtuemartorders']['to'] == 'other'){
				$receiver_id = $oneMessage->message_receiver['auto']['virtuemartorders']['other']['phoneNumber'];
			}else if($oneMessage->message_receiver['auto']['virtuemartorders']['to'] == 'vendor'){
				if(empty($data->virtuemart_vendor_id)) continue;

				$queryUser = 'SELECT virtuemart_userinfo_id FROM #__virtuemart_userinfos as vmuserinfos
				JOIN #__virtuemart_vmusers as vmusers ON vmuserinfos.virtuemart_user_id = vmusers.virtuemart_user_id
				WHERE vmusers.user_is_vendor AND vmusers.virtuemart_vendor_id =  '.intval($data->virtuemart_vendor_id);
				$db->setQuery($queryUser);
				$receiver_id = $db->loadResult();
			}else{
				if(empty($data->virtuemart_user_id)) continue;
				$queryUser = 'SELECT virtuemart_userinfo_id FROM #__virtuemart_userinfos WHERE virtuemart_user_id  = '.intval($data->virtuemart_user_id);
				$db->setQuery($queryUser);
				$receiver_id = $db->loadResult();
			}
			if(empty($receiver_id)) continue;


			$sendNow = false;
			if(empty($oneMessage->message_receiver['auto']['virtuemartorders']['delay']['duration'])) $sendNow = true;

			$params = new stdClass();
			$params->virtuemart_order_id = $data->virtuemart_order_id;
			if($sendNow) $params->virtuemart_newStatus = $data->order_status;
			$paramqueue = serialize($params);

			$acyquery = ACYSMS::get('class.acyquery');
			$integrationTo = $integrationFrom = 'virtuemart_2';
			$integration = ACYSMS::getIntegration($integrationTo);
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);
			$acyquery->addUserFilters(array($receiver_id), $integrationFrom, $integrationTo);

			$querySelect = $acyquery->getQuery(array($oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($oneMessage->message_receiver_table).','.$senddate.',0,2,'.$db->Quote($paramqueue)));


			$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`,`queue_paramqueue`) '.$querySelect;
			$db->setQuery($finalQuery);
			$db->query();

			if($sendNow){
				$queueHelper = ACYSMS::get('helper.queue');
				$queueHelper->report = false;
				$queueHelper->message_id = $oneMessage->message_id;
				$queueHelper->process();
			}
		}
	}

	public function checkForAcySMSCredits($order){
		if(empty($order->order_status) || $order->order_status != 'C') return;

		$db = JFactory::getDBO();
		$AcySMSProductID = $this->params->get('productId');
		$customerClass = ACYSMS::get('class.customer');

		$query = 'SELECT orderitem.virtuemart_product_id AS product_id, orderitem.product_quantity AS quantity, orderitem.order_item_sku AS sku
				FROM `#__virtuemart_order_items` AS orderitem
				WHERE orderitem.virtuemart_order_id = '.intval($order->virtuemart_order_id);

		$db->setQuery($query);
		$allProducts = $db->loadObjectList();
		if(empty($allProducts)) return;
		if(empty($order->virtuemart_user_id)) return;

		$creditsAdded = false;

		foreach($allProducts as $oneProduct){
			if(preg_match('#SMSCREDITS_([0-9]*)#', $oneProduct->sku, $result)){
				$nbCredits = $result[1];
				$customerClass->changeCredits($order->virtuemart_user_id, $oneProduct->quantity * $nbCredits, 'add');
				$creditsAdded = true;
			}else if($oneProduct->product_id == $AcySMSProductID){
				$customerClass->changeCredits($order->virtuemart_user_id, $oneProduct->quantity, 'add');
				$creditsAdded = true;
			}
		}

		if($creditsAdded){
			$query = 'UPDATE #__acysms_message SET `message_status` = "sent" WHERE message_status = "waitingcredits" AND `message_userid` = '.$order->virtuemart_user_id;
			$db->setQuery($query);
			$db->query();
		}
	}
}
