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

class ACYSMSimportHelper{

	var $db;
	var $totalTry = 0;
	var $totalValid = 0;
	var $totalInserted = 0;
	var $allUserid = array();

	var $charsetConvert;
	var $overwriteExisting = false;
	var $removeSep = 0;
	var $importFirstLine = true;
	var $importBlocked = true;
	var $columns = array();
	var $dispresults = true;

	function importHelper(){
		ACYSMS::increasePerf();

		$this->db = JFactory::getDBO();

		JPluginHelper::importPlugin('acysms');
		$this->dispatcher = JDispatcher::getInstance();
	}

	function textarea(){
		$content = JRequest::getString('textareaentries');
		jimport('joomla.filesystem.file');

		$uploadPath = $this->getUploadDirectory();

		ACYSMS::createDir($uploadPath);

		$this->filename = 'importTextArea_'.time().'.csv';

		if(!JFile::write($uploadPath.DS.$this->filename, $content)){
			ACYSMS::enqueueMessage('Could not create the file '.$uploadPath.$this->filename, 'error');
			return false;
		}

		return true;
	}

	function file(){
		jimport('joomla.filesystem.file');

		$importFile = JRequest::getVar('importfile', array(), 'files', 'array');

		if(empty($importFile['name'])){
			ACYSMS::enqueueMessage(JText::_('SMS_BROWSE_FILE'), 'notice');
			return false;
		}

		$uploadPath = $this->getUploadDirectory();

		ACYSMS::createDir($uploadPath);

		if(!is_writable($uploadPath)){
			@chmod($uploadPath, '0755');
			if(!is_writable($uploadPath)){
				ACYSMS::enqueueMessage(JText::sprintf('SMS_WRITABLE_FOLDER', $uploadPath), 'notice');
			}
		}

		$this->filename = 'importFile_'.time().'.csv';

		if(!JFile::upload($importFile['tmp_name'], $uploadPath.$this->filename)){
			if(!move_uploaded_file($importFile['tmp_name'], $uploadPath.$this->filename)){
				ACYSMS::enqueueMessage(JText::sprintf('SMS_FAIL_UPLOAD', '<b><i>'.$importFile['tmp_name'].'</i></b>', '<b><i>'.$uploadPath.$this->filename.'</i></b>'), 'error');
				return false;
			}
		}

		return true;
	}


	function handleContent($filename){
		$success = true;
		$this->db = JFactory::getDBO();
		$encodingHelper = ACYSMS::get('helper.encoding');

		$uploadPath = $this->getUploadDirectory();

		$contentFile = file_get_contents($uploadPath.$filename);


		$contentFile = str_replace(array("\r\n", "\r"), "\n", $contentFile);

		$importLines = explode("\n", $contentFile);
		$i = 0;
		if(!$this->importFirstLine){
			$this->header = '';
			while(empty($this->header) && $i < 10){
				$this->header = trim($importLines[$i]);
				$i++;
			}
		}else{
			if(isset($importLines[0])) $this->header = $importLines[0];
		}
		$this->_autoDetectSeparator();

		$numberColumns = count($this->columns);
		$importUsers = array();

		while(isset($importLines[$i])){

			if(!empty($this->charsetConvert)){
				$importLines[$i] = $encodingHelper->change($importLines[$i], $this->charsetConvert, 'UTF-8');
			}

			$data = explode($this->separator, rtrim(trim($importLines[$i]), $this->separator));

			if(!empty($this->removeSep)){
				for($b = $numberColumns + $this->removeSep - 1; $b >= $numberColumns; $b--){
					if(isset($data[$b]) AND (strlen($data[$b]) == 0 || $data[$b] == ' ')){
						unset($data[$b]);
					}
				}
			}

			$i++;
			if(empty($importLines[$i - 1])) continue;

			$this->totalTry++;
			if(count($data) > $numberColumns){
				$copy = $data;
				foreach($copy as $oneelem => $oneval){
					if(!empty($oneval[0]) && $oneval[0] == '"' && $oneval[strlen($oneval) - 1] != '"' && isset($copy[$oneelem + 1]) && $copy[$oneelem + 1][strlen($copy[$oneelem + 1]) - 1] == '"'){
						$data[$oneelem] = $copy[$oneelem].$this->separator.$copy[$oneelem + 1];
						unset($data[$oneelem + 1]);
					}
				}
				$data = array_values($data);
			}

			if(count($data) < $numberColumns){
				for($a = count($data); $a < $numberColumns; $a++){
					$data[$a] = '';
				}
			}

			if(count($data) != $numberColumns){
				$success = false;
				static $errorcount = 0;
				if(empty($errorcount)){
					ACYSMS::enqueueMessage(JText::sprintf('SMS_IMPORT_ARGUMENTS', $numberColumns), 'error');
				}
				$errorcount++;
				if($errorcount < 20){
					ACYSMS::enqueueMessage(JText::sprintf('SMS_IMPORT_ERRORLINE', '<b><i>'.$importLines[$i - 1].'</i></b>'), 'notice');
				}elseif($errorcount == 20){
					ACYSMS::enqueueMessage('...', 'notice');
				}

				if($this->totalTry == 1) return false;
				continue;
			}

			$newUser = new stdClass();
			$phoneNumberField = false;
			$specifyFields = true;
			foreach($data as $num => $value){
				$field = $this->columns[$num];
				if($field != 'ignore' && !empty($field)) $newUser->$field = trim($value, '\'" ');
				if($field == 'user_phone_number') $phoneNumberField = true;
				if(!empty($field)) $specifyFields = false;
			}
			if(!$phoneNumberField && !$specifyFields){
				ACYSMS::enqueueMessage(JText::_('SMS_IMPORT_PHONE_NUMBER'), 'warning');
				return false;
			}
			if($specifyFields){
				ACYSMS::enqueueMessage(JText::_('SMS_PLEASE_SPECIFY_FIELDS'), 'warning');
				return false;
			}

			$newUser->user_phone_number = trim(str_replace(array(' ', "\t"), '', $newUser->user_phone_number));

			$phoneHelper = ACYSMS::get('helper.phone');

			if(!$phoneHelper->getValidNum($newUser->user_phone_number)){
				$success = false;
				static $errorcountfail = 0;
				$errorcountfail++;
				if($errorcountfail < 50){
					ACYSMS::enqueueMessage(JText::sprintf('SMS_NOT_VALID_PHONE_NUMBER', '<b><i>'.$newUser->user_phone_number.'</i></b>'.' | '.($i - 1).' : '.$importLines[$i - 1]), 'notice');
				}elseif($errorcountfail == 50){
					ACYSMS::enqueueMessage('...', 'notice');
				}
				continue;
			}else $newUser->user_phone_number = $phoneHelper->getValidNum($newUser->user_phone_number);

			unset($newUser->user_id);
			unset($newUser->user_joomid);

			$importUsers[] = $newUser;
			$this->totalValid++;

			if($this->totalValid % 50 == 0){
				$this->_insertUsers($importUsers);
				$importUsers = array();
			}
		}
		$this->_insertUsers($importUsers);

		$this->_subscribeUsers();

		if($this->dispresults){
			ACYSMS::enqueueMessage(JText::sprintf('SMS_IMPORT_REPORT', $this->totalTry, $this->totalInserted, $this->totalTry - $this->totalValid, $this->totalValid - $this->totalInserted));
		}

		$uploadPath = $this->getUploadDirectory();
		if($success) JFile::delete($uploadPath.$filename);
		return $success;
	}

