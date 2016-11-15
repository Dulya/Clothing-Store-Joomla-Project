<?php
/**
* Securitycheck Pro Control Panel View para el Componente Securitycheckpro
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Protect from unauthorized access
defined('_JEXEC') or die('Restricted Access');

// Load framework base classes
jimport('joomla.application.component.view');

/**
 * Securitycheck Pro Control Panel view class
 *
 */
class SecuritychecksViewCpanel extends JViewLegacy
{
	function display($tpl = NULL)
	{
		JToolBarHelper::title( JText::_( 'Securitycheck' ).' | ' .JText::_('COM_SECURITYCHECK_CONTROLPANEL'), 'securitycheck' );
		
		
		// Obtenemos los datos del modelo...
		$model = $this->getModel();
		$firewall_plugin_enabled = $model->PluginStatus(1);
		$update_database_plugin_enabled = $model->PluginStatus(3);
		$update_database_plugin_exists = $model->PluginStatus(4);
		$spam_protection_plugin_enabled = $model->PluginStatus(5);
		$spam_protection_plugin_exists = $model->PluginStatus(6);
		$logs_pending = $model->LogsPending();
		$sc_plugin_id = $model->get_plugin_id(1);
		$params = JComponentHelper::getParams('com_securitycheck');
		// ... y el tipo de servidor web
		$mainframe = JFactory::getApplication();
		$server = $mainframe->getUserState("server",'apache');
		// ... y las estadísticas de los logs
		$last_year_logs = $model->LogsByDate('last_year');
		$this_year_logs = $model->LogsByDate('this_year');
		$last_month_logs = $model->LogsByDate('last_month');
		$this_month_logs = $model->LogsByDate('this_month');
		$last_7_days = $model->LogsByDate('last_7_days');
		$yesterday = $model->LogsByDate('yesterday');
		$today = $model->LogsByDate('today');
		$total_firewall_rules = $model->LogsByType('total_firewall_rules');
		$total_blocked_access = $model->LogsByType('total_blocked_access');
		$total_user_session_protection = $model->LogsByType('total_user_session_protection');
		$easy_config_applied = $model->Get_Easy_Config();
		
		// Obtenemos el status de la seguridad
		require_once JPATH_ROOT.'/administrator/components/com_securitycheck/models/sysinfo.php';
		$overall = new SecuritychecksModelSysinfo();
		$overall = $overall->getInfo();		
		$overall = $overall['overall_joomla_configuration'];
		
		// Ponemos los datos en el template
		$this->assignRef('firewall_plugin_enabled', $firewall_plugin_enabled);
		$this->assignRef('update_database_plugin_enabled', $update_database_plugin_enabled);
		$this->assignRef('update_database_plugin_exists', $update_database_plugin_exists);
		$this->assignRef('spam_protection_plugin_enabled', $spam_protection_plugin_enabled);
		$this->assignRef('spam_protection_plugin_exists', $spam_protection_plugin_exists);
		$this->assignRef('logs_pending', $logs_pending);
		$this->assignRef('sc_plugin_id', $sc_plugin_id);
		$this->assignRef('server', $server);
		$this->assignRef('last_year_logs', $last_year_logs);
		$this->assignRef('this_year_logs', $this_year_logs);
		$this->assignRef('last_month_logs', $last_month_logs);
		$this->assignRef('this_month_logs', $this_month_logs);
		$this->assignRef('last_7_days', $last_7_days);
		$this->assignRef('yesterday', $yesterday);
		$this->assignRef('today', $today);
		$this->assignRef('total_firewall_rules', $total_firewall_rules);
		$this->assignRef('total_blocked_access', $total_blocked_access);
		$this->assignRef('total_user_session_protection', $total_user_session_protection);
		$this->assignRef('easy_config_applied', $easy_config_applied);
		$this->assignRef('overall', $overall);
				
		parent::display();
	}
}