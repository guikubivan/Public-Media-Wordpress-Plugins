<?
global $wpdb;
$schedules = $wpdb->get_results("SELECT * FROM ps_stations ORDER BY name;");
$names = array();
foreach($schedules as $schedule)$names[] = "'" . $schedule->name . "'";
?>
<script type="text/javascript">

jQuery(document).ready(function($){
	jQuery('.calendar_canvas').calendar({
		schedule_name: [<? echo implode(",", $names); ?>],
		hour_fraction: <?php echo $granularity; ?>,
		mod_date_class: 'date_changer',
		cell_width: <?php echo $cell_width; ?>,
		cell_height: <?php echo $cell_height; ?>,
		datepicker_selector: '.datepicker'
		});		
	});
</script>
