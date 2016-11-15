<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

function modChrome_no($module, &$params, &$attribs)
{
if ($module->content)
{
echo $module->content;
}
}

function modChrome_crate($module, &$params, &$attribs) {
$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
if (!empty ($module->content)) : ?>
<div class="crate moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php if ($module->showtitle) : ?>
<h<?php echo $headerLevel; ?>><?php echo $module->title; ?></h<?php echo $headerLevel; ?>>
<?php endif; ?>
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}

function modChrome_crate_notitle($module, &$params, &$attribs) {
if (!empty ($module->content)) : ?>
<div class="crate moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}

function modChrome_color1($module, &$params, &$attribs) {
$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
if (!empty ($module->content)) : ?>
<div class="color1 moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php if ($module->showtitle) : ?>
<h<?php echo $headerLevel; ?>><?php echo $module->title; ?></h<?php echo $headerLevel; ?>>
<?php endif; ?>
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}

function modChrome_color1_notitle($module, &$params, &$attribs) {
if (!empty ($module->content)) : ?>
<div class="color1 moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}

function modChrome_color2($module, &$params, &$attribs) {
$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
if (!empty ($module->content)) : ?>
<div class="color2 moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php if ($module->showtitle) : ?>
<h<?php echo $headerLevel; ?>><?php echo $module->title; ?></h<?php echo $headerLevel; ?>>
<?php endif; ?>
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}

function modChrome_color2_notitle($module, &$params, &$attribs) {
if (!empty ($module->content)) : ?>
<div class="color2 moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}

function modChrome_color3($module, &$params, &$attribs) {
$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
if (!empty ($module->content)) : ?>
<div class="color3 moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php if ($module->showtitle) : ?>
<h<?php echo $headerLevel; ?>><?php echo $module->title; ?></h<?php echo $headerLevel; ?>>
<?php endif; ?>
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}

function modChrome_color3_notitle($module, &$params, &$attribs) {
if (!empty ($module->content)) : ?>
<div class="color3 moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}

function modChrome_color4($module, &$params, &$attribs) {
$headerLevel = isset($attribs['headerLevel']) ? (int) $attribs['headerLevel'] : 3;
if (!empty ($module->content)) : ?>
<div class="color4 moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php if ($module->showtitle) : ?>
<h<?php echo $headerLevel; ?>><?php echo $module->title; ?></h<?php echo $headerLevel; ?>>
<?php endif; ?>
<?php echo $module->content; ?>
</div>
<div class="clear"></div>
<?php endif;
}

function modChrome_color4_notitle($module, &$params, &$attribs) {
if (!empty ($module->content)) : ?>
<div class="color4 moduletable<?php echo $params->get('moduleclass_sfx'); ?>">
<?php echo $module->content; ?>
<div class="clear"></div>
</div>
<?php endif;
}