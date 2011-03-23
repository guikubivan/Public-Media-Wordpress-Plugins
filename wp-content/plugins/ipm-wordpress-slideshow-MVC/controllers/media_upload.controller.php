<?php

class WPSSMediaUpload
{
	
	public $plugin_prefix;
	private $plugin;
	private $this->wpdb;
	
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
		$post_id = $this->plugin->_get['post_id'];

		if($pid){
			
			$media_items = get_media_items($pid, null);
			
			$params = array("post_id" => 0, 
						"type"=>"image", 
						"form_action_url"=> admin_url("media-upload.php?type=$type&tab=type&post_id=0&flash=0"), 
						"callback" => "type_form_image");
			
			echo $this->plugin->render_backend_view("media_uploader_chooser.php", $params);
			return;
		}
		
		$parameters = array();
		
		$_REQUEST['flash'] = 0;
		
		ob_start();
		media_upload_form();
		$parameters['media_upload_form'] = ob_get_contents()
		ob_end_clean();
		
		$parameters['img_id'] = $img_id = $this->plugin->_get['img_id'];
		$parameters['img_id_var'] = $img_id_var = $img_id ? "&img_id=$img_id" : '';
		$parameters['search'] = $search = $this->plugin->_post['s'] ? $this->plugin->_post['s'] : $this->plugin->_get['s']; 
		
		$maxposts = 10;
		$start = $this->plugin->_get['start'] ? $this->plugin->_get['start'] : 0;

		if(!isset($this->plugin->_get['order_by']) && !isset($_REQUEST['s']) && !isset($this->plugin->_post['see_all'])){
			return;
		}
		
		
		$msg = '';
		$parameters['order_by'] = $orderby = $this->plugin->_get['order_by'] ? $this->plugin->_get['order_by'] : 'title';
		$parameters['direction'] = $direction = $this->plugin->_get['direction'] ? $this->plugin->_get['direction'] : 'asc';
		
		if($this->plugin->_get['order_by'] != $this->plugin->_get['porder_by']){
			$parameters['direction'] = $direction = 'asc';
		}

		$query = "SELECT 
					ID, 
					post_title, 
					post_excerpt, 
					post_content, 
					post_modified, 
					post_mime_type 
				FROM 
					".$this->wpdb->prefix."posts 
				WHERE 
					post_mime_type LIKE '%image%' 
					AND post_type='attachment'";
		if($orderby == 'title'){
			$query .= " ORDER BY post_title $direction";
		}else if($orderby ==  'date'){
			$query .= " ORDER BY post_modified $direction";
		}else if($orderby == 'photo_credit'){
			$query = "SELECT DISTINCT 
						posts.ID, 
						posts.post_title, 
						posts.post_excerpt, 
						posts.post_content, 
						posts.post_modified, 
						post_mime_type 
					FROM 
						".$this->wpdb->prefix."posts as posts 
						LEFT JOIN ".$this->wpdb->prefix."postmeta as postmeta 
							ON (posts.ID=postmeta.post_id AND postmeta.meta_key='photo_credit') 
					WHERE 
						posts.post_mime_type LIKE '%image%' 
						AND posts.post_type='attachment' 
					ORDER BY postmeta.meta_value $direction";
		}
		
		if($search && !$this->plugin->_post['see_all'])
		{
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
			$query = "SELECT DISTINCT 
						posts.ID, 
						posts.post_title, 
						posts.post_excerpt, 
						posts.post_content, 
						posts.post_modified, 
						post_mime_type 
					FROM 
						".$this->wpdb->prefix."posts as posts 
						LEFT JOIN ".$this->wpdb->prefix."postmeta as postmeta 
							ON (posts.ID=postmeta.post_id AND postmeta.meta_key='photo_credit') 
					WHERE 
						posts.post_mime_type LIKE '%image%' 
						AND posts.post_type='attachment' 
						AND (postmeta.meta_value LIKE \"%".$search."%\" $post_ids) 
						ORDER BY postmeta.meta_value $direction";

			$rows = $this->wpdb->get_results($query);

			if(is_array($rows)){
				foreach ( $rows as $attachment )
					$posts[$attachment->ID] = $attachment;
			}
			if(sizeof($posts)==0){
				$msg .= "No results found for \"$search\".\n<br />";
			}
				
		}
		else
		{
			$posts = $this->wpdb->get_results($query);
		}

		$parameters["search"] = $search = $search ? "&s=$search" : '';
		if(!$msg){
			$parameters['end'] = $end = (($start + $maxposts) > sizeof($posts)) ? sizeof($posts) : ($start+$maxposts);
			$parameters['pstart'] = $pstart = (($start - $maxposts) >=0 ) ? ($start-$maxposts) : 0;
			$parameters['nstart'] = $nstart = (($start + $maxposts) > sizeof($posts)) ? sizeof($posts) : ($start+$maxposts);
			
			$parameters['direction'] = $direction = ($direction == 'asc') ? 'desc' : 'asc';
			
			$count = 0;
			
			$better_posts = array();
			
			foreach($posts as $img_post)
			{
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
				
				$better_posts[$id]['select'] = $select_value;
				$better_posts[$id]['titleCol'] = $titleCol;
				$better_posts[$id]['photo_credit'] = $photo_credit;
				$better_posts[$id]['photo_date'] = $photo_date;
				
				
				++$count;
			}

			$parameters['better_posts'] = $better_posts;
			
			echo $this->plugin->render_backend_view("media_uploader_form.php", $parameters);

			
		}

	}
	
}

?>