<?php
/*
Plugin Name:WFIU Playlist
Plugin URI: http://www.wfiu.org
Description: Adds a playlist input to a post and posts it (well, not yet).
Version: 1.1
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/
define('WPINC', 'wp-includes');

require_once(ABSPATH. WPINC . '/post.php');
//require_once(ABSPATH.PLUGINDIR.'/wfiu_playlist/wfiu_playlist_functions.php');


if(!class_exists ('wfiuPlaylist_class')) {
	require_once(ABSPATH.PLUGINDIR.'/wfiu_playlist/wfiu_playlist_class.php');
	$wfiuPlaylist = new wfiuPlaylist_class;
}

if(!function_exists('wfiu_maybe_create_table')) {
	//copied from podpress
	function wfiu_maybe_create_table($table_name, $create_ddl) {
		GLOBAL $wpdb;
		foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
			if ($table == $table_name) {
				return true;
			}
		}
		//didn't find it try to create it.
		$q = $wpdb->query($create_ddl);
		// we cannot directly tell that whether this succeeded!
		foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
			if ($table == $table_name) {
				return true;
			}
		}
		return false;
	}
}

function activate_wfiu_playlist() {
	global $wpdb,$wfiuPlaylist;
	$table = $wpdb->prefix."wfiu_playlist";
	$query = "CREATE TABLE $table (
	playlist_item_id INT(9) NOT NULL AUTO_INCREMENT,
	post_id INT(9) NOT NULL DEFAULT 0,
	title VARCHAR(255) NOT NULL,
	composer VARCHAR(255),
	artist VARCHAR(255) NOT NULL,
	album VARCHAR(255),
	label VARCHAR(255),
	release_year INT(4) DEFAULT 0,
	asin VARCHAR(20),
	notes VARCHAR(1023),
	PRIMARY KEY (playlist_item_id),
	KEY post_id (post_id),
	INDEX(post_id)
	);";

	wfiu_maybe_create_table($table,$query);

	if(!class_exists ('XmlTransformClass')) {
		require_once(dirname(__FILE__).'/xml_transform_class.php');
	}
	$transformClass = new XmlTransformClass($wfiuPlaylist->plugin_url());
	$transformClass->activate();
}


function page_form_wfiu_playlist() {
	global $wfiuPlaylist;
	return $wfiuPlaylist->post_form('page');
}

$wp_importers['wfiuPlaylist'] = array ('WFIU Playlists', 'WFIU playlist import of playlists from an XML file (defined by Pablo).', array(&$wfiuPlaylist, 'import_playlists'));
//*********************ADMIN STUFF*********************************
//activate plugin
add_action('activate_wfiu_playlist/wfiu_playlist.php', 'activate_wfiu_playlist');

//admin javacript, css, etc..
add_action('admin_print_scripts', array(&$wfiuPlaylist,'admin_head'));


//Admin form functions
add_action('admin_menu', array(&$wfiuPlaylist, 'post_box'), 1);//add slideshow box

/*add_action('simple_edit_form', array(&$wfiuPlaylist, 'post_form'));
add_action('edit_form_advanced', array(&$wfiuPlaylist, 'post_form'));
add_action('edit_page_form', 'page_form_wfiu_playlist');*/
//add_action('admin_head',array(&$wfiuPlaylist, 'import_dispatch') );

//save and delete post hook
add_action('save_post', array(&$wfiuPlaylist,'save_postdata'), 1, 2);
add_action('delete_post', array(&$wfiuPlaylist,'delete_playlist'));

//*********************ADMIN STUFF END*********************************


//*********************FRONTEND STUFF*********************************
	//add_action('the_content', array(&$wfiuPlaylist, 'insert_content')); //used a tag isntead
function the_playlist($xsl_file, $suppress = true){
	global $wfiuPlaylist;
	$wfiuPlaylist->get_wfiu_playlist($xsl_file, $suppress);
}

add_action('the_playlist', array(&$wfiuPlaylist,'get_wfiu_playlist') );
add_action('the_content', array(&$wfiuPlaylist, 'replace_tags')); 

//**************************AJAX STUFF(needs work)*****************************
add_action('admin_print_scripts', 'myplugin_js_admin_header' );
add_action('wp_ajax_add_one_playlist', array(&$wfiuPlaylist,'importOnePlaylist') );
function myplugin_js_admin_header() // this is a PHP function
{
  // use JavaScript SACK library for Ajax
  wp_print_scripts( array( 'sack' ));

  // Define custom JavaScript function
?>
	<script type="text/javascript">
	//<![CDATA[

	function myplugin_ajax_elevation( post_id, playlist_url)
	{
	   var mysack = new sack( 
	       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

	  mysack.execute = 1;
	  mysack.method = 'POST';
	  mysack.setVar( "action", "add_one_playlist" );
	  mysack.setVar( "post_id", post_id );
	  mysack.setVar( "playlist_url", playlist_url );
	  mysack.encVar( "cookie", document.cookie, false );
	  mysack.onError = function() { alert('Ajax error in importing playlist.' )};
	  //mysack.onCompletion = whenImportCompleted;
	  mysack.runAJAX();

	  return true;


	}
	

	 // end of JavaScript function myplugin_ajax_elevation
	//]]>
	</script>
<?php



} // end of PHP function myplugin_js_admin_header
//**************************END AJAX STUFF*****************************



?>
