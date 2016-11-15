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

class ACYSMSGateway_default_gateway{


	protected $availableConn = array();

	protected $connexions = array();

	public $password;
	public $username;
	public $smsid;
	public $waittosend = 0;

	public $errors = array();
	public $debug = false;
	public $name = 'Gateway Name';

	public $creditsUrl = '';
	public $type = "fsock";

	public $domain = 'domainusedtoconnect';
	public $port = 80;

	public $fullMessage;

	public $handleMMS = false;

	public $keepInQueue = false;

	public function open(){
		return true;
	}

	public function send($message, $phone){
		$idConnection = $this->openSend($message, $phone);
		if($idConnection === false) return false;
		return $this->closeSend($idConnection);
	}

	public function openSend($message, $phone){

		$fsockParameter = "GET / HTTP/1.1\r\n";
		$fsockParameter .= "Host: ".$this->domain."\r\n";
		$fsockParameter .= "Content-Type: application/x-www-form-urlencoded\r\n";

		return $this->sendRequest($fsockParameter);
	}


	public function closeSend($idConnection){
		$res = $this->readResult($idConnection);
		if($res === false) return false;
		return $this->interpretSendResult($res);
	}

	protected function interpretSendResult($res){
		return true;
	}

	protected function readResult($idConnection){
		$function = 'readResult_'.$this->type;
		return $this->$function($idConnection);
	}

	protected function readResult_fsock($idConnection){
		if(!is_resource($this->connexions[$idConnection])){
			$this->errors[] = 'No connexion available : '.$idConnection;
			return false;
		}

		$res = '';
		$length = 0;

		stream_set_timeout($this->connexions[$idConnection], 3);

		while(!feof($this->connexions[$idConnection])){

			$line = fread($this->connexions[$idConnection], 2048);
			$res .= $line;

			if(empty($length) && strpos(strtolower($line), 'content-length:')){
				preg_match('#content-length: *([0-9]*)#i', $line, $lengths);
				$contentlength = intval($lengths[1]);

				$length = strlen(substr($res, 0, strpos($res, "\r\n\r\n"))) + $contentlength + 4;
			}
			if($length > 0 && strlen($res) >= $length) break;

			if(empty($length) && strpos(strtolower($res), 'transfer-encoding: chunked')){
				if(strpos($res, "\r\n0\r\n")){
					$res = preg_replace('#\r\n[0-9a-fA-F]*\r\n#s', '', $res);
					break;
				}
			}
		}

		if(preg_match('#Connection *: *close#i', $res, $matches)){
			if(is_resource($this->connexions[$idConnection])) fclose($this->connexions[$idConnection]);
		}else{
			$this->availableConn[] = $this->connexions[$idConnection];
		}

		unset($this->connexions[$idConnection]);

		return $res;
	}

	public function __destruct(){
		$function = 'destruct_'.$this->type;
		if(method_exists($this, $function)) $this->$function();
	}

	protected function destruct_fsock(){
		foreach($this->availableConn as $conn => $connexion){
			if(is_resource($connexion)) fclose($this->availableConn[$conn]);
		}

		unset($this->availableConn);

		if(!empty($this->connexions)){
			$this->errors[] = 'There are still connexions in use? weird...';
			return false;
		}

		return true;
	}

	protected function sendRequest($parameters){
		$function = 'sendRequest_'.$this->type;
		return $this->$function($parameters);
	}

	private function getConn_fsock(){

		if(!empty($this->availableConn)){
			$connexion = array_shift($this->availableConn);
		}else{
			ob_start();
			$connexion = fsockopen($this->domain, $this->port, $errno, $errstr, 5);
			$warning = ob_get_clean();
			if(!$connexion){
				$this->errors[] = 'Error '.$errno.' => '.$errstr.' '.$warning;
				return false;
			}
		}

		$idConnection = count($this->connexions);
		$this->connexions[$idConnection] = $connexion;

		return $idConnection;
	}


	protected function sendRequest_fsock($parameters){

		$idConnection = $this->getConn_fsock();
		if($idConnection === false) return false;

		fwrite($this->connexions[$idConnection], $parameters);

		return $idConnection;
	}

	public function close(){
		return true;
	}

	public function displayConfig(){

		?>
		<table>
			<tr>
				<td>
					<label for="senderprofile_username"><?php echo JText::_('SMS_USERNAME'); ?></label>
				</td>
				<td>
					<input type="text" name="data[senderprofile][senderprofile_params][username]" id="senderprofile_username" class="inputbox" style="width:200px;" value="<?php echo htmlspecialchars(@$this->username, ENT_COMPAT, 'UTF-8'); ?>"/>
				</td>
			</tr>
			<tr>
				<td>
					<label for="senderprofile_password"><?php echo JText::_('SMS_PASSWORD') ?></label>
				</td>
				<td>
					<input name="data[senderprofile][senderprofile_params][password]" id="senderprofile_password" class="inputbox" type="password" style="width:200px;" value="<?php echo htmlspecialchars(@$this->password, ENT_COMPAT, 'UTF-8'); ?>"/>
				</td>
			</tr>
			<tr>
				<td colspan="2">
					<label for="senderprofile_from"><?php echo JText::sprintf('SMS_WAIT_TO_SEND', '<input type="text" name="data[senderprofile][senderprofile_params][waittosend]" id="senderprofile_waittosend" class="inputbox" style="width:200px;" value="'.@$this->waittosend.'" />'); ?></label>
				</td>
			</tr>
		</table>
		<?php
	}

	public function afterSaveConfig($senderprofile){
	}

	public function beforeSaveConfig(&$senderprofile){
	}


	public function getBalance(){
		return array();
	}

	protected function checkNum($phone){
		return preg_replace('#[^0-9]#', '', $phone);
	}

	protected function checkMessage($message){
		return html_entity_decode($message, ENT_NOQUOTES, 'UTF-8');
	}

	public function answer(){
		$apiAnswer = new stdClass();
		$apiAnswer->answer_date = 'date of the answer';
		$apiAnswer->answer_body = 'content of the answer';
		$apiAnswer->answer_from = 'from number (the client)';
		$apiAnswer->answer_to = 'to number (usually that\'s yours)';
		$apiAnswer->answer_sms_id = 'Id of the SMS you sent corresponding to this message';

		$apiAnswer->answer_message_id = 'SMS ID in AcySMS, the one you sent';
		$apiAnswer->answer_receiver_id = 'receiver ID in AcySMS, the user corresponding to the from number';
		$apiAnswer->answer_receiver_table = 'receiver table where the receiver ID is stored';

		return $apiAnswer;
	}


	public function closeRequest(){
	}

	protected function unicodeChar(&$string){
		$characters = '@£$¥èéùìòÇØøÅåΔ_ΦΓΛΩΠΨΣΘΞÆæßÉ !"#¤%&\'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà';
		$characters2 = '^{}\[~]|€';
		$characters3 = "\n\r";
		if(preg_match('#[^'.preg_quote($characters.$characters2, '#').$characters3.']#is', $string)){
			return true;
		}
		return false;
	}
}
