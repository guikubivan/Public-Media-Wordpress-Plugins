<?php


abstract class map_item {
	// Force Extending class to define this method
	abstract public function getArray();
	abstract public function retrievePostData();
	abstract public function saveItem($id);
	abstract public function deleteItem($id);
	abstract public function editForm();
	abstract public function showItems();

	//abstract public function editItem();
	//abstract protected function prefixValue($prefix);
	public $location;
	public $longitude;
	public $latitude;
	protected $namePrefix = 'map_item';
	public $actions = array("add"=>"map_add_item", "edit"=>"map_edit_item", "delete"=> "map_delete_item", "update"=> "map_update_item");

	public function __construct($loc = 0, $long = 0, $lat =0){
		$this->location = $loc;
		$this->longitude = $long;
		$this->latitude = $lat;
	}

	// Common method
	public function getBaseArray() {
		return array('location'=>$this->location, 'longitude'=>$this->longitude, 'latitude'=>$this->latitude);
	}

	protected function retrieveBasePost(){
		$this->location = $_POST['location'];
		$this->longitude = $_POST['longitude'];
		$this->latitude = $_POST['latitude'];
	}
	
	protected function clearItem(){
		$this->location = '';
		$this->longitude = '';
		$this->latitude = '';
	}


}


class postLocation extends map_item{
	//abstract public function getArray();
	//abstract public function retrievePostData();
	//abstract public function saveItem();
	//abstract public function deleteItem($id);
	//abstract public function editForm();
	//abstract public function showItems();

	//delete_post_meta($postdata['ID'], 'podPressMedia');
	//delete_post_meta($postdata['ID'], 'podPressMedia');

	public function __construct($loc = 0, $long = 0, $lat =0){
		parent::__construct($loc, $long, $lat);
		//$this->option_name = $this->namePrefix . '_stations';
	}

	public function add_column($defaults){//add column when listing posts
		$defaults['mapper'] = 'Mapper';
		//print_r($defaults);
		return $defaults;
	}
	
	public function item_empty(){
		//if($this->location || $this->latitude || $this->longitude){
		if($this->latitude && $this->longitude){
			return false;
		}else{
			return true;
		}
	}

	public function clearItem(){
		$this->location = '';
		$this->latitude = '';
		$this->longitude = '';
	}

	public function do_column($column_name, $id){
    		if( $column_name == 'mapper' ) {
				$this->clearItem();
				$this->getPostMetaArray($id);
				if(!$this->item_empty()){
					echo $this->location;
				}
    		}


	}

	function plugin_url(){
		$result = get_bloginfo('url').'/wp-content/plugins/mapper/';
		return $result;
	}

	public function plugin_head(){
		global $google_api_key;
		//			<script type="text/javascript">alert("'.$google_api_key.'");</script>
		wp_register_script( 'google_map_api', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$google_api_key);
		wp_enqueue_script( 'google_map_api' );
		wp_enqueue_script('google_map_api_utils', get_bloginfo('url').'/wp-content/plugins/wfiu_utils/google_maps_utils.js', array('google_map_api'));
		wp_localize_script( 'google_map_api_utils', 'WPGoogleAPI', array(
		'key' => $google_api_key));


	}
	public function mapper_box(){
		$box_postion = (get_bloginfo('version')>=2.7) ? 'side' : 'normal';

		add_meta_box('mapper-div', __('Location Mapper', 'mapperbox'), array(&$this, 'post_form'), 'post', $box_postion, 'high');
		add_meta_box('mapper-div', __('Location Mapper', 'mapperbox'), array(&$this, 'post_form'), 'page', $box_postion, 'high');
	}



