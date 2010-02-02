<?php

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




if(isset($_GET['schedule_name']) ){
	$sname = $_GET['schedule_name'];
	$scheduleObj = new ProgramScheduler($sname);
	if(!$scheduleObj->is_valid()){
		echo "<strong>Schedule $sname does not exist.</strong>";
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
		$str .= "<span class='single_program_main_name'  $name_style onclick=\"ajax_get_program('$schedule', ".$program->ID.", this, 'right')\"> " . $program->name . "</span>";
		$start = strtotime($program->start_date);
		$end = $eventHelper->fix_end_time($start, $program->end_date);
		
		$stime = (intval(date('i', $start)) > 0) ? $stime = date('g:i a', $start) : date("g a", $start);
		$etime = (intval(date('i', $end)) > 0) ? $etime = date('g:i a', $end) : date("g a", $end);
		$str .= " <span class='single_program_time' $name_style >" . $stime . ' - ' . $etime . '</span>';
		$str .= "</div>";
		return $str;
	}
}


if($_GET[mode] == 'single'){
	$hd1 = new ProgramScheduler('HD1');
	$hd2 = new ProgramScheduler('HD2');
	$end_date = $start_date + 86400;
	//echo "start date: " .Date('N', $start_date). "<br />";
	//echo "end date: " .JFormatDateTime($end_date). "<br />";
	$programs_hd1 = $hd1->php_get_programs($start_date, $end_date);
	$programs_hd2 = $hd2->php_get_programs($start_date, $end_date);
	//print_r($programs_hd2);
	echo "<div id='single_wrapper' >";
	echo "<div style='width: 33%;float:left;text-align: left;'>";
	if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])){
		echo "<a href='?page=".$_GET['page']."&mode=single&schedule_name=".$_GET['schedule_name']."&start_date=".urlencode(date("Y-m-d", $start_date-86400))."' > << </a>";
	}else{
		echo "<span class='clickable' onclick=\"single_change_date(-1);\" > << </span>";
	}
	echo "</div>";
	echo "<div id='single_date' style='width: 33%;float:left;text-align: center;'>".date("l", $start_date)." " . date("F j, Y", $start_date) . "</div>";
	echo "<div style='width: 33%;float:left;text-align: right;'>";
	if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])){
		echo "<a href='?page=".$_GET['page']."&mode=single&schedule_name=".$_GET['schedule_name']."&start_date=".urlencode(date("Y-m-d", $start_date+86400))."' > >> </a>";
	}else{
		echo "<span class='clickable' onclick=\"single_change_date(+1);\" > >> </span>";
	}
	echo "</div>";

	echo "<table style='clear:both' class='single'>";
	echo table_headers('&nbsp;', 'HD1', 'HD2');
	$noPrograms= true;
	$time = $start_date + 1800;;
	$color1 = '';
	$color2 = '';

	for($i=0; $i<48; ++$i){
		
		$mins_thresh = intval(date('H', $time))*60 + intval(date('i', $time));
		
		$c1 = current($programs_hd1);
		$start = strtotime($c1->start_date);
		$smins = intval(date('H', $start))*60 + intval(date('i', $start));
		$end = $eventHelper->fix_end_time($start, $c1->end_date) - 1;
		$emins = intval(date('H', $end))*60 + intval(date('i', $end));
		$module = (($emins-$smins) >= 15) ? false : true;
		$str1 = '';

		while($smins < $mins_thresh ){
			if(!$c1->name)break;
			if($hd1->program_tablerow($c1, $start_date, true, $max_cell_height, true, true)){

				if( ($c1->category_color != $color1) && !$module){
					$color1 = $c1->category_color;
				}
				$str1 .= single_program_div('HD1', $c1, $module);

			}
			if(!next($programs_hd1))break;
			$c1 = current($programs_hd1);
			$start = strtotime($c1->start_date);
			$smins = intval(date('H', $start))*60 + intval(date('i', $start));
			$end = $eventHelper->fix_end_time($start, $c1->end_date) - 1;
			$emins = intval(date('H', $end))*60 + intval(date('i', $end));
			$module = (($emins-$smins) >= 15) ? false : true;
		}


		$c2 = current($programs_hd2);
		$start = strtotime($c2->start_date);
		$smins = intval(date('H', $start))*60 + intval(date('i', $start));
		$end = $eventHelper->fix_end_time($start, $c2->end_date) -1 ;
		$emins = intval(date('H', $end))*60 + intval(date('i', $end));
		$module = (($emins-$smins) >= 15) ? false : true;
		
		$str2 = '';
		while($smins < $mins_thresh ){
			if(!$c2->name)break;
			if($hd2->program_tablerow($c2, $start_date, true, $max_cell_height, true, true)){
				if($c2->category_color != $color2){
					$color2 = $c2->category_color;
				}
				//echo $c2->category_color;
				
				$str2 .= single_program_div('HD2', $c2);
			}
			if(!next($programs_hd2))break;
			$c2 = current($programs_hd2);
			$start = strtotime($c2->start_date);
			$smins = intval(date('H', $start))*60 + intval(date('i', $start));
			
			$end = $eventHelper->fix_end_time($start, $c2->end_date) -1 ;
			$emins = intval(date('H', $end))*60 + intval(date('i', $end));
			$module = (($emins-$smins) >= 15) ? false : true;
		}
		$hour = (($i % 2) == 0) ? date('g a', $time) : '&nbsp;';
		$style = (($i % 2) == 0) ? "style='border-top: 1px solid #E7B35C;'" : '';
		$class1 = $str1 ? "class='single_program_cell'" : '';
		$class2 = $str2 ? "class='single_program_cell'" : '';
		echo "<tr><td $style class='time'>$hour</td>";
		echo $color1 ?  "<td style='background-color: $color1' $class1 >" : "<td $class1 >";
		echo $str1 ? $str1 : '&nbsp;';
		echo "</td>";
		echo $color2 ?  "<td style='background-color: $color2' $class2 >" : "<td $class2 >";
		echo $str2 ? $str2 : '&nbsp;';
		echo "</td></tr>";

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
}else if($_GET[mode] == 'listing'){
	$programs = $scheduleObj->get_listing();
	
	echo "<div style=''>";

	$noPrograms= sizeof($programs)> 0 ? false : true;
	$cid = -1;
	$cs = "";
	$schedule_name = '';
	
	$rows = $scheduleObj->parse_program_times($programs, $eventHelper);
	if($rows !== false){
		echo "<ul class='listing_list'>";
		foreach($rows as $row){
			echo "<li>";
			echo "<a style='cursor: auto' href=\"#".sanitize_title($row['name'])."\">".$row['name']."</a>";
			echo $scheduleObj->format_single_row($row, 'listing_item', false);
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
			$noPrograms = false;
			echo  $scheduleObj->get_link_name($program);
			break;
		}
	}
	if($noPrograms){
		echo "";
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
		require_once(dirname(__FILE__).'/load_week_schedule_all_js.php'); 
	}
}	



?>
