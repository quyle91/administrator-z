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

