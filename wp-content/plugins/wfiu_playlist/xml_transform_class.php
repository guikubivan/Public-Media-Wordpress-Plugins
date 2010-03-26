<?php
class XmlTransformClass {
	private $plugin_url = '';
	private $plugin_prefix = 'ipm_playlist_';
	public $default_style_playlist = 'playlist.xsl';
	public $default_style_item = 'item.xsl';

	function __construct($plugin_url){
		$this->plugin_url = $plugin_url;
		$this->option_style_playlist = $plugin_prefix.'stylesheet_playlist';
		$this->option_style_item = $plugin_prefix.'stylesheet_item';
	}
	function activate(){
		/*Options */
		add_option($this->option_style_playlist, $this->default_style_playlist);
		add_option($this->option_style_item, $this->default_style_item);

	}

	function preparefield($field){
		global $wpdb;
		return htmlspecialchars($field);
		$badChars = array('“', '”', '’', '‘', '’', "…", 'é');
		$repChars = array('"', '"', "'", "'", "'", "...", '&eacute;');
		return htmlentities(str_replace($badChars, $repChars, $field, $count));
			//if($count>0)echo "alert('replaced one');";
	}

	function transform_playlist($post_id, $stylesheet){
		global $wpdb;
		$mytable = $wpdb->prefix."wfiu_playlist";
		$query = "SELECT count(post_id) FROM ".$mytable ." WHERE post_id=".$post_id . " ORDER BY playlist_item_id ASC";
		$playlist_count = $wpdb->get_var($query);
		if($playlist_count>0){

			$xml = new DOMDocument;
			@$xml->loadXML($this->get_xml_playlist($post_id, $stylesheet));

			$xsl = new DOMDocument;
			@$xsl->load(dirname(__FILE__).'/stylesheets/'.$stylesheet);

			// Configure the transformer
			$proc = new XSLTProcessor;
			@$proc->importStyleSheet($xsl); // attach the xsl rules
			$output = @$proc->transformToXML($xml);

			if($output){
				echo $output;
			}else{
				echo 'error';
			}
		}

	}

	function get_xml_playlist($post_id, $stylesheet){
		global $wpdb;
		$mytable = $wpdb->prefix. "wfiu_playlist";

		$query = "SELECT post_title, post_modified FROM ".$wpdb->prefix."posts WHERE ID=$post_id;";

		$post_info = $wpdb->get_row($query);

		$query = "SELECT * FROM ".$mytable ." WHERE post_id=$post_id ORDER BY playlist_item_id ASC";
		$playlist = $wpdb->get_results($query);
		if(sizeof($playlist)==0){
			return '';
		}

		$string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$string .= '<?xml-stylesheet type="text/xsl" href="'.$this->plugin_url.'stylesheets/'.$stylesheet.'" version="1.0"?>' . "\n";
		
		$string .= '<post date="'.$post_info->post_modified.'" >';
		$string .= '<showtitle>'.$post_info->post_title.'</showtitle>';

		foreach($playlist as $playlistItem){
			$string .= $this->get_xml_item($playlistItem);
		}
		$string .= '</post>';
		return $string;
	}

