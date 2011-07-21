<?php  

if($type == "slideshows")
{
	foreach($slideshows as $key => $slideshow)
	{
		//print_r($slideshow->thumb);
?>
<div class="program-slideshow">

	<h3><?php echo $slideshow->title ?></h3>

	<div class="scrollable" rel="#overlay<?php echo$slideshow->slideshow_id ?>"><ul class="items">
	<!-- here goes the intro slide stuff -->
	
		<li class="item">
		
			<a rel="slideshow-colorbox" class="colorbox" href="<?php echo $slideshow->thumb->large_url ?>" >
				<img alt="<?php echo $slideshow->description ?>" src="<?php echo $slideshow->thumb->large_url ?>" />
				<div class="slideshow-enlarge enlarge-title"></div>
			</a>
			
			<p class="photo-number">
			<?php echo count($slideshow->photos)?>
			 Images</p>
		
		<? if( !empty($slideshow->thumb->original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?php echo $slideshow->thumb->original_url ?>"><?php echo $slideshow->thumb->photo_credit ?></a></p>
		<? } else { ?>		
				<p class="photo-credit">Slideshow: <?php echo $slideshow->thumb->photo_credit ?></p>
		<? } ?>
		
			<div class="clear"></div>
		<p class="photo-caption"><?php echo $slideshow->thumb->description ?></p>
	</li><!--item-->
	
	<!-- and now, the rest of the images -->
	<? foreach ($slideshow->photos as $key => $photo) { ?>
	<li class="item">
		<? if( !empty($photo->url) ) { ?>
			<a rel="slideshow-colorbox" class="colorbox" href="<?php echo $photo->large_url ?>" >
			<? if(!empty($photo->thumb_url) ) { ?>
				<img alt="<?php echo $photo->alt ?>" src="<?php echo $photo->large_url ?>" />	
			<? } ?>
			<div class="slideshow-enlarge enlarge-slide"></div>
			</a>
			
			<p class="photo-number">Image <?php echo $key + 1 ?>
			of
			<?php echo count($slideshow->photos) ?>
			</p>
			
			<? if(!empty($photo->
			original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?php echo $photo->original_url ?>"><?php echo $photo->photo_credit ?></a></p>
			<? } else { ?>
				<p class="photo-credit">Photo: <?php echo $photo->photo_credit ?></p>
			<? } ?>
			<div class="clear"></div>
			<p class="photo-caption"><?php echo $photo->caption ?></p>
		<? } ?>

	</li><!--item-->

	<? } ?>

	</ul><!--items-->
	</div><!--scrollable-->

	<a class="scrollable-nav prevPage"></a>
	<a class="scrollable-nav nextPage"></a>
	
</div><!-- program-slideshow -->



<div class="the-overlay" id="overlay]]><?php echo $slideshow->slideshow_id ?>" style="display:none;">

	<h3><?php echo $slideshow->title ?></h3>
	
	<div class="scrollable"><ul class="items">
	
	
	
	<li><a class="colorbox"><img alt="<?php echo $slideshow->alt ?>" src="<?php echo $slideshow->thumb->large_url ?>" /></a>
				
				<p class="photo-number">
			<?php echo count($slideshow->photos) ?>
			Images</p>
			
				<? if(!empty($slideshow->thumb->original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?php echo $slideshow->thumb->original_url?>"><?php echo $slideshow->thumb->photo_credit ?></a></p>
			<? } else { ?>
				<p class="photo-credit">Slideshow: <?php echo $slideshow->thumb->photo_credit ?></p>
			<? } ?>
		<div class="clear"></div>
		<p class="photo-caption"><?php echo $slideshow->thumb->description ?></p>
		</li>
	
	
	
	
	<? foreach ($slideshow->photos as $key => $photo) { ?>
		<li><a class="colorbox"><img alt="" src="<?php echo $photo->large_url ?>" /><a>
				
				<p class="photo-number">Image <?php echo $key + 1 ?>"/>
			of
			<?php echo count($slideshow->photos)?>
			</p>
				
			<? if(!empty($photo->original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?php echo $photo->original_url ?>"><?php echo $photo->photo_credit ?></a></p>
			<? } else { ?>
				<p class="photo-credit">Photo: <?php echo $photo->photo_credit ?></p>
			<? } ?>
		<div class="clear"></div>
		<p class="photo-caption"><?php echo $photo->caption ?></p>
		</li>
	<? } ?>
	</ul></div> <!--the-images-->
	<a class="prevPage"></a> 
	<a class="nextPage"></a> 
</div><!-- the-overlay --> 


<?
	}
}
else if($type == "photo")
{
	?>
		<? if(!empty($photo->url)) { ?>
			<div class="postimage-wrapper"><div class="the-image-wrapper"><img class="postimage" src="<?php echo $photo->large_url?>" alt="<?php echo $photo->alt?>" /></div>
		<? } ?>
		
        <? if(!empty($photo->original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?php echo $photo->original_url?>"><?php echo $photo->photo_credit?></a></p>
		<? } else { ?>
				<p class="photo-credit">Photo: <?php echo $photo->photo_credit?></p>
		<? } ?>
		
		<div class="clear"></div>
		
        <? if(!empty($photo->caption) ) { ?>
			<p class="photo-caption"><?php echo $photo->caption?></p>
		<? } ?>
		
		<? if(!empty($photo->url)) { ?>
			</div><!--end postimage-wrapper-->
		<? } ?>
<?
}
?>
