<?php

class IPM_Photo
{
	public $photo_id = "";
	public $post_id = "";
	
	//public $wp_photo_id = ""; //this and post_id are the same thing
	public $title = "";
	public $alt = "";
	public $caption = "";
	public $geo_location = "";
	public $photo_credit = "";
	public $latitude = "";
	public $longitude = "";
	public $original_url = "";
	public $url = "";
	public $large_url = "";
	public $medium_url = "";
	public $thumb_url = "";
	public $update = "";
	
	
	//always send the wordpress_slideshow object, so we can have access to 
	//fun things like the $wpdb object and global variables and such.
	private $wpss;
	private $wpdb;
	
	public function __construct($wordpress_slideshow, $photo_id = "")
	{
		$this->wpss = $wordpress_slideshow;
		$this->wpdb = $wordpress_slideshow->wpdb;
		
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
		$post_id = $photo['wp_photo_id'];

		//NOTE:  $pid and $post_id are different

		$thumb = wp_get_attachment_image_src( $post_id, 'thumbnail');
		$photo['url'] = $thumb[0];
		$photo['url'] = $this->wpss->process_thumb_url($photo['url'], $post_id);
		$large = wp_get_attachment_image_src( $post_id, 'large');
		$photo['large_url'] =  $large[0] ? $large[0] : current(wp_get_attachment_image_src( $post_id, 'full'));
		$photo['large_url'] = $this->wpss->process_large_url($photo['large_url'], $post_id);
				
		$photo['thumb_url']=$this->wpss->process_thumb_url($photo['url'], $post_id);
		$photo['medium_url']=$this->wpss->process_medium_url(current(wp_get_attachment_image_src( $post_id, $image_size)), $post_id);
		$photo['large_url']=$this->wpss->process_large_url(current(wp_get_attachment_image_src( $post_id, 'large')), $post_id);
		$photo['url']=current(wp_get_attachment_image_src( $post_id, 'full'));
		
		
		$this->post_id = $this->post_id = $photo['wp_photo_id'];
		$this->title = $photo['title'];
		$this->alt = $photo['alt'];
		$this->caption = $photo['caption'];
		$this->geo_location = stripslashes(get_post_meta($this->post_id, "geo_location", true));
		$this->photo_credit = stripslashes(get_post_meta($this->post_id, "photo_credit", true));
		$this->latitude = stripslashes(get_post_meta($this->post_id, "latitude", true));
		$this->longitude = stripslashes(get_post_meta($this->post_id, "longitude", true));
		$this->original_url = stripslashes(get_post_meta($this->post_id, "original_url", true));
		$this->url = $photo['url'];
		$this->large_url = $photo['large_url'];
		$this->medium_url = $photo['medium_url'];
		$this->thumb_url = $photo['thumb_url'];
		$this->update = $photo['update'];
		
		//echo "<pre>".print_r($this, true)."</pre>";
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

	public function link_to_slideshow($slideshow, $order)
	{
		$relation_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."slideshow_photo_relations";
		
		$result = $this->wpdb->query("REPLACE INTO ".$relation_table." VALUES( '".$slideshow."', '".$this->photo_id."', '".$order."' );");//LINK PHOTO TO SLIDESHOW
		
		if($result === false)
			return false;
		else
			return true;
					
	}
		


	


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