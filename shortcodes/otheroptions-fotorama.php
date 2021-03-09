<?php 
use Adminz\Admin\Adminz as Adminz;

function adminz_enqueue_fotorama() {
	wp_register_style( 'adminz_fotorama_css', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.css');
	wp_register_style( 'adminz_fotorama_fix_css', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama_fix.css');
   	wp_register_script( 'adminz_fotorama_js', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.js', array( 'jquery' ) );
	wp_register_script( 'adminz_fotorama_config' , plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/adminz_fotorama_config.js', array('jquery'));
}
add_action( 'wp_enqueue_scripts', 'adminz_enqueue_fotorama',101 );

add_action('ux_builder_setup', 'adminz_fotorama');
add_shortcode('adminz_fotorama', 'adminz_fotorama_function');
function adminz_fotorama(){
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
				'options' => array(
					'width'=>array(
						'type' =>'textfield',
						'heading'=> 'width',
						'default'=> '',
					),
					'minwidth'=>array(
						'type' =>'textfield',
						'heading'=> 'minwidth',
						'default'=> '',
					),
					'maxwidth'=>array(
						'type' =>'textfield',
						'heading'=> 'maxwidth',
						'default'=> '',
					),
					'height'=>array(
						'type' =>'textfield',
						'heading'=> 'height',
						'default'=> '',
					),
					'minheight'=>array(
						'type' =>'textfield',
						'heading'=> 'minheight',
						'default'=> '',
					),
					'maxheight'=>array(
						'type' =>'textfield',
						'heading'=> 'maxheight',
						'default'=> '',
					),
					'ratio'=>array(
						'type' =>'textfield',
						'heading'=> 'ratio',
						'default'=> '',
					),
					'margin'=>array(
						'type' =>'textfield',
						'heading'=> 'margin',
						'default'=> '',
					),
					'glimpse'=>array(
						'type' =>'textfield',
						'heading'=> 'glimpse',
						'default'=> '',
					),
					'nav'=>array(
						'type' =>'textfield',
						'heading'=> 'nav',
						'default'=> '',
					),
					'navposition'=>array(
						'type' =>'textfield',
						'heading'=> 'navposition',
						'default'=> '',
					),
					'navwidth'=>array(
						'type' =>'textfield',
						'heading'=> 'navwidth',
						'default'=> '',
					),
					'thumbwidth'=>array(
						'type' =>'textfield',
						'heading'=> 'thumbwidth',
						'default'=> '',
					),
					'thumbheight'=>array(
						'type' =>'textfield',
						'heading'=> 'thumbheight',
						'default'=> '',
					),
					'thumbmargin'=>array(
						'type' =>'textfield',
						'heading'=> 'thumbmargin',
						'default'=> '',
					),
					'thumbborderwidth'=>array(
						'type' =>'textfield',
						'heading'=> 'thumbborderwidth',
						'default'=> '',
					),
					'allowfullscreen'=>array(
						'type' =>'textfield',
						'heading'=> 'allowfullscreen',
						'default'=> '',
					),
					'fit'=>array(
						'type' =>'textfield',
						'heading'=> 'fit',
						'default'=> '',
					),
					'thumbfit'=>array(
						'type' =>'textfield',
						'heading'=> 'thumbfit',
						'default'=> '',
					),
					'transition'=>array(
						'type' =>'textfield',
						'heading'=> 'transition',
						'default'=> '',
					),
					'clicktransition'=>array(
						'type' =>'textfield',
						'heading'=> 'clicktransition',
						'default'=> '',
					),
					'transitionduration'=>array(
						'type' =>'textfield',
						'heading'=> 'transitionduration',
						'default'=> '',
					),
					'captions'=>array(
						'type' =>'textfield',
						'heading'=> 'captions',
						'default'=> '',
					),
					'hash'=>array(
						'type' =>'textfield',
						'heading'=> 'hash',
						'default'=> '',
					),
					'startindex'=>array(
						'type' =>'textfield',
						'heading'=> 'startindex',
						'default'=> '',
					),
					'loop'=>array(
						'type' =>'textfield',
						'heading'=> 'loop',
						'default'=> '',
					),
					'autoplay'=>array(
						'type' =>'textfield',
						'heading'=> 'autoplay',
						'default'=> '',
					),
					'stopautoplayontouch'=>array(
						'type' =>'textfield',
						'heading'=> 'stopautoplayontouch',
						'default'=> '',
					),
					'keyboard'=>array(
						'type' =>'textfield',
						'heading'=> 'keyboard',
						'default'=> '',
					),
					'arrows'=>array(
						'type' =>'textfield',
						'heading'=> 'arrows',
						'default'=> '',
					),
					'click'=>array(
						'type' =>'textfield',
						'heading'=> 'click',
						'default'=> '',
					),
					'swipe'=>array(
						'type' =>'textfield',
						'heading'=> 'swipe',
						'default'=> '',
					),
					'trackpad'=>array(
						'type' =>'textfield',
						'heading'=> 'trackpad',
						'default'=> '',
					),
					'shuffle'=>array(
						'type' =>'textfield',
						'heading'=> 'shuffle',
						'default'=> '',
					),
					'direction'=>array(
						'type' =>'textfield',
						'heading'=> 'direction',
						'default'=> '',
					),
					'spinner'=>array(
						'type' =>'textfield',
						'heading'=> 'spinner',
						'default'=> '',
					),
				)
			)
        ),
    ));
}
function adminz_fotorama_function($atts){
	wp_enqueue_style( 'adminz_fotorama_css');
	wp_enqueue_style( 'adminz_fotorama_fix_css');
	wp_enqueue_script( 'adminz_fotorama_js');	
	wp_enqueue_script( 'adminz_fotorama_config');
	$adminz = new Adminz;
	$map = shortcode_atts(array(
        'ids'    => '',
        'width'=> '',
		'minwidth'=> '',
		'maxwidth'=> '100%',
		'height'=> '',
		'minheight'=> '',
		'maxheight'=> '100%',
		'ratio'=> '',
		'margin'=> '',
		'glimpse'=> '',
		'nav'=> 'thumbs',
		'navposition'=> 'bottom',
		'navwidth'=> '100%',
		'thumbwidth'=> '100',
		'thumbheight'=> '100',
		'thumbmargin'=> '15',
		'thumbborderwidth'=> '5',
		'allowfullscreen'=> 'true',
		'fit'=> 'scaledown',
		'thumbfit'=> 'cover',
		'transition'=> '',
		'clicktransition'=> '',
		'transitionduration'=> '',
		'captions'=> '',
		'hash'=> '',
		'startindex'=> '',
		'loop'=> 'true',
		'autoplay'=> '',
		'stopautoplayontouch'=> '',
		'keyboard'=> '',
		'arrows'=> 'always',
		'click'=> 'true',
		'swipe'=> 'true',
		'trackpad'=> 'true',
		'shuffle'=> '',
		'direction'=> '',
		'spinner'=> '',        
    ), $atts);
    extract($map);
    
    $datahtml = "";
    foreach ($map as $key => $value) {
    	if($value){
    		$datahtml.= 'data-'.$key.'="'.$value.'" ';
    	}
    }
    ob_start();
    ?>    
	<div class="adminz_fotorama" <?php echo $datahtml; ?>>
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
