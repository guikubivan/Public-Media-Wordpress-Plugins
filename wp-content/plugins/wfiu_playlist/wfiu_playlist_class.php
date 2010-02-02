<?php
class wfiuPlaylist_class {
	var $write_text = "Enter the playlist information.";
	var $file ='';
	var $id = -1;
	var $fetch_attachments = false;
	//var $justposted = false;
	/*function insert_content($content = ''){
		global $post, $wpdb;
		$mytable = $wpdb->prefix."wfiu_playlist";

		$query = "SELECT count(*) FROM ".$mytable ." WHERE post_id=".$post->ID . " ORDER BY playlist_item_id ASC";

		$playlist = $wpdb->get_results($query);
		$playlistLink = '';
		if(sizeof($playlist)>0){
			$playlistLink = "<a href='" . $this->wfiuPlaylist_url().'xml_playlist.php?blog='.$wpdb->prefix . "&post_id=" . $post->ID . "'>Playlist XML</a>";
		}
		
		return $content . $playlistLink;
	}*/

	function get_wfiu_playlist($xslfile){
		global $wpdb, $post;
		$post_id = $post->ID;
		//if(!$post_id){
		//	$post_id = get_the_id();
		//}
		//echo 'post id: ' . $post_id;

		$mytable = $wpdb->prefix."wfiu_playlist";
		$query = "SELECT count(post_id) FROM ".$mytable ." WHERE post_id=".$post_id . " ORDER BY playlist_item_id ASC";
		$playlist_count = $wpdb->get_var($query);
		if($playlist_count>0){

			$xml = new DOMDocument;
			//echo $this->wfiuPlaylist_url().'xml_playlist.php?blog='.$wpdb->prefix . "&post_id=" . $post_id;
			$xml_url = $this->wfiuPlaylist_url().'xml_playlist.php?blog='.$wpdb->prefix . "&post_id=" . $post_id;
			@$xml->load($xml_url);

			$xsl = new DOMDocument;
			@$xsl->load($this->wfiuPlaylist_url().'stylesheets/'.$xslfile);

			// Configure the transformer
			$proc = new XSLTProcessor;
			@$proc->importStyleSheet($xsl); // attach the xsl rules
			$output = @$proc->transformToXML($xml);

			if($output){
				echo @$proc->transformToXML($xml);
			}else{
				echo $xml_url;
			}
		}
	}

	function wfiuPlaylist_url(){
		$result = get_bloginfo('url').'/wp-content/plugins/wfiu_playlist/';
		return $result;
	}

	function cleanField($field){
		global $wpdb;

		//return $wpdb->escape($field);
		return $field;
/*
		$badChars = array('“', '”', '’', '‘', '’', "…", 'é');
		$repChars = array('"', '"', "'", "'", "'", "...", '&eacute;');
		return $wpdb->escape(str_replace($badChars, $repChars, $field, $count));
			//if($count>0)echo "alert('replaced one');";
*/
	}

	function post_box() {
		add_meta_box('wfiu_playlist-box-div', __('WFIU Playlist', 'wfiuplaylistbox'), array(&$this, 'post_form'), 'post', 'normal', 'high');
		add_meta_box('wfiu_playlist-box-div', __('WFIU Playlist', 'wfiuplaylistbox'), array(&$this, 'post_form'), 'page', 'normal', 'high');
	}


