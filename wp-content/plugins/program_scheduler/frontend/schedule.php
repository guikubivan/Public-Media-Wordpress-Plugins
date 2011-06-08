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
<?
include("_navigation.php");
the_schedule($sname, $ps_mode);
?>
</div>