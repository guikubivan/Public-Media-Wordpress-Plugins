

<div class='<?=$this->plugin_prefix?>slideshow_container' id='slideshowContainer_<?=$slideshow->slideshow_id?>'>
	<?//if(!$edit_mode){?>
	<div class='button' style="float: right;" onclick='jQuery(this).parent().parent().fadeOut(500, function(){jQuery(this).remove();});'>Remove Slideshow</div>
	<div class='button slideshow_collapse_button' onclick="jQuery(this).siblings('div.slideshow_wrapper').toggle(); jQuery(this).html( jQuery(this).html()=='Collapse'?'Expand':'Collapse');" style="float: right;" >Collapse</div>
	<?//}?>

	
	<h4 class='organizer_item_heading' id='slideshow_heading_<?=$slideshow->slideshow_id?>'>Slideshow</h4>
	<input type='hidden' name='slideshowItem[<?=$slideshow->slideshow_id?>][update]' id='slideshowItem[<?=$slideshow->slideshow_id?>][update] value='<? /*$slideshow->update*/ ?>' /> 
	
	
	
	<div class='slideshow_wrapper'>
		<div class='slideshow_fields'>
			<div style="float: right;">	
				<label for="slideshow_field<?=$slideshow->slideshow_id?>][description]">Slideshow description</label>
				<textarea tabindex='<?= ($this->tab_order+2)?>' id='slideshow_field[<?= $slideshow->slideshow_id ?>][description]' name='slideshow_field[<?= $slideshow->slideshow_id ?>][description]' style='width:100%;height:100%;' class='<?= $this->plugin_prefix ?>required' ><?= $slideshow->description ?></textarea>
			</div>	
			<div>
				<label for="slideshow_field[<?=$slideshow->slideshow_id?>][title]">Slideshow title</label>
				<input type='text' tabindex='<?= ($this->tab_order +1) ?>' id='slideshow_field[<?= $slideshow->slideshow_id ?>][title]' name='slideshow_field[<?= $slideshow->slideshow_id ?>][title]' style='width:$fieldW' value='<?=  $slideshow->title ?>' class='<?= $this->plugin_prefix ?>required' />
			</div>	
			<div>	
				<label for="slideshow_field<?=$slideshow->slideshow_id?>][photo_credit]">Slideshow photo credit</label>
				<input type='text' tabindex='<?= ($this->tab_order+3)?>' name='slideshow_field[<?= $slideshow->slideshow_id ?>][photo_credit]' id='slideshow_field[<?= $slideshow->slideshow_id ?>][photo_credit]' style='width:$fieldW' value='<?=  $slideshow->photo_credit ?>' />
			</div>	
			<div>	
				<label for="slideshow_field<?=$slideshow->slideshow_id?>][geo_location]">Slideshow geo location</label>
				<input type='text' tabindex='<?= ($this->tab_order+4)?>' name='slideshow_field[<?= $slideshow->slideshow_id ?>][geo_location]' id='slideshow_field[<?= $slideshow->slideshow_id ?>][geo_location]' style='width:50%' value='<?=  $slideshow->geo_location ?>' onkeyup='slideshow_getCoords(this.id);' onblur='slideshow_getCoords(this.id);' />
				<img onClick='showMap(this.previousSibling.value,this.nextSibling.id, this.nextSibling.nextSibling.id, this);' class='map_icon centervertical' src='images/map_icon.jpg' />
			</div>
			<input type='hidden' id='slideshowItem[<?= $slideshow->slideshow_id ?>][latitude]' name='slideshowItem[<?= $slideshow->slideshow_id ?>][latitude]' ReadOnly size='4' value='<?=  $slideshow->latitude ?>' />
			<input type='hidden' id='slideshowItem[<?= $slideshow->slideshow_id ?>][longitude]' name='slideshowItem[<?= $slideshow->slideshow_id ?>][longitude]' ReadOnly size='4' value='<?=  $slideshow->longitude ?>' /><!--&#176; latitude--><!--&#176; longitude-->
		</div>
	
	<ul>
		<? foreach($photo_editors as $key => $photo_editor)
		{
		?>
		<li>
			<?=$photo_editor?>
		</li>		
		<?	
		}?>
		<li>
			<span id='addphoto_button_<?= $slideshow->slideshow_id ?>' class='button' style='margin-left:45%;' class='alignright' onClick='pickPhoto(this.previousSibling.id);' tip='Add Media'>Add photo</span>
		</li>
	</ul>
	
	
		<table style="width: 100%" >
			this->slideshowFields($slideshow->slideshow_id, $itemV);
	
	This is a slideshow.<br />
	These are the photos:<br />


	
	
		</table>
	</div>
</div>