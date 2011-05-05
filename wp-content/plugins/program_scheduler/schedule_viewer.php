<?php
#globals used
global $start_date;
global $eventHelper;
global $scheduleObj;
global $ps_query;
global $sname; #single day

#Find your timezone by city at http://us.php.net/manual/en/timezones.america.php
#date_default_timezone_set("America/New_York");#Eastern Time
date_default_timezone_set("America/Denver");#Mountain Time

$ps_query['schedule_name'] = $_GET['schedule_name'];
$ps_query['mode'] = $_GET['mode'];
$ps_query['start_date'] = $_GET['start_date'];
$ps_query['echo'] = empty($_GET['echo']) ? true : ($_GET['echo'] == "1" ? true : false);

/* Deprecated */
/*if(!function_exists('print_program_row') ){
	function print_program_row ($row){
		echo "<li><div class='program_name'>";
		echo $row[url] ? "<a href='$row[url]' >$row[name] </a>" : $row['name'];
		echo "</div>";
		echo $row[description] ? "<div> $row[description] </div>" : '';
		if($row['hd1'] || $row['hd2']){
			echo "Airdates & Times: ";
		}
		
		if($row['hd1']){
			echo "<ul class='air_times'>HD1";
			echo $row['hd1'];
			echo "</ul>";
		}
		if($row['hd2']){
			echo "<ul class='air_times'>HD2";
			echo $row['hd2'];
			echo "</ul>";
		}
		echo $row[url] ? "<a href='$row[url]' > Website link &raquo; </a>" : '';
		echo "</li>";
		
	}
}
*/


if(isset($_GET['schedule_name']) && !isset($scheduleObj)){
	$sname = $_GET['schedule_name'];
	$scheduleObj = ProgramScheduler::find_by_name($sname);

	if(!is_null($scheduleObj) && !$scheduleObj->is_valid()){
          echo "<strong>Schedule $sname does not exist.</strong>";
	}else if(is_null($scheduleObj)){
          $scheduleObj = new ProgramScheduler();
        }
}else if(!isset($_GET['schedule_name'])){
	die("<b>No schedule selected</b>");
}

$start_date = strtotime($_GET['start_date']) ? strtotime($_GET['start_date']) : strtotime('today');

$max_cell_height = 800;

$eventHelper = new SchedulerEvent('', '', '');


if($_GET['mode'] == 'program-ajax'){
  $program_id = $_POST['program_id'];
  include(dirname(__FILE__) . "/frontend/program/ajax.php");

}else if($_GET['mode'] == 'single'){
  
  if(empty($sname)){
    echo "No schedule given";
    return;
  }

  include(dirname(__FILE__).'/frontend/single_day/default.php');
}else if($_GET['mode'] == 'listing'){
        #uncomment to list all programs in all schedules
	#$airtimes = $scheduleObj->get_listing('');
        $airtimes = $scheduleObj->get_listing('', $scheduleObj->id);
        $schedules = array();
        foreach($airtimes as $item){
          if(!in_array($item->schedule_name, $schedules)) $schedules[] = $item->schedule_name;
        }
	echo "<div style=''>";

	$noPrograms= sizeof($airtimes)> 0 ? false : true;
	$cid = -1;
	$cs = "";
	$schedule_name = '';
	
	$rows = $scheduleObj->parse_program_times($airtimes, $eventHelper);
	if($rows !== false){
		echo "<ul class='listing_list'>";
		foreach($rows as $row){
			echo "<li>";
			echo "<a style='cursor: auto' href=\"#".sanitize_title($row['name'])."\">".$row['name']."</a>";
			echo $scheduleObj->format_single_row($schedules, $row, 'listing_item', false);
			echo "</li>";
		}
		echo "</ul>";
	}else{
		echo "No programs exist yet.";
	}

	echo "</div>";
}else if($_GET['mode'] == 'now'){
	$start_date = time();
        $program = $scheduleObj->get_program_playing_at($start_date);
        if(!is_null($program)){
          $ps_query['program'] = $program;#global
          require_once(dirname(__FILE__) . "/hooks/program.php");
          if($ps_query['echo']){
            include(dirname(__FILE__) . "/frontend/now/default.php");
          }
	}else{
          if($ps_query['echo']){
		echo "N/A";
          }
        }

}else{
	include(dirname(__FILE__).'/schedule_editor.php');
	if($cats = $scheduleObj->get_categories()){
		echo "<div style='width: 100%; text-align: left; margin-top: 5px;padding: 10px; font-size: 13px'>";
		foreach($cats as $cat){
			echo "<span style='border: 1px solid grey; margin-right: 2px; padding-right: 8px; background-color:" . $cat->category_color . "'>&nbsp; </span> <span style='margin-right: 13px;'>" .  $cat->category_name  . '</span>';
		}
		echo "</div>";
		
	}
	
	if(!preg_match("/schedule_viewer\.php/", $_SERVER['REQUEST_URI']) ){
          if(!empty($_GET['schedule_name'])){
            require_once(dirname(__FILE__).'/load_week_schedule_js.php');
          }
	}
}	



?>
