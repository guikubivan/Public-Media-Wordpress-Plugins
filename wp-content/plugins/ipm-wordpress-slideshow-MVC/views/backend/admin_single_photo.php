
<div class='<?php echo $this->plugin_prefix?>slideshow_container' id='slideshowContainer_<?php echo $slideshow_id?>'>
	<h4 class='organizer_item_heading' id='slideshow_heading_<?php echo $slideshow_id?>'>Single Photo</h4>
	<input type='hidden' name='slideshowItem[<?php echo $slideshow_id?>][update]' id='slideshowItem[<?php echo $slideshow_id?>][update] value='<? /*$slideshow->update*/ ?>' /> 
	
	<div class='slideshow_wrapper'>
		<ul>
			<li>
				<?php echo $photo_editor?>
			</li>		
			<li id="slideshow_<?php echo $slideshow_id?>_add_button" >
				<span id='addphoto_button_<?php echo $slideshow_id ?>' class='button' style='margin-left:45%;' class='alignright' onClick='if(confirm("Adding additional photos will change this into a slide show.  Proceed?") ) { pickPhoto("<?php echo  $slideshow_id ?>") };' tip='Add Media'>Add photo</span>
			</li>
		</ul>
	</div>
</div>