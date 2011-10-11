<?php
/* This class it defined for the use of ajax functions which are called by ajax actions on the backend mainly
   for making database updates
*/
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
		add_action('wp_ajax_action_update_photo', array(&$this, 'php_update_photo'));
		add_action('wp_ajax_action_update_slideshow', array(&$this, 'php_update_slideshow'));
		add_action('wp_ajax_action_add_photo_to_slideshow', array(&$this, 'php_add_photo_to_slideshow'));
		add_action('wp_ajax_action_remove_photo_from_slideshow', array(&$this, 'php_remove_photo_from_slideshow'));
		add_action('wp_ajax_action_remove_slideshow', array(&$this, 'php_remove_slideshow'));
		add_action('wp_ajax_action_replace_wp_photo', array(&$this, 'php_replace_wp_photo'));
		add_action('wp_ajax_action_add_new_slideshow', array(&$this, 'php_add_new_slideshow') );
		add_action('wp_ajax_action_set_cover_image', array(&$this, 'php_set_cover_image') );
		add_action('wp_ajax_action_change_photo_order', array(&$this, 'php_change_photo_order') );
		add_action('wp_ajax_action_update_post_image_menu', array(&$this, 'php_update_post_image_menu') );
		add_action('wp_ajax_action_set_post_image', array(&$this, 'php_set_post_image') );
		
	}

	public function php_get_coordinates()
	{}
	public function php_get_slideshow()
	{}

// This function is used to set the cover image for each slideshow	
	public function php_set_cover_image()
	{
		$slideshow_id = $this->plugin->_post["slideshow_id"];
		$photo_id = $this->plugin->_post["photo_id"];
		
		$slideshow = new IPM_Slideshow($this->plugin, $slideshow_id);
		$thumb = new IPM_SlideshowPhoto($this->plugin, $photo_id);
		$slideshow->set_thumbnail($thumb);
		
		if($success !== false)
			die( "1" );
		else
			die( print_r($success, true) );
		
	}
	
//This function is used to change the photo order within the slideshow	
	public function php_change_photo_order()
	{
		$slideshow_id = $this->plugin->_post["slideshow_id"];
		$current_index = $this->plugin->_post["current_index"];
		$new_index = $this->plugin->_post["new_index"];
		$slideshow = new IPM_Slideshow($this->plugin, $slideshow_id);
		$slideshow->change_order($current_index, $new_index);
		$slideshow->save_photo_order();
		//die(print_r($this->plugin->_post, true) );
		
		//die(print_r($slideshow->photos, true) );
			
	}
	
//This function is used to update the photo fields to the database	
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
		
		die();		
	}
	
//This function is used to update the slideshow fields to the database	
	function php_update_slideshow()
	{
		$slideshow_id = $this->plugin->_post['slideshow_id'];
		$slideshow = new IPM_Slideshow($this->plugin, $slideshow_id);
		
		$slideshow->title = $this->plugin->_post['title'];
		$slideshow->description = $this->plugin->_post['description'];
		$slideshow->geo_location = $this->plugin->_post['geo_location'];
		$slideshow->photo_credit = $this->plugin->_post['photo_credit'];
		
		$slideshow->update();
		print_r($slideshow);
		die();		
	}
	
//This function removes the selected image form the slideshow	
	function php_remove_photo_from_slideshow()
	{
		$photo_id = $this->plugin->_post['photo_id'];
		$slideshow_id = $this->plugin->_post['slideshow_id'];
		
		if($slideshow_id == "single")
		{
			$post_slideshows = new IPM_PostSlideshows($this->plugin, "", $this->plugin->_post["post_id"]);
			$success = $post_slideshows->remove_single_photo();
			
			//remove the whole thing and show the "no slideshows" view
			$new_editor = $this->plugin->render_backend_view("admin_no_slideshows.php", array());
			die($new_editor);
		} 
		else 
		{

			$photo = new IPM_SlideshowPhoto($this->plugin, $photo_id);
			$success = $photo->remove_from_slideshow($slideshow_id);
		}
		
		if($success)
		{
			die("1");
		}
		else
		{
			die("0");
		}
		
		die();
	}
	
//This function removes the selected slideshow form the post	
	function php_remove_slideshow()
		{
			$slideshow_id = $this->plugin->_post['slideshow_id'];
			$post_id = $this->plugin->_post['post_id'];
			
			$post_slideshows = new IPM_PostSlideshows($this->plugin, "", $this->plugin->_post["post_id"]);
			$post_slideshows->get_slideshows();
			$new_slideshows = array();
			foreach($post_slideshows->slideshows as $slideshow)
			{
				if($slideshow->slideshow_id != $slideshow_id)
				{
					$new_slideshows[] = $slideshow;
				}
			}
			$post_slideshows->slideshows = $new_slideshows;
			
			$success = $post_slideshows->save_slideshows();
			
			if($success !== false && count($post_slideshows->slideshows) <= 0)
			{
				$new_editor = $this->plugin->render_backend_view("admin_no_slideshows.php", array());
				die($new_editor);
			}
			else if ($success !== false)
			{
				die($slideshow_id);
			}
			else
			{
				die("0");	
			}
			
			if($success)
			{
				die("1");
			}
			else
			{
				die("0");
			}
			
			die();
		}

