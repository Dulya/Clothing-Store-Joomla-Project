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

class ACYSMSGateway_acygateway_gateway extends ACYSMSGateway_default_gateway{

	public $apiKey;
	public $senderName;
	public $waittosend = 0;
	public $indexMessage = 0;
	public $messageToSend = array();
	public $messageResults = array();


	public $sendMessage = true;
	public $deliveryReport = true;
	public $answerManagement = true;

	public $domain = 'www.acygateway.com';
	public $port = 80;

	public $name = 'Acyba Sending Service';
	public $creditsUrl = 'https://www.acyba.com/acysms/purchase-sms.html';


	public $keepInQueue = false;


	public function openSend($message, $phone){
		$this->indexMessage += 1;

		$oneMessage = new stdClass();
		$oneMessage->receiver = $this->checkNum($phone);
		$oneMessage->body = $message;
		$this->messageToSend[$this->indexMessage] = $oneMessage;

		return $this->indexMessage;
	}

	public function displayConfig(){

		$text = "<div> To make your life easier we developed our own gateway to send your SMS. You can <a href=\"http://www.acyba.com/acysms/purchase-sms.html\">check our prices and purchase credits here</a>.
			<br/>
			The sender name needs to be approved before using it.
			<br/>
			The sender name must be a mobile phone number including country code, or a word of up to 11 characters (e.g. John).
			<br/>
			Warning: sender names which contain any characters other than a-z A-Z and 0-9 will intermittently cause poor delivery to many networks. Please do not request such characters, as your request will typically be rejected.
			<br/>
			Please send it to our team at support@acyba.com with the subject 'AcySMS sender name approval'.
		</div>";
		ACYSMS::display($text, 'info');

		$keepInQueueData[] = JHTML::_('select.option', '1', JText::_('SMS_YES'));
		$keepInQueueData[] = JHTML::_('select.option', '0', JText::_('SMS_NO'));

		$keepInQueue = empty($this->keepInQueue) ? 0 : 1;

		$keepInQueueOption = JHTML::_('acysmsselect.radiolist', $keepInQueueData, 'data[senderprofile][senderprofile_params][keepInQueue]', '', 'value', 'text', $keepInQueue);

		?>


		<table>
			<tr>
				<td class="key">
					<label for="senderprofile_apiKey"><?php echo JText::_('SMS_API_KEY'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][apiKey]" id="senderprofile_apiKey" class="inputbox" value="<?php echo htmlspecialchars(@$this->apiKey, ENT_COMPAT, 'UTF-8'); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="senderprofile_senderName"><?php echo JText::_('SMS_SENDER'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][senderName]" id="senderprofile_sendername" class="inputbox" value="<?php echo htmlspecialchars(@$this->senderName, ENT_COMPAT, 'UTF-8'); ?>"/>
				</td>
			</tr>
			<tr>
				<td class="key">
					<label for="senderprofile_senderName"><?php echo ACYSMS::tooltip(JText::_('SMS_KEEP_IN_QUEUE_DESC'), JText::_('SMS_KEEP_IN_QUEUE'), '', JText::_('SMS_KEEP_IN_QUEUE')); ?></label>
				</td>
				<td>
					<?php echo $keepInQueueOption; ?>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label
						for="senderprofile_waittosend"><?php echo JText::sprintf('SMS_WAIT_TO_SEND', '<input type="text" style="width:20px;" name="data[senderprofile][senderprofile_params][waittosend]" id="senderprofile_waittosend" class="inputbox" value="'.intval($this->waittosend).'" />'); ?></label>
				</td>
			</tr>
		</table>

		<?php
	}

	public function closeSend($idMessage){
		if(!empty($this->messageToSend)){
			$this->_sendMessages();
			$this->messageToSend = array();
		}
		if(empty($this->messageResults[$idMessage])){
			$this->errors[] = 'Status not found for the message ID : '.$idMessage;
			$this->errors[] = print_r($this->messageResults, true);
			return false;
		}

		if(!empty($this->messageResults[$idMessage]->smsid)) $this->smsid = $this->messageResults[$idMessage]->smsid;
		if(!empty($this->messageResults[$idMessage]->info)) $this->errors[] = $this->messageResults[$idMessage]->info;
		return $this->messageResults[$idMessage]->status;
	}

	private function _sendMessages(){
		$config = ACYSMS::config();

		$params = new stdClass();
		$params->user_api_key = $this->apiKey;
		$params->messages = array();

		foreach($this->messageToSend as $oneIndex => $oneMessageToSend){
			$message = new stdClass();
			$message->receiver_phone = $oneMessageToSend->receiver;
			$message->message_body = $oneMessageToSend->body;
			$message->sender_name = $this->senderName;
			$message->callback_url = ACYSMS_LIVE.'index.php?option=com_acysms&gateway=acygateway&pass='.$config->get('pass');

			$params->messages[$oneIndex] = $message;
		}

		$jsonString = 'params='.urlencode(json_encode($params));

		ob_start();
		$connexion = fsockopen($this->domain, 80, $errno, $errstr, 30);
		$warning = ob_get_clean();
		if(!$connexion){
			$this->errors[] = 'Error '.$errno.' => '.$errstr.' '.$warning;
			return false;
		}

		$idConnection = count($this->connexions);
		$this->connexions[$idConnection] = $connexion;


		$fsockParameter = "POST /api/sendMsg HTTP/1.1\r\n";
		$fsockParameter .= "Host: www.acygateway.com \r\n";
		$fsockParameter .= "Content-type: application/x-www-form-urlencoded\r\n";
		$fsockParameter .= "Content-length: ".strlen($jsonString)."\r\n\r\n";
		$fsockParameter .= $jsonString;

		fwrite($this->connexions[$idConnection], $fsockParameter);
		$result = $this->readResult_fsock($idConnection);

		if($result === false){
			$this->errors[] = 'Error : Read Result returned false';
			return false;
		}

		if(!strpos($result, '200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}else $res = trim(substr($result, strpos($result, "\r\n\r\n")));
		$result = json_decode($res);
		if(empty($result->messages)){
			$answer = new stdClass();
			$answer->status = $result->status;
			$answer->info = nl2br($result->status_message);
			foreach($this->messageToSend as $oneIndex => $oneMessageSent){
				$this->messageResults[$oneIndex] = $answer;
			}
		}else{
			foreach($result->messages as $oneId => $oneMessage){
				$answer = new stdClass();
				$answer->smsid = empty($oneMessage->message_id) ? '' : $oneMessage->message_id;
				$answer->status = $oneMessage->status;

				if(!empty($oneMessage->status_message)) $answer->info = $oneMessage->status_message;
				$this->messageResults[$oneId] = $answer;
			}
		}
		$this->indexMessage = 0;
	}

	public function afterSaveConfig($senderProfile){
		if(in_array(JRequest::getCmd('task'), array('save', 'apply'))) $this->displayBalance();
	}

	public function getBalance(){
		$fsockParameter = "GET /api/getBalance?apiKey=".urlencode($this->apiKey)." HTTP/1.1\r\n";
		$fsockParameter .= "Host: www.acygateway.com \r\n";
		$fsockParameter .= "Content-type: application/x-www-form-urlencoded\r\n\r\n";

		$idConnection = $this->sendRequest($fsockParameter);
		$result = $this->readResult($idConnection);

		if($result === false){
			ACYSMS::enqueueMessage(implode('\n', $this->errors), 'error');
			return false;
		}
		if(!strpos($result, '200 OK')){
			$this->errors[] = 'Error 200 KO => '.$result;
			return false;
		}
		$res = json_decode(trim(substr($result, strpos($result, "\r\n\r\n"))));


		if(!empty($res->status)){
			$res->user_nb_credits = substr($res->user_nb_credits, 0, strpos($res->user_nb_credits, '.') + 3);
			return array("default" => $res->user_nb_credits);
		}else{
			ACYSMS::enqueueMessage($res->status_message, 'error');
			return false;
		}
	}

	private function displayBalance(){

		$balance = $this->getBalance();

		if($balance === false){
			ACYSMS::enqueueMessage(implode('<br />', $this->errors), 'error');
			return false;
		}


		ACYSMS::enqueueMessage(JText::sprintf('SMS_CREDIT_LEFT_ACCOUNT', $balance["default"]), 'message');
	}


	public function deliveryReport(){

		$callbackInformationsEncoded = JRequest::getVar("callbackInformations", '');
		$callbackInfoDecoded = json_decode($callbackInformationsEncoded);
		if(empty($callbackInfoDecoded)) return;



		$status = array();
		$status[0] = "Not sent";
		$status[1] = "Sent";
		$status[2] = "Accepted by the gateway";
		$status[3] = "Sent to the operator";
		$status[4] = "Buffered";
		$status[5] = "Delivered";
		$status[-1] = "Not delivered";
		$status[-2] = "Timed out";
		$status[-99] = "Error unknown status";

		$deliveryInformations = array();

		foreach($callbackInfoDecoded as $oneCallback){
			$oneInformation = new stdClass();
			$oneInformation->statsdetails_error = array();


			$messageStatus = empty($oneCallback->status) ? '' : $oneCallback->status;
			$completed_time = empty($oneCallback->completed_time) ? '' : $oneCallback->completed_time;

			if(empty($messageStatus)) $oneInformation->statsdetails_error[] = 'Empty status received';
			if($messageStatus == 5){
				if(empty($completed_time)){
					$oneInformation->statsdetails_received_date = time();
				}else $oneInformation->statsdetails_received_date = $completed_time;
			}

			$smsId = empty($oneCallback->message_id) ? '' : $oneCallback->message_id;
			if(empty($smsId)) $oneInformation->statsdetails_error[] = 'Can t find the message_id';

			if(!isset($status[$messageStatus])){
				$oneInformation->statsdetails_error[] = 'Unknow status : '.$messageStatus;
				$oneInformation->statsdetails_status = -99;
			}else{
				$oneInformation->statsdetails_status = $messageStatus;
				$oneInformation->statsdetails_error[] = $status[$messageStatus];
			}

			$oneInformation->statsdetails_sms_id = $smsId;

			$deliveryInformations[] = $oneInformation;
		}

		return $deliveryInformations;
	}

	public function answer(){

		$callbackInformationsEncoded = JRequest::getVar("callbackInformations", '');
		$callbackInfoDecoded = json_decode($callbackInformationsEncoded);
		if(empty($callbackInfoDecoded)) return;

		$answerInformations = array();

		foreach($callbackInfoDecoded as $oneAnswerInformation){

			$oneInformation = new stdClass();
			$oneInformation->statsdetails_error = array();

			$oneInformation->answer_date = empty($oneAnswerInformation->received_time) ? time() : $oneAnswerInformation->received_time;

			$oneInformation->answer_body = empty($oneAnswerInformation->answer_body) ? '' : $oneAnswerInformation->answer_body;

			$answerSender = empty($oneAnswerInformation->answer_sender) ? '' : $oneAnswerInformation->answer_sender;
			$answerReceiver = empty($oneAnswerInformation->answer_receiver) ? '' : $oneAnswerInformation->answer_receiver;

			if(!empty($answerSender)) $oneInformation->answer_from = '+'.$answerSender;
			if(!empty($answerReceiver)) $oneInformation->answer_to = '+'.$answerReceiver;

			$oneInformation->answer_sms_id = empty($oneAnswerInformation->message_id) ? '' : $oneAnswerInformation->message_id;

			$answerInformations[] = $oneInformation;
		}

		return $answerInformations;
	}
}
