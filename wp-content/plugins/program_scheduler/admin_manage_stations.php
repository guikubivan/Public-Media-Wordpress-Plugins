<?php

global $helper_schedule, $wpdb;
echo "<div class='wrap'>";

$sname = $_POST['schedule_name'];
if (!empty($sname)) {
  $schedules = $wpdb->get_results("SELECT * FROM " . $helper_schedule->t_s . " ORDER BY name");

  $scheduleObject = new ProgramScheduler($sname);
  if ($scheduleObject->save_station()) {
    echo "<div class='updated fade'>Schedule " . $sname . " added.</div>";
  } else {
    echo "<div class='updated fade'>Error adding " . $sname . ". Make sure to use unique names for schedule.</div>";
  }
} else if (isset($_POST['schedule_index'])) {


  $id = $_POST['schedule_index'];
  $station = ProgramScheduler::find($id);
  if ($_POST['delete']) {
    echo "Are you sure you want to delete schedule <b>" . $station->schedule_name . "</b>?<br />
                    This will delete all events related to this schedule.<br />";
    echo "<form action='' method='post'>
                    <input type='hidden' name='schedule_index' value='$id'/>
                    <input type='submit' name='confirm_delete' value='Yes, I am sure'/>
                    </form>";
    return;
  } else if ($_POST['confirm_delete']) {
    if ($station->delete_station()) {
      echo "<div class='updated fade'>Schedule $station->schedule_name deleted</div>";
    } else {
      echo "<div class='updated fade'>Error: Schedule $station->schedule_name could not be deleted</div>";
    }
  }
}


$query = "SELECT * FROM " . $helper_schedule->t_s . " ORDER BY name";
$schedules = $wpdb->get_results($query);

if (sizeof($schedules)) {
  echo "<h2>Schedules</h2>";
  echo "<form action='' method='post'><table style>";
  foreach ($schedules as $i => $station) {
    echo "<tr>";
    echo "<td>";
    echo "<input type='radio' id='schedule_index_$i' name='schedule_index' value='$station->ID' />";
    echo "</td>";
    echo "<td><label for='schedule_index_$i'>";
    echo $station->name;
    echo "</label></td>";
    echo "<td style='padding: 10px;'>";
    echo "<a href='?page=program_scheduler/schedule_editor.php&schedule_name=" . urlencode($station->name) . "' >Manage</a> ";
    echo " View &raquo; ";
    echo " <a href='?page=program_scheduler/schedule_viewer.php&schedule_name=" . urlencode($station->name) . "'>week</a>";
    echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=single&schedule_name=" . urlencode($station->name) . "'>single</a>";
    echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=now&schedule_name=" . urlencode($station->name) . "'>now</a>";
    echo " <a href='?page=program_scheduler/schedule_viewer.php&mode=listing&schedule_name=" . urlencode($station->name) . "'>listing</a>";
    echo "</td>";
    echo "</tr>";
  }
  echo "</table>";
  echo "<input type='submit' name='delete' value='Delete' />";
  echo "</form>";
  echo "<hr />";
}


echo "<h2>Add schedules</h2>";

echo "<form action='' method='POST' />";
echo "<input type='text' name='schedule_name' value='Enter schedule name' onfocus=\"if(this.defaultValue==this.value) this.value = '';\" />";
echo "<br/><input type='submit' value='Create Schedule' />";
echo "</form>";
echo "</div>";
?>
