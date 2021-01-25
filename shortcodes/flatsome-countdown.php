<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'adminz_countdown');
add_shortcode('adminz_countdown', 'adminz_countdown_function');

function adminz_countdown(){
	$adminz = new Adminz;
    add_ux_builder_shortcode('adminz_countdown', array(
        'name'      => __('Count Down'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'countdown' . '.svg',
        'options' => array(
            'text_days' => array(
                'type'       => 'textfield',
                'heading'    => 'Days',
                'default'    => 'Days',
            ),
            'text_hours' => array(
                'type'       => 'textfield',
                'heading'    => 'Hours',
                'default'    => 'Hours',
            ),
            'text_minutes' => array(
                'type'       => 'textfield',
                'heading'    => 'Minutes',
                'default'    => 'Minutes',
            ),
            'text_seconds' => array(
                'type'       => 'textfield',
                'heading'    => 'Secconds',
                'default'    => 'Secconds',
            ),
        ),
    ));
}

function adminz_countdown_function($atts){
    wp_enqueue_script( 'adminz_countdown_js', plugin_dir_url(ADMINZ_BASENAME).'assets/js/countdown.js', array( 'jquery' ) );
    extract(shortcode_atts(array(
        'text_days'    => 'Days',
        'text_hours'    => 'Hours',
        'text_minutes'    => 'Minutes',
        'text_seconds'    => 'Secconds',
    ), $atts));
    ob_start();
    ?>
    <div class="ux-timer-wrapper row row-xsmall">
        <div class="col small-3 cd countdown-item text-center">
        	<div class="col-inner" style="border: 1px solid #ccc;">
                <?php echo do_shortcode('[gap]'); ?>
		        <h3 class="top countdown-day"> 00 </h3>
		        <p class="bottom"><?php echo $text_days; ?></p>
	        </div>
        </div>
        <div class="col small-3 cd countdown-item text-center">
        	<div class="col-inner" style="border: 1px solid #ccc;">
                <?php echo do_shortcode('[gap]'); ?>
		        <h3 class="top countdown-hour"> 00 </h3>
		        <p class="bottom"><?php echo $text_hours; ?></p>
	        </div>
        </div>
        <div class="col small-3 cd countdown-item text-center">
        	<div class="col-inner" style="border: 1px solid #ccc;">
                <?php echo do_shortcode('[gap]'); ?>
		        <h3 class="top countdown-minute"> 00 </h3>
		        <p class="bottom"><?php echo $text_minutes; ?></p>
	        </div>
        </div>
        <div class="col small-3 cd countdown-item text-center">
        	<div class="col-inner" style="border: 1px solid #ccc;">
                <?php echo do_shortcode('[gap]'); ?>
		        <h3 class="top countdown-second"> 00 </h3>
		        <p class="bottom"><?php echo $text_seconds; ?></p>
	        </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}