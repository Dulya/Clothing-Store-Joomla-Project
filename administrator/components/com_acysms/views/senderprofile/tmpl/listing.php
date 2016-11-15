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
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=senderprofile" method="post" name="adminForm" id="adminForm">
		<table class="acysms_table_options">
			<tr>
				<td>
					<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
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
				<th class="title titlename">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_SENDER_PROFILE_NAME'), 'a.senderprofile_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlegateway">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_GATEWAY'), 'a.senderprofile_gateway', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titlesender">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_CREATOR'), 'b.name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_DEFAULT'), 'a.senderprofile_default', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
				</th>
				<th class="title titleid">
					<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'a.senderprofile_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
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
				$visibleid = 'senderprofile_id-'.$row->senderprofile_id;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
						<?php echo $this->pagination->getRowOffset($i); ?>
					</td>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->senderprofile_id); ?>
					</td>
					<td align="center">
						<?php echo '<a href="'.ACYSMS::completeLink('senderprofile&task=edit&cid[]='.$row->senderprofile_id).'">'.ACYSMS::dispSearch($row->senderprofile_name, $this->pageInfo->search).'</a>'; ?>
					</td>
					<td align="center">
						<?php echo $row->senderprofile_gateway; ?>
					</td>
					<td align="center">
						<?php
						if(!empty($row->name)){
							$text = '<b>'.JText::_('SMS_NAME').' : </b>'.$row->name;
							$text .= '<br /><b>'.JText::_('SMS_USERNAME').' : </b>'.$row->username;
							$text .= '<br /><b>'.JText::_('SMS_EMAIL').' : </b>'.$row->email;
							$text .= '<br /><b>'.JText::_('SMS_ID').' : </b>'.$row->senderprofile_userid;
							echo ACYSMS::tooltip($text, $row->name, '', $row->name, 'index.php?option=com_users&task=edit&cid[]='.$row->senderprofile_userid);
						}
						?>
					</td>
					<td align="center" id="icon">
						<span id="<?php echo $visibleid ?>" class="loading"><?php echo $this->toggleHelper->toggle($visibleid, $row->senderprofile_default, 'senderprofile') ?></span>
					</td>
					<td width="1%" align="center">
						<?php echo $row->senderprofile_id; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
		<script type="text/javascript">
			function switchDefault(idField){
				var table = document.getElementsByClassName('acysms_table')[0]; //The table of the gateways
				var tr = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr'); //The gateways

				for(var i = 0; i < tr.length; i++){ //Change all the icon to cancel icon
					var icon = tr[i].getElementById('icon'); //The td that contain the icon
					var a = icon.getElementsByTagName('a')[0]; //The icon
					var span = icon.getElementsByTagName('span')[0]; //the span get the id of the gateway
					var id = span.id.replace('senderprofile_id-', ''); //The treatment to get the id of the gateway
					a.className = 'smsicon-cancel'; //Change the icon to cancel icon
				}
				joomTogglePicture('senderprofile_id-' + idField, '1', 'senderprofile'); //we set the new default gateway
			}
		</script>

		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
