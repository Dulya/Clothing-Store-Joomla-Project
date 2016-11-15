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
<script language="javascript" type="text/javascript">
<!--
	var selectedContents = new Array();
	var allElements = <?php echo count($this->rows);?>;
	<?php
		foreach($this->rows as $oneRow){
			if(!empty($oneRow->selected)){
				echo "selectedContents[".$oneRow->group_id."] = 'content';";
			}
		}
	?>
	function applyContent(contentid,rowClass){
		if(selectedContents[contentid]){
			window.document.getElementById('content'+contentid).className = rowClass;
			delete selectedContents[contentid];
		}else{
			window.document.getElementById('content'+contentid).className = 'selectedrow';
			selectedContents[contentid] = 'content';
		}
	}

	function insertTag(){
		var tag = '';
		for(var i in selectedContents){
			if(selectedContents[i] == 'content'){
				allElements--;
				if(tag != '') tag += ',';
				tag = tag + i;
			}
		}
		if(allElements == 0) tag = 'All';
		if(allElements == <?php echo count($this->rows);?>) tag = 'None';
		window.top.document.getElementById('<?php echo (!empty($this->nbDisplay)) ? $this->controlName.$this->fieldName.'_'.$this->nbDisplay : $this->controlName.$this->fieldName; ?>').value = tag;
		window.top.document.getElementById('link<?php echo (!empty($this->nbDisplay)) ? $this->controlName.$this->fieldName.'_'.$this->nbDisplay : $this->controlName.$this->fieldName; ?>').href = 'index.php?option=com_acysms&tmpl=component&ctrl=group&task=choose&control=<?php echo $this->controlName; ?>&values='+tag+'<?php if(!empty($this->nbDisplay)) echo "&nb_display=".$this->nbDisplay; ?>';
		acysms_js.closeBox(true);
	}
//-->
</script>
<style type="text/css">
	table.adminlist tr.selectedrow td{
		background-color:#FDE2BA;
	}
</style>
<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=group" method="post" name="adminForm" id="adminForm" >
<div style="float:right;margin-bottom : 10px">
	<button class="acysms_button" id="insertButton" onclick="insertTag(); return false;"><?php echo JText::_('SMS_APPLY'); ?></button>
</div>
<div style="clear:both"/>
	<table class="acysms_table">
		<thead>
			<tr>
				<th class="title">

				</th>
				<th class="title titlecolor">

				</th>
				<th class="title">
					<?php echo JText::_('SMS_NAME'); ?>
				</th>
				<th class="title titleid">
					<?php echo JText::_('SMS_ID'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$k = 0;

				for($i = 0,$a = count($this->rows);$i<$a;$i++){
					$row =& $this->rows[$i];
			?>
				<tr class="<?php echo empty($row->selected) ? "row$k" : 'selectedrow'; ?>" id="content<?php echo $row->group_id?>" onclick="applyContent(<?php echo $row->group_id.",'row$k'"?>);" style="cursor:pointer;">
					<td class="acysmstdcheckbox"></td>
					<td>
					<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->group_color.'"></div>'; ?>
					</td>
					<td>
					<?php
						echo ACYSMS::tooltip($this->escape($row->group_description), $this->escape($row->group_name), 'tooltip.png', $this->escape($row->group_name));
					?>
					</td>
					<td align="center">
						<?php echo $row->group_id; ?>
					</td>
				</tr>
			<?php
					$k = 1-$k;
				}
			?>
		</tbody>
	</table>
</form>
</div>
