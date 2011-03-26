#!/usr/bin/php -q
<?php
if($argc ==1)
  die("Usage: ./import_daily_playlist.php file_name\n");


require_once('/home/www/wordpress_3.1/wp-config.php');
require_once('/home/www/wordpress_3.1/wp-db.php');
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
  if (!feof($handle)) {
    echo "Error: unexpected fgets() fail\n";
  }
  fclose($handle);
}


function parse_playlist_item($line){
  $fields = explode("\t", $line);
  //echo sizeof($fields);
  #echo "|". implode("|", $fields) ."|\n";

  $item = array();
  $item['start_time'] = $fields[0];
  $item['duration'] = $fields[2];
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

  #echo "\n|".$item['composer']."|";
  #echo "\n|".$fields[11]."|";

}

function combine_array_items($array){
  return preg_replace(array('/\s{2,}/','/\s,/'), array(" ", ","), trim(implode(' ', $array) ));

}
?> 