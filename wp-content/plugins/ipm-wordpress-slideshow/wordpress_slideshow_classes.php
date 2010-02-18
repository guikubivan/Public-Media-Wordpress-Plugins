<?php
class wordpress_slideshow{
	public $fieldname = 'slideshow_id';
	public $plugin_prefix = 'wpss_';
	public $slideshow_props = Array('id','title','photo_credit','description','geo_location');
	public $photo_props = Array('id','title', 'caption', 'geo_location', 'photo_credit','description','url');
	private $fixed_photo_props = array('geo_location', 'photo_credit','latitude','longitude', 'original_url');
	public $t_s, $t_spr, $t_p, $t_pm, $t_pmr;
	public $default_style_photo = 'wpss_simple.xsl';
	public $default_style_slideshow = 'wpss_simple.xsl';
	public $default_style_post_image = 'wpss_simple.xsl';
	public $default_multiple_slideshows = false;
	public $photo_id_translation = Array();
	public function __construct(){
	    	global $wpdb;
		$this->t_s = $wpdb->prefix.$this->plugin_prefix."slideshows";
		$this->t_p = $wpdb->prefix.$this->plugin_prefix."photos";
		$this->t_spr = $wpdb->prefix.$this->plugin_prefix."slideshow_photo_relations";
		$this->t_pm = $wpdb->prefix.$this->plugin_prefix."photo_meta";
		$this->t_pmr = $wpdb->prefix.$this->plugin_prefix."photo_meta_relations";
		//$donuthing = '';
		//parent::__construct('Slideshows', $this->fieldname, $this->fieldname);

		//add_action('activate_wordpress_slideshow/wordpress_slideshow.php', array(&$this, 'activate'));
		add_action('wp_ajax_action_get_coordinates', array(&$this, 'php_get_coordinates'));
		add_action('wp_ajax_action_get_slideshow', array(&$this, 'php_get_slideshow'));
		add_action('wp_ajax_action_update_photo_property', array(&$this, 'php_update_photo_property'));
		add_action('wp_ajax_action_replace_wp_photo', array(&$this, 'php_replace_wp_photo'));

		$this->option_default_style_photo = $plugin_prefix.'slideshow_stylesheet';
		$this->option_default_style_slideshow = $plugin_prefix.'photo_stylesheet';
		$this->option_default_style_post_image = $plugin_prefix.'post_image_stylesheet';
		$this->option_multiple_slideshows = $plugin_prefix.'multiple_slideshows';
		$this->postmeta_post_image = $plugin_prefix.'post_image';
		$this->utils = new IPM_Utils();
	}
	function add_column($defaults){//add column when listing posts
		$defaults['wpss'] = 'Photos';
		//print_r($defaults);
		return $defaults;
	}

	function do_column($column_name, $id){
    		if( $column_name == 'wpss' ) {
				if($slideshows=get_post_meta($id,$this->fieldname, false)){
					//echo "&#166;";
					if(is_array($slideshows)){

						foreach($slideshows as $sid){
							$sProps = $this->getSlideshowProps($sid);
							$photos = $this->getPhotos($sid);
							//print_r($photos);
							if(is_array($photos)){
								echo "<i>".$sProps['title']."</i> (" . sizeof($photos) . " photos)";
							}
						}
					}
				}else if($pid=get_post_meta($id,$this->plugin_prefix.'photo_id', true)){
					//echo "&#8227;";
					$photo = $this->getPhoto($pid);
					echo "<b>".$photo['title'] . "</b>";
				
				}else{
					echo "";//N/A

				}
    		}
	}


	function activate(){
	    global $wpdb;

		//need mysql >= 5.0.3
		$table = $this->t_s;
		$query = "CREATE TABLE $table (
		ID INT(9) NOT NULL AUTO_INCREMENT,
		title VARCHAR(255) NOT NULL,
		photo_credit VARCHAR(255),
		description VARCHAR(1023),
		geo_location VARCHAR(255),
		latitude VARCHAR(10),
		longitude VARCHAR(10),
		thumb_id INT(9),
		PRIMARY KEY (ID),
		INDEX(photo_credit)
		);";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';

