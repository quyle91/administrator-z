<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'mk_text_readmore');

function mk_text_readmore(){
	$adminz = new Adminz;
	add_ux_builder_shortcode('mk_readmore_readless', array(
		'type' => 'container',
		'name'      => __('Read more Read Less '),
		'category'  => $adminz->get_adminz_menu_title(),
		'priority'  => 1,
		'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'accordion' . '.svg',
		'info' => '{{ title }}',
    	'presets' => array(
	        array(
	            'name' => __( 'Default' ),
	            'content' => '
	                [mk_readmore_readless]
	                    <h3>This is a simple headline</h3><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</p>
	                [/mk_readmore_readless]
	            '
	        ),
	    ),
	    'options' => array(
	    	'max_height' => array(
	            'type' => 'scrubfield',
	            'responsive' => true,
	            'heading' => __( 'Max height' ),
	            'default' => '10em',
	            'min' => 0,
	        ),
	    	'min_height' => array(
	            'type' => 'scrubfield',
	            'responsive' => true,
	            'heading' => __( 'Min height' ),
	            'default' => '5em',
	            'min' => 0,
	        ),
	        'gap' => array(
	            'type' => 'scrubfield',
	            'responsive' => true,
	            'heading' => __( 'Gap after content' ),
	            'default' => '30px',
	            'min' => 0,
	        ),
	        'readmore'             => array(
				'type'       => 'textfield',
				'heading'	=> __('Read more text'),
				'default'    => 'Read more',
				'holder'    => 'Read more',
			),
			'readless'             => array(
				'type'       => 'textfield',
				'heading'	=> __('Read less text'),
				'default'    => 'Read less',
				'holder'    => 'Read less',
			),	        
	        'max_height_expand' => array(
	            'type' => 'scrubfield',
	            'responsive' => true,
	            'heading' => __( 'Max height expanded' ),
	            'default' => '1000em',
	            'min' => 0,
	        ),
	    )
	) );
};

add_shortcode('mk_readmore_readless', 'mk_readmore_readless_shortcode');
function mk_readmore_readless_shortcode($atts, $content = null ) {
    extract(shortcode_atts(array(
    	'gap' => '30px',
    	'readmore'=> 'Read more',
    	'readless' => 'Read less',
        'min_height'    => '5em',
        'max_height'    => '10em',
        'max_height_expand' => '1000em'
    ), $atts));
    ob_start();
    $stylecss = array(
    	'overflow: hidden'.";",
    	'min-height: '.$min_height.";",
    	'max-height: '.$max_height.";",
	    'transition: max-height 0.3s ease-out'
    );
    ?>
    <div class="mk_readmore_readless">
    <div class="mk_readmore_readless_content relative">
    	<div class="inner" style="<?php echo implode(" ", $stylecss) ?>;">
    	<?php 
    	echo flatsome_contentfix( $content );
    	?>
    	</div>    
	    <?php 
	    	echo '<div class="bot" style="position: absolute; bottom: 0; left: 0; background: rgb(254,254,254); background: linear-gradient(0deg, rgba(254,254,254,1) 0%, rgba(255,255,255,0) 100%); width: 100%; height: '.$gap.' "></div>';
		?>
	</div>
	<?php
    	$button = '[button text="'.$readmore.'" letter_case="lowercase" color="white" size="smaller"]';
    	echo do_shortcode( '[gap height="20px"]');
    	echo do_shortcode($button);
    ?>
    </div>
    <?php
    $return = ob_get_clean();
    if($content) {
    	add_action('wp_footer','mk_readmore_readless_script');
    	$unset = explode("em", $max_height)[0];    	
    	$buffer = '<style> 
    	.mk_readmore_readless_content.unset>.inner{max-height: '.(5*$unset).'em !important;}
    	.mk_readmore_readless_content.unset .bot{background: none !important;}
    	.mk_readmore_readless_content.unset+*+.button:before{content:"'.$readless.'";}
    	.mk_readmore_readless_content.unset+*+.button span{display: none;}
    	</style>';
    	echo str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
    }
    return $return;
}

function mk_readmore_readless_script(){
	?>
	<script type="text/javascript">
		jQuery(document).on('click','.mk_readmore_readless>.button',function(e){
		    e.preventDefault();
		    var parent = jQuery(this).closest(".mk_readmore_readless");
		    var target = parent.find(".mk_readmore_readless_content");
		    jQuery(target).toggleClass('unset');
		});


	</script>
	<?php
}