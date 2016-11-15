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

class ACYSMSfieldsClass extends acysmsClass{

	var $tables = array('fields' => 'fields_fieldid');
	var $pkey = 'fields_fieldid';
	var $errors = array();
	var $prefix = 'fields_';
	var $suffix = '';
	var $excludeValue = array();
	var $formoption = '';
	var $autofocus;

	function getFields($area, &$user){

		if(empty($user)) $user = new stdClass();

		$where = array();
		$where[] = 'fields.`fields_published` = 1';
		if($area == 'backend'){
			$where[] = 'fields.`fields_backend` = 1';
			$where[] = 'fields.`fields_core` = 0';
		}elseif($area == 'backlisting'){
			$where[] = 'fields.`fields_listing` = 1';
		}elseif($area == 'frontcomp'){
			$where[] = 'fields.`fields_frontcomp` = 1';
			$where[] = 'fields.`fields_core` = 0';
		}elseif($area == 'frontlisting'){
			$where[] = 'fields.`fields_frontcomp` = 1';
		}elseif($area == 'core'){
			$where[] = 'fields.`fields_core` = 1';
		}elseif($area != 'module'){
			return false;
		}

		$this->database->setQuery('SELECT * FROM `#__acysms_fields` AS fields WHERE '.implode(' AND ', $where).' ORDER BY fields.`fields_ordering` ASC');
		$fields = $this->database->loadObjectList('fields_namekey');
		foreach($fields as $namekey => $field){
			if(!empty($fields[$namekey]->fields_options)){
				$fields[$namekey]->fields_options = unserialize($fields[$namekey]->fields_options);
			}
			if(!empty($field->fields_value)){
				$fields[$namekey]->fields_value = $this->explodeValues($fields[$namekey]->fields_value);
			}
			if($field->fields_type == 'file') $this->formoption = 'enctype="multipart/form-data"';
			if(empty($user->user_id)) $user->$namekey = $field->fields_default;
		}
		return $fields;
	}

	function getFieldName($field){
		$addLabels = array('textarea', 'text', 'dropdown', 'multipledropdown', 'file');
		return '<label '.(empty($this->labelClass) ? '' : ' class="'.$this->labelClass.'" ').(in_array($field->fields_type, $addLabels) ? ' for="'.$this->prefix.$field->fields_namekey.$this->suffix.'" ' : '').'>'.$this->trans($field->fields_fieldname).'</label>';
	}

	function trans($name){
		if(preg_match('#^[A-Z_]*$#', $name)){
			return JText::_($name);
		}
		return $name;
	}

	function listing($field, $value, $search = ''){
		$functionType = '_listing'.ucfirst($field->fields_type);
		return method_exists($this, $functionType) ? $this->$functionType($field, $value) : ACYSMS::dispSearch(nl2br($this->trans($value)), $search);
	}

	function explodeValues($values){
		$allValues = explode("\n", $values);
		$returnedValues = array();
		foreach($allValues as $id => $oneVal){
			$line = explode('::', trim($oneVal));
			$var = @$line[0];
			$val = @$line[1];
			if(strlen($val) < 1) continue;

			$obj = new stdClass();
			$obj->value = $val;
			for($i = 2; $i < count($line); $i++){
				$obj->{$line[$i]} = 1;
			}
			$returnedValues[$var] = $obj;
		}
		return $returnedValues;
	}


	function get($fieldid, $default = null){
		$column = is_numeric($fieldid) ? 'fields_fieldid' : 'fields_namekey';
		$query = 'SELECT fields.* FROM '.ACYSMS::table('fields').' AS fields WHERE fields.`'.$column.'` = '.$this->database->Quote($fieldid).' LIMIT 1';
		$this->database->setQuery($query);

		$field = $this->database->loadObject();
		if(!empty($field->fields_options)){
			$field->fields_options = unserialize($field->fields_options);
		}

		if(!empty($field->fields_value)){
			$field->fields_value = $this->explodeValues($field->fields_value);
		}

		return $field;
	}

