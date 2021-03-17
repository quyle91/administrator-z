<?php
/*
Plugin Name: Administrator Z
Description: Lots of tools for quick website setup.
Version: 1.6.2
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
	new Adminz\Admin\ADMINZ_Enqueue;
	new Adminz\Admin\ADMINZ_ContactGroup;
	new Adminz\Admin\ADMINZ_Flatsome;
	new Adminz\Admin\ADMINZ_Elementor;
	new Adminz\Admin\ADMINZ_Import;
	new Adminz\Admin\ADMINZ_OtherTools;	
	new Adminz\Admin\ADMINZ_Sercurity;
	new Adminz\Admin\ADMINZ_Me;
}