		$table = $this->t_spr;
		$query = "CREATE TABLE $table (
		slideshow_id INT(9) NOT NULL,
		photo_id INT(9) NOT NULL,
		photo_order INT(3) NOT NULL,
		PRIMARY KEY (slideshow_id, photo_id),
		INDEX(slideshow_id),
		INDEX(photo_id)
		);";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';


		$table = $this->t_p;
		$query = "CREATE TABLE $table (
		photo_id INT(9) NOT NULL AUTO_INCREMENT,
		wp_photo_id INT(9) NOT NULL,
		PRIMARY KEY (photo_id),
		INDEX(wp_photo_id)
		);";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';


		$table = $this->t_pmr;
		$query = "CREATE TABLE $table (
		photo_id INT(9) NOT NULL,
		meta_id INT(3) NOT NULL,
		meta_value VARCHAR(1023),
		PRIMARY KEY (photo_id, meta_id),
		INDEX(photo_id),
		INDEX(meta_id)
		);";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';


		$table = $this->t_pm;
		$query = "CREATE TABLE $table (
		meta_id INT(9) NOT NULL AUTO_INCREMENT,
		meta_name VARCHAR(255) NOT NULL,
		PRIMARY KEY (meta_id),
		UNIQUE KEY (meta_name)
		);";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';

		/*Options */
		add_option($this->option_default_style_slideshow, $this->default_style_slideshow);
		add_option($this->option_default_style_photo, $this->default_style_photo);
		add_option($this->option_default_style_photo, $this->default_style_photo);
		add_option($this->option_default_style_post_image, $this->default_style_post_image);
		if($this->default_multiple_slideshows) add_option($this->option_multiple_slideshows, $this->default_multiple_slideshows);
	}



	function php_get_coordinates(){
		$location = $_POST['geo_location'];
		$lat_id = $_POST['latitude_id'];
		$long_id = $_POST['longitude_id'];

		//$apicall = "http://maps.google.com/maps/geo?q=".str_replace(',','+',str_replace(' ','',$location))."&output=csv&key=ABQIAAAApmJqiGeKB-folcWGraVHMhQFYWY_ifD3IWMB9xUmFY5e004cnhSiWgNhJltBkYMFEzExEdBE784vAA";

		$coords = get_google_coordinates($location);
		$lat = $coords[0];
		$long = $coords[1];


		/*$retString = "document.getElementById('station_latitude').value = '". $lat . "';
				document.getElementById('station_longitude').value = '". $long . "';";
		*/
		$retString = "document.getElementById('$lat_id').value = '$lat';
				document.getElementById('$long_id').value = '$long';";
		die($retString);
	}

	function php_get_slideshow(){
		$sid = $_POST['sid'];
		$sProps = $this->getSlideshowProps($sid);
		$sProps['update']= 'yes';
		$retString = "slideshowOrganizer.organizerAddItem($sid,\"".$this->slideshowItemHTML($sid,$sProps, false, true)."\");

		currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();";
		$photos = $this->getPhotos($sid);
		//print_r($photos);
		if(is_array($photos)){
			foreach($photos as $pid => $photo){
				$retString .= "send_to_slideshow($pid,\"" . $this->photoItemHTML_simple($pid,$photo) . "\");";
			}
		}
		$retString .= "showSlideshowMenu('',false);";

		$retString .= "jQuery('#".$this->plugin_prefix."parent_div').show();";
		$retString .= "jQuery('.slideshow_box_footer').show();";
		//$retString .= "jQuery('#".$this->plugin_prefix."parent_div').prepend(jQuery('#slideshow_box_footer').clone());";

		die($retString);
	}

	function php_update_photo_property(){
		$photo_id = $_POST['photo_id'];
		$property_name = $_POST['property_name'];
		$property_value = $_POST['property_value'];
		$update = $_POST['update'];
		$element_id = $_POST['element_id'];
		$retStr = "currentFieldValue=\"$property_value\";";
		$post_id = -1;
		if($update){
			$photo = $this->getPhoto($photo_id);
			$post_id = $photo['wp_photo_id'];
			//$retStr .= "alert('$post_id');";

		}else{
			$post_id = $photo_id;
		}

		$retStr .= "el = document.getElementById(\"$element_id\");
		while(el.nodeName !='SPAN'){
			el = el.nextSibling;

		}";
	
		if(update_post_meta($post_id, $property_name, $property_value)){
			if($property_name == 'geo_location'){
				$coords = get_google_coordinates($property_value);
				$lat = $coords[0];
				$long = $coords[1];
				add_post_meta($post_id, 'latitude', $lat, true) or update_post_meta($post_id, 'latitude', $lat);
				add_post_meta($post_id, 'longitude', $long, true) or update_post_meta($post_id, 'longitude', $long);
			}	
			$retStr .= "el.className='highlight';";
			$retStr .= "el.innerHTML='Updated';";
		}else{
			$retStr .= "el.className='highlight';";
			$retStr .= "el.innerHTML='Error...';";
		}
		die($retStr);
	}

	public function php_replace_wp_photo(){
		global $wpdb;
		$photo_id = $_POST['photo_id'];
		$wp_photo_id = $_POST['wp_photo_id'];
		$new_url = $_POST['new_url'];
		$photo_credit = $_POST['photo_credit'];
		$geo_location = $_POST['geo_location'];
		$retStr = '';
		if($photo_credit=='undefined' || $geo_location=='undefined'){
			$photo = $this->getPhotoFixedProps($wp_photo_id);
			$photo_credit = $photo['photo_credit'];
			$geo_location = $photo['geo_location'];
			$original_url = $photo['original_url'];
		}
		//$retStr = "var win = window.dialogArguments || opener || parent || top;";
		if($photo_id && $wp_photo_id){
			$result = $wpdb->query("UPDATE $this->t_p SET wp_photo_id=$wp_photo_id WHERE photo_id=$photo_id");
			if($result){
				$retStr .= "slideshowOrganizer.replaceSubItem(currentSlideshowID, $photo_id, '$new_url',\"".$photo_credit."\", \"".$geo_location."\", \"".$original_url."\");";
			}else{
				$retStr = 'alert("Photo could not be updated (possibly picked same photo?)");';
			}
		}

		die($retStr);
	}

	public function plugin_head(){
		$google_api_key = $this->utils->google_api_key;
		//Register javascript 
		wp_register_script( 'google_map_api', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$google_api_key);
		if(file_exists(ABSPATH.PLUGINDIR.'/wfiu_utils/google_maps_utils.js')){
			wp_register_script('google_map_api_utils', get_bloginfo('url').'/wp-content/plugins/wfiu_utils/google_maps_utils.js', array ('google_map_api'));
		}else{
			wp_register_script('google_map_api_utils', $this->plugin_url().'google_maps_utils.js', array ('google_map_api'));
		}
		wp_localize_script( 'google_map_api_utils', 'WPGoogleAPI', array('key' => $google_api_key));

		wp_register_script( 'wp_slideshow', $this->plugin_url().'wordpress_slideshow_admin_js.php', array('jquery', 'google_map_api', 'google_map_api_utils'));
		//wp_enqueue_script('jquery-ui-sortable');
		if( preg_match("/post\.php|post\-new\.php|page\.php|page\-new\.php|media\-upload\.php/", $_SERVER[ 'REQUEST_URI' ]) ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'thickbox' );# thickbox
			wp_admin_css('thickbox');
			wp_enqueue_script( 'wp_slideshow' );
			echo '<link rel="stylesheet" href="'.$this->plugin_url().'wordpress_slideshow_admin.css" type="text/css" />'."\n";
		}

		if( preg_match("/post\.php|post\-new\.php|page\.php|page\-new\.php/", $_SERVER[ 'REQUEST_URI' ]) ) {
			echo '<link rel="stylesheet" href="'.$this->plugin_url().'wordpress_slideshow_admin.css" type="text/css" />'."\n";
		}

		if($_GET['page'] == "Slideshow Manager"){
			echo "\n<!-- Slideshow manager tabs-->\n";
			echo '<link rel="stylesheet" href="'.$this->plugin_url().'ui.tabs.css" type="text/css" />'."\n";
			wp_enqueue_script( 'jquery-ui-tabs', array('jquery') );
			echo "\n<!-- END Slideshow manager tabs-->\n";
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'thickbox' );# thickbox
			wp_admin_css('thickbox');
			wp_enqueue_script( 'wp_slideshow' );
			echo '<link rel="stylesheet" href="'.$this->plugin_url().'wordpress_slideshow_admin.css" type="text/css" />'."\n";
		}


  		wp_print_scripts( array( 'sack' ));
		?>

		<script type="text/javascript">
		//<![CDATA[
		function ajax_replace_wp_photo(photo_id, wp_photo_id, new_url, pcred, gloc){
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  mysack.execute = 1;
		  mysack.method = 'POST';
		  mysack.setVar( "action", "action_replace_wp_photo" );
		  mysack.setVar( "photo_id", photo_id );
		  mysack.setVar( "wp_photo_id", wp_photo_id );
		  mysack.setVar( "new_url", new_url );
		  mysack.setVar( "photo_credit", pcred );
		  mysack.setVar( "geo_location", gloc );
		  mysack.encVar( "cookie", document.cookie, false );
		  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
		  //mysack.onCompletion = whenImportCompleted;
		  mysack.runAJAX();

		  return true;

		}

		function mapper_ajax_getCoords(station_location, latitude_id, longitude_id)
		{//alert("hi mapper");
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  mysack.execute = 1;
		  mysack.method = 'POST';
		  mysack.setVar( "action", "action_get_coordinates" );
		  mysack.setVar( "geo_location", station_location );
		  mysack.setVar( "latitude_id", latitude_id );
		  mysack.setVar( "longitude_id", longitude_id );
		  mysack.encVar( "cookie", document.cookie, false );
		  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
		  //mysack.onCompletion = whenImportCompleted;
		  mysack.runAJAX();

		  return true;


		}

		function ajax_getSlideshow(sid)
		{//alert("hi mapper");
		if(sid=='none'){return;}
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  mysack.execute = 1;
		  mysack.method = 'POST';
		  mysack.setVar( "action", "action_get_slideshow" );
		  mysack.setVar( "sid", sid );
		  mysack.encVar( "cookie", document.cookie, false );
		  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
		  //mysack.onCompletion = whenImportCompleted;
		  mysack.runAJAX();
		  return true;
		}

		function ajax_update_photo_property(photo_id, property_name, property_value, update, element_id){
			   var mysack = new sack( 
			       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

			  mysack.execute = 1;
			  mysack.method = 'POST';
			  mysack.setVar( "action", "action_update_photo_property" );
			  mysack.setVar( "photo_id", photo_id );
			  mysack.setVar( "property_name", property_name );
			  mysack.setVar( "property_value", property_value );
			  mysack.setVar( "update", update );
			  mysack.setVar( "element_id", element_id );
			  mysack.encVar( "cookie", document.cookie, false );
			  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
			  //mysack.onCompletion = whenImportCompleted;
			  mysack.runAJAX();
			  return true;
		}


		 // end of JavaScript function myplugin_ajax_elevation
		//]]>




		</script>
<?php

	}

	function plugin_url(){
		$result = get_bloginfo('url').'/wp-content/plugins/ipm-wordpress-slideshow/';
		return $result;
	}
	function show_map(){
		global $google_api_key;
		?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>

	<?php
		$location = $_GET['location'];
		$lat = $_GET['latitude'];
		$long = $_GET['longitude'];
		if(!$lat && !$long){
			$coords = get_google_coordinates($location);
			$lat=$coords[0];
			$long=$coords[1];
		}
		echo "<title>Show map for $location</title>
			<script type='text/javascript' src='http://maps.google.com/maps?file=api&amp;v=2.x&amp;sensor=false&amp;key=$google_api_key'> </script>";
?>
<script type="text/javascript">			
function initialize() {
	if (GBrowserIsCompatible()) {
		setTimeout( function() {


	       		var map = new GMap2(document.getElementById("map_canvas"));
			map.setCenter(new GLatLng(<?php echo $lat;?>, <?php echo $long;?>), 13);
			map.setUIToDefault();

			var marker = new GMarker(new GLatLng(<?php echo $lat;?>, <?php echo $long;?>));	


			GEvent.addListener(marker, 'click',
				function() {
					marker.openInfoWindowHtml("<?php echo $location;?>");
				}
			);
			map.addOverlay(marker);
            	}, 1 ); 
 
	}
}



</script>
</head>
	<body onload="initialize()" onunload="GUnload()"> 
	Map for "<?php echo $location;?>"
	<div id="map_canvas" style="width: 500px; height: 300px"></div>

	</body>
</html>
<?php
	}

	public function slideshowBox(){
		add_meta_box('slideshow-box-div', __('Slideshow', 'slideshowbox'), array(&$this, 'post_form'), 'post', 'normal', 'high');
		add_meta_box('slideshow-box-div', __('Slideshow', 'slideshowbox'), array(&$this, 'page_form'), 'page', 'normal', 'high');

	}

	function get_list_slideshows($type='select', $orderby = 'title', $direction ='asc'){
		global $wpdb;
		$ret = '';
		$prefix = $wpdb->prefix.$this->plugin_prefix;

		$orderby = $_GET['order_by'] ? $_GET['order_by'] : 'title';
		$direction = $_GET['direction'] ? $_GET['direction'] : 'asc';
		if($_GET['order_by'] != $_GET['porder_by']){
			$direction = 'asc';
		}


		$query = "SELECT ID, title, photo_credit, description, thumb_id, count(spr.photo_id) as nphotos FROM  $this->t_s as s LEFT JOIN $this->t_spr as spr ON (s.ID=spr.slideshow_id) GROUP BY (s.ID) ORDER BY $orderby $direction;";// title;";
		//echo $query;
		$results = $wpdb->get_results($query);
		if(sizeof($results) > 0){
			if($type=='select'){
				$ret .= '<select name="'.$this->plugin_prefix.'id" onChange="ajax_getSlideshow(this.value);" >';//onchange="showSlideshowMenu(this.value, false);"
			}else{?>


<style type="text/css">



	.title_column {
		background-color: #C4AEAE;
	}

	.credit_column{
		background-color: #D0BF87;
	}

	.desc_column{
		background-color: #A4B6B2;
	}
	
	.rowheight {
		height: 112px;
	}

	th, td {
		width: 200px;
		width: 200px;
	}

</style> 

<?php 
				$ret .= "<table>";
				$direction = ($direction == 'asc') ? 'desc' : 'asc';
				$ret .= $this->utils->table_headers('&nbsp;', array("class='title_column'","<a href=\"?page=".$_GET['page']."&order_by=title&direction=$direction&porder_by=$orderby\" >Title</a>"), array("class='credit_column'","<a href=\"?page=".$_GET['page']."&order_by=photo_credit&direction=$direction&porder_by=$orderby\" >Photo Credit</a>"), array("class='desc_column'",'Description'), '# of Photo(s)',array("", "<a href=\"?page=".$_GET['page']."&order_by=ID&direction=$direction&porder_by=$orderby\" >Slideshow ID</a>"));
			}
		}else{ return false;}
		if($type=='select'){
			$ret .= '<option value="none">Select existing slideshow</option>';
		}
		foreach($results as $row){
			$photos = $wpdb->get_results("SELECT spr.photo_id, p.wp_photo_id FROM $this->t_p as p, $this->t_spr as spr WHERE spr.photo_id=p.photo_id AND slideshow_id=$row->ID;");
			$missing = 0;
			foreach($photos as $photo){
				$this->utils->wp_exist_post($photo->wp_photo_id) ? '' : ++$missing ;
			}
			$missing_span = $missing >0 ? "<span style='background-color: #000000;color:#FFFFFF;padding:3px;' >".$missing.' of '.sizeof($photos)." missing</span>" : '';
			if($type=='select'){
				$ret .= '<option value="'.$row->ID.'">'.$row->title.'('.$row->nphotos.' photos)</option>';
			}else{
				if($row->thumb_id){
					$photo = $this->getPhoto($row->thumb_id);
					$url = $photo['url'];
				}else{
					$photos = $this->getPhotos($row->ID);
					$photo = current($photos);
					$url = $photo['url'];
				}
				
				$ret .= $this->utils->table_data_fill("<span class='button' onClick=\"maybeDeleteSlideshow(".$row->ID.");\" >Delete</span> ".
							" <span class=\"button slideshowEditButton\" onClick=\"clearSlideshows(this);;ajax_getSlideshow($row->ID);\">Edit</span>",
							array("class='title_column rowheight' style=\"background-image: url($url);width:180px;background-position:center; \"", "<div style='background-color: #000000;color:#FF580F;font-weight: bold;text-align:center' id='title[".$row->ID."]'>".$row->title."</div>"),
							array("class='credit_column rowheight'", $row->photo_credit),
							array("class='desc_column rowheight'", $row->description),
							array("class='rowheight' style=\"text-align:center\"", $missing_span ? "$missing_span" : "<span style='padding:3px;' >".sizeof($photos)."</span>")  ,
							array("style='text-align:center;'",$row->ID));
			}
		}
		if($type=='select'){
			$ret .= "</select>";
		}else{
			$ret .= "</table>";
		}
		return $ret;
	}
	function process_delete_slideshow(){
		$msg = '';
		$sid = $_POST['slideshow_id'];
		$title = stripslashes($_POST['slideshow_title']);

		if(!$sid){
			echo "<div id='message' class='updated fade'>No post data received, try again.</div>";
			return;
		}
		$msg .= $this->delete_slideshow($sid);


		if($msg){
			echo "<div id='message' class='updated fade'>$msg</div>";
		}else{
			echo "<div id='message' class='updated fade'>Slideshow <b>$title</b> deleted succesfully.</div>";
		}

	}
	
	function url_search_id($url){
		global $wpdb;
		preg_match("/\/([^\/]*\/[^\/]*\/[^\/]*)$/", $url, $match);
		if($match[1]){
			$s = $match[1];
			$wp_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_type='attachment' AND post_mime_type LIKE \"image%\" AND guid LIKE \"%$s\";");
			return $wp_id;
		}
		return false;
	}


	function import_utility(){
		global $wpdb;
		$good = "<img src='".$this->plugin_url()."images/accept.png' />";
		$bad = "<img src='".$this->plugin_url()."images/exclamation.png' />";
		$import = 0;
		$fail = 0;
		$skipped = 0;
		if($key = $_POST['meta_key']){
			echo "<hr />";
			if($_POST['custom_field_test'])echo "<h3>Test run</h3>";
			$images = $wpdb->get_results("SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key=\"$key\";");
	
			foreach($images as $pm){
				preg_match("/\/([^\/]*\/[^\/]*\/[^\/]*)$/", $pm->meta_value, $match);

				$post_title = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID=$pm->post_id");

				if($pm->meta_value){
					$str = "Importing image from post <i>$post_title</i> (id: $pm->post_id)...";
					$wp_id = $this->url_search_id($pm->meta_value);

					if($wp_id){


						++$imported;

						if($pid=get_post_meta($pm->post_id,$this->plugin_prefix.'photo_id', true)){
							//already has image, do nothing
							$str = $bad.$str."skipping(already has image)<br />";
							++$skipped;
						}else{
							$photos[$wp_id] = array('title'=> $post_title);
							if($_POST['custom_field'])$errormsg = $this->insert_photos('', $photos, true, $pm->post_id);
							if($errormsg){
								$str = $bad.$str."<p style='margin-left: 20px;'>$errormsg</p>";
								++$fail;
							}else{
								$str = $good.$str."Success<br />";
								++$import;
							}
						}
					}else{
						++$fail;
						$str = $bad.$str."wp id not found.<br />";
					}
					echo $str;
				}

			}
			echo "<div>Imported: $import</div>";
			echo "<div>Failed: $fail</div>";
			echo "<div>Skipped: $skipped</div>";
			echo "<hr />";
			//$a = $wpdb->get_results('wp_attached_file');
		}else if($_POST['extract'] || $_POST['extract_test'] || $_POST['remove_images']){
			echo "<hr />";
			if($_POST['extract_test'])echo "<h3>Test run</h3>";
			$images = $wpdb->get_results("SELECT ID, post_title, post_content FROM $wpdb->posts WHERE post_content LIKE \"%<img%\" AND post_type='post' AND post_status !='inherit'");
			$imported = 0;
			$fail = 0;
			foreach($images as $p){
				$str = $_POST['remove_images'] ? "Removing image from content in post <i>$p->post_title</i> (id: $p->ID)..." : "Importing image from post <i>$p->post_title</i> (id: $p->ID)...";
				if($pid=get_post_meta($p->ID,$this->plugin_prefix.'photo_id', true) && !$_POST['remove_images']){
					//already has image, do nothing
					$str = $bad.$str."skipping(already has image)<br />";
				}else{
					$ID = -1;
					$replace_string = '';
					$caption = '';
					$alt = '';
					if(!preg_match("/\[caption\s*id=[\"|\']attachment_([0-9]+)[\"|\'][^\]]+caption=\"([^\"]+)\"[^\]]*\][^\[]*\[\s*\/\s*caption\s*\]/", $p->post_content, $caption_matches)){
						if(preg_match("/<\s*a\s*href\s*=\s*['|\"][^'|\"]+['|\"]\s*>\s*<\s*img\s*[^>]*src=['|\"]([^'|\"]+)['|\"][^>]*><\/\s*a\s*>/i", $p->post_content, $matches)|| preg_match("/<\s*img\s*[^>]*src=['|\"]([^'|\"]+)['|\"][^>]*>/i", $p->post_content, $matches2)){
							
							$replace_string = sizeof($matches)>1 ? $matches[0]:$matches2[0];
							$url = sizeof($matches)>1 ? $matches[1]:$matches2[1];
							if(preg_match("/.*\/.+(\-[0-9]+x[0-9]+)\.[^\.]+$/",$url, $nmatches)){
								$url = str_replace($nmatches[1], '', $url);
							}
							if($nID = $this->url_search_id($url)){
								$ID=$nID;
							}
							$caption = $wpdb->get_var("SELECT post_content FROM $wpdb->posts WHERE ID=$ID;");
							$alt = $wpdb->get_var("SELECT post_excerpt FROM $wpdb->posts WHERE ID=$ID;");
						}
					}else{
						$replace_string = $caption_matches[0];
						$ID=$caption_matches[1];
						//print_r($caption_matches);
						$caption = $caption_matches[2];//$wpdb->get_var("SELECT post_content FROM $wpdb->posts WHERE ID=$ID;");
						$alt = $wpdb->get_var("SELECT post_excerpt FROM $wpdb->posts WHERE ID=$ID;");
					}
					if($ID> -1){
						if($_POST['remove_images']){
							if(get_post_meta($p->ID,$this->plugin_prefix.'photo_id', true)){
								$ncontent = str_replace($replace_string, '', $p->post_content);
								$wpdb->query("UPDATE $wpdb->posts SET post_content='".addslashes($ncontent)."' WHERE ID=$p->ID;");
								$str = $good.$str."Success<br />";
								//continue;
							}else{
								$str = $bad.$str."skipping(image not yet imported)<br />";

							}
						}else{

							$photos[$ID] = array('title'=> $p->post_title, 'caption'=>$caption, 'alt'=>$alt);
							if($_POST['extract'])$errormsg = $this->insert_photos('', $photos, true, $p->ID);
							if($errormsg){
								$str = $bad.$str."<p style='margin-left: 20px;'>$errormsg</p>";
								++$fail;
								break;
							}else{
								$str = $good.$str."Success<br />";
								/*$ncontent = str_replace($replace_string, '', $p->post_content);
								//echo htmlentities($ncontent);
								if($_POST['extract'])$wpdb->query("UPDATE $wpdb->posts SET post_content='".addslashes($ncontent)."' WHERE ID=$p->ID;");
								*/
								++$import;
							}
						}
					}else{
						++$fail;
						$str = $bad.$str."could not find ID of embedded image<br />";
					}
				}
				echo $str;
			}
			echo "<hr />";
			
		}
		?>
		<form action='?page=<?php echo $_GET['page']; ?>#wpss_import' method='post'>
		<h3>Import by custom field</h3>
		<p>You can import pictures into the plugin by specifying the name of the custom field containing the image url</p>
		Custom field name: <input type='text' name='meta_key' />
		<input type='submit' value='Test' name='custom_field_test'/>
		<input type='submit' value='Import' name='custom_field'/>

		<h3>Extract and import embedded images</h3>
		<input type='submit' name='extract_test' value='Test' />
		<input type='submit' name='extract' value='Import images to slideshow plugin' />
		<input type='submit' name='remove_images' value='Remove post_content images from posts that have been imported' />

		</form>

<?php
	} 

	function show_stylesheet_list($name, $selected = ''){
		$stylesheets = $this->utils->get_files_array(dirname(__FILE__) . "/stylesheets");
		echo "<select name=\"$name\">";
		foreach($stylesheets as $sf){
			if(!preg_match("/.*\.xsl$/",$sf))continue;
			$iselected = '';
			if(basename($sf) == $selected){
				$iselected = 'SELECTED="SELECTED"';
			}
			echo "<option $iselected value=\"".basename($sf)."\">".basename($sf)."</option>";
		}
		echo "</select>";
	}

	function add_or_update_setting($option){
		if($_POST[$option]){

			if(get_option($option)){
				update_option($option,$_POST[$option]);
			}else{
				add_option($option,$_POST[$option]);//probably never runned
			}
			return true;
		}
		return false;
	}


	function plugin_settings(){
		$updated = false;
		$updated = $this->add_or_update_setting($this->option_default_style_slideshow);
		$updated = $this->add_or_update_setting($this->option_default_style_photo);
		$updated = $this->add_or_update_setting($this->option_default_style_post_image);


		if($_POST[$this->option_multiple_slideshows]){
			$updated = true;
			if(get_option($this->option_multiple_slideshows) && $_POST[$this->option_multiple_slideshows]){
				update_option($this->option_multiple_slideshows,$_POST[$this->option_multiple_slideshows]);
			}else if($_POST[$this->option_multiple_slideshows]){
echo ' hello' ;
				update_option($this->option_multiple_slideshows,true);
			}
		}else if($_POST['wpss_settings_update']){
			$updated = true;
			delete_option($this->option_multiple_slideshows,true);
		}

		if($updated){
			echo "<div id='message' class='updated fade'>Slideshow settings updated succesfully.</div>";
		}
		if(!($slideshow_stylesheet = get_option($this->option_default_style_slideshow))){
			$slideshow_stylesheet = $this->default_style_slideshow;
		}
		if(!($photo_stylesheet = get_option($this->option_default_style_photo))){
			$photo_stylesheet = $this->default_style_photo;
		}
		if(!($post_image_stylesheet = get_option($this->option_default_style_post_image))){
			$post_image_stylesheet = $this->default_style_post_image;
		}

		$checked = get_option($this->option_multiple_slideshows) ? 'CHECKED="CHECKED"' : '' ;
?>
		<h3>Plugin Settings</h3>		



		<form action='?page=<?php echo $_GET['page']; ?>#wpss_settings' name='wpss_settings_form' method="post">
		<h5>Allow multiple slideshows</h5>
		<p><input type='checkbox' <?php echo $checked; ?> name="<?php echo $this->option_multiple_slideshows; ?>" /> Yes </p>
		<h5>Default slideshow stylesheet</h5>
		<?php $this->show_stylesheet_list($this->option_default_style_slideshow, $slideshow_stylesheet); ?>
		<h5>Default single-photo stylesheet</h5>
		<?php $this->show_stylesheet_list($this->option_default_style_photo, $photo_stylesheet); ?>
		<h5>Default post image stylesheet</h5>
		<?php $this->show_stylesheet_list($this->option_default_style_post_image, $post_image_stylesheet); ?>

		<p><input type='submit' name='wpss_settings_update' value="Update" /></p>
		</form>
		<hr/>
<?php

	}


	function manage_slideshows(){
		if($_GET['delete_slideshow']){
			$this->process_delete_slideshow();
		}else if($_POST[$this->plugin_prefix.'process']){
			$this->save_slideshow('','');
		}
?>
		<div class='wrap'>
		<h2>Slideshow Management</h2>
		<div id="wpss_tabs">
		     <ul>
			 <li><a href="#wpss_settings"><span>Settings</span></a></li>
			 <li><a href="#wpss_main"><span>Slideshows</span></a></li>
			 <li><a href="#wpss_import"><span>Import</span></a></li>
		     </ul>
		</div>

		<div id='wpss_settings'>
	<?php echo $this->plugin_settings(); ?>
		</div>

		<div id='wpss_main'>
<?php
		echo "<form name='slideshow_management_form' id='slideshow_management_form' action='?page=" . $_GET['page'] . "&delete_slideshow=true' method='post'>";
		echo "<input type='hidden' id='slideshow_id' name='slideshow_id' />";
		echo "<input type='hidden' id='slideshow_title' name='slideshow_title' />";
		$output = $this->get_list_slideshows('form');

		echo $output;
		echo "</form>";
		echo "<br /><hr /><br />";
		echo "<form id='edit_slideshow_form' action='?page=" . $_GET['page'] . "' method='post'>";
		echo '<input type="hidden" name="'.$this->plugin_prefix.'noncemane" id="'.$this->plugin_prefix.'noncemane" value="' .wp_create_nonce( plugin_basename(__FILE__) ) .'" />';
		$this->print_slideshow_html(true);//print beginnin generic html, with edit mode set to true, meaning hide this div initially

		echo "</form>";

		//echo "post id: " .$post->ID;
?>

<script type='text/javascript'>

	function clearSlideshows(editbutton){
		jQuery(".slideshowEditButton").show();
		jQuery(editbutton).hide();
		
		slideshowOrganizer.clearOrganizer();
		jQuery("#<?php echo $this->plugin_prefix; ?>slideshows_ul").html('');

	}

	function maybeDeleteSlideshow(sid){
		document.getElementById('slideshow_id').value=sid;
		document.getElementById('slideshow_title').value=document.getElementById('title['+sid+']').innerHTML;
		var answer = confirm("Are you sure you want to delete slideshow '"+document.getElementById('slideshow_title').value+"'?");
		if (answer){
			jQuery("#slideshow_management_form").submit();
		}
		else{
			return false;
		}


	}
	 jQuery(document).ready(function(){
		jQuery('#wpss_tabs').tabs({ remote: true });
		slideshowOrganizer = new itemOrganizer("<?php echo $this->plugin_prefix; ?>slideshows_ul");
	});

</script>
			</div>

		<div id='wpss_import'>
	<?php echo $this->import_utility(); ?>
		</div>
<?php 
		echo "</div>";
	}

	function page_form(){
		$this->post_form('page');

	}

	function slideshowFields($id, $itemV= array()){
		

		$fieldW = '70%';

		$str = "<tr>
				<td style='width:auto'>Slideshow title: 
				</td>
				<td style='width:auto;'> 
				<input type='text' tabindex='140' id='slideshowItem[$id][title]' name='slideshowItem[$id][title]' style='width:$fieldW' value='" . $itemV['title'] . "' class='".$this->plugin_prefix."required' />
				</td>
				<td style='vertical-align:top;width:auto;' rowspan='3'>
				Slideshow description:<br />
				<textarea tabindex='143' id='slideshowItem[$id][description]' name='slideshowItem[$id][description]' style='width:100%;height:100%;' class='".$this->plugin_prefix."required' >".$itemV['description']."</textarea>
				</td>
			</tr>
			<tr>
				<td>Slideshow photo credit:
				</td>
				<td>
				<input type='text' tabindex='141' name='slideshowItem[$id][photo_credit]' style='width:$fieldW' value='" . $itemV['photo_credit'] . "' />
				</td>
			</tr>
			<tr>
				<td class='topalign'>Slideshow geo location:
				</td>
				<td>
				<input type='text' tabindex='142' name='slideshowItem[$id][geo_location]' id='slideshowItem[$id][geo_location]' style='width:50%' value='" . $itemV['geo_location'] . "' onkeyup='slideshow_getCoords(this.id);' onblur='slideshow_getCoords(this.id);' /><img onClick='showMap(this.previousSibling.value,this.nextSibling.id, this.nextSibling.nextSibling.id, this);' class='map_icon centervertical' src='".$this->plugin_url()."images/map_icon.jpg' /><input type='hidden' id='slideshowItem[$id][latitude]' name='slideshowItem[$id][latitude]' ReadOnly size='4' value='" . $itemV['latitude'] . "' /><input type='hidden' id='slideshowItem[$id][longitude]' name='slideshowItem[$id][longitude]' ReadOnly size='4' value='" . $itemV['longitude'] . "' /><!--&#176; latitude--><!--&#176; longitude-->
				</td>

			</tr>

			</table>
				<ul class='sortable' style='margin-bottom: 10px;' id='".$this->plugin_prefix."slideshow_photos_ul_${id}'></ul><span id='addphoto_button_$id' class='button' style='margin-left:45%;' class='alignright' onClick='pickPhoto(this.previousSibling.id);' tip='Add Media'>Add photo</span>

";

		$str .= "</li>";			//$notes = str_replace(array("\r", "\n", "\0"), array('\r', '\n', '\0'), $notes);
		
		return $str;
	}

	function slideshowItemHTML($id, $itemV = array(), $single=false, $edit_mode = false){
		$itemV = $this->utils->convertquotes($itemV);
		global $wpdb;
		if(!$id){
			$id = "insert_id";
		}
		$heading = $single ? 'Single Photo' : 'Slideshow';
		
		$ret = "<li class='".$this->plugin_prefix."slideshow_container' id='slideshowContainer_$id'>";
		if(!$edit_mode){
			$ret .= "<span class='button' style='float:right' onclick='if ( this.parentNode.parentNode && this.parentNode.parentNode.removeChild ) {slideshowOrganizer.organizerRemoveItem(this.parentNode.id);this.parentNode.parentNode.removeChild(this.parentNode);}'>Remove</span>";
			$ret .= "<span class='button slideshow_collapse_button' style='float:right'>Collapse</span>";
		}
		$ret .= "<h4 class='organizer_item_heading' id='slideshow_heading_$id'>$heading</h4>";
		if($itemV['update']){
			$ret .= "<input type='hidden' name='slideshowItem[$id][update]' id='slideshowItem[$id][update] value='".$itemV['update']."' /> ";
		}
		$ret .= "<div class='slideshow_wrapper'>";
		if($single){
			 $table_props = "id='slideshowTable_$id' style='display:none'";
		}else{
			 $table_props = "style='width:100%'";
		}
		$ret .= "<table $table_props >";
		$ret .= $this->slideshowFields($id, $itemV);
		$ret .= "</div>";
		return str_replace(array("\r", "\n", "\0"),array('\r', '\n', '\0'), addslashes($ret));

	}

	function slideshowSingleItemHTML($id, $itemV = array()){
		global $wpdb;
		$itemV = $this->utils->convertquotes($itemV);
		if(!$id){
			$id = "insert_id";
		}
		$ret = "<li class='".$this->plugin_prefix."slideshow_container' id='slideshowContainer_$id' >
					
					<span class='button' style='float:right' onclick='if ( this.parentNode.parentNode && this.parentNode.parentNode.removeChild ) {slideshowOrganizer.organizerRemoveItem(this.parentNode.id);this.parentNode.parentNode.removeChild(this.parentNode);}'>Remove</span>
					<h4 class='organizer_item_heading'>Single Photo</h4>";
		if($itemV['update']){
			$ret .= "<input type='hidden' name='slideshowItem[$id][update]' id='slideshowItem[$id][update]' value='".$itemV['update']."' /> ";
		}
		$ret .= "<table  id='slideshowTable_$id' style='width:100%;display:none'>";
		$ret .= $this->slideshowFields($id, $itemV);
		/*$ret .= "<tr>
				<td style='width:300px;'>Slideshow title: 
				</td>
				<td width='370px;'>
				<input type='text' id='slideshowItem[$id][title]' name='slideshowItem[$id][title]' size='20' value='" . $itemV['title'] . "' class='".$this->plugin_prefix."required'/> *
				</td>
				<td style='vertical-align:top;width:400px;' rowspan='3'>
				Slideshow description:<br />
				<textarea id='slideshowItem[$id][description]' name='slideshowItem[$id][description]' rows='4' cols='38' class='".$this->plugin_prefix."required' >".$itemV['description']."</textarea>
				</td>
			</tr>
			<tr>
				<td>Slideshow photo credit:
				</td>
				<td>
				<input type='text' name='slideshowItem[$id][photo_credit]' size='20' value='" . $itemV['photo_credit'] . "' />
				</td>
			</tr>
			<tr>
				<td class='topalign'>Slideshow geo location:
				</td>
				<td>
				<input type='text' name='slideshowItem[$id][geo_location]' id='slideshowItem[$id][geo_location]' size='13' value='" . $itemV['geo_location'] . "' onkeyup='slideshow_getCoords(this.id);' onblur='slideshow_getCoords(this.id);' /><img onClick='showMap(this.previousSibling.value,this.nextSibling.id, this.nextSibling.nextSibling.id, this);' class='map_icon centervertical' src='".$this->plugin_url()."images/map_icon.jpg' /><input type='hidden' id='slideshowItem[$id][latitude]' name='slideshowItem[$id][latitude]' ReadOnly size='4' value='" . $itemV['latitude'] . "' /><input type='hidden' id='slideshowItem[$id][longitude]' name='slideshowItem[$id][longitude]' ReadOnly size='4' value='" . $itemV['longitude'] . "' /><!--&#176; latitude--><!--&#176; longitude-->
				</td>

			</tr>

			</table>

					<ul class='sortable' id='".$this->plugin_prefix."slideshow_photos_ul_${id}'></ul><span  id='addphoto_button_$id' class='button' style='margin-left:47%;' class='alignright' onClick='pickPhoto(this.previousSibling.id);' tip='Add Media'>Add photo</span>
 

";

		$ret .= "</li>";			//$notes = str_replace(array("\r", "\n", "\0"), array('\r', '\n', '\0'), $notes);
		 */
		return str_replace(array("\r", "\n", "\0"),array('\r', '\n', '\0'), addslashes($ret));

	}
	function get_wp_photo_id($pid){
		global $wpdb;
		$wp_photo_id = $wpdb->get_var("SELECT wp_photo_id FROM $this->t_p WHERE photo_id=$pid;");
		return $wp_photo_id;
	}

	function getPhoto($pid){
		global $wpdb;

		$photo_meta_rels = $wpdb->prefix.$this->plugin_prefix."photo_meta_relations";
		$slideshow_photos = $wpdb->prefix.$this->plugin_prefix."photos";
		$photo_meta = $wpdb->prefix.$this->plugin_prefix."photo_meta";
		$query ="SELECT DISTINCT wp_photo_id, meta_name, meta_value FROM  $slideshow_photos as sp,$photo_meta as pm, $photo_meta_rels as pmr WHERE
				sp.photo_id=pmr.photo_id AND
				pmr.meta_id=pm.meta_id AND
				sp.photo_id=$pid;";
		//echo $query;
		$result = $wpdb->get_results($query);
		$photo= array();
		foreach($result as $row){
			$photo['wp_photo_id']=$row->wp_photo_id;
			$photo[$row->meta_name]=$row->meta_value;
		}
		$fprops = $this->getPhotoFixedProps($photo['wp_photo_id']);
		$photo = array_merge($photo,$fprops);
		$photo['update']='yes';
		return $photo;

	}

	function getPhotos($sid){
		global $wpdb;

		$photo_meta_rels = $wpdb->prefix.$this->plugin_prefix."photo_meta_relations";
		$slideshow_photos = $wpdb->prefix.$this->plugin_prefix."photos";
		$photo_meta = $wpdb->prefix.$this->plugin_prefix."photo_meta";
		$slideshow_photo_rels = $wpdb->prefix.$this->plugin_prefix."slideshow_photo_relations";
		$query ="SELECT DISTINCT spr.photo_id, wp_photo_id, meta_name, meta_value FROM $slideshow_photo_rels as spr, $slideshow_photos as sp,$photo_meta as pm, $photo_meta_rels as pmr WHERE
				spr.photo_id=sp.photo_id AND
				sp.photo_id=pmr.photo_id AND
				pmr.meta_id=pm.meta_id AND
				spr.slideshow_id=$sid ORDER BY spr.photo_order;";
		//echo $query;
		$result = $wpdb->get_results($query);
		$photos= array();
		foreach($result as $row){
			$photos[$row->photo_id]['wp_photo_id']=$row->wp_photo_id;
			$photos[$row->photo_id][$row->meta_name]=$row->meta_value;

		}
		//print_r($photos);
		foreach($photos as $spid => $photo){
			$fprops = $this->getPhotoFixedProps($photo['wp_photo_id']);
			$photos[$spid] = array_merge($photos[$spid],$fprops);
			$photos[$spid]['update']='yes';
		}
		return (array)$photos;

	}

	function getPhotoFixedProps($post_id){
		global $wpdb;

		//$query = "SELECT photo_credit, geo_location, latitude, longitude FROM ".$wpdb->prefix.$this->plugin_prefix."slideshow_photos WHERE wp_photo_id=$post_id LIMIT 1;";
		//$results = $wpdb->get_results($query);
		foreach($this->fixed_photo_props as $prop){
			//$props[$prop] = htmlentities(stripslashes(get_post_meta($post_id, $prop, true)));
			$props[$prop] = stripslashes(get_post_meta($post_id, $prop, true));
		}
		$thumb = wp_get_attachment_image_src( $post_id, 'thumbnail');
		$props['url'] = $thumb[0];

		$props['url'] = $this->process_thumb_url($props['url'], $post_id);

		$large = wp_get_attachment_image_src( $post_id, 'large');
		if(!is_array($large))echo "error: id=$post_id<br />";
		$props['large_url'] =  $large[0] ? $large[0] : current(wp_get_attachment_image_src( $post_id, 'full'));
		$props['large_url'] = $this->process_large_url($props['large_url'], $post_id);

		return $props;
	}

	function getSlideshowProps($sid){
		global $wpdb;
		$table = $wpdb->prefix.$this->plugin_prefix."slideshows";

		$results = $wpdb->get_row("SELECT * FROM $table WHERE ID=$sid");
		//$results->description = str_replace(array("\r", "\n", "\0"), array('\r', '\n', '\0'), $results->description);
		return (array)$results;
	}

	function print_slideshow_html($edit_mode = false){
		if($edit_mode){$hide='display:none;';}
		echo "<div style='$hide' id='".$this->plugin_prefix."parent_div'><input type='hidden' name='".$this->plugin_prefix."process' id='".$this->plugin_prefix."process' value='yes'/>";
		if($edit_mode){
			echo "<div class='slideshow_box_footer' style='text-align:right;$hide'>".'<input class="button button-highlighted" id="'.$this->plugin_prefix.'save" type="submit" value="Save" name="save" />'."</div>";
		}
		echo "<div><span class='".$this->plugin_prefix."required' >&nbsp;</span> Required fields</div>";
		if(!$edit_mode){
			$hideSettings = get_option($this->option_multiple_slideshows) ? "showSlideshowMenu(this.id, false);" : "showSlideshowMenu(this.value, false);";
			echo "	<div style='padding:10px;' id='slideshow_options' >
			<div style='float:right'><select style='display: none;' id='wpss_post_photo' class='".$this->plugin_prefix."required' name='wpss_post_photo'><option value=''>Choose post image</option></select></div>
			<span id='single_photo_button'  class='button' style='margin-top:10px;' onclick=\"slideshowOrganizer.organizerAddItem('','".$this->slideshowItemHTML('', array(), true)."', true);currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();showSlideshowMenu(this.id, false);\"> Add Photo</span> <span id='wpss_or'>OR</span>
				<span id='slideshow_button' class='button' style='margin-top:10px;' onclick=\"slideshowOrganizer.organizerAddItem('','".$this->slideshowItemHTML('')."');currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();$hideSettings\"> Add Slideshow</span>

			</div>";
		}




		echo "<ul class='sortable' id='".$this->plugin_prefix."slideshows_ul'>";
		echo '</ul>';
		
		echo '</div>';

		echo "<div class='slideshow_box_footer' style='text-align:right;$hide'>".'<input class="button button-highlighted" id="'.$this->plugin_prefix.'save" type="submit" value="Save" name="save" />'."</div>";		

		echo '<script type="text/javascript">
			slideshowOrganizer = new itemOrganizer("'.$this->plugin_prefix.'slideshows_ul");
			 </script>';//jQuery("#'.$this->plugin_prefix.'slideshows_ul").sortable();
	}

	function post_form($entryType = 'post') {
		global $post;

		echo '<input type="hidden" name="'.$this->plugin_prefix.'nonce" id="'.$this->plugin_prefix.'nonce" value="' .wp_create_nonce( plugin_basename(__FILE__) ) .'" />';

		$this->print_slideshow_html();


		//echo "post id: " .$post->ID;


?>
<script type="text/javascript">
jQuery(document).ready(function() {
<?php
		if($slideshows=get_post_meta($post->ID,$this->fieldname, false)){//slideshow
			if(is_array($slideshows)){
				$post_image_id = get_post_meta($post->ID, $this->postmeta_post_image, true);
				foreach($slideshows as $sid){
					$sProps = $this->getSlideshowProps($sid);
					$sProps['update']= 'yes';
					$thumb_id = $sProps['thumb_id'];
					echo "slideshowOrganizer.organizerAddItem($sid,\"".$this->slideshowItemHTML($sid,$sProps)."\");
						currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();";
					$photos = $this->getPhotos($sid);
					//print_r($photos);
					if(is_array($photos)){
						foreach($photos as $pid => $photo){
							if($pid == $post_image_id){
								$post_image_slideshow_id = $sid;
							}
							if($pid == $thumb_id){
								$photo['cover'] = 'yes';	
							}
							echo "send_to_slideshow($pid,\"" . $this->photoItemHTML_simple($pid,$photo) . "\");";
							if($pid == $thumb_id){
								echo "setCoverImage($sid, $pid);";
							}							
						}
					}
				}

				echo get_option($this->option_multiple_slideshows) ? "showSlideshowMenu('slideshow_button', false);" : "showSlideshowMenu('', false);";
				if($post_image_id){
					echo "reloadPostImageSelect('${post_image_slideshow_id}_${post_image_id}');";
				}
			}

		}else if($pid=get_post_meta($post->ID,$this->plugin_prefix.'photo_id', true)){//single photo
			echo "slideshowOrganizer.organizerAddItem('',\"".$this->slideshowItemHTML('',array(), true)."\", true);
				currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();";
			$photo = $this->getPhoto($pid);
			echo "\nsend_to_slideshow($pid,'" . $this->photoItemHTML_simple($pid,$photo) . "');";
			echo "showSlideshowMenu('', false);";
		}
?>

});
</script>
<?php
	}

	function process_medium_url($url, $id=''){
		$pic =  get_post_meta($id, '_wp_attachment_metadata');
		if(!is_array($pic))return $url;
		if(isset($pic[0]['sizes']['medium'])){
			return str_replace(basename($url), $pic[0]['sizes']['medium']['file'], $url);
	
		}
		else if(!isset($pic[0]['sizes']['medium']['width']) || !isset($pic[0]['sizes']['medium']['height'])){
			return $url;
		}

		$w = $pic[0]['sizes']['medium']['width'];//get_option('thumbnail_size_w');
		$h = $pic[0]['sizes']['medium']['height'];

		if(!preg_match("/\-${w}x${h}\./", $url)){
			$base = basename($url);
			$dot = strrpos($base, '.');
			$name = substr($base, 0, $dot);
			$new_name = $name . "-${w}x${h}";

			return str_replace($name, $new_name, $url);
		}
		return $url;
	}

	function process_thumb_url($url, $id=''){
		//return $url;
		$pic =  get_post_meta($id, '_wp_attachment_metadata');
		if(!is_array($pic))return $url;
		if(isset($pic[0]['sizes']['thumbnail'])){
			return str_replace(basename($url), $pic[0]['sizes']['thumbnail']['file'], $url);
	
		}
		else if(!isset($pic[0]['sizes']['thumbnail']['width']) || !isset($pic[0]['sizes']['thumbnail']['height'])){
			return $url;
		}
		
		$tw = $pic[0]['sizes']['thumbnail']['width'];//get_option('thumbnail_size_w');
		$th = $pic[0]['sizes']['thumbnail']['height'];
		//echo "UrL: $url";
		if(!preg_match("/\-${tw}x${th}\./", $url)){
		//if(false){
			$base = basename($url);
			$dot = strrpos($base, '.');
			$name = substr($base, 0, $dot);
			$new_name = $name . "-${tw}x${th}";
			$new_url = str_replace($name, $new_name, $url);
			//if(@fopen($new_url,'r')!== false){
			return $new_url;
			//}
		}
		return $url;
	}

	function process_large_url($url, $id=''){
		//return $url;
		$pic =  get_post_meta($id, '_wp_attachment_metadata');
		if(!is_array($pic))return $url;
		if(isset($pic[0]['sizes']['large'])){
			return str_replace(basename($url), $pic[0]['sizes']['large']['file'], $url);
	
		}else if(!isset($pic[0]['sizes']['large']['width']) || !isset($pic[0]['sizes']['large']['height'])){
			return $url;
		}


		$tw = $pic[0]['sizes']['large']['width'];//get_option('thumbnail_size_w');
		$th = $pic[0]['sizes']['large']['height'];
		if(!preg_match("/\-${tw}x${th}\./", $url)){
		//if(false){
			$base = basename($url);
			$dot = strrpos($base, '.');
			$name = substr($base, 0, $dot);
			$new_name = $name . "-${tw}x${th}";
			$new_url = str_replace($name, $new_name, $url);
			//if(@fopen($new_url,'r')!== false){
			return $new_url;
			//}
		}
		return $url;
	}

	function get_photo_xml($photo, $root_tag = 'photo'){
		if(!is_array($photo)){
			$photo = $this->getPhoto($photo);
		}
		
		$str = "<$root_tag>\n";
		unset($photo['update']);
		$photo['thumb_url']=$this->process_thumb_url($photo['url'], $photo['wp_photo_id']);
		$photo['medium_url']=$this->process_medium_url(current(wp_get_attachment_image_src( $photo['wp_photo_id'], $image_size)), $photo['wp_photo_id']);

		$photo['large_url']=$this->process_large_url(current(wp_get_attachment_image_src( $photo['wp_photo_id'], 'large')), $photo['wp_photo_id']);
		$photo['url']=current(wp_get_attachment_image_src( $photo['wp_photo_id'], 'full'));//str_ireplace('-150x150', '', $photo['thumb_url']);

		foreach($photo as $name => $value){
			if($value){
				$str .= "<$name>". htmlspecialchars($value)."</$name>\n";
			}								
		}
		$str .= "</$root_tag>\n";
		return $str;
	}

	function get_slideshow_xml($sid){

		$str = '';
		$photos = $this->getPhotos($sid);
		foreach($photos as $key => $photo){
			if(!$this->utils->wp_exist_post($photo['wp_photo_id'])){
				unset($photos[$key]);
			}
		}

		if(sizeof($photos)==0)return;

		$sProps = $this->getSlideshowProps($sid);
		$sProps['update']= 'yes';


		$str .= '<slideshow>';
		unset($sProps['update']);
		foreach($sProps as $name => $value){
			if($value){
				$str .= "<$name>$value</$name>\n";
			}
		}
		//slideshow thumb photo
		//********************
		$thumb_id = $sProps['thumb_id'];
		$thumb_photo = array();
		$pid = '';
		//echo "thumb_id : $thumb_id \n<br />";
		if(!$thumb_id){
			$thumb_photo = current($photos);
		}else{
			$thumb_photo = $this->getPhoto($thumb_id);
			//$pid = key($thumb_photo);
			//$thumb_photo = current($thumb_photo);
		}
		
		//$pid = $thumb_id;			
		$str .= $this->get_photo_xml($thumb_photo,'slideshow_thumb');

		if(is_array($photos)){
			foreach($photos as $pid => $photo){
				//echo "send_to_slideshow($pid,\"" . $this->photoItemHTML_simple($pid,$photo) . "\");";
				$str .= $this->get_photo_xml($photo);
			}
		}
		$str .= "</slideshow>\n\n";
		return $str;
	}	

	function getXML($post_id, $stylesheet, $photo_id = -1){
		global $wpdb;

		$image_size = 'medium';
		$str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$str .= '<?xml-stylesheet type="text/xsl" href="'.$this->plugin_url().'stylesheets/'.$stylesheet.'" version="1.0"?>' . "\n";
		if($slideshows=get_post_meta($post_id,$this->fieldname, false)){
			if(is_array($slideshows)){
				$str .="<slideshows>";
				foreach($slideshows as $sid){
					$str .= $this->get_slideshow_xml($sid);
				}
				$str .="</slideshows>";
			}
		}else if($pid=get_post_meta($post_id,$this->plugin_prefix.'photo_id', true)){//photo only
			$str .= $this->get_photo_xml($pid);
		}
		//echo nl2br(htmlentities($str));
		return $str;

	}


	function show_photos($post_id, $stylesheet=''){
		global $wpdb;
		if($slideshows=get_post_meta($post_id,$this->fieldname, false)){
			$stylesheet = $stylesheet ? $stylesheet : get_option($this->option_default_style_slideshow);
		}else{
			$stylesheet = $stylesheet ? $stylesheet : get_option($this->option_default_style_photo);
		}
		$xml_text = $this->getXML($post_id, $stylesheet);
		//echo $xml_text;
		$xml = new DOMDocument;
		if(!@$xml->loadXML($xml_text)){
			//echo $xml_text;
			//$xml->loadXML($xml_text);
			return;
		}

		$xsl = new DOMDocument;
		@$xsl->load($this->plugin_url().'/stylesheets/'.$stylesheet);

		// Configure the transformer
		$proc = new XSLTProcessor;
		@$proc->importStyleSheet($xsl); // attach the xsl rules
		$output = @$proc->transformToXML($xml);
		if($output){
			echo $output;
		}else{
			echo "error";
		}

	}


	function makeField($pid, $fieldlable, $fieldname, $req=false){
		$field = "</td></tr>\n\t\t<tr>
			<th class='label'>
				<label for='attachments[$pid][$fieldname]'>
				<span class='alignleft'>$fieldlable</span>";
		if($req){
			$field .= "<span class='alignright'>
					<abbr class='required' title='required'>*</abbr>
					</span>";
		}
		$field .=" <br class='clear'/>
				</label>
				</th><td class='field'>";
		$field .= "<input type=\"text\" name=\"attachments[$pid][$fieldname]\" id=\"attachments[$pid][$fieldname]\"/>";

			
		return $field;
	}

