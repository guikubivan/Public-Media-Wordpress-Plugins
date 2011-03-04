<?php
if(!class_exists('ProgramSchedule')) {
	//require_once(dirname(__FILE__).'/../../wp-load.php');
	require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');
	require_once(ABSPATH.PLUGINDIR.'/program_scheduler/program_scheduler_classes.php');	
	require_once(ABSPATH.PLUGINDIR.'/program_scheduler/ajax_get_programs.php');
}


if($_POST['action'] == 'action_send_programs'){
	php_get_programs();
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

if(!function_exists('the_schedule') ){
	function the_schedule($schedule_name, $mode='weekly'){
		$_GET['schedule_name'] = $schedule_name;
		$_GET['mode'] = $mode;
		include(ABSPATH.PLUGINDIR.'/program_scheduler/schedule_viewer.php'); 
	}
}
?>