	function _autoDetectSeparator(){
		$this->separator = ',';

		$this->header = str_replace("\xEF\xBB\xBF", "", $this->header);

		$listSeparators = array("\t", ';', ',');
		foreach($listSeparators as $sep){
			if(strpos($this->header, $sep) !== false){
				$this->separator = $sep;
				break;
			}
		}
	}

	function _insertUsers($users){
		if(empty($users)) return true;

		if($this->overwriteExisting){
			$phoneNumbertoload = array();
			$phoneHelper = ACYSMS::get('helper.phone');
			foreach($users as $a => $oneUser){
				$phoneNumbertoload[] = $this->db->Quote($oneUser->user_phone_number);
			}
			$this->db->setQuery('SELECT * FROM `#__acysms_user` WHERE `user_phone_number` IN ('.implode(',', $phoneNumbertoload).')');
			$user_id = $this->db->loadObjectList('user_phone_number');
			$dataoneuser = @array_keys(get_object_vars(reset($user_id)));
			foreach($users as $a => $oneUser){
				$users[$a]->user_id = (!empty($user_id[$oneUser->user_phone_number]->user_id)) ? $user_id[$oneUser->user_phone_number]->user_id : 'NULL';
				if(empty($dataoneuser)) continue;
				foreach($dataoneuser as $oneField){
					if(!isset($users[$a]->$oneField)) $users[$a]->$oneField = @$subids[$oneUser->user_phone_number]->$oneField;
				}
			}
			$this->totalInserted -= (count($user_id) * 2);
		}
		foreach($users as $a => $oneUser){
			$this->_checkData($users[$a]);
		}

		$columns = reset($users);

		$ignore = '';
		$onDuplicateValues = '';

		if(!$this->overwriteExisting) $ignore = 'IGNORE';

		$query = 'INSERT '.$ignore.' INTO '.ACYSMS::table('user').' (`'.implode('`,`', array_keys(get_object_vars($columns))).'`) VALUES (';
		foreach($users as $a => $oneUser){
			$value = array();
			foreach($oneUser as $map => $oneValue){
				$value[] = $this->db->Quote($oneValue);
			}
			$values[] = implode(',', $value);
		}

		$query .= implode('),(', $values).')';
		if($this->overwriteExisting){
			$oneUser = reset($users);
			foreach($oneUser as $oneColumn => $oneValue){
				if($oneColumn != 'user_phone_number') $onDuplicateValues .= $oneColumn.'= VALUES('.$oneColumn.'), ';
			}
			$query .= ' ON DUPLICATE KEY UPDATE '.' '.rtrim($onDuplicateValues, ", ");
		}
		$this->db->setQuery($query);
		$this->db->query();

		$this->totalInserted += $this->db->getAffectedRows();

		if($this->importBlocked){
			foreach($users as $oneUser){
				$this->db->setQuery('INSERT IGNORE INTO '.ACYSMS::table('phone').'(phone_number) VALUES ('.$this->db->Quote($oneUser->user_phone_number).')');
				$this->db->query();
			}
		}

		$this->db->setQuery('SELECT user_id FROM '.ACYSMS::table('user').' WHERE user_phone_number IN ('.implode(',', $values).')');

		$this->allUserid = array_merge($this->allUserid, acysms_loadResultArray($this->db));

		return true;
	}

