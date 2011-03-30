<?php  

if($type == "slideshows")
{
	foreach($slideshows as $key => $slideshow)
	{
		//print_r($slideshow->thumb);
?>
<div class="program-slideshow">

	<h3><?= $slideshow->title ?></h3>

	<div class="scrollable" rel="#overlay<?=$slideshow->slideshow_id ?>"><ul class="items">
	<!-- here goes the intro slide stuff -->
	
		<li class="item">
		
			<a rel="slideshow-colorbox" class="colorbox" href="<?= $slideshow->thumb->large_url ?>" >
				<img alt="<?= $slideshow->description ?>" src="<?= $slideshow->thumb->large_url ?>" />
				<div class="slideshow-enlarge enlarge-title"></div>
			</a>
			
			<p class="photo-number">
			<?= count($slideshow->photos)?>
			 Images</p>
		
		<? if( !empty($slideshow->thumb->original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?= $slideshow->thumb->original_url ?>"><?= $slideshow->thumb->photo_credit ?></a></p>
		<? } else { ?>		
				<p class="photo-credit">Slideshow: <?= $slideshow->thumb->photo_credit ?></p>
		<? } ?>
		
			<div class="clear"></div>
		<p class="photo-caption"><?= $slideshow->thumb->description ?></p>
	</li><!--item-->
	
	<!-- and now, the rest of the images -->
	<? foreach ($slideshow->photos as $key => $photo) { ?>
	<li class="item">
		<? if( !empty($photo->url) ) { ?>
			<a rel="slideshow-colorbox" class="colorbox" href="<?= $photo->large_url ?>" >
			<? if(!empty($photo->thumb_url) ) { ?>
				<img alt="<?= $photo->alt ?>" src="<?= $photo->large_url ?>" />	
			<? } ?>
			<div class="slideshow-enlarge enlarge-slide"></div>
			</a>
			
			<p class="photo-number">Image <?= $key + 1 ?>
			of
			<?= count($slideshow->photos) ?>
			</p>
			
			<? if(!empty($photo->
			original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?= $photo->original_url ?>"><?= $photo->photo_credit ?></a></p>
			<? } else { ?>
				<p class="photo-credit">Photo: <?= $photo->photo_credit ?></p>
			<? } ?>
			<div class="clear"></div>
			<p class="photo-caption"><?= $photo->caption ?></p>
		<? } ?>

	</li><!--item-->

	<? } ?>

	</ul><!--items-->
	</div><!--scrollable-->

	<a class="scrollable-nav prevPage"></a>
	<a class="scrollable-nav nextPage"></a>
	
</div><!-- program-slideshow -->



<div class="the-overlay" id="overlay]]><?= $slideshow->slideshow_id ?>" style="display:none;">

	<h3><?= $slideshow->title ?></h3>
	
	<div class="scrollable"><ul class="items">
	
	
	
	<li><a class="colorbox"><img alt="<?= $slideshow->alt ?>" src="<?= $slideshow->thumb->large_url ?>" /></a>
				
				<p class="photo-number">
			<?= count($slideshow->photos) ?>
			Images</p>
			
				<? if(!empty($slideshow->thumb->original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?= $slideshow->thumb->original_url?>"><?= $slideshow->thumb->photo_credit ?></a></p>
			<? } else { ?>
				<p class="photo-credit">Slideshow: <?= $slideshow->thumb->photo_credit ?></p>
			<? } ?>
		<div class="clear"></div>
		<p class="photo-caption"><?= $slideshow->thumb->description ?></p>
		</li>
	
	
	
	
	<? foreach ($slideshow->photos as $key => $photo) { ?>
		<li><a class="colorbox"><img alt="" src="<?= $photo->large_url ?>" /><a>
				
				<p class="photo-number">Image <?= $key + 1 ?>"/>
			of
			<?= count($slideshow->photos)?>
			</p>
				
			<? if(!empty($photo->original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?= $photo->original_url ?>"><?= $photo->photo_credit ?></a></p>
			<? } else { ?>
				<p class="photo-credit">Photo: <?= $photo->photo_credit ?></p>
			<? } ?>
		<div class="clear"></div>
		<p class="photo-caption"><?= $photo->caption ?></p>
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
			<div class="postimage-wrapper"><div class="the-image-wrapper"><img class="postimage" src="<?=$photo->large_url?>" alt="<?=$photo->alt?>" /></div>
		<? } ?>
		
        <? if(!empty($photo->original_url) ) { ?>
				<p class="photo-credit">Photo: <a href="<?=$photo->original_url?>"><?=$photo->photo_credit?></a></p>
		<? } else { ?>
				<p class="photo-credit">Photo: <?=$photo->photo_credit?></p>
		<? } ?>
		
		<div class="clear"></div>
		
        <? if(!empty($photo->caption) ) { ?>
			<p class="photo-caption"><?=$photo->caption?></p>
		<? } ?>
		
		<? if(!empty($photo->url)) { ?>
			</div><!--end postimage-wrapper-->
		<? } ?>
<?
}
?>
