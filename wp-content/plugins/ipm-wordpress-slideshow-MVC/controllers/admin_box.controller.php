<?php

/*
 * Contains function that is used to display the meta box on the backend */

class WPSSAdminBox
{
	
	public $plugin_prefix;
	private $plugin;
	private $wpdb;
	
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->wpdb = $plugin->wpdb;
		$this->plugin_prefix = $plugin->plugin_prefix;
	}
	
	function page_form()
	{
		$this->post_form('page');
		//echo WPSSDIR;

	}

	
	function post_form($entryType = 'post') 
	{
		$this->plugin->get_post();	
		
		$post_slideshows = new IPM_PostSlideshows($this->plugin);
		$post_slideshows->get_slideshows();
		$slideshow_editors = array();
		
		if(!empty($post_slideshows->slideshows) )
		{
			foreach($post_slideshows->slideshows as $key => $slideshow)
			{
				$slideshow_thumb = $slideshow->thumb->photo_id;
				$photo_editors = array();
				foreach($slideshow->photos as $key => $photo)
				{
					$photo_editors[] = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$photo, "slideshow_id"=>$slideshow->slideshow_id, "ss_thumb"=>$slideshow_thumb) );
				}
				$slideshow_editors[] = $this->plugin->render_backend_view("admin_slideshow_editor.php", array("photo_editors"=>$photo_editors, "slideshow"=>$slideshow) );
			}
			$admin_box = $this->plugin->render_backend_view("admin_slideshow_wrapper.php", array("slideshow_editors"=>$slideshow_editors) );
		}
		else if ( $post_slideshows->photo !== false )
		{
			$photo_editor = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$post_slideshows->photo, "slideshow_id"=>"single") );
			$admin_box = $this->plugin->render_backend_view("admin_single_photo.php", array("photo_editor"=>$photo_editor, "slideshow_id" => "single") );
		}
		else
		{
			$admin_box = $this->plugin->render_backend_view("admin_no_slideshows.php", array() );
			
		}
	
		
		$admin_box = $this->plugin->render_backend_view("admin_box.php", array("content"=>$admin_box) );
			
		
		echo $admin_box; 
		
	}
	
	
		
		
	
}

?>