
<div class='<?=$this->plugin_prefix?>slideshow_container' id='slideshowContainer_<?=$slideshow->slideshow_id?>'>
	<?//if(!$edit_mode){?>
	<div class='button' style="float: right;" onclick='if(confirm("Are you sure you want to remove the Slideshow ?") ) {removeSlideshow("<?=$slideshow->slideshow_id?>") }' >Remove Slideshow</div>
	<div class='button slideshow_collapse_button' onclick="jQuery(this).siblings('div.slideshow_wrapper').toggle(); jQuery(this).html( jQuery(this).html()=='Collapse'?'Expand':'Collapse');" style="float: right;" >Collapse</div>
	<?//}?>

	
	<h4 class='organizer_item_heading' id='slideshow_heading_<?=$slideshow->slideshow_id?>'>Slideshow</h4>
	<input type='hidden' name='slideshowItem[<?=$slideshow->slideshow_id?>][update]' id='slideshowItem[<?=$slideshow->slideshow_id?>][update] value='<? /*$slideshow->update*/ ?>' /> 
	
	
	
	<div class='slideshow_wrapper'>
		<div class='slideshow_fields'>
			<div style="float: right;">	
				<label for="slideshow_<?=$slideshow->slideshow_id?>_description">Slideshow description</label>
				<textarea id='slideshow_<?= $slideshow->slideshow_id ?>_description' name='slideshow[<?= $slideshow->slideshow_id ?>][description]' rows='6' style='width:300px;' class='<?= $this->plugin_prefix ?>required' ><?= $slideshow->description ?></textarea>
			</div>	
			<div>
				<label for="slideshow_field_<?=$slideshow->slideshow_id?>_title">Slideshow title</label>
				<input type='text' id='slideshow_<?= $slideshow->slideshow_id ?>_title' name='slideshow[<?= $slideshow->slideshow_id ?>][title]' style='width:$fieldW' value='<?=  $slideshow->title ?>' class='<?= $this->plugin_prefix ?>required' />
			</div>				
			<div>	
				<label for="slideshow_<?=$slideshow->slideshow_id?>_photo_credit">Slideshow photo credit</label>
				<input type='text' name='slideshow[<?= $slideshow->slideshow_id ?>][photo_credit]' id='slideshow_<?= $slideshow->slideshow_id ?>_photo_credit' style='width:$fieldW' value='<?=  $slideshow->photo_credit ?>' />
			</div>	
			<div>	
				<label for="slideshow_<?=$slideshow->slideshow_id?>_geo_location">Slideshow geo location</label>
				<input type='text' name='slideshow[<?= $slideshow->slideshow_id ?>][geo_location]' id='slideshow_<?= $slideshow->slideshow_id ?>_geo_location' style='width:50%' value='<?=  $slideshow->geo_location ?>' />
				<? /*<img onClick='showMap(this.previousSibling.value,this.nextSibling.id, this.nextSibling.nextSibling.id, this);' class='map_icon centervertical' src='images/map_icon.jpg' /> */?>
			</div>

			<div>	
				<span tip="Save Slideshow Info" onclick="ajax_update_slideshow('<?=$slideshow->slideshow_id?>')" class="button" style="white-space: nowrap;">Save Slideshow Info</span>
			</div>
			<input type='hidden' id='slideshowItem[<?= $slideshow->slideshow_id ?>][latitude]' name='slideshowItem[<?= $slideshow->slideshow_id ?>][latitude]' ReadOnly size='4' value='<?=  $slideshow->latitude ?>' />
			<input type='hidden' id='slideshowItem[<?= $slideshow->slideshow_id ?>][longitude]' name='slideshowItem[<?= $slideshow->slideshow_id ?>][longitude]' ReadOnly size='4' value='<?=  $slideshow->longitude ?>' /><!--&#176; latitude--><!--&#176; longitude-->
		</div>
	
		<ul slideshow_id="<?=$slideshow->slideshow_id?>" >
			<? 
			$count = 0;
			foreach($photo_editors as $key => $photo_editor)
			{
			?>
			<li order="<?= $count ?>" >
				<?=$photo_editor?>
			</li>		
			<?	
				$count ++;
			}?>
			<li class="add_li" id="slideshow_<?=$slideshow->slideshow_id?>_add_button" >
				<span id='addphoto_button_<?= $slideshow->slideshow_id ?>' class='button' style='margin-left:45%;' class='alignright' onClick='pickPhoto(<?= $slideshow->slideshow_id ?>);' tip='Add Media'>Add photo</span>
			</li>
		</ul>
		
		
		
		
	</div>
</div>
<script type="text/javascript">
	
</script>
	