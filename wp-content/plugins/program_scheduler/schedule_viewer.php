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

$ps_query = array();
$ps_query['schedule_name'] = $_GET['schedule_name'];
$ps_query['mode'] = $_GET['mode'];
$ps_query['start_date'] = $_GET['start_date'];
$ps_query['echo'] = empty($_GET['echo']) ? true : ($_GET['echo'] == "1" ? true : false);

if(!function_exists('ps_program_name') ){
  function ps_program_name(){
    global $ps_query;
    echo empty($ps_query['program']) ? 'N/A' : $ps_query['program']->name;
  }

  function ps_start_time($format){
    global $ps_query;
    if(empty($format))$format = "h:i:s A";
    echo empty($ps_query['program']) ? 'N/A' : date($format, strtotime($ps_query['program']->start_date));
  }

  function ps_end_time($format){
    global $ps_query;
    if(empty($format))$format = "h:i:s A";
    echo empty($ps_query['program']) ? 'N/A' : date($format, strtotime($ps_query['program']->start_date));
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

/* Deprecated */
/*
if(!function_exists('print_program_row') ){
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


if(isset($_GET['schedule_name']) ){
	$sname = $_GET['schedule_name'];
	$scheduleObj = ProgramScheduler::find_by_name($sname);

	if(!is_null($scheduleObj) && !$scheduleObj->is_valid()){
          echo "<strong>Schedule $sname does not exist.</strong>";
	}else if(is_null($scheduleObj)){
          $scheduleObj = new ProgramScheduler();
        }
}else{
	die("<b>No schedule selected</b>");
}

if(current_user_can('edit_plugins')){
/*
<div class='schedule_menu' style='text-align:left;'>
<a href='?page=<?php echo $_GET['page']; ?>&mode=single&schedule_name=<?php echo $_GET['schedule_name']; ?>' >Single day</a> 
<a href='?page=<?php echo $_GET['page']; ?>&mode=listing&schedule_name=<?php echo $_GET['schedule_name']; ?>' >Listing</a> 
<a href='?page=<?php echo $_GET['page']; ?>&mode=weekly&schedule_name=<?php echo $_GET['schedule_name']; ?>' >Weekly</a> 
<a href='?page=<?php echo $_GET['page']; ?>&mode=now&schedule_name=<?php echo $_GET['schedule_name']; ?>' >Now Playing</a> 
</div>
*/
}

$start_date = strtotime($_GET['start_date']) ? strtotime($_GET['start_date']) : strtotime('today');

$max_cell_height = 800;

$eventHelper = new SchedulerEvent('', '', '');

if($_GET[mode] == 'single'){
  if(empty($sname)){
    echo "No schedule given";
    return;
  }

  $sname = explode(",", $sname);
  foreach($sname as $key=>$value){
    $sname[$key] = trim($sname[$key]);
  }
  include(dirname(__FILE__).'/frontend/single_day/default.php');
}else if($_GET[mode] == 'listing'){
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
}else if($_GET[mode] == 'now'){
	$start_date = time();
	$end_date = $start_date;
	//echo "end date: " .JFormatDateTime($end_date).
	$programs = $scheduleObj->php_get_programs($start_date, $end_date, "(weekdays!='' OR WEEKDAY(start_date)=WEEKDAY('" . formatdatetime($start_date) . "') )");

	//echo "<table style='width: 100%;clear:both;'>";
	$noPrograms= true;

	foreach($programs as $program){
		if($scheduleObj->program_tablerow($program, $start_date, true, $max_cell_height, false, true)){
                        $ps_query['program'] = $program;#global
			$noPrograms = false;
                        if($ps_query['echo']){
                          include(dirname(__FILE__) . "/frontend/now/default.php");
                        }
			break;
		}
	}
	if($noPrograms && $ps_query['echo']){
		echo "N/A";
	}
	//echo "</table>";

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
                #only load it once for all schedules
		require_once(dirname(__FILE__).'/load_week_schedule_all_js.php'); 
	}
}	



?>