//This function is called when a new slideshow needs to be added to the post		
		function php_add_new_slideshow()
		{
			//create new slideshow
			$new_slideshow = new IPM_Slideshow($this->plugin);
			$new_slideshow->insert();
			$new_slideshow->title = "Untitled Slideshow";
			$new_slideshow->update();
			
			//create post-slideshows object... this could be new, or it could be empty
			$post_slideshows = new IPM_PostSlideshows($this->plugin, "", $this->plugin->_post["post_id"]);
			$post_slideshows->get_slideshows(); //just in case there's already some slideshows associated with this post
			
			//this will matter later when we decide what to send back to the main page.	
			$original_number_of_slideshows = $this->plugin->_post["current_slideshows"];
			
			//add new slideshow to the post-slideshows object
			$post_slideshows->slideshows[] = $new_slideshow;
			
			//save the whole shabang
			$post_slideshows->save_slideshows();
		
			$slideshow_editors = array( $this->plugin->render_backend_view("admin_slideshow_editor.php", array("photo_editors"=>array(), "slideshow"=>$new_slideshow) ) );
			if($original_number_of_slideshows == 0)
			{
				//if there was nothing before it, print out the wrapper, too
				$slideshow_editor = $this->plugin->render_backend_view("admin_slideshow_wrapper.php", array("slideshow_editors"=>$slideshow_editors) );
			}
			else
			{
				$slideshow_editor = $slideshow_editors[0];
			}
			die($slideshow_editor);
		}
		
//This function is called for adding a photo to the selected slideshow		
	public function php_add_photo_to_slideshow()
	{	usleep(600000);
		$photo_post_id = $this->plugin->_post['photo_post_id'];
		$slideshow_id = $this->plugin->_post['slideshow_id'];
		$slideshow_photo = new IPM_SlideshowPhoto($this->plugin); //create new Photo-Slideshow Relationship
		$success = $slideshow_photo->get_photo_only($photo_post_id); //fill the slideshow photo with default photo data
	
		if($slideshow_id == "new_single") //adding a new single photo
		{
			$slideshow_photo->insert();
			if($this->plugin->_post["title"] != "")
				$slideshow_photo->title = $this->plugin->_post["title"];
			$slideshow_photo->update();
			
			$post_slideshow = new IPM_PostSlideshows($this->plugin);  
			$post_slideshow->post_id = $this->plugin->_post["post_id"]; 
			$post_slideshow->photo = $slideshow_photo;
			$post_slideshow->save_single_photo();
			
			$photo_editor = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$slideshow_photo, "slideshow_id"=>"single") );
			$editor = $this->plugin->render_backend_view("admin_single_photo.php", array("photo_editor"=>$photo_editor, "slideshow_id" => "single") );
		}
		else if($slideshow_id == "single" ) //transforming a single into a slideshow
		{
			//get the current photo	
			$post_slideshow = new IPM_PostSlideshows($this->plugin);
			$post_slideshow->post_id = $this->plugin->_post["post_id"];
			$post_slideshow->get_single_photo();
			$current_photo = $post_slideshow->photo;
			
			//create new slideshow
			$new_slideshow = new IPM_Slideshow($this->plugin);
			$new_slideshow->insert();
			$new_slideshow->attach_to_post($post_slideshow->post_id);
			$new_slideshow->title = $current_photo->title;
			$new_slideshow->update();
		
			//add current photo to new slideshow
			$current_photo->add_to_slideshow($new_slideshow->slideshow_id);
			
			//create NEW photo
			$slideshow_photo->insert();
			if($this->plugin->_post["title"] != "")
				$slideshow_photo->title = $this->plugin->_post["title"];
			$slideshow_photo->update();
			
			//add new photo to slideshow
			$slideshow_photo->add_to_slideshow($new_slideshow->slideshow_id);
			
			//remove the current single photo because we just added it to the new slideshow
			$post_slideshow->remove_single_photo();
		
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
			$editor = $this->plugin->render_backend_view("admin_slideshow_wrapper.php", array("slideshow_editors"=>$slideshow_editors) );
			//we're sending the whole view over so that it can replace what's there and look nice and spiffy like it had been there the whole time
		}
		else
		{
			$success = $slideshow_photo->insert(); //add the record to the database
			if($this->plugin->_post["title"] != "")
				$slideshow_photo->title = $this->plugin->_post["title"];
			$success = $slideshow_photo->update();
			$success = $slideshow_photo->add_to_slideshow($slideshow_id); //link the record to the slideshow
			$editor = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$slideshow_photo, "slideshow_id" => $slideshow_id) );
		}
		
		die($editor);				
	}
	
//This function updates the drop down list for post iamge whenever an image or slideshow is added or removed	
	function php_update_post_image_menu()
		{
			$post_id = $this->plugin->_post['post_id'];
			
			$post_slideshows = new IPM_PostSlideshows($this->plugin, "", $this->plugin->_post["post_id"]);
			$post_slideshows->get_slideshows();
			$slideshow_editors = array();
			$post_image_title = array();
			$msg = "<option value=''>Choose post image</option>";
		if(!empty($post_slideshows->slideshows) )
		{
			foreach($post_slideshows->slideshows as $key => $slideshow)
			{
				foreach($slideshow->photos as $key => $photo)
				{
					$post_image_title[$photo->photo_id] = $photo->title;
					$msg .= "<option value='";
					$msg .= $photo->photo_id;
					$msg .= "' ";
					if($photo->photo_id == $post_slideshows->post_image_id)
						{
							$msg .= "selected='selected'";
						}
					$msg .= ">";
					$msg .= $photo->title;
					$msg .= "</option>";							
				}
			}
		}

			die($msg);		
		}
	
//This function is used to set the post image
	function php_set_post_image()
		{
			$post_id = $this->plugin->_post['post_id'];
			$photo_id = $this->plugin->_post["photo_id"];
		
			$post_slideshows = new IPM_PostSlideshows($this->plugin, "", $this->plugin->_post["post_id"]);
			
			$post_slideshows->post_image_id = $photo_id;
			
			$post_slideshows->save_post_image();
			
			die();		
		}
}

?>