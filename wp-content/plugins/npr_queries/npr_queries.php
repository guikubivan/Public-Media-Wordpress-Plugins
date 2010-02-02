<?php
/*
Plugin Name:NPR Queries
Plugin URI: http://www.wfiu.org
Description: Outputs formatted output from NPR's API
Version: 1.0
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/
define('WPINC', 'wp-includes');

require_once(ABSPATH. WPINC . '/post.php');
//require_once(ABSPATH.PLUGINDIR.'/npr_related_stories/nprapi_functions.php');

/*
if(!class_exists ('wfiuPlaylist_class')) {
	require_once(ABSPATH.PLUGINDIR.'/wfiu_playlist/wfiu_playlist_class.php');
	$wfiuPlaylist = new wfiuPlaylist_class;
}*/

function activate_npr_queries() {
    global $wpdb;
    $donothing='sdklfsajdf';
}



//*********************ADMIN STUFF*********************************
//activate plugin
//add_action('activate_wfiu_playlist/wfiu_playlist.php', 'activate');

//admin javacript, css, etc..
//add_action('admin_head', array(&$wfiuPlaylist,'admin_head'));


//Admin form functions
//add_action('simple_edit_form', array(&$wfiuPlaylist, 'post_form'));
//add_action('edit_form_advanced', array(&$wfiuPlaylist, 'post_form'));
//add_action('edit_page_form', 'page_form');
//add_action('admin_head',array(&$wfiuPlaylist, 'import_dispatch') );

//save and delete post hook
//add_action('save_post', array(&$wfiuPlaylist,'save_postdata'));
//add_action('delete_post', array(&$wfiuPlaylist,'delete_playlist'));

//*********************BRAINS OF OPERATION*********************************
function plugin_url(){
	$result = 'http';
	$result .= '://'.$_SERVER['HTTP_HOST'].'/wp-content/plugins/npr_queries/';
	return $result;
}




function getNPRStories($NPRML_file, $xslfile){

	$xml = new DOMDocument;
	@$xml->load($NPRML_file);

	$xsl = new DOMDocument;
	@$xsl->load($xslfile);

	// Configure the transformer
	$proc = new XSLTProcessor;
	@$proc->importStyleSheet($xsl); // attach the xsl rules
	$outText = @$proc->transformToXML($xml);
	$outText = trim($outText);
	//echo "(".$outText.")";
	if($outText){
		if(!preg_match("/There were no results that matched your query criteria/i",$outText) && !preg_match("/Requested List/i",$outText)){
			return $outText;
		}
	}

	return false;
}


function process_NPR_query($files){
//function get_npr_related_stories($xslfile){

	$cacheDir = getcwd() . "/wp-content/plugins/npr_queries/cached/";
	//$xslfile = 'npr_related_stories.xsl';
	//$XMLfile = plugin_url().'cached/'.$files[0];
	$XMLfile = $cacheDir.$files[0];
	$xslfile = plugin_url().$files[1];
	
	//echo $XMLfile . "<br />";
	//echo $xslfile . "<br />";
	if($outText = getNPRStories($XMLfile, $xslfile)){
		echo $outText;
	}
}

//*********************FRONTEND STUFF*********************************
	//add_action('the_content', array(&$wfiuPlaylist, 'insert_content')); //used a tag isntead
	//add_action('the_content', 'insert_content'); //used a tag isntead

	add_action('npr_query', 'process_NPR_query' );

	/* stuff that goes in the display of the Post */
/*	add_action('the_content', array(&$podPress, 'insert_content'));
	add_action('get_the_excerpt', array(&$podPress, 'insert_the_excerpt'), 1);
	add_action('the_excerpt', array(&$podPress, 'insert_the_excerptplayer'));

	add_filter('get_attached_file', 'podPress_get_attached_file');
	add_filter('wp_get_attachment_metadata', 'podPress_wp_get_attachment_metadata');
	
	// stuff that goes in the HTML header
	add_action('wp_head', 'podPress_wp_head');
	add_action('wp_footer', 'podPress_wp_footer');
	add_action('switch_theme', 'podPress_switch_theme');
*/



/*
function wfiuPlaylist_menu()
{
    global $wpdb;
    include 'wfiu_playlist-admin.php';
}
*/
/*function wfiuPlaylist_admin_actions(){
	add_options_page("WFIU Playlist", "WFIU Playlist", 1, "WFIU Playlist", "wfiuPlaylist_menu");
}*/
 
//add_action('admin_menu', 'wfiuPlaylist_admin_actions');


?>