	function chart($table, $field){
		static $a = false;
		$doc = JFactory::getDocument();
		if(!$a){
			$a = true;
			$doc->addScript(((empty($_SERVER['HTTPS']) OR strtolower($_SERVER['HTTPS']) != "on") ? 'http://' : 'https://')."www.google.com/jsapi");
		}
		$namekey = ACYSMS::secureField($field->fields_namekey);
		if(in_array($field->fields_type, array('checkbox', 'multipledropdown'))){
			if(empty($field->fields_value)) return;
			$results = array();
			foreach($field->fields_value as $valName => $oneValue){
				if(strlen($oneValue->value) < 1) continue;
				$this->database->setQuery('SELECT COUNT(user_id) AS total, '.$this->database->Quote($valName).' AS name FROM '.ACYSMS::table($table).' WHERE `'.$namekey.'` LIKE '.$this->database->Quote('%,'.$valName.',%').' OR `'.$namekey.'` LIKE '.$this->database->Quote($valName.',%').' OR `'.$namekey.'` LIKE '.$this->database->Quote('%,'.$valName).' OR `'.$namekey.'` = '.$this->database->Quote($valName));
				$myResult = $this->database->loadObject();
				if(!empty($myResult->total)) $results[] = $myResult;
			}
		}else{
			$this->database->setQuery('SELECT COUNT(`'.$namekey.'`) AS total,`'.$namekey.'` AS name FROM '.ACYSMS::table($table).' WHERE `'.$namekey.'` IS NOT NULL AND `'.$namekey.'` != \'\' GROUP BY `'.$namekey.'` ORDER BY total DESC LIMIT 20');
			$results = $this->database->loadObjectList();
		}

		?>
		<script language="JavaScript" type="text/javascript">
			function drawChart<?php echo $namekey; ?>(){
				var dataTable = new google.visualization.DataTable();
				dataTable.addColumn('string');
				dataTable.addColumn('number');
				dataTable.addRows(<?php echo count($results); ?>);

				<?php
				$export = '';
				foreach($results as $i => $oneResult){
				$name = isset($field->fields_value[$oneResult->name]) ? $this->trans($field->fields_value[$oneResult->name]->value) : $oneResult->name;
				$export .= "\n".$name.','.$oneResult->total;
				?>
				dataTable.setValue(<?php echo $i ?>, 0, '<?php echo addslashes($name).' ('.$oneResult->total.')'; ?>');
				dataTable.setValue(<?php echo $i ?>, 1, <?php echo intval($oneResult->total); ?>);
				<?php } ?>

				var vis = new google.visualization.<?php echo (in_array($field->fields_type, array('checkbox', 'multipledropdown'))) ? 'ColumnChart' : 'PieChart'; ?>(document.getElementById('fieldchart<?php echo $namekey;?>'));
				var options = {
					width: 600, height: 400, is3D: true, legendTextStyle: {color: '#333333'}, legend:<?php echo (in_array($field->fields_type, array('checkbox', 'multipledropdown'))) ? "'none'" : "'right'"; ?>
				};
				vis.draw(dataTable, options);
			}
			google.load("visualization", "1", {packages: ["corechart"]});
			google.setOnLoadCallback(drawChart<?php echo $namekey; ?>);

			function exportData<?php echo $namekey;?>(){
				if(document.getElementById('exporteddata<?php echo $namekey;?>').style.display == 'none'){
					document.getElementById('exporteddata<?php echo $namekey;?>').style.display = '';
				}else{
					document.getElementById('exporteddata<?php echo $namekey;?>').style.display = 'none';
				}
			}
		</script>

		<div style="width:600px;" class="acychart" id="fieldchart<?php echo $namekey; ?>"></div>
		<img style="position:relative;top:-45px;left:5px;cursor:pointer;" onclick="exportData<?php echo $namekey; ?>();" src="<?php echo ACYSMS_IMAGES.'smallexport.png'; ?>" alt="<?php echo JText::_('SMS_EXPORT', true) ?>" title="<?php echo JText::_('SMS_EXPORT', true) ?>"/>
		<textarea cols="50" rows="10" id="exporteddata<?php echo $namekey; ?>" style="display:none;position:absolute;margin-top:-150px;"><?php echo $export; ?></textarea>
		<?php
	}

