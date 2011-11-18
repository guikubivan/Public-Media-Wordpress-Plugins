<?php
/*
Plugin Name: Merlin customizations
Description: add merlin-spec custom fields to wordpress.
Author: Chris Ege | WTTW-11
Version: 0.2
License: GPL
*/

/*  
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


//
/* begin custom field/form functions */
//


/* turn on theme support for post thumbnails, add 16x9 merlin-sized size preset to be called in feed */
if (function_exists('add_theme_support')) {
	add_theme_support( 'post-thumbnails' );
	add_image_size('thumbnail-merlin', 288, 162, true);
	add_image_size('thumbnail-merlin-large', 640, 360, true);
}


/* Use the admin_menu action to define the custom boxes */
add_action('admin_menu', 'merlin_add_custom_box');
add_action('activate_wordpress-merlin/merlin.php', 'activate');
add_action('deactivate_wordpress-merlin/merlin.php', 'deactivate');

/* Adds a custom section to the "side" of the post edit screen */
function merlin_add_custom_box() {
     add_meta_box('merlin', 'Merlin Data', 'merlin_custom_box', 'post', 'side', 'high');
}

function activate() {
	
	if(!category_exists('Merlin')) {
	//Define the merlin category
	$my_cat = array('cat_name' => 'Merlin', 
					'category_description' => '', 
					'category_nicename' => 'merlin', 
					'category_parent' => '');

	// Create the category
	$my_cat_id = wp_insert_category($my_cat);
	}
}

function deactivate(){
	$my_cat_id = get_cat_ID('merlin'); 
  	wp_delete_category($my_cat_id);	
}

