jQuery(document).ready(function(){
	jQuery('.minifrontpage-tabbed-slide-down-up').each(function(){
		var theId = jQuery(this).attr('id');

		var mfpElement = jQuery('#'+theId+' .animate');
		var mfpElementLength = mfpElement.length;

		if(mfpElementLength>1){
			var addto = '<ul>';
			for(var i=0;i<mfpElementLength;i++){
				addto += '<li><span>'+ (i+1) + '</span></li>';
			}
			addto += '</ul>';
			addto += '<div class="clr">&nbsp;</div>';
			jQuery(addto).appendTo('#'+theId+' #mfptabs');
		}
		jQuery('#'+theId+' .minifrontpageid').css('height', jQuery('.animate:first').css('height'));
		jQuery('#'+theId+' .minifrontpageid').css('overflow', 'hidden');


		var elWidth = mfpElement.css('width').replace('px','');
		var elHeight = mfpElement.css('height').replace('px','');
		jQuery('#'+theId+' .anim').css('width', elWidth+'px');
		jQuery('#'+theId+' .anim').css('height', elHeight+'px');
		jQuery('#'+theId+' .anim').css('overflow', 'hidden');
	
		mfpElement.css('position', 'absolute');
		mfpElement.css('z-index', '10 !important');
		//mfpElement.css('width',mpfAnimateDiv+'%');
		
		var bottomPos = 0;
		var arrBottomPos = new Array;
	
		for(var i=0; i<mfpElement.length; i++){
			arrBottomPos[i] = bottomPos;
			if(navigator.userAgent.toLowerCase().indexOf('chrome') > -1){
				jQuery(mfpElement[i]).css('margin-top', bottomPos+'px !important');
			}
			else{
				jQuery(mfpElement[i]).css('margin-top', bottomPos+'px');
			}
			bottomPos = bottomPos+parseInt(jQuery(mfpElement[i]).css('height').replace('px',''));
		}
	
		jQuery('#'+theId+' .animate:first').addClass('activex');
		jQuery('#'+theId+' #mfptabs li:first').addClass('activex');
	
		jQuery('#'+theId+' #mfptabs li').live('click', function(){ 
			var current = jQuery('#'+theId+' #mfptabs li.activex').index();
			var index = jQuery(this).index();
			if( current != index ){
				jQuery('#'+theId+' #mfptabs li.activex').removeClass('activex');
				jQuery(this).addClass('activex'); 

				jQuery('#'+theId+' .minifrontpageid').animate({
					height: jQuery(mfpElement[index]).css('height')
				});

				jQuery('#'+theId+' .anim').animate({
					height: jQuery(mfpElement[index]).css('height')
				});
	
				jQuery('#'+theId+' .anim-div').animate({
					marginTop:-(arrBottomPos[index])+'px'
				});
			}
			return false;
		});
	});
});
