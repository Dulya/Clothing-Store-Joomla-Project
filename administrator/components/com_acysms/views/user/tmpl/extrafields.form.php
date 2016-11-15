<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div class="<?php echo $divClass; ?>">
	<span class="acysmsblocktitle"><?php echo JText::_('SMS_EXTRA_INFORMATION'); ?></span>
	<table class="acysms_blocktable">
	<?php foreach($this->extraFields as $fieldName => $oneExtraField) {
		echo '<tr id="tr'.$fieldName.'"><td class="key">'.$this->fieldsClass->getFieldName($oneExtraField).'</td><td>'.$this->fieldsClass->display($oneExtraField,@$this->user->$fieldName,'data[user]['.$fieldName.']').'</td></tr>';
	}
	 ?>
	</table>
</div>
