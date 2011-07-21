		<div  class='<?php echo $this->plugin_prefix ?>photo_container' id='photoContainer_<?php echo $photo->photo_id?>'>

		<span id='deletePhotoButton_<?php echo $photo->photo_id ?>_<?php echo $slideshow_id ?>' title="Delete Photo" class='button' style='float:right; font-size: 200%;' 
			onclick='if(confirm("Are you sure you want to remove the Photo ?") ) {removePhotoItem(<?php echo $photo->photo_id ?>, "<?php echo $slideshow_id ?>"); }'>X</span>

		<input type='hidden' id='slideshowItem_<?php echo $slideshow_id ?>_photos_<?php echo $photo->photo_id?>_cover' class='photo_in_slideshow_<?php echo $slideshow_id ?>' name='slideshowItem_<?php echo $slideshow_id ?>_photos_<?php echo$photo->photo_id?>_cover' value='<?php echo$photo->cover?>' /> 
			
			<div class='photo_fields'>
				
				<div style="float: right; max-width: 150px; text-align: center; vertical-align: bottom;" class="<? if(isset($ss_thumb) && !empty($ss_thumb) && $ss_thumb == $photo->photo_id) {?>wpss_cover_highlight<?}?>" >
					<img style="width: 130px; height: auto; max-height: 130px;"  id='img_<?php echo $slideshow_id ?>_<?php echo $photo->photo_id?>' class='wpss_photo_thumb' title='Click to replace image' onclick='/*confirmChooseNewPhoto(<?php echo $slideshow_id ?>, <?php echo $photo->photo_id?>);*/' src='<?php echo $photo->thumb_url ?>' /><br />
	<? if ($slideshow_id != "single") {?> <div onclick='setCoverImage("<?php echo $slideshow_id ?>", "<?php echo $photo->photo_id?>");' id='cover_button_<?php echo $slideshow_id ?>_<?php echo $photo->photo_id?>' class='button set_cover_button_<?php echo $slideshow_id ?>' style='text-align:center;margin-top:5px;' ><? if(isset($ss_thumb) && !empty($ss_thumb) && $ss_thumb == $photo->photo_id) {?>This is the slideshow thumbnail<?}else{?>Make this the slideshow thumbnail<?}?></div> <? } ?>
				</div>
				
				
				
				<div>
					<label >Title:</label>
					<input type='text'  id='slideshowItem_<?php echo $slideshow_id ?>_photos_<?php echo $photo->photo_id ?>_title' name='slideshow[<?php echo $slideshow_id ?>][photos][<?php echo $photo->photo_id ?>][title]' style='width:300px' value='<?php echo htmlentities($photo->title)?>' class='<?php echo $this->plugin_prefix ?>required photo_title' />
				</div>
				<div>	
					<label >Photo credit</label>
					<input type='text' id='slideshowItem_<?php echo $slideshow_id ?>_photos_<?php echo$photo->photo_id?>_photo_credit' name='slideshow[<?php echo $slideshow_id ?>][photos][<?php echo $photo->photo_id ?>][photo_credit]' style='width:300px' value='<?php echo htmlentities($photo->photo_credit) ?>'  title='Click to edit' class='editable <?php echo $this->plugin_prefix ?>required' />
				</div>
				<div>
					<label >Geo location</label>
					<input type='text' id='slideshowItem_<?php echo $slideshow_id ?>_photos_<?php echo$photo->photo_id?>_geo_location' name='slideshow[<?php echo $slideshow_id ?>][photos][<?php echo $photo->photo_id ?>][geo_location]' style='width:300px' value='<?php echo htmlentities($photo->geo_location) ?>' class='editable' title='Click to edit' /<img onClick='showMapForPhoto(this.previousSibling.previousSibling.value);' class='map_icon centervertical' src='images/map_icon.jpg' />
				</div>
				<div>
					<label >Original URL</label>
					<input type='text' id='slideshowItem_<?php echo $slideshow_id ?>_photos_<?php echo$photo->photo_id?>_original_url' name='slideshow[<?php echo $slideshow_id ?>][photos][<?php echo $photo->photo_id ?>][original_url]' style='width:300px' value='<?php echo htmlentities($photo->original_url) ?>'  title='Click to edit' class='editable' />
				</div>
				<div>
					<label >Alt text <small>Specific to this Slideshow</small></label>
					<input type='text' id='slideshowItem_<?php echo $slideshow_id ?>_photos_<?php echo $photo->photo_id?>_alt' name='slideshow[<?php echo $slideshow_id ?>][photos][<?php echo $photo->photo_id ?>][alt]' style='width:300px' value='<?php echo htmlentities($photo->alt) ?>' />
					
				</div>
				
				<div>
					<label >Caption</label>
					<textarea  id='slideshowItem_<?php echo $slideshow_id ?>_photos_<?php echo $photo->photo_id?>_caption' name='slideshow[<?php echo $slideshow_id ?>][photos][<?php echo $photo->photo_id ?>][caption]' rows='2' style='width:300px' class='<?php echo $this->plugin_prefix ?>required' ><?php echo htmlentities($photo->caption) ?></textarea>
				</div>
				
				<div >
					<span onclick='ajax_update_photo(<?php echo $photo->photo_id?>, "<?php echo $slideshow_id ?>");' id='save_photo_button_<?php echo $photo->photo_id?>' class='button save_photo_button_<?php echo $slideshow_id ?>' style='text-align:center;margin-top:5px; display: none;' >Update This Photo</span>
				</div>
				
	
			</div>
		</div>
		