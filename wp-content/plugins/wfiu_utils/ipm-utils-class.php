<?php
if (!class_exists('IPM_Utils')) {

	class IPM_Utils {
		public $google_api_key = 'ABQIAAAAIVgh1deAtW1cu85uZMLCBhTnn3xAjQHqpFtU4TMbi6qQki1QvhSoa7FSZMKpfIEdNdXx68sI8xbwhA';

		public function __construct($title = 'IPM Plugins'){
			if(function_exists('add_site_option')){//single wordpress install
				if($slak=get_site_option('google_api_key')){
					$this->google_api_key = $slak;
				}
			} else if($slak=get_option('google_api_key')){//wordpress mu
				$this->google_api_key = $slak;
			}
			$this->title = $title;
		}

		function main_page_path(){
			global $menu;
			$exists = false;
			$found = -1;
			foreach($menu as $key => $m){
				if($m[0] == $this->title){
					$exists = true;
					$found = $key;
				}
			}
			if($exists){
				return $menu[$key][2];
			}

			return  __FILE__;
		}

		function add_page_main(){
			global $menu;
			$exists = false;
			$found = -1;
			foreach($menu as $key => $m){
				if($m[0] == $this->title){
					$exists = true;
					$found = $key;
				}
			}
			if(!$exists){
				add_menu_page($this->title, $this->title, 7, __FILE__, array(&$this,'ipm_plugins_homepage'));
			}
			/*********SUBMENU***************/
			global $submenu;

			if($submenu[$this->main_page_path()][0][0] == $this->title){
				unset($submenu[$this->main_page_path()][0]);
			}
		}


		function ipm_plugins_homepage(){

	?>
	<div class='wrap'>
	<h2>Plugins Home</h2>

	<?php
	global $submenu;

	if($submenu[$this->main_page_path()][0][0] == $this->title){
		unset($submenu[$this->main_page_path()][0]);
	}

	if(sizeof($submenu[$this->main_page_path()])>0){
		echo "<ul>";
		foreach($submenu[$this->main_page_path()] as $sm){
			$name = $sm[0];
			echo "<li><b>".$name ."</b>: <a href='".get_bloginfo('url')."/wp-admin/admin.php?page=$name' > Go to page</a></li>";

		}
		echo "</ul>";
	}
	?> 
	</div>
	<?php
		}

		

		function add_page_google_api_key(){
			/******************************/
			global $submenu;

			if(get_bloginfo('version')>= 2.7 ){
			
				$exists = false;
				foreach($submenu[$this->main_page_path()] as $key => $m){
					if($m[0] == 'Google API Key'){
						$exists = true;
						//$found = $key;
					}
				}
				if(!$exists){
					$this->add_page_main();
					add_submenu_page($this->main_page_path(), 'Google API Key', 'Google API Key', 7, 'Google API Key', array(&$this,'google_api_key_form'));
				}
			}else{
				global $menu;
				$exists = false;

				foreach($menu as $key => $m){
					if($m[0] == 'Google API Key'){
						$exists = true;
					}
				}
				if(!$exists){
					add_menu_page('Google API Key', 'Google API Key', 7, 'Google API Key', __FILE__, array(&$this,'google_api_key_form'));
				}

			}
		}

		function get_google_coordinates($location){
			global $google_api_key;
			$apicall = "http://maps.google.com/maps/geo?q=".urlencode($location)."&output=csv&key=$google_api_key";
			$apiresult = file_get_contents($apicall);
			preg_match("/.+,.+,(.+),(.+)/", $apiresult, $matches);
			$lat = $matches[1];
			$long = $matches[2];
			return array($lat, $long);
		}

		function get_files_array($dir){
			$farray = array();
			if ($handle = opendir($dir)) {
			
			    while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
				    $farray[] = $file;
				}
			    }
			    closedir($handle);
			}
			return $farray;
		}

		function JFormatDateTime($datetime){
			if(is_int($datetime) ){
				$timestamp = $datetime;
			}else{
				$timestamp= strtotime($datetime);
			}
		
			return date("n/j/Y H:i:s",$timestamp);
		}	

		function formatdatetime($timestamp){
			return date("Y-m-d H:i:s",$timestamp);			
		}	

		function mu_get_post_categories($blog_id, $post_id){
			global $wpdb;
			$current_blog_id = $wpdb->blogid;
			switch_to_blog( $blog_id );
			$categories = wp_get_post_categories( $post_id );

			$cats = array();
			foreach( $categories as $c ) {
				$cat = get_category( $c );
				//$cats[] = $cat->name;
				$cats[$cat->cat_ID] = $cat->name;
			}
		
			switch_to_blog( $current_blog_id );
			return $cats;
		}

		function mu_get_post_tags($blog_id, $post_id){
			global $wpdb;
			$current_blog_id = $wpdb->blogid;
		
			switch_to_blog( $blog_id );
			//$tags = implode( ', ', wp_get_post_tags( $post_id, array('fields' => 'names') ) );
			$tags = wp_get_post_tags( $post_id, array('fields' => 'slugs'));
		
			switch_to_blog( $current_blog_id );
			return $tags;
		}

		function is_post_force_publish($thispost){
			if((strtotime($thispost->post_date) > time()) && ($thispost->post_status =='publish')){
				return true;
			}else{
				return false;
			}
		
		}

		function wp_exist_post($id) {
			global $wpdb;
			$r=$wpdb->get_row("SELECT ID FROM ".$wpdb->prefix."posts WHERE ID = '" . $id . "'", 'ARRAY_A');
			return sizeof($r)>0 ? true : false;
		}

		function maybe_create_table($table_name, $create_ddl) {
			GLOBAL $wpdb;
			foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
				if ($table == $table_name) {
					return true;//tables exist already
				}
			}
			//didn't find it try to create it.
			$q = $wpdb->query($create_ddl);
			// we cannot directly tell that whether this succeeded!
			foreach ($wpdb->get_col("SHOW TABLES",0) as $table ) {
				if ($table == $table_name) {
					return false; //no error
				}
			}
			return true;//yes error, table not created
		}

		function convertquotes($object){
			if(is_array($object)){
				foreach($object as $key=>$val){
					$object[$key]= htmlspecialchars($object[$key], ENT_QUOTES);
					//echo $array[$key]. '&quot;';
				}
				return $object;
			}else{
				return htmlspecialchars($object, ENT_QUOTES);

			}
		}

		function table_headers($titles){
		
			$arg_list = func_get_args();
			if(is_array($titles) && sizeof($arg_list)==1){
				$arg_list = $titles;	
			} 
		
			$retStr = "\n<tr>\n";
			$percent = intval(100/sizeof($arg_list));
			foreach($arg_list as $item) {
				if(is_array($item) ){
					$retStr .= "<th $item[0] >$item[1]</th>\n";//style='width:$percent%'
				}else{
					$retStr .= "<th >$item</th>\n";//style='width:$percent%'
				}
			}
			$retStr .= "</tr>\n";
			return $retStr;
		}

		function table_data_fill($cols){
			$arg_list = func_get_args();

			$retStr = "\n<tr>\n";
			foreach($arg_list as $col) {
				if(is_array($col)){
					$props = $col[0];
					$text = $col[1];
					$retStr .= "<td $props >$text</td>\n";
				}else{
					$retStr .= "<td>$col</td>\n";
				}
			}
			$retStr .= "</tr>\n";
			return $retStr;
		}

		function prepareDHTML($text_array){
			$return ='';
			foreach($text_array as $textblock){
				if($textblock['escape']===true){
					$return .= addslashes($textblock['text']);
				}else{
					$return .= $textblock['text'];
				}
			}

			return $return;
		}

		function makeSelectBox($name,$options){
		
			$str = "<select name='$name' id='$name'>";
			foreach ($options as $option){
				$str .= "<option value=\"$option\">$option</option>";

			}
			$str .="</select>";
			return $str;
		}

		function shorten_name($name,$max_chars){

			//Make sure that we are not making it longer with that ellipse
			if((strlen($name) + 3) > $max_chars)
			{
				//Trim the title
				$name = substr($name, 0, $max_chars - 1);
				//Make sure we can split at a space, but we want to limmit to cutting at max an additional 25%
				if(strpos($name, " ", .75 * $max_chars) > 0)
				{
					//Don't split mid word
					while(substr($name,-1) != " ")
					{
						$name = substr($name, 0, -1);
					}
				}
				//Remove the whitespace at the end and add the hellip
				$name = rtrim($name) . '&hellip;';
			}
			return $name;
		}

		function order_object_array($objArray, $field){
			$ordered = array();
			$fields =  array();
			foreach($objArray as $key => $obj){
				$fields[$key] = $obj[$field];
			}
			asort($fields);
			foreach($fields as $new_key =>	$val){
				$ordered[]=$objArray[$new_key];
			}
			return $ordered;

		}

		function order_array_by_keys($objArray){
		
			$keys =  array_keys($objArray);
			sort($keys);
		
			$ordered = array();
			foreach($keys as $index => $ordered_key){
				$ordered[$ordered_key]=$objArray[$ordered_key];
			}
			return $ordered;
		}

		function get_days($format, $sort_by = 'N'){
			$days = array();
			for($i=0;$i<7;++$i){
				$cur = 3600*24*$i + time();
				$days[date($format,$cur)]=intval(date($sort_by,$cur));
			}
			asort($days);
			return array_keys($days);
		}

		function single_google_map($div_id = 'map', $ids= array('location', 'latitude', 'longitude'), $found_zoom = 13, $start_zoom = 2, $clat = 43.0, $clong = -99.0){

		$ret = "<script type='text/javascript'>
	//jQuery(document).ready(function(){
			var _map;
			var centerLatitude = $clat;
			var centerLongitude = $clong;

			var startZoom = $start_zoom;
			var foundZoom = $found_zoom;

			function executeCenter(){
				lat=document.getElementById(\"$ids[1]\").value;
				long=document.getElementById(\"$ids[2]\").value;

				if(lat==0 && long ==0){
					return;
				}
				setCenter(lat,long,foundZoom);
				var latlng = new GLatLng(lat, long);
				if(document.getElementById(\"$ids[0]\").value){
					_map.openInfoWindow(latlng, document.createTextNode(document.getElementById(\"$ids[0]\").value));
				}
			}

			function setCenter(clat, clong, zoom){
				//alert(clat+' ' + clong + ' ' +zoom);
				_map.setCenter(new GLatLng(clat , clong ), zoom);
			}


			function init() {
				if (GBrowserIsCompatible()) {
					_map = new GMap2(document.getElementById(\"$div_id\"));
					_map.setUIToDefault();//_map.addControl(new GSmallMapControl());
					_map.setCenter(new GLatLng(centerLatitude , centerLongitude ), startZoom );";

		$ret .= $ids[0] ? 'executeCenter();' : '';
		$ret .="
				}
			}
			//_functionToCall = 'executeCenter()';
			//jQuery('#map').after(\"<div style='text-align:center'><span class='button' id='execute_center_button'>Show in map</span></div>\");
			//jQuery('#execute_center_button').click(function(){
			//	get_google_coordinates(document.getElementById(\"$ids[0]\").value, \"$ids[1]\", \"$ids[2]\");

			//	executeCenter();
			//});
			init();
			window.onunload = GUnload;
	//});
			</script>";

			return $ret;
		}


		/***
		 * @snarf the first n words from a string
		 *
		 * @param string $string
		 *
		 * @param int $num
		 *
		 * @param string $tail
		 *
		 * @return string
		 */
		function first_words($string, $num, $tail='&nbsp;...'){
				/** words into an array **/
				$words = str_word_count($string, 2);

				/*** get the first $num words ***/
				$firstwords = array_slice( $words, 0, $num);

			if(sizeof($words)>$num){
				/** return words in a string **/
				return  implode(' ', $firstwords).$tail;
			}else{
				$string;
			}
		}


		function google_api_key_form(){
	?>

	<div class='wrap'>
	<?php 

	if ($_POST) {
	if(function_exists('add_site_option')){
		add_site_option('google_api_key', $_POST[store_locator_api_key]) or update_site_option('google_api_key', $_POST[store_locator_api_key]);

	}else{
		update_option('google_api_key', $_POST[store_locator_api_key]);
	}
	//update_option('sl_language', $_POST[sl_language]);

	//$sl_google_map_arr=explode(":", $_POST[google_map_domain]);
	//update_option('google_map_country', $sl_google_map_arr[0]);
	//update_option('google_map_domain', $sl_google_map_arr[1]);
	//update_option('sl_distance_unit', $_POST[sl_distance_unit]);

	print "<div class='highlight'>".__("Successful Update", $text_domain)."</div> <!--meta http-equiv='refresh' content='0'-->";
	}

	print "<h2>".__("Update Your Google API Key", $text_domain)."</h2><br><form action='' method='post'><table class='widefat'><thead><tr ><td colspan='1'>".__("Your API Key Status", $text_domain).":</td><td>";
	//load_plugin_textdomain('api-key');
	//$text_domain='api=key';
	if(function_exists('get_site_option')){
		$slak=get_site_option('google_api_key');
	}else{
		$slak=get_option('google_api_key');
	}

	if (!$slak || trim($slak)=='') {
		print "<div style='border:solid red 1px; padding:5px; background-color:pink; width:400px; color:black'>".__("Please Update Your Google API Key", $text_domain)."</div>";
	}
	else {
		print "<div style='border:solid green 1px; padding:5px; background-color:LightGreen; width:400px; color:black'>".__("API Key Submitted", $text_domain)."</div>";
	}

	print "</td></tr></thead><tr><td>".__("Google API Key", $text_domain).":</td><td><input name='store_locator_api_key' value='$slak' size='100'><br><br><div class=''><strong>".__("Note: <br>", $text_domain)."</strong>".__("Each API Key is unique to one web domain. To obtain a Google API Key for this domain ($_SERVER[HTTP_HOST]):<br><br><b>Step 1.</b> Log into your Google Account<br>(If you use any Google Product, such as Gmail, Google Docs, etc., then you have a Google Account. If not, <a href='https://www.google.com/accounts/' target='_blank'>you can sign up for one</a>) <br><br><b>Step 2.</b> Visit: <a href='http://code.google.com/apis/maps/signup.html' target='_blank'>http://code.google.com/apis/maps/signup.html</a> and submit your domain, and receive an API Key from Google", $text_domain)."</div></td></tr>";

	/*print "<tr><td>".__("Choose Your Language", $text_domain).":</td><td><select name='sl_language'>";
	$the_lang["United States"]="en_EN";
	$the_lang["France"]="fr_FR";

	foreach ($the_lang as $key=>$value) {
		$selected=(get_option('sl_language')==$value)?" selected " : "";
		print "<option value='$value' $selected>$key ($value)</option>\n";
	}
	print "</td></tr>";*/


	/*
	print "<tr><td>".__("Select Your Location", $text_domain).":</td><td><select name='google_map_domain'>";
	$the_domain["United States"]="maps.google.com";
	$the_domain["Austria"]="maps.google.com.at";
	$the_domain["Australia"]="maps.google.com.au";
	$the_domain["Bosnia and Herzegovina"]="maps.google.com.ba";
	$the_domain["Belgium"]="maps.google.be";
	$the_domain["Brazil"]="maps.google.com.br";
	$the_domain["Canada"]="maps.google.ca";
	$the_domain["Switzerland"]="maps.google.ch";
	$the_domain["Czech Republic"]="maps.google.cz";
	$the_domain["Germany"]="maps.google.de";
	$the_domain["Denmark"]="maps.google.dk";
	$the_domain["Spain"]="maps.google.es";
	$the_domain["Finland"]="maps.google.fi";
	$the_domain["France"]="maps.google.fr";
	$the_domain["Italy"]="maps.google.it";
	$the_domain["Japan"]="maps.google.jp";
	$the_domain["Netherlands"]="maps.google.nl";
	$the_domain["Norway"]="maps.google.no";
	$the_domain["New Zealand"]="maps.google.co.nz";
	$the_domain["Poland"]="maps.google.pl";
	$the_domain["Russia"]="maps.google.ru";
	$the_domain["Sweden"]="maps.google.se";
	$the_domain["Taiwan"]="maps.google.tw";
	$the_domain["United Kingdom"]="maps.google.co.uk";

	foreach ($the_domain as $key=>$value) {
		$selected=(get_option('sl_google_map_domain')==$value)?" selected " : "";
		print "<option value='$key:$value' $selected>$key ($value)</option>\n";
	}
	print "</select></td></tr>";

	print "<tr><td>".__("Select Distance Unit", $text_domain).":</td><td><select name='sl_distance_unit'>";

	$the_distance_unit["".__("Kilometers", $text_domain).""]="km";
	$the_distance_unit["".__("Miles", $text_domain).""]="miles";
	foreach ($the_distance_unit as $key=>$value) {
		$selected=(get_option('sl_distance_unit')==$value)?" selected " : "";
		print "<option value='$value' $selected>$key</option>\n";
	}

	print "</select><br><br><input type='submit' value='".__("Update", $text_domain)."' class='button'></td></tr>";
	*/
	print "<tr><td>&nbsp;</td><td><input type='submit' value='".__("Update", $text_domain)."' class='button'></td></tr>";
	print "</table></form>";

	?></div>

	<?php
		}

	}/*class definition ends here*/
}

?>
