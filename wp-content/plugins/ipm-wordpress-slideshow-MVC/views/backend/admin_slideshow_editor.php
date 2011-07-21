
<div class='<?php echo $this->plugin_prefix?>slideshow_container' id='slideshowContainer_<?php echo $slideshow->slideshow_id?>'>
	<?//if(!$edit_mode){?>
	<div class='button' style="float: right;" onclick='if(confirm("Are you sure you want to remove the Slideshow ?") ) {removeSlideshow("<?php echo $slideshow->slideshow_id?>") }' >Remove Slideshow</div>
	<div class='button slideshow_collapse_button' onclick="jQuery(this).siblings('div.slideshow_wrapper').toggle(); jQuery(this).html( jQuery(this).html()=='Collapse'?'Expand':'Collapse');" style="float: right;" >Collapse</div>
	<?//}?>

	
	<h4 class='organizer_item_heading' id='slideshow_heading_<?php echo $slideshow->slideshow_id?>'>Slideshow</h4>
	<input type='hidden' name='slideshowItem[<?php echo $slideshow->slideshow_id?>][update]' id='slideshowItem[<?php echo $slideshow->slideshow_id?>][update] value='<? /*$slideshow->update*/ ?>' /> 
	
	
	
	<div class='slideshow_wrapper'>
		<div class='slideshow_fields'>
			<div style="float: right;">	
				<label for="slideshow_<?php echo $slideshow->slideshow_id?>_description">Slideshow description</label>
				<textarea id='slideshow_<?php echo $slideshow->slideshow_id ?>_description' name='slideshow[<?php echo $slideshow->slideshow_id ?>][description]'  style='width:300px; height:125px' class='<?php echo $this->plugin_prefix ?>required' ><?php echo $slideshow->description ?></textarea>
			</div>	
			<div>
				<label for="slideshow_field_<?php echo $slideshow->slideshow_id?>_title">Slideshow title</label>
				<input type='text' id='slideshow_<?php echo $slideshow->slideshow_id ?>_title' name='slideshow[<?php echo $slideshow->slideshow_id ?>][title]' style='width:50%' value='<?php echo  $slideshow->title ?>' class='<?php echo $this->plugin_prefix ?>required' />
			</div>				
			<div>	
				<label for="slideshow_<?php echo $slideshow->slideshow_id?>_photo_credit">Slideshow photo credit</label>
				<input type='text' name='slideshow[<?php echo $slideshow->slideshow_id ?>][photo_credit]' id='slideshow_<?php echo $slideshow->slideshow_id ?>_photo_credit' style='width:50%' value='<?php echo  $slideshow->photo_credit ?>' />
			</div>	
			<div>	
				<label for="slideshow_<?php echo $slideshow->slideshow_id?>_geo_location">Slideshow geo location</label>
				<input type='text' name='slideshow[<?php echo $slideshow->slideshow_id ?>][geo_location]' id='slideshow_<?php echo $slideshow->slideshow_id ?>_geo_location' style='width:50%' value='<?php echo $slideshow->geo_location ?>' />
				<? /*<img onClick='showMap(this.previousSibling.value,this.nextSibling.id, this.nextSibling.nextSibling.id, this);' class='map_icon centervertical' src='images/map_icon.jpg' /> */?>
			</div>

			<div>	
				<span tip="Save Slideshow Info" onclick="ajax_update_slideshow('<?php echo $slideshow->slideshow_id?>')" class="button" style="white-space: nowrap; display: none;">Save Slideshow Info</span>
			</div>
			<input type='hidden' id='slideshowItem[<?php echo $slideshow->slideshow_id ?>][latitude]' name='slideshowItem[<?php echo $slideshow->slideshow_id ?>][latitude]' ReadOnly size='4' value='<?php echo $slideshow->latitude ?>' />
			<input type='hidden' id='slideshowItem[<?php echo $slideshow->slideshow_id ?>][longitude]' name='slideshowItem[<?php echo $slideshow->slideshow_id ?>][longitude]' ReadOnly size='4' value='<?php echo $slideshow->longitude ?>' /><!--&#176; latitude--><!--&#176; longitude-->
		</div>
	
		<ul slideshow_id="<?php echo $slideshow->slideshow_id?>" >
			<? 
			$count = 0;
			foreach($photo_editors as $key => $photo_editor)
			{
			?>
			<li order="<?php echo $count ?>" >
				<?php echo $photo_editor?>
			</li>		
			<?	
				$count ++;
			}?>
			<li class="add_li" id="slideshow_<?php echo $slideshow->slideshow_id?>_add_button" >
				<span id='addphoto_button_<?php echo $slideshow->slideshow_id ?>' class='button' style='margin-left:45%;' class='alignright' onClick='pickPhoto(<?php echo $slideshow->slideshow_id ?>);' tip='Add Media'>Add photo</span>
			</li>
		</ul>
		
		
		
		
	</div>
</div>
<script type="text/javascript">
	
</script>
	