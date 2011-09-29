<?php
/*
This is the wordpress_slideshow_classes.php file with the desired functions 
from the previous version of the plugin
Contains functionality for plugin activation, creation of wpss tables, customizing upload image form,
slideshow manager and saving fixed properties of the photo.
*/

$tab_order = 100;


class plugin_activation{
	public $fieldname = 'slideshow_id';
	public $plugin_prefix = 'wpss_';
	public $slideshow_props = Array('id','title','photo_credit','description','geo_location');
	public $photo_props = Array('id','title', 'caption', 'geo_location', 'photo_credit','description','url');
	public $fixed_photo_props = array('geo_location', 'photo_credit','latitude','longitude', 'original_url');
	public $t_s, $t_spr, $t_p, $t_pm, $t_pmr;
	public $default_style_photo = 'wpss_program_single_new.xsl';
	public $default_style_slideshow = 'wpss_program_single_new.xsl';
	public $default_style_post_image = 'wpss_program_thumb_small.xsl';
	public $default_style_photo_left = 'wpss_program_single_new_left.xsl';
	public $default_style_slideshow_left = 'wpss_program_single_new_left.xsl';
	public $default_style_photo_right = 'wpss_program_single_new_right.xsl';
	public $default_style_slideshow_right = 'wpss_program_single_new_right.xsl';
		
	public $default_multiple_slideshows = true;
	public $photo_id_translation = Array();
	public $wpdb;
	public $tab_order = 100;
	
	public $plugin_path = "/ipm-wordpress-slideshow-MVC/";#folder name in wp-content/plugins, updated in constructor
    public $stylesheets_path = "";#Default is set in constructor if none given here
	
	public function __construct(){
	    	global $wpdb;
		$this->wpdb = $wpdb;
        $this->plugin_path =  ABSPATH.PLUGINDIR.$this->plugin_path;
        $this->stylesheets_path =  $this->plugin_path . "stylesheets/";
		$this->t_s = $wpdb->prefix.$this->plugin_prefix."slideshows";
		$this->t_p = $wpdb->prefix.$this->plugin_prefix."photos";
		$this->t_spr = $wpdb->prefix.$this->plugin_prefix."slideshow_photo_relations";
		$this->t_pm = $wpdb->prefix.$this->plugin_prefix."photo_meta";
		$this->t_pmr = $wpdb->prefix.$this->plugin_prefix."photo_meta_relations";

		$this->option_default_style_photo = $plugin_prefix.'photo_stylesheet';
		$this->option_default_style_slideshow = $plugin_prefix.'slideshow_stylesheet';
		$this->option_default_style_post_image = $plugin_prefix.'post_image_stylesheet';
		$this->option_multiple_slideshows = $plugin_prefix.'multiple_slideshows';
		$this->postmeta_post_image = $plugin_prefix.'post_image';
		$this->utils = new IPM_Utils();
		
	
	}
    
//This function creates all the required wpss tables in the database	
	function activate(){
	    global $wpdb;
	
		//need mysql >= 5.0.3
		$table = $this->t_s;
		$query = "CREATE TABLE $table (
		ID INT(9) NOT NULL AUTO_INCREMENT,
		title VARCHAR(255) NOT NULL,
		photo_credit VARCHAR(255),
		description VARCHAR(1023),
		geo_location VARCHAR(255),
		latitude VARCHAR(10),
		longitude VARCHAR(10),
		thumb_id INT(9),
		PRIMARY KEY (ID),
		INDEX(photo_credit)
		) CHARACTER SET utf8 COLLATE utf8_general_ci ;";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';

