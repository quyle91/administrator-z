<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'ux_adminz_add_viewcount');
function ux_adminz_add_viewcount(){
	$adminz = new Adminz;
    add_ux_builder_shortcode('adminz_countviews', array(
        'name'      => __('Count Views'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'countdown' . '.svg',
        'options' => array(
			'post_id' => array(
                'type' => 'select',
		        'heading' => 'Custom Posts',
		        'param_name' => 'ids',
		        'config' => array(
		            'multiple' => false,
		            'placeholder' => 'Select..',
		            'postSelect' => array(
		                'post_type' => array()
		            ),
		        )
            ),
            'icon'=>array(
                'type' => 'textfield',
                'description' => "Settings/ ".$adminz->get_adminz_menu_title()."/ contactgroup",
                'heading'   =>'Use icon',
                'default' => 'eye'
            ),
            'textbefore' => array(
                'type'       => 'textfield',
                'heading'   => __('Text before'),
                'default'    => '',
            ),
            'textafter' => array(
                'type'       => 'textfield',
                'heading'   => __('Text after'),
                'default'    => '',
            ),
            'class' => array(
                'type'       => 'textfield',
                'heading'   => __('Class'),
                'default'    => '',
            ),
        ),
    ));
}