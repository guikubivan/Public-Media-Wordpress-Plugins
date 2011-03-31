
		<script type="text/javascript">//<![CDATA[

       <?php if(!current_user_can('edit_plugins') && preg_match("/media\-upload.php/", $_SERVER[ 'REQUEST_URI' ]) ): ?>

          function simplifyUploadInterface(){
                  jQuery("#tab-gallery").hide();
                  jQuery("#tab-library").hide();
                  jQuery("#add_media").hide();


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
        
/*
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
*/		

		
		//This is called when you hit the "Update This Photo" button
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
		
		//called when pressing the "X" button on a photo
		function removePhotoItem(photo_id, slideshow_id)
		{
			wpss_start_loading();
		
			var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
			mysack.method = 'POST';
			mysack.setVar( "action", "action_remove_photo_from_slideshow" );
			mysack.setVar( "photo_id", photo_id );
			mysack.setVar( "slideshow_id", slideshow_id);
			mysack.setVar( "post_id", get_wordpress_post_id() );
			mysack.encVar( "cookie", document.cookie, false );
			mysack.onCompletion = function () 
			{ 
				var msg = "";
				if(mysack.response == "1")
				{
					msg = "Successfully deleted photo";
					if ( document.getElementById("photoContainer_"+photo_id).parentNode && document.getElementById("photoContainer_"+photo_id).parentNode.removeChild ) 
						document.getElementById("photoContainer_"+photo_id).parentNode.removeChild(document.getElementById("photoContainer_"+photo_id));
				}
				else if(mysack.response == "0")
				{
					msg = "Could not delete photo";
				}
				else 
				{
					jQuery("#slideshow_content_box").empty();
					jQuery("#slideshow_content_box").html(mysack.response);
				}
				wpss_stop_loading(msg + " " + mysack.response);
			};
			mysack.onError = function() { alert('Ajax error in deleting slideshow photo from database.' )};
			mysack.runAJAX();
			return true;
		}
		
		//called when pressing the "X" button on a photo
		function removeSlideshow(slideshow_id)
		{
			wpss_start_loading();
		
			var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
			mysack.method = 'POST';
			mysack.setVar( "action", "action_remove_slideshow" );
			mysack.setVar( "slideshow_id", slideshow_id);
			mysack.setVar( "post_id", get_wordpress_post_id() );
			mysack.encVar( "cookie", document.cookie, false );
			mysack.onCompletion = function () 
			{ 
				var msg = "";
				if(mysack.response == "1")
				{
					msg = "Successfully removed slideshow";
					if ( document.getElementById("slideshowContainer_"+slideshow_id).parentNode && document.getElementById("slideshowContainer_"+slideshow_id).parentNode.removeChild ) 
						document.getElementById("slideshowContainer_"+slideshow_id).parentNode.removeChild(document.getElementById("slideshowContainer_"+slideshow_id));
				}
				else if(mysack.response == "0")
				{
					msg = "Could not remove slideshow";
				}
				else
				{
					jQuery("#slideshow_content_box").empty();
					jQuery("#slideshow_content_box").html(mysack.response);
				}
				wpss_stop_loading(msg + " " + mysack.response);
			};
			mysack.onError = function() { alert('Ajax error in deleting slideshow photo from database.' )};
			mysack.runAJAX();
			return true;
		}
		
		//adds a new, empty slideshow
		function addSlideshow()
		{
			var number_of_slideshows = jQuery(".slideshow_wrapper").length;
			wpss_start_loading();
			var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
			mysack.method = 'POST';
			mysack.setVar( "action", "action_add_new_slideshow" );
			mysack.setVar( "current_slideshows", number_of_slideshows);
			mysack.setVar( "post_id", get_wordpress_post_id());
			mysack.encVar( "cookie", document.cookie, false );
			mysack.onCompletion = function () 
			{ 
				if(number_of_slideshows > 0)
				{
					jQuery("#slideshow_"+current_slideshow+"_add_button").before("<li>"+mysack.response+"</li>");
				}
				else
				{
					jQuery("#slideshow_content_box").empty();
					jQuery("#slideshow_content_box").html(mysack.response);
				}
				wpss_stop_loading( "" );
			};
				
			mysack.onError = function() { alert('Ajax error in add the image to the slideshow.' )};
			mysack.runAJAX();
		}
		
		
		
		//Used to control the loading animations
		function wpss_start_loading()
		{
			jQuery("#hourglass").show();
		}
		function wpss_stop_loading(msg)
		{
			jQuery("#hourglass").hide();
			console.log( msg );
		}
		
		
	
		
		//Used for adding images to the slideshow... sets the current_slideshow global and opens the thick box
		var current_slideshow = "";
		function pickPhoto(slideshowID){
			current_slideshow = slideshowID
			
			if(document.getElementById('post_ID')!=null){
				pid = document.getElementById('post_ID').value;
			}
		
			tb_show('', 'media-upload.php?type=slideshow_image&TB_iframe=true', false);
		}
		
		//called by the image chooser box when clicking an image
		function wpss_send_and_return(photo_id, title){
			var win = window.dialogArguments || opener || parent || top;
			win.send_to_slideshow(photo_id, title);
			//alert(photo_id);
		}
		
		//adds the image to the slideshow
		function send_to_slideshow(photo_post_id, title)
		{
			wpss_start_loading();
			var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    
			mysack.method = 'POST';
			mysack.setVar( "action", "action_add_photo_to_slideshow" );
			mysack.setVar( "photo_post_id", photo_post_id );
			mysack.setVar( "slideshow_id", current_slideshow);
			mysack.setVar( "post_id", get_wordpress_post_id() );
			mysack.setVar( "title", title );
			mysack.encVar( "cookie", document.cookie, false );
			mysack.onCompletion = function () 
			{ 
				if(current_slideshow != "single" && current_slideshow != "new_single")
				{
					jQuery("#slideshow_"+current_slideshow+"_add_button").before("<li>"+mysack.response+"</li>");
				}
				else if(current_slideshow == "single" || current_slideshow == "new_single")
				{
					jQuery("#slideshow_content_box").empty();
					jQuery("#slideshow_content_box").html(mysack.response);
				}
					
				wpss_stop_loading( "" );
				current_slideshow = "";
				tb_remove();
				};
				
			mysack.onError = function() { alert('Ajax error in add the image to the slideshow.' )};
			mysack.runAJAX();
		}
		
		function get_wordpress_post_id()
		{
			var key="post";
			var default_="";
			key = key.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
			var regex = new RegExp("[\\?&]"+key+"=([^&#]*)");
			var qs = regex.exec(window.location.href);
			if(qs == null)
				return default_;
			else
				return qs[1];
		}
		
		 // end of JavaScript function myplugin_ajax_elevation
		//]]>
		</script>