<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'adminz_random');
function adminz_random(){
	$adminz = new Adminz;
	add_ux_builder_shortcode('adminz_random', array(
        'name'      => __('Random Number'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'countdown' . '.svg',
        'options' => array(
            'textbefore' => array(
                'type'       => 'textfield',
                'heading'   => __('Text before number'),
                'default'    => '',
            ),
            'min' => array(
                'type'       => 'scrubfield',
                'heading'    => 'Start number',
                'unit'    => '',
                'default'	=> 0,
            ),
            'max' => array(
                'type'       => 'scrubfield',
                'heading'    => 'End number',
                'unit'    => '',
                'default'	=> 99,
                'max' => mt_getrandmax()
            ),
            'textafter' => array(
                'type'       => 'textfield',
                'heading'   => __('Text after number'),
                'default'    => '',
            ),            
            'use_global'=>array(
            	'type' => 'checkbox',
            	'heading'	=>'Use Global'                
            ),
            'use_inline'=>array(
                'type' => 'checkbox',
                'heading'   =>'Inline Element',
                'default' => 'true'
            )
        ),
    ));
}