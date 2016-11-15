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
	$saveOrder = ($this->pageInfo->filter->order->value == 'acysmsgroup.group_ordering' ? true : false);
	if(ACYSMS_J30 && $saveOrder){
		$saveOrderingUrl = 'index.php?option=com_acysms&task=saveorder&tmpl=component';
		JHtml::_('sortablelist.sortable', 'grouplisting', 'adminForm', strtolower($this->pageInfo->filter->order->dir), $saveOrderingUrl);
	}
	?>

	<form action="index.php?option=<?php echo ACYSMS_COMPONENT.'&ctrl='.JRequest::getCmd('ctrl'); ?>" method="post" name="adminForm" id="adminForm">
		<table class="acysms_table_options">
			<tr>
				<td>
				<td id="subscriberfilter">
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
				</td>
				</td>
				<td align="right">
					<?php echo $this->filters->creator; ?>
					<?php echo $this->filters->publishedStatus; ?>
				</td>
			</tr>
		</table>

		<table class="acysms_table" id="grouplisting">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_('SMS_NUM'); ?>
				</th>
				<?php if(ACYSMS_J30){ ?>
					<th class="title titleorder" style="width:32px !important; padding-left:1px; padding-right:1px;">
						<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'acysmsgroup.group_ordering', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
				<?php } ?>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this);"/>
				</th>
				<th class="title titlecolor">
				</th>
				<th class="title titlename">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_GROUP_NAME'), 'acysmsgroup.group_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlelink">
					<?php echo JText::_('SMS_NUMBER_OF_USERS'); ?>
				</th>
				<th class="title titlesender">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_CREATOR'), 'creatorname', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<?php if(!ACYSMS_J30){ ?>
					<th class="title titleorder">
						<?php echo JHTML::_('grid.sort', JText::_('SMS_ORDERING'), 'acysmsgroup.group_ordering', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
						<?php if($this->order->ordering) echo JHTML::_('grid.order', $this->rows); ?>
					</th>
				<?php } ?>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'acysmsgroup.group_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="12">
					<?php echo $this->pagination->getListFooter();
					echo $this->pagination->getResultsCounter();
					if(ACYSMS_J30) echo '<br />'.$this->pagination->getLimitBox(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;

			for($i = 0, $a = count($this->rows); $i < $a; $i++){
				$row =& $this->rows[$i];

				$publishedid = 'group_published_'.$row->group_id;
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
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->group_ordering; ?>" class="width-20 text-area-order"/>
							<?php } ?>
						</td>
					<?php } ?>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->group_id); ?>
					</td>
					<td align="center" width="12">
						<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$this->escape($row->group_color).'"></div>'; ?>
					</td>
					<td align="center">
						<?php
						echo ACYSMS::tooltip($row->group_description, $row->group_name, 'tooltip.png', $row->group_name, ACYSMS::completeLink(JRequest::getCmd('ctrl').'&task=edit&group_id='.$row->group_id));
						?>
					</td>
					<td align="center">
						<?php echo $row->nbsub; ?>
					</td>

					<?php
					if($this->app->isAdmin()){ ?>
						<td align="center">
						<?php
						if(!empty($row->group_user_id)){
							$text = '<b>'.JText::_('SMS_NAME').' : </b>'.$row->creatorname;
							$text .= '<br /><b>'.JText::_('SMS_USERNAME').' : </b>'.$row->username;
							$text .= '<br /><b>'.JText::_('SMS_EMAIL').' : </b>'.$row->email;
							$text .= '<br /><b>'.JText::_('SMS_ID').' : </b>'.$row->group_user_id;
							echo ACYSMS::tooltip($text, $row->creatorname, 'tooltip.png', $row->creatorname, 'index.php?option=com_acysms&ctrl=user&task=edit&cid[]='.$row->group_user_id);
						}
						?>

						</td><?php
					}else{
						echo '<td>'.$row->creatorname.'</td>';
					}
					?>
					<?php if(!ACYSMS_J30){ ?>
						<td align="center" class="order">
							<span><?php echo $this->pagination->orderUpIcon($i, $this->order->reverse XOR ($row->group_ordering >= @$this->rows[$i - 1]->group_ordering), $this->order->orderUp, 'Move Up', $this->order->ordering); ?></span>
							<span><?php echo $this->pagination->orderDownIcon($i, $a, $this->order->reverse XOR ($row->group_ordering <= @$this->rows[$i + 1]->group_ordering), $this->order->orderDown, 'Move Down', $this->order->ordering); ?></span>
							<input type="text" name="order[]" size="5" <?php if(!$this->order->ordering) echo 'disabled="disabled"' ?> value="<?php echo $row->group_ordering; ?>" class="text_area" style="text-align: center"/>
						</td>
					<?php } ?>
					<td align="center">
						<?php echo $row->group_id; ?>
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
