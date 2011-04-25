<?
/*********
REQUIRED VARIABLES
  $playlist - array of playlist items
VALID ATTRIBUTES
  -ID
  -post_id
  -title
  -composer
  -artist
  -album
  -label
  -release_year
  -asin
  -notes
  -start_time
  -duration
  -label_id
  -station_id
*********/
?>
<div>
  <div>
    &nbsp;
    <div class="show_hide_playlist canclick" onclick="jQuery(this).parent().next().toggleClass('hide');">
      Show/Hide Playlist
    </div>
  </div>
  <table class="ps_playlist hide">
<? foreach($playlist as $item):
    $start_at = strtotime($item->start_time);
    #print_r($item);
  ?>

    <tr class="ps_playlist_item">
      <td>
      <?= date("h:i a", $start_at); ?> -
      </td>
      <td>
        <div style="float: right;"><span>Buy Now</span></div>
        <strong><?= $item->composer; ?>: <?= $item->title; ?></strong><br/>
        <i><?= $item->artist ?></i><br/>
        <?= $item->label ?> <?= $item->label_id ?>
      </td>
    </tr>

<? endforeach; ?>

  </table>
</div>
