<?php 
use Adminz\Admin\Adminz as Adminz;

add_shortcode('adminz_random', 'adminz_random_function');
function adminz_random_function($atts){    
	extract(shortcode_atts(array(
        'min'    => 1,
        'max'	=> 99,
        'textafter' => "",
        'textbefore'=>"",
        'use_global' =>false,
        'use_inline' =>true
    ), $atts));

    $return = mt_rand(intval($min),intval($max));
    
    if($use_global){
        if(!isset($GLOBALS['adminz']['random'])){
            $GLOBALS['adminz']['random'] = $return;
        }
        $return = $GLOBALS['adminz']['random'];
    }

    $use_inline = $use_inline?  "span" : "div";
    return sprintf('<%1$s>%2$s %3$s %4$s</%1$s>', $use_inline, $textbefore, $return, $textafter );
}


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
                'default'   => 0,
            ),
            'max' => array(
                'type'       => 'scrubfield',
                'heading'    => 'End number',
                'unit'    => '',
                'default'   => 99,
                'max' => mt_getrandmax()
            ),
            'textafter' => array(
                'type'       => 'textfield',
                'heading'   => __('Text after number'),
                'default'    => '',
            ),            
            'use_global'=>array(
                'type' => 'checkbox',
                'heading'   =>'Use Global'                
            ),
            'use_inline'=>array(
                'type' => 'checkbox',
                'heading'   =>'Inline Element',
                'default' => 'true'
            )
        ),
    ));
}