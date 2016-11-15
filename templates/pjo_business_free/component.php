<?php
// NO DIRECT ACCESS
defined( '_JEXEC' ) or die( 'Restricted access' );
$root					= $this->baseurl;
$template				= $this->template;
$css					= ''.$root.'/templates/'.$template.'/css/';

$app					= JFactory::getApplication();
$doc					= JFactory::getDocument();
$this->language			= $doc->language;
$this->direction		= $doc->direction;

// ADD JAVASCRIPT FRAMEWORKS
JHtml::_('bootstrap.framework');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>">

<head>
<jdoc:include type="head" />
<!--[if lt IE 9]>
<script src="<?php echo $this->baseurl; ?>/media/jui/js/html5.js"></script>
<![endif]-->
<link rel="stylesheet" href="<?php echo $css; ?>/grid.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $css; ?>/bootstrap.min.css.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $css; ?>/nav.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $css; ?>/template.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $css; ?>/css.css" type="text/css" />
</head>

<body class="contentpane">
<jdoc:include type="message" />
<jdoc:include type="component" />
</body>

</html>
