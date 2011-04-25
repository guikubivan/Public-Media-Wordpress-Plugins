<?
/*********
REQUIRED VARIABLES
  $sname - station name
  $start_date - date we are looking at
  $scheduleObj - ProgramScheduler object referencing current station/schedule
*********/

if(!function_exists('single_program_div') ){
	function single_program_div($sname, $program, $ismodule=false){
                ob_start();
                include(dirname(__FILE__). '/../program/default.php');
                $str = ob_get_contents();
                ob_end_clean();
		return $str;
	}
}

if(!function_exists('single_day_view')){
  function single_day_view($stations){//station name or array of station names
    global $start_date, $eventHelper, $scheduleObj;
    #echo date('l jS \of F Y h:i:s A', $start_date);
    if(!is_array($stations))$stations = array($stations);

    $tr_seconds_delta = 30*60;#seconds at each table row
    $rows = 24*(60*60/$tr_seconds_delta);
    $hour_modulus = $rows/24;
    $end_date = $start_date + 24*60*60;

    $schedules = array();
    $programs = array();
    foreach($stations as $index => $sname){
      $schedules[$index] = array("object" => ProgramScheduler::find_by_name($sname));
      $programs[$index] = $schedules[$index]['object']->php_get_programs($start_date, $end_date);
    }


    #print_r($programs);
    echo table_headers(array_merge(array('&nbsp;'), $stations));
    $noPrograms= true;
    $next_row_time = $start_date + $tr_seconds_delta;
    $color = '';

    for($i=0; $i<$rows; ++$i) {

      #End of current timeslot in minutes of the day
      $mins_thresh = intval(date('H', $next_row_time)) * 60 + intval(date('i', $next_row_time));

      #hour in format such as "1 pm" if it's even
      $hour = (($i % $hour_modulus) == 0) ? date('g a', $next_row_time -  $tr_seconds_delta) : '&nbsp;';
      $style = (($i % $hour_modulus) == 0) ? "style='border-top: 1px solid #E7B35C;'" : '';
      echo "<tr class='ps_time_row'><td $style class='time'>$hour</td>";


      foreach($schedules as $index => $sched_pair){
        $module = false;
        $temp_string = '';
        $this_program = current($programs[$index]);
        if($this_program !== false){
          $start = strtotime($this_program->start_date);//timestamp
          $smins = intval(date('H', $start)) * 60 + intval(date('i', $start));//minutes of the day
          $end = $eventHelper->fix_end_time($start, $this_program->end_date) - 1;//timestamp
          $emins = intval(date('H', $end)) * 60 + intval(date('i', $end));//minutes of the day
          if(($emins - $smins) < 15) $module = true;
          /*echo "\nstart: $start\n";
          echo "\nsmins: $smins\n";
          echo "\nend: $end\n";
          echo "\nemins: $emins\n";*/

          #While program starts before the end of the current time slot ends AND
          #ends after the beginning of current time slot
          if( ($smins < $mins_thresh)){
            while ( ($smins < $mins_thresh)){
              #echo "<b>-smins: $smins-</b>\n";
              #echo "<b>-thres: $mins_thresh-</b>\n";
              if (empty($this_program->name))break;
              $is_valid = $sched_pair['object']->program_tablerow($this_program, $start_date, true, $max_cell_height, true, true);
              #echo "<b>-isvalid? ".($is_valid? '1':'0')."-</b>";
              if ($is_valid) {

                if (($this_program->category_color != $color) && !$module) {
                  $color = $this_program->category_color;
                }
                $temp_string .= single_program_div($sched_pair['object']->schedule_name, $this_program, $module);
              }
              if (next($programs[$index]) === false){ break;}
              $this_program = current($programs[$index]);
              $start = strtotime($this_program->start_date);
              $smins = intval(date('H', $start)) * 60 + intval(date('i', $start));
              $end = $eventHelper->fix_end_time($start, $this_program->end_date) - 1;
              $emins = intval(date('H', $end)) * 60 + intval(date('i', $end));
              $module = (($emins - $smins) >= 15) ? false : true;
            }
          }
        }
        #TODO: when no programming is scheduled, add horizontal line (code below adds horizontal lines right away and infinitely
        #else{
        #  $temp_string = "<div class='single_program'>&nbsp;</div>";
        #}
        $class = $temp_string ? "class='single_program_cell'" : '';
        $style = $color ? "style='background-color: $color'" : "";
        echo "<td $style $class >";
        echo $temp_string ? $temp_string : '&nbsp;';
        echo "</td>";
      }

      echo "</tr>";

      $next_row_time += $tr_seconds_delta;
    }


  }
}


?>


<div id='single_wrapper'>
  <div style='text-align: center;'>
<? if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])): ?>
    <a style='padding-right: 10px;' href='?page=<? echo $_GET['page'] ?>&mode=single&schedule_name=<? echo $_GET['schedule_name'] ?>&start_date=<? echo urlencode(date("Y-m-d", $start_date-86400)); ?>' >
      <<
    </a>
<? else: ?>
    <span style='padding-right: 10px;' class='clickable' onclick="single_change_date(-1);" >
      <<
    </span>
<? endif; ?>
    <span id='single_date'>
      <? echo date("l", $start_date)." " . date("F j, Y", $start_date); ?>
    </span>

<? if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])): ?>
    <a style='padding-left: 10px;' href='?page=<? echo $_GET['page']; ?>&mode=single&schedule_name=<? echo $_GET['schedule_name'] ?>&start_date=<? echo urlencode(date("Y-m-d", $start_date+86400))?>' >
      >>
    </a>
<? else: ?>
    <span style='padding-left: 10px;' class='clickable' onclick="single_change_date(+1);" >
      >>
    </span>
<? endif; ?>

    <? /******** BRAINS OF THE WHOLE THING **********/ ?>
    <table style='clear:both' class='single'>
    <? single_day_view($sname); ?>
    </table>
    <? /******** End BRAINS OF THE WHOLE THING ******/ ?>

    
<? if($cats = $scheduleObj->get_categories()): ?>
    <div style='width: 100%; text-align: left; margin-top: 5px;padding: 10px; font-size: 13px'>
  <? foreach($cats as $cat): ?>
      <span style='border: 1px solid grey; margin-right: 2px; padding-right: 8px; background-color:<? echo $cat->category_color ?>'>&nbsp; </span> <span style='margin-right: 13px;'><? echo $cat->category_name; ?></span>;
  <? endforeach; ?>
    </div>
<? endif; ?>
  </div>
</div>

