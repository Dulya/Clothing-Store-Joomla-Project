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
	<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<?php
		$divClass = $this->app->isAdmin() ? 'acysmsblockoptions' : 'acysmsonelineblockoptions';

		if(!empty($this->coreFields)) include(dirname(__FILE__).DS.'corefields.'.basename(__FILE__));
		if(!empty($this->extraFields)) include(dirname(__FILE__).DS.'extrafields.'.basename(__FILE__));
		?>

		<div class="<?php echo $divClass; ?>">
			<span class="acysmsblocktitle"><?php echo JText::_('SMS_GROUPS'); ?></span>
			<table class="acysms_table" cellspacing="1" align="center">
				<thead>
				<tr>
					<th class="title titlenum">
						<?php echo JText::_('SMS_NUM'); ?>
					</th>
					<th class="title titlecolor">
					</th>
					<th class="title" nowrap="nowrap">
						<?php echo JText::_('SMS_NAME'); ?>
					</th>
					<th class="title" nowrap="nowrap">
						<?php echo JText::_('SMS_STATUS');
						echo '<span style="display:inline-block;font-style:italic;margin-left:50px">'.$this->filters->statusquick.'</span>'; ?>
					</th>
					<th class="title titleid">
						<?php echo JText::_('SMS_ID'); ?>
					</th>
				</tr>
				</thead>
				<tbody>
				<?php
				$k = 0;
				$i = 0;
				foreach($this->subscription as $row){
					?>
					<tr class="<?php echo "row$k "; ?>">
						<td align="center">
							<?php echo $i + 1; ?>
						</td>
						<td width="12">
							<?php echo '<div class="roundsubscrib rounddisp" style="background-color:'.$row->group_color.'"></div>'; ?>
						</td>
						<td>
							<?php
							echo ACYSMS::tooltip($row->group_description, $row->group_name, 'tooltip.png', $row->group_name);
							?>
						</td>
						<td align="center" nowrap="nowrap">
							<?php echo $this->statusType->display('data[groupuser]['.$row->group_id.'][status]', (empty($this->user->user_id) && JRequest::getInt('filter_group') == $row->group_id) ? 1 : @$row->groupuser_status); ?>
						</td>
						<td align="center">
							<?php echo $row->group_id; ?>
						</td>
					</tr>
					<?php
					$k = 1 - $k;
					$i++;
				} ?>
				</tbody>
			</table>
		</div>
		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->user->user_id); ?>"/>
		<?php $app = JFactory::getApplication();
		if($app->isAdmin()) echo '<input type="hidden" id="user_joomid" name="user_joomid" value="'.$this->escape(@$this->user->user_joomid).'" />'; ?>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<?php
		if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		echo JHTML::_('form.token');
		?>
	</form>
</div>
