<?php
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
	
	public $thumbnail; //IPM_Photo object;
	
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
		
		$this->get_photos();
	}
	
	public function get_photos()
	{
		$query = "SELECT DISTINCT *  
			FROM `".$this->wpdb->prefix.$this->wpss->plugin_prefix."slideshow_photo_relations`
			WHERE `slideshow_id` = '".$this->slideshow_id."' ";
		$result = $this->wpdb->get_results($query);
		//$this->photos = $result;
		foreach($result as $key=>$row){
			$this->photos[] = new IPM_SlideshowPhoto($this->wpss, $row->photo_id);
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
			

		$result = $wpdb->query($query);
		if($result===false){
			$wpdb->print_error();
			return false;
		}else if($sid){
			return $sid;
		}

		$this->slideshow_id = $this->wpdb->get_var('SELECT LAST_INSERT_ID();');
		
		if($post_id && $post_id > -1){
			add_post_meta($post_id, $this->fieldname, $slideshow_id, false);
		}

		return $slideshow_id;
	}
		
	
	//thumbnail_id is a photo_id, rather than a post_id
	public function set_thumbnail($thumbnail)
	{
		$this->thumb_id = $thumbnail->photo_id;
		$this->thumbnail = $thumbnail;
		$success = $this->update();	
		
		return $success;
	}
}

?>