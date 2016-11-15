jQuery.noConflict();
var baseURL;
function getScrollY() {	if (window.pageYOffset) { return window.pageYOffset; } else if (document.documentElement && document.documentElement.scrollTop) { return document.documentElement.scrollTop; } else if (document.body) { return document.body.scrollTop; } }
function getScrollX() {	if (window.pageXOffset) { return window.pageXOffset; } else if (document.documentElement && document.documentElement.scrollLeft) { return document.documentElement.scrollLeft; } else if (document.body) { return document.body.scrollLeft; } }
function getWindowWidth() {	if(window.innerWidth){ return window.innerWidth; } else if (document.documentElement && document.documentElement.clientWidth){ return document.documentElement.clientWidth;	} else if (document.body) { return document.body.offsetWidth; } }
function getWindowHeight() { if(window.innerHeight){ return window.innerHeight; } else if (document.documentElement && document.documentElement.clientHeight) { return document.documentElement.clientHeight; }	else if (document.body) { return document.body.clientHeight; } }
function fb_overlayPosition () { jQuery("#fb_overlay").height(getWindowHeight()); jQuery("#fb_overlay").css("top", getScrollY()+"px"); jQuery("#fb_overlay").css("left", getScrollX()+"px"); }
function fb_contentPosition () { jQuery("#fb_zoom_container").width("100%"); jQuery("#fb_zoom_container").css("top", getScrollY()+"px"); jQuery("#fb_zoom_container").css("left", getScrollX()+"px"); }

function fb_ajaxZoom(page_number) {
	page_number--;
	
	var image_url=book_settings.enlargedImages[page_number];
	if (image_url.substr(image_url.length - 1) == "|") {
		image_url = image_url.substr(0, image_url.length - 1);
	}
	var swf_width=book_settings.swfWidth[page_number]; 
	var swf_height=book_settings.swfHeight[page_number];
	var fb_zoom_file_type = image_url.substr(image_url.length - 3).toLowerCase();
	if (fb_zoom_file_type == 'swf')	{
		var fb_page_image = '<object id="fb_zoomed_image" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0" width="' + swf_width + '" height="' + swf_height + '"><param name="movie" value="'+image_url+'" /><param name="quality" value="high" /><embed id="fb_zoomed_image" src="' + image_url + '" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="' + swf_width + '" height="' + swf_height + '"></embed></object>';
	} else {
		var fb_page_image = '<img id="fb_zoomed_image" src="' + image_url + '" />';
	}
	var fb_zoom_container = '<table width="100%" height="' + getWindowHeight() + '" border="0" align="center" cellpadding="0" cellspacing="0" class="zoom_table">'
	+ '	<tr>'
	+ '		<td>&nbsp;</td>'
	+ '		<td width="10%">'
	+ '			<div id="fb_container_buttons">'
	+ '				<a id="fb_closeButton" href="javascript:fb_zoom_close()">&nbsp;</a>'
	+ '				<a id="fb_printButton" href="javascript:fb_print_window(' + "'" + escape(fb_page_image) + "'" + ', ' + page_number + ')">&nbsp;</a>'
	+ '			</div>'
	+ '			<div id="fb_container">'
	+ '				<div id="fb_container_image" align="center">' + fb_page_image + '</div>'
	+ '				<div id="fb_container_page_description"></div>'
	+ '			</div>'
	+ '		</td>'
	+ '		<td>&nbsp;</td>'
	+ '	</tr>'
	+ '</table>'; 
	fb_overlayPosition();
	fb_contentPosition();
	jQuery("#fb_overlay").css("opacity", 0);
	jQuery("#fb_overlay").show();
	jQuery("#fb_overlay").fadeTo('fast', 0.8, function() {
		jQuery("#fb_zoom_container").show();
		jQuery("#fb_zoom_container").html(fb_zoom_container);
		page_number++;
		jQuery("#fb_container_page_description").html(jQuery("#fb_page_" + page_number).html());
	});
}

function zoom_init(base_url, fbSettings) {
	book_settings = fbSettings;
	baseURL = base_url;
	if (window.addEventListener) { 
		window.addEventListener("scroll", fb_overlayPosition, false); 
		window.addEventListener("resize", fb_overlayPosition, false); 
	} 
	else if (window.attachEvent) { 
		window.attachEvent("onscroll", fb_overlayPosition); 
		window.attachEvent("onresize", fb_overlayPosition);	
	} 
	var fb_overlay = document.createElement("div");
	fb_overlay.id = "fb_overlay";
	document.body.appendChild(fb_overlay);
	jQuery("#fb_overlay").addClass("fb_overlay");
	jQuery("#fb_overlay").hide();
	
	var fb_zoom_container = document.createElement("div");
	fb_zoom_container.id = "fb_zoom_container";
	jQuery("#fb_zoom_container").css("position", 'absolute');
	document.body.appendChild(fb_zoom_container);
	jQuery("#fb_zoom_container").addClass("fb_zoom_container");
	jQuery("#fb_zoom_container").hide();
}

function fb_zoom_close() {
	jQuery("#fb_overlay").fadeTo('fast', 0, function () {
		jQuery("#fb_overlay").hide();
		jQuery("#fb_zoom_container").hide();
	});
}

function fb_print_window(fb_page_image, page_number) {
	var PrintWindow=window.open("", "", "height=800,width=600,status=no,menubar=no,resizable=yes,toolbar=no,scrollbars=yes,titlebar=no");
	PrintWindow.document.write('<html><head>');
	PrintWindow.document.write('<link rel="stylesheet" href="' + baseURL + 'components/com_flippingbook/css/ajax-zoom.css" type="text/css" />');
	PrintWindow.document.write('<title>Print</title>');
	PrintWindow.document.write('</head>');
	PrintWindow.document.write('<body>');
	PrintWindow.document.write(unescape(fb_page_image));
	PrintWindow.document.write('<br>');
	PrintWindow.document.write('</body>');
	PrintWindow.document.write('</html>');
	PrintWindow.document.close('');
	PrintWindow.print();
}