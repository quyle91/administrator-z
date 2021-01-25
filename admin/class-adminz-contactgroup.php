<?php 
namespace Adminz\Admin;
use Adminz\Admin\Adminz as Adminz;

class ADMINZ_ContactGroup extends Adminz {
	public $options_group = "adminz_contactgroup";
	public $title = "Contact Group";
	public $slug = "contactgroup";	
	public $locations = [];
	function __construct()
	{
		add_filter( 'adminz_setting_tab', [$this,'register_tab']);
		add_action(	'admin_init', [$this,'register_option_setting'] );
		add_action( 'init', array( $this, 'init' ) );
 	}
 	function init(){
 		$menuids = get_option( 'contactgroup_style',array() );
 		$styles = $this->get_styles();
 		if(!empty($menuids)){
 			foreach ($menuids as $key=>$menuid) {
 				
 				//check menu assigned
 				if($menuid){
 					$style = $styles[$key+1];
 					$name = sanitize_title($this->slug.$style['name']);
 					$css = $style['css'];
 					$js = $style['js'];
 					add_action('wp_enqueue_scripts', function() use ($css,$js,$name ) {

 						// css
 						if(is_array($css) and !empty($css)){
 							foreach ($css as $icss => $cssurl) {
 								wp_enqueue_style( $name.$icss."-css", $cssurl );
 							}
 						}

 						// js
 						if(is_array($js) and !empty($js)){
							foreach ($js as $ijs => $jsurl) {
								// check wp library script
								if($jsurl == wp_http_validate_url($jsurl)){									
									wp_enqueue_script($name.$ijs."-js", $jsurl, array('jquery'),null, true);
								}else{
									wp_enqueue_script($jsurl);
								}
	 						}
 						}
 					});

 					// call template
 					add_action('wp_footer', function() use ($menuid,$style) { 					
 						echo call_user_func([$this,$style['callback']],$menuid);
 					});
 					
 				}
 			}
 		}
 	}
	function callback_style1($menuid){
		if(is_admin() and is_blog_admin()) die;
		$adminz = new Adminz;
		$items = wp_get_nav_menu_items($menuid);		
		ob_start();
		echo '<div class="contactgroup_style1">';
		if(!empty($items)){
			foreach ($items as $item) {				
				$style = $item->xfn? ' background-color: #'.$item->xfn.';' : "";
		    	$icon = $this->get_icon($item->post_excerpt);
		    	$item->classes[] = 'item';
	    		$item->classes[] = $item->post_excerpt;
				echo '<a 
				href="'.$item->url.'"
				class="'.$this->get_item_class($item).'" 
				target="'.$item->target.'"		        
		        style="color: white;',$style,'"
		        >
		        '.$icon.'	
		        </a>';
			}
		}
		echo '</div>';
		$return = ob_get_clean();
		return $return;
	}
	function callback_style2($menuid){		
		if(is_admin() and is_blog_admin()) die;
		$adminz = new Adminz;
		$items = wp_get_nav_menu_items($menuid);
		ob_start();
		if(!empty($items)){
			?>
			<div class="contact-group contactgroup_style2">
				<?php 
				$distinct = array();
				$itemcount1 = array(
					'href' => '#open',
					'target' => "",
					'title'=> get_option('contactgroup_title','Liên hệ nhanh')
				);		
				if(count($items) ==1){
					$itemcount1['href'] = $items[0]->url;
					$itemcount1['target'] = $items[0]->target;
					$itemcount1['title'] = $items[0]->title;
				}

				foreach ($items as $key => $item) {
					$distinct[] = $item->post_excerpt;
				}			
				$distinct = array_unique($distinct);
			 	?>
			    <div class="button-contact icon-loop-<?php echo count($distinct)?> item-count-<?php echo count($items); ?>">
			        <a href="<?php echo $itemcount1['href']; ?>" target="<?php echo $itemcount1['target']; ?>" class="icon-box icon-open">
			        	<span>
				            <?php 
				            foreach ($distinct as $item) {				            	
				            	$icon = $this->get_icon($item);
			            			echo '<span class="icon-box">
			            			'.$icon.'
			            			</span>';
				            }
				            ?>
			        	</span>
			    	</a>
			        <a href="#close" class="icon-box icon-close">
			        	<?php 
			        	echo  $this->get_icon('close');
			        	 ?>	            
			        </a>
			        <span class="button-over icon-box"></span>
			        <div class="text-box text-contact"><?php echo $itemcount1['title']; ?></div>
			    </div>
			    <?php if(count($items)>1){ ?>
			    <ul class="button-list">
			        <?php
			        foreach ($items as $key=> $item) {
			        	$style = $item->xfn? ' background-color: #'.$item->xfn.';' : "";			        	
			        	$icon = $this->get_icon($item->post_excerpt);
			        	echo '<li class="',$item->post_excerpt,' button-', $key,' ',implode(' ', $item->classes),'">
			                <a href="', $item->url,'" target="',$item->target,'">
			                	<span 
			                	class="icon-box icon-', $key,'" 
			                	style="color: white;',$style,'"
			                	>
			                		'.$icon.'
			            		</span>';
			            		if ($this->get_item_title($item->title)){
						        	echo '<span class="text-box text-', $key,'">'.$this->get_item_title($item->title).'</span>';
						        }
			                echo '</a>
			                </li>';
			        }
			        ?>
			    </ul>
				<?php }; ?>
			</div>
			<?php
		}
		$return = ob_get_clean();
		return $return;
	}
	function callback_style3($menuid){
		// get only first
		if(is_admin() and is_blog_admin()) die;
		$adminz = new Adminz;
		$items = wp_get_nav_menu_items($menuid);
		$item = $items[0];
		$color = $item->xfn? $item->xfn : '00aff2';
		$style = ' background-color: #'.$color.'; border-color: #'.$color.';';
		ob_start();
		if(!empty($items)){
			?>			
			<div class="quick-alo-phone">
				<?php if($this->get_item_title($item->title)){ ?>
					<div class="phone"><a href="<?php echo $item->url; ?>" class="number-phone"><?php echo $this->get_item_title($item->title); ?></a></div>
				<?php }else{
					?>
					<div style="margin-bottom: 50px;"></div>
					<?php
				} ?>
			  	<a 
			  	href="<?php echo $item->url; ?>"
			  	class="<?php echo implode(' ', $item->classes); ?>" 
				target="<?php echo $item->target; ?>"		        
		        style="color: white; <?php echo $style; ?>"
			  	>
				  	<div class="quick-alo-ph-circle"></div>
				  	<div class="quick-alo-ph-circle-fill"></div>
				  	<div class="quick-alo-ph-img-circle">
				  		<?php 
				  		echo $this->get_icon($item->attr_title);
				  		?>
				  	</div>
				</a>
			</div>			
			<?php
		}
		return ob_get_clean();
	}
	function callback_style4($menuid){
		if(is_admin() and is_blog_admin()) die;
		$adminz= new Adminz;
		$items = wp_get_nav_menu_items($menuid);
		ob_start();
		if(!empty($items)){
		?>
		<div class="admz_ctg4">
			<div class="inner">
			<?php 
				foreach ($items as $item) {
					$style = "color: white; ";
					$style .= $item->xfn? 'background-color: #'.$item->xfn.';' : "";
		    		$icon = $this->get_icon($item->post_excerpt);
		    		echo '<a 
		    		href="'.$item->url.'"
					class="'.$this->get_item_class($item).'" 
					target="'.$item->target.'"
			        style="',$style,'"
			        > '.$icon;
			        if ($this->get_item_title($item->title)){
			        	echo '<span>'.$this->get_item_title($item->title).'</span>';
			        }
			        echo '</a>';
				}
			?>
			</div>
		</div>
		<?php
		}
		return ob_get_clean();
	}	
	function get_item_class($item){
		$return = $item->classes;
		$return[] = 'item';
		$return[] = $item->post_excerpt;		
		return implode(' ', array_filter($return));
	}
	function get_item_title($title){
		if($title == "0") return ; 
		return $title;
	}
 	function get_styles(){
 		$styles = array();

 		$styles[1]= array(			
			'name'=>'Left',
			'css'=> [plugin_dir_url(ADMINZ_BASENAME).'assets/css/style2.css'],
			'js'=> [plugin_dir_url(ADMINZ_BASENAME).'assets/js/style2.js'],
			'callback' => 'callback_style2'
		);
 		$styles[2]= array(			
			'name'=>'Right',
			'css'=> [plugin_dir_url(ADMINZ_BASENAME).'assets/css/style1.css'],
			'js'=> [],
			'callback' => 'callback_style1'
		);		
		$styles[3]= array(			
			'name'=>'Left single',
			'css'=> [plugin_dir_url(ADMINZ_BASENAME).'assets/css/style3.css'],
			'js'=> [/*'jquery-ui-core',*/ ],
			'callback' => 'callback_style3'
		);
		$styles[4]= array(			
			'name'=>'Left Expand',
			'css'=> [plugin_dir_url(ADMINZ_BASENAME).'assets/css/style4.css'],
			'js'=> [],
			'callback' => 'callback_style4'
		);
 		return apply_filters( 'adminz_contactgroup_styles', $styles);
 	}
 	function get_style_data($style_value){
 		$styles = $this->get_styles();
 		if(!empty($styles)){
 			foreach ($styles as $key => $style) {
	 			if(($style['value']) == $style_value){
	 				return $style;
				}
	 		}
 		} 	
 		return;	
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
	    register_setting( $this->options_group, 'contactgroup_style' );
	    register_setting( $this->options_group, 'contactgroup_title' );
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
	        		<th><h3>Assign menu</h3></th>
	        		<td></td>
	        	</tr>
	        	<?php
	    			$optionstyle = get_option( 'contactgroup_style',array() );
	    			$styles = $this->get_styles();
	    			
	    			foreach ($styles as $key => $value) {	    				
	    				?>
	    				<tr valign="top">
        					<th scope="row"><?php echo $value['name']; ?></th>
        					<td>
	        						<select name="contactgroup_style[]">
        							<option value="">- Not assigned -</option>
        							<?php 
        							$menus = wp_get_nav_menus();
        							if (!empty($menus)){
    									foreach ($menus as $key2 => $menu) {
    										$selected = "";
    										if(isset($optionstyle[$key-1]) and $optionstyle[$key-1] == $menu->term_id){
    											$selected = "selected";
    										}	    										
    										echo '<option ',$selected,' value="'.$menu->term_id.'">',$menu->name,'</option>';
    									}
        							}
        							?>        							
        						</select>
        					</td>
	    				<?php
	    			}
	        	?>
	        	<tr valign="top">
	        		<th>Contact Group title</th>
	        		<td>
	        			<input type="text" name="contactgroup_title" value="<?php echo get_option('contactgroup_title','Quick contact'); ?>">
	        		</td>
	        	</tr>
 			</table>
 			<div class="notice">
 				<h4>How to use</h4> 
 				<p>Choose icon: Type name icon into <code>Menu item</code> -> <code>Title attribute</code></p>
 				<p>Background: Type color code into <code>Menu item</code> -> <code>XFN</code></p>
 				<p>Remove contact title: Type "0" into Navigation Label</p>
 			</div> 			
	        <?php submit_button(); ?>
	    </form>
	    <h3>Support icons</h3>
	    <div>			
			<div style="display: flex; flex-wrap: wrap; justify-content: flex-start;">
			<?php 
			foreach ($this->get_support_icons() as $key=> $icon) {
				?>
				<div class="contactgroupicon" 
			style="width: calc( 10% - 2px)  ; margin: 1px; border-radius: 5px;  box-sizing: border-box; border: 1px solid #ccc; cursor: pointer; ">
					<div style="margin: 5px; display: flex; align-items: center;">
						<img alt="<?php echo $icon; ?>" width="25px" src="<?php echo plugin_dir_url(ADMINZ_BASENAME). 'assets/images/'.$icon ; ?>" style="margin-right: 10px;"/> 
						<small><?php echo substr($icon, 0,strlen ($icon)-4); ?></small> 
						<small class="tooltip">Copy to clipboard</small>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<style type="text/css">
			<?php 
				global $_wp_admin_css_colors;
				$admin_color = get_user_option( 'admin_color' );
				$colors      = $_wp_admin_css_colors[$admin_color]->colors;
			?>
			@media (max-width: 768px){
				.contactgroupicon {
					width: calc( 33% - 2px ) !important;							
				}
			}
			.contactgroupicon{
				
				position: relative;
			}
			.contactgroupicon .tooltip::before{
				content: "";
			    width: 8px;
			    height: 8px;
			    background: inherit;
			    display: inline-block;
			    bottom: -3px;
			    left: 50%;
			    position: absolute;
			    transform: translateX(-50%) rotate(45deg);
			}
			.contactgroupicon .tooltip{
				position: absolute;
				bottom: calc( 100% + 5px ) ;
				left: 0;
				background-color: <?php echo $colors[1]; ?> ;
				padding: 5px;
				color: white;
				border-radius: 5px;
				display: none;
			}
			.contactgroupicon.copied .tooltip,
			.contactgroupicon:hover .tooltip{
				display: block;
			}
			.contactgroupicon.copied,
			.contactgroupicon:hover{						
					fill: white;
					color: white;
					background-color: <?php echo $colors[2];?>;
			}

		</style>
		<script type="text/javascript">
			jQuery( document ).ready(function() {
			    jQuery(document).on('click', '.contactgroupicon', function(e){		
					e.preventDefault();
					var icon_text = jQuery(this).find('small').html();
					var textArea = jQuery('<textarea>'+icon_text+'</textarea>');
				  	jQuery(this).append(textArea);
				  	textArea.focus();
				  	textArea.select();
					try {
				    	var successful = document.execCommand('copy');
					    var msg = successful ? 'successful' : 'unsuccessful';
					    console.log('Copying text command was ' + msg);
					    jQuery('.contactgroupicon').removeClass('copied');
						jQuery(this).addClass('copied');
						jQuery('.contactgroupicon').find('.tooltip').html('Copy to clipboard');
						jQuery(this).find('.tooltip').html('Copied: '+icon_text);
				  	} catch (err) {
					    alert('Oops, unable to copy');
				  	}
				  	jQuery(this).find('textarea').remove();
				})
			});
		</script>
		</div>
		<?php
		return ob_get_clean();
	}
	function get_icon($name){
		$adminz = new Adminz;
		return $adminz->get_icon_html(plugin_dir_url(ADMINZ_BASENAME)."assets/images/".$name.".svg");
	}
}