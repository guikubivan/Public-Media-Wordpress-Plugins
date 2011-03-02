<?php
/* 
Plugin Name: IPM - Category Manager
Plugin URI: http://wfiu.org
Version: 1.0.1
Description: Managing categories with logic.
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/


if(!class_exists('category_manager')) {
	require_once(ABSPATH.PLUGINDIR.'/ipm-category_manager/category_manager_classes.php');
	//echo "<h1>sdklfj</h1>";
	//print_r(get_declared_classes());
	$category_manager  =  new category_manager("Top Categories");	

						//(mult?, req?)
	//$category_manager->add_category_group(9356, true, true);//Content Type
	//$category_manager->add_category_group(9353, true, false);//Special
	//$category_manager->add_category_group(27, false, false, 9353);//Podcasts

	//=============================================
	//10: Visual Arts
	//11: Movies
	//6: Classical

	//9351: Interview
	//9359: Pocasts *
	//9352: Review *

	//$category_manager->add_relationship(3943, array(10,9359)); //Angles
	//$category_manager->add_relationship(4003, array(11, 9359, 9352)); //Movie Reviews
	//$category_manager->add_relationship(3089, array(6,9359, 9352)); //Featured Classical

	//=============================================
}

add_action('admin_menu', 'category_manager_menu');
function category_manager_menu() {
	global $category_manager;
	// Add a new top-level menu:
	if(current_user_can('edit_plugins')){
		if(get_bloginfo('version')>= 2.7 ){
			cm_do_main_page();
			add_submenu_page(cm_main_page_path(), 'IPM Category Manager', 'IPM Category Manager', 7, 'ipm-category_manager',   array($category_manager, 'main_config_form'));
		}else{

			add_menu_page('WFIU Category Manager', 'WFIU Category Manager', 7, __FILE__ , array($category_manager, 'config_form'));

			//add_submenu_page( __FILE__ , 'Category Manager Config', 'Category Manager Config', 7, 'Category Manager Config', array($category_manager, 'config_form'));
			add_submenu_page( __FILE__ , 'Relationships', 'Relationships', 7, 'Relationships', array($category_manager, 'config_form_relationships'));
			add_submenu_page( __FILE__ , 'Mass Change', 'Mass Change', 7, 'Mass Change', array($category_manager, 'config_form_masschange'));
		}
	}

}


if(!function_exists('cm_main_page_path'))
{
	function cm_main_page_path()
	{
		global $menu;
		$exists = false;
		$found = -1;
		foreach($menu as $key => $m){
			if($m[0] == "IPM Plugins"){
				$exists = true;
				$found = $key;
			}
		}
		if($exists){
			return $menu[$key][2];
		}
	
		return  __FILE__;
	}
}

if(!function_exists('cm_do_main_page'))
{
	function cm_do_main_page()
	{
		global $menu;
		$exists = false;
		$found = -1;
		foreach($menu as $key => $m){
			if($m[0] == 'IPM Plugins'){
				$exists = true;
				$found = $key;
			}
		}
		if(!$exists){
			add_menu_page('IPM Plugins', 'IPM Plugins', 7, ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php');
		}
		/******************************/
		global $submenu;
	
		if($submenu['wfiu_utils/wfiu_plugins_homepage.php'][0][0] == 'IPM Plugins'){
			unset($submenu['wfiu_utils/wfiu_plugins_homepage.php'][0]);
		}
		/*else{
			unset($menu[$found]);
	
		}*/
	}
}




add_action('admin_menu', array(&$category_manager, 'postBox'), 1);

//add_action('admin_head', array(&$category_manager,'admin_head'));

//activate plugin
add_action('activate_category_manager/category_manager.php', array(&$category_manager, 'activate'));
/*
add_action('admin_head', array(&$map_item_class,'admin_head'));
*/
//Edit form hooks
/*
add_action('simple_edit_form', array(&$category_manager, 'ajax_post_form'));
add_action('edit_form_advanced', array(&$category_manager, 'ajax_post_form'));
add_action('edit_page_form', array(&$category_manager, 'ajax_post_form'));
*/

//save and delete post hook
add_action('save_post', array(&$category_manager,'save_post'));
//add_action('delete_post', array(&$map_item_class,'deleteItem'));
/*

add_filter('manage_posts_columns', array(&$map_item_class,'add_column'));
add_filter('manage_posts_custom_column', array(&$map_item_class,'do_column'), 10, 2);
*/


//**************************AJAX STUFF*****************************
add_action('admin_print_scripts', array(&$category_manager, 'admin_head') );
add_action('wp_ajax_action_get_panel', array(&$category_manager, 'php_get_panel'));
add_action('wp_ajax_action_get_panel_generic', array(&$category_manager, 'php_get_panel_generic'));
//**************************END AJAX STUFF*****************************

?>
