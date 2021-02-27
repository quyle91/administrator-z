<?php 
use Adminz\Admin\Adminz as Adminz;
add_action('ux_builder_setup', 'adminz_flickity_navigation');
add_shortcode('adminz_flickity_navigation', 'adminz_flickity_navigation_function');
function adminz_flickity_navigation(){
	$adminz = new Adminz;
	add_ux_builder_shortcode('adminz_flickity_navigation', array(
		'type' => 'container',
		'priority'  => 1,
		'info' => '{{ title }}',
        'name'      => __('Flickity Custom Navigation'),
        'category'  => $adminz->get_adminz_menu_title(),
        'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'ux_stack.svg',
        'allow'     => array( 'adminz_flickity_navigation_item' ),
     //    'presets' => array(
	    //     array(
	    //         'name' => __( 'Default' ),
	    //         'content' => '
	    //             [mk_readmore_readless]
	    //                 <h3>This is a simple headline</h3><p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.</p>
	    //             [/mk_readmore_readless]
	    //         '
	    //     ),
	    // ),
        'options' => array(
            'parent_class'             => array(
				'type'       => 'textfield',
				'heading'	=> __('Target slider class'),
				'default'    => '',
				'holder'    => '',
			),
			'class'             => array(
				'type'       => 'textfield',
				'heading'	=> __('Navigation class'),
				'default'    => '',
				'holder'    => '',
			),
        ),
    ));
}
function adminz_flickity_navigation_function($atts){
	$adminz = new Adminz;
	extract(shortcode_atts(array(
		'class' => '',
        'parent_class'    => '',
    ), $atts));
    ob_start();
	?>
	<div class="<?php echo $class; ?>">
	  <button class="button button--previous">&larr;</button>
	  <div class="button-group button-group--cells">
	    <button class="button is-selected">1</button>
	    <button class="button">2</button>
	    <button class="button">3</button>
	  </div>
	  <button class="button button--next">&rarr;</button>
	</div>
	<?php 
	add_action('wp_footer',function () use( $parent_class){
		?>
		<script type="text/javascript">
			jQuery(function($){	
				$('<?php echo $parent_class? ".".$parent_class." ": ""; ?>.slider').each(function(){
					var data = $(this).data('flickity-options')
					var $carousel = $(this).flickity(data);
					// Flickity instance
					var flkty = $carousel.data('flickity');
					// elements
					var $cellButtonGroup = $('.button-group--cells');
					var $cellButtons = $cellButtonGroup.find('.button');

					// update selected cellButtons
					$carousel.on( 'select.flickity', function() {
					  $cellButtons.filter('.is-selected')
					    .removeClass('is-selected');
					  $cellButtons.eq( flkty.selectedIndex )
					    .addClass('is-selected');
					});

					// select cell on button click
					$cellButtonGroup.on( 'click', '.button', function() {
					  var index = $(this).index();
					  $carousel.flickity( 'select', index );
					});
					// previous
					$('.button--previous').on( 'click', function() {
					  $carousel.flickity('previous');
					});
					// next
					$('.button--next').on( 'click', function() {
					  $carousel.flickity('next');
					});
				});			
			});
		</script>
		<?php	
	} );
	
    return ob_get_clean();
}
add_action('ux_builder_setup', 'adminz_flickity_navigation_item');
add_shortcode('adminz_flickity_navigation_item', 'adminz_flickity_navigation_item_function');
function adminz_flickity_navigation_item(){
	$adminz = new Adminz;
	add_ux_builder_shortcode( 'adminz_flickity_navigation_item', array(
	    'name' => __( 'Item' ),	    
	    'info' => '{{ title }}',
	    'require' => array( 'adminz_flickity_navigation' ),
	    'category'  => $adminz->get_adminz_menu_title(),
	    'thumbnail' =>  get_template_directory_uri() . '/inc/builder/shortcodes/thumbnails/' . 'ux_image.svg',
	    'wrap'      => false,
	    'options' => array(
	        'title' => array(
	            'type' => 'textfield',
	            'heading' => __( 'Title' ),
	            'default' => __( 'Accordion Panel Title' ),
	            'auto_focus' => true,
	        ),
	        'class' => array(
	            'type' => 'textfield',
	            'heading' => 'Custom Class',
	            'full_width' => true,
	            'placeholder' => 'class-name',
	            'default' => '',
	        ),
	    ),
	) );

}
function adminz_flickity_navigation_item_function(){
	$adminz = new Adminz;
	extract(shortcode_atts(array(
		'class' => '',
        'parent_class'    => '',
    ), $atts));
    ob_start();
    ?>
    19999
    <?php
    return ob_get_clean();
}
