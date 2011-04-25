<?php
/* 
Plugin Name: Program Scheduler
Plugin URI: http://wfiu.org
Version: 1.0
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

require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');
if(!class_exists('ProgramSchedule')) {
  require_once(ABSPATH.PLUGINDIR.'/program_scheduler/program_scheduler_classes.php');
  global $helper_schedule;
  $helper_schedule = new ProgramScheduler();
  add_action('init', array(&$helper_schedule, 'register_scripts_and_styles'));
  //add_action( "admin_head", array(&$helper_schedule, 'admin_head') );
}

register_activation_hook(ABSPATH.PLUGINDIR."/program_scheduler/".basename(__FILE__), array(&$helper_schedule, 'create_tables'));

//add_action('wp_head', 'ps_frontend_include');
add_action('wp_ajax_action_send_categories', 'php_get_categories');

add_action('wp_ajax_action_send_programs', 'global_get_programs');
add_action('wp_ajax_nopriv_action_send_programs', 'global_get_programs');

add_action('wp_ajax_action_single_day', 'global_get_single_day');
add_action('wp_ajax_nopriv_action_single_day', 'global_get_single_day');

if( is_admin() ){
  add_action('wp_ajax_action_receive_event', 'php_receive_event');
  add_action('wp_ajax_action_delete_event', 'php_delete_event');
}
add_action('admin_menu', 'add_pg_editor_page');

/*******************************************
 ************Frontend-only functions********
 *******************************************/
if ( !is_admin() ) { // instruction to only load if it is not the admin area
  function ps_frontend_include(){
          echo '<link rel="stylesheet" href="' . get_bloginfo('url') . '/wp-content/plugins/program_scheduler/css/datepicker.css" type="text/css" />'."\n";
          echo '<link rel="stylesheet" href="'. get_bloginfo('url') .'/wp-content/plugins/program_scheduler/css/viewer.css" type="text/css" />'."\n";
  }

  #Note: you need to use wp_head
  function ps_frontend_enqueue($in_footer = false){
    global $helper_schedule;
    $helper_schedule->frontend_enque($in_footer);
  }
}
/************End frontend-only functions********/


/***************************************
 ************Universal functions********
 ***************************************/
if(!function_exists('the_schedule') ){
	function the_schedule($schedule_name, $mode='weekly', $echo = true){
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
		$scheduleObject = ProgramScheduler::find_by_name($sname);
		if($scheduleObject && $scheduleObject->is_valid()){//if not null
			unset($_POST['schedule_name']);
			if($single){
				$scheduleObject->php_get_program($_POST['program_id']);
			}else{
				$scheduleObject->php_get_programs();
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

/************END Universal functions ********/

/************************************
 ************Admin functions ********
 ************************************/
if( is_admin() ){

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
	if(current_user_can('edit_plugins')){
		add_menu_page('Schedules', 'Schedules', "edit_plugins", ABSPATH.PLUGINDIR.'/program_scheduler/admin_manage_stations.php');
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
