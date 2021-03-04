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
			'imagesLoaded'=> array(
	        	'type'=>'textfield',
	        	'heading'=> 'imagesLoaded'
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
	if(!in_array('Flatsome', [wp_get_theme()->name, wp_get_theme()->parent_theme])){
		wp_enqueue_script( 'adminz_flickity_js', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.pkgd.min.js', array('jquery') );
		wp_enqueue_style( 'adminz_flickity_css', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.min.css');
	}	
	$adminz = new Adminz;
	$map = shortcode_atts(array(
        'ids'    => '',
        'draggable'=> 'true', 
        'freeScroll'=> 'false',
        'imagesLoaded' => 'true',
        'wrapAround'=> 'true',
        'groupCells'=> 'false',
        'autoPlay'=> 'false',
        'pauseAutoPlayOnHover'=> 'false',
        'fade'=> 'false',
        'adaptiveHeight'=> 'true',
        'asNavFor'=> '.adminz_flickity',
        'selectedAttraction'=> '0.025',
		'friction'=> '0.28',
		'lazyLoad'=> 'false',
		'cellSelector'=> '',
		'initialIndex'=> '0',
		'accessibility'=> 'true',
		'setGallerySize'=> 'true',
		'resize'=> 'false',
		'cellAlign'=> 'left',
		'percentPosition'=> 'false',
		'rightToLeft'=> 'false',
		'prevNextButtons'=> 'true',
		'pageDots'=> 'true',
		'arrowShape'=> ''
    ), $atts);    
	extract($map);	
    ob_start();
    $data_flickity = '"":""';
    foreach ($map as $key => $value) {
    	if($value){
    		$data_flickity.=',"'.$key.'":"'.$value.'"';
    	}
    }
    ?>
    <div class="adminz_flickity main-carousel" data-flickity='{<?php echo $data_flickity; ?>}'>
	  <?php 
		$idss = explode(',', $ids);
		if(!empty($idss) and is_array($idss)){
			foreach ($idss as $id) {
				$src = wp_get_attachment_image_src( $id, 'full',false );
  				echo '<img src="'.$src[0].'"/>';
				//echo wp_get_attachment_image($id,'full');
				?>
				<!-- <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/82/orange-tree.jpg" alt="orange tree" /> -->
				<?php
				
			}
		}
		?>
	</div>
	<div class="adminz_flickity main-carousel" data-flickity='{<?php echo $data_flickity; ?>}'>
	  <?php 
		$idss = explode(',', $ids);
		if(!empty($idss) and is_array($idss)){
			foreach ($idss as $id) {
				$src = wp_get_attachment_image_src( $id, 'full',false );
  				echo '<div style="max-width: 20%"/><img src="'.$src[0].'"/></div>';
				//echo wp_get_attachment_image($id,'full');
				?>
				<!-- <img src="https://s3-us-west-2.amazonaws.com/s.cdpn.io/82/orange-tree.jpg" alt="orange tree" /> -->
				<?php
				
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