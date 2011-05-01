<?
global $wpdb;
$schedules = $wpdb->get_results("SELECT * FROM ps_stations ORDER BY name;");
if (empty($schedules))
  echo "No schedules exist.";

$names = array();
foreach($schedules as $schedule)$names[] = $schedule->name;
?>

<script type="text/javascript">
  jQuery(document).ready(function(){
    jQuery("#schedule-tabs").tabs();
    jQuery("ul.listing_list").accordion({autoHeight:false});
  });
</script>
<a id="skip"></a>

<div class="top-page">
  <h1 class="page-title">Program Schedule</h1>

  <div id="schedule-tabs">

    <ul id="schedule-tabs-ul">
<!--      <div>-->
  <? foreach ($schedules as $schedule): ?>
        <li><a href="#tabs-<? echo $schedule->ID ?>"><? echo $schedule->name; ?> Per Week</a></li>
  <? endforeach; ?>

        <li><a href="#tabs-single">Per Day</a></li>
        <li><a href="#tabs-list">Programs (A-Z)</a></li>
<!--      </div>-->
    </ul>

  <? foreach ($schedules as $schedule): ?>
    <div id="tabs-<? echo $schedule->ID; ?>">
      <?php the_schedule($schedule->name, 'weekly'); ?>
    </div>
  <? endforeach; ?>

    <div id="tabs-single">
      <?php the_schedule(implode(",", $names), 'single'); ?>
    </div>

    <div id="tabs-list">
      <?php the_schedule('', 'listing'); ?>
    </div>


  </div>
</div><!--tabs top-page-->

<div class="clear"></div>

<?
#only load it once for all schedules
require_once(dirname(__FILE__).'/../load_week_schedule_all_js.php');
?>