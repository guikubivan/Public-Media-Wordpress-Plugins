<?php




class eartheats extends box_custom_field_plugin{
	private $recipe_category_option = 'eartheats_recipe_category';
	private $parents_meta_key = 'recipe_parent_posts';


	function plugin_url(){
		$result = get_bloginfo('url').'/wp-content/plugins/eartheats/';
		return $result;
	}

	function compare($new, $old){
		$removed = array();
		foreach($old as $o){
			if(!in_array($o, $new)){
				$removed[] = $o;
			}
		}
		return $removed;
	}

	function remove_references($ids, $post_id){
		foreach($ids as $id){
			$parents = get_post_meta($id, $this->parents_meta_key);
			if(is_array($parents)){
				$parents = $parents[0];
			}
			if($parents){
				$parents = $this->parseIDS($parents);
				if(in_array($post_id, $parents)){
					unset($parents[array_search($post_id, $parents)]);
					if(sizeof($parents)>0){
						$nval = implode(',',$parents);
						update_post_meta($id, $this->parents_meta_key, $nval);
					}else{
						delete_post_meta($id, $this->parents_meta_key);
					}
				}
			}
		}
	}

	function saveItem($post_id){

		if ( !wp_verify_nonce( $_POST[$this->inputname.'noncename'], plugin_basename($this->inputname . 'nonce') )) {
			return $post_id;
		}
		if(wp_is_post_revision($post_id)) { 
			return $post_id;
		}

		$val = $_POST[$this->inputname];
		$prev = $this->get_recipe_ids($post_id);
					
		if(strip_tags($val) || get_post_meta($post_id, $this->fieldname)){
			if(strip_tags($val)){

				//add postmeta to child recipe posts
				$pids=$this->parseIDS($val);
				if(is_array($pids) && is_array($prev)){
					$removed = $this->compare($pids, $prev);

					if(sizeof($removed)>0) $this->remove_references($removed, $post_id);
				}
				foreach($pids as $recipe_id){
					$parents = get_post_meta($recipe_id, $this->parents_meta_key);
					if(is_array($parents)){
						$parents = $parents[0];
					}
					if($parents){
						$parents = $this->parseIDS($parents);
						if(!in_array($post_id, $parents)){
							$nval = implode(',',$parents) . "," . $post_id;
							add_post_meta($recipe_id, $this->parents_meta_key, $nval, true) or update_post_meta($recipe_id, $this->parents_meta_key, $nval);
						}
					}else{
						$nval = $post_id;
						add_post_meta($recipe_id, $this->parents_meta_key, $nval, true) or update_post_meta($recipe_id, $this->parents_meta_key, $nval);
					}
				}

			}else{
				if(is_array($prev)){
					$this->remove_references($prev, $post_id);
				}
			}
		}

		box_custom_field_plugin::saveItem($post_id);

	}


	public function deleteItem($post_id){
		box_custom_field_plugin::deleteItem($post_id);
	}

	public function editForm(){
		global $post;

		$recipe_id = get_option($this->recipe_category_option);

		if(in_array($recipe_id, wp_get_post_categories($post->ID))){
			$parents = get_post_meta($post->ID, $this->parents_meta_key);
			if(is_array($parents)){
				$parents = $parents[0];
			}
			if($parents){
				echo "<div>This recipe is referenced by: </div>\n<ol>\n";
				$parents = $this->parseIDS($parents);
				foreach($parents as $parent){
					echo "<li>" . get_the_title($parent) . "</li>\n";
				}
				echo "</ol>\n";
			}
			echo "<div><strong>Note:</strong> Recipe posts cannot reference other recipes.</div>";
			
			return;
		}
		
		if(!$recipe_id){
			echo "Please set the recipe category in order to select recipes. <a href='admin.php?page=Eartheats'>Set now</a>";
			return;
		}
		echo'<input type="hidden" name="'.$this->inputname.'noncename" id="'.$this->inputname.'noncename" value="' . wp_create_nonce( $this->inputname . 'nonce') .'" />';
		
		$value = get_post_meta($post->ID, $this->fieldname);
		if(is_array($value)){
			$value = $value[0];
		}
		$value = stripslashes($value);

		echo "<div>Recipe ids (seperate multiple ids by comma):</div>" . "<input value=\"$value\" name=\"".$this->inputname."\" id=\"".$this->inputname."\"/>" . "<br />\n";

		echo "<select id='recent_recipes' style='width: 200px;' onchange=\"if(this.value){if(jQuery('#".$this->inputname."').val()){jQuery('#".$this->inputname."').val(jQuery('#".$this->inputname."').val() + ', '+this.value)}else{jQuery('#".$this->inputname."').val(this.value);};}\">\n<option value=''>Choose recent recipe</option>";

		$recipes = get_posts("numberposts=10&category=$recipe_id");
		foreach($recipes as $r){
			echo "<option value='$r->ID'>$r->ID: $r->post_title</option>";
		}
		echo "</select>\n";
	}

