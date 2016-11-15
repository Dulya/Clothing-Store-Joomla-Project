<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;
JHtml::addIncludePath(JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function(task)
	{
			Joomla.submitform(task,document.getElementById('book-form'));
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_flippingbook'); ?>" method="post" name="adminForm" id="book-form" class="form-validate">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#book_parameters" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_BOOK_PARAMETERS');?></a></li>
		<li><a href="#advanced_parameters" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_ADVANCED_PARAMETERS');?></a></li>
		<li><a href="#background" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_BACKGROUND');?></a></li>
		<li><a href="#navigation_bar" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_NAVIGATION_BAR');?></a></li>
		<li><a href="#zoom_settings" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_ZOOM_SETTINGS');?></a></li>
		<li><a href="#download_book" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_DOWNLOAD_BOOK');?></a></li>
		<li><a href="#slideshow" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_SLIDESHOW');?></a></li>
		<li><a href="#book_window" data-toggle="tab"><?php echo JText::_('COM_FLIPPINGBOOK_BOOK_WINDOW');?></a></li>
	</ul>
	<div class="tab-content">
		<div class="tab-pane active" id="book_parameters">
			<div class="row-fluid">
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo empty($this->item->id) ? JText::_('COM_FLIPPINGBOOK_NEW_BOOK') : JText::sprintf('COM_FLIPPINGBOOK_EDIT_BOOK', $this->item->id); ?></legend>
						<?php foreach ($this->form->getFieldset('book_edit') as $field) { ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input; ?></div>
						</div>
						<?php } ?>
					</fieldset>
				</div>
				<div class="span6">
					<fieldset class="form-horizontal">
						<legend><?php echo JText::_('COM_FLIPPINGBOOK_BOOK_PARAMETERS'); ?></legend>
						<?php foreach ($this->form->getFieldset('book_parameters') as $field) { ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input; ?></div>
						</div>
						<?php } ?>
					</fieldset>
				</div>
			</div>
		</div>
		<div class="tab-pane" id="advanced_parameters">
			<div class="row-fluid">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('COM_FLIPPINGBOOK_ADVANCED_PARAMETERS'); ?></legend>
					<?php foreach ($this->form->getFieldset('advanced_parameters') as $field) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php } ?>
				</fieldset>
			</div>
		</div>
		<div class="tab-pane" id="background">
			<div class="row-fluid">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('COM_FLIPPINGBOOK_BACKGROUND'); ?></legend>
					<?php foreach ($this->form->getFieldset('background') as $field) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php } ?>
				</fieldset>
			</div>
		</div>
		<div class="tab-pane" id="navigation_bar">
			<div class="row-fluid">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('COM_FLIPPINGBOOK_NAVIGATION_BAR'); ?></legend>
					<?php foreach ($this->form->getFieldset('navigation_bar') as $field) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php } ?>
				</fieldset>
			</div>
		</div>
		<div class="tab-pane" id="zoom_settings">
			<div class="row-fluid">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('COM_FLIPPINGBOOK_ZOOM_SETTINGS'); ?></legend>
					<?php foreach ($this->form->getFieldset('zoom_settings') as $field) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php } ?>
				</fieldset>
			</div>
		</div>
		<div class="tab-pane" id="download_book">
			<div class="row-fluid">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('COM_FLIPPINGBOOK_DOWNLOAD_BOOK'); ?></legend>
					<?php foreach ($this->form->getFieldset('download_book') as $field) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php } ?>
				</fieldset>
			</div>
		</div>
		<div class="tab-pane" id="slideshow">
			<div class="row-fluid">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('COM_FLIPPINGBOOK_SLIDESHOW'); ?></legend>
					<?php foreach ($this->form->getFieldset('slideshow') as $field) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php } ?>
				</fieldset>
			</div>
		</div>
		<div class="tab-pane" id="book_window">
			<div class="row-fluid">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('COM_FLIPPINGBOOK_BOOK_WINDOW'); ?></legend>
					<?php foreach ($this->form->getFieldset('book_window') as $field) { ?>
					<div class="control-group">
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
					<?php } ?>
				</fieldset>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<style>
