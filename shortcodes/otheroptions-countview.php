<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('wp_footer','adminz_add_viewcount');
add_shortcode('adminz_countviews', 'adminz_countview_function');

function adminz_add_viewcount(){
	$key = 'adminz_countview';
    $post_id = get_the_ID();
    $count = (int) get_post_meta( $post_id, $key, true );
    $count++;
    update_post_meta( $post_id, $key, $count );
}

function adminz_countview_function($atts){
    $adminz = new Adminz;
    extract(shortcode_atts(array(
        'post_id'    => get_the_ID(),
        'use_icon' => 'eye',
        'textbefore' => '',
        'textafter' => '',
        'class' => 'adminz_count_view'
    ), $atts));
    ob_start();
    echo '<div class="'.$class.'">';
    echo $textbefore;
    if($use_icon) {        
        echo $adminz->get_icon_html(plugin_dir_url(ADMINZ_BASENAME).'assets/images/'.$use_icon.'.svg',"width: 1em; display: inline-block;");
    }
    echo " ". get_post_meta( $post_id, 'adminz_countview', true );
    echo " ".$textafter;    
    echo "</div>";
    return ob_get_clean();
}