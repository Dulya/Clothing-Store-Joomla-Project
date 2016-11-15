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
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=stats" method="post" name="adminForm" id="adminForm">
		<table class="acysms_table_options">
			<tr>
				<td>
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
				</td>
				<td align="right">
					<?php if($this->app->isAdmin()) echo $this->dropdownFilters->type; ?>
				</td>
			</tr>
		</table>
		<table class="acysms_table">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_('SMS_NUM'); ?>
				</th>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this);"/>
				</th>
				<th class="title titlesubject">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SUBJECT'), 'message.message_subject', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title senddate">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SEND_DATE'), 'message.message_senddate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesent">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_STATS_SEND'), 'stats.stats_nbsent', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlefailed">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_STATS_FAILED'), 'stats.stats_nbfailed', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titletype">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_TYPE'), 'message.message_type', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'stats.stats_message_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter();
					if(ACYSMS_J30) echo '<br />'.$this->pagination->getLimitBox(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for($i = 0, $a = count($this->rows); $i < $a; $i++){
				$row =& $this->rows[$i];
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->stats_message_id); ?>
					</td>
					<td align="center">
						<?php echo '<a href="'.ACYSMS::completeLink('stats&task=detaillisting&filter_message='.$row->stats_message_id).'">'.ACYSMS::dispSearch($row->message_subject, $this->pageInfo->search).'</a>'; ?>
					</td>
					<td align="center">
						<?php echo ACYSMS::getDate($row->message_senddate); ?>
					</td>
					<td align="center">
						<?php echo $row->stats_nbsent; ?>
					</td>
					<td align="center">
						<?php echo $row->stats_nbfailed; ?>
					</td>
					<td align="center">
						<?php echo $row->message_type; ?>
					</td>
					<td align="center">
						<?php echo $row->stats_message_id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
