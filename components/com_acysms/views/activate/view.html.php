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
class activateViewActivate extends acysmsView
{
	var $ctrl = 'activate';


	function display($tpl = null)
	{
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();

		parent::display($tpl);
	}

	function activate(){

		$userId =  JRequest::getInt('userId',0);
		$userClass = ACYSMS::get('class.user');
		$user = $userClass->get($userId);

		$countryType = ACYSMS::get('type.country');
		$config = ACYSMS::config();
		$moduleId =  JRequest::getInt('moduleId',0);

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
				$this->assign('activatetext',$menuparams->get('activatetext'));


				$document = JFactory::getDocument();
				if($menuparams->get('menu-meta_description')) $document->setDescription($menuparams->get('menu-meta_description'));
				if($menuparams->get('menu-meta_keywords')) $document->setMetadata('keywords',$menuparams->get('menu-meta_keywords'));
				if($menuparams->get('robots')) $document->setMetadata('robots',$menuparams->get('robots'));
				if($menuparams->get('page_title')) $document->setTitle($menuparams->get('page_title'));
			}
		}


		$this->assignRef('config',$config);
		$this->assignRef('countryType',$countryType);
		$this->assignRef('user',$user);
		$this->assignRef('moduleId',$moduleId);

	}
}
