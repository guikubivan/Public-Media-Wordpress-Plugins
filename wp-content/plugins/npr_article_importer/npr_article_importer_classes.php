<?php




class npr_article_importer extends box_custom_field_plugin{
	private $npr_author_option = 'npr_importer_author';
	private $story_active_field = 'npr_importer_activate';
	private $author_custom_field = 'npr_author';
	private $article_id = -1;


	function plugin_url(){
		$result = get_bloginfo('url').'/wp-content/plugins/npr_article_importer/';
		return $result;
	}

	function processSave($post_id, $val, $meta_key){
		if(strip_tags($val) || get_post_meta($post_id, $meta_key)){
			if(strip_tags($val)){
				add_post_meta($post_id, $meta_key, $val, true) or update_post_meta($post_id, $meta_key, $val);
			}else{
				delete_post_meta($post_id, $meta_key);

				if($meta_key == $this->story_active_field){
					$article_id = get_post_meta($post_id, $this->fieldname);
					if(is_array($article_id)){
						$article_id = $article_id[0];
					}
					$article_id = stripslashes($article_id);
					if($article_id){
						$this->deleteXMLFile($article_id);
					}
				}

			}
		}


	}

	public function deleteItem($post_id){
		box_custom_field_plugin::deleteItem($post_id);

		$article_id = get_post_meta($post_id, $this->fieldname);
		if(is_array($article_id)){
			$article_id = $article_id[0];
		}
		$article_id = stripslashes($article_id);
		$this->deleteXMLFile($article_id);
	}

     function getNPRStories($nprapicall, $xslfile){

	$xml = new DOMDocument;
	$xml->load($nprapicall);

	$xsl = new DOMDocument;
	$xsl->load($xslfile);

	// Configure the transformer
	$proc = new XSLTProcessor;
	$proc->importStyleSheet($xsl); // attach the xsl rules
	$outText = @$proc->transformToXML($xml);
	$outText = trim($outText);
	if($outText){
		if(!preg_match("/There were no results that matched your query criteria/i",$outText) && !preg_match("/Requested List/i",$outText)){
			return $outText;
		}
	}
	return false;
     }

	function make_NPR_call($article_id){
		$nprapicall = "http://api.npr.org/query?id=$article_id";
		$output = "&output=NPRML";
		$nprapicall .= "&fields=title,teaser,image,audio,byline&apiKey=MDAxNzgwMzI5MDEyMTQ4NzkxODhlNWQ5OA004";
		//return urlencode($nprapicall);
		return $nprapicall;
	}

	function deleteXMLFile($article_id){
		$story_path = dirname(__FILE__) . "/npr_articles/${article_id}_story.xml";
		unlink($story_path);
	}

	function maybeAddStory($post_id, $article_id){
		if(!$article_id)return;

		$story_path = dirname(__FILE__) . "/npr_articles/${article_id}_story.xml";
		if(!file_exists($story_path)){
			$nprapicall = $this->make_NPR_call($article_id);
			$output_path = dirname(__FILE__) . "/npr_articles/${article_id}_output.txt";
			$command = "wget --no-verbose -O $story_path '$nprapicall'";
			//echo $command;
			exec($command, $output);
			$outtext = str_replace('"', '\"', implode("\n", $output));
			//exec("echo \"".$outtext."\"  > $output_path");


			//die();
			$xslfile = dirname(__FILE__) . '/stylesheets/basic.xsl';
		}
		
		$xmlText = file_get_contents($story_path);
		$xmlDoc = new SimpleXMLElement($xmlText);
		$author = $xmlDoc->list[0]->story[0]->byline[0]->name; // "So this language. It's like..."
		//echo $author;
		$this->processSave($post_id, $author, $this->author_custom_field);
		
		//die();
	}



