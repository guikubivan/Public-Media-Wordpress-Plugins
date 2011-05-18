<?php
if(!function_exists('ps_get_pitem_composer') ){
  function ps_get_pitem_composer(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->composer;
  }

  function ps_pitem_composer(){
    echo ps_get_pitem_composer();
  }


  function ps_get_pitem_title(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->title;
  }

  function ps_pitem_title(){
    echo ps_get_pitem_title();
  }


  function ps_get_pitem_artist(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->artist;
  }
  function ps_pitem_artist(){
    echo ps_get_pitem_artist();
  }

  function ps_get_pitem_album(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->album;
  }

  function ps_pitem_album(){
    echo ps_get_pitem_album();
  }
  
  function ps_get_pitem_label(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->label;
  }

  function ps_pitem_label(){
    echo ps_get_pitem_label();
  }

  function ps_get_pitem_release_year(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->release_year;
  }

  function ps_pitem_release_year(){
    echo ps_get_pitem_release_year();
  }

  function ps_get_pitem_asin(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->asin;
  }

  function ps_pitem_asin(){
    echo ps_get_pitem_asin();
  }

  function ps_get_pitem_notes(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->notes;
  }

  function ps_pitem_notes(){
    echo ps_get_pitem_notes();
  }

  function ps_get_pitem_start_time($format = ''){
    global $ps_query;
    if(empty($format))$format = "h:i a";
    return empty($ps_query['playlist_item']) ? 'N/A' : date("h:i a", strtotime($ps_query['playlist_item']->start_time));
  }

  function ps_pitem_start_time($format = ''){
    echo ps_get_pitem_start_time($format = '');
  }

  function ps_get_pitem_duration($format=''){
    global $ps_query;
    if( empty($ps_query['playlist_item']) ){
      return 'N/A';
    }else{
      if(empty($format))$format = "%02s:%02s";
      $minutes = floor($ps_query['playlist_item']->duration / 60.0);
      $seconds= $ps_query['playlist_item']->duration - $minutes*60;
      return sprintf($format, $minutes, $seconds);
    }
  }

  function ps_pitem_duration($format = ''){
    echo ps_get_pitem_duration($format);
  }

  function ps_get_pitem_label_id(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? 'N/A' : $ps_query['playlist_item']->label_id;
  }

  function ps_pitem_label_id(){
    echo ps_get_pitem_label_id();
  }

  function ps_get_current_playlist_url(){
    global $ps_query;
    return empty($ps_query['playlist_item']) ? '' : ps_single_day_url($ps_query['schedule_name'], time()) . '#' . $ps_query['playlist_url_hash'];
  }
  function ps_current_playlist_url(){
    global $ps_query;
    echo empty($ps_query['playlist_item']) ? '' : '<a href="' . ps_get_current_playlist_url() . '">See playlist</a>';
  }
/*
  $funcs=get_defined_functions();
  echo "\n";
  foreach($funcs['user'] as $f){
    if(preg_match("/get/", $f) == 0 ){
      echo $f . "()\n";
    }
  }
 */
}
?>
