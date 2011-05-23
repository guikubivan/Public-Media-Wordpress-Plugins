<?
#If there is a playlist item now, alculate when this playlist item will end
#Otherwise, just keep checking every 1 minute for a playlist item

$ps_refresh_timeout = 60000;#default 1 minute
if(!empty($ps_query['playlist_item']) && ($ps_query['playlist_item']->duration > 0)){
  $start_time =  strtotime($ps_query['playlist_item']->start_time);
  $remaining_time = $ps_query['playlist_item']->duration - ( time() - $start_time );

  if($remaining_time  <= 0) $remaining_time = 0;
  
  $ps_refresh_timeout = ($remaining_time + 1) * 1000;
}
?>

<div id="ps_last_playlist_item">
<?#echo date("H:i:s");?>
<script type="text/javascript">
  //alert('test');

setTimeout(function() {
 jQuery.post("<?= get_bloginfo('url'); ?>/wp-admin/admin-ajax.php",
            {action:"action_playlist_latest", 'cookie': document.cookie, 'schedule_name': '<?= rawurlencode($ps_query['schedule_name']); ?>'},
            function(str){
              jQuery("#ps_last_playlist_item").after(str).remove();
            }
  );
}, <?= $ps_refresh_timeout ?>);
</script>
  
<? if(!empty($ps_query['playlist_item'])): ?>
  <p><strong>Now Playing</strong></p>
  <?php ps_pitem_composer(); ?><br /><?php ps_pitem_title(); ?><br />
  <a href="http://www.arkivmusic.com/classical/Playlist?source=WOSU&composer=<?= rawurlencode(ps_get_pitem_composer()); ?>&work=<?= rawurlencode(ps_get_pitem_title()); ?>&label=<?= rawurlencode(ps_get_pitem_label()); ?>&catalog=<?= rawurlencode(ps_pitem_label_id()); ?>" target="_blank">Buy This Recording</a>
  <?php $playlisturl = ps_get_current_playlist_url(); ?>
  <p><a href="<?php echo $playlisturl; ?>" target="_blank">Today's Playlist &raquo;</a></p>
<? endif; ?>
</div>