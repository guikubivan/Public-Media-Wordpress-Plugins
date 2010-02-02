<?php
/* 
Plugin Name: Paste Detect
Plugin URI: http://wfiu.org
Version: 1.0
Description: Detects for pasting from word
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/

//require_once(ABSPATH.'wp-includes/formatting.php');
/*
if(!class_exists('paste_detect')) {
	require_once(ABSPATH.PLUGINDIR.'/paste_detect/functions.php');
	require_once(ABSPATH.PLUGINDIR.'/paste_detect/paste_detect_classes.php');

	//echo "<h1>sdklfj</h1>";
	//print_r(get_declared_classes());
	$paste_detect  =  new paste_detect('Picture Manager');
}
*/

require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');

function do_check($post_time){
	$p_timestamp = strtotime($post_time);

	$date_thresh = strtotime("1 February 2009");

	if($p_timestamp < $date_thresh){
		return false;
	}
	return true;

}

function has_word_stuff($text){
	str_ireplace('mso','',$text, $count);
	//if($count > 2){
		if(preg_match("/[^A-Za-z]mso[^A-Za-z]/i",$text)){
			return 'mso ('.$count.')';
		}
		//return 'mso ('.$count.')';
	//}

	$count =0;
	str_ireplace('msoNormal','',$text, $count);
	if($count > 0){
		return 'msoNormal';
	}

	$count =0;
	str_ireplace('if gte mso','',$text, $count);
	if($count > 0){
		return 'if gte mso';
	}

	$count =0;
	str_ireplace('StartFragment','',$text, $count);
	if($count > 0){
		return 'StartFragment';
	}

	return false;

}


function check_wordpaste_posts(){
	global $wpdb;
	if($_GET['post_id']){
		echo "Removing ".$_GET['post_id']."...\n<br />";
		//remove_word_paste($post->ID);
	}

	$post_id = $post->ID;
	$query = "SELECT * FROM $wpdb->posts WHERE post_type ='post' OR post_type='page';";
	$posts = $wpdb->get_results($query);
	echo "<table>";
	echo table_headers('&nbsp;', 'Post Title', 'Cause', 'Date' );
	foreach($posts as $post){

		$content = html_entity_decode($post->post_content);
		
		if(($needle = has_word_stuff($content)) !== false){
			echo table_data_fill("<a href='".get_bloginfo('url')."/wp-admin/post.php?action=edit&post=".$post->ID."'>edit</a>", $post->post_title, array('style="text-align:center;"',$needle), $post->post_date);
			//echo " <a href='?page=" . $_GET['page'] . "&post_id=".$post->ID."'>Fix</a>";
			//echo "<br />";

			//break;
		}


		//echo "<pre>".$content . "</pre><br />==============<br />";
	}
	echo "</table>";
}


//add_action('admin_menu', 'paste_detect');

//add_action('admin_head', array(&$paste_detect,'admin_head'));

//activate plugin
//add_action('activate_paste_detect/paste_detect.php', array(&$paste_detect, 'activate'));



function cleanText(&$text){
	$update = false;

	preg_match("/^ *(<!--\[if gte mso \d*\]>[^<]*<!\[endif\]-->)(.*)/i", $text, $matches);

	while(sizeof($matches)>1){
		//print_r($matches);
		$text = str_replace($matches[1],'',$text);
		//preg_match("/^ *(<!--\[if gte mso \d*\]>.*<!\[endif\]-->)(.*)/i", $content, $matches);
		preg_match("/^ *(<!--\[if gte mso \d*\]>[^<]*<!\[endif\]-->)(.*)/i", $text, $matches);
		$update = true;

	}


	preg_match("/^ *(<!--[^>]*mso[^>]*>)(.*)/i", $text, $matches);
	while(sizeof($matches)>1){
		//print_r($matches);
		$text = str_replace($matches[0],'',$text);
		preg_match("/^ *(<!--[^>]*mso[^>]*>)(.*)/i", $text, $matches);
		$update = true;
		//$query = "UPDATE $wpdb->posts SET post_content = \"".addslashes($newc)."\" WHERE ID=$post_id;";
		//$wpdb->query($query);
	}
	

	preg_match("/(\/\* [^\s]* Definitions[^{]*\{[^}]*\})/i", $text, $matches);
	//print_r($matches);
	while(sizeof($matches)>1){
		$text = str_replace($matches[0],'',$text);
		preg_match("/(\/\* [^\s]* Definitions[^{]*\{[^}]*\})/i", $text, $matches);
		$update = true;
	}	

	return $update;

}

