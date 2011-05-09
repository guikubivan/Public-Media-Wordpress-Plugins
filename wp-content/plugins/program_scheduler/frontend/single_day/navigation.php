<?
/*********
REQUIRED VARIABLES
 $start_date
 $sname
 $ps_query
 $scheduleObj
*********/
?>

<div class="ps_single_nav">

<? if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])): ?>
    <a class="arrow_left" href='?page=<? echo $_GET['page'] ?>&mode=single&schedule_name=<? echo $_GET['schedule_name'] ?>&start_date=<? echo urlencode(date("Y-m-d", $start_date-86400)); ?>' >
      <img src="<?= $scheduleObj->plugin_url(); ?>images/arrow_left.png"/>
    </a>
<? else: #TODO: make this work for when showing all the modes?>
    <a class="arrow_left"
       onclick="return true; single_change_date(-1, '<?= $sname ?>');"
       href='<?= ps_single_day_url($sname, $start_date - 86400); ?>'
    >
      <img src="<?= $scheduleObj->plugin_url(); ?>images/arrow_left.png"/>
    </a>
<? endif; ?>

<? if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])): ?>
    <a class="arrow_right" href='?page=<? echo $_GET['page']; ?>&mode=single&schedule_name=<? echo $_GET['schedule_name'] ?>&start_date=<? echo urlencode(date("Y-m-d", $start_date+86400))?>' >
      <img src="<?= $scheduleObj->plugin_url(); ?>images/arrow_right.png"/>
    </a>
<? else: #TODO: make this work for when showing all the modes?>
    <a class="arrow_right"
       onclick="return true; single_change_date(-1, '<?= $sname ?>');"
       href='<?= get_bloginfo('url'); ?>/schedule/<?= $ps_query['use_default'] ? '' : $sname . "/" ?>daily/<? echo urlencode(date("Y-m-d", $start_date+86400)); ?>'
    >
      <img src="<?= $scheduleObj->plugin_url(); ?>images/arrow_right.png"/>
    </a>

<? endif; ?>
  <div class="ps_single_date">
      <? echo date("l", $start_date)." " . date("F j, Y", $start_date); ?>
  </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($){
  <? #Check to see if we're looking at a particular date in the daily view  = "$scheduleID_" . $hour24 . $minute) ?>
  var matches = window.location.toString().match(/#(\d_\d+)$/);
  if(matches && (jQuery("#" + matches[1]).find(".ps_playlist").length > 0 )){
    jQuery("#" + matches[1]).find(".ps_playlist").toggleClass('hide');
  }

  jQuery('#single_day_datepicker_<?= $scheduleObj->id; ?>').datepicker({
    showOn: 'button',
    showAnim: 'fadeIn',
    buttonImage: '<?= $scheduleObj->plugin_url(); ?>images/calendar.gif',
    buttonImageOnly: true,
    onSelect: function(dateText) {
      var d = new Date(dateText);

      window.location = update_single_day_url('<?= ps_single_day_url($sname, time()); ?>', d);
    }
  });

});
</script>