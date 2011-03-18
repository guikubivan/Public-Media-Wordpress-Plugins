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
		add_action('wp_ajax_action_replace_wp_photo', array(&$this, 'php_replace_wp_photo'));
	
		
	}

	public function php_get_coordinates()
	{}
	public function php_get_slideshow()
	{}
	function php_update_photo()
	{
		$photo_id = $this->plugin->_post['photo_id'];
		$photo = new IPM_Photo($this->plugin, $photo_id);
		
		$photo->title = $this->plugin->_post['title'];
		$photo->alt = $this->plugin->_post['alt'];
		$photo->caption = $this->plugin->_post['caption'];
		$photo->geo_location = $this->plugin->_post['geo_location'];
		$photo->photo_credit = $this->plugin->_post['photo_credit'];
		$photo->original_url = $this->plugin->_post['original_url'];
		
		$photo->update();
		
		echo "<pre>".print_r($this->plugin->_post)."</pre>"; //urlencode(print_r($photo, true));
		
		die();
		
	}
	public function php_replace_wp_photo()
	{}



}

?>