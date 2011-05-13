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

  function ps_get_program_name(){
    global $ps_query;
    return empty($ps_query['program']) ? 'N/A' : $ps_query['program']->name;
  }

  function ps_program_name(){
    echo ps_get_program_name();
  }

  function ps_get_start_time($format = ''){
    global $ps_query;
    if(empty($format))$format = "g:i A";
    return empty($ps_query['program']) ? 'N/A' : date($format, strtotime($ps_query['program']->start_date));
  }

  function ps_start_time($format = ''){
    echo ps_get_start_time($format);
  }

  function ps_get_end_time($format=''){
    global $ps_query;
    if(empty($format))$format = "g:i A";
    return empty($ps_query['program']) ? 'N/A' : date($format, strtotime($ps_query['program']->end_date));
  }

  function ps_end_time($format=''){
    echo ps_get_end_time($format);
  }

  function ps_get_program_info_link(){
    global $ps_query;
    return empty($ps_query['program']) ? '' : $ps_query['program']->url;
  }

  function ps_program_info_link(){
    echo ps_get_program_info_link();
  }

  function ps_get_program_description(){
    global $ps_query;
    return empty($ps_query['program']) ? '' : $ps_query['program']->description;
  }

  function ps_program_description(){
    echo ps_get_program_description();
  }

  function ps_get_host_name(){
    global $ps_query;
    return empty($ps_query['program']) ? '' : $ps_query['program']->host_name;
  }

  function ps_host_name(){
    echo ps_get_host_name();
  }

  function ps_get_host_bio(){
    global $ps_query;
    return empty($ps_query['program']) ? '' : $ps_query['program']->host_bio;
  }

  function ps_host_bio(){
    echo ps_get_host_bio();
  }

  function ps_get_host_photo_url(){
    global $ps_query;
    return empty($ps_query['program']) ? '' : $ps_query['program']->host_photo_url;
  }

  function ps_host_photo_url(){
    echo ps_get_host_photo_url();
  }

  function ps_get_host_bio_link(){
    global $ps_query;
    return empty($ps_query['program']) ? '' : $ps_query['program']->host_bio_link;
  }
  
  function ps_host_bio_link(){
    echo ps_host_bio_link();
  }
}

?>
