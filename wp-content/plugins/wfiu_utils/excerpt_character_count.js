function check_excerpt_char_count(excerpt_obj, do_message){
	if(jQuery(excerpt_obj).val() == undefined){
		return;
	}
	jQuery(excerpt_obj).next().val(jQuery(excerpt_obj).val().length);

	if(jQuery(excerpt_obj).val().length > 160){
		jQuery(excerpt_obj).css('background-color', '#FF9A8D');
		if(do_message){
			alert("Please shorten the excerpt to 160 characters or less");
		}
	}else{
		jQuery(excerpt_obj).css('background-color', '');
	}

}

jQuery(document).ready(function($){
	$("#excerpt").after('<input type="text" value="0" maxlength="3" size="3" name="excerpt_char_count" readonly="" /> Max of 160 characters');
	check_excerpt_char_count($("#excerpt"), true);

	$("#excerpt").keydown(function(event){
		check_excerpt_char_count(this, false);
	});
	$("#excerpt").keyup(function(event){
		check_excerpt_char_count(this, false);
	});

	$("#post").submit(function() {//for post.php
		if($("#excerpt").val().length > 160){
			alert("Please shorten the excerpt to 160 characters or less");
			return false;
		}

	});

}); 
