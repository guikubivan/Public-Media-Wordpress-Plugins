<?php

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
		echo WPSSDIR;

	}

	
	function post_form($entryType = 'post') 
	{
		$this->plugin->get_post();	
		
		$post_slideshows = new IPM_PostSlideshows($this->plugin);
		$post_slideshows->get_slideshows();
		
		
		$slideshow_editors = array();
		foreach($post_slideshows->slideshows as $key => $slideshow)
		{
			$tab_order = $this->plugin->tab_order + 4;
			$photo_editors = array();
			foreach($slideshow->photos as $key => $photo)
			{
				$photo_editors[] = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$photo, "tab_order"=>$tab_order, "slideshow_id"=>$slideshow->slideshow_id) );
				$tab_order += 6;
			}
			$slideshow_editors[] = $this->plugin->render_backend_view("admin_slideshow_editor.php", array("photo_editors"=>$photo_editors, "slideshow"=>$slideshow) );
			$this->plugin->tab_order += 4;
			$this->plugin->tab_order += $tab_order;
		}
			
		
		echo $this->plugin->render_backend_view("admin_box.php", array("slideshow_editors"=>$slideshow_editors) );
	}
	
	
		
		
	
}

?>