.colorpicker {
	text-align:center;
	visibility:hidden;
	display:none;
	position:absolute;
	background-color:#FFF;
	border:solid 1px #CCC;
	padding:4px;
	z-index:999;
	filter:progid:DXImageTransform.Microsoft.Shadow(color=#D0D0D0,direction=135);
}

.obrd {
	border-bottom:solid 1px #DFDFDF;
	border-right:solid 1px #DFDFDF;
	padding:0;
	width:8px;
	height:8px;
}
a.itm, .itm, .itma {
	font-family:arial,tahoma,sans-serif;
	line-height: 7px;
	text-decoration:underline;
	font-size:10px;
	color:#666;
	border:none;
}

.itm, .itma {
	text-align:center;
	line-height: 7px;
	text-decoration:none;
}

a:hover.itm {
	text-decoration:none;
	line-height: 7px;
	color:#FFA500;
	cursor:pointer;
}

.close_btn {
	padding:1px 4px 1px 2px;
	background:whitesmoke;
	border:solid 1px #DFDFDF;
}
</style>
<script language="JavaScript" type="text/javascript">
function setStyle( objID, prop, val ) {
	switch( prop ) {
		case "bgColor":
			if( objID != 'none' ) {
				document.getElementById(objID).style.backgroundColor = val;
			}
		break;
		case "visibility":
			document.getElementById(objID).style.visibility = val;
		break;
		case "display":
			document.getElementById(objID).style.display = val;
		break;
		case "top":
			document.getElementById(objID).style.top = val;
		break;
	}
}

function putColor( OBjElem, Samp, Xmarker ) {
	if ( Xmarker != 'x') {
		document.getElementById(OBjElem).value = Xmarker;
		setStyle( Samp, 'bgColor', Xmarker );
	}
	setStyle('colorpicker', 'visibility', 'hidden');
	setStyle('colorpicker', 'display', 'none');
}

function colorSelector( OBjElem, Sam ) {
	var c = 0;
	var xl = '"' + OBjElem + '","' + Sam + '","x"';
	var mid = '';
	
	//Color table
	var objX = new Array( '00', '33', '66', '99', 'CC', 'FF' );
	mid += '<table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" style="border:solid 1px #F0F0F0;padding:2px;">';
	mid += "<tr><td colspan='18' align='center' style='margin:0;padding:2px;height:14px;' ><input class='itm' type='text' size='10' id='itm' value='FFFFFF'><input class='itma' type='text' size='2' style='width:14px;' id='itma' value='' style='border:solid 1px #666;'>&nbsp;&nbsp;&nbsp;<a class='itm' href='javascript:onclick=putColor(" + xl + ")'><span class='close_btn'><?php echo JText::_('COM_FLIPPINGBOOK_CLOSE'); ?></span></a></td></tr><tr>";
	var br = 1;
	for ( o = 0; o < 6; o++ ) {
		mid += '</tr><tr>';
		for ( y = 0; y < 6; y++) {
			if ( y == 3 ) {
				mid += '</tr><tr>';
			}
			for ( x = 0; x < 6; x++ ) {
				var grid = '';
				grid = objX[o] + objX[y] + objX[x];
				var b ="'" + OBjElem + "', '" + Sam + "','" + grid + "'";
				mid += '<td class="obrd" style="background-color:#' + grid + '"><a class="itm" href="javascript:onclick=putColor(' + b + ');" onmouseover=javascript:document.getElementById("itm").value="' + grid + '";javascript:document.getElementById("itma").style.backgroundColor="#' + grid + '"; title="' + grid + '"><div style="width:10px;height:10px;"></div></a></td>';
				c++;
			}
		}
	}
	mid += '</tr></table>';
	
	//greyscale table
	var objX = new Array( '0', '3', '6', '9', 'C', 'F' );
	mid += '<table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0" style="border:solid 1px #F0F0F0;padding:2px;"><tr>';
	var br = 0;
	for ( y = 0; y < 6; y++) {
		for ( x = 0; x < 6; x++) {
			if ( br == 18 ) {
				br = 0; mid += '</tr><tr>';
			}
			br++;
			var grid = '';
			grid = objX[y] + objX[x] + objX[y] + objX[x] + objX[y] + objX[x];
			var b = "'" + OBjElem + "', '" + Sam + "','" + grid + "'";
			mid += '<td class="obrd" style="background-color:#' + grid + '"><a class="itm" href="javascript:onclick=putColor(' + b + ');" onmouseover=javascript:document.getElementById("itm").value="' + grid + '";javascript:document.getElementById("itma").style.backgroundColor="#' + grid + '"; title="' + grid + '"><div style="width:10px;height:10px;"></div></a></td>';
			c++;
		}
	}
	mid += "</tr>";
	mid += '</table>';
	
	setStyle( 'colorpicker', 'top', '200px' );
	setStyle( 'colorpicker', 'visibility', 'visible' );
	setStyle( 'colorpicker', 'display', 'block' );
	document.getElementById('colorpicker').innerHTML = mid;
}
</script>
<div class="span12" style="font-size: 80%; line-height: 120%; margin: 20px 0 0 0;">
	FlippingBook Gallery Component for Joomla CMS v. <?php echo FBComponentVersion; ?><br />
	<a href="http://page-flip-tools.com" target="_blank">Check for latest version</a> | <a href="http://page-flip-tools.com/faqs/" target="_blank">FAQ</a> | <a href="http://page-flip-tools.com/support/" target="_blank">Technical support</a><br />
	<div style="padding: 10px 0 20px 0;">Copyright &copy; 2012 Mediaparts Interactive. All rights reserved.</div>
</div>