<?php
/**
 * Contains functions for displaying photos and slideshows on the front-end.
 */
class IPM_FrontEnd
{
	private $plugin;
	private $wpdb;
	
	public function __construct($plugin)
	{
		$this->plugin = $plugin;
		$this->wpdb = $plugin->wpdb;
		$this->plugin->get_post();	
		
	}

	//this function is what is used by the show_photos method in the main controller which is, 
	//    in turn, called by the global wpss_photos function for use in templates or whatever.
	function show_photos($stylesheet='')
	{
		$this->plugin->get_post();	
		if( !$this->post_has_tags($this->plugin->post->post_content) )
		{
			$post_slideshows = new IPM_PostSlideshows($this->plugin);
			$post_slideshows->get_slideshows();
			
			$type = "";	
			
			if(!empty($post_slideshows->slideshows) ) //if the post has slideshows
			{
				$type = "slideshows";
			}
			else if ( $post_slideshows->photo !== false ) //if the post doesn't have slideshows, but does have a photo
			{
				$type = "photo";
			}	
			
			$stylesheet = $this->plugin->convert_stylesheet($stylesheet);
			$output = $this->plugin->render_frontend_view($stylesheet, array("type"=>$type, "slideshows"=>$post_slideshows->slideshows, "photo"=>$post_slideshows->photo) );
			if($output){
				echo $output;
			}else{
				if($stylesheet = "")
					echo "Error. The Slideshow Stylesheets have not been defined.";
				else
					echo "";
			}
		}
	}
	
	//check if the post content has short tags that can be replaced without actually printing the images 
	private function post_has_tags($text){
		return $this->replace_tags($text, true); //$this->replace_photo_tags($this->plugin->post->post_content, true) || $this->replace_slideshow_tags($this->plugin->post->post_content, true);
	}
	
