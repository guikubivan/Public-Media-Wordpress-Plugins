<style type="text/css">

	.select_column{
		color: #FFFFFF;
		background-color: #000000;
		text-align:center;
		height:50px;
		font-family: serif;
		font-style: italic;
		font-size: 13px;
		padding-bottom:0px;
	}
	img {
		height:50px;
	}

	.title_column{
		background-color: #C4AEAE;
	}

	.credit_column{
		background-color: #D0BF87;
	}

	.date_column{
		background-color: #A4B6B2;
	}
	
	.button {
		background-color:#E5E5E5;
		border:solid 1px #333333;
		padding:5px;
		cursor: pointer;
	}

	th, td {
		width: 25%;
	}
	.caption {
		margin:0px;
	}
</style> 

	<h2>Upload new photo</h2>
			<form enctype="multipart/form-data" method="post" action="<?= attribute_escape($form_action_url) ?>" class="media-upload-form type-form validate" id="<?= $type ?>-form">
				<input type="hidden" name="post_id" id="post_id" value="<?= (int) $post_id ?>" />
				<?= wp_nonce_field('media-form') ?>
				
				<script type="text/javascript">
				<!--
				jQuery(function($){
					var preloaded = $(".media-item.preloaded");
					if ( preloaded.length > 0 ) {
						preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
					}
					updateMediaForm();
				});
				-->
				</script>
				
				<?= $media_upload_form ?>
				
				<? if($img_id) { ?>
					<input type='hidden' name='img_id' value='<?= $img_id ?>' />
				<? } ?>
			</form>

	<hr />

	<h2 style='margin-bottom:50px'>Choose picture</h2>
	<div>
	
	<form method='post' name='search_form' action='?type=slideshow_image<?= $img_id_var ?>' >
	
	
	<input id="post-search-input" type="text" value="<?= $this->plugin->_post['see_all'] ? '' : $search ?>" name="s"/>
	<input type="submit" value="Search Photos"/> <input type="submit" name='see_all' value="See all"/>
	
	</form>
	</div>
	<?php

		if(!isset($_GET['order_by']) && !isset($_REQUEST['s']) && !isset($_POST['see_all'])){
			echo '<hr />';
			return;
		}
		if(!$msg){
	?>
	
	<div style='position:relative;text-align: left'>
	
		<? if($start > 0){ ?>
			<a href="?type=slideshow_image&post_id=$pid&order_by=$orderby&direction=$direction&porder_by=$orderby&start=$pstart <?=$search . $img_id_var?>"  > << </a>
		<? } ?>
	
		<span style='top:0;right:0;'>
		Showing <?=($start+1) ?> - <?=$end?> of <?= sizeof($posts) ?>
		<? if($nstart < sizeof($posts)){ ?>
			<a href=\"?type=slideshow_image&post_id=$pid&order_by=$orderby&direction=$direction&porder_by=$orderby&start=$nstart<?= $search . $img_id_var ?>"  > >> </a>
		<? } ?>
		</span>
		
	</div>


	<form method="POST" name="edit_form" action="?type=image&flash=0" >

	<input type='hidden' id='post_id' name='post_id' value=''>
	<table>
		<tr>
			<th>Select</th>
			<th class='title_column' >
				<a href="?type=slideshow_image&post_id=<?= $pid ?>&order_by=title&direction=<?=$direction?>&porder_by=<?=$orderby?>&start=<?=$start?><?=$search?><?=$img_id_var?>" >Title</a>
			</th>
			<th class='credit_column' >
				<a href="?type=slideshow_image&post_id=<?= $pid ?>&order_by=photo_credit&direction=<?=$direction?>&porder_by=<?=$orderby?>&start=<?=$start?><?=$search?><?=$img_id_var?>" >Photo Credit</a>
			</th>
			<th class='date_column' >
				<a href="?type=slideshow_image&post_id=<?= $pid ?>&order_by=date&direction=<?=$direction?>&porder_by=<?=$orderby?>&start=<?=$start?><?=$search?><?=$img_id_var?>" >Upload Date</a>
			</th>
			<th>
				&nbsp;
			</th>
		</tr>
	</form>
	
	<?php
//		if($img_id){
//					$select_value = " <img onmouseover=\"this.style.border='3px solid red';\" onmouseout=\"this.style.border='none';\" onclick=\"wpss_replace_photo($img_id,$id,'".$photo->url."');\" style='margin:0px;' title=\"$alt\" alt=\"$alt\" src=\"$thumb\" /><p class=\"caption\">$caption</p>";
//				}else{
			//		$select_value = " <img onmouseover=\"this.style.border='3px solid red';\" onmouseout=\"this.style.border='none';\" onclick=\"wpss_send_and_return('$id','".$this->photoItemHTML_simple($id, $items, true)."');\" style='margin:0px;' title=\"$alt\" alt=\"$alt\" src=\"$thumb\" /><p class=\"caption\">$caption</p>";
//				}
			//	$titleCol = sizeof($titles)>1 ? $this->utils->makeSelectBox("${id}[title]",$titles) : "<input type='hidden' id='${id}[title]' value='$titles[0]'/>". $this->utils->shorten_name($titles[0], 10);
				
	
	
		foreach($better_posts as $photo){
			?>			
			<tr>
				<td class="select_column">
					<img 
						onmouseover="this.style.border='3px solid red';" 
						onmouseout="this.style.border='none';" 
				<? if($img_id) { ?>
						onclick="wpss_replace_photo( <?=$img_id?>, <?=$photo->post_id?>, '<?=$photo->url?>');"
				<? } else { ?>
						onclick="wpss_send_and_return('<?=$photo->post_id?>', jQuery(this).parent().next().children('select').val() );" 
				<? } ?> 
						style='margin:0px;' title="<?=$photo->alt?>" alt="<?=$photo->alt?>" src="<?=$photo->thumb_url?>" />
					<p class="caption">
						<?= $photo->caption ?>
					</p>
				</td>
				<td class="title_column">
					<select>
						<option><?=$photo->title?></option>
					<?
						foreach($photo->extra_titles() as $title)
						{ ?>
						<option><?=$title?></option>
						<? } ?>
					</select>
				</td>
				<td class="credit_column">
					<?=$photo->photo_credit?>
				</td>
				<td class="date_column">
					<?=date("Y-m-d", strtotime($photo->date_modified) ) ?>
				</td>
				<td>
					<a href="?type=slideshow_image&post_id=<?=$photo->post_id . $img_id_var?>" class="button" >Edit</a>
				</td>
			
			</tr>
			
			<?
		}
		?>
		</table>
		</form>
	<?
	}
	?>	
		<div >
			<?= $msg ?>
		</div>
		
