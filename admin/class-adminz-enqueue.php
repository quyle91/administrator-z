<?php 
namespace Adminz\Admin;

class ADMINZ_Enqueue extends Adminz {
	public $options_group = "adminz_enqueue";
	public $title = "Enqueue";
	public $slug = "enqueue";	
	public $font_upload_dir = "/administrator-z/fonts";
	public $js_upload_dir = "/administrator-z/js";
	public $css_upload_dir = "/administrator-z/css";
	function __construct() {		
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);
		add_action(	'admin_init', [$this,'register_option_setting'] );	

		add_action( 'wp_head', [$this,'enqueue_custom_font'],101); // for important new style
		add_action( 'admin_enqueue_scripts', [$this,'adminz_codemirror_enqueue_scripts']);	
		add_action( 'wp_enqueue_scripts', [$this,'enqueue_uploaded_js']);
		add_action( 'wp_enqueue_scripts', [$this,'adminz_enqueue_scripts'] );
		add_action( 'wp_enqueue_scripts', [$this,'adminz_enqueue_styles'] );
		add_action( 'wp_footer', [$this,'adminz_enqueue_custom_scripts'] );

		add_action( 'wp_ajax_adminz_f_font_upload', [$this,'font_upload_callback']);
		add_action( 'wp_ajax_adminz_f_get_fonts', [$this, 'get_fonts']);
		add_action( 'wp_ajax_adminz_f_js_upload', [$this,'js_upload_callback']);
		add_action( 'wp_ajax_adminz_f_get_js', [$this, 'get_js']);
		add_action( 'wp_ajax_adminz_f_css_upload', [$this,'css_upload_callback']);
		add_action( 'wp_ajax_adminz_f_get_css', [$this, 'get_css']);
		add_action( 'wp_ajax_adminz_f_delete_file', [$this, 'delete_file']);		
 	} 	
 	function delete_file($filepath = false){
 		if(!$filepath){
 			$filepath = $_POST['filepath'];
 		}
 		if(!$wp_option){
 			$wp_option = $_POST['wp_option'];
 		}
 		$option = json_decode(get_option( $wp_option,'' ));
 		foreach ($option as $key => $value) {
 			if(strpos($value[0], basename($filepath))){
 				unset($option[$key]);
 			}
 		}
 		if(file_exists($filepath)){
 			wp_delete_file( $filepath );
 			update_option( $wp_option, json_encode( array_values($option)));
 			$message = "Done!";
 		}else{
 			$message = "No file exits";
 		}
 		wp_send_json_success($message);
        wp_die();
 	}
 	function font_upload_callback() {
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
 	function js_upload_callback() {
 		$html = [];
 		for($i = 0; $i < count($_FILES['file']['name']); $i++) {
 			$filter_upload_dir = true;
 			$filter_upload_mimes = true;
 			add_filter( 'upload_dir', function( $arr ) use( &$filter_upload_dir){
			    if ( $filter_upload_dir ) {		    	
			        $arr['path'] = str_replace($arr['subdir'], "", $arr['path']).$this->js_upload_dir;
				    $arr['url'] = str_replace($arr['subdir'], "", $arr['url']).$this->js_upload_dir;
				    $arr['subdir'] = $this->js_upload_dir;
			    }
			    return $arr;
			} );
 			add_filter( 'upload_mimes', function ($mime_types) use (&$filter_upload_mimes){
 				if ($filter_upload_mimes){
 					$mime_types['js'] = 'text/javascript';
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
					'status'=> "Js file uploaded!"
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
 	function css_upload_callback() {
 		$html = [];
 		for($i = 0; $i < count($_FILES['file']['name']); $i++) {
 			$filter_upload_dir = true;
 			$filter_upload_mimes = true;
 			add_filter( 'upload_dir', function( $arr ) use( &$filter_upload_dir){
			    if ( $filter_upload_dir ) {		    	
			        $arr['path'] = str_replace($arr['subdir'], "", $arr['path']).$this->css_upload_dir;
				    $arr['url'] = str_replace($arr['subdir'], "", $arr['url']).$this->css_upload_dir;
				    $arr['subdir'] = $this->css_upload_dir;
			    }
			    return $arr;
			} );
 			add_filter( 'upload_mimes', function ($mime_types) use (&$filter_upload_mimes){
 				if ($filter_upload_mimes){
 					$mime_types['css'] = 'text/css';
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
					'status'=> "File css uploaded!"
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
	function get_fonts(){
		ob_start();
		$font_files = glob(wp_upload_dir()['basedir'].$this->font_upload_dir.'/*');
		if(!empty($font_files) and is_array($font_files)){
			?>			
			<textarea style="display: none; " cols="100" rows="10" name="adminz_fonts_uploaded"><?php echo get_option('adminz_fonts_uploaded',''); ?></textarea>
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
							<table class="font-face-attributes" data-font="<?php echo wp_upload_dir()['baseurl'].$this->font_upload_dir.'/'.basename($font); ?>">
								<tr>
									<td><code>src:</code></td>
									<td><code><?php echo wp_upload_dir()['baseurl'].$this->font_upload_dir.'/'.basename($font); ?></code></td>
								</tr>
								<tr>
									<td><code>font-family:</code></td>
									<td><input style="width: 100%;" type="" name="font-family" required></td>
								</tr>
								<tr>
									<td><code>font-weight:</code></td>
									<td><input style="width: 100%;" type="" name="font-weight" required></td>
								</tr>
								<tr>
									<td><code>font-style:</code></td>
									<td><input style="width: 100%;" type="" name="font-style" required></td>
								</tr>
								<tr>
									<td><code>font-stretch:</code></td>
									<td><input style="width: 100%;" type="" name="font-stretch" required></td>
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
					    padding: 0px 0px;
						background: #f2f2f2;
				}
			</style>							
			<?php
		}
		wp_send_json_success(ob_get_clean());
        wp_die();
	}	
	function get_js(){
		ob_start();
		$js_files = glob(wp_upload_dir()['basedir'].$this->js_upload_dir.'/*');		
		if(!empty($js_files) and is_array($js_files)){
			?>
			<textarea style="display: none;  " cols="100" rows="10" name="adminz_js_uploaded"><?php echo get_option('adminz_js_uploaded',''); ?></textarea>
			<div style="padding: 10px; background: white;">            						
				<table>
					<tr>
						<td><code>File Js</code></td>
						<td><code>Delete</code></td>
					</tr>
			<?php
				foreach ($js_files as $js) {
					?>
					<tr>
						<td>
							<table class="js-attributes" data-js="<?php echo wp_upload_dir()['baseurl'].$this->js_upload_dir.'/'.basename($js); ?>">
								<tr>
									<td><code>src:</code></td>
									<td><code><?php echo wp_upload_dir()['baseurl'].$this->js_upload_dir.'/'.basename($js); ?></code></td>
								</tr>
								<tr>
									<td><code>Handle:</code></td>
									<td><input style="width: 100%;" type="text" name="handle" required placeholder="your-handle"></td>
								</tr>
								<tr>
									<td><code>Deps:</code></td>
									<td><input style="width: 100%;" type="text" name="deps" placeholder="jquery,jquery-ui"></td>
								</tr>
								<tr>
									<td><code>Ver:</code></td>
									<td><input style="width: 100%;" type="text" name="ver" placeholder="1.0"></td>
								</tr>
								<tr>
									<td><code>In footer:</code></td>
									<td><input style="width: 100%;" type="text" name="in_footer" placeholder="true | false "></td>
								</tr>
								<tr>
							</table>            								
						</td>
						<td>
							<button class="delete_file_js button" data-js="<?php echo wp_upload_dir()['basedir'].$this->js_upload_dir.'/'.basename($js); ?>" >Delete</button>
						</td>
					</tr>
					<?php					
				}
			?>
			</table>			
			</div>
			<style type="text/css">
				table.js-attributes td,
				.data_test td {
					    padding: 0px 0px;
						background: #f2f2f2;
				}
			</style>							
			<?php
		}
		wp_send_json_success(ob_get_clean());
        wp_die();
	}
	function get_css(){
		ob_start();
		$css_files = glob(wp_upload_dir()['basedir'].$this->css_upload_dir.'/*');		
		if(!empty($css_files) and is_array($css_files)){
			?>
			<textarea style="display: none;" cols="100" rows="10" name="adminz_css_uploaded"><?php echo get_option('adminz_css_uploaded',''); ?></textarea>
			<div style="padding: 10px; background: white;">            						
				<table>
					<tr>
						<td><code>File css</code></td>
						<td><code>Delete</code></td>
					</tr>
			<?php
				foreach ($css_files as $css) {
					?>
					<tr>
						<td>
							<table class="css-attributes" data-css="<?php echo wp_upload_dir()['baseurl'].$this->css_upload_dir.'/'.basename($css); ?>">
								<tr>
									<td><code>src:</code></td>
									<td><code><?php echo wp_upload_dir()['baseurl'].$this->css_upload_dir.'/'.basename($css); ?></code></td>
								</tr>
								<tr>
									<td><code>Handle:</code></td>
									<td><input style="width: 100%;" type="text" name="handle" required placeholder="your-handle"></td>
								</tr>
								<tr>
									<td><code>Deps:</code></td>
									<td><input style="width: 100%;" type="text" name="deps" placeholder=""></td>
								</tr>
								<tr>
									<td><code>Ver:</code></td>
									<td><input style="width: 100%;" type="text" name="ver" placeholder="1.0"></td>
								</tr>
								<tr>
									<td><code>Media:</code></td>
									<td><input style="width: 100%;" type="text" name="media" placeholder="all | print | screen "></td>
								</tr>
								<tr>
							</table>            								
						</td>
						<td>
							<button class="delete_file_css button" data-css="<?php echo wp_upload_dir()['basedir'].$this->css_upload_dir.'/'.basename($css); ?>" >Delete</button>
						</td>
					</tr>
					<?php					
				}
			?>
			</table>			
			</div>
			<style type="text/css">
				table.css-attributes td,
				.data_test td {
					    padding: 0px 0px;
						background: #f2f2f2;
				}
			</style>							
			<?php
		}
		wp_send_json_success(ob_get_clean());
        wp_die();
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
						<h3>Custom font</h3>
					</th>
				</tr>
				<tr valign="top">
	                <th scope="row">Upload your font files</th>
	                <td>
						<form class="fileUpload" enctype="multipart/form-data">
						    <div class="form-group">
						        <input type="file" id="upload_fonts" accept=".otf,.ttf,.woff,.woff2,.sfnt" multiple />
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
	            	<td class="get_fonts"> </td>
	            </tr>	            
				<tr valign="top">
	                <th scope="row">Fonts supported</th>
	                <td>
 						<label>
	                		<input type="checkbox" name="adminz_choose_font_lato" <?php if(get_option('adminz_choose_font_lato') =="on") echo "checked"; ?>> Lato vietnamese
	                	</label><br>
	                	<?php 
	                	$adminz_supported_font = (array)get_option( 'adminz_supported_font' );
	                	?>
	                	<label>
	                		<input type="checkbox" name="adminz_supported_font[]" value="fontawesome" <?php if(in_array('fontawesome', $adminz_supported_font)) echo "checked"; ?>> Font Awesome <a target="_blank" href="<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/fontawesome/demo.html'; ?>"></a><a target="_blank" href="https://fontawesome.com/icons?d=gallery&p=2&m=free"></a>
	                	</label><br>
	                	<label>
	                		<input type="checkbox" name="adminz_supported_font[]" value="icofont" <?php if(in_array('icofont', $adminz_supported_font)) echo "checked"; ?>> Icofont <a target="_blank" href="<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/icofont/demo.html'; ?>"></a>
	                	</label><br>
	                	<label>
	                		<input type="checkbox" name="adminz_supported_font[]" value="eicons" <?php if(in_array('eicons', $adminz_supported_font)) echo "checked"; ?>> Eicons <a target="_blank" href="<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/eicons/demo.html'; ?>"></a>
	                	</label><br>
	                </td>
	            </tr>
	            <tr valign="top">
					<th scope="row">
						<h3>CSS Libraries</h3>
					</th>
				</tr> 
				<tr valign="top">
	                <th scope="row">Upload your CSS library files</th>
	                <td>
						<form class="fileUpload" enctype="multipart/form-data">
						    <div class="form-group">						        
						        <input type="file" id="upload_css" accept=".css" multiple />
						    </div>
						</form>	
						<br>
						<div class="data_test"></div>
	                </td>
	            </tr>
	            <tr valign="top">
	            	<th scope="row">
	            		CSS uploaded
	            	</th>
	            	<td class="get_css"></td>	            	
	            </tr>
	            <tr valign="top">
	            	<th>Custom CSS</th>
	            	<td>
	            		<textarea class="adminz_css_editor" style="width: 100%; background: #f2f2f2; border: 3px solid gray;" rows="10" name="adminz_custom_css_fonts" placeholder="Your custom css here will be enqueue in header..."><?php echo get_option('adminz_custom_css_fonts',''); ?></textarea>
	            	</td>
	            </tr>
	            <tr valign="top">
					<th scope="row">
						<h3>JS Libraries</h3>
					</th>
				</tr> 
				<tr valign="top">
	                <th scope="row">Upload your JS library files</th>
	                <td>
						<form class="fileUpload" enctype="multipart/form-data">
						    <div class="form-group">						        
						        <input type="file" id="upload_js" accept=".js" multiple />
						    </div>
						</form>	
						<br>
						<div class="data_test"></div>
	                </td>
	            </tr>
	            <tr valign="top">
	            	<th scope="row">
	            		JS uploaded
	            	</th>
	            	<td class="get_js"></td>	            	
	            </tr>
	            <tr valign="top">
	            	<th>Custom Javascript </th>
	            	<td>
	            		<textarea class="adminz_js_editor" style="width: 100%; background: #f2f2f2; border: 3px solid gray;" rows="10" name="adminz_custom_js" placeholder="Code inside script tag will be enqueue in footer..."><?php echo get_option('adminz_custom_js',''); ?></textarea>
	            	</td>
	            </tr>
				<tr valign="top">
					<th scope="row">
						JS supported
					</th>
					<td>
						<?php 
	                	$adminz_supported_js = (array)get_option( 'adminz_supported_js' );
	                	?>
						<label>
	                		<input type="checkbox" name="adminz_supported_js[]" value="flickity" <?php if(in_array('flickity',$adminz_supported_js)) echo "checked"; ?>> Flickity
	                	</label><br>
	                	<label>
	                		<input type="checkbox" name="adminz_supported_js[]" value="fotorama" <?php if(in_array('fotorama',$adminz_supported_js)) echo "checked"; ?>> Fotorama
	                	</label><br>
					</td>
				</tr>
				<tr valign="top">
	                <th scope="row">Wordpress Registered</th>
	                <td>
	                	<?php 	     
	                	
						foreach ($GLOBALS['wp_scripts']->registered as $handle=> $obj){
							$option = (array)get_option('adminz_enqueue_registed_js_');
							$checked = in_array($handle,$option)? 'checked' : "" ;
							$link = $obj->src.'<a target="blank" href="'.$obj->src.'"></a>';
							ob_start();
							echo "<p>handle:</p>";
							echo '<code>'; print_r($obj->handle); echo '</code>'; 
							echo "<p>src:</p>";
							echo '<code>'; print_r($obj->src); echo '</code>'; 
							echo "<p>deps:</p>";
							echo '<code>'; print_r($obj->deps); echo '</code>'; 
							echo "<p>ver:</p>";
							echo '<code>'; print_r($obj->ver); echo '</code>'; 
							echo "<p>args:</p>";
							echo '<code>'; print_r($obj->args); echo '</code>'; 
							//print_r($obj->extra);
							echo "<p>textdomain:</p>";
							echo '<code>'; print_r($obj->textdomain); echo '</code>'; 
							echo "<p>translations_path:</p>";
							echo '<code>'; print_r($obj->translations_path); echo '</code>'; 
							$objhtml = ob_get_clean();
							echo '<label><input class="adminz_enqueue_registed_js_" type="checkbox" name="adminz_enqueue_registed_js_[]" value="'.$handle.'" '.$checked.' /> '.$handle.'<code>'.$link.'</code></label><button class="show_js_data" type="button" style="border: none; cursor: pointer;">...</button></br>';
							echo "<div class='more_info hidden'><pre>";echo $objhtml; echo "</pre></div>";
						}
						?>
	                	<p><em>https://developer.wordpress.org/reference/functions/wp_enqueue_script/</em></p> 
                	</td>
	            </tr>  
	            <tr valign="top">
					<th scope="row">
						<h3>CSS Libraries</h3>
					</th>
				</tr>  
				<tr valign="top">
					<th scope="row">
						Wordpress Registered
					</th>
					<td>
						<?php 
						foreach ($GLOBALS['wp_styles']->registered as $handle => $obj) {
							$option = (array)get_option('adminz_enqueue_registed_css_');
							$checked = in_array($handle,$option)? 'checked' : "" ;
							$link = $obj->src.'<a target="blank" href="'.$obj->src.'"></a>';
							ob_start();
							echo "<p>handle:</p>";
							echo '<code>'; print_r($obj->handle); echo '</code>'; 
							echo "<p>src:</p>";
							echo '<code>'; print_r($obj->src); echo '</code>'; 
							echo "<p>deps:</p>";
							echo '<code>'; print_r($obj->deps); echo '</code>'; 
							echo "<p>ver:</p>";
							echo '<code>'; print_r($obj->ver); echo '</code>'; 
							echo "<p>args:</p>";
							echo '<code>'; print_r($obj->args); echo '</code>'; 
							//print_r($obj->extra);
							echo "<p>textdomain:</p>";
							echo '<code>'; print_r($obj->textdomain); echo '</code>'; 
							echo "<p>translations_path:</p>";
							echo '<code>'; print_r($obj->translations_path); echo '</code>'; 
							$objhtml = ob_get_clean();
							echo '<label><input class="adminz_enqueue_registed_css_" type="checkbox" name="adminz_enqueue_registed_css_[]" value="'.$handle.'" '.$checked.' /> '.$handle.'<code>'.$link.'</code></label><button class="show_js_data" type="button" style="border: none; cursor: pointer;">...</button></br>';
							echo "<div class='more_info hidden'><pre>";echo $objhtml; echo "</pre></div>";
						}
						 ?>
					</td>
				</tr>            
 			</table>		
	        <?php echo submit_button(); ?>
	    </form>	    
		<?php
		echo $this->tab_scripts();
		return ob_get_clean();
	}
	function tab_scripts(){
		?>
		<style type="text/css">
	    	.more_info:not(.hidden){
	    		padding: 10px;
	    		background: white;
	    	}
	    </style>
		<script type="text/javascript">
			jQuery(function($) {								
				function fill_data_fields_font(){
					var data_fonts = $('textarea[name="adminz_fonts_uploaded"]').val();
					if(!data_fonts) return;
					data_fonts = JSON.parse(data_fonts);
					if( data_fonts.length){
						for (var i = 0; i < data_fonts.length; i++) {
							var font_key = data_fonts[i][0];							
							var table_fonts = $('.font-face-attributes[data-font="'+data_fonts[i][0]+'"');
							table_fonts.find('input[name="font-family"]').val(data_fonts[i][1]);
							table_fonts.find('input[name="font-weight"]').val(data_fonts[i][2]);
							table_fonts.find('input[name="font-style"]').val(data_fonts[i][3]);
							table_fonts.find('input[name="font-stretch"]').val(data_fonts[i][4]);
						}
					}
				}
				get_fonts();
				function get_fonts(){
					$(".get_fonts").html("");
					$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : {
                            action: "adminz_f_get_fonts"
                        },
                        context: this,
                        beforeSend: function(){ },
                        success: function(response) {
                        	if(response.data.length){
                        		$(".get_fonts").html(response.data);
                        	}
                        	fill_data_fields_font();
                        },
                        error: function( jqXHR, textStatus, errorThrown ){
                        	console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                        }
                    })
				}
				function fill_data_fields_js(){
					var data_js = $('textarea[name="adminz_js_uploaded"]').val();
					if(!data_js) return;
					data_js = JSON.parse(data_js);
					if( data_js.length){
						for (var i = 0; i < data_js.length; i++) {
							var font_key = data_js[i][0];							

							var src = data_js[i][1];
							var handle = data_js[i][0];
							var deps = data_js[i][2]
							var ver = data_js[i][3];
							var in_footer = data_js[i][4];

							var table_js = $('.js-attributes[data-js="'+src+'"');							
							table_js.find('input[name="handle"]').val(handle);
							table_js.find('input[name="deps"]').val(deps);
							table_js.find('input[name="ver"]').val(ver);
							table_js.find('input[name="in_footer"]').val(in_footer);
						}
					}
				}
				get_js();	
				function get_js(){
					$(".get_js").html("");
					$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : {
                            action: "adminz_f_get_js"
                        },
                        context: this,
                        beforeSend: function(){ },
                        success: function(response) {
                        	if(response.data.length){
                        		$(".get_js").html(response.data);
                        	}   
                        	fill_data_fields_js();                     	
                        },
                        error: function( jqXHR, textStatus, errorThrown ){
                        	console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                        }
                    })
				}
				function fill_data_fields_css(){
					var data_css = $('textarea[name="adminz_css_uploaded"]').val();
					if(!data_css) return;
					data_css = JSON.parse(data_css);					
					if( data_css.length){
						for (var i = 0; i < data_css.length; i++) {
							var font_key = data_css[i][0];							

							var src = data_css[i][1];
							var handle = data_css[i][0];
							var deps = data_css[i][2]
							var ver = data_css[i][3];
							var media = data_css[i][4];

							var table_css = $('.css-attributes[data-css="'+src+'"');							
							table_css.find('input[name="handle"]').val(handle);
							table_css.find('input[name="deps"]').val(deps);
							table_css.find('input[name="ver"]').val(ver);
							table_css.find('input[name="media"]').val(media);
						}
					}
				}
				get_css();	
				function get_css(){
					$(".get_css").html("");
					$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : {
                            action: "adminz_f_get_css"
                        },
                        context: this,
                        beforeSend: function(){ },
                        success: function(response) {
                        	if(response.data.length){
                        		$(".get_css").html(response.data);
                        	}   
                        	fill_data_fields_css();                     	
                        },
                        error: function( jqXHR, textStatus, errorThrown ){
                        	console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                        }
                    })
				}
				$('body').on('keyup', '.font-face-attributes input', function() {	
					var fonts_uploaded = [];
					$(".font-face-attributes").each(function(){
						var data_font = $(this).data('font');
						var font_family = $(this).find('input[name="font-family"]').val();
						var font_weight = $(this).find('input[name="font-weight"]').val();
						var font_style = $(this).find('input[name="font-style"]').val();
						var font_stretch = $(this).find('input[name="font-stretch"]').val();
						fonts_uploaded.push([
							data_font,
							font_family,
							font_weight,
							font_style,
							font_stretch,
							]);						
					});
					$('textarea[name="adminz_fonts_uploaded"]').val(JSON.stringify(fonts_uploaded));
				});	
				$('body').on('keyup', '.js-attributes input', function() {	
					var js_uploaded = [];
					$(".js-attributes").each(function(){
						var handle = $(this).find('input[name="handle"]').val();
						var src = $(this).data('js');
						var deps = $(this).find('input[name="deps"]').val();
						var ver = $(this).find('input[name="ver"]').val();
						var in_footer = $(this).find('input[name="in_footer"]').val();
						js_uploaded.push([
							handle,
							src,
							deps,
							ver,
							in_footer	
							]);		
					});
					$('textarea[name="adminz_js_uploaded"]').val(JSON.stringify(js_uploaded));
				});
				$('body').on('keyup', '.css-attributes input', function() {	
					var css_uploaded = [];
					$(".css-attributes").each(function(){
						var handle = $(this).find('input[name="handle"]').val();
						var src = $(this).data('css');
						var deps = $(this).find('input[name="deps"]').val();
						var ver = $(this).find('input[name="ver"]').val();
						var media = $(this).find('input[name="media"]').val();
						css_uploaded.push([
							handle,
							src,
							deps,
							ver,
							media	
							]);		
					});
					$('textarea[name="adminz_css_uploaded"]').val(JSON.stringify(css_uploaded));
				});
			    $('body').on('click', '.delete_file_font', function() {
		        	var font_path = $(this).data("font");
		        	$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : {
                            action: "adminz_f_delete_file",
                            filepath : font_path,
                            wp_option: "adminz_fonts_uploaded"
                        },
                        context: this,
                        beforeSend: function(){ },
                        success: function(response) {
                            if(response.success) {                            	
                            	get_fonts();
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
			    $('body').on('click', '.delete_file_js', function() {
		        	var font_path = $(this).data("js");
		        	$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : {
                            action: "adminz_f_delete_file",
                            filepath : font_path,
                            wp_option: "adminz_js_uploaded"
                        },
                        context: this,
                        beforeSend: function(){ },
                        success: function(response) {
                            if(response.success) {                            	
                            	get_js();
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
			    $('body').on('click', '.delete_file_css', function() {
		        	var font_path = $(this).data("css");
		        	$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : {
                            action: "adminz_f_delete_file",
                            filepath : font_path,
                            wp_option: "adminz_css_uploaded"
                        },
                        context: this,
                        beforeSend: function(){ },
                        success: function(response) {
                            if(response.success) {                            	
                            	get_css();
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
			        form_data.append('action', 'adminz_f_font_upload');
			        $.ajax({
			            url : '<?php echo admin_url('admin-ajax.php'); ?>',
			            type: 'POST',
			            contentType: false,
			            processData: false,
			            data: form_data,
			            beforeSend: function(){                                 
	                        var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
	                        $this.closest('td').find('.data_test').html(html_run);
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
			            	$this.closest('td').find('.data_test').html(html_run);
			            	get_fonts();
			            }
			        });
			    });
			    $('body').on('change', '#upload_js', function() {
			        $this = $(this);
			        file_obj = $this.prop('files');
			        console.log(file_obj);
			        form_data = new FormData();
			        for(i=0; i<file_obj.length; i++) {
			            form_data.append('file[]', file_obj[i]);
			        }
			        form_data.append('action', 'adminz_f_js_upload');
			        $.ajax({
			            url : '<?php echo admin_url('admin-ajax.php'); ?>',
			            type: 'POST',
			            contentType: false,
			            processData: false,
			            data: form_data,
			            beforeSend: function(){                                 
	                        var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
	                        $this.closest('td').find('.data_test').html(html_run);
	                    },
			            success: function (response) {
			            	console.log(response.data);
			            	var html_run = "<div style='padding: 10px; background: white;'><table>";
			            	for (var i = 0; i < response.data.length; i++) {
			            		html_run += "<tr>";
			            		if(response.data[i].status == "Js file uploaded!"){
			            			html_run += "<td><div class='notice notice-alt notice-success updated-message'><p aria-label='done'>"+ response.data[i].status + "</p></td>";
			            		}else{
			            			html_run += "<td><div class='notice notice-alt notice-warning upload-error-message'><p aria-label='Checking...'>"+ response.data[i].status + "</p></td>";
			            		}
			            		
			            		html_run += "<td>"+ response.data[i].file + "</td>";
			            		html_run += "</tr>";
			            	}
			            	$this.closest('td').find('.data_test').html(html_run);
			            	get_js();
			            }
			        });
			    });	
			    $('body').on('change', '#upload_css', function() {
			        $this = $(this);
			        file_obj = $this.prop('files');
			        console.log(file_obj);
			        form_data = new FormData();
			        for(i=0; i<file_obj.length; i++) {
			            form_data.append('file[]', file_obj[i]);
			        }
			        form_data.append('action', 'adminz_f_css_upload');
			        $.ajax({
			            url : '<?php echo admin_url('admin-ajax.php'); ?>',
			            type: 'POST',
			            contentType: false,
			            processData: false,
			            data: form_data,
			            beforeSend: function(){                                 
	                        var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
	                        $this.closest('td').find('.data_test').html(html_run);
	                    },
			            success: function (response) {
			            	console.log(response.data);
			            	var html_run = "<div style='padding: 10px; background: white;'><table>";
			            	for (var i = 0; i < response.data.length; i++) {
			            		html_run += "<tr>";
			            		if(response.data[i].status == "File css uploaded!"){
			            			html_run += "<td><div class='notice notice-alt notice-success updated-message'><p aria-label='done'>"+ response.data[i].status + "</p></td>";
			            		}else{
			            			html_run += "<td><div class='notice notice-alt notice-warning upload-error-message'><p aria-label='Checking...'>"+ response.data[i].status + "</p></td>";
			            		}
			            		
			            		html_run += "<td>"+ response.data[i].file + "</td>";
			            		html_run += "</tr>";
			            	}
			            	$this.closest('td').find('.data_test').html(html_run);
			            	get_css();
			            }
			        });
			    });		    
			    $('body').on('click', '.show_js_data', function(){
			    	var target = $(this).next().next(".more_info").toggleClass('hidden');
			    });

			    wp.codeEditor.initialize($('.adminz_css_editor'));
			    wp.codeEditor.initialize($('.adminz_js_editor'));			    
			});
		</script>
		<?php
	}
	function enqueue_custom_font(){
		$this->enqueue_supported_font();
		$fonts = json_decode(get_option( 'adminz_fonts_uploaded','' ));
		$css = get_option( 'adminz_custom_css_fonts','' );
		ob_start();
		if(!empty($fonts) and is_array($fonts)){			
			?>
			<style id="adminz_custom_fonts" type="text/css">
				<?php foreach ($fonts as $font) {
					?>
					@font-face {
						src: url(<?php echo $font[0]; ?>);
					  	font-family: <?php echo $font[1]; ?>;
					  	font-weight: <?php echo $font[2]; ?>;
					  	font-style: <?php echo $font[3]; ?>;
					  	font-stretch: <?php echo $font[4]; ?>;
					}
					<?php
				} ?>
			</style>
			<?php
		}
		if($css){ 
			?>
			<style id="adminz_custom_css" type="text/css">
				<?php echo $css; ?>
			</style>
			<?php
		}
		$buffer = ob_get_clean();
 		echo str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);
	}
	function enqueue_supported_font(){
		if(get_option('adminz_choose_font_lato') =="on"){
			wp_enqueue_style( 'adminz_lato',plugin_dir_url(ADMINZ_BASENAME).'assets/lato/all.css', array(), '1.0', $media = 'all' );
		}
		$adminz_supported_font = (array)get_option( 'adminz_supported_font' );
		if(is_array($adminz_supported_font) and !empty($adminz_supported_font)){
			foreach ($adminz_supported_font as $key => $value) {
				switch ($value) {
					case 'fontawesome':
						wp_enqueue_style( 'adminz_fontawesome',plugin_dir_url(ADMINZ_BASENAME).'assets/fontawesome/css/all.min.css', array(), '5.15.2', $media = 'all' );
						break;
					case 'icofont':
						wp_enqueue_style( 'adminz_icofont',plugin_dir_url(ADMINZ_BASENAME).'assets/icofont/icofont.min.css', array(), '1.0.1', $media = 'all' );						
						break;
					case 'eicons':
						wp_enqueue_style( 'adminz_eicons',plugin_dir_url(ADMINZ_BASENAME).'assets/eicons/all.min.css', array(), '5.11.0', $media = 'all' );
						break;
				}
			}
		}
	}
	function enqueue_uploaded_js(){
		$option = get_option('adminz_js_uploaded','');
		if(!$option) return ; 
		$option = json_decode( $option);
		if(!empty($option) and is_array($option)){			
			foreach ($option as $key => $value) {
				$handle = $value[0];  
				$src = $value[1];  
				$deps = $value[2]? explode(",", $value[2]) : [];
				$ver = $value[3];  
				$in_footer = ($value[4] == "true")? true : false; 
				wp_enqueue_script( $handle, $src, $deps, $ver, $in_footer );
			}	
		}
	}
	function adminz_enqueue_styles(){
		$option = (array)get_option('adminz_enqueue_registed_css_');		
 		if(!empty($option) and is_array($option)){ 			
 			foreach ($option as $key => $value) {
 				wp_enqueue_style($value);
 			} 			
 		}
 		$css_uploaded = get_option('adminz_css_uploaded','');

 		if(!$css_uploaded) return ; 
 		$css_uploaded = json_decode( $css_uploaded);

 		if(!empty($css_uploaded) and is_array($css_uploaded)){			
			foreach ($css_uploaded as $key => $value) {
				$handle = $value[0];  
				$src = $value[1];  
				$deps = $value[2]? explode(",", $value[2]) : [];
				$ver = $value[3];  
				$media = $value[4]? "all" : $value[4];
				wp_enqueue_style( $handle, $src, $deps, $ver, $media );
			}	
		}
	}
 	function adminz_enqueue_scripts(){
 		wp_register_script( 'adminz_flickity_js', plugin_dir_url(ADMINZ_BASENAME).'assets/flickity/flickity.pkgd.min.js', array('jquery'),null,true );
 		wp_register_script( 'adminz_fotorama_js', plugin_dir_url(ADMINZ_BASENAME).'assets/fotorama/fotorama.js', array( 'jquery' ),null,true );
 		$adminz_supported_js = (array)get_option( 'adminz_supported_js' );
 		if(!empty($adminz_supported_js) and is_array($adminz_supported_js)){
 			foreach ($adminz_supported_js as $key => $value) {
 				switch ($value) {
 					case 'flickity':
 						wp_enqueue_script( 'adminz_flickity_js');
 						break;
 					case 'fotorama':
 						wp_enqueue_script( 'adminz_fotorama_js');
 						break;
 				}
 			}
 		}
 		$option = (array)get_option('adminz_enqueue_registed_js_');
 		if(!empty($option) and is_array($option)){ 			
 			foreach ($option as $key => $value) {
 				wp_enqueue_script($value);
 			} 			
 		} 		
 	}
 	function adminz_enqueue_custom_scripts(){
 		$custom_script = get_option( 'adminz_custom_js', '' );
 		if($custom_script){
 			echo '<script>'.str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $custom_script).'</script>';
 		}
 	}
 	function adminz_codemirror_enqueue_scripts($hook) {

	  	$editor['codeEditor'] = wp_enqueue_code_editor(array('type' => 'text/html'));
	  	wp_localize_script('jquery', "adminz_editor", $editor);
	  	wp_enqueue_script('wp-theme-plugin-editor');
	  	wp_enqueue_style('wp-codemirror');
	}
 	function register_option_setting() {
		register_setting( $this->options_group, 'adminz_fonts_uploaded' );
		register_setting( $this->options_group, 'adminz_js_uploaded' );
		register_setting( $this->options_group, 'adminz_css_uploaded' );
		register_setting( $this->options_group, 'adminz_custom_css_fonts' );
		register_setting( $this->options_group, 'adminz_custom_js' );
		register_setting( $this->options_group, 'adminz_supported_font' );
		register_setting( $this->options_group, 'adminz_choose_font_lato' );
		register_setting( $this->options_group, 'adminz_enqueue_registed_js_' );
		register_setting( $this->options_group, 'adminz_enqueue_registed_css_' );
		register_setting( $this->options_group, 'adminz_supported_js' );
	}
 }


 
