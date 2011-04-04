<?php
if(!class_exists ('SchedulerEvent')) {
	class SchedulerEvent {
		public $start_date; //timestamp
		public $end_date; //timestamp
		public $weekdays; //integer days of week
		public $days_array; //integer days of week
		public $repeat_presets = array('1234567'=> 'daily', '12345'=> 'every weekday (Mon-Fri)', '135'=> 'every Mon., Wed., Fri.', '24'=> 'every Tues., and Thurs.'); 
		private $Scheduler;
		private $days_of_week;
		public $event_id;
		public $never_ends;
		public $daysHash;

		public function __construct($Scheduler_pointer, $start, $end, $weekdays = '', $event_id ='', $never_ends = 'false'){
			$this->daysHash = get_days('D','N');
			if(is_int($start) ){
				$this->start_date = $start;
			}else{
				$this->start_date = strtotime($this->cleanTime($start));
			}
			
			if($never_ends){
				eval("\$this->never_ends = $never_ends;");
			}else{
				$this->never_ends = false;
			}
			
			if(is_int($end) ){
				$this->end_date = $end;
			}else{
				if($this->never_ends===true){
					//echo 'end: ' . $end .'(' .  substr($this->cleanTime($start),0,strpos($start, ' ' )) . ")\n";					
					$this->end_date = substr($this->cleanTime($start),0,strpos($start, ' ' )) . substr($this->cleanTime($end), strpos($end, ' ' ));
					//echo $this->end_date;
					$this->end_date = strtotime($this->end_date);
				}else{
					$this->end_date = strtotime($this->cleanTime($end));
				}
			}
			//echo  $start . "\n" . date('m/d/Y H:i',$this->start_date) . "\n". $end . "\n" . date('m/d/Y H:i',$this->end_date);
			//die();
			$this->weekdays = $weekdays;
			if($this->weekdays){
				echo "";
				$this->days_array = ($this->weekdays == 'weekly') ? array($this->get_day_of_week($this->start_date)) : str_split($this->weekdays);
				$this->correct_end_day();
				$this->correct_start_day();
			}else{
				echo "";	
			}
			
			$this->Scheduler = &$Scheduler_pointer;
			$this->days_of_week = get_days('D');
			if($event_id){
				$this->event_id = $event_id;
			}

			//echo  $start . "\n" . date('m/d/Y H:i',$this->start_date) . "\n". $end . "\n" . date('m/d/Y H:i',$this->end_date);
		}
		
		function is_start_time_bigger($start, $end){ //timestamps as params
			
			$smins = intval(date('H', $start))*60 + intval(date('i', $start));
			if($smins == 0)return false;

			$smins = intval(date('H', $start-1))*60 + intval(date('i', $start-1));
			$emins = intval(date('H', $end-1))*60 + intval(date('i', $end-1));
			
			if($smins > $emins){
				return true;	
			}
			return false;
		}
		
		function fix_end_time($start, $str_end){
			if(intval(substr($str_end, 0, 4)) > $this->max_year ){
				$end = date('Y', $start) . substr($str_end, 4);
				$end = strtotime($end);
			}else{
				$end = strtotime($str_end);
			}
			return $end;
		}
		
		function get_times_array($weekdays, $start, $end){
			$times = array();
			if($weekdays){
				$when = ($weekdays == 'weekly') ? array(date("N", $start)) : str_split($weekdays);
			}

			$stime = (intval(date('i', $start)) > 0) ? $stime = date('g:i a', $start) : date("g a", $start);
			$etime = (intval(date('i', $end)) > 0) ? $etime = date('g:i a', $end) : date("g a", $end);
			
			if($weekdays){
				//foreach($when as $i => $d){
					$consec = 0;
					$consecTimes = array($this->daysHash[$when[0]-1]);
					//echo "------------------<br />";
					for($i=1; $i < sizeof($when);++$i){
						$d = $when[$i];
						
						$dp = $when[$i-1];
						//echo "d: $d, dp: $dp, consec: $consec\n<br />";
						if($d == ($dp+1)){
							++$consec;
							if( $i == (sizeof($when)-1) ){
								if($consec >=2){
									$consecTimes[sizeof($consecTimes)-1] .= '-' . $this->daysHash[$d-1];
								}else{
									$consecTimes[] .= $this->daysHash[$d-1];
								}
							}
						}else{
							if($consec >=2){
								$consecTimes[sizeof($consecTimes)-1] .= '-' . $this->daysHash[$dp-1];
							}else if($consec==1){
								$consecTimes[] = $this->daysHash[$dp-1];
							}
							$consecTimes[] = $this->daysHash[$d-1];
							$consec = 0;
						}
						//$when[$i] = $daysHash[$d-1];
					}

					
					$when = implode(', ', $consecTimes);
					//$times[] = $daysHash[$d-1] . ' ' . date("h:i A", $start) . ' - ' . date("h:i A", $end); 
					$times[] = $when . ', ' . $stime . ' - ' .$etime; 
				//}
				//$when = implode(', ', $when);
				
			}else{
				$times[] = date('D', $start) . ', ' . $stime . ' - ' . $etime;
			}
			return $times;
		}

		function correct_start_day(){
			$first_rep = intval($this->days_array[0]);
			$start_day = $this->get_day_of_week($this->start_date);
			//echo "first rep : $first_rep \n start day: $start_day \n";
			$days_diff = $first_rep - $start_day;
			if($days_diff != 0){
				$this->start_date += $days_diff*86400;
			}
			return;	
		}
		
		function correct_end_day(){
			$last_rep = intval($this->days_array[sizeof($this->days_array)-1]);
			$end_day = $this->get_day_of_week($this->end_date);
			
			$days_diff = $last_rep - $end_day;
			if($days_diff != 0){
				$this->end_date += $days_diff*86400;
			}	
			return;	
		}
		
		function cleanTime($js_date){
			//return substr($js_date, 0, strpos($js_date, 'GMT')-1);
			return $js_date;
		}
		
		function getStartDateTime(){
			return formatdatetime($this->start_date);
		}
		
		function getEndDateTime(){
			global $wpdb;
			if($this->never_ends===true){
				if($this->weekdays){
					$last_rep = intval($this->days_array[sizeof($this->days_array)-1]);
					$datetime = formatdatetime($this->end_date);
					$datetime = (1000+$this->Scheduler->max_year) . substr($datetime, 4);
					$datetime = $wpdb->get_var("SELECT ADDDATE('$datetime', $last_rep-DAYOFWEEK('$datetime'));");
				}else{
					$datetime = formatdatetime($this->end_date);
					$datetime = (1000+$this->Scheduler->max_year) . substr($datetime, 4);
				}
				return $datetime;
			}else{
				return formatdatetime($this->end_date);
			}
		}
		
		function get_day_of_week($timestamp, $format='N'){
			$day = intval(date($format,$timestamp));
			//return ($day == 0) ? 7 : $day;
			return $day;
		}		
		
		function getConfinedDateClause($option = ''){
			$start_date = $this->getStartDateTime();
			$end_date = $this->getEndDateTime();
			$ret = "Date('$start_date') <= Date(end_date) AND Date('$end_date') >= Date(start_date)";
			if($option == 'time_end'){
				$ret .= " AND Time('$start_date') < Time(end_date)";
			}else if(!$option){//make end_date -1
			//SELECT ADDTIME('2007-12-31 23:59:59.999999', '1 1:1:1.000002');

				$ret .= " AND Time('$start_date') < Time(ADDTIME(end_date, -1)) AND (Time(ADDTIME('$end_date', -1)) > Time(start_date) OR TIME(ADDTIME(start_date,-1)) > TIME(end_date) )";
				//$ret .= " AND ( Time('$start_date') < Time(ADDTIME(end_date, -1)) OR TIME(ADDTIME(start_date,-1)) > TIME(end_date) ) AND (Time(ADDTIME('$end_date', -1)) > Time(start_date) OR TIME(ADDTIME(start_date,-1)) > TIME(end_date) )";
			}
			return $ret;
		}
		
		function has_valid_timeframe(){
			global $wpdb;
			$query = "SELECT * FROM " . $this->Scheduler->t_e . " WHERE " . $this->getConfinedDateClause() .";";
			
			$overlaps = $wpdb->get_results($query);
			$confirmed = array();
			return $confirmed;
			//echo $query;
			if(sizeof($overlaps) == 0){
				if(isset($overlaps->weekdays) && empty($overlaps->weekdays) ){
					return array($this->getStartDateTime() ." - " . $this->getEndDateTime());
				}
			}else{				
				foreach($overlaps as $row){
					$start = strtotime($row->start_date);
					$end = strtotime($row->end_date);
					if($row->weekdays){
						$row->weekdays = ($row->weekdays == 'weekly') ? array($this->get_day_of_week($row->start_date)) : str_split($row->weekdays);
						foreach($this->days_array as $d){
							if(in_array($d, $row->weekdays) ){
								$confirmed[] = $this->days_of_week[$d-1] . '-weekly: ' . date("H:i", $this->start_date) . ' - ' . date("H:i", $this->end_date) . ' (' . date("m/d/y", $this->start_date) . ' - ' . date("m/d/y", $this->end_date) .')';
							}
						}
					}else if( date('N', $start) == date('N', $this->start_date) ){
						$confirmed[] = date('D', $start) . '-single: ' . date("H:i", $start) . ' - ' . date("H:i", $end) . ' (' . date("m/d/y", $start) . ' - ' . date("m/d/y", $end) .')';
					}
				}
			}
			return $confirmed;
		}
		
		function insert_dates($ID){
			global $wpdb;
			$query = "INSERT INTO " . $this->Scheduler->t_e ." (station_id, program_id, weekdays, start_date, end_date) VALUES(" . $this->Scheduler->id . ", $ID, '$this->weekdays', '".$this->getStartDateTime()."', '".$this->getEndDateTime()."');";
			$result = $wpdb->query($query);	
			if($result==false){
				return 'Error inserting: ' . $query;
			}
			$this->event_id = $wpdb->insert_id;
			return;	
		}
		
		function update_dates($ID){
			global $wpdb;
			$query = "UPDATE " . $this->Scheduler->t_e ." SET weekdays= '$this->weekdays', start_date = '".$this->getStartDateTime()."', end_date =  '".$this->getEndDateTime()."' WHERE ID = $this->event_id;";
			$result = $wpdb->query($query);	
			if($result===false){
				return 'Error updating dates: ' . $query;
			}
			return;	
		}
	}
}


