<?php 
namespace Adminz\Admin;
class ADMINZ_OtherTools extends Adminz
{
	public $options_group = "adminz_othertools";
	public $title = 'Other Tools';
	public $slug  = 'adminz_othertools';
	public $rand;
	function __construct() {		
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);		
		add_action( 'init', [$this, 'load_shortcodes'] );	
		add_action( 'wp_ajax_file_upload', [$this,'file_upload_callback']);
		add_action( 'wp_ajax_nopriv_file_upload', [$this,'file_upload_callback']);
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
	function file_upload_callback() {
		global $wpdb;
	    $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
	    $html = [];
	    for($i = 0; $i < count($_FILES['file']['name']); $i++) {
	    	$filename = $_FILES['file']['name'][$i];
        	$filetype = $_FILES['file']['type'][$i];
	    	$html_item = [
	    		'name' => $filename,
	    		'type' => $filetype,	    		
	    		'replaced_url' =>false,
	    	];
	        if (in_array($_FILES['file']['type'][$i], $arr_img_ext)) {
	        	$html_item['type_support'] = true;
	        	$searchname = sanitize_title(str_replace([".jpg",".jpeg",".png",".gif"], "", $filename));
	        	$args = array(	        
			        'name' => $searchname,
			        'post_type'=> 'attachment',
			    );     
			    $olds = get_posts( $args );
			    $old = 0;
			    if($olds){
			        $old = $olds[0];
			    }

	        	if($old){
	        		$html_item['replaced'] = true;

			        // 1: get informations
			        $oldid = $old->ID;
			        $parent = $old->post_parent;

			        $meta = get_post_meta($oldid);
			        $_wp_attached_file = $meta['_wp_attached_file'][0];
					$_wp_attachment_metadata = $meta['_wp_attachment_metadata'][0];
					$olddir = "/".substr($_wp_attached_file,0,7);
					

					$post_set_thumbnail = $wpdb->get_results ( "
					    SELECT post_id
					    FROM  $wpdb->postmeta
					        WHERE meta_key = '_thumbnail_id'
					        AND meta_value = ".$oldid."
					" );

					// 2: delete old Img
					wp_delete_attachment( $oldid, true );
					
					// 3: upload new image
					$_filterhook = true;
					add_filter( 'upload_dir', function( $arr ) use( &$_filterhook ,$olddir){
					    if ( $_filterhook ) {
					    	$target = $olddir;
					        $arr['path'] = str_replace($arr['subdir'], "", $arr['path']).$target;
						    $arr['url'] = str_replace($arr['subdir'], "", $arr['url']).$target;
						    $arr['subdir'] = $target;
					    }
					    return $arr;
					} );
					$res = wp_upload_bits($filename, null, file_get_contents($_FILES['file']['tmp_name'][$i]));
					$dirs = wp_upload_dir();
					$_filterhook = false; // for remove filter hook

					// 4: update/ fix for new image informations
					$restype = wp_check_filetype($res['file']);					
				    $attachment = array(
				        'guid' => $dirs['baseurl'] . '/' . _wp_relative_upload_path($res['file']) ,
				        'post_mime_type' => $restype['type'],
				        'post_title' => preg_replace('/\.[^.]+$/', '', basename($res['file'])) ,
				        'post_content' => '',
				        'post_status' => 'inherit',
				        'post_parent' => $parent
				    );
				    $attach_id = wp_insert_attachment($attachment, $res['file']);
				    $attach_data = wp_generate_attachment_metadata($attach_id, $res['file']);
				    wp_update_attachment_metadata($attach_id, $attach_data);				    
				    

				    $wpdb->update( 
				        $wpdb->posts,         
				        array('ID'=>$oldid),
				        array('ID'=>$attach_id)
				    );
				    $wpdb->update( 
				        $wpdb->postmeta,         
				        array('post_id'=>$oldid),
				        array('post_id'=>$attach_id)
				    );	
				    $attach_id = $oldid;


				    $filter_blank = true;
				    add_filter( 'wp_get_attachment_link', function ($markup) use (&$filter_blank) {
				    	if($filter_blank){
				    		return preg_replace('/^<a([^>]+)>(.*)$/', '<a\\1 target="_blank">\\2', $markup);
				    	}
					    return $markup;
					}, 10, 6 );
				    $html_item['replaced_url'] = wp_get_attachment_link($attach_id,"full",false,false,false,['target'=>'_blank']);
				    $filter_blank = false;


				    if(!empty($post_set_thumbnail) and is_array($post_set_thumbnail)){
						foreach ($post_set_thumbnail as $key => $value) {	
							set_post_thumbnail( $value->post_id, $attach_id );
						}
					}
			    }else{
			    	$html_item['replaced'] = false;
			    }	            
	        }else{
	        	$html_item['type_support'] = false;
	        }
	        $html[] = $html_item;
	    }	    
	    wp_send_json_success($html);
	    wp_die();
	}
	function tab_html(){
		global $adminz;
		ob_start();
		?>
		<table class="form-table">
        	<tr valign="top">
        		<th><h3>Replace Image </h3></th>
        		<td>
        			
        		</td>
        	</tr>	  
        	<tr valign="top">
        		<th>Input your image</th>
        		<td>
        			<form class="fileUpload" enctype="multipart/form-data">
					    <div class="form-group">
					        <label><?php _e('Choose File:'); ?></label>
					        <input type="file" id="replace_image" accept="image/*" multiple />
					        <p><code>Keep ID & image url</code></p>
					        <div><em>Usage: Prepare a replacement image in advance with the same name as the current image on the website. Click the upload button.</em></div>
					        <div><em>Note: Image type support: Jpg/ jpeg/ png/ gif</em></div>
					        <div><em>Note: Only images listed in the gallery are supported.</em></div>
					        <div><em>Note: File search must be full size.</em></div>
					    </div>
					</form>
					<div class="data_test"></div>
					<script type="text/javascript">
						jQuery(function($) {
					    $('body').on('change', '#replace_image', function() {
					        $this = $(this);
					        file_obj = $this.prop('files');
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
					            	var html_run = '<div style="padding: 10px; background-color: white;">';
					            	html_run +="<table class='replace_image_table'>";
					            	html_run +="<tr>";
				            			html_run +="<th>Image preview</th>";
				            			html_run +="<th>Name</th>";
				            			html_run +="<th>Image type</th>";
				            			html_run +="<th>Suport type</th>";
				            			html_run +="<th>Replace status</th>";
				            		html_run +="</tr>";
					            	for (var i = 0; i < response.data.length; i++) {
					            		html_run +="<tr>";
					            			html_run +="<td>"+response.data[i].replaced_url+"</td>";
					            			html_run +="<td>"+response.data[i].name+"</td>";
					            			html_run +="<td>"+response.data[i].type+"</td>";
					            			html_run +="<td>"+response.data[i].type_support+"</td>";
					            			html_run +="<td>"+response.data[i].replaced+"</td>";
					            		html_run +="</tr>";
					            	}
					            	html_run +="</table>";
					            	html_run +='</div>';
					            	html_run +='<style type="text/css">.replace_image_table img{max-width: 120px; height: auto;border: 5px solid lightgray;}</style>';
	                                $('.data_test').html(html_run);
					            }
					        });
					    });
					});
					</script>			
        		</td>
        	</tr>      	     	
        </table>        
		<?php
		return ob_get_clean();
	}
}