<?php

$google_api_key = 'ABQIAAAAIVgh1deAtW1cu85uZMLCBhTnn3xAjQHqpFtU4TMbi6qQki1QvhSoa7FSZMKpfIEdNdXx68sI8xbwhA';
if(function_exists('add_site_option')){
	if($slak=get_site_option('google_api_key')){
		$google_api_key = $slak;
	}
} else if($slak=get_option('google_api_key')){
	$google_api_key = $slak;
}

if(!function_exists('get_files_array')){
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
}

if(!function_exists('JFormatDateTime')){
	function JFormatDateTime($datetime){
		if(is_int($datetime) ){
			$timestamp = $datetime;
		}else{
			$timestamp= strtotime($datetime);
		}
		
		return date("n/j/Y H:i:s",$timestamp);
	}	
}

if(!function_exists('formatdatetime')){
	function formatdatetime($timestamp){
		return date("Y-m-d H:i:s",$timestamp);			
	}	
}

if(!function_exists('my_get_post_categories')){
	function my_get_post_categories($blog_id, $post_id){
		global $wpdb;
		$current_blog_id = $wpdb->blogid;
		switch_to_blog( $blog_id );
		//echo "blog id: $blog_id, $post_id (".$wpdb->blogid." )<br />";
		$categories = wp_get_post_categories( $post_id );
		//print_r($categories);
		//echo "<br />";
		$cats = array();
		foreach( $categories as $c ) {
			$cat = get_category( $c );
			//$cats[] = $cat->name;
			$cats[$cat->cat_ID] = $cat->name;
		}
		
		switch_to_blog( $current_blog_id );
		return $cats;
	}

}

if(!function_exists('my_get_post_tags')){
	function my_get_post_tags($blog_id, $post_id){
		global $wpdb;
		$current_blog_id = $wpdb->blogid;
		
		switch_to_blog( $blog_id );
		//$tags = implode( ', ', wp_get_post_tags( $post_id, array('fields' => 'names') ) );
		$tags = wp_get_post_tags( $post_id, array('fields' => 'slugs'));
		
		switch_to_blog( $current_blog_id );
		return $tags;
	}

}

if(!function_exists('is_post_force_publish')){
	function is_post_force_publish($thispost){
		if((strtotime($thispost->post_date) > time()) && ($thispost->post_status =='publish')){
			return true;
		}else{
			return false;
		}
		
	}	
}

if(!function_exists('wp_exist_post')) {
	function wp_exist_post($id) {
		global $wpdb;
		$r=$wpdb->get_row("SELECT ID FROM ".$wpdb->prefix."posts WHERE ID = '" . $id . "'", 'ARRAY_A');
		return sizeof($r)>0 ? true : false;
	}
}

if(!function_exists('wfiu_do_main_page')){
	function wfiu_do_main_page(){
		global $menu;
		$exists = false;
		$found = -1;
		foreach($menu as $key => $m){
			if($m[0] == 'IPM Plugins'){
				$exists = true;
				$found = $key;
			}
		}
		if(!$exists){
			add_menu_page('IPM Plugins', 'IPM Plugins', 7, ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php');
		}
		/******************************/
		global $submenu;

		if($submenu['wfiu_utils/wfiu_plugins_homepage.php'][0][0] == 'IPM Plugins'){
			unset($submenu['wfiu_utils/wfiu_plugins_homepage.php'][0]);
		}
		/*else{
			unset($menu[$found]);

		}*/
	}
}

if(!function_exists('wfiu_google_api_key')){
	function wfiu_google_api_key(){
		/******************************/
		global $submenu;

		if(get_bloginfo('version')>= 2.7 ){
			
			$exists = false;
			foreach($submenu['wfiu_utils/wfiu_plugins_homepage.php'] as $key => $m){
				if($m[0] == 'Google API Key'){
					$exists = true;
					//$found = $key;
				}
			}
			if(!$exists){
				wfiu_do_main_page();
			//}
			//if(!is_array($submenu['wfiu_utils/google-api-key.php'])){
				add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Google API Key', 'Google API Key', 7, ABSPATH.PLUGINDIR.'/wfiu_utils/google-api-key.php');
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
				add_menu_page('Google API Key', 'Google API Key', 7, ABSPATH.PLUGINDIR.'/wfiu_utils/google-api-key.php');
			}

		}
		/*else{
			unset($menu[$found]);

		}*/
	}
}

if(!function_exists('get_google_coordinates')) {
	function get_google_coordinates($location){
		global $google_api_key;
		$apicall = "http://maps.google.com/maps/geo?q=".urlencode($location)."&output=csv&key=$google_api_key";
		$apiresult = file_get_contents($apicall);
		preg_match("/.+,.+,(.+),(.+)/", $apiresult, $matches);
		$lat = $matches[1];
		$long = $matches[2];
		return array($lat, $long);
	}
}

if(!function_exists('wfiu_maybe_create_table')) {
	//copied from podpress
	function wfiu_maybe_create_table($table_name, $create_ddl) {
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

}

if(!function_exists('convertquotes')){
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
}

if(!function_exists('table_headers')) {
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
}

if(!function_exists('table_data_fill')) {
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
}
if(!function_exists('prepareDHTML')) {
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

}

if(!function_exists('makeSelectBox')) {
	function makeSelectBox($name,$options){
		
		$str = "<select name='$name' id='$name'>";
		foreach ($options as $option){
			$str .= "<option value=\"$option\">$option</option>";

		}
		$str .="</select>";
		return $str;
	}
}

if(!function_exists('shorten_name')) {
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
}

if(!function_exists('order_object_array')) {
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

}


if(!function_exists('order_array_by_keys')) {
	function order_array_by_keys($objArray){
		
		$keys =  array_keys($objArray);
		sort($keys);
		
		$ordered = array();
		foreach($keys as $index => $ordered_key){
			$ordered[$ordered_key]=$objArray[$ordered_key];
		}
		return $ordered;
	}

}


if(!function_exists('get_days')){
	function get_days($format, $sort_by = 'N'){
		$days = array();
		for($i=0;$i<7;++$i){
			$cur = 3600*24*$i + time();
			$days[date($format,$cur)]=intval(date($sort_by,$cur));
		}
		asort($days);
		return array_keys($days);
	}
}

if(!function_exists('single_google_map')){
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
if(!function_exists('first_words')){
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
}

?>
