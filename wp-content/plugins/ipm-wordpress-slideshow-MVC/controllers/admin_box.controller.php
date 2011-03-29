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
		//echo WPSSDIR;

	}

	
	function post_form($entryType = 'post') 
	{
		$this->plugin->get_post();	
		
		$post_slideshows = new IPM_PostSlideshows($this->plugin);
		$post_slideshows->get_slideshows();
		$slideshow_editors = array();
		
		// global $post;
		// echo get_post_meta($post->ID,$this->plugin_prefix.'photo_id', true);
		
		
	/*	if($pid=get_post_meta($post->ID,$this->plugin_prefix.'photo_id', true)){//single photo
			echo "slideshowOrganizer.organizerAddItem('',\"".$this->slideshowItemHTML('',array(), true)."\", true);
				currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();";
			$photo = $this->getPhoto($pid);
			echo "\nsend_to_slideshow($pid,'" . $this->photoItemHTML_simple($pid,$photo) . "');";
			echo "showSlideshowMenu('', false);";
		}
		*/
		
		if(!empty($post_slideshows->slideshows) )
		{
			foreach($post_slideshows->slideshows as $key => $slideshow)
			{
				//$tab_order = $this->plugin->tab_order + 4;
				$photo_editors = array();
				foreach($slideshow->photos as $key => $photo)
				{
					$photo_editors[] = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$photo, "slideshow_id"=>$slideshow->slideshow_id) );
					//$tab_order += 6;
				}
				$slideshow_editors[] = $this->plugin->render_backend_view("admin_slideshow_editor.php", array("photo_editors"=>$photo_editors, "slideshow"=>$slideshow) );
				//$this->plugin->tab_order += 4;
				//$this->plugin->tab_order += $tab_order;
			}
			$admin_box = $this->plugin->render_backend_view("admin_box.php", array("slideshow_editors"=>$slideshow_editors) );
		}
		else if ( $post_slideshows->photo !== false )
		{
			$photo_editor = $this->plugin->render_backend_view("admin_photo_editor.php", array("photo"=>$post_slideshows->photo, "slideshow_id"=>"single") );
			$admin_box = $this->plugin->render_backend_view("admin_single_photo.php", array("photo_editor"=>$photo_editor, "slideshow_id" => "single") );
			
			
		}
		
		
		echo $admin_box; 
		
	}
	
	
		
		
	
}

?>