	function _subscribeUsers(){

		if(empty($this->allUserid)) return true;

		$subdate = time();

		$groupClass = ACYSMS::get('class.group');

		if(empty($this->importUserInLists)){
			$groups = JRequest::getVar('importgroups', array());

			$newGroupName = JRequest::getString('creategroup');

			if(!empty($newGroupName)){
				$newGroup = new stdClass();
				$newGroup->group_name = $newGroupName;
				$newGroup->group_published = 1;

				$groupClass = ACYSMS::get('class.group');
				$groupid = $groupClass->save($newGroup);

				if(!empty($groupid)){
					$groups[$groupid] = 1;
				}
			}

			foreach($groups as $groupId => $val){
				if(!empty($val)){
					$nbsubscribed = 0;
					$groupId = (int)$groupId;
					$query = 'INSERT IGNORE INTO '.ACYSMS::table('groupuser').' (groupuser_group_id,groupuser_user_id,groupuser_subdate,groupuser_status) VALUES ';
					$b = 0;
					foreach($this->allUserid as $oneUserid){
						$b++;
						if($b > 200){
							$query = rtrim($query, ',');
							$this->db->setQuery($query);
							$this->db->query();
							$nbsubscribed += $this->db->getAffectedRows();
							$b = 0;
							$query = 'INSERT IGNORE INTO '.ACYSMS::table('groupuser').' (groupuser_group_id,groupuser_user_id,groupuser_subdate,groupuser_status) VALUES ';
						}
						$query .= '('.intval($groupId).','.intval($oneUserid).','.intval($subdate).',1),';
					}
					$query = rtrim($query, ',');
					$this->db->setQuery($query);
					$this->db->query();
					$nbsubscribed += $this->db->getAffectedRows();
					if(isset($this->subscribedUsers[$groupId])){
						$this->subscribedUsers[$groupId]->nbusers += $nbsubscribed;
					}else{
						$myList = $groupClass->get($groupId);
						$this->subscribedUsers[$groupId] = $myList;
						$this->subscribedUsers[$groupId]->nbusers = $nbsubscribed;
					}
				}
			}
		}
		return true;
	}

	function _checkData(&$user){
		if(!empty($user->user_birthdate)){
			$user->user_birthdate = date('Y-m-d', strtotime($user->user_birthdate));
		}
	}


	public function getUploadDirectory(){
		$config = ACYSMS::config();
		$uploadFolder = JPath::clean(html_entity_decode($config->get('uploadfolder')));
		$uploadFolder = trim($uploadFolder, DS.' ').DS;
		$uploadPath = JPath::clean(ACYSMS_ROOT.$uploadFolder);

		return $uploadPath;
	}

	public function get10Lines($filename){
		$encodingHelper = ACYSMS::get('helper.encoding');

		$uploadPath = $this->getUploadDirectory();

		$contentFile = file_get_contents($uploadPath.$filename);

		if(!$contentFile){
			ACYSMS::enqueueMessage(JText::sprintf('SMS_FAIL_OPEN', '<b><i>'.$uploadPath.$filename.'</i></b>'), 'error');
			return false;
		};

		if(empty($this->charsetConvert)){
			$this->charsetConvert = $encodingHelper->detectEncoding($contentFile);
		}


		$contentFile = str_replace(array("\r\n", "\r"), "\n", $contentFile);
		$importLines = explode("\n", $contentFile);
		$i = 0;
		$this->header = '';
		while(empty($this->header) && $i < 10){
			$this->header = trim($importLines[$i]);
			$i++;
		}

		$i = 0;
		$this->header = '';
		while(empty($this->header) && $i < 10){
			$this->header = trim($importLines[$i]);
			$i++;
		}

		$this->_autoDetectSeparator();

		$lines = array();
		for($i = 0; $i < 10; $i++){
			if(empty($importLines[$i])) continue;
			if(isset($importLines[$i])){
				if(!empty($this->charsetConvert)){
					$importLines[$i] = $encodingHelper->change($importLines[$i], $this->charsetConvert, 'UTF-8');
				}
				$lines[] = explode($this->separator, $importLines[$i]);
			}
		}

		return $lines;
	}
}
