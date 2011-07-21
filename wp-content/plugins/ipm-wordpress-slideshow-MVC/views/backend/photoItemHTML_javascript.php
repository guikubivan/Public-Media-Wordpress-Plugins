<? ?>
		<li  class='<?php echo $this->plugin_prefix ?>photo_container' id='photoContainer_<?php echo $id ?>'>
		<? 
		?>
		<span id='deletePhotoButton_<?php echo $id ?>_s_id' class='button' style='float:right' onclick='if ( this.parentNode.parentNode && this.parentNode.parentNode.removeChild ) {this.parentNode.parentNode.removeChild(this.parentNode);}'><img class='adjustIcon' src='<?php echo $this->plugin_url() ?>images/delete.gif' /></span>
		<? 
		if($itemV['update']){
			?>
			<input type='hidden' id='slideshowItem[s_id][photos][<?php echo $id ?>][update]' name='slideshowItem[s_id][photos][<?php echo $id ?>][update]' value='<?php echo $itemV['update'] ?>' />
			<? 
		}
		?>
		<input type='hidden' id='slideshowItem[s_id][photos][<?php echo $id ?>][cover]' class='photo_in_slideshow_s_id' name='slideshowItem[s_id][photos][<?php echo $id ?>][cover]' value='<?php echo $itemV['cover'] ?>' /> 
		<table>
			<tr>
				<td>Title: 
				</td>
				<td>
				<input tabindex='<?php echo ($this->tab_order + 1) ?>' type='text' id='slideshowItem[s_id][photos][<?php echo $id ?>][title]' name='slideshowItem[s_id][photos][<?php echo $id ?>][title]' size='20' value='<? 
		if($itemV['title']){
			?><?php echo$itemV['title']?><?
		}else{
			 ?>'+ convertquotes(document.getElementById('attachments[<?php echo $id ?>][post_title]').value) +'<? 
		}
		
		?>' class='<?php echo $this->plugin_prefix ?>required photo_title' />
				</td>
				<td rowspan='5' style='width:100%; text-align:center;'>
					<img id='img_s_id_<?php echo $id ?>' class='wpss_photo_thumb' title='Click to replace image' onclick='confirmChooseNewPhoto(s_id, <?php echo $id ?>);' src='<?php echo  $itemV['url']  ?>' /><div onclick='setCoverImage(s_id, <?php echo $id ?>);' id='cover_button_s_id_<?php echo $id ?>' class='button set_cover_button_s_id' style='text-align:center;margin-top:5px;' >Slideshow Thumbnail</div>
				</td>
			</tr>

			<tr>
				<td>Photo credit:
				</td>
				<td>
				<input tabindex='<?php echo ($this->tab_order + 2) ?>' type='text' name='slideshowItem[s_id][photos][<?php echo $id ?>][photo_credit]' id='slideshowItem[s_id][photos][<?php echo $id ?>][photo_credit]' size='20' ReadOnly value='<? 

		if($itemV['photo_credit']){
			?><?php echo$itemV['photo_credit']?><?
		}else{
			 ?>'+ convertquotes(document.getElementById('attachments[<?php echo $id ?>][photo_credit]').value) +'<? 
		}
		?>' title='Click to edit' class='editable <?php echo $this->plugin_prefix ?>required' /> <span class='button' style='display:none' >Update</span>
				</td>
			</tr>
			<tr>
				<td>Geo location:
				</td>
				<td>
				<input tabindex='<?php echo ($this->tab_order + 3) ?>' type='text' name='slideshowItem[s_id][photos][<?php echo $id ?>][geo_location]' id='slideshowItem[s_id][photos][<?php echo $id ?>][geo_location]' size='20' ReadOnly value='<? 
		if($itemV['geo_location']){
			?><?php echo$itemV['geo_location']?><?
		}else{
			 ?>'+ convertquotes(document.getElementById('attachments[<?php echo $id ?>][geo_location]').value) +'<? 
		}

		?>' class='editable' title='Click to edit' /> <span class='button' style='display:none' >Update</span>
				</td>

			</tr>
			<tr>
				<td>Original URL:
				</td>
				<td>
				<input tabindex='<?php echo ($this->tab_order + 4) ?>'type='text' id='slideshowItem[s_id][photos][<?php echo $id ?>][original_url]' name='slideshowItem[s_id][photos][<?php echo $id ?>][original_url]' size='20' ReadOnly value='<? 
		if($itemV['original_url']){
			?><?php echo $itemV['original_url'] ?><?
		}else{
			 ?>'+ convertquotes(document.getElementById('attachments[<?php echo $id ?>][original_url]').value) +'<? 
		}
	 	?>'  title='Click to edit' class='editable' /> <span class='button' style='display:none' >Update</span>
				</td>
			</tr>
			<tr>
				<td>Alt text:
				</td>
				<td>
				<input type='text' tabindex='<?php echo ($this->tab_order + 5) ?>'name='slideshowItem[s_id][photos][<?php echo $id ?>][alt]' size='20' value='<? 
		if($itemV['alt']){
			?><?php echo $itemV['alt']?><?
		}else{
			 ?>'+ convertquotes(removeHTMLTags(document.getElementById('attachments[<?php echo $id ?>][post_excerpt]').value)) +'<? 
		}
		?>' /><small>Specific to this Slideshow</small>
				
				</td>
			</tr>
			<tr>
				<td>Caption:
				</td>
				<td style='width:auto;'>
					<textarea name='slideshowItem[s_id][photos][<?php echo $id ?>][caption]' id='slideshowItem[s_id][photos][<?php echo $id ?>][caption]' tabindex='<?php echo ($this->tab_order + 6) ?>' rows='2' style='width:300px' class='<?php echo $this->plugin_prefix ?>required' ><? 
		if($itemV['caption']){
			?><?php echo $itemV['caption']?><?
		}else{
			 ?>'+ convertquotes(document.getElementById('attachments[<?php echo $id ?>][post_content]').value) +'<? 
		}
		?></textarea>
				</td>

			</tr>

			</table>
		</li>