#!/usr/bin/php -q
<?php

ini_set('memory_limit','128M'); 
/*****************************************************
 * Required variables - change to match your setup ***
 *****************************************************/
#blog id where content should be imported, will be ignored if install is not multi-site
$target_blog_id = 2;

global $default_author_id, $default_role;
$default_author_id = 2;
$default_role = 'author';

#specify host or domain (needed for wp-includes/ms-settings.php:100)
$_SERVER[ 'HTTP_HOST' ] = 'pablo.dyndns-office.com';

$wordpress_dir = "/home/www/wordpress_3.1/";

#path of audio files used as blogs.dir/$target_blog_id/$path_audio
$path_audio = "files/import-audio/";
#path of image files used as blogs.dir/$target_blog_id/$path_images
$path_images = "files/import-images/";
$process = array("audio"=>false, "images"=>true);
/*****************************************************
 *******end required variables************************
 *****************************************************/

#location of wp-load.php so we have access to database and $wpdb object
$wp_load_loc = $wordpress_dir . 'wp-load.php';
#Location of PowerPress main plugin file
$wp_powerpress_loc = $wordpress_dir . "wp-content/plugins/powerpress/powerpress.php";

#Worpdress file that contains the code for inserting attachments (images in our case)
$wp_admin_image_functions = $wordpress_dir . "wp-admin/includes/image.php";

if($argc ==1)
  die("Usage: import_pi_content.php folder_with_text_files \n");

require_once($wp_load_loc);

if(is_multisite()) switch_to_blog($target_blog_id);

/*  START EXECUTION */

date_default_timezone_set("America/New_York");
define('WP_ADMIN', true);#make script run as admin

require_once($wp_powerpress_loc);
require_once($wp_admin_image_functions);

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
  global $default_author_id, $process;
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

  echo "Processing articles...\n";
  $file = joinPaths($dir, "articles.csv");
  $added = 0;
  $skipped = 0;
  $total = 0;
  $handle = @fopen($file, "r");
  $articles = array();
  if ($handle) {
    while (($buffer = fgets($handle)) !== false) {#fgets length was 4096, but it was truncating really long lines stuff
      ++$total;
      if($total==1) continue;
      $line = trim($buffer);
      if(!empty($line)){
        #echo $total;
        $current_item = parse_article_item($line);
        $key = key($current_item);
        if($current_item[$key]['imported']){
          echo ".";
          ++$added;
        }else{
          echo "*";
          ++$skipped;
        }
        $articles = $articles + $current_item;
      }
    }

    echo "\n($added added / $skipped skipped)";
    echo "\n";
    if (!feof($handle)) {
      echo "Error: unexpected fgets() fail\n";
    }

    #print_r($articles);

    fclose($handle);
  }

  if($process['audio']){

    echo "Processing audio items...\n";

    $file = joinPaths($dir, "audio.csv");
    $added = 0;
    $skipped = 0;
    $wrong_format = 0;
    $article_missing = 0;
    $total2 = 0;
    $handle = @fopen($file, "r");

    if ($handle) {
      while (($buffer = fgets($handle)) !== false) {#fgets length was 4096, but it was truncating really long lines stuff
        ++$total2;
        if($total2==1) continue;
        $line = trim($buffer);
        if(!empty($line)){

          $fields = explode("~", $line);
          $article_id = trim($fields[0]);
          if(array_key_exists($article_id, $articles)){
            $result = parse_audio_item($articles[$article_id], $fields);
            if(is_null($result)){
              ++$wrong_format;
              echo "x";
            }else if($result){
              echo ".";
              ++$added;
            }else{
              ++$skipped;
              echo "*";
            }
          }else{
            echo "x";
            ++$article_missing;
          }

        }
      }

      echo "\n($added added / $skipped skipped/ $wrong_format had wrong format or not found/ $article_missing article not found)";
      echo "\n";
      if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
      }

      #print_r($articles);
      fclose($handle);
    }
  }
  if($process['images']){
    echo "Processing image items...\n";

    $file = joinPaths($dir, "images.csv");
    $added = 0;
    $skipped = 0;
    $wrong_format = 0;
    $article_missing = 0;
    $total2 = 0;
    $handle = @fopen($file, "r");

    if ($handle) {
      while (($buffer = fgets($handle)) !== false) {#fgets length was 4096, but it was truncating really long lines stuff
        ++$total2;
        if($total2==1) continue;
        $line = trim($buffer);
        if(!empty($line)){

          $fields = explode("~", $line);
          $article_id = trim($fields[0]);
          if(array_key_exists($article_id, $articles)){
            $result = parse_image_item($articles[$article_id], $fields);
            if(is_null($result)){
              ++$wrong_format;
              echo "x";
            }else if($result){
              echo ".";
              ++$added;
            }else{
              ++$skipped;
              echo "*";
            }
          }else{
            echo "x";
            ++$article_missing;
          }

        }
      }

      echo "\n($added added / $skipped skipped/ $wrong_format had wrong format or not found/ $article_missing article not found)";
      echo "\n";
      if (!feof($handle)) {
        echo "Error: unexpected fgets() fail\n";
      }

      #print_r($articles);
      fclose($handle);
    }
  }
}

