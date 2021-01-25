<?php 
namespace Adminz\Admin;

class ADMINZ_ThemeOptions extends Adminz{
	public $setting_tab;

	function __construct() {
		add_action('admin_menu', [$this,'adminz_add_menu_page']);
	}
	function init(){
		
	}
	function adminz_add_menu_page() {
	    add_submenu_page (
	        $this->options_pageslug,
	        apply_filters( 'adminz_menu_title', $this->name),
	        apply_filters( 'adminz_menu_title', $this->name),
	        'manage_options',
	        $this->slug,
	        [$this,'setting_pages' ]
	    );
	}
	function get_settings_tab(){
		return apply_filters( 'adminz_setting_tab', $this->setting_tab );
	}
	function setting_pages() {
		$tabs = $this->get_settings_tab();
		?>
		<style type="text/css">
			.adminz_nav_tab .nav-tab {
				display: flex;
			}
			.adminz_nav_tab svg{
			    width: 17px;
			    color: gray;
			    margin-right: 5px;
			}
			.adminz_tab_content a::after{
				content: url(<?php echo plugin_dir_url(ADMINZ_BASENAME).'assets/images/external-link-alt.svg'; ?>);
			    width: 12px;
			    display: inline-block;
			    opacity: 0.2;
				fill: currentColor;
				padding-left: 5px;
				padding-right: 5px;
			}
		</style>
		<h1><?php echo apply_filters( 'adminz_menu_title', $this->name); ?> settings</h1>
		<nav class="adminz_nav_tab nav-tab-wrapper"><?php		
		foreach ($tabs as $key=> $tab) {
			$href = "#";
			echo wp_sprintf( 
				'<a href="%1$s" class="nav-tab %2$s">%3$s</a>', 
				get_admin_url().$this->options_pageslug.'?page='.$this->slug."&tab=".$tab['slug'],
				((isset($_GET['tab']) and $_GET['tab'] == $tab['slug']) or (!isset($_GET['tab']) and $key ==0)) ? 'nav-tab-active' : "",
 				$tab['title']
 			);
		}
		?> </nav> <?php
		foreach ($tabs as $key=> $tab) {
			echo '<div class="adminz_tab_content wrap">';
			if(!isset($_GET['tab']) and $key ==0){
				echo $tabs[0]['html'];
			}
			elseif(isset($_GET['tab']) and ($tab['slug'] == $_GET['tab'])){
				
				echo $tab['html'];
				
			}
			echo '</div>';	
		}

	}
}