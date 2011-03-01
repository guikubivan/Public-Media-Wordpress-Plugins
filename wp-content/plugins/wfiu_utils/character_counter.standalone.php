<?php
/* 
Plugin Name: Character Counter
Plugin URI: http://wfiu.org
Version: 1.0
Description: Checks for min and max # of characters for certain fields
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
	//echo get_option('sl_google_map_country');
	foreach($defaults as $name => $props){
		//echo "*";
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
	}	
}
character_counter_initialize();
//add_action('activate_'.__FILE__,'character_counter_initialize');
//register_activation_hook(__FILE__, 'character_counter_initialize');


$path = $_SERVER['SCRIPT_NAME'];
if(strpos($path, 'post') !== false){
	add_action( "admin_print_scripts", 'character_count_js');
}


if(!function_exists('character_count_js')){
	function character_count_js(){

		
		wp_register_script('fields_character_count', get_bloginfo('url').'/wp-content/plugins/character_counter/character_counter.js', array('jquery'));
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
	require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_classes_php4.php');
	//element, position, box title, post meta key, inputfield name
	$teaser_box = new box_custom_field_plugin(array('settings'=> "style='width: 100%;'", 'element' =>'textarea'), 'normal','Teaser', 'teaser_text', 'teaser' );
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
		add_menu_page('Character Counter', 'Character Counter', 7, ABSPATH.PLUGINDIR.'/character_counter/char_count_settings.php');
	}
}





?>
