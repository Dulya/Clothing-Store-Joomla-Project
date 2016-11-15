<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=user" method="post" name="adminForm" id="adminForm">
	<table class="acysms_table_options">
		<tr>
			<td id="joomuserfilter">
				<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
			</td>
			<td nowrap="nowrap">
			</td>
		</tr>
	</table>
	<table class="acysms_table">
		<thead>
		<tr>
			<th class="title titlenum">
				<?php echo JText::_('SMS_NUM'); ?>
			</th>

			<th class="title">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_USERNAME'), 'username', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_EMAIL'), 'email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title titleid">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="7">
				<?php echo $this->pagination->getListFooter(); ?>
				<?php echo $this->pagination->getResultsCounter(); ?>
			</td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		$k = 0;
		for($i = 0, $a = count($this->rows); $i < $a; $i++){
			$row = &$this->rows[$i];
			?>
			<tr class="<?php echo "row$k"; ?>" style="cursor:pointer" onclick="window.top.affectUser(<?php echo addslashes(intval($row->id)); ?>,'<?php echo addslashes($this->escape($row->name)); ?>','<?php echo addslashes($this->escape($row->email)); ?>'); acysms_js.closeBox(true);">
				<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->name); ?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->username); ?>
				</td>
				<td align="center">
					<a><?php echo $this->escape($row->email); ?></a>
				</td>
				<td width="1%" align="center">
					<?php echo $row->id; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="choosejoomuser"/>
	<input type="hidden" name="tmpl" value="component"/>
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
</div>
