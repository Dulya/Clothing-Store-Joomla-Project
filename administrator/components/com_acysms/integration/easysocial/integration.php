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

class ACYSMSIntegration_easysocial_integration extends ACYSMSIntegration_default_integration{


	var $tableName = '#__users';

	var $componentName = 'easysocial';

	var $displayedName = 'EasySocial';

	var $primaryField = 'user_id';

	var $nameField = 'name';

	var $emailField = 'email';

	var $joomidField = 'user_id';

	var $editUserURL = 'index.php?option=com_easysocial&view=users&layout=form&id=';

	var $addUserURL = 'index.php?option=com_easysocial&view=users';

	var $tableAlias = 'easysocialusers';

	var $useJoomlaName = 1;

	var $submoduleId;

	var $integrationType = 'communityIntegration';

	public function __construct($moduleid = ''){
		$this->submoduleId = $moduleid;
		if(!empty($moduleid)) $this->componentName .= '-'.$moduleid;
	}


	public function getPhoneField(){
		$db = JFactory::getDBO();
		$lang = JFactory::getLanguage();
		$return = $lang->load('com_easysocial', JPATH_ADMINISTRATOR);
		$result = array();

		$query = 'SELECT  socialprofiles.id AS socialprofilesid, socialfields.title AS "name", socialfields.id AS "column", socialprofiles.id AS "idprofile", socialprofiles.title AS "profile"
				FROM #__social_fields AS socialfields
				JOIN #__social_fields_steps AS socialfieldssteps ON socialfields.step_id = socialfieldssteps.id
				JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
				WHERE socialfields.unique_key LIKE "TEXTBOX%"
				AND socialfields.title <> "COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_COLLEGE_OR_UNIVERSITY"
				AND socialfields.title <> "COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_GRADUATION_YEAR"
				';

		$db->setQuery($query);
		$phonefieldList = $db->loadObjectList();

		$profilesList = array();
		foreach($phonefieldList as $onePhoneField){
			if(!in_array($onePhoneField, $profilesList)){
				$row = new stdClass();
				$row->profile = $onePhoneField->profile;
				$row->idprofile = $onePhoneField->idprofile;
				$profilesList[] = $row;
			}
		}

		foreach($profilesList as $oneProfile){
			$fieldList = array();
			foreach($phonefieldList as $onePhoneField){
				if($onePhoneField->profile == $oneProfile->profile) $fieldList[] = JHTML::_('select.option', JText::_($onePhoneField->column), JText::_($onePhoneField->name), 'column', 'name');
			}
			$result[$this->componentName.' '.$oneProfile->profile] = new stdClass();
			$result[$this->componentName.' '.$oneProfile->profile]->displayedName = $this->displayedName.' ('.$oneProfile->profile.')';
			$result[$this->componentName.' '.$oneProfile->profile]->componentName = $this->componentName.'-'.$oneProfile->idprofile;
			$result[$this->componentName.' '.$oneProfile->profile]->fields = $fieldList;
		}
		return $result;
	}

	public function getQueryUsers($search, $order, $filters){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();
		$searchFields = array('joomusers.name', 'joomusers.email', 'easysocialusers.user_id', 'fieldsvalue.data');
		$result = new stdClass();

		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}
		$query = 'SELECT easysocialusers.*, easysocialusers.user_id AS receiver_id, joomusers.name AS receiver_name, joomusers.email AS receiver_email, fieldsvalue.data AS receiver_phone
				FROM #__social_users as easysocialusers
				JOIN #__users as joomusers ON easysocialusers.user_id = joomusers.id
				LEFT JOIN #__social_fields_data fieldsvalue ON fieldsvalue.uid = easysocialusers.user_id
				JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id
				JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id
				JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
				WHERE fieldsvalue.field_id = '.intval($config->get('easysocial-'.($this->submoduleId).'_field'));

		if(!empty($filters)){
			$query .= ' AND ('.implode(') AND (', $filters).')';
		}
		if(!empty($order)){
			$query .= ' ORDER BY '.$order->value.' '.$order->dir;
		}

		$queryCount = 'SELECT easysocialusers.*, easysocialusers.user_id as receiver_id, joomusers.name as receiver_name, joomusers.email as receiver_email, fieldsvalue.data as receiver_phone
				FROM #__social_users as easysocialusers
				JOIN #__users as joomusers ON easysocialusers.user_id = joomusers.id
				LEFT JOIN #__social_fields_data fieldsvalue ON fieldsvalue.uid = easysocialusers.user_id
				JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id
				JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id
				JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id';
		if(!empty($filters)){
			$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
		}
		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;

