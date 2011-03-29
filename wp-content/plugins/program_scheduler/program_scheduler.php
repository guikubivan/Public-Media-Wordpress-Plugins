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
  add_action('init', array(&$helper_schedule, 'register_js_scripts'));
  add_action( "admin_print_scripts", array(&$helper_schedule, 'admin_print_scripts') );
  //add_action( "admin_head", array(&$helper_schedule, 'admin_head') );
}

register_activation_hook(ABSPATH.PLUGINDIR."/program_scheduler/".basename(__FILE__), array(&$helper_schedule, 'create_tables'));

function add_how_option($name, $val){
	if(function_exists('add_site_option')){
		add_site_option('schedules', $val);
	}else{
		add_option('schedules', $val);
	}
}

function update_how_option($name, $val){
		if(function_exists('add_site_option')){
		update_site_option('schedules', $val);
	}else{
		update_option('schedules', $val);
	}
}


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


function configure_channels(){
        global $helper_schedule, $wpdb;
	echo "<div class='wrap'>";
        
        $sname = $_POST['schedule_name'];
	if(!empty($sname)){
          $schedules = $wpdb->get_results("SELECT * FROM ".$helper_schedule->t_s." ORDER BY name");

          $scheduleObject = new ProgramScheduler($sname, !empty($_POST['show_playlist']) );
          if($scheduleObject->save_station()){
            echo "<div class='updated fade'>Schedule " . $sname . " added.</div>";
          }else{
            echo "<div class='updated fade'>Error adding " . $sname . ". Make sure to use unique names for schedule.</div>";
          }
	}else if(isset($_POST['schedule_index'])){


          $id = $_POST['schedule_index'];
          $station = ProgramScheduler::find($id);
          if($_POST['delete']){
            echo "Are you sure you want to delete schedule <b>" . $station->schedule_name . "</b>?<br />
                    This will delete all events related to this schedule.<br />";
            echo "<form action='' method='post'>
                    <input type='hidden' name='schedule_index' value='$id'/>
                    <input type='submit' name='confirm_delete' value='Yes, I am sure'/>
                    </form>";
            return;
          }else if($_POST['confirm_delete']){
            if($station->delete_station()){
              echo "<div class='updated fade'>Schedule $station->schedule_name deleted</div>";
            }else{
              echo "<div class='updated fade'>Error: Schedule $station->schedule_name could not be deleted</div>";
            }
            
          }
	}


        $query = "SELECT * FROM ".$helper_schedule->t_s." ORDER BY name";
        $schedules = $wpdb->get_results($query);
	
	if(sizeof($schedules)){
		echo "<h2>Schedules</h2>";
		echo "<form action='' method='post'><table style>";
		foreach($schedules as $i => $station){
			echo "<tr>";
			echo "<td>";
			echo "<input type='radio' id='schedule_index_$i' name='schedule_index' value='$station->ID' />";
			echo "</td>";
			echo "<td><label for='schedule_index_$i'>";
			echo $station->name;
			echo "</label></td>";
			echo "<td style='padding: 10px;'>";
			echo "<a href='?page=program_scheduler/schedule_editor.php&schedule_name=".urlencode($station->name)."' >Manage</a> ";
			echo " View &raquo; ";
			echo " <a href='?page=program_scheduler/schedule_viewer.php&schedule_name=".urlencode($station->name)."'>week</a>";
			echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=single&schedule_name=".urlencode($station->name)."'>single</a>";
			echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=now&schedule_name=".urlencode($station->name)."'>now</a>";
			echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=listing&schedule_name=".urlencode($station->name)."'>listing</a>";
			echo "</td>";
			echo "</tr>";
		}
		echo "</table>";
		echo "<input type='submit' name='delete' value='Delete' />";
		echo "</form>";
		echo "<hr />";
	}
	

	echo "<h2>Add schedules</h2>";

	echo "<form action='' method='POST' />";
	echo "<input type='text' name='schedule_name' value='Enter schedule name' onfocus=\"if(this.defaultValue==this.value) this.value = '';\" />";
        echo "<br/><input type='checkbox' id='show_playlist' name='show_playlist' /> <label for='show_playlist'>Show playlist (if available)</label>";
	echo "<br/><input type='submit' value='Create Schedule' />";
	echo "</form>";
	echo "</div>";

}

function ps_frontend_include(){
	echo '<link rel="stylesheet" href="' . get_bloginfo('url') . '/wp-content/plugins/program_scheduler/datepicker.css" type="text/css" />'."\n";
	echo '<link rel="stylesheet" href="'. get_bloginfo('url') .'/wp-content/plugins/program_scheduler/viewer.css" type="text/css" />'."\n";
}	

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

if(!function_exists('the_schedule') ){
	function the_schedule($schedule_name, $mode='weekly'){
		$_GET['schedule_name'] = $schedule_name;
		$_GET['mode'] = $mode;
		include(dirname(__FILE__).'/schedule_viewer.php'); 
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
}

function global_get_single_day(){
	if(isset($_POST['start_date']) ){
		$_GET['schedule_name'] = '';
		$_GET['mode'] = 'single';
		$_GET['start_date'] = $_POST['start_date'];
		include(dirname(__FILE__).'/schedule_viewer.php');
	}
}

add_action('admin_menu', 'add_pg_editor_page');
//add_action( "admin_print_scripts-program_scheduler/schedule_editor.php", 'schedule_editor_header');

function schedule_editor_header(){
		echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/wp-content/plugins/program_scheduler/editor.css" type="text/css" />'."\n";
}

function add_pg_editor_page(){
	//global $schedule_class;
	if(current_user_can('edit_plugins')){
		add_menu_page('Schedules', 'Schedules', 7, __FILE__, 'configure_channels');
		$schedules = get_how_option('schedules');
		add_submenu_page(__FILE__, 'Manage', 'Manage', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_editor.php');
		add_submenu_page(__FILE__, 'View', 'View', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_viewer.php');
		/*if($schedules){
			add_submenu_page(__FILE__, 'Manage Program Schedule', 'Manage', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_editor.php');
			add_submenu_page(__FILE__, 'View Program Schedule', 'View', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_viewer.php');
			//add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Scheduler', 'Scheduler', 7, ABSPATH.PLUGINDIR.'/program_scheduler/schedule_editor.php');
		}*/
	}
}
?>
