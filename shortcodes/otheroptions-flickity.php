<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'adminz_flickity');
add_shortcode('adminz_flickity', 'adminz_flickity_function');
function adminz_flickity(){
	$adminz = new Adminz;
	add_ux_builder_shortcode('adminz_flickity', array(
		'info' => '{{ heading }}',
        'name'      => __('Flickity'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'ux_stack.svg',
        'options' => array(
            'ids'             => array(
				'type'       => 'gallery',
				'heading'	=> __('Images'),
			),
			'draggable'=> array(
				'type'=>'textfield',
				'heading'=> 'draggable'
			),
	        'freeScroll'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'freeScroll'
	        ),
	        'wrapAround'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'wrapAround'
	        ),
	        'groupCells'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'groupCells'
	        ),
	        'autoPlay'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'autoPlay'
	        ),
	        'pauseAutoPlayOnHover'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'pauseAutoPlayOnHover'
	        ),
	        'fade'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'fade'
	        ),
	        'adaptiveHeight'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'adaptiveHeight'
	        ),
	        'asNavFor'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'asNavFor'
	        ),
	        'selectedAttraction'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'selectedAttraction'
	        ),
			'friction'=> array(
				'type'=>'textfield',
				'heading'=> 'friction'
			),
			'lazyLoad'=> array(
				'type'=>'textfield',
				'heading'=> 'lazyLoad'
			),
			'cellSelector'=> array(
				'type'=>'textfield',
				'heading'=> 'cellSelector'
			),
			'initialIndex'=> array(
				'type'=>'textfield',
				'heading'=> 'initialIndex'
			),
			'accessibility'=> array(
				'type'=>'textfield',
				'heading'=> 'accessibility'
			),
			'setGallerySize'=> array(
				'type'=>'textfield',
				'heading'=> 'setGallerySize'
			),
			'resize'=> array(
				'type'=>'textfield',
				'heading'=> 'resize'
			),
			'cellAlign'=> array(
				'type'=>'textfield',
				'heading'=> 'cellAlign'
			),
			'percentPosition'=> array(
				'type'=>'textfield',
				'heading'=> 'percentPosition'
			),
			'rightToLeft'=> array(
				'type'=>'textfield',
				'heading'=> 'rightToLeft'
			),
			'prevNextButtons'=> array(
				'type'=>'textfield',
				'heading'=> 'prevNextButtons'
			),
			'pageDots'=> array(
				'type'=>'textfield',
				'heading'=> 'pageDots'
			),
			'arrowShape'=> array(
				'type'=>'textfield',
				'heading'=> 'arrowShape'
			),
        ),
    ));
}
function adminz_flickity_function($atts){
	wp_enqueue_script( 'adminz_flickity_js', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.pkgd.min.js', array('jquery') );
	wp_enqueue_style( 'adminz_flickity_css', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.min.css');
	$adminz = new Adminz;
	extract(shortcode_atts(array(
        'ids'    => '',
        'draggable'=> 'true', 
        'freeScroll'=> 'false',
        'wrapAround'=> 'true',
        'groupCells'=> 'false',
        'autoPlay'=> 'false',
        'pauseAutoPlayOnHover'=> 'false',
        'fade'=> 'false',
        'adaptiveHeight'=> 'true',
        'asNavFor'=> '',
        'selectedAttraction'=> '0.025',
		'friction'=> '0.28',
		'lazyLoad'=> 'false',
		'cellSelector'=> '',
		'initialIndex'=> '0',
		'accessibility'=> 'true',
		'setGallerySize'=> 'true',
		'resize'=> 'true',
		'cellAlign'=> 'left',
		'percentPosition'=> 'false',
		'rightToLeft'=> 'false',
		'prevNextButtons'=> 'true',
		'pageDots'=> 'true',
		'arrowShape'=> ''
    ), $atts));

    ob_start();
    ?>
    <div class="adminz_flickity" data-flickity='{ 
    	"draggable": <?php echo $draggable;?>,
        "freeScroll": <?php echo $freeScroll;?>,
        "wrapAround": <?php echo $wrapAround;?>,
        "groupCells": <?php echo $groupCells;?>,
        "autoPlay": <?php echo $autoPlay;?>,
        "pauseAutoPlayOnHover": <?php echo $pauseAutoPlayOnHover;?>,
        "fade": <?php echo $fade;?>,
        "adaptiveHeight": <?php echo $adaptiveHeight;?>,
        "asNavFor": "<?php echo $asNavFor;?>",
        "selectedAttraction": <?php echo $selectedAttraction;?>,
		"friction": <?php echo $friction;?>,
		"lazyLoad": <?php echo $lazyLoad;?>,
		"cellSelector": "<?php echo $cellSelector;?>",
		"initialIndex": <?php echo $initialIndex;?>,
		"accessibility": <?php echo $accessibility;?>,
		"setGallerySize": <?php echo $setGallerySize;?>,
		"resize": <?php echo $resize;?>,
		"cellAlign": "<?php echo $cellAlign;?>",
		"percentPosition": <?php echo $percentPosition;?>,
		"rightToLeft": <?php echo $rightToLeft;?>,
		"prevNextButtons": <?php echo $prevNextButtons;?>,
		"pageDots": <?php echo $pageDots;?>,
		"arrowShape": "<?php echo $arrowShape;?>"
	}'>
	  <?php 
		$ids = explode(',', $ids);
		if(!empty($ids) and is_array($ids)){
			foreach ($ids as $id) {
				echo wp_get_attachment_image($id,'full');
			}
		}
		?>
	</div>
	<?php
    return ob_get_clean();
}
/*add_action('wp_enqueue_scripts',function (){
	wp_enqueue_script( 'adminz_flickity_js', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.js', array( 'jquery' ) );
	wp_enqueue_style( 'adminz_flickity_css', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.css');
});*/