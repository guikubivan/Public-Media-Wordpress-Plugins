<?
/*********
REQUIRED VARIABLES
  $sname - station name
  $start_date - date we are looking at
  $scheduleObj - ProgramScheduler object referencing current station/schedule
  $eventHelper - SchedulerEvent instance
*********/

#TO-DO: pull playlists if $program->show_playlist == 1
if(!function_exists('single_program_div') ){
	function single_program_div($sname, $program, $ismodule=false){
                ob_start();
                include(dirname(__FILE__). '/../program/default.php');
                $str = ob_get_contents();
                ob_end_clean();
		return $str;
	}
}

?>


<div id='single_wrapper' style="width:99%;">
  <div style="text-align:center;">
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
  </div>
    <? /******** BRAINS OF THE WHOLE THING **********/ ?>
    <? if(is_array($sname)) $sname = $sname[0];

       $mySchedule = ProgramScheduler::find_by_name($sname);
       $end_date = $start_date + 24*60*60;
       $programs = $mySchedule->php_get_programs($start_date, $end_date);

       
       foreach($programs as $this_program){
           #print_r($this_program);
           $ps_query['program'] = $this_program;
           $start = strtotime($this_program->start_date);//timestamp
           $smins = intval(date('H', $start)) * 60 + intval(date('i', $start));//minutes of the day
           $end = $eventHelper->fix_end_time($start, $this_program->end_date) - 1;//timestamp
           $emins = intval(date('H', $end)) * 60 + intval(date('i', $end));//minutes of the day
           $module = false;
           if(($emins - $smins) < 15) $module = true;

           echo single_program_div($sname, $this_program, $module);
       }
    ?>
    <? /******** End BRAINS OF THE WHOLE THING ******/ ?>

    
<? if($cats = $scheduleObj->get_categories()): ?>
    <div style='width: 100%; text-align: left; margin-top: 5px;padding: 10px; font-size: 13px'>
  <? foreach($cats as $cat): ?>
      <span style='border: 1px solid grey; margin-right: 2px; padding-right: 8px; background-color:<? echo $cat->category_color ?>'>&nbsp; </span> <span style='margin-right: 13px;'><? echo $cat->category_name; ?></span>;
  <? endforeach; ?>
    </div>
<? endif; ?>
  
</div>

