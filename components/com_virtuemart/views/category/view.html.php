<?php
/**
*
* Handle the category view
*
* @package	VirtueMart
* @subpackage
* @author RolandD
* @link http://www.virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 9288 2016-09-12 15:20:56Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmView'))require(VMPATH_SITE.DS.'helpers'.DS.'vmview.php');

/**
* Handle the category view
*
* @package VirtueMart
* @author RolandD
* @todo set meta data
* @todo add full path to breadcrumb
*/
class VirtuemartViewCategory extends VmView {

	public function display($tpl = null) {

		$this->show_prices  = (int)VmConfig::get('show_prices',1);

		if(!class_exists('calculationHelper')) require(VMPATH_ADMIN.DS.'helpers'.DS.'calculationh.php');
		if (!class_exists('CurrencyDisplay'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		if(!class_exists('shopFunctionsF'))require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');

		$document = JFactory::getDocument();

		$this->app = JFactory::getApplication();
		$pathway = $this->app->getPathway();

		if (!class_exists('VmImage'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'image.php');

		// set search and keyword
		if ($keyword = vRequest::getString('keyword', false)){//uword('keyword', false, ' ,-,+,.,_')) {
			$pathway->addItem($keyword);
			//$title .=' ('.$keyword.')';
		}

		if( ShopFunctionsF::isFEmanager('product.edit') ){
			$add_product_link = JURI::root() . 'index.php?option=com_virtuemart&tmpl=component&view=product&task=edit&virtuemart_product_id=0&manage=1' ;
			$add_product_link = $this->linkIcon($add_product_link, 'COM_VIRTUEMART_PRODUCT_FORM_NEW_PRODUCT', 'edit', false, false);
		} else {
			$add_product_link = "";
		}
		$this->assignRef('add_product_link', $add_product_link);

		$menus	= $this->app->getMenu();
		$menu = $menus->getActive();

		if(!empty($menu->id)){
			$itemId = $menu->id;
		} else {

			$itemId = vRequest::getInt('Itemid',false);
			if($itemId){
				$menus->setActive($itemId);
				$menu = $menus->getActive();
				vmdebug('$itemId by request setActive',$itemId);
			}

		}
		ShopFunctionsF::setLastVisitedItemId($itemId);
		$this->Itemid = $itemId;

		$virtuemart_manufacturer_id = vRequest::getInt('virtuemart_manufacturer_id', -1 );
		if($virtuemart_manufacturer_id ===-1 and !empty($menu->query['virtuemart_manufacturer_id'])){
			$virtuemart_manufacturer_id = $menu->query['virtuemart_manufacturer_id'];
			vRequest::setVar('virtuemart_manufacturer_id',$virtuemart_manufacturer_id);
		}

		$this->categoryId = vRequest::getInt('virtuemart_category_id', -1);
		if($this->categoryId === -1 and !empty($menu->query['virtuemart_category_id'])){
			$this->categoryId = $menu->query['virtuemart_category_id'];
			vRequest::setVar('virtuemart_category_id',$this->categoryId);
		} else if ( $this->categoryId === -1 and $virtuemart_manufacturer_id === -1){
			$this->categoryId = ShopFunctionsF::getLastVisitedCategoryId();
		}

		$this->setCanonicalLink($tpl,$document,$this->categoryId,$virtuemart_manufacturer_id);

		if (($this->categoryId === -1 or $this->categoryId === 0 ) and $virtuemart_manufacturer_id){
			$this->categoryId = 0;
			$catType = 'manufacturer';
			$this->setCanonicalLink($tpl,$document,$virtuemart_manufacturer_id,$catType);
		}

		$categoryModel = VmModel::getModel('category');
		$productModel = VmModel::getModel('product');

		if($this->categoryId===-1) $this->categoryId = 0;

		$category = $categoryModel->getCategory($this->categoryId);

		$this->searchcustom = '';
		$this->searchCustomValues = '';

		if(!isset($menu->query['showsearch'])) $menu->query['showsearch'] = VmConfig::get ('showsearch', false);
		$this->showsearch = vRequest::getInt('showsearch',$menu->query['showsearch']);

		if($keyword or $this->showsearch){
			vmSetStartTime('getSearchCustom');
			$customfields = vRequest::getString('customfields');
			$app = JFactory::getApplication();

			if(is_array($customfields)){
				foreach($customfields as $k=>$f){
					if(empty($f)) $f='';
					$app->setUserState('com_virtuemart.customfields.'.trim($k),$f);
				}
			}
			vmTime('getSearchCustom after setUserState','getSearchCustom');
			$this->getSearchCustom();
			vmTime('getSearchCustom End','getSearchCustom');
			$this->searchAllCats = $app->getUserStateFromRequest('com_virtuemart.customfields.searchAllCats','searchAllCats',false);
			//$app->setUserState('com_virtuemart.customfields.searchAllCats',$f);

		} else {
			$app = JFactory::getApplication();
			$app->setUserState('com_virtuemart.customfields','');
		}

		$this->assignRef('keyword', $keyword);

		if(!isset($menu->query['showproducts'])) $menu->query['showproducts'] = 1;
		$this->showproducts = vRequest::getInt('showproducts',$menu->query['showproducts']);

		if(!empty($category)){

			$vendorId = $category->virtuemart_vendor_id;


			$ratingModel = VmModel::getModel('ratings');
			$productModel->withRating = $this->showRating = $ratingModel->showRating();

			$this->products = false;

			if(!empty($menu->query['products_per_row'])){
				$this->perRow = $menu->query['products_per_row'];
			} else {
				$this->perRow = empty($category->products_per_row)? VmConfig::get('products_per_row',3):$category->products_per_row;
			}

			$imgAmount = VmConfig::get('prodimg_browse',1);

			$dynamic = vRequest::getInt('dynamic',false);

			if(!$dynamic){
				$opt = array('featured','latest','topten','recent');
				foreach($opt as $o){

					//Lets check, if we use the new Frontpages settings
					if(!empty($menu->query[$o]) and !empty($menu->query[$o.'_rows'])){

						$this->products[$o] = $productModel->getProductListing($o, $this->perRow * $menu->query[$o.'_rows']);
						$productModel->addImages($this->products[$o],$imgAmount);
					}
				}
			}


			$this->vmPagination = '';
			$this->orderByList = '';


			if($this->showproducts){

				if ($dynamic) {

					$id = vRequest::getInt('virtuemart_product_id',false);
					$p = $productModel->getProduct ($id);
					$pa = array();
					$pa[] = $p;
					$this->products['0'][] = $p;

				} else {
					// Load the products in the given category
					$ids = $productModel->sortSearchListQuery (TRUE, $this->categoryId);
					$this->vmPagination = $productModel->getPagination($this->perRow);
					$this->orderByList = $productModel->getOrderByList($this->categoryId);
					$this->products['0'] = $productModel->getProducts ($ids);
				}

				$productModel->addImages($this->products['0'], $imgAmount );

			}

			if ($this->products) {
				$this->currency = CurrencyDisplay::getInstance( );
				$display_stock = VmConfig::get('display_stock',1);
				$showCustoms = VmConfig::get('show_pcustoms',1);
				if($display_stock or $showCustoms){

					if(!$showCustoms){
						foreach($this->products as $pType => $productSeries){
							foreach($productSeries as $i => $productItem){
								$this->products[$pType][$i]->stock = $productModel->getStockIndicator($productItem);
							}
						}
					} else {
						if (!class_exists ('vmCustomPlugin')) {
							require(JPATH_VM_PLUGINS . DS . 'vmcustomplugin.php');
						}
						//vmdebug('My products ',$this->products);
						foreach($this->products as $pType => $productSeries) {
							shopFunctionsF::sortLoadProductCustomsStockInd($this->products[$pType],$productModel);
						}
					}
				}
			}

			// Add feed links
			if ($this->products  && VmConfig::get('feed_cat_published', 0)==1) {
				$link = '&format=feed&limitstart=';
				$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
				$document->addHeadLink(JRoute::_($link . '&type=rss', FALSE), 'alternate', 'rel', $attribs);
				$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
				$document->addHeadLink(JRoute::_($link . '&type=atom', FALSE), 'alternate', 'rel', $attribs);
			}

			$this->showBasePrice = (vmAccess::manager() or vmAccess::isSuperVendor());



			//No redirect here, for category id = 0 means show ALL categories! note by Max Milbers
			if ((!empty($this->categoryId) and $this->categoryId!==-1 ) and (empty($category->slug) or !$category->published)) {

				$this->handle404();
			}

			shopFunctionsF::setLastVisitedCategoryId($this->categoryId);
			shopFunctionsF::setLastVisitedManuId($virtuemart_manufacturer_id);

			// Add the category name to the pathway
			if ($category->parents) {
				foreach ($category->parents as $c){
					$pathway->addItem(strip_tags(vmText::_($c->category_name)),JRoute::_('index.php?option=com_virtuemart&view=category&virtuemart_category_id='.$c->virtuemart_category_id, FALSE));
				}
			}
			$catImgAmount = VmConfig::get('catimg_browse',1);
			$categoryModel->addImages($category,$catImgAmount);

			if(!isset($menu->query['showcategory'])) $menu->query['showcategory'] = VmConfig::get ('showCategory', 1);
			$this->showcategory = vRequest::getInt('showcategory',$menu->query['showcategory']);
			//$this->showcategory = vRequest::getInt('showcategory',true);
			if($this->showcategory){
			//if($category->category_layout == 'categories' or ($this->categoryId >0 and $virtuemart_manufacturer_id <1)){
				$category->children = $categoryModel->getChildCategoryList( $vendorId, $this->categoryId, $categoryModel->getDefaultOrdering(), $categoryModel->_selectedOrderingDir );
				$categoryModel->addImages($category->children,$catImgAmount);
			} else {
				$category->children = false;
			}

			if (VmConfig::get('enable_content_plugin', 0)) {
				shopFunctionsF::triggerContentPlugin($category, 'category','category_description');
			}

			$metadesc = '';
			$metakey = '';
			$metarobot = '';

			if(isset($menu->params)){
				$metadesc = $menu->params->get('menu-meta_description');
				$metakey = $menu->params->get('menu-meta_keywords');
				$metarobot = $menu->params->get('robots');
			}

			if ($category->metadesc) {
				$metadesc = $category->metadesc;
			}
			if ($category->metakey) {
				$metakey = $category->metakey;
			}
			if ($category->metarobot) {
				$metarobot = $category->metarobot;
			}

			$document->setDescription( $metadesc );
			$document->setMetaData('keywords', $metakey);
			$document->setMetaData('robots', $metarobot);

			if ($this->app->getCfg('MetaAuthor') == '1' and !empty($category->metaauthor)) {
				$document->setMetaData('author', $category->metaauthor);
			}

			if(empty($category->category_template)){
				$category->category_template = VmConfig::get('categorytemplate');
			}

			if(!empty($menu->query['categorylayout'])){
			//if(!empty($menu->query['categorylayout']) and $menu->query['virtuemart_category_id']==$this->categoryId){
				$category->category_layout = $menu->query['categorylayout'];
			}

			vmJsApi::jPrice();

			$productsLayout = VmConfig::get('productsublayout','products');
			if(empty($productsLayout)) $productsLayout = 'products';
			$this->productsLayout = empty($menu->query['productsublayout'])? $productsLayout:$menu->query['productsublayout'];

			shopFunctionsF::setVmTemplate($this,$category->category_template,0,$category->category_layout);
		} else {
			//Backward compatibility
			if(!isset($category)) {
				$category = new stdClass();
				$category->category_name = '';
				$category->category_description= '';
				$category->haschildren= false;
			}
		}

		$this->assignRef('category', $category);

	    // Set the titles
		if (!empty($category->customtitle)) {
        	$title = strip_tags($category->customtitle);
     	} elseif (!empty($category->category_name)) {
     		$title = strip_tags($category->category_name);
		} else {
			$title = $this->setTitleByJMenu();
		}

		$title = vmText::_($title);

	  	if(vRequest::getInt('error')){
			$title .=' '.vmText::_('COM_VIRTUEMART_PRODUCT_NOT_FOUND');
		}
		if(!empty($keyword)){
			$title .=' ('.strip_tags(htmlspecialchars_decode($keyword)).')';
		}

		if ($virtuemart_manufacturer_id>0 and !empty($this->products['0'])){

			if (!empty($this->products['0'][0])) $title .=' '.$this->products['0'][0]->mf_name ;
			// Override Category name when viewing manufacturers products !IMPORTANT AFTER page title.
			if (!empty($this->products['0'][0]) and isset($category->category_name)) $category->category_name = $this->products['0'][0]->mf_name ;

		}

		$document->setTitle( $title );
		if ($this->app->getCfg('MetaTitle') == '1') {
			$document->setMetaData('title',  $title);
		}

		//Fallback for older layouts, will be removed vm3.2
		$this->fallback=false;
		if(count($this->products)===1 and isset($this->products['0'])){
			$this->products = $this->products['0'];
			$this->fallback=true;
			vmdebug('Fallback active');
		}

		if (VmConfig::get ('jdynupdate', TRUE)) {
			vmJsApi::jDynUpdate();
		}

		parent::display($tpl);
	}

	public function setTitleByJMenu(){
		$menus	= $this->app->getMenu();
		$menu = $menus->getActive();

		$title = 'VirtueMart Category View';
		if ($menu) $title = $menu->title;
		// $title = $this->params->get('page_title', '');
		// Check for empty title and add site name if param is set
		if (empty($title)) {
			$title = $this->app->getCfg('sitename');
		}
		elseif ($this->app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = vmText::sprintf('JPAGETITLE', $this->app->getCfg('sitename'), $title);
		}
		elseif ($this->app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = vmText::sprintf('JPAGETITLE', $title, $this->app->getCfg('sitename'));
		}
		return $title;
	}

	public function setCanonicalLink($tpl,$document,$categoryId,$manId){
		// Set Canonic link
		if (!empty($tpl)) {
			$format = $tpl;
		} else {
			$format = vRequest::getCmd('format', 'html');
		}
		if ($format == 'html') {

			// remove joomla canonical before adding it
			foreach ( $document->_links as $k => $array ) {
				if ( $array['relation'] == 'canonical' ) {
					unset($document->_links[$k]);
					break;
				}
			}

			$link = 'index.php?option=com_virtuemart&view=category';
			if($categoryId!==-1){
				$link .= '&virtuemart_category_id='.$categoryId;
			}
			if($manId!==-1 and !empty($manId)){
				$link .= '&virtuemart_manufacturer_id='.$manId;
			}

			$document->addHeadLink( JUri::getInstance()->toString(array('scheme', 'host', 'port')).JRoute::_($link, FALSE) , 'canonical', 'rel', '' );

		}
	}

	/*
	 * generate custom fields list to display as search in FE
	 */
	public function getSearchCustom() {

		$emptyOption  = array('virtuemart_custom_id' =>'', 'custom_title' => vmText::_('COM_VIRTUEMART_LIST_EMPTY_OPTION'));
		$this->custom_parent_id = vRequest::getInt('custom_parent_id', 0);
		$this->searchCustomList = '';

		$db =JFactory::getDBO();

		$q1= 'SELECT c.* FROM #__virtuemart_customs  as c ';
		if(!empty($this->categoryId)){
			$q1 .= 'INNER JOIN #__virtuemart_product_customfields as pc on (c.virtuemart_custom_id=pc.virtuemart_custom_id)
INNER JOIN #__virtuemart_product_categories as cat ON (pc.virtuemart_product_id=cat.virtuemart_product_id)';
		}
		$q1 .= ' WHERE';
		if(!empty($this->categoryId)){
			$q1 .= ' virtuemart_category_id="'.$this->categoryId.'" and';
		}
		$q1 .= ' searchable="1" and (field_type="S" or field_type="P") GROUP BY c.virtuemart_custom_id';

		$db->setQuery($q1);
		$this->selected = $db->loadObjectList();
		//vmdebug('getSearchCustom '.str_replace('#__',$db->getPrefix(),$db->getQuery()),$this->selected);//,$this->categoryId,$this->selected);
		if($this->selected) {
			$app = JFactory::getApplication();
			foreach ($this->selected as $selected) {
				$valueOptions = array();
				if($selected->field_type=="S") {

					//if($selected->is_list) {
						//if($selected->is_list == "1") {
						$q2= 'SELECT pc.* FROM #__virtuemart_product_customfields  as pc ';
						$q2 .= 'INNER JOIN #__virtuemart_products as p on (pc.virtuemart_product_id=p.virtuemart_product_id)';
						if(!empty($this->categoryId)){
							$q2 .= 'INNER JOIN #__virtuemart_product_categories as cat on (pc.virtuemart_product_id=cat.virtuemart_product_id)';
						}
						$q2 .= ' WHERE virtuemart_custom_id="'.$selected->virtuemart_custom_id.'" and p.published="1" ';
						if(!empty($this->categoryId)){
							$q2 .= ' and virtuemart_category_id="'.$this->categoryId.'" ';
						}
						$q2 .= ' GROUP BY `customfield_value`';

						/*$q2 = 'SELECT * FROM `#__virtuemart_product_customfields` WHERE virtuemart_custom_id="'.$selected->virtuemart_custom_id.'" ';
						if(!empty($this->categoryId)){
							$q1 .= ' virtuemart_category_id="'.$this->categoryId.'" and';
						}
						$q2 = 'GROUP BY `customfield_value` ';*/
						$db->setQuery( $q2 );
						$Opts = $db->loadObjectList();
						//vmdebug('getSearchCustom my  q2 '.str_replace('#__',$db->getPrefix(),$db->getQuery()) );
						if($Opts){
							foreach( $Opts as $k => $v ) {
								if(!isset($valueOptions[$v->customfield_value])) {
									$valueOptions[$v->customfield_value] = $v->customfield_value;
								}
							}
							$valueOptions = array_merge(array($emptyOption), $valueOptions);
							$v = $app->getUserStateFromRequest ('com_virtuemart.customfields.'.$selected->virtuemart_custom_id, 'customfields['.$selected->virtuemart_custom_id.']', '', 'string');
							$this->searchCustomValues .= '<div class="vm-search-custom-values-group"><div class="vm-custom-title-select">' .  vmText::_( $selected->custom_title ).'</div>'.JHtml::_( 'select.genericlist', $valueOptions, 'customfields['.$selected->virtuemart_custom_id.']', 'class="inputbox vm-chzn-select changeSendForm"', 'virtuemart_custom_id', 'custom_title', $v ) . '</div>';
						}

						//vmdebug('getSearchCustom '.$q2,$Opts,$valueOptions);
						/*} else if($selected->is_list == "2" and !empty($selected->custom_value)) {
							$valueOptions = array();
							$Opts = explode( ';', $selected->custom_value );
							foreach( $Opts as $k => $v ) {
								$valueOptions[$v] = $v;
							}
						}*/

				} else if($selected->field_type=="P"){
					$v = vRequest::getString('customfields['.$selected->virtuemart_custom_id.']');
					$n = 'customfields['.$selected->virtuemart_custom_id.']';
					$this->searchCustomValues .= vmText::_( $selected->custom_title ).' <input name="'.$n.'" class="inputbox vm-chzn-select" type="text" size="20" value="'.$v.'"/>';
				} else {
				//Atm not written for other field types
				/*	$db->setQuery('SELECT `customfield_value` as virtuemart_custom_id,`custom_value` as custom_title FROM `#__virtuemart_product_customfields` WHERE virtuemart_custom_id='.$selected->virtuemart_custom_id);
					$valueOptions= $db->loadAssocList();

					$valueOptions = array_merge(array($emptyOption), $valueOptions);
					$this->searchCustomValues .= '<div class="vm-search-custom-values-group"><div class="vm-search-title">'. vmText::_($selected->custom_title).'</div> '.JHtml::_('select.genericlist', $valueOptions, 'customfields['.$selected->virtuemart_custom_id.']', 'class="inputbox vm-chzn-select"', 'virtuemart_custom_id', 'custom_title', 0) . '</div>';*/

				}
			}
			$this->searchCustomValues .= '<div class="clear"></div>';
		}

		if(VmConfig::get('useCustomSearchTrigger',false)){
			// add search for declared plugins
			JPluginHelper::importPlugin('vmcustom');
			$dispatcher = JDispatcher::getInstance();
			$plgDisplay = $dispatcher->trigger('plgVmSelectSearchableCustom',array( &$this->options,&$this->searchCustomValues,$this->custom_parent_id ) );
		}
		//vmTime('getSearchCustom after trigger','getSearchCustom');
		vmJsApi::chosenDropDowns();
	}

	public function handle404($cat = false){
		if(!$cat or empty($cat->slug)){
			vmInfo(vmText::_('COM_VIRTUEMART_CAT_NOT_FOUND'));
		} else {
			if($cat->virtuemart_id!==0 and !$cat->published){
				vmInfo('COM_VIRTUEMART_CAT_NOT_PUBL',$cat->category_name,$this->categoryId);
			}
		}

		//Fallback
		$catLink = '';
		if ($cat and !empty($cat->category_parent_id)) {
			$catLink = '&view=category&virtuemart_category_id=' .$cat->category_parent_id;
		} else {
			$last_category_id = shopFunctionsF::getLastVisitedCategoryId();
			if (!$last_category_id or $this->categoryId == $last_category_id) {
				$last_category_id = vRequest::getInt('virtuemart_category_id', false);
			}
			if ($last_category_id and $this->categoryId != $last_category_id) {
				$catLink = '&view=category&virtuemart_category_id=' . $last_category_id;
			}
		}

		if ((int)VmConfig::get('handle_404',1)) {
			$this->app->redirect(JRoute::_('index.php?option=com_virtuemart' . $catLink . '&error=404', FALSE));
		} else {
			JError::raise(E_ERROR,'404','Not found');
		}

		return;
	}
}


//no closing tag