/* prints the custom field in the new custom post section */
function merlin_custom_box() {
     //get post meta value
    global $post;
	$program_name = get_post_meta($post->ID, 'program_name', true);
	$producing_member_station = get_post_meta($post->ID, 'producing_member_station',  true);
	$owner_member_station = get_post_meta($post->ID, 'owner_member_station', true);
	$thumbnail = get_post_meta($post->ID, 'thumbnail', true);
	$expiration_date = get_post_meta($post->ID, 'expiration_date', true);
	$distribution = get_post_meta($post->ID, 'distribution', true);
	$options = get_option('merlin_channel_elements');
	$primary_category = get_post_meta($post->ID, 'merlin_primary_category', true);
	$secondary_category = get_post_meta($post->ID, 'merlin_secondary_category', true);
//	$add_to_merlin = get_post_meta($post->ID, 'add_to_merlin', true);
	
	$parents = array();
	$parents[0] = "Arts & Entertainment";
	$parents[1] = "Culture & Society";
	$parents[2] = "Health";
	$parents[3] = "Parents";
	$parents[4] = "Home & How-to";
	$parents[5] = "History";
	$parents[6] = "Science & Nature";
	$parents[7] = "News & Public Affairs";
	$parents[8] = "Technology";
	
	$programs = array();
	$programs[0][0]="Film & Television"; 
	$programs[0][1]="Performing Arts"; 
	$programs[0][2]="Music";
	$programs[0][3]="Fine Arts";
	$programs[0][4]="Literature & Writing";
	$programs[0][5]="Drama"; 
	$programs[0][6]="Folk Art & Crafts"; 
	$programs[0][7]="Biography & Profiles";
	
	$programs[1][0]="Religion & Beliefs";
	$programs[1][1]="Community";
	$programs[1][2]="Race & Ethnicity";
	$programs[1][3]="Gender & Sexual Identity";
	$programs[1][4]="Food";
	$programs[1][5]="Travel";
	$programs[1][6]="Media & Technology";
	$programs[1][7]="Sports & Recreation"; 
	$programs[1][8]="Holidays";
	$programs[1][9]="People & Profiles"; 
	
	$programs[2][0]="Health Care & Illnesses";
	$programs[2][1]="Fitness & Nutrition";
	$programs[2][2]="Aging";
	$programs[2][3]="Women";
	$programs[2][4]="Lifestyle";
	$programs[2][5]="Caregiving";
	
	$programs[3][0]="Child Development";
	$programs[3][1]="Education";
	$programs[3][2]="Fun & Games ";
	$programs[3][3]="Healthy Kids";
	$programs[3][4]="Issues & Advice ";
	$programs[3][5]="Birthday Parties";
		
	$programs[4][0]="Antiques & Collectibles";
	$programs[4][1]="Cooking";
	$programs[4][2]="Gardening";
	$programs[4][3]="Home Improvement";
	$programs[4][4]="Personal Finance";
	
	$programs[5][0]="Civilizations";
	$programs[5][1]="World";
	$programs[5][2]="United States";
	$programs[5][3]="Prehistoric";
	$programs[5][4]="Ancient & Classical";
	$programs[5][5]="Middle Ages";
	$programs[5][6]="Renaissance";
	$programs[5][7]="Enlightenment";
	$programs[5][8]="Victorian";
	$programs[5][9]="Modern";
	$programs[5][10]="Archeaology";
	$programs[5][11]="Geneology";
	$programs[5][12]="Archeaology";
	$programs[5][13]="Geneology";
	$programs[5][14]="War & Conflict";
	$programs[5][15]="Biography & Profiles";
	
	$programs[6][0]="Earth";
	$programs[6][1]="Environment";
	$programs[6][2]="Natural History";
	$programs[6][3]="Weather & Natural Disasters";
	$programs[6][4]="Animals";
	$programs[6][5]="Psychology & The Mind";
	$programs[6][6]="Biology & Chemistry";
	$programs[6][7]="Astronomy";
	$programs[6][8]="Physics & Mathematics";
	$programs[6][9]="Forensics & Investigations";
	$programs[6][10]="Biography & Profiles";
	
	$programs[7][0]="Business & Economy";
	$programs[7][1]="Government";
	$programs[7][2]="Politics";
	$programs[7][3]="National Security";
	$programs[7][4]="Global Affairs";
	$programs[7][5]="Environment";
	$programs[7][6]="Science & Technology";
	$programs[7][7]="Women";
	$programs[7][8]="Race & Ethnicity";
	$programs[7][9]="Social Issues";
	$programs[7][10]="Health";
	$programs[7][11]="Education";
	$programs[7][12]="Religion & Beliefs";
	$programs[7][13]="Arts & Entertainment";
	$programs[7][14]="Media";
	$programs[7][15]="Sports";
	$programs[7][16]="Opinion";
	$programs[7][17]="Biography & Profiles";
	
	$programs[8][0]="Space";
	$programs[8][1]="Energy & Environment";
	$programs[8][2]="Transportation & Infrastructure";
	$programs[8][3]="Military & Espionage";
	$programs[8][4]="Health";
	$programs[8][5]="Communications & Media";
	$programs[8][6]="Society";
	$programs[8][7]="Biography & Profiles";
			
	
	if ($program_name == null) {
		$program_name = $options['program_name'];
	}
	
	if ($producing_member_station == null) {
		$producing_member_station = $options['producing_member_station'];
	}
	
	if ($owner_member_station == null) {
		$owner_member_station = $options['owner_member_station'];
	}
	//Update by Priyank starts here
/*	if ($thumbnail == null) {
		//Get the Thumbnail URL. if large thumb exists, send that. otherwise, send small.
		$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail-merlin-large');
		if ((preg_match("/640x360/", $src[0])) == false) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail-merlin');
		}
		$thumbnail = $src[0]; 
	}
*/
	//Update by Priyank ends here
   
  // use nonce for verification
     echo '<input type="hidden" name="merlin_noncename" id="merlin_noncename" value="'.wp_create_nonce('merlin').'" />';

     // The actual fields for data entry
		/*
		to add:
		media:description (same as description?)
		pbscontent:program_name
		pbscontent:producing_member_station
		pbscontent:owner_member_station
		media:thumbnail (288x162)
		dcterms:valid (expiration date)
		pbscontent:distribution
		*/

//	 echo '<p><input type="checkbox" name="add_to_merlin" value="Yes"';
//	 if($add_to_merlin == "Yes")
//	 echo 'checked="yes"';
//	 echo '/>';
//	 echo '<label for="add_to_merlin"> Add to Merlin Feed</label></p>';	
		
     echo '<p><label for="program_name">Program Name</label>';
     echo '<input type="text" name="program_name" id="program_name" size="30" value="'.$program_name.'" maxlength="150"  /></p>';

     echo '<p><label for="producing_member_station">Producing Member Station</label>';
     echo '<input name="producing_member_station" type="text" id="producing_member_station" value="'.$producing_member_station.'" size="30" maxlength="150" /></p>';

     echo '<p><label for="owner_member_station">Owner Member Station</label>';
     echo '<input type="text" name="owner_member_station" id="owner_member_station" size="30" value="'.$owner_member_station.'" maxlength="150" /></p>';

	//Update by Priyank starts here
//     echo '<p><label for="thumbnail">Thumbnail (url)</label>';
//     echo '<input type="text" name="thumbnail" id="thumbnail" size="30" value="'.$thumbnail.'" maxlength="150" /></p>';
	//Update by Priyank ends here

     echo '<p><label for="expiration_date">Expiration date</label>';
     echo '<input type="text" name="expiration_date" id="expiration_date" size="30" value="'.$expiration_date.'" maxlength="150" /></p>';
     
     echo '<p><label for="distribution">Distribution</label> ';
     echo '<select name="distribution" id="distribution"> <option value="local" selected="selected">local</option><option value="national">national</option></select>';

	 echo '<p><label for="primary_category">Primary Category </label>';
	 echo '<select name="primary_category" id="primary_category"> <option></option>'; 
     for($i=0; $i<sizeof($parents); $i++){
	 	echo '<option disabled="disabled" style="font-weight: bold" value="'.$parents[$i].'">' . $parents[$i] . '</option>\n';
		foreach ($programs as $j=>$elem) {
			if($j == $i){
				foreach ($elem as $program) {
			echo '<option value="'.$parents[$i].'/'.$program.'" ';
			if($primary_category == $parents[$i]."/".$program)
				echo 'selected="selected"';
			echo '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $program;
			
			echo '</option>\n';
			}
			}
		}
	}
	 echo '</select>';   
		   
	 echo '<p><label for="secondary_category">Secondary Category </label>';
	 echo '<select name="secondary_category" id="secondary_category"> <option></option>'; 
	 for($i=0; $i<sizeof($parents); $i++){
	 	echo '<option disabled="disabled" style="font-weight: bold" value="'.$parents[$i].'">' . $parents[$i] . '</option>\n';
		foreach ($programs as $j=>$elem) {
			if($j == $i){
				foreach ($elem as $program) {
			echo '<option value="'.$parents[$i].'/'.$program.'" ';
			if($secondary_category == $parents[$i]."/".$program)
				echo 'selected="selected"';
			echo '>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $program;
			
			echo '</option>\n';
			}
			}
		}
	}	   
	 echo '</select><br>';
	 
}

