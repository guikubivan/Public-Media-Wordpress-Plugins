<?php
/* 
Plugin Name: NPR Article Importer
Plugin URI: http://wfiu.org
Version: 1.0
Description: Allows users to import articles from NPR based on its article id. <a href="admin.php?page=NPR Importer">Settings</a>
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/

//require_once(ABSPATH.'wp-includes/formatting.php');
if(!class_exists('npr_article_importer')) {
require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_classes.php');
	require_once(ABSPATH.PLUGINDIR.'/npr_article_importer/npr_article_importer_classes.php');
	$npr_importer_class  =  new npr_article_importer('input', 'side', 'NPR Article Import(s)', 'npr_article_id', 'npr_article_id_input');
}

function npr_article_importer_menu() {
	global $npr_article_importer;
}


add_action('the_content', array(&$npr_importer_class, 'insert_story')); //replace with npr article if linked to one
add_action('the_author', array(&$npr_importer_class, 'replace_author'));

//save and delete post hook
//add_action('save_post', array(&$npr_article_importer,'saveItem'));
add_action('admin_menu', 'npr_article_importer_settings_menu', 1);


function npr_article_importer_settings_menu() {
	global $npr_importer_class;

	$path = $_SERVER['SCRIPT_NAME'];
	if(strpos($path, 'post') !== false){
		//add_action( "admin_print_scripts", 'character_count_js');
	        wp_enqueue_script('npr_article_importer', get_bloginfo('url').'/wp-content/plugins/npr_article_importer/write_post_functions_js.php');
	}



	if(!function_exists('wfiu_do_main_page')){
		require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');
	}
	if(current_user_can('edit_plugins')){
		if(get_bloginfo('version')>= 2.7 ){
			wfiu_do_main_page();
			add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'NPR Importer', 'NPR Importer', 7, 'NPR Importer',   array($npr_importer_class, 'settings'));
		}else{
			add_menu_page('NPR_Importer', 'NPR Importer', 7, __FILE__ , array($npr_importer_class, 'settings'));
		}
	}

}

?>
