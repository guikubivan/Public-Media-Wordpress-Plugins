#!/usr/bin/php -q
<?php
/*****************************************************
 * Required variables - change to match your setup ***
 *****************************************************/
$playlists_url_directory = "http://classical24.publicradio.org/listings/listings.php?display=true&source=C24";
#blog id where playlist tables are, will be ignored if install is not multi-site
$playlists_blog_id = 7;

#station id for the scheduler
$station_id = 1;//set to integer or "" for no schedule

#specify host or domain (needed for wp-includes/ms-settings.php:100)
$_SERVER[ 'HTTP_HOST' ] = 'pablo.dyndns-office.com';

#location of wp-load.php so we have access to database and $wpdb object
$wp_load_loc = '/home/www/wordpress_3.1/wp-load.php';

#valid times in Central Time, later in function parse_playlist_item, we add an hour to the time
$valid_times = Array(
    Array("12345", "00:00", "05:00"),
    Array("12345", "19:00", "23:59"),
    Array("6",  "00:00", "07:00"),
    Array("6", "16:00", "23:59"),
    Array("7", "00:00", "05:00"),
    Array("7", "12:00", "17:00"),
    );

global $time_offset;
$time_offset = 3600; #will add one hour to time in parse_playlist_item
/*****************************************************
 *******end required variables************************
 *****************************************************/


if($argc ==1)
  die("Usage: import_daily_playlist.php [--today] [--date YYYYMMDD] [--all-since YYYYMMDD] [file1 file2 ...] \n");

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

date_default_timezone_set("America/Chicago");

echo str_repeat("=", 25) . "\nPLAYLIST IMPORT SCRIPT (publicradio.org) STARTED AT: " . date("D M j G:i:s T Y") . "\n";


