<?php
/*
 * Used to abstract the relationship between posts and their slideshows
 * Does not map directly to any database tables
 * 
 * Methods:
 * get_slideshows() - populates $slideshows
 * save_single_photo()
 * get_single_photo() - gets the $photo object
 * 
 * Attributes:
 * $slideshows - array of IPM_Slideshow objects
 * $photo - the main photo of the post or if it's just a single photo slideshow - IPM_SlideshowPhoto object
 * $post_id - the post_id of the current post
 * $post - the global $post variable got from the main_controller object
 * $plugin - the main controller object
 * $wpdb - the wordpress database object
 * $postmeta_post_image - the name of the wordpress post_meta that you need to get the $photo
 * 
 */
class IPM_PostSlideshows
{
	public $slideshows = array();
	public $photo = false;
	public $post_id;
	public $post;
	private $plugin;
	private $wpdb;
	private $postmeta_post_image = "";
	public $post_image_id;
	
	public function __construct($plugin, $post = "", $post_id = "")
	{
		$this->plugin = $plugin;
		$this->wpdb = $plugin->wpdb;
		if(empty($post))
		{
			global $post;
		}
		$this->post = $post;
		
		if(!empty($post_id))
			$this->post_id = $post_id;
		else
			$this->post_id = $this->post->ID;
		
		$this->postmeta_post_image = $this->plugin->plugin_prefix.'post_image';
		$this->get_single_photo();
		
	}	
	
	public function get_slideshows()
	{
		if( $slideshows = get_post_meta($this->post_id , "slideshow_id" , false)) //check for slideshows
		{
			if(is_array($slideshows))
			{
				$this->slideshows = array();
			
				$this->post_image_id = get_post_meta($this->post_id, $this->postmeta_post_image, true);
				
				foreach($slideshows as $slideshow_id)
				{
					$this->slideshows[] = new IPM_Slideshow($this->plugin, $slideshow_id);
				}
			}
		}
	}
	
	public function save_single_photo()
	{
		update_post_meta($this->post_id, $this->plugin->plugin_prefix.'photo_id', $this->photo->photo_id);
	}
	
	public function get_single_photo()
	{
		$photo_id = get_post_meta($this->post_id, $this->plugin->plugin_prefix.'photo_id', true);
		if(!empty($photo_id) )
		{
			$this->photo = new IPM_SlideshowPhoto($this->plugin, $photo_id);
			return $this->photo;
		}
		return false;
	}
	public function remove_single_photo()
	{
		delete_post_meta($this->post_id, $this->plugin->plugin_prefix.'photo_id', $this->photo->photo_id);
		return true;
	}
	
	public function save_slideshows()
	{
		delete_post_meta($this->post_id, "slideshow_id");
		foreach($this->slideshows as $slideshow)
		{
			//echo $slideshow->slideshow_id;	
			$success = add_post_meta($this->post_id, "slideshow_id", $slideshow->slideshow_id);
		}
		return $success;	
	}
		
public function save_post_image()
	{
		update_post_meta($this->post_id, $this->postmeta_post_image, $this->post_image_id);
		return true;
	}


}
	