function parse_image_item($article_item, $fields){
  global $wordpress_dir, $wp_powerpress_loc, $target_blog_id, $path_images, $wpdb, $default_author_id;
  
  $attachment = $wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE post_type = 'attachment' AND post_parent = " . $article_item['post_id']);
  if($attachment) return false;

  $item = array();
  $item['article_id_ref'] = trim($fields[0]);
  $item['image_id'] = trim($fields[1]);
  $item['image_caption'] = trim($fields[2]);#not used
  $item['image_credit'] = trim($fields[3]);#not used

  $file = $item['image_id'] . ".jpg";
  $image_local = $wordpress_dir . "wp-content/blogs.dir/$target_blog_id/$path_images" . $file;
  
  if(!file_exists($image_local)){
    echo "not found(" . $file. ")";
    return null;
    #only for testing purposes
    #copy($wordpress_dir . "wp-content/blogs.dir/$target_blog_id/$path_images" . "dummy.jpg", $image_local);
  }
  #$image_meta = wp_read_image_metadata($file);


  $url = get_bloginfo('url') . "/" . $path_images . $file;

  // Construct the attachment array
  $attachment =array(
          'post_mime_type' => 'image/jpeg',
          'guid' => $url,
          #'post_parent' => $post_id,
          'post_title' => $item['image_caption'],
          'post_content' => $item['image_caption'],
      'post_author' => $default_author_id
  );
  #$file = array('url' => $url, 'type' => 'image/jpeg', 'file'=> $image_local);

  // Save the data
  $id = wp_insert_attachment($attachment, $file, $article_item['post_id']);

  if ( !is_wp_error($id) ) {
          wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $image_local ) );
  }
  update_post_meta($id, "_wp_attached_file", substr($path_images, strpos($path_images, "files/")+6) . $file);

  return true;
}

