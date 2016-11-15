function FlippingBook() {
	this.pages = [];
	this.enlargedImages = [];
	this.pageLinks = [];
	this.stageWidth = "100%";
	this.stageHeight = "600";
	this.settings = {
			allowPagesUnload: false,
			alwaysOpened: false,
			autoFlipSize: 50,
			backgroundColor: "FFFFFF",
			backgroundImage: "",
			backgroundImagePlacement: "fit", 
			bookHeight: 480,
			bookWidth: 640,
			centerBook: true,
			centerContent: true,
			closeSpeed: 3,
			darkPages: false,
			downloadComplete: "Complete",
			downloadSize: "Size: 4.7 Mb",
			downloadTitle: "Download PDF",
			downloadURL: "",
			dropShadowEnabled: true,
			dropShadowHideWhenFlipping: true,
			dynamicShadowsDarkColor: 0x000000,
			dynamicShadowsDepth: 1,
			dynamicShadowsLightColor: 0xFFFFFF, // works for "dark" pages only
			enlargedImagesSet: this.enlargedImages,
			extXML: "",
			firstLastButtons: true,
			firstPageNumber: 1,
			flipCornerAmount: 70,
			flipCornerAngle: 45,
			flipCornerPlaySound: false,
			flipCornerPosition: "top-right",// "bottom-right","top-right","bottom-left","top-left"
			flipCornerRelease: true,
			flipCornerStyle: "manually",// "first page only", "each page", "manually"
			flipCornerVibrate: true,
			flipOnClick: true,
			flipSound: "",
			frameAlpha: 100,
			frameColor: 0x000000,
			frameWidth: 0,
			freezeOnFlip: false,
			fullscreenEnabled: true,
			fullscreenHint: "",
			goToPageField: true,
			gotoSpeed: 3,
			handOverCorner: true,
			handOverPage: true,
			hardcover: false,
			hardcoverEdgeColor: 0xFFFFFF,
			hardcoverSound: "",
			hardcoverThickness: 3,
			highlightHardcover: true,
			loadOnDemand: true,
			moveSpeed: 2,
			navigationBarPlacement: "bottom", //  "top", "bottom"
			navigationFlipOffset: 30,
			pageBackgroundColor: 0x99CCFF,
			pageLinksSet: this.pageLinks,
			pagesSet: this.pages,
			playOnDemand: true,
			preloaderType: "Progress Bar", 
			preserveProportions: false,
			printEnabled: true,
			printTitle: "Print Pages",
			rigidPages: false,
			rigidPageSpeed: 5,
			scaleContent: true,
			showUnderlyingPages: false,
			slideshowAutoPlay: false,
			slideshowButton: true,
			slideshowDisplayDuration: 5000,
			smoothPages: true,
			staticShadowsDarkColor: 0x000000,
			staticShadowsDepth: 1,
			staticShadowsLightColor: 0xFFFFFF, // works for "Symmetric" shadows only
			staticShadowsType: "Symmetric", 
			useCustomCursors: false,
			zoomEnabled: true,
			zoomHint: "Double click for zooming.",
			zoomHintEnabled: true,
			zoomImageHeight: 1165,
			zoomImageWidth: 900,
			zoomingMethod: 0, 
			zoomOnClick: true,
			zoomUIColor: 0x8f9ea6
		};
};

FlippingBook.prototype.create = function(swfpath) {
	this.settings.pagesSet = this.pages;
	this.settings.enlargedImagesSet = this.enlargedImages;
	this.settings.pageLinksSet = this.pageLinks;
	swfobject.embedSWF(swfpath, this.containerId, this.stageWidth, this.stageHeight, "8.0.0", "js/expressInstall.swf", this.settings, {allowFullScreen: "true", allowScriptAccess: "always", bgcolor:  "#" + this.settings.backgroundColor, wmode: "opaque" });
}

FlippingBook.prototype.getFlippingBookReference = function() {
	return document.getElementById( this.containerId );
}

FlippingBook.prototype.onPutPage = function( leftPageNumber, rightPageNumber ) {
	jQuery("#fb_leftPageDescription_"+this.settings.uniqueSuffix).slideUp("slow");
	if ((leftPageNumber != undefined) && (jQuery("#fb_page_"+this.settings.uniqueSuffix+"_"+leftPageNumber).html().length > 0)) { 
		jQuery("#fb_leftPageDescription_"+this.settings.uniqueSuffix).html(jQuery("#fb_page_"+this.settings.uniqueSuffix+"_"+leftPageNumber).html());
		jQuery("#fb_leftPageDescription_"+this.settings.uniqueSuffix).slideDown("slow");
	}
	jQuery("#fb_rightPageDescription_"+this.settings.uniqueSuffix).slideUp("slow");
	if ((rightPageNumber != undefined) && (jQuery("#fb_page_"+this.settings.uniqueSuffix+"_"+rightPageNumber).html().length > 0)) {
		jQuery("#fb_rightPageDescription_"+this.settings.uniqueSuffix).html(jQuery("#fb_page_"+this.settings.uniqueSuffix+"_"+rightPageNumber).html());
		jQuery("#fb_rightPageDescription_"+this.settings.uniqueSuffix).slideDown("slow");
	}
}

FlippingBook.prototype.ajaxZoom = function( PageNumber ) {
	fb_ajaxZoom (PageNumber);
}

FlippingBook.prototype.sizeContent = function( uniqueId ) {
	var fb_windowHeight = jQuery(window).height();
	var fb_headerHeight = jQuery("#fbHeader").height();
	if (fb_headerHeight == null) fb_headerHeight = 0;
	var fb_footerHeight = jQuery("#fbFooter").height();
	if (fb_footerHeight == null) fb_footerHeight = 0;
	var fb_contentHeight = fb_windowHeight - fb_footerHeight - fb_headerHeight;
	jQuery("#fbContainer_" + uniqueId).height( fb_contentHeight );
}

FlippingBook.prototype.removeSpaces = function() {
	jQuery("body").css("padding", "0px");
	jQuery("body").css("margin", "0px");
	jQuery("html").css("padding", "0px");
	jQuery("html").css("margin", "0px");
}