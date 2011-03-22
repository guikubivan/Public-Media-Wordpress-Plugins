<?php

class WPSSMediaUpload
{
	
	public $plugin_prefix;
	private $plugin;
	private $wpdb;
	
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->wpdb = $plugin->wpdb;
		$this->plugin_prefix = $plugin->plugin_prefix;
		
		
			
	}
	
	function initialize_uploader()
	{
		wp_enqueue_style( 'global' );
		wp_enqueue_style( 'wp-admin' );
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'media' );
		wp_iframe(array(&$this, 'simple_image_chooser_content'));
	}
	
	function wpss_media_upload_form(){

	}
	
	function simple_image_chooser_content(){
		$post_id = $_GET['post_id'];

		if($pid){
			
			$media_items = get_media_items($pid, null);
			
			$params = array("post_id" => 0, 
						"type"=>"image", 
						"form_action_url"=> admin_url("media-upload.php?type=$type&tab=type&post_id=0&flash=0"), 
						"callback" => "type_form_image");
			
			echo $this->plugin->render_backend_view("media_uploader_chooser.php", $params);
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

<form method='post' name='search_form' action='?type=slideshow_image<?php echo $img_id_var;?>' >


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
			$q['orderby']=$orderby;
			$q['order']=$direction;
			$q['s']=$search;
			$q['post_mime_type'] = 'image';
			$q['post_status'] = 'open';
			$wp_results = new WP_Query($q);
			//print_r($wp_results);
			//$search->posts
			//list($post_mime_types, $avail_post_mime_types) = wp_edit_attachments_query($q);
			if( is_array($wp_results->posts) && ( sizeof($wp_results->posts) > 0 ) ) {
				$post_ids_array = array();
				foreach ( $wp_results->posts as $attachment )
					$post_ids_array[] = $attachment->ID;
				$post_ids = "OR posts.ID IN (".implode(',', $post_ids_array).") ";
			}
			$query = "SELECT DISTINCT posts.ID, posts.post_title, posts.post_excerpt, posts.post_content, posts.post_modified, post_mime_type FROM ".$wpdb->prefix."posts as posts LEFT JOIN ".$wpdb->prefix."postmeta as postmeta ON (posts.ID=postmeta.post_id AND postmeta.meta_key='photo_credit') WHERE posts.post_mime_type LIKE '%image%' AND posts.post_type='attachment' AND (postmeta.meta_value LIKE \"%".$search."%\" $post_ids) ORDER BY postmeta.meta_value $direction";

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
	
}

?>