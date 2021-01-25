<?php 
namespace Adminz\Admin;
use WP_Error;

class ADMINZ_Sercurity extends Adminz
{
	public $options_group = "adminz_sercurity";
	public $title = 'Sercurity';
	public $slug  = 'adminz_sercurity';
	function __construct() {
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);
		add_action(	'admin_init', [$this,'register_option_setting'] );
		add_action( 'init', array( $this, 'init' ) );
	}
	function init(){
		if(get_option('adminz_xmlrpc_enabled','on') == 'on'){
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}		

		if(get_option('adminz_disable_x_pingback','on') == 'on'){
			add_filter( 'wp_headers', [$this,'disable_x_pingback'] );
		}
		if(get_option('adminz_disable_json','on') == 'on'){
			add_filter( 'rest_authentication_errors', function( $result ) {
			    if ( ! empty( $result ) ) {
			        return $result;
			    }
			    if ( ! is_user_logged_in() ) {
			        return new WP_Error( 'rest_not_logged_in', 'AdministratorZ alert: Need logged in ', array( 'status' => 401 ) );
			    }
			    return $result;
			});
		}	
		if(get_option('adminz_disable_file_edit','on') == 'on'){
			define( 'DISALLOW_FILE_EDIT', true );
		}

	}
	function disable_x_pingback( $headers ) {
		    unset( $headers['X-Pingback'] );
			return $headers;
		}
	function register_option_setting(){
		register_setting( $this->options_group, 'adminz_xmlrpc_enabled' );
		register_setting( $this->options_group, 'adminz_disable_x_pingback' );
		register_setting( $this->options_group, 'adminz_disable_json' );
		register_setting( $this->options_group, 'adminz_disable_file_edit' );
		
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
	        		<th><h3>Enable functions</h3></th>
	        		<td></td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Disable use XML-RPC
	        		</th>
	        		<td>
	        			<div>
	        				<input type="checkbox" <?php echo get_option('adminz_xmlrpc_enabled','on') == 'on' ? 'checked' : ''; ?>  name="adminz_xmlrpc_enabled"/>
	        			</div>
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Disable X-Pingback to header
	        		</th>
	        		<td>
	        			<div>
	        				<input type="checkbox" <?php echo get_option('adminz_disable_x_pingback','on') == 'on' ? 'checked' : ''; ?>  name="adminz_disable_x_pingback"/>
	        			</div>
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Disable REST API (wp-json)
	        		</th>
	        		<td>
	        			<div>
	        				<input type="checkbox" <?php echo get_option('adminz_disable_json','on') == 'on' ? 'checked' : ''; ?>  name="adminz_disable_json"/>
	        			</div>
	        		</td>
	        	</tr>
	        	<tr valign="top">	        		
	        		<th>
	        			Disable file edit
	        		</th>
	        		<td>
	        			<div>
	        				<input type="checkbox" <?php echo get_option('adminz_disable_file_edit','on') == 'on' ? 'checked' : ''; ?>  name="adminz_disable_file_edit"/>
	        			</div>
	        		</td>
	        	</tr>
	        </table>
	        <?php submit_button(); ?>
        </form>
		<?php
		return ob_get_clean();
	}
}