<?php
/**********************************************
* 	FlippingBook Gallery Component.
*	© Mediaparts Interactive. All rights reserved.
* 	Released under Commercial License.
*	www.page-flip-tools.com
**********************************************/
defined('_JEXEC') or die;

		$document = JFactory::getDocument();
		$document->addStyleSheet('../administrator/components/com_flippingbook/css/common.css');
		
		$file_path = JRequest::getVar( 'file', '', 'get', 'string' );
		
		$image_format = strtolower( substr( $file_path, -3 ) );
		$images_extensions = array ('jpg', 'peg', 'png', 'gif', 'bmp');
?>
<div align="center">
	<div style="color: #999"><?php echo $file_path; ?></div>
	<div style="margin-top: 10px;">
<?php		
$file_path = strtr ( $file_path, '\\', '/');

if ( in_array( $image_format, $images_extensions ) ) { ?>
		<img src="..<?php echo $file_path; ?>">
<?php	}

if ( $image_format == 'swf' ) { ?>
		<object width="790" height="540">
			<param name="movie" value="..<?php echo $file_path; ?>" />
			<param name="wmode" value="transparent" />
			<embed src="..<?php echo $file_path; ?>" type="application/x-shockwave-flash" width="790" height="540"/>
		</object>
<?php	} ?>
	</div>
</div>