#!/usr/bin/php -q
<?php
/*****************************************************
 * Required variables - change to match your setup **
 *****************************************************/
#blog id where playlist tables are
$blog_id = 1;

#station id for the scheduler
$station_id = "";//set to integer or ""

#specify host or domain (needed for wp-includes/ms-settings.php:100)
$_SERVER[ 'HTTP_HOST' ] = 'localhost';

#location of wp-load.php so we have access to database and $wpdb object
$wp_load_loc = '/home/www/wordpress_3.1/wp-load.php';
/*******end required variables*************************/

if($argc ==1)
  die("Usage: ./import_daily_playlist.php file_name\n");

require_once($wp_load_loc);
switch_to_blog($blog_id);

try{

  $results = $wpdb->get_results($wpdb->prepare("DESCRIBE ".$wpdb->prefix."wfiu_playlist;"));
  if(sizeof($results)==0)
    Throw new Exception();
}catch(Exception $e) {
  die($wpdb->prefix."wfiu_playlist table not found, make sure plugin is activated in blog with id $blog_id\n");
}


$file = $argv[1];

$handle = @fopen($file, "r");
if ($handle) {
  while (($buffer = fgets($handle, 4096)) !== false) {
    $line = trim($buffer);
    if(!empty($line)){
      //echo ".";
      parse_playlist_item($line);
    }
  }
  echo "\n";
  if (!feof($handle)) {
    echo "Error: unexpected fgets() fail\n";
  }
  fclose($handle);
}


function parse_playlist_item($line){
  global $station_id;
  $fields = explode("\t", $line);
  #echo sizeof($fields);
  #echo "|". implode("|", $fields) ."|\n";

  $item = array();
  $time_parts = preg_split("/[:\s]+/", $fields[0]);
  
  if($time_parts[1] > 60){
    $time_parts[0] += intval($time_parts[1] / 60);
    $time_parts[1] = intval($time_parts[1] % 60);
  }
  if($time_parts[0] > 12){
    $time_parts[0] = intval($time_parts[0] % 12);
    $time_parts[2] = $time_parts[2]=="am" ? "pm" : "am";
  }
  $fields[0] = $time_parts[0] . ":" . $time_parts[1] . ":00 " . $time_parts[2];
  
  $item['start_time'] = date("Y-m-d H:i:s", strtotime(date("Y-m-d "). $fields[0]));

  #caculate seconds of track
  $item['duration'] = preg_split("/[:]+/", $fields[2]);
  $item['duration'] = $item['duration'][0]*60 + $item['duration'][1];

  $item['composer'] = $fields[3];
  $item['title'] = combine_array_items(array_slice($fields, 4, 4));#e.g. |Adagio|from Choral Fantasy|in c|,  Op. 80|
  $item['artist'] = "";#initialize, will be in format "soloist; conductor/orchestra"
  
  #soloist
  if(array_key_exists(12, $fields)){
    $item['artist'] = $fields[12];
  }

  #conductor/orchestra
  $artist_cond_orch = "";
  if( !empty($fields[8]) && !empty($fields[9]) ){
    $artist_cond_orch = $fields[8] . "/" . $fields[9];
  }else{
    $artist_cond_orch = $fields[8] . $fields[9];#at least one will be empty
  }

  #Either both soloist or $artist_cond_orch exist, or one of them doesn't
  $item['artist'] .= !empty($item['artist'] ) && !empty($artist_cond_orch) ? "; " . $artist_cond_orch : $artist_cond_orch;

  $item['label'] = $fields[10];
  $item['label_id'] = $fields[11];

  if(!empty($station_id))
    $item['station_id'] = $station_id;
  insert_playlist_item($item);

  #echo "\n|" . $fields[0]."|";
  #echo "\n\t|" . $item['duration']."|";
}

function insert_playlist_item($item){
  global $wpdb;
  
  $wpdb->insert( $wpdb->prefix . "wfiu_playlist", $item);
  echo "+";
}

function combine_array_items($array){
  return preg_replace(array('/\s{2,}/','/\s,/'), array(" ", ","), trim(implode(' ', $array) ));

}
?> 