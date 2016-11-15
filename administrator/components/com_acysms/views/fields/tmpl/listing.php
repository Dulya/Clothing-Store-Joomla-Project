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
	if(ACYSMS_J30){
		$saveOrderingUrl = 'index.php?option=com_acymailing&task=saveorder&tmpl=component';
		JHtml::_('sortablelist.sortable', 'fieldslisting', 'adminForm', 'asc', $saveOrderingUrl);
	}
	?>
	<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=fields" method="post" name="adminForm" id="adminForm">
		<table class="acysms_table" id="fieldslisting">
			<thead>
			<tr>
				<th class="title titlenum">
					<?php echo JText::_('SMS_NUM'); ?>
				</th>
				<?php if(ACYSMS_J30){ ?>
					<th width="1%" class="title titleorder">
						<?php echo '<i class="icon-menu-2"></i>'; ?>
					</th>
				<?php } ?>
				<th class="title titlebox">
					<input type="checkbox" name="toggle" value="" onclick="acysms_js.checkAll(this);"/>
				</th>
				<th class="title titlecolumn">
					<?php echo JText::_('SMS_FIELD_COLUMN'); ?>
				</th>
				<th class="title titlelabel">
					<?php echo JText::_('SMS_FIELD_LABEL'); ?>
				</th>
				<th class="title titletype">
					<?php echo JText::_('SMS_FIELD_TYPE'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_REQUIRED'); ?>
				</th>
				<?php if(!ACYSMS_J30){ ?>
					<th class="title titleorder">
						<?php echo JText::_('SMS_ORDERING');
						echo JHTML::_('grid.order', $this->rows); ?>
					</th>
				<?php } ?>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_DISPLAY_FRONTCOMP'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_DISPLAY_BACKEND'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_DISPLAY_LISTING'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_PUBLISHED'); ?>
				</th>
				<th class="title titletoggle">
					<?php echo JText::_('SMS_CORE'); ?>
				</th>
				<th class="title titleid">
					<?php echo JText::_('SMS_ID'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$k = 0;

			for($i = 0, $a = count($this->rows); $i < $a; $i++){
				$row =& $this->rows[$i];

				$publishedid = 'fields_published-'.$row->fields_fieldid;
				$requiredid = 'fields_required-'.$row->fields_fieldid;
				$backendid = 'fields_backend-'.$row->fields_fieldid;
				$frontcompid = 'fields_frontcomp-'.$row->fields_fieldid;
				$listingid = 'fields_listing-'.$row->fields_fieldid;
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td align="center">
						<?php echo $i + 1; ?>
					</td>
					<?php if(ACYSMS_J30){ ?>
						<td class="order" width="3%">
						<span class="sortable-handler">
							<i class="icon-menu"></i>
						</span>
							<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->fields_ordering; ?>" class="width-20 text-area-order"/>
						</td>
					<?php } ?>
					<td align="center">
						<?php echo JHTML::_('grid.id', $i, $row->fields_fieldid); ?>
					</td>
					<td align="center">
						<a href="<?php echo ACYSMS::completeLink('fields&task=edit&fields_fieldid='.$row->fields_fieldid); ?>">
							<?php echo $this->escape($row->fields_namekey); ?>
						</a>
					</td>
					<td align="center">
						<?php echo $this->fieldsClass->trans($row->fields_fieldname); ?>
					</td>
					<td align="center">
						<?php echo $this->fieldtype->allValues[$row->fields_type]; ?>
					</td>
					<td align="center">
						<span id="<?php echo $requiredid ?>" class="loading"><?php echo $this->toggleClass->toggle($requiredid, (int)$row->fields_required, 'fields') ?></span>
					</td>
					<?php if(!ACYSMS_J30){ ?>
						<td align="center" class="order">
							<span><?php echo $this->pagination->orderUpIcon($i, $row->fields_ordering >= @$this->rows[$i - 1]->fields_ordering, 'orderup', 'Move Up', true); ?></span>
							<span><?php echo $this->pagination->orderDownIcon($i, $a, $row->fields_ordering <= @$this->rows[$i + 1]->fields_ordering, 'orderdown', 'Move Down', true); ?></span>
							<input type="text" name="order[]" size="5" value="<?php echo $row->fields_ordering; ?>" class="text_area" style="text-align: center"/>
						</td>
					<?php } ?>
					<td align="center">
						<span id="<?php echo $frontcompid ?>" class="loading"><?php echo $this->toggleClass->toggle($frontcompid, (int)$row->fields_frontcomp, 'fields') ?></span>
					</td>
					<td align="center">
						<span id="<?php echo $backendid ?>" class="loading"><?php echo $this->toggleClass->toggle($backendid, (int)$row->fields_backend, 'fields') ?></span>
					</td>
					<td align="center">
						<span id="<?php echo $listingid ?>" class="loading"><?php echo $this->toggleClass->toggle($listingid, (int)$row->fields_listing, 'fields') ?></span>
					</td>
					<td align="center">
						<span id="<?php echo $publishedid ?>" class="loading"><?php echo $this->toggleClass->toggle($publishedid, (int)$row->fields_published, 'fields') ?></span>
					</td>
					<td align="center">
						<?php echo $this->toggleClass->display('activate', $row->fields_core); ?>
					</td>
					<td width="1%" align="center">
						<?php echo $row->fields_fieldid; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
			<tfoot>
			<tr>
				<td colspan="12">
					<?php echo $this->pagination->getListFooter(); ?>
					<?php echo $this->pagination->getResultsCounter(); ?>
				</td>
			</tr>
			</tfoot>
		</table>

		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="fields"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
