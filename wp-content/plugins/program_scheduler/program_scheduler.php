<?php
/* 
Plugin Name: Program Scheduler
Plugin URI: http://wfiu.org
Version: 2.0
Description: Program Scheduler for WFIU
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/

/*Classes:

Program scheduler: Manages table updates and info retrieval. 
It prints out the schedule in different formats:
1. A-Z list of all the programs
2. Weekly grid layout
3. Daily program list with links to archives, podcast, and playlist


METHODS
-printProgramList()
	-getPrograms(syndicated)
-printWeeklyGrid()
	-For each day, starting Monday, do:
		
-printDailyList()

-getPrograms(type): retrieves programs of type 'type', as well as the airtime (for airtimes with airtime_date_end > today's date)



-Classical music hour
	-playlist of music

*/

global $helper_schedule, $ps_page_option_name, $ps_default_schedule_option_name, $ps_default_playlist_blog_option_name, $ps_default_weekly_width_option_name, $ps_default_timezone_option_name, $ps_query;

$ps_page_option_name = "program_scheduler_page";
$ps_default_schedule_option_name = "program_scheduler_default_id";
$ps_default_weekly_width_option_name = "program_scheduler_default_weekly_width";
$ps_default_timezone_option_name = "program_scheduler_default_timezone";
$ps_default_playlist_blog_option_name = "program_scheduler_default_playlist_blog";

$ps_query = array();
if(get_option($ps_default_schedule_option_name)) $ps_query['use_default'] = true;

require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');
if(!class_exists('ProgramSchedule')) {
  require_once(ABSPATH.PLUGINDIR.'/program_scheduler/program_scheduler_classes.php');

  $helper_schedule = new ProgramScheduler();
  add_action('init', array(&$helper_schedule, 'register_scripts_and_styles'));
  //add_action( "admin_head", array(&$helper_schedule, 'admin_head') );
}

register_activation_hook(ABSPATH.PLUGINDIR."/program_scheduler/".basename(__FILE__), array(&$helper_schedule, 'create_tables_and_flush_rules'));
register_deactivation_hook(ABSPATH.PLUGINDIR."/program_scheduler/".basename(__FILE__), 'ps_flush_rewrite_rules');

//add_action('wp_head', 'ps_frontend_include');
add_action('wp_ajax_action_send_categories', 'php_get_categories');

add_action('wp_ajax_action_send_programs', 'global_get_programs');
add_action('wp_ajax_nopriv_action_send_programs', 'global_get_programs');

add_action('wp_ajax_action_single_day', 'global_get_single_day');
add_action('wp_ajax_nopriv_action_single_day', 'global_get_single_day');

add_action('wp_ajax_action_playlist_latest', 'global_playlist_latest');
add_action('wp_ajax_nopriv_action_playlist_latest', 'global_playlist_latest');

add_action('wp_ajax_action_program_now', 'global_program_now');
add_action('wp_ajax_nopriv_action_program_now', 'global_program_now');

if( is_admin() ){
  add_action('wp_ajax_action_receive_event', 'php_receive_event');
  add_action('wp_ajax_action_delete_event', 'php_delete_event');
}
add_action('admin_menu', 'add_pg_editor_page');

// hook ps_add_query_schedule_vars function into query_vars
add_filter('query_vars', 'ps_add_query_schedule_vars');

// hook add_rewrite_rules function into ps_add_schedule_rewrite_rules
add_filter('rewrite_rules_array', 'ps_add_schedule_rewrite_rules');

add_filter( "the_content", "ps_maybe_show_schedule");

#We flush rules at activation and when page name is change in settings


/*******************************************
 ************Frontend-only functions********
 *******************************************/
if ( !is_admin() ) { // instruction to only load if it is not the admin area
  function ps_frontend_include(){
          echo '<link rel="stylesheet" href="' . get_bloginfo('url') . '/wp-content/plugins/program_scheduler/css/datepicker.css" type="text/css" />'."\n";
          echo '<link rel="stylesheet" href="'. get_bloginfo('url') .'/wp-content/plugins/program_scheduler/css/viewer.css" type="text/css" />'."\n";
  }

  #Note: you need to use wp_head
  function ps_frontend_enqueue($in_footer = false, $force = false){
    global $post,
            $helper_schedule, $ps_page_option_name;
    $valid = false;
    if(is_singular()){
      if($post->post_name == get_option($ps_page_option_name)){
        $valid = true;
      }
    }
    
    if($valid || $force){
      $helper_schedule->frontend_enque(null, null, $in_footer);
    }else{
      $helper_schedule->frontend_enque(array('jquery'), array("ps_now", "ps_program"), $in_footer);
    }
  }
}
/************End frontend-only functions********/


