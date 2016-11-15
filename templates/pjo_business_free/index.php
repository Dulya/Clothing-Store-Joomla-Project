<?php
/**
* @author    pickjoomla.com http://www.pickjoomla.com
* @copyright copyright (c) 2016 pickjoomla.com. all rights reserved
* @license   GNU/GPL v2 http://www.gnu.org/licenses/gpl-2.0.html
*/
defined('_JEXEC') or die('Restricted index access');
$root					= $this->baseurl;
$template				= $this->template;
$slideshow				= 'templates/'.$template.'/images/slideshow/';
$images					= 'templates/'.$template.'/images/';
$css					= ''.$root.'/templates/'.$template.'/css/';

$app											= JFactory::getApplication();
$doc											= JFactory::getDocument();
$user											= JFactory::getUser();
$this->language									= $doc->language;
$this->direction								= $doc->direction;
$sitename                                       = $app->getCfg('sitename');
$menuid                                         = $this->params->get('menuid');
$menu                                           = $app->getMenu();
$renderer	       = $doc->loadRenderer( 'module' );
$module            = JModuleHelper::getModule( 'mod_menu', "hornav_menu" );
$menu_name		   = $this->params->get("hornav_menu", "mainmenu");
$module->params    = "menutype=$menu_name\nshowAllChildren=1";
$hornav = $renderer->render( $module);



$params = $app->getTemplate(true)->params;
if ($this->params->get('logoFile')) {
$logo = '<img src="'. JURI::root() . $this->params->get('logoFile') .'" alt="'. $sitename .'" />';
}
elseif ($this->params->get('sitetitle'))
{
$logo = '<span class="site-title" title="'. $sitename .'">'. htmlspecialchars($this->params->get('sitetitle')) .'</span>';
}
else
{
$logo = '<img src="'. $images .'/logo.png" alt="'. $sitename .'" />';
}
$display_social_links           = $this->params->get('display_social_links', 'yes');
$header_top_pos					= (($this->countModules('header_top1 or header_top2')) or ($logo));
$header_top1                    = $this->countModules('header_top1');
$header_top2                    = $this->countModules('header_top2');
$top_pos						= $this->countModules('top1 or top2 or top3 or top4');
$top1_position                  = $this->countModules('top1');
$top2_position                  = $this->countModules('top2');
$top3_position                  = $this->countModules('top3');
$top4_position                  = $this->countModules('top4');
$navigation_position            = $this->countModules('top_menu');
$bottom_pos						= $this->countModules('bottom1 or bottom2 or bottom3 or bottom4');
$bottom1_position               = $this->countModules('bottom1');
$bottom2_position               = $this->countModules('bottom2');
$bottom3_position               = $this->countModules('bottom3');
$bottom4_position               = $this->countModules('bottom4');
$footer_pos						= $this->countModules('footer1 or footer2 or footer3 or footer4'); 
$cp = 'Designed by <a href="http://www.pickjoomla.com" 
target="_blank" title="www.pickjoomla.com">pickjoomla.com</a>';
$comp_info                      = $this->params->get('comp_info');
$footer1_position               = $this->countModules('footer1');
$footer2_position               = $this->countModules('footer2');
$footer3_position               = $this->countModules('footer3');
$footer4_position               = $this->countModules('footer4');
$footer_position                = $this->countModules('footer');
$maxWidth						= $this->params->get('maxWidth');
$thumbs_wrapper_width							= '100';																 
$count_images                                   = $this->params->get('count_images');	
$thumbs_margin									= $count_images * 0.98;
$thumbs_padding									= $count_images * 0.58;												  
$thumbs_total_margin_padding					= $thumbs_margin + $thumbs_padding;	
$nav_bg_image_default                           = $this->params->get('nav_bg_image_default');
$nav_bg_image_file                              = $this->params->get('nav_bg_image_file');
$menu_text_transform                            = $this->params->get('menu_text_transform','inherit');
$nagivation_font_weight                         = $this->params->get('nagivation_font_weight','normal');
$CssStyle                                       = $this->params->get('CssStyle', 'default');
$doc->addStyleSheet($images.'slideshow/styles/glass/engine1/style.css'); 
$doc->addStyleSheet($css.'grid.css');
$doc->addStyleSheet($css.'bootstrap.min.css');
$doc->addStyleSheet($css.'nav.css');
$doc->addStyleSheet($css.'template.css');
$doc->addStyleSheet($css.'css.css');
JHtml::_('bootstrap.framework');
?>
<?php $header_top=0;
$pos_header_top = 'header_top';
for ($i=1; $i<=2 ; $i++) { if (($this->countModules($pos_header_top.$i)) or ($logo)) { $header_top++; } } ?>
<?php if ($header_top == 2) : $header_top_val = '6'; else: $header_top_val = '12'; endif; ?>
<?php $top=0;
$pos_top = 'top';
for ($i=1; $i<=4 ; $i++) { if ($this->countModules($pos_top.$i)) { $top++; } } ?>
<?php if ($top == 2) : $top_val = '6'; elseif ($top == 3) : $top_val = '4'; elseif ($top == 4) : $top_val = '3'; else: $top_val = '12'; endif; ?>
<?php $bottom=0;
$pos_bottom = 'bottom';
for ($i=1; $i<=4 ; $i++) { if ($this->countModules($pos_bottom.$i)) { $bottom++; } } ?>
<?php if ($bottom == 2) : $bottom_val = '6'; elseif ($bottom == 3) : $bottom_val = '4'; elseif ($bottom == 4) : $bottom_val = '3'; else: $bottom_val = '12'; endif; ?>
<?php $footer=0;
$pos_footer = 'footer';
for ($i=1; $i<=4 ; $i++) { if ($this->countModules($pos_footer.$i)) { $footer++; } } ?>
<?php if ($footer == 2) : $footer_val = '6'; elseif ($footer == 3) : $footer_val = '4'; elseif ($footer == 4) : $footer_val = '3'; else: $footer_val = '12'; endif; ?>
<!DOCTYPE html><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>"><head><jdoc:include type="head" /></head>

