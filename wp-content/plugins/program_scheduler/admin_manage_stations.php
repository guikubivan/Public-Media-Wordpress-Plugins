<?php

global $helper_schedule, $wpdb;
?>

<div class='wrap'>
  
<?
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
/**********Settings*************/

if(!empty($_POST['schedule_settings_submit'])){
  if(!empty($_POST['schedule_page_name'])){
    update_option($ps_page_option_name, $_POST['schedule_page_name']);
    ps_flush_rewrite_rules();
  }
  

  if(!empty($_POST['default_schedule'])){
    update_option($ps_default_schedule_option_name, $_POST['default_schedule']);
  }else{
    delete_option($ps_default_schedule_option_name);
  }


  if(!empty($_POST['default_weekly_width'])){
    update_option($ps_default_weekly_width_option_name, $_POST['default_weekly_width']);
  }else{
    delete_option($ps_default_weekly_width_option_name);
  }

  if(!empty($_POST['default_playlist_blog'])){
    update_option($ps_default_playlist_blog_option_name, $_POST['default_playlist_blog']);
  }else{
    delete_option($ps_default_playlist_blog_option_name);
  }

  if(!empty($_POST['ps_timezone_string'])){
    update_option($ps_default_timezone_option_name, $_POST['ps_timezone_string']);
  }else{
    delete_option($ps_default_timezone_option_name);
  }

  echo "<div class='updated fade'>Settings saved.</div>";
}
$page_name = get_option($ps_page_option_name);
$default_schedule_id = get_option($ps_default_schedule_option_name);
$default_weekly_width = get_option($ps_default_weekly_width_option_name);
$default_tzstring = get_option($ps_default_timezone_option_name);
/************************************/


$query = "SELECT * FROM " . $helper_schedule->t_s . " ORDER BY name";
$schedules = $wpdb->get_results($query);

if (sizeof($schedules)): ?>
  <h2>Schedules</h2>

  <form action='' method='POST' />
    <input type='text' name='schedule_name' value='Enter schedule name' onfocus="if(this.defaultValue==this.value) this.value = '';" />
    <input type='submit' value='Add Schedule' />
  </form>



  <form action='' method='post'>
    <table>

<? foreach ($schedules as $i => $station) {
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
?>
    </table>
    <input type='submit' name='delete' value='Delete' />
  </form>
  <hr />
<? endif; ?>



  <h2>Settings (per blog)</h2>
  
  <form action='' method='POST' />
  <h3>Default schedule page</h3>
    <p>Select page where you want the schedule to appear. This will modify your wordpress url structure so you can easily access and query the schedule.</p>
    <select name="schedule_page_name">
      <option value="">
        <?php echo attribute_escape(__('Select page')); ?>
      </option>
      <?php
      $pages = get_pages();
      foreach ($pages as $pagg) {
            $option = '<option value="' . $pagg->post_name . '" ' . ($page_name == $pagg->post_name ? 'SELECTED' : '') . '>';
            $option .= $pagg->post_title;
            $option .= '</option>';
            echo $option;
      }
      ?>
    </select>
<? if(!empty($page_name)): ?>
    <a href="<? echo get_bloginfo('url') . "/" . $page_name; ?>" target="_blank">
        Go to schedule page
    </a>
<? endif; ?>

    <h3>Default schedule</h3>
    <p>Select a default schedule to use. This will only allow one schedule to exist. Set to "None" if you want all schedules to be accessible (TO-DO)</p>
    <select name="default_schedule">
      <option value="">
        <?php echo attribute_escape(__('None, show all')); ?>
      </option>
      <?php
      foreach ($schedules as $s) {
            $option = '<option value="' . $s->ID . '" ' . ($default_schedule_id == $s->ID ? 'SELECTED' : '') . '>';
            $option .= $s->name;
            $option .= '</option>';
            echo $option;
      }
      ?>
    </select>

    <h3>Default playlist blog</h3>
    <p>By default, the plugin will use the playlists of the blog being viewed. However, you can change that here (currently only affects the "playlist-item-now-ajax" view).</p>
    <select name="default_playlist_blog">
      <option value="">
        <?php echo attribute_escape(__('None')); ?>
      </option>
      <?php
      echo "blogs list";
      ?>
    </select>

    <h3>Default timezone</h3>
    <p>Specifiy timezone of the dates stored. Used for determining prev/current/next programs.</p>
    <select id="ps_timezone_string" name="ps_timezone_string">
    <?php echo wp_timezone_choice($default_tzstring); ?>
    </select>


    <h3>Weekly schedule width</h3>
    <p>Specifiy the width for the weekly schedule view, in pixels (defaults to 627 pixels otherwise).</p>
    <input type="text" style="width: 60px;" name="default_weekly_width" value="<?= $default_weekly_width; ?>"/>px

    <br/><br/>
    <input name="schedule_settings_submit" type='submit' value='Save settings' />

  </form>
</div>