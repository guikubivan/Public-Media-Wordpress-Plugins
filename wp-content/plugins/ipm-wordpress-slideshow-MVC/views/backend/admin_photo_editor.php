		<div  class='<?= $this->plugin_prefix ?>photo_container' id='photoContainer_<?=$photo->photo_id?>'>

		<span id='deletePhotoButton_<?= $photo->photo_id ?>_s_id' title="Delete Photo" class='button' style='float:right; font-size: 200%;' onclick='if ( this.parentNode.parentNode && this.parentNode.parentNode.removeChild ) {this.parentNode.parentNode.removeChild(this.parentNode);removePhotoItem(this.id);}'>&#10008;</span>

		<input type='hidden' id='slideshowItem[s_id][photos][<?=$photo->photo_id?>][cover]' class='photo_in_slideshow_s_id' name='slideshowItem[s_id][photos][<?=$photo->photo_id?>][cover]' value='<?=$photo->cover?>' /> 
			
			<div class='photo_fields'>
				
				<div style="float: right;">
					<img id='img_s_id_<?=$photo->photo_id?>' class='wpss_photo_thumb' title='Click to replace image' onclick='confirmChooseNewPhoto(s_id, <?=$photo->photo_id?>);' src='<?= $photo->url ?>' /><br />
					<span onclick='setCoverImage(s_id, <?=$photo->photo_id?>);' id='cover_button_s_id_<?=$photo->photo_id?>' class='button set_cover_button_s_id' style='text-align:center;margin-top:5px;' >Slideshow Thumbnail</span>
				</div>
				
				<div style="float: right;">
					<span onclick='ajax_update_photo(<?=$photo->photo_id?>);' id='save_photo_button_<?=$photo->photo_id?>' class='button save_photo_button_s_id' style='text-align:center;margin-top:5px;' >Update This Photo</span>
				</div>
				
				<div>
					<label >Title:</label>
					<input type='text'  tabindex='<?=($tab_order + 1)?>' id='slideshowItem[s_id][photos][<?= $photo->photo_id ?>][title]' name='slideshowItem[s_id][photos][<?= $photo->photo_id ?>][title]' size='20' value='<?= $photo->title?>' class='<?= $this->plugin_prefix ?>required photo_title' />
				</div>
				<div>	
					<label >Photo credit</label>
					<input type='text'  tabindex='<?= ($tab_order + 2) ?>' id='slideshowItem[s_id][photos][<?=$photo->photo_id?>][photo_credit]' name='slideshowItem[s_id][photos][<?=$photo->photo_id?>][photo_credit]' size='20' value='<?= $photo->photo_credit ?>'  title='Click to edit' class='editable <?= $this->plugin_prefix ?>required' />
				</div>
				<div>
					<label >Geo location</label>
					<input type='text'  tabindex='<?= ($tab_order + 3) ?>' name='slideshowItem[s_id][photos][<?=$photo->photo_id?>][geo_location]' id='slideshowItem[s_id][photos][<?=$photo->photo_id?>][geo_location]' size='20' value='<?= $photo->geo_location ?>' class='editable' title='Click to edit' /<img onClick='showMapForPhoto(this.previousSibling.previousSibling.value);' class='map_icon centervertical' src='images/map_icon.jpg' />
				</div>
				<div>
					<label >Original URL</label>
					<input type='text'  tabindex='<?= ($tab_order + 4) ?>' id='slideshowItem[s_id][photos][<?=$photo->photo_id?>][original_url]' name='slideshowItem[s_id][photos][<?=$photo->photo_id?>][original_url]' size='20' value='<?= $photo->original_url ?>'  title='Click to edit' class='editable' />
				</div>
				<div>
					<label >Alt text <small>Specific to this Slideshow</small></label>
					<input type='text'  tabindex='<?= ($tab_order + 5) ?>'  name='slideshowItem[s_id][photos][<?=$photo->photo_id?>][alt]' size='20' value='<?= $photo->alt ?>' />
					
				</div>
			
				<div>
					<label >Caption</label>
					<textarea  tabindex='<?= ($tab_order + 6) ?>' name='slideshowItem[s_id][photos][<?=$photo->photo_id?>][caption]' id='slideshowItem[s_id][photos][<?=$photo->photo_id?>][caption]' rows='2' style='width:300px' class='<?= $this->plugin_prefix ?>required' ><?= $photo->caption?></textarea>
				</div>
	
			</div>
		</div>
		