/***************************************
 ************Universal functions********
 ***************************************/
function ps_single_day_url($schedule_name, $timestamp){
  global $ps_query, $ps_page_option_name;
  $schedule_page_name = get_option($ps_page_option_name);
  if($schedule_page_name){
  return get_bloginfo('url') . "/" . $schedule_page_name . "/" . ($ps_query['use_default']===true ? '' : $sname . "/") . "daily/" . urlencode(date("Y-m-d", $timestamp));
  }

  return '';
}

function ps_add_query_schedule_vars($vars) {
  array_push($vars, 'ps_sname');
  array_push($vars, 'ps_mode');
  array_push($vars, 'ps_date');
  return $vars;
}

function ps_maybe_show_schedule($content){
  global $wpdb, $post, $wp_query,
          $helper_schedule, $ps_page_option_name, $ps_default_schedule_option_name, $ps_query;

  $ps_query['in_loop'] = true;

  if(is_singular() && ($schedule_page_name = get_option($ps_page_option_name) ) ){

      if($post->post_name == $schedule_page_name){
        $sname = urldecode($wp_query->query_vars['ps_sname']);
        $ps_mode = urldecode($wp_query->query_vars['ps_mode']);
        $ps_date = urldecode($wp_query->query_vars['ps_date']);

        if($ps_mode == 'daily')$ps_mode = 'single';
        
        
        if(empty($sname)){
          $sid = get_option($ps_default_schedule_option_name);
          $sid_clause = $sid ? "WHERE ID= $sid" : '';
          $query = "SELECT name FROM " . $helper_schedule->t_s . " $sid_clause ORDER BY name LIMIT 1;";

          $scheduleRow = $wpdb->get_results($query);
          if(sizeof($scheduleRow) > 0) $sname = $scheduleRow[0]->name;
        }

        if(!empty($sname)){
          $_GET['mode'] = $ps_mode;
          $_GET['start_date'] = $ps_date;
          $_GET['schedule_name'] = $sname;
          $ps_query['schedule_name'] = $_GET['schedule_name'];
          $ps_query['mode'] = $_GET['mode'];

          $scheduleObj = ProgramScheduler::find_by_name($sname);
          $ps_query['schedule_id'] =  $scheduleObj->id;
          include(dirname(__FILE__) . "/frontend/schedule.php");
        }
      }
  }
  
  return $content;
}

if(!function_exists('the_schedule') ){
	function the_schedule($schedule_name, $mode='weekly', $echo = true){
                $ps_query['program'] = null;
                $ps_query['playlist_item'] = null;

		$_GET['schedule_name'] = $schedule_name;
                #echo "<b>".$mode."</b>";
		$_GET['mode'] = $mode;
                $_GET['echo'] = $echo ? "1" : "0";
                if($mode=='all'){
                  include(dirname(__FILE__).'/frontend/all.php');
                }else{
                  include(dirname(__FILE__).'/schedule_viewer.php');
                }
	}
}


function php_get_categories(){
	if(isset($_POST['schedule_name']) ){
		$sname = $_POST['schedule_name'];
		//echo $sname;
		$scheduleObject = ProgramScheduler::find_by_name($sname);
		if($scheduleObject && $scheduleObject->is_valid()){//if not null
			unset($_POST['schedule_name']);
			$scheduleObject->php_get_categories();
		}else{
			echo "alert('Invalid schedule: $sname');";	
		}
	}
        die();
}


function global_get_programs($single=false){
        if($_POST['single'] == '1'){
          $single = true;
        }
	if(isset($_POST['schedule_name']) ){
		$sname = $_POST['schedule_name'];
		//echo $sname;
		$scheduleObj = ProgramScheduler::find_by_name($sname);
		if($scheduleObj && $scheduleObj->is_valid()){//if not null
                        $_GET['schedule_name'] = $_POST['schedule_name'];
			unset($_POST['schedule_name']);
			if($single){
                          $_GET['mode'] = 'popup';
                          include(dirname(__FILE__).'/schedule_viewer.php');
			}else{
				$scheduleObj->php_get_programs();
			}
		}else{
			echo "alert('Invalid schedule: $sname');";
		}
	}
        die();
}

