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
		echo $photo->update();
		
		//print_r($photo);
		//print_r($this->plugin->_post); 
		
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
	
		
		if($slideshow_id == "new") //adding a new single photo
		{
			$post_slideshow = new IPM_PostSlideshows($this->plugin);
			$post_slideshow->post_id = $this->plugin->_post["post_id"];
			$post_slideshow->photo = $slideshow_photo;
			$post_slideshow->save_single_photo();
			$photo_editor = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$slideshow_photo, "slideshow_id"=>"single") );
			$admin_box = $this->plugin->render_backend_view("admin_single_photo.php", array("photo_editor"=>$photo_editor) );
			
		}
		else if($slideshow_id == "single" ) //transforming a single into a slideshow
		{
			//get the current photo	
			$post_slideshow = new IPM_PostSlideshows($this->plugin);
			$post_slideshow->post_id = $this->plugin->_post["post_id"];
			$post_slideshow->get_single_photo();
			$current_photo = $post_slideshow->photo;
		//	print_r($current_photo);
			
			//create new slideshow
			$new_slideshow = new IPM_Slideshow($this->plugin);
			$new_slideshow->insert();
			$new_slideshow->attach_to_post($post_slideshow->post_id);
			$new_slideshow->title = $current_photo->title;
			$new_slideshow->update();
		
			//add current photo to new slideshow
			$current_photo->add_to_slideshow($new_slideshow->slideshow_id);
			
		//	print_r($new_slideshow);
			
			//create NEW photo
			$slideshow_photo->insert();
			$slideshow_photo->title = $this->plugin->_post["title"];
			$slideshow_photo->update();
			
			//add new photo to slideshow
			$slideshow_photo->add_to_slideshow($new_slideshow->slideshow_id);
			
			//create the new editor
			$post_slideshow->get_slideshows();
			$slideshow_editors = array();
			foreach($post_slideshow->slideshows as $key => $slideshow)
			{
				$photo_editors = array();
				foreach($slideshow->photos as $key => $photo)
				{
					$photo_editors[] = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$photo, "slideshow_id"=>$slideshow->slideshow_id) );
				}
				$slideshow_editors[] = $this->plugin->render_backend_view("admin_slideshow_editor.php", array("photo_editors"=>$photo_editors, "slideshow"=>$slideshow) );
			}
			$editor = $this->plugin->render_backend_view("admin_box.php", array("slideshow_editors"=>$slideshow_editors) );
		
		}
		else
		{
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
			
			$editor = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$slideshow_photo, "slideshow_id" => $slideshow_id) );
		}
		
		die($editor);
		
		
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