/* use save_post action to handle data entered */
add_action('save_post', 'merlin_save_postdata');

/* when the post is saved, save the custom data */
function merlin_save_postdata($post_id) {
     // verify this with nonce because save_post can be triggered at other times
     if (!wp_verify_nonce($_POST['merlin_noncename'], 'merlin')) return $post_id;

     // do not save if this is an auto save routine
     if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

     $program_name = $_POST['program_name'];
     update_post_meta($post_id, 'program_name', $program_name);
     
     $producing_member_station = $_POST['producing_member_station'];
     update_post_meta($post_id, 'producing_member_station', $producing_member_station);

     $owner_member_station = $_POST['owner_member_station'];
     update_post_meta($post_id, 'owner_member_station', $owner_member_station);

     //Update by Priyank starts here
    // $thumbnail = $_POST['thumbnail'];
    
    	$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail-merlin-large');
		if ((preg_match("/640x360/", $src[0])) == false) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumbnail-merlin');
		}
		$thumbnail = $src[0];
     //Update by Priyank ends here
     update_post_meta($post_id, 'thumbnail', $thumbnail);
     
     $expiration_date = $_POST['expiration_date'];
     update_post_meta($post_id, 'expiration_date', $expiration_date);
     
     $thumbnail = $_POST['distribution'];
     update_post_meta($post_id, 'distribution', $distribution);
	 
	 $primary_category = $_POST['primary_category'];
	 update_post_meta($post_id, 'merlin_primary_category', $primary_category);
	 
	 $secondary_category = $_POST['secondary_category'];
	 update_post_meta($post_id, 'merlin_secondary_category', $secondary_category);
	 
//	 $add_to_merlin = $_POST['add_to_merlin'];
//	 update_post_meta($post_id, 'add_to_merlin', $add_to_merlin);
	 
	 //$idObj = get_category_by_slug('merlin'); 
  	 //$id = $idObj->term_id;
	 //$save_cats = array();
	 //$save_cats = wp_get_post_categories($post_id);
	 //$save_cats[] = $id;
	 //wp_set_post_categories($post_id, $save_cats);
	 
	 
	
	 

}

//
/* end custom field/form functions */
//

//
/* begin custom RSS Feed functions */
//

add_action('do_feed_merlin', 'create_merlin_customfeed', 20, 1); 

// Loads merlin RSS template
function create_merlin_customfeed() {
	load_template ( WP_PLUGIN_DIR.'/wordpress-merlin/feed-merlin.php'); 
}



