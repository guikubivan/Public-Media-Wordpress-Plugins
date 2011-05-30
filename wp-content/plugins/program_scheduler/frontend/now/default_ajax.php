<?php
/*********
REQUIRED VARIABLES
  $ps_query['program'] - contains program properties such as $ps_query['program']->name, which can be accessible with tag functions (see schedule_viewer.php)
AVAILABLE FUNCTIONS
  See plugin README
*********/

#If there is a program now, alculate when it will end
#Otherwise, just keep checking every 1 minute for a program

$ps_refresh_timeout = 60000;#default 1 minute
if(!empty($ps_query['program'])){
  $start_time =  strtotime($ps_query['playlist_item']->start_date);
  $remaining_time = $ps_query['playlist_item']->duration - ( time() - $start_time );

  if($remaining_time  <= 0) $remaining_time = 0;

  $ps_refresh_timeout = ($remaining_time + 1) * 1000;
}

$has_program_url = empty($ps_query['program']->url) ? false : true;
$has_photo_url = empty($ps_query['program']->host_photo_url) ? false : true;
$show_playlist = $ps_query['program']->show_playlist == "1" ? true : false;

?>
<div id="ps_program_now_ajax">
<div class="ps_now_wrapper">
<?#echo date("H:i:s");?>
<script type="text/javascript">
  //alert('test');

setTimeout(function() {
 jQuery.post("<?= get_bloginfo('url'); ?>/wp-admin/admin-ajax.php",
            {action:"action_program_now", 'cookie': document.cookie, 'schedule_name': '<?= rawurlencode($ps_query['schedule_name']); ?>'},
            function(str){
              jQuery("#ps_program_now_ajax").after(str).remove();
            }
  );
}, <?= $ps_refresh_timeout ?>);
</script>
<? if(!empty($ps_query['program'])): ?>
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
<? endif; ?>
</div>
</div>