<?php
/*
 * Used to add character counter functionality to the more fields plugin
 * 
 * Include this file in the more-fields.php file.  Add the functions below into the files/lines that 
 * 	are described above the function declaration.
 * They're all static functions, so there's no need to create an instance of the class... just call
 *  them statically...
 * Ex:  MoreFieldsCharacterCounter::text_fields_character_counter_checker_function();
 *  
 */

class MoreFieldsCharacterCounter
{
	
	
	//more-fields-manage-boxes.php line:426
	public static function edit_field_character_counter_fields($field)
	{
		?>
				 <tr id="charcount_container">
 					<th scope="row" valign="top"><?php _e('Max/Min Characters', 'more-fields'); ?></th>
 					<td>
 						<input type="text" name="min" value="<?php echo $field['min']; ?>" style='width: 50px' /> Min
 						<input type="text" name="max" value="<?php echo $field['max']; ?>" style='width: 50px' /> Max
 					</td>
 				</tr>
				 <tr id="enforce_limits_container">
 					<th scope="row" valign="top"><?php _e('Enforce limits?', 'more-fields'); ?></th>
 					<td>
 						<input type="checkbox" name="enforce" <?php echo $field['enforce'] ? 'checked="checked"' : ''; ?> />
 					</td> 
 				</tr>
	 	<?
	}
	
	//more-fields-manage-boxes.php line:147
	public static function save_character_counter_fields(&$box_field_index, $POST)
	{
		//New fields for character count limits on text and textarea fields
			$box_field_index['min'] = isset($POST['min']) ? attribute_escape($POST['min']) : 0;
			$box_field_index['max'] = isset($POST['max']) ? attribute_escape($POST['max']) : 350;
			//enforce the limits or just show a message?
			$box_field_index['enforce'] = isset($POST['enforce']) ? true : false;
	}
	
	
	//more-fields-manage-js.php line:48
	public static function manage_js_show_char_count()
	{
		?>
		/** Show/Hide character count limits and enforce checkbox **/
		if(jQuery('#type').val() == 'textarea'  | jQuery('#type').val() == 'text' ){
			jQuery('#charcount_container').show();
			jQuery('#enforce_limits_container').show();
		}else{
			jQuery('#charcount_container').hide();
			jQuery('#enforce_limits_container').hide();
		}
		<?
	}
	
	//more-fields-write.js line: 30
	public static function text_fields_character_counter_checker_function (){
	?>
	
/******* TEXT FIELDS CHARACTER COUNT CHECKER FUNCTION *********/
var optionalColor = '#FFD7D7';
var requiredColor = '#FF9A8D';

function check_object_char_count(obj, min, max, enforce, do_message){

	var fieldName = jQuery(obj).prev().text();
	fieldName = fieldName.substr(0, fieldName.length-1);
	
	if(jQuery(obj).val() == undefined){
		return;
	}
	
	jQuery(obj).next().val(jQuery(obj).val().length);

	if(jQuery(obj).val().length < min){
		jQuery(obj).css('background-color', enforce ? requiredColor : optionalColor);
		if(do_message){
			alert("Please make the field labeled \"" + fieldName + "\" between " + min + " and " + max + " characters");
		}
		return false;
	}else if(jQuery(obj).val().length > max){
		jQuery(obj).css('background-color', enforce ? requiredColor : optionalColor);
		if(do_message){
			alert("Please shorten the field labeled \"" + fieldName + "\" to " + max + " characters or less");
		}
		return false;
	}else{
		jQuery(obj).css('background-color', '');
	}
	return true;
}
/**************************************************************/
	
	<?	
	}

	//more-fields-write.js line: 212
	//$mf0->get_boxes(true);
	public static function character_count_checking($boxes_array){
	?>
/******Initialize charater count checking for all 'more-fields-plugin' text fields**********/	
	var minText='';
	var mfFields = [];
	var myfield;
	<?php $boxes = (array) $boxes_array ?>
	<?php foreach($boxes as $box) : ?>
		<?php foreach((array)$box['field'] as $index => $f) : ?>
			<?php  if($f['max']!='' && $f['min']=='')$f['min']=0;
				if ( ( ($f['type'] == 'textarea') || ($f['type'] == 'text') ) && ($f['min']!='' && $f['max']!='') ) : ?>
	if(<?php echo $f['min']; ?> > 0 ){
			minText = " / Min of <?php echo $f['min']; ?> characters";
	}
	
	myfield = [];
	myfield['obj'] = jQuery('<?php echo $f['type'] == 'text' ? 'input' : $f['type']; ?>[name="<?php echo sanitize_title($f['key']); ?>"]');
	myfield['min'] =  <?php echo $f['min']; ?>;
	myfield['max'] =  <?php echo $f['max']; ?>;
	myfield['enforce'] =  <?php echo $f['enforce'] ? 'true': 'false'; ?>;
	mfFields.push(myfield);	

	jQuery(mfFields[mfFields.length -1]['obj']).after('<input type="text" value="0" maxlength="3" size="3" name="<?php echo $f['key']; ?>_char_count" readonly="" /> Max of <?php echo $f['max']; ?> characters' + minText);
	jQuery(mfFields[mfFields.length -1]['obj']).bind("keydown", function(e){
		check_object_char_count(this, <?php echo $f['min']; ?>, <?php echo $f['max']; ?>, <?php echo $f['enforce'] ? 'true': 'false'; ?>, false);
	});
	jQuery(mfFields[mfFields.length -1]['obj']).bind("keyup", function(e){
		check_object_char_count(this, <?php echo $f['min']; ?>, <?php echo $f['max']; ?>, <?php echo $f['enforce'] ? 'true': 'false'; ?>, false);
	});
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endforeach; ?>

	/****** Check field's character count and [!]show message for old posts, otherwise, just highlight********/
	//if(window.location.toString().indexOf("post-new.php") != -1){
		for (i in mfFields) { 
			check_object_char_count(mfFields[i]['obj'], mfFields[i]['min'], mfFields[i]['max'], mfFields[i]['enforce'], false);
		}
	//}else{
	//	for (i in mfFields) { 
	//		check_object_char_count(mfFields[i]['obj'], mfFields[i]['min'], mfFields[i]['max'], mfFields[i]['enforce'], false);
	//	}
	//}
	/*******Force user to input a certain number of characters for fields which require */
	/************* a certain range of charaters*******/
	/*******Will not allow you to save until you fix it ********************/
	<?php if(!current_user_can('edit_plugins')) : ?>
	jQuery("#post").submit(function() {//for post.php
		for (i in mfFields) {
			if(mfFields[i]['enforce']){
				return check_object_char_count(mfFields[i]['obj'], mfFields[i]['min'], mfFields[i]['max'], mfFields[i]['enforce'], true);
			}
		}

	});
	<?php endif; ?>

	<?
	}
}
?>