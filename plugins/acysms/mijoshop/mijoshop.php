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

class plgAcysmsMijoshop extends JPlugin{
	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_mijoshop')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'mijoshop');
			$this->params = new acysmsParameter($plugin->params);
		}
		$lang = JFactory::getLanguage();
		$lang->load('com_mijoshop', JPATH_SITE);
	}




	function onACYSMSGetTags(&$tags){

		$tags['ecommerceTags']['mijoShopUser'] = new stdClass();
		$tags['ecommerceTags']['mijoShopUser']->name = JText::sprintf('SMS_X_USER_INFO', 'MijoShop');

		$tableFieldsUser = acysms_getColumns('#__mijoshop_user');

		$tags['ecommerceTags']['mijoShopUser']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFieldsUser as $oneField => $fieldType){
			$tags['ecommerceTags']['mijoShopUser']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{mijoShopUser:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['ecommerceTags']['mijoShopUser']->content .= '</tbody></table>';



		$tags['ecommerceTags']['mijoCoupon'] = new stdClass();
		$tags['ecommerceTags']['mijoCoupon']->name = JText::sprintf('SMS_X_COUPON', 'Mijoshop');
		$prefix = 'mijo';


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
		$dayField = JHTML::_('select.genericlist', $days, '', 'style="width:60px;" class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'd'), $prefix.'datascheduleddateday');
		$monthField = JHTML::_('select.genericlist', $months, '', 'style="width:100px;" class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'm'), $prefix.'datascheduleddatemonth');
		$yearField = JHTML::_('select.genericlist', $years, '', 'style="width:70px;" class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'Y'), $prefix.'datascheduleddateyear');
		$timeField = array($dayField, $monthField, $yearField);

		$value = array();
		$value[0] = JHTML::_('select.option', 'percent', '%');
		$value[1] = JHTML::_('select.option', 'price', JText::_('COM_MIJOSHOP_MARKETING_COUPON_TEXT_AMOUNT'));
		$listCouponValue = JHTML::_('select.genericlist', $value, $prefix."dropDownPercentPrice", "", 'value', 'text', 'percent', $prefix.'valueType_');
		$expiry = array();
		$expiry[0] = JHTML::_('select.option', 'date', JText::_('SMS_FIELD_DATE'));
		$expiry[1] = JHTML::_('select.option', 'delay', JText::_('SMS_DELAY'));
		$radioListExpiry = JHTML::_('acysmsselect.radiolist', $expiry, $prefix."radioDateDelay", 'onclick="displayTypeOfDelay(\''.$prefix.'\')"', 'value', 'text', 'date', $prefix.'expiryType_');

		$delay = array();
		$delay[] = JHTML::_('select.option', 'days', JText::_('SMS_DAYS'));
		$delay[] = JHTML::_('select.option', 'months', JText::_('SMS_MONTHS'));
		$delay[] = JHTML::_('select.option', 'years', JText::_('SMS_YEARS'));

		$delayNumber = array();
		for($i = 0; $i < 100; $i++){
			$delayNumber[] = JHTML::_('select.option', $i + 1, $i + 1);
		}
		$displayDelay = array();
		$displayDelay[] = JHTML::_('select.genericlist', $delayNumber, $prefix.'numberdelay', "", 'value', 'text', '1', $prefix.'numberdelay');
		$displayDelay[] = JHTML::_('select.genericlist', $delay, $prefix.'delayLength', "", 'value', 'text', 'days', $prefix.'delayLength');

		$tags['ecommerceTags']['mijoCoupon']->content = '<table class="acysms_blocktable"><tbody>';
		$tags['ecommerceTags']['mijoCoupon']->content .= '<tr><td class="key">'.JText::_('COM_MIJOSHOP_PAYMENT_AMAZON_CHECKOUT_TEXT_COUPON').'</th> <td colspan="2"><input  maxlength="10" id="'.$prefix.'coupon" type="textbox" value="[key]"/> </td>';
		$tags['ecommerceTags']['mijoCoupon']->content .= '<tr><td class="key">'.JText::_('COM_MIJOSHOP_OPENBAY_ETSY_ORDER_TEXT_TOTAL_DISCOUNT').'</th> <td><input id="'.$prefix.'couponvalue" size="5" type="textbox"/> ';
		$tags['ecommerceTags']['mijoCoupon']->content .= $listCouponValue.'</td></tr>';
		$tags['ecommerceTags']['mijoCoupon']->content .= '<tr><td class="key">'.JText::_('SMS_EXPIRY_DATE').'</th> <td colspan="2">'.$radioListExpiry.'</td> </tr>';
		$tags['ecommerceTags']['mijoCoupon']->content .= '<tr id="'.$prefix.'expiryDate"> <td>'.JText::_('SMS_FIELD_DATE').'</th> <td colspan="2">'.$timeField[0].$timeField[1].$timeField[2].'</td></tr>';
		$tags['ecommerceTags']['mijoCoupon']->content .= '<tr id="'.$prefix.'expiryDelay" style="display:none"> <td>'.JText::_('SMS_DELAY').'</th> <td colspan="2">'.$displayDelay[0].$displayDelay[1].'</td></tr>';
		$tags['ecommerceTags']['mijoCoupon']->content .= '<tr><td colspan="3"> <input type="button" class="acysms_button" value="'.JText::_('SMS_INSERT_COUPON').'" onclick="createTagmijoCoupon(\''.$prefix.'\')"/> </td></tr>';
		$tags['ecommerceTags']['mijoCoupon']->content .= '</tbody></table>';
		?>
		<script language="javascript" type="text/javascript">

			function displayTypeOfDelay(prefix){

				if(document.getElementById(prefix + 'expiryType_delay').checked){
					document.getElementById(prefix + 'expiryDelay').style.display = 'table-row';
					document.getElementById(prefix + 'expiryDate').style.display = 'none';
				}else if(document.getElementById(prefix + 'expiryType_date').checked){
					document.getElementById(prefix + 'expiryDate').style.display = 'table-row';
					document.getElementById(prefix + 'expiryDelay').style.display = 'none';
				}
			}

			function createTagmijoCoupon(prefix){   //end date of the coupon
				if(document.getElementById(prefix + 'expiryType_delay').checked){
					difference = document.getElementById(prefix + 'numberdelay').value;
					difference = parseInt(difference);
					typeOfDifference = document.getElementById(prefix + 'delayLength').value;
				}else if(document.getElementById(prefix + 'expiryType_date').checked){
					endDate = new Date;
					var day = document.getElementById(prefix + 'datascheduleddateday').value;
					var month = document.getElementById(prefix + 'datascheduleddatemonth').value;
					var year = document.getElementById(prefix + 'datascheduleddateyear').value;
					endDate.setDate(day);
					endDate.setMonth(month - 1);
					endDate.setFullYear(year);
				}
				var typeValue = document.getElementById(prefix + 'valueType_').value;

				var couponName = document.getElementById(prefix + 'coupon').value;
				var couponValue = document.getElementById(prefix + 'couponvalue').value;

				if(document.getElementById(prefix + 'expiryType_delay').checked){
					var finalCoupon = "{mijocoupon:" + couponName + "|value:" + couponValue + "|typevalue:" + typeValue + "|delay:" + difference + "|typeofdelay:" + typeOfDifference + "}";
				}else{
					var day = endDate.getDate();
					var month = endDate.getMonth() + 1;
					var year = endDate.getFullYear();
					if(day < 10){
						day = '0' + day.toString();
					}else{
						day = day.toString();
					}
					if(month < 10){
						month = '0' + month.toString();
					}else{
						month = month.toString();
					}
					var endDate = year.toString() + "-" + month + "-" + day;
					var finalCoupon = "{mijocoupon:" + couponName + "|value:" + couponValue + "|typevalue:" + typeValue + "|expiry:" + endDate + "}";
				}
				insertTag(finalCoupon);
			}

		</script>
		<?php
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$this->_replaceCouponTags($message, $send);
		$this->_replaceUserTags($message, $user, $send);
	}

	private function _replaceCouponTags($message, $send){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'mijocoupon');
		foreach($tags as $oneTag){
			$key = ACYSMS::generateKey(5);
			$couponName = $oneTag->id;
			$couponValue = $oneTag->value;
			if($oneTag->typevalue == "percent"){
				$couponTypeValue = 'P';
			}else{
				$couponTypeValue = 'F';
			}
			if(isset($oneTag->expiry)){
				$couponExpiry = $oneTag->expiry;
			}else{
				switch($oneTag->typeofdelay){
					case 'days':
						$couponExpiry = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") + $oneTag->delay, date("Y")));
						break;
					case 'months':
						$couponExpiry = date("Y-m-d", mktime(0, 0, 0, date("m") + $oneTag->delay, date("d"), date("Y")));
						break;
					case 'years':
						$couponExpiry = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") + $oneTag->delay));
				}
			}
			if(!empty($couponName)){
				$couponName = str_replace("[key]", $key, $couponName);
				if(strlen($couponName) > 10){
					ACYSMS::enqueueMessage(JText::_('COM_MIJOSHOP_SALE_VOUCHER_ERROR_CODE'), 'error');
				}else if(strlen($couponName) < 3){
					ACYSMS::enqueueMessage(JText::_('COM_MIJOSHOP_SALE_VOUCHER_ERROR_CODE'), 'error');
					$couponName = $couponName.$key;
				}
				$couponName = substr($couponName, 0, 10);
				$message->message_body = str_replace(array_search($oneTag, $tags), $couponName, $message->message_body);
				if($send){
					$db = JFactory::getDBO();

					$query = "INSERT INTO #__mijoshop_coupon (name,code,type,discount,logged,shipping,total,date_start,date_end,uses_total,uses_customer,status,date_added)
					VALUES(".$db->Quote($couponName).",".$db->Quote($couponName).",".$db->Quote($couponTypeValue).",".intval($couponValue).",0,0,0,now(),".$db->Quote($couponExpiry).",1,1,1,now())";
					$db->setQuery($query);
					$db->query();
				}
			}
		}
	}

	private function _replaceUserTags(&$message, &$user, $send = true){
		$db = JFactory::getDBO();
		$helperPlugin = ACYSMS::get('helper.plugins');

		$match = '#(?:{|%7B)mijoShopUser:(.*)(?:}|%7D)#Ui';
		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;

		if(!isset($user->mijoshop)){
			if(!empty($user->joomla->id)){
				$query = 'SELECT *
				 FROM #__mijoshop_customer as mijoshopusers
				 JOIN #__mijoshop_juser_ocustomer_map as mijoshoprelation ON mijoshopusers.customer_id = mijoshoprelation.ocustomer_id
				 JOIN '.ACYSMS::table('users', false).' as joomusers ON joomusers.id = mijoshoprelation.juser_id
				 WHERE id = '.intval($user->joomla->id).' AND customer_id > 0';
				$db->setQuery($query);
				$user->mijoshop = $db->loadObject();
			}
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
			$tags[$oneTag] = (isset($user->mijoshop->$field) && strlen($user->mijoshop->$field) > 0) ? $user->mijoshop->$field : $mytag->default;
			$helperPlugin->formatString($tags[$oneTag], $mytag);
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}




	function onACYSMSDisplayFiltersSimpleMessage($componentName, &$filters){
		$app = JFactory::getApplication();
		$config = ACYSMS::config();
		$allowCustomerManagement = $config->get('allowCustomersManagement');
		$displayToCustomers = $this->params->get('displayToCustomers', '1');
		if($allowCustomerManagement && empty($displayToCustomers) && !$app->isAdmin()) return;

		$app = JFactory::getApplication();
		$helperPlugin = ACYSMS::get('helper.plugins');

		$newFilter = new stdClass();
		$newFilter->name = JText::sprintf('SMS_X_ORDER', 'Mijoshop');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('mijoshoporder'))) $filters['ecommerceFilters']['mijoshoporder'] = $newFilter;
	}

	function onACYSMSDisplayFilterParams_mijoshoporder($message){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$query = 'SELECT DISTINCT cat.category_id, name FROM #__mijoshop_category cat
					INNER JOIN #__mijoshop_category_description catDesc ON cat.category_id = catDesc.category_id';
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$categoryAvailable = array();
		$categoryAvailable[] = JHTML::_('select.option', '', JText::_('SMS_ANY_CATEGORIES'));
		foreach($categories as $category){
			$categoryAvailable[] = JHTML::_('select.option', $category->category_id, $category->name);
		}
		$categoryDropDown = JHTML::_('select.genericlist', $categoryAvailable, "data[message][message_receiver][standard][mijoshoporders][category]", '', 'value', 'text', '', 'categoryDropDown_');


		$query = 'SELECT * FROM #__mijoshop_order_status';
		$db->setQuery($query);
		$status = $db->loadObjectList();
		$orderStatus = array();
		$orderStatus[] = JHTML::_('select.option', '', JText::_('SMS_ALL_STATUS'));
		foreach($status as $oneStatus){
			$orderStatus[] = JHTML::_('select.option', $oneStatus->order_status_id, $oneStatus->name);
		}
		$orderStatusDropDown = JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][standard][mijoshoporders][status]", '', 'value', 'text', '', 'orderStatus_');

		$productName = '';
		if(!empty($message->message_receiver['standard']['mijoshoporders']['productName'])) $productName = $message->message_receiver['standard']['mijoshoporders']['productName'];

		$ctrl = 'cpanel';
		if(!$app->isAdmin()) $ctrl = 'frontcpanel';

		echo JText::sprintf('SMS_ORDER_WITH_STATUS', $orderStatusDropDown).'<br />';
		echo JText::_('SMS_ORDER_CONTAINS_PRODUCT').' : <span id="displayedMijoProduct"/>'.$productName.'</span><a class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=plgtrigger&plg=mijoshop&fctName=displayMijoArticles\');return false; " rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
		echo '<input type="hidden" name="data[message][message_receiver][standard][mijoshoporders][product]" id="selectedMijoProduct"></input><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][mijoshoporders][productName]" id="hiddenMijoProduct"></input><br />';
		if(!empty($categoryDropDown)) echo JText::_('SMS_ONLY_ORDER_CONTAINS_PRODUCT_FROM_CATEGORY').' : '.$categoryDropDown.'<br />';
	}

	function onAcySMSdisplayMijoArticles(){
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'component.css');


		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYSMS_COMPONENT.'mijoproducts';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'product.product_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$query = 'SELECT product.product_id, productDesc.name AS product_name, productDesc.description AS product_description, GROUP_CONCAT(distinct cat.name)  AS product_category
				FROM #__mijoshop_product product INNER JOIN #__mijoshop_product_description productDesc ON product.product_id = productDesc.product_id
				LEFT JOIN #__mijoshop_product_to_category pt ON pt.product_id = product.product_id
				LEFT JOIN #__mijoshop_category_description cat ON cat.category_id = pt.category_id';


		$searchMap = array('productDesc.name', 'productDesc.description', 'cat.name');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';

		$query .= ' GROUP BY product.product_id';

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

				parent.window.document.getElementById("selectedMijoProduct").value = selected.substring(0, selected.length - 1);

				parent.window.document.getElementById("displayedMijoProduct").innerHTML = displayed.substring(1, displayed.length - 3);
				parent.window.document.getElementById("hiddenMijoProduct").value = displayed.substring(1, displayed.length - 3);


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
							<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'productDesc.name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titledesc">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_DESCRIPTION'), 'productDesc.description', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titlecat">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_CATEGORY'), 'cat.name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titleid">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'product.product_id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
									<input type="checkbox" value="<?php echo $row->product_id ?>" id="cb<?php echo $i; ?>" onclick="addProduct();">
								</td>
								<td align="center" id="productNamecb<?php echo $i; ?>">
									<?php
									echo $row->product_name
									?>
								</td>
								<td align="center">
									<?php
									echo substr(strip_tags(html_entity_decode($row->product_description), '<br>'), 0, 200).'...';
									?>
								</td>
								<td align="center">
									<?php
									echo $row->product_category;
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
			</div>
		</div>
		<?php
	}

	function onACYSMSSelectData_mijoshoporder(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['mijoshoporders']['product']) && empty($message->message_receiver['standard']['mijoshoporders']['category']) && empty($message->message_receiver['standard']['mijoshoporders']['status'])) return;

		if(empty($acyquery->join['mijoshoprelation'])){
			$acyquery->join['mijoshoprelation'] = 'JOIN #__mijoshop_juser_ocustomer_map mijoshoprelation ON joomusers.id = mijoshoprelation.juser_id';
		}

		$acyquery->join['mijoshoporder'] = 'JOIN #__mijoshop_order AS mijoshoporder ON mijoshoporder.customer_id = mijoshoprelation.ocustomer_id';
		$acyquery->join['mijoshoporderproduct'] = 'LEFT JOIN #__mijoshop_order_product AS mijoshoporderproduct ON mijoshoporderproduct.order_id =  mijoshoporder.order_id';
		$acyquery->join['mijoshopproduct'] = 'LEFT JOIN #__mijoshop_product as mijoshopproduct ON mijoshopproduct.product_id =  mijoshoporderproduct.product_id';
		$acyquery->join['mijoshopproducttocategory'] = 'LEFT JOIN #__mijoshop_product_to_category as mijoshopproducttocategory ON mijoshopproducttocategory.product_id = mijoshopproduct.product_id';

		if(!empty($message->message_receiver['standard']['mijoshoporders']['product'])){
			$listProduct = $message->message_receiver['standard']['mijoshoporders']['product'];
			$listProductExploded = explode(',', $listProduct);

			JArrayHelper::toInteger($listProductExploded);

			$acyquery->where[] = ' mijoshopproduct.product_id IN ('.implode(',', $listProductExploded).')';
		}
		if(!empty($message->message_receiver['standard']['mijoshoporders']['category'])){
			$acyquery->where[] = ' mijoshopproducttocategory.category_id ='.intval($message->message_receiver['standard']['mijoshoporders']['category']);
		}
		if(!empty($message->message_receiver['standard']['mijoshoporders']['status']) && !empty($message->message_receiver['standard']['mijoshoporders']['status'])){
			$acyquery->where[] = ' mijoshoporder.order_status_id = '.intval($message->message_receiver['standard']['mijoshoporders']['status']);
		}
	}




	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_X_ORDER', 'Mijoshop');
		$authorizedFilters['mijoshoporder'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_mijoshoporder(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}
}//endclass
