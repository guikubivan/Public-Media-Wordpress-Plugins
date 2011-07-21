
		<li  class='<?php echo $this->plugin_prefix ?>photo_container' id='photoContainer_<?php echo$id?>'>
	<? if($itemV['update']){	?>
			<input type='hidden' id='slideshowItem[s_id][photos][<?php echo$id?>][update]' name='slideshowItem[s_id][photos][<?php echo$id?>][update]' value='<?php echo$itemV['update']?>' />
		<? if(!$this->utils->wp_exist_post($this->get_wp_photo_id($id))){ ?>
				<div id='transparency_s_id_<?php echo$id?>' class='missing_photo_transparency' ><div style='margin-bottom:50px;'>Photo is missing</div><span class='button' style='padding:10px;' onclick='chooseNewPhoto(s_id, <?php echo$id?>);'>Set new photo</span></div>
	<?
			}
		} 
	?>

		<span id='deletePhotoButton_<?php echo $id ?>_s_id' class='button' style='float:right' onclick='if ( this.parentNode.parentNode && this.parentNode.parentNode.removeChild ) {this.parentNode.parentNode.removeChild(this.parentNode);removePhotoItem(this.id);}'><img class='adjustIcon' src=' <?php echo $this->plugin_url() ?>images/delete.gif' /></span>

		<input type='hidden' id='slideshowItem[s_id][photos][<?php echo$id?>][cover]' class='photo_in_slideshow_s_id' name='slideshowItem[s_id][photos][<?php echo$id?>][cover]' value='<?php echo$itemV['cover']?>' /> 
			<table>
			<tr>
				<td>Title: 
				</td>
				<td>
				<input type='text'  tabindex='<?php echo($this->tab_order + 1)?>' id='slideshowItem[s_id][photos][<?php echo $id ?>][title]' name='slideshowItem[s_id][photos][<?php echo $id ?>][title]' size='20' value='<? 
			if($itemV['title']){ 
				?><?php echo $itemV['title']?><?
		
			}else if($doJS){
			
				?><!-- UNESCAPE ['+ convertquotes(document.getElementById('<?php echo $id ?>[title]').value) +'] --><?
			}
				?>' class='<?php echo $this->plugin_prefix ?>required photo_title' />
				</td>
				<td rowspan='5' style='text-align:center;width: 100%'>
					<img id='img_s_id_<?php echo$id?>' class='wpss_photo_thumb' title='Click to replace image' onclick='confirmChooseNewPhoto(s_id, <?php echo$id?>);' src='<?php echo $itemV['url'] ?>' /><br />
					<span onclick='setCoverImage(s_id, <?php echo$id?>);' id='cover_button_s_id_<?php echo$id?>' class='button set_cover_button_s_id' style='text-align:center;margin-top:5px;' >Slideshow Thumbnail</span>
				</td>
			</tr>
			<tr>
				<td>Photo credit:
				</td>
				<td>
				<input type='text'  tabindex='<?php echo ($this->tab_order + 2) ?>' id='slideshowItem[s_id][photos][<?php echo$id?>][photo_credit]' name='slideshowItem[s_id][photos][<?php echo$id?>][photo_credit]' size='20' ReadOnly value='<?php echo $itemV['photo_credit'] ?>'  title='Click to edit' class='editable <?php echo $this->plugin_prefix ?>required' /> <span class='button' style='display:none' >Update</span>
				</td>
			</tr>
			<tr>
				<td>Geo location:
				</td>
				<td>
				<input type='text'  tabindex='<?php echo ($this->tab_order + 3) ?>' name='slideshowItem[s_id][photos][<?php echo$id?>][geo_location]' id='slideshowItem[s_id][photos][<?php echo$id?>][geo_location]' size='20' ReadOnly value='<?php echo $itemV['geo_location'] ?>' class='editable' title='Click to edit' /><span class='button' style='display:none' >Update</span><img onClick='showMapForPhoto(this.previousSibling.previousSibling.value);' class='map_icon centervertical' src='<?php echo $this->plugin_url()?>images/map_icon.jpg' />
				</td>

			</tr>
			<tr>
				<td>Original URL:
				</td>
				<td>
				<input type='text'  tabindex='<?php echo ($this->tab_order + 4) ?>' id='slideshowItem[s_id][photos][<?php echo$id?>][original_url]' name='slideshowItem[s_id][photos][<?php echo$id?>][original_url]' size='20' ReadOnly value='<?php echo $itemV['original_url'] ?>'  title='Click to edit' class='editable' /> <span class='button' style='display:none' >Update</span>
				</td>
			</tr>
			<tr>
				<td>Alt text:
				</td>
				<td>
				<input type='text'  tabindex='<?php echo ($this->tab_order + 5) ?>'  name='slideshowItem[s_id][photos][<?php echo$id?>][alt]' size='20' value='<?php echo $itemV['alt'] ?>' />
				<small>Specific to this Slideshow</small>
				</td>
			</tr>


			<tr>
				<td>Caption:
				</td>
				<td style='width:auto'>
					<textarea  tabindex='<?php echo ($this->tab_order + 6) ?>' name='slideshowItem[s_id][photos][<?php echo$id?>][caption]' id='slideshowItem[s_id][photos][<?php echo$id?>][caption]' rows='2' style='width:300px' class='<?php echo $this->plugin_prefix ?>required' ><?php echo $itemV['caption']?></textarea>
				</td>

			</tr>

			</table>
		</li>
		