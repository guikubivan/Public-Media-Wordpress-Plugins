<?php
/* 
Plugin Name: Earth Eats
Plugin URI: http://wfiu.org
Version: 1.5
Description: Earth eats box. <a href="admin.php?page=Eartheats">Settings</a>
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/

//require_once(ABSPATH.'wp-includes/formatting.php');
if(!class_exists('eartheats')) {
require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_classes.php');
	require_once(ABSPATH.PLUGINDIR.'/eartheats/eartheats_classes.php');
	$eartheats  =  new eartheats('input', 'side', 'Recipe(s)', 'recipe_ids', 'recipe_ids_input');
}

function eartheats_menu() {
	global $eartheats;
}
function eartheats_get_recipes($post_id = -1){
	global $eartheats;
	return $eartheats->get_recipe_ids($post_id);
}

add_action('the_content', array(&$eartheats, 'insert_recipes')); //using a machine tag

//save and delete post hook
//add_action('save_post', array(&$eartheats,'saveItem'));
add_action('delete_post', array(&$eartheats,'deleteItem'));
add_action('admin_menu', 'eartheats_settings_menu', 1);


function eartheats_settings_menu() {
	global $eartheats;
	if(!function_exists('wfiu_do_main_page')){
		require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');
	}
	if(current_user_can('edit_plugins')){
		if(get_bloginfo('version')>= 2.7 ){
			wfiu_do_main_page();
			add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Eartheats', 'Eartheats', 7, 'Eartheats',   array($eartheats, 'settings'));
		}else{
			add_menu_page('Eartheats', 'Eartheats', 7, __FILE__ , array($eartheats, 'settings'));
		}
	}

}

?>
