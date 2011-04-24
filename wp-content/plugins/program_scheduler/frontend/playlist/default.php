<?
/*********
REQUIRED VARIABLES
  $playlist - array of playlist items
*********/
?>
<table class="ps_playlist">
<? foreach($playlist as $item):
  $start_at = strtotime($item->start_time);
  ?>

<tr class="ps_playlist_item">
  <td>
  <?= date("h:i a", $start_at); ?>
  </td>
  <td>
    <strong><?= $item->composer; ?>: <?= $item->title; ?></strong><br/>
  </td>
</tr>

<? endforeach; ?>

</table>

