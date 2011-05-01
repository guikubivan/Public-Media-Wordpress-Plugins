<?
/*********
REQUIRED VARIABLES
 $start_date
 $sname
 $ps_query
 $scheduleObj
*********/


?>

<div style="text-align:center;">
  <div style="font-size: 12px;">
    <a href="<?= ps_single_day_url($sname, time()); ?>">Today</a> | <input style='visibility: hidden; width:0px; margin:0px;padding:0px;' type="text" id="single_day_datepicker_<?= $scheduleObj->id; ?>" />
  </div>

<? if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])): ?>
    <a style='padding-right: 10px;' href='?page=<? echo $_GET['page'] ?>&mode=single&schedule_name=<? echo $_GET['schedule_name'] ?>&start_date=<? echo urlencode(date("Y-m-d", $start_date-86400)); ?>' >
      <<
    </a>
<? else: #TODO: make this work for when showing all the modes?>
    <a style='padding-right: 10px;'
       onclick="return true; single_change_date(-1, '<?= $sname ?>');"
       href='<?= ps_single_day_url($sname, $start_date - 86400); ?>'
    >
      <<
    </a>
<? endif; ?>
    <span id='single_date'>
      <? echo date("l", $start_date)." " . date("F j, Y", $start_date); ?>
    </span>

<? if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])): ?>
    <a style='padding-left: 10px;' href='?page=<? echo $_GET['page']; ?>&mode=single&schedule_name=<? echo $_GET['schedule_name'] ?>&start_date=<? echo urlencode(date("Y-m-d", $start_date+86400))?>' >
      >>
    </a>
<? else: #TODO: make this work for when showing all the modes?>
    <a style='padding-left: 10px;'
       onclick="return true; single_change_date(-1, '<?= $sname ?>');"
       href='<?= get_bloginfo('url'); ?>/schedule/<?= $ps_query['use_default'] ? '' : $sname . "/" ?>daily/<? echo urlencode(date("Y-m-d", $start_date+86400)); ?>'
    >
      >>
    </a>

<? endif; ?>
  </div>

<script type="text/javascript">
  jQuery('#single_day_datepicker_<?= $scheduleObj->id; ?>').datepicker({
  showOn: 'button',
  showAnim: 'fadeIn',
  buttonImage: WPScheduler.url + '/wp-content/plugins/program_scheduler/images/calendar.gif',
  buttonImageOnly: true,
  onSelect: function(dateText) {
    var d = new Date(dateText);
    
    window.location = update_single_day_url('<?= ps_single_day_url($sname, time()); ?>', d);
  }
});
</script>