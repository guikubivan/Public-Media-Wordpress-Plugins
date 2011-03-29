<?php

/**
 * Wrapper for Word Press's attachement post types... 
 * Normalizes the attribute names and provides a few methods for dealing with extra metadata
 */

class IPM_Photo
{
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
	public $date_modified = "";
	public $mime_type = "";
	public $extra_titles = array();
	
	
	//always send the wordpress_slideshow object, so we can have access to 
	//fun things like the $wpdb object and global variables and such.
	private $wpss;
	private $wpdb;
	
	public function __construct($wordpress_slideshow, $post_id = "")
	{
		$this->wpss = $wordpress_slideshow;
		$this->wpdb = $wordpress_slideshow->wpdb;
		
		if(!empty($post_id))
		{
			$this->get_photo($post_id);
		}
		
	}
	
	public function get_photo($post_id = "")
	{
		if(!empty($post_id))		
			$this->post_id = $post_id;
		
		$query = "SELECT 
					`ID`, 
					`post_title`, 
					`post_excerpt`, 
					`post_content`, 
					`post_modified`, 
					`post_mime_type` 
				FROM 
					`".$this->wpdb->prefix."posts` 
				WHERE 
					`post_mime_type` LIKE '%image%' 
					AND `post_type`='attachment'
					AND `ID` = '".$this->post_id."'
					";
		$result = $this->wpdb->get_results($query);
		if( count($result) > 0 )
		{
			$result = $result[0];
			//echo "<pre>".print_r($result, true)."</pre>";
			
			$this->title = stripslashes($result->post_title);
			$this->alt = stripslashes($result->post_excerpt);
			$this->caption = stripslashes($result->post_content);
			$this->date_modified = stripslashes($result->post_modified);
			$this->mime_type = stripslashes($result->post_mime_type);
			
			$this->geo_location = stripslashes(get_post_meta($this->post_id, "geo_location", true));
			$this->photo_credit = stripslashes(get_post_meta($this->post_id, "photo_credit", true));
			$this->latitude = stripslashes(get_post_meta($this->post_id, "latitude", true));
			$this->longitude = stripslashes(get_post_meta($this->post_id, "longitude", true));
			$this->original_url = stripslashes(get_post_meta($this->post_id, "original_url", true));
			$this->update = $photo['update'];
			
			$post_id = $this->post_id;
			$photo['url'] = $thumb[0];
	
			$thumb = wp_get_attachment_image_src( $this->post_id, 'thumbnail');
			$photo['url'] = $this->process_thumb_url($photo['url']);
	
			$large = wp_get_attachment_image_src( $post_id, 'large');
			$photo['large_url'] =  $large[0] ? $large[0] : current(wp_get_attachment_image_src( $post_id, 'full'));
			$photo['large_url'] = $this->process_large_url($photo['large_url']);
					
			$photo['thumb_url']=$this->process_thumb_url(current(wp_get_attachment_image_src( $post_id, 'thumbnail')));
			$photo['medium_url']=$this->process_medium_url(current(wp_get_attachment_image_src( $post_id, $image_size)));
			$photo['large_url']=$this->process_large_url(current(wp_get_attachment_image_src( $post_id, 'large')));
			$photo['url']=current(wp_get_attachment_image_src( $post_id, 'full'));
			
			
			$this->url = $photo['url'];
			$this->large_url = $photo['large_url'];
			$this->medium_url = $photo['medium_url'];
			$this->thumb_url =  $photo['thumb_url'];
			
			
			//echo "<pre>".print_r($this, true)."</pre>";
			return true;
		}
		return false;
	}

	private function process_thumb_url($url)
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

	public function update()
	{
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
	
	//get additional slideshow-specific titles for the photo
	public function get_extra_titles()
	{
		$photo_meta_rel = $this->wpdb->prefix.$this->wpss->plugin_prefix."photo_meta_relations";
		$photo_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."photos";
		
		$query = "SELECT 
						`meta_value` 
					FROM 
						`".$photo_meta_rel."` `r`,
						`".$photo_table."` `p`
					WHERE
						`r`.`photo_id` = `p`.`photo_id`
						AND `p`.`wp_photo_id` = '".$this->post_id."'
						AND `r`.`meta_id` = '1'
					";
						
		$results=$this->wpdb->get_results($query);
		$this->extra_titles = array();
		foreach($results as $row)
			$this->extra_titles[] = $row->meta_value;
			
		return $this->extra_titles;
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