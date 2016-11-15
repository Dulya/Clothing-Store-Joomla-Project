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
	<form action="index.php?option=<?php echo($this->app->isAdmin() ? ACYSMS_COMPONENT.'&ctrl=answer' : ACYSMS_COMPONENT.'&ctrl=frontanswer'); ?>" ) ) method="post" name="adminForm" id="adminForm">

		<div id="answerlistingdata">
			<?php include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'answerdatalisting.php'); ?>
		</div>


		<div class="clr"></div>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value=""/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<input type="hidden" name="boxchecked" value="0"/>
		<input type="hidden" name="filter_order" value="<?php echo $this->pageInfo->filter->order->value; ?>"/>
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->pageInfo->filter->order->dir; ?>"/>
		<?php echo JHTML::_('form.token'); ?>
	</form>
</div>
