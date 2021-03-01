<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'adminz_fotorama');
add_shortcode('adminz_fotorama', 'adminz_fotorama_function');
function adminz_fotorama(){
	$adminz = new Adminz;
	add_ux_builder_shortcode('adminz_fotorama', array(
		'info' => '{{ title }}',
        'name'      => __('Fotorama'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'ux_stack.svg',
        'options' => array(
            'ids'             => array(
				'type'       => 'gallery',
				'heading'	=> __('Images'),
			),
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
				'heading'=> '',
				'default'=> '',
			),
			'direction'=>array(
				'type' =>'textfield',
				'heading'=> '',
				'default'=> '',
			),
			'spinner'=>array(
				'type' =>'textfield',
				'heading'=> 'spinner',
				'default'=> '',
			),
			
        ),
    ));
}
function adminz_fotorama_function($atts){
	wp_enqueue_script( 'adminz_fotorama_js', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.js', array( 'jquery' ) );
	wp_enqueue_style( 'adminz_fotorama_css', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.css');
	$adminz = new Adminz;
	extract(shortcode_atts(array(
        'ids'    => '',
        'width'=> '',
		'minwidth'=> '',
		'maxwidth'=> '100%',
		'height'=> '',
		'minheight'=> '',
		'maxheight'=> '100%',
		'ratio'=> '1/1',
		'margin'=> '',
		'glimpse'=> '',
		'nav'=> 'thumbs',
		'navposition'=> 'bottom',
		'navwidth'=> '100%',
		'thumbwidth'=> '100',
		'thumbheight'=> '100',
		'thumbmargin'=> '5',
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
		'loop'=> '',
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
    ), $atts));

    ob_start();
    ?>
    <!-- Add images to <div class="fotorama"></div> -->
	<div class="adminz_fotorama" 
		data-width="<?php echo $width; ?>"
		data-minwidth="<?php echo $minwidth; ?>"
		data-maxwidth="<?php echo $maxwidth; ?>"
		data-height="<?php echo $height; ?>"
		data-minheight="<?php echo $minheight; ?>"
		data-maxheight="<?php echo $maxheight; ?>"
		data-ratio="<?php echo $ratio; ?>"
		data-margin="<?php echo $margin; ?>"
		data-glimpse="<?php echo $glimpse; ?>"
		data-nav="<?php echo $nav; ?>"
		data-navposition="<?php echo $navposition; ?>"
		data-navwidth="<?php echo $navwidth; ?>"
		data-thumbwidth="<?php echo $thumbwidth; ?>"
		data-thumbheight="<?php echo $thumbheight; ?>"
		data-thumbmargin="<?php echo $thumbmargin; ?>"
		data-thumbborderwidth="<?php echo $thumbborderwidth; ?>"
		data-allowfullscreen="<?php echo $allowfullscreen; ?>"
		data-fit="<?php echo $fit; ?>"
		data-thumbfit="<?php echo $thumbfit; ?>"
		data-transition="<?php echo $transition; ?>"
		data-clicktransition="<?php echo $clicktransition; ?>"
		data-transitionduration="<?php echo $transitionduration; ?>"
		data-captions="<?php echo $captions; ?>"
		data-hash="<?php echo $hash; ?>"
		data-startindex="<?php echo $startindex; ?>"
		data-loop="<?php echo $loop; ?>"
		data-autoplay="<?php echo $autoplay; ?>"
		data-stopautoplayontouch="<?php echo $stopautoplayontouch; ?>"
		data-keyboard="<?php echo $keyboard; ?>"
		data-arrows="<?php echo $arrows; ?>"
		data-click="<?php echo $click; ?>"
		data-swipe="<?php echo $swipe; ?>"
		data-trackpad="<?php echo $trackpad; ?>"
		data-shuffle="<?php echo $shuffle; ?>"
		data-direction="<?php echo $direction; ?>"
		data-spinner="<?php echo $spinner; ?>"
		>
		<?php 
		$ids = explode(',', $ids);
		if(!empty($ids) and is_array($ids)){
			foreach ($ids as $id) {
				echo wp_get_attachment_image($id,'full');
			}
		}
		?>
	</div>
	<script type="text/javascript">
		jQuery(function($){
			$('.adminz_fotorama').each(function(){
				$(this).fotorama({
				  
				});
			});
		});
	</script>
	<?php
    return ob_get_clean();
}
/*add_action('wp_enqueue_scripts',function (){
	wp_enqueue_script( 'adminz_fotorama_js', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.js', array( 'jquery' ) );
	wp_enqueue_style( 'adminz_fotorama_css', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.css');
});*/