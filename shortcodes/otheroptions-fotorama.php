<?php 
use Adminz\Admin\Adminz as Adminz;

add_action( 'wp_enqueue_scripts', function () {
	wp_register_style( 'adminz_fotorama_css', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.css');
	wp_register_style( 'adminz_fotorama_fix_css', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama_fix.css');
   	wp_register_script( 'adminz_fotorama_js', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.js', array( 'jquery' ) );
	wp_register_script( 'adminz_fotorama_config' , plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/adminz_fotorama_config.js', array('jquery'));
},101 );

$fotorama_attributes = [
	'width'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'width'],
	'minwidth'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'minwidth'],
	'maxwidth'=>['default'=> '100%', 'type'=>'textfield', 'heading'=> 'maxwidth'],
	'height'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'height'],
	'minheight'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'minheight'],
	'maxheight'=>['default'=> '100%', 'type'=>'textfield', 'heading'=> 'maxheight'],
	'ratio'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'ratio'],
	'margin'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'margin'],
	'glimpse'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'glimpse'],
	'nav'=>['default'=> 'thumbs', 'type'=>'textfield', 'heading'=> 'nav'],
	'navposition'=>['default'=> 'bottom', 'type'=>'textfield', 'heading'=> 'navposition'],
	'navwidth'=>['default'=> '100%', 'type'=>'textfield', 'heading'=> 'navwidth'],
	'thumbwidth'=>['default'=> '100', 'type'=>'textfield', 'heading'=> 'thumbwidth'],
	'thumbheight'=>['default'=> '100', 'type'=>'textfield', 'heading'=> 'thumbheight'],
	'thumbmargin'=>['default'=> '10', 'type'=>'textfield', 'heading'=> 'thumbmargin'],
	'thumbborderwidth'=>['default'=> '5', 'type'=>'textfield', 'heading'=> 'thumbborderwidth'],
	'allowfullscreen'=>['default'=> 'true', 'type'=>'textfield', 'heading'=> 'allowfullscreen'],
	'fit'=>['default'=> 'scaledown', 'type'=>'textfield', 'heading'=> 'fit'],
	'thumbfit'=>['default'=> 'cover', 'type'=>'textfield', 'heading'=> 'thumbfit'],
	'transition'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'transition'],
	'clicktransition'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'clicktransition'],
	'transitionduration'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'transitionduration'],
	'captions'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'captions'],
	'hash'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'hash'],
	'startindex'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'startindex'],
	'loop'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'loop'],
	'autoplay'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'autoplay'],
	'stopautoplayontouch'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'stopautoplayontouch'],
	'keyboard'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'keyboard'],
	'arrows'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'arrows'],
	'click'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'click'],
	'swipe'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'swipe'],
	'trackpad'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'trackpad'],
	'shuffle'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'shuffle'],
	'direction'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'direction'],
	'spinner'=>['default'=> '', 'type'=>'textfield', 'heading'=> 'spinner'],
];	

add_action('ux_builder_setup', function () use ($fotorama_attributes){
	$adminz = new Adminz;
	add_ux_builder_shortcode('adminz_fotorama', array(
		'info' => '{{ title }}',
        'name'      => __('Fotorama'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'slider.svg',
        'scripts' => array(
	        'adminz_fotorama_js' => plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.js',
	        'adminz_fotorama_config' => plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/adminz_fotorama_config.js'
	    ),
	    'styles' => array(
	        'adminz_fotorama_css' => plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.css'
	    ),
        'options' => array(
            'ids'             => array(
				'type'       => 'gallery',
				'heading'	=> __('Images'),
			),
			// js library document link 
			'note'=> array(
				'type'=>'group',
				'heading'=> 'JS Document',
				'description'=> "https://fotorama.io/docs/4/options/",
				'options' => $fotorama_attributes
			)
        ),
    ));
});

add_shortcode('adminz_fotorama', function ($atts) use ($fotorama_attributes){
		wp_enqueue_style( 'adminz_fotorama_css');		
		wp_enqueue_script( 'adminz_fotorama_js');
		wp_enqueue_script( 'adminz_fotorama_config');
		$adminz = new Adminz;
		$defaultmap = ['ids'=>'','auto'=>'false'];
		foreach ($fotorama_attributes as $key => $value) {
			$defaultmap[$key] = $value['default']; 
		}
		$map = shortcode_atts($defaultmap, $atts);
	    extract($map);
	    
	    $datahtml = "";
	    foreach ($map as $key => $value) {
	    	if($value){
	    		$datahtml.= 'data-'.$key.'="'.$value.'" ';
	    	}
	    }
	    ob_start();
	    ?>    
		<div class="adminz_fotorama" <?php echo $datahtml; ?> style="-js-display: flex; display: -webkit-box; display: -ms-flexbox; -webkit-box-orient: horizontal; -webkit-box-direction: normal; -ms-flex-flow: row wrap; flex-flow: row wrap; white-space: nowrap; overflow-y: hidden; overflow-x: hidden; width: auto; ">
			<?php 
			$ids = explode(',', $ids);
			if(!empty($ids) and is_array($ids)){
				foreach ($ids as $id) {
					echo wp_get_attachment_image($id,'full',false, ['style'=>'display: inline-block; ']);
				}
			}
			?>
		</div>
		<?php
	    return ob_get_clean();
	}
);