		return $result;
	}

	function getStatDetailsQuery($queryConditions, $search){
		$db = JFactory::getDBO();
		$result = new stdClass();
		$config = ACYSMS::config();

		$queryConditions->where[] = 'statsdetails_receiver_table = "'.$this->componentName.'"';
		$queryConditions->where[] = 'socialprofiles.id = '.intval($this->submoduleId);

		$searchFields = array('joomusers.name', 'joomusers.email', 'fieldsvalue.data', 'stats.statsdetails_message_id', 'stats.statsdetails_status', 'message.message_subject');
		if(!empty($search)){
			$searchVal = '\'%'.acysms_getEscaped($search, true).'%\'';
			$queryConditions->where[] = implode(" LIKE $searchVal OR ", $searchFields)." LIKE $searchVal";
		}

		$query = 'SELECT stats.*, message.message_id as message_id, stats.statsdetails_sentdate as message_sentdate, message.message_subject as message_subject, joomusers.name as receiver_name, joomusers.email as receiver_email, easysocialusers.user_id as receiver_id, fieldsvalue.data as receiver_phone, stats.statsdetails_status as message_status
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__social_users as easysocialusers ON stats.statsdetails_receiver_id = easysocialusers.user_id
					LEFT JOIN #__social_fields_data fieldsvalue ON fieldsvalue.uid = easysocialusers.user_id
					JOIN #__users as joomusers ON easysocialusers.user_id = joomusers.id
					JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id
					JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id
					JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
					WHERE fieldsvalue.field_id = '.intval($config->get('easysocial-'.($this->submoduleId).'_field'));

		$query .= ' AND ('.implode(') AND (', $queryConditions->where).')';
		if(!empty($queryConditions->order)){
			$query .= ' ORDER BY '.$queryConditions->order->value.' '.$queryConditions->order->dir;
		}
		$query .= ' LIMIT '.$queryConditions->offset;
		if(!empty($queryConditions->limit)) $query .= ', '.$queryConditions->limit;


		$queryCount = 'SELECT COUNT(stats.statsdetails_message_id)
					FROM '.ACYSMS::table('statsdetails').' AS stats
					LEFT JOIN '.ACYSMS::table('message').' AS message ON stats.statsdetails_message_id = message.message_id
					LEFT JOIN #__social_users as easysocialusers ON stats.statsdetails_receiver_id = easysocialusers.user_id
					LEFT JOIN #__social_fields_data fieldsvalue ON fieldsvalue.uid = easysocialusers.user_id
					JOIN #__users as joomusers ON easysocialusers.user_id = joomusers.id
					JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id
					JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id
					JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
					WHERE fieldsvalue.field_id = '.intval($config->get('easysocial-'.($this->submoduleId).'_field'));

		$queryCount .= ' AND ('.implode(') AND (', $queryConditions->where).')';

		$db->setQuery($queryCount);
		$result->count = $db->loadResult();
		$result->query = $query;
		return $result;
	}

	function initQuery(&$acyquery){
		$config = ACYSMS::config();
		$acyquery->from = '#__social_users as easysocialusers ';
		$acyquery->join['joomusers'] = 'JOIN #__users as joomusers ON easysocialusers.user_id = joomusers.id';
		$acyquery->join['socialfieldsvalue'] = 'JOIN #__social_fields_data AS socialfieldsvalue ON socialfieldsvalue.uid = easysocialusers.user_id';
		$acyquery->join['fields'] = 'JOIN #__social_fields AS fields ON socialfieldsvalue.field_id = fields.id';
		$acyquery->join['socialfieldssteps'] = 'JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id';
		$acyquery->join['socialprofiles'] = 'JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id';
		$acyquery->where[] = 'socialfieldsvalue.field_id = '.intval($config->get('easysocial-'.($this->submoduleId).'_field')).' AND joomusers.block=0 AND CHAR_LENGTH(socialfieldsvalue.data) > 3';
		return $acyquery;
	}

	function isPresent(){
		if(file_exists(rtrim(JPATH_ADMINISTRATOR, DS).DS.'components'.DS.'com_easysocial')) return true;
		return false;
	}


	function addUsersInformations(&$queueMessage){

		$config = ACYSMS::config();
		$db = JFactory::getDBO();

		$userId = array();
		$juserid = array();
		$acyUser = array();

		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			$userId[$oneMessage->queue_receiver_id] = intval($oneMessage->queue_receiver_id);
		}

		JArrayHelper::toInteger($userId);

		$queryPhone = 'SELECT fieldsvalue.data AS receiver_phone, fieldsvalue.uid FROM #__social_fields_data fieldsvalue
						JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id
						JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id
						JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
						WHERE field_id = '.intval($config->get('easysocial-'.($this->submoduleId).'_field')).'
						AND fieldsvalue.uid IN ('.implode(',', $userId).')';
		$db->setQuery($queryPhone);
		$phoneNumbers = $db->loadObjectList('uid');

		JArrayHelper::toInteger($userId);

		$query = 'SELECT easysocialusers.*, easysocialusers.user_id as receiver_id, joomusers.name as receiver_name
			FROM #__social_users as easysocialusers
			JOIN #__users as joomusers ON easysocialusers.user_id = joomusers.id
			AND joomusers.id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$easySocialUsers = $db->loadObjectList('receiver_id');

		if(empty($easySocialUsers)) return false;

		JArrayHelper::toInteger($userId);

		$query = 'SELECT joomusers.*
			FROM #__social_users as easysocialusers
			JOIN #__users as joomusers ON easysocialusers.user_id = joomusers.id
			AND joomusers.id IN ('.implode(',', $userId).')';
		$db->setQuery($query);
		$joomuserArray = $db->loadObjectList('id');


		foreach($queueMessage as $messageID => $oneMessage){
			if(empty($oneMessage->queue_receiver_id)) continue;
			if(empty($easySocialUsers[$oneMessage->queue_receiver_id])) continue;

			$queueMessage[$messageID]->easysocial = $easySocialUsers[$oneMessage->queue_receiver_id];
			$queueMessage[$messageID]->receiver_phone = $phoneNumbers[$oneMessage->queue_receiver_id]->receiver_phone;
			$queueMessage[$messageID]->receiver_name = $easySocialUsers[$oneMessage->queue_receiver_id]->receiver_name;
			$queueMessage[$messageID]->receiver_id = $oneMessage->queue_receiver_id;

			if(!empty($queueMessage[$messageID]->easysocial->userid) && !empty($joomuserArray[$queueMessage[$messageID]->easysocial->userid])){
				$queueMessage[$messageID]->joomla = $joomuserArray[$queueMessage[$messageID]->easysocial->userid];
				$queueMessage[$messageID]->receiver_email = $queueMessage[$messageID]->joomla->email;
			}
		}
	}

	function getQueueListingQuery($filters, $order){
		$result = new stdClass();
		$config = ACYSMS::config();

		$filters[] = 'socialprofiles.id = '.intval($this->submoduleId);

		$app = JFactory::getApplication();
		if(!$app->isAdmin()){
			$my = JFactory::getUser();
			$filters[] = ' message.message_userid = '.intval($my->id);
		}

		$query = 'SELECT queue.*, queue.queue_priority as queue_priority, queue.queue_try as queue_try, queue.queue_senddate as queue_senddate, message.message_subject as message_subject, joomusers.'.$this->nameField.' as receiver_name, fieldsvalue.data as receiver_phone, easysocialusers.user_id as receiver_id';
		$query .= ' FROM '.ACYSMS::table('queue').' AS queue';
		$query .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';
		$query .= ' JOIN #__social_users as easysocialusers  ON easysocialusers.user_id = queue.queue_receiver_id';
		$query .= ' JOIN #__users AS joomusers ON easysocialusers.user_id = joomusers.id';
		$query .= ' JOIN #__social_fields_data fieldsvalue ON fieldsvalue.uid = easysocialusers.user_id ';
		$query .= '	JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id';
		$query .= '	JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id';
		$query .= '	JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id';
		$query .= ' WHERE fieldsvalue.field_id = '.intval($config->get('easysocial-'.($this->submoduleId).'_field'));

		if(!empty($filters)) $query .= ' AND  ('.implode(') AND (', $filters).')';
		$query .= ' ORDER BY '.$order->value.' '.$order->dir.', queue.`queue_receiver_id` ASC';

		$queryCount = 'SELECT COUNT(queue.queue_message_id), joomusers.'.$this->nameField.' as receiver_name';
		$queryCount .= ' FROM '.ACYSMS::table('queue').' AS queue';
		$queryCount .= ' JOIN '.ACYSMS::table('message').' AS message ON queue.queue_message_id = message.message_id';
		$queryCount .= ' JOIN #__social_users as easysocialusers  ON easysocialusers.user_id = queue.queue_receiver_id';
		$queryCount .= ' JOIN #__users AS joomusers ON easysocialusers.user_id = joomusers.id';
		$queryCount .= ' JOIN #__social_fields_data fieldsvalue ON fieldsvalue.uid = easysocialusers.user_id ';
		$queryCount .= ' JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id';
		$queryCount .= ' JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id';
		$queryCount .= ' JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id';
		$queryCount .= ' WHERE fieldsvalue.field_id = '.intval($config->get('easysocial-'.($this->submoduleId).'_field'));

		if(!empty($filters)) $queryCount .= ' AND ('.implode(') AND (', $filters).')';
		$queryCount .= ' ORDER BY '.$order->value.' '.$order->dir.', queue.`queue_receiver_id` ASC';

		$result->query = $query;
		$result->queryCount = $queryCount;

		return $result;
	}

	function getInformationsByPhoneNumber($phoneNumber){
		$config = ACYSMS::config();
		$phoneHelper = ACYSMS::get('helper.phone');
		$db = JFactory::getDBO();

		$integrationPhoneField = $config->get($this->componentName.'_field');
		$countryCode = $phoneHelper->getCountryCode($phoneNumber);

		$phoneNumberToSearch = str_replace('+'.$countryCode, '', $phoneNumber);

		if(!empty($integrationPhoneField)){
			$db->setQuery('SELECT easysocialusers.*, easysocialusers.user_id as receiver_id, fieldsvalue.data as receiver_phone
				FROM #__social_users as easysocialusers
				JOIN #__social_fields_data fieldsvalue ON fieldsvalue.uid = easysocialusers.user_id
				JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id
				JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id
				JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
				WHERE fieldsvalue.field_id = '.$db->Quote($integrationPhoneField).'
				AND socialprofiles.id = '.intval($this->submoduleId).'
				AND fieldsvalue.data = '.$db->Quote($phoneNumberToSearch).' OR fieldsvalue.data LIKE '.$db->Quote('%'.$phoneNumberToSearch));
			$informations = $db->loadObject();
			return $informations;
		}
	}


	function getNames(){
		$db = JFactory::getDBO();
		$config = ACYSMS::config();

		$query = 'SELECT DISTINCT socialprofiles.id AS "idprofile", socialprofiles.title AS "profile"
				FROM #__social_fields AS socialfields
				JOIN #__social_fields_steps AS socialfieldssteps ON socialfields.step_id = socialfieldssteps.id
				JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
				WHERE socialfields.unique_key LIKE "TEXTBOX%"
				AND socialfields.title <> "COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_COLLEGE_OR_UNIVERSITY"
				AND socialfields.title <> "COM_EASYSOCIAL_FIELDS_PROFILE_DEFAULT_EDUCATION_GRADUATION_YEAR"';

		$db->setQuery($query);
		$profileList = $db->loadObjectList();
		$result = array();

		foreach($profileList as $oneProfile){
			$row = new stdClass();
			$row->text = $this->displayedName.' ('.$oneProfile->profile.')';
			$row->value = $this->componentName.'-'.$oneProfile->idprofile;

			$phoneFieldSubIntegration = $config->get($this->componentName.'-'.$oneProfile->idprofile.'_field');
			if(empty($phoneFieldSubIntegration)) continue;

			$result[] = $row;
		}
		return $result;
	}

	public function getReceiversByName($name, $isFront, $receiverId){
		if(empty($name)) return;

		$config = ACYSMS::config();
		$db = JFactory::getDBO();
		$query = 'SELECT joomusers.'.$this->nameField.' AS name, easysocialusers.'.$this->primaryField.' AS receiverId
				FROM #__social_users as easysocialusers
				JOIN #__users as joomusers ON easysocialusers.user_id = joomusers.id
				LEFT JOIN #__social_fields_data fieldsvalue ON fieldsvalue.uid = easysocialusers.user_id
				JOIN #__social_fields AS fields ON fieldsvalue.field_id = fields.id
				JOIN #__social_fields_steps AS socialfieldssteps ON fields.step_id = socialfieldssteps.id
				JOIN #__social_profiles AS socialprofiles ON socialfieldssteps.uid = socialprofiles.id
				WHERE '.$this->nameField.' LIKE '.$db->Quote('%'.$name.'%').'
				AND fieldsvalue.field_id = '.intval($config->get('easysocial-'.($this->submoduleId).'_field')).'
				LIMIT 10';

		$db->setQuery($query);
		return $db->loadObjectList();
	}
}
