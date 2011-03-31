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


if(!class_exists ('wordpress_slideshow')) {
	require_once(dirname(__FILE__).'/wordpress_slideshow_classes.php');
	$wp_slideshow = new wordpress_slideshow();
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

//*********************ADMIN STUFF*********************************
//activate plugin
add_action('activate_ipm-wordpress-slideshow/wordpress_slideshow.php', array(&$wp_slideshow, 'activate'));
add_action('media_upload_slideshow_image', array(&$slideshow_plugin,'media_upload'));
add_action('media_upload_replace_wp_image', array(&$wp_slideshow,'new_image_chooser'));
add_action('media_upload_map', array(&$wp_slideshow,'show_map'));

//admin javacript, css, etc..
add_action( "admin_print_scripts", array(&$slideshow_plugin, 'admin_print_scripts') );
add_action( "admin_head", array(&$slideshow_plugin, 'admin_head_scripts') );
add_action('admin_menu', array(&$slideshow_plugin, 'show_editor_box'), 1);//add slideshow box

//save and delete post hook
add_action('save_post', array(&$wp_slideshow,'save_slideshow'), 1, 2);
add_action('delete_post', array(&$wp_slideshow,'delete_photos'));

add_filter('media_meta', array(&$wp_slideshow,'mediaItem'),  109, 2);//called at wp-admin/includes/media.php
add_filter('attachment_fields_to_save', array(&$wp_slideshow,'save_photo_fixed_props'), 109, 2);//called at wp-admin/includes/media.php

/*
add_filter('manage_posts_columns', array(&$wp_slideshow,'add_column'));
add_filter('manage_posts_custom_column', array(&$wp_slideshow,'do_column'), 10, 2);
/*
 * 
 */
$myutils = new IPM_Utils();
function slideshow_manager_menu(){
	global $wp_slideshow, $myutils;

	if(current_user_can('edit_plugins')){
		$myutils->add_page_main();
		add_submenu_page($myutils->main_page_path(), 'Slideshow Manager', 'Slideshow Manager', 7, $wp_slideshow->plugin_path.basename(__FILE__), array(&$wp_slideshow, 'manage_slideshows'));
		$myutils->add_page_google_api_key();
	}
}
add_action('admin_menu', 'slideshow_manager_menu');
//$wp_roles = new WP_Roles();
//$wp_roles->add_cap('contributor', 'upload_files', true);
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
	//$slideshows->get_slideshows();
	$post_image = $post_slideshows->get_single_photo();
	
	if(empty($post_image->medium_url)){
		$post_slideshows->get_slideshows();
		if(!empty($post_slideshows->slideshows) )
		{
			$slideshow = $post_slideshows->slideshows[0];
			
			if(!empty($slideshow->photos) )
			{
				$post_image = $slideshow->photos[0];
			}
			else
			{
				$post_image = false;
			}
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

//****************************************************************

//slideshow menu stuff... haven't touched it yet
function wp_slideshow_menu(){
	global $wp_slideshow, $wpdb, $post;

?>
<script type='text/javascript'>
//currentSlideshowID = 'wpss_slideshow_photos_ul_3228';
</script>
<?php
	$post_id = 5639;
	$wp_slideshow->save_slideshow($post_id,$post);
	echo "<form action='?page=" . $_GET['page'] . "' method='post'>";

	$post->ID =$post_id;
	echo "<input type='hidden' id='post_ID' value ='".$post->ID."'/>";
	$wp_slideshow->post_form();


		$query = "SELECT ID, post_title, post_excerpt, post_content FROM ".$wpdb->prefix."posts WHERE post_mime_type LIKE '%image%';";
		$posts = $wpdb->get_results($query);

		echo $myutils->table_headers('&nbsp;','Title', 'Photo Credit','&nbsp;');
		foreach($posts as $img_post){
			$id = $img_post->ID;
			$titles = array($img_post->post_title);
			$wp_slideshow->getExtraTitles($id, $titles);

			$alt = strip_tags($img_post->post_excerpt);
			$caption = $img_post->post_content;
			$thumb = wp_get_attachment_image_src( $id, 'thumbnail');
			$thumb = $thumb[0];

			$photo_props = $wp_slideshow->getPhotoFixedProps($id);
			$photo_credit = $photo_props['photo_credit'];

			$items['title'] = $titles[0];
			$items['alt'] = $alt;
			$items['caption'] = $caption;
			$items['url'] = $thumb;

			echo "<span class='button' onclick=\"send_to_slideshow('234','".$wp_slideshow->photoItemHTML_simple($id, $items)."');\">addphoto</span>";
		}



	echo "<input type='submit' />";
	echo "</form>";
}





?>
