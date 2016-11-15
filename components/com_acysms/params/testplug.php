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
if(version_compare(JVERSION,'1.6.0','<')){
	class JElementTestplug extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			JHTML::_('behavior.modal','a.modal');
			$link = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=cpanel&amp;task=plgtrigger&amp;plg='.$value.'&amp;plgtype='.$name;
			return '<a class="modal" title="Click here"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button class="acysms_button" onclick="return false">Click here</button></a>';
		}
	}
}else{
	class JFormFieldTestplug extends JFormField
	{
		var $type = 'testplug';
		function getInput() {
			JHTML::_('behavior.modal','a.modal');
			$link = 'index.php?option=com_acysms&amp;tmpl=component&amp;ctrl=cpanel&amp;task=plgtrigger&amp;plg='.$this->value.'&amp;plgtype='.$this->fieldname;
			return '<a class="modal" title="Click here"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 650, y: 375}}"><button class="acysms_button" onclick="return false">Click here</button></a>';
		}
	}
}
