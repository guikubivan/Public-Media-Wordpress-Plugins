#!/usr/bin/php -q
<?php
/*****************************************************
 * Required variables - change to match your setup ***
 *****************************************************/
#blog id where content should be imported, will be ignored if install is not multi-site
$target_blog_id = 2;

global $default_user_id, $default_role;
$default_user_id = 2;
$default_role = 'autorh';

#specify host or domain (needed for wp-includes/ms-settings.php:100)
$_SERVER[ 'HTTP_HOST' ] = 'pablo.dyndns-office.com';

#location of wp-load.php so we have access to database and $wpdb object
$wp_load_loc = '/home/www/wordpress_3.1/wp-load.php';
/*****************************************************
 *******end required variables************************
 *****************************************************/


if($argc ==1)
  die("Usage: import_pi_content.php folder_with_text_files \n");

require_once($wp_load_loc);

if(is_multisite()) switch_to_blog($target_blog_id);

/*  START EXECUTION */

date_default_timezone_set("America/New_York");

echo str_repeat("=", 25) . "\nPI IMPORT SCRIPT STARTED AT: " . date("D M j G:i:s T Y") . "\n";

$files = Array();
$date = false;
foreach($argv as $key=>$val){
  if($key==0) continue;
    $files[] = $val;
}

$count = 0;
echo "\nPROCESSING DATA BUNDLE(S)\n";
foreach($files as $file){
  if(is_dir($val)){
    process_bundled_data($file);
    ++$count;
  }else{
    echo "Argument \"$file\" is not a directory on the filesystem...skipping.\n";
  }
}

if($count > 0){
  echo "
______________________
|      LEGEND        |
|   +  | item added  |
|   ^  | item exists |
______________________
";
}

echo "DONE\n" . str_repeat("=", 25) . "\n";

function process_bundled_data($dir){
  global $default_user_id;
  $files = array();
  
  echo "Bundle directory: $dir:\n";
  if ($dh = opendir($dir)) {
      while (($file = readdir($dh)) !== false) {
        
        if( (filetype(joinPaths($dir, $file)) == 'file') && in_array($file, array("articles.csv", "audio.csv", "images.csv")) ){
          $files[] = joinPaths($dir, $file);
        }
      }
      closedir($dh);
  }
  #return;
  if(sizeof($files) != 3){
    die("Directory did not contain the three needed files (articles, audio, images).\n");
  }

  $file = joinPaths($dir, "articles.csv");
  $added = 0;
  $skipped = 0;
  $handle = @fopen($file, "r");
  if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
      $line = trim($buffer);
      if(!empty($line)){
        //echo ".";
        $article_item = parse_article_item($line);
        if(!is_null($article_item)){
          ++$added;
        }else{
          ++$skipped;
        }
      }
    }
    echo " ($added added / $skipped skipped)";
    echo "\n";
    if (!feof($handle)) {
      echo "Error: unexpected fgets() fail\n";
    }
    fclose($handle);
  }
}


function parse_article_item($line){
  global $default_user_id;
  
  $fields = explode("~", $line);
  #echo sizeof($fields);
  #echo "|". implode("|", $fields) ."|\n";

  $item = array();
  
  $post_id = wp_insert_post( array( 'post_title' => "My title", 'post_content'=>"This is a new post.", 'post_type' => 'post', 'post_author' => $default_user_id, 'post_status'=>'publish' ) );
  #update_post_meta( $post_ID, '_edit_last', $GLOBALS['current_user']->ID );
  #return insert_playlist_item($item);

  #echo "\n|" . $fields[0]."|";
  #echo "\n\t|" . $item['duration']."|";
}

function insert_playlist_item($item){
  global $wpdb;

  $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "wfiu_playlist WHERE title = %s AND start_time = %s", $item['title'], $item['start_time']);
  #echo $query . "\n";
  if(sizeof($wpdb->get_results($query)) > 0){
    echo "^";
    return false;
  }
  $wpdb->insert( $wpdb->prefix . "wfiu_playlist", $item);
  echo "+";
  return true;
}

function combine_array_items($array){
  return preg_replace(array('/\s{2,}/','/\s,/'), array(" ", ","), trim(implode(' ', $array) ));

}

function joinPaths() {
    $args = func_get_args();
    $paths = array();
    foreach ($args as $arg) {
        $paths = array_merge($paths, (array)$arg);
    }

    $paths = array_map(create_function('$p', 'return trim($p, "/");'), $paths);
    $paths = array_filter($paths);
    return join('/', $paths);
}

function pm_add_wp_user($first, $last){
  global $wpdb, $default_role;


  
  $login = strtolower(substr($last, 0, 1) . $first);
  $email = strtolower($first . "." . $last . "@wosu.org");


  $user_details = wpmu_validate_user_signup($login, $email );
  $new_user_login = apply_filters('pre_user_login', sanitize_user(stripslashes($login), true));
  add_filter( 'wpmu_signup_user_notification', '__return_false' ); // Disable confirmation email
  wpmu_signup_user( $new_user_login, $email, array( 'add_to_blog' => $wpdb->blogid, 'new_role' => $default_role ) );
  $key = $wpdb->get_var( $wpdb->prepare( "SELECT activation_key FROM {$wpdb->signups} WHERE user_login = %s AND user_email = %s", $new_user_login, $email) );
  wpmu_activate_signup( $key );

  $newuser = get_userdatabylogin( $new_user_login );
  wp_update_user(array('ID'=> $newuser->ID, 'first_name'=>$first, 'last_name'=>$last, 'display_name' => $first . ' ' . $last));
}
?>