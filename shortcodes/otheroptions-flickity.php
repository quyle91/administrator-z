<?php 
use Adminz\Admin\Adminz as Adminz;

add_action( 'wp_enqueue_scripts', function () {
   	wp_register_style( 'adminz_fix_flickity_css', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/custom_flickity.css');
   	wp_register_style( 'adminz_flickity_css', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.min.css');
   	wp_register_script( 'adminz_flickity_config' , plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/adminz_flickity_config.js', array('adminz_flickity_js'));
	wp_register_script( 'adminz_flickity_js', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.pkgd.min.js', array('jquery'));
},101 );

$flickity_attributes = [
	'draggable'=> ['heading'=>'draggable', 'type'=>'textfield' , 'jsvar'=>'draggable', 'default'=> 'true'],
	'freescroll'=> ['heading'=>'freeScroll', 'type'=>'textfield' , 'jsvar'=>'freeScroll', 'default'=> 'false'],
	'contain'=> ['heading'=>'contain', 'type'=>'textfield' , 'jsvar'=>'contain', 'default'=> 'true'],
	'wraparound'=> ['heading'=>'wrapAround', 'type'=>'textfield' , 'jsvar'=>'wrapAround', 'default'=> 'true'],
	'groupcells'=> ['heading'=>'groupCells', 'type'=>'textfield' , 'jsvar'=>'groupCells', 'default'=> 'false'],
	'autoplay'=> ['heading'=>'autoPlay', 'type'=>'textfield' , 'jsvar'=>'autoPlay', 'default'=> 'false'],
	'pauseautoplayonhover'=> ['heading'=>'pauseAutoPlayOnHover', 'type'=>'textfield' , 'jsvar'=>'pauseAutoPlayOnHover', 'default'=> 'false'],
	'adaptiveheight'=> ['heading'=>'adaptiveHeight', 'type'=>'textfield' , 'jsvar'=>'adaptiveHeight', 'default'=> 'true'],
	'whatcss'=> ['heading'=>'whatCSS', 'type'=>'textfield' , 'jsvar'=>'whatCSS', 'default'=> 'false'],
	'asnavfor'=> ['heading'=>'asNavFor', 'type'=>'textfield' , 'jsvar'=>'asNavFor', 'default'=> ""],
	'selectedattraction'=> ['heading'=>'selectedAttraction', 'type'=>'textfield' , 'jsvar'=>'selectedAttraction', 'default'=> '0.025'],
	'friction'=> ['heading'=>'friction', 'type'=>'textfield' , 'jsvar'=>'friction', 'default'=> '0.28'],
	'imagesloaded'=> ['heading'=>'imagesLoaded', 'type'=>'textfield' , 'jsvar'=>'imagesLoaded', 'default'=> 'true'],
	'lazyload'=> ['heading'=>'lazyLoad', 'type'=>'textfield' , 'jsvar'=>'lazyLoad', 'default'=> 'true'],
	'cellselector'=> ['heading'=>'cellSelector', 'type'=>'textfield' , 'jsvar'=>'cellSelector', 'default'=> ''],
	'initialindex'=> ['heading'=>'initialIndex', 'type'=>'textfield' , 'jsvar'=>'initialIndex', 'default'=> '0'],
	'accessibility'=> ['heading'=>'accessibility', 'type'=>'textfield' , 'jsvar'=>'accessibility', 'default'=> 'true'],
	'setgallerysize'=> ['heading'=>'setGallerySize', 'type'=>'textfield' , 'jsvar'=>'setGallerySize', 'default'=> 'true'],
	'resize'=> ['heading'=>'resize', 'type'=>'textfield' , 'jsvar'=>'resize', 'default'=> 'true'],
	'cellalign'=> ['heading'=>'cellAlign', 'type'=>'textfield' , 'jsvar'=>'cellAlign', 'default'=> 'left'],
	'percentposition'=> ['heading'=>'percentPosition', 'type'=>'textfield' , 'jsvar'=>'percentPosition', 'default'=> 'false'],
	'righttoleft'=> ['heading'=>'rightToLeft', 'type'=>'textfield' , 'jsvar'=>'rightToLeft', 'default'=> 'false'],
	'prevnextbuttons'=> ['heading'=>'prevNextButtons', 'type'=>'textfield' , 'jsvar'=>'prevNextButtons', 'default'=> 'true'],
	'pagedots'=> ['heading'=>'pageDots', 'type'=>'textfield' , 'jsvar'=>'pageDots', 'default'=> 'false'],
	'arrowshape'=> ['heading'=>'arrowShape', 'type'=>'textfield' , 'jsvar'=>'arrowShape', 'default'=> ''],
];

add_action('ux_builder_setup', function () use($flickity_attributes) {
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
                'default' => 'false'
            ),
            'thumbnailscol'=> array(
				'type'=>'slider',
				'min'=> 1,
				'max'=> 24,
				'default'=>1,
				'heading'=> 'Thumbnails columns'
			),
			// js library document link 
			'note'=> array(
				'type'=>'group',
				'heading'=> 'JS Document',
				'description'=> "https://flickity.metafizzy.co/options.html",
				'options' => $flickity_attributes
			),			
        ),
    ));
});

