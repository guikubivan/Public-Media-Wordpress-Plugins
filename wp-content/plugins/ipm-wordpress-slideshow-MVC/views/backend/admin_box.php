



<style type="text/css">
#hourglass
{
	/*font-size: 300%;*/ 
	position: fixed;
	right: 0;
	bottom: 0;
	background: white;
	color: black;
	border: solid 2px black;
	/*padding: 1ex;*/
	vertical-align: sub;
	display: none;
	z-index: 1000;
	width: 100%;
	max-height: 100%;
	overflow: auto;
}
</style>
<div id="hourglass">&#8987;</div>
<div>
	<span class='wpss_required' >&nbsp;</span> Required fields
</div>
<div id="slideshow_content_box">
<?= $content ?>
</div>
<div class='slideshow_box_footer' style='text-align:right;'>
	<input class="button button-highlighted" id="wpss_save" type="submit" value="Save" name="save" />
</div>




<?php

/*echo '<input type="hidden" name="'.$this->plugin_prefix.'nonce" id="'.$this->plugin_prefix.'nonce" value="' .wp_create_nonce( plugin_basename(__FILE__) ) .'" />';

		$this->print_slideshow_html();


		//echo "post id: " .$post->ID;


?>



<script type="text/javascript">
jQuery(document).ready(function() {
<?php
		if($slideshows=get_post_meta($post->ID,$this->fieldname, false)){//slideshow
			if(is_array($slideshows)){
				$post_image_id = get_post_meta($post->ID, $this->postmeta_post_image, true);
				foreach($slideshows as $sid){
					$sProps = $this->getSlideshowProps($sid);
					$sProps['update']= 'yes';
					$thumb_id = $sProps['thumb_id'];
					echo "slideshowOrganizer.organizerAddItem($sid,\"".$this->slideshowItemHTML($sid,$sProps)."\");
						currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();";
					$photos = $this->getPhotos($sid);
					//print_r($photos);
					if(is_array($photos)){
						foreach($photos as $pid => $photo){
							if($pid == $post_image_id){
								$post_image_slideshow_id = $sid;
							}
							if($pid == $thumb_id){
								$photo['cover'] = 'yes';	
							}
							echo "send_to_slideshow($pid,\"" . $this->photoItemHTML_simple($pid,$photo) . "\");";
							if($pid == $thumb_id){
								echo "setCoverImage($sid, $pid);";
							}							
						}
					}
				}

				echo get_option($this->option_multiple_slideshows) ? "showSlideshowMenu('slideshow_button', false);" : "showSlideshowMenu('', false);";
				if($post_image_id){
					echo "reloadPostImageSelect('${post_image_slideshow_id}_${post_image_id}');";
				}
			}

		}else if($pid=get_post_meta($post->ID,$this->plugin_prefix.'photo_id', true)){//single photo
			echo "slideshowOrganizer.organizerAddItem('',\"".$this->slideshowItemHTML('',array(), true)."\", true);
				currentSlideshowID = '".$this->plugin_prefix."slideshow_photos_ul_'+slideshowOrganizer.getLastID();";
			$photo = $this->getPhoto($pid);
			echo "\nsend_to_slideshow($pid,'" . $this->photoItemHTML_simple($pid,$photo) . "');";
			echo "showSlideshowMenu('', false);";
		}
?>

});
</script>
<?php */
?>
