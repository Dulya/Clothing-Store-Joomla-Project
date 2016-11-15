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
	<span class="acysmsblocktitle"><?php echo JText::_('SMS_USER_INFORMATIONS'); ?></span>
	<table class="acysms_blocktable">
		<?php
		$this->fieldsClass->autofocus = true;
		foreach($this->coreFields as $fieldName => $oneExtraField){
			echo '<tr id="tr'.$fieldName.'"><td class="key">'.$this->fieldsClass->getFieldName($oneExtraField).'</td><td>'.$this->fieldsClass->display($oneExtraField, @$this->user->$fieldName, 'data[user]['.$fieldName.']').'</td></tr>';
			$this->fieldsClass->autofocus = false;
		}
		$app = JFactory::getApplication();
		if($app->isAdmin()){
			?>
			<tr>
				<td class="key">
					<label for="user_joomid">
						<?php echo JText::_('SMS_ASSIGNED_JOOMUSER'); ?>
					</label>
				</td>
				<td>
				<span id="joomuser">
				<?php
				if(!empty($this->joomUser->name) && !empty($this->joomUser->email)){
					echo $this->joomUser->name.' ('.$this->joomUser->email.')';
				}else  echo JText::_('SMS_SELECT_JOOMUSER');
				echo "</span>";
				echo ' <a class="modal"  href="index.php?option=com_acysms&tmpl=component&ctrl=user&task=choosejoomuser" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"><i class="smsicon-edit"></i></a>';
				?>
				</td>
			</tr>
			<?php
		} ?>
	</table>
</div>
