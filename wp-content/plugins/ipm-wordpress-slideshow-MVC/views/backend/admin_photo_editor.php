		<div  class='<?= $this->plugin_prefix ?>photo_container' id='photoContainer_<?=$photo->photo_id?>'>

		<span id='deletePhotoButton_<?= $photo->photo_id ?>_<?= $slideshow_id ?>' title="Delete Photo" class='button' style='float:right; font-size: 200%;' 
			onclick='
				removePhotoItem(<?= $photo->photo_id ?>, <?= $slideshow_id ?>)
			'>X</span>

		<input type='hidden' id='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_cover' class='photo_in_slideshow_<?= $slideshow_id ?>' name='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_cover' value='<?=$photo->cover?>' /> 
			
			<div class='photo_fields'>
				
				<div style="float: right;">
					<img id='img_<?= $slideshow_id ?>_<?=$photo->photo_id?>' class='wpss_photo_thumb' title='Click to replace image' onclick='confirmChooseNewPhoto(<?= $slideshow_id ?>, <?=$photo->photo_id?>);' src='<?= $photo->url ?>' /><br />
					<span onclick='setCoverImage(<?= $slideshow_id ?>, <?=$photo->photo_id?>);' id='cover_button_<?= $slideshow_id ?>_<?=$photo->photo_id?>' class='button set_cover_button_<?= $slideshow_id ?>' style='text-align:center;margin-top:5px;' >Slideshow Thumbnail</span>
				</div>
				
				
				
				<div>
					<label >Title:</label>
					<input type='text'  id='slideshowItem_<?= $slideshow_id ?>_photos_<?= $photo->photo_id ?>_title' name='slideshowItem_<?= $slideshow_id ?>_photos_<?= $photo->photo_id ?>_title' size='20' value='<?= htmlentities($photo->title)?>' class='<?= $this->plugin_prefix ?>required photo_title' />
				</div>
				<div>	
					<label >Photo credit</label>
					<input type='text' id='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_photo_credit' name='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_photo_credit' size='20' value='<?= htmlentities($photo->photo_credit) ?>'  title='Click to edit' class='editable <?= $this->plugin_prefix ?>required' />
				</div>
				<div>
					<label >Geo location</label>
					<input type='text' name='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_geo_location' id='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_geo_location' size='20' value='<?= htmlentities($photo->geo_location) ?>' class='editable' title='Click to edit' /<img onClick='showMapForPhoto(this.previousSibling.previousSibling.value);' class='map_icon centervertical' src='images/map_icon.jpg' />
				</div>
				<div>
					<label >Original URL</label>
					<input type='text' id='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_original_url' name='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_original_url' size='20' value='<?= htmlentities($photo->original_url) ?>'  title='Click to edit' class='editable' />
				</div>
				<div>
					<label >Alt text <small>Specific to this Slideshow</small></label>
					<input type='text' id='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_alt' name='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_alt' size='20' value='<?= htmlentities($photo->alt) ?>' />
					
				</div>
				
				<div>
					<label >Caption</label>
					<textarea  name='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_caption' id='slideshowItem_<?= $slideshow_id ?>_photos_<?=$photo->photo_id?>_caption' rows='2' style='width:300px' class='<?= $this->plugin_prefix ?>required' ><?= htmlentities($photo->caption) ?></textarea>
				</div>
				
				<div >
					<span onclick='ajax_update_photo(<?=$photo->photo_id?>, "<?= $slideshow_id ?>");' id='save_photo_button_<?=$photo->photo_id?>' class='button save_photo_button_<?= $slideshow_id ?>' style='text-align:center;margin-top:5px;' >Update This Photo</span>
				</div>
				
	
			</div>
		</div>
		