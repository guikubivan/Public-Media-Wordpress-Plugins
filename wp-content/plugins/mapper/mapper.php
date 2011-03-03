<?php
/* 
Plugin Name: Mapper
Plugin URI: http://wfiu.org
Version: 1.0
Description: Lets you manage stations where shows are broadcasted. Uses Google's Map API
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/

//class map_locations
/*
	A class to hold data structures and methods to manipulate locations in a google map.
	It will have:
		-a google map (or rather methods to manipulate the map)
		-data structures to hold the location points

	Data structure for an ITEM:

	Item ADT:
		-Has a place in the world (business, town, etc.)

	Items seem to be of a certain type, such as:
		-Town in Indiana
		-Radio station
		
		
*/
require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');

if(!class_exists('map_item')) {
	require_once(ABSPATH.PLUGINDIR.'/mapper/map_item_classes.php');
//	$map_item_class =  new radioStation();
	$map_item_class  =  new postLocation();

	//echo $myMapOrganizer->mapType();
}
/*
add_filter( 'wp_page_menu', 'test_menu_args');

function test_menu_args($menu, $args){

return array();
}*/




function mapper_menu() {
	global $map_item_class;

	if(current_user_can('edit_plugins')){
		if(get_bloginfo('version')>= 2.7 ){
			wfiu_do_main_page();
			add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Mapper', 'Mapper', 7, 'Mapper', array(&$map_item_class, 'config_form'));
		}else{
			add_menu_page('Mapper', 'Mapper', 7, __FILE__ , array(&$map_item_class, 'config_form'));
		}
		wfiu_google_api_key();

	} 
}



//activate plugin
add_action('activate_mapper/mapper.php', array(&$map_item_class, 'activate'));

add_action( "admin_print_scripts", array(&$map_item_class,'plugin_head'));

//Edit form hooks
/*add_action('simple_edit_form', array(&$map_item_class, 'post_form'));
add_action('edit_form_advanced', array(&$map_item_class, 'post_form'));
add_action('edit_page_form', array(&$map_item_class, 'post_form'));*/
add_action('admin_menu', 'mapper_menu');
add_action('admin_menu', array(&$map_item_class, 'mapper_box'), 1);//add slideshow box

//save and delete post hook
add_action('save_post', array(&$map_item_class,'saveItem'));
add_action('delete_post', array(&$map_item_class,'deleteItem'));


add_filter('manage_posts_columns', array(&$map_item_class,'add_column'));
add_filter('manage_posts_custom_column', array(&$map_item_class,'do_column'), 10, 2);
?>