	public function editForm(){
		?>

			<table>
			<tr>
				<td>		<div id="map" style="height: 250px;width:100%;float:left;"></div>
Enter a location: <input type='text' id='location' name='location' onBlur="_functionToCall = 'executeCenter()';get_google_coordinates(this.value, 'latitude', 'longitude')" onkeyup="_functionToCall = '';get_google_coordinates(this.value, 'latitude', 'longitude')" value="<?php echo $this->location ? stripslashes($this->location) : ''; ?>"  /><span class='button'  onClick="_functionToCall = 'executeCenter()';get_google_coordinates(document.getElementById('location').value, 'latitude', 'longitude');"  >Show in map</span>
				<input type='hidden' id='latitude' name='latitude' value='<?php echo $this->latitude ? $this->latitude : '';; ?>'  />
				<input type='hidden' id='longitude' name='longitude' value='<?php echo $this->longitude ? $this->longitude : '';; ?>'  />
				</td>
			</tr>

		</table>



		<?php
		echo single_google_map('map',array('location', 'latitude', 'longitude'), 13);
	}

	public function editForm2(){
		?>
		<div id="map" style="height: 250px;float:right"></div>

		<table style='width:300px;'>
			<tr>
				<td>Location:</td>
				<td><input type='text' id='location' name='location' onBlur="_functionToCall = 'executeCenter()';get_google_coordinates(this.value, 'latitude', 'longitude')" onkeyup="_functionToCall = '';get_google_coordinates(this.value, 'latitude', 'longitude')" value="<?php echo $this->location ? stripslashes($this->location) : ''; ?>"  /></td>
			</tr>
			<tr>
				<td>Latitude:</td>
				<td><input type='text' id='latitude' name='latitude' value='<?php echo $this->latitude ? $this->latitude : '';; ?>'  /></td>
			</tr>
			<tr>
				<td>Longitude:</td>
				<td><input type='text' id='longitude' name='longitude' value='<?php echo $this->longitude ? $this->longitude : '';; ?>'  /></td>
			</tr>
			<tr>
				<td colspan='2' style='text-align:center;width:50%'><span class='button'  onClick="_functionToCall = 'executeCenter()';get_google_coordinates(document.getElementById('location').value, 'latitude', 'longitude');"  >Show in map</span></td>


			</tr>
		</table>




		<?php
		echo single_google_map('map',array('location', 'latitude', 'longitude'), 13);
	}

	public function getPostMetaArray($id=null){
		global $post;
		if($id == null){
			$id = $post->ID;
		}

		$locationR = get_post_meta($id, $this->namePrefix, true);
		if(!is_array($locationR)) {
			$locationR = maybe_unserialize($locationR);
		}

		if(is_array($locationR)){
			$this->location = $locationR['location'];
			$this->latitude = $locationR['latitude'];
			$this->longitude = $locationR['longitude'];
		}
	}

	public function norm($a, $b){
		$num = $a*$a+$b*$b;
		//return sqrt($num);
		return $num;
	}

	public function computeCenter($itemsArray){
		$cLat = 38.0;
		$cLong = -99.0;
		$maxLat = $maxLong = -999;
		$minLat = $minLong = 999;
		foreach($itemsArray as $item){

			$long = $item['longitude'];
			if($long > 0)$long = -(180+(180-$l));
			if($long > $maxLong){
				$maxLong = $long;
			}
			if($long < $minLong){
				$minLong = $long;
			}
			if($item['latitude'] > $maxLat){
				$maxLat = $item['latitude'];
			}
			if($item['latitude'] < $minLat){
				$minLat = $item['latitude'];
			}
		}
		//echo $maxLat .'*'. $minLat .'*'. $maxLong .'*'. $minLong;
		$cLat = ($maxLat+$minLat)/2;
		$cLong = ($maxLong+$minLong)/2;
		//return array($cLat, $cLong, $minLat, $maxLat, $minLong, $maxLong);
		return array($cLat, $cLong);
	}

	public function distance($coord1, $coord2){
		//print_r($coord1);
		$result = 0;
		for($i=0;$i<sizeof($coord1);$i++){
			//echo $coord2[$i];
			$temp = $coord1[$i]-$coord2[$i];
			$result += $temp * $temp;

		}

		return $result;
	}

