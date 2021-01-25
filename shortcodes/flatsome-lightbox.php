<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'adminz_lightbox');

function adminz_lightbox(){
	$adminz = new Adminz;
    add_ux_builder_shortcode('adminz_lightbox', array(
        'name'      => __('Lightbox'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'ux_banner' . '.svg',
        'info'      => '{{ id }}',
        'options' => array(
        	'auto_open' => array(
	          'type' => 'select',
	          'heading' => __( 'Auto open','ux-builder' ),
	          'default' => 'false',
	          'options' => array(
	              'false' => 'False',
	              'true' => 'True',
	          )
	    	),
	    	'auto_timer' => array(
				'type'       => 'slider',
				'heading'	=> __('Auto timer'),
				'default'    => 0,
				'min'	=> 0,
				'step'=> 500,
				'unit'=>"",
				'max'=> 10000
			),
			'auto_show' => array(
	          'type' => 'select',
	          'heading' => __( 'Auto show','ux-builder' ),
	          'default' => 'once',
	          'options' => array(
	              'once' => 'Once',
	              'always' => 'Always',
	          )
	    	),
            'id' => array(
                'type'       => 'textfield',
                'heading'    => __('Lightbox ID'),
            ),
            'width' => array(
	            'type' => 'scrubfield',
	            'heading' => __( 'Width' ),
	            'default' => '650px',
	        ),
            'padding' => array(
                'type' => 'scrubfield',
	            'heading' => __( 'Padding' ),
	            'default' => '20px',
	            'min' => '0px'
            ),
            'block' => array(
	          'type' => 'select',
		      'heading' => __( 'Block', 'flatsome' ),
		      'config' => array(
		        'placeholder' => __( 'Select', 'flatsome' ),
		        'postSelect' => array(
		          'post_type' => array( 'blocks' )
		        ),
		      )
	    	),
        ),
    ));
}
add_shortcode('adminz_lightbox', 'adminz_lightbox_shortcode');
function adminz_lightbox_shortcode($atts, $content = null ) {
	extract(shortcode_atts(array(
		'auto_open'=> "false",
		'auto_timer'=> '0',
		'auto_show'=> 'once',		
    	'id' => '',
    	'width'=> '650px',
    	'padding' => '20px',
    	'block' =>''
    ), $atts));
    ob_start();

	$shortcode = '[lightbox auto_open="'.$auto_open.'" auto_timer="'.$auto_timer.'" auto_show="'.$auto_show.'" id="'.$id.'" width="'.$width.'" padding="'.$padding.'"';
	$shortcode.= ']';

	// shortcode content 
	$shortcode .= '[block id="'.$block.'"]';
	
	$shortcode.='[/lightbox]';
	echo do_shortcode($shortcode);
    $return = ob_get_clean();
    return $return;
}