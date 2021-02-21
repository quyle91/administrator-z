<?php 
namespace Adminz\Admin;

class ADMINZ_Fonts extends Adminz {
	public $options_group = "adminz_fonts";
	public $title = "Fonts";
	public $slug = "fonts";	
	public $font_upload_dir = "/administrator-z/fonts";
	function __construct() {
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);
		add_action(	'admin_init', [$this,'register_option_setting'] );
		add_action( 'wp_head', [$this,'enqueue_lato'],101);
		add_action( 'wp_ajax_file_upload', [$this,'file_upload_callback']);	
		add_action( 'wp_ajax_delete_file', [$this, 'delete_file']);
		add_action( 'wp_ajax_get_fields', [$this, 'get_fields']);
 	}
 	function delete_file($filepath = false){
 		if(!$filepath){
 			$filepath = $_POST['filepath'];
 		}
 		if(file_exists($filepath)){
 			wp_delete_file( $filepath );
 			$message = "Done!";
 		}else{
 			$message = "No file exits";
 		}

 		wp_send_json_success($message);
        wp_die();
 	}
 	function file_upload_callback() {
 		$html = []; 		
 		for($i = 0; $i < count($_FILES['file']['name']); $i++) {
 			$filter_upload_dir = true;
 			$filter_upload_mimes = true;
 			add_filter( 'upload_dir', function( $arr ) use( &$filter_upload_dir){
			    if ( $filter_upload_dir ) {		    	
			        $arr['path'] = str_replace($arr['subdir'], "", $arr['path']).$this->font_upload_dir;
				    $arr['url'] = str_replace($arr['subdir'], "", $arr['url']).$this->font_upload_dir;
				    $arr['subdir'] = $this->font_upload_dir;
			    }
			    return $arr;
			} );
 			add_filter( 'upload_mimes', function ($mime_types) use (&$filter_upload_mimes){
 				if ($filter_upload_mimes){
 					$mime_types['otf'] = 'font/otf';
				  	$mime_types['ttf'] = 'font/ttf';
				  	$mime_types['woff'] = 'font/woff';
				  	$mime_types['woff2'] = 'font/woff2';
				  	$mime_types['sfnt'] = 'font/sfnt';
				  	return $mime_types;
 				}
 			}, 1, 1 );

			$res = wp_upload_bits($_FILES['file']['name'][$i], null, file_get_contents($_FILES['file']['tmp_name'][$i]));	

			// remove filters
			$filter_upload_dir = false;
			$filter_upload_mimes = false;

			if($res['url']){
				$html[] = [
					'file'=>$res['url'],
					'status'=> "File font uploaded!"
				];
			}else{
				$html[] = [
					'file'=>$_FILES['file']['name'][$i],
					'status'=> $res['error']
				];
			}
 		}
 		wp_send_json_success($html);
	    wp_die();
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
	function get_fields(){
		ob_start();
		$font_files = glob(wp_upload_dir()['basedir'].$this->font_upload_dir.'/*');
		if(!empty($font_files) and is_array($font_files)){
			?>
			<div style="padding: 10px; background: white;">            						
				<table>
					<tr>
						<td><code>File font</code></td>
						<td><code>Delete</code></td>
					</tr>
			<?php
				foreach ($font_files as $font) {
					?>
					<tr>
						<td>
							<table class="font-face-attributes">
								<tr>
									<td><code>src:</code></td>
									<td><code>url("<?php echo wp_upload_dir()['baseurl'].$this->font_upload_dir.'/'.basename($font); ?>");</code></td>
								</tr>
								<tr>
									<td><code>font-family:</code></td>
									<td><input type="" name=""></td>
								</tr>
								<tr>
									<td><code>font-weight:</code></td>
									<td><input type="" name=""></td>
								</tr>
								<tr>
									<td><code>font-style:</code></td>
									<td><input type="" name=""></td>
								</tr>
								<tr>
									<td><code>font-stretch:</code></td>
									<td><input type="" name=""></td>
								</tr>
							</table>            								
						</td>
						<td>
							<button class="delete_file_font button" data-font="<?php echo wp_upload_dir()['basedir'].$this->font_upload_dir.'/'.basename($font); ?>" >Delete</button>
						</td>
					</tr>
					<?php					
				}
			?>
			</table>
			</div>
			<style type="text/css">
				table.font-face-attributes td,
				.data_test td
				{
					    padding: 5px 0px;
						background: #f2f2f2;
				}
			</style>							
			<?php
		}
		wp_send_json_success(ob_get_clean());
        wp_die();
	}
	function tab_scripts(){
		?>
		<script type="text/javascript">
			jQuery(function($) {
				get_fields();
				function get_fields(){
					$(".get_fields").html("");
					$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : {
                            action: "get_fields"
                        },
                        context: this,
                        beforeSend: function(){ },
                        success: function(response) {
                        	if(response.data.length){
                        		$(".get_fields").html(response.data);
                        	}			                        	
                        },
                        error: function( jqXHR, textStatus, errorThrown ){
                        	console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                        }
                    })
				}						
			    $('body').on('click', '.delete_file_font', function() {
		        	var font_path = $(this).data("font");
		        	$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : {
                            action: "delete_file",
                            filepath : font_path
                        },
                        context: this,
                        beforeSend: function(){ },
                        success: function(response) {
                            if(response.success) {                            	
                            	get_fields();
                            }
                            else {
                                alert('There is an error');
                            }
                        },
                        error: function( jqXHR, textStatus, errorThrown ){
                            
                            console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                        }
                    })
			        return false;
			    });
			    $('body').on('change', '#upload_fonts', function() {
			        $this = $(this);
			        file_obj = $this.prop('files');
			        console.log(file_obj);
			        form_data = new FormData();
			        for(i=0; i<file_obj.length; i++) {
			            form_data.append('file[]', file_obj[i]);
			        }
			        form_data.append('action', 'file_upload');
			        $.ajax({
			            url : '<?php echo admin_url('admin-ajax.php'); ?>',
			            type: 'POST',
			            contentType: false,
			            processData: false,
			            data: form_data,
			            beforeSend: function(){                                 
	                        var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
	                        $('.data_test').html(html_run);
	                    },
			            success: function (response) {
			            	console.log(response.data);
			            	var html_run = "<div style='padding: 10px; background: white;'><table>";
			            	for (var i = 0; i < response.data.length; i++) {
			            		html_run += "<tr>";
			            		if(response.data[i].status == "File font uploaded!"){
			            			html_run += "<td><div class='notice notice-alt notice-success updated-message'><p aria-label='done'>"+ response.data[i].status + "</p></td>";
			            		}else{
			            			html_run += "<td><div class='notice notice-alt notice-warning upload-error-message'><p aria-label='Checking...'>"+ response.data[i].status + "</p></td>";
			            		}
			            		
			            		html_run += "<td>"+ response.data[i].file + "</td>";
			            		html_run += "</tr>";
			            	}
			            	$('.data_test').html(html_run);
			            	get_fields();
			            }
			        });
			    });
			});
		</script>
		<?php
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
						<h3>Fonts Upload</h3>
					</th>
				</tr>
				<tr valign="top">
	                <th scope="row">Upload your font files</th>
	                <td>
						<form class="fileUpload" enctype="multipart/form-data">
						    <div class="form-group">
						        <label><?php _e('Choose File:'); ?></label>
						        <input type="file" id="upload_fonts" accept="*" multiple />
						    </div>
						</form>	
						<br>
						<div class="data_test"></div>						
	                </td>
	            </tr>
	            <tr valign="top">
	            	<th scope="row">
	            		Fonts uploaded
	            	</th>
	            	<td class="get_fields">
	            	</td>	            	
	            </tr>
	        	<tr valign="top">
					<th scope="row">
						<h3>Fonts Supported</h3>
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
		echo $this->tab_scripts();
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