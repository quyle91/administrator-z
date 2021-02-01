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
		add_action('wp_ajax_test_images', [$this, 'test_images']);
	}	
	function test_images($files = false){	
		if(!$files){ $files = $_POST['files']; }		
        wp_send_json_success("123");
        wp_die();
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
		<table class="form-table">
        	<tr valign="top">
        		<th><h3>Replace Image </h3></th>
        		<td>
        			
        		</td>
        	</tr>	  
        	<tr valign="top">
        		<th>Input your image</th>
        		<td>
        			<form method="post" action="options.php" id="replace_images">
					  <label for="adminz_images">Select files:</label>
					  <input type="file" id="adminz_images" class="adminz_images" name="adminz_images" multiple>
					  <button type="button" class="button test_images">Test images</button>
					  <div class="data_test"></div>
					</form>        					
        		</td>
        	</tr>      	     	
        </table>
        <script type="text/javascript">
        	(function($){
				jQuery(document).on("click", ".test_images", function() {				  
				  var output = $(this).closest('form').find('.data_test');
				  var FormData =  new FormData($(this).closest("#replace_images"));
				  console.log(FormData);
				  //test_images($(this),output);
				  return false;
				});
				function test_images(button,output){

					if(!button.length) {
						alert("Choose images")
						return;
					}else{
						
					}
					console.log(files);
					$.ajax({
                        type : "post",
                        dataType : "json",
                        url : '<?php echo admin_url('admin-ajax.php'); ?>',
                        data : formdata,
                        context: this,
                        beforeSend: function(){
                            var html_run = '<div class="notice notice-alt notice-warning updating-message"><p aria-label="Checking...">Checking...</p></div>';
                            output.html(html_run);
                        },
                        success: function(response) {                                       
                            
                            if(response.success) {                                          
                                var data_test = JSON.parse(response.data);    
                                console.log(data_test);        
                            }
                            else {
                                alert('There is an error');
                            }
                        },
                        error: function( jqXHR, textStatus, errorThrown ){                            
                            console.log( 'Administrator Z: The following error occured: ' + textStatus, errorThrown );
                        }
                    })					
				}
			})(jQuery)
		</script>
		<?php
		return ob_get_clean();
	}
}