var isAdmin = CharCountSettings.isAdmin;//obtained from wordpress plugin when localized
var optionalColor = '#D6FFD8';
var requiredColor = '#FFD7D7';

function check_excerpt_char_count(excerpt_obj, do_message){
	if(jQuery(excerpt_obj).val() == undefined){
		return;
	}
	jQuery(excerpt_obj).next().val(jQuery(excerpt_obj).val().length);

	if(jQuery(excerpt_obj).val().length < CharCountSettings.char_count_excerpt_min){
		jQuery(excerpt_obj).css('background-color', CharCountSettings.char_count_excerpt_optional =='off' ? requiredColor : optionalColor);
		if(do_message){
			alert("Please make the excerpt between " + CharCountSettings.char_count_excerpt_min + " and " + CharCountSettings.char_count_excerpt_max + " characters");
		}
	}else if(jQuery(excerpt_obj).val().length > CharCountSettings.char_count_excerpt_max){
		jQuery(excerpt_obj).css('background-color', CharCountSettings.char_count_excerpt_optional =='off' ? requiredColor : optionalColor);
		if(do_message){
			alert("Please shorten the excerpt to " + CharCountSettings.char_count_excerpt_max + " characters or less");
		}
	}else{
		jQuery(excerpt_obj).css('background-color', '');
	}
}

function check_teaser_char_count(teaser_obj, do_message){
	if(jQuery(teaser_obj).val() == undefined){
		return;
	}
	jQuery(teaser_obj).next().val(jQuery(teaser_obj).val().length);

	if(jQuery(teaser_obj).val().length < CharCountSettings.char_count_teaser_min){
		jQuery(teaser_obj).css('background-color', CharCountSettings.char_count_teaser_optional =='off' ? requiredColor : optionalColor);
		if(do_message){
			alert("Please make the teaser between " + CharCountSettings.char_count_teaser_min + " and " + CharCountSettings.char_count_teaser_max + " characters");
		}
	}else if(jQuery(teaser_obj).val().length > CharCountSettings.char_count_teaser_max){
		jQuery(teaser_obj).css('background-color', CharCountSettings.char_count_teaser_optional =='off' ? requiredColor : optionalColor);
		if(do_message){
			alert("Please shorten the teaser to " + CharCountSettings.char_count_teaser_max + " characters or less");
		}
	}else{
		jQuery(teaser_obj).css('background-color', '');
	}


}


function check_title_char_count(title_obj, do_message){
	if(jQuery(title_obj).val() == undefined){
		return;
	}
	jQuery(title_obj).next().val(jQuery(title_obj).val().length);

	if(jQuery(title_obj).val().length > CharCountSettings.char_count_title_max){
		jQuery(title_obj).css('background-color', CharCountSettings.char_count_title_optional =='off' ? requiredColor : optionalColor);
		if(do_message){
			alert("Please shorten the title to " + CharCountSettings.char_count_title_max + " characters or less");
		}
	}else if(jQuery(title_obj).val().length < CharCountSettings.char_count_title_min){
		jQuery(title_obj).css('background-color', CharCountSettings.char_count_title_optional =='off' ? requiredColor : optionalColor);
		if(do_message){
			alert("Please make the title between " + CharCountSettings.char_count_title_min + " and " + CharCountSettings.char_count_title_max + " characters");
		}
	}else{
		jQuery(title_obj).css('background-color', '');
	}


}

jQuery(document).ready(function($){
	if(isAdmin){
		$("#title").parent().prepend("<div style='text-align: right;'><span style='background-color: #D6FFD8;'>You are an admin and can override the character count limits.</span></div>");
	}

	var minText = '';
	if(CharCountSettings.char_count_excerpt_min > 0 ){
			minText = " / Min of " + CharCountSettings.char_count_excerpt_min + " characters";
	}
	$("#excerpt").after('<input type="text" value="0" maxlength="3" size="3" name="excerpt_char_count" readonly="" /> Max of ' + CharCountSettings.char_count_excerpt_max + ' characters' + minText);

	$("#excerpt").keydown(function(event){
		check_excerpt_char_count(this, false);
	});
	$("#excerpt").keyup(function(event){
		check_excerpt_char_count(this, false);
	});
	
	/**********************************************/
	if(CharCountSettings.char_count_teaser_min > 0 ){
			minText = " / Min of " + CharCountSettings.char_count_teaser_min + " characters";
	}
	$("#teaser").after('<input type="text" value="0" maxlength="3" size="3" name="teaser_char_count" readonly="" /> Max of ' + CharCountSettings.char_count_teaser_max + ' characters' + minText);

	$("#teaser").keydown(function(event){
		check_teaser_char_count(this, false);
	});
	$("#teaser").keyup(function(event){
		check_teaser_char_count(this, false);
	});
	
	/**********************************************/
	minText = '';
	if(CharCountSettings.char_count_title_min > 0 ){
			minText = " / Min of " + CharCountSettings.char_count_title_min + " characters";
	}
	$("#title").after('<input type="text" value="0" maxlength="2" size="2" name="title_char_count" readonly="" /> Max of 66 characters' + minText);

	$("#title").keydown(function(event){
		check_title_char_count(this, false);
	});
	$("#title").keyup(function(event){
		check_title_char_count(this, false);
	});
	/**********************************************/
	if( window.location.toString().match(/(post|page)-new.php/) ){
		check_excerpt_char_count($("#excerpt"), false);
		check_teaser_char_count($("#teaser"), false);
		check_title_char_count($("#title"), false);
	}else{
		check_excerpt_char_count($("#excerpt"), true);
		check_teaser_char_count($("#teaser"), true);
		check_title_char_count($("#title"), true);
	}
	
	
	/**********************************************/
	if(!isAdmin){
		$("#post").submit(function() {//for post.php
		
			if(CharCountSettings.char_count_excerpt_optional =='off'){
				if($("#excerpt").val().length > CharCountSettings.char_count_excerpt_max){
					alert("Please shorten the excerpt to " + CharCountSettings.char_count_excerpt_max + " characters or less");
					return false;
				}
				
				if($("#excerpt").val().length < CharCountSettings.char_count_excerpt_min){
					alert("Please make the excerpt between " + CharCountSettings.char_count_excerpt_min + " and " + CharCountSettings.char_count_excerpt_max + " characters");
					return false;
				}
			}
			
			if(CharCountSettings.char_count_teaser_optional =='off'){
				if($("#teaser").val().length > CharCountSettings.char_count_teaser_max){
					alert("Please shorten the teaser to " + CharCountSettings.char_count_teaser_max + " characters or less");
					return false;
				}
				
				if($("#teaser").val().length < CharCountSettings.char_count_teaser_min){
					alert("Please make the teaser between " + CharCountSettings.char_count_teaser_min + " and " + CharCountSettings.char_count_teaser_max + " characters");
					return false;
				}
			}

			if(CharCountSettings.char_count_title_optional =='off'){

				if($("#title").val().length > CharCountSettings.char_count_title_max){
					alert("Please shorten the title to " + CharCountSettings.char_count_title_max + " characters or less");
					return false;
				}
				if($("#title").val().length < CharCountSettings.char_count_title_min){
					alert("Please make the title between " + CharCountSettings.char_count_title_min + " and " + CharCountSettings.char_count_title_max + " characters");
					return false;
				}
				
			}

		});
	}

}); 