	public function computeDistances($coords, $index){
		$distances= array();

		for($i=0; $i<sizeof($coords); ++$i){
			if($i==$index){
				$distances[] = -1;
				//$donothing='';
			}else{
				$distances[] = $this->distance($coords[$index], $coords[$i]);
			}
		}
		asort($distances);
		return $distances;
	}
	function printDistancesList($items, $coords){
		for($i=0;$i<sizeof($items);$i++){
			
			$distances = $this->computeDistances($coords,$i);
			//print_r($distances);
			//reset($distances);
			//next($distances);
			/*
			$dist = current($distances);
			while ($dist !== false) {
				echo $items[key($distances)]['post_title'] . "(".$dist.")<br />";
				next($distances);
				$dist = current($distances);
			}*/
			echo "For <b>" . $items[$i]['post_title'] ."</b> <a href='".get_bloginfo('url')."/wp-admin/post.php?action=edit&post=".$items[$i]['post_id']."'>Edit</a> <br />";
			foreach($distances as $key => $dist){
				if($dist >= 0){
					echo $items[$key]['post_title'] . "(".$dist.")<br />";
					//echo $items[$key]['post_blurb'] . " <br />";
				}
			}
		}

	}

	public function showItems(){
		global $wpdb;
		$urlPre = get_option( "siteurl" );

		$myquery = "SELECT post_id, meta_value, post_title, post_excerpt, post_content FROM $wpdb->postmeta, $wpdb->posts WHERE $wpdb->posts.ID=$wpdb->postmeta.post_id AND meta_key='".$this->namePrefix."'";
		//echo $myquery;

		$meta_values = $wpdb->get_results($myquery);
		$items = array();
		$coords = array();
		foreach($meta_values as $row){
			//print_r($row);
			$val = $row->meta_value;
			if(!is_array($val)) {
				$valR= maybe_unserialize($val);
				$valR= maybe_unserialize($valR);
				//print_r($valR);
				$valR['post_id']=$row->post_id;

				if($row->post_excerpt){
					$valR['post_blurb']= preg_replace("/[\n|\r]/", " ", first_words(strip_tags($row->post_excerpt), 30));
				}else{
					$valR['post_blurb']= preg_replace("/[\n|\r]/", " ", first_words(strip_tags($row->post_content), 30));
				}

				$valR['post_title']=$row->post_title;
				
				$items[] = $valR;
				$loc = $valR['location'];
				$lat = $valR['latitude'];
				$long = $valR['longitude'];
				$coords[] = array($lat, $long);
				//echo $loc . ": " . $norms[sizeof($norms)-1] . "<br />";
			}
		}
		$cCoords = $this->computeCenter($items);
		$cLat = $cCoords[0];
		$cLong = $cCoords[1];
		//echo $cLat . ', ' . $cLong . '<br />';
		$divW = 500;
		$divH = 400;
?>
<div id="map" style="width: <?php echo $divW; ?>px; height: <?php echo $divH; ?>px"></div>
<script type="text/javascript">			
var map;
var centerLatitude = <?php echo $cLat ? $cLat : '43.0';; ?>;
var centerLongitude = <?php echo $cLong ? $cLong : '-99.0';; ?>;
var startZoom = <?php echo "5";//echo $this->location ? '13' : '2'; ?>;


function addMarker(latitude, longitude, description) {
	var marker = new GMarker(new GLatLng(latitude, longitude));	


	GEvent.addListener(marker, 'click',
		function() {
			marker.openInfoWindowHtml(description);
		}
	);
	map.addOverlay(marker);
}


function init() {
	if (GBrowserIsCompatible()) {
		map = new GMap2(document.getElementById("map"));
		map.addControl(new GSmallMapControl());
		map.setCenter(new GLatLng(centerLatitude , centerLongitude ), startZoom );
		<?php
			foreach($items as $item){
				$link = "<a href='$urlPre/?p=${item['post_id']}' >read more</a>.";
				$title = "<b>${item['post_title']}</b><br />";
				$location = "<i>${item['location']}</i>";
				$blurb = "<div style='font-size: 12px;width:" . .8*$divW . "px;'>";

				$blurb .= $item['post_blurb'].$link;

				$blurb .= "</div>";
				$blurb = $title . $location . addslashes($blurb);
				echo "addMarker(" . $item['latitude'] . "," . $item['longitude'] . ",\"$blurb\");\n";
			}
		?>
		//var location = new GLatLng(centerLatitude, centerLongitude);
		//map.setCenter(location, startZoom);
	}
}
init();
window.onunload = GUnload;
</script>

<?php


		$this->printDistancesList($items, $coords);
	}

