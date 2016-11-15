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

<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=receiver" method="post" name="adminForm" id="adminForm">
	<table>
		<tr>
			<td id="receiverfilter">
				<?php ACYSMS::listingSearch($this->escape($this->pageInfo->search)); ?>
			</td>
			<td nowrap="nowrap">
			</td>
		</tr>
	</table>
	<table class="adminlist table table-striped table-hover" cellpadding="1">
		<thead>
		<tr>
			<th class="title titlenum">
				<?php echo JText::_('SMS_NUM'); ?>
			</th>

			<th class="title">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_NAME'), 'receiver_name', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_EMAIL'), 'receiver_email', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_PHONE'), 'receiver_phone', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
			</th>
			<th class="title titleid">
				<?php echo JHTML::_('grid.sort', JText::_('SMS_ID'), 'receiver_id', $this->pageInfo->filter->order->dir, $this->pageInfo->filter->order->value); ?>
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
		$phoneHelper = ACYSMS::get('helper.phone');
		for($i = 0, $a = count($this->rows); $i < $a; $i++){
			$row = &$this->rows[$i];
			$receiverid = 'receiver_status_'.$row->receiver_id;

			$onClick = '';
			if($this->jsFct == 'affectTestUser'){
				$onClick = 'onclick="window.top.affectTestUser(\''.strip_tags(intval($row->receiver_id)).'\',\''.addslashes(strip_tags($row->receiver_name)).'\',\''.addslashes(strip_tags($phoneHelper->getValidNum($row->receiver_phone))).'\',\''.addslashes(strip_tags($this->htmlID)).'\'); acysms_js.closeBox(true);"';
			}else if($this->jsFct == 'affectUser') $onClick = 'onclick="window.top.affectUser(\''.strip_tags(intval($row->receiver_id)).'\',\''.addslashes(strip_tags($row->receiver_name)).'\',\''.addslashes(strip_tags($phoneHelper->getValidNum($row->receiver_phone))).'\'); acysms_js.closeBox(true);"';

			?>
			<tr class="<?php echo "row$k"; ?>" style="cursor:pointer" <?php echo $onClick; ?>>
				<td align="center">
					<?php echo $this->pagination->getRowOffset($i); ?>
				</td>
				<td align="center">
					<?php echo $this->escape($row->receiver_name); ?>
				</td>
				<td align="center">
					<a><?php echo $this->escape($row->receiver_email); ?></a>
				</td>
				<td align="center">
					<?php
					if(!empty ($row->receiver_phone)){
						$phone = $phoneHelper->getValidNum($row->receiver_phone);
						if(!($phone)){
							echo '<font color="red" >'.$row->receiver_phone.'</font>';
						}else    echo $phone;
					}else{
						echo "";
					}
					?>
				</td>
				<td width="1%" align="center">
					<?php echo $row->receiver_id; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
	<input type="hidden" name="task" value="choose"/>
	<input type="hidden" name="tmpl" value="component"/>
	<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>"/>
	<?php echo JHTML::_('form.token'); ?>
</form>
</div>
