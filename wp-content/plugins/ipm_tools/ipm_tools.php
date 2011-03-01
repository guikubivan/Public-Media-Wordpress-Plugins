<?php
/* 
Plugin Name: IPM Tools
Plugin URI: http://wfiu.org
Version: 1.0
Description: Various tools
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/


require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');


function ipm_tools_main(){
	global $wpdb;

	if($_POST['show_posts']){
		$query = "SELECT ID, post_name, guid FROM $wpdb->posts WHERE post_type='post' AND post_status='publish' ORDER BY ID ASC;";
		$posts = $wpdb->get_results($query);
	}
	?>

<form method='POST' name='search_form' action='?page=<?php echo $_GET['page']; ?>' >
<input type='submit' name='show_posts' />
</form>


<?php


}

function ipm_tools_menu() {
	if(get_bloginfo('version')>= 2.7 ){
		wfiu_do_main_page();

		add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'IPM Tools', 'IPM Tools', 7, 'IPM Tools', 'ipm_tools_main');


	}else{
		// Add a new top-level menu:
		add_menu_page('IPM Tools', 'IPM Tools', 7, __FILE__ , 'ipm_tools_main');
	}



	//add_submenu_page(__FILE__, 'Relationships', 'Relationships', 7, 'Relationships', array($ipm_tools, 'config_form_relationships'));


}

add_action('admin_menu', 'ipm_tools_menu');
//add_meta_box('adv-tagsdiv', __('Tags (Simple Tags)', 'simpletags'), array(&$this, 'boxTags'), 'page', 'side', 'core');

?>
