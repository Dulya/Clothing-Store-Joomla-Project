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
$app = JFactory::getApplication();
$style = $app->isAdmin() ? 'min-width: 600px;' : 'width:100%;';
?>
<textarea style="<?php echo $style; ?> height: 100px;" rows="20" name="textareaentries">
<?php $text = JRequest::getString("textareaentries");
if(empty($text)){ ?>
phone_number,firstname,lastname,birthdate,email
+33xxxxx,AlexandreFirstName,AlexandreLastName,1900-06-15,alexandre@example.com
<?php }else echo $text ?>
</textarea>