/* 	Generates merlin-spec category tags:
	1)	adds domain="PBS/taxonomy/topic" to category tag
	2)	adds parent category with "/" delimiter

*/
function get_the_category_merlin($post_id, $type = null) {
	if ( empty($type) )
		$type = get_default_feed();
	$categories = get_the_category();
	$tags = get_the_tags();
	$the_list = '';
	$cat_names = array();

	$filter = 'rss';
	if ( 'atom' == $type )
		$filter = 'raw';

/*
	if ( !empty($categories) ) foreach ( (array) $categories as $category ) {
		$cat_names[] = substr_replace((get_category_parents($category, FALSE, '/')),"",-1);
	}
		
	$cat_names = array_unique($cat_names);

	foreach ( $cat_names as $cat_name ) {
			$the_list .= "\t\t<category domain=\"PBS/taxonomy/topic\"><![CDATA[" . @html_entity_decode( $cat_name, ENT_COMPAT, get_option('blog_charset') ) . "]]></category>\n";
	}
*/
	$primary_category = get_post_meta($post_id, 'merlin_primary_category', true);
	$secondary_category = get_post_meta($post_id, 'merlin_secondary_category', true);
	
	$pos = strpos($primary_category, '/');
	$primary_category_parent = substr($primary_category, 0, $pos);
	$the_list .= "\t\t<category domain=\"PBS/taxonomy/topic\"><![CDATA[".$primary_category_parent."]]></category>\n";
	$the_list .= "\t\t<category domain=\"PBS/taxonomy/topic\"><![CDATA[".$primary_category."]]></category>\n";
	
	$pos = strpos($secondary_category, '/');
	$secondary_category_parent = substr($secondary_category, 0, $pos);
	$the_list .= "\t\t<category domain=\"PBS/taxonomy/topic\"><![CDATA[".$secondary_category_parent."]]></category>\n";
	$the_list .= "\t\t<category domain=\"PBS/taxonomy/topic\"><![CDATA[".$secondary_category."]]></category>\n";
	
	return apply_filters('the_category_merlin', $the_list, $type);
}


function the_category_merlin($post_id, $type = null) {
	echo get_the_category_merlin($post_id, $type);
}

// function to shorten wordpress excerpt by character count
// this is needed because wordpress's built-in excerpt functions are all based on word count
function shorten_text($text, $chars) {
        $text = $text." ";
        $text = substr($text,0,$chars);
        // trim to last space in string
        $text = substr($text,0,strrpos($text,' '));
        //remove wp's [...] string so it doesnt' get doubled-up when we add our own.
        $text = rtrim($text,'[...]');
        $text = $text." [...]";
        return $text;
}
 
 



//
/* end custom RSS Feed functions */
//


//
/* begin plugin settings */
//

add_action('admin_init', 'merlin_options_init' );
add_action('admin_menu', 'merlin_options_add_page');

/* Init plugin options to white list our options */
function merlin_options_init(){
	register_setting( 'merlin_options', 'merlin_channel_elements' );
}

// Add menu page
function merlin_options_add_page() {
	add_options_page('Merlin Options', 'Merlin Options', 'manage_options', 'merlin-options', 'merlin_options_do_page');
}

// Draw the menu page itself
/*	channel elements reference:
	all are stored in the array: merlin_channel_elements[]
		category
		image
		program_name
		producing_member_station
		owner_member_station   */
function merlin_options_do_page() {
	?>
	<div class="wrap">
		<h2>Merlin Feed Options</h2>
		<form method="post" action="options.php">
			<?php settings_fields('merlin_options'); ?>
			<?php $options = get_option('merlin_channel_elements'); ?>
			<h3>RSS Channel Elements</h3>
			<p>Channel elements not listed on this form are available under wordpress's general settings menu.</p>
			<p>Values entered here for Program Name, Producing Member Station, and Owner Member Station will be used as defaults for item-level elements.</p>
			<table class="form-table">
				<tr valign="top"><th scope="row">Category</th>
					<td><input name="merlin_channel_elements[category]" type="text" value="<?php echo $options['category']; ?>" />
					<p>Default categories for all items in the channel.</p>
				
					</td>
				</tr>
				<tr valign="top"><th scope="row">Image</th>
					<td>
					<p>URL:	<input type="text" name="merlin_channel_elements[image]" value="<?php echo $options['image']; ?>" /></p>
					<p>Title: <input type="text" name="merlin_channel_elements[image_title]" value="<?php echo $options['image_title']; ?>" /></p>
					<p>Link: <input type="text" name="merlin_channel_elements[image_link]" value="<?php echo $options['image_link']; ?>" /></p>
					<p>Specifies an image that can be displayed with the channel.</p> </td>
				</tr>
				<tr valign="top"><th scope="row">Program Name</th>
					<td>
					<input type="text" name="merlin_channel_elements[program_name]" value="<?php echo $options['program_name']; ?>" />
					<p>The name of the program associated with the content item. Must match with information in the program database.</p> 
					</td>
				</tr>
				<tr valign="top"><th scope="row">Producing Member Station</th>
					<td><input type="text" name="merlin_channel_elements[producing_member_station]" value="<?php echo $options['producing_member_station']; ?>" />
					<p>The station call letters of the member station that is producing the content in the content item. In the case of national content, leave as blank.</p> </td>
				</tr>
				<tr valign="top"><th scope="row">Owner Member Station</th>
					<td><input type="text" name="merlin_channel_elements[owner_member_station]" value="<?php echo $options['owner_member_station']; ?>" />
					<p>The station call letters of the member station that has ownership (i.e. is allowed to maintain) the content item.</p> </td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	<?php	
}

?>