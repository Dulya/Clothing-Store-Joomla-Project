<?php
/**
 * @package	AcySMS for Joomla!
 * @version	3.1.0
 * @author	acyba.com
 * @copyright	(C) 2009-2016 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><div id="acysms_content" >
<div id="iframedoc"></div>
<form action="index.php?option=<?php echo ACYSMS_COMPONENT ?>&amp;ctrl=cpanel" method="post" name="adminForm" autocomplete="off" id="adminForm" >
	<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="ctrl" value="cpanel" />
	<?php echo JHTML::_( 'form.token' );

		echo $this->tabs->startPane( 'config_tab');
		echo $this->tabs->startPanel( JText::_( 'SMS_MESSAGE_CONFIG' ), 'config_message');
		include(dirname(__FILE__).DS.'message.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'SMS_QUEUE_PROCESS' ), 'config_queue');
		include(dirname(__FILE__).DS.'queue.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'SMS_INTERFACES' ), 'config_interface');
		include(dirname(__FILE__).DS.'interface.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'SMS_ACCESS_LEVEL' ), 'config_acl');
		include(dirname(__FILE__).DS.'acl.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'SMS_PLUGINS' ), 'config_plugins');
		include(dirname(__FILE__).DS.'plugins.php');
		echo $this->tabs->endPanel();
		echo $this->tabs->startPanel( JText::_( 'SMS_LANGUAGES' ), 'config_languages');
		include(dirname(__FILE__).DS.'languages.php');
		echo $this->tabs->endPane();
	?>
	<div class="clr"></div>
</form>
</div>
