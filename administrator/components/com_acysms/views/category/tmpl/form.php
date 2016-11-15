<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysms_content">
	<div id="iframedoc"></div>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=category" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<div class="acysmsblockoptions">
		<span class="acysmsblocktitle"><?php echo JText::_('SMS_CREATE_NEW_CATEGORY'); ?></span>
		<table class="acysms_blocktable">
			<tr>
				<td class="key">
					<label for="category_name">
						<?php echo JText::_('SMS_NAME'); ?>
					</label>
				</td>
				<td>
					<input type="text" name="data[category][category_name]" id="category_name" class="inputbox" style="width:200px" value="<?php echo $this->escape($this->category->category_name); ?>"/>
				</td>
			</tr>
		</table>
		</div>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->category->category_id); ?>"/>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value="save"/>
		<input type="hidden" name="ctrl" value="category"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
