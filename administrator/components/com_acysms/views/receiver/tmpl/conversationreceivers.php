<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><table>
	<?php
	foreach($this->receivers as $oneUser){
		echo '<tr class="row_user" onclick="setUser(\''.str_replace("'", "\'", $oneUser->name).'\',\''.str_replace("'", "\'", $oneUser->receiverId).'\');loadConversation();" style="cursor:pointer"><td>'.htmlspecialchars($oneUser->name, ENT_COMPAT, 'UTF-8').'</td></tr>';
	}
	exit;
	?>
</table>
