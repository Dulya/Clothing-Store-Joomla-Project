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
	<script langage="javascript">
		window.addEvent("domready", function(){
			showAutoParams()
		});
		function showAutoParams(){
			document.getElementById('typeauto_params').style.display = "none";
			document.getElementById('typestandard_params').style.display = "none";
			document.getElementById('typescheduled_params').style.display = "none";
			document.getElementById('acysms_filters').style.display = "none";
			document.getElementById('submitsend').style.display = "none";
			document.getElementById('submitsave').style.display = "none";


			if(document.getElementById('messageType_auto') && document.getElementById('messageType_auto').checked){
				document.getElementById('typeauto_params').style.display = "block";
				document.getElementById('submitsave').style.display = "block";
				document.getElementById('acysms_filters').style.display = "block";
			}else if(document.getElementById('messageType_standard').checked && document.getElementById('messageType_scheduled').checked){
				document.getElementById('typestandard_params').style.display = "block";
				document.getElementById('typescheduled_params').style.display = "block";
				document.getElementById('acysms_filters').style.display = "block";
				document.getElementById('submitsend').style.display = "block";

				document.getElementById('submitsend').firstElementChild.innerHTML = "<?php echo JText::_('SMS_SCHEDULE'); ?>";

			}else if(document.getElementById('messageType_standard').checked){
				document.getElementById('typestandard_params').style.display = "block";
				document.getElementById('acysms_filters').style.display = "block";
				document.getElementById('submitsend').style.display = "block";

				document.getElementById('submitsend').firstElementChild.innerHTML = "<?php echo JText::_('SMS_SEND'); ?>";
			}else if(document.getElementById('messageType_draft').checked){
				document.getElementById('submitsave').style.display = "block";
			}

		}

		function acysmsToggleFilters(event, button, id, myclass){
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
	<div id="iframedoc"></div>
	<form action="<?php echo JRoute::_('index.php?option=com_acysms&ctrl='.JRequest::getCmd('ctrl')); ?>" method="post" name="adminForm" id="adminForm" autocomplete="off">
		<div<?php echo $this->app->isAdmin() ? ' style="float:left; width:48%"' : ''; ?>>
			<div class="acysmsonelineblockoptions acysmsradio" id="smsParams">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_PARAMS'); ?></span>

				<div id="acysmsIntegrationDropdown">
					<?php
					if($this->integrations->nbIntegrations > 1){
						echo JText::sprintf('SMS_SEND_THIS_SMS_TO', $this->integrations->display);
					}else echo '<input type="hidden" name="data[message][message_receiver_table]" id="selectedIntegration" value="'.$this->currentIntegration.'" />';
					?>
				</div>
				<div>
					<?php echo JText::_('SMS_WHAT_TYPE'); ?>
					<?php
					foreach($this->message_types as $oneType){
						$checked = ($oneType->value == $this->message->message_type) ? 'checked="checked"' : '';
						echo '<input type="radio" '.$checked.' name="data[message][message_type]" value="'.$oneType->value.'" id="messageType_'.$oneType->value.'">';
						echo '<label for="messageType_'.$oneType->value.'" onclick="setTimeout(function(){showAutoParams();}, 50);">'.$oneType->text.'</label>';
					}
					?>
				</div>
				<div class="acysmsmessagetype_params">
					<!-- auto message -->
					<div id="typeauto_params" <?php if($this->message->message_type != "auto") echo 'style="display:none"'; ?>>
						<div id="autoSendParameters">
							<div id="sendBasedOn" style="margin: 10px 0px ">
								<?php echo $this->messageBasedOn; ?>
							</div>
							<div id="autosms_params"><?php
								if(!empty($this->message->message_autotype)){
									$this->dispatcher->trigger('onACYSMSDisplayParamsAutoMessage_'.$this->message->message_autotype, array($this->message));
								}
								?>
							</div>
						</div>
					</div>
					<!-- Standard message -->
					<div id="typestandard_params" <?php if($this->message->message_type != "standard") echo 'style="display:none"'; ?>>
						<div>
							<?php
							$selectedValue = (isset($this->message->message_status) && $this->message->message_status == 'scheduled') ? 'scheduled' : 'notsent';
							foreach($this->message_senddate as $oneSendDate){
								$checked = ($oneSendDate->value == $selectedValue) ? 'checked="checked"' : '';
								echo '<input '.$checked.' type="radio" name="data[message][message_status]" value="'.$oneSendDate->value.'" id="messageType_'.$oneSendDate->value.'">';
								echo '<label onclick="setTimeout(function(){showAutoParams();}, 50);" for="messageType_'.$oneSendDate->value.'">'.$oneSendDate->text.'</label>';
							}
							?>
						</div>
						<div id="typescheduled_params" <?php if($this->message->message_status != "scheduled") echo 'display:none;'; ?>>
							<?php foreach($this->timeField as $oneField) echo $oneField.' '; ?>
						</div>
					</div>
					<!-- Draft -->
					<div id="submitsave" style="<?php if($this->message->message_type == "standard") echo 'display:none;'; ?>">
						<button class="acysms_button" type="submit" onclick="<?php if(ACYSMS_J30){
							echo "Joomla.submitbutton('save')";
						}else echo "submitbutton('save')"; ?> "><?php echo JText::_('SMS_SAVE') ?></button>
					</div>
					<div id="submitsend" style="<?php if($this->message->message_type != "standard") echo 'display:none;'; ?>">
						<button class="acysms_button" type="submit" onclick="<?php if(ACYSMS_J30){
							echo "Joomla.submitbutton('summaryBeforeSend')";
						}else echo "submitbutton('summaryBeforeSend')"; ?> "><?php echo JText::_('SMS_SEND') ?></button>
					</div>
				</div>
			</div>
			<!-- Filters -->
			<div id="acysms_filters" style="display:none"></div>

			<?php
			if(!empty($this->filters)){
				echo '<span style="margin-left: 15px">'.JText::_('SMS_ONY_USERS_SELECTED_INTEGRATION').JText::_('SMS_REFINE_SELECTION_ADDIND_CRITERIA').'</span>'.'<br/>';

				$translationStrings = array('ecommerceFilters' => 'SMS_ECOMMERCE_MANAGEMENT_EXTENSION', 'eventFilters' => 'SMS_EVENT_MANAGEMENT_EXTENSION', 'communityFilters' => 'SMS_COMMUNITY_MANAGEMENT_EXTENSION', 'otherFilters' => 'SMS_OTHER_EXTENSIONS');
				$iconClass = array('communityFilters' => 'smsicon-megaphone', 'ecommerceFilters' => 'smsicon-pricetag', 'eventFilters' => 'smsicon-calendar');

				foreach($this->filters as $filterTypeTitle => $filtersForThisType){
					if(!in_array($filterTypeTitle, array('communityFilters', 'ecommerceFilters', 'eventFilters'))){
						$tagTypes = array($filterTypeTitle => $filtersForThisType);
						$filterTypeTitle = 'otherFilters';
					}
					?>

					<div class="acysmsonelineblockoptions">
						<div class="acysmsblockbluetitle">
							<i class="<?php echo !empty($iconClass[$filterTypeTitle]) ? $iconClass[$filterTypeTitle] : 'smsicon-pricetag' ?>"></i>
							<span class="extensions_choice_intro"><?php echo JText::sprintf('SMS_FILTER_BASED', '<span class="extension_bluedetail">'.JText::_($translationStrings[$filterTypeTitle]).'</span>'); ?></span>
						</div>
						<div class="introfilters">
							<?php
							foreach($this->filters[$filterTypeTitle] as $oneType => $filter){
								echo '<div><input type="checkbox" id="filter_'.$oneType.'" name="data[message][message_receiver][standard][type]['.$oneType.']" value="'.$oneType.'" onclick="loadFilterParams(this.checked, \''.$oneType.'\')" /><label id="label_'.$oneType.'" for="filter_'.$oneType.'" >'.$filter->name.'</label></div>';
							}
							?>

							<div id="<?php echo strtolower($filterTypeTitle) ?>container" style="display: block">
								<?php
								foreach($this->filters[$filterTypeTitle] as $oneType => $filter){

									if(isset($this->message->message_receiver['standard']) && isset($this->message->message_receiver['standard']['type']) && isset($this->message->message_receiver['standard']['type'][$oneType])){
										$style = 'style="display:block"';
										$class = 'opened';
										$isOpen = true;
									}else{
										$style = 'style="display:none"';
										$class = '';
										$isOpen = false;
									}

									echo '<div id="'.$oneType.'_filters" '.$style.'>';
									echo '<div class="acysmsonelineblockoptions" width="100%">';
									echo '<button onclick="acysmsToggleFilters(event, this, \'displayfilterparams_'.$oneType.'\', \'opened\')" class="c-hamburger c-hamburger--htx is-active">
											<span>menu</span>
										</button>';
									echo '<span class="acysmsblocktitle">'.$filter->name.'</span>';
									echo '<div id="displayfilterparams_'.$oneType.'" class="'.$class.'">';
									if($isOpen) $this->dispatcher->trigger('onACYSMSDisplayFilterParams_'.$oneType, array($this->message));
									echo '</div>';
									echo '</div>';
									echo '</div>';
								}
								?>
							</div>
						</div>
					</div>
					<?php
				}
			} ?>
		</div>
		<div<?php echo $this->app->isAdmin() ? ' style="float:left; width:48%"' : ''; ?> class="acysmstelaffix">
			<!-- Test Part of the preview -->
			<div<?php echo $this->app->isAdmin() ? ' class="acysmsblockoptions" style="float:none; width:226px"' : ' class="acysmsonelineblockoptions"'; ?> id="smsPreview">
				<span class="acysmsblocktitle"><?php echo JText::_('SMS_PREVIEW'); ?></span>

				<!-- Test Message -->
				<div id="message-test">
					<?php
					$countryType = ACYSMS::get('type.country');
					$testInputType = ACYSMS::get('type.testReceiver');
					$phoneHelper = ACYSMS::get('helper.phone');

					$app = JFactory::getApplication();
					$ctrl = ($app->isAdmin() ? 'receiver' : 'frontreceiver');

					$front = ($app->isAdmin()) ? false : true;
					$testInputType->display($front, $this->currentIntegration, $this->listReceiversTest);

					if(ACYSMS_J30){
						$submitButton = "Joomla.submitbutton('sendTest')";
					}else $submitButton = "submitbutton('sendTest')";
					echo '<button class="acysms_button" type="submit" onclick="'.$submitButton.'">'.JText::_('SMS_SEND_TEST').'</button>';

					?>
				</div>
			</div>
			<div id="phone_interface">
				<div id="sms_global"<?php echo $this->app->isAdmin() ? ' style="margin:10px 0 0 16px"' : ''; ?>>
					<?php
					$countType = ACYSMS::get('type.countcharacters');
					echo $countType->countCaracters('message_body', '');
					?>
					<div id="sms_body">
						<div style="width:241px !important;overflow: auto; height:318px;" rows="20" name="data[message][message_body]" id="message_body"><?php echo nl2br(@$this->message->message_body); ?></div>
						<?php $phoneType = ACYSMS::get('helper.phone');
						echo $phoneType->displayMMS($this, false); ?>
					</div>
					<div id="sms_bottom">
					</div>
				</div>
			</div>
		</div>

		<input type="hidden" name="cid[]" value="<?php echo $this->message->message_id; ?>"/>
		<input type="hidden" name="option" value="<?php echo ACYSMS_COMPONENT; ?>"/>
		<input type="hidden" name="task" value="sendtest"/>
		<input type="hidden" name="ctrl" value="<?php echo JRequest::getCmd('ctrl'); ?>"/>
		<input type="hidden" name="currentTestIntegration" value="<?php echo $this->currentIntegration; ?>"/>
		<input type="hidden" name="<?php echo $this->currentIntegration.'_testNumberReceiver'; ?>" id="testNumberReceiver" value=""/>
		<?php
		if(!empty($this->Itemid)) echo '<input type="hidden" name="Itemid" value="'.$this->Itemid.'" />';
		echo JHTML::_('form.token');
		?>
	</form>
</div>
