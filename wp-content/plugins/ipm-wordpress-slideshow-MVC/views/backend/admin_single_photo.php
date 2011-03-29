
<div class='<?=$this->plugin_prefix?>slideshow_container' id='slideshowContainer_<?=$slideshow_id?>'>
	<h4 class='organizer_item_heading' id='slideshow_heading_<?=$slideshow_id?>'>Single Photo</h4>
	<input type='hidden' name='slideshowItem[<?=$slideshow_id?>][update]' id='slideshowItem[<?=$slideshow_id?>][update] value='<? /*$slideshow->update*/ ?>' /> 
	
	<div class='slideshow_wrapper'>
		<ul>
			<li>
				<?=$photo_editor?>
			</li>		
			<li id="slideshow_<?=$slideshow_id?>_add_button" >
				<span id='addphoto_button_<?= $slideshow_id ?>' class='button' style='margin-left:45%;' class='alignright' onClick='if(confirm("Adding additional photos will change this into a slide show.  Proceed?") ) { pickPhoto("<?= $slideshow_id ?>") };' tip='Add Media'>Add photo</span>
			</li>
		</ul>
	</div>
</div>