if(!class_exists('SchedulerProgram') ){
	class SchedulerProgram {
		protected $ID;
		public $scheduler_event;
		public $fields;
		public $isNew;
		private $Scheduler;
		public $category_name;
		public $category_color;
                public $show_playlist;

		public function __construct($Scheduler, $props = array()){
			$this->Scheduler = &$Scheduler;
			
			$this->fields = $props;
			if( isset($props[program_id]) ){
				$this->isNew = false;
				$this->ID = $props[program_id];
			}else{
				$this->isNew = true;
			}
			unset($this->fields[program_id]);
			$this->category_name = $this->fields[category_name];
			$this->category_color = $this->fields[category_color];
			
			unset($this->fields[category_name], $this->fields[category_color]);
			if($this->fields[start_date] && $this->fields[end_date]){
				$this->scheduler_event =  new SchedulerEvent($this->Scheduler, $this->fields[start_date], $this->fields[end_date], $this->fields[weekdays], $this->fields[event_id], $this->fields[never_ends]);
				//echo $this->fields[never_ends] ? 'never ends: ' . $this->fields[never_ends] : 'nope';
				unset($this->fields[start_date], $this->fields[end_date], $this->fields[weekdays], $this->fields[never_ends]);
			}

		}

		function update_program(){
			global $wpdb;
			$query = "UPDATE " . $this->Scheduler->t_p . " SET ";
			foreach($this->fields as $field => $val){
				$query .= "$field = \"$val\",";
			}
			$query = substr($query, 0, -1);
			$query .= " WHERE ID = " . $this->ID . ";";
			
			$result = $wpdb->query($query);	
			if($result===false){
				return 'Error updating: ' . $query;
			}
			return;
		}
		
		function insert_program(){
			global $wpdb;
			$query = "INSERT INTO " . $this->Scheduler->t_p . " (".implode(",",array_keys($this->fields)).") VALUES(\"".implode("\",\"",array_values($this->fields))."\");";
			$result = $wpdb->query($query);	
			if(!$result){
				return 'Error inserting: ' . $query;
			}else{
				$this->ID = $wpdb->get_var('SELECT LAST_INSERT_ID();');	
			}
			return;
		}
		
		function bind_category(){
			global $wpdb;
			$query = "SELECT ID FROM " . $this->Scheduler->t_c . " WHERE category_name LIKE \"$this->category_name\" LIMIT 1;";
			$cat_id = $wpdb->get_var($query);	
			
			if(!$cat_id){
				$result = $wpdb->query("INSERT INTO " . $this->Scheduler->t_c . " (category_name, category_color) VALUES (\"$this->category_name\", \"$this->category_color\");");
				if(!$result){
					return 'Error inserting new category '. $this->category_name;
				}else{
					$cat_id = $wpdb->get_var('SELECT LAST_INSERT_ID();');	
				}
			}
			$query = "INSERT INTO " . $this->Scheduler->t_pc . " (program_id, category_id) VALUES ($this->ID, $cat_id);";
			$result = $wpdb->query($query);	
			if($result){
				return;	
			}else if($result == false){//duplicate primary key (photo_id, category_id), so try to update
				$query = "UPDATE " .$this->Scheduler->t_pc  . " SET category_id=$cat_id WHERE program_id=$this->ID;";
				$result = $wpdb->query($query);
			}else{
				return 'Error binding category to program: ' . $query;
			}
			
			return;
		}
		
		function save_new_program(){
			global $wpdb;
			
			$conflicts = $this->scheduler_event->has_valid_timeframe();
			if(!empty($conflicts)){
				$errors[] = "Overlapping events: ". implode("<br />\\n", $conflicts);
				return $errors;
			}
			
			$error = $this->insert_program();
			
			if(isset($error) ){
				$errors[] = $error;	
			}else{
				if($this->category_name){
					$error = $this->bind_category();
					if(isset($error) ){
						$errors[] = $error;	
					}
				}
								
				$error = $this->scheduler_event->insert_dates($this->ID);
				if(isset($error) ){
					$errors[] = $error;	
				}
			}

			return $errors;
		}
		
		function update_program_and_instance(){
			/*$conflicts = $this->scheduler_event->has_valid_timeframe();
			if(!empty($conflicts)){
				$errors[] = "Overlapping events: ". implode("<br />\\n", $conflicts);
				return $errors;
			}*/
			
			$error = $this->update_program();

			if(isset($error) ){
				$errors[] = $error;	
			}else{
				if($this->category_name){
					$error = $this->bind_category();
					if(isset($error) ){
						$errors[] = $error;	
					}
				}
				
				$error = $this->scheduler_event->update_dates($this->ID);
				if(isset($error) ){
					$errors[] = $error;	
				}
			}

			return $errors;
			
		}
		
		function add_event_to_program(){
			$conflicts = $this->scheduler_event->has_valid_timeframe();
			
			if(!empty($conflicts)){
				$errors[] = "Overlapping events: ". implode("<br />\\n", $conflicts);
				return $errors;
			}	
			
			$error = $this->scheduler_event->insert_dates($this->ID);
			if(isset($error) ){
				$errors[] = $error;	
			}
			return $errors;
		}
		
		function submit(){
			$str = '';

			$errors = array();
			if( $this->isNew ){//insert new program
				$errors = $this->save_new_program();
				if(empty($errors)){
					//$str .= "Event saved";
					die("[$this->ID, " . $this->scheduler_event->event_id . "]" );
				}
			}else if(isset($this->fields['event_id']) ) {//update existing program and given instance
			 	unset($this->fields[event_id]);
				$errors = $this->update_program_and_instance();
				if(empty($errors)){
					die("true");
				}
			}else{//bind new event to existing program
				$errors = $this->add_event_to_program();
				if(empty($errors)){
					die("[$this->ID, " . $this->scheduler_event->event_id . "]" );
				}
			}
			
			if(!empty($errors)){
				$str .= implode("<br />", $errors);
			}
			echo $this->Scheduler->process_alert($str);

			return;
			
		}
		
	}
}


