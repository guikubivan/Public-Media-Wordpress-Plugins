<?php
/*
Plugin Name:NPR Related Stories
Plugin URI: http://www.wfiu.org
Description: Brings in articles/shorts from NPR's API
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

function activate() {
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
	$result .= '://'.$_SERVER['HTTP_HOST'].'/wp-content/plugins/npr_related_stories/';
	return $result;
}

function format_ids($npr_ids){
	if(sizeof($npr_ids)>0){
		$ids_str = "id=";
		foreach($npr_ids as $name => $nid){
			$ids_str .= $nid . ",";
		}
		$ids_str = substr($ids_str, 0, -1);
		$ids_str .= "&";
		return $ids_str;
	}else return '';
}

function make_NPR_call($ids, $tags){
	$keywords = '';
	$count=0;
	foreach($tags as $tag){
		//echo $tag . '|';
		$keywords .= $tag . ' ';
		++$count;
	}
	$keywords = str_ireplace(array(' in ',' and '),'',$keywords);
	$keywords = urlencode(trim($keywords));
	$keywords = "&searchTerm=" . $keywords;
	//$keywords = '';
	//$nprapicall = "http://api.npr.org/query?fields=title,teaser,audio&searchTerm=" . $keywords . "&output=NPRML&searchType=fullContent&apiKey=MDAyMjcyMzExMDEyMjYzNDYzMDI0MDk3Yw001";

	$nprapicall = "http://api.npr.org/query?";
	//$nprapicall .= format_ids(array(10002, 17080801));
	//$nprapicall .= format_ids(array('Jazz & Blues'=>10002, 'Gerard Wilson'=>15156346));
	$nprapicall .= format_ids($ids);
	$output = "&output=NPRML";
	$nprapicall .= "fields=title,teaser,image,audio" . $keywords . $output . "&apiKey=MDAxNzgwMzI5MDEyMTQ4NzkxODhlNWQ5OA004";
	return $nprapicall;
}


function getNPRStories($nprapicall, $xslfile){

	$xml = new DOMDocument;
	@$xml->load($nprapicall);

	$xsl = new DOMDocument;
	@$xsl->load(plugin_url().$xslfile);

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


function insert_content($content){
//function get_npr_related_stories($xslfile){
		global $wpdb, $post;

		//$query0 = "SELECT term_taxonomy_id FROM ".$wpdb->prefix."term_relationships WHERE object_id=".$post->ID ." ORDER BY term_taxonomy_id";
		//$query1 = "SELECT term_id FROM ".$wpdb->prefix."term_taxonomy WHERE term_taxonomy_id IN ($query0) AND taxonomy='post_tag' ORDER BY term_id";
		//$query1 = "SELECT ".$wpdb->prefix."term_taxonomy.term_id FROM ".$wpdb->prefix."term_taxonomy,".$wpdb->prefix."term_relationships  WHERE ".$wpdb->prefix."term_taxonomy.term_taxonomy_id=".$wpdb->prefix."term_relationships.term_taxonomy_id AND object_id=" . $post->ID . " AND taxonomy='post_tag' ORDER BY ".$wpdb->prefix."term_taxonomy.count DESC";

		$query = "SELECT ".$wpdb->prefix."terms.name FROM ".$wpdb->prefix."terms, ".$wpdb->prefix."term_taxonomy,".$wpdb->prefix."term_relationships  WHERE ".$wpdb->prefix."term_taxonomy.term_id=".$wpdb->prefix."terms.term_id AND ".$wpdb->prefix."term_taxonomy.term_taxonomy_id=".$wpdb->prefix."term_relationships.term_taxonomy_id AND object_id=" . $post->ID . " AND taxonomy='post_tag' ORDER BY ".$wpdb->prefix."term_taxonomy.count DESC";


		$tags = $wpdb->get_col($query);
		//$tagcounts = $wpdb->get_col($TCQ);

		//print_r($tags);
		//print_r($tagcounts);

		if(sizeof($tags)==0){
			$donothing='stuff';
			//return;
		}else{
			//$tags = array_reverse($tags);
			//$tags = array_combine($tags,$tagcounts);
			//arsort($tags);
			//print_r($tags);
			$found = false;
			$try = 1;
			print_r($tags);
			while(sizeof($tags)>0 && $found == false){
				//print_r($tags);
				$nprapicall = make_NPR_call(array('Jazz & Blues'=>10002), $tags);



				$xslfile = 'npr_related_stories.xsl';
				if($outText = getNPRStories($nprapicall, $xslfile)){
					$found = true;
					echo "<a href=\"$nprapicall\">npr api call</a>";
					echo $outText;
					break;
				}/*else{
					echo "No related found (try ".$try.")<br />\n";
				}*/
				++$try;
				array_pop($tags);

			}
		}
		/*$tags =get_the_tags($post->ID);
		foreach($tags as $onetag){
			//echo $onetag->name . "<br />\n";
			//print_r($onetag);
			echo $onetag->term_id . ' ' . $onetag->name . "<br />";
		}*/
		echo $content;

}

//*********************FRONTEND STUFF*********************************
	//add_action('the_content', array(&$wfiuPlaylist, 'insert_content')); //used a tag isntead
	add_action('the_content', 'insert_content'); //used a tag isntead

	add_action('npr_related_stories', array(&$wfiuPlaylist,'get_npr_related_stories') );

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
