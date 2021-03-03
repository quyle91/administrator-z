<?php 
namespace Adminz\Admin;

class ADMINZ_Flatsome extends Adminz {
	public $options_group = "adminz_flatsome";
	public $title = 'Flatsome';
	public $slug  = 'adminz_flatsome';
	public $flatsome_actions = ['flatsome_absolute_footer_primary','flatsome_absolute_footer_secondary','flatsome_account_links','flatsome_after_404','flatsome_after_account_user','flatsome_after_blog','flatsome_after_body_open','flatsome_after_breadcrumb','flatsome_after_footer','flatsome_after_header','flatsome_after_header_bottom','flatsome_after_page','flatsome_after_page_content','flatsome_after_product_images','flatsome_after_product_page','flatsome_before_404','flatsome_before_blog','flatsome_before_breadcrumb','flatsome_before_comments','flatsome_before_footer','flatsome_before_header','flatsome_before_page','flatsome_before_page_content','flatsome_before_product_images','flatsome_before_product_page','flatsome_before_product_sidebar','flatsome_before_single_product_custom','flatsome_blog_post_after','flatsome_blog_post_before','flatsome_breadcrumb','flatsome_cart_sidebar','flatsome_category_title','flatsome_category_title_alt','flatsome_footer','flatsome_header_background','flatsome_header_elements','flatsome_header_wrapper','flatsome_portfolio_title_after','flatsome_portfolio_title_left','flatsome_portfolio_title_right','flatsome_product_box_actions','flatsome_product_box_after','flatsome_product_box_tools_bottom','flatsome_product_box_tools_top','flatsome_product_image_tools_bottom','flatsome_product_image_tools_top','flatsome_product_title','flatsome_product_title_tools','flatsome_products_after','flatsome_products_before','flatsome_sale_flash','flatsome_woocommerce_shop_loop_images'];
	function __construct() {
		if(!in_array('Flatsome', [wp_get_theme()->name, wp_get_theme()->parent_theme])) return;
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);
		add_action(	'admin_init', [$this,'register_option_setting'] );
		add_action( 'init', array( $this, 'add_shortcodes') );
		add_action( 'wp_head', [$this,'adminz_fix_css'], 101 );
		add_action( 'wp_enqueue_scripts', [$this,'enqueue_package'],102);		
 		$this->flatsome_filter_hook();
 		$this->flatsome_action_hook();
	}
	function enqueue_package(){
		$choose = get_option('adminz_choose_stylesheet');
		foreach ($this->get_packages() as $key => $value) {
			if($value['slug'] == $choose){
				wp_enqueue_style( 'flatsome_css_pack',$value['url']);
			}
		}
 		
 	}
	function adminz_fix_css(){
		ob_start();
		?>
		<style id="adminz_flatsome_fix" type="text/css">
			/*Custom class*/
			.nopadding,.nopaddingbottom{
				padding-bottom: 0 !important;
			}
			.sliderbot{
				position: absolute;
				left:0;
				bottom: 0;
			}			
			/*fix*/
			.mfp-close{
			    mix-blend-mode: unset;
			}

			.sliderbot .img-inner{
				border-radius: 0;
			}
			<?php 
			$site_width = intval(get_theme_mod('site_width'));
			if($site_width) {
				?>
				.container-width, .full-width .ubermenu-nav, .container, .row{
					max-width: <?php echo $site_width?>px;
				}
				<?php
			}
			$enable_sidebar_divider = get_theme_mod('blog_layout_divider');
			if(!$enable_sidebar_divider){
				?>
					body.page .col-divided,
					body.single-product .row-divided>.col+.col:not(.large-12){
					border-right: none;
					border-left: none;
				}
				<?php
			}
			$footer_bottom_align = get_theme_mod('footer_bottom_align');
			$footer_right_text = get_theme_mod('footer_right_text');
			if(!$footer_bottom_align){
				?>
					#footer .footer-primary{
					    padding: 7.5px 0;
					}
				<?php
			}
			?>
		</style>
		<?php
		$buffer = ob_get_clean();
		echo str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
	}
	function add_shortcodes(){
		$shortcodefiles = glob(ADMINZ_DIR.'shortcodes/flatsome*.php');
		if(!empty($shortcodefiles)){
			foreach ($shortcodefiles as $file) {
				require_once $file;
			}
		}
	}
	function flatsome_action_hook(){		
		$this->flatsome_actions;
		foreach ($this->flatsome_actions as $action) {
			$get_option = get_option( 'adminz_'.$action,'' );
			if($get_option){				
				add_action($action, function () use($get_option){					
					echo do_shortcode(html_entity_decode($get_option));
				});
			}
			
		}
	}
	function flatsome_filter_hook(){
		$btn_inside = get_option('adminz_flatsome_lightbox_close_btn_inside','');
		if( $btn_inside == 'on'){
			add_filter( 'flatsome_lightbox_close_btn_inside', '__return_true' );
		}
		$btn_close = get_option( 'adminz_flatsome_lightbox_close_button','' );		
		if($btn_close){
			$btn_close.=".svg";			
			if(in_array($btn_close, $this->get_support_icons())){
				add_filter( 'flatsome_lightbox_close_button', function ( ) use ($btn_close){
					$html = '<button title="%title%" type="button" style="fill:white; display: grid; padding: 5px;" class="mfp-close">';
					$html .= $this->get_icon_html(plugin_dir_url(ADMINZ_BASENAME).'assets/images/'.$btn_close );
					$html .= '</button>';
					return $html;
				});
			}
		}
		$adminz_use_mce_button = get_option('adminz_use_mce_button','');	
		if($adminz_use_mce_button){
			add_filter("mce_buttons", function ($buttons){
		        array_push($buttons,
		            "alignjustify",
		            "subscript",
		            "superscript"
		        );
		        return $buttons;
		    });
			add_filter("mce_buttons_2", function ($buttons){
		        array_push($buttons,
		            "fontselect",
		            "cleanup"
		        );
		        return $buttons;
		    }, 9999);
		}
		$viewport = get_option('adminz_flatsome_viewport_meta','');
		if($viewport =="on"){
			add_filter( 'flatsome_viewport_meta',function (){ __return_null();});
		}
		$wpsep_remove_last = get_option('adminz_flatsome_wpseo_breadcrumb_remove_last','');
		if($wpsep_remove_last =="on"){
			add_filter( 'flatsome_wpseo_breadcrumb_remove_last',function (){ __return_true();});
		}
		$sidebar_classes = get_option('adminz_flatsome_sidebar_class','');		
		if($sidebar_classes){
			add_filter('flatsome_sidebar_class',function ($classes) use ($sidebar_classes){
				$classes[] = $sidebar_classes;
				return $classes;
			});
		}
		$main_class = get_option('adminz_flatsome_main_class','');		
		if($main_class){
			add_filter('flatsome_main_class',function ($classes) use ($main_class){
				$classes[] = $main_class;
				return $classes;
			});
		}
		$header_class = get_option('adminz_flatsome_header_class','');		
		if($header_class){
			add_filter('flatsome_header_class',function ($classes) use ($header_class){
				$classes[] = $header_class;
				return $classes;
			});
		}
		$header_title_class = get_option('adminz_flatsome_header_title_class','');		
		if($header_title_class){
			add_filter('flatsome_header_title_class',function ($classes) use ($header_title_class){
				$classes[] = $header_title_class;
				return $classes;
			});
		}
		$script_priority = get_option('adminz_flatsome_before_body_close_priority','');	
		if($script_priority){
			add_filter('flatsome_before_body_close_priority',function (){
				return 12;
			});
			/*add_filter('flatsome_before_body_close_priority',function () use ($script_priority){
				return $script_priority;
			});*/
		}
	}
	function register_option_setting() {
		register_setting( $this->options_group, 'adminz_choose_stylesheet' );
		register_setting( $this->options_group, 'adminz_use_mce_button' );
	    register_setting( $this->options_group, 'adminz_flatsome_lightbox_close_btn_inside' );
	    register_setting( $this->options_group, 'adminz_flatsome_lightbox_close_button' );	
	    register_setting( $this->options_group, 'adminz_flatsome_viewport_meta' );	 
	    register_setting( $this->options_group, 'adminz_flatsome_wpseo_breadcrumb_remove_last' );
	    register_setting( $this->options_group, 'adminz_flatsome_sidebar_class' );
	    register_setting( $this->options_group, 'adminz_flatsome_main_class' );
	    register_setting( $this->options_group, 'adminz_flatsome_header_class' );
	    register_setting( $this->options_group, 'adminz_flatsome_header_title_class' );
	    register_setting( $this->options_group, 'adminz_flatsome_before_body_close_priority' );
	    foreach ($this->flatsome_actions as $value) {
	    	register_setting( $this->options_group, 'adminz_'.$value );
	    }
	}
	function register_tab($tabs){
		$tabs[] = array(
			'title'=> $this->title,
			'slug' => $this->slug,
			'html'=> $this->tab_html()
		);
		return $tabs;
	}
	function tab_html(){
		ob_start();
		?>
		<form method="post" action="options.php">
	        <?php 
	        settings_fields($this->options_group);
	        do_settings_sections($this->options_group);
	        ?>
	        <table class="form-table">	        	
	        	<tr valign="top">
	        		<th><h3>UX builder</h3></th>
	        		<td>Some shortcode from ux builder has beed added. Open Ux builder to show</td>
	        	</tr>	
	        	<tr valign="top">
					<th scope="row">
						<h3>Stylesheet CSS package</h3>
					</th>
				</tr>
	            <tr valign="top">
	                <th scope="row">Choose style</th>
	                <td>
	                	<?php 
						$choose = get_option('adminz_choose_stylesheet');
	                	 ?>
	                	<select name="adminz_choose_stylesheet">
	                	<?php
                		foreach ($this->get_packages() as $pack) {
                			$seleted = ($choose == $pack['slug']) ? "selected" : "";
                			?>
                			<option <?php echo $seleted; ?> value="<?php echo $pack['slug'] ?>"><?php echo $pack['name']; ?></option>
                			<?php
                		}
                	 	?>
                	 	</select>
	                </td>
	            </tr>
	            <tr valign="top">
	        		<th><h3>Tiny MCE Editor</h3></th>
	        		<td>
	        			<label>
	                		<input type="checkbox" name="adminz_use_mce_button" <?php if(get_option('adminz_use_mce_button') =="on") echo "checked"; ?>> Enable
	                	</label><br>
	        		</td>
	        	</tr>
	        	<tr valign="top">
	        		<th><h3>Flatsome Filters hook</h3></th>
	        		<td></td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Lightbox close button inside
	        		</th>
	        		<td>
	        			<input type="checkbox" <?php echo get_option('adminz_flatsome_lightbox_close_btn_inside','') == 'on' ? 'checked' : ''; ?>  name="adminz_flatsome_lightbox_close_btn_inside"/>
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Lightbox close button icon
	        		</th>
	        		<td>
	        			<input type="text" name="adminz_flatsome_lightbox_close_button" value="<?php echo get_option( 'adminz_flatsome_lightbox_close_button','' ); ?>" />
	        			<em>Example: close-round</em>
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Disable Meta viewport
	        		</th>
	        		<td>
	        			<input type="checkbox" <?php echo get_option('adminz_flatsome_viewport_meta','') == 'on' ? 'checked' : ''; ?>  name="adminz_flatsome_viewport_meta"/>
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			WP SEO beadcrumb remove last
	        		</th>
	        		<td>
	        			<input type="checkbox" <?php echo get_option('adminz_flatsome_wpseo_breadcrumb_remove_last','') == 'on' ? 'checked' : ''; ?>  name="adminz_flatsome_wpseo_breadcrumb_remove_last"/>
	        		</td>
	        	</tr>	 
	        	<tr valign="top">	        		
	        		<th>
	        			Sidebar Classes
	        		</th>
	        		<td>
	        			<input type="text" name="adminz_flatsome_sidebar_class" value="<?php echo get_option( 'adminz_flatsome_sidebar_class','' ); ?>" />
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Main Class
	        		</th>
	        		<td>
	        			<input type="text" name="adminz_flatsome_main_class" value="<?php echo get_option( 'adminz_flatsome_main_class','' ); ?>" />
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Header Class
	        		</th>
	        		<td>
	        			<input type="text" name="adminz_flatsome_header_class" value="<?php echo get_option( 'adminz_flatsome_header_class','' ); ?>" />
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Header Title Class
	        		</th>
	        		<td>
	        			<input type="text" name="adminz_flatsome_header_title_class" value="<?php echo get_option( 'adminz_flatsome_header_title_class','' ); ?>" />
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Insert custom body bottom script Priority
	        		</th>
	        		<td>
	        			<input type="number" name="adminz_flatsome_before_body_close_priority" value="<?php echo get_option( 'adminz_flatsome_before_body_close_priority','' ); ?>" />
	        		</td>
	        	</tr>
	        	
	        	<!-- action  -->
	        	<tr valign="top">
	        		<th><h3>Flatsome Actions hook</h3></th>
	        		<td></td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Action name
	        		</th>
	        		<td>
	        			<p>type <code>[adminz_test]</code> to test</p>	        			
	        		</td>
	        	</tr>
	        	<?php 
	        	foreach ($this->flatsome_actions as $value) {
	        		?>
	        		<tr valign="top">	        		
		        		<th>
		        			<?php echo $value; ?>
		        		</th>
		        		<td>
		        			<textarea cols="70" rows="1" name="adminz_<?php echo $value; ?>"><?php echo get_option( 'adminz_'.$value ,'' ); ?></textarea>
		        		</td>
		        	</tr>
	        		<?php
	        	}
	        	 ?>
	        </table>
	        <?php submit_button(); ?>
        </form>
        <?php
		return ob_get_clean();
	}
	function get_packages(){
		return [
			[
				'name'=>'Choose style',
				'slug'=>'',
				'url' => ''
			],
			[
				'name'=>'Round',
				'slug'=>'pack1',
				'url' => plugin_dir_url(ADMINZ_BASENAME).'assets/css/pack/1.css'
			]
			
		];
	}
}