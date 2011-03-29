<?php

/*
 * This class is used primarily for scoping the action functions and holding globals
 */
class wpss_main{
	public $post;
	
	public $_post;
	public $_get;
	
	public $tab_order = 200;
	public $ajax;
	
	public $plugin_prefix = 'wpss_';
	public $plugin_url;
	
	public $google_api_key = 'ABQIAAAAIVgh1deAtW1cu85uZMLCBhTnn3xAjQHqpFtU4TMbi6qQki1QvhSoa7FSZMKpfIEdNdXx68sI8xbwhA';
		
	
	public $wpdb;
	public function __construct($wpdb)
	{
		$this->wpdb = $wpdb;
		$this->_post = $_POST;
		$this->_get = $_GET;
		
		$this->plugin_url = get_bloginfo('url').'/wp-content/plugins/ipm-wordpress-slideshow-MVC/';
		
		$this->ajax = new IPM_Ajax($this);
		
		
		
	}	
	
	public function front_end()
	{
		
	}
	
	function show_photos($post_id, $stylesheet=''){
		global $wpdb;
		if($slideshows=get_post_meta($post_id,$this->fieldname, false)){
			$stylesheet = $stylesheet ? $stylesheet : get_option($this->option_default_style_slideshow);
		}else{
			$stylesheet = $stylesheet ? $stylesheet : get_option($this->option_default_style_photo);
		}
		$xml_text = $this->getXML($post_id, $stylesheet);
		//echo $xml_text;
		$xml = new DOMDocument;
		if(!@$xml->loadXML($xml_text)){
			//echo $xml_text;
			//$xml->loadXML($xml_text);
			return;
		}
	
		$xsl = new DOMDocument;
		@$xsl->load($this->stylesheets_path.$stylesheet);

		// Configure the transformer
		$proc = new XSLTProcessor;
		@$proc->importStyleSheet($xsl); // attach the xsl rules
		$output = @$proc->transformToXML($xml);
		//echo "<pre>".print_r($xml_text, true)."</pre>";
		if($output){
			echo $output;
		}else{
			if($stylesheet = "")
				echo "Error. The Slideshow Stylesheets have not been defined.";
			else
				echo "Unknown error.";
		}

	}
	
	public function convert_stylesheet($stylesheet)
	{
		if(substr($stylesheet, -3) == "xsl")
		{
			$stylesheet = substr($stylesheet, 0, strlen($stylesheet) - 3);
			$stylesheet .= "php";
		}
		return $stylesheet;
			
	}
	
	public function media_upload()
	{
		require_once(WPSSCONTROLLERS."media_upload.controller.php");
		$media_uploader = new WPSSMediaUpload($this);
		$media_uploader->initialize_uploader();
		
	}
	
	//function for the admin_menu action	
	public function show_editor_box()
	{
		require_once(WPSSCONTROLLERS."admin_box.controller.php");
		$slideshow_box = new WPSSAdminBox($this);
		
		add_meta_box('slideshow-box-div', __('Slideshows', 'slideshowbox'), array(&$slideshow_box, 'post_form'), 'post', 'normal', 'high');
		add_meta_box('slideshow-box-div', __('Slideshows', 'slideshowbox'), array(&$slideshow_box, 'page_form'), 'page', 'normal', 'high');
		
	}	
	
	
	
	//must be called during runtime functions 
	public function get_post()
	{
		global $post;
		$this->post = $post;
	}
		
	public function admin_head_scripts()
	{
		 echo $this->render_backend_view("admin_head_javascript.php", array());
		
	}
		
	public function admin_print_scripts()
	{
		$google_api_key = $this->google_api_key;
		
		//Register javascript 
		wp_register_script( 'google_map_api', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$google_api_key);
		wp_register_script('google_map_api_utils', WPSSDIR.'/google_maps_utils.js', array ('google_map_api'));
		wp_localize_script( 'google_map_api_utils', 'WPGoogleAPI', array('key' => $google_api_key));

		wp_register_script( 'wp_slideshow', $this->plugin_url.'new_slideshow_admin.js', array('jquery', 'google_map_api', 'google_map_api_utils'));
		
		if( preg_match("/post\.php|post\-new\.php|page\.php|page\-new\.php|media\-upload\.php/", $_SERVER[ 'REQUEST_URI' ]) ) {
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'thickbox' );# thickbox
			wp_admin_css('thickbox');
			wp_enqueue_script( 'wp_slideshow' );
			echo '<link rel="stylesheet" href="'.$this->plugin_url.'wordpress_slideshow_admin.css" type="text/css" />'."\n";
		}

		if( preg_match("/post\.php|post\-new\.php|page\.php|page\-new\.php/", $_SERVER[ 'REQUEST_URI' ]) ) {
			echo '<link rel="stylesheet" href="'.$this->plugin_url.'wordpress_slideshow_admin.css" type="text/css" />'."\n";
		}

		if($_GET['page'] == "Slideshow Manager"){
			echo "\n<!-- Slideshow manager tabs-->\n";
			echo '<link rel="stylesheet" href="'.$this->plugin_url.'ui.tabs.css" type="text/css" />'."\n";
			wp_enqueue_script( 'jquery-ui-tabs', array('jquery') );
			echo "\n<!-- END Slideshow manager tabs-->\n";
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'thickbox' );# thickbox
			wp_admin_css('thickbox');
			wp_enqueue_script( 'wp_slideshow' );
			echo '<link rel="stylesheet" href="'.$this->plugin_url.'wordpress_slideshow_admin.css" type="text/css" />'."\n";
		}

                if(current_user_can('edit_plugins') && preg_match("/media\-upload.php/", $_SERVER[ 'REQUEST_URI' ]) ){
                  wp_enqueue_script( 'jquery' );
                }

  		wp_print_scripts( array( 'sack' )); //Doesn't seem like I need this
	}
		
		
		
		
	
	//generic render view function
	private function render_view($view, $parameter, $end)
	{
		ob_start();
		if(is_array($parameter))
		{
			extract($parameter);
		}
		
		include(WPSSVIEWS .  $view);
		$output = ob_get_contents();
		ob_end_clean();
		
		/*if($addslashes)
		{
			$output = addslashes($output);
			$output = preg_replace_callback("<<!-- UNESCAPE \[(.*)\] -->>", "wordpress_slideshow::render_view_unescape_callback",  $output);
		}*/
		
		return $output;
	}
	
	//render view wrapper that only gets backend views
	public function render_backend_view($view, $parameters = array())
	{
		$view = "backend/".$view;
		$output = $this->render_view($view, $parameters, "backend");
		return $output;
	}
		
		
}


?>