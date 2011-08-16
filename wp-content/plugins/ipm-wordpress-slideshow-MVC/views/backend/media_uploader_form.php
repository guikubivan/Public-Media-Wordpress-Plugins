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
	
	.dropdown {
		width: 200px;
	}
</style> 

<center>	<h2>Upload new photo</h2> 
	<?php 
		$post_id = 0;
		$type='image';
		$form_action_url = admin_url("media-upload.php?type=$type&tab=type&post_id=0&flash=0");
		//$form_action_url = apply_filters('media_upload_form_url', $form_action_url, $type);

		$callback = "type_form_$type";
	?>
			<form enctype="multipart/form-data" method="post" action="<?php echo attribute_escape($form_action_url) ?>" class="media-upload-form type-form validate" id="<?php echo $type ?>-form">
				<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id ?>" />
				<?php echo wp_nonce_field('media-form') ?>
				
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
				
				<?php echo $media_upload_form ?>
				
				<? if($img_id) { ?>
					<input type='hidden' name='img_id' value='<?php echo $img_id ?>' />
				<? } ?>
			</form>

	<hr />

<h2 style='margin-bottom:0px'>Choose picture</h2> 
	<div>
	
	<form method='post' name='search_form' action='?type=slideshow_image<?php echo $img_id_var ?>' >
	
	
	<input id="post-search-input" type="text" value="<?php echo $this->plugin->_post['see_all'] ? '' : $search_field ?>" name="s"/>
	<input type="submit" value="Search Photos"/> <input type="submit" name='see_all' value="See all"/>

	</form>
</center>

	</div>
	<?php

		if(!isset($_GET['order_by']) && !isset($_REQUEST['s']) && !isset($_POST['see_all'])){
			echo '<hr />';
			return;
		}
		if(!$msg){
	?>
	
	<div style='position:relative;text-align: right'>
	
		<? if($start > 0){ ?>
			<a href="?type=slideshow_image&post_id=<?php echo $pid ?>&order_by=<?php echo $orderby ?>&direction=<?php echo $nav_direction ?>&porder_by=<?php echo $orderby ?>&start=<?php echo $pstart ?><?php echo $search . $img_id_var?>"  > << Previous</a>&nbsp;
		<? } ?>
	
		<span style='top:0;right:0;'>
		Showing <?php echo ($start+1) ?> - <?php echo $end?> of <?php echo  sizeof($posts) ?>
		<? if($nstart < sizeof($posts)){ ?>
			<a href="?type=slideshow_image&post_id=<?php echo $pid ?>&order_by=<?php echo $orderby ?>&direction=<?php echo $nav_direction ?>&porder_by=<?php echo $orderby ?>&start=<?php echo $nstart ?><?php echo $search . $img_id_var ?>"  > Next >> </a>
		<? } ?>
		</span>
		
	</div>


	<form method="POST" name="edit_form" action="?type=image&flash=0" >

	<input type='hidden' id='post_id' name='post_id' value=''>
	<table>
		<tr>
			<th>Select</th>
			<th class='title_column' >
				<a href="?type=slideshow_image&post_id=<?php echo $pid ?>&order_by=title&direction=<?php echo $direction?>&porder_by=<?php echo $orderby?>&start=<?php echo $start?><?php echo $search?><?php echo $img_id_var?>" >Title</a>
			</th>
			<th class='credit_column' >
				<a href="?type=slideshow_image&post_id=<?php echo $pid ?>&order_by=photo_credit&direction=<?php echo $direction?>&porder_by=<?php echo $orderby?>&start=<?php echo $start?><?php echo $search?><?php echo $img_id_var?>" >Photo Credit</a>
			</th>
			<th class='date_column' >
				<a href="?type=slideshow_image&post_id=<?php echo $pid ?>&order_by=date&direction=<?php echo $direction?>&porder_by=<?php echo $orderby?>&start=<?php echo $start?><?php echo $search?><?php echo $img_id_var?>" >Upload Date</a>
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
						onclick="wpss_replace_photo( <?php echo $img_id?>, <?php echo $photo->post_id?>, '<?php echo $photo->url?>');"
				<? } else { ?>
						onclick="wpss_send_and_return('<?php echo $photo->post_id?>', jQuery('#photo_title_<?php echo $photo->post_id?>').val() );" 
				<? } ?> 
						style='margin:0px;' title="<?php echo $photo->alt?>" alt="<?php echo $photo->alt?>" src="<?php echo $photo->thumb_url?>" />
					<p class="caption">
						<?php echo  $photo->caption ?>
					</p>
				</td>
				<td class="title_column">
					<select id="photo_title_<?php echo $photo->post_id?>" class="dropdown">
						<option><?php echo $photo->title?></option>
					<?
						foreach($photo->extra_titles as $title)
						{ ?>
						<option><?php echo $title?></option>
						<? } ?>
					</select>
				</td>
				<td class="credit_column">
					<?php echo $photo->photo_credit?>
				</td>
				<td class="date_column">
					<?php echo date("Y-m-d", strtotime($photo->date_modified) ) ?>
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
			<?php echo $msg ?>
		</div>
		
