<?php
/**
* Initialize_data View para el Componente Securitycheck
* @ author Jose A. Luque
* @ Copyright (c) 2011 - Jose A. Luque
* @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
*/
// Chequeamos si el archivo está incluido en Joomla!
defined('_JEXEC') or die();
jimport( 'joomla.application.component.view' );
jimport( 'joomla.plugin.helper' );

/**
* Initialize_data View
*
*/
class SecuritychecksViewInitialize_data extends JViewLegacy{

protected $state;
/**
* Initialize_data view método 'display'
**/
function display($tpl = null)
{

JToolBarHelper::title( JText::_( 'Securitycheck' ).' | ' .JText::_('COM_SECURITYCHECK_INITIALIZE_DATA'), 'securitycheck' );
JToolBarHelper::custom('redireccion_control_panel','arrow-left','arrow-left','COM_SECURITYCHECK_REDIRECT_CONTROL_PANEL');

parent::display($tpl);
}
}