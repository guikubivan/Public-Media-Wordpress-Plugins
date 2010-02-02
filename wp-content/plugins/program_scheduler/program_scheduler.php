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
require_once(ABSPATH.PLUGINDIR.'/program_scheduler/ajax_get_programs.php');

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
	echo "<div class='wrap'>";
	$schedules = get_how_option('schedules');
	//print_r($schedules);
	//echo $schedules . "<br />";
	
	$schedules = $schedules ? unserialize($schedules) : $schedules;
	//print_r($schedules);
	//echo $schedules . "<br />";

	if($sname = $_POST['schedule_name']){
		$new_schedules = $schedules;
		if(!is_array($new_schedules) ) $new_schedules = array();
		if($schedules===false){
			//echo 'adding<br />';
			$scheduleObject = new ProgramScheduler($sname);
			if($scheduleObject->create_tables()){
				$new_schedules = serialize(array($sname));
				add_how_option('schedules', $new_schedules);
				echo "<div class='updated fade'>Schedule " . $sname . " added and tables created</div>";
			}else{
				echo "<div class='updated fade'>Error adding " . $sname . ". Make sure to use unique names for schedule.</div>";
			}
		}else{
			$scheduleObject = new ProgramScheduler($sname);
			if($scheduleObject->create_tables()){
				$new_schedules[] = $sname;
				update_how_option('schedules', serialize($new_schedules));
				echo "<div class='updated fade'>Schedule " . $sname . " added and tables created</div>";
			}else{
				echo "<div class='updated fade'>Error adding " . $sname . ". Make sure to use unique names for schedule.</div>";
			}
			
		}
	}else if(isset($_POST['schedule_index'])){
		$index = $_POST['schedule_index'];
		if($_POST['delete']){
			echo "Are you <b>sure</b> you want to delete schedule <b>" . $schedules[$index] . "</b>?<br />
				This will delete all events and tables related to this schedule.<br />";
			echo "<form action='' method='post'>
				<input type='hidden' name='schedule_index' value='$index'/>
				<input type='submit' name='confirm_delete' value='Yes, I am sure'/>
				</form>";
			return;
		}else if($_POST['confirm_delete']){
			$sname = $schedules[$index];
			$scheduleObject = new ProgramScheduler($sname);
			$scheduleObject->delete_tables();
			unset($schedules[$index]);
			if(sizeof($schedules)>0){
				update_how_option('schedules', serialize($schedules));
			}else{
				update_how_option('schedules', '');
			}
			echo "<div class='updated fade'>Schedule " . $sname . " deleted</div>";
		}
	}

	$schedules = get_how_option('schedules');
	$schedules = unserialize($schedules);
	//echo $schedules . "<br />";

	
	if($schedules){
		echo "<h2>Schedules</h2>";
		echo "<form action='' method='post'><table style>";
		asort($schedules);
		foreach($schedules as $i => $s){
			echo "<tr>";
			echo "<td>";
			echo "<input type='radio' name='schedule_index' value='$i' />";
			echo "</td>";
			echo "<td>";
			echo $s;
			echo "</td>";
			echo "<td style='padding: 10px;'>";
			echo "<a href='?page=program_scheduler/schedule_editor.php&schedule_name=".urlencode($s)."' >Manage</a> ";
			echo " View &raquo; ";
			echo " <a href='?page=program_scheduler/schedule_viewer.php&schedule_name=".urlencode($s)."'>week</a>";
			echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=single&schedule_name=".urlencode($s)."'>single</a>";
			echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=now&schedule_name=".urlencode($s)."'>now</a>";
			echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=listing&schedule_name=".urlencode($s)."'>listing</a>";
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
	echo "<input type='submit' value='Create Schedule' />";
	echo "</form>";
	echo "</div>";

}

function ps_frontend_include(){
	echo '<link rel="stylesheet" href="' . get_bloginfo('url') . '/wp-content/plugins/program_scheduler/datepicker.css" type="text/css" />'."\n";
	echo '<link rel="stylesheet" href="'. get_bloginfo('url') .'/wp-content/plugins/program_scheduler/viewer.css" type="text/css" />'."\n";
}	

				
if(!class_exists('ProgramSchedule')) {
	require_once(ABSPATH.PLUGINDIR.'/program_scheduler/program_scheduler_classes.php');
	$helper_schedule = new ProgramScheduler();
	add_action('init', array(&$helper_schedule, 'register_js_scripts'));
	add_action( "admin_print_scripts", array(&$helper_schedule, 'plugin_head') );
}

//add_action('wp_head', 'ps_frontend_include');
add_action('wp_ajax_action_send_categories', 'php_get_categories');

add_action('wp_ajax_action_send_programs', 'php_get_programs');
add_action('wp_ajax_action_receive_event', 'php_receive_event');
add_action('wp_ajax_action_delete_event', 'php_delete_event');

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
		$scheduleObject = new ProgramScheduler($sname);
		if($scheduleObject->is_valid()){
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
		$scheduleObject = new ProgramScheduler($sname);
		if($scheduleObject->is_valid()){
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
		$scheduleObject = new ProgramScheduler($sname);
		if($scheduleObject->is_valid()){
			unset($_POST['schedule_name']);
			$scheduleObject->php_delete_event();
		}else{
			echo "alert('Invalid schedule: $sname');";
		}
	}
}




//add_action('activate_program_scheduler/program_scheduler.php', array(&$schedule_class, 'create_tables'));


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
