<?php
/* 
Plugin Name: Excerpt Character Count
Plugin URI: http://wfiu.org
Version: 1.0
Description: Checks for max character count of 160
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/



function character_counter_initialize(){
	$defaults = array();
	$prefix='char_count_';

	$defaults['title']['min']=0;
	$defaults['title']['max']=66;
	$defaults['title']['optional']='off';

	$defaults['teaser']['min']=50;
	$defaults['teaser']['max']=160;
	$defaults['teaser']['optional']='off';

	$defaults['excerpt']['min']=0;
	$defaults['excerpt']['max']=350;
	$defaults['excerpt']['optional']='on';
	$defaults['in_pages']='off';
	//echo get_option('sl_google_map_country');
	foreach($defaults as $name => $props){
		//echo "*";
		if(is_array($props)){
			foreach($props as $p => $option_val){
				//echo "-";
			
				$option_name = $prefix . $name . '_' . $p;
				//echo $option_name . ': ' . $option_val ."<br />";
				if ( get_option($option_name) === false) {
				
					//echo $option_name;
					add_option($option_name, $option_val);
					//$val = get_option($option_name);
				}
			
			}
		}else{
			$option_name = $prefix . $name;
			if ( get_option($option_name) === false) {
			
				//echo $option_name;
				add_option($option_name, $option_val);
				//$val = get_option($option_name);
			}
		}
	}	
}

character_counter_initialize();
//add_action('activate_'.__FILE__,'character_counter_initialize');
//register_activation_hook(__FILE__, 'character_counter_initialize');


$path = $_SERVER['SCRIPT_NAME'];

$pattern = get_option('char_count_in_pages') ?  'post|page' : 'post';
if(preg_match("/wp-admin\/$pattern/",$path)){
	add_action( "admin_print_scripts", 'character_count_js');
}


if(!function_exists('character_count_js')){
	function character_count_js(){

		
		wp_register_script('fields_character_count', get_bloginfo('url').'/wp-content/plugins/wfiu_utils/character_counter.js', array('jquery'));
		$fields= array('title','teaser', 'excerpt');
		$props = array('min', 'max', 'optional');
		$prefix='char_count_';
		$fprops = array();
		foreach($fields as $f){
			foreach($props as $p){
				$option_name = $prefix . $f . '_' . $p;
				$val = get_option($option_name);
				if( ($p =='optional') && !$val ){
					$val = 'off';
				}
				$fprops[$option_name] = $val;
			}			
		}

		$prefix='char_count_';
		if(current_user_can('edit_plugins')){
			wp_localize_script( 'fields_character_count', 'CharCountSettings', array_merge(array('isAdmin' => true), $fprops));
		}else{
			wp_localize_script( 'fields_character_count', 'CharCountSettings', $fprops);
		}
		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script('fields_character_count');

	}
}

if(!class_exists('box_custom_field_plugin')) {
	require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_classes.php');
	//element, position, box title, post meta key, inputfield name
	$teaser_box = new box_custom_field_plugin(array('settings'=> "style='width: 100%;'", 'element' =>'textarea'), 'normal','Teaser', 'teaser_text', 'teaser' );
}

if(!function_exists('the_teaser')){
	function the_teaser(){


		global $post, $drop_caps_plugin;

		$teaser = get_post_meta($post->ID, 'teaser_text', false);
		$teaser = "<p>".stripslashes($teaser[0]). "</p>";


		if(class_exists('DropCaps')) {
			//require_once(ABSPATH.PLUGINDIR.'/wp_drop_caps.php');
			$options = $drop_caps_plugin->get_options();

			if ($options['the_excerpt'] && $drop_caps_plugin->is_enabled($options) && !$drop_caps_plugin->is_excluded($options)){
				//We're look for the first paragraph tag followed by a capital letter
				$pattern = '/<p>((&quot;|&#8220;|&#8216;|&lsquo;|&ldquo;|\')?[A-Z])/';
				$replacement = '<p class="first-child"><span title="$1" class="cap"><span>$1</span></span>';
				$teaser = preg_replace($pattern, $replacement, $teaser, 1 );
			}
		}

		echo $teaser;	
	}
}

add_action('save_post', 'copy_teaser2excerpt', 1);
function copy_teaser2excerpt($post_id){
	global $teaser_box, $wpdb;
	if ( !wp_verify_nonce( $_POST['teasernoncename'], plugin_basename('teasernonce') )) {
		return $post_id;
	}
	
	if(!trim($_POST[excerpt])){
			$excerpt = $_POST[teaser];
		if( $post_id > 0 ) {
			$query = "UPDATE {$wpdb->posts} SET post_excerpt = \"$excerpt\" WHERE ID = ".$post_id;
			$wpdb->query($query);
		}
	}
	return;
}


add_action('admin_menu', 'character_count_menu');
function character_count_menu(){
	if(current_user_can('edit_plugins')){
		require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');
		if(get_bloginfo('version')>= 2.7 ){
			wfiu_do_main_page();
			add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Character Counter', 'Character Counter', 7, ABSPATH.PLUGINDIR.'/wfiu_utils/char_count_settings.php');
		}else{
			add_menu_page('Character Counter', 'Character Counter', 7, ABSPATH.PLUGINDIR.'/wfiu_utils/char_count_settings.php');
		}		
	}
}



?>
