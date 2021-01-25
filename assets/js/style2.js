jQuery( document ).ready(function() {
    jQuery('.contact-group').on('click', '.button-contact', function(e){		
		if(jQuery(this).hasClass('item-count-1')){
			return;
		}else{
			e.preventDefault();
			var t = jQuery(this).parents('.contact-group').first()
			if(t.hasClass('extend')){
				t.removeClass('extend')
			}else{
				t.addClass('extend')
			}
		}
		
	})
});