	function saveForm(){

		$field = new stdClass();
		$field->fields_fieldid = ACYSMS::getCID('fields_fieldid');

		$formData = JRequest::getVar('data', array(), '', 'array');

		foreach($formData['fields'] as $column => $value){
			ACYSMS::secureField($column);
			if(is_array($value)){
				if(isset($value['day']) || isset($value['month']) || isset($value['year'])){
					$value = (empty($value['year']) ? '0000' : intval($value['year'])).'-'.(empty($value['month']) ? '00' : intval($value['month'])).'-'.(empty($value['day']) ? '00' : intval($value['day']));
				}else{
					$value = implode(',', $value);
				}
			}
			$field->$column = strip_tags($value);
		}

		$fieldValues = JRequest::getVar('fieldvalues', array(), '', 'array');
		if(!empty($fieldValues)){
			$field->fields_value = array();
			foreach($fieldValues['title'] as $i => $title){
				$title = trim(strip_tags($title));
				$value = trim(strip_tags($value));
				if(strlen($title) < 1 AND strlen($fieldValues['value'][$i]) < 1) continue;
				$value = strlen($fieldValues['value'][$i]) < 1 ? $title : $fieldValues['value'][$i];
				$extra = '';
				if(!empty($fieldValues['disabled'][$i])) $extra .= '::disabled';
				$field->fields_value[] = $title.'::'.$value.$extra;
			}
			$field->fields_value = implode("\n", $field->fields_value);
		}

		$fieldsOptions = JRequest::getVar('fieldsoptions', array(), '', 'array');
		foreach($fieldsOptions as $column => $value){
			$fieldsOptions[$column] = strip_tags($value);
		}
		if($field->fields_type == "customtext"){
			$fieldsOptions['customtext'] = JRequest::getVar('fieldcustomtext', '', '', 'string', JREQUEST_ALLOWHTML);
			if(empty($field->fields_fieldid)) $field->fields_namekey = 'customtext_'.date('z_G_i_s');
		}

		if(in_array($field->fields_type, array('birthday', 'date')) && !empty($fieldsOptions['format']) && strpos($fieldsOptions['format'], '%') === false){
			ACYSMS::enqueueMessage('Invalid Format: "'.$fieldsOptions['format'].'"<br /><br />Please use a combination of:<br /> - %d (which will be replaced by days)<br /> - %m (which will be replaced by months)<br /> - %Y (which will be replaced by years)', 'notice');
			$fieldsOptions['format'] = '';
		}

		$field->fields_options = serialize($fieldsOptions);

		if(empty($field->fields_fieldid) AND $field->fields_type != 'customtext'){
			if(empty($field->fields_namekey)) $field->fields_namekey = $field->fields_fieldname;
			$field->fields_namekey = substr(preg_replace('#[^a-z0-9_]#i', '', strtolower($field->fields_namekey)), 0, 50);
			if(empty($field->fields_namekey) || !preg_match('#^[a-z]#', $field->fields_namekey)){
				$this->errors[] = 'Please specify a valid Column Name';
				return false;
			}

			$columns = acysms_getColumns('#__acysms_user');

			if(isset($columns[$field->fields_namekey])){
				$this->errors[] = 'The field "'.$field->fields_namekey.'" already exists';
				return false;
			}

			if($field->fields_type == 'textarea'){
				$query = 'ALTER TABLE `#__acysms_user` ADD `'.$field->fields_namekey.'` TEXT NULL';
			}else{
				$query = 'ALTER TABLE `#__acysms_user` ADD `'.$field->fields_namekey.'` VARCHAR ( 250 ) NULL';
			}
			$this->database->setQuery($query);
			if(!$this->database->query()) return false;
		}
		$fieldid = $this->save($field);
		if(!$fieldid) return false;

		if(empty($field->field_ordering)){
			$helperOrder = ACYSMS::get('helper.order');
			$helperOrder->pkey = 'fields_fieldid';
			$helperOrder->table = 'fields';
			$helperOrder->orderingColumnName = 'fields_ordering';
			$helperOrder->reOrder();
		}
		JRequest::setVar('fields_fieldid', $fieldid);
		return true;
	}