function global_get_single_day(){
  global $wpdb;
  if(isset($_POST['start_date']) ){
    $_GET['mode'] = 'single';
    $_GET['start_date'] = $_POST['start_date'];
    #$names = $wpdb->get_col("SELECT name FROM ps_stations ORDER BY name;");
    #the_schedule(implode(",", $names), 'single');
    the_schedule($_POST['schedule_name'], 'single');
    #include(dirname(__FILE__).'/schedule_viewer.php');
  }
  die();
}

function global_playlist_latest(){
  global $wpdb;
  $schedule_name = $_POST['schedule_name'];
  the_schedule($schedule_name, 'playlist-item-now-ajax');
  
  die();
}

function global_program_now(){
  global $wpdb;
  $schedule_name = $_POST['schedule_name'];
  the_schedule($schedule_name, 'now-ajax');

  die();
}
/************END Universal functions ********/

/************************************
 ************Admin functions ********
 ************************************/
if( is_admin() ){

  function ps_flush_rewrite_rules(){
    global $wp_rewrite;
    $wp_rewrite->flush_rules();
  }

  #Rewrite rules if option 'program_schduler_page' is set and we're not in the process of deactivating the plugin
  function ps_add_schedule_rewrite_rules($rules) {
      global $ps_page_option_name;
      $page_name = get_option($ps_page_option_name);
      if($page_name && ($_GET['action'] != 'deactivate')){
          $newrules = array('(' . $page_name . ')/([^/]*)?/?(weekly|daily)/?([\d\-]*)$' => 'index.php?pagename=$matches[1]&ps_sname=$matches[2]&ps_mode=$matches[3]&ps_date=$matches[4]');
          #$newrules['(project)/(\d*)$'] = 'index.php?pagename=$matches[1]&id=$matches[2]';
          $rules = $newrules + $rules;
      }
      return $rules;
  }

  function php_receive_event(){
          if(isset($_POST['schedule_name']) ){
                  $sname = $_POST['schedule_name'];
                  $scheduleObject = ProgramScheduler::find_by_name($sname);
                  if($scheduleObject && $scheduleObject->is_valid()){//if not null
                          unset($_POST['schedule_name']);
                          $scheduleObject->php_receive_event();
                  }else{
                          echo "alert('Invalid schedule: $sname');";
                  }
          }
          die();
  }

  function php_delete_event(){
          if(isset($_POST['schedule_name']) ){
                  $sname = $_POST['schedule_name'];
                  $scheduleObject = ProgramScheduler::find_by_name($sname);
                  if($scheduleObject && $scheduleObject->is_valid()){//if not null
                          unset($_POST['schedule_name']);
                          $scheduleObject->php_delete_event();
                  }else{
                          echo "alert('Invalid schedule: $sname');";
                  }
          }
          die();
  }

  function add_pg_editor_page(){
	global $helper_schedule;
	if(current_user_can('manage_options')){
		add_menu_page('Schedules', 'Schedules', "manage_options", ABSPATH.PLUGINDIR.'/program_scheduler/admin_manage_stations.php');
		//$schedules = get_how_option('schedules');
                //
                #add pages that are only accessible from Schedules page
		add_submenu_page(__FILE__, 'Manage', 'Manage', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_editor.php');
		add_submenu_page(__FILE__, 'View', 'View', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_viewer.php');
		/*if($schedules){
			add_submenu_page(__FILE__, 'Manage Program Schedule', 'Manage', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_editor.php');
			add_submenu_page(__FILE__, 'View Program Schedule', 'View', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_viewer.php');
			//add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Scheduler', 'Scheduler', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_editor.php');
		}*/
                add_action( "admin_print_scripts", array(&$helper_schedule, 'admin_print_scripts') );
	}
  }
}
/************END admin functions ********/

/************************************
/************Deprecated functions ********
 ************************************/

/*function add_how_option($name, $val){
	if(function_exists('add_site_option')){
		add_site_option('schedules', $val);
	}else{
		add_option('schedules', $val);
	}
}
*/
/*
function update_how_option($name, $val){
		if(function_exists('add_site_option')){
		update_site_option('schedules', $val);
	}else{
		update_option('schedules', $val);
	}
}
*/
/*
if(!function_exists('get_how_option') ){
	function get_how_option($name){
		if(function_exists('get_site_option')){
			return get_site_option($name);
		}else{
			//echo 'getting option';
			return get_option($name);
		}
	}
}

function valid_schedule_name($sname){
		$schedules = get_how_option('schedules');
		$schedules = $schedules ? unserialize($schedules) : $schedules;
		if(is_array($schedules)){
			if(in_array($sname, $schedules)){
				return true;
			}
		}
		return false;
}


 
*/

?>