	public function config_form(){
		$this->showItems();
	}

	public function getArray(){
		return $this->getBaseArray();
	}
	public function deleteItem($id){
		delete_post_meta($id, $this->namePrefix);

	}

	public function retrievePostData(){
		$this->retrieveBasePost();
	}

	public function saveItem($post_id){
		$this->retrievePostData();
		if(!$this->item_empty()){
			$pdata = $this->getArray();
			$ser_pdata = maybe_serialize($pdata);
			add_post_meta($post_id, $this->namePrefix, $ser_pdata, true) or update_post_meta($post_id, $this->namePrefix, $ser_pdata);
		}else{
			delete_post_meta($post_id, $this->namePrefix);
		}
	}

	public function post_form(){
		$this->getPostMetaArray();
/*		if(!$this->item_empty()){
			echo '<div id="postMAPPERBOX" class="postbox if-js-open">';
		}else{
			echo '<div id="postMAPPERBOX" class="postbox if-js-closed">';
		}

		echo <<<END
			<h3>Location Mapper</h3>
			<div class="inside">

				<div id="postMAPPERCONTENT">
END;
*/
		$this->editForm();
/*
		echo "		</div>
			</div>
			
		</div>";
*/
	}
}






















//radioStation Class
class radioStation extends map_item{
	public $station_url;
	public $station_name;
	public $station_airtime;
	public $option_name;

	public $extraProperties = array("station_name"=>'', "station_airtime"=>'', "station_url"=>'');

	public function __construct($loc = 0, $long = 0, $lat =0, $station_name='', $station_airtime='', $station_url=''){
		parent::__construct($loc, $long, $lat);
		$this->extraProperties['station_name'] = $station_name;
		$this->extraProperties['station_airtime'] = $station_airtime;
		$this->extraProperties['station_url'] = $station_url;
		$this->option_name = $this->namePrefix . '_stations';
	}

	public function activate() {
		add_option($this->option_name, $serializedArray, '', 'no');
	}

	public function getArray(){
		$baseArray = $this->getBaseArray();
		return array_merge($baseArray, $this->extraProperties);
	}

	public function retrievePostData(){
		$this->retrieveBasePost();

		$this->extraProperties['station_name'] = $_POST['station_name'];
		$this->extraProperties['station_url'] = $_POST['station_url'];

		$day = $_POST['stations_map_day'];
		$hour = $_POST['stations_map_hour'];
		$ampm = $_POST['stations_map_ampm'];
		$timezone = $_POST['stations_map_timezone'];
		$this->extraProperties['station_airtime'] = $day . ' ' . $hour . $ampm . ' ' . $timezone;
	}

	public function saveItem($arrayIndex){
		$this->retrievePostData();
		$oldstations = get_option($this->option_name);
		$oldstations = unserialize($oldstations);
		//print_r($oldstations);
		//print_r($this->getArray());
		$newStation = $this->getArray();
		if($arrayIndex == null){
			$oldstations[]=$newStation;
		}else{
			$oldstations[$arrayIndex]=$newStation;
		}
		$new_stations = serialize($oldstations);
		update_option($this->option_name, $new_stations);
	}

	public function deleteItem($arrayIndex){
		//echo "deleting " . $_POST['station_index'] ."\n";
		$stations = get_option($this->option_name);
		$stations = unserialize($stations);
		$count = 0;
		$newstations = array();
		foreach($stations as $olds){
			if($count != $arrayIndex){
				$newstations[] = $olds;
			}
			++$count;
		}
		$newstations = serialize($newstations);
		update_option($this->option_name, $newstations);
		echo "<div id='message' class='updated fade'>Station " . $stations[$arrayIndex]['station_name'] ." deleted...</div>";	
	}
	
