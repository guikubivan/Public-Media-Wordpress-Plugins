<?php
#globals used
global $start_date;
global $eventHelper;
global $scheduleObj;
global $ps_query;
global $sname; #single day
global $ps_default_timezone_option_name;

#Change your timezone on the plugin settings
$ps_query['timezone'] = get_option($ps_default_timezone_option_name);
if(!$ps_query['timezone']) $ps_query['timezone'] = "America/New_York";
date_default_timezone_set($ps_query['timezone']);#Default is eastern Time

$_GET['schedule_name'] = rawurldecode($_GET['schedule_name']);
$ps_query['schedule_name'] = $_GET['schedule_name'];
$ps_query['mode'] = $_GET['mode'];
$ps_query['start_date'] = $_GET['start_date'];
$ps_query['echo'] = isset($_GET['echo']) ? ($_GET['echo'] == "1" ? true : false) : true;

if(isset($_GET['schedule_name']) && (!isset($scheduleObj) || ($_GET['schedule_name'] != $scheduleObj->schedule_name) ) ) {
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


if($_GET['mode'] == 'popup'){
  $program_id = $_POST['program_id'];
  include(dirname(__FILE__) . "/frontend/program/popup.php");
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
			echo $scheduleObj->format_single_row($schedules, $row, 'listing_item');
			echo "</li>";
		}
		echo "</ul>";
	}else{
		echo "No programs exist yet.";
	}

	echo "</div>";
}else if( in_array($_GET['mode'], array('now', 'next', 'prev', 'now-ajax')) ){
	$start_date = time();
        #$start_date = mktime(23,59,55);

        #echo date('l jS \of F Y h:i:s A', $start_date) . "<br/>";
        $program = $scheduleObj->get_program_playing_at($start_date);

        if(!is_null($program)){
          $start = strtotime($program->start_date);
          $end = $eventHelper->fix_end_time($start, $program->end_date);


          switch($_GET['mode']){
            case 'next':
              $day_now_seconds = date("H", $start_date)*60*60 + date("i", $start_date)*60 + date("s", $start_date);
              $day_end_seconds = date("H", $end-1)*60*60 + date("i", $end-1)*60 + date("s", $end-1);
              $different_to_next_program = $day_end_seconds- $day_now_seconds + 2;#+1+1 for cases when $end date is 23:59
              $start_date += $different_to_next_program;
              #echo date('l jS \of F Y h:i:s A', $start_date);
              $program = $scheduleObj->get_program_playing_at($start_date);
              break;
            case 'prev':
              #echo date('h:i:s A', $start);
              #echo "<br/>";
              #echo date('h:i:s A', $start_date);
              $day_now_seconds = date("H", $start_date-1)*60*60 + date("i", $start_date-1)*60 + date("s", $start_date-1);#avoid when current time is 0:0
              $day_start_seconds = date("H", $start)*60*60 + date("i", $start)*60 + date("s", $start);
              
              $different_to_prev_program = $day_now_seconds - $day_start_seconds + 3;#due to $start_date < end_date-1 in *time* query
              $start_date = $start_date - $different_to_prev_program;
              #echo "<br/>";
              #echo date('h:i:s A', $start_date);
              $program = $scheduleObj->get_program_playing_at($start_date);
              #echo "<br/>";
              break;
          }
        }
        #global, needs to be set to null so hooks don't have old info in case no program was found
        $ps_query['program'] = $program;
        require_once(dirname(__FILE__) . "/hooks/program.php");
        require_once(dirname(__FILE__) . "/hooks/playlist_item.php");

        if($_GET['mode'] == "now-ajax"){
            include(dirname(__FILE__) . "/frontend/now/default_ajax.php");
        }else{
            if(!is_null($program)){
              if($ps_query['echo']){
                include(dirname(__FILE__) . "/frontend/now/default.php");
              }
            }else{
              if($ps_query['echo']){
                    echo "N/A";
              }
            }
        }
}else if($_GET['mode'] == 'playlist-item-now-ajax'){

	$start_date = time();
        $program = $scheduleObj->get_program_playing_at($start_date);

        #global, needs to be set to null so hooks don't have old info in case no program was found
        $ps_query['program'] = $program;

        require_once(dirname(__FILE__) . "/hooks/playlist_item.php");
        if(!is_null($program)){
          $start = strtotime($program->start_date);
          $end = $eventHelper->fix_end_time($start, $program->end_date);
          $show_only_last = true;
          $ps_query['echo'] = false;
          #will act as controller to populate $ps_query['playlist_item']
          include(dirname(__FILE__) . "/frontend/playlist/default.php");
          $ps_query['echo'] = true;
        }
        include(dirname(__FILE__) . "/frontend/playlist/last_item_ajax.php");
}else if($_GET['mode'] == 'playlist-item-now'){
  
	$start_date = time();
        $program = $scheduleObj->get_program_playing_at($start_date);

        #global, needs to be set to null so hooks don't have old info in case no program was found
        $ps_query['program'] = $program;

        require_once(dirname(__FILE__) . "/hooks/playlist_item.php");
        if(!is_null($program)){
          $start = strtotime($program->start_date);
          $end = $eventHelper->fix_end_time($start, $program->end_date);
          $show_only_last = true;
          #will act as controller to populate $ps_query['playlist_item']
          include(dirname(__FILE__) . "/frontend/playlist/default.php");

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
