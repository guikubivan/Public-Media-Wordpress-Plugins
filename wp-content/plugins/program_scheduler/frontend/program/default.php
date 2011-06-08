<?
/*********
REQUIRED VARIABLES
  $sname - schedule name
  $program - program properties object
  $ismodule - bool
  $start_date - date we are looking at
  $scheduleObj - ProgramScheduler object referencing current station/schedule
*********/
global $wpdb, $start_date, $scheduleObj;

$playlists_blog = 1;#not used if not multi-blog install


$eventHelper = new SchedulerEvent('', '', '');
$style = ($program->category_color && (!$ismodule)) ? "style='background-color: " . $program->category_color . "; " : '';
$style .= $style ? "'" : '';
$class = $ismodule ? 'single_program_module' : 'single_program';


$start = strtotime($program->start_date);
$end = $eventHelper->fix_end_time($start, $program->end_date);

$stime = (intval(date('i', $start)) > 0) ? $stime = date('g:i a', $start) : date("g a", $start);
$etime = (intval(date('i', $end)) > 0) ? $etime = date('g:i a', $end) : date("g a", $end);
//$name_style = $ismodule ? "style='font-size: x-small;'" : '';
$program_css_id = $scheduleObj->id . "_" . date('Hi', $start);
?>
<a name="<?= $program_css_id ?>"></a>
<div id="<?= $program_css_id ?>" class='<?= $class ?>' <?= $style ?> >
  <img style='vertical-align: middle;' src="<?= $scheduleObj->plugin_url(); ?>images/clock_18x18.png"/>
  <span <?= $name_style ?>>
    <?= $stime ?> - <?= $etime ?>
  </span>
  <br/>
  <span class='single_program_main_name'  <?= $name_style ?>>
    <?= $program->name ?>
  </span><br/>
<? if(!empty($program->description)): ?>
  <div class="ps_program_description">
  <?= $program->description ?>
  </div>
<? endif; ?>
<?
if($program->show_playlist == "1"):
  include(dirname(__FILE__) . "/../playlist/default.php");
endif; ?>

<? if($program->host_name): ?>
<div class="ps_host_info">
  <table>
    <tr>
  <? if($program->host_photo_url): ?>
      <td><img class="ps_host_photo" src="<?= $program->host_photo_url ?>" /></td>
  <? endif; ?>
      <td>
<? if(empty($program->host_bio)): ?>
        <span class="ps_host_name"><?= $program->host_name ?></span>
<? else: ?>
        <i>About the host -</i><br/>
        <span class="ps_host_name"><?= $program->host_name ?></span><br/>
        <div class="ps_host_description">
          <?= $program->host_bio ?>
        </div>
        
<? endif; ?>
      </td>
    </tr>
  </table>
</div>

<? endif; ?>

  </div>