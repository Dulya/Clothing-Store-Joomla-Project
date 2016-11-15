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

class plgHikashopAcysmsorders extends JPlugin{
	function __construct(&$subject, $config){
		if(!file_exists(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_hikashop')) return;
		parent::__construct($subject, $config);
		if(!isset($this->params)){
			$plugin = JPluginHelper::getPlugin('hikashop', 'acysmsorders');
			$this->params = new JParameter($plugin->params);
		}
	}


	private function init(){
		if(defined('ACYSMS_COMPONENT')) return true;
		$acySmsHelper = rtrim(JPATH_ROOT, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php';
		if(file_exists($acySmsHelper)){
			include_once $acySmsHelper;
		}else return false;
		return defined('ACYSMS_COMPONENT');
	}

	public function onAfterOrderCreate(&$order, &$send_email){
		$this->manageMessage($order, 'afterorderscreate');
	}

	public function onAfterOrderUpdate(&$order, &$send_email){
		$this->manageMessage($order, 'afterordersupdate');
		$this->checkForAcySMSCredits($order);
	}

	private function manageMessage(&$order, $status){
		$integrationFrom = 'hikashop';

		if(!$this->init()) return;
		$db = JFactory::getDBO();
		$messageClass = ACYSMS::get('class.message');
		$allMessages = $messageClass->getAutoMessage('hikashoporders');
		if(empty($allMessages)) return;

		foreach($allMessages as $messageID => $oneMessage){
			$continueToNextMsg = true;
			if(empty($order->order_status)) continue;
			if(empty($oneMessage->message_receiver['auto']['hikashoporders']['status'])) continue;
			if(empty($order->old->order_status) && !empty($oneMessage->message_receiver['auto']['hikashoporders']['status']['status1'])) continue;
			if(!empty($oneMessage->message_receiver['auto']['hikashoporders']['status']['status1']) && $order->old->order_status != $oneMessage->message_receiver['auto']['hikashoporders']['status']['status1']) continue;
			if(!empty($oneMessage->message_receiver['auto']['hikashoporders']['status']['status2']) && $order->order_status != $oneMessage->message_receiver['auto']['hikashoporders']['status']['status2']) continue;


			if(!empty($oneMessage->message_receiver['auto']['hikashoporders']['product'])){
				$query = 'SELECT product_id FROM #__hikashop_order_product WHERE order_id = '.intval($order->order_id);
				$db->setQuery($query);
				$productIds = acysms_loadResultArray($db);
				$listProductFilter = explode(',', $oneMessage->message_receiver['auto']['hikashoporders']['product']);
				foreach($listProductFilter as $productFilter){
					if(in_array($productFilter, $productIds)) $continueToNextMsg = false;
				}
				if($continueToNextMsg) continue;
			}
			if(!empty($oneMessage->message_receiver['auto']['hikashoporders']['category'])){
				$query = 'SELECT hikaProductCategory.category_id
					FROM #__hikashop_order_product AS hikaOrderProduct
					JOIN #__hikashop_product_category AS hikaProductCategory
					ON hikaOrderProduct.product_id = hikaProductCategory.product_id
					WHERE order_id = '.intval($order->order_id);
				$db->setQuery($query);
				$categoryIds = acysms_loadResultArray($db);
				if(!in_array($oneMessage->message_receiver['auto']['hikashoporders']['category'], $categoryIds)) continue;
			}

			$senddate = strtotime('+'.intval($oneMessage->message_receiver['auto']['hikashoporders']['delay']['duration']).' '.$oneMessage->message_receiver['auto']['hikashoporders']['delay']['timevalue'], time());

			if($oneMessage->message_receiver_table == 'hikashop'){
				if(empty($oneMessage->message_receiver['auto']['hikashoporders']['address'])) continue;
				$paramqueue = new stdClass;
				$address_type = 'order_'.$oneMessage->message_receiver['auto']['hikashoporders']['address'].'_address_id';

				if(!empty($order->$address_type)){
					$paramqueue->address_id = $order->$address_type;
				}else if(empty($order->$address_type) && !empty($order->old->$address_type)){
					$paramqueue->address_id = $order->old->$address_type;
				}else continue;
			}
			if($oneMessage->message_receiver_table != 'hikashop'){
				if($oneMessage->message_receiver['auto']['hikashoporders']['receiver_type'] == 'buyer'){
					if(!empty($order->order_user_id)){
						$receiver_id = $order->order_user_id;
					}else if(empty($order->order_user_id) && !empty($order->old->order_user_id)){
						$receiver_id = $order->old->order_user_id;
					}else continue;
				}else if($oneMessage->message_receiver['auto']['hikashoporders']['receiver_type'] == 'all'){
					$receiver_id = '';
				}
			}else{
				if(empty($oneMessage->message_receiver['auto']['hikashoporders']['address'])) continue;
				$paramqueue = new stdClass;

				$address_type = 'order_'.$oneMessage->message_receiver['auto']['hikashoporders']['address'].'_address_id';
				if(!empty($order->$address_type)){
					$paramqueue->address_id = $order->$address_type;
				}else if(empty($order->$address_type) && !empty($order->old->$address_type)){
					$paramqueue->address_id = $order->old->$address_type;
				}else continue;

				if(!empty($order->order_user_id)){
					$receiver_id = $order->order_user_id;
				}else if(empty($order->order_user_id) && !empty($order->old->order_user_id)){
					$receiver_id = $order->old->order_user_id;
				}else continue;
			}

			$params = new stdClass();
			$params->order_id = $order->order_id;
			$paramqueue = serialize($params);

			$acyquery = ACYSMS::get('class.acyquery');
			$integrationTo = $oneMessage->message_receiver_table;
			$integration = ACYSMS::getIntegration($integrationTo);
			$integration->initQuery($acyquery);
			$acyquery->addMessageFilters($oneMessage);

			if(!empty($receiver_id)) $acyquery->addUserFilters(array($receiver_id), $integrationFrom, $integrationTo);

			$querySelect = $acyquery->getQuery(array($oneMessage->message_id.','.$integration->tableAlias.'.'.$integration->primaryField.','.$db->Quote($oneMessage->message_receiver_table).','.$senddate.',0,2,'.$db->Quote($paramqueue)));

			$finalQuery = 'INSERT IGNORE INTO `#__acysms_queue` (`queue_message_id`,`queue_receiver_id`,`queue_receiver_table`,`queue_senddate`,`queue_try`,`queue_priority`, `queue_paramqueue`) '.$querySelect;
			$db->setQuery($finalQuery);
			$db->query();

			if(empty($oneMessage->message_receiver['auto']['hikashoporders']['delay']['duration'])){
				$queueHelper = ACYSMS::get('helper.queue');
				$queueHelper->report = false;
				$queueHelper->message_id = $oneMessage->message_id;
				$queueHelper->process();
			}
		}
	}

	public function checkForAcySMSCredits($order){
		if(empty($order->order_status) || $order->order_status != 'confirmed') return;


		$db = JFactory::getDBO();
		$AcySMSProductID = $this->params->get('productId');
		$customerClass = ACYSMS::get('class.customer');

		$query = 'SELECT product.product_id AS product_id, product.order_product_quantity AS quantity, order_product_code AS product_code
					FROM `'.hikashop_table('order_product').'` AS product
					WHERE product.order_id = '.intval($order->order_id);
		$db->setQuery($query);
		$allProducts = $db->loadObjectList();
		if(empty($allProducts)) return;


		$hikaShopIntegration = ACYSMS::getIntegration('hikashop');
		$joomUsers = empty($order->order_user_id) ? $hikaShopIntegration->getJoomUserId($order->old->order_user_id) : $hikaShopIntegration->getJoomUserId($order->order_user_id);
		$joomlaUserId = reset($joomUsers);
		if(empty($joomlaUserId)) return;

		$creditsAdded = false;

		foreach($allProducts as $oneProduct){
			if(preg_match('#SMSCREDITS_([0-9]*)#', $oneProduct->product_code, $result)){
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
