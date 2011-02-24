<?php
class Slideshow
{
	
	
	//always send the wordpress_slideshow object, so we can have access to 
	//fun things like the $wpdb object and global variables and such.
	private $wpss;
	private $wpdb;
	
	public function __construct($wordpress_slideshow)
	{
		$this->wpss = $wordpress_slideshow;
		$this->wpdb = $wordpress_slideshow->wpdb;
	}
	
}

?> 