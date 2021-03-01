<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'adminz_flickity');
add_shortcode('adminz_flickity', 'adminz_flickity_function');
function adminz_flickity(){
	$adminz = new Adminz;
	add_ux_builder_shortcode('adminz_flickity', array(
		'info' => '{{ title }}',
        'name'      => __('Flickity slider'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'ux_stack.svg',
        'options' => array(
            'ids'             => array(
				'type'       => 'gallery',
				'heading'	=> __('Images'),
			),
			'columns'             => array(
				'type'       => 'textfield',
				'heading'	=> __('Thumbnails column'),
				'default'    => '6',
				'holder'    => '',
			),
        ),
    ));
}
function adminz_flickity_function($atts){
	add_action( 'wp_footer', 'woocommerce_photoswipe' );
	$adminz = new Adminz;
	extract(shortcode_atts(array(
        'ids'    => '',
        'columns' => 6,
    ), $atts));
    ob_start();
    $rtl = 'false';
    $attachment_ids = explode(",", $ids);
    // 1. big ========================================================================
    $wrapper_classes   = apply_filters( 'woocommerce_single_product_image_gallery_classes', array(
		'woocommerce-product-gallery',
		'woocommerce-product-gallery--with-images' ,
		'woocommerce-product-gallery--columns-' . absint( $columns ),
		'images',
	) );
	$slider_classes = array('product-gallery-slider','slider','slider-nav-small','mb-half');
	$slider_classes[] = 'adminz_flickity';

	if(get_theme_mod('product_lightbox','default') == 'disabled'){
	  $slider_classes[] = 'disable-lightbox';
	}

    ?>
    <!-- start check -->
    <div class="product-images relative mb-half has-hover <?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>" data-columns="<?php echo esc_attr( $columns ); ?>">
    	<figure class="woocommerce-product-gallery__wrapper <?php echo implode(' ', $slider_classes); ?>"
	        data-flickity-options='{
	                "cellAlign": "center",
	                "wrapAround": true,
	                "autoPlay": false,
	                "prevNextButtons":true,
	                "adaptiveHeight": true,
	                "imagesLoaded": true,
	                "lazyLoad": 1,
	                "dragThreshold" : 15,
	                "pageDots": false,
	                "rightToLeft": <?php echo $rtl; ?>
	       }'>
	    <?php
	    $image_size     = get_theme_mod( 'product_layout' ) == 'gallery-wide' ? 'full' : 'woocommerce_single';
		if ( $attachment_ids) {
			foreach ( $attachment_ids as $attachment_id ) {
					echo apply_filters( 
					'woocommerce_single_product_image_thumbnail_html', 
					flatsome_wc_get_gallery_image_html( $attachment_id, $main_image = false, $image_size ), 
					$attachment_id 
				);
			}
		}
	    ?>
	  	</figure>
    	<div class="image-tools absolute bottom left z-3">
    		<?php do_action('flatsome_product_image_tools_bottom'); ?>
    	</div>
    </div>
    <?php
    // 2. small ========================================================================
    
	$thumb_count    = count( $attachment_ids ) + 1;
	if ( $thumb_count == 1 ) {
		return;
	}
	$thumb_cell_align = 'left';
	if ( is_rtl() ) {
		$rtl              = 'true';
		$thumb_cell_align = 'right';
	}

	if ( $attachment_ids ) {
		$loop          = 0;
		$image_size    = 'thumbnail';
		$gallery_class = array( 'product-thumbnails', 'thumbnails' );

		// Check if custom gallery thumbnail size is set and use that.
		$image_check = wc_get_image_size( 'gallery_thumbnail' );
		if ( $image_check['width'] !== 100 ) {
			$image_size = 'gallery_thumbnail';
		}
		$gallery_thumbnail = wc_get_image_size( apply_filters( 'woocommerce_gallery_thumbnail_size', 'woocommerce_' . $image_size ) );
		if ( $thumb_count <= 5 ) {
			$gallery_class[] = 'slider-no-arrows';
		}
		$gallery_class[] = 'slider row row-small row-slider slider-nav-small small-columns-'.$columns;
		?>
		<div class="<?php echo implode( ' ', $gallery_class ); ?>"
			data-flickity-options='{
				"cellAlign": "<?php echo $thumb_cell_align; ?>",
				"wrapAround": false,
				"autoPlay": false,
				"prevNextButtons": true,
				"asNavFor": ".adminz_flickity",
				"percentPosition": true,
				"imagesLoaded": true,
				"pageDots": false,
				"rightToLeft": <?php echo $rtl; ?>,
				"contain": true
			}'>
		<?php
		foreach ( $attachment_ids as $attachment_id ) {

			$classes     = array( '' );
			$image_class = esc_attr( implode( ' ', $classes ) );
			$image       = wp_get_attachment_image_src( $attachment_id, apply_filters( 'woocommerce_gallery_thumbnail_size', 'woocommerce_' . $image_size ) );
			$image_alt   = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			$image       = '<img src="' . $image[0] . '" alt="' . $image_alt . '" width="' . $gallery_thumbnail['width'] . '" height="' . $gallery_thumbnail['height'] . '"  class="attachment-woocommerce_thumbnail" />';
			echo sprintf( '<div class="col"><a>%s</a></div>', $image );
			$loop ++;
		}
		?>
	</div>	
	<!-- end check -->
	<?php
	}
    return ob_get_clean();
}
add_action( 'wp_enqueue_scripts',function (){
	wp_enqueue_script( 'photoswipe' );
	wp_enqueue_script( 'photoswipe-ui-default' );
	wp_enqueue_script( 'wc-single-product' );
	wp_enqueue_style( 'photoswipe' );
	wp_enqueue_style( 'photoswipe-default-skin' );
});
