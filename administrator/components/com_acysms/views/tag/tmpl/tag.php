<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><style type="text/css">
	body {
		height: auto;
		min-width: 650px !important;
	}

	html {
		overflow-y: auto;
	}

	.rt-container, .rt-block {
		width: auto !important;
		background-color: #f6f7f9 !important;
	}
</style>
<div id="acysms_content">
	<div id="acysms_edit" class="acytagpopup">
		<div id="inserttagdiv">
			<input type="text" class="inputbox" style="width:300px;" id="tagstring" name="tagstring" value="" onclick="this.select();">
			<button class="acysms_button" id="insertButton" onclick="insertTag();"><?php echo JText::_('SMS_INSERT_TAG') ?></button>
		</div>
		<form action="#" method="post" name="adminForm" id="adminForm" autocomplete="off">
			<div id="plugarea">
				<?php echo $this->defaultContent; ?>
			</div>
			<div class="clr"></div>

			<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
			<input type="hidden" name="task" value="tag"/>
			<input type="hidden" name="content" value="<?php echo @$this->content; ?>"/>
			<input type="hidden" id="fctplug" name="fctplug" value="<?php echo $this->fctplug; ?>"/>
			<input type="hidden" name="ctrl" value="<?php echo $this->ctrl; ?>"/>
			<?php echo JHTML::_('form.token'); ?>
		</form>
	</div>
</div>
