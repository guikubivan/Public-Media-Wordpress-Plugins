<?
#do not use scheduleObj
global $ps_query;

?>

<div class="ps_main_nav">

  <div class="ps_main_mode">
    VIEW:
    <a <? if($ps_query['mode'] == "weekly"): ?> class="ps_selected" <? endif; ?> href="<?= get_bloginfo('url') . "/" . get_option($ps_page_option_name) . "/" . ($ps_query['use_default'] ? '' : $sname . "/") . "weekly/"; ?>">
      Weekly Schedule
    </a>
    -
    <a <? if($ps_query['mode'] == "single"): ?> class="ps_selected" <? endif; ?> href="<?= get_bloginfo('url') . "/" . get_option($ps_page_option_name) . "/" . ($ps_query['use_default'] ? '' : $sname . "/") . "daily/"; ?>">
      Daily Playlist
    </a>
  </div>

  <div class="ps_main_datepicker">
    <span class="canclick">
<? if($ps_query['mode'] == "single"): ?>
      <input style='visibility: hidden; width:0px; margin:0px;padding:0px;' type="text" id="single_day_datepicker_<?= $ps_query['schedule_id']; ?>" />
      <span onclick="jQuery(this).prev().datepicker( 'show' );">Select Date</span> |
      <a href="<?= ps_single_day_url($sname, time()); ?>">Jump to Today</a>
<? else: #weekly mode?>
      <input style='visibility: hidden; width:0px; margin:0px;padding:0px;' type="text" class="datepicker" />
      <span onclick="jQuery(this).prev().datepicker( 'show' );">Select Date</span>
      |
      <span class='canclick date_changer' onclick='goToToday()'>This week</span>
<? endif; ?>
    </span>
    
  </div>

  


</div>