add_shortcode('adminz_flickity', function ($atts) use($flickity_attributes){
	wp_enqueue_script( 'adminz_flickity_config');
	if(!in_array('Flatsome', [wp_get_theme()->name, wp_get_theme()->parent_theme])){		
		wp_enqueue_style( 'adminz_flickity_css');
	}
	$adminz = new Adminz;
	$mapdefault = [		
		'ids'    => '',
        'usethumbnails'=>false,
        'thumbnailscol' => 1,
	];
	foreach ($flickity_attributes as $key => $value) {
		$mapdefault[$key] = $value['default'];
	}
	$map = shortcode_atts($mapdefault, $atts);		
	$randomclass = "adminz_flickity".wp_rand();
	$map['asnavfor'] = $map['asnavfor']? $map['asnavfor'] : ".".$randomclass;
	extract($map);		
    ob_start();    
    $data_flickity = [];    
    foreach ($map as $key => $value) {
    	if($value){
    		if(!($value == 'true' or $value == 'false' or is_bool($value))){
    			$value = '"'.$value.'"';
			}			
			$jskey = isset($flickity_attributes[$key]['jsvar']) ?  $flickity_attributes[$key]['jsvar'] : $key;
			$data_flickity[]='"'.$jskey.'":'.$value.'';
    	}
    }
    ?> 
    <style type="text/css">
		.<?php echo $randomclass; ?>:not(.flickity-enabled){
			-js-display: flex; display: -webkit-box; display: -ms-flexbox; -webkit-box-orient: horizontal; -webkit-box-direction: normal; -ms-flex-flow: row wrap; flex-flow: row wrap; white-space: nowrap; overflow-y: hidden; overflow-x: hidden; width: auto;
		}
		.<?php echo $randomclass; ?>:not(.flickity-enabled)>.col{
			display: inline-block;
		}
	</style>   
    <div class="adminz_flickity slider mb-half <?php echo $randomclass; ?> row" data-adminz='{<?php echo implode(",", $data_flickity ); ?>}' >
	  <?php 
		$idss = explode(',', $ids);
		if($ids and is_array($idss) and !empty($idss) ){
			foreach ($idss as $id) {
				$bigcol = $usethumbnails ? 1 : $thumbnailscol;
				?>
				<div class="col" style="width: <?php echo (100/$bigcol); ?>% !important;">
					<a href="javascript:void(0);">
					<?php echo wp_get_attachment_image($id,'full',false); ?>
					</a>
				</div>
				<?php		
			}
		}
		?>
	</div>
	<!-- thumbnails -->
	<?php if($usethumbnails){		
	$data_flickity2 = [];
	$map['contain'] = "true";
	$map['wrapAround'] = "false";
	$map['pagedots'] = 'false';
	foreach ($map as $key => $value) {
    	if($value){
    		if(!($value == 'true' or $value == 'false' or is_bool($value))){
    			$value = '"'.$value.'"';
			}			
    		$jskey = isset($flickity_attributes[$key]['jsvar']) ?  $flickity_attributes[$key]['jsvar'] : $key;
			$data_flickity2[]='"'.$jskey.'":'.$value.'';
    	}
    }
	?>	
	<div class="adminz_flickity slider product-thumbnails row <?php echo $randomclass; ?>" data-adminz='{<?php echo implode(",", $data_flickity2 ); ?>}'>
	  <?php 
		$idss = explode(',', $ids);
		if($ids and is_array($idss) and !empty($idss) ){
			foreach ($idss as $key=> $id) {
				?>
				<div class="col" style="width: <?php echo (100/$thumbnailscol); ?>% !important;">
					<a href="javascript:void(0);">
					<?php echo wp_get_attachment_image($id,'thumbnail',false); ?>
					</a>
				</div>
				<?php				
 			}
		}
		?>
	</div>	
	<?php }
    return ob_get_clean();
});

