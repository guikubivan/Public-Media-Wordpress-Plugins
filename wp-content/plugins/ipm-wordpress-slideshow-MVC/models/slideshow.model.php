<?php

/*
 * This contains information specifically about a slideshow.
 * It maps directly to the wpss_slideshows table
 * 
 * Methods:
 * get_slideshow() - gets all the slideshow info
 * get_photos() - populates the $photos attribute with an array of IPM_SlideshowPhoto items
 * update()
 * insert()
 * attach_to_post() - attach the slideshow to a specific post... It's usually better to modify the
 * 		slideshows attribute, the remove_slideshow(), and save_slideshows() in the IPM_PostSlideshow class
 * set_thumbnail( IPM_SlideshowPhoto ) - set the thumbnail and update the slideshow
 * count_photos() - returns the number of photos in the slideshow
 * 
 * Attributes
 * slideshow_id
 * title
 * photo_credit
 * description
 * geo_location  
 * latitude
 * longitude
 * thumb_id
 * thumb - IPM_SlideshowPhoto object
 * photos - array of IPM_SlideshowPhoto objects
 * 
 * wpss - the main slideshow object
 * wpdb - wrapper for the wordpress database object
 * 
 * 
 */


class IPM_Slideshow
{
	public $slideshow_id;
	public $title;
	public $photo_credit;
	public $description;
	public $geo_location;
	public $latitude;
	public $longitude;
	public $thumb_id; //photo_id
	
	public $thumb; //IPM_Photo object;
	
	public $photos = array(); //array of IPM_Photo objects
	
	//always send the wordpress_slideshow object, so we can have access to 
	//fun things like the $wpdb object and global variables and such.
	private $wpss;
	private $wpdb;
	
	public function __construct($plugin, $slideshow_id = "")
	{
		$this->wpss = $plugin;
		$this->wpdb = $plugin->wpdb;
		
		if(!empty($slideshow_id))
		{
			$this->slideshow_id = $slideshow_id;
			$this->get_slideshow();
		}
		
	}
	
	public function get_slideshow($slideshow_id = "")
	{
		if(!empty($slideshow_id))
		{
			$this->slideshow_id = $slideshow_id;
		}
				
		$query = "SELECT DISTINCT * FROM `".$this->wpdb->prefix.$this->wpss->plugin_prefix."slideshows`
					WHERE `ID` = '".$this->slideshow_id."'";
					
		$result = $this->wpdb->get_row($query, ARRAY_A);
		
		$this->title = $result['title'];
		$this->photo_credit = $result['photo_credit'];
		$this->description = $result['description'];
		$this->geo_location = $result['geo_location'];
		$this->longitude = $result['longitude'];
		$this->latitude = $result['latitude'];
		$this->thumb_id = $result['thumb_id'];
		
		if(!empty($this->thumb_id) )
			$this->thumb = new IPM_SlideshowPhoto($this->wpss, $this->thumb_id);
		$this->get_photos();
		if(empty($this->thumb_id) && count($this->photos) > 0)
		{
			$this->thumb_id = $this->photos[0]->photo_id;
			$this->thumb = $this->photos[0];
		}
	}
	
	public function get_photos()
	{
		$query = "SELECT DISTINCT *  
			FROM `".$this->wpdb->prefix.$this->wpss->plugin_prefix."slideshow_photo_relations`
			WHERE `slideshow_id` = '".$this->slideshow_id."' 
			ORDER BY `photo_order` ";
		$result = $this->wpdb->get_results($query);
		//$this->photos = $result;
		$this->photos = array();
		foreach($result as $key=>$row){
			$this->photos[/*$row->photo_order*/] = new IPM_SlideshowPhoto($this->wpss, $row->photo_id);
		}
	}
	
