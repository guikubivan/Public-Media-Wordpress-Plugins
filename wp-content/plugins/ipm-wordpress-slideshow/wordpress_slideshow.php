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
if(file_exists(ABSPATH.PLUGINDIR.'/wfiu_utils/ipm-utils-class.php')){
	require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/ipm-utils-class.php');
}else{
	require_once(dirname(__FILE__) . '/ipm-utils-class.php');
}



if(!class_exists ('wordpress_slideshow')) {
	require_once(dirname(__FILE__).'/wordpress_slideshow_classes.php');
	$wp_slideshow = new wordpress_slideshow;
}


//*********************ADMIN STUFF*********************************
//activate plugin
add_action('activate_ipm-wordpress-slideshow/wordpress_slideshow.php', array(&$wp_slideshow, 'activate'));
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

$myutils = new IPM_Utils();
function slideshow_manager_menu(){
	global $wp_slideshow, $myutils;

	if(current_user_can('edit_plugins')){
		$myutils->add_page_main();
		add_submenu_page($myutils->main_page_path(), 'Slideshow Manager', 'Slideshow Manager', 7, 'Slideshow Manager',  array(&$wp_slideshow, 'manage_slideshows'));
		$myutils->add_page_google_api_key();
	}
}
add_action('admin_menu', 'slideshow_manager_menu');
//$wp_roles = new WP_Roles();
//$wp_roles->add_cap('contributor', 'upload_files', true);
//*********************ADMIN STUFF END*********************************


//*********************FRONTEND STUFF*********************************
add_action('the_content', array(&$wp_slideshow, 'replace_tags')); 

function wpss_photos($stylesheet=''){
	global $wp_slideshow, $post;
	if(!$wp_slideshow->post_has_tags($post->post_content)){
		$wp_slideshow->show_photos($post->ID, $stylesheet);
	}
}

function wpss_post_image($stylesheet= ''){
	global $wp_slideshow, $post;
	$stylesheet = $stylesheet ? $stylesheet : get_option($wp_slideshow->option_default_style_post_image);

	$post_image_id = get_post_meta($post->ID, $wp_slideshow->postmeta_post_image, true);
	if(!$post_image_id){
		//the image if it's single photo post
		$post_image_id=get_post_meta($post->ID,$wp_slideshow->plugin_prefix.'photo_id', true);
		if(!$post_image_id){
			if($sid=get_post_meta($post->ID,$wp_slideshow->fieldname, true)){
				$sProps = $wp_slideshow->getSlideshowProps($sid);
				$post_image_id = $sProps['thumb_id'];
				if(!$post_image_id){
					$photos = $wp_slideshow->getPhotos($sid);
					$post_image_id = key($photos);
				}
			}
		}
	}
	if($post_image_id){
		echo $wp_slideshow->get_photo_clip($post_image_id,$stylesheet);
		return true;
	}
	return false;
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
