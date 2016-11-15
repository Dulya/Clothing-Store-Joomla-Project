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

class ACYSMSeditorHelper{

	var $width = '95%';

	var $height = '600';

	var $cols = 100;

	var $rows = 30;

	var $editor = null;

	var $name = '';

	var $content = '';

	var $editorConfig = array();

	var $editorContent = '';

	function __construct(){

		$config = ACYSMS::config();
		$this->editor = $config->get('editor',null);
		if(empty($this->editor)) $this->editor = null;

		$this->myEditor = JFactory::getEditor($this->editor);
		$this->myEditor->initialise();

		$name = $this->myEditor->get('_name');
		if(ACYSMS_J16){
			if($name == 'tinymce'){
				$this->editorConfig['extended_elements'] = 'table[background|cellspacing|cellpadding|width|align|bgcolor|border|style|class|id],tr[background|width|bgcolor|style|class|id|valign],td[background|width|align|bgcolor|valign|colspan|rowspan|height|style|class|id|nowrap]';
			}
		}
	}

	function prepareDisplay(){
		$this->content = htmlspecialchars($this->content, ENT_COMPAT, 'UTF-8');
		ob_start();
		if(!ACYSMS_J16){
			echo $this->myEditor->display( $this->name,  $this->content ,$this->width, $this->height, $this->cols, $this->rows,array('pagebreak', 'readmore'),$this->editorConfig ) ;
		}else{
			echo $this->myEditor->display( $this->name,  $this->content ,$this->width, $this->height, $this->cols, $this->rows,array('pagebreak', 'readmore'),null,'com_content',null,$this->editorConfig ) ;
		}

		$this->editorContent = ob_get_clean();
	}

	function display(){
		if(empty($this->editorContent)) $this->prepareDisplay();
		return $this->editorContent;
	}


	function setDescription(){
		$this->width = 700;
		$this->height = 200;
		$this->cols = 80;
		$this->rows = 10;
	}

	function jsCode(){
		return $this->myEditor->save( $this->name );
	}

}//endclass
