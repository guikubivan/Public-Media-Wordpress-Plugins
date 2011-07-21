
<div style='padding:10px;' id='slideshow_options' >
	<div style='float:right'>
		<select style='' id='wpss_post_photo' class='<?php echo $this->plugin_prefix."required" ?>' name='wpss_post_photo' onchange='setPostImage(this.value)'>
			<option value=''>Choose post image</option>
			<?php foreach($post_image_menu_titles as $id => $value)
		{
		if($id == $post_image_id){
		?>
		<option value="<?php echo $id ?>" selected="selected"><?php echo $value ?></option>
		<?php
		}
		else{
		?>
		<option value="<?php echo $id ?>"><?php echo $value ?></option>
		<?php
		}
		}
		?>
		</select>
	</div>
</div>
</br>
<ul id="slideshows_wrapper_ul">
	<? foreach($slideshow_editors as $key => $slideshow_editor)
	{
		?>
		<li>
			<?php echo $slideshow_editor?>
		</li>	
		<?
	}
	?>
	
</ul>
<span id='slideshow_button' class='button' style='margin-top:10px;' 
		onclick="addSlideshow()"> 
		Add Another Slideshow
</span>