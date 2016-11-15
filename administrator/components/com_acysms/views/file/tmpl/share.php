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

		<div class="acysmsblockoptions">
			<?php ACYSMS::display(JText::_('SMS_SHARE_CONFIRMATION_1').'<br />'.JText::_('SMS_SHARE_CONFIRMATION_2').'<br />'.JText::_('SMS_SHARE_CONFIRMATION_3'), 'info'); ?><br/>
			<textarea style="width:700px;" rows="8" name="msgbody">Hi Acyba team,
Here is a new version of the language file, I translated few more strings...</textarea>
		</div>
		<div class="clr"></div>

		<input type="hidden" name="code" value="<?php echo $this->file->name; ?>"/>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="file"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
