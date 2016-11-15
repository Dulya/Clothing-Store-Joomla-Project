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
	<form action="index.php?tmpl=component&amp;option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=file" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<div class="acysmsonelineblockoptions">
			<div class="acysmsblocktitle"><?php echo JText::_('ACY_FILE').' : '.@$this->escape($this->file->name); ?>
				<?php if(!empty($this->showLatest)){ ?>
					<button type="button" class="acysms_button" onclick="javascript:submitbutton('latest')"> <?php echo JText::_('SMS_LOAD_LATEST_LANGUAGE'); ?> </button>
				<?php } ?>
			</div>
			<textarea style="width:100%;" rows="18" name="content" id="translation"><?php echo $this->escape(@$this->file->content); ?></textarea>
		</div>

		<div class="acysmsblockoptions">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_CUSTOM_TRANS'); ?></span>
			<?php echo JText::_('SMS_CUSTOM_TRANS_DESC'); ?>
			<textarea style="width:100%;" rows="5" name="customcontent"><?php echo $this->escape(@$this->file->customcontent); ?></textarea>
		</div>

		<div class="clr"></div>
		<input type="hidden" name="code" value="<?php echo $this->escape($this->file->name); ?>"/>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="file"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
