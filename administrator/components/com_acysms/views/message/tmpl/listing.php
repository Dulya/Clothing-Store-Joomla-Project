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
	<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm" id="adminForm">
		<table class="acysms_table_options">
			<tr>
				<td>
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
				</td>
				<td align="right">
					<?php echo $this->dropdownFilters->senderprofile; ?>
					<?php if($this->app->isAdmin()) echo $this->dropdownFilters->creator; ?>
					<?php echo $this->dropdownFilters->category; ?>
					<?php echo $this->dropdownFilters->type; ?>
					<?php echo $this->dropdownFilters->publishedStatus; ?>
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
				<th class="title">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SUBJECT'), 'message.message_subject', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titledate">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SEND_DATE'), 'message.message_senddate', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesender">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_CREATOR'), 'joomuser.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesender">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_TYPE'), 'message.message_type', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesender">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_STATUS'), 'message.message_status', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesender">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_CATEGORY'), 'category.category_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'message.message_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="9">
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
				$visibleid = 'visible_'.$row->message_id;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->message_id); ?>
					</td>
					<td align="center">
						<?php
						if(ACYSMS::isAllowed($this->config->get('acl_stats_manage', 'all'))){
							if($this->app->isAdmin()){
								$urlStat = ACYSMS::completeLink('stats&task=diagram&message_id='.$row->message_id, true);
							}else{
								$urlStat = ACYSMS::completeLink('frontstats&task=diagram&message_id='.$row->message_id, true);
							} ?>
							<a class="modal" href="<?php echo $urlStat; ?>" rel="{handler: 'iframe', size: {x: 800, y: 590}}"><i class="smsicon-stats"></i></a>
							<?php
						}
						$ctrl = JRequest::getCmd('ctrl');
						if(ACYSMS::isAllowed($this->config->get('acl_messages_create_edit', 'all'))){
							echo '<a href="'.ACYSMS::completeLink($ctrl.'&task=edit&cid='.$row->message_id.$this->itemId).'">'.ACYSMS::dispSearch($row->message_subject, $this->pageInfo->search).'</a>';
						}else echo $this->escape($row->message_subject);
						?>
					</td>
					<td align="center">
						<?php echo ACYSMS::getDate($row->message_senddate); ?>
					</td>
					<td align="center">
						<?php
						if(!empty($row->name)){
							$text = '<b>'.JText::_('SMS_NAME').' : </b>'.$row->name;
							$text .= '<br /><b>'.JText::_('SMS_USERNAME').' : </b>'.$row->username;
							$text .= '<br /><b>'.JText::_('SMS_EMAIL').' : </b>'.$row->email;
							$text .= '<br /><b>'.JText::_('SMS_ID').' : </b>'.$row->message_userid;
							echo ACYSMS::tooltip($text, $row->name, '', $row->name);
						}
						?>
					</td>
					<?php if($row->message_type == 'auto'){ ?>
						<td colspan="2" align="center">
							<?php echo JText::_('SMS_AUTO').' : '.JText::_('SMS_'.strtoupper($row->message_autotype)); ?>
						</td>
					<?php }else{ ?>
						<td align="center">
							<?php echo JText::_(strtoupper('SMS_'.$row->message_type)); ?>
						</td>
						<td align="center">
							<?php echo JText::_(strtoupper('SMS_STATUS_'.$row->message_status)); ?>
						</td>
					<?php } ?>
					<td align="center">
						<?php echo $row->category_name; ?>
					</td>
					<td width="1%" align="center">
						<?php echo $row->message_id; ?>
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
		<?php
		echo JHTML::_('form.token');
		if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		?>
	</form>
</div>