	function parseIDS($string){
		$value = stripslashes($string);
		$ids = explode(',', $value);
		for($i=0;$i<sizeof($ids);++$i){
			$ids[$i] = trim($ids[$i]);
		}
		return $ids;
	}

	function wpss_medium_url($post_id){
		global $wpdb;
		$url = '';
		if($pid=get_post_meta($post_id,'wpss_photo_id', true)){
			$wp_id = $wpdb->get_var("SELECT wp_photo_id FROM " . $wpdb->prefix."wpss_photos WHERE photo_id=$pid;");
			$url = current(wp_get_attachment_image_src( $wp_id, 'full'));
			$pic = get_post_meta($wp_id, '_wp_attachment_metadata');
			if(isset($pic[0]['sizes']['medium'])){
				$url = str_replace(basename($url), $pic[0]['sizes']['medium']['file'], $url);
			}
		}
		return $url;
	}

	function create_xml($post_id, $stylesheet){
		global $wpdb;

		$item['title'] = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID=$post_id;");
		$item['teaser'] = get_post_meta($post_id, 'teaser_text',true);
		$item['permalink'] = get_permalink($post_id);
		$item['image'] = $this->wpss_medium_url($post_id);
		$str = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$str .= '<?xml-stylesheet type="text/xsl" href="'.$this->plugin_url().'/stylesheets/'.$stylesheet.'" version="1.0"?>' . "\n";
		$str .= '<post>';
		foreach($item as $name => $value){
			if($value){
				$str .= "<$name>". htmlspecialchars($value)."</$name>\n";
			}								
		}
		$str.='</post>';
		return $str;
	}

	function get_clip($post_id, $stylesheet='recipe_clip.xsl'){

		$xml_text = $this->create_xml($post_id, $stylesheet);
		$xml = new DOMDocument;
		if(!@$xml->loadXML($xml_text)){
			return;
		}

		$xsl = new DOMDocument;
		@$xsl->load($this->plugin_url().'/stylesheets/'.$stylesheet);

		// Configure the transformer
		$proc = new XSLTProcessor;
		@$proc->importStyleSheet($xsl); // attach the xsl rules
		$output = @$proc->transformToXML($xml);

		if($output){
			return $output;
		}else{
			return "error";
		}
	}

	function replace_clips($text){
		if(preg_match_all("/\[\s*recipe_clip\s*\-?\s*([0-9]+)\s*\]/",$text, $matches)>0){
			for($i=0;$i<sizeof($matches[0]);++$i){
				$ID = $matches[1][$i];
				$out = $this->get_clip($ID);
				$text = str_replace($matches[0][$i], $out, $text);
				//echo $xml;
			}
		}
		return $text;
	}

	function insert_recipes($content){
		global $post;
		$value = get_post_meta($post->ID, $this->fieldname);
		if(is_array($value)){
			$value = $value[0];
		}
		$value = stripslashes($value);
		$ids = explode(',', $value);
		for($i=0;$i<sizeof($ids);++$i){
			$j = $i+1;
			$id = trim($ids[$i]);
			//echo "|".$id."|";
			if(stripos($content, "[recipe-$j]")!==false){
				$p = get_post($id);
				//print_r($p);
				$content = str_replace("[recipe-$j]", $p->post_content, $content);
			}
		}
		$content = $this->replace_clips($content);
		return $content;
	}

	function get_recipe_ids($ID = -1){
		global $post;
		$ID = $ID > -1 ? $ID : $post->ID;
		$value = get_post_meta($ID, $this->fieldname);
		if(is_array($value)){
			$value = $value[0];
		}

		return $this->parseIDS($value);
	}

	function get_parent_ids($ID = -1){
		global $post;
		$ID = $ID > -1 ? $ID : $post->ID;
		$value = get_post_meta($ID, $this->parents_meta_key);
		if(is_array($value)){
			$value = $value[0];
		}

		return $this->parseIDS($value);
	}


	function settings(){
		if($_POST['cat']){
			if(get_option($this->recipe_category_option)){
				update_option($this->recipe_category_option, $_POST['cat']);
			}else{
				add_option($this->recipe_category_option, $_POST['cat']);
			}
			echo "<div style='background-color:#7FFF47'>Category saved.</div>";
		}
		$recipe_category = get_option($this->recipe_category_option);
		$params = array('hide_empty'=> 0, 'exclude'=> 1);
		if($recipe_category){ 
			$params['selected'] = $recipe_category;
		}

?>
<form method='POST' action="?page=<?php echo $_GET['page']; ?>" >
  <fieldset>
    <legend>Recipes</legend>
    <label>Choose recipe posts category:
      <?php wp_dropdown_categories($params); ?>
    </label>
  </fieldset>
  <input type='submit' value='Save' />
</form>
<?php 
	}


}

//$test = new eartheats('Recipe', 'recipe_text', 'recipe_content');
//$test->editForm();

 ?>