	function post_form($entryType = 'post') {
		global $post, $wpdb;
		//print_r(pathinfo($_SERVER[ 'REQUEST_URI' ] ));

		echo '<input type="hidden" name="wfiu_playlist_noncename" id="wfiu_playlist_noncename" value="' .wp_create_nonce( plugin_basename(__FILE__) ) .'" />';


		$urlparts = parse_url(get_settings('siteurl'));
		$imgFolder= "http://".$_SERVER['HTTP_HOST']. $urlparts['path'] . "/wp-admin/wp-content/plugins/wfiu_playlist/images/";

		//error_reporting(0);
		$table = $wpdb->prefix."wfiu_playlist";

		$existing_post = $wpdb->get_results("SELECT * FROM ".$table ." WHERE post_id=".$post->ID . 
" ORDER BY playlist_item_id ASC");

	/*	if($existing_post){
			echo '<div id="postWFIUPLAYLIST" class="postbox if-js-open">';
			//echo "post id" . $post->ID. "\n";
		}else{
			echo '<div id="postWFIUPLAYLIST" class="postbox if-js-closed">';
		}
*/
//			<h3>WFIU Playlist</h3>
			//<div class=\"inside\">
		echo "<div id=\"wfiuPlaylistContentPane\">";


		//print_r($existing_post);


		echo "</div><br/>
				<span class=\"button button-highlighted\" onClick=\"wfiuPlaylistAddItem('', '', '', '', '', '', '','', false);wfiuPlaylistDisplayItems();\" />Add playlist item</span><br/><br/>
<input type=\"submit\" name=\"save\" id=\"save-post\" value=\"Save\" class=\"button button-highlighted\" />";

		//echo "</div></div>";
		echo "<script type=\"text/javascript\">\n";	
		echo "var wfiuPlaylistImgFolder = '$imgFolder';";

		//echo "alert('hi');";
		foreach($existing_post as $playlistItem){
			$artist = $wpdb->escape($playlistItem->artist);
	
			$composer = $wpdb->escape($playlistItem->composer);
			//$title =  htmlentities($this->cleanField($playlistItem->title), ENT_QUOTES);
			$title =  $wpdb->escape($playlistItem->title);
			$album = $wpdb->escape($playlistItem->album);

			$label = $wpdb->escape($playlistItem->label);
			$asin = $wpdb->escape($playlistItem->asin);

			$notes = $wpdb->escape($playlistItem->notes);
			//$notes = str_replace(array('"',"'", "\r", "\n", "\0"), array('\"','\\\'','\r', '\n', '\0'), $notes);
			$notes = str_replace(array("\r", "\n", "\0"), array('\r', '\n', '\0'), $notes);
			$rel_year = $playlistItem->release_year;

			
			echo "wfiuPlaylistAddItem('$artist', '$composer', '$title', '$album', '$label', '$asin', '$notes', $rel_year, true);\n";

			//wfiuPlaylistAddItem(artist, composer, title, album, label, asin, notes, release_year)
		}
		echo "wfiuPlaylistDisplayItems();";
		echo '</script>';
	}

	function admin_head(){
		global $post, $wpdb;
		echo '<link rel="stylesheet" href="'.$this->wfiuPlaylist_url().'wfiu_playlist.css" type="text/css" />'."\n";
		wp_register_script( 'wfiu_playlist', $this->wfiuPlaylist_url().'wfiu_playlist.js', array());
		//wp_enqueue_script('jquery-ui-sortable');
		$pinfo = pathinfo($_SERVER[ 'REQUEST_URI' ] );
		//if( in_array($pinfo['filename'] , array('post','post-new')) ) {
		wp_enqueue_script( 'wfiu_playlist' );
		//}
	}

	function save_postdata($post_id, $post) {
		global $wpdb;

		if ( !wp_verify_nonce($_POST['wfiu_playlist_noncename'], plugin_basename(__FILE__) )) {
			return $post_id;
		}

		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ))
			    return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ))
			    return $post_id;
		}

		if(wp_is_post_revision($post_id)) { 
			return $post_id;
		}
		/*
		if($this->justposted) {
			return;
		}
		$this->justposted = true;
		*/
		//error_reporting(0);