function check_word_paste(){
	global $wpdb, $post;

	if(!do_check($post->post_date)){
		return;
	}

	$post_id = $post->ID;
	$query = "SELECT post_content, post_date FROM $wpdb->posts WHERE ID=$post_id;";
	//$post_row = $wpdb->get_row($query);
	$content = html_entity_decode($post->post_content);



	if(has_word_stuff($content) !== false){
		$query = "UPDATE $wpdb->posts SET post_status = 'draft' WHERE ID=$post_id;";
		$wpdb->query($query);

		echo '<script type="text/javascript">';
		echo 'alert("Error: post not published.\n\nIt looks like you\'ve pasted some text directly from Microsoft Word or another rich text editor.  Posts cannot be published if they contain Word-inserted tags that may change post or entire site formatting. \n\nIn order to publish the post, click on the HTML editing mode tab, and delete the pasted text.  Then, re-paste the text in this mode.")';
		echo '</script>';
	}

	$update = false;
/*
	echo "<b>Before:</b><br />".htmlentities($content) . "<br />==============<br />";

	if(cleanText($content)){
		echo "<b>After:</b><br />".htmlentities($content) . "<br />";
	}
*/
/*
	preg_match("/^ *(<!--\[if gte mso \d*\]>[^<]*<!\[endif\]-->)(.*)/i", $content, $matches);
	while(sizeof($matches)>1){
		//print_r($matches);
		$content = str_replace($matches[1],'',$content);
		echo "<b>1</b>".$content . "<br />==============<br />";
		//preg_match("/^ *(<!--\[if gte mso \d*\]>.*<!\[endif\]-->)(.*)/i", $content, $matches);
		preg_match("/^ *(<!--\[if gte mso \d*\]>[^<]*<!\[endif\]-->)(.*)/i", $content, $matches);
		$update = true;
		//$query = "UPDATE $wpdb->posts SET post_content = \"".addslashes($newc)."\" WHERE ID=$post_id;";
		//$wpdb->query($query);
	}


	preg_match("/^ *(<!--[^>]*mso[^>]*>)(.*)/i", $content, $matches);
	while(sizeof($matches)>1){
		//print_r($matches);
		$content = str_replace($matches[0],'',$content);
		echo "<b>2</b>".$content . "<br />==============<br />";
		preg_match("/^ *(<!--[^>]*mso[^>]*>)(.*)/i", $content, $matches);
		$update = true;
		//$query = "UPDATE $wpdb->posts SET post_content = \"".addslashes($newc)."\" WHERE ID=$post_id;";
		//$wpdb->query($query);
	}
	

	preg_match("/(\/\* [^\s]* Definitions[^{]*\{[^}]*\})/i", $content, $matches);
	//print_r($matches);
	while(sizeof($matches)>1){
		$content = str_replace($matches[0],'',$content);
		echo "<b>3</b>" . $content . "<br />==============<br />";
		preg_match("/(\/\* [^\s]* Definitions[^{]*\{[^}]*\})/i", $content, $matches);
		$update = true;
	}
	//echo "<b>3</b>" . $content . "<br />==============<br />";
	if($update){
		echo "UPDATE $wpdb->posts SET post_content = \"".addslashes($content)."\" WHERE ID=$post_id;";
		//$wpdb->query($query);
	}*/
}


function remove_word_paste($post_id){
	global $wpdb;

	$query = "SELECT post_content, post_date FROM $wpdb->posts WHERE ID=$post_id;";
	$post_row = $wpdb->get_row($query);
	if(!do_check($post_row ->post_date)){
		return;
	}

	$content = html_entity_decode($post_row->post_content);



	//if(cleanText($content)){
	if(has_word_stuff($content) !== false){

		//$query = "UPDATE $wpdb->posts SET post_content = \"".addslashes($content)."\" WHERE ID=$post_id;";
		$query = "UPDATE $wpdb->posts SET post_status = 'draft' WHERE ID=$post_id;";
		$wpdb->query($query);
	}

}

//add_action('admin_head', 'admin_head');

//Edit form hooks
add_action('simple_edit_form', 'check_word_paste');
add_action('edit_form_advanced', 'check_word_paste');
add_action('edit_page_form', 'check_word_paste');

function paste_detect_menu() {
	if(get_bloginfo('version')>= 2.7 ){
		wfiu_do_main_page();

		add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Word Paste Destroyer', 'Word Paste Destroyer', 7, 'Word Paste Destroyer', 'check_wordpaste_posts');


	}else{
		// Add a new top-level menu:
		add_menu_page('Word Paste Destroyer', 'Word Paste Destroyer', 7, __FILE__ , 'check_wordpaste_posts');
	}



	//add_submenu_page(__FILE__, 'Relationships', 'Relationships', 7, 'Relationships', array($paste_detect, 'config_form_relationships'));


}

add_action('admin_menu', 'paste_detect_menu');
//add_meta_box('adv-tagsdiv', __('Tags (Simple Tags)', 'simpletags'), array(&$this, 'boxTags'), 'page', 'side', 'core');

//save and delete post hook
add_action('wp_insert_post', 'remove_word_paste');
//add_action('delete_post', array(&$paste_detect,'deleteItem'));

//*******************CONTENT FUNCTIONS***********************
//add_action('the_content', array(&$paste_detect, 'insert_content')); //used a tag isntead
//*******************************************

//**************************AJAX STUFF*****************************
//add_action('admin_head', array(&$paste_detect, 'admin_head') );
//add_action('wp_ajax_action_get_panel', array(&$paste_detect, 'php_get_panel'));
//add_action('wp_ajax_action_get_panel_generic', array(&$paste_detect, 'php_get_panel_generic'));
//**************************END AJAX STUFF*****************************

function admin_head(){

?>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(document).keyup(function(event){

       var ctrlKey;
        if (event.modifiers) {
            ctrlKey = event.modifiers & Event.CONTROL_MASK
        } else {
            ctrlKey = event.ctrlKey
        }

	    if (ctrlKey) {
		if(event.keyCode == '86'){
			alert("you're tyring to paste stuff, huh?");
		}
	    //jQuery('#content').focus();
	    }
	});
});
</script>


<?php

}

?>
