<?php

// no direct access 
defined('_JEXEC') or die('Restricted access');

$total_articles = count($list);
$number_of_article = intval( $params->get( 'number_of_article', 5 ) );
$number_of_article = ( $number_of_article >= $total_articles ) ? $total_articles : $number_of_article;

$more_article_placement = $params->get( 'more_article_placement', 'bottom' );
$number_of_column = intval( $params->get( 'number_of_column', 1 ) );
$number_of_row = intval( $params->get( 'number_of_row', 1 ) );
$number_of_tab = intval( $params->get( 'number_of_tab', 2 ) );
$item_direction = $params->get( 'item_direction', 'across' );

$total_intro_text = ( $number_of_column * $number_of_row ) * $number_of_tab;
$total_intro_text = ( $total_intro_text <= $number_of_article ) ? $total_intro_text : $number_of_article;

if( $total_intro_text < ( ( $number_of_column * $number_of_row ) * $number_of_tab ) && $number_of_tab > 1){
	$number_of_tab = round($total_intro_text / ( $number_of_column * $number_of_row ));
}
$show_more_article = ( $number_of_article > $total_intro_text ) ? true : false; 

/*
if($number_of_column == 1 && $show_more_article && $more_article_placement == 'lastcolumn'){
	$number_of_column = 0;
}elseif($show_more_article && $more_article_placement == 'lastcolumn'){
	$number_of_column = $number_of_column - 1;
}

if($number_of_column == 0){
	if($more_article_placement == 'lastcolumn'){
		if($number_of_row == 1){
			$more_article_placement = 'bottom';
		}else{
			$more_article_placement = 'lastcell';
		}
	}
}elseif($number_of_row == 1){
	if($more_article_placement == 'lastcell'){
		if($number_of_column == 1){
			$more_article_placement = 'bottom';
		}else{
			$more_article_placement = 'lastcolumn';
		}
	}
}
*/

$counting = 0;
$column_percentage = ($number_of_column) ? round((100/$number_of_column),2) : 100;
switch($more_article_placement){
	case 'lastcell':
	case 'lastcolumn':
	case 'bottom':
	break;
	default:
		$more_article_placement = 'bottom';
	break;
}
if($more_article_placement == 'lastcolumn'){
	$column_percentage = round((100/($number_of_column+1)),2);
	$column_percentage_inner = round((100/$number_of_column),2);
}else{
	$column_percentage = round((100/$number_of_column),2);
}

$animate_row_count = 1;
$number_of_row_animate = $number_of_row;
$number_of_row = $number_of_row * $number_of_tab;

$document->addStyleSheet( $modulebase . 'themes/'.$theme.'/assets/css/style.css' );
$document->addScript( $modulebase . 'themes/'.$theme.'/assets/script/script.js' );
$document->addScriptDeclaration( 'var mpfAnimateDiv=\''.$column_percentage*$number_of_column.'\';' );

require dirname(dirname(JModuleHelper::getLayoutPath('mod_minifrontpage'))).TP_DS.'themes'.TP_DS.$theme.TP_DS.'tmpl'.TP_DS.$more_article_placement.'.php';

?>