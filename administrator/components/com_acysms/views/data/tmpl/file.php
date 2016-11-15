<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table class="acysms_blocktable" cellspacing="1">
	<tr id="trfileupload">
		<td class="key" >
			<?php echo JText::_('SMS_UPLOAD_FILE'); ?>
		</td>
		<td>
			<input type="file" style="width:auto" name="importfile" />
			<?php echo '<br />'.(JText::sprintf('SMS_MAX_UPLOAD',(ACYSMS::bytes(ini_get('upload_max_filesize')) > ACYSMS::bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize'))); ?>
		</td>
	</tr>
</table>
