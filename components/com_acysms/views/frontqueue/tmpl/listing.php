<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><fieldset id="acysms_queue_listing_menu">
	<div class="toolbar" id="acysmstoolbar" style="float: right;">
		<table>
			<tr>
				<?php if(ACYSMS::isAllowed($this->config->get('acl_queue_manage', 'all'))){ ?>
					<td id="acysmsbutton_queue_process">
						<a class="modal" rel="{handler: 'iframe', size: {x: 750, y: 550}}" href="<?php echo ACYSMS::completeLink('frontqueue&task=process&tmpl=component', true, false); ?>"><span title="<?php echo JText::_('SMS_PROCESS'); ?>"><i class="smsicon-process"></i></span><?php echo JText::_('SMS_PROCESS'); ?></a>
					</td>
				<?php } ?>

				<?php if(ACYSMS::isAllowed($this->config->get('acl_queue_delete', 'all'))){ ?>
					<td id="acysmsbutton_queue_delete">
						<a onclick="javascript:if(confirm('<?php echo JText::sprintf('SMS_CONFIRM_DELETE_QUEUE', $this->pageInfo->elements->total); ?>')){submitbutton('remove');} return false;" href="#"><span title="<?php echo JText::_('SMS_DELETE'); ?>"><i class="smsicon-delete"></i></span><?php echo JText::_('SMS_DELETE'); ?></a>
					</td>
				<?php } ?>
			</tr>
		</table>
	</div>
	<div class="acysmsheader" style="float: left;"><h1><?php echo JText::_('SMS_QUEUE'); ?></h1></div>
</fieldset>
<?php
include(ACYSMS_BACK.'views'.DS.'queue'.DS.'tmpl'.DS.'listing.php');
