<?php
/*
Plugin Name: Administrator Z
Description: Some selected functions that are commonly used to support building websites quickly: some shortcodes integrated on UX Builder - Flatsome and Elementor, automatic updates, shutting down xmrpc.php, contact button groups, input support.
Version: 1.2
Author: Quyle91
Author URI: https://quyle91.github.io/
License: GPLv2 or later
Text Domain: adminz
*/
define('ADMINZ_DIR', plugin_dir_path( __FILE__ )); 
define('ADMINZ_BASENAME', plugin_basename(__FILE__));
use Adminz\Admin;

require_once( trailingslashit( ADMINZ_DIR ) . 'autoload/autoloader.php' );

add_action( 'plugins_loaded', 'adminz_namespace' );
function adminz_namespace() {
	new Adminz\Admin\Adminz;
	new Adminz\Admin\ADMINZ_ThemeOptions;
	new Adminz\Admin\ADMINZ_DefaultOptions;
	new Adminz\Admin\ADMINZ_Styles;
	new Adminz\Admin\ADMINZ_ContactGroup;
	new Adminz\Admin\ADMINZ_Flatsome;
	new Adminz\Admin\ADMINZ_Elementor;
	new Adminz\Admin\ADMINZ_Tools;
	new Adminz\Admin\ADMINZ_OtherOptions;	
	new Adminz\Admin\ADMINZ_Sercurity;
	new Adminz\Admin\ADMINZ_Me;
}
