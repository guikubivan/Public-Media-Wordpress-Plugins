<?
/*********
REQUIRED VARIABLES
  $sname - schedule name
  $program - program properties object
  $ismodule - bool
  $start_date - date we are looking at
  $scheduleObj - ProgramScheduler object referencing current station/schedule
*********/
global $wpdb, $start_date, $scheduleObj;

$playlists_blog = 1;#not used if not multi-blog install


$eventHelper = new SchedulerEvent('', '', '');
$style = ($program->category_color && (!$ismodule)) ? "style='background-color: " . $program->category_color . "; " : '';
$style .= $style ? "'" : '';
$class = $ismodule ? 'single_program_module' : 'single_program';
//$name_style = $ismodule ? "style='font-size: x-small;'" : '';
?>

<div class='<?= $class ?>' <?= $style ?> >

  <span class='single_program_main_name'  <?= $name_style ?> onclick="ajax_get_program('<?= $sname ?>', <?= $program->program_id ?>, this, 'right')">
    <?= $program->name ?>
  </span>
  <?
  $start = strtotime($program->start_date);
  $end = $eventHelper->fix_end_time($start, $program->end_date);

  $stime = (intval(date('i', $start)) > 0) ? $stime = date('g:i a', $start) : date("g a", $start);
  $etime = (intval(date('i', $end)) > 0) ? $etime = date('g:i a', $end) : date("g a", $end);
  ?>

  <span class='single_program_time' <?= $name_style ?>>
    <?= $stime ?> - <?= $etime ?>
  </span>
<?
if($program->show_playlist == "1"):
  #echo date("Y-m-d ",$start_date);
  $program_start = formatdatetime(strtotime(date("Y-m-d ",$start_date) . date("H:i:s", $start) ));
  $program_end = formatdatetime(strtotime(date("Y-m-d ",$start_date) . date("H:i:s", $end) ));
  $cur_datetime = formatdatetime(time());
  if(is_multisite()) switch_to_blog($playlists_blog);

  $query = $wpdb->prepare("SELECT DISTINCT s.id FROM " . $scheduleObj->t_s . " AS s JOIN " . $scheduleObj->t_e . " AS e ON s.id = e.id");
  $station_clause = "";
  if(sizeof($wpdb->get_results($query)) > 1) $station_clause = "AND station_id = %d";
  
  $query = $wpdb->prepare("SELECT * FROM ". $wpdb->prefix . "wfiu_playlist WHERE start_time >= %s AND start_time < %s AND start_time <= %s $station_clause",
          $program_start, $program_end, $cur_datetime, $scheduleObj->id);

  $playlist = $wpdb->get_results($query);
  if(is_multisite()) restore_current_blog();
  
  if(sizeof($playlist) > 0):
    include(dirname(__FILE__) . "/../playlist/default.php");
  endif;
endif; ?>
</div>