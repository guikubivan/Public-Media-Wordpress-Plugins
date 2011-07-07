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
		//echo "<pre>".print_r($this->plugin, true)."</pre>";	
		$post_id = $this->plugin->_get['post_id'];

		if($post_id){
			
			$media_items = get_media_items($post_id, null);
			
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
		$parameters['media_upload_form'] = ob_get_contents();
		ob_end_clean();
		
		$parameters['path'] = $this->plugin->plugin_url;
		$parameters['img_id'] = $img_id = $this->plugin->_get['img_id'];
		$parameters['img_id_var'] = $img_id_var = $img_id ? "&img_id=$img_id" : '';
		$parameters['search'] = $search = $this->plugin->_post['s'] ? $this->plugin->_post['s'] : $this->plugin->_get['s']; 
		
		$maxposts = 10;
		$parameters['start'] = $start = $this->plugin->_get['start'] ? $this->plugin->_get['start'] : 0;

		if(!isset($this->plugin->_get['order_by']) && !isset($_REQUEST['s']) && !isset($this->plugin->_post['see_all'])){
			//return;
		}
		else
		{
		
		
		$msg = '';
		$parameters['orderby'] = $orderby = $this->plugin->_get['order_by'] ? $this->plugin->_get['order_by'] : 'title';
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
			$parameters['posts'] = $posts;
			}
			
			if(sizeof($posts)==0){
				$parameters['msg'] = $msg .= "No results found for \"$search\".\n<br />";
			}
				
		}
		else
		{
			$parameters['posts'] = $posts = $this->wpdb->get_results($query);
			
		}
		$parameters["search_field"] = $search;
		$parameters["search"] = $search = $search ? "&s=$search" : '';
		if(!$msg)
		{
			$parameters['end'] = $end = (($start + $maxposts) > sizeof($posts)) ? sizeof($posts) : ($start+$maxposts);
			$parameters['pstart'] = $pstart = (($start - $maxposts) >=0 ) ? ($start-$maxposts) : 0;
			$parameters['nstart'] = $nstart = (($start + $maxposts) > sizeof($posts)) ? sizeof($posts) : ($start+$maxposts);
			$parameters['nav_direction'] = $direction;
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
				$photo = new IPM_Photo($this->plugin, $id);
			//	$titles = array($img_post->post_title);
				
				
			//	$this->getExtraTitles($id, $titles);
			//	$titles = array_unique($titles);

			//	$titles = $this->utils->convertquotes($titles);

				$photo->get_extra_titles();
				$better_posts[$id] = $photo;
				
			//	$better_posts[$id]['select'] = $select_value;
			//	$better_posts[$id]['titleCol'] = $titleCol;
			//	$better_posts[$id]['photo_credit'] = $photo->photo_credit;
			//	$better_posts[$id]['photo_date'] = $photo->modified_date;
				
				++$count;
			//	print_r($photo);
			}

			$parameters['better_posts'] = $better_posts;
		//	echo "<pre>".print_r($parameters, true)."</pre>";	
		
		}
		
		}
		
		echo $this->plugin->render_backend_view("media_uploader_form.php", $parameters);


	}
	
}

?>