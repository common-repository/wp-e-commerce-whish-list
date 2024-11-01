<?php defined('ABSPATH') or die("No script kiddies please!");
/*
Plugin Name: WP eCommerce Wishlist
Plugin URI: http://androidbubble.com/blog/wp-e-commerce-wish-list
Description: This is Wishlist plugin for WP eCommerce Site. It has a widget which you can use with ultimate convenience.
Version: 1.1.6
Author: Fahad Mahmood 
Text Domain: wp-ecomm
Domain Path: /languages/
Author URI: http://www.androidbubbles.com

License: GPL2

This WordPress Plugin is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
This free software is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with this software. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	global $wpews_premium_link, $wpews_pro, $wpews_dir, $wpews_data;
	
	$wpews_dir = plugin_dir_path( __FILE__ );
	$wpews_pro = file_exists($wpews_dir.'pro/wpews_extended.php');
	$wpews_premium_link = 'http://shop.androidbubbles.com/product/wp-e-commerce-whish-list-pro';
	$wpews_data = get_plugin_data(__FILE__);
	


	
	
	
		
	if(is_admin()){

		$plugin = plugin_basename(__FILE__); 
		add_filter("plugin_action_links_$plugin", 'wpews_plugin_links' );	
		
		
	}else{
		
	
		
		
	}

	
	
	if($wpews_pro){					
		$wpews_dir.'pro/wpews_extended.php';//exit;
		include($wpews_dir.'pro/wpews_extended.php');
	}
	
	include('inc/functions.php');
	
	if(is_admin() && get_option('wpsc_compatibility')=='')
	update_option('wpsc_compatibility', wp_plugin_info('wp-e-commerce'));
			
	add_action('wpsc_product_form_fields_end', 'initialize_wpecwl');
	add_action('wp_footer', 'ajaxUrl');
	add_action( 'wp_enqueue_scripts', 'wpecwl_scripts' );	
	add_action( 'wp_ajax_add_wish_list', 'add_wish_list_callback' );
	add_action( 'wp_ajax_remove_wish_list', 'remove_wish_list_callback' );
	add_action( 'wp_ajax_load_wish_list', 'load_wish_list_callback' );
	add_action( 'widgets_init', create_function('', 'return register_widget("wpecwlWidget");') );