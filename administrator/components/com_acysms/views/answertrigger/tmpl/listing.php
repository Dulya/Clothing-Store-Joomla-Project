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

	<?php
	$saveOrder = ($this->pageInfo->filter->order->value == 'answertrigger.answertrigger_ordering' ? true : false);
	if(ACYSMS_J30 && $saveOrder){
		$saveOrderingUrl = 'index.php?option=com_acysms&task=saveorder&tmpl=component';
		JHtml::_('sortablelist.sortable', 'answertriggerslisting', 'adminForm', strtolower($this->pageInfo->filter->order->dir), $saveOrderingUrl);
	}
	?>

	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=answertrigger" method="post" name="adminForm" id="adminForm">
		<table class="acysms_table_options">
			<tr>
				<td>
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
				</td>
			</tr>
		</table>
		<table class="acysms_table" cellpadding="1" id="answertriggerslisting">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_('SMS_NUM'); ?>
				</th>
				<?php if(ACYSMS_J30){ ?>
					<th class="title titleorder" style="width:32px !important; padding-left:1px; padding-right:1px;">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'answertrigger.answertrigger_ordering', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
				<?php } ?>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this);"/>
				</th>
				<th class="title titlename">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'answertrigger.answertrigger_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleactions">
					<?php echo JText::_('SMS_ACTIONS'); ?>
				</th>
				<th class="title titleenabled">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_ENABLED'), 'answertrigger.answertrigger_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<?php if(!ACYSMS_J30){ ?>
					<th class="title titleorder">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_ORDERING'), 'answertrigger.answertrigger_ordering', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
						<?php if($this->order->ordering) echo JHTML::_('grid.order', $this->rows); ?>
					</th>
				<?php } ?>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'answertrigger.answertrigger_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
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
				$row =& $this->rows[$i];
				$publishedid = 'answertrigger_publish-'.$row->answertrigger_id;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<?php if(ACYSMS_J30){ ?>
						<td class="order">
							<?php $iconClass = '';
							if(!$saveOrder) $iconClass = ' inactive tip-top hasTooltip" title="'.JHtml::tooltipText('JORDERINGDISABLED'); ?>
							<span class="sortable-handler<?php echo $iconClass ?>">
							<i class="icon-menu"></i>
						</span>
							<?php if($saveOrder){ ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->answertrigger_ordering; ?>" class="width-20 text-area-order"/>
							<?php } ?>
						</td>
					<?php } ?>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->answertrigger_id); ?>
					</td>
					<td align="center">
						<?php echo '<a href="'.ACYSMS::completeLink('answertrigger&task=edit&cid[]='.$row->answertrigger_id).'">'.ACYSMS::dispSearch($row->answertrigger_name, $this->pageInfo->search).'</a>'; ?>
					</td>
					<td align="center">
						<?php echo $row->answertrigger_actions; ?>
					</td>
					<td align="center">
						<span id="<?php echo $publishedid ?>" class="loading" align="center"><?php echo $this->toggleHelper->toggle($publishedid, $row->answertrigger_publish, 'answertrigger') ?></span>
					</td>
					<?php if(!ACYSMS_J30){ ?>
						<td align="center" class="order">
							<span><?php echo $this->pagination->orderUpIcon($i, $this->order->reverse XOR ($row->answertrigger_ordering >= @$this->rows[$i - 1]->answertrigger_ordering), $this->order->orderUp, 'Move Up', $this->order->ordering); ?></span>
							<span><?php echo $this->pagination->orderDownIcon($i, $a, $this->order->reverse XOR ($row->answertrigger_ordering <= @$this->rows[$i + 1]->answertrigger_ordering), $this->order->orderDown, 'Move Down', $this->order->ordering); ?></span>
							<input type="text" name="order[]" size="5" <?php if(!$this->order->ordering) echo 'disabled="disabled"' ?> value="<?php echo $row->answertrigger_ordering; ?>" class="text_area" style="text-align: center"/>
						</td>
					<?php } ?>
					<td width="1%" align="center">
						<?php echo $row->answertrigger_id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
		<div class="clr"></div>

		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
