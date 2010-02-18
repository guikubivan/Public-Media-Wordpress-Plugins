<?php
/*
Plugin Name: IPM - Archives permalink modifier
Plugin URI: http://indianapublicmedia.org
Description: Does a rewrite to match archives/year and archives/year/month
Version: 1.0
Author: Pablo Vanwoerkom
Author URI: http://mypage.iu.edu/~gvanwoer/
*/

$option_name = 'ipm_archives_permalink_categories';

add_action('init', 'archives_flush_rewrite_rules');

function archives_flush_rewrite_rules() 
{
   global $wp_rewrite;
   $wp_rewrite->flush_rules();
}


add_action('generate_rewrite_rules', 'archives_add_rewrite_rules');

function archives_add_rewrite_rules( $wp_rewrite ) {
	global $wpdb, $option_name;
  $categories = get_option($option_name);
  if($categories)$categories = '&category='.trim($categories);
	$lastpost = get_posts("numberposts=1${categories}&orderby=date&order=desc&post_status=publish&post_type=post");
	$lastpost = $lastpost[0];
	$timestamp = strtotime($lastpost->post_date);
	$month = date("m", $timestamp);
	$year = date("Y", $timestamp);

	$new_rules = array('archives/?' => 'index.php?year=' . $year . '&monthnum=' . $month);
	//$new_rules = array('archives/?' => "index.php?year=2009" );
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;	

	$new_rules = array('archives/([0-9]{4})/?' => 'index.php?year=' . $wp_rewrite->preg_index(1) . '&monthnum=01' );
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;

	$new_rules = array('archives/([0-9]{4})/([0-9]{1,2})/?' => 'index.php?year=' . $wp_rewrite->preg_index(1) . '&monthnum=' . $wp_rewrite->preg_index(2));
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	return $wp_rewrite->rules;
}

if(file_exists(ABSPATH.PLUGINDIR.'/wfiu_utils/ipm-utils-class.php')){
	require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/ipm-utils-class.php');
}else{
	require_once(dirname(__FILE__) . '/ipm-utils-class.php');
}

$myutils = new IPM_Utils();
function ipm_archives_permalink_menu(){
	global $wp_slideshow, $myutils;

	if(current_user_can('edit_plugins')){
		$myutils->add_page_main();
		add_submenu_page($myutils->main_page_path(), 'Archives Permalink', 'Archives Permalink', 7, 'Archives Permalink',  'ipm_archives_permalink_settings');

	}
}

function ipm_archives_permalink_settings(){
  global $option_name;
  if(isset($_POST[$option_name])){

    if(get_option($option_name)){
      update_option($option_name, $_POST[$option_name]);
    }else{
      add_option($option_name, $_POST[$option_name]);
    }
    echo "<div class='updated fade' >Settings saved</div>";
  }
  $value = get_option($option_name );
?>
<div class='wrap'>
<h2>Archives Permalink Settings</h2>
<form method='post' action=''>
<table>
<tr><td>Restrict to categories(seperate multiple ids by comma):</td>
</tr><tr><td>
  <input value="<?php echo $value; ?>" name="<?php echo $option_name; ?>" id="<?php echo $option_name; ?>" />
</td>
</tr>
</table>
<input type='submit' value='Save' />
</form>
</div>
<?php
}
add_action('admin_menu', 'ipm_archives_permalink_menu');

?>
