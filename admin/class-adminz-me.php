<?php 
namespace Adminz\Admin;

class ADMINZ_Me extends Adminz {
	public $options_group = "adminz_me";
	public $title = "Support";
	public $slug = 'me';
	function __construct() {
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);		
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
						<h3>Contact</h3>
					</th>
				</tr>	
				<tr valign="top">
					<th scope="row">
						Support forum
					</th>
					<td>
						<a href="https://wordpress.org/support/plugin/administrator-z/">Link</a>
						<p>You can create topic here</p>
					</td>
				</tr>			
	            <tr valign="top">
	                <th scope="row">Plugin name</th>
	                <td>
	                	<img width="100px" src="https://ps.w.org/administrator-z/assets/icon.svg?rev=2451301"></br>
 						<p>
 							“Administrator Z” is open source software. 
 						</p>
 						<p>For more details you can send mail with your suggestions, recommendation to quylv.dsth@gmail.com or:</p>
 						<p>
 							<a href="https://wordpress.org/plugins/administrator-z/">View details</a>
 							<a href="https://github.com/quyle91/administrator-z/issues/new">Submit Issues</a>
 						</p>
	                </td>
	            </tr>
	            <tr valign="top">
	                <th scope="row">Author</th>
	                <td>
	                	<strong>Quy Le 91</strong>
	                	<p>
	                		<em>You want to develop functionality for own project ? Inbox me for below details.</em>
	                	</p>
	                	<p>
	                		<a href="https://paypal.me/quyle91">Donate</a>
	                		<a href="https://m.me/timquen2014" class="">Messenger me</a>
	                		<a href="mailto:quylv.dsth@gmail.com" class="">Email </a>
	                	</p>
	                	
	                </td>
	            </tr>
	            	            
	        </table>
			
		</form>
		<?php
		return ob_get_clean();
	}
}