$files = Array();
$mode = '';
$date = false;
foreach($argv as $key=>$val){
  if($key==0) continue;
  if($date){
    if(preg_match("/(\d{4})(\d{2})(\d{2})$/i", $val) == 0){
      die("Incorrect format when using --$mode. Needs to be YYYYMMDD.\n");
    }
    
    if($mode == 'all-since'){
      $start_ts = strtotime($val);
      $now = time();
      while($start_ts < $now){
        $today_f = fetch_playlist_at($start_ts);
        if(is_null($today_f)){
          die("ERROR: Could not download playlist (url exists?)\n");
        }else{
          $files[] = $today_f;
        }
        $start_ts = strtotime("+1 day", $start_ts);
      }
    }else{
      $today_f = fetch_playlist_at(strtotime($val));
      if(is_null($today_f)){
        die("ERROR: Could not download playlist (url exists?)\n");
      }else{
        $files = Array($today_f);
      }
    }
    break;
  }else if($val == '--today'){
    $mode = 'today';
    $today_f = fetch_todays_file();
    if(is_null($today_f)){
      die("ERROR: Could not download today's playlist (url exists?)\n");
    }else{
      $files = Array($today_f);
    }
    break;
  }else if($val == '--all-since'){
    $mode = 'all-since';
    $date = true;
    continue;
  }else if ($val == '--date'){
    $mode = 'date';
    $date = true;
    continue;
  }else{
    $mode = 'files';
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


  $download_file = "$temp_folder/" . date("Ymd", $timestamp) . "-publicradio.txt";
  $remote_playlist = $playlists_url_directory . "&month=" . date("m", $timestamp) . "&day=" . date("d", $timestamp) . "&year=" . date("Y", $timestamp);

  $command = "curl -f -o " . escapeshellarg($download_file) . " " .  escapeshellarg($remote_playlist);
  echo "\nRETRIEVING PLAYLIST FILE FOR " . date("m-d-Y", $timestamp) . ":\n";
  echo $command . "\n";
  exec($command);
  if(file_exists($download_file)) return $download_file;

  return null;
}

function fetch_todays_file(){
  return fetch_playlist_at(time());
}


function process_playlist_file($file){
  global $file_date_string, $valid_times;

  $filename = basename($file);

  preg_match("/(\d{4})(\d{2})(\d{2})-publicradio\.txt$/i", $filename, $matches);

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

  $doc = new DOMDocument();
  @$doc->loadHTMLFile($file);

  $nodes = $doc->getElementsByTagName('table');
  $items_to_parse = array();
  foreach($nodes as $node){
    $tr = $node->firstChild;
    $td = $tr->firstChild;
    $attr_width = $td->getAttribute('width');
    if($attr_width == '20%'){
      $time = trim($td->textContent);

      $item_ts = strtotime($file_date_string . " " . $time);

      $valid = false;
      foreach($valid_times as $vtime){
        
        if( strpos($vtime[0], date("N", $item_ts)) !== false){
          $start = strtotime($file_date_string . " " . $vtime[1]);
          $end = strtotime($file_date_string . " " . $vtime[2]);
          if( ($item_ts >= $start)  && ($item_ts <= $end) ){
            $items_to_parse[] = array($item_ts, $td->nextSibling);
            #echo date("Y-m-d H:i:s\n", $item_ts);
          }
        }
      }
    }
  }

  for($i=0; $i < sizeof($items_to_parse); ++$i){
    
    if($i < sizeof($items_to_parse)-1){
      $duration = $items_to_parse[$i+1][0] - $items_to_parse[$i][0];
      $imported = parse_playlist_item($items_to_parse[$i][0], $duration, $items_to_parse[$i][1]);
    }else{
      $next_day = strtotime("+1 day", $items_to_parse[$i][0]);
      $duration = strtotime(date("Y-m-d 00:00:00", $next_day)) - $items_to_parse[$i][0];
      $imported = parse_playlist_item($items_to_parse[$i][0], $duration, $items_to_parse[$i][1]);
    }

    if($imported){
      ++$added;
    }else{
      ++$skipped;
    }

  }

  echo " ($added added / $skipped skipped)";
  echo "\n";
}


function parse_playlist_item($start_time, $duration, $td){
  global $station_id, $file_date_string, $time_offset;

  $item = array();

  $start_time += $time_offset;#times are in Central time, need to move forward 1 hour
  $item['start_time'] = date("Y-m-d H:i:s", $start_time);
  $item['duration'] = $duration;

  $temp = $td->getElementsByTagName("b");
  $b_tag = $temp->item(0);
  $temp = trim($b_tag->textContent);

  list($composer, $title ) = explode(" - ", $temp, 2);

  $item['composer'] = trim($composer);
  $item['title'] = trim($title);


  $item['artist'] = array();
  
  $temp  = $b_tag->nextSibling;
  $element_group = array(array());
  do {
    $temp  = $temp->nextSibling;
    if($temp->nodeName=='br'){
      $element_group[] = array();
    }else if( in_array($temp->nodeName, array("i")) ){
      $element_group[sizeof($element_group)-1][] = $temp;
    }
  }while( !is_null($temp) );

  #remove empty last item
  if( empty($element_group[sizeof($element_group)-1]) ) array_pop($element_group);

  #process artists
  for($i=0; $i < sizeof($element_group)-1; ++$i){
    $temp = $element_group[$i][0];
    $artist = trim($temp->textContent);
    $item['artist'][] = $artist;
  }

  $item['artist'] = implode("; ", $item['artist']);

  $last_i_tag = $element_group[sizeof($element_group)-1][0];

  $temp = trim($last_i_tag->textContent);

  preg_match("/([^\s]+) *(\d+)/", $temp, $matches);
  $item['label'] = $matches[1];
  $item['label_id'] = $matches[2];
  
  if(!empty($station_id))
    $item['station_id'] = $station_id;

  
  return insert_playlist_item($item);

  #caculate seconds of track
  #$item['duration'] = preg_split("/[:]+/", $fields[2]);
  #$item['duration'] = $item['duration'][0]*60 + $item['duration'][1];



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