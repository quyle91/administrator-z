jQuery(function($){
 
	// on upload button click
	$('body').on( 'click', '.adminz-upl', function(e){
 
		e.preventDefault();
 
		var button = $(this),
		custom_uploader = wp.media({
			title: 'Insert image',
			library : {
				// uploadedTo : wp.media.view.settings.post.id, // attach to the current post?
				type : 'image'
			},
			button: {
				text: 'Use this image' // button label text
			},
			multiple: false
		}).on('select', function() { // it also has "open" and "close" events
			var attachment = custom_uploader.state().get('selection').first().toJSON();

			button.html('<img width="300px" src="' + attachment.url + '">');
			button.next().show().next().val(attachment.id);
		}).open();
 
	});
 
	// on remove button click
	$('body').on('click', '.adminz-rmv', function(e){
 
		e.preventDefault();
 
		var button = $(this);
		button.hide().prev().html('Upload image');
		button.next().val(''); // emptying the hidden field
	});
 
});
