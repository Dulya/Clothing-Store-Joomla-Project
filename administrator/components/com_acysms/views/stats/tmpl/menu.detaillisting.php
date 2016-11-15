<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset>
	<div class="acysmsheader icon-48-stat" style="float: left;"><?php echo $this->selectedMessage->message_subject; ?></div>
	<div class="toolbar" id="toolbar" style="float: right;">
		<table><tr>
		<td><a href="<?php $app=JFactory::getApplication(); $link = $app->isAdmin() ? 'stats&task=diagram' : 'frontstats&task=diagram'; echo ACYSMS::completeLink($link.'&message_id='.JRequest::getInt('filter_message'),true); ?>" ><span class="icon-32-cancel" title="<?php echo JText::_('SMS_CANCEL',true); ?>"></span><?php echo JText::_('SMS_CANCEL'); ?></a></td>
		</tr></table>
	</div>
</fieldset>
