<?php
namespace Adminz\Admin;

class Adminz {
	public $name= 'Administrator Z';
	public $options_pageslug = 'tools.php';
	public $slug = 'adminz';
	public $rand;
	function __construct(){
		if(get_option('adminz_menu_title')){
			add_filter( 
				'adminz_menu_title', 
				function (){
					return get_option('adminz_menu_title');
				}
			);
			add_filter( 
				'login_headertext', 
				function (){
					return get_option('adminz_menu_title');
				}
			);
			add_filter( 
				'adminz_slug', 
				function (){
					return sanitize_title(get_option('adminz_menu_title'));
				}
			);
		}
		
		if(get_option( 'adminz_logo_url ')) {
			add_filter( 
				'login_headerurl', 
				function (){
					return get_option( 'adminz_logo_url ');
				}
			);
		}
		
		if(get_option( 'adminz_login_logo')){
			add_action(
				'login_head', 
				function (){
					$image = wp_get_attachment_image_src( get_option( 'adminz_login_logo'),'full' );
					echo '<style type="text/css"> h1 a {background-image: url('.$image[0].') !important; background-size: contain !important;    width: 100%!important;}
						</style>';
				}
			);
		}
		add_action( 'admin_enqueue_scripts', [$this,'adiminz_enqueue_js'] );
		add_filter( 'plugin_action_links_' . ADMINZ_BASENAME, [$this,'add_action_links']);
		add_shortcode( 'adminz_test', [$this,'test'] );
	}
 
	function add_action_links ( $actions ) {
	   $mylinks = array(
	      '<a href="' . admin_url( $this->options_pageslug.'?page='.$this->slug ) . '">Settings</a>',
	   );
	   $actions = array_merge( $actions, $mylinks );
	   return $actions;
	}
	function get_adminz_slug(){
		return apply_filters( 'adminz_slug', $this->slug);
	}
	function get_adminz_menu_title(){
		return apply_filters( 'adminz_menu_title', $this->name);
	}	
	function adiminz_enqueue_js() {
		if ( ! did_action( 'wp_enqueue_media' ) ) {wp_enqueue_media(); }
	 	wp_register_script( 'adminz_media_upload', plugin_dir_url(ADMINZ_BASENAME).'assets/js/media-uploader.js', array( 'jquery' ) );
	 	wp_enqueue_script( 'adminz_media_upload');
	}
	function get_support_icons(){
		$files = array_map('basename',glob(ADMINZ_DIR.'/assets/images/*.svg'));
		return $files;
 	}
	function get_icon_html($url,$color=false){		
		$default = plugin_dir_url(ADMINZ_BASENAME).'assets/images/info-circle.svg';
		$file = $_SERVER['DOCUMENT_ROOT'] . parse_url($url, PHP_URL_PATH);
		if(!file_exists ($file)){
			$url = $default;
		}
		$svg_style = 'fill:currentColor;';
		if($color) $svg_style.= $color;
		$return = str_replace(
			[
				'#<script(.*?)>(.*?)</script>#is',
				'<svg',
				'<?xml version="1.0" encoding="utf-8"?>'
			], 
			[
				'',
				'<svg  style="'.$svg_style.'"',
				''
			], 
			@file_get_contents( $url )
		);
		$return = preg_replace('/<!--(.*)-->/', '', $return);
		return $return;
	}
	function test(){
		return 'test';
	}
}
// test commit fetch
// test commit mka
// test commit home pc