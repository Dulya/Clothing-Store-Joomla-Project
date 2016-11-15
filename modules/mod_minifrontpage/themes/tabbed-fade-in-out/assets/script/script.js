jQuery(document).ready(function(){
	jQuery('.minifrontpage-tabbed-fade-in-out').each(function(){
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
	
		mfpElement.hide();
		jQuery('#'+theId+' .animate:first').show();
		jQuery('#'+theId+' .animate:first').addClass('activex');
		jQuery('#'+theId+' #mfptabs li:first').addClass('activex');
		mfpElement.css('position','absolute');
		//mfpElement.css('width',mpfAnimateDiv+'%');
		jQuery('#'+theId+' .minifrontpageid').css('height', jQuery('.animate:first').css('height'));
	
		jQuery('#'+theId+' #mfptabs li').live('click', function(){ 
			var current = jQuery('#'+theId+' #mfptabs li.activex').index();
			var index = jQuery(this).index();
			if( current != index ){
				jQuery('#'+theId+' #mfptabs li.activex').removeClass('activex');
				jQuery(this).addClass('activex'); 
				
				jQuery('#'+theId+' .minifrontpageid').animate({
					height: jQuery(mfpElement[index]).css('height')
				},
				{
					step: function(now, fx) {
						jQuery(mfpElement[current]).fadeOut();
						jQuery(mfpElement[index]).fadeIn();
					}
				});
			}
			return false;
		});
	});
});