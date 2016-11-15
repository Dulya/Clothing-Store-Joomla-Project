<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php if(!empty($this->campaigns)) {?>
<br style="font-size:1px;"/>
<div id="dash_campaigns">

	<h1 class="acysms_graphtitle"> <?php echo JText::_('SMS_LATEST_CAMPAIGN') ?> </h1>
	<table class="acysms_table" cellpadding="1">
		<thead>
		<tr>
			<th class="title">
				<?php echo JText::_('ID'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('SUBJECT'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('TYPE'); ?>
			</th>
			<th class="title">
				<?php echo JText::_('STATUS'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$k = 0;
		foreach($this->campaigns as $oneCampaign){
			$row =& $oneCampaign;
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="center" style="text-align:center">
					<?php echo $this->escape($row->message_id); ?>
				</td>
				<td align="center" style="text-align:center">
					<a href="<?php echo ACYSMS::completeLink('index.php?option=com_acysms&ctrl=message&task=edit&cid='.$row->message_id) ?>"><?php echo $this->escape($row->message_subject); ?></a>
				</td>
				<td align="center" style="text-align:center">
					<?php echo $this->escape($row->message_type); ?>
				</td>
				<td align="center" style="text-align:center">
					<?php echo $this->escape($row->message_status); ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
	</table>
</div>
<?php }else if(!empty($this->campaignPerMonth)) echo JText::_("SMS_NO_STATISTICS"); ?>
