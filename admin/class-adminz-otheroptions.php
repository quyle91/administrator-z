<?php 
namespace Adminz\Admin;

class ADMINZ_OtherOptions extends Adminz
{
	public $options_group = "adminz_otheroptions";
	public $title = 'Other Functions';
	public $slug  = 'adminz_otheroptions';
	public $rand;
	function __construct() {
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);		
		add_action( 'init', [$this, 'load_shortcodes'] );	

	}
	
	
	function load_shortcodes(){
		$shortcodefiles = glob(ADMINZ_DIR.'shortcodes/otheroptions*.php');
		if(!empty($shortcodefiles)){
			foreach ($shortcodefiles as $file) {
				require_once $file;
			}
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
		global $adminz;
		ob_start();
		?>
		<form method="post" action="options.php">
	        <?php 
	        settings_fields($this->options_group);
	        do_settings_sections($this->options_group);
	        ?>
	        <table class="form-table">
	        	<tr valign="top">
	        		<th><h3>Enable functions</h3></th>
	        		<td>
	        			
	        		</td>
	        	</tr>	  
	        	<tr valign="top">
	        		<th>Auto replace image</th>
	        		<td>
	        			<p>
	        				<label>
	        					<input type="checkbox" />
	        					When upload the image, it will be replace old image if the image name as same as name old image from library.<br><code>Coming soon</code>
	        				</label>
	        			</p>
	        		</td>
	        	</tr>      	     	
	        </table>
	        <?php submit_button(); ?>
        </form>
		<?php
		return ob_get_clean();
	}
}