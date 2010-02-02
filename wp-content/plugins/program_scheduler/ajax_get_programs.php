<?php
if(!class_exists('ProgramSchedule')) {
	require_once(dirname(__FILE__).'/../../../wp-load.php');
	require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');
	require_once(ABSPATH.PLUGINDIR.'/program_scheduler/program_scheduler_classes.php');	
	if($_POST['action'] == 'action_send_programs'){
		php_get_programs();
	}else if($_POST['action'] == 'action_single_program'){
		php_get_programs(true);
	}else if($_POST['action'] == 'action_single_day'){	
		php_get_single_day();
	}
}

function php_get_programs($single=false){
	if(isset($_POST['schedule_name']) ){
		$sname = $_POST['schedule_name'];
		//echo $sname;
		$scheduleObject = new ProgramScheduler($sname);
		if($scheduleObject->is_valid()){
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

function php_get_single_day(){
	if(isset($_POST['start_date']) ){
		$_GET['schedule_name'] = '';
		$_GET['mode'] = 'single';
		$_GET['start_date'] = $_POST['start_date'];
		include(dirname(__FILE__).'/schedule_viewer.php');
	}
}


?>
