<?php
/*********
REQUIRED VARIABLES
  $scheduleObj - ProgramScheduler object referencing current station/schedule
  $program_id
*********/

$airtimes = $scheduleObj->get_listing($program_id, '');
$schedules = array();
foreach($airtimes as $item){
  if(!in_array($item->schedule_name, $schedules)) $schedules[] = $item->schedule_name;
}

$rows = $scheduleObj->parse_program_times($airtimes);

if(sizeof($rows) <= 0 ) return '';

?>
<div class='single_details'>

  <div>
    <span class='single_program_name' >

    <? if(!empty($rows[0]['url'])): ?>
      <a href='<?= $rows[0]['url'] ?>'><?= $rows[0]['name'] ?></a>
    <? else: ?>
      <?= $rows[0]['name']; ?>
    <? endif; ?>

    </span>

    <span class='clickable single_close_button' onclick="jQuery(this).parent().parent().remove();" >x</span>
  </div>

  <div class='single_program_content'>
<? if(!empty($rows[0]['description'])): ?>
    <div class='single_program_description'><?= $rows[0]['description']; ?></div>
<? endif; ?>

    <div class='single_program_times'>
      <span>Airdates and Times:</span><br />
<?
foreach($schedules as $schedule_name){
  echo $rows[0][$schedule_name] ? "<span class='single_program_channel'>$schedule_name</span> <div>".$rows[0][$schedule_name]."</div>" : '';
}
?>
    </div>

<? if(!empty($rows[0]['url'])): ?>
    <div class='single_program_url'><a href='<?= $rows[0]['url']; ?>'>Website link &raquo;</a></div>
<? endif; ?>
  </div>
</div>