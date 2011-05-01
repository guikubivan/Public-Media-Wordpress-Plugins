<?
/*********
REQUIRED VARIABLES
 $start_date
 $sname
 $ps_query
*********/


?>

<div style="text-align:center;">
<? if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])): ?>
    <a style='padding-right: 10px;' href='?page=<? echo $_GET['page'] ?>&mode=single&schedule_name=<? echo $_GET['schedule_name'] ?>&start_date=<? echo urlencode(date("Y-m-d", $start_date-86400)); ?>' >
      <<
    </a>
<? else: #TODO: make this work for when showing all the modes?>
    <a style='padding-right: 10px;'
       onclick="return true; single_change_date(-1, '<?= $sname ?>');"
       href='<?= ps_single_day_url($sname, $start_date - 86400); ?>'
    >
      <<
    </a>
<? endif; ?>
    <span id='single_date'>
      <? echo date("l", $start_date)." " . date("F j, Y", $start_date); ?>
    </span>

<? if(preg_match("/admin\.php/", $_SERVER['REQUEST_URI'])): ?>
    <a style='padding-left: 10px;' href='?page=<? echo $_GET['page']; ?>&mode=single&schedule_name=<? echo $_GET['schedule_name'] ?>&start_date=<? echo urlencode(date("Y-m-d", $start_date+86400))?>' >
      >>
    </a>
<? else: #TODO: make this work for when showing all the modes?>
    <a style='padding-left: 10px;'
       onclick="return true; single_change_date(-1, '<?= $sname ?>');"
       href='<?= get_bloginfo('url'); ?>/schedule/<?= $ps_query['use_default'] ? '' : $sname . "/" ?>daily/<? echo urlencode(date("Y-m-d", $start_date+86400)); ?>'
    >
      >>
    </a>

<? endif; ?>
  </div>