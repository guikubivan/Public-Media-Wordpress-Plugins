<?php
	require_once(dirname(__FILE__).'/../../../wp-load.php');
	require_once(dirname(__FILE__).'/../../../wp-admin/admin.php');
	header("Content-Type: text/javascript");
?>


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

	jQuery("#library-form, #image-form").submit(function() { //for media-upload.php
		return check_required('wpss_required', true);

	});



	jQuery("#post").submit(function() {//for post.php//, #edit_slideshow_form
		valid=true;
		jQuery("input.wpss_required, textarea.wpss_required").each(function(index){
			//alert(this);
			this.style.backgroundColor = '';
			this_id = this.id;
			slideshow_id = this_id.substring(14,this_id.indexOf(']',14));
			
			if(slideshowOrganizer.isSingle(slideshow_id) && this_id.search('photos')==-1){
				return;
			}

			if(this.value === ''){
				
				this.style.backgroundColor = '#FF9A8D';
				jQuery(this).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
				
				valid=false;
				//return false;
			}
			
		});
		if(!valid){
			alert("Please fill required fields");
		}
		return valid;
	});
	<?php if(!current_user_can('edit_plugins')){ echo "simplifyUploadInterface();"; }?>
	
});


function check_required(elementClass, showAlert){
	valid=true;
	jQuery("."+elementClass).each(function(index){
		//alert(this);
		this.style.backgroundColor = '';
		if(this.value === ''){
			if (typeof currentMediaItem != 'undefined') {
				if(this.id.search(currentMediaItem)==-1)return;
			}
			if(showAlert){
				this.style.backgroundColor = '#FF9A8D';
				jQuery(this).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
			}
			valid=false;
			//return false;
		}
		
	});
	if(!valid && showAlert){
		alert("Please fill required fields");
	}
	return valid;
}

function convertquotes(str){
	str = str.replace( /"/g, '&quot;' );
	str = str.replace( /'/g, '&#039;' );
	return str;
}

function setCoverImage(sid, pid){
		//alert(sid + ' ' +pid);
	if(!slideshowOrganizer.isSingle(sid)){
		jQuery(".photo_in_slideshow_"+sid).val('');	
		document.getElementById('slideshowItem['+sid+'][photos]['+pid+'][cover]').value='yes';
		//jQuery(".wpss_photo_container").css({'background-color' : ''});	
		//jQuery('#photoContainer_'+pid).css({'background-color' : '#A09C42'});
		/*jQuery(".set_cover_button_"+sid).css({'background-color' : ''});
		jQuery(".set_cover_button_"+sid).prev().css({'border-color' : '', 'border-size': ''});

		jQuery('#cover_button_'+sid+'_'+pid).css({'background-color' : '#A09C42'});
		jQuery('#cover_button_'+sid+'_'+pid).prev().css({'border-color' : '#A09C42', 'border-width': '3px'});*/
		jQuery(".set_cover_button_"+sid).removeClass('wpss_cover_highlight');
		jQuery(".set_cover_button_"+sid).siblings('img').removeClass('wpss_cover_highlight');
		jQuery(".set_cover_button_"+sid).siblings('img').addClass('wpss_photo_thumb');


		jQuery('#cover_button_'+sid+'_'+pid).addClass('wpss_cover_highlight');
		jQuery('#cover_button_'+sid+'_'+pid).siblings('img').removeClass('wpss_photo_thumb');
		jQuery('#cover_button_'+sid+'_'+pid).siblings('img').addClass('wpss_cover_highlight');


	}

}
//*********************GOOGLE MAPS STUFF**********************

function loadMap(id, location, lat, long) {
	div = document.getElementById("map_canvas_"+id);
	//jQuery(div).toggle("slow");
	if (GBrowserIsCompatible()) {
		div.style.display = 'block';
		setTimeout( function() {


	       		var map = new GMap2(div);
			map.setCenter(new GLatLng(lat, long), 7);
			map.setUIToDefault();

			var marker = new GMarker(new GLatLng(lat, long));	


			GEvent.addListener(marker, 'click',
				function() {
					marker.openInfoWindowHtml(location);
				}
			);
			map.addOverlay(marker);
	    	}, 1 ); 
 
	}/*else{
		div.style.display='none';
	}*/

}

window.onunload = GUnload;
//*********************END OF GOOGLE MAPS STUFF**********************

function resetPhotoJS(){


	jQuery(".editable").hover(
	      function () {
		jQuery(this).css({'background-color' : 'yellow', 'text-decoration': 'underline'});
	      }, 
	      function () {
		jQuery(this).css({'background-color' : '', 'text-decoration': ''});
	      }
	    );

	jQuery(".editable").focus(function () {
		jQuery(this).removeAttr("readonly");
		jQuery(this).next().show();
		jQuery(this).nextAll('img').hide();
		jQuery(this).next().removeClass("highlight");
		jQuery(this).next().addClass("editable");
		jQuery(this).next().addClass("button");
		jQuery(this).next().html("Update");

		jQuery(this).css({'background-color' : 'yellow', 'text-decoration': 'underline'});
		currentFieldValue = jQuery(this).val();
	});

	jQuery(".editable").blur(function () {
		jQuery(this).attr("readonly", true);
		jQuery(this).next().hide();
		jQuery(this).nextAll('img').show();
		jQuery(this).css({'background-color' : '', 'text-decoration': ''});
		jQuery(this).val(currentFieldValue);
	});

	jQuery(".editable").next().click(function () {
		elementID = jQuery(this).prev().attr("id");
		prop = elementID.substring(elementID.lastIndexOf('[')+1,elementID.lastIndexOf(']'));
		photo_id = elementID.substring(elementID.lastIndexOf('[', elementID.lastIndexOf('[')-1)+1,elementID.lastIndexOf(']', elementID.lastIndexOf(']')-1));
		slideshow_id = elementID.substring(14,elementID.indexOf(']',14));
		updateID = elementID.replace(prop,'update');
		update = '';
		if(document.getElementById(updateID) != null){
			update = document.getElementById(updateID).value;
		}
		//alert(photo_id);
		//alert(elementID);
		//alert(update);
		val = jQuery(this).prev().val();
		//alert(val);
		ajax_update_photo_property(photo_id, prop, val, update, elementID);

		jQuery(this).prev().attr("readonly", true);
		jQuery(this).prev().css({'background-color' : '', 'text-decoration':''});
		//jQuery(this).hide();

		//jQuery(this).prev().val(currentFieldValue);
	});
}
jQuery(document).ready(function(){
	resetPhotoJS();
});

var currentSlideshowID = '';
var currentFieldValue = '';

function removeHTMLTags(strInputCode){
 	 strInputCode = strInputCode.replace(/&(lt|gt);/g, function (strMatch, p1){
 	 	return (p1 == "lt")? "<" : ">";
 	});
 	var strTagStrippedText = strInputCode.replace(/<\/?[^>]+(>|$)/g, "");
	return strTagStrippedText;
}


function showSlideshowMenu(checkValue,show){
	//alert(checkValue);
	if(show ==true){
		jQuery("#slideshow_options").show();
		jQuery("#wpss_save").hide();
	}else if(checkValue!='none'){
		jQuery("#slideshow_options").hide();
		jQuery("#wpss_save").show();
	}
}

function removePhotoItem(deletePhotoButtonID){
	sid = deletePhotoButtonID.substring(deletePhotoButtonID.lastIndexOf('_')+1,deletePhotoButtonID.length);
	slideshowOrganizer.removeSubItem(sid);
	/*if(slideshowOrganizer.isSingle(sid) ){
		jQuery("#addphoto_button_"+sid).show();

	}*/
}

function confirmChooseNewPhoto(sid, imgID){
	if(document.getElementById("slideshowItem["+sid+"][photos]["+imgID+ "][update]") != null){
		val = window.confirm("Do you want to replace this image, but maintain the meta information?");
		if(val==true){
			chooseNewPhoto(sid,imgID);
		}else{
			return;
		}
	}
}

function chooseNewPhoto(sid, imgID){
	currentSlideshowID = sid;
	//alert(imgID);
	tb_show('', 'media-upload.php?img_id='+imgID+'&type=replace_wp_image&TB_iframe=true', false);


}

function pickPhoto(slideshowID){
	currentSlideshowID = slideshowID;
	slideshow_id = currentSlideshowID.substring(currentSlideshowID.lastIndexOf('_')+1,currentSlideshowID.length);
	if(slideshowOrganizer.isSingle(slideshow_id) && (slideshowOrganizer.subItems(slideshow_id)==1)){
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
	}

	if(document.getElementById('post_ID')!=null){
		pid = document.getElementById('post_ID').value;
	}

	tb_show('', 'media-upload.php?type=slideshow_image&TB_iframe=true', false);
	//tb_show('', 'media-upload.php?type=slideshow_image&post_id=' + pid + '&TB_iframe=true', false);

}
function blinkElement(element){
		jQuery(element).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);

}

function executeMap(location, latID, longID){
	lat = document.getElementById(latID).value;
	long = document.getElementById(longID).value;
	if(lat || long){
		//alert('go'+lat +'|'+ long);
		//alert('media-upload.php?post_id=' + pid + '&TB_iframe=true&type=slideshow_image');
		tb_show('', 'media-upload.php?type=map&location=' + location + '&latitude='+ lat + '&longitude=' + long + '&TB_iframe=true', false);
		//tb_show('', 'media-upload.php?post_id=post_id=5471&tab=library&post_mime_type=image', false)
	}else{
		alert('Location query timed out, try again');
	}

}

function showMap(location, latID, longID, element){

	if(location){

		lat = document.getElementById(latID).value;
		long = document.getElementById(longID).value;
		
		if(!lat || !long || lat==0 || long==0){
			setTimeout("executeMap(\""+location+"\",\""+latID+"\",\""+longID+"\")",500);
			//alert('x'+lat +'|'+ long);

		}else{
			//alert('go'+lat +'|'+ long);
			//alert('media-upload.php?post_id=' + pid + '&TB_iframe=true&type=slideshow_image');
			//alert('media-upload.php?type=map&location=' + location + '&latitude='+ lat + '&longitude=' + long + '&TB_iframe=true');
			tb_show('', 'media-upload.php?type=map&location=' + location + '&latitude='+ lat + '&longitude=' + long + '&TB_iframe=true', false);
			//tb_show('', 'media-upload.php?post_id=post_id=5471&tab=library&post_mime_type=image', false)
		}
	}else{
		blinkElement(element);
	}
}

function showMapForPhoto(value){
	//alert(value);
	//photo_id = elementID.substring(elementID.lastIndexOf('[', elementID.lastIndexOf('[')-1)+1,elementID.lastIndexOf(']', elementID.lastIndexOf(']')-1));
	tb_show('', 'media-upload.php?type=map&location=' + value + '&TB_iframe=true', false);
//	alert(photo_id);

}

function wpss_replace_photo_javascript(photo_id, wp_photo_id, new_url){
	var win = window.dialogArguments || opener || parent || top;
	if(typeof(win.currentSlideshowID) !== 'undefined' ){
		pcred = convertquotes(document.getElementById('attachments['+wp_photo_id+'][photo_credit]').value);
		gloc = convertquotes(document.getElementById('attachments['+wp_photo_id+'][geo_location]').value);
		win.ajax_replace_wp_photo(photo_id,wp_photo_id, new_url, pcred, gloc);
		//alert('img_'+win.currentSlideshowID+'_'+photo_id);


	}else{alert('No slideshow currently selected.');
		return;
	}

	win.tb_remove();
	win.resetPhotoJS();

}

function wpss_replace_photo(photo_id, wp_photo_id, new_url){
	var win = window.dialogArguments || opener || parent || top;
	if(typeof(win.currentSlideshowID) !== 'undefined' ){
		win.ajax_replace_wp_photo(photo_id,wp_photo_id, new_url);
		//alert('img_'+win.currentSlideshowID+'_'+photo_id);


	}else{alert('No slideshow currently selected.');
		return;
	}

	win.tb_remove();
	win.resetPhotoJS();
}

function wpss_send_and_return(photo_id, html){
	var win = window.dialogArguments || opener || parent || top;
	win.send_to_slideshow(photo_id, html);
}


function send_to_slideshow(photo_id, html){
	//alert(currentSlideshowID);
	if(typeof(currentSlideshowID) !== 'undefined' ){
		jQuery("#"+currentSlideshowID).sortable(/*{
   			start: function(event, ui) {
   				var w = jQuery(this).css('width');
   				jQuery("#wpss_parent_div").after(w);
   				jQuery(this).css('width', w);
   					
   			 }	

		}*/);
		slideshow_id = currentSlideshowID.substring(currentSlideshowID.lastIndexOf('_')+1,currentSlideshowID.length);
		html = html.replace( /s_id/g, slideshow_id);
		var photoOrganizer = new itemOrganizer(currentSlideshowID);
		photoOrganizer.organizerAddItem(photo_id,html);
		slideshowOrganizer.addSubItem(slideshow_id);
		/*if(slideshowOrganizer.subItems(slideshow_id)==1){
			document.getElementById('slideshowItem['+sid+'][photos]['+pid+'][cover]').value='yes';
			setCoverImage(slideshow_id, pid);
		}*/
		if(slideshowOrganizer.isSingle(slideshow_id) ){
			if(slideshowOrganizer.subItems(slideshow_id)==1){
				jQuery("#addphoto_button_"+slideshow_id).html('Add more photos');
			}
			jQuery('#cover_button_'+slideshow_id+'_'+photo_id).hide();
		}
		//slideshowOrganizer.items[slideshow_id];
		/*if(slideshowOrganizer.isSingle(slideshow_id) ){
			jQuery("#addphoto_button_"+slideshow_id).hide();
		}*/
	}else{alert('No slideshow currently selected.');
		return;
	}
	//currentSlideshowID ='';
	tb_remove();
	resetPhotoJS();
}

function slideshow_getCoords(field_id){
	loc = document.getElementById(field_id).value;
	slideshow_id = field_id.substring(14,field_id.indexOf(']',14));
	//alert(loc);
	lat_id = "slideshowItem["+slideshow_id+"][latitude]";
	long_id = "slideshowItem["+slideshow_id+"][longitude]";
	get_google_coordinates(loc, lat_id, long_id)
}


function itemOrganizer(parent_div){
	var items = Array();
	//var properties = Array();
	var itemLabelPrefix = 'organizerItemContainer_';
	this.nItems=0;
	var dataChanged = false;
	var deleteIndex = 0; 

	this.getLastID = function() {
		return items[items.length-1]['id'];
	}

	this.getIndex = function(sid){
		index=-1;
		for(i=0;i<items.length;++i){
			if(items[i]['id']==slideshow_id)index=i;		
		}
		if(index==-1){
			alert("Slideshow not found");
			return;
		}

		return index;
	}
	this.subItems = function(sid){
		index=this.getIndex(sid);
		return items[index]['sub_items'];
	}
	this.isSingle = function(sid){
		index=this.getIndex(sid);
		return items[index]['single'];
	}
	this.setSingle = function (sid, value){
		index=this.getIndex(sid);
		items[index]['single'] = false;
	}

	this.addSubItem = function(sid){
		index=this.getIndex(sid);
		++items[index]['sub_items'];
	}

	this.removeSubItem = function(sid){
		index=this.getIndex(sid);
		--items[index]['sub_items'];
	}

	this.organizerRemoveItem = function (idtext){
		if(items.length==0){
			return;
		}
		slideshow_id = idtext.substring(idtext.lastIndexOf('_')+1,idtext.length);
		index=-1;
		for(i=0;i<items.length;++i){
			if(items[i]['id']==slideshow_id)index=i;		
		}
		//index = jQuery.inArray(slideshow_id, items);
		//alert(index);
		if(index==-1){
			alert("Slideshow not found");
			return;
		}
		items.splice(index-1,1);

		if(items.length==0){
			//alert('true');
			showSlideshowMenu('', true);
		}
		this.nItems = items.length;
	}
	this.clearOrganizer = function() {
		items = []; 

	}


	this.organizerAddItem = function() {
		if(arguments.length < 1)return;
		//if(items.length >0) {return;}
		
		dataChanged= true;
	
		itemID = arguments[0];
		itemHTML = arguments[1];
		single = arguments[2];
		//alert(itemHTML);
		if(itemID.length == 0){
			itemID = this.nItems;
			counter =0;
			while(jQuery.inArray(itemID, items)>-1){
				itemID += 1;
				++counter;
				if(counter ==100){alert("infinite");break;}
			}
			//alert(itemHTML.replace( /insert_id/g, itemID));
			itemHTML = itemHTML.replace( /insert_id/g, itemID);
		}
		var newItem = new Array();

		newItem['id'] = itemID;
		newItem['sub_items'] = 0;
		if(single==undefined){
			newItem['single'] = false;
		}else{
			//alert(single);
			newItem['single'] = single;
		}
		
		items.push(newItem);

		jQuery('#'+parent_div).append(itemHTML);
		this.nItems+=1;



	}



}

