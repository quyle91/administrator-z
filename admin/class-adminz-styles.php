<?php 
namespace Adminz\Admin;

class ADMINZ_Styles extends Adminz {
	public $options_group = "adminz_style";
	public $title = "CSS Style";
	public $slug = "style";	
	function __construct()
	{
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);
		add_action(	'admin_init', [$this,'register_option_setting'] );
		add_action( 'wp_head', [$this,'enqueue_lato'],101);
 	}
 	function register_tab($tabs){
		$tabs[] = array(
			'title'=> $this->title,
			'slug' => $this->slug,
			'html'=> $this->tab_html()
		);
		return $tabs;
	}
	function register_option_setting() {
		register_setting( $this->options_group, 'adminz_choose_font_lato' );	    
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
					<th scope="row">
						<h3>Stylesheet CSS Options</h3>
					</th>
				</tr>
				<tr valign="top">
	                <th scope="row">Import Lato font</th>
	                <td>
 						<label>
	                		<input type="checkbox" name="adminz_choose_font_lato" <?php if(get_option('adminz_choose_font_lato') =="on") echo "checked"; ?>> Lato
	                	</label><br>
	                </td>
	            </tr>	            
 			</table>		
	        <?php submit_button(); ?>
	    </form>
		<?php
		return ob_get_clean();
	}
	function enqueue_lato(){
 		ob_start();
 		if(get_option('adminz_choose_font_lato') =="on"){
 		?>
 		<style id="adminz_choose_font_lato" type="text/css">
 			@font-face {
			  font-family: Lato;
			  font-style: normal;
			  font-weight: 400;
			  src: url(<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/font/Lato-Regular.ttf'; ?>);
			}
			@font-face {
			  font-family: Lato;
			  font-style: italic;
			  src: url(<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/font/Lato-Italic.ttf'; ?>);
			}
			@font-face {
			  font-family: Lato;
			  font-weight: 100;
			  src: url(<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/font/Lato-Thin.ttf'; ?>);
			}
			@font-face {
			  font-family: Lato;
			  font-weight: 700;
			  src: url(<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/font/Lato-Bold.ttf'; ?>);
			}
			@font-face {
			  font-family: Lato;
			  font-weight: 800;
			  src: url(<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/font/Lato-Black.ttf'; ?>);
			}
			@font-face {
			  font-family: Lato;
			  font-weight: 900;
			  src: url(<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/font/Lato-Heavy.ttf'; ?>);
			}
			*,
			body,
			.nav > li > a ,
			.mobile-sidebar-levels-2 .nav > li > ul > li > a,
			h1,h2,h3,h4,h5,h6,.heading-font, .off-canvas-center .nav-sidebar.nav-vertical > li > a {
				font-family: Lato;
			}
 		</style>
 		<?php
 		}
 		$buffer = ob_get_clean();
 		echo str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

 	}

 }