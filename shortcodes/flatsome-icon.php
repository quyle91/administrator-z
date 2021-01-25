<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'adminz_icon');
add_shortcode('adminz_icon', 'adminz_icon_function');
function adminz_icon(){
	$adminz = new Adminz;
	$options = [];
    $options[]  = '--Select--';
	foreach ($adminz->get_support_icons() as $icon) {
		$options[str_replace(".svg", "", $icon)] = $icon;
	}
	add_ux_builder_shortcode('adminz_icon', array(
        'name'      => __('Icon'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'icon_box' . '.svg',
        'options' => array(
            'icon' => array(
                'type'       => 'select',
                'heading'    => 'Select Icon',
                'default' => '',
                'options'=>$options
            ),
            'image' => array(
                'type'       => 'image',
                'heading'    => 'Or Upload SVG',
                'default' => '',
            ),
            'color' =>array(
	          	'type' => 'colorpicker',
	          	'heading' => __('Icon Color'),
	          	'alpha' => true,
	          	'format' => 'hex',
	        ),
            'max_width' => array(
                'type' => 'scrubfield',
                'heading' => __( 'Width' ),
                'default' => '100%',
                'min' => 0,
                'max' => 100,
            ),
            'link' =>array(
                'type' => 'textfield',
                'heading' => __('Link'),
            ),
            'class' =>array(
                'type' => 'textfield',
                'heading' => __('SVG Class'),
            ),
        ),
    ));
}
function adminz_icon_function($atts){
	$adminz = new Adminz;
	extract(shortcode_atts(array(
        'icon'    => '',
        'image' => '',
        'color'=> '',
        'link' => '',
        'class'=>'',
        'max_width'=>''
    ), $atts));
    ob_start();
    
    $style = "";
    if($color) $style.="color:".$color.";";
    if($max_width) $style .="max-width:".$max_width.";";    
    if($class) $style.= '" class="'.$class .'';
    
    if($icon){
        $icon_url = plugin_dir_url(ADMINZ_BASENAME)."assets/images/".$icon.".svg";
    }
    if($image){
        $a = get_post($image);
        $icon_url  = $a->guid;
    }
    $before = ""; $after = "";
    if($link) {
        $before = "<a href='".$link."'>";
        $after = "</a>";
    }
    echo $before;
    echo $adminz->get_icon_html($icon_url,$style);
    echo $after;
    return ob_get_clean();
}