	public function update()
	{		
		$query = "UPDATE `".$this->wpdb->prefix.$this->wpss->plugin_prefix."slideshows`
					SET
						`title` = '".$this->title."',
						`photo_credit` = '".$this->photo_credit."',
						`description` = '".$this->description."',
						`geo_location` = '".$this->geo_location."',
						`longitude` = '".$this->longitude."',
						`latitude` = '".$this->latitude."',
						`thumb_id` = '".$this->thumb_id."'
		
					WHERE `ID` = '".$this->slideshow_id."'
					";
					
		$result = $this->wpdb->query($query);
		return $result;
		
	}
	
	
	public function insert()
	{
		$table = $this->wpdb->prefix.$this->wpss->plugin_prefix."slideshows";
		$query = "INSERT INTO $table 
						SET
							`title` = '".$this->title."',
							`photo_credit` = '".$this->photo_credit."',
							`description` = '".$this->description."',
							`geo_location` = '".$this->geo_location."',
							`longitude` = '".$this->longitude."',
							`latitude` = '".$this->latitude."',
							`thumb_id` = '".$this->thumb_id."'
			";
		
		$result = $this->wpdb->query($query);
		if($result===false){
			$this->wpdb->print_error();
			return false;
		}
		//$this->slideshow_id = $this->wpdb->get_var('SELECT LAST_INSERT_ID();');
		$this->slideshow_id = $this->wpdb->insert_id;
		return $slideshow_id;
	}
	
	public function attach_to_post($post_id)
	{
		if(!empty($this->slideshow_id) )
		{	
			$current_slideshows = array();
			$tmp = get_post_meta($post_id , "slideshow_id", true);
			$success = update_post_meta($post_id, "slideshow_id", $this->slideshow_id);
			return $success;
		}
		else
		{
			return false;
		}
	}
	
	public function save_photo_order()
	{
		foreach($this->photos as $key => $photo)
		{
			$photo->order = $key;
			$query = "UPDATE `".$this->wpdb->prefix.$this->wpss->plugin_prefix."slideshow_photo_relations`
			SET `photo_order` = '".$key."'
			WHERE 
				`slideshow_id` = '".$this->slideshow_id."'
				AND `photo_id` = '".$photo->photo_id."'
			";
			$result = $this->wpdb->query($query);
		
		
		}  
	}
	
	
	public function change_order($current_index, $new_index)
	{
		foreach($this->photos as $photo)
			echo $photo->photo_id . " ";
		echo "\n";
			
		$photos = array_values($this->photos);
		$new_photos = array();
		$new_photos = $photos;	
		
		if($current_index > $new_index)
		{
			$update_bound = $new_index + ($current_index - $new_index);
			for($i=$new_index; $i<$update_bound; $i++)
			{
				$new_photos[$i+1] = $photos[$i];
			}
				
			$new_photos[$new_index] = $photos[$current_index];
								
			$this->photos = $new_photos;	
		}
		
		else if($current_index < $new_index)
		{
			$update_bound = $current_index + ($new_index - $current_index);
			for($i=$new_index; $i>$current_index; $i--)
			{
				$new_photos[$i-1] = $photos[$i];
			}
				
			$new_photos[$new_index] = $photos[$current_index];
								
			$this->photos = $new_photos;
		}
		
		
		foreach($this->photos as $photo)
			echo $photo->photo_id . " ";
		echo "\n";
	}
		
	
	
	//thumbnail_id is a photo_id, rather than a post_id
	public function set_thumbnail($thumbnail)
	{
		$this->thumb_id = $thumbnail->photo_id;
		$this->thumb = $thumbnail;
		$success = $this->update();	
		
		return $success;
	}
	
	public function count_photos()
	{
		$query = "SELECT DISTINCT *  
			FROM `".$this->wpdb->prefix.$this->wpss->plugin_prefix."slideshow_photo_relations`
			WHERE `slideshow_id` = '".$this->slideshow_id."' 
			ORDER BY `photo_order` ";
		$result = $this->wpdb->get_results($query);
		$count = 0;
		foreach($result as $key=>$row){
			$count++;
		}
		return $count;
	}
	
}

?>