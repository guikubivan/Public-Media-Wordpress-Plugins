#!/usr/bin/php -q
<?php
/*****************************************************
 * Required variables - change to match your setup ***
 *****************************************************/
$playlists_url_directory = "http://pablo.dyndns-office.com/";
#blog id where playlist tables are, will be ignored if install is not multi-site
$playlists_blog_id = 7;

#station id for the scheduler
$station_id = 1;//set to integer or "" for no schedule

#specify host or domain (needed for wp-includes/ms-settings.php:100)
$_SERVER[ 'HTTP_HOST' ] = 'pablo.dyndns-office.com';

#location of wp-load.php so we have access to database and $wpdb object
$wp_load_loc = '/home/www/wordpress_3.1/wp-load.php';
/*****************************************************
 *******end required variables************************
 *****************************************************/


if($argc ==1)
  die("Usage: import_daily_playlist.php [--today | --date YYYYMMDD] [file1 file2 ...] \n");

require_once($wp_load_loc);

if(is_multisite()) switch_to_blog($playlists_blog_id);

try{

  $results = $wpdb->get_results($wpdb->prepare("DESCRIBE ".$wpdb->prefix."wfiu_playlist;"));
  if(sizeof($results)==0)
    Throw new Exception();
}catch(Exception $e) {
  die($wpdb->prefix."wfiu_playlist table not found, make sure plugin is activated in blog with id $playlists_blog_id\n");
}

/*  START EXECUTION */
global $file_date_string;

date_default_timezone_set("America/New_York");

echo str_repeat("=", 25) . "\nPLAYLIST IMPORT SCRIPT STARTED AT: " . date("D M j G:i:s T Y") . "\n";

$files = Array();
$date = false;
foreach($argv as $key=>$val){
  if($key==0) continue;
  if($date){
    if(preg_match("/(\d{4})(\d{2})(\d{2})$/i", $val) == 0){
      die("Incorrect format when using --date. Needs to be YYYYMMDD.\n");
    }

    $today_f = fetch_playlist_at(strtotime($val));
    if(is_null($today_f)){
      die("ERROR: Could not download today's playlist (url exists?)\n");
    }else{
      $files = Array($today_f);
    }
    break;
  }else if($val == '--today'){
    $today_f = fetch_todays_file();
    if(is_null($today_f)){
      die("ERROR: Could not download today's playlist (url exists?)\n");
    }else{
      $files = Array($today_f);
    }
    break;
  }else if ($val == '--date'){
    $date = true;
    continue;
  }else{
    $files[] = $val;
  }
}

$count = 0;
echo "\nPROCESSING PLAYLIST FILE(S)\n";
foreach($files as $file){
  if(file_exists($file)){
    process_playlist_file($file);
    ++$count;
  }else{
    echo "File \"$file\" does not exist on the filesystem...skipping.\n";
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

function fetch_playlist_at($timestamp){
  global $playlists_url_directory;
  $user_info = posix_getpwuid(posix_getuid());
  $home_dir = $user_info['dir'];
  $temp_folder = $home_dir . "/temp";
  if(!file_exists($temp_folder) || is_file($temp_folder)){
    mkdir($temp_folder);
  }

  $file_name = date("Ymd", $timestamp) . ".txt";
  $download_file = "$temp_folder/$file_name";
  $remote_playlist = $playlists_url_directory . $file_name;

  $command = "curl -f -o " . escapeshellarg($download_file) . " " .  escapeshellarg($remote_playlist);
  echo "\nRETRIEVING TODAYS PLAYLIST FILE\n";
  echo $command . "\n";
  exec($command);
  if(file_exists($download_file)) return $download_file;

  return null;
}

function fetch_todays_file(){
  return fetch_playlist_at(time());
}


function process_playlist_file($file){
  global $file_date_string;

  $filename = basename($file);

  preg_match("/(\d{4})(\d{2})(\d{2})\.txt$/i", $filename, $matches);

  if(sizeof($matches) == 4){
    $file_ts = mktime(0, 0, 0, $matches[2], $matches[3], $matches[1]);
  }else{
    $file_ts = filectime($file);
  }
  
  $file_date_string = date("Y-m-d ", $file_ts);

  echo $filename . "($file_date_string):\t";
  //return;
  $added = 0;
  $skipped = 0;
  $handle = @fopen($file, "r");
  if ($handle) {
    while (($buffer = fgets($handle, 4096)) !== false) {
      $line = trim($buffer);
      if(!empty($line)){
        //echo ".";
        if(parse_playlist_item($line)){
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


function parse_playlist_item($line){
  global $station_id, $file_date_string;
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
  
  $item['start_time'] = date("Y-m-d H:i:s", strtotime($file_date_string. $fields[0]));

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
  return insert_playlist_item($item);

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
?>