function parse_audio_item($article_item, $fields){
  global $wp_powerpress_loc, $target_blog_id, $path_audio, $wpdb;
  
  $item = array();
  $item['article_id_ref'] = trim($fields[0]);
  $item['audio_id'] = trim($fields[1]);
  $item['audio_caption'] = trim($fields[2]);#not used
  $item['audio_credit'] = trim($fields[3]);#not used
  $item['audio_type'] = trim($fields[4]);#do not import files that are not mp3 format
  if($item['audio_type'] == 'mp3'){
    $enclosure = $wpdb->get_row( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'enclosure' AND post_id = " . $article_item['post_id']);
    if(!$enclosure){
      $file = $item['audio_id'] . ".mp3";
      preg_match("/^(.*wp\-content\/).*$/", $wp_powerpress_loc, $matches);
      $audio_local = $matches[1] . "blogs.dir/$target_blog_id/" . $path_audio . $file;
      if(!file_exists($audio_local)){
        echo "not found(" . $file. ")";
        return null;
        #only for testing purposes
        #copy($matches[1] . "blogs.dir/$target_blog_id/" . $path_audio . "dummy.mp3", $audio_local);
      }
      
      $audio_url = get_bloginfo('url') . "/" . $path_audio . $file;

      $r = powerpress_get_media_info_local($audio_local);
      if(!empty($r['error'])){
        echo "error(" . $file . ")";
        return null;
      }

      $enclosure = $audio_url . "\n";
      $enclosure .= $r['length'] . "\n";
      $enclosure .= $r['content-type'] . "\n";
      unset($r['content-type']);
      unset($r['length']);
      $enclosure .= serialize($r);
      #echo $enclosure . "\n=========================\n";
      update_post_meta($article_item['post_id'], 'enclosure', $enclosure);
      return true;
    }
    return false;
  }
  return null;
  
}

function parse_article_item($line){
  global $default_author_id, $wpdb;
  
  $fields = explode("~", $line);
  #echo sizeof($fields);
  #echo "|". implode("|", $fields) ."|\n";

  $item = array();
  $item['article_id'] = trim($fields[0]);
  $item['section_id'] = trim($fields[1]);
  $item['article_date'] = trim($fields[2]);
  $item['article_tease'] = trim($fields[3]);
  $item['byline'] = trim($fields[4]);
  $item['dateline'] = trim($fields[5]);
  $item['headline'] = trim($fields[6]);
  $item['body'] = trim($fields[7]);
  $item['keywords'] = cleanText(trim($fields[8]));
  
  #echo $item['article_date'];

  $wp_post = array('post_type' => 'post', 'post_status'=>'publish');
  $wp_post['post_title'] = sanitize_post_field('post_title', $item['headline'], '', 'db');
  $wp_post['post_content'] = tidy_up_html($item['body']);
  $wp_post['post_date'] = $item['article_date'] . " 00:00:00";
  $wp_post['post_excerpt'] = $item['article_tease'];


  $authors = str_ireplace(array('wosu news reporter', 'wosu news staff', 'wosu news', 'associated press', 'associtaed press',  'ap', 'wosu reporter', 'wosu staff', 'wosu science reporter', 'wosu producers', 'assistant', 'wosu'), '', $item['byline']);
  $authors = trim($authors, "/\\;, \t");
  #if($author)echo $author . "\n";
  if(empty($authors)){
    $wp_post['post_author'] = $default_author_id;
  }

  $current_item = array();
  $current_item[$item['article_id']] = array();

  $cat_name = "section_id_" . $item['section_id'];
  
  if ( !$existing_post = $wpdb->get_row( "SELECT * FROM $wpdb->posts WHERE post_title = '" . $wp_post['post_title'] . "'") ){
    #echo $wp_post['post_title'];

    if(!array_key_exists('post_author', $wp_post)) {
      $authors = preg_split("/[,;]+/", $authors);
      $wp_post['post_author'] = find_or_create_author(trim($authors[0]));#use first author if more than one
    }
    $post_id = wp_insert_post($wp_post);
    #echo $post_id;
    

    /***** SET CATEGORIES ****/
    #We need to insert the categories first because is_taxonomy_hierarchical('category') = true
    $term_id = term_exists($cat_name, 'category');
    if(!$term_id){
      $term_id = wp_insert_term($cat_name, 'category');
    }
    $term_id = $term_id['term_id'];
    wp_set_post_terms( $post_id, array($term_id), 'category');

    /******SET TAGS***********/
    $post_tags = explode(' ', $item['keywords']);
    $post_tags = array_map('strtolower', $post_tags);
    wp_set_post_tags($post_id, $post_tags);

    /**** INSERT EXTRA DATA AS CUSTOM FIELDS ****/
    if($item['dateline'])
      update_post_meta( $post_id, 'PI_dateline', $item['dateline'] );

//	if ($postarr['post_type'] == 'attachment')
//		return wp_insert_attachment($postarr);

    $current_item[$item['article_id']]['imported'] = true;
  }else{
    $current_item[$item['article_id']]['imported'] = false;
    $post_id =  $existing_post->ID;
  }
  $current_item[$item['article_id']]['post_id']  = $post_id;

  return $current_item;
  
  #return insert_playlist_item($item);

  
}

//function insert_playlist_item($item){
//  global $wpdb;
//
//  $query = $wpdb->prepare("SELECT ID FROM " . $wpdb->prefix . "wfiu_playlist WHERE title = %s AND start_time = %s", $item['title'], $item['start_time']);
//  #echo $query . "\n";
//  if(sizeof($wpdb->get_results($query)) > 0){
//    echo "^";
//    return false;
//  }
//  $wpdb->insert( $wpdb->prefix . "wfiu_playlist", $item);
//  echo "+";
//  return true;
//}

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

function find_or_create_author($first_last){
  global $wpdb;
  $first_last = preg_replace("/  +/", " ", $first_last);
  
  #echo $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->users WHERE display_name = %s", $first_last) );
  #print_r($wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->users WHERE display_name = %s", $first_last) ));
  if( !$user = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $wpdb->users WHERE display_name = %s", $first_last) ) ){
    
    #add new author
    $name_parts = explode(" ", $first_last);
    $first = trim($name_parts[0]);
    $last = sizeof($name_parts) > 1 ? trim($name_parts[1]) : '';
    $newuser = pm_add_wp_user($first, $last);
    return $newuser->ID;
  }else{
    return $user->ID;
  }
}

function pm_add_wp_user($first, $last){
  global $wpdb, $default_role;
  #echo $first . "-" . $last;

  $login = strtolower(substr($last, 0, 1) . $first);
  $email = strtolower($first . (empty($last) ? "" : ".$last" ) . "@wosu.org");


  $user_details = wpmu_validate_user_signup($login, $email );
  $new_user_login = apply_filters('pre_user_login', sanitize_user(stripslashes($login), true));
  add_filter( 'wpmu_signup_user_notification', '__return_false' ); // Disable confirmation email
  wpmu_signup_user( $new_user_login, $email, array( 'add_to_blog' => $wpdb->blogid, 'new_role' => $default_role ) );
  $key = $wpdb->get_var( $wpdb->prepare( "SELECT activation_key FROM {$wpdb->signups} WHERE user_login = %s AND user_email = %s", $new_user_login, $email) );
  wpmu_activate_signup( $key );

  $newuser = get_userdatabylogin( $new_user_login );
  wp_update_user(array('ID'=> $newuser->ID, 'first_name'=>$first, 'last_name'=>$last, 'display_name' => $first . ' ' . $last));
  #echo '|';
  return get_userdatabylogin( $new_user_login );
}

function tidy_up_html($html, $force_p_tags = true){
  require_once("htmlparser.inc");
  $START = 1;
  $END = 2;
  $TEXT = 3;
  $COMMENT = 4;
  $NULL_ELEMENT = "-999";

  if( empty($html) ) return $html;
  
  $parser = new HtmlParser($html);

  $inPar = false;
  $parText = '';
  $outString = '';

  $count = 0;
  while ($parser->parse()) {

        $element = strtolower($parser->iNodeName);
        $type = $parser->iNodeType;

        $myArray = $parser->iNodeAttributes;
        $text_val = cleanText($parser->iNodeValue);
/*      if(($element == "p") && ($type==$START) ){
                if($inPar){
                        $outString .= getTag($END,$element, array());
                }
                $outString .= getTag($START,$element, array());
                $inPar = true;
        }
*/
        #first tag is not <p>
        if( ($count == 0) && ($element != "p")){
          $inPar = true;
          $parText = "";
        }
        
        if(($element == "p") && ($type==$START) ){
                $parText = cleanText($parText);
                if($inPar && !empty($parText) ){
                        $outString .= getTag($START, 'p', array());//write out p start tag
                        $parText .= getTag($END, 'p', array());//write out p end tag
                        $outString .= $parText;//write out text and end p tag
                        echo ".";
                }
                $inPar = true;
                $parText = "";
        }


        if($element == "br"){
          $text_val = '';
          do{
            $parser->parse();
            $element = strtolower($parser->iNodeName);
            $type = $parser->iNodeType;
            $myArray = $parser->iNodeAttributes;
            if($type == $TEXT){
                  $text_val = cleanText($parser->iNodeValue);
            }
          }while($type == $TEXT && empty($text_val));

          #if using two <br> for spacing, close the current paragraph
          #if no text in current paragraph, do nuthin
          if($element == "br" && !empty($parText)){
            $outString .= getTag($START,'p', array());//write out p start tag
            $outString .= $parText;
            $outString .= getTag($END,'p', array());//write out p end tag
            $parText = "";

          }
        }

        if($type==$TEXT){
            if(empty($parText)){
              $parText = $text_val;
            }else{# !empty($parText)
              $parText .= " " .  $text_val;
            }
        }
        ++$count;
  }
  
  $parText = cleanText($parText);
  if($inPar && !empty($parText) ){
    $outString .= getTag($START, 'p', array());//write out p start tag
    $outString .= $parText;
    $outString .= getTag($END, 'p', array());//write out p end tag
  }
  $outString = cleanText(preg_replace("/<br\/?>\s*<\/p>/", '</p>', $outString));//check for this

  return $outString;
}

function cleanText($someText){
	$cleanText = str_ireplace("&nbsp;", " ", $someText);
	$cleanText = preg_replace("/\n/", " ", $cleanText);
	#$cleanText = trim($cleanText);
	$cleanText = preg_replace("/  +/", " ", $cleanText);
	return $cleanText;
}


//Formats a tag labeled as $name correctly,
//adding a backslash to closing tags, and adding it's attributes
function getTag($type,$name, $attrib){
	$END = 2;

	$retString = "<";
	if($type == $END){
		$retString .= "/";
	}
	$retString .= $name;
	$a_Keys = array_keys($attrib);
	foreach($a_Keys as $s_Key) {
		$retString .= " " . $s_Key .  "=\"" . $attrib[$s_Key] . "\"";
	}
	$retString .= ">";
  return $retString;
}
?>