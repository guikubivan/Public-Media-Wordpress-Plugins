			<p>
				<a href="?type=slideshow_image" class="button"  >Go back</a>
			</p>
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

			<button class='button' onclick="var win = window.dialogArguments || opener || parent || top;tb_remove();">Save</button>
			<br />
			<div id="media-items">
				
			<?= $media_items ?>
			
			</div></form>