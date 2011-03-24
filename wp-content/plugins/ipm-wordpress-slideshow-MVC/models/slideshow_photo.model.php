<?php

class IPM_SlideshowPhoto extends IPM_Photo
{
	public $photo_id = "";
	
	public function __construct($wordpress_slideshow, $photo_id = "")
	{
		$this->wpss = $wordpress_slideshow;
		$this->wpdb = $wordpress_slideshow->wpdb;
		
		parent::__construct($this->wpss);
		
		if(!empty($photo_id))
		{
			$this->get_photo($photo_id);
		}
		
	}
	
	public function get_photo($photo_id = "")
	{
		if(!empty($photo_id))		
			$this->photo_id = $photo_id;
		
		$photo_meta_rels = $this->wpdb->prefix.$this->wpss->plugin_prefix."photo_meta_relations";
		$slideshow_photos = $this->wpdb->prefix.$this->wpss->plugin_prefix."photos";
		$photo_meta = $this->wpdb->prefix.$this->wpss->plugin_prefix."photo_meta";
		$pid = $this->photo_id;
		
		$query ="SELECT DISTINCT `wp_photo_id`, `meta_name`, `meta_value` 
					FROM  
						`".$slideshow_photos."` as `sp`,
						`".$photo_meta."` as `pm`, 
						`". $photo_meta_rels ."` as `pmr` 
					WHERE
						`sp`.`photo_id`=`pmr`.`photo_id` AND
						`pmr`.`meta_id`=`pm`.`meta_id` AND
						`sp`.`photo_id`='".$pid."' ";
		
		$result = $this->wpdb->get_results($query);
		$photo= array();
		foreach($result as $row){
			$photo['wp_photo_id']=$row->wp_photo_id;
			$photo[$row->meta_name]=$row->meta_value;
		}
		$this->post_id = $photo['wp_photo_id'];
		parent::get_photo();
		
		$this->title = stripslashes($photo['title']);
		$this->alt = stripslashes($photo['alt']);
		$this->caption = stripslashes($photo['caption']);
		
		return true;
	}


	public function get_photo_by_post_id($post_id = "")
	{
		$photo_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."photos";
			
		$query = "SELECT `photo_id` FROM `".$photo_table."` WHERE `wp_photo_id` = '".$this->post_id."';";
		$result = $this->wpdb->get_results($query);
		
		if($result===false)
			return false;
		else
		{
			echo $this->photo_id = $result['photo_id'];
			if(!empty($this->photo_id))
				$this->get_photo();
				
			return true;
		}
		
	}
	
	public function insert()
	{
		$photo_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."photos";
			
		$query = "INSERT INTO ".$photo_table." (wp_photo_id) VALUES (".$this->post_id.");";//INSERT PHOTO
		$result = $this->wpdb->query($query);
		
		if($result===false)
			return false;
		else
			$this->photo_id = $this->wpdb->get_var('SELECT LAST_INSERT_ID();');
			$this->get_photo($this->photo_id);
		return true;
	}

	public function update()
	{
		$photo_meta_rels = $this->wpdb->prefix.$this->wpss->plugin_prefix."photo_meta_relations";
		$query = "UPDATE `".$photo_meta_rels."`
					SET
						`meta_value` = '". addslashes($this->title) ."'
					WHERE
						`photo_id` = '".$this->photo_id."'
						AND `meta_id` = '1'
					";
					
		$success  = $this->wpdb->get_results($query);
		
		$query  = "UPDATE `".$photo_meta_rels."`
					SET
						`meta_value` = '". addslashes($this->alt) ."'
					WHERE
						`photo_id` = '".$this->photo_id."'
						AND `meta_id` = '2'
					";			
		
		if($success !== false)
			$success  = $this->wpdb->get_results($query);
		$query = "UPDATE `".$photo_meta_rels."`
					SET
						`meta_value` = '". addslashes($this->caption) ."'
					WHERE
						`photo_id` = '".$this->photo_id."'
						AND `meta_id` = '3'
					";				
		if($success !== false)
			$success  = $this->wpdb->get_results($query);
		
		
		if($success !== false)
			$success  = update_post_meta($this->post_id, "geo_location", addslashes($this->geo_location) );
		if($success !== false)
			$success  = update_post_meta($this->post_id, "photo_credit", addslashes($this->photo_credit) );
		if($success !== false)
			$success  = update_post_meta($this->post_id, "latitude", addslashes($this->latitude) );
		if($success !== false)
			$success  = update_post_meta($this->post_id, "longitude", addslashes($this->longitude) );
		if($success !== false)
			$success  = update_post_meta($this->post_id, "original_url", addslashes($this->original_url) );
		
		return $success;
		
		
	}

	public function add_to_slideshow($slideshow, $order)
	{
		$relation_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."slideshow_photo_relations";
		$result = $this->wpdb->query("REPLACE INTO ".$relation_table." VALUES( '".$slideshow."', '".$this->photo_id."', '".$order."' );");//LINK PHOTO TO SLIDESHOW
		if($result === false)
			return false;
		else
			return true;			
	}
	public function remove_from_slideshow($slideshow_id)
	{
		$relation_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."slideshow_photo_relations";
		$result = $this->wpdb->query("DELETE FROM ".$relation_table." WHERE `slideshow_id` = '".$slideshow_id."' AND `photo_id` = '".$this->photo_id."' ");//REMOVE FROM SLIDESHOW
		if($result === false)
			return false;
		else
			return true;			
	}



	/*private function process_thumb_url($url)
	{
		$id = $this->post_id;
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
		if(!preg_match("/\-${tw}x${th}\./", $url)){
			$base = basename($url);
			$dot = strrpos($base, '.');
			$name = substr($base, 0, $dot);
			$new_name = $name . "-${tw}x${th}";
			$new_url = str_replace($name, $new_name, $url);
			return $new_url;
		}
		return $url;	
	}

	private function process_large_url($url)
	{
		$id= $this->post_id;
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

	private function process_medium_url($url)
	{
		$id = $this->post_id;
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
*/
		


	


	//this gets stuck into the getPhoto method of the wpss class to return an array
	// that looks like what the function was originally outputting.  This is so that we can
	// use the new class and not break everything that depends on the old function.
	public function getPhoto_emulator()
	{
		$tmp = array(
			"wp_photo_id" => $this->post_id,
			"title" => $this->title,
			"alt" => $this->alt,
			"caption" => $this->caption,
			"geo_location" => $this->geo_location,
			"photo_credit" => $this->photo_credit,
			"latitude" => $this->latitude,
			"longitude" => $this->longitude,
			"original_url" => $this->original_url,
			"url" => $this->url,
			"large_url" => $this->large_url,
			"update" =>$this->update
		);
		
		return $tmp;
	}
	
	
}

?>