jQuery(function($){
	$(".adminz_flickity").each(function(){
		var data_flickity = $(this).data('adminz');
		$(this).flickity(data_flickity);
	})
})