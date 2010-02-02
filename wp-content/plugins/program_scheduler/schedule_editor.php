<?php

$granularity = 2; //in divisions per hour

//$singleDay = 4;
$cell_width = isset($singleDay) ? 300 : 80;
$cell_height = 30;
$cal_height = ($cell_height) * $granularity * 24;//in pixels
$cal_width = isset($singleDay) ? $cell_width : $cell_width * 7;

//echo JFormatDateTime($start_date) ."\n<br />";
if(!isset($start_date) ){
	$start_date = strtotime("last Monday", time()+ 86400);
}else{
	$start_date = strtotime("last Monday", $start_date+ 86400);
}
//echo JFormatDateTime($start_date);

$end_date = strtotime("next Sunday");
$mf = 'M';
?>
 <STYLE type="text/css">
   .hour_cell { font-size: <?php echo $cell_height-19; ?>px }
 </STYLE>

<script type="text/javascript">
<?php 
echo "var isSingle = ";
echo isset($singleDay) ? 'true;' : 'false;';



echo "\nvar months  = { \n";
$month_array = array();
for($i=0; $i<12; ++$i){
	$month_array[] = date("$mf", mktime(0, 0, 0, $i+2, 0, 2009)) . ": $i";
}
echo implode(',', $month_array);
echo "};";


?>
</script>

<div class='wrap'>
<?php 
if(preg_match("/schedule_editor\.php/", $_SERVER['REQUEST_URI'])){
	echo "<h2>Schedule Editor</h2>";
}
?>

	<table class='calendar_top_table'>
	<tr >
	<td>&nbsp;
	</td>
	<td>
	<div style='width:33.33%;float:left;text-align:left' > <span class='clickable date_changer' onclick='if(isSingle){offset_date(-1);}else{offset_date(-7);}' > <<</span> </div> 
	<div style='width:33.33%;float:left;text-align:center' >
		<input style='visibility: hidden; width:0px; margin:0px;padding:0px;' type="text" class="datepicker" />
		<span class='calendar_year'><?php echo date('Y', $start_date); ?></span>
		<span><input type='button' class='date_changer' onclick='goToToday()' value='Today'/></span>
	</div>
	<div style='width:33.33%;float:left; clear:right;text-align: right' >  
<?php 

if(preg_match("/schedule_editor\.php/", $_SERVER['REQUEST_URI'])){
	echo "<input class='dontdelete' type='button' id='neweventbutton' value='Add new event'/>";
}

?>

		<span class='clickable date_changer' onclick='if(isSingle){offset_date(+1);}else{offset_date(+7);}' > >> </span> </div> 
	<div style='clear:both;margin-bottom: 10px;'></div>
		<table class='calendar_days' style='width: <?php echo $cal_width; ?>px'>
		<?php $days = array();//get_days('D');
			$cur_day = $start_date;
			for($i=0;$i<7;++$i){
				if($singleDay && ($i!=$singleDay) ){
					$cur_day += 86400;
					continue;
				}
				$days[] = "<span class='day'>" . date('D',$cur_day) . "</span><br /><span class=\"day_".$i."\"><a href='#both-single' >" . date('M j', $cur_day) . "</a></span>";
				$cur_day += 86400;
			}
			echo table_headers($days);?>
		</table>
	</td>
	</tr>	
	<tr>
	<td style='height: 100%'>
		<table class='calendar_hours' style='height:100%;'>
		<?php
			$time = mktime(0,0,0);
			
			for($h=0; $h < 2*24; ++$h){
				echo "<tr>";
				echo "<td class='hour_cell' style='height: ". ($granularity*$cell_height/2) ."px'>";
				//echo "<td class='hour_cell' style='font-size: " . ($cell_height-6) . "px; height: ". ($granularity*$cell_height/2) ."px'>";
				echo date("g:i a", $time);
				echo "</td>";
				echo "</tr>";
				$time += 60*30;
			}
			
		?>
		</table>
	</td>
	<td>
		<table class='calendar_canvas' >
		<?php
			for($h=0; $h < $granularity*24; ++$h){
				echo "<tr>";
				for($i=0;$i<7;++$i){
					if($singleDay && ($i!=$singleDay) ){
							continue;					
					}
					$class = ($h % $granularity)==0 ? 'selectable_cell calendar_cell calendar_cell_hour' : 'selectable_cell calendar_cell';
					echo "<td id='${h}_${i}' class='$class' style='height:".($cell_height)."px;width:".($cell_width)."px;' >";
					echo "<div style='width:100%;height:100%;position: relative;'><img src='".get_bloginfo('url')."/wp-content/plugins/program_scheduler/spacer.gif' />";
					if( ($h==0) && ($i==0) ){
						echo '<div class="loading_div calendar_overlay" style="height: ' .  ($cell_height*($granularity*24)) . 'px; width: ' . ($cell_width*7-35) . 'px;"><img src="' . get_bloginfo('url') . '/wp-content/plugins/program_scheduler/images/loading.gif" /></div>';
					}
					
					echo "</div>";
					/*if($h==0 && $i==0){
						echo "<div class='calendar_overlay' style='height: ".($cell_height)."px; width: ".($cell_width-1)."px'></div>";
						//echo "<div style='width:100%;height:100%'></div>";
					}*/
					echo "</td>";
				}
				echo "</tr>";
			}
		?>
		</table>
	</td>
	</tr>
	</table>		
</div>

<?php 
if(preg_match("/schedule_editor\.php/", $_SERVER['REQUEST_URI']) || preg_match("/schedule_viewer\.php/", $_SERVER['REQUEST_URI']) ){
	require_once('load_week_schedule_js.php');
}

if(preg_match("/schedule_editor\.php/", $_SERVER['REQUEST_URI'])){
	$scheduler = new ProgramScheduler($_GET['schedule_name']);
	$rows = $scheduler->get_all_programs();
	echo "<table >";
	echo table_headers('Name', 'Start date', 'End date', 'Repeats');
	foreach($rows as $row){
		echo "<tr id='".$row->ID."' event_id='".$row->time_id."' class='edit_program dontdelete' style='cursor:pointer;' onmouseover=\"jQuery(this).css('background-color','#FFBBBB');\" onmouseout=\"jQuery(this).css('background-color','');\" >";
		echo "<td style='padding: 5px'>";
		echo $row->name;
		echo "</td><td style='padding: 5px'>". $row->start_date;
		echo "</td>";
		echo "<td style='padding: 5px'>" . $row->end_date ."</td>";
		echo "<td style='padding: 5px'>";
		echo $row->weekdays;
		echo "</td></tr>";
		
	}
	echo "</table>";
}
?>
