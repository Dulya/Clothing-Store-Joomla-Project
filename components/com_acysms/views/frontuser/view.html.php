<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
include(ACYSMS_BACK.'views'.DS.'user'.DS.'view.html.php');

class FrontUserViewFrontUser extends UserViewUser
{
	function display($tpl = null)
	{
		JHTML::_('behavior.tooltip');

		global $Itemid;
		$this->assignRef('Itemid',$Itemid);

		parent::display($tpl);
	}
}
