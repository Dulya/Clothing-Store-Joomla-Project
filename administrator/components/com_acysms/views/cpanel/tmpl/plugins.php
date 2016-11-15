<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="config_plugins">
	<?php $header = '<div class="acysmsblockoptions">
			<div class="acysmsblocktitle">'.JText::_('SMS_PLUGINS').'</div>
			<table class="acysms_table">
				<thead>
				<tr>
					<th class="title titlenum">
						'.JText::_('SMS_NUM').'
					</th>
					<th class="title">
						'.JText::_('SMS_NAME').'
					</th>
					<th class="title titletoggle">
						'.JText::_('SMS_ENABLED').'
					</th>
					<th class="title titleid">
						'.JText::_('SMS_ID').'
					</th>
				</tr>
				</thead>
				<tbody>';

	$footer = '</tbody>
			</table>
			</div>';


	echo $header;

	$k = 0;
	for($i = 0, $a = count($this->plugins); $i < $a; $i++){
		$row =& $this->plugins[$i];
		$publishedid = 'published-'.$row->id;
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td align="center">
				<?php echo $i + 1 ?>
			</td>
			<td>
				<a target="_blank" href="<?php echo version_compare(JVERSION, '1.6.0', '<') ? 'index.php?option=com_plugins&amp;view=plugin&amp;client=site&amp;task=edit&amp;cid[]=' : 'index.php?option=com_plugins&amp;task=plugin.edit&amp;extension_id=';
				echo $row->id ?>"><?php echo $row->name; ?></a>
			</td>
			<td align="center">
				<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleHelper->toggle($publishedid, $row->published, 'plugins') ?></span>
			</td>
			<td align="center">
				<?php echo $row->id; ?>
			</td>
		</tr>
		<?php
		if($i == ceil(count($this->plugins) / 2)){
			echo $footer;
			echo $header;
		}
		$k = 1 - $k;
	}
	echo $footer;
	?>
</div>
