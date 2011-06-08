<?php
/*********
REQUIRED VARIABLES
  $ps_query['program'] - contains program properties such as $ps_query['program']->name, which can be accessible with tag functions (see schedule_viewer.php)
AVAILABLE FUNCTIONS
  See plugin README
*********/

$has_program_url = empty($ps_query['program']->url) ? false : true;
$has_photo_url = empty($ps_query['program']->host_photo_url) ? false : true;
$show_playlist = $ps_query['program']->show_playlist == "1" ? true : false;

?>
<div class="ps_now_wrapper">
  <div>
<? if($has_photo_url): ?>
  <div style="display:inline">
    <img class="ps_host_photo" src="<?= $ps_query['program']->host_photo_url; ?>"/>
  </div>
<? endif; ?>
    <span class="ps_host_name"><?= ps_host_name(); ?></span>
  </div>

  <div class='ps_program_name'>
<? if( false && !empty($ps_query['program']->url) ): #disabled for now?>
    <a href='<?= ps_program_info_link() ?>' ><?= ps_program_name() ?></a>
<? else:
      ps_program_name();
endif; ?>
  </div>


 <?= ps_start_time(); ?> - <?= ps_end_time(); ?> <br/>
  <div class="ps_program_description">
    <?= ps_program_description(); ?>
  </div>

 <? if($show_playlist){
   $start = strtotime($ps_query['program']->start_date);
   $end = $eventHelper->fix_end_time($start, $ps_query['program']->end_date);
   $show_only_last = true;
   include(dirname(__FILE__) . "/../playlist/default.php");
 }?>
 
  <br/>
 <? if($has_program_url): ?>
    <div><a class="ps_program_url" href="<? ps_program_info_link(); ?>">More information</a></div>
 <? endif; ?>
</div>