/*
		if(sizeof($_POST['wfiuPlaylistItems'])==0){
			return $post_id;	
		}
*/

		$table = $wpdb->prefix."wfiu_playlist";
		$existing_post = $wpdb->get_col("SELECT playlist_item_id FROM ".$table ." WHERE post_id=".$post_id .  " ORDER BY playlist_item_id ASC");


		$maxUpdates = sizeof($existing_post);
		$count = 0;
		if(is_array($_POST['wfiuPlaylistItems'])){
			foreach($_POST['wfiuPlaylistItems'] as $val) {

				//$artist = 'sdlkfjsjdf';//$val['artist'];
				//$title = 'lskjfksdjf';//$val['title'];

				$artist = $this->cleanField($val['artist']);
				$composer = $this->cleanField($val['composer']);
				$title = $this->cleanField($val['title']);

				$album = $this->cleanField($val['album']);
				$label = $this->cleanField($val['label']);
				$asin = $this->cleanField($val['asin']);

				$notes = $this->cleanField($val['notes']);
				$rel_year = $val['release_year'];
				$showme = $val['showme'];
				if($artist && $title && $showme=='true' ){
					if( !preg_match('/^\d\d\d\d$/',$rel_year) && ($rel_year != '') && ($rel_year !=0) ){continue;}
					if($rel_year == '')$rel_year =0;
				
					if($count < $maxUpdates){
						$itemID = $existing_post[$count];
						$sqlI = "UPDATE " . $table . " SET artist=\"$artist\", composer=\"$composer\", title=\"$title\", album=\"$album\", label=\"$label\", release_year=$rel_year, asin=\"$asin\", notes=\"$notes\" WHERE playlist_item_id=$itemID;";
					}else{
						$sqlI = "INSERT INTO " . $table . " (post_id, artist, composer, title, album, label, release_year, asin, notes) VALUES ($post_id, \"$artist\", \"$composer\", \"$title\", \"$album\", \"$label\", $rel_year, \"$asin\", \"$notes\");";
					}

					if($wpdb->query($sqlI)===false ){
						echo "ERROR!\n";
						//return;
					}
					++$count;

				}
			}
		}
		if($count > 0){
			add_post_meta($post_id, 'wfiu_playlist_id', $post_id, true) or update_post_meta($post_id, 'wfiu_playlist_id', $post_id);
		}
		while($count < $maxUpdates) {
			$sqlD = "delete from " . $table . " where playlist_item_id=". $existing_post[$count] .";";
			$wpdb->query($sqlD);
			++$count;
		}

		$existing_post = $wpdb->get_col("SELECT playlist_item_id FROM ".$table ." WHERE post_id=".$post_id .  " ORDER BY playlist_item_id ASC");
		if(sizeof($existing_post) == 0 ){
			delete_post_meta($post_id, 'wfiu_playlist_id');
		}
		//return $mydata;
	}

	function delete_playlist($post_id){
		GLOBAL $wpdb;
		
		$thispost = get_post($post_id); 
		if( $thispost->post_type =='revision' ){
			return;
		}
		if(wp_is_post_revision($post_id)) { 
			return $post_id;
		}
		//$wpdb->show_errors();
		$table = $wpdb->prefix."wfiu_playlist";
		$sqlD = "delete from " . $table . " where post_id=". $post_id .";";
		$wpdb->query($sqlD);
		
		if( $wpdb->query($sqlD)===false ){
			$donuthing = '';
			//echo "Error: was unable to delete respective playlist from wfiu_playlist table:<br />\n";
			//echo "$sqlD \n<br />";
			//$wpdb->print_error();
		}else{
			delete_post_meta($post_id, 'wfiu_playlist_id'); 
		}
		//$wpdb->hide_errors(); 
	}

	function handle_upload() {
		$file = wp_import_handle_upload();
		//echo "wp_import_handle_upload result: ";
		//print_r ($file);
		if ( isset($file['error']) ) {
			echo '<p>'.__('Sorry, there has been an error.').'</p>';
			echo '<p><strong>' . $file['error'] . '</strong></p>';
			return false;
		}
		$this->file = $file['file'];
		$this->id = (int) $file['id'];
		return true;
	}

	function importPlaylistItem($playlistItem){
		GLOBAL $wpdb;
		
		echo "<br/>";
		print_r($playlistItem);

		$poststable = $wpdb->prefix."posts";
		$playlisttable = $wpdb->prefix."wfiu_playlist";
		//echo "before: ". $playlistItem['post_date']. "<br/>";
		//$playlistItem['post_date'] = strtotime($playlistItem['post_date'])+3600*4;
		//$playlistItem['post_date'] = date("Y-m-d H:i:s",$playlistItem['post_date']);
		//echo "after: ". $playlistItem['post_date']. "<br/>";
		$myquery = "SELECT ID FROM ".$poststable ." WHERE post_date=\"".$playlistItem['post_date'] . "\";";
		//echo $myquery . "<br/>";
		$existing_post = $wpdb->get_col($myquery);
		if(sizeof($existing_post)==0){
			preg_match("/(\d\d\d\d-\d\d-\d\d).*/",$playlistItem['post_date'],$matches);
			$playlistItem['post_date'] = $matches[1];
			$myquery = "SELECT ID FROM ".$poststable ." WHERE post_date like \"%".$playlistItem['post_date'] . "%\";";

			//echo $myquery . "<br/>";
			$existing_post = $wpdb->get_col($myquery);
			$specialIDs= array(805,818);
			if(sizeof($existing_post)>1){
				foreach($existing_post as $pid){
					if(in_array($pid,$specialIDs)){
						$existing_post=array($pid);
						break;
					}
				}
			}
		}
		if(sizeof($existing_post)>0){
			if(sizeof($existing_post) >1){
				echo "Too many matches returned.\n";
				print_r($existing_post);
			}else{

				if(!$playlistItem['release_year']){
					$playlistItem['release_year'] = 0;
				}
				$post_id = $existing_post[0];
				$sqlI = "INSERT INTO " . $playlisttable . " (post_id, artist, composer, title, album, label, release_year, asin, notes) VALUES ($post_id, '" . $playlistItem['artist'] . "', '" . $playlistItem['composer'] . "', '" . $playlistItem['title'] . "', '" . $playlistItem['album'] . "', '" . $playlistItem['label'] . "', " . $playlistItem['release_year'] . ", '" . $playlistItem['asin'] . "', \"" . $playlistItem['notes'] . "\")";
				if(!$wpdb->query($sqlI) ){
					echo "Error inserting playlist into table.\n";
					echo "Query:\n" . $sqlI;
				}
			}
		}else{
			echo "Error: no post found\n<br/>";
		}
		echo "<br/>";
	}
	function resetItemArray($post_date='', $post_title=''){
		$myItem = array('artist' => '' ,
				'composer' => '',
				'title' => '',
				'album' => '',
				'label' => '',
				'release_year' =>'',
				'asin' => '',
				'notes' => '',
				'post_date' => $post_date,
				'post_title' => $post_title
				);
		return $myItem;
	}

	function import_file($file){
		global $wpdb;
		$this->file = $file;
		$doing_entry = false;
		$doing_item = false;

		$fp = fopen($this->file, 'r');
		$playlistItem = array();
		if ($fp) {
			while ( !feof($fp) ) {
				$importline = rtrim(fgets($fp));
				//str_replace(array ('<![CDATA[', ']]>')
				//$wpdb->escape($string)
				echo "*";
				if ( preg_match("/\<post date\=\"(.*)\" \>/", $importline, $showDate) ) {
					//echo $showDate[1] . "\n";

					$playlistItem = $this->resetItemArray();
					$playlistItem['post_date'] = $showDate[1];
					$doing_entry = true;
					
				}

				if ( preg_match("/\<\/post\>/", $importline, $showDate) ) {
					$doing_entry = false;
				}

				if ( preg_match("/\<showtitle\>(.*)<\/showtitle\>/", $importline, $showTitle) ) {
					//echo $showTitle[1] . "\n";
					$playlistItem['post_title'] = $wpdb->escape($showTitle[1]);
				}

				if ( false !== strpos($importline, '<item>')) {
					$doing_item = true;
					$playlistItem = $this->resetItemArray($playlistItem['post_date'],$playlistItem['post_title']);
				}

				if ( false !== strpos($importline, '</item>')) {
					$doing_item = false;
					$this->importPlaylistItem($playlistItem);
				}

				//***********playlist item fields*******
				if ( preg_match("/\<title\>(.*)<\/title\>/", $importline, $title) ) {
					//echo $title[1] . "\n";
					$playlistItem['title'] = $wpdb->escape($title[1]);
				}

				if ( preg_match("/\<artist\>(.*)<\/artist\>/", $importline, $artist) ) {
					//echo $artist[1] . "\n";
					$playlistItem['artist'] = $wpdb->escape($artist[1]);
				}


				if ( preg_match("/\<composer\>(.*)<\/composer\>/", $importline, $composer) ) {
					//echo $composer[1] . "\n";
					$playlistItem['composer'] = $wpdb->escape($composer[1]);
				}


				if ( preg_match("/\<album\>(.*)<\/album\>/", $importline, $album) ) {
					//echo $album[1] . "\n";
					$playlistItem['album'] = $wpdb->escape($album[1]);
				}


				if ( preg_match("/\<label\>(.*)<\/label\>/", $importline, $label) ) {
					//echo $label[1] . "\n";
					$playlistItem['label'] = $wpdb->escape($label[1]);
				}

				if ( preg_match("/\<release_year\>(.*)<\/release_year\>/", $importline, $release_year) ) {
					//echo $release_year[1] . "\n";
					$release_year[1] = (int)$release_year[1];
					if( is_int($release_year[1]) ){
						$playlistItem['release_year'] = $release_year[1];
					}else{
						echo "|" . $release_year[1] . "| is not a valid integer...year omitted.\n";
					}
				}

				if ( preg_match("/\<asin\>(.*)<\/asin\>/", $importline, $asin) ) {
					//echo $asin[1] . "\n";
					$playlistItem['asin'] = $asin[1];
				}

				if ( false !== strpos($importline, '<notes>') ) {
					$start = strpos($importline, '<![CDATA[');
					$end = strpos($importline, ']]>');
					$importline = substr($importline, $start, $end-4);
					//echo $importline . "\n";
					$string = str_replace(array ('<![CDATA[', ']]>'), '',$importline);
					//$wpdb->escape($string)
					$playlistItem['notes'] = $wpdb->escape($string);
				}


				/*if ( false !== strpos($importline, '<wp:category>') ) {
					preg_match('<artist>(.*?)</artist>', $importline, $artist);
					$this->categories[] = $artist;
					continue;
				}

				if ( false !== strpos($importline, '<playslist>') ) {
					$this->post = '';
					$doing_entry = true;
					continue;
				}
				if ( false !== strpos($importline, '</playslist>') ) {
					$doing_entry = false;
					//process playlist
					continue;
				}*/

			}
		}

	}


	function import($id, $fetch_attachments = false) {
		$this->id = (int) $id;
		$this->fetch_attachments = (bool) $fetch_attachments;
		$file = get_attached_file($this->id);
		$this->import_file($file);
	}
	function importOnePlaylist(){
		$this->importOnePlaylist_basic($_POST['post_id'],$_POST['playlist_url']);
	}
	function importOnePlaylist_basic($post_id, $playlist_url){
		global $wpdb;

		require_once(ABSPATH.PLUGINDIR.'/wfiu_playlist/parser/parse.inc');
		$myparser = new playlist_parser($playlist_url);
		$playlist=$myparser->parse();
		$retString = '';
		if(sizeof($playlist)>0 && $playlist){
			$playlisttable = $wpdb->prefix."wfiu_playlist";
			//print_r($playlist);
			foreach($playlist as $playlistItem){
				$artist = $wpdb->escape($playlistItem['Name:']);
				$composer = '';//$playlistItem['Name of :'];
				$title = $wpdb->escape($playlistItem['Trackname:']);
				$album = $wpdb->escape($playlistItem['Name of CD:']);
				$label = $wpdb->escape($playlistItem['Track Label:']);
				$rel_year = $playlistItem['Release Year:'];
				$asin = '';
				$notes = $wpdb->escape($playlistItem['Notes:']);

				if($rel_year == '')$rel_year =0;

				$sqlI = "INSERT INTO " . $playlisttable . " (post_id, artist, composer, title, album, label, release_year, asin, notes) VALUES ($post_id, '$artist', '$composer', '$title', '$album', '$label', $rel_year, '$asin', '$notes');";
				//echo $sqlI;
				if(!$wpdb->query($sqlI) ){
					$retString .= "*";
				}else{
					$retString .= ".";
				}
	
			}
			$retString .= "done.";
			//break;
		}else{
			$retString .= "No playlist extracted (rerun?)";
			//return false;
		}
		$retString = "document.getElementById('wfiuplaylists').innerHTML += '". $retString . "';" ;
		//$retString = "alert('$retString')";
		// Compose JavaScript for return
		die($retString);
					/*echo "jQuery.get('$file', function(data){
						jQuery('#wfiuplaylists').append('$showtitle');
						jQuery('#wfiuplaylists').append(data);
						});";*/
	}
	function import_playlists(){//needs work when importing playlists from urls
					//-too slow, progress unclear, may overload server
		global $wpdb;

		//echo getcwd(). "\n";


		if ($_GET['process']){
			check_admin_referer('import-upload');
			if ( $this->handle_upload() ){
				echo "file uploaded successfully.\n";
				$this->import_file($this->file);
			}
		}else if ($_GET['playlists']){

			echo "<h2>Processing playlists</h2>";
			//return;
			echo "<div id='wfiuplaylists'></div>\n";
		
			$pmtable = $wpdb->prefix."postmeta";
			$poststable = $wpdb->prefix."posts";
			$pltable = $wpdb->prefix."wfiu_playlist";			
			//$query_p = "SELECT DISTINCT $poststable.ID FROM $poststable, $pltable WHERE $poststable.ID != $pltable.post_id";
			//$query= "SELECT $pmtable.post_id, $poststable.post_title, $pmtable.meta_value FROM $pmtable, $poststable WHERE $pmtable.post_id=$poststable.ID AND $pmtable.meta_key ='playlist_url' AND $pmtable.post_id IN ($query_p) ORDER BY post_id;";
			/*$query= "SELECT $pmtable.post_id, $poststable.post_title, $pmtable.meta_value 
				FROM $pmtable INNER JOIN $poststable ON $pmtable.post_id=$poststable.ID
				INNER JOIN $pltable ON $poststable.ID <> $pltable.post_id
				WHERE $pmtable.meta_key ='playlist_url' 
				ORDER BY post_id;";*/
			$query =  "SELECT $pmtable.post_id, $poststable.post_title, $pmtable.meta_value FROM $pmtable, $poststable WHERE $pmtable.post_id=$poststable.ID AND $pmtable.meta_key ='playlist_url' ORDER BY post_id;";

			$results = $wpdb->get_results($query);
			//echo "<b>Query: </b>$query\n<br/>";
			//print_r($results);
			if($results){
				//echo sizeof($results);
				//return;
				//set_time_limit(1000);
				
				$count = 1;
				echo "<script type=\"text/javascript\">\n";
				foreach($results as $item){
					$post_id = $item->post_id;
					$showtitle = $wpdb->escape($item->post_title);
					//$file ="admin.php?import=wfiuPlaylist&doPlaylist=1&post_id=$post_id&playlist_url=".$item->meta_value;
					$purl = $item->meta_value;
	//				echo $file;

					echo "	document.getElementById('wfiuplaylists').innerHTML += '<br/><b>$count. $showtitle</b>: ';";
					echo "	myplugin_ajax_elevation('$post_id','$purl');";
					//if($count >8)break;
					//break;
					++$count;
				}

				echo '</script>';
			}


		}else{
			echo "<h2>Import playlist for WFIU playlist plugin</h2>";
			wp_import_upload_form("admin.php?import=wfiuPlaylist&process=1");
			echo "<hr/>\n";
			//less used....
			echo "<h2>Import playlist for WFIU playlist plugin</h2>";
			echo "<form action='admin.php?import=wfiuPlaylist&playlists=1' method='post'><input class='button' type='submit' value='Import playlists'/></form>\n";
		}

	}

}
?>
