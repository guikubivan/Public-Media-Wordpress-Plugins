<?php
#globals used
global $start_date;
global $eventHelper;
global $scheduleObj;
global $ps_query;

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
#Find your timezone by city at http://us.php.net/manual/en/timezones.america.php
#date_default_timezone_set("America/New_York");#Eastern Time
date_default_timezone_set("America/Denver");#Mountain Time

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

if(!function_exists('single_program_div') ){
	function single_program_div($schedule, $program, $ismodule=false){
		$eventHelper = new SchedulerEvent('', '', '');
		$str = '';
		$style = ($program->category_color && (!$ismodule)) ? "style='background-color: " . $program->category_color . "; " : '';
		$style .= $style ? "'" : '';
		$class = $ismodule ? 'single_program_module' : 'single_program';
		$str .= "<div class='$class' $style >";
		//$name_style = $ismodule ? "style='font-size: x-small;'" : '';
		$str .= "<span class='single_program_main_name'  $name_style onclick=\"ajax_get_program('$schedule', ".$program->program_id.", this, 'right')\"> " . $program->name . "</span>";
		$start = strtotime($program->start_date);
		$end = $eventHelper->fix_end_time($start, $program->end_date);
		
		$stime = (intval(date('i', $start)) > 0) ? $stime = date('g:i a', $start) : date("g a", $start);
		$etime = (intval(date('i', $end)) > 0) ? $etime = date('g:i a', $end) : date("g a", $end);
		$str .= " <span class='single_program_time' $name_style >" . $stime . ' - ' . $etime . '</span>';
		$str .= "</div>";
		return $str;
	}
}

if(!function_exists('single_day_view')){
  function single_day_view($stations){//station name or array of station names
    global $start_date, $eventHelper, $scheduleObj;

    if(!is_array($stations))$stations = array($stations);

    $minutes_delta = 30;#minutes at each time step
    $end_date = $start_date + 86400;

    $schedules = array();
    foreach($stations as $index => $sname){
      $schedules[$index] = array("object" => ProgramScheduler::find_by_name($sname));
      $schedules[$index]['programs'] = $schedules[$index]['object']->php_get_programs($start_date, $end_date);
    }

    echo "<div id='single_wrapper' >";
    //echo "<div style='width: 33%;float:left;text-align: left;'>";

    //echo "</div>";
    echo "<div style='text-align: center;'>";
    if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])){
            echo "<a style='padding-right: 10px;' href='?page=".$_GET['page']."&mode=single&schedule_name=".$_GET['schedule_name']."&start_date=".urlencode(date("Y-m-d", $start_date-86400))."' ><<</a>";
    }else{
            echo "<span style='padding-right: 10px;' class='clickable' onclick=\"single_change_date(-1);\" > << </span>";
    }
    echo "<span id='single_date'>" . date("l", $start_date)." " . date("F j, Y", $start_date) . "</span>";

    if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])){
            echo "<a style='padding-left: 10px;' href='?page=".$_GET['page']."&mode=single&schedule_name=".$_GET['schedule_name']."&start_date=".urlencode(date("Y-m-d", $start_date+86400))."' > >> </a>";
    }else{
            echo "<span style='padding-left: 10px;' class='clickable' onclick=\"single_change_date(+1);\" > >> </span>";
    }
    echo "</div>";

    echo "<table style='clear:both' class='single'>";
    echo table_headers(array_merge(array('&nbsp;'), $stations));
    $noPrograms= true;
    $time = $start_date + 1800;
    $color1 = '';

    for($i=0; $i<48; ++$i) {

      #End of current timeslot in minutes of the day
      $mins_thresh = intval(date('H', $time)) * 60 + intval(date('i', $time));

      $hour = (($i % 2) == 0) ? date('g a', $time) : '&nbsp;';
      $style = (($i % 2) == 0) ? "style='border-top: 1px solid #E7B35C;'" : '';
      echo "<tr><td $style class='time'>$hour</td>";

      foreach($schedules as $index => $sched_pair){
        $programs = $sched_pair['programs'];
        $this_program = current($programs);
        
        $start = strtotime($this_program->start_date);//timestamp
        $smins = intval(date('H', $start)) * 60 + intval(date('i', $start));//minutes of the day
        $end = $eventHelper->fix_end_time($start, $this_program->end_date) - 1;//timestamp
        $emins = intval(date('H', $end)) * 60 + intval(date('i', $end));//minutes of the day
        $module = (($emins - $smins) >= 15) ? false : true;
        $str1 = '';
        /*echo "\nstart: $start\n";
        echo "\nsmins: $smins\n";
        echo "\nend: $end\n";
        echo "\nemins: $emins\n";*/


        
        #While program starts before the end of the current time slot ends AND
        #ends after the beginning of current time slote
        while ( ($smins < $mins_thresh) && ($emins > ($mins_thresh -$minutes_delta)) ) {
          if (!$this_program->name)break;
          if ($sched_pair['object']->program_tablerow($this_program, $start_date, true, $max_cell_height, true, true)) {

            if (($this_program->category_color != $color1) && !$module) {
              $color1 = $this_program->category_color;
            }
            $str1 .= single_program_div($sched_pair['object']->schedule_name, $this_program, $module);
          }
          if ( !next($programs) ){ break;}
          $this_program = current($programs);
          $start = strtotime($this_program->start_date);
          $smins = intval(date('H', $start)) * 60 + intval(date('i', $start));
          $end = $eventHelper->fix_end_time($start, $this_program->end_date) - 1;
          $emins = intval(date('H', $end)) * 60 + intval(date('i', $end));
          $module = (($emins - $smins) >= 15) ? false : true;
        }

        $class1 = $str1 ? "class='single_program_cell'" : '';

        echo $color1 ? "<td style='background-color: $color1' $class1 >" : "<td $class1 >";
        echo $str1 ? $str1 : '&nbsp;';
        echo "</td>";
      }

      echo "</tr>";

      $time += 1800;
    }

    echo "</table>";

    if($cats = $scheduleObj->get_categories()){
            echo "<div style='width: 100%; text-align: left; margin-top: 5px;padding: 10px; font-size: 13px'>";
            foreach($cats as $cat){
                    echo "<span style='border: 1px solid grey; margin-right: 2px; padding-right: 8px; background-color:" . $cat->category_color . "'>&nbsp; </span> <span style='margin-right: 13px;'>" .  $cat->category_name  . '</span>';
            }
            echo "</div>";
    }
    echo "</div>";


  }
}

if($_GET[mode] == 'single'){
  if(empty($sname)){
    echo "No schedule given";
    return;
  }

  $sname = explode(",", $sname);
  foreach($sname as $key=>$value){
    $sname[$key] = trim($sname[$key]);
  }
  single_day_view($sname);
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
                          echo  $scheduleObj->get_link_name($program);
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
