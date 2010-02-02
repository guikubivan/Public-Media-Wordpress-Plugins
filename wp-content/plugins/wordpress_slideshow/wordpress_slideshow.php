<?php
/*
Plugin Name:Wordpress slideshow
Plugin URI: http://www.wfiu.org
Description: Let's you make a slideshow using wordpress' native way of storing photos.
Version: 1.0
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/
define('WPINC', 'wp-includes');

require_once(ABSPATH. WPINC . '/post.php');
require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');


if(!class_exists ('wordpress_slideshow')) {
	require_once(ABSPATH.PLUGINDIR.'/wordpress_slideshow/wordpress_slideshow_classes.php');
	$wp_slideshow = new wordpress_slideshow;
}


//*********************ADMIN STUFF*********************************
//activate plugin
add_action('activate_wordpress_slideshow/wordpress_slideshow.php', array(&$wp_slideshow, 'activate'));
add_action('media_upload_slideshow_image', array(&$wp_slideshow,'simple_image_chooser'));
add_action('media_upload_replace_wp_image', array(&$wp_slideshow,'new_image_chooser'));
add_action('media_upload_map', array(&$wp_slideshow,'show_map'));

//admin javacript, css, etc..

add_action( "admin_print_scripts", array(&$wp_slideshow, 'plugin_head') );
add_action('admin_menu', array(&$wp_slideshow, 'slideshowBox'), 1);//add slideshow box

//save and delete post hook
add_action('save_post', array(&$wp_slideshow,'save_slideshow'), 1, 2);
add_action('delete_post', array(&$wp_slideshow,'delete_photos'));

add_filter('media_meta', array(&$wp_slideshow,'mediaItem'),  109, 2);//called at wp-admin/includes/media.php
add_filter('attachment_fields_to_save', array(&$wp_slideshow,'save_photo_fixed_props'), 109, 2);//called at wp-admin/includes/media.php


add_filter('manage_posts_columns', array(&$wp_slideshow,'add_column'));
add_filter('manage_posts_custom_column', array(&$wp_slideshow,'do_column'), 10, 2);


function slideshow_manager_menu(){
	global $wp_slideshow;

	if(current_user_can('edit_plugins')){
		wfiu_do_main_page();
		add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Slideshow Manager', 'Slideshow Manager', 7, 'Slideshow Manager',  array($wp_slideshow, 'manage_slideshows'));
		wfiu_google_api_key();
	}
}
add_action('admin_menu', 'slideshow_manager_menu');
//$wp_roles = new WP_Roles();
//$wp_roles->add_cap('contributor', 'upload_files', true);
//*********************ADMIN STUFF END*********************************


//*********************FRONTEND STUFF*********************************
add_action('the_content', array(&$wp_slideshow, 'replace_tags')); 

function wpss_photos($stylesheet){
	global $wp_slideshow, $post;
	$wp_slideshow->show_photos($post->ID, $stylesheet);

}

//****************************************************************




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

		echo table_headers('&nbsp;','Title', 'Photo Credit','&nbsp;');
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
