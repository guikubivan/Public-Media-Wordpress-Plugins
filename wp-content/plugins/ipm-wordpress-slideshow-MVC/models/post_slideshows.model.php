<?php

class IPM_PostSlideshows
{
	public $slideshows = array();
	public $photo = false;
	public $post_id;
	public $post;
	private $plugin;
	private $wpdb;
	private $postmeta_post_image = "";
	
	public function __construct($plugin, $post = "")
	{
		$this->plugin = $plugin;
		$this->wpdb = $plugin->wpdb;
		if(empty($post))
		{
			global $post;
		}
		$this->post = $post;
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
				
				$post_image_id = get_post_meta($this->post_id, $this->postmeta_post_image, true);
				
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
		
}
	