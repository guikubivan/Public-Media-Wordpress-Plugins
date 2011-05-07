<?
/*********
REQUIRED VARIABLES
  $sname - station name
  $ps_mode
  $ps_date
*********/

#echo $_GET['start_date'];
#echo strtotime($_GET['start_date']);

?>
<div id="ps_main_wrapper">
<div>
  <ul>
    <li style="float: left;padding-right: 20px">
      <a href="<?= get_bloginfo('url') . "/" . get_option($ps_page_option_name) . "/" . ($ps_query['use_default'] ? '' : $sname . "/") . "weekly/"; ?>">
        Weekly
      </a>
    </li>
    <li>
      <a href="<?= get_bloginfo('url') . "/" . get_option($ps_page_option_name) . "/" . ($ps_query['use_default'] ? '' : $sname . "/") . "daily/"; ?>">
        Daily
      </a>
    </li>
  </ul>
</div>

<? the_schedule($sname, $ps_mode); ?>
</div>