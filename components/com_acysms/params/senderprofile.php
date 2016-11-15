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
JHTML::_('behavior.modal','a.modal');
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acysms'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	echo 'This module can not work without the AcySMS Component';
}

if(!ACYSMS_J16){
	class JElementSenderprofile extends JElement
	{
		function fetchElement($name, $value, &$node, $control_name)
		{
			$senderType = ACYSMS::get('type.senderprofile');
			return $senderType->display($control_name.'[senderprofile]',(int) $value,false);
		}
	}
}else{
	class JFormFieldSenderprofile extends JFormField
	{
		var $type = 'senderprofile';

		function getInput() {
			$senderType = ACYSMS::get('type.senderprofile');
			return $senderType->display('jform[params][senderprofile]', $this->value);
		}
	}
}
