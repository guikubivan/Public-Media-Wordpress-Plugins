	<tr>
				<td style='width:auto'>Slideshow title: 
				</td>
				<td style='width:auto;'> 
				<input type='text' tabindex='<?php echo ($this->tab_order +1) ?>' id='slideshowItem[<?php echo $id ?>][title]' name='slideshowItem[<?php echo $id ?>][title]' style='width:$fieldW' value='<?php echo  $itemV['title'] ?>' class='<?php echo $this->plugin_prefix ?>required' />
				</td>
				<td style='vertical-align:top;width:auto;' rowspan='3'>
				Slideshow description:<br />
				<textarea tabindex='<?php echo ($this->tab_order+4)?>' id='slideshowItem[<?php echo $id ?>][description]' name='slideshowItem[<?php echo $id ?>][description]' style='width:100%;height:100%;' class='<?php echo $this->plugin_prefix ?>required' ><?php echo $itemV['description'] ?></textarea>
				</td>
			</tr>
			<tr>
				<td>Slideshow photo credit:
				</td>
				<td>
				<input type='text' tabindex='<?php echo ($this->tab_order+2)?>' name='slideshowItem[<?php echo $id ?>][photo_credit]' style='width:$fieldW' value='<?php echo  $itemV['photo_credit'] ?>' />
				</td>
			</tr>
			<tr>
				<td class='topalign'>Slideshow geo location:
				</td>
				<td>
				<input type='text' tabindex='<?php echo ($this->tab_order+3)?>' name='slideshowItem[<?php echo $id ?>][geo_location]' id='slideshowItem[<?php echo $id ?>][geo_location]' style='width:50%' value='<?php echo  $itemV['geo_location'] ?>' onkeyup='slideshow_getCoords(this.id);' onblur='slideshow_getCoords(this.id);' />
				<img onClick='showMap(this.previousSibling.value,this.nextSibling.id, this.nextSibling.nextSibling.id, this);' class='map_icon centervertical' src='<?php echo $this->plugin_url() ?>images/map_icon.jpg' />
				<input type='hidden' id='slideshowItem[<?php echo $id ?>][latitude]' name='slideshowItem[<?php echo $id ?>][latitude]' ReadOnly size='4' value='<?php echo  $itemV['latitude'] ?>' />
				<input type='hidden' id='slideshowItem[<?php echo $id ?>][longitude]' name='slideshowItem[<?php echo $id ?>][longitude]' ReadOnly size='4' value='<?php echo  $itemV['longitude'] ?>' /><!--&#176; latitude--><!--&#176; longitude-->
				</td>

			</tr>

			</table>
				<ul class='sortable' style='margin-bottom: 10px;' id='<?php echo $this->plugin_prefix ?>slideshow_photos_ul_<?php echo $id ?>'></ul><span id='addphoto_button_<?php echo $id ?>' class='button' style='margin-left:45%;' class='alignright' onClick='pickPhoto(this.previousSibling.id);' tip='Add Media'>Add photo</span></li>
