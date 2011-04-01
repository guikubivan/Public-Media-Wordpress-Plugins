
<div style='padding:10px;' id='slideshow_options' >
	<div style='float:right'>
		<select style='display: none;' id='wpss_post_photo' class='".$this->plugin_prefix."required' name='wpss_post_photo'>
			<option value=''>Choose post image</option>
		</select>
	</div>
</div>
<ul id="slideshows_wrapper_ul">
	<? foreach($slideshow_editors as $key => $slideshow_editor)
	{
		?>
		<li>
			<?=$slideshow_editor?>
		</li>	
		<?
	}
	?>
	
</ul>
<span id='slideshow_button' class='button' style='margin-top:10px;' 
		onclick="addSlideshow()"> 
		Add Another Slideshow
</span>