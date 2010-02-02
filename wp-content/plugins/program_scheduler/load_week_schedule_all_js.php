<script type="text/javascript">
jQuery(document).ready(function($){
	$('.calendar_canvas').calendar({
		schedule_name: ['HD1', 'HD2'],
		hour_fraction: <?php echo $granularity; ?>,
		mod_date_class: 'date_changer',
		cell_width: <?php echo $cell_width; ?>,
		cell_height: <?php echo $cell_height; ?>,
		datepicker_selector: '.datepicker'
		});		
	});
</script>
