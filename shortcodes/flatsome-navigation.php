<?php 
use Adminz\Admin\Adminz as Adminz;
function adminz_navigation(){
	$adminz = new Adminz;
    add_ux_builder_shortcode('adminz_navigation', array(
        'name'      => __('Navigation'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'nav' . '.svg',
        'options' => array(
        	'nav' => array(
	          'type' => 'select',
	          'heading' => __( 'Choose Navigation','ux-builder' ),	          
	          'param_name' => 'slug',
		        'config' => array(
		            'multiple' => false,
		            'placeholder' => 'Select..',
		            'termSelect' => array(
		                'taxonomies' => 'nav_menu'
		            ),
		        )
	    	),
            'type' => array(
	          'type' => 'select',
	          'heading' => __( 'Direction','ux-builder' ),
	          'default' => 'vertical',
	          'options' => array(
	              'horizontal' => 'Horizontal',
	              'vertical' => 'Vertical',
	          )
	    	),
	    	'style' => array(
	          'type' => 'select',
	          'heading' => __( 'Nav Style','ux-builder' ),
	          'default' => '',
	          'options' => array(
	          		''=> "Default",
	              	'divided' => 'Divided',
					'line' => 'Line',
					'line-grow' => 'Line grow',
					'line-bottom' => 'Line bottom',
					'box' => 'Box',
					'outline' => 'Outline',
					'pills' => 'Pills',
					'tabs' => 'Tabs',
	          )
	    	),
	    	'size' => array(
	          'type' => 'select',
	          'heading' => __( 'Nav Size','ux-builder' ),
	          'default' => 'default',
	          'options' => array(
	              	'xsmall' => 'Xsmall',
	              	'small'	=> 'Small',
	              	'default'	=> 'Default',
	              	'medium'	=> 'Medium',
	              	'large'	=> 'Large',
	              	'xlarge'	=> 'Xlarge',
	          )
	    	),
	    	'spacing' => array(
	          'type' => 'select',
	          'heading' => __( 'Nav Spacing','ux-builder' ),
	          'conditions' => 'type == "horizontal"',
	          'default' => 'default',
	          'options' => array(
	              	'xsmall' => 'Xsmall',
	              	'small'	=> 'Small',
	              	'default'	=> 'Default',
	              	'medium'	=> 'Medium',
	              	'large'	=> 'Large',
	              	'xlarge'	=> 'Xlarge',
	          )
	    	),
	    	'uppercase' => array(
	          'type' => 'select',
	          'heading' => __( 'Uppercase','ux-builder' ),
	          'default' => 'normal',
	          'options' => array(
	              	'uppercase' => 'Uppercase',
	              	'normal' => 'Normal',
	              	'captilizer' => 'Captilizer'
	          )
	    	),
	    	'horizontal_align' => array(
	          'type' => 'select',
	          'heading' => __( 'Items align','ux-builder' ),
	          'default' => 'left',
	          'conditions' => 'type == "horizontal"',
	          'options' => array(
	              'left' => 'Left',
	              'right' => 'Right',
	          )
	    	),
	    	'toggle' => array(
	          'type' => 'select',
	          'heading' => __( 'Items toggled','ux-builder' ),
	          'conditions' => 'type == "vertical"',
	          'default' => 'no',
	          'options' => array(
	              'no' => 'No',
	              'yes' => 'Yes',
	          )
	    	),
	    	'class' => array(
	          'type' => 'textfield',
	          'heading' => __( 'Class','ux-builder' ),
	    	),
        ),
    ));
}
add_action('ux_builder_setup', 'adminz_navigation');

function adminz_navigation_shortcode($atts){
	add_filter('nav_menu_css_class', 'adminz_add_additional_class_on_li', 1, 3);
    extract(shortcode_atts(array(
        'nav'    => '2',
        'type'	=> 'vertical',
        'uppercase' => 'normal',	
        'style' => '',
        'toggle' => 'no',
        'horizontal_align' => 'left',
        'size' => 'default',
        'spacing' => 'default',
        'class'=> 'adminz_navigation_custom'
    ), $atts));
    
    $ul_class = 'nav-'.$horizontal_align.' nav-'.$style.' nav-'.$uppercase.' nav-size-'.$size.' nav-spacing-'.$spacing." ".$class;

    $walker  = 'FlatsomeNavDropdown';

    if($type=='vertical'){
    	$ul_class = "menu ".$ul_class;
    	$walker = "";
    	$add_li_class = $toggle =='yes'	 ? 'active' : "";
    }else{
    	$ul_class = "header-nav header-nav-main nav ".$ul_class;
    	$walker  = new $walker();
    	$add_li_class = "";
    }
	
    $args = array(
    	'menu'              => $nav,
	    'menu_class'	=> $ul_class,
	    'container'      => false,
	    'items_wrap'        => '<ul id="%1$s" class="%2$s">%3$s</ul>',
	    'walker'         => $walker,
	    'add_li_class'  => $add_li_class,
    );

    ob_start();
    wp_nav_menu($args);


    return ob_get_clean();
}
add_shortcode('adminz_navigation', 'adminz_navigation_shortcode');

function adminz_add_additional_class_on_li($classes, $item, $args) {
    if(isset($args->add_li_class)) {
        $classes[] = $args->add_li_class;
    }
    return $classes;
}