	function delete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}

		foreach($elements as $key => $val){
			$elements[$key] = acysms_getEscaped($val);
		}
		if(empty($elements)) return false;

		JArrayHelper::toInteger($elements);

		$this->database->setQuery('SELECT `fields_namekey`,`fields_fieldid` FROM `#__acysms_fields`  WHERE `fields_core` = 0 AND `fields_fieldid` IN ('.implode(',', $elements).')');
		$fieldsToDelete = $this->database->loadObjectList('fields_fieldid');

		if(empty($fieldsToDelete)) return false;

		$namekeys = array();
		foreach($fieldsToDelete as $oneField){
			if(substr($oneField->fields_namekey, 0, 11) == 'customtext_') continue;
			$namekeys[] = $oneField->fields_namekey;
		}
		if(!empty($namekeys)){
			$this->database->setQuery('ALTER TABLE `#__acysms_user` DROP `'.implode('`, DROP `', $namekeys).'`');
			$this->database->query();
		}

		JArrayHelper::toInteger($fieldsToDelete);

		$this->database->setQuery('DELETE FROM `#__acysms_fields` WHERE `fields_fieldid` IN ('.implode(',', array_keys($fieldsToDelete)).')');
		$result = $this->database->query();
		if(!$result) return false;

		$affectedRows = $this->database->getAffectedRows();

		$helperOrder = ACYSMS::get('helper.order');
		$helperOrder->pkey = 'fields_fieldid';
		$helperOrder->table = 'fields';
		$helperOrder->orderingColumnName = 'fields_ordering';
		$helperOrder->reOrder();

		return $affectedRows;
	}

	private function _listingFile($field, $value){
		if(empty($value)) return;
		static $path = '';
		if(empty($path)){
			$config = ACYSMS::config();
			$path = trim(JPath::clean(html_entity_decode($config->get('uploadfolder'))), DS.' ').DS;
			$path = ACYSMS_LIVE.str_replace(DS, '/', $path.'userfiles/');
		}

		if(preg_match('#\.(jpg|gif|png|jpeg|ico|bmp)$#i', $value)){
			$fileName = '<img src="'.$path.$value.'" style="max-width:120px;max-height:80px;"/>';
		}else{
			$fileName = str_replace('_', ' ', substr($value, strpos($value, '_')));
		}
		return '<a href="'.$path.$value.'" target="_blank">'.$fileName.'</a>';
	}

	function _listingPhone($field, $value){
		return str_replace(array(','), ' ', $value);
	}


	private function _displayPhone($field, $value, $map, $inside){
		$countryType = ACYSMS::get('type.country');
		$countryType->field = $field;
		$countryType->inside = $inside;
		$countryType->idtag = $this->prefix.$field->fields_namekey.$this->suffix.'_country';
		$countryType->countrywidth = '80';
		$countryType->autofocus = $this->autofocus;

		if($inside){
			$placeholderValue = $this->trans($field->fields_fieldname);
			$placeholderValue = addslashes($placeholderValue);
			$this->excludeValue[$field->fields_namekey] = $placeholderValue;
			$countryType->placeholder = $placeholderValue;
		}
		return $countryType->displayPhone($value, $map);
	}

	private function _listingBirthday($field, $value){
		if(empty($value) || $value == '0000-00-00') return;
		if(empty($field->fields_options['format'])) $field->fields_options['format'] = "%d %m %Y";
		list($year, $month, $day) = explode('-', $value);
		return str_replace(array('%Y', '%m', '%d'), array($year, $month, $day), $field->fields_options['format']);
	}

	function display($field, $value, $map, $inside = false){
		if(empty($field->fields_type)) return;
		$functionType = '_display'.ucfirst($field->fields_type);
		return $this->$functionType($field, $value, $map, $inside);
	}

	private function _displayFile($field, $value, $map, $inside){
		$style = array();
		if(!empty($field->fields_options['size'])){
			$style[] = 'width:'.(is_numeric($field->fields_options['size']) ? ($field->fields_options['size'].'px') : $field->fields_options['size']);
		}
		$styleline = empty($style) ? '' : ' style="'.implode($style, ';').'"';

		$id = str_replace(' ', '_', $this->prefix.$field->fields_namekey.$this->suffix);
		$result = '<input type="file" id="'.$id.'" name="'.$map.'" '.$styleline.' />';
		if(empty($value)) return $result;
		$config = ACYSMS::config();
		$uploadFolder = trim(JPath::clean(html_entity_decode($config->get('uploadfolder'))), DS.' ').DS;
		$fileName = str_replace('_', ' ', substr($value, strpos($value, '_')));
		$result .= ' <span class="fileuploaded"><a href="'.ACYSMS_LIVE.str_replace(DS, '/', $uploadFolder).'userfiles/'.$value.'" target="_blank">'.$fileName.'</a></span>';
		return $result;
	}

	private function _displayText($field, $value, $map, $inside){
		$class = empty($field->fields_required) ? 'class="inputbox"' : 'class="inputbox required"';
		$style = array();
		if(!empty($field->fields_options['size'])){
			$style[] = 'width:'.(is_numeric($field->fields_options['size']) ? ($field->fields_options['size'].'px') : $field->fields_options['size']);
		}
		$styleline = empty($style) ? '' : ' style="'.implode($style, ';').'"';
		$value = 'value="'.htmlspecialchars($value, ENT_COMPAT, 'UTF-8').'"';
		$placeholder = '';
		if($inside){
			$placeholderValue = $this->trans($field->fields_fieldname);
			$placeholderValue = addslashes($placeholderValue);
			$this->excludeValue[$field->fields_namekey] = $placeholderValue;
			$placeholder = 'placeholder="'.htmlspecialchars($placeholderValue, ENT_COMPAT, 'UTF-8').'"';
		}
		$id = str_replace(' ', '_', $this->prefix.$field->fields_namekey.$this->suffix);

		$autofocus = '';
		if($this->autofocus) $autofocus = 'autofocus';

		return '<input id="'.$id.'" '.$styleline.' type="text" '.$class.' '.$autofocus.' name="'.$map.'" '.$value.' '.$placeholder.'/>';
	}

	private function _displayTextarea($field, $value, $map, $inside){
		$class = empty($field->fields_required) ? 'class="inputbox"' : 'class="inputbox required"';
		$placeholder = '';
		if($inside){
			if(strlen($value) < 1){
				$value = addslashes($this->trans($field->fields_fieldname));
				$this->excludeValue[$field->fields_namekey] = $value;
			}
			$placeholder = ' placeholder="'.$value.'"';
			$value = '';
		}
		$cols = empty($field->fields_options['cols']) ? '' : 'cols="'.intval($field->fields_options['cols']).'"';
		$rows = empty($field->fields_options['rows']) ? '' : 'rows="'.intval($field->fields_options['rows']).'"';
		return '<textarea '.$class.' id="'.$this->prefix.$field->fields_namekey.$this->suffix.'" name="'.$map.'" '.$cols.' '.$rows.$placeholder.'>'.$value.'</textarea>';
	}

	private function _listingTextarea($field, $value){
		if(strlen($value) > 80){
			return substr($value, 0, 77).'...';
		}
		return $value;
	}

	private function _listingSelectedvals(&$field, $values){
		$return = '';
		foreach($values as $value){
			if(isset($field->fields_value[$value]->value)){
				$return .= ', '.$this->trans($field->fields_value[$value]->value);
			}else $return .= ', '.$value;
		}

		return trim($return, ', ');
	}

	private function _listingSingledropdown($field, $value){
		return $this->_listingSelectedvals($field, array($value));
	}

	private function _listingMultipledropdown($field, $value){
		return $this->_listingSelectedvals($field, explode(',', $value));
	}

	private function _listingRadio($field, $value){
		return $this->_listingSelectedvals($field, array($value));
	}

	private function _listingCheckbox($field, $value){
		return $this->_listingSelectedvals($field, explode(',', $value));
	}

	private function _displayCustomtext($field, $value, $map, $inside){
		return $this->trans(@$field->fields_options['customtext']);
	}

	private function _displayRadio($field, $value, $map, $inside){
		return $this->_displayRadioCheck($field, $value, $map, 'radio', $inside);
	}

	private function _displaySingledropdown($field, $value, $map, $inside){
		return $this->_displayDropdown($field, $value, $map, 'single', $inside);
	}

	private function _displayMultipledropdown($field, $value, $map, $inside){
		$value = explode(',', $value);
		return $this->_displayDropdown($field, $value, $map, 'multiple', $inside);
	}

	private function _displayDropdown($field, $value, $map, $type, $inside){
		$class = empty($field->fields_required) ? '' : 'class="required"';
		$string = '';
		$style = array();
		if($type == "multiple"){
			$string .= '<input type="hidden" name="'.$map.'" value=" "/>'."\n";
			$map .= '[]';
			$arg = 'multiple="multiple"';
			if(!empty($field->fields_options['size'])) $arg .= ' size="'.intval($field->fields_fields_options['size']).'"';
		}else{
			$arg = 'size="1"';
			if(!empty($field->fields_options['size'])){
				$style[] = 'width:'.(is_numeric($field->fields_options['size']) ? ($field->fields_options['size'].'px') : $field->fields_options['size']);
			}
		}
		$styleline = empty($style) ? '' : ' style="'.implode($style, ';').'"';
		$string .= '<select '.$class.' id="'.$this->prefix.$field->fields_namekey.$this->suffix.'" name="'.$map.'" '.$arg.$styleline.' >'."\n";
		if(empty($field->fields_value)) return $string;
		foreach($field->fields_value as $oneValue => $myValue){
			$selected = ((is_string($value) AND $oneValue == $value) OR is_array($value) AND in_array($oneValue, $value)) ? 'selected="selected"' : '';
			$id = str_replace(' ', '_', $this->prefix.$field->fields_namekey.$this->suffix.'_'.$oneValue);
			$disabled = empty($myValue->disabled) ? '' : 'disabled="disabled"';
			$string .= '<option value="'.$oneValue.'" id="'.$id.'" '.$disabled.' '.$selected.' >'.$this->trans($myValue->value).'</option>'."\n";
		}
		$string .= '</select>';
		return $string;
	}

	private function _displayRadioCheck($field, $value, $map, $type, $inside){
		$string = '';
		if($inside) $string = $this->trans($field->fields_fieldname).' ';
		if($type == 'checkbox'){
			$string .= '<input type="hidden" name="'.$map.'" value=" " />'."\n";
			$map .= '[]';
		}
		if(empty($field->fields_value)) return $string;
		foreach($field->fields_value as $oneValue => $myValue){
			$checked = ((is_string($value) AND $oneValue == $value) OR is_array($value) AND in_array($oneValue, $value)) ? 'checked="checked"' : '';
			$id = str_replace(' ', '_', $this->prefix.$field->fields_namekey.$this->suffix.'_'.$oneValue);
			$disabled = empty($myValue->disabled) ? '' : 'disabled="disabled"';
			$string .= '<span id="span_'.$id.'"><input type="'.$type.'" name="'.$map.'" value="'.htmlspecialchars($oneValue, ENT_COMPAT, 'UTF-8').'" id="'.$id.'" '.$disabled.' '.$checked.' /><label for="'.$id.'">'.$this->trans($myValue->value).'</label></span>'."\n";
		}
		return $string;
	}

	private function _displayDate($field, $value, $map, $inside){
		if(empty($field->fields_options['format'])) $field->fields_options['format'] = "%Y-%m-%d";
		$style = array();
		if(!empty($field->fields_options['size'])){
			$style[] = 'width:'.(is_numeric($field->fields_options['size']) ? ($field->fields_options['size'].'px') : $field->fields_options['size']);
		}
		$styleForCalendar = array();
		if(!empty($style)) $styleForCalendar['style'] = implode($style, ';');

		if($inside AND strlen($value) < 1){
			$value = addslashes($this->trans($field->fields_fieldname));
			$this->excludeValue[$field->fields_namekey] = $value;
			$styleForCalendar['onfocus'] = 'if(this.value == \''.$value.'\') this.value = \'\'';
			$styleForCalendar['onblur'] = 'if(this.value==\'\') this.value=\''.$value.'\';';
		}
		if($inside) $styleForCalendar['placeholder'] = $field->fields_fieldname;

		if(!empty($field->required)) $styleForCalendar['class'] = 'required';

		if($value == '{now}' AND $map != 'data[fields][fields_default]') $value = strftime($field->fields_options['format'], time());
		return JHTML::_('calendar', $value, $map, $this->prefix.$field->fields_namekey.$this->suffix, $field->fields_options['format'], $styleForCalendar);
	}

	private function _displayBirthday($field, $value, $map, $inside){
		$class = empty($field->fields_required) ? '' : 'class="required"';
		if(empty($field->fields_options['format'])) $field->fields_options['format'] = "%d %m %Y";
		$vals = explode('-', $value);
		$days = array();
		$days[] = JHTML::_('select.option', '', JText::_('SMS_DAY'));
		for($i = 1; $i < 32; $i++) $days[] = JHTML::_('select.option', (strlen($i) == 1) ? '0'.$i : $i, $i);
		$years = array();
		$years[] = JHTML::_('select.option', '', JText::_('SMS_YEAR'));
		for($i = 1901; $i < date('Y') + 10; $i++) $years[] = JHTML::_('select.option', $i, $i);
		$months = array();
		$months[] = JHTML::_('select.option', '', JText::_('SMS_MONTH'));
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


		$dayField = JHTML::_('select.genericlist', $days, $map.'[day]', $class.' style="max-width:80px;"', 'value', 'text', @$vals[2], $this->prefix.$field->fields_namekey.$this->suffix.'_day');
		$monthField = JHTML::_('select.genericlist', $months, $map.'[month]', $class.' style="max-width:130px;"', 'value', 'text', @$vals[1], $this->prefix.$field->fields_namekey.$this->suffix.'_month');
		$yearField = JHTML::_('select.genericlist', $years, $map.'[year]', $class.' style="max-width:100px;"', 'value', 'text', intval(@$vals[0]), $this->prefix.$field->fields_namekey.$this->suffix.'_year');
		return '<div class="smsbirthday_area">'.str_replace(array('%d', '%m', '%Y'), array($dayField, $monthField, $yearField), $field->fields_options['format']).'</div>';
	}

	private function _displayCheckbox($field, $value, $map, $inside){
		$value = explode(',', $value);
		return $this->_displayRadioCheck($field, $value, $map, 'checkbox', $inside);
	}
}
