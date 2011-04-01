<?php
/*
 * Defines a link between a photo and a slideshow.
 * Maps directly to the wpss_slideshow_photo_relationship table
 * 
 * Each photo can have a certain set of properties that are specific to a slideshow.  This
 *    model overrides the original properties of the IPM_photo object that it extends, but
 *    providing a slideshow-specific version of that photo.  It also includes addition
 *    methods and properties for dealing with the slideshow-photo link.
 * 
 * Methods: 
 * get_photo() - gets the photo properties, including the slideshow specific properties
 * get_photo_only() - only gets the general non-specific photo properties
 * get_post_id_by_photo_id() - gets the id of the wordpress post(attachment) based on the slideshow-specific photo_id
 * insert() - inserts the link/slideshow specific properties
 * update() - updates the slideshow specific properties
 * delete() - deletes the slideshow specific properties and the link between the slideshow and photo.  DOES NOT delete the photo itself
 * add_to_slideshow() - adds the photo to a given slideshow_id
 * remove_from_slideshow() - removes teh photo from the slideshow and deletes the slideshow specific data
 * 
 * Properties:
 * $photo_id
 * $slideshow_id
 * $order_id
 * 
 * also, everything inhereted by the Photo it extends
 * 
 */


class IPM_SlideshowPhoto extends IPM_Photo
{
	public $photo_id = "";
	public $slideshow_id = "";
	public $order;
	
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
		
		$this->get_photo_only();
		$photo_meta_rels = $this->wpdb->prefix.$this->wpss->plugin_prefix."photo_meta_relations";
		$query = "SELECT `meta_id`, `meta_value` FROM `".$photo_meta_rels."`
					WHERE
						`photo_id` = '".$this->photo_id."'
					";
		$results = $this->wpdb->get_results($query);
		if(!empty($results) )
		{
			foreach($results as $row)
			{
				switch($row->meta_id)
				{
					case 1 : $this->title = stripslashes($row->meta_value); break;
					case 2 : $this->alt = stripslashes($row->meta_value); break;
					case 3 : $this->caption = stripslashes($row->meta_value); break;
				}
			}			
		}	
		
		return true;
		
	}

	/* this function does not work as intended... */
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
	public function get_post_id_by_photo_id()
	{
		$photo_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."photos";
		
		$query = "SELECT `wp_photo_id` FROM `".$photo_table."` WHERE `photo_id` = '".$this->photo_id."'";
		$result = $this->wpdb->get_results($query);
		//print_r($result);
		if($result===false)
			return false;
		else
		{
			//$this->post_id = $result[0]['wp_photo_id'];
			return $result[0]->wp_photo_id;
		}
		
	}
	
	public function get_photo_only($post_id = "")
	{
		if( !empty($post_id) )
			$this->post_id = $post_id;
		if( empty($this->post_id) )
			$this->post_id = $this->get_post_id_by_photo_id();
				
		$success = parent::get_photo();
		return $success;
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
			$this->update();
			$this->get_photo($this->photo_id);
		return true;
	}

	public function update()
	{
		$msg = "";
		$photo_meta_rels = $this->wpdb->prefix.$this->wpss->plugin_prefix."photo_meta_relations";
		$query = "REPLACE INTO `".$photo_meta_rels."`
					SET
						`meta_value` = '". addslashes($this->title) ."',
						`photo_id` = '".$this->photo_id."',
						`meta_id` = '1'
					";
		$query;
		$success  = $this->wpdb->query($query);
		if($success === false)
			$msg .= "Failed to Update Title - $query"; 
		
		$query  = "REPLACE INTO `".$photo_meta_rels."`
					SET
						`meta_value` = '". addslashes($this->alt) ."',
						`photo_id` = '".$this->photo_id."',
						`meta_id` = '2'
					";			
		
		if($success !== false)
		{
			$success  = $this->wpdb->query($query);
			if($success === false)
				$msg .= "Failed to Update Alt - $query";
		}		
				
		$query = "REPLACE INTO `".$photo_meta_rels."`
					SET
						`meta_value` = '". addslashes($this->caption) ."',
						`photo_id` = '".$this->photo_id."',
						`meta_id` = '3'
					";		
		
		
		if($success !== false) 
		{ 
			$success  = $this->wpdb->query($query);
			if($success === false)
				$msg .= "Failed to Update Caption - $query";
		} 
		 
	/*	$photo_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."photos";
		echo $query = "UPDATE `".$photo_table."` SET `photo_order` = '".$this->order."' WHERE `photo_id` = '".$this->photo_id."'";
		$result = $this->wpdb->query($query);
	*/	
		 
		if($success !== false)
		{
			$success = parent::update();
			if($success !== true)
				$msg .= "Failed to Update Parent Photo - $success";
		}	

		if(empty($msg))
		{
			return true;
		}
		else
		{
			return $msg;
		}
		
		
	}

	public function delete()
	{
		$photo_meta_rels = $this->wpdb->prefix.$this->wpss->plugin_prefix."photo_meta_relations";
		$query = "DELETE FROM `".$photo_meta_rels."`
					WHERE
						`photo_id` = '".$this->photo_id."'
						AND `meta_id` = '1'
					";
		$success  = $this->wpdb->query($query);
		
		$query  = "DELETE FROM `".$photo_meta_rels."`
					WHERE
						`photo_id` = '".$this->photo_id."'
						AND `meta_id` = '2'
					";			
		if($success !== false)
			$success  = $this->wpdb->query($query);
		
		$query = "DELETE FROM `".$photo_meta_rels."`
					WHERE
						`photo_id` = '".$this->photo_id."'
						AND `meta_id` = '3'
					";				
		if($success !== false)
			$success  = $this->wpdb->query($query);
		
		
		$photo_table = $this->wpdb->prefix.$this->wpss->plugin_prefix."photos";
		$query = "DELETE FROM ".$photo_table." WHERE `photo_id` = '".$this->photo_id."' ;";//INSERT PHOTO
		if($success !== false)
			$success  = $this->wpdb->query($query);
		
		return $success;
		
	}

	public function add_to_slideshow($slideshow, $order=0)
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
		{
			return $this->delete();	
		}			
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