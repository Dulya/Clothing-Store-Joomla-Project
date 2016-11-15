<?php
/**
* Securitycheck Control Panel View para el Componente Securitycheck
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load language
$lang = JFactory::getLanguage();
$lang->load('com_securitycheck.sys');

$review = sprintf( $lang->_('COM_SECURITYCHECK_REVIEW'), '<a href="http://extensions.joomla.org/extensions/extension/access-a-security/site-security/securitycheck" target="_blank">', '</a>' );
$translator_name = $lang->_('COM_SECURITYCHECK_TRANSLATOR_NAME');
$firewall_plugin_status = $lang->_('COM_SECURITYCHECK_FIREWALL_PLUGIN_STATUS');
$cron_plugin_status = $lang->_('COM_SECURITYCHECK_CRON_PLUGIN_STATUS');
$update_database_plugin_status = $lang->_('COM_SECURITYCHECKPRO_UPDATE_DATABASE_PLUGIN_STATUS');
$spam_protection_plugin_status = $lang->_('COM_SECURITYCHECKPRO_SPAM_PROTECTION_PLUGIN_STATUS');
$logs_status = $lang->_('COM_SECURITYCHECK_LOGS_STATUS');
$autoupdate_status = $lang->_('COM_SECURITYCHECK_AUTOUPDATE_STATUS');
$translator_url = $lang->_('COM_SECURITYCHECK_TRANSLATOR_URL');
if (!file_exists(JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . "language" . DIRECTORY_SEPARATOR . $lang->get("tag") . DIRECTORY_SEPARATOR . $lang->get("tag") . ".com_securitycheck.ini")){
	// No existe traducción
	$translator_name = "<blink>" . $lang->get("name") . " translation is missing.</blink> Please contribute writing this translation. It's easy. Click to see how.";
	$translator_url = "http://securitycheck.protegetuordenador.com/index.php/forum/13-news-and-announcement/4-contribute-send-us-your-translation";
}

JHTML::_('behavior.framework');
JHtml::_('behavior.modal');

// Add style declaration
$media_url = "media/com_securitycheck/stylesheets/cpanelui.css";
JHTML::stylesheet($media_url);

$bootstrap_css = "media/com_securitycheck/stylesheets/bootstrap.min.css";
JHTML::stylesheet($bootstrap_css);

$opa_icons = "media/com_securitycheck/stylesheets/opa-icons.css";
JHTML::stylesheet($opa_icons);

$jquery_meter = "media/com_securitycheck/stylesheets/jquery.percentageloader-0.1.css";
JHTML::stylesheet($jquery_meter);

// Load Javascript
$document = JFactory::getDocument();
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/jquery.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/charisma.js');
// Char libraries
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/excanvas.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/jquery.flot.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/jquery.flot.pie.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/jquery.flot.stack.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/jquery.flot.resize.min.js');
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/bootstrap-tab.js');
//Jquery meter
$document->addScript(rtrim(JURI::base(),'/').'/../media/com_securitycheck/javascript/jquery.percentageloader-0.1.js');

// Url to be used on statistics
$logUrl = 'index.php?option=com_securitycheck&controller=securitycheck&view=logs&datefrom=%s&dateto=%s';
?>



<script type="text/javascript" language="javascript">
		
	window.addEvent('domready', function() {
		//pie chart
		var data = [
		{ label: "A",  data: <?php echo $this->total_firewall_rules; ?>},
		{ label: "B",  data: <?php echo $this->total_blocked_access; ?>},
		{ label: "C",  data: <?php echo $this->total_user_session_protection; ?>}
		];

		if($("#piechart").length)
		{
			$.plot($("#piechart"), data,
			{
				series: {
						pie: {
								show: true
						}
				},
				grid: {
						hoverable: true,
						clickable: true
				},
				legend: {
					show: false
				}
			});
				
			function pieHover(event, pos, obj)
			{
				if (!obj)
						return;
				percent = parseFloat(obj.series.percent).toFixed(2);
				$("#hover").html('<span style="font-weight: bold; color: '+obj.series.color+'">'+obj.series.label+' ('+percent+'%)</span>');
			}
			$("#piechart").bind("plothover", pieHover);
		}
	});
</script>

<script type="text/javascript" language="javascript">

	function Set_Easy_Config() {
		url = 'index.php?option=com_securitycheck&controller=cpanel&format=raw&task=Set_Easy_Config';
		new Request({
			url: url,							
			method: 'GET',
			onSuccess: function(responseText){
				window.location.reload();				
			}
		}).send();
	}
	
	function Set_Default_Config() {
		url = 'index.php?option=com_securitycheck&controller=cpanel&format=raw&task=Set_Default_Config';
		new Request({
			url: url,							
			method: 'GET',
			onSuccess: function(responseText){
				window.location.reload();				
			}
		}).send();
	}
	
</script>

<form action="<?php echo JRoute::_('index.php?option=com_securitycheck');?>" method="post" name="adminForm" id="adminForm">

<div class="securitycheck-bootstrap">
	<div class="row-fluid">
		<div class="box span9">
			<div class="box-header well" data-original-title>
				<i class="icon-tasks"></i><?php echo ' ' . JText::_('COM_SECURITYCHECK_CPANEL_EXTENSION_STATUS'); ?>
				<div class="box-icon">
					<a href="#" class="btn btn-close btn-round"><i class="icon-remove"></i></a>
				</div>
			</div>
						
		<div class="box-content">
		
			<div class="well span3 top-block">
				<?php $enabled = $this->firewall_plugin_enabled; ?>
				<span class="sc-icon32 sc-icon-darkgray sc-icon-globe"></span>
				<div><?php echo($firewall_plugin_status); ?></div>
				<div>
				<?php
						if ($enabled){ ?>
							<span class="label label-success"><?php echo(JText::_( 'COM_SECURITYCHECK_PLUGIN_ENABLED' )); ?></span>
				<?php 	}else{ ?>
							<span class="label label-important"><?php echo(JText::_( 'COM_SECURITYCHECK_PLUGIN_DISABLED' )); ?></span>
				<?php	}  ?>
				</div>
			</div>
			
			<div data-rel="tooltip" title="<?php echo $this->logs_pending . JText::_( 'COM_SECURITYCHECK_UNREAD_LOGS' ); ?>" class="well span3 top-block">
				<span class="sc-icon32 sc-icon-darkgray sc-icon-clipboard"></span>
				<div><?php echo($logs_status); ?></div>
				<div>
				<?php
						if ($this->logs_pending == 0){ ?>
							<span class="label label-success"><?php echo(JText::_( 'COM_SECURITYCHECK_NO_LOGS_PENDING' )); ?></span>
				<?php 	}else{ ?>
							<span class="label label-warning"><?php echo(JText::_( 'COM_SECURITYCHECK_LOGS_PENDING' )); ?></span>
				<?php	}  ?>
				</div>
				<span class="notification"><?php echo($this->logs_pending); ?></span>
			</div>
			
			<div class="well span3 top-block">
				<?php $exists = $this->update_database_plugin_exists; 
					$enabled = $this->update_database_plugin_enabled; 
				?>
				<?php
						if (!$exists) { ?>
							<span class="sc-icon32 sc-icon-black sc-icon-refresh"></span>						
				<?php  } else if ($enabled && $exists){ ?>
							<span class="sc-icon32 sc-icon-green sc-icon-refresh"></span>
				<?php 	}else if (!$enabled && $exists){ ?>
							<span class="sc-icon32 sc-icon-darkgray sc-icon-refresh"></span>
				<?php	}  ?>
				
				<div><?php echo($update_database_plugin_status); ?></div>
				<div>
				<?php
						if (!$exists) { ?>
							<span class="label label-inverse"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_NOT_INSTALLED' )); ?></span>
							
				<?php  } else if ($enabled && $exists) { ?>
							<span class="label label-success"><?php echo(JText::_( 'COM_SECURITYCHECK_PLUGIN_ENABLED' )); ?></span>
				<?php 	}else if (!$enabled && $exists) { ?>
							<span class="label label-important"><?php echo(JText::_( 'COM_SECURITYCHECK_PLUGIN_DISABLED' )); ?></span>
				<?php	}  ?>
				</div>
				<div style="margin-top: 10px;">
				<?php
					if ($enabled && $exists ){ 
				?>
					<button class="btn btn-danger" onclick="Joomla.submitbutton('disable_update_database')" href="#">
						<i class="icon-off icon-white"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE'); ?>
					</button>
				<?php } else if (!$enabled && $exists ) { ?>
					<button class="btn btn-success" onclick="Joomla.submitbutton('enable_update_database')" href="#">
						<i class="icon-ok icon-white"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_ENABLE'); ?>
					</button>
				<?php } else if (!$exists ) { ?>
					<a class="btn btn-info" type="button" href="http://securitycheck.protegetuordenador.com/index.php/our-products/securitycheck-pro-database-update" target="_blank"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
				<?php } ?>
				</div>
			</div>			
			
			<div class="well span3 top-block">
				<?php $exists = $this->spam_protection_plugin_exists; 
					$enabled = $this->spam_protection_plugin_enabled; 
				?>
				<?php
						if (!$exists) { ?>
							<span class="sc-icon32 sc-icon-black sc-icon-user"></span>						
				<?php  } else if ($enabled && $exists){ ?>
							<span class="sc-icon32 sc-icon-green sc-icon-user"></span>
				<?php 	}else if (!$enabled && $exists){ ?>
							<span class="sc-icon32 sc-icon-darkgray sc-icon-user"></span>
				<?php	}  ?>
				
				<div><?php echo($spam_protection_plugin_status); ?></div>
				<div>
				<?php
						if (!$exists) { ?>
							<span class="label label-inverse"><?php echo(JText::_( 'COM_SECURITYCHECKPRO_PLUGIN_NOT_INSTALLED' )); ?></span>
							
				<?php  } else if ($enabled && $exists) { ?>
							<span class="label label-success"><?php echo(JText::_( 'COM_SECURITYCHECK_PLUGIN_ENABLED' )); ?></span>
				<?php 	}else if (!$enabled && $exists) { ?>
							<span class="label label-important"><?php echo(JText::_( 'COM_SECURITYCHECK_PLUGIN_DISABLED' )); ?></span>
				<?php	}  ?>
				</div>
				<div style="margin-top: 10px;">
				<?php
					if ($enabled && $exists ){ 
				?>
					<button class="btn btn-danger" onclick="Joomla.submitbutton('disable_spam_protection')" href="#">
						<i class="icon-off icon-white"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_DISABLE'); ?>
					</button>
				<?php } else if (!$enabled && $exists ) { ?>
					<button class="btn btn-success" onclick="Joomla.submitbutton('enable_spam_protection')" href="#">
						<i class="icon-ok icon-white"> </i>
						<?php echo JText::_('COM_SECURITYCHECKPRO_ENABLE'); ?>
					</button>
				<?php } else if (!$exists ) { ?>
					<a class="btn btn-info" type="button"href="https://securitycheck.protegetuordenador.com/index.php/our-products/securitycheck-spam-protection"  target="_blank"><?php echo JText::_('COM_SECURITYCHECKPRO_MORE_INFO'); ?></a>
				<?php } ?>
				</div>
			</div>
		</div>
			
		<div class="well span5 top-block" id="topLoader">
			<strong><?php echo JText::_( 'COM_SECURITYCHECKPRO_SECURITY_OVERALL_SECURITY_STATUS' ); ?></strong>
			<button class="btn btn-info btn-mini right" type="button" onclick="Joomla.submitbutton('Go_system_info')" href="#"><?php echo JText::_( 'COM_SECURITYCHECKPRO_CHECK_STATUS' ); ?></i></button>
		</div>
		
		<div class="well span3 top-block">
			<?php echo LiveUpdate::getIcon(); ?>
		</div>
		
			<div class="span11 alert alert-warning"><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_HELP'); ?></div>
		</div>
		
		<div class="box span3">
			<div class="box-header well" data-original-title>
				<i class="icon-plus-sign"></i><?php echo ' ' . JText::_('COM_SECURITYCHECK_CPANEL_ADVERTISING'); ?>				
			</div>
			<div class="box-content">
				<div class="alert alert-success" style="text-align: center; font-size:20px; font-weight: bold;">
					<?php echo JText::_('COM_SECURITYCHECK_FREE_VERSION_LINE1') ?>
				</div>
				<h3 align="center" style="color: #FF4500;"><?php echo JText::_('COM_SECURITYCHECK_FREE_VERSION_LINE2') ?></h3>
				<h2 align="center"><?php echo JText::_('COM_SECURITYCHECK_FREE_VERSION_LINE3') ?></h2>
				<hr>
				<p align="center"><a href="https://securitycheck.protegetuordenador.com/index.php/subscriptions/levels" target="_blank" class="btn btn-primary"><?php echo JText::_('COM_SECURITYCHECK_FREE_VERSION_LINE4') ?></a>
			</div>
		</div>
		
	</div>
	
	<div class="row-fluid" id="cpanel">
		<div class="box span6">
			<div class="box-header well" data-original-title>
				<i class="icon-home"></i><?php echo ' ' . JText::_('COM_SECURITYCHECK_CPANEL_MAIN_MENU'); ?>
			</div>
		<div class="box-content">
			<fieldset>
			<legend><?php echo JText::_('COM_SECURITYCHECK_CPANEL_OPTIONS'); ?></legend>
		
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheck&controller=securitycheck&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-check_vuln">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECK_CPANEL_CHECK_VULNERABILITIES_TEXT'); ?></span>
				</a>
			</div>

			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheck&controller=filemanager&view=filemanager&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-file_manager">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECK_CPANEL_FILE_MANAGER_TEXT'); ?></span>
				</a>
			</div>	

			<div class="icon">
				<a href="<?php echo 'index.php?option=com_securitycheck&controller=securitycheck&view=logs'?>">
				<div class="sc-icon-view_logs">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECK_CPANEL_VIEW_FIREWALL_LOGS'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheck&controller=protection&view=protection&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-htaccess_protection">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECK_CPANEL_HTACCESS_PROTECTION_TEXT'); ?></span>
				</a>
			</div>
			
		</fieldset>
		
		<fieldset>
			<legend><?php echo JText::_('COM_SECURITYCHECK_CPANEL_CONFIGURATION'); ?></legend>
		
			<div class="icon">
				<a href="index.php?option=com_config&view=component&component=com_securitycheck&path=&return=<?php echo base64_encode(JURI::getInstance()->toString()) ?>">
				<div class="sc-icon-configuration">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECK_CPANEL_GLOBAL_CONFIGURATION'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $this->sc_plugin_id?>">
				<div class="sc-icon-firewall_config">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECK_CPANEL_FIREWALL_CONFIGURATION'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheck&controller=filemanager&view=sysinfo&'. JSession::getFormToken() .'=1' );
	?>">
				<div class="sc-icon-sysinfo">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECK_CPANEL_SYSINFO_TEXT'); ?></span>
				</a>
			</div>
			
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheck&controller=controlcenter&view=controlcenter&'. JSession::getFormToken() .'=1' );
				?>">
				<div class="sc-icon-controlcenter">&nbsp;</div>
					<span><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_CONTROLCENTER_TEXT'); ?></span>
				</a>
			</div>
		</fieldset>
		
		<fieldset>
			<legend><?php echo JText::_('COM_SECURITYCHECK_CPANEL_TASKS'); ?></legend>
		
			<div class="icon">
				<a href="<?php echo 'index.php?option=com_securitycheck&controller=filemanager&view=initialize_data&'. JSession::getFormToken() .'=1'?>">
				<div class="sc-icon-initialize_data">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECK_CPANEL_INITIALIZE_DATA'); ?></span>
				</a>
			</div>
			<div class="icon">
				<a href="#" onclick="Joomla.submitbutton('Export_config')">
				<div class="sc-icon-export_config">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_EXPORT_CONFIG'); ?></span>
				</a>
			</div>
			<div class="icon">
				<a href="<?php echo JRoute::_( 'index.php?option=com_securitycheck&controller=filemanager&view=upload&'. JSession::getFormToken() .'=1' );?>">
				<div class="sc-icon-import_config">&nbsp;</div>
				<span><?php echo JText::_('COM_SECURITYCHECKPRO_CPANEL_IMPORT_CONFIG'); ?></span>
				</a>
			</div>
		</fieldset>
		</div>
		</div>
		
		<div class="box span3">
			<div class="box-header well" data-original-title>
				<i class="icon-list-alt"></i><?php echo ' ' . JText::_('COM_SECURITYCHECK_CPANEL_STATISTICS'); ?>
				<div class="box-icon">
					<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
					<a href="#" class="btn btn-close btn-round"><i class="icon-remove"></i></a>
				</div>
			</div>
			<div class="box-content">
				<ul class="nav nav-tabs" id="myTab">
					<li class="active"><a href="#historic"><?php echo JText::_('COM_SECURITYCHECK_CPANEL_HISTORIC'); ?></a></li>
					<li><a href="#detail"><?php echo JText::_('COM_SECURITYCHECK_CPANEL_DETAIL'); ?></a></li>
				</ul>
				<div id="myTabContent" class="tab-content">
					<div class="tab-pane active" id="historic">
						<h5 style="text-align: center;"><?php echo JText::_('COM_SECURITYCHECK_GRAPHIC_HEADER'); ?></h5>
						<div id="piechart" style="height:300px"></div>
						<ul class="dashboard-list">
							<li>
								<div class="yellow">
									<span>A</span>
									<?php echo JText::_('COM_SECURITYCHECK_FIREWALL_RULES_APLIED'); ?>
									<?php echo ' (' . $this->total_firewall_rules . ')'; ?>
								</div>
							</li>
							<li>
								<div class="blue">
									<span>B</span>
									<?php echo JText::_('COM_SECURITYCHECK_BLOCKED_ACCESS'); ?>
									<?php echo ' (' . $this->total_blocked_access . ')'; ?>
								</div>
							</li>
							<li>
								<div class="red">
									<span>C</span>
									<?php echo JText::_('COM_SECURITYCHECK_USER_AND_SESSION_PROTECTION'); ?>
									<?php echo ' (' . $this->total_user_session_protection . ')'; ?>
								</div>
							</li>
						</ul>	
					</div>
					<div class="tab-pane" id="detail">
						<table class="table table-striped">
							<thead>
							  <tr>
								  <th><?php echo JText::_('COM_SECURITYCHECK_CPANEL_PERIOD'); ?></th>
								  <th class="center"><?php echo JText::_('COM_SECURITYCHECK_CPANEL_ENTRIES'); ?></th>
							  </tr>
							</thead> 
							<tbody>
								<tr>
									<td>
										<a href="<?php echo sprintf($logUrl, (gmdate('Y')-1).'-01-01 00:00:00', (gmdate('Y')-1).'-12-31 23:59:59')?>">
										<?php echo JText::_('COM_SECURITYCHECK_CPANEL_LAST_YEAR'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->last_year_logs ?></b>
									</td>						
								</tr>
								<tr>
									<td>
										<a href="<?php echo sprintf($logUrl, gmdate('Y').'-01-01', gmdate('Y').'-12-31 23:59:59')?>">
										<?php echo JText::_('COM_SECURITYCHECK_CPANEL_THIS_YEAR'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->this_year_logs ?></b>
									</td>						
								</tr>
								<tr>
									<?php
										$y = gmdate('Y');
										$m = gmdate('m');
										if($m == 1) {
											$m = 12; $y -= 1;
										} else {
											$m -= 1;
										}
										switch($m) {
											case 1: case 3: case 5: case 7: case 8: case 10: case 12:
												$lmday = 31; break;
											case 4: case 6: case 9: case 11:
												$lmday = 30; break;
											case 2:
												if( !($y % 4) && ($y % 400) ) {
													$lmday = 29;
												} else {
													$lmday = 28;
												}
										}
										if($y < 2011) $y = 2011;
										if($m < 1) $m = 1;
										if($lmday < 1) $lmday = 1;
									?>
									<td>
										<a href="<?php echo sprintf($logUrl, $y.'-'.$m.'-01', $y.'-'.$m.'-'.$lmday.' 23:59:59')?>">
										<?php echo JText::_('COM_SECURITYCHECK_CPANEL_LAST_MONTH'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->last_month_logs ?></b>
									</td>						
								</tr>
								<tr>
									<?php
										switch(gmdate('m')) {
											case 1: case 3: case 5: case 7: case 8: case 10: case 12:
												$lmday = 31; break;
											case 4: case 6: case 9: case 11:
												$lmday = 30; break;
											case 2:
												$y = gmdate('Y');
												if( !($y % 4) && ($y % 400) ) {
													$lmday = 29;
												} else {
													$lmday = 28;
												}
										}
										if($lmday < 1) $lmday = 28;
									?>
									<td>
										<a href="<?php echo sprintf($logUrl, gmdate('Y').'-'.gmdate('m').'-01', gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')?>">
										<?php echo JText::_('COM_SECURITYCHECK_CPANEL_THIS_MONTH'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->this_month_logs ?></b>
									</td>						
								</tr>
								<tr>
									<td>
										<a href="<?php echo sprintf($logUrl, gmdate('Y-m-d', time()-7*24*3600), gmdate('Y-m-d 23:59:59'))?>">
										<?php echo JText::_('COM_SECURITYCHECK_CPANEL_LAST_7_DAYS'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->last_7_days ?></b>
									</td>						
								</tr>
								<tr>
									<?php
										$date = new DateTime();
										$date->setDate(gmdate('Y'), gmdate('m'), gmdate('d'));
										$date->modify("-1 day");
										$yesterday = $date->format("Y-m-d");
										$date->modify("+1 day")
									?>
									<td>
										<a href="<?php echo sprintf($logUrl, $yesterday, $date->format("Y-m-d"))?>">
										<?php echo JText::_('COM_SECURITYCHECK_CPANEL_YESTERDAY'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->yesterday ?></b>
									</td>						
								</tr>
								<tr>
									<?php
										$expiry = clone $date;
										$expiry->modify('+1 day');
									?>
									<td>
										<a href="<?php echo sprintf($logUrl, $date->format("Y-m-d"), $expiry->format("Y-m-d"))?>">
										<?php echo JText::_('COM_SECURITYCHECK_CPANEL_TODAY'); ?></a>
									</td>
									<td class="center">
										<b><?php echo $this->today ?></b>
									</td>						
								</tr>
							</tbody>
						</table>
					</div>					
				</div>
			</div>
		</div>
		
		<div class="box span3">
			<div class="box-header well" data-original-title>
				<i class="icon-ok"></i><?php echo ' ' . JText::_('COM_SECURITYCHECK_CPANEL_EASY_CONFIG'); ?>
				<div class="box-icon">
					<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
					<a href="#" class="btn btn-close btn-round"><i class="icon-remove"></i></a>
				</div>
			</div>
			<div class="box-content buttonwrapper">
				<?php $easy_config_applied = $this->easy_config_applied; ?>
				<div class="buttonwrapper"><?php echo JText::_( 'COM_SECURITYCHECK_CPANEL_EASY_CONFIG_STATUS' ); ?></div>
				<?php
						if ($easy_config_applied){ ?>
							<span class="label label-success"><?php echo(JText::_( 'COM_SECURITYCHECK_CPANEL_APPLIED' )); ?></span>
				<?php 	}else{ ?>
							<span class="label label-info"><?php echo(JText::_( 'COM_SECURITYCHECK_CPANEL_NOT_APPLIED' )); ?></span>
				<?php	}  ?>
				<div class="easy_config"><?php echo(JText::_( 'COM_SECURITYCHECK_CPANEL_EASY_CONFIG_DEFINITION' )); ?></div>
				<?php
						if ($easy_config_applied){ ?>
							<button class="btn btn-primary" type="button" onclick="Set_Default_Config();"><?php echo JText::_( 'COM_SECURITYCHECK_CPANEL_APPLY_DEFAULT_CONFIG' ); ?></button>
				<?php 	}else{ ?>
							<button class="btn btn-success" type="button" onclick="Set_Easy_Config();"><?php echo JText::_( 'COM_SECURITYCHECK_CPANEL_APPLY_EASY_CONFIG' ); ?></button>							
						</p>
						<p class="center">
				<?php	}  ?>				
			</div>
		</div>
		
		<div class="box span3">
			<div class="box-header well" data-original-title>
				<i class="icon-thumbs-up"></i><?php echo ' ' . JText::_('COM_SECURITYCHECK_CPANEL_HELP_US'); ?>
				<div class="box-icon">
					<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
					<a href="#" class="btn btn-close btn-round"><i class="icon-remove"></i></a>
				</div>
			</div>
			<div class="box-content">
				<div><?php echo($review); ?></div>
				<div><?php echo('<a href="' . $translator_url . '" target="_blank">' . $translator_name . '</a>'); ?></div>
			</div>
		</div>
		
		<div class="box span3">
			<div class="box-header well" data-original-title>
				<i class="icon-bullhorn"></i><?php echo ' ' . JText::_('COM_SECURITYCHECK_CPANEL_DISCLAIMER'); ?>
				<div class="box-icon">
					<a href="#" class="btn btn-minimize btn-round"><i class="icon-chevron-up"></i></a>
					<a href="#" class="btn btn-close btn-round"><i class="icon-remove"></i></a>
				</div>
			</div>
			<div class="box-content">
				<div class="alert alert-info">
					<p><?php echo JText::_('COM_SECURITYCHECK_CPANEL_DISCLAIMER_TEXT'); ?></p>
				</div>
			</div>			
		</div>
	</div>
</div>

<script>
		$(function() {
			var $topLoader = $("#topLoader").percentageLoader({width: 100, height: 100, controllable : false, progress : 0.5, onProgressUpdate : function(val) {
              $topLoader.setValue(Math.round(val * 100.0));
            }});

			var topLoaderRunning = false;
		  
			// Cuando el DOM esté disponible, actualizamos el porcentaje
			window.addEvent('domready', function() {
				if (topLoaderRunning) {
				  return;
				}
				topLoaderRunning = true;
				$topLoader.setProgress(0);
			   
				var kb = 0;
				// Porcentaje de cumplimiento
				var percent = <?php echo $this->overall ?>;
				
				var animateFunc = function() {
					kb += 5;
					$topLoader.setProgress(kb/100);
								
					if (kb < percent) {
						setTimeout(animateFunc, 55);
					} else {
						topLoaderRunning = false;
					}
				}
				
				setTimeout(animateFunc, 55);
				
			 });
        });      
      </script>

<input type="hidden" name="option" value="com_securitycheck" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="1" />
<input type="hidden" name="controller" value="cpanel" />
</form>