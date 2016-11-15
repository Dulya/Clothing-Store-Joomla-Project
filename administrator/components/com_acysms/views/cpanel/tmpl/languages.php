<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="config_languages">
	<div class="acysmsonelineblockoptions">
		<div class="acysmsblocktitle"><?php echo JText::_('SMS_LANGUAGES'); ?></div>
		<table class="acysms_table">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_('SMS_NUM'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_EDIT'); ?>
				</th>
				<th class="title">
					<?php echo JText::_('SMS_NAME'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_ID'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$k = 0;
			for($i = 0, $a = count($this->languages); $i < $a; $i++){
				$row =& $this->languages[$i];
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
						<?php echo $i + 1; ?>
					</td>
					<td align="center">
						<?php echo $row->edit; ?>
					</td>
					<td align="center">
						<?php echo $row->name; ?>
					</td>
					<td align="center">
						<?php echo $row->language; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
	</div>
</div>