	public function editForm(){
		$stations = get_option($this->option_name);
		$stations = unserialize($stations);
		$mystation = $stations[$_POST['station_index']];
		echo "<div style='float:left;line-height: 21pt'>Name: <br />Location: <br />Airtime: <br />URL: <br />Latitude: <br />Longitude: <br /></div>";
		echo "<div><input name='station_name' type='text' value='".$mystation['station_name']."'/><br />";
		echo "<input name='station_location' type='text' value='".$mystation['station_location']."'/><br />";
		echo "<input name='station_airtime' type='text' value='".$mystation['station_airtime']."'/><br />";
		echo "<input name='station_url' type='text' value='".$mystation['station_url']."'/><br />";
		echo "<input name='station_latitude' type='text' value='".$mystation['latitude']."'/><br />";
		echo "<input name='station_longitude' type='text' value='".$mystation['longitude']."'/><br /></div>";

		echo "<br /><input class='button' type='submit' value='Update Station'/>";
		echo "<input type='hidden' name='station_index' value='".$_POST['station_index']."' />";
		return;
	}

	public function showItems($form_type="radio_buttons"){
		echo "<table>
			<tr>
			<th>&nbsp;</th>
			<th>Name</th>
			<th>Location</th>
			<th>Airtime</th>
			<th>URL</th>
			<th>Latitude</th>
			<th>Longitude</th>
			</tr>";
		$mystations = get_option($this->option_name);
		$mystations = unserialize($mystations);
		if($mystations && sizeof($mystations)>0){
			$indexes = array();
			$states = array();
			$cities = array();
			foreach($mystations as $station){
				preg_match("/(.*), ?([^\s]+)/", $station['location'], $matches);
				$cities[] = $matches[1];
				$states[] = $matches[2];
			}
	
			asort($states);
			$indexes = array_keys($states);
			//print_r($indexes);
			$citiesQ = array($indexes[0]=>$mystations[$indexes[0]]['location']);
			//print_r($citiesQ);
			//echo $states[$indexes[0]] . ' ';
			for($i=1; $i<sizeof($mystations); ++$i){
				//echo $states[$indexes[$i]] . ' ';
				if($states[$indexes[$i]]==$states[$indexes[$i-1]]){
					//print_r($citiesQ);
					$citiesQ[$indexes[$i]] = $mystations[$indexes[$i]]['location'];
	
				}else{
					if(sizeof($citiesQ)>1){
						asort($citiesQ);
						$newindexes = array_keys($citiesQ);
						array_splice($indexes, $i-sizeof($newindexes), sizeof($newindexes), $newindexes);
					}
					$citiesQ = array();
					$citiesQ = array($indexes[$i]=>$mystations[$indexes[$i]]['location']);
				}
			}
			foreach($indexes as $myindex){
				$station = $mystations[$myindex];
				echo "<tr>\n";
				if($form_type =="radio_buttons"){
					echo "<td><input type='radio' name='station_index' value='" . $myindex . "'></td>";
				}
				echo "<td>" . $station['station_name']."</td>";
				echo "<td>" . $station['location'] . "</td>";
				echo "<td>" . $station['station_airtime'] . "</td>";
				echo "<td>" . $station['station_url'] . "</td>";
				echo "<td>" . $station['latitude'] . "</td>";
				echo "<td>" . $station['longitude'] . "</td>";
				echo "</tr>\n";
			}
		}
		echo "</table>";

		
	}
	public function checkIndexPost(){
		if(!isset($_POST['station_index'])){
			echo "<div id='message' class='updated fade'>No station selected, please go back and try again.</div>";
			return false;
		}
		return true;
	}
	