		$table = $this->t_spr;
		$query = "CREATE TABLE $table (
		slideshow_id INT(9) NOT NULL,
		photo_id INT(9) NOT NULL,
		photo_order INT(3) NOT NULL,
		PRIMARY KEY (slideshow_id, photo_id),
		INDEX(slideshow_id),
		INDEX(photo_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci ;";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';


		$table = $this->t_p;
		$query = "CREATE TABLE $table (
		photo_id INT(9) NOT NULL AUTO_INCREMENT,
		wp_photo_id INT(9) NOT NULL,
		PRIMARY KEY (photo_id),
		INDEX(wp_photo_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci ;";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';


		$table = $this->t_pmr;
		$query = "CREATE TABLE $table (
		photo_id INT(9) NOT NULL,
		meta_id INT(3) NOT NULL,
		meta_value VARCHAR(1023),
		PRIMARY KEY (photo_id, meta_id),
		INDEX(photo_id),
		INDEX(meta_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci ;";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';


		$table = $this->t_pm;
		$query = "CREATE TABLE $table (
		meta_id INT(9) NOT NULL AUTO_INCREMENT,
		meta_name VARCHAR(255) NOT NULL,
		PRIMARY KEY (meta_id),
		UNIQUE KEY (meta_name)
		) CHARACTER SET utf8 COLLATE utf8_general_ci ;";
		echo $this->utils->maybe_create_table($table,$query) ? "Error for : $query \n<br />" : '';

		/*Options */
		add_option($this->option_default_style_slideshow, $this->default_style_slideshow);
		add_option($this->option_default_style_photo, $this->default_style_photo);
		add_option($this->option_default_style_photo, $this->default_style_photo);
		add_option($this->option_default_style_post_image, $this->default_style_post_image);
		if($this->default_multiple_slideshows) add_option($this->option_multiple_slideshows, $this->default_multiple_slideshows);
	}
        
//This function returns the plugin url        
	function plugin_url(){
		$result = get_bloginfo('url').'/wp-content/plugins/ipm-wordpress-slideshow-MVC/';
		return $result;
	}

//This function returns a list of all the stylesheets present in the plugin folder
	function show_stylesheet_list($name, $selected = ''){
		$stylesheets = $this->utils->get_files_array($this->plugin_path . "stylesheets");
		echo "<select name=\"$name\">";
		foreach($stylesheets as $sf){
			if(!preg_match("/.*\.xsl$/",$sf))continue;
			$iselected = '';
			if(basename($sf) == $selected){
				$iselected = 'SELECTED="SELECTED"';
			}
			echo "<option $iselected value=\"".basename($sf)."\">".basename($sf)."</option>";
		}
		echo "</select>";
	}

//This function is used to update the options table in the database
	function add_or_update_setting($option){
		if($_POST[$option]){

			if(get_option($option)){
				update_option($option,$_POST[$option]);
			}else{
				add_option($option,$_POST[$option]);//probably never runned
			}
			return true;
		}
		return false;
	}

//This function provides the front-end for the slideshow manager page
	function plugin_settings(){
		$updated = false;
		$updated = $this->add_or_update_setting($this->option_default_style_slideshow);
		$updated = $this->add_or_update_setting($this->option_default_style_photo);
		$updated = $this->add_or_update_setting($this->option_default_style_post_image);


		if($_POST[$this->option_multiple_slideshows]){
			$updated = true;
			if(get_option($this->option_multiple_slideshows) && $_POST[$this->option_multiple_slideshows]){
				update_option($this->option_multiple_slideshows,$_POST[$this->option_multiple_slideshows]);
			}else if($_POST[$this->option_multiple_slideshows]){
echo ' hello' ;
				update_option($this->option_multiple_slideshows,true);
			}
		}else if($_POST['wpss_settings_update']){
			$updated = true;
			delete_option($this->option_multiple_slideshows,true);
		}

		if($updated){
			echo "<div id='message' class='updated fade'>Slideshow settings updated succesfully.</div>";
		}
		if(!($slideshow_stylesheet = get_option($this->option_default_style_slideshow))){
			$slideshow_stylesheet = $this->default_style_slideshow;
		}
		if(!($photo_stylesheet = get_option($this->option_default_style_photo))){
			$photo_stylesheet = $this->default_style_photo;
		}
		if(!($post_image_stylesheet = get_option($this->option_default_style_post_image))){
			$post_image_stylesheet = $this->default_style_post_image;
		}

		$checked = get_option($this->option_multiple_slideshows) ? 'CHECKED="CHECKED"' : '' ;
?>
		<h3>Plugin Settings</h3>		



		<form action='?page=<?php echo $_GET['page']; ?>#wpss_settings' name='wpss_settings_form' method="post">
		<h5>Allow multiple slideshows</h5>
		<p><input type='checkbox' <?php echo $checked; ?> name="<?php echo $this->option_multiple_slideshows; ?>" /> Yes </p>
		<h5>Default slideshow stylesheet</h5>
		<?php $this->show_stylesheet_list($this->option_default_style_slideshow, $slideshow_stylesheet); ?>
		<h5>Default single-photo stylesheet</h5>
		<?php $this->show_stylesheet_list($this->option_default_style_photo, $photo_stylesheet); ?>
		<h5>Default post image stylesheet</h5>
		<?php $this->show_stylesheet_list($this->option_default_style_post_image, $post_image_stylesheet); ?>

		<p><input type='submit' name='wpss_settings_update' value="Update" /></p>
		</form>
		<hr/>
<?php

	}

//This functions hooks upto the Slideshow Manager option is the side menu
	function manage_slideshows(){
?>
		<div class='wrap'>
		<h2>Slideshow Management</h2>
		<div id='wpss_settings'>
	<?php echo $this->plugin_settings(); ?>
		</div>

<?php 
	}

	function getPhoto($pid){		
		$photo = new IPM_Photo($this, $pid);
		$temp = $photo->getPhoto_emulator();
		
		return $temp;
	}

	
	function process_thumb_url($url, $id=''){
		$pic =  get_post_meta($id, '_wp_attachment_metadata');
		if(!is_array($pic))return $url;
		if(isset($pic[0]['sizes']['thumbnail'])){
			return str_replace(basename($url), $pic[0]['sizes']['thumbnail']['file'], $url);
	
		}
		else if(!isset($pic[0]['sizes']['thumbnail']['width']) || !isset($pic[0]['sizes']['thumbnail']['height'])){
			return $url;
		}
		
		$tw = $pic[0]['sizes']['thumbnail']['width'];//get_option('thumbnail_size_w');
		$th = $pic[0]['sizes']['thumbnail']['height'];
		//echo "UrL: $url";
		if(!preg_match("/\-${tw}x${th}\./", $url)){
		//if(false){
			$base = basename($url);
			$dot = strrpos($base, '.');
			$name = substr($base, 0, $dot);
			$new_name = $name . "-${tw}x${th}";
			$new_url = str_replace($name, $new_name, $url);
			//if(@fopen($new_url,'r')!== false){
			return $new_url;
			//}
		}
		return $url;
	}

//This function is used to customize the upload image form
	function media_field($name, $label, $id, $size='100%', $required=false){

		$string = "<tr>";
		$string .= "<th class='label' valign='top' scope='row'>
					<label for='attachments[$id][$name]'>
					<span class='alignleft'>$label</span>
					<span class='alignright'>";
		$string .= $required ? "<abbr class='required' title='required'>*</abbr>" : '';
		$string .= "</span>
					<br class='clear'/>
					</label>
					</th>";
		$class = $required ? "class='".$this->plugin_prefix."required'" : '';
		$string .= "<td class='field'>";
		if(isset($_REQUEST['img_id']) && $_REQUEST['img_id']){
			$oldphoto = $this->getPhoto($_REQUEST['img_id']);
			$val = htmlentities(stripslashes(get_post_meta($oldphoto['wp_photo_id'], $name, true)), ENT_QUOTES);
		}else{
			$val = htmlentities(stripslashes(get_post_meta($id, $name, true)), ENT_QUOTES);
		}
		if($name=='geo_location'){
			$location = $val;
						
			$lat = get_post_meta($id, 'latitude', true);
			$lat = $lat ? $lat : '0';
			$long = get_post_meta($id, 'longitude', true);
			$long = $long ? $long : '0';

			$string .= "<input id='attachments[$id][$name]' type='text' style='width:$size' value='".$location."' id='attachments[$id][$name]' name='attachments[$id][$name]' onkeyup=\"get_google_coordinates(this.value,'attachments[$id][latitude]','attachments[$id][longitude]');\" onblur=\"get_google_coordinates(this.value,'attachments[$id][latitude]','attachments[$id][longitude]');\" />";
			$string .= "<input id='attachments[$id][latitude]' type='hidden' style='width:$size' value='".$lat."' name='attachments[$id][latitude]'/>";
			$string .= "<input id='attachments[$id][longitude]' type='hidden' style='width:$size' value='".$long."' name='attachments[$id][longitude]'/>";
			//$location = $location ? $location : 'N/A';
			$string .= " <span class='button' onClick=\"get_google_coordinates(document.getElementById('attachments[$id][geo_location]').value,'attachments[$id][latitude]','attachments[$id][longitude]'); loadMap($id, document.getElementById('attachments[$id][geo_location]').value, document.getElementById('attachments[$id][latitude]').value, document.getElementById('attachments[$id][longitude]').value);jQuery('#hide_button_$id').show();\" >Show in map</span>
				<span id='hide_button_$id' style='display:none' class='button' onClick=\"document.getElementById('map_canvas_$id').style.display = 'none';this.style.display='none';\" >Hide</span>";
			$string .= "<div id=\"map_canvas_$id\" style=\"width: 300px; height: 200px;display:none;\"></div>";

		}else{
			$string .= "<input id='attachments[$id][$name]' $class type='text' style='width:$size' value='".$val."' name='attachments[$id][$name]'/>";
		}
		$string .= "</td></tr>";

		return $string;
	}
	
//This function is used to customize the upload image form	
	public function mediaItem($string, $post){
		global $post_mime_types;

		if(preg_match("/image/i",$post->post_mime_type)){
			$id=$post->ID;
			$thumb_url = wp_get_attachment_image_src( $post->ID, 'thumbnail');

			$thumb_url = $thumb_url[0];
			$thumb_url = $this->process_thumb_url($thumb_url, $post->ID);
			$items['url'] = $thumb_url;
			$img_id = $_REQUEST['img_id'];
			
			$fields_new = get_attachment_fields_to_edit($post, $errors);
			$img_title = $fields_new['post_title']['value'];
			
			$string .= "<br /><br />";
			$string .="<span id='insert_photo_span_$id' class='alignleft'  style='display:none'><button class='button' onclick=\"currentMediaItem=$id;if(check_required('wpss_required', false)){";
			if($img_id){
				$string .= "wpss_replace_photo_javascript($img_id,$id,'".$items['url']."');";
				$string .= "}\">Replace photo";
			}else{
				$string .= "wpss_send_and_return('$id','".addslashes(html_entity_decode($img_title, NULL, 'UTF-8'))."');";
				$string .= "}\">Insert photo";
			}
			$string .= "</button></span>";
			$string .= "
						<script type=\"text/javascript\">
						var currentMediaItem = -1;
  						jQuery(document).ready(function(){

						jQuery(\"input[name='send[$id]']\").hide();";
			$string .= "
						jQuery(\"#media-item-$id\").find(\".url\").hide();
						jQuery(\"#media-item-$id\").find(\".align\").hide();
						jQuery(\"#media-item-$id\").find(\".image-size\").hide();
						//jQuery(\"#media-item-$id\").find(\".submit\").hide();";
			
			$string .= "jQuery(\"#media-item-$id\").find(\".submit\").find(\"td.savesend\").find(\"input:first\").hide();
						jQuery(\"#media-item-$id\").find(\".submit\").find(\"td.savesend\").prepend(jQuery(\"#insert_photo_span_$id\").html());
						jQuery(\"label[for='attachments[$id][post_excerpt]']\").find(\"span:first\").html('Alt Text <br /><small>Slideshow Default</small>');
						jQuery(\"label[for='attachments[$id][post_excerpt]']\").find(\"span:first\").after(\"<span class='alignright'><abbr class='required' title='required'>*</abbr></span>\");
						jQuery(\"#attachments\\\\[$id\\\\]\\\\[post_excerpt\\\\]\").next().hide();
						jQuery(\"tr.image_alt\").hide();
						jQuery(\"input#attachments\\\\[$id\\\\]\\\\[post_excerpt\\\\]\").blur(function(){
						jQuery(\"input#attachments\\\\[$id\\\\]\\\\[image_alt\\\\]\").val( jQuery(\"input#attachments\\\\[$id\\\\]\\\\[post_excerpt\\\\]\").val()	);
						});
						jQuery(\"input#attachments\\\\[$id\\\\]\\\\[image_alt\\\\]\").val( jQuery(\"input#attachments\\\\[$id\\\\]\\\\[post_excerpt\\\\]\").val()	);
						jQuery(\"label[for='attachments[$id][post_content]']\").find(\"span:first\").html('Caption');
						jQuery(\"label[for='attachments[$id][post_content]']\").find(\"span:first\").after(\"<span class='alignright'><abbr class='required' title='required'>*</abbr></span>\");
						jQuery(\"#attachments\\\\[$id\\\\]\\\\[post_content\\\\]\").addClass('".$this->plugin_prefix."required');
						jQuery(\"#attachments\\\\[$id\\\\]\\\\[post_title\\\\]\").addClass('".$this->plugin_prefix."required');
						jQuery(\"#attachments\\\\[$id\\\\]\\\\[post_excerpt\\\\]\").addClass('".$this->plugin_prefix."required');";
			if($img_id){
						$oldphoto = $this->getPhoto($img_id);
						$string .= "jQuery('#attachments\\\\[$id\\\\]\\\\[post_title\\\\]').val(\"".$this->utils->convertquotes($oldphoto['title'])."\");";
						$string .= "jQuery('#attachments\\\\[$id\\\\]\\\\[post_excerpt\\\\]').val(\"".$this->utils->convertquotes($oldphoto['alt'])."\");";
						$string .= "jQuery('#attachments\\\\[$id\\\\]\\\\[post_content\\\\]').val(\"".$this->utils->convertquotes($oldphoto['caption'])."\");";

			}
			$string .= "  });
						</script>";
			$string .= "</td></tr><tr><th>&nbsp;</th><td>&nbsp;</td></tr>";
			$string .= $this->media_field('original_url', 'Original URL', $id, '100%', false);
			$string .= $this->media_field('photo_credit', 'Photo credit', $id, '100%', true);
			$string .= $this->media_field('geo_location', 'Geo location', $id, '180px');
			$string .= "<tr><td>";
		}
		return $string;
	}

//This function is called for saving the fixed properties of the photo to the database
	function save_photo_fixed_props($post, $attachment){
		foreach($this->fixed_photo_props as $prop){
			if($attachment[$prop]){
				add_post_meta($post['ID'], $prop, $attachment[$prop], true) or update_post_meta($post['ID'], $prop, $attachment[$prop]);//unique
			}
		}
		return $post;
	}
}
?>
