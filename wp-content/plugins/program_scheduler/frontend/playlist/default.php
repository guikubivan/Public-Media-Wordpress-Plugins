<?
/*********
REQUIRED VARIABLES
  $start_date - reference date
  $start - program start timestamp
  $end - program end timestamp
  $wpdb
  $scheduleObj
  $ps_query
  
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
global $wpdb, $ps_query;

#echo date("g:i A Y-m-d ",$start_date);
#echo date("g:i A Y-m-d ",$start); echo "<br/>";
#echo date("g:i A Y-m-d ",$end);
$program_start = formatdatetime(strtotime(date("Y-m-d ",$start_date) . date("H:i:s", $start) ));
$program_end = formatdatetime(strtotime(date("Y-m-d ",$start_date) . date("H:i:s", $end) ));
$cur_datetime = formatdatetime(time());
#if(is_multisite()) switch_to_blog($playlists_blog);

#$query = $wpdb->prepare("SELECT DISTINCT s.id FROM " . $scheduleObj->t_s . " AS s JOIN " . $scheduleObj->t_e . " AS e ON s.id = e.id");
#$station_clause = "";
#if(sizeof($wpdb->get_results($query)) > 1){
$station_clause = "AND station_id = %d";
#}

if(!isset($show_only_last)) $show_only_last = false;

$order_and_limit_clause = $show_only_last ? 'ORDER BY start_time DESC LIMIT 1' : 'ORDER BY start_time ASC';

$query = $wpdb->prepare("SELECT * FROM ". $wpdb->prefix . "wfiu_playlist WHERE start_time >= %s AND start_time < %s AND start_time <= %s $station_clause $order_and_limit_clause",
        $program_start, $program_end, $cur_datetime, $scheduleObj->id);

$playlist = $wpdb->get_results($query);

if( ($ps_query['mode'] =='now') && (sizeof($playlist) == 1) ):
  $item = current($playlist);
  $program_css_id = $scheduleObj->id . "_" . date('Hi', $start);
  ?>
<div>
Last played <strong><?= $item->composer; ?>: <?= $item->title; ?></strong>. <a href="<?= ps_single_day_url($scheduleObj->schedule_name, time()); ?>#<?= $program_css_id; ?>">See playlist</a>.
</div>

<? elseif(sizeof($playlist) > 1): ?>
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
      <td class="ps_playlist_item_start">
      <?= date("h:i a", $start_at); ?> -
      </td>
      <td>
        <strong><?= $item->composer; ?>: <?= $item->title; ?></strong><br/>
        <i><?= $item->artist ?></i><br/>
        <?= $item->label ?> <?= $item->label_id ?>
      </td>
      <td class="ps_buy_now">
        <a target="_blank"
           href="http://www.arkivmusic.com/classical/Playlist?source=WOSU&composer=<?= rawurlencode($item->composer); ?>&work=<?= rawurlencode($item->title); ?>&label=<?= rawurlencode($item->label); ?>&catalog=<?= rawurlencode($item->label_id); ?>">
          Buy Now
        </a>
      </td>
    </tr>

<? endforeach; ?>

  </table>
</div>
<? endif; ?>