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
	<script type="text/javascript">
		function insertTag(tag){
			try{
				myField = document.getElementById('message_body');
				if(document.selection){
					myField.focus();
					sel = document.selection.createRange();
					sel.text = tag;
				}else if(myField.selectionStart || myField.selectionStart == '0'){
					var startPos = myField.selectionStart;
					var endPos = myField.selectionEnd;
					myField.value = myField.value.substring(0, startPos) + tag + myField.value.substring(endPos, myField.value.length);
				}else{
					myField.value += tag;
				}
				countCharacters();
			}catch(err){
				document.getElementById("messagetags_info").innerHTML = '<?php echo JText::_('SMS_COPY_TAG'); ?><br />' + tag;
			}
		}

		function chooseTags(checkbox, id, className){
			var button = checkbox.parentNode.parentNode.getElementsByClassName('c-hamburger')[0];
			if(button.className.search('is-active') < 0) button.click();


			var elements = document.getElementsByClassName(className);
			for(var i = 0; i < elements.length; i++){
				elements[i].style.display = 'none';
			}
			document.getElementById(id).style.display = 'block';
		}

		function acysmsToggleTags(event, button, id, myclass){
			event.preventDefault();
			if(button.className.search('is-active') < 0){
				button.className += ' is-active';
			}else{
				button.className = button.className.replace('is-active', '');
			}

			elem = document.getElementById(id);
			if(elem.className.search(myclass) < 0){
				elem.className += myclass;
				elem.style.display = 'block';
			}else{
				elem.className = elem.className.replace(myclass, '');
				elem.style.display = 'none';
			}
		}
	</script>
	<form action="<?php echo JRoute::_('index.php?option=com_acysms&tmpl=component&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		<div id="phone_tags"<?php echo $this->app->isAdmin() ? ' style="float:left; width:48%"' : ''; ?>>
			<div class="acysmsonelineblockoptions">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_MESSAGE'); ?></span>
				<table class="acysms_blocktable">
					<tr>
						<td class="key" id="subjectkey">
							<label for="message_subject">
								<?php echo JText::_('SMS_SUBJECT'); ?>
							</label>
						</td>
						<td id="subjectinput">
							<input type="text" name="data[message][message_subject]" id="message_subject" class="inputbox" style="width:80%" value="<?php echo $this->escape(@$this->message->message_subject); ?>" autofocus/>
						</td>
					</tr>
					<tr>
						<td class="key" id="senderkey">
							<label for="status">
								<?php echo JText::_('SMS_SENDER_PROFILE'); ?>
							</label>
						</td>
						<td>
							<?php
							$this->senderprofile->includeMMSJS = true;
							echo $this->senderprofile->display('data[message][message_senderprofile_id]', @$this->message->message_senderprofile_id);
							?>
						</td>
					</tr>
					<?php
					if(!empty($this->message->message_senddate)){ ?>
						<tr>
							<td class="key" id="senddatekey">
								<?php echo JText::_('SMS_SEND_DATE'); ?>
							</td>
							<td id="senddateinput">
								<?php echo ACYSMS::getDate(@$this->message->message_senddate); ?>
							</td>
							<td/>
							<td/>
						</tr>
					<?php } ?>
				</table>
			</div>


			<?php

			$translationStrings = array('ecommerceTags' => 'SMS_ECOMMERCE_MANAGEMENT_EXTENSION', 'eventTags' => 'SMS_EVENT_MANAGEMENT_EXTENSION', 'communityTags' => 'SMS_COMMUNITY_MANAGEMENT_EXTENSION', 'otherTags' => 'SMS_OTHER_EXTENSIONS');
			$iconClass = array('communityTags' => 'smsicon-megaphone', 'ecommerceTags' => 'smsicon-pricetag', 'eventTags' => 'smsicon-calendar', 'otherTags' => 'smsicon-extension');

			foreach($this->tags as $tagTypeTitle => $tagTypes){
				if(!in_array($tagTypeTitle, array('communityTags', 'ecommerceTags', 'eventTags'))){
					$tagTypes = array($tagTypeTitle => $tagTypes);
					$tagTypeTitle = 'otherTags';
				} ?>

				<div class="acysmsonelineblockoptions">
					<button onclick="acysmsToggleTags(event, this, '<?php echo strtolower($tagTypeTitle) ?>container','opened')" class="c-hamburger c-hamburger--htx">
						<span>menu</span>
					</button>
					<div class="acysmsblockbluetitle">
						<i class="<?php echo !empty($iconClass[$tagTypeTitle]) ? $iconClass[$tagTypeTitle] : 'smsicon-pricetag' ?>"></i>
						<span class="extensions_choice_intro"><?php echo JText::sprintf('SMS_INSERT_AUTOMATIC_CONTENT_FROM', '<span class="extension_bluedetail">'.JText::_($translationStrings[$tagTypeTitle]).'</span>'); ?></span>
					</div>
					<div class="introtags acysmsradio">
						<?php

						$tagsContents = array();
						$i = 0;
						foreach($tagTypes as $oneTagName => $oneTagDetails){
							$checked = ($i == 0) ? 'checked="checked"' : '';
							$tagsContents[$oneTagName] = $oneTagDetails->content;
							echo '<div><input type="radio" '.$checked.' name="'.strtolower($tagTypeTitle).'" id="'.strtolower($tagTypeTitle).'_'.$i.'" onclick="chooseTags(this, \''.$oneTagName.'_tags\', \''.strtolower($tagTypeTitle).'\');"><label for="'.strtolower($tagTypeTitle).'_'.$i.'">'.$oneTagDetails->name.'</label></div>';
							$i++;
						}
						?>
					</div>
					<div style="display:none" id="<?php echo strtolower($tagTypeTitle) ?>container">
						<?php
						$i = 0;
						foreach($tagsContents as $oneName => $content){
							$style = ($i == 0) ? 'style="display:block"' : 'style="display:none"';
							echo '<div class="'.strtolower($tagTypeTitle).'" '.$style.' id="'.$oneName.'_tags">'.$content.'</div>';
							$i++;
						}
						?>
					</div>
				</div>
				<?php
			} ?>

		</div>

		<?php $app = JFactory::getApplication();
		$class = '';
		if($app->isAdmin()) $class = 'class="acysmstelaffix"';
		?>

		<div id="phone_interface" style="float:left; width:46%; margin:15px;" <?php echo $class; ?>>
			<?php
			$countType = ACYSMS::get('type.countcharacters');
			echo $countType->countCaracters('message_body', '');
			?>
			<div id="sms_body">
				<textarea <?php echo empty($this->messageMaxChar) ? "" : 'maxlength="'.$this->messageMaxChar.'"'; ?> onclick="countCharacters();" onkeyup="countCharacters();" rows="20" name="data[message][message_body]" id="message_body"><?php echo $this->escape(@$this->message->message_body); ?></textarea>
				<?php $phoneType = ACYSMS::get('helper.phone');
				echo $phoneType->displayMMS($this, true); ?>
			</div>
			<div id="sms_bottom">
			</div>
		</div>

		<div class="clr"></div>
		<input type="hidden" name="cid[]" value="<?php echo $this->escape(@$this->message->message_id); ?>"/>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="data[message][message_type]" value="<?php echo $this->message->message_type; ?>"/>
		<input type="hidden" name="data[message][message_autotype]" value="<?php echo $this->escape(@$this->message->message_autotype); ?>"/>
		<input type="hidden" name="data[message][message_status]" value="<?php echo $this->escape(@$this->message->message_status); ?>"/>
		<input type="hidden" name="task" value="answermessage"/>
		<?php
		if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		echo JHTML::_('form.token');
		?>
	</form>
</div>
