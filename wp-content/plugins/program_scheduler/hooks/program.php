<?php

if(!function_exists('ps_program_name') ){
  function ps_program_has_url(){
    global $ps_query;
    return empty($ps_query['program']->url) ? false : true;
  }

  function ps_program_has_photo_url(){
    global $ps_query;
    return empty($ps_query['program']->host_photo_url) ? false : true;
  }

  function ps_program_show_playlist(){
    global $ps_query;
    return $ps_query['program']->show_playlist == "1" ? true : false;
  }

  function ps_program_name(){
    global $ps_query;
    echo empty($ps_query['program']) ? 'N/A' : $ps_query['program']->name;
  }

  function ps_start_time($format = ''){
    global $ps_query;
    if(empty($format))$format = "g:i A";
    echo empty($ps_query['program']) ? 'N/A' : date($format, strtotime($ps_query['program']->start_date));
  }

  function ps_end_time($format=''){
    global $ps_query;
    if(empty($format))$format = "g:i A";
    echo empty($ps_query['program']) ? 'N/A' : date($format, strtotime($ps_query['program']->end_date));
  }

  function ps_program_info_link(){
    global $ps_query;
    echo empty($ps_query['program']) ? '' : $ps_query['program']->url;
  }

  function ps_program_description(){
    global $ps_query;
    echo empty($ps_query['program']) ? '' : $ps_query['program']->description;
  }

  function ps_host_name(){
    global $ps_query;
    echo empty($ps_query['program']) ? '' : $ps_query['program']->host_name;
  }

  function ps_host_bio(){
    global $ps_query;
    echo empty($ps_query['program']) ? '' : $ps_query['program']->host_bio;
  }

  function ps_host_photo_url(){
    global $ps_query;
    echo empty($ps_query['program']) ? '' : $ps_query['program']->host_photo_url;
  }

  function ps_host_bio_link(){
    global $ps_query;
    echo empty($ps_query['program']) ? '' : $ps_query['program']->host_bio_link;
  }
}

?>
