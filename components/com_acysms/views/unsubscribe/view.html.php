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
class unsubscribeViewunsubscribe extends acysmsView
{
	var $ctrl = 'unsubscribe';


	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();

		global $Itemid;
		$this->assignRef('Itemid',$Itemid);

		parent::display($tpl);
	}

	function unsubscribe(){

		global $Itemid;

		$app = JFactory::getApplication();
		$document	= JFactory::getDocument();
		$values = new stdClass();
		$values->show_page_heading = 0;
		$jsite = JFactory::getApplication('site');
		$menus = $jsite->getMenu();
		$menu	= $menus->getActive();

		if(empty($menu) AND !empty($Itemid)){
			$menus->setActive($Itemid);
			$menu	= $menus->getItem($Itemid);
		}

		if(is_object( $menu )) {
			jimport('joomla.html.parameter');
			$menuparams = new acysmsParameter( $menu->params );

			if(!empty($menuparams)){
				$this->assign('introtext',$menuparams->get('introtext'));
				$this->assign('finaltext',$menuparams->get('finaltext'));
				$this->assign('unsubscribetext',$menuparams->get('unsubscribetext'));


				$document = JFactory::getDocument();
				if($menuparams->get('menu-meta_description')) $document->setDescription($menuparams->get('menu-meta_description'));
				if($menuparams->get('menu-meta_keywords')) $document->setMetadata('keywords',$menuparams->get('menu-meta_keywords'));
				if($menuparams->get('robots')) $document->setMetadata('robots',$menuparams->get('robots'));
				if($menuparams->get('page_title')) $document->setTitle($menuparams->get('page_title'));
			}
		}


		$config = ACYSMS::config();
		$this->assignRef('config',$config);
		$countryType = ACYSMS::get('type.country');
		$this->assignRef('countryType',$countryType);

	}
}