if(!class_exists ('ProgramScheduler')) {
	class ProgramScheduler {
		public $plugin_url;
		public $plugin_prefix = 'ps_';
		public $t_p, $t_e, $t_c, $t_pc, $t_t, $t_pt;
		public $program;
		public $scheduler_event;
		public $schedule_name;
		public $max_year = 3000;
                public $id;

                static function find($id){
                  global $wpdb;
                  $record = new ProgramScheduler();
                  $r = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $record->t_s WHERE ID = %s", $id) );
                  if(is_null($r))return $r;

                  $record->schedule_name = $r->name;
                  $record->id = $r->ID;
                  return $record;
                }

                static function find_by_name($name){
                  global $wpdb;
                  $record = new ProgramScheduler();
                  $r = $wpdb->get_row( $wpdb->prepare("SELECT * FROM $record->t_s WHERE name like %s", $name) );
                  if(is_null($r))return $r;

                  $record->schedule_name = $r->name;
                  $record->id = $r->ID;
                  return $record;
                }


                public function __construct($schedule_name = ''){
			//global $wpdb;
			//$schedule_name = $this->clean_name($schedule_name);
			$this->plugin_prefix .= $schedule_name ? $schedule_name . '_' : '';
			$this->schedule_name  = $schedule_name;

                        $this->t_s = "ps_stations";
			$this->t_p = "ps_programs";
			$this->t_e = "ps_events";
			
			$this->t_c = "ps_categories";
			
			$this->t_pc = "ps_program_categories";
			$this->t_t = "ps_tags";
			$this->t_pt = "ps_program_tags";

			$this->plugin_url = get_bloginfo('url').'/wp-content/plugins/program_scheduler/';
			/*add_action( "admin_print_scripts", array(&$this, 'plugin_head') );
			add_action('wp_ajax_action_send_programs', array(&$this, 'php_get_programs'));
			add_action('wp_ajax_action_receive_event', array(&$this, 'php_receive_event'));
			add_action('wp_ajax_action_delete_event', array(&$this, 'php_delete_event'));
			*/

		}
                
		function delete_tables(){
			global $wpdb;
                        $table = $this->t_s;
                        //$wpdb->query("DROP TABLE $table;") ? "Error when deleting $table \n<br />" : '';
                        
			$table = $this->t_p;
			//$wpdb->query("DROP TABLE $table;") ? "Error when deleting $table \n<br />" : '';

			$table = $this->t_e;
			$wpdb->query("DROP TABLE $table;") ? "Error when deleting $table \n<br />" : '';
			
			$table = $this->t_c;
			//$wpdb->query("DROP TABLE $table;") ? "Error when deleting $table \n<br />" : '';
			
			$table = $this->t_pc;
			//$wpdb->query("DROP TABLE $table;") ? "Error when deleting $table \n<br />" : '';
			
			$table = $this->t_t;
			//$wpdb->query("DROP TABLE $table;") ? "Error when deleting $table \n<br />" : '';
			
			$table = $this->t_pt;
			//$wpdb->query("DROP TABLE $table;") ? "Error when deleting $table \n<br />" : '';
		}
		
		function create_tables(){
			global $wpdb;
			//need mysql >= 5.0.3
                        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

			$table = $this->t_s;
			$query = "CREATE TABLE $table (
			ID INT(9) NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			PRIMARY KEY (ID),
                        UNIQUE KEY (name)
			);";

                        dbDelta($query);

			$table = $this->t_p;
			$query = "CREATE TABLE $table (
			ID INT(9) NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			url VARCHAR(255),
			description TEXT,
			blog_id INT(9),
			post_id INT(9),
			color VARCHAR(255),
                        host_name VARCHAR(255),
                        host_bio TEXT,
                        host_photo_url TEXT,
                        host_bio_link TEXT,
                        host_wordpress_username VARCHAR(255),
                        show_playlist TINYINT(1) DEFAULT 0,
			PRIMARY KEY (ID),
			KEY (blog_id),
			KEY (post_id),
                        KEY (host_wordpress_username (3))
			);";

                        dbDelta($query);

			//need mysql >= 5.0.3
			$table = $this->t_e;
			$query = "CREATE TABLE $table (
			ID INT(9) NOT NULL AUTO_INCREMENT,
                        station_id INT(9) NOT NULL,
			program_id INT(9) NOT NULL,
			weekdays VARCHAR(7),
			start_date DATETIME,
			end_date DATETIME,
                        post_id INT(9),
			PRIMARY KEY(ID),
                        KEY (station_id),
                        KEY (program_id),
			KEY (weekdays),
                        KEY (post_id)
			);";
			dbDelta($query);
                        
			//need mysql >= 5.0.3
			$table = $this->t_c;
			$query = "CREATE TABLE $table (
			ID INT(9) NOT NULL AUTO_INCREMENT,
			category_name VARCHAR(255),
			category_color VARCHAR(255),
			PRIMARY KEY (ID)
			);";
			dbDelta($query);
			
			//need mysql >= 5.0.3
			$table = $this->t_pc;
			$query = "CREATE TABLE $table (
                        ID INT(9) NOT NULL AUTO_INCREMENT,
			program_id INT(9) NOT NULL,
			category_id INT(9) NOT NULL,
			PRIMARY KEY (ID),
			UNIQUE KEY (program_id, category_id)
			);";
			dbDelta($query);

			//need mysql >= 5.0.3
			$table = $this->t_t;
			$query = "CREATE TABLE $table (
			ID INT(9) NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			PRIMARY KEY (ID),
			UNIQUE KEY (name)
			);";
                        dbDelta($query);
                        
			$table = $this->t_pt;
			$query = "CREATE TABLE $table (
                        ID INT(9) NOT NULL AUTO_INCREMENT,
			program_id INT(9) NOT NULL,
			tag_id INT(9) NOT NULL,
			value VARCHAR(255),
			PRIMARY KEY (ID),
                        UNIQUE KEY(program_id, tag_id)
			);";
			dbDelta($query);
			
			return true;			
			
		}

                public function save_station(){
                  global $wpdb;
                  $wpdb->insert($this->t_s, array("name" => $this->schedule_name));
                  if($wpdb->insert_id === false) return false;
                          
                  return true;
                }

                public function delete_station(){
                  global $wpdb;

                  $r = $wpdb->query( $wpdb->prepare("DELETE FROM $this->t_e WHERE station_id = %s", $this->id) );
                  if($r===false)return false;
                  $r = $wpdb->query( $wpdb->prepare("DELETE FROM $this->t_s WHERE ID = %s", $this->id) );
                  if($r===false)return false;
                  return true;
                }

                #Not sure why we need this check -Pablo
		public function is_valid(){
			global $wpdb;
			
			$result = $wpdb->get_var("SELECT ID FROM  $this->t_p LIMIT 1;");
			if($result === false){
				return false;	
			}
			return true;
		}
		
		function clean_name($schedule_name){
			return str_replace('-', '_', sanitize_title($schedule_name));
		}
		
		function register_js_scripts(){
			//wp_register_script('jquery-ui-selectable', $this->plugin_url.'ui.selectable.js', array('jquery-ui-core'));
			wp_register_script('jquery-ui-datepicker', $this->plugin_url.'ui.datepicker.js', array('jquery-ui-core'));

			wp_register_script('schedule_week_editor', $this->plugin_url.'week_schedule_edit_js.php', array('jquery-ui-datepicker', 'jquery-ui-selectable', 'sack'));
                        wp_register_script('schedule_week_viewer', $this->plugin_url.'week_schedule_js.php', array('jquery-ui-datepicker', 'jquery-ui-selectable', 'sack'));

                        $js_params = array(
                          'url'=>get_bloginfo('url')
                        );
                        wp_localize_script( 'schedule_week_editor', 'WPScheduler', $js_params);
                        wp_localize_script( 'schedule_week_viewer', 'WPScheduler', $js_params);
		}
		
		function admin_print_scripts(){
				//wp_register_script('jquery-ui-selectable', $this->plugin_url.'ui.selectable.js', array('jquery-ui-core'));				
				//wp_register_script('jquery-ui-datepicker', $this->plugin_url.'ui.datepicker.js', array('jquery-ui-core'));
				
				if(preg_match("/schedule_editor\.php/", $_SERVER['REQUEST_URI'])){
					echo '<link rel="stylesheet" href="'.$this->plugin_url.'datepicker.css" type="text/css" />'."\n";
					echo '<link rel="stylesheet" href="'.$this->plugin_url.'editor.css" type="text/css" />'."\n";
					wp_enqueue_script( 'schedule_week_editor' );
				}else{
					//wp_register_script('schedule_week_viewer', $this->plugin_url.'week_schedule_js.php', array('jquery-ui-datepicker', 'jquery-ui-selectable', 'sack'));
					echo '<link rel="stylesheet" href="'.$this->plugin_url.'datepicker.css" type="text/css" />'."\n";
					echo '<link rel="stylesheet" href="'.$this->plugin_url.'viewer.css" type="text/css" />'."\n";
					wp_enqueue_script( 'schedule_week_viewer' );
					
				}
				//wp_register_script('schedule_admin', $this->plugin_url.'schedule_admin.js', array('jquery'));

				//wp_enqueue_script( 'jquery-ui-selectable' );
				
				
				
				//wp_enqueue_script( 'jquery-ui-datepicker' );
				//wp_enqueue_script( 'sack' );
				
				
				
				
				 //wp_print_scripts( array( 'sack' ));
	
		}
		function configure_channels(){
			echo "blah";	
			
		}
		

		function plugin_url(){
			$result = get_bloginfo('url').'/wp-content/plugins/program_scheduler/';
			return $result;
		}
		function process_alert($str){
			$str = str_replace('"', '&quot;', $str);
			$str = str_replace("'", '&#039;', $str);
			return "alert(\"$str\");";
		}
				
		function program_tablerow($program, $start_date, $doTime=true, $max_cell_height=800, $scale_height=false, $test_only = false){
			$start = strtotime($program->start_date);
			$eventHelper = new SchedulerEvent('', '', '');
			$end = $eventHelper->fix_end_time($start, $program->end_date);
			$sday = date('N',$start_date);
			$valid = false;
			if($program->weekdays){
				$when = ($program->weekdays == 'weekly') ? array(date("N", $start)) : str_split($program->weekdays);
				if($start_date){

					foreach($when as $d){
						if($sday == $d){
							$valid = true;
							break;
						}else if($eventHelper->is_start_time_bigger($start, $end)){
							$d2 = (($d+1) == 8) ? 1 : $d+1;
							if($sday == $d2 ){
								$valid = true;	
							}
						}
					}
					
				}
			}else{
				$d = date("N", $start);
				if($eventHelper->is_start_time_bigger($start, $end)){
					$d2 = (($d+1) == 8) ? 1 : $d+1;
					if($sday == $d2) $valid = true;
				}else if( $sday == $d ){
					$valid = true;
				}
			}
			
			if(!$valid)return false;
			
			if($test_only){
				return true;
			}
			
			$height = floatval(date("H",$end)) + floatval(date("i",$end))/60.00;
			$height -= floatval(date("H",$start)) + floatval(date("i",$start))/60.00;
			if($height < 0){
				$height += 24;	
			}
			$height = intval($max_cell_height*($height/24.0));
			//$height = $height/24.0;
			/*if($doTime){
				echo "<tr>";
				echo "<td colspan='2' style='text-align:left' >" . date("h:i A", $start) . "</td>";
				echo "</tr>";
			}*/
			if($scale_height){
				$rowheight = "height: ${height}px;";
			}
			
			echo "<tr style='${rowheight}background-color: ".$program->category_color."' >";
			if($doTime){
				echo "<td style='text-align:right;vertical-align: top;background-color: black; color:white; width:80px;' >" . date("h:i A", $start) . "</td>";
			}
			echo "<td>";
			echo "<span class='program_name'>$program->name</span>";
			echo $program->url ? "<br /><a href='$program->url' target='_blank' class='program_url'>URL</a>" : '';
			echo "</td>";
			//echo "<td>$program->url</td>";
			echo "<td>";
				if($program->weekdays){
					foreach($when as $i =>$d) $when[$i] = $eventHelper->daysHash[$d-1];
					$when = implode(', ', $when);
					echo "<br /> Airs weekly on <span class='program_weekdays'>$when</span> from ";
					echo date("h:i A", $start) . " to ";
					echo date("h:i A", $end);
					echo $program->schedule_name ? " on $program->schedule_name." : '';
				}else{
					echo "<br /> Airs on " . date("l, F j, Y", $start) . " from " . date("h:i A", $start) . ' to ' . date("h:i A", $end);
				}
			echo "</td>";
			//echo "<td>$program->end_date</td>";
			echo "</tr>";
			return true;
		}

		function parse_program_times($programs, $eventHelper = null){
			if($eventHelper == null) $eventHelper = new SchedulerEvent('', '', '');
			$noPrograms= sizeof($programs)> 0 ? false : true;
			if($noPrograms) return false;
			$cid = -1;
			$cs = "";
			$schedule_name = '';
			
			$rows = array();
			$row = array();
			foreach($programs as $program){
                          
				if($cid != $program->ID){
					if($cid > -1){
						$rows[] = $row;
						$row = array();
					}
					$row['name'] =  $program->name;
					$row['description'] =  $program->description;
					$row['url'] =  $program->url;
				}
				$start = strtotime($program->start_date);
				
				$end = $eventHelper->fix_end_time($start, $program->end_date);
				

                                if($row[$program->schedule_name]){
                                        $row[$program->schedule_name] .=  "<div>" . implode(', ', $eventHelper->get_times_array($program->weekdays, $start, $end) ) . "</div>";
                                }else{
                                        $row[$program->schedule_name] =  "<div>" . implode(', ', $eventHelper->get_times_array($program->weekdays, $start, $end) ) . "</div>";
                                }

				$cid = $program->ID;
				$cs = $program->schedule_name;
			}
			if($cid > -1){
				$rows[] = $row;
			}
			return $rows;
			
		}
                /* To-do: check ID references*/
		function get_all_programs(){
			global $wpdb;
			$fields = 'p.ID, e.ID AS event_id, name, url, description, blog_id, p.post_id, color, category_name, category_color, weekdays, start_date, end_date, date_format(end_date, "%c/%d/%Y %k:%i:%s") as mod_end_date';
			$whichtables = "$this->t_e as e JOIN $this->t_p as p ON (p.ID=e.program_id) LEFT JOIN $this->t_pc as cr ON (p.ID=cr.program_id) LEFT JOIN $this->t_c as c ON (cr.category_id = c.ID)";
			
			$query = "SELECT DISTINCT $fields FROM $whichtables";
                        if($this->id) $query .= " WHERE e.station_id = $this->id";
                        $query .= " ORDER BY name";

			$results = $wpdb->get_results($query);
			return $results;
		}
		
		function get_listing($ID='', $station_id=''){
			global $wpdb;
			
                        $query = "SELECT DISTINCT s.name as schedule_name, tt.ID as event_id, p.ID, p.name, url, description, blog_id, p.post_id, color, category_name, category_color, weekdays, start_date, end_date FROM $this->t_e as tt JOIN $this->t_p as p ON (p.ID=tt.program_id) LEFT JOIN $this->t_pc as cr ON (p.ID=cr.program_id) LEFT JOIN $this->t_c as c ON (cr.category_id = c.ID) LEFT JOIN ps_stations as s ON (s.ID=tt.station_id)";
                        if(!empty($station_id)) $query .= " WHERE s.ID=$station_id";
                        if(!empty($ID)) $query .= empty($station_id) ? " WHERE p.ID=$ID" : " AND p.ID=$ID";
			$query .= " ORDER BY p.name, schedule_name, WEEKDAY(start_date), Time(start_date);";
			//echo $query."\n";
                        $results = $wpdb->get_results($query);
			return $results;
		}
		
		function php_get_categories(){
			global $wpdb;
			$query = "SELECT * FROM $this->t_c;";
			$results = $wpdb->get_results($query);
			
			if($results === false){
				echo $this->process_alert($query);
				exit();
			}else if(sizeof($results) == 0){
				die('false');
			}
			foreach($results as $cat){
				echo "categories.name[" . $cat->ID .	"] = \"" . $cat->category_name . "\";";
				echo "categories.color[" . $cat->ID .	"] = \"" . $cat->category_color . "\";";
			}
		}

		function get_categories(){
			global $wpdb;
			
			$query = "SELECT * FROM " . $this->t_c . " ORDER BY category_name;";
			$results = $wpdb->get_results($query);
			if(sizeof($results) ==0){
				return false;				
			}				
			return $results;
		}
		
		function get_link_name($program){
			return $program->url ? "<a href='$program->url' >$program->name</a>" : $program->name;	
		}

		function format_single_row($allowed_schedules, $row, $class='single_details', $popup = true){
			$str = "<div class='$class'>";
			if($popup){
				$str .= "<div><span class='single_program_name' >";
				$str .= $row['url'] ? "<a href='" . $row['url'] . "' >" . $row['name'] . "</a>" : $row['name'];
				$str .= "</span>";
				
				$str .= "<span class='clickable' onclick=\"jQuery(this).parent().parent().remove();\" >Close</span>";
				$str .= "</div>";
			}
			$str .= "<div class='single_program_content'>";
			$str .= $row['description'] ? "<div class='single_program_description'>".$row['description']."</div>" : '';

			$str .= "<div class='single_program_times'> <span>Airdates and Times:</span><br />";
                        foreach($allowed_schedules as $schedule_name){
                          $str .= $row[$schedule_name] ? "<span class='single_program_channel'>$schedule_name</span> <div>".$row[$schedule_name]."</div><br/>" : '';
                        }
			$str .= "</div>";
			
			$str .= $row['url']? "<div class='single_program_url'><a href='".$row['url']."'>Website link &raquo;</a></div>" : '';
			
			$str .= "</div>";
			$str .= "</div>";
			return $str;
		}
		
		function php_get_program($ID){
			global $wpdb;
			$airtimes = $this->get_listing($ID, '');//$this->id ? $this->id : '');
                        $schedules = array();
                        foreach($airtimes as $item){
                          if(!in_array($item->schedule_name, $schedules)) $schedules[] = $item->schedule_name;
                        }
			$rows = $this->parse_program_times($airtimes);

			if(sizeof($rows) <= 0 ) return '';

			echo "<div class='single_details'>";

			echo "<div><span class='single_program_name' >";
			echo $rows[0]['url'] ? "<a href='" . $rows[0]['url'] . "' >" . $rows[0]['name'] . "</a>" : $rows[0]['name'];
			echo "</span>";
			echo "<span class='clickable single_close_button' onclick=\"jQuery(this).parent().parent().remove();\" >x</span>";
			echo "</div>";

			echo "<div class='single_program_content'>";
			echo $rows[0]['description'] ? "<div class='single_program_description'>".$rows[0]['description']."</div>" : '';

			echo "<div class='single_program_times'> <span>Airdates and Times:</span><br />";
                        foreach($schedules as $schedule_name){
                          echo $rows[0][$schedule_name] ? "<span class='single_program_channel'>$schedule_name</span> <div>".$rows[0][$schedule_name]."</div>" : '';
                        }
			echo "</div>";
			
			echo $rows[0]['url']? "<div class='single_program_url'><a href='".$rows[0]['url']."'>Website link &raquo;</a></div>" : '';
			
			echo "</div>";
			echo "</div>";
		}

		function php_get_programs($start_date = '', $end_date = '', $sql = ''){
			global $wpdb;
			$start_date = $_POST[start_date] ? $_POST[start_date] : $start_date;
			$end_date = $_POST[end_date] ? $_POST[end_date] : $end_date;
			$content = $_POST['content'] ? $_POST['content'] : 'full';
			$where = array();
                        
			if($start_date && $end_date ){
				$this->scheduler_event = new SchedulerEvent($this, $start_date, $end_date);
				if( !isset($_POST['content']) ){
					$date_clause = $this->scheduler_event->getConfinedDateClause();
				}else{
					$date_clause = $this->scheduler_event->getConfinedDateClause('time');
				}
			}
			if( isset($_POST[event_id]) ){
				$event_id_clause = 'tt.ID = ' . $_POST[event_id];
			}
			if($content == 'full'){
                          $fields = 'p.ID as program_id, tt.ID as event_id, name, url, description, blog_id, p.post_id, color, category_name, category_color, weekdays, start_date, end_date, date_format(end_date, "%c/%d/%Y %k:%i:%s") as mod_end_date';
                        }else{
                          $fields = 'ID as program_id, name, color';
                        }
                        if($content == 'minimal'){//just need list of programs
                          $whichtables =  "$this->t_p as p";
                        }else{
                          $whichtables = "$this->t_e as tt JOIN $this->t_p as p ON (p.ID=tt.program_id) LEFT JOIN $this->t_pc as cr ON (p.ID=cr.program_id) LEFT JOIN $this->t_c as c ON (cr.category_id = c.ID)";
                          $where[] = "tt.station_id = " . $this->id;
                        }
			
			$query = "SELECT DISTINCT $fields FROM $whichtables";

                        if($event_id_clause) $where[] = $event_id_clause;
                        
			if($event_id_clause && $date_clause) $where[] = $date_clause;

			if( isset($_POST['content']) && $content != 'minimal' && !isset($_POST['program_id']) && !isset($_POST['event_id']) ){
                          $where[] = "(TIMEDIFF(TIME(ADDTIME(end_date, -1)), TIME(start_date) ) >= '00:15:00' AND TIME(ADDTIME(end_date, -1)) >= TIME(start_date))";
			}
                        
                        if($sql) $where[] = $sql;
                        if( sizeof($where) > 0 ) $query .= " WHERE " . implode(" AND ", $where);

			$query .= ($content == 'full') ? " ORDER BY Time(start_date) ASC,  TIMEDIFF(TIME(ADDTIME(end_date,-1)), TIME(start_date)) DESC ;" : " ORDER BY name;";

			//if(preg_match("/schedule_editor\.php/", $_SERVER['REQUEST_URI']) || preg_match("/schedule_viewer\.php/", $_SERVER['REQUEST_URI']) ){
			//	echo $query;
			//}

			$results = $wpdb->get_results($query);
			if( !isset($_POST['content']) ){
				return $results;	
			}
			
			$programs = array();
			if($results === false){
				echo $this->process_alert($query);
				exit();
			}else if(sizeof($results) == 0){
				die('false');
			}
			echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
			echo "\n<programs>";
			foreach($results as $row){

				echo "\n<program>";
				$programs[] = $row->program_id;
				echo "\n<ID>".$row->program_id."</ID>";
				echo "\n<name>".htmlspecialchars($row->name)."</name>";
				echo "\n<color>".$row->color."</color>";
				echo $row->category_color ? "\n<category_color>".$row->category_color."</category_color>" : '';
				if($content == 'full'){
					echo "\n<event_id>".$row->event_id."</event_id>";
					echo "\n<start_date>".JFormatDateTime($row->start_date)."</start_date>";	
					//echo "\n<end_date>".JFormatDateTime($row->end_date)."</end_date>";
					echo "\n<end_date>".$row->mod_end_date."</end_date>";
					echo "\n<weekdays>" .$row->weekdays."</weekdays>";
					echo "\n<never_ends>";
					echo (intval(substr($row->end_date, 0, 4)) > $this->max_year) ? 'true' : 'false';
					echo "</never_ends>";
					//echo ($row->weekdays == 'weekly') ? $this->scheduler_event->get_day_of_week($row->start_date) : $row->weekdays;
					
					if($event_id_clause){
                                          $properties = array('description', 'url', 'blog_id', 'post_id', 'category_name',
                                                              'host_name', 'host_bio', 'host_photo_url', 'host_bio_link',
                                                              'host_wordpress_username', 'show_playlist');
                                          foreach($properties as $property){
                                            eval("\$val = \$row->$property;");
                                            if($val) echo "\n<$property>" . ent2ncr($val) . "</$property>";
                                          }
						//echo $row->category_color ? "\n<category_color>".$row->category_color."</category_color>" : '';
						//echo "\n<category_name>None</category_name>";
					}
				}
				echo "\n</program>";
				
				//break;
			}
			echo "\n<query><![CDATA[$query]]></query>";
			echo "\n</programs>";
			
			//echo json_encode($programs);
			//echo "alert(\"" . implode(',' , $programs) . "\");";
			
		}
		
		function php_receive_event(){
			global $wpdb;
			$event = $_POST;
			unset($event[action], $event[cookie], $event[rndval]);
			
			$this->program = new SchedulerProgram($this, $event);
			$this->program->submit();
		}
		
		function delete_event($event_id, $ID =''){
			global $wpdb;
			$query = "DELETE FROM $this->t_e WHERE ID = $event_id;";
			if($results = $wpdb->query($query)){
				die('true');
			}else{
				die($this->process_alert($query));
			}
		}

		function php_delete_event(){
			global $wpdb;
			//$ID = $_POST['program_id'];
			$event_id = $_POST['event_id'];
			
			$this->delete_event($event_id);
		}

	}
} 
?>
