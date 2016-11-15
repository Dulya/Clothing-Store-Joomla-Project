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

class plgSystemAcysmsMijoShopOrders extends JPlugin{
	var $version = false;

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) return false;
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_mijoshop')) return;
		parent::__construct($subject, $config);
	}

	private function init(){
		if(defined('ACYSMS_COMPONENT')) return true;
		$acySmsHelper = rtrim(JPATH_ROOT, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper)){
			include_once $acySmsHelper;
		}else return false;
		return defined('ACYSMS_COMPONENT');
	}






	function onACYSMSGetMessageType(&$types, $integration){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_AUTO_ORDER_STATUS', JText::_('SMS_MIJOSHOP'));
		$types['mijoshoporders'] = $newType;
	}


	function onACYSMSdisplayParamsAutoMessage_mijoshoporders(){
		$db = JFactory::getDBO();
		$timevalue = array();
		$timevalue[] = JHTML::_('select.option', 'hours', JText::_('SMS_HOURS'));
		$timevalue[] = JHTML::_('select.option', 'days', JText::_('SMS_DAYS'));
		$timevalue[] = JHTML::_('select.option', 'weeks', JText::_('SMS_WEEKS'));
		$timevalue[] = JHTML::_('select.option', 'months', JText::_('SMS_MONTHS'));

		$orderStatus[] = JHTML::_('select.option', '', JText::_(' - - - '));
		$query = 'SELECT `order_status_id` AS value, `name` AS text
				 FROM `#__mijoshop_order_status`
				 ORDER BY `order_status_id` ASC';
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		foreach($orders as $oneOrder){
			$orderStatus[] = JHTML::_('select.option', $oneOrder->value, $oneOrder->text);
		}

		$delay = JHTML::_('select.genericlist', $timevalue, "data[message][message_receiver][auto][mijoshoporders][delay][timevalue]", 'size="1" style="width:auto"', 'value', 'text', '0');
		$status1 = JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][auto][mijoshoporders][status][status1]", 'size="1"  style="width:auto;"', 'value', 'text', '0');
		$status2 = JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][auto][mijoshoporders][status][status2]", 'size="1"  style="width:auto;"', 'value', 'text', '0');


		$timeNumber = '<input type="text" name="data[message][message_receiver][auto][mijoshoporders][delay][duration]" class="inputbox" style="width:30px" value="0">';
		echo JText::sprintf('SMS_AFTER_ORDER_MODIF', $timeNumber.' '.$delay).'<br />';

		$to = '<input id="tobuyer" type="radio" name="data[message][message_receiver][auto][mijoshoporders][to]" value="buyer" checked="checked"><label for="tobuyer">'.JText::_('SMS_BUYER').'</label>';
		echo JTEXT::sprintf('SMS_SENDTO_ADDRESS', $to).'<br />';

		$db->setQuery('SELECT category_id , name
						FROM `#__mijoshop_category_description` AS mijoCategory
						ORDER BY `category_id`');
		$mijoCategories = $db->loadObjectList();

		if(!empty($mijoCategories)){
			$mijoCategoriesOptions = array();
			$mijoCategoriesOptions[] = JHTML::_('select.option', '', JText::_('SMS_ANY_CATEGORIES'));
			foreach($mijoCategories as $oneMijoCategory){
				$mijoCategoriesOptions[] = JHTML::_('select.option', $oneMijoCategory->category_id, $oneMijoCategory->name);
			}
			$mijoCategoryDropDown = JHTML::_('select.genericlist', $mijoCategoriesOptions, "data[message][message_receiver][auto][mijoshoporders][other][category]", 'size="1" style="width:auto"', 'value', 'text', '0');
		}

		echo str_replace(array('%s', '%t'), array($status1, $status2), JText::_('SMS_STATUS_CHANGES')).'<br />';
		echo JText::_('SMS_ORDER_CONTAINS_PRODUCT').' : <span id="displayedMijoProduct"/></span><a class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl=cpanel&task=plgtrigger&plgtype=system&plg=acysmsmijoshoporders&fctName=displayMijoArticles\');return false;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
		echo '<input type="hidden" name="data[message][message_receiver][auto][mijoshoporders][other][product]" id="selectedMijoProduct"></input><br />';
		echo '<input type="hidden" name="data[message][message_receiver][auto][mijoshoporders][productName]" id="hiddenMijoProduct"/><br />';
		if(!empty($mijoCategoryDropDown)) echo JText::_('SMS_ONLY_ORDER_CONTAINS_PRODUCT_FROM_CATEGORY').' : '.$mijoCategoryDropDown;
	}

	function onAcySMSdisplayMijoArticles(){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYSMS_COMPONENT.'content';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'product_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$query = ('SELECT mijoProduct.product_id, mijoProduct.name, mijoProduct.description, GROUP_CONCAT(DISTINCT mijoCategory.name) AS catname
					FROM #__mijoshop_product_description AS mijoProduct
					LEFT JOIN #__mijoshop_product_to_category AS mijoProductToCategory ON mijoProduct.product_id = mijoProductToCategory.product_id
					LEFT JOIN #__mijoshop_category_description AS mijoCategory ON mijoCategory.category_id = mijoCategory.category_id');

		$searchMap = array('mijoProduct.name', 'mijoProduct.description', 'mijoCategory.name');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';

		$query .= ' GROUP BY mijoProduct.product_id';

		if(!empty ($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;

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

					parent.window.document.getElementById("selectedMijoProduct").value = selected.substring(0, selected.length - 1);

					parent.window.document.getElementById("displayedMijoProduct").innerHTML = displayed.substring(1, displayed.length - 3);
					parent.window.document.getElementById("hiddenMijoProduct").value = displayed.substring(1, displayed.length - 3);


					acysms_js.closeBox(true);
				}

			</script>
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
						<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'mijoProduct.name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title titledesc">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_DESCRIPTION'), 'mijoProduct.description', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title titlecode">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_CATEGORY'), 'catname', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
					</th>
					<th class="title titleid">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'mijoProduct.product_id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
						<tr class="<?php echo "row$k"; ?>">
							<td align="center">
								<input type="checkbox" value="<?php echo $row->product_id ?>" id="cb<?php echo $i; ?>" onclick="addProduct();">
							</td>
							<td align="center" id="productNamecb<?php echo $i; ?>">
								<?php
								echo $row->name;
								?>
							</td>
							<td align="center">
								<?php
								if(!empty($row->description)) echo substr(strip_tags(html_entity_decode($row->description), '<br>'), 0, 200).'...';
								?>
							</td>
							<td align="center">
								<?php
								echo $row->catname;
								?>
							</td>
							<td align="center" id="productIdcb<?php echo $i; ?>">
								<?php
								echo $row->product_id;
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
			<?php
		}
	}

	public function onMijoshopBeforeOrderStatusUpdate($data, $order_id, $order_status_id, $notify){
		if(empty($order_id)) return;
		if(!$this->init()) return;
		$this->checkForAcySMSCredits($order_id, $order_status_id);
		$this->manageStatus($data, $order_id, $order_status_id, $notify);
	}





	function onACYSMSGetTags(&$tags){
		$tags['ecommerceTags']['mijoshoporders'] = new stdClass();
		$tags['ecommerceTags']['mijoshoporders']->name = JText::sprintf('SMS_X_ORDER_INFO', JText::_('SMS_MIJOSHOP'));

		$tableFields = array();
		$tableFields['order_status_name'] = 'char';
		$tableFields['comments'] = 'char';
		$tableFields += acysms_getColumns('#__mijoshop_order');

		$tags['ecommerceTags']['mijoshoporders']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['ecommerceTags']['mijoshoporders']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{mijoshoporders:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['ecommerceTags']['mijoshoporders']->content .= '</tbody></table>';
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$db = JFactory::getDBO();
		$helperPlugin = ACYSMS::get('helper.plugins');

		$match = '#(?:{|%7B)mijoshoporders:(.*)(?:}|%7D)#Ui';
		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;

		$orders = new stdClass();
		if(!empty($user->queue_paramqueue->order_id)){
			$query = 'SELECT *,status.name AS order_status_name,orderHistories.comment AS comments
					FROM #__mijoshop_order AS orders
					LEFT JOIN #__mijoshop_order_status status
					ON orders.order_status_id = status.order_status_id
					LEFT JOIN #__mijoshop_order_history AS orderHistories
					ON orderHistories.order_id = orders.order_id
					WHERE orders.order_id = '.intval($user->queue_paramqueue->order_id).'
					ORDER BY orderHistories.date_added DESC
					LIMIT 1';
			$db->setQuery($query);
			$orders = $db->loadObject();
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
			$helperPlugin->formatString($tags[$oneTag], $mytag);
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}

	public function manageStatus($data, $order_id, $order_status_id, $notify){
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$allMessages = $messageClass->getAutoMessage('mijoshoporders');
		if(empty($allMessages)) return;

		$query = 'SELECT order_status_id FROM #__mijoshop_order WHERE order_id = '.intval($order_id);
		$db->setQuery($query);
		$old_order_status = $db->loadResult();

		foreach($allMessages as $messageID => $oneMessage){
			if(empty($oneMessage->message_receiver['auto']['mijoshoporders']['status'])) continue;

			if(empty($old_order_status) && !empty($oneMessage->message_receiver['auto']['mijoshoporders']['status']['status1'])) continue;

			if(!empty($oneMessage->message_receiver['auto']['mijoshoporders']['status']['status1']) && $old_order_status != $oneMessage->message_receiver['auto']['mijoshoporders']['status']['status1']) continue;
			if(!empty($oneMessage->message_receiver['auto']['mijoshoporders']['status']['status2']) && $order_status_id != $oneMessage->message_receiver['auto']['mijoshoporders']['status']['status2']) continue;

			if(!empty($oneMessage->message_receiver['auto']['mijoshoporders']['other']['product'])){
				$query = 'SELECT product_id FROM #__mijoshop_order_product WHERE order_id = '.intval($order_id);
				$db->setQuery($query);
				$productIds = acysms_loadResultArray($db);
				if(!in_array($oneMessage->message_receiver['auto']['mijoshoporders']['other']['product'], $productIds)) continue;
			}
			if(!empty($oneMessage->message_receiver['auto']['mijoshoporders']['other']['category'])){
				$query = 'SELECT MijoProductCategory.category_id
					FROM #__mijoshop_order_product AS mijoOrderProduct
					JOIN #__mijoshop_product_to_category AS MijoProductCategory
					ON mijoOrderProduct.product_id = MijoProductCategory.product_id
					WHERE mijoOrderProduct.order_id = '.intval($order_id);
				$db->setQuery($query);
				$categoryIds = acysms_loadResultArray($db);
				if(!in_array($oneMessage->message_receiver['auto']['mijoshoporders']['other']['category'], $categoryIds)) continue;
			}
			$senddate = strtotime('+'.intval($oneMessage->message_receiver['auto']['mijoshoporders']['delay']['duration']).' '.$oneMessage->message_receiver['auto']['mijoshoporders']['delay']['timevalue'], time());

			$queryUser = 'SELECT mijoCustomer.customer_id
			FROM #__mijoshop_customer AS mijoCustomer
			INNER JOIN #__mijoshop_order AS mijoOrder ON mijoOrder.customer_id = mijoCustomer.customer_id
			WHERE mijoOrder.order_id  = '.intval($order_id);
			$db->setQuery($queryUser);
			$receiver_id = $db->loadResult();
			$params = new stdClass();
			$params->order_id = $order_id;
			$paramqueue = serialize($params);
			$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue`
							(`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`,`queue_paramqueue`)
							VALUES ("'.intval($oneMessage->message_id).'","'.intval($receiver_id).'", "mijoshop", '.intval($senddate).', "0", "3", '.$db->Quote($paramqueue).' )';
			$db->setQuery($finalQuery);
			$db->query();

			if(empty($oneMessage->message_receiver['auto']['mijoshoporders']['delay']['duration'])){
				$queueHelper = ACYSMS::get('helper.queue');
				$queueHelper->report = false;
				$queueHelper->message_id = $oneMessage->message_id;
				$queueHelper->process();
			}
		}
	}

	public function checkForAcySMSCredits($order_id, $order_status_id){
		if(!isset($order_confirmed)) static $order_confirmed = -1;
		if($order_confirmed == $order_id) return;

		$db = JFactory::getDBO();
		$AcySMSProductID = $this->params->get('productId');
		$customerClass = ACYSMS::get('class.customer');

		$db->setQuery('SELECT order_status_id AS order_status FROM #__mijoshop_order_status WHERE name = "Complete"');
		$order_id_complete = $db->loadResult();
		if(empty($order_id_complete) || $order_status_id != $order_id_complete) return;

		$db->setQuery('SELECT customer_id FROM #__mijoshop_order WHERE order_id = '.intval($order_id));
		$user_id = $db->loadResult();
		if(empty($user_id)) return;

		$query = 'SELECT product.product_id AS product_id, orderproduct.quantity AS quantity, product.sku AS sku
		FROM `#__mijoshop_order_product` AS orderproduct
		INNER JOIN `#__mijoshop_product` AS product
		ON orderproduct.product_id = product.product_id
		WHERE orderproduct.order_id = '.intval($order_id);
		$db->setQuery($query);
		$allProducts = $db->loadObjectList();

		if(empty($allProducts)) return;;
		$mijoshopIntegration = ACYSMS::getIntegration('mijoshop');
		$joomlaUserId = reset($mijoshopIntegration->getJoomUserId($user_id));
		if(empty($joomlaUserId)) return;


		$creditsAdded = false;


		foreach($allProducts as $oneProduct){
			if(preg_match('#SMSCREDITS_([0-9]*)#', $oneProduct->sku, $result)){
				$nbCredits = $result[1];
				$customerClass->changeCredits($joomlaUserId, $oneProduct->quantity * $nbCredits, 'add');
				$creditsAdded = true;
			}else if($oneProduct->product_id == $AcySMSProductID){
				$customerClass->changeCredits($joomlaUserId, $oneProduct->quantity, 'add');
				$creditsAdded = true;
			}
		}

		if($creditsAdded){
			$query = 'UPDATE #__acysms_message SET `message_status` = "sent" WHERE message_status = "waitingcredits" AND `message_userid` = '.$joomlaUserId;
			$db->setQuery($query);
			$db->query();
		}
	}
}
