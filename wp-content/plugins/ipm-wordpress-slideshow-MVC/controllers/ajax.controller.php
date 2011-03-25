<?php

class IPM_Ajax
{
	private $plugin;
	private $wpdb;
	
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->wpdb = $plugin->wpdb;
		
		add_action('wp_ajax_action_get_coordinates', array(&$this, 'php_get_coordinates'));
		add_action('wp_ajax_action_get_slideshow', array(&$this, 'php_get_slideshow'));
		//add_action('wp_ajax_action_update_photo_property', array(&$slideshow_box, 'php_update_photo_property'));
		add_action('wp_ajax_action_update_photo', array(&$this, 'php_update_photo'));
		add_action('wp_ajax_action_add_photo_to_slideshow', array(&$this, 'php_add_photo_to_slideshow'));
		add_action('wp_ajax_action_remove_photo_from_slideshow', array(&$this, 'php_remove_photo_from_slideshow'));
		add_action('wp_ajax_action_replace_wp_photo', array(&$this, 'php_replace_wp_photo'));
	
		
	}

	public function php_get_coordinates()
	{}
	public function php_get_slideshow()
	{}
	function php_update_photo()
	{
		$photo_id = $this->plugin->_post['photo_id'];
		$photo = new IPM_SlideshowPhoto($this->plugin, $photo_id);
		
		$photo->title = $this->plugin->_post['title'];
		$photo->alt = $this->plugin->_post['alt'];
		$photo->caption = $this->plugin->_post['caption'];
		$photo->geo_location = $this->plugin->_post['geo_location'];
		$photo->photo_credit = $this->plugin->_post['photo_credit'];
		$photo->original_url = $this->plugin->_post['original_url'];
		
		$photo->update();
		
		echo "<pre>".print_r($this->plugin->_post, true)."</pre>"; //urlencode(print_r($photo, true));
		
		die();
		
	}
	function php_remove_photo_from_slideshow()
	{
		$photo = new IPM_SlideshowPhoto($this->plugin, $this->plugin->_post['photo_id']);
		$success = $photo->remove_from_slideshow($this->plugin->_post['slideshow_id']);
		
		if($success)
		{
			die("Successfully Removed Photo");
		}
		else
		{
			die("Could not remove photo");
		}
		
		die();
	}
	public function php_add_photo_to_slideshow()
	{
		//die("TEST");
		$photo_post_id = $this->plugin->_post['photo_post_id'];
		$slideshow_id = $this->plugin->_post['slideshow_id'];
		$slideshow_photo = new IPM_SlideshowPhoto($this->plugin); //create new Photo-Slideshow Relationship
		$success = $slideshow_photo->get_photo_only($photo_post_id); //fill the slideshow photo with default photo data
		if(!$success)
			die("could not get");
		$success = $slideshow_photo->insert(); //add the record to the database
		if(!$success)
			die("could not insert");
		$success = $slideshow_photo->title = $this->plugin->_post["title"];
		$success = $slideshow_photo->update();
		if(!$success)
			die("could not update");
		$success = $slideshow_photo->add_to_slideshow($slideshow_id); //link the record to the slideshow
		if(!$success)
			die("could not link");
		die("success?".print_r($slideshow_photo, true) );
		
	}
	
	public function php_replace_wp_photo(){
	/*	global $wpdb;
		$photo_id = $_POST['photo_id'];
		$wp_photo_id = $_POST['wp_photo_id'];
		$new_url = $_POST['new_url'];
		$photo_credit = $_POST['photo_credit'];
		$geo_location = $_POST['geo_location'];
		$retStr = '';
		if($photo_credit=='undefined' || $geo_location=='undefined'){
			$photo = $this->getPhotoFixedProps($wp_photo_id);
			$photo_credit = $photo['photo_credit'];
			$geo_location = $photo['geo_location'];
			$original_url = $photo['original_url'];
		}
		//$retStr = "var win = window.dialogArguments || opener || parent || top;";
		if($photo_id && $wp_photo_id){
			$result = $wpdb->query("UPDATE $this->t_p SET wp_photo_id=$wp_photo_id WHERE photo_id=$photo_id");
			if($result){
				$retStr .= "slideshowOrganizer.replaceSubItem(currentSlideshowID, $photo_id, '$new_url',\"".$photo_credit."\", \"".$geo_location."\", \"".$original_url."\");";
			}else{
				$retStr = 'alert("Photo could not be updated (possibly picked same photo?)");';
			}
		}

		die($retStr);*/
	}



}

?>