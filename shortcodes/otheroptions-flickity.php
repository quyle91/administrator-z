<?php 
use Adminz\Admin\Adminz as Adminz;
function adminz_enqueue_flickity() {
   	wp_register_style( 'adminz_fix_flickity_css', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/custom_flickity.css');
   	wp_register_style( 'adminz_flickity_css', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.min.css');
   	wp_register_script( 'adminz_flickity_config' , plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/adminz_flickity_config.js', array('adminz_flickity_js'));
	wp_register_script( 'adminz_flickity_js', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.pkgd.min.js', array('jquery') );
	
}
add_action( 'wp_enqueue_scripts', 'adminz_enqueue_flickity' );

add_action('ux_builder_setup', 'adminz_flickity');
add_shortcode('adminz_flickity', 'adminz_flickity_function');
function adminz_flickity(){
	$adminz = new Adminz;
	add_ux_builder_shortcode('adminz_flickity', array(
		'info' => '{{ heading }}',
        'name'      => __('Flickity'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'slider.svg',
        'scripts' => array(
        	'adminz_flickity_js' => plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.pkgd.min.js',
	        'adminz_flickity_config' => plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/adminz_flickity_config.js',	        
	    ),
	    'styles' => array(
	        'adminz_fix_flickity_css' => plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/custom_flickity.css',
	    ),
        'options' => array(
            'ids'             => array(
				'type'       => 'gallery',
				'heading'	=> __('Images'),
			),
			'usethumbnails'=>array(
                'type' => 'checkbox',
                'heading'   =>'Use small thumbnails',
                'default' => 'true'
            ),
            'thumbnailscol'=> array(
				'type'=>'slider',
				'min'=> 1,
				'max'=> 24,
				'default'=>4,
				'heading'=> 'Thumbnails columns'
			),
			// js library document link 
			'note'=> array(
				'type'=>'group',
				'heading'=> 'JS Document',
				'description'=> "https://flickity.metafizzy.co/options.html",
				'options' => array(
				    'draggable'=> array(
						'type'=>'textfield',
						'heading'=> 'draggable'
					),
			        'freeScroll'=> array(
			        	'type'=>'textfield',
			        	'heading'=> 'freeScroll'
			        ),
			        'contain'=> array(
			        	'type'=>'textfield',
			        	'heading'=> 'contain'
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
			),			
        ),
    ));
}
function adminz_flickity_function($atts){	
	wp_enqueue_style( 'adminz_fix_flickity_css');
	wp_enqueue_script( 'adminz_flickity_config');
	if(!in_array('Flatsome', [wp_get_theme()->name, wp_get_theme()->parent_theme])){		
		wp_enqueue_style( 'adminz_flickity_css');
		wp_enqueue_script( 'adminz_flickity_js');
		wp_enqueue_script( 'adminz_flickity_config');
	}
	$adminz = new Adminz;
	$map = shortcode_atts(array(
        'ids'    => '',
        'usethumbnails'=>true,
        'thumbnailscol' => 4,
        // slider args
        'draggable'=> 'true', 
        'freeScroll'=> 'false',
        'contain'=> 'true',
        'imagesLoaded' => 'true',
        'wrapAround'=> 'true',
        'groupCells'=> 'false',
        'autoPlay'=> 'false',
        'pauseAutoPlayOnHover'=> 'false',        
        'adaptiveHeight'=> 'true',
        'asNavFor'=> '.adminz_flickity',
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
		'pageDots'=> 'false',
		'arrowShape'=> '',
		/*'watchCSS'=> 'true'*/
    ), $atts);    
	extract($map);		
    ob_start();    
    $data_flickity = [];
    foreach ($map as $key => $value) {
    	if($value){
    		if(!($value == 'true' or $value == 'false' or is_bool($value))){
    			$value = '"'.$value.'"';
			}
			$data_flickity[]='"'.$key.'":'.$value.'';			
    	}
    }
    ?>    
    <div class="adminz_flickity slider mb-half" data-adminz='{<?php echo implode(",", $data_flickity ); ?>}'>
	  <?php 
		$idss = explode(',', $ids);
		if(!empty($idss) and is_array($idss)){
			foreach ($idss as $id) {
				$src = wp_get_attachment_image_src( $id, 'full',false );
				if($src){
					echo '<img src="'.$src[0].'"/>';
				}  				
			}
		}
		?>
	</div>
	<?php if($usethumbnails){ ?>	
		<?php 
		$map['wrapAround'] = 'false';
		$data_flickity2 = [];
		foreach ($map as $key => $value) {
	    	if($value){
	    		if(!($value == 'true' or $value == 'false' or is_bool($value))){
	    			$value = '"'.$value.'"';
				}			
	    		$data_flickity2[]='"'.$key.'":'.$value.'';
	    	}
	    }
		?>	
		<div class="adminz_flickity slider product-thumbnails row" data-adminz='{<?php echo implode(",", $data_flickity2 ); ?>}'>
		  <?php 
			$idss = explode(',', $ids);
			if(!empty($idss) and is_array($idss)){
				foreach ($idss as $id) {
					$src = wp_get_attachment_image_src( $id, array(100,100),false );
					if($src){
						?>
						<div class="col" style="width: <?php echo (100/$thumbnailscol); ?>% !important;">
							<a>
								<img src="<?php echo $src[0]; ?> "/>
							</a>
						</div>
						<?php 
					}	  				
	 			}
			}
			?>
		</div>
	<?php } ?>
	<?php
    return ob_get_clean();
}
