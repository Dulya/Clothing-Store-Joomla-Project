<?php
/**
* Securitychecks View para el Componente Securitycheck
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/

// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
/**
* Securitychecks View
*
*/
class SecuritychecksViewSecuritychecks extends JViewLegacy
{
/**
* Securitychecks view método 'display'
**/
function display($tpl = null)
{
JToolBarHelper::title( JText::_( 'Securitycheck' ).' | ' .JText::_('COM_SECURITYCHECK_VULNERABILITIES'), 'securitycheck' );
JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_CONTROL_PANEL');
JToolBarHelper::custom('redireccion_system_info','arrow-left','arrow-left','COM_SECURITYCHECKPRO_REDIRECT_SYSTEM_INFO');

// Obtenemos los datos del modelo...
$model = $this->getModel();
$update_database_plugin_enabled = $model->PluginStatus(3);
$update_database_plugin_exists = $model->PluginStatus(4);
$last_check = $model->get_campo_bbdd('securitycheckpro_update_database','last_check');
$database_version = $model->get_campo_bbdd('securitycheckpro_update_database','version');
$database_message = $model->get_campo_bbdd('securitycheckpro_update_database','message');
if ( $update_database_plugin_exists ) {
	$plugin_id = $model->get_plugin_id(1);
}

// Obtenemos los datos del modelo
$items = $this->get('Data');
$pagination = $this->get('Pagination');
$eliminados = JRequest::getVar('comp_eliminados');
$core_actualizado = JRequest::getVar('core_actualizado');
$comps_actualizados = JRequest::getVar('componentes_actualizados');
$comp_ok = JRequest::getVar('comp_ok');
$new_versions = JRequest::getVar('new_versions');
$plugin_enabled = JRequest::getVar('plugin_enabled');
$logs_pending = JRequest::getVar('logs_pending');

// Ponemos los datos y la paginación en el template
$this->assignRef('items', $items);
$this->assignRef('pagination', $pagination);
$this->assignRef('eliminados', $eliminados);
$this->assignRef('core_actualizado', $core_actualizado);
$this->assignRef('comps_actualizados', $comps_actualizados);
$this->assignRef('comp_ok', $comp_ok);
$this->assignRef('new_versions', $new_versions);
$this->assignRef('plugin_enabled', $plugin_enabled);
$this->assignRef('logs_pending', $logs_pending);
$this->assignRef('update_database_plugin_exists', $update_database_plugin_exists);
$this->assignRef('update_database_plugin_enabled', $update_database_plugin_enabled);
$this->assignRef('last_check', $last_check);
$this->assignRef('database_version', $database_version);
$this->assignRef('database_message', $database_message);
if ( $update_database_plugin_exists ) {
	$this->assignRef('plugin_id', $plugin_id);
}

parent::display($tpl);
}
}