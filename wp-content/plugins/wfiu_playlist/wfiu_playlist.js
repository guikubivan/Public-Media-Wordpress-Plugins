var wfiuPlaylistItems = new Array();
var playlistItemLabelPrefix = 'playlistItemContainer_';
var maxItems=0;
var dataChanged = false;

var deleteIndex = 0;

function generic_check_required(elementClass, showAlert){
	valid=true;
	jQuery("."+elementClass).each(function(index){
		//alert(this);
		this.style.backgroundColor = '';
		if(this.value === ''){

			//if(showAlert){
				this.style.backgroundColor = '#FF9A8D';
				jQuery(this).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
			//}
			valid=false;
			//return false;
		}
		
	});
	if(!valid && showAlert){
		alert("Please fill required fields");
	}
	return valid;
}

jQuery(document).ready(function(){
	jQuery("#post").submit(function() {//for post.php
		valid=true;
		invalidyear = false;
		jQuery("input.wfiu_playlist_required, textarea.wfiu_playlist_required").each(function(index){

			this.style.backgroundColor = '';
			if(this.id.search('relyear') > -1){
				invalidyear = true;
			}
			if(this.value === '' || (invalidyear && !(this.value === 0)) ){
				if(this.type=='hidden'){
					this_id = this.id;
					item_id = this_id.substring(18,this_id.indexOf(']',18));
					//wfiuPlaylistEditItem(item_id);

					var itemdiv = document.getElementById(playlistItemLabelPrefix+item_id);
					jQuery(itemdiv).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
				}else{

					this.style.backgroundColor = '#FF9A8D';

					jQuery(this).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
				}

			
				valid=false;
				//return false;
			}


		});
		if(!valid){
			msg = "Please fill required fields";
			if(invalidyear){
				msg += " and/or correct release year";
			}
			alert(msg);
		}
		return valid;
	});

});

function insertComposerField(itemID,comp){
	var mySpan = document.getElementById('wfiuPlaylist_composer_'+itemID);
	mySpan.innerHTML= "<b>Composer:</b><br/><input type='text' name=\"wfiuPlaylistItems["+itemID+"][composer]\" size='25' value=\""+comp.replace( /"/g, '&quot;') +"\" /> ";

}

function checkYear(itemID,el) {
	//alert (val.length);
	if(el.value.length==0){
		el.style.backgroundColor = '';
		jQuery(el).removeClass("wfiu_playlist_required");
		return;
	}
	
	if(!el.value.match(/\d\d\d\d/) || !(el.value.length==4)  ){
		el.style.backgroundColor = '#009999';
		jQuery(el).addClass("wfiu_playlist_required");
	}else{
		el.style.backgroundColor = '';
		jQuery(el).removeClass("wfiu_playlist_required");
	}
}

function returnIndexFromPosition(position){

	for (var i=0; i < wfiuPlaylistItems.length; i++) {
		if(position == wfiuPlaylistItems[i]['position']){
			return i;
		}
	}
	alert("returnIndexFromPosition("+position+"): No object with such position found");
	return -1;
}


function returnIndex(id){

	for (var i=0; i < wfiuPlaylistItems.length; i++) {
		if(id == wfiuPlaylistItems[i]['id']){
			return i;
		}
	}
	alert("returnIndex("+id+"): No object with such id found");
	return -1;
}

function wfiuPlaylistEditItem(id){
	//alert("id: "+ id);
	var index = returnIndex(id);
	//alert("index: "+index);
	var itemdiv = document.getElementById(playlistItemLabelPrefix+id);
	itemdiv.innerHTML= getItemHTML(wfiuPlaylistItems[index]);
	generic_check_required('wfiu_playlist_required', false);
}


function moveItemUp(id, mode){
	//if(editing)return;
	shrinkAllOtherItems();
	//alert("id: "+ id);
	index = returnIndex(id);
	//alert(wfiuPlaylistItems[index]['position'] );

	//alert("index: "+index);
	if(wfiuPlaylistItems[index]['position'] != 0){
		prevPosIndex = returnIndexFromPosition(wfiuPlaylistItems[index]['position']-1);

		var itemdiv = document.getElementById(playlistItemLabelPrefix+id);
		var prevdiv = document.getElementById(playlistItemLabelPrefix+wfiuPlaylistItems[prevPosIndex]['id']);

		wfiuPlaylistItems[prevPosIndex]['position'] = wfiuPlaylistItems[index]['position'];
		wfiuPlaylistItems[index]['position'] = wfiuPlaylistItems[index]['position']-1;
			
		prevID = wfiuPlaylistItems[prevPosIndex]['id'];
		wfiuPlaylistItems[prevPosIndex]['id'] = wfiuPlaylistItems[index]['id'];
		wfiuPlaylistItems[index]['id'] = prevID;
		if(mode == 'short'){
			prevdiv.innerHTML= getItemHTMLshort(wfiuPlaylistItems[index]);
			itemdiv.innerHTML= getItemHTMLshort(wfiuPlaylistItems[prevPosIndex]);

		}else{
			prevdiv.innerHTML= getItemHTML(wfiuPlaylistItems[index]);
			itemdiv.innerHTML= getItemHTMLshort(wfiuPlaylistItems[prevPosIndex]);
		}
	}
	deleteIndex=(deleteIndex+1)%2;
	wfiuPlaylistDisplayItems();
}


function moveItemDown(id, mode){
	//if(editing)return;
	shrinkAllOtherItems();
	//alert("id: "+ id);
	index = returnIndex(id);
	//alert(wfiuPlaylistItems[index]['position'] );

	//alert("index: "+index);
	if(wfiuPlaylistItems[index]['position'] != wfiuPlaylistItems.length-1){
		nextPosIndex = returnIndexFromPosition(wfiuPlaylistItems[index]['position']+1);

		var itemdiv = document.getElementById(playlistItemLabelPrefix+id);
		var nextdiv = document.getElementById(playlistItemLabelPrefix+wfiuPlaylistItems[nextPosIndex]['id']);

		wfiuPlaylistItems[nextPosIndex]['position'] = wfiuPlaylistItems[index]['position'];
		wfiuPlaylistItems[index]['position'] = wfiuPlaylistItems[index]['position']+1;
			
		nextID = wfiuPlaylistItems[nextPosIndex]['id'];
		wfiuPlaylistItems[nextPosIndex]['id'] = wfiuPlaylistItems[index]['id'];
		wfiuPlaylistItems[index]['id'] = nextID;
		//alert(wfiuPlaylistItems[index]['position'] );
		if(mode == 'short'){	
			nextdiv.innerHTML= getItemHTMLshort(wfiuPlaylistItems[index]);
			itemdiv.innerHTML= getItemHTMLshort(wfiuPlaylistItems[nextPosIndex]);
		}else{
			//alert ("hi");
			nextdiv.innerHTML= getItemHTML(wfiuPlaylistItems[index]);
			itemdiv.innerHTML= getItemHTMLshort(wfiuPlaylistItems[nextPosIndex]);
		}
	}
	deleteIndex=(deleteIndex+1)%2;
	wfiuPlaylistDisplayItems();
}

function getItemHead(itemObject, mode){

	mypos = itemObject['position']+1;
	//alert("mypos: " + mypos);
	var string = "<div class='wfiuItemHead' id='" + playlistItemLabelPrefix+"head_" + itemObject['id']+"' >";

/*
	string +="<div style='text-align:left;float:left; width:10%'>";
	string +="</div>";
*/


	string += "<div style='text-align:left;float:left;width:95%;overflow:hidden'>";
	if( mode == 'short'){
		//expand and edit
		string += "<span class=\"button\" title=\"Edit\" style='margin-right: 10px; cursor:pointer;' onClick=\"javascript:wfiuPlaylistEditItem('"+itemObject['id']+"');\">&nbsp;+</span>";
	}else{
		string += "<span class=\"button\" title=\"Hide\" style='margin-right: 10px; cursor:pointer;' onClick=\"javascript:wfiuPlaylistShrinkItem('"+itemObject['id']+"');\">&nbsp;-&nbsp;</span>";		
	}
	string += " <span class=\"button\" title=\"Move Up\" style='cursor:pointer;' onClick=\"javascript:moveItemUp('"+itemObject['id']+"','"+mode+"');\"><img class='wfiuPlaylist_imgHeight' src=\""+wfiuPlaylistImgFolder+"up.gif"+"\"/></span>  ";
	string += "<span class=\"button\" title=\"Move Down\" style='margin-right: 10px;color: #662222; cursor:pointer;'  onClick=\"javascript:moveItemDown('"+itemObject['id']+"', '"+mode+"');\"> <img class='wfiuPlaylist_imgHeight' src=\""+wfiuPlaylistImgFolder+"down.gif"+"\"/></span>\n";

	string += mypos+ ". ";
	if(mode == 'short'){
		string +=  "<span style='color:#992266'>" + itemObject['artist'] + "</span> - ";
		string += "<span style='color:#222299'>" + itemObject['title'] + "</span>";
	}
	string += "</div>";


	string +="<div style='text-align:right;float:right; width:5%'>";
	string += "<span title=\"Remove Item\" class=\"button\" style='color: #662222; cursor:pointer;'  onClick=\"javascript:wfiuPlaylistRemoveItem('"+itemObject['id']+"');wfiuPlaylistDisplayItems();\"><img class='wfiuPlaylist_imgHeight' src=\""+wfiuPlaylistImgFolder+"delete.gif"+"\"/></span>\n";
	string += "</div>";
	string += "</div>";

	return string;

}

function convertquotes(str){
	str = str.replace( /"/g, '&quot;' );
	str = str.replace( /'/g, '&#039;' );
	return str;
}

function getItemHTMLshort(itemObject){

	var string = getItemHead(itemObject, 'short');

	string +="<div>";
	string += "<input type=\"hidden\" name=\"wfiuPlaylistItems["+itemObject['id']+"][artist]\" id=\"wfiuPlaylistItems["+itemObject['id']+"][artist]\" class=\"wfiu_playlist_required\" value=\""+convertquotes(itemObject['artist'])  +"\" />";
	string += "<input type='hidden' name=\"wfiuPlaylistItems["+itemObject['id']+"][composer]\" value=\""+itemObject['composer']  +"\" />";

	string += "<input type=\"hidden\" name=\"wfiuPlaylistItems["+itemObject['id']+"][title]\" id=\"wfiuPlaylistItems["+itemObject['id']+"][title]\" class=\"wfiu_playlist_required\" value=\""+convertquotes(itemObject['title'])  +"\" /> ";
	string += "<input type=\"hidden\" name=\"wfiuPlaylistItems["+itemObject['id']+"][asin]\" value=\""+itemObject['asin']+"\"/>";

	string += "<input type=\"hidden\" name=\"wfiuPlaylistItems["+itemObject['id']+"][album]\" value=\""+convertquotes(itemObject['album'])+ "\"/> ";
	string += "<input type=\"hidden\" name=\"wfiuPlaylistItems["+itemObject['id']+"][label]\" value=\""+convertquotes(itemObject['label'])+"\"/>";
	string += "<input type=\"hidden\" id=\"wfiuPlaylist_relyear_"+itemObject['id']+"\" name=\"wfiuPlaylistItems["+itemObject['id']+"][release_year]\" value=\""+itemObject['release_year']+"\"/>";

	string += "<input type=\"hidden\" name=\"wfiuPlaylistItems["+itemObject['id']+"][notes]\" value=\""+convertquotes(itemObject['notes'])+"\"/>";
	string += "<input type=\"hidden\" id=\"wfiuPlaylist_showme_"+itemObject['id']+"\" name=\"wfiuPlaylistItems["+itemObject['id']+"][showme]\" size=\"20\" value=\""+itemObject['showme']+"\"/>";
	string += "</div>";

	return string;
}

function wfiuPlaylistShrinkItem(id){
	//editing = false;
	var index = returnIndex(id);


	for (field in wfiuPlaylistItems[index]) { 

		if(field == "notes"){
			wfiuPlaylistItems[index][field] = document.getElementById("wfiuPlaylistItems["+id+"][" + field + "]").value;
		}else if(field != "id" && field != "showme" && jQuery("input[@name='wfiuPlaylistItems["+id+"][" + field + "]']").val() != undefined){
			wfiuPlaylistItems[index][field] = jQuery("input[@name='wfiuPlaylistItems["+id+"][" + field + "]']").val();
		}

	};
	var itemdiv = document.getElementById(playlistItemLabelPrefix+id);
	itemdiv.innerHTML= getItemHTMLshort(wfiuPlaylistItems[index]);

}

function getItemHTML(itemObject){
	//if( (artist == '' && title == '' ) || (!intable)){
	//	shrinkAllOtherItems();
	//}
	//editing = true;


	var string = getItemHead(itemObject, 'edit');

	string +="<div style='float:left;width:260px;'>";

	string += "<b>Artist:</b><br/> <input type=\"text\" class=\"wfiu_playlist_required\" id=\"wfiuPlaylistItems["+itemObject['id']+"][artist]\" name=\"wfiuPlaylistItems["+itemObject['id']+"][artist]\" size=\"25\" value=\""+itemObject['artist'].replace( /"/g, '&quot;' )  +"\" /><br/> ";
	if(itemObject['composer']){
		string += "<b>Composer:</b><br/> <input type='text' name=\"wfiuPlaylistItems["+itemObject['id']+"][composer]\" size='25' value=\""+itemObject['composer'].replace( /"/g, '&quot;' )  +"\" /><br/> ";
	}else{
		string += " <span id='wfiuPlaylist_composer_"+itemObject['id']+"'><a href='#wfiuPlaylistNoLink' onClick=\"javascript:insertComposerField('"+itemObject['id']+"','"+itemObject['composer']+"')\">Composer?</a></span><br/> ";
	}
	string += "<b>Title:</b><br/><input type=\"text\" class=\"wfiu_playlist_required\" id=\"wfiuPlaylistItems["+itemObject['id']+"][title]\" name=\"wfiuPlaylistItems["+itemObject['id']+"][title]\" size=\"25\" value=\""+ itemObject['title'].replace( /"/g, '&quot;' ) +"\" /> ";
	//string += "<br/>";
	string += "	</div> ";
	string += "	<div style='float:left;width:260px;'> ";

	if(itemObject['album']=='' && itemObject['label']=='' && itemObject['release_year']=='' ){	
		string += "	<a href='#wfiuPlaylistNoLink' onClick=\"javascript:document.getElementById('wfiuPlaylist_AlbumInfo_"+itemObject['id']+"').style.display='block';this.innerHTML='';\">Add Album Info</a>";
	}
	string += "	</div> ";
	string += "	<div id='wfiuPlaylist_AlbumInfo_"+itemObject['id']+"' style='float:left;width:260px;";

	if(itemObject['album']=='' && itemObject['label']=='' && itemObject['release_year']=='' ){
		string += "display:none;";
	}
	string += "' >";
	string += "	<b>ASIN:</b><br/> <input type=\"text\" name=\"wfiuPlaylistItems["+itemObject['id']+"][asin]\" size=\"20\" value=\""+itemObject['asin'].replace( /"/g, '&quot;' ) +"\"/><br/>";
	string += "	<b>Album:</b><br/> <input type=\"text\" name=\"wfiuPlaylistItems["+itemObject['id']+"][album]\" size=\"25\" value=\""+itemObject['album'].replace( /"/g, '&quot;' ) + "\"/><br/> ";
	string += "	<b>Label:</b><br/> <input type=\"text\" name=\"wfiuPlaylistItems["+itemObject['id']+"][label]\" size=\"25\" value=\""+itemObject['label'].replace( /"/g, '&quot;' )+"\"/> <br/>";
	string += "	<b>Release Year:</b><br/><input type=\"text\" id=\"wfiuPlaylist_relyear_"+itemObject['id']+"\" name=\"wfiuPlaylistItems["+itemObject['id']+"][release_year]\" size=\"5\" value=\""+itemObject['release_year']+"\" onkeyup='javascript:checkYear("+itemObject['id']+",this);'/> <br/>";
	string += "	</div>";

	//string +="<span style='margin-left:5px' class=\"button\" onClick=\"javascript:wfiuPlaylistShrinkItem('"+itemObject['id']+"');\">Shrink</span><br/><br/>";
	//string+="<div style='clear:'>";
	//string +="<span style='margin-left:5px; color: #662222;' class=\"button\" onClick=\"javascript:wfiuPlaylistRemoveItem('"+itemObject['id']+"');wfiuPlaylistDisplayItems();\">Remove</span><br/>";

	//string +="</div>";

	string+="<div style='clear:both'>";
	string += "	<b>Notes:</b><br/><textarea id=\"wfiuPlaylistItems["+itemObject['id']+"][notes]\" name=\"wfiuPlaylistItems["+itemObject['id']+"][notes]\" tabindex='6' rows='2' cols='50' >"+itemObject['notes']+"</textarea>";

	string += "	<input type=\"hidden\" id=\"wfiuPlaylist_showme_"+itemObject['id']+"\" name=\"wfiuPlaylistItems["+itemObject['id']+"][showme]\" size=\"20\" value='"+itemObject['showme']+"'/><br/>";

	string +="</div>";

	return string;
}

function yesShowItem(j){
	var show=false;
	for (var i=0; i < wfiuPlaylistItems.length; i++) {
		if(j == wfiuPlaylistItems[i]['id']){
			if(wfiuPlaylistItems[i]['showme']==true){
				return 2;
			}else{
				return 1;
			}
		}
	}
	return false;
}

	deleteIndex=(deleteIndex+1)%2;
function wfiuPlaylistDisplayItems() {
	//jQuery('#wfiuPlaylistContentPane').text("sdfsdf");
	/*if(wfiuPlaylistItems.length>0){
		jQuery('#wfiuPlaylistContentPane').text("");
	}*/

	var j=deleteIndex;
	for (var i=0; i < maxItems; i++) {
		existsOrShow = yesShowItem(i);
		if (existsOrShow==2) {//2 means it exists and showme is set to true, 1 means it exists, false means do not exist
			document.getElementById(playlistItemLabelPrefix+i).style.display = 'block';
			if((j%2)==0){
				document.getElementById(playlistItemLabelPrefix+i).style.backgroundColor = '#8E9CA7';
			}else{
				document.getElementById(playlistItemLabelPrefix+i).style.backgroundColor = '#A79C8E';
			}
			++j;
		} else{
			document.getElementById(playlistItemLabelPrefix+i).style.display = 'none';
		}
	}
}

function shrinkAllOtherItems(){
	dataChanged=true;
	for (i=0;i<wfiuPlaylistItems.length;i++) { 
		//alert(document.getElementById("wfiuPlaylistItems["+wfiuPlaylistItems[i]['id']+"][notes]"));
		if( document.getElementById("wfiuPlaylistItems["+wfiuPlaylistItems[i]['id']+"][notes]") != null){
			wfiuPlaylistShrinkItem(wfiuPlaylistItems[i]['id']);
		}
	}

}

function returnPosition(){
	var pos = -1;

	for (var i=0; i < wfiuPlaylistItems.length; i++) {
		if( (wfiuPlaylistItems[i]['showme']==true) && (wfiuPlaylistItems[i]['position']>pos)){
			pos = wfiuPlaylistItems[i]['position'];
		}
	}
	
	return (pos+1);
}

function wfiuPlaylistAddItem(artist, composer, title, album, label, asin, notes, release_year, intable) {
	dataChanged= true;
	var newPlaylistItem = new Array();
	newPlaylistItem['artist'] = artist;
	newPlaylistItem['composer'] = composer;
	newPlaylistItem['title'] = title;
	newPlaylistItem['album'] = album;
	newPlaylistItem['label'] = label;
	newPlaylistItem['asin'] = asin;
	newPlaylistItem['notes'] = notes;
	newPlaylistItem['release_year'] = release_year;

	newPlaylistItem['id'] = maxItems;
	newPlaylistItem['position'] = returnPosition();
	maxItems+=1;

	newPlaylistItem['showme'] = true;
	wfiuPlaylistItems[wfiuPlaylistItems.length] = newPlaylistItem;

	var newString  = "<div class='wfiuPlaylistItem' id='" + playlistItemLabelPrefix + wfiuPlaylistItems[wfiuPlaylistItems.length-1]['id']+"' >";
	//newString += "id=playlistItemContainer_"+wfiuPlaylistItems[wfiuPlaylistItems.length-1]['id']+"<br/>";

	if(intable){
		newString += getItemHTMLshort(wfiuPlaylistItems[wfiuPlaylistItems.length-1]);
	}else{
		newString += getItemHTML(wfiuPlaylistItems[wfiuPlaylistItems.length-1]);
	}

	newString += "</div>";
	jQuery('#wfiuPlaylistContentPane').append(newString);


}


function wfiuPlaylistRemoveItem(itemID) {
	var orig_wfiuPlaylistItems = wfiuPlaylistItems;
	var index = returnIndex(itemID);
	wfiuPlaylistItems = new Array();
	counter = 0;
	for (var i=0; i<orig_wfiuPlaylistItems.length; i++) {
		if(orig_wfiuPlaylistItems[i]['id'] != itemID) {
			wfiuPlaylistItems[counter] = orig_wfiuPlaylistItems[i];
			if(wfiuPlaylistItems[counter]['position'] > orig_wfiuPlaylistItems[index]['position']){
				wfiuPlaylistItems[counter]['position'] -= 1;
				var itemHeadDiv = document.getElementById(playlistItemLabelPrefix+"head_"+wfiuPlaylistItems[counter]['id']);
				if( document.getElementById("wfiuPlaylistItems["+wfiuPlaylistItems[counter]['id']+"][notes]") == null){
					itemHeadDiv.innerHTML= getItemHead(wfiuPlaylistItems[counter], 'short');
				}else{
					itemHeadDiv.innerHTML= getItemHead(wfiuPlaylistItems[counter], 'edit');
				}
			}
			counter++;
		}
	}
	//alert("#wfiuPlaylistItems["+itemID+"][artist]");
	//jQuery("#wfiuPlaylistItems[0][artist]").remove();
	//jQuery("#wfiuPlaylistItems["+itemID+"][artist]").removeClass('wfiu_playlist_required');
	//jQuery("#wfiuPlaylistItems["+itemID+"][title]").removeClass('wfiu_playlist_required');
	var hiddenShowme = document.getElementById('wfiuPlaylist_showme_'+itemID);
	hiddenShowme.value= 'false';
	jQuery('#playlistItemContainer_'+itemID).find('div').remove();
	deleteIndex=(deleteIndex+1)%2;
	
}

