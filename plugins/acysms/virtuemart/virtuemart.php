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

class plgAcysmsVirtuemart extends JPlugin{

	var $lastName = '';
	var $firstName = '';
	var $email = '';
	var $phoneNumber = '';

	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_virtuemart')) return;
		parent::__construct($subject, $config);
		if(!isset ($this->params)){
			$plugin = JPluginHelper::getPlugin('acysms', 'virtuemart');
			$this->params = new acysmsParameter($plugin->params);
		}
		$file = rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_virtuemart'.DS.'version.php';
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
		$lang = JFactory::getLanguage();
		$lang->load('com_virtuemart', JPATH_ADMINISTRATOR.'/components/com_virtuemart');

		defined('VMPATH_ROOT') or define('VMPATH_ROOT', JPATH_ROOT);
		defined('VMPATH_SITE') or define('VMPATH_SITE', VMPATH_ROOT.DS.'components'.DS.'com_virtuemart');
		if(!class_exists('VmConfig')) require(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_virtuemart'.DS.'helpers'.DS.'config.php');
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
		$newFilter->name = JText::sprintf('SMS_X_GROUPS', JText::_('SMS_VIRTUEMART'));
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('virtuemart_groups'))) $filters['ecommerceFilters']['virtuemart_groups'] = $newFilter;

		$secondFilter = new stdClass();
		$secondFilter->name = JText::sprintf('SMS_INTEGRATION_FIELDS', JText::_('SMS_VIRTUEMART'));
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('virtuemartfields'))) $filters['ecommerceFilters']['virtuemartfield'] = $secondFilter;

		$secondFilter = new stdClass();
		$secondFilter->name = JText::sprintf('SMS_X_VENDORS', JText::_('SMS_VIRTUEMART'));
		if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('virtuemart_vendors'))) $filters['ecommerceFilters']['virtuemart_vendors'] = $secondFilter;

		if(!empty($this->version) && version_compare($this->version, '2.0.0', '>')){
			$newFilter = new stdClass();
			$newFilter->name = JText::sprintf('SMS_X_ORDER', JText::_('SMS_VIRTUEMART'));
			if($app->isAdmin() || (!$app->isAdmin() && $helperPlugin->allowSendByGroups('virtuemartorder'))) $filters['ecommerceFilters']['virtuemartorders'] = $newFilter;
		}
	}



	function onACYSMSDisplayFilterParams_virtuemartorders($message){
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();

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
		$categories = $db->loadObjectList();

		$categoryAvailable = array();
		$categoryAvailable[] = JHTML::_('select.option', '', JText::_('SMS_ANY_CATEGORIES'));
		foreach($categories as $category){
			$categoryAvailable[] = JHTML::_('select.option', $category->virtuemart_category_id, $category->category_name);
		}
		$categoryDropDown = JHTML::_('select.genericlist', $categoryAvailable, "data[message][message_receiver][standard][virtuemartorders][category]", '', 'value', 'text', '', 'categoryDropDown_');


		$lang = JFactory::getLanguage();
		$lang->load('com_virtuemart_orders', ACYSMS_ROOT);

		$query = 'SELECT `order_status_code` AS value, `order_status_name` AS text
								 FROM `#__virtuemart_orderstates`
								 WHERE `virtuemart_vendor_id` = 1
								 ORDER BY `ordering` ASC';
		$db->setQuery($query);
		$orders = $db->loadObjectList();

		$orderStatus[] = JHTML::_('select.option', '', JText::_('SMS_ALL_STATUS'));
		foreach($orders as $oneOrder){
			$orderStatus[] = JHTML::_('select.option', $oneOrder->value, JText::_($oneOrder->text));
		}
		$orderStatusDropDown = JHTML::_('select.genericlist', $orderStatus, "data[message][message_receiver][standard][virtuemartorders][status]", '', 'value', 'text', '', 'orderStatus_');

		$productName = '';
		if(!empty($message->message_receiver['standard']['virtuemartorders']['productName'])) $productName = $message->message_receiver['standard']['virtuemartorders']['productName'];

		$ctrl = 'cpanel';
		if(!$app->isAdmin()) $ctrl = 'frontcpanel';

		echo JText::sprintf('SMS_ORDER_WITH_STATUS', $orderStatusDropDown).'<br />';
		echo JText::_('SMS_ORDER_CONTAINS_PRODUCT').' : <span id="displayedVMProduct">'.$productName.'</span><a class="modal"  onclick="window.acysms_js.openBox(this,\'index.php?option=com_acysms&tmpl=component&ctrl='.$ctrl.'&task=plgtrigger&plg=virtuemart&fctName=displayVMArticles\');return false; " rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
		echo '<input type="hidden" name="data[message][message_receiver][standard][virtuemartorders][product]" id="selectedVMProduct"></input><br />';
		echo '<input type="hidden" name="data[message][message_receiver][standard][virtuemartorders][productName]" id="hiddenVMProduct"></input><br />';
		if(!empty($categoryDropDown)) echo JText::_('SMS_ONLY_ORDER_CONTAINS_PRODUCT_FROM_CATEGORY').' : '.$categoryDropDown.'<br />';
	}

	function onACYSMSDisplayFilterParams_virtuemart_vendors($message){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$my = JFactory::getUser();
		if(!ACYSMS_J16){
			$myJoomlaGroups = array($my->gid);
		}else{
			jimport('joomla.access.access');
			$myJoomlaGroups = JAccess::getGroupsByUser($my->id, false);
		}

		if(!$app->isAdmin()){
			$frontEndFilters = $config->get('frontEndFilters');
			if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

			$availableLists = array();
			foreach($frontEndFilters as $oneCondition){
				if($oneCondition['filters'] != 'virtuemart_vendors') continue;
				if(empty($oneCondition['filterDetails']) || empty($oneCondition['filterDetails']['virtuemart_vendors'])) continue;
				if($oneCondition['typeDetails'] != 'all' && !in_array($oneCondition['typeDetails'], $myJoomlaGroups)) continue;
				$availableLists = array_merge($availableLists, $oneCondition['filterDetails']['virtuemart_vendors']);
			}
			if(empty($availableLists)) return;
		}

		if(version_compare($this->version, '2.0.0', '<')){
			$query = "SELECT vendor_name, vendor_id FROM #__vm_vendor";
		}else{
			$query = "SELECT vendor_name, virtuemart_vendor_id as vendor_id FROM #__virtuemart_vendors";
		}
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		echo JText::sprintf('SMS_SEND_X_VENDORS', JText::_('SMS_VIRTUEMART')).'<br />';
		foreach($groups as $oneGroup){
			if(!$app->isAdmin()){
				if(!in_array($oneGroup->vendor_id, $availableLists)) continue;
			} ?>
			<label><input type="checkbox" name="data[message][message_receiver][standard][virtuemart][vendors][<?php echo $oneGroup->vendor_id; ?>]" value="<?php echo $oneGroup->vendor_id ?>" title="<?php echo $oneGroup->vendor_name ?>"/> <?php echo $oneGroup->vendor_name ?></label><br/>
		<?php }
	}

	function onACYSMSDisplayFilterParams_virtuemartfield($message){

		if(version_compare($this->version, '2.0.0', '<')){
			$fields = acysms_getColumns('#__vm_user_info');
		}else    $fields = acysms_getColumns('#__virtuemart_userinfos');

		if(empty($fields)) return;

		$field = array();
		$field[] = JHTML::_('select.option', '', ' - - - ');
		foreach($fields as $oneField => $fieldType){
			$field[] = JHTML::_('select.option', $oneField, $oneField);
		}

		$relation = array();
		$relation[] = JHTML::_('select.option', 'AND', JText::_('SMS_AND'));
		$relation[] = JHTML::_('select.option', 'OR', JText::_('SMS_OR'));

		$operators = ACYSMS::get('type.operators');

		?>
		<span id="countresult_virtuemartField"></span>
		<?php
		for($i = 0; $i < 5; $i++){
			$operators->extra = 'onchange="countresults(\'virtuemartField\')"';
			$return = '<div id="filter'.$i.'vmfield">'.JHTML::_('select.genericlist', $field, "data[message][message_receiver][standard][virtuemart][virtuemartfield][".$i."][map]", 'onchange="countresults(\'virtuemartField\')" class="inputbox" size="1"', 'value', 'text');
			$return .= ' '.$operators->display("data[message][message_receiver][standard][virtuemart][virtuemartfield][".$i."][operator]").' <input onchange="countresults(\'virtuemartField\')" class="inputbox" type="text" name="data[message][message_receiver][standard][virtuemart][virtuemartfield]['.$i.'][value]" style="width:200px" value=""></div>';
			if($i != 4) $return .= JHTML::_('select.genericlist', $relation, "data[message][message_receiver][standard][virtuemart][virtuemartfield][".$i."][relation]", 'onchange="countresults(\'virtuemartField\')" class="inputbox" style="width:100px;" size="1"', 'value', 'text');
			echo $return;
		}
	}

	function onACYSMSDisplayFilterParams_virtuemart_groups($message){
		$app = JFactory::getApplication();
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$my = JFactory::getUser();
		if(!ACYSMS_J16){
			$myJoomlaGroups = array($my->gid);
		}else{
			jimport('joomla.access.access');
			$myJoomlaGroups = JAccess::getGroupsByUser($my->id, false);
		}

		if(!$app->isAdmin()){
			$frontEndFilters = $config->get('frontEndFilters');
			if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);
			$availableLists = array();
			foreach($frontEndFilters as $oneCondition){
				if($oneCondition['filters'] != 'virtuemart_groups') continue;

				if(empty($oneCondition['filterDetails']) || empty($oneCondition['filterDetails']['virtuemart_groups'])) continue;
				if($oneCondition['typeDetails'] != 'all' && !in_array($oneCondition['typeDetails'], $myJoomlaGroups)) continue;
				$availableLists = array_merge($availableLists, $oneCondition['filterDetails']['virtuemart_groups']);
			}
			if(empty($availableLists)) return;

			if(in_array('userownlists', $availableLists)){
				$currentUser = JFactory::getUser();
				if(version_compare($this->version, '2.0.0', '<')){
					$query = "SELECT shopper_group_id as group_id FROM #__vm_user_shopper_group_xref WHERE user_id = ".intval($currentUser->id);
				}else{
					$query = "SELECT virtuemart_shoppergroup_id as group_id FROM #__virtuemart_vmuser_shoppergroups WHERE virtuemart_user_id = ".intval($currentUser->id);
				}
				$db->setQuery($query);
				$groupsForCurrentUser = $db->loadObjectList();
				foreach($groupsForCurrentUser as $oneGroup){
					$availableLists[] = $oneGroup->group_id;
				}
			}
		}

		if(version_compare($this->version, '2.0.0', '<')){
			$query = "SELECT shopper_group_name, shopper_group_id FROM #__vm_shopper_group";
		}else{
			$query = "SELECT shopper_group_name, virtuemart_shoppergroup_id as group_id FROM #__virtuemart_shoppergroups";
		}
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		echo JText::sprintf('SMS_SEND_X_GROUPS', JText::_('SMS_VIRTUEMART')).'<br />';
		foreach($groups as $oneGroup){
			if(!$app->isAdmin()){
				if(!in_array($oneGroup->group_id, $availableLists)) continue;
			} ?>
			<label><input type="checkbox" name="data[message][message_receiver][standard][virtuemart][groups][<?php echo $oneGroup->group_id; ?>]" value="<?php echo $oneGroup->group_id ?>" title="<?php echo $oneGroup->shopper_group_name ?>"/> <?php echo $oneGroup->shopper_group_name ?></label><br/>
		<?php }
	}




	function onACYSMSSelectData_virtuemartorders(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['virtuemartorders']['product']) && empty($message->message_receiver['standard']['virtuemartorders']['category']) && empty($message->message_receiver['standard']['virtuemartorders']['status'])) return;

		$db = JFactory::getDBO();

		if(empty($acyquery->join['virtuemartusers_2'])){
			$acyquery->join['virtuemartusers_2'] = 'JOIN #__virtuemart_userinfos as virtuemartusers_2 ON joomusers.id = virtuemartusers_2.virtuemart_user_id ';
		}

		$acyquery->join['virtuemartorders'] = 'JOIN #__virtuemart_orders AS virtuemartorders ON virtuemartorders.virtuemart_user_id =  virtuemartusers_2.virtuemart_user_id';
		$acyquery->join['virtuemartorderproduct'] = 'LEFT JOIN #__virtuemart_order_items AS virtuemartorderproduct ON virtuemartorderproduct.virtuemart_order_id =  virtuemartorders.virtuemart_order_id';
		$acyquery->join['virtuemartproduct'] = 'LEFT JOIN #__virtuemart_products AS virtuemartproduct ON virtuemartproduct.virtuemart_product_id =  virtuemartorderproduct.virtuemart_product_id';
		$acyquery->join['virtuemartproductcategories'] = 'LEFT JOIN #__virtuemart_product_categories AS virtuemartproductcategories ON virtuemartproductcategories.virtuemart_product_id = virtuemartproduct.virtuemart_product_id';

		if(!empty($message->message_receiver['standard']['virtuemartorders']['product'])){
			$listProduct = $message->message_receiver['standard']['virtuemartorders']['product'];
			$listProductExploded = explode(',', $listProduct);

			JArrayHelper::toInteger($listProductExploded);


			$acyquery->where[] = ' virtuemartproduct.virtuemart_product_id IN ('.implode(',', $listProductExploded).')';
		}
		if(!empty($message->message_receiver['standard']['virtuemartorders']['category'])){
			$acyquery->where[] = ' virtuemartproductcategories.virtuemart_category_id ='.intval($message->message_receiver['standard']['virtuemartorders']['category']);
		}
		if(!empty($message->message_receiver['standard']['virtuemartorders']['status']) && !empty($message->message_receiver['standard']['virtuemartorders']['status'])){
			$acyquery->where[] = ' virtuemartorders.order_status = '.$db->Quote($message->message_receiver['standard']['virtuemartorders']['status']);
		}
	}

	function onACYSMSSelectData_virtuemart_vendors(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['virtuemart']['vendors'])) return;

		JArrayHelper::toInteger($message->message_receiver['standard']['virtuemart']['vendors']);

		if(version_compare($this->version, '2.0.0', '<')){
			if(empty($acyquery->join['virtuemart_groups'])) $acyquery->join['virtuemart_groups'] = 'JOIN `#__vm_shopper_vendor_xref` as vmusergroup  ON vmusergroup.user_id = joomusers.id';
			$acyquery->where[] = ' vmusergroup.vendor_id IN ('.implode(',', ($message->message_receiver['standard']['virtuemart']['vendors'])).') ';
		}else{
			if(empty($acyquery->join['virtuemart_groups'])) $acyquery->join['virtuemart_groups'] = 'JOIN #__virtuemart_vmuser_shoppergroups as vmusergroup  ON vmusergroup.virtuemart_user_id = joomusers.id';
			$acyquery->join['virtuemart_vendors'] = 'JOIN #__virtuemart_shoppergroups as vmgroups  ON vmusergroup.virtuemart_shoppergroup_id =  vmgroups.virtuemart_shoppergroup_id';
			$acyquery->where[] = ' vmgroups.virtuemart_vendor_id IN ('.implode(',', ($message->message_receiver['standard']['virtuemart']['vendors'])).') ';
		}
	}

	function onACYSMSSelectData_virtuemartfield(&$acyquery, $message){

		if(!empty($message->message_receiver_table)){
			$integration = ACYSMS::getIntegration($message->message_receiver_table);
		}else $integration = ACYSMS::getIntegration();

		if(empty($acyquery->from)) $integration->initQuery($acyquery);
		if(!isset($message->message_receiver['standard']['virtuemart']['virtuemartfield'])) return;

		if(version_compare($this->version, '2.0.0', '<')){
			if(!isset($acyquery->join['virtuemartusers_1']) && $integration->componentName != 'virtuemart_1') $acyquery->join['virtuemartusers_1'] = 'JOIN #__vm_user_info as virtuemartusers_1 ON joomusers.id = virtuemartusers_1.user_id ';
		}else{
			if(!isset($acyquery->join['virtuemartusers_2']) && $integration->componentName != 'virtuemart_2') $acyquery->join['virtuemartusers_2'] = 'JOIN #__virtuemart_userinfos as virtuemartusers_2 ON joomusers.id = virtuemartusers_2.virtuemart_user_id ';
		}

		$addCondition = '';
		$whereConditions = '';

		foreach($message->message_receiver['standard']['virtuemart']['virtuemartfield'] as $filterNumber => $oneFilter){
			if(empty($oneFilter['map'])) continue;
			if(!empty($addCondition)) $whereConditions = '('.$whereConditions.') '.$addCondition.' ';
			if(!empty($oneFilter['relation'])){
				$addCondition = $oneFilter['relation'];
			}else  $addCondition = 'AND';

			$type = '';
			$value = ACYSMS::replaceDate($oneFilter['value']);

			if(version_compare($this->version, '2.0.0', '<')){
				$whereConditions .= $acyquery->convertQuery('virtuemartusers_1', $oneFilter['map'], $oneFilter['operator'], $value, $type);
			}else{
				$whereConditions .= $acyquery->convertQuery('virtuemartusers_2', $oneFilter['map'], $oneFilter['operator'], $value, $type);
			}
		}
		if(!empty($whereConditions)) $acyquery->where[] = $whereConditions;
	}


	function onACYSMSSelectData_virtuemart_groups(&$acyquery, $message){
		if(empty($message->message_receiver['standard']['virtuemart']['groups'])) return;

		JArrayHelper::toInteger($message->message_receiver['standard']['virtuemart']['groups']);

		if(version_compare($this->version, '2.0.0', '<')){
			$acyquery->join['virtuemart_groups'] = 'JOIN `#__vm_shopper_vendor_xref` as vmusergroup  ON vmusergroup.user_id = joomusers.id';
			$acyquery->where[] = ' vmusergroup.shopper_group_id IN ('.implode(',', ($message->message_receiver['standard']['virtuemart']['groups'])).') ';
		}else{
			$acyquery->join['virtuemart_groups'] = 'JOIN #__virtuemart_vmuser_shoppergroups as vmusergroup  ON vmusergroup.virtuemart_user_id = joomusers.id';
			$acyquery->where[] = ' vmusergroup.virtuemart_shoppergroup_id IN ('.implode(',', ($message->message_receiver['standard']['virtuemart']['groups'])).') ';
		}
	}


	function onAcySMSdisplayVMArticles(){
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
						if(document.getElementById("productId" + form[i].id).innerHTML.length == 0) continue;
						oneProductId = document.getElementById("productId" + form[i].id).innerHTML.trim();

						productId = "productId" + form[i].id
						if(!document.getElementById("productName" + form[i].id)) continue;
						if(document.getElementById("productName" + form[i].id).innerHTML.length == 0) continue;
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

				parent.window.document.getElementById("selectedVMProduct").value = selected.substring(0, selected.length - 1);

				parent.window.document.getElementById("displayedVMProduct").innerHTML = displayed.substring(1, displayed.length - 3);
				parent.window.document.getElementById("hiddenVMProduct").value = displayed.substring(1, displayed.length - 3);


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
		if(version_compare($this->version, '2.0.0', '<')){
			$version = 'virtuemart_1';
		}else $version = 'virtuemart_2';
		$integration = ACYSMS::getIntegration($version);
		if(!$integration->isPresent()) return;

		$tags['ecommerceTags']['virtuemartuser'] = new stdClass();
		$tags['ecommerceTags']['virtuemartuser']->name = JText::sprintf('SMS_X_USER_INFO', 'VirtueMart');

		$tableFields = acysms_getColumns('#__virtuemart_userinfos');

		$tableFields['shopper_group_name'] = 'char(128)';

		$tags['ecommerceTags']['virtuemartuser']->content = '<table class="acysms_table"><tbody>';
		$k = 0;
		foreach($tableFields as $oneField => $fieldType){
			$tags['ecommerceTags']['virtuemartuser']->content .= '<tr style="cursor:pointer" onclick="insertTag(\'{virtuemartuser:'.$oneField.'}\')" class="row'.$k.'"><td>'.$oneField.'</td></tr>';
			$k = 1 - $k;
		}
		$tags['ecommerceTags']['virtuemartuser']->content .= '</tbody></table>';



		$tags['ecommerceTags']['vmartCoupon'] = new stdClass();
		$tags['ecommerceTags']['vmartCoupon']->name = JText::sprintf('SMS_X_COUPON', 'VirtueMart');
		$prefix = 'vmart';


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
		$value[1] = JHTML::_('select.option', 'price', JText::_('COM_VIRTUEMART_COUPON_TOTAL'));
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

		$tags['ecommerceTags']['vmartCoupon']->content = '<table class="acysms_blocktable"><tbody>';
		$tags['ecommerceTags']['vmartCoupon']->content .= '<tr><td class="key">'.JText::_('COM_VIRTUEMART_COUPON_CODE').'</th> <td colspan="2"><input id="'.$prefix.'coupon" type="textbox" maxlength="32" value="[key][user]"/> </td>';
		$tags['ecommerceTags']['vmartCoupon']->content .= '<tr><td class="key">'.JText::_('COM_VIRTUEMART_COUPON_VALUE_TIP').'</th> <td><input id="'.$prefix.'couponvalue" size="5" type="textbox"/> ';
		$tags['ecommerceTags']['vmartCoupon']->content .= $listCouponValue.'</td></tr>';
		$tags['ecommerceTags']['vmartCoupon']->content .= '<tr><td class="key">'.JText::_('SMS_EXPIRY_DATE').'</th> <td colspan="2">'.$radioListExpiry.'</td> </tr>';
		$tags['ecommerceTags']['vmartCoupon']->content .= '<tr id="'.$prefix.'expiryDate"> <td>'.JText::_('SMS_FIELD_DATE').'</th> <td colspan="2">'.$timeField[0].$timeField[1].$timeField[2].'</td></tr>';
		$tags['ecommerceTags']['vmartCoupon']->content .= '<tr id="'.$prefix.'expiryDelay" style="display:none"> <td>'.JText::_('SMS_DELAY').'</th> <td colspan="2">'.$displayDelay[0].$displayDelay[1].'</td></tr>';
		$tags['ecommerceTags']['vmartCoupon']->content .= '<tr><td colspan="3"> <input type="button" class="acysms_button" value="'.JText::_('SMS_INSERT_COUPON').'" onclick="createTagvmartCoupon(\''.$prefix.'\')"/> </td></tr>';
		$tags['ecommerceTags']['vmartCoupon']->content .= '</tbody></table>';
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

			function createTagvmartCoupon(prefix){   //end date of the coupon
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
					var finalCoupon = "{vmcoupon:" + couponName + "|value:" + couponValue + "|typevalue:" + typeValue + "|delay:" + difference + "|typeofdelay:" + typeOfDifference + "}";
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
					var finalCoupon = "{vmcoupon:" + couponName + "|value:" + couponValue + "|typevalue:" + typeValue + "|expiry:" + endDate + "}";
				}
				insertTag(finalCoupon);
			}

		</script>
		<?php
	}

	private function _replaceCouponTags($message, $send, $user){

		$helperPlugin = ACYSMS::get('helper.plugins');
		$tags = $helperPlugin->extractTags($message, 'vmcoupon');

		foreach($tags as $oneTag){
			$key = ACYSMS::generateKey(5);
			$couponName = $oneTag->id;
			$couponValue = $oneTag->value;
			if($oneTag->typevalue == "percent"){
				$couponTypeValue = 'percent';
			}else{
				$couponTypeValue = 'total';
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
			if(!empty($couponName)) //change the tag for the real values
			{
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
					$query = "INSERT INTO #__virtuemart_coupons (coupon_code,percent_or_total,coupon_type,coupon_value,coupon_start_date,coupon_expiry_date,coupon_used,published)
					VALUES(".$db->Quote($couponName).",".$db->Quote($couponTypeValue).",'gift',".intval($couponValue).","."now(),".$db->Quote($couponExpiry).",0,1);";
					$db->setQuery($query);
					$db->query();
				}
			}
		}
	}


	function onACYSMSReplaceUserTags(&$message, &$user, $send = true){
		$this->_replaceCouponTags($message, $send, $user); //we replace tags for coupon

		$db = JFactory::getDBO();
		$helperPlugin = ACYSMS::get('helper.plugins');

		$match = '#(?:{|%7B)virtuemartuser:(.*)(?:}|%7D)#Ui';
		if(empty($message->message_body)) return;
		if(!preg_match_all($match, $message->message_body, $results)) return;

		$address = new stdClass();

		if(!empty($user->queue_paramqueue->address_id)){
			$query = 'SELECT *
						FROM #__virtuemart_userinfos as userInfos
						JOIN #__virtuemart_vmuser_shoppergroups as userShopperGroup ON userShopperGroup.virtuemart_user_id = userInfos.virtuemart_user_id
						JOIN #__virtuemart_shoppergroups as shopperGroup ON shopperGroup.virtuemart_shoppergroup_id = userShopperGroup.virtuemart_shoppergroup_id
						WHERE virtuemart_userinfo_id = '.intval($user->queue_paramqueue->address_id);
			$db->setQuery($query);
			$address = $db->loadObject();
		}//From the joomla user id
		else if(!isset($user->virtueMart) && isset($user->joomla->id)){
			$query = 'SELECT *
						FROM #__virtuemart_userinfos as virtuemartusers
						JOIN #__virtuemart_vmuser_shoppergroups as userShopperGroup ON userShopperGroup.virtuemart_user_id = virtuemartusers.virtuemart_user_id
						JOIN #__virtuemart_shoppergroups as shopperGroup ON shopperGroup.virtuemart_shoppergroup_id = userShopperGroup.virtuemart_shoppergroup_id
						WHERE virtuemartusers.virtuemart_user_id = '.intval($user->joomla->id);
			$db->setQuery($query);
			$address = $db->loadObject();
		}elseif(isset($user->virtueMart)){
			$address = $user->virtueMart;
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
		$newType->name = JText::sprintf('SMS_X_GROUPS', JText::_('SMS_VIRTUEMART'));
		$authorizedFilters['virtuemart_groups'] = $newType;

		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_INTEGRATION_FIELDS', JText::_('SMS_VIRTUEMART'));
		$authorizedFilters['virtuemartfields'] = $newType;

		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_X_VENDORS', JText::_('SMS_VIRTUEMART'));
		$authorizedFilters['virtuemart_vendors'] = $newType;

		$newType = new stdClass();
		$newType->name = JText::sprintf('SMS_X_ORDER', JText::_('SMS_VIRTUEMART'));
		$authorizedFilters['virtuemartorder'] = $newType;
	}

	public function onACYSMSdisplayAuthorizedFilters_virtuemartfield(&$authorizedFiltersSelection, $conditionNumber){
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails"></span>';
	}

	public function onACYSMSdisplayAuthorizedFilters_virtuemart_groups(&$authorizedFiltersSelection, $conditionNumber){
		$db = JFactory::getDBO();
		if(version_compare($this->version, '2.0.0', '<')){
			$query = "SELECT shopper_group_name AS title, shopper_group_id AS id FROM #__vm_shopper_group";
		}else{
			$query = "SELECT shopper_group_name AS title, virtuemart_shoppergroup_id AS id FROM #__virtuemart_shoppergroups";
		}
		$db->setQuery($query);
		$vmGroups = $db->loadObjectList();

		$ownListsObject = new stdClass();
		$ownListsObject->id = 'userownlists';
		$ownListsObject->title = JText::_('SMS_USER_OWN_GROUPS');
		array_unshift($vmGroups, $ownListsObject);

		if(empty($vmGroups)) return;

		$config = ACYSMS::config();
		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

		$result = '<br />';
		foreach($vmGroups as $oneGroup){
			if(!empty($frontEndFilters[$conditionNumber]['filterDetails']['virtuemart_groups']) && in_array($oneGroup->id, $frontEndFilters[$conditionNumber]['filterDetails']['virtuemart_groups'])){
				$checked = 'checked="checked"';
			}else $checked = '';
			$result .= '<label><input type="checkbox" name="config[frontEndFilters]['.$conditionNumber.'][filterDetails][virtuemart_groups]['.$oneGroup->id.']" value="'.$oneGroup->id.'" '.$checked.' title= "'.$oneGroup->title.'"/> '.$oneGroup->title.'</label><br />';
		}
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails">'.$result.'</span>';
	}

	public function onACYSMSdisplayAuthorizedFilters_virtuemart_vendors(&$authorizedFiltersSelection, $conditionNumber){
		$db = JFactory::getDBO();
		if(version_compare($this->version, '2.0.0', '<')){
			$query = "SELECT vendor_name AS title, vendor_id AS id FROM #__vm_vendor";
		}else{
			$query = "SELECT vendor_name AS title, virtuemart_vendor_id AS id FROM #__virtuemart_vendors";
		}
		$db->setQuery($query);
		$vmVendors = $db->loadObjectList();

		if(empty($vmVendors)) return;

		$config = ACYSMS::config();
		$frontEndFilters = $config->get('frontEndFilters');
		if(is_string($frontEndFilters)) $frontEndFilters = unserialize($frontEndFilters);

		$result = '<br />';
		foreach($vmVendors as $oneVendor){
			if(!empty($frontEndFilters[$conditionNumber]['filterDetails']['virtuemart_vendors']) && in_array($oneVendor->id, $frontEndFilters[$conditionNumber]['filterDetails']['virtuemart_vendors'])){
				$checked = 'checked="checked"';
			}else $checked = '';
			$result .= '<label><input type="checkbox" name="config[frontEndFilters]['.$conditionNumber.'][filterDetails][virtuemart_vendors]['.$oneVendor->id.']" value="'.$oneVendor->id.'" '.$checked.' title= "'.$oneVendor->title.'"/> '.$oneVendor->title.'</label><br />';
		}
		$authorizedFiltersSelection .= '<span id="'.$conditionNumber.'_acysmsAuthorizedFilterDetails">'.$result.'</span>';
	}



	public function fillViewComponent(&$view_compo){
		$view = JRequest::getCmd('view', '');
		$option = 'com_virtuemart';
		if($view == 'cart' || $view == 'user') $view_compo[$option] = $view;
	}

	public function getNeededIntegration($integration){
		$config = ACYSMS::config();
		if($integration == 'virtuemart'){
			return $config->get('require_confirmation_virtuemart');
		}else if($integration == 'virtuemart_registration') return $config->get('require_confirmation_virtuemart_registration');
	}


	public function displayConfirmationError($informations){
		$task = $informations['task'];
		$option = $informations['option'];
		if($task == 'confirm' && $option == 'com_virtuemart' && !isset($_POST['setpayment'])){
			if(!$this->_getPaymentMethodVM()) return;
			if($this->getNeededIntegration('virtuemart')){
				echo '<script>alert("Phonenumber confirmation didn t process");history.back();</script>';
				exit;
			}
		}else if($task == 'saveUser' && $option == 'com_virtuemart'){
			if($this->getNeededIntegration('virtuemart_registration')){

				$config = ACYSMS::config();
				$phoneField = $config->get('virtuemart_2_field');
				$phoneFieldParameter = JRequest::getCmd($phoneField, '');
				if(empty($phoneFieldParameter)) return;

				echo '<script>alert("Phonenumber confirmation didn\'t process");history.back();</script>';
				exit;
			}
		}
	}

	public function displayConfirmationArea($infoUrl){
		$option = $infoUrl['option'];
		$view = $infoUrl['view'];

		$phoneNumber = $this->_getUserInformationVM('phonenumber');
		$this->phoneNumber = $phoneNumber;
		$this->firstName = $this->_getUserInformationVM('firstname');
		$this->lastName = $this->_getUserInformationVM('lastname');

		if($this->_checkIfConfirmed()) return;

		if($option == 'com_virtuemart' && $view == 'cart'){

			$config = ACYSMS::config();
			$vmPhoneField = $config->get('virtuemart_2_field');
			if(!$this->getNeededIntegration('virtuemart') || empty($vmPhoneField)) return;

			$newField = $this->displayPhoneField('virtuemart'); //we generate the html/js we need for phonenumber validation
			$this->_replaceConfirmButtonVM($newField);
		}else if($option == 'com_virtuemart' && $view == 'user'){

			$addrType = JRequest::getCmd('addrtype', '');
			if($addrType == 'ST') return;

			if(!$this->getNeededIntegration('virtuemart_registration')) return;
			$newField = $this->displayPhoneField('virtuemart_registration');
			$this->_replaceConfirmButtonVMRegistration($newField);
		}
	}

	private function _replaceConfirmButtonVM($newField){
		$body = JResponse::getBody();
		$body = preg_replace('#<button.*id=\"checkoutFormSubmit\".*>.*<\/button>#', $newField, $body);
		JResponse::setBody($body);
	}


	private function _replaceConfirmButtonVMRegistration($newField){
		$body = JResponse::getBody();
		$config = ACYSMS::config();
		$phoneField = $config->get('virtuemart_2_field');
		if(preg_match('<input type="text".*id="'.$phoneField.'_field".*>', $body, $matches)){
			$body = preg_replace('#(<form.*id=\"(adminForm|userForm)\".*>.*<input type=\"text\".*id=\"'.$phoneField.'_field\".*>)(.*<\/form>)#sU', '$1'.$newField.'$3', $body);
		}else{
			$body = preg_replace('#(<button.?name=\"register\".*type=\"submit\".*onclick=\".*myValidator.*\".*>.*<\/button>)#sU', $newField, $body);
		}
		JResponse::setBody($body);
	}

	public function verificationCodeIntegration(&$integrationVerificationCode){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_virtuemart')){
			$integrationVerificationCode['virtuemart'] = true;
			$integrationVerificationCode['virtuemart_registration'] = true;
		}
	}


	private function _getPaymentMethodVM(){
		if(!class_exists('VirtueMartCart')) require(VMPATH_SITE.DS.'helpers'.DS.'cart.php');
		$cart = VirtueMartCart::getCart();

		$db = JFactory::getDBO();
		$db->setQuery('SELECT payment_element FROM #__virtuemart_paymentmethods AS pm WHERE virtuemart_paymentmethod_id ='.intval($cart->virtuemart_paymentmethod_id).' LIMIT 1');
		$element = $db->loadResult();
		if(!empty($element) && $element == 'standard') return true;
		return false;
	}


	private function _getUserInformationVM($information){

		if(!class_exists('VirtueMartCart')) require(VMPATH_SITE.DS.'helpers'.DS.'cart.php');
		$cart = VirtueMartCart::getCart();

		$acyConfig = ACYSMS::config();
		$phoneField = $acyConfig->get('virtuemart_2_field');

		switch($information){
			case 'phonenumber':
				if(is_object($cart->BT) && !empty($cart->BT->$phoneField)){
					return $cart->BT->$phoneField;
				}else if(is_array($cart->BT) && !empty($cart->BT[$phoneField])) return $cart->BT[$phoneField];
				break;
			case 'firstname':
				if(is_object($cart->BT) && !empty($cart->BT->first_name)){
					return $cart->BT->first_name;
				}else if(is_array($cart->BT) && !empty($cart->BT['first_name'])) return $cart->BT['first_name'];
				break;
			case 'lastname':
				if(is_object($cart->BT) && !empty($cart->BT->last_name)){
					return $cart->BT->last_name;
				}else if(is_array($cart->BT) && !empty($cart->BT['last_name'])) return $cart->BT['last_name'];
				break;
		}
		return false;
	}

	public function displayPhoneField($integration, $extraInformations = null){
		$jtextInstruction = 'SMS_VERIFICATION_CODE_SELECT';
		$config = ACYSMS::config();
		$script = '';

		$ajaxURLForCodeRequest = '';
		$idElementCodeRequest = '';
		$phoneFieldToDisplay = '';
		$additionalTreatmentForCodeRequest = '';
		$actionToAddFormCodeRequest = '';
		$ajaxURLForSendCode = '';
		$additionalTreatmentForSendCode = '';


		if($integration == 'virtuemart'){
			if(empty($this->phoneNumber)) return '<div>'.JText::_('SMS_NO_PHONE_FOUND_FOR_VALIDATION').'</div>';


			$ajaxURLForCodeRequest = '"?verificationcode="+verificationCode+"&phonenumber='.$this->phoneNumber.'"';
			$idElementCodeRequest = 'checkoutForm';
			$actionToAddFormCodeRequest = '"phonenumber='.$this->phoneNumber.'&verificationcodesubmited="+verificationCode';
			$ajaxURLForSendCode = '"?sendCode=1&lastname='.$this->lastName.'&firstname='.$this->firstName.'&phonenumber='.$this->phoneNumber.'"';
		}else if($integration == 'virtuemart_registration'){
			$jtextInstruction = 'SMS_VERIFICATION_CODE_CONFIRM';
			$ajaxURLForCodeRequest = '"?verificationcode="+verificationCode+"&phonenumber="+phonenumber';

			$idElementCodeRequest = 'adminForm';

			$phoneField = $config->get('virtuemart_2_field');

			$additionalTreatmentForCodeRequest = 'if(document.getElementById("'.$phoneField.'_field") == undefined) phonenumber = document.getElementById("sms_sent_to").value; else phonenumber = document.getElementById("'.$phoneField.'_field").value;';
			$actionToAddFormCodeRequest = '"phonenumber="+phonenumber+"&verificationcodesubmited="+verificationCode';
			$ajaxURLForSendCode = '"?sendCode=1&lastname="+name+"&phonenumber="+phonenumber';
			$additionalTreatmentForSendCode = '
					if(document.getElementById("adminForm") != undefined) form = document.getElementById("adminForm");
					else if(document.getElementById("userForm") != undefined) form = document.getElementById("userForm");
					else return;

					if(!document.formvalidator.isValid(form)) return;
					if(document.getElementById("'.$phoneField.'_field") == undefined)
							phonenumber = document.getElementsByName("phonenumber_verification[phone_country]")[0].value+document.getElementsByName("phonenumber_verification[phone_num]")[0].value;
					else
							phonenumber = document.getElementById("'.$phoneField.'_field").value;
					name = document.getElementById("first_name_field").value;
			';

			$body = JResponse::getBody();
			if(!preg_match('#<input type="text".*id="'.$phoneField.'_field".*>#', $body)){
				return '<div>'.JText::_('SMS_NO_PHONE_FOUND_FOR_VALIDATION').'</div>';
			}
			$script = '<script>
						if(document.getElementsByName("register")[0] != undefined) document.getElementsByName("register")[0].style.display = "none";
							if(document.querySelector(".buttonBar-right button") != undefined) document.querySelector(".buttonBar-right button").style.display = "none";

					   </script>';
		}else return;

		$script .= '
		<script>

			codeRequest = function(){
				verificationCode = document.getElementById("verification_code").value;
				if(!verificationCode){ alert("'.JText::_('SMS_PLEASE_ENTER_CODE').'"); return;}
				document.getElementById("spinner_button").innerHTML = \'<span id=\"ajaxSpan\" class=\"onload\"></span>\';
				'.$additionalTreatmentForCodeRequest.'
				try{
					new Ajax('.$ajaxURLForCodeRequest.', {
						method: "post",
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.verify) {';
		if($integration == 'virtuemart_registration'){
			$script .= 'if(document.getElementById("adminForm") != undefined) form = document.getElementById("adminForm");
						else if(document.getElementById("userForm") != undefined) form = document.getElementById("userForm");
						else return;

						signParameter = (form.action.contains("?")) ? "&" : "?";
						form.action += signParameter+'.$actionToAddFormCodeRequest.';
						document.getElementById("validation_result").innerHTML = \''.str_replace("'", "\'", JText::_('SMS_VERIFICATION_CODE_SUCCESS')).'\';
						document.getElementById("validation_result").style.color="green";
						document.getElementById("acysms_phoneverification").style.display="none";
						}';
		}else{
			$script .= 'signParameter = (document.getElementById("'.$idElementCodeRequest.'").action.contains("?")) ? "&" : "?";
						document.getElementById("'.$idElementCodeRequest.'").action+=signParameter+'.$actionToAddFormCodeRequest.';
						jQuery(this).vm2front("startVmLoading");
						jQuery("#checkoutForm").append("<input name=\"confirm\" value=\"1\" type=\"hidden\">");
						jQuery("#checkoutForm").submit();
						}';
		}

		$script .= 'else {
												document.getElementById("spinner_button").innerHTML = \'<button type="button" onclick="codeRequest();">'.JText::_('SMS_VERIFY_CODE').'</button>\';
												document.getElementById("validation_result").innerHTML = response.errorMessage;
												document.getElementById("validation_result").style.color="red";
											}
					}
					}).request();
				}catch(err){
					new Request({
						method: "post",
						url: '.$ajaxURLForCodeRequest.',
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.verify) {';
		if($integration == 'virtuemart_registration'){
			$script .= 'if(document.getElementById("adminForm") != undefined) form = document.getElementById("adminForm");
						else if(document.getElementById("userForm") != undefined) form = document.getElementById("userForm");
						else return;

						signParameter = (form.action.contains("?")) ? "&" : "?";
						form.action += signParameter+'.$actionToAddFormCodeRequest.';
						document.getElementById("validation_result").innerHTML = \''.str_replace("'", "\'", JText::_('SMS_VERIFICATION_CODE_SUCCESS')).'\';
						document.getElementById("validation_result").style.color="green";
						document.getElementById("acysms_phoneverification").style.display="none";
						}';
		}else{
			$script .= 'signParameter = (document.getElementById("'.$idElementCodeRequest.'").action.contains("?")) ? "&" : "?";
						document.getElementById("'.$idElementCodeRequest.'").action+=signParameter+'.$actionToAddFormCodeRequest.';
						jQuery(this).vm2front("startVmLoading");
						jQuery("#checkoutForm").append("<input name=\"confirm\" value=\"1\" type=\"hidden\">");
						jQuery("#checkoutForm").submit();
						}';
		}

		$script .= 'else {
												document.getElementById("spinner_button").innerHTML = \'<button type="button" onclick="codeRequest();">'.JText::_('SMS_VERIFY_CODE').'</button>\';
												document.getElementById("validation_result").innerHTML = response.errorMessage;
												document.getElementById("validation_result").style.color="red";
											}
						}
					}).send();
				}
			};
			sendCode = function(){
				'.$additionalTreatmentForSendCode.'
				document.getElementById("spinner_button").innerHTML = "<span id=\"ajaxSpan\" class=\"onload\"></span>";
				try{
					new Ajax('.$ajaxURLForSendCode.', {
						method: "post",
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.sendingResult)
								document.getElementById("acysms_button_send").innerHTML = response.display;
							else {
								document.getElementById("spinner_button").innerHTML = 	\'<button id="send_code" type="button" onclick="sendCode();">'.str_replace("'", "\'", JText::_('SMS_SEND_CODE')).'</button>\';
								document.getElementById("sending_result").innerHTML = response.display;
							}
						}
					}).request();
				}catch(err){
					new Request({
						method: "post",
						url: '.$ajaxURLForSendCode.',
						onSuccess: function(responseText, responseXML) {
							response = JSON.parse(responseText);
							if(response.sendingResult)
								document.getElementById("acysms_button_send").innerHTML = response.display;
							else {
								document.getElementById("spinner_button").innerHTML = 	\'<button id="send_code" type="button" onclick="sendCode();">'.str_replace("'", "\'", JText::_('SMS_SEND_CODE')).'</button>\';
								document.getElementById("sending_result").innerHTML = response.display;
							}
						}
					}).send();
				}
			};
		</script>
		<div id="acysms_button_send">
				<span style="color:#1EA0FC">'.str_replace("'", "\'", JText::_($jtextInstruction)).'</span>
				'.$phoneFieldToDisplay.'
				<div id="spinner_button"><button id="send_code" type="button" onclick="sendCode();">'.JText::_('SMS_SEND_CODE').'</button></div>
				<span style="color:red" id="sending_result"></span>
		</div>';

		return $script;
	}

	private function _checkIfConfirmed(){
		$phoneHelper = ACYSMS::get('helper.phone');
		$userPhoneNumber = $phoneHelper->getValidNum($this->phoneNumber);
		if(empty($userPhoneNumber)) return false;
		$userClass = AcySMS::get('class.user');
		$user = $userClass->getByPhone($userPhoneNumber);
		if(empty($user)) return false;
		$result = unserialize($user->user_activationcode);
		if(isset($result['activation_optin']) && empty($result['activation_optin'])) return true;
		return false;
	}

} //endclass