	function get_xml_item($playlistItem){
		global $wpdb;

		if(!is_object($playlistItem)){

			$query = "SELECT * FROM ".$wpdb->prefix ."wfiu_playlist WHERE playlist_item_id=$playlistItem;";
			$playlistItem = $wpdb->get_row($query);
		}

		$artist = $this->preparefield($playlistItem->artist);
		//$artist = str_replace('&','\&',$artist);
		$composer = $this->preparefield($playlistItem->composer);
		$title = $this->preparefield($playlistItem->title);

		$album = $this->preparefield($playlistItem->album);
		$label = $this->preparefield($playlistItem->label);
		$asin = $this->preparefield($playlistItem->asin);

		$notes = $this->preparefield($playlistItem->notes);
		//$notes = str_replace(array('"',"'", "\r", "\n", "\0"), array('\"','\\\'','\r', '\n', '\0'), $notes);
		$rel_year = $playlistItem->release_year;

		$string = "<item>\n";
		$string .= $title ? "<title>$title</title>\n" : '';;
		$string .= $artist ? "<artist>$artist</artist>\n" : '';
		$string .= $composer ? "<composer>$composer</composer>\n" : '';
		$string .= $album ? "<album>$album</album>\n" : '';

		$string .= $label ? "<label>$label</label>\n" : '';
		$string .= $rel_year ? "<release_year>$rel_year</release_year>\n" : '';
		$string .= $asin ? "<asin>$asin</asin>\n" : '';
		$string .= $notes ? "<notes><![CDATA[$notes]]></notes>\n" : '';
		$string .= "</item>\n\n";
		return $string;

	}
	function process_transform($xml_string,$stylesheet){
		$xml = new DOMDocument;
		@$xml->loadXML($xml_string);

		$xsl = new DOMDocument;
		@$xsl->load(dirname(__FILE__).'/stylesheets/'.$stylesheet);

		// Configure the transformer
		$proc = new XSLTProcessor;
		@$proc->importStyleSheet($xsl); // attach the xsl rules
		$output = @$proc->transformToXML($xml);
		if($output){
			return $output;
		}else{
			return 'error';
		}
	}

	function get_playlist_clip($post_id, $stylesheet){
		if(!$stylesheet){
		  $stylesheet=$this->default_style_playlist;
		}
		if(!preg_match("/^.*\.xsl$/", $stylesheet)){
			$stylesheet = $stylesheet.".xsl";
		}
		if(!$stylesheet || !file_exists(dirname(__FILE__).'/stylesheets/'.$stylesheet)){
			$stylesheet = get_option($this->option_style_playlist);
		}
		$xml = $this->get_xml_playlist($post_id, $stylesheet);	
		return $this->process_transform($xml, $stylesheet);
	}

	function get_item_clip($item_id, $stylesheet){
		if(!$stylesheet){
		  $stylesheet=$this->default_style_item;
		}
		if(!preg_match("/^.*\.xsl$/", $stylesheet)){
			$stylesheet = $stylesheet.".xsl";
		}
		if(!$stylesheet || !file_exists(dirname(__FILE__).'/stylesheets/'.$stylesheet)){
			$stylesheet = get_option($this->option_style_item);
		}

		$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$xml .= '<?xml-stylesheet type="text/xsl" href="'.$this->plugin_url.'stylesheets/'.$stylesheet.'" version="1.0"?>' . "\n";
		$xml .= "<post>\n";
		$xml .= $this->get_xml_item($item_id);
		$xml .= "</post>";
		//echo htmlentities($xml);	
		return $this->process_transform($xml, $stylesheet);

	}

	function replace_playlist_tags($text, $probe=false){
		global $wpdb, $post;
		$post_id = $post->ID;
		$query = "SELECT playlist_item_id FROM ".$wpdb->prefix ."wfiu_playlist WHERE post_id=$post_id ORDER BY playlist_item_id ASC;";

		$playlist = $wpdb->get_col($query);
		if(sizeof($playlist)==0){
		 return $text;
		}

		for($h=0;$h<sizeof($playlist);++$h){

			$index = $h+1;
			if(preg_match_all("/\[\s*playlist\-item\s*\-?\s*$index\s*([^\]\s]*)\s*\]/",$text, $matches)>0){
				if($probe){
					return true;
				}
				for($i=0;$i<sizeof($matches[0]);++$i){
					$stylesheet = $matches[1][$i];
echo $stylesheet;
					$out = $this->get_item_clip($playlist[$h],$stylesheet);
					$text = str_replace($matches[0][$i], $out, $text);
				}
			}
		}
		/*full playlist*/
		if(preg_match_all("/\[\s*playlist\s*\-?\s*([^\]\s]*)\s*\]/",$text, $matches)>0){
			if($probe){
				return true;
			}
			for($i=0;$i<sizeof($matches[0]);++$i){
				$stylesheet = $matches[1][$i];
				$out = $this->get_playlist_clip($post_id,$stylesheet);
				$text = str_replace($matches[0][$i], $out, $text);
			}
		}

		if($probe)return false;
		return $text;
	}

}
?>