<body>
<header role="banner">
<div class="wrap">
<section style="background-color:#e0b8e0;">
<?php if ($header_top_pos) : ?>
<div class="container_12">
<?php if ($header_top1) : ?>
<div class="grid_<?php echo $header_top_val; ?>">
<jdoc:include type="modules" name="header_top1" style="crate_notitle" />
</div>
<?php else : ?>
<div class="grid_<?php echo $header_top_val; ?>">
<div class="crate">
<a href="<?php echo $this->baseurl; ?>">
<?php echo $logo;?>
<?php if ($this->params->get('sitedescription'))
{
echo '<div class="site-description">'. htmlspecialchars($this->params->get('sitedescription')) .'</div>';
}
?>
</a>
</div>
</div>
<?php endif; ?>
<?php if ($header_top2) : ?>
<div class="grid_<?php echo $header_top_val; ?>">
<jdoc:include type="modules" name="header_top2" style="crate_notitle" />
</div>
<?php else : ?>
<?php endif; ?>
<div class="clear"></div>
</div>
<?php endif; ?>

<div id="hor_nav">
<?php echo $hornav; ?>
</div>
<div class="clear"></div>


</section>
</div>
</header>

<main role="main">
<div class="wrap">
<section>
<?php if ($this->countModules('left') && $this->countModules('right')): ?>
<div class="container_12" id="main">
<div class="grid_3" id="left_side_bar">
<jdoc:include type="modules" name="left" style="crate" />
</div>
<div class="grid_6">
<?php include ($slideshow.'slideshow.php'); ?>
<div class="crate" id="main_body">
<jdoc:include type="message" />
<jdoc:include type="component" />
<div class="clear"></div>
</div>
</div>
<div class="grid_3" id="right_side_bar">
<jdoc:include type="modules" name="right" style="crate" />
</div>
<div class="clear"></div>
</div>
<?php elseif ( $this->countModules('left')) : ?>
<div class="container_12" id="main">
<div class="grid_3" id="left_side_bar">
<jdoc:include type="modules" name="left" style="crate" />
</div>
<div class="grid_9">
<?php include ($slideshow.'slideshow.php'); ?>
<div class="crate" id="main_body">
<jdoc:include type="message" />
<jdoc:include type="component" />
<div class="clear"></div>
</div>
</div>
<div class="clear"></div>
</div>
<?php elseif ( $this->countModules('right')): ?>
<div class="container_12" id="main">
<div class="grid_9">
<?php include ($slideshow.'slideshow.php'); ?>
<div class="crate" id="main_body">
<jdoc:include type="message" />
<jdoc:include type="component" />
<div class="clear"></div>
</div>
</div>
<div class="grid_3" id="right_side_bar">
<jdoc:include type="modules" name="right" style="crate" />
</div>
<div class="clear"></div>
</div>
<?php else : ?>
<div class="container_12" id="main">
<div class="grid_12">
<?php include ($slideshow.'slideshow.php'); ?>
<div class="crate" id="main_body">
<jdoc:include type="message" />
<jdoc:include type="component" />
<div class="clear"></div>
</div>
</div>
<div class="clear"></div>
</div>
<?php endif; ?>
<?php if ($bottom_pos) : ?>
<div class="container_12" id="bottom">
<?php if ($bottom1_position) : ?>
<div class="grid_<?php echo $bottom_val; ?>">
<jdoc:include type="modules" name="bottom1" style="crate" />
</div>
<?php else : ?>
<?php endif; ?>
<?php if ($bottom2_position) : ?>
<div class="grid_<?php echo $bottom_val; ?>">
<jdoc:include type="modules" name="bottom2" style="crate" />
</div>
<?php else : ?>
<?php endif; ?>
<?php if ($bottom3_position) : ?>
<div class="grid_<?php echo $bottom_val; ?>">
<jdoc:include type="modules" name="bottom3" style="crate" />
</div>
<?php else : ?>
<?php endif; ?>
<?php if ($bottom4_position) : ?>
<div class="grid_<?php echo $bottom_val; ?>">
<jdoc:include type="modules" name="bottom4" style="crate" />
</div>
<?php else : ?>
<?php endif; ?>
<div class="clear"></div>
</div>
<?php endif; ?>
</section>
</div>
</main>
<footer role="contentinfo">
<div class="wrap">
<section>
<?php if ($footer_pos) : ?>
<div class="container_12">
<?php if ($footer1_position) : ?>
<div class="grid_<?php echo $footer_val; ?>">
<jdoc:include type="modules" name="footer1" style="no" />
</div>
<?php else : ?>
<?php endif; ?>

<?php if ($footer2_position) : ?>
<div class="grid_<?php echo $footer_val; ?>">
<jdoc:include type="modules" name="footer2" style="no" />
</div>
<?php else : ?>
<?php endif; ?>
<?php if ($footer3_position) : ?>
<div class="grid_<?php echo $footer_val; ?>">
<jdoc:include type="modules" name="footer3" style="no" />
</div>
<?php else : ?>
<?php endif; ?>
<?php if ($footer4_position) : ?>
<div class="grid_<?php echo $footer_val; ?>">
<jdoc:include type="modules" name="footer4" style="no" />
</div>
<?php else : ?>
<?php endif; ?>
<div class="clear"></div>
</div>
<?php endif; ?>
<?php if ($footer_position) : ?>
<div class="container_12">
<div class="grid_12">
<div class="copr">
<?php echo $comp_info; ?>
<jdoc:include type="modules" name="footer" style="no" />
<?php echo $cp; ?>
</div>
<div class="clear"></div>
</div>
<div class="clear"></div>
</div>
<?php else: ?> 
<div class="container_12">
<div class="grid_12">
<div class="copr">
<?php echo $comp_info; ?>
<?php echo $cp; ?>
</div>
</div>
<div class="clear"></div>
<?php endif; ?>
</div>
</section>
</div>
</footer>
<?php if ($display_social_links == "yes") {include ($images.'social_media/socialmedia.php');} ?>
</body>
</html>