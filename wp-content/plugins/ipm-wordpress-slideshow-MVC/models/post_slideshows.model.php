<?php

class IPM_PostSlideshows
{
	public $slideshows = false;
	public $photo = false;
	
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
		
		$this->postmeta_post_image = $this->plugin->plugin_prefix.'post_image';
		
	}	
	
	public function get_slideshows()
	{
		
		if( $slideshows = get_post_meta($this->post->ID , "slideshow_id" , false)) //check for slideshows
		{
				if(is_array($slideshows))
				{
					$this->slideshows = array();
					
					$post_image_id = get_post_meta($this->post->ID, $this->postmeta_post_image, true);
					
					foreach($slideshows as $slideshow_id)
					{
						$this->slideshows[] = new IPM_Slideshow($this->plugin, $slideshow_id);
						
						/*$sProps = $this->getSlideshowProps($slideshow_id);
						$sProps['update']= 'yes';
						$thumb_id = $sProps['thumb_id'];
						echo "slideshowOrganizer.organizerAddItem($sid,\"".$this->slideshowItemHTML($slideshow_id,$sProps)."\");
							currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();";
						$photos = $this->getPhotos($slideshow_id);
						//print_r($photos);
						if(is_array($photos)){
							foreach($photos as $pid => $photo){
								if($pid == $post_image_id){
									$post_image_slideshow_id = $slideshow_id;
								}
								if($pid == $thumb_id){
									$photo['cover'] = 'yes';	
								}
								echo "send_to_slideshow($pid,\"" . $this->photoItemHTML_simple($pid,$photo) . "\");";
								if($pid == $thumb_id){
									echo "setCoverImage($sid, $pid);";
								}							
							}
						}*/
					}
			
					/*echo get_option($this->option_multiple_slideshows) ? "showSlideshowMenu('slideshow_button', false);" : "showSlideshowMenu('', false);";
					if($post_image_id){
						echo "reloadPostImageSelect('${post_image_slideshow_id}_${post_image_id}');";
					}*/
				}
	
			}
	/*		else if($pid=get_post_meta($post->ID,$this->plugin_prefix.'photo_id', true))
			{//single photo
				echo "slideshowOrganizer.organizerAddItem('',\"".$this->slideshowItemHTML('',array(), true)."\", true);
					currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();";
				$photo = $this->getPhoto($pid);
				echo "\nsend_to_slideshow($pid,'" . $this->photoItemHTML_simple($pid,$photo) . "');";
				echo "showSlideshowMenu('', false);";
			}	*/
		
	}
		
}
	