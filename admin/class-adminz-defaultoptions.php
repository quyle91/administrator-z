<?php 
namespace Adminz\Admin;

class ADMINZ_DefaultOptions extends Adminz {
	public $options_group = "adminz_defaultoption";
	public $title = "Default Options";
	public $slug = 'default-setting';

	function __construct() {
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);
		add_action( 'admin_init', [$this,'register_option_setting'] );
		add_action( 'init', [$this, 'init']);
		add_action( 'admin_init', [$this,'remove_pages'],999);
	}
	function remove_pages(){
		
		global $user_ID;
		$user_excluded= get_option('adminz_user_excluded',array(1));
		if(!$user_excluded) $user_excluded = array();
		
		if(in_array($user_ID, $user_excluded )) return;

		$hide = get_option('adminz_hide_admin_menu',array());
		if(!empty($hide)){
			foreach ($hide as $page) {
				remove_menu_page($page);
			}
		}
	}
	function init(){
		$adminz_autoupdate = get_option('adminz_autoupdate',array());
		if(!$adminz_autoupdate){
			$adminz_autoupdate = array();
		}
		if(in_array('update_core', $adminz_autoupdate)){
			add_filter( 'auto_update_core', '__return_true' );
		}
		if(in_array('update_plugin', $adminz_autoupdate)){
			add_filter( 'auto_update_plugin', '__return_true' );
		}
		if(in_array('update_theme', $adminz_autoupdate)){
			add_filter( 'auto_update_theme', '__return_true' );
		}
		if(in_array('update_translation', $adminz_autoupdate)){
 			add_filter( 'auto_update_translation', '__return_true' );
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
					<th scope="row">
						<h3>Admin settings</h3>
					</th>
				</tr>
	            <tr valign="top">
	                <th scope="row">Plugin name</th>
	                <td>
 						<input type="text" name="adminz_menu_title" value="<?php echo get_option('adminz_menu_title','Administrator Z'); ?>" />
	                </td>
	            </tr>
	            <tr valign="top">
	                <th scope="row">Logo login image</th>
	                <td>
 						<?php 
 						$image_id = get_option('adminz_login_logo');
 						if( $image = wp_get_attachment_image_src( $image_id ) ) {
 
							echo '<a href="#" class="button adminz-upl"><img src="' . $image[0] . '" /></a>
							      <a href="#" class="button adminz-rmv">Remove image</a>
							      <input type="hidden" name="adminz_login_logo" value="' . $image_id . '">';
						 
						} else {
						 
							echo '<a href="#" class="button adminz-upl">Upload image</a>
							      <a href="#" class="button adminz-rmv" style="display:none">Remove image</a>
							      <input type="hidden" name="adminz_login_logo" value="">';
						 
						}
 						 ?>
	                </td>
	            </tr>
	            <tr valign="top">
	            	<th scope="row">Logo link</th>
	            	<td>
 						<input type="text" name="adminz_logo_url" value="<?php echo get_option('adminz_logo_url'); ?>" />
	                </td>
	            </tr>
	            <tr>
					<th scope="row">
						<h3>Auto update settings</h3>
					</th>
				</tr>
				<tr valign="top">
					<th scope="row">Core updates</th>
					<td>
						<?php 
						$adminz_autoupdate = get_option( 'adminz_autoupdate', array() );
						if(!$adminz_autoupdate){
							$adminz_autoupdate = array();
						}

						$update_array = [
							['update_core','WP Core update'],
							['update_plugin','Plugin update'],
							['update_theme','Theme update'],
							['update_translation','Translation update']
						];
						foreach ($update_array as $value) {
							echo '<label> <input type="checkbox" name="adminz_autoupdate[]" value="'.$value[0].'"';
							echo in_array( $value[0], $adminz_autoupdate) ? 'checked' : "";
							echo "/>";
							echo $value[1];
							echo '</label></br>';
						}
						?>						
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><h3>Hide Admin menu</h3></th>
					<td>
					</td>
				</tr>				
				<tr valign="top">
					<th scope="row">Choose menu</th>
					<td>
						<?php 
							$adminz_hide_admin_menu = get_option( 'adminz_hide_admin_menu', array() );
							if(!$adminz_hide_admin_menu){
								$adminz_hide_admin_menu = array();
							}							
							
							foreach ($GLOBALS[ 'menu' ] as $value) {
								if($value[0]){
								?>
								<label>
							   		<input type="checkbox" name ="adminz_hide_admin_menu[]" value="<?php echo $value[2]; ?>" 
							   		<?php echo in_array( $value[2], $adminz_hide_admin_menu) ? 'checked' : ""; ?>
							   		/><?php echo $value[0]."<code>".$value[2]."</code></br>"; ?>
							   	</label>
								<?php
								}
							}
						?>					   	
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">Choose user exclude</th>
					<td>						
					   	<?php 		

					   	$adminz_user_excluded = get_option( 'adminz_user_excluded', array(1) );
					   	
						if(!$adminz_user_excluded){
							$adminz_user_excluded = array();
						}
 						foreach (get_users() as $user) {
					   		echo "<label>";
						   		echo "<input 
						   		type='checkbox' 
						   		name='adminz_user_excluded[]' 
						   		value='".$user->data->ID."'";
						   		echo in_array( $user->data->ID, $adminz_user_excluded) ? 'checked' : "";
						   		echo "/>";
						   		echo $user->data->user_nicename;
						   		echo "<code>".$user->roles[0]."</code>";
					   		echo " </label>";
					   	}
					   	 ?>
					</td>
				</tr>
	        </table>
			<?php submit_button(); ?>
		</form>
		<?php
		return ob_get_clean();
	}
	function register_option_setting(){
		register_setting( $this->options_group, 'adminz_menu_title' );
		register_setting( $this->options_group, 'adminz_logo_url' );
		register_setting( $this->options_group, 'adminz_login_logo' );
		register_setting( $this->options_group, 'adminz_autoupdate' );
		register_setting( $this->options_group, 'adminz_hide_admin_menu' );
		register_setting( $this->options_group, 'adminz_user_excluded' );
	}
}
