<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="page-acl">
	<div class="acysmsblockoptions">
		<div class="acysmsblocktitle"><?php echo JText::_('SMS_ACCESS_LEVEL'); ?></div>
		<table class="acysms_blocktable" cellspacing="1">
			<?php
			$acltable = ACYSMS::get('type.acltable');

			$aclcats['answers'] = array('manage', 'delete', 'export');
			$aclcats['answers_trigger'] = array('manage', 'delete', 'copy');
			$aclcats['categories'] = array('manage', 'delete', 'copy');
			$aclcats['configuration'] = array('manage');
			$aclcats['cpanel'] = array('manage');
			$aclcats['groups'] = array('manage', 'delete');
			$aclcats['messages'] = array('create_edit', 'manage_all', 'manage_own', 'delete', 'send', 'copy', 'send_test');
			$aclcats['queue'] = array('manage', 'delete', 'process');
			$aclcats['receivers'] = array('view', 'manage', 'delete', 'export', 'import', 'block', 'unblock');
			$aclcats['sender_profiles'] = array('manage', 'delete', 'copy', 'send_test');
			$aclcats['stats'] = array('manage', 'manage_details', 'export', 'delete');
			$aclcats['tags'] = array('view');
			foreach($aclcats as $category => $actions){ ?>
				<tr>
					<td width="185" class="key" valign="top">
						<?php $trans = JText::_('SMS_'.strtoupper($category));
						if($trans == 'SMS_'.strtoupper($category)) $trans = JText::_(strtoupper($category));
						echo $trans;
						?>
					</td>
					<td>
						<?php echo $acltable->display($category, $actions) ?>
					</td>
				</tr>
			<?php } ?>
		</table>
	</div>
</div>
