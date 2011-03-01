	<tr>
				<td style='width:auto'>Slideshow title: 
				</td>
				<td style='width:auto;'> 
				<input type='text' tabindex='<?= ($this->tab_order +1) ?>' id='slideshowItem[<?= $id ?>][title]' name='slideshowItem[<?= $id ?>][title]' style='width:$fieldW' value='<?=  $itemV['title'] ?>' class='<?= $this->plugin_prefix ?>required' />
				</td>
				<td style='vertical-align:top;width:auto;' rowspan='3'>
				Slideshow description:<br />
				<textarea tabindex='<?= ($this->tab_order+4)?>' id='slideshowItem[<?= $id ?>][description]' name='slideshowItem[<?= $id ?>][description]' style='width:100%;height:100%;' class='<?= $this->plugin_prefix ?>required' ><?= $itemV['description'] ?></textarea>
				</td>
			</tr>
			<tr>
				<td>Slideshow photo credit:
				</td>
				<td>
				<input type='text' tabindex='<?= ($this->tab_order+2)?>' name='slideshowItem[<?= $id ?>][photo_credit]' style='width:$fieldW' value='<?=  $itemV['photo_credit'] ?>' />
				</td>
			</tr>
			<tr>
				<td class='topalign'>Slideshow geo location:
				</td>
				<td>
				<input type='text' tabindex='<?= ($this->tab_order+3)?>' name='slideshowItem[<?= $id ?>][geo_location]' id='slideshowItem[<?= $id ?>][geo_location]' style='width:50%' value='<?=  $itemV['geo_location'] ?>' onkeyup='slideshow_getCoords(this.id);' onblur='slideshow_getCoords(this.id);' />
				<img onClick='showMap(this.previousSibling.value,this.nextSibling.id, this.nextSibling.nextSibling.id, this);' class='map_icon centervertical' src='<?= $this->plugin_url() ?>images/map_icon.jpg' />
				<input type='hidden' id='slideshowItem[<?= $id ?>][latitude]' name='slideshowItem[<?= $id ?>][latitude]' ReadOnly size='4' value='<?=  $itemV['latitude'] ?>' />
				<input type='hidden' id='slideshowItem[<?= $id ?>][longitude]' name='slideshowItem[<?= $id ?>][longitude]' ReadOnly size='4' value='<?=  $itemV['longitude'] ?>' /><!--&#176; latitude--><!--&#176; longitude-->
				</td>

			</tr>

			</table>
				<ul class='sortable' style='margin-bottom: 10px;' id='<?= $this->plugin_prefix ?>slideshow_photos_ul_<?= $id ?>'></ul><span id='addphoto_button_<?= $id ?>' class='button' style='margin-left:45%;' class='alignright' onClick='pickPhoto(this.previousSibling.id);' tip='Add Media'>Add photo</span></li>