	function saveItem($post_id){
		global $wpdb;
		if ( !wp_verify_nonce( $_POST[$this->inputname.'noncename'], plugin_basename($this->inputname . 'nonce') )) {
			return $post_id;
		}
		if(wp_is_post_revision($post_id)) { 
			return $post_id;
		}

		$story_id = $_POST[$this->inputname];
		$this->processSave($post_id, $story_id, $this->fieldname);

		$val = $_POST[$this->story_active_field];
		$this->processSave($post_id, $val, $this->story_active_field);
		//echo "stuff".$this->inputname;
		//$val = addslashes($val);
		if($story_id && $_POST[$this->story_active_field]){
			$this->maybeAddStory($post_id, $story_id);
			if($author_id = get_option($this->npr_author_option)){
				$wpdb->query("UPDATE $wpdb->posts SET post_author=$author_id WHERE ID = $post_id;");
				//echo $wpdb->get_var("SELECT post_author FROM $wpdb->posts where ID = $post_id;");
				//die();
			}
		}
	}

	public function editForm(){
		global $post;

		echo'<input type="hidden" name="'.$this->inputname.'noncename" id="'.$this->inputname.'noncename" value="' . wp_create_nonce( $this->inputname . 'nonce') .'" />';


		$author_id = get_option($this->npr_author_option);
		if(!$author_id){
			echo "<div>Please set up a dummy NPR Author first and assign it in the <a href='admin.php?page=NPR Importer'>NPR Article Importer settings</a>.</div>\n";
			return;			
		}

		
		$story_id = get_post_meta($post->ID, $this->fieldname);
		if(is_array($story_id)){
			$story_id = $story_id[0];
		}
		$story_id = stripslashes($story_id);

		$story_active = get_post_meta($post->ID, $this->story_active_field);
		if(is_array($story_active)){
			$story_active = $story_active[0];
		}
		$story_active = stripslashes($story_active);
		$checked = $story_active ? 'checked="checked"' : '';
		echo "<div>";
		echo "Story id: <input type='text' value=\"$story_id\" name=\"".$this->inputname."\" id=\"".$this->inputname."\"/>" . "<br />\n";
		echo "Active? <input type='checkbox' $checked name=\"".$this->story_active_field."\" id=\"".$this->story_active_field."\" /> Yes";
		echo "</div>";
	}

	function parseIDS($string){
		$value = stripslashes($string);
		$ids = explode(',', $value);
		for($i=0;$i<sizeof($ids);++$i){
			$ids[$i] = trim($ids[$i]);
		}
		return $ids;
	}



	function get_transform($article_path, $stylesheet='simple.xsl'){

		$xml = new DOMDocument;
		//echo $article_path;
		if(!@$xml->loadXML(file_get_contents($article_path))){
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



	function insert_story($content){
		global $post;
		if($article_id = get_post_meta($post->ID, $this->fieldname, true)){
			$article_path = dirname(__FILE__) . "/npr_articles/${article_id}_story.xml";
			$content = $this->get_transform($article_path, 'simple.xsl');
		}
		return $content;
	}

	function replace_author($author){
		global $post;
		if($article_id = get_post_meta($post->ID, $this->fieldname, true)){
			if($authorpm = get_post_meta($post->ID, $this->author_custom_field, true)){
				$author = $authorpm;
			}
		}
		return $author;
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
		if($_POST['user']){
			if(get_option($this->npr_author_option)){
				update_option($this->npr_author_option, $_POST['user']);
			}else{
				add_option($this->npr_author_option, $_POST['user']);
			}
			echo "<div style='background-color:#7FFF47'>NPR dummy user saved.</div>";
		}
		$author_id = get_option($this->npr_author_option);
		$params = array();
		
		if($author_id){ 
			$params['selected'] = $author_id;
		}

?>
<form method='POST' action="?page=<?php echo $_GET['page']; ?>" >
  <fieldset>
    <legend>NPR Article Importer</legend>
    <label>Choose dummy author for NPR Articles :
      <?php wp_dropdown_users($params); ?>
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
