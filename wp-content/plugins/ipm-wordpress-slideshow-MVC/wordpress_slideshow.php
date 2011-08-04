<?php
/*
Plugin Name: IPM - Wordpress slideshow - MVC
Plugin URI: http://www.wfiu.org
Description: Let's you make a slideshow using wordpress' native way of storing photos.
Version: 1.0
Author: Pablo Vanwoerkom, Ben Serrette
Author URI: http://www.wfiu.org
*/
define('WPINC', 'wp-includes');
define('WPSSDIR', dirname(__FILE__));
define('WPSSCONTROLLERS', WPSSDIR."/controllers/");
define('WPSSVIEWS', WPSSDIR."/views/");
define('WPSSMODELS', WPSSDIR."/models/");

require_once(ABSPATH. WPINC . '/post.php');
if(file_exists(ABSPATH.PLUGINDIR.'/wfiu_utils/ipm-utils-class.php')){
	require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/ipm-utils-class.php');
}else{
	require_once(dirname(__FILE__) . '/ipm-utils-class.php');
}

if ( ! function_exists( 'is_plugin_active_for_network' ) )
   require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
   
if(!class_exists ('plugin_activation')) {
	require_once(dirname(__FILE__).'/controllers/plugin_activation.controller.php');
	$plugin_activation_object = new plugin_activation();
}

if(!class_exists ('IPM_Photo')) {
	require_once(dirname(__FILE__).'/models/photo.model.php');
}
if(!class_exists ('IPM_Slideshow')) {
	require_once(dirname(__FILE__).'/models/slideshow.model.php');
}
if(!class_exists ('IPM_SlideshowPhoto')) {
	require_once(dirname(__FILE__).'/models/slideshow_photo.model.php');
}
if(!class_exists ('IPM_PostSlideshows')) {
	require_once(dirname(__FILE__).'/models/post_slideshows.model.php');
}

if(!class_exists ('IPM_Ajax')) {
	require_once(dirname(__FILE__).'/controllers/ajax.controller.php');
}
if(!class_exists ('wpss_actions')) {
	require_once(dirname(__FILE__).'/controllers/main.controller.php');
	$slideshow_plugin = new wpss_main($wpdb);
}
add_action( 'wpmu_new_blog', 'new_blog', 10, 6); 		
 
function new_blog($blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	global $wpdb;
 
	if (is_plugin_active_for_network('ipm-wordpress-slideshow-MVC/wordpress_slideshow.php')) {
		$old_blog = $wpdb->blogid;
		switch_to_blog($blog_id);
		if(!class_exists ('plugin_activation')) {
			require_once(dirname(__FILE__).'/controllers/plugin_activation.controller.php');
		}
		$plugin_activation_object = new plugin_activation();
		$plugin_activation_object->activate();
		switch_to_blog($old_blog);
	}
}
//*********************ADMIN STUFF*********************************
//activate plugin
add_action('activate_ipm-wordpress-slideshow-MVC/wordpress_slideshow.php', array(&$plugin_activation_object, 'activate'));
add_action('media_upload_slideshow_image', array(&$slideshow_plugin,'media_upload'));

//admin javacript, css, etc..
add_action( "admin_print_scripts", array(&$slideshow_plugin, 'admin_print_scripts') );
add_action( "admin_head", array(&$slideshow_plugin, 'admin_head_scripts') );
add_action('admin_menu', array(&$slideshow_plugin, 'show_editor_box'), 1);//add slideshow box

//save and delete post hook
add_action('save_post', array(&$slideshow_plugin,'save_slideshow'), 1, 2);

add_filter('media_meta', array(&$plugin_activation_object,'mediaItem'),  109, 2);//called at wp-admin/includes/media.php
add_filter('attachment_fields_to_save', array(&$plugin_activation_object,'save_photo_fixed_props'), 109, 2);//called at wp-admin/includes/media.php

$myutils = new IPM_Utils();
function slideshow_manager_menu(){
	global $plugin_activation_object, $myutils;

	if(current_user_can('edit_plugins')){
		$myutils->add_page_main();
		add_submenu_page($myutils->main_page_path(), 'Slideshow Manager', 'Slideshow Manager', 7, $plugin_activation_object->plugin_path.basename(__FILE__), array(&$plugin_activation_object, 'manage_slideshows'));
		$myutils->add_page_google_api_key();
	}
}
add_action('admin_menu', 'slideshow_manager_menu');
//*********************ADMIN STUFF END*********************************


//*********************FRONTEND STUFF*********************************
add_action('the_content', array(&$slideshow_plugin, 'front_end')); 

//this is the function that is called by the templates that shows the photos.
function wpss_photos($stylesheet=''){
	global $slideshow_plugin;
	$slideshow_plugin->show_photos($slideshow_plugin->convert_stylesheet($stylesheet));
}

//used in templates to display the main image for a specific post
function wpss_post_image($stylesheet= '', $post_id='')
{
	global $slideshow_plugin;
	$slideshow_plugin->get_post();
	if(!$post_id)$post_id = $slideshow_plugin->post->ID;
	$stylesheet = $stylesheet ? $stylesheet : get_option($slideshow_plugin->option_default_style_post_image);
	$stylesheet = $slideshow_plugin->convert_stylesheet($stylesheet);
	$post_slideshows = new IPM_PostSlideshows($slideshow_plugin);
	$post_image = $post_slideshows->get_single_photo();
	
	if(empty($post_image->medium_url)){
		$post_slideshows->get_slideshows();
		if(!empty($post_slideshows->slideshows) )
		{
			$post_image = $post_slideshows->get_post_photo();
		}
		else
		{
			$post_image = false;
		}
	}
	if($post_image){
		echo $slideshow_plugin->render_frontend_view($stylesheet, array("type"=>"photo", "photo"=>$post_image) );
		return true;
	}
	return false;	
	
}

?>