	public function config_form(){
		if( $_GET[$this->actions['add']] ){
			//echo "Saving item\n";
			$this->saveItem(null);
		}else if($_POST[$this->actions['edit']]){
			if($this->checkIndexPost()){
				echo "<form action='?page=" . $_GET['page'] . "&" . $this->actions['update'] . "=true' method='post'>";
				echo "edit form";
				$this->editForm();
				echo "</form>\n";
			}
		}else if($_POST[$this->actions['update']]){
			if($this->checkIndexPost()){
				$sindex = intval($_POST['station_index']);
				$this->saveItem($sindex);
				echo "<div id='message' class='updated fade'>Station ".$sname." updated...</div>";
			}
		}else if($_POST[$this->actions['delete']]){
			if( $this->checkIndexPost() ){
				$sindex = intval($_POST['station_index']);
				$this->deleteItem($sindex);
			}
		}else{
			?>

			<div class="wrap">
			<h2>Stations</h2>
			<!-- <p>Here you can modify the stations that broadcast this show.</p>-->
			</div>
			
			
			<?php
			echo "<form action='?page=" . $_GET['page'] . "' method='post'>";
			$this->showItems('radio_buttons');
			echo "<br /><input class='button' type='submit' name='".$this->actions['edit']."' value='Edit selected station'/>";
			echo "&nbsp;&nbsp;&nbsp;<input class='button' type='submit' name='".$this->actions['delete']."' value='Delete selected station'/>";
			echo "</form>\n";
			
		?>




		<div class="wrap">
		<h2>Add a new station</h2>
		</div>
		<script type="text/javascript">
		function newTimezone(){
			var listChoice=document.getElementById('stations_map_timezone').value;
			if(listChoice == 'other'){
				document.getElementById('stations_map_timezone_span').innerHTML="<input name='stations_map_timezone' type='text' size='5' title='Enter alternative timezone'/>";
			}
		}

		</script>

		<?php
			echo "<form action='?page=" . $_GET['page'] . "&".$this->actions['add']."=true' method='post'>";
			echo "<div style='float:left;line-height: 21pt'>Name: <br />Location: <br />Airtime: <br />URL: <br />Latitude: <br />Longitude: <br /></div>";
			echo "<div><input name='station_name' type='text' /><br />";
			echo "<input id='station_location' onkeyup=\"javascript:mapper_ajax_getCoords(this.value, 'latitude', 'longitude')\" name='location' type='text' /><br />";
			echo "<SELECT name='stations_map_day'>
				<OPTION value='Sunday'>Sunday</OPTION>
				<OPTION value='Monday'>Monday</OPTION>
				<OPTION value='Tuesday'>Tuesday</OPTION>
				<OPTION value='Wednesday'>Wednesday</OPTION>
				<OPTION value='Thursday'>Thursday</OPTION>
				<OPTION value='Friday'>Friday</OPTION>
				<OPTION value='Saturday'>Saturday</OPTION>
				</SELECT>

				<SELECT name='stations_map_hour'>";
			for($i=1;$i<=12;$i++){
				echo "		<OPTION value='".$i."'>".$i."</OPTION>\n";
			}
			echo "		</SELECT>
				<SELECT name='stations_map_ampm'>
				<OPTION value='am'>am</OPTION>
				<OPTION value='pm'>pm</OPTION>
				</SELECT>
		<span id='stations_map_timezone_span' >
				<SELECT onchange=\"javascript:newTimezone()\" onkeyup=\"javascript:newTimezone()\" id='stations_map_timezone' name='stations_map_timezone'>
				<OPTION value='EST'>EST</OPTION>
				<OPTION value='CST'>CST</OPTION>
				<OPTION value='MST'>MST</OPTION>
				<OPTION value='PST'>PST</OPTION>
				<OPTION value='PHT'>PHT</OPTION>
				<OPTION value='other'>Other</OPTION>
				</SELECT>
		</span>
			<br />";
			echo "<input name='station_url' type='text' /><br />";
			echo "<input id='station_latitude' name='latitude' type='text' /><br />";
			echo "<input id='station_longitude' name='longitude' type='text' /><br /></div>";

			echo "<br /><input class='button' type='submit' value='Add Station'/>";
			echo "</form>\n";
		}
	}
}
 ?>
