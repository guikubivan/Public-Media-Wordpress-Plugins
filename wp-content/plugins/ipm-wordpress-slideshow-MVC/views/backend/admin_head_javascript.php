
		<script type="text/javascript">//<![CDATA[

       <?php if(!current_user_can('edit_plugins') && preg_match("/media\-upload.php/", $_SERVER[ 'REQUEST_URI' ]) ): ?>

          function simplifyUploadInterface(){
                  jQuery("#tab-gallery").hide();
                  jQuery("#tab-library").hide();
                  jQuery("#media-buttons").hide();



                  if(jQuery(".post_title").length == 0){
                          jQuery("#html-upload-ui").next().nextAll().hide();
                  }else{
                          //alert("must hide svae button");
                          jQuery("#media-items").nextAll().hide();
                          jQuery("input[name='save']").hide();
                  }
          }


          jQuery(document).ready(function(){
            simplifyUploadInterface();
          });
        <?php endif; ?>
        

		function mapper_ajax_getCoords(station_location, latitude_id, longitude_id)
		{//alert("hi mapper");
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  mysack.execute = 1;
		  mysack.method = 'POST';
		  mysack.setVar( "action", "action_get_coordinates" );
		  mysack.setVar( "geo_location", station_location );
		  mysack.setVar( "latitude_id", latitude_id );
		  mysack.setVar( "longitude_id", longitude_id );
		  mysack.encVar( "cookie", document.cookie, false );
		  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
		  //mysack.onCompletion = whenImportCompleted;
		  mysack.runAJAX();

		  return true;
		}
		
		
		function ajax_getSlideshow(sid)
		{//alert("hi mapper");
		if(sid=='none'){return;}
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  mysack.execute = 1;
		  mysack.method = 'POST';
		  mysack.setVar( "action", "action_get_slideshow" );
		  mysack.setVar( "sid", sid );
		  mysack.encVar( "cookie", document.cookie, false );
		  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
		  //mysack.onCompletion = whenImportCompleted;
		  mysack.runAJAX();
		  return true;
		}

		function ajax_update_photo_property(photo_id, property_name, property_value, update, element_id){
			   var mysack = new sack( 
			       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

			  mysack.execute = 1;
			  mysack.method = 'POST';
			  mysack.setVar( "action", "action_update_photo" );
			  mysack.setVar( "photo_id", photo_id );
			  mysack.setVar( "property_name", property_name );
			  mysack.setVar( "property_value", property_value );
			  mysack.setVar( "update", update );
			  mysack.setVar( "element_id", element_id );
			  mysack.encVar( "cookie", document.cookie, false );
			  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
			  mysack.runAJAX();
			  return true;
		}
		
		function ajax_update_photo(photo_id, slideshow_id)
		{
			wpss_start_loading();
		
			var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
		
			mysack.method = 'POST';
			mysack.setVar( "action", "action_update_photo" );
			mysack.setVar( "photo_id", photo_id );
			mysack.setVar( "title", jQuery("#slideshowItem_"+slideshow_id+"_photos_"+photo_id+"_title").val() );
			mysack.setVar( "photo_credit", jQuery("#slideshowItem_"+slideshow_id+"_photos_"+photo_id+"_photo_credit").val() );
			mysack.setVar( "geo_location", jQuery("#slideshowItem_"+slideshow_id+"_photos_"+photo_id+"_geo_location").val() );
			mysack.setVar( "original_url", jQuery("#slideshowItem_"+slideshow_id+"_photos_"+photo_id+"_original_url").val() );
			mysack.setVar( "alt", jQuery("#slideshowItem_"+slideshow_id+"_photos_"+photo_id+"_alt").val() );
		  	mysack.setVar( "caption", jQuery("#slideshowItem_"+slideshow_id+"_photos_"+photo_id+"_caption").val() );
			mysack.encVar( "cookie", document.cookie, false );
			mysack.onCompletion = function () { 
					wpss_stop_loading( mysack.response );
				};
			mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
			mysack.runAJAX();
			return true;
		}
		
		function removePhotoItem(photo_id, slideshow_id)
		{
			wpss_start_loading();
		
			var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
			mysack.method = 'POST';
			mysack.setVar( "action", "action_remove_photo_from_slideshow" );
			mysack.setVar( "photo_id", photo_id );
			mysack.setVar( "slideshow_id", slideshow_id);
			mysack.encVar( "cookie", document.cookie, false );
			mysack.onCompletion = function () 
			{ 
				wpss_stop_loading(mysack.response);
				if ( document.getElementById("photoContainer_"+photo_id).parentNode && document.getElementById("photoContainer_"+photo_id).parentNode.removeChild ) 
					document.getElementById("photoContainer_"+photo_id).parentNode.removeChild(document.getElementById("photoContainer_"+photo_id));
			};
			mysack.onError = function() { alert('Ajax error in deleting slideshow photo from database.' )};
			mysack.runAJAX();
			return true;
		}
		
		function wpss_start_loading()
		{
			jQuery("#hourglass").show();
		}
		function wpss_stop_loading(msg)
		{
			jQuery("#hourglass").hide();
			 console.log( msg );
		}
		
		
	
		
		var current_slideshow = "";
		function pickPhoto(slideshowID){
			current_slideshow = slideshowID
			/*if(slideshowOrganizer.isSingle(slideshow_id) && (slideshowOrganizer.subItems(slideshow_id)==1)){
				val = window.confirm("This will convert the image management to slideshow mode, is this what you want?");
				if(val==true){
					//alert('yep');
					jQuery("#slideshowTable_"+slideshow_id).show();
					jQuery("#slideshow_heading_"+slideshow_id).html('Slideshow');
					slideshowOrganizer.setSingle(slideshow_id, false);
					jQuery('.set_cover_button_'+slideshow_id).show();
		
				}else{
					return;
				}
			}*/
		
			if(document.getElementById('post_ID')!=null){
				pid = document.getElementById('post_ID').value;
			}
		
			tb_show('', 'media-upload.php?type=slideshow_image&TB_iframe=true', false);
			//tb_show('', 'media-upload.php?type=slideshow_image&post_id=' + pid + '&TB_iframe=true', false);
		
		}
		
		
		function wpss_replace_photo(current_photo_id, new_photo_post_id, new_url){
			var win = window.dialogArguments || opener || parent || top;
			if(typeof(win.currentSlideshowID) !== 'undefined' ){
				//win.ajax_replace_wp_photo(photo_id,wp_photo_id, new_url);
			}
			else
			{
				alert('No slideshow currently selected.');
				return;
			}
		
			win.tb_remove();
		}
		
		          
		function ajax_replace_wp_photo(photo_id, wp_photo_id, new_url, pcred, gloc){
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  //mysack.execute = 1;
		  mysack.method = 'POST';
		  mysack.setVar( "action", "action_replace_wp_photo" );
		  mysack.setVar( "photo_id", photo_id );
		  mysack.setVar( "wp_photo_id", wp_photo_id );
		  mysack.setVar( "new_url", new_url );
		  mysack.setVar( "photo_credit", pcred );
		  mysack.setVar( "geo_location", gloc );
		  mysack.encVar( "cookie", document.cookie, false );
		  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
		  //mysack.onCompletion = whenImportCompleted;
		  mysack.runAJAX();

		  return true;

		}
		
		function wpss_send_and_return(photo_id){
			var win = window.dialogArguments || opener || parent || top;
			win.send_to_slideshow(photo_id);
			//alert(photo_id);
		}
		function send_to_slideshow(photo_post_id, title)
		{
			wpss_start_loading();
		
			var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
			mysack.method = 'POST';
			mysack.setVar( "action", "action_add_photo_to_slideshow" );
			mysack.setVar( "photo_post_id", photo_post_id );
			mysack.setVar( "slideshow_id", current_slideshow);
			mysack.setVar( "title", title );
			mysack.encVar( "cookie", document.cookie, false );
			mysack.onCompletion = function () 
			{ 
				wpss_stop_loading( mysack.response );
				current_slideshow = "";
				tb_remove();
				};
			mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
			mysack.runAJAX();
		}
		
		
		
		 // end of JavaScript function myplugin_ajax_elevation
		//]]>
		</script>