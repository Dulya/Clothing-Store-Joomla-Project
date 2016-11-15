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

class plgAcysmsRedshop extends JPlugin{
	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_redshop')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'redshop');
			$this->params = new acysmsParameter($plugin->params);
		}
		$lang = JFactory::getLanguage();
		$lang->load('com_redshop', JPATH_SITE);
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
		$newFilter->name = JText::sprintf('SMS_X_ORDER', 'RedShop');
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('redshoporder'))) $filters['ecommerceFilters']['redshoporder'] = $newFilter;
	}

	function onACYSMSDisplayFilterParams_redshoporder($message){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

		$query = 'SELECT DISTINCT category_name, category_id FROM #__redshop_category ORDER BY category_id';
		$db->setQuery($query);
		$categories = $db->loadObjectList();

		$categoryAvailable = array();
		$categoryAvailable[] = JHTML::_('select.option', '', JText::_('SMS_ANY_CATEGORIES'));
		foreach($categories as $category){
			$categoryAvailable[] = JHTML::_('select.option', $category->category_id, $category->category_name);
		}
		$categoryDropDown = JHTML::_('select.genericlist', $categoryAvailable, "data[message][message_receiver][standard][redshoporders][category]", '', 'value', 'text', '', 'categoryDropDown_');


		$query = 'SELECT order_status_name, order_status_code FROM #__redshop_order_status';
		$db->setQuery($query);
		$status = $db->loadObjectList();
		$orderStatus = array();
		$orderStatus[] = JHTML::_('select.option', '', JText::_('SMS_ALL_STATUS'));
		foreach($status as $oneStatus){
			$orderStatus[] = JHTML::_('select.option', $oneStatus->order_status_code, $oneStatus->order_status_name);
		}
		$orderStatusDropDown = JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][standard][redshoporders][status]", '', 'value', 'text', '', 'orderStatus_');

		$productName = '';
		if(!empty($message->message_receiver['standard']['redshoporders']['productName'])) $productName = $message->message_receiver['standard']['redshoporders']['productName'];

		$ctrl = 'cpanel';
		if(!$app->isAdmin()) $ctrl = 'frontcpanel';

		echo JText::sprintf('SMS_ORDER_WITH_STATUS', $orderStatusDropDown).'<br />';
		echo JText::_('SMS_ORDER_CONTAINS_PRODUCT').' : <span id="displayedRedProduct"/>'.$productName.'</span><a class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=plgtrigger&plg=redshop&fctName=displayRedArticles\');return false; " rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
		echo '<input type="hidden" name="data[message][message_receiver][standard][redshoporders][product]" id="selectedRedProduct"></input><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][redshoporders][productName]" id="hiddenRedProduct"></input><br />';
		if(!empty($categoryDropDown)) echo JText::_('SMS_ONLY_ORDER_CONTAINS_PRODUCT_FROM_CATEGORY').' : '.$categoryDropDown.'<br />';
	}

	function onAcySMSdisplayRedArticles(){
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$doc->addStyleSheet(ACYSMS_CSS.'component.css');


		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYSMS_COMPONENT.'redshop';
		$pageInfo->filter->order->value = $app->getUserStateFromRequest($paramBase.".filter_order", 'filter_order', 'product_id', 'cmd');
		$pageInfo->filter->order->dir = $app->getUserStateFromRequest($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		$pageInfo->search = $app->getUserStateFromRequest($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = JString::strtolower(trim($pageInfo->search));

		$pageInfo->limit->value = $app->getUserStateFromRequest($paramBase.'.list_limit', 'limit', $app->getCfg('list_limit'), 'int');
		$pageInfo->limit->start = $app->getUserStateFromRequest($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$query = 'SELECT redshopproduct.product_id, redshopproduct.product_name, redshopproduct.product_s_desc, category_name
					FROM #__redshop_product AS redshopproduct
					LEFT JOIN #__redshop_product_category_xref AS redshopproductcategory ON redshopproductcategory.product_id = redshopproduct.product_id
					LEFT JOIN #__redshop_category AS redshopcategory ON redshopcategory.category_id = redshopproductcategory.product_id';

		$searchMap = array('category_name', 'product_name', 'product_s_desc');
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acysms_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';

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

				parent.window.document.getElementById("selectedRedProduct").value = selected.substring(0, selected.length - 1);

				parent.window.document.getElementById("displayedRedProduct").innerHTML = displayed.substring(1, displayed.length - 3);
				parent.window.document.getElementById("hiddenRedProduct").value = displayed.substring(1, displayed.length - 3);


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
							<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'redshopproduct.product_name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titledesc">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_DESCRIPTION'), 'redshopproduct.product_s_desc', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titlecat">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_CATEGORY'), 'redshopcategory.category_name', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
						</th>
						<th class="title titleid">
							<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'redshopproduct.product_id', $pageInfo->filter->order->dir, $pageInfo->filter->order->value); ?>
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
									if(!empty($row->product_s_desc)) echo substr(strip_tags(html_entity_decode($row->product_s_desc), '<br>'), 0, 200).'...';
									?>
								</td>
								<td align="center">
									<?php
									echo $row->category_name;
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

	function onACYSMSSelectData_redshoporder(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['redshoporders']['product']) && empty($message->message_receiver['standard']['redshoporders']['category']) && empty($message->message_receiver['standard']['redshoporders']['status'])) return;

		$db = JFactory::getDBO();
		if(empty($acyquery->join['redshopusers'])){
			$acyquery->join['redshopusers'] = 'JOIN #__redshop_users_info as redshopusers ON joomusers.id = redshopusers.user_id ';
		}

		$acyquery->join['redshoporder'] = 'JOIN #__redshop_orders AS redshoporders ON redshoporders.user_id = redshopusers.user_id';
		$acyquery->join['redshoporderproduct'] = 'LEFT JOIN #__redshop_order_item AS redshoporderproduct ON redshoporderproduct.order_id =  redshoporders.order_id';
		$acyquery->join['redshopproduct'] = 'LEFT JOIN #__redshop_product as redshopproduct ON redshopproduct.product_id =  redshoporderproduct.product_id';
		$acyquery->join['redshopproducttocategory'] = 'LEFT JOIN #__redshop_product_category_xref AS redshopproducttocategory ON redshopproducttocategory.product_id = redshopproduct.product_id';

		if(!empty($message->message_receiver['standard']['redshoporders']['product'])){
			$listProduct = $message->message_receiver['standard']['redshoporders']['product'];
			$listProductExploded = explode(',', $listProduct);

			JArrayHelper::toInteger($listProductExploded);

			$acyquery->where[] = ' redshopproduct.product_id IN ('.implode(',', $listProductExploded).')';
		}
		if(!empty($message->message_receiver['standard']['redshoporders']['category'])){
			$acyquery->where[] = ' redshopproducttocategory.category_id ='.intval($message->message_receiver['standard']['redshoporders']['category']);
		}
		if(!empty($message->message_receiver['standard']['redshoporders']['status']) && !empty($message->message_receiver['standard']['redshoporders']['status'])){
			$acyquery->where[] = ' redshoporders.order_status = '.$db->Quote($message->message_receiver['standard']['redshoporders']['status']);
		}
	}





	function onACYSMSGetTags(&$tags){
		$integration = ACYSMS::getIntegration('redshop');
		if(!$integration->isPresent()) return;

		$tags['ecommerceTags']['redshopuser'] = new stdClass();
		$tags['ecommerceTags']['redshopuser']->name = JText::sprintf('SMS_X_USER_INFO', 'RedShop');


		$tableFields = acysms_getColumns('#__redshop_users_info');

		$tags['ecommerceTags']['redshopuser']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['ecommerceTags']['redshopuser']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{redshopuser:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['ecommerceTags']['redshopuser']->content .= '</tbody></table>';


		$tags['ecommerceTags']['rshopCoupon'] = new stdClass();
		$tags['ecommerceTags']['rshopCoupon']->name = JText::sprintf('SMS_X_COUPON', 'RedShop');
		$prefix = 'rshop';


		if(empty($field->options['format'])) $field->options['format'] = "%d %m %Y";
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
		$dayField = JHTML::_('select.genericlist', $days, '', 'style="width:50px;" class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'd'), $prefix.'datascheduleddateday');
		$monthField = JHTML::_('select.genericlist', $months, '', 'style="width:100px;" class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'm'), $prefix.'datascheduleddatemonth');
		$yearField = JHTML::_('select.genericlist', $years, '', 'style="width:70px;" class="inputbox"', 'value', 'text', ACYSMS::getDate(time(), 'Y'), $prefix.'datascheduleddateyear');
		$timeField = array($dayField, $monthField, $yearField);

		$value = array();
		$value[0] = JHTML::_('select.option', 'percent', '%');
		$value[1] = JHTML::_('select.option', 'price', JText::_('COM_REDSHOP_TOTAL'));
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

		$tags['ecommerceTags']['rshopCoupon']->content = '<table class="acysms_blocktable"><tbody>';
		$tags['ecommerceTags']['rshopCoupon']->content .= '<tr><td class="key">'.JText::_('COM_REDSHOP_COUPON_CODE').'</th> <td colspan="2"><input id="'.$prefix.'coupon" type="textbox" maxlength="32" value="[key][user]"/> </td>';
		$tags['ecommerceTags']['rshopCoupon']->content .= '<tr><td class="key">'.JText::_('COM_REDSHOP_COUPON_VALUE').'</th> <td><input id="'.$prefix.'couponvalue" size="5" type="textbox"/> ';
		$tags['ecommerceTags']['rshopCoupon']->content .= $listCouponValue.'</td></tr>';
		$tags['ecommerceTags']['rshopCoupon']->content .= '<tr><td class="key">'.JText::_('SMS_EXPIRY_DATE').'</th> <td colspan="2">'.$radioListExpiry.'</td> </tr>';
		$tags['ecommerceTags']['rshopCoupon']->content .= '<tr id="'.$prefix.'expiryDate"> <td>'.JText::_('SMS_FIELD_DATE').'</th> <td colspan="2">'.$timeField[0].$timeField[1].$timeField[2].'</td></tr>';
		$tags['ecommerceTags']['rshopCoupon']->content .= '<tr id="'.$prefix.'expiryDelay" style="display:none"> <td>'.JText::_('SMS_DELAY').'</th> <td colspan="2">'.$displayDelay[0].$displayDelay[1].'</td></tr>';
		$tags['ecommerceTags']['rshopCoupon']->content .= '<tr><td colspan="3"> <input type="button" value="'.JText::_('SMS_INSERT_COUPON').'" onclick="createTagrshopCoupon(\''.$prefix.'\')"/> </td></tr>';
		$tags['ecommerceTags']['rshopCoupon']->content .= '</tbody></table>';
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

			function createTagrshopCoupon(prefix){   //end date of the coupon
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
					var finalCoupon = "{rshopcoupon:" + couponName + "|value:" + couponValue + "|typevalue:" + typeValue + "|delay:" + difference + "|typeofdelay:" + typeOfDifference + "}";
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
					var finalCoupon = "{rshopcoupon:" + couponName + "|value:" + couponValue + "|typevalue:" + typeValue + "|expiry:" + endDate + "}";
				}
				insertTag(finalCoupon);
			}

		</script>
		<?php
	}

	private function _replaceCouponTags($message, $send, $user){
		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'rshopcoupon');

		foreach($tags as $oneTag){
			$key = ACYSMS::generateKey(5);
			$couponName = $oneTag->id;
			$couponValue = $oneTag->value;
			if($oneTag->typevalue == "percent"){
				$couponTypeValue = '1';
			}else{
				$couponTypeValue = '0';
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

				$tempCouponName = str_replace("[user]", "", $couponName); //we delete [user] tags to count the character
				$availableChar = 32 - strlen($tempCouponName);
				if($availableChar > 0){
					if(!empty($user)){
						$userName = str_replace(' ', '', $user->receiver_name);
					}else{
						$userName = JFactory::getUser()->name;
					}
					$userName = substr($userName, 0, $availableChar);
					$couponName = str_replace("[user]", $userName, $couponName);
				}else{
					$couponName = $tempCouponName;
				} //we delete user tag if we can't replace it

				$couponNameBeforeCut = $couponName;
				$couponName = substr($couponName, 0, 32);
				if(strstr(substr($couponNameBeforeCut, 32), $key) == true){
					$couponName = substr_replace($couponName, $key, -strlen($key), strlen($key));
				}

				$message->message_body = str_replace(array_search($oneTag, $tags), $couponName, $message->message_body);
				if($send){
					$db = JFactory::getDBO();
					$query = "INSERT INTO #__redshop_coupons (coupon_code,percent_or_total,start_date,end_date,coupon_value,coupon_left,published,free_shipping)
					VALUES(".$db->Quote($couponName).",".$db->Quote($couponTypeValue).",UNIX_TIMESTAMP(),".intval(strtotime($couponExpiry)).",".intval($couponValue).",1,1,0);";
					$db->setQuery($query);
					$db->query();
				}
			}
		}
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){

		$this->_replaceCouponTags($message, $send, $user);//we replace tag for coupon

		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$helperPlugin = ACYSMS::get('helper.plugins');

		$match = '#(?:{|%7B)redshopuser:(.*)(?:}|%7D)#Ui';
		$variables = array('message_body');
		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;
		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = $integration = ACYSMS::getIntegration($config->get('default_integration'));

		$address = new stdClass();
		if(!isset($user->redShop) && !empty($user->joomla->id)){
			$query = 'SELECT *
						FROM #__redshop_users_info
						WHERE user_id = '.intval($user->joomla->id);
			$db->setQuery($query);
			$address = $db->loadObject();
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
			$tags[$oneTag] = (isset($address->$field) && strlen($address->$field) > 0) ? $address->$field : $mytag->default;
			$helperPlugin->formatString($tags[$oneTag], $mytag);
		}
		$message->message_body = str_replace(array_keys($tags), $tags, $message->message_body);
	}



	public function onACYSMSdisplayAuthorizedFilters(&$authorizedFilters, $type){
		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_X_ORDER', 'RedShop');
		$authorizedFilters['redshoporder'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_redshoporder(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}
}//endclass
