<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(!empty($this->users)) { ?>
<br style="font-size:1px;"/>
<div id="dash_users">

	<h1 class="acysms_graphtitle"> <?php echo JText::_('SMS_LATEST_USERS') ?> </h1>
	<table class="acysms_table" cellpadding="1">
		<thead>
		<tr>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('NAME'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('PHONE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('EMAIL'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		foreach($this->users as $oneUser){
			$row =& $oneUser;
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center" style="text-align:center">
					<?php echo $this->escape($row->receiver_id); ?>
				</td>
				<td align="center" style="text-align:center">
					<a href="<?php echo $this->integration->editUserURL.$row->receiver_id ?>"><?php echo $this->escape($row->receiver_name); ?></a>
				</td>
				<td align="center" style="text-align:center">
					<?php echo $this->escape(empty($row->receiver_phone) ? JText::_('NO_PHONE') : $row->receiver_phone); ?>
				</td>
				<td align="center" style="text-align:center">
					<?php echo $this->escape($row->receiver_email); ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
	</table>
</div>
<?php }else if(!empty($this->chronoUsers)) echo JText::_("SMS_NO_STATISTICS"); ?>