	//used to replace the short tags with photos/slideshows in the post content
	function replace_tags($text, $probe = false)
	{
		//$this->plugin->get_post();	
		//$text = $this->plugin->post->post_content;
		$post_slideshows = new IPM_PostSlideshows($this->plugin);
		$post_slideshows->get_slideshows();
		
		if(!empty($post_slideshows->slideshows) ) //if the post has slideshows
		{
			foreach($post_slideshows->slideshows as $s_key => $slideshow)
			{
				$s_index = $s_key + 1;
				//replace all the slideshow tags with slideshows inside the post
				
				$patterns1 = array("/\[\s*slideshow\s*\-?\s*".$s_index."\s*([^\]\s]*)\s*\]/",
								   "/\[\s*slideshow\s*\-?\s*".$s_index."\s*,\s*left\s*([^\]\s]*)\s*\]/",
								   "/\[\s*slideshow\s*\-?\s*".$s_index."\s*,\s*right\s*([^\]\s]*)\s*\]/");
				
				foreach($patterns1 as $pat1)
				{
				if(preg_match_all($pat1,$text, $matches)>0)
				{
					for($i=0;$i<sizeof($matches[0]);++$i){
						if($probe){
							return true;
						}				
						
						/*$stylesheet = $matches[1][$i];
						if(!preg_match("/^.*\.xsl$/", $stylesheet)){
							$stylesheet = $stylesheet.".xsl";
						}
						if(!$stylesheet || !file_exists(dirname(__FILE__).'/stylesheets/'.$stylesheet)){
							$stylesheet = get_option($this->option_default_style_slideshow);
						}
						$out = $this->get_slideshow_clip($sids[$h],$stylesheet);
						$text = str_replace($matches[0][$i], $out, $text);*/
						if (strstr($matches[0][$i], "left"))
							$stylesheet = $this->plugin->convert_stylesheet($this->plugin->default_style_slideshow_left);
						
						else if (strstr($matches[0][$i], "right"))
							$stylesheet = $this->plugin->convert_stylesheet($this->plugin->default_style_slideshow_right);
						
						else
							$stylesheet = $this->plugin->convert_stylesheet($this->plugin->default_style_slideshow);
						
						$output = $this->plugin->render_frontend_view($stylesheet, array("type"=>"slideshows", "slideshows"=>array($slideshow), "photo"=>"") );
						$text = str_replace($matches[0][$i], $output, $text);
					}
				}
				}
				
				foreach( $slideshow->photos as $key => $photo)
				{
					//replace all the photo tags inside the post
					$patterns2 = array("/\[\s*photo\s*\-?\s*".($key+1)."\s*([^\]\s]*)\s*\]/", "/\[\s*slideshow\s*".($s_key+1)."\s*,\s*photo\s*\-?\s*".($key+1)."\s*([^\]\s]*)\s*\]/",
									  "/\[\s*photo\s*\-?\s*".($key+1)."\s*,\s*left\s*([^\]\s]*)\s*\]/", "/\[\s*slideshow\s*".($s_key+1)."\s*,\s*photo\s*\-?\s*".($key+1)."\s*,\s*left\s*([^\]\s]*)\s*\]/",
									  "/\[\s*photo\s*\-?\s*".($key+1)."\s*,\s*right\s*([^\]\s]*)\s*\]/", "/\[\s*slideshow\s*".($s_key+1)."\s*,\s*photo\s*\-?\s*".($key+1)."\s*,\s*right\s*([^\]\s]*)\s*\]/");
			      	
			      	foreach($patterns2 as $pat2)
			      	{
						if(preg_match_all($pat2,$text, $matches)>0)
						{
							for($i=0;$i<sizeof($matches[0]);++$i)
							{
								if($probe){
									return true;
								}	
								//$text .= "TEST";
								/*$stylesheet = $matches[1][$i];
								if(!preg_match("/^.*\.xsl$/", $stylesheet))
								{
								  $stylesheet = $stylesheet.".xsl";
								}
								if(empty($stylesheet) || !file_exists(dirname(__FILE__).'/stylesheets/'.$stylesheet))
								{
								  $stylesheet = get_option($this->plugin->option_default_style_photo);
								}*/
								//$stylsheet;
								//$out = $this->get_photo_clip($ids[$h],$stylesheet);
								//$text .= $stylesheet;
								if (strstr($matches[0][$i], "left"))
									$stylesheet = $this->plugin->convert_stylesheet($this->plugin->default_style_photo_left);
									
								else if (strstr($matches[0][$i], "right"))
									$stylesheet = $this->plugin->convert_stylesheet($this->plugin->default_style_photo_right);
								
								else	
									$stylesheet = $this->plugin->convert_stylesheet($this->plugin->default_style_photo);
								
								$output = $this->plugin->render_frontend_view($stylesheet, array("type"=>"photo", "slideshows"=>array(), "photo"=>$photo) );
								$text = str_replace($matches[0][$i], $output, $text);
							}
						}
			     	}
				}
				
				
			}
		}
		if($probe)
			return false;
		return $text;
	}




/*
	
	function replace_photo_tags($text, $probe=false){
		global $post;
		$post_id = $post->ID;
    $sids = get_post_meta($post_id, $this->fieldname, false);//get all slideshows, not single
    $text_or_probe = $text;
    if(sizeof($sids) > 0){
      foreach($sids as $index => $sid){
        $text_or_probe = $this->replace_photo_tags2($sid, $text_or_probe, $index+1, $probe);
      }
    }else{
      $text_or_probe = $this->replace_photo_tags2('', $text_or_probe, -1, $probe);
    }
    return $text_or_probe;
	}

	function replace_slideshow_tags($text, $probe=false){
		
		global $wpdb, $post;
		$post_id = $post->ID;
		$sids=get_post_meta($post_id,$this->fieldname, false);
		if(!$sids){
			if($probe)return false;
			return $text;
		}
		sort($sids);
		for($h=0;$h<sizeof($sids);++$h){
			$index = $h+1;
			if(preg_match_all("/\[\s*slideshow\s*\-?\s*$index\s*([^\]\s]*)\s*\]/",$text, $matches)>0){
				if($probe){
					return true;
				}				
				for($i=0;$i<sizeof($matches[0]);++$i){
					$stylesheet = $matches[1][$i];
					if(!preg_match("/^.*\.xsl$/", $stylesheet)){
						$stylesheet = $stylesheet.".xsl";
					}
					if(!$stylesheet || !file_exists(dirname(__FILE__).'/stylesheets/'.$stylesheet)){
						$stylesheet = get_option($this->option_default_style_slideshow);
					}
					$out = $this->get_slideshow_clip($sids[$h],$stylesheet);
					$text = str_replace($matches[0][$i], $out, $text);
				}
			}
		}

		if($probe)return false;
		return $text;
	}
	
	
	
	function replace_photo_tags2($sid, $text, $s_index, $probe=false){
		global $wpdb, $post;
		$post_id = $post->ID;

		if($sid){
			$query ="SELECT DISTINCT spr.photo_id FROM $this->t_spr as spr WHERE spr.slideshow_id=$sid ORDER BY spr.photo_order;";
			$ids = $wpdb->get_col($query);
		}else if($pid=get_post_meta($post_id,$this->plugin_prefix.'photo_id', true)){//photo only
			$ids = array($pid);
		}else{
			if($probe)return false;
			return $text;
		}

		for($h=0;$h<sizeof($ids);++$h){
			$index = $h+1;
      $patterns = array("/\[\s*photo\s*\-?\s*$index\s*([^\]\s]*)\s*\]/", "/\[\s*slideshow\s*$s_index\s*,\s*photo\s*\-?\s*$index\s*([^\]\s]*)\s*\]/");
      foreach($patterns as $pat){
			  if(preg_match_all($pat,$text, $matches)>0){
				  if($probe){
					  return true;
				  }	
				  for($i=0;$i<sizeof($matches[0]);++$i){
					  $stylesheet = $matches[1][$i];
					  if(!preg_match("/^.*\.xsl$/", $stylesheet)){
						  $stylesheet = $stylesheet.".xsl";
					  }
					  if(!$stylesheet || !file_exists(dirname(__FILE__).'/stylesheets/'.$stylesheet)){
						  $stylesheet = get_option($this->option_default_style_photo);
					  }
					  $out = $this->get_photo_clip($ids[$h],$stylesheet);
					  $text = str_replace($matches[0][$i], $out, $text);
				  }
			  }
		}
	}
	if($probe)return false;
	return $text;
  }
	
	*/
	
}

?>