/*// get an image to represent an attachment - a mime icon for files, thumbnail or intermediate size for images
// returns an array (url, width, height), or false if no image is available
function wp_get_attachment_image_src($attachment_id, $size='thumbnail', $icon = false) {
	
	// get a thumbnail or intermediate image if there is one
	if ( $image = image_downsize($attachment_id, $size) )
		return $image;

	if ( $icon && $src = wp_mime_type_icon($attachment_id) ) {
		$icon_dir = apply_filters( 'icon_dir', includes_url('images/crystal') );
		$src_file = $icon_dir . '/' . basename($src);
		@list($width, $height) = getimagesize($src_file);
	}
	if ( $src && $width && $height )
		return array( $src, $width, $height );
	return false;
}*/


	function photoItemHTML_simple($id, $itemV = array(), $doJS=false){
		global $wpdb;

		$itemV = $this->utils->convertquotes($itemV);
		if(!$id){
			$id = "insert_id";
		}
		$ret = array();
		$ret[] = array('escape'=>true,'text'=>"<li  class='".$this->plugin_prefix."photo_container' id='photoContainer_$id'>");

		if($itemV['update']){
			$ret[] = array('escape'=>true, 'text'=>"<input type='hidden' id='slideshowItem[s_id][photos][$id][update]' name='slideshowItem[s_id][photos][$id][update]' value='".$itemV['update']."' />");
			if(!$this->utils->wp_exist_post($this->get_wp_photo_id($id))){
				$ret[] = array('escape'=>true, 'text'=>"<div id='transparency_s_id_$id' class='missing_photo_transparency' ><div style='margin-bottom:50px;'>Photo is missing</div><span class='button' style='padding:10px;' onclick='chooseNewPhoto(s_id, $id);'>Set new photo</span></div>");
			}
		}

		$ret[] = array('escape'=>true,'text'=>" <span id='deletePhotoButton_${id}_s_id' class='button' style='float:right' onclick='if ( this.parentNode.parentNode && this.parentNode.parentNode.removeChild ) {this.parentNode.parentNode.removeChild(this.parentNode);removePhotoItem(this.id);}'><img class='adjustIcon' src='".$this->plugin_url()."images/delete.gif' /></span>");

		$ret[] = array('escape'=>true,'text'=>"<input type='hidden' id='slideshowItem[s_id][photos][$id][cover]' class='photo_in_slideshow_s_id' name='slideshowItem[s_id][photos][$id][cover]' value='".$itemV['cover']."' /> 
			<table>
			<tr>
				<td>Title: 
				</td>
				<td>
				<input type='text' id='slideshowItem[s_id][photos][$id][title]' name='slideshowItem[s_id][photos][$id][title]' size='20' value='");
		if($itemV['title']){
			$ret[] = array('escape'=>true, 'text'=>$itemV['title']);
		}else if($doJS){
			$ret[] = array('escape'=>false, 'text'=>"'+ convertquotes(document.getElementById('${id}[title]').value) +'");
		}
		$ret[] = array('escape'=>true, 'text'=>"' class='".$this->plugin_prefix."required photo_title' />
				</td>
				<td rowspan='5' style='text-align:center;width: 100%'>
					<img id='img_s_id_$id' class='wpss_photo_thumb' title='Click to replace image' onclick='confirmChooseNewPhoto(s_id, $id);' src='". $itemV['url'] ."' /><br />
					<span onclick='setCoverImage(s_id, $id);' id='cover_button_s_id_${id}' class='button set_cover_button_s_id' style='text-align:center;margin-top:5px;' >Slideshow Thumbnail</span>
				</td>
			</tr>
			<tr>
				<td>Photo credit:
				</td>
				<td>
				<input type='text' id='slideshowItem[s_id][photos][$id][photo_credit]' name='slideshowItem[s_id][photos][$id][photo_credit]' size='20' ReadOnly value='" . $itemV['photo_credit'] . "'  title='Click to edit' class='editable ".$this->plugin_prefix."required' /> <span class='button' style='display:none' >Update</span>
				</td>
			</tr>
			<tr>
				<td>Geo location:
				</td>
				<td>
				<input type='text' name='slideshowItem[s_id][photos][$id][geo_location]' id='slideshowItem[s_id][photos][$id][geo_location]' size='20' ReadOnly value='" . $itemV['geo_location'] . "' class='editable' title='Click to edit' /><span class='button' style='display:none' >Update</span><img onClick='showMapForPhoto(this.previousSibling.previousSibling.value);' class='map_icon centervertical' src='".$this->plugin_url()."images/map_icon.jpg' />
				</td>

			</tr>
			<tr>
				<td>Original URL:
				</td>
				<td>
				<input type='text' id='slideshowItem[s_id][photos][$id][original_url]' name='slideshowItem[s_id][photos][$id][original_url]' size='20' ReadOnly value='" . $itemV['original_url'] . "'  title='Click to edit' class='editable' /> <span class='button' style='display:none' >Update</span>
				</td>
			</tr>
			<tr>
				<td>Alt text:
				</td>
				<td>
				<input type='text' name='slideshowItem[s_id][photos][$id][alt]' size='20' value='".$itemV['alt']."' />
				</td>
			</tr>


			<tr>
				<td>Caption:
				</td>
				<td style='width:auto'>
					<textarea name='slideshowItem[s_id][photos][$id][caption]' id='slideshowItem[s_id][photos][$id][caption]' tabindex='6' rows='2' style='width:300px' class='".$this->plugin_prefix."required' >".$itemV['caption']."</textarea>
				</td>

			</tr>

			</table>
		</li>");
		//print_r($ret);
		$dhtml = $this->utils->prepareDHTML($ret);
		return str_replace(array("\r", "\n", "\0"),array('\r', '\n', '\0'), $dhtml);
	}

	function photoItemHTML_javascript($id, $itemV = array()){
		global $wpdb;
		$itemV = $this->utils->convertquotes($itemV);
		if(!$id){
			$id = "insert_id";
		}
		$ret = array();
		$ret[] = array('escape'=>true,'text'=>"<li  class='".$this->plugin_prefix."photo_container' id='photoContainer_$id'>");
		$ret[] = array('escape'=>true,'text'=>"<span id='deletePhotoButton_${id}_s_id' class='button' style='float:right' onclick='if ( this.parentNode.parentNode && this.parentNode.parentNode.removeChild ) {this.parentNode.parentNode.removeChild(this.parentNode);}'><img class='adjustIcon' src='".$this->plugin_url()."images/delete.gif' /></span>");//maybeShowPhotoButton(this.id);
		if($itemV['update']){
			$ret[] = array('escape'=>true, 'text'=>"<input type='hidden' id='slideshowItem[s_id][photos][$id][update]' name='slideshowItem[s_id][photos][$id][update]' value='".$itemV['update']."' /> ");
		}

		$ret[] = array('escape'=>true,'text'=>"<input type='hidden' id='slideshowItem[s_id][photos][$id][cover]' class='photo_in_slideshow_s_id' name='slideshowItem[s_id][photos][$id][cover]' value='".$itemV['cover']."' /> 
		<table>
			<tr>
				<td>Title: 
				</td>
				<td>
				<input type='text' id='slideshowItem[s_id][photos][$id][title]' name='slideshowItem[s_id][photos][$id][title]' size='20' value='");
		if($itemV['title']){
			$ret[] = array('escape'=>true, 'text'=>$itemV['title']);
		}else{
			$ret[] = array('escape'=>false, 'text'=>"'+ convertquotes(document.getElementById('attachments[${id}][post_title]').value) +'");
		}

		$ret[] = array('escape'=>true, 'text'=>"' class='".$this->plugin_prefix."required photo_title' />
				</td>
				<td rowspan='5' style='width:100%; text-align:center;'>
					<img id='img_s_id_$id' class='wpss_photo_thumb' title='Click to replace image' onclick='confirmChooseNewPhoto(s_id, $id);' src='". $itemV['url'] ."' /><div onclick='setCoverImage(s_id, $id);' id='cover_button_s_id_${id}' class='button set_cover_button_s_id' style='text-align:center;margin-top:5px;' >Slideshow Thumbnail</div>
				</td>
			</tr>

			<tr>
				<td>Photo credit:
				</td>
				<td>
				<input type='text' name='slideshowItem[s_id][photos][$id][photo_credit]' id='slideshowItem[s_id][photos][$id][photo_credit]' size='20' ReadOnly value='");

		if($itemV['photo_credit']){
			$ret[] = array('escape'=>true, 'text'=>$itemV['photo_credit']);
		}else{
			$ret[] = array('escape'=>false, 'text'=>"'+ convertquotes(document.getElementById('attachments[${id}][photo_credit]').value) +'");
		}
		$ret[] = array('escape'=>true, 'text'=>"' title='Click to edit' class='editable ".$this->plugin_prefix."required' /> <span class='button' style='display:none' >Update</span>
				</td>
			</tr>
			<tr>
				<td>Geo location:
				</td>
				<td>
				<input type='text' name='slideshowItem[s_id][photos][$id][geo_location]' id='slideshowItem[s_id][photos][$id][geo_location]' size='20' ReadOnly value='");
		if($itemV['geo_location']){
			$ret[] = array('escape'=>true, 'text'=>$itemV['geo_location']);
		}else{
			$ret[] = array('escape'=>false, 'text'=>"'+ convertquotes(document.getElementById('attachments[${id}][geo_location]').value) +'");
		}

		$ret[] = array('escape'=>true, 'text'=>"' class='editable' title='Click to edit' /> <span class='button' style='display:none' >Update</span>
				</td>

			</tr>
			<tr>
				<td>Original URL:
				</td>
				<td>
				<input type='text' id='slideshowItem[s_id][photos][$id][original_url]' name='slideshowItem[s_id][photos][$id][original_url]' size='20' ReadOnly value='");
		if($itemV['original_url']){
			$ret[] = array('escape'=>true, 'text'=>$itemV['original_url']);
		}else{
			$ret[] = array('escape'=>false, 'text'=>"'+ convertquotes(document.getElementById('attachments[${id}][original_url]').value) +'");
		}
	 	$ret[] = array('escape'=>true, 'text'=>"'  title='Click to edit' class='editable' /> <span class='button' style='display:none' >Update</span>
				</td>
			</tr>
			<tr>
				<td>Alt text:
				</td>
				<td>
				<input type='text' name='slideshowItem[s_id][photos][$id][alt]' size='20' value='");
		if($itemV['alt']){
			$ret[] = array('escape'=>true, 'text'=>$itemV['alt']);
		}else{
			$ret[] = array('escape'=>false, 'text'=>"'+ convertquotes(removeHTMLTags(document.getElementById('attachments[${id}][post_excerpt]').value)) +'");
		}
		$ret[] = array('escape'=>true, 'text'=>"' />
				</td>
			</tr>
			<tr>
				<td>Caption:
				</td>
				<td style='width:auto;'>
					<textarea name='slideshowItem[s_id][photos][$id][caption]' id='slideshowItem[s_id][photos][$id][caption]' tabindex='6' rows='2' style='width:300px' class='".$this->plugin_prefix."required' >");
		if($itemV['caption']){
			$ret[] = array('escape'=>true, 'text'=>$itemV['caption']);
		}else{
			$ret[] = array('escape'=>false, 'text'=>"'+ convertquotes(document.getElementById('attachments[${id}][post_content]').value) +'");
		}
		$ret[] = array('escape'=>true, 'text'=>"</textarea>
				</td>

			</tr>

			</table>
		</li>");
		//print_r($ret);
		$dhtml = $this->utils->prepareDHTML($ret);
		return str_replace(array("\r", "\n", "\0"),array('\r', '\n', '\0'), $dhtml);
	}


	function get_meta_query($meta_name, $post_id, $org_title){
		global $wpdb;
		$photo_meta_rels = $wpdb->prefix.$this->plugin_prefix."photo_meta_relations";


		$slideshow_photos = $wpdb->prefix.$this->plugin_prefix."photos";
		$photo_meta = $wpdb->prefix.$this->plugin_prefix."photo_meta";


		$query ="SELECT DISTINCT meta_value FROM $photo_meta, $photo_meta_rels, $slideshow_photos WHERE $slideshow_photos.photo_id=$photo_meta_rels.photo_id AND
				$photo_meta_rels.meta_id=$photo_meta.meta_id AND
				$photo_meta.meta_name='$meta_name' AND
				wp_photo_id=$post_id ;";
		return $query;


	}

	function getExtraTitles($post_id, &$titles){
		global $wpdb;
		$query = $this->get_meta_query('title',$post_id, $titles[0]);

		$results=$wpdb->get_col($query);
		//echo $query;
		if(sizeof($results)>0){
			//print_r($results);
			$titles = array_merge($titles, $results);
		}
		//$titles[]='Dummy title';
		return;
	}

	function simple_image_chooser(){
		wp_enqueue_style( 'global' );
		wp_enqueue_style( 'wp-admin' );
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'media' );
		wp_iframe(array(&$this, 'simple_image_chooser_content'));
	}
/*

<style type="text/css">

	.select_column{
		color: #FFFFFF;
		background-color: #000000;
		text-align:center;
		height:50px;
		font-family: serif;
		font-style: italic;
		font-size: 13px;
		padding-bottom:0px;
	}
	img {
		height:50px;
	}

	.title_column{
		background-color: #C4AEAE;
	}

	.credit_column{
		background-color: #D0BF87;
	}

	.date_column{
		background-color: #A4B6B2;
	}
	
	.button {
		background-color:#E5E5E5;
		border:solid 1px #333333;
		padding:5px;
		cursor: pointer;
	}

	th, td {
		width: 25%;
	}
	.caption {
		margin:0px;
	}
</style> 
*/
	function wpss_media_upload_form(){
		$post_id = 0;
		$type='image';
		$form_action_url = admin_url("media-upload.php?type=$type&tab=type&post_id=0&flash=0");
		//$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);

		$callback = "type_form_$type";

?>
<form enctype="multipart/form-data" method="post" action="<?php echo attribute_escape($form_action_url); ?>" class="media-upload-form type-form validate" id="<?php echo $type; ?>-form">
<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
<?php wp_nonce_field('media-form'); ?>


<script type="text/javascript">
<!--
jQuery(function($){
	var preloaded = $(".media-item.preloaded");
	if ( preloaded.length > 0 ) {
		preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
	}
	updateMediaForm();
});
-->
</script>
<?php

	}

	function new_image_chooser(){

		$this->simple_image_chooser();

	}

	function simple_image_chooser_content(){
		global $wpdb;
		$pid = $_GET['post_id'];

		if($pid){
			echo '<p><a href="?type=slideshow_image" class="button"  >Go back</a></p>';

			$this->wpss_media_upload_form();
			?><button class='button' onclick="var win = window.dialogArguments || opener || parent || top;tb_remove();">Save</button><br />
			<?php
			echo '<div id="media-items">';
			echo get_media_items( $pid, null);


			echo '</div></form>';
			return;
		}


?>
<style type="text/css">

	.select_column{
		color: #FFFFFF;
		background-color: #000000;
		text-align:center;
		height:50px;
		font-family: serif;
		font-style: italic;
		font-size: 13px;
		padding-bottom:0px;
	}
	img {
		height:50px;
	}

	.title_column{
		background-color: #C4AEAE;
	}

	.credit_column{
		background-color: #D0BF87;
	}

	.date_column{
		background-color: #A4B6B2;
	}
	
	.button {
		background-color:#E5E5E5;
		border:solid 1px #333333;
		padding:5px;
		cursor: pointer;
	}

	th, td {
		width: 25%;
	}
	.caption {
		margin:0px;
	}
</style> 
	<h2>Upload new photo</h2>
<?php			$_REQUEST['flash'] = 0;
			$this->wpss_media_upload_form();
			media_upload_form();
			$img_id = $_GET['img_id'];
			$img_id_var = $img_id ? "&img_id=$img_id" : '';
			//echo $img_id ? $img_id : 'none';

			echo $img_id ? "<input type='hidden' name='img_id' value='$img_id' />" : '';
			echo "</form>\n";
//<a href='media-upload.php?post_id=$pid&type=image&flash=0' class='button'>Upload new photo</a>
?>

	<hr />
	<h2 style='margin-bottom:50px'>Choose picture</h2>
<div>

<form method='POST' name='search_form' action='?type=slideshow_image&post_id=<?php echo $pid; ?><?php echo $img_id_var;?>' >
<input id="post-search-input" type="text" value="<?php $search = $_POST['s'] ? $_POST['s'] : $_GET['s']; echo $_POST['see_all'] ? '' : $search; ?>" name="s"/>
<input type="submit" value="Search Photos"/> <input type="submit" name='see_all' value="See all"/>

</form>
</div>
<?php
		$maxposts = 10;
		$start = $_GET['start'] ? $_GET['start'] : 0;

		if(!isset($_GET['order_by']) && !isset($_REQUEST['s']) && !isset($_POST['see_all'])){
			echo '<hr />';
			return;
		}
		//echo $_POST['order_by'] ? "Order by "  . $_POST['order_by'] : '';
		$msg = '';
		$orderby = $_GET['order_by'] ? $_GET['order_by'] : 'title';
		$direction = $_GET['direction'] ? $_GET['direction'] : 'asc';
		if($_GET['order_by'] != $_GET['porder_by']){
			$direction = 'asc';
		}

		$query = "SELECT ID, post_title, post_excerpt, post_content, post_modified, post_mime_type FROM ".$wpdb->prefix."posts WHERE post_mime_type LIKE '%image%' AND post_type='attachment'";
		if($orderby == 'title'){
			$query .= " ORDER BY post_title $direction";
		}else if($orderby ==  'date'){
			$query .= " ORDER BY post_modified $direction";
		}else if($orderby == 'photo_credit'){
			//$query = "SELECT DISTINCT posts.ID, posts.post_title, posts.post_excerpt, posts.post_content FROM ".$wpdb->prefix."posts as posts RIGHT JOIN ".$wpdb->prefix."postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_mime_type LIKE '%image%' AND postmeta.meta_key='photo_credit' ORDER BY postmeta.meta_value";
			//$query = "SELECT DISTINCT posts.ID, posts.post_title, posts.post_excerpt, posts.post_content, posts.post_modified FROM ".$wpdb->prefix."posts as posts, ".$wpdb->prefix."postmeta as postmeta WHERE posts.ID=postmeta.post_id AND posts.post_mime_type LIKE '%image%' AND postmeta.meta_key='photo_credit' ORDER BY postmeta.meta_value $direction";
			$query = "SELECT DISTINCT posts.ID, posts.post_title, posts.post_excerpt, posts.post_content, posts.post_modified, post_mime_type FROM ".$wpdb->prefix."posts as posts LEFT JOIN ".$wpdb->prefix."postmeta as postmeta ON (posts.ID=postmeta.post_id AND postmeta.meta_key='photo_credit') WHERE posts.post_mime_type LIKE '%image%' AND posts.post_type='attachment' ORDER BY postmeta.meta_value $direction";
			//echo $query;
		}
		//$query .= ' LIMIT 20';
		if($search && !$_POST['see_all']){
			//echo "S!";

			$q['orderby']=$orderby;
			$q['order']=$direction;
			list($post_mime_types, $avail_post_mime_types) = wp_edit_attachments_query($q);
			if( is_array($GLOBALS['wp_the_query']->posts) ) {
				foreach ( $GLOBALS['wp_the_query']->posts as $attachment )
					$posts[$attachment->ID] = $attachment;
				//print_r($posts);
			}else{
				$msg .= "No results found, try again.";

			}
			$query = "SELECT DISTINCT posts.ID, posts.post_title, posts.post_excerpt, posts.post_content, posts.post_modified, post_mime_type FROM ".$wpdb->prefix."posts as posts LEFT JOIN ".$wpdb->prefix."postmeta as postmeta ON (posts.ID=postmeta.post_id AND postmeta.meta_key='photo_credit') WHERE posts.post_mime_type LIKE '%image%' AND posts.post_type='attachment' AND postmeta.meta_value LIKE \"%".$search."%\" ORDER BY postmeta.meta_value $direction";
			$rows = $wpdb->get_results($query);
			if(is_array($rows)){
				//$posts = array_merge($posts, $rows);
				foreach ( $rows as $attachment )
					$posts[$attachment->ID] = $attachment;
			}
			if(sizeof($posts)==0){
				$msg .= "No results found for \"$search\".\n<br />";
			}
			//return;
			//	
		}else{
			$posts = $wpdb->get_results($query);
		}

		$search = $search ? "&s=$search" : '';
		if(!$msg){
			$end = (($start + $maxposts) > sizeof($posts)) ? sizeof($posts) : ($start+$maxposts);
			$pstart = (($start - $maxposts) >=0 ) ? ($start-$maxposts) : 0;
			$nstart = (($start + $maxposts) > sizeof($posts)) ? sizeof($posts) : ($start+$maxposts);
			echo "<div style='position:relative;text-align: left'>&nbsp;";
			if($start > 0){
				echo "<a href=\"?type=slideshow_image&post_id=$pid&order_by=$orderby&direction=$direction&porder_by=$orderby&start=$pstart".$search."$img_id_var\"  > << </a>";
			}

			echo "<span style='position: absolute;top:0;right:0;'>";
			echo "Showing " . ($start+1) . "-$end of " . sizeof($posts);
			if($nstart < sizeof($posts)){
				echo " <a href=\"?type=slideshow_image&post_id=$pid&order_by=$orderby&direction=$direction&porder_by=$orderby&start=$nstart".$search."$img_id_var\"  > >> </a></span>";
			}
			echo "</div>";


			$direction = ($direction == 'asc') ? 'desc' : 'asc';
			echo '<form method="POST" name="edit_form" action="?type=image&flash=0" >';

			echo "<input type='hidden' id='post_id' name='post_id' value=''>";
			echo "<table>";
			echo $this->utils->table_headers(array(" ", 'Select'),
					array("class='title_column'","<a href=\"?type=slideshow_image&post_id=$pid&order_by=title&direction=$direction&porder_by=$orderby&start=$start".$search."$img_id_var\" >Title</a>"),
					array("class='credit_column'","<a href=\"?type=slideshow_image&post_id=$pid&order_by=photo_credit&direction=$direction&porder_by=$orderby&start=$start".$search."$img_id_var\" >Photo Credit</a>"), 
					array("class='date_column'","<a href=\"?type=slideshow_image&post_id=$pid&order_by=date&direction=$direction&porder_by=$orderby&start=$start".$search."$img_id_var\" >Upload Date</a>"),
					'');
			//echo "</form>\n";
			$count = 0;

			foreach($posts as $img_post){
				if($count >= $end)break;
				if($count < $start){ 
					++$count;
					continue;
				}
				if(!preg_match("/image/",$img_post->post_mime_type))continue;
				
				$id = $img_post->ID;
				$titles = array($img_post->post_title);
				$this->getExtraTitles($id, $titles);

				$alt =htmlentities( strip_tags($img_post->post_excerpt), ENT_QUOTES);
				$caption =  htmlentities( strip_tags($img_post->post_content), ENT_QUOTES);
				$photo_date = $img_post->post_modified;
				$thumb = wp_get_attachment_image_src( $id, 'thumbnail');

				$photo_props = $this->getPhotoFixedProps($id);

				$thumb = $photo_props['url'];

				$photo_credit =  htmlentities(stripslashes($photo_props['photo_credit']), ENT_QUOTES);

				//$items['title'] = $titles[0];
				$items['alt'] = $alt;
				$items['caption'] = $caption;

				$items['url'] = $thumb;
				$items['photo_credit'] = $photo_credit;
				$items['geo_location'] = $photo_props['geo_location'];
				$items['original_url'] = $photo_props['original_url'];
				$titles = array_unique($titles);
				$titles = $this->utils->convertquotes($titles);
				if($img_id){
					$select_value = " <img onmouseover=\"this.style.border='3px solid red';\" onmouseout=\"this.style.border='none';\" onclick=\"wpss_replace_photo($img_id,$id,'".$items['url']."');\" style='margin:0px;' title=\"$alt\" alt=\"$alt\" src=\"$thumb\" /><p class=\"caption\">$caption</p>";
				}else{
					$select_value = " <img onmouseover=\"this.style.border='3px solid red';\" onmouseout=\"this.style.border='none';\" onclick=\"wpss_send_and_return('$id','".$this->photoItemHTML_simple($id, $items, true)."');\" style='margin:0px;' title=\"$alt\" alt=\"$alt\" src=\"$thumb\" /><p class=\"caption\">$caption</p>";
				}
				$titleCol = sizeof($titles)>1 ? $this->utils->makeSelectBox("${id}[title]",$titles) : "<input type='hidden' id='${id}[title]' value='$titles[0]'/>". $this->utils->shorten_name($titles[0], 10);
				echo $this->utils->table_data_fill(array("class='select_column'",$select_value),array("class='title_column'",$titleCol),
						array("class='credit_column'","$photo_credit"),
						array("class='date_column'",date("Y-m-d",strtotime($photo_date))),
						'<a href="?type=slideshow_image&post_id='.$id . $img_id_var.'" class="button" >Edit</a>'  );//
				//echo "<h3 style='margin:0px;padding:0px;'>$title</h3><br />$caption<br /><br />";
				++$count;
			}
			echo "</table>";
			echo "</form>";
		}

		echo "<div >$msg</div>";

	}


	function media_field($name, $label, $id, $size='100%', $required=false){

		$string = "<tr>";
		$string .= "<th class='label' valign='top' scope='row'>
<label for='attachments[$id][$name]'>
<span class='alignleft'>$label</span>
<span class='alignright'>";
		$string .= $required ? "<abbr class='required' title='required'>*</abbr>" : '';
		$string .="</span>
<br class='clear'/>
</label>
</th>";
		$class = $required ? "class='".$this->plugin_prefix."required'" : '';
		$string .= "<td class='field'>";
		if(isset($_REQUEST['img_id']) && $_REQUEST['img_id']){
			$oldphoto = $this->getPhoto($_REQUEST['img_id']);
			$val = htmlentities(stripslashes(get_post_meta($oldphoto['wp_photo_id'], $name, true)), ENT_QUOTES);
		}else{
			$val = htmlentities(stripslashes(get_post_meta($id, $name, true)), ENT_QUOTES);
		}
		if($name=='geo_location'){
			$location = $val;
						
			$lat = get_post_meta($id, 'latitude', true);
			$lat = $lat ? $lat : '0';
			$long = get_post_meta($id, 'longitude', true);
			$long = $long ? $long : '0';

			$string .= "<input id='attachments[$id][$name]' type='text' style='width:$size' value='".$location."' id='attachments[$id][$name]' name='attachments[$id][$name]' onkeyup=\"get_google_coordinates(this.value,'attachments[$id][latitude]','attachments[$id][longitude]');\" onblur=\"get_google_coordinates(this.value,'attachments[$id][latitude]','attachments[$id][longitude]');\" />";
			$string .= "<input id='attachments[$id][latitude]' type='hidden' style='width:$size' value='".$lat."' name='attachments[$id][latitude]'/>";
			$string .= "<input id='attachments[$id][longitude]' type='hidden' style='width:$size' value='".$long."' name='attachments[$id][longitude]'/>";
			//$location = $location ? $location : 'N/A';
			$string .= " <span class='button' onClick=\"get_google_coordinates(document.getElementById('attachments[$id][geo_location]').value,'attachments[$id][latitude]','attachments[$id][longitude]'); loadMap($id, document.getElementById('attachments[$id][geo_location]').value, document.getElementById('attachments[$id][latitude]').value, document.getElementById('attachments[$id][longitude]').value);jQuery('#hide_button_$id').show();\" >Show in map</span>
				<span id='hide_button_$id' style='display:none' class='button' onClick=\"document.getElementById('map_canvas_$id').style.display = 'none';this.style.display='none';\" >Hide</span>";
			$string .= "<div id=\"map_canvas_$id\" style=\"width: 300px; height: 200px;display:none;\"></div>";

		}else{
			$string .= "<input id='attachments[$id][$name]' $class type='text' style='width:$size' value='".$val."' name='attachments[$id][$name]'/>";
		}
/*		$string .= "<input type='text' id='location' name='location' onkeyup=\"javascript:mapper_ajax_getCoords(this.value,'latitude','longitude');" value="<?php echo $this->location ? stripslashes($this->location) : ''; ?>" size='25' />";*/
		$string .= "</td></tr>";

		return $string;
	}
	
	public function mediaItem($string, $post){
		global $post_mime_types;

		if(preg_match("/image/i",$post->post_mime_type)){
			$id=$post->ID;
			$thumb_url = wp_get_attachment_image_src( $post->ID, 'thumbnail');

			$thumb_url = $thumb_url[0];
			$thumb_url = $this->process_thumb_url($thumb_url, $post->ID);
			//$items = get_attachment_fields_to_edit($post, $errors);
			//$fields = get_attachment_fields_to_edit($post, $errors);
//	public $photo_props = Array('id','title', 'caption', 'geo_location', 'photo_credit','description','url');
			/*$items['title'] = $fields['post_title']['value'];
			$items['caption'] = $fields['post_content']['value'];
			$items['alt'] = $fields['post_excerpt']['value'];
*/			$items['url'] = $thumb_url;
			$img_id = $_REQUEST['img_id'];
			//$string .= $img_id ? "img id: $img_id<br />":'';
			$string .= "<br /><br />";
			$string .="<span id='insert_photo_span_$id' class='alignleft'  style='display:none'><button class='button' onclick=\"currentMediaItem=$id;if(check_required('wpss_required', false)){";
			if($img_id){
				$string .= "wpss_replace_photo_javascript($img_id,$id,'".$items['url']."');";
				$string .= "}\">Replace photo";
			}else{
				$string .= "wpss_send_and_return('$id','".$this->photoItemHTML_javascript($id, $items)."');";
				$string .= "}\">Insert photo";
			}
			$string .= "</button></span>";
			$string .= "
<script type=\"text/javascript\">
	var currentMediaItem = -1;
  jQuery(document).ready(function(){

//	jQuery(\"input[name='send[$id]']\").hide();";
			//if(!current_user_can('edit_plugins')){
				$string .= "
		jQuery(\"#media-item-$id\").find(\".url\").hide();
		jQuery(\"#media-item-$id\").find(\".align\").hide();
		jQuery(\"#media-item-$id\").find(\".image-size\").hide();
		//jQuery(\"#media-item-$id\").find(\".submit\").hide();";
			//}
			$string .= "jQuery(\"#media-item-$id\").find(\".submit\").find(\"td.savesend\").find(\"input:first\").hide();
	jQuery(\"#media-item-$id\").find(\".submit\").find(\"td.savesend\").prepend(jQuery(\"#insert_photo_span_$id\").html());



	jQuery(\"label[for='attachments[$id][post_excerpt]']\").find(\"span:first\").html('Alt Text');
	jQuery(\"#attachments\\\\[$id\\\\]\\\\[post_excerpt\\\\]\").next().hide();


	jQuery(\"label[for='attachments[$id][post_content]']\").find(\"span:first\").html('Caption');
	jQuery(\"label[for='attachments[$id][post_content]']\").find(\"span:first\").after(\"<span class='alignright'><abbr class='required' title='required'>*</abbr></span>\");
	jQuery(\"#attachments\\\\[$id\\\\]\\\\[post_content\\\\]\").addClass('".$this->plugin_prefix."required');
	jQuery(\"#attachments\\\\[$id\\\\]\\\\[post_title\\\\]\").addClass('".$this->plugin_prefix."required');";
	if($img_id){
		$oldphoto = $this->getPhoto($img_id);
		$string .= "jQuery('#attachments\\\\[$id\\\\]\\\\[post_title\\\\]').val(\"".$this->utils->convertquotes($oldphoto['title'])."\");";
		$string .= "jQuery('#attachments\\\\[$id\\\\]\\\\[post_excerpt\\\\]').val(\"".$this->utils->convertquotes($oldphoto['alt'])."\");";
		$string .= "jQuery('#attachments\\\\[$id\\\\]\\\\[post_content\\\\]').val(\"".$this->utils->convertquotes($oldphoto['caption'])."\");";

	}
	$string .= "  });
</script>";
			$string .= "</td></tr>";
			$string .= $this->media_field('original_url', 'Original URL', $id, '100%', false);
			$string .= $this->media_field('photo_credit', 'Photo credit', $id, '100%', true);
			$string .= $this->media_field('geo_location', 'Geo location', $id, '180px');


			//$string .= $this->media_field('latitude', 'Latitude', $id, '45px');
			//$string .= $this->media_field('longitude', 'Longitude', $id, '45px');
			$string .= "<tr><td>";

			//<input class='button' type='submit' value='Insert into Slideshow' name='send[$id]'/>
			//$form_fields = get_attachment_fields_to_edit($post, $errors);
			//foreach($form_fields as $id=>$val){
			//	$string.= "$id => $val , ";
			//}

			//$string .= $thumb_url;

			//$string .= $this->makeField($postId, 'Photo credit', 'photo_credit', true);
			//$string .= $this->makeField($postId, 'Purchase URL', 'purchase_url', true);


		}

		return $string;
	}

	function insert_slideshow($slideshow, $post_id, $sid=''){//if $sid is passsed, we know we are updating
		global $wpdb;
		$table = $wpdb->prefix.$this->plugin_prefix."slideshows";
		$query = '';
		if($sid){
			$query = "UPDATE $table SET ";
			foreach($slideshow as $key=>$val){
				$query .= "$key=$val,";
			}
			$query = substr($query, 0, -1);
			$query .= " WHERE ID=$sid;";
		}else{
			$query = "INSERT INTO $table (".implode(",",array_keys($slideshow)).") VALUES(".implode(",",array_values($slideshow)).");";
		}
		//echo $query;

		$result = $wpdb->query($query);
		if($result===false){
			$wpdb->print_error();
			return false;
		}else if($sid){
			return $sid;
		}

		$slideshow_id = $wpdb->get_var('SELECT LAST_INSERT_ID();');
		if($post_id && $post_id > -1){
			add_post_meta($post_id, $this->fieldname, $slideshow_id, false);
		}

		return $slideshow_id;
	}

	function save_photo_fixed_props_deprecated($post, $attachment){
		$wpss = $_POST['wpss'];
		foreach($wpss as $pid => $post_item){
			foreach ($post_item as $pName => $pVal){
				if($pVal){
					add_post_meta($pid, $pName, $pVal, true) or update_post_meta($pid, $pName, $pVal);//unique
				}
			}
		}
		return $post;
	}

	function save_photo_fixed_props($post, $attachment){
		foreach($this->fixed_photo_props as $prop){
			if($attachment[$prop]){
				add_post_meta($post['ID'], $prop, $attachment[$prop], true) or update_post_meta($post['ID'], $prop, $attachment[$prop]);//unique
			}
		}
		return $post;
	}

	function delete_slideshow($sid){
		global $wpdb;
		if(!$sid){
			return;
		}
		$msg ='';

		$photos = $wpdb->get_col("SELECT photo_id FROM $this->t_spr WHERE slideshow_id=$sid;");
		if(is_array($photos)){
			//print_r($photos);
			foreach($photos as $photo){
				$msg .= $this->delete_photo($photo, $sid);
			}
			if(!$msg){
				$result = $wpdb->query("DELETE FROM $this->t_s WHERE ID=$sid;");
				$msg .= ($result!==false) ? '' : "<p>Could not delete slideshow $sid</p>";
			}

		}else{
			$msg.="<p>Slideshow deleted, but no photos where deleted</p>";
		}

		return $msg;

	}

	function delete_photo($photo_id, $sid=''){
		global $wpdb;
		if(!$photo_id){
			return;
		}
		$msg ='';

		$result = $wpdb->query("DELETE FROM $this->t_pmr WHERE photo_id=$photo_id;");
		$msg .= ($result!==false) ? '' : "<p>Could not delete photo meta for photo $photo_id</p>";

		if($sid){
			$result = $wpdb->query("DELETE FROM $this->t_spr WHERE photo_id=$photo_id AND slideshow_id=$sid;");
			$msg .= ($result!==false) ? '' : "<p>Could not delete slideshow relationship between slideshow $sid and photo $photo_id</p>";
		}
		$result = $wpdb->query("DELETE FROM $this->t_p WHERE photo_id=$photo_id;");
		$msg .= ($result!==false) ? '' : "<p>Could not delete photo $photo_id</p>";
		return $msg;
	}

	function insert_photo_meta($internal_photo_id, $form_photo_id, $photo){
		global $wpdb;
		$sptable = $wpdb->prefix.$this->plugin_prefix."photos";
		$pmtable = $wpdb->prefix.$this->plugin_prefix."photo_meta";
		$pmrtable = $wpdb->prefix.$this->plugin_prefix."photo_meta_relations";
		$msg = '';
		if(!$photo['alt']){
			$photo['alt'] = $photo['title'];
		}
		foreach($photo as $pProp => $pPropVal){//INSERT PHOTO META
			if(!$pPropVal){
				continue;
			}

			if(in_array($pProp, $this->fixed_photo_props) || !$pPropVal){
				/*if(get_post_meta($pid, $pProp) != $pPropVal){
					update_post_meta($pid, $pProp, $pPropVal);//unique
				}*/
				continue;
			}


			$mid = $wpdb->get_var("SELECT meta_id FROM $pmtable WHERE meta_name='$pProp';");
			if(!$mid){
				$result = $wpdb->query("INSERT INTO $pmtable (meta_name) VALUES (\"$pProp\");");
				$msg .= ($result!==false) ? '' : "<p>Could not insert new photo meta name into photo meta table</p>";
				if($result===false)break;
				$mid = $wpdb->get_var('SELECT LAST_INSERT_ID();');
			}
			//echo"$mid***<br />";
			//if($photo['update']){
			//	echo "updating photo $pid<br />";
			$query= "INSERT INTO $pmrtable VALUES($internal_photo_id, $mid, '".addslashes($pPropVal)."');";
			$result = $wpdb->query($query);
			if($result===false){//duplicate primary key (photo_id, meta_id), so try to update
				$query = "UPDATE $pmrtable SET meta_value=\"$pPropVal\" WHERE photo_id=$internal_photo_id AND meta_id=$mid;";
				$result = $wpdb->query($query);
			}
			//echo $query;
			$msg .= ($result!==false) ? '' : "<p>Could not link photo $form_photo_id with meta $mid</p>";

			if($result===false)break;
		}

		return $msg;
	}

	function insert_photos($sid, $photos, $photo_only = false, $post_id=''){
		global $wpdb;
		//echo $photo_only ? 'photo only' : 'no photo only';
		$sptable = $wpdb->prefix.$this->plugin_prefix."photos";
		$pmtable = $wpdb->prefix.$this->plugin_prefix."photo_meta";

		$sprtable = $wpdb->prefix.$this->plugin_prefix."slideshow_photo_relations";
		$pmrtable = $wpdb->prefix.$this->plugin_prefix."photo_meta_relations";
		$msg = '';

		if(!$sid && !$photo_only){
			return "<p>No slideshow id provided to link with photos</p>";
		}
		$old_order = $lost_photos = $wpdb->get_col("SELECT photo_id FROM ".$sprtable ." WHERE slideshow_id=".$sid .  " ORDER BY photo_id ASC");
		//print_r($old_order);
		$thumb_id = '';
		if(is_array($photos)){

			//$maxUpdates = sizeof($existing_photos);
			$count = 0;

			foreach($photos as $pid => $photo){
				
				$sphoto_id = $pid;
				if(!$photo['update']){
					$query = "INSERT INTO $sptable (wp_photo_id) VALUES($pid);";//INSERT PHOTO
					$result = $wpdb->query($query);
				
					$msg .= ($result!==false) ? '' : "<p>Could not insert photo into slideshow photos table</p>";
					if($result===false)break;
					$sphoto_id = $wpdb->get_var('SELECT LAST_INSERT_ID();');
					if(!$photo_only){
						$result = $wpdb->query("INSERT INTO $sprtable VALUES($sid, $sphoto_id, $count);");//LINK PHOTO TO SLIDESHOW
						$msg .= ($result!==false) ? '' : "<p>Could not link slideshow $sid with photo $sphoto_id</p>";
						if($result===false)break;
					}
				}else{
					//echo "$pid and result " . array_search($pid, $lost_photos). "<br />";
					//$oldindex = array_search($pid, $old_photos);
					//$old
					$result = $wpdb->query("INSERT INTO $sprtable VALUES($sid, $pid, $count);");//LINK PHOTO TO SLIDESHOW
					//$msg .= ($result!==false) ? '' : "<p>Could not link slideshow $sid with previously single photo $pid.</p>";
					//if($result===false)break;

					unset($photo['update']);
					if(!$photo_only){
						unset($lost_photos[array_search($pid, $lost_photos)]);
						$result = $wpdb->query("UPDATE $sprtable SET photo_order=$count WHERE photo_id=$pid AND slideshow_id=$sid;");//LINK PHOTO TO SLIDESHOW
						$msg .= ($result!==false) ? '' : "<p>Could not update photo order for photo $pid</p>";
						if($result===false)break;
					}
				}
				$this->photo_id_translation[$pid] = $sphoto_id;
				if($photo['cover'] && $sid){
					$result = $wpdb->query("UPDATE ".$this->t_s." SET thumb_id=$sphoto_id WHERE ID=$sid;");//LINK PHOTO TO SLIDESHOW
					$msg .= ($result!==false) ? '' : "<p>Could not set thumb id $sphoto_id for slideshow $sid</p>";
					if($result===false)break;
					$thumb_id = $pid;
				}else if($count==0 && $sid && !$thumb_id){
					$result = $wpdb->query("UPDATE ".$this->t_s." SET thumb_id=$sphoto_id WHERE ID=$sid;");//LINK PHOTO TO SLIDESHOW
					$msg .= ($result!==false) ? '' : "<p>Could not set default thumb id $sphoto_id for slideshow $sid</p>";
					if($result===false)break;
				}

				if($photo['cover']){unset($photo['cover']);}
				//else{
				//	$sphoto_id = $wpdb->get_var('SELECT wp_photo_id FROM '.$sptable ." WHERE photo_id=$pid;");
				$msg .= $this->insert_photo_meta($sphoto_id, $pid, $photo);
				if(!$msg && $photo_only){
					$prev_photo_id = get_post_meta($post_id, $this->plugin_prefix."photo_id", true);
					if($prev_photo_id != $sphoto_id){
						$mst .= $this->delete_photo($prev_photo_id);
					}
					add_post_meta($post_id, $this->plugin_prefix."photo_id", $sphoto_id, true) or update_post_meta($post_id, $this->plugin_prefix."photo_id", $sphoto_id) ;
				}
				++$count;
			}
		}

		foreach($lost_photos as $photo_id){
			$msg .= $this->delete_photo($photo_id, $sid);
		}
		return $msg;
	}

	function save_slideshow($post_id, $post){
		if ( !wp_verify_nonce( $_POST[$this->plugin_prefix.'nonce'], plugin_basename(__FILE__) ) || ($post->post_type=='revision')) {
			return $post_id;
		}


		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ))
			    return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ))
			    return $post_id;
		}

		/*if( $post->post_type =='revision' || !$_POST[$this->plugin_prefix.'process']){
			return;
		}*/

		$slideshows = $_POST['slideshowItem'];
		if(sizeof($slideshows)==0){//delete post meta
			delete_post_meta($post_id, $this->fieldname);
			delete_post_meta($post_id, $this->plugin_prefix."photo_id");
			return;
		}
		if(!is_array($slideshows))return;
		$existing_slideshows = get_post_meta($post_id, $this->fieldname, false);
		

		$maxUpdates = sizeof($existing_slideshows);
		$count = 0;
		$photo_count = 0;
		$verbose = false;
		foreach($slideshows as $sid => $slideshow){

			//echo "<h3>".$stitle."</h3><h4>ID: ".$sid."</h4>";
			$valid= true;
			$errormsg ='';
			$photos= array();
			foreach($slideshow as $sprop => $spropVal){
				if( in_array($sprop, array('title')) && !$spropVal ){//if not title, invalid
					$valid = false;
					break;
				}

				echo $verbose ? "$sprop: " : '';
				if(is_array($spropVal)){
					echo $verbose ? "\n<br />" : '';
					$photos = $spropVal;
					foreach($spropVal as $pid => $photo){
						foreach($photo as $pProp => $pPropVal){
							echo $verbose ? "<b> Photo $pid ($pProp)</b>: $pPropVal\n<br />" : '';
						}
					}
					unset($slideshow[$sprop]);
					//print_r($slideshow);
				}else{
					$slideshow[$sprop] = "\"$spropVal\"";
					echo $verbose ? "$spropVal" : '';
				}
				echo $verbose ? "\n<br />" : '';

			}
			if($valid === true){
				if($slideshow['update']){
					unset($slideshow['update']);
					//echo "sid: |$sid| <br />";
					$sid = $this->insert_slideshow($slideshow, $post_id, $sid);//update
					//echo "sid: |$sid| <br />";
				}else{
					$sid = $this->insert_slideshow($slideshow, $post_id);//insert
				}

				if ($sid !== false){
					$errormsg .= $this->insert_photos($sid, $photos);
					++$count;
				}else{
					$errormsg .= "<p>Slideshow could not be saved to the database...</p>";
				}	
			}else if(sizeof($slideshow['photos'])==1){//no title, so it either is a single photo or invalid slideshow
				$photos = $slideshow['photos'];
				$errormsg .= $this->insert_photos('', $photos, true, $post_id);
			}else{
				$errormsg .= "You did not fill all the required fields.";
			}



			if($errormsg){
				echo "<div class='updated fade' style='background-color: rgb(255, 251, 204);'>$errormsg</div>";
			}else{
				
				if($count >0){
					if($pid = get_post_meta($post_id,$this->plugin_prefix.'photo_id', true)){
						delete_post_meta($post_id, $this->plugin_prefix."photo_id");
						if( !$photos[$pid]){
							$this->delete_photo($pid);
						}else if(!$photos[$pid]['update']){
							$this->delete_photo($pid);
						}
					}
					
				}else if($ids = get_post_meta($post_id,$this->fieldname, false)){
					if(sizeof($ids)>0){
						delete_post_meta($post_id, $this->fieldname);
					}
				}
			}
			echo $verbose ? "<br /><br />" : '';
		}
		if(!$errormsg){
			if($sid_pid = $_POST['wpss_post_photo']){
				list($ignore, $image_id) = split('_', $sid_pid);
				add_post_meta($post_id, $this->postmeta_post_image, $this->photo_id_translation[$image_id], true) or update_post_meta($post_id, $this->postmeta_post_image, $this->photo_id_translation[$image_id]);
			}else{
				delete_post_meta($post_id, $this->postmeta_post_image);
			}
		}
				//die();
		//$myErrors = new my_class();
		//echo $myErrors->get_error('my_weird_error');


	}
	function delete_photos($post_id){
		/*if($slideshows=get_post_meta($post_id,$this->fieldname, false)){
			delete_post_meta($post_id, $this->fieldname);
		}else*/ if($pid=get_post_meta($post_id,$this->plugin_prefix.'photo_id', true)){
			$this->delete_photo($pid);
		}
	}

	function send_to_editor_filter($html='', $send_id='', $attachment=''){
	?>
<script type="text/javascript">
/* <![CDATA[ */
var win = window.dialogArguments || opener || parent || top;
win.send_to_editor('<?php echo addslashes($html); ?>');
/* ]]> */
</script>
	<?php
	exit;
	}

	function get_slideshow_clip($sid, $stylesheet='wpss_simple.xsl'){
		$str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$str .= '<?xml-stylesheet type="text/xsl" href="'.$this->plugin_url().'/stylesheets/'.$stylesheet.'" version="1.0"?>' . "\n";

		$str .= "<slideshows>".$this->get_slideshow_xml($sid). "</slideshows>";
		//echo htmlentities($str);
		$xml = new DOMDocument;
		if(!$xml->loadXML($str)){

			return '';
		}

		$xsl = new DOMDocument;
		@$xsl->load(dirname(__FILE__).'/stylesheets/'.$stylesheet);

		// Configure the transformer
		$proc = new XSLTProcessor;
		@$proc->importStyleSheet($xsl); // attach the xsl rules
		$output = @$proc->transformToXML($xml);

		if($output){
			return $output;
		}else{
			return "error";
		}

	}


	function get_photo_clip($pid, $stylesheet='wpss_simple.xsl'){
		$str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$str .= '<?xml-stylesheet type="text/xsl" href="'.$this->plugin_url().'/stylesheets/'.$stylesheet.'" version="1.0"?>' . "\n";

		$str .= $this->get_photo_xml($pid);
		//echo nl2br(htmlentities($str));
		$xml = new DOMDocument;
		if(!$xml->loadXML($str)){

			return '';
		}

		$xsl = new DOMDocument;
		@$xsl->load(dirname(__FILE__).'/stylesheets/'.$stylesheet);

		// Configure the transformer
		$proc = new XSLTProcessor;
		@$proc->importStyleSheet($xsl); // attach the xsl rules
		$output = @$proc->transformToXML($xml);

		if($output){
			return $output;
		}else{
			return "error";
		}

	}

	function replace_photo_tags($text, $probe=false){
		global $wpdb, $post;
		$post_id = $post->ID;
		if($sid=get_post_meta($post_id,$this->fieldname, true)){
			$slideshow_photo_rels = $wpdb->prefix.$this->plugin_prefix."slideshow_photo_relations";
			$query ="SELECT DISTINCT spr.photo_id FROM $slideshow_photo_rels as spr WHERE spr.slideshow_id=$sid ORDER BY spr.photo_order;";
			$ids = $wpdb->get_col($query);
		}else if($pid=get_post_meta($post_id,$this->plugin_prefix.'photo_id', true)){//photo only
			$ids = array($pid);
		}else{
			if($probe)return false;
			return $text;
		}
		
		for($h=0;$h<sizeof($ids);++$h){
			$index = $h+1;
			if(preg_match_all("/\[\s*photo\s*\-?\s*$index\s*([^\]\s]*)\s*\]/",$text, $matches)>0){
				if($probe){
					return true;
				}	
				for($i=0;$i<sizeof($matches[0]);++$i){
					$stylesheet = $matches[1][$i];
					if(!preg_match("/^.*\.xsl$/", $stylesheet)){
						$stylesheet = $stylesheet.".xsl";
					}
					if(!$stylesheet || !file_exists(dirname(__FILE__).'/stylesheets/'.$stylesheet)){
						$stylesheet = get_option($this->option_default_style_photo);
					}
					$out = $this->get_photo_clip($ids[$h],$stylesheet);
					$text = str_replace($matches[0][$i], $out, $text);
				}
			}
		}
		if($probe)return false;
		return $text;
	}

	function replace_slideshow_tags($text, $probe=false){
		global $wpdb, $post;
		$post_id = $post->ID;
		$sids=get_post_meta($post_id,$this->fieldname, false);
		if(!$sids){
			if($probe)return false;
			return $text;
		}
		sort($sids);
		for($h=0;$h<sizeof($sids);++$h){
			$index = $h+1;
			if(preg_match_all("/\[\s*slideshow\s*\-?\s*$index\s*([^\]\s]*)\s*\]/",$text, $matches)>0){
				if($probe){
					return true;
				}				
				for($i=0;$i<sizeof($matches[0]);++$i){
					$stylesheet = $matches[1][$i];
					if(!preg_match("/^.*\.xsl$/", $stylesheet)){
						$stylesheet = $stylesheet.".xsl";
					}
					if(!$stylesheet || !file_exists(dirname(__FILE__).'/stylesheets/'.$stylesheet)){
						$stylesheet = get_option($this->option_default_style_slideshow);
					}
					$out = $this->get_slideshow_clip($sids[$h],$stylesheet);
					$text = str_replace($matches[0][$i], $out, $text);
				}
			}
		}
		if($probe)return false;
		return $text;
	}

	function replace_tags($text){
		$text = $this->replace_photo_tags($text);
		$text = $this->replace_slideshow_tags($text);
		return $text;
	}

	function post_has_tags($text){
		return $this->replace_photo_tags($text, true) || $this->replace_slideshow_tags($text, true);
	}
}

//$test = new wordpress_slideshow('Recipe', 'recipe_text', 'recipe_content');
//$test->editForm();

 ?>
