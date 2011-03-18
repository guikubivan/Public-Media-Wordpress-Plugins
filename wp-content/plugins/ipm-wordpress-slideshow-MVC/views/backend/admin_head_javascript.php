
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
                  
		function ajax_replace_wp_photo(photo_id, wp_photo_id, new_url, pcred, gloc){
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  mysack.execute = 1;
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
			  //mysack.onCompletion = whenImportCompleted;
			  mysack.runAJAX();
			  return true;
		}
		
		function ajax_update_photo(photo_id){
				wpss_start_loading();
		
			   var mysack = new sack( 
			       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

			 // mysack.execute = 1;
			  mysack.method = 'POST';
			  mysack.setVar( "action", "action_update_photo" );
			  mysack.setVar( "photo_id", photo_id );
			  mysack.setVar( "title", jQuery("#slideshowItem[s_id][photos]["+photo_id+"][title]").val() );
			  mysack.setVar( "photo_credit", jQuery("#slideshowItem[s_id][photos]["+photo_id+"][photo_credit]").val() );
			  mysack.setVar( "geo_location", jQuery("#slideshowItem[s_id][photos]["+photo_id+"][geo_location]").val() );
			  mysack.setVar( "original_url", jQuery("#slideshowItem[s_id][photos]["+photo_id+"][original_url]").val() );
			  mysack.setVar( "alt", jQuery("#slideshowItem[s_id][photos]["+photo_id+"][alt]").val() );
			  mysack.setVar( "caption", jQuery("#slideshowItem[s_id][photos]["+photo_id+"][caption]").val() );
			//  mysack.element = "hourglass";
			//  mysack.setVar( "property_name", property_name );
			//  mysack.setVar( "property_value", property_value );
			//  mysack.setVar( "update", update );
			//  mysack.setVar( "element_id", element_id );
			  mysack.encVar( "cookie", document.cookie, false );
			  mysack.onCompletion = function () { 
					//wpss_stop_loading();
					jQuery("#hourglass").html( mysack.response );
				};
			  mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
			  //mysack.onCompletion = whenImportCompleted;
			  mysack.runAJAX();
			  return true;
		}
		
		function wpss_start_loading()
		{
			jQuery("#hourglass").show();
		}
		function wpss_stop_loading()
		{
			jQuery("#hourglass").hide();
		}
		
		 // end of JavaScript function myplugin_ajax_elevation
		//]]>
		</script>