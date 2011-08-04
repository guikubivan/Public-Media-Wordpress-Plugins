<?php

/*
 * This class is used primarily for scoping the action functions and holding globals
 */
class wpss_main{
	//a copy of the global wordpress $post variable
	public $post;
	
	//wrappers for the $_POST and $_GET super globals in case you want to sanitize them in the custructor or whatever
	public $_post; 
	public $_get;
	
	//public $tab_order = 200;
	
	//instance of the ajax controller so we can call the ajax functions whenever
	public $ajax;
	
	public $plugin_prefix = 'wpss_';
	public $plugin_url;
	
	public $google_api_key = 'ABQIAAAAIVgh1deAtW1cu85uZMLCBhTnn3xAjQHqpFtU4TMbi6qQki1QvhSoa7FSZMKpfIEdNdXx68sI8xbwhA';
	
	//default sylesheets left over from the original version
	public $default_style_photo = 'wpss_program_single_new.xsl';
	public $default_style_slideshow = 'wpss_program_single_new.xsl';
	public $default_style_post_image = 'wpss_program_thumb_small.xsl';
	
	//these are global options that can be pulled from the wordpress options table if need be.
	public $option_default_style_photo = "";
	public $option_default_style_slideshow = "";
	public $option_default_style_post_image = "";
	public $option_multiple_slideshows = "";
	
	
	public $wpdb;
	public function __construct($wpdb)
	{
		$this->wpdb = $wpdb;
		$this->_post = $_POST;
		$this->_get = $_GET;
		
		$this->plugin_url = get_bloginfo('url').'/wp-content/plugins/ipm-wordpress-slideshow-MVC/';
		
		$this->ajax = new IPM_Ajax($this);
		
		$this->option_default_style_photo = $plugin_prefix.'slideshow_stylesheet';
		$this->option_default_style_slideshow = $plugin_prefix.'photo_stylesheet';
		$this->option_default_style_post_image = $plugin_prefix.'post_image_stylesheet';
		$this->option_multiple_slideshows = $plugin_prefix.'multiple_slideshows';
		//$this->postmeta_post_image = $this->plugin_prefix.'post_image';
		
	}	
	
	//called by the the_content action on the front-end
	public function front_end()
	{
		require_once(WPSSCONTROLLERS."front_end.controller.php");
		$front_end = new IPM_FrontEnd($this);
		return $front_end->replace_tags();
	}
	
	//wrapper to redirect the front-end global function "wpss_photos($stylesheet) to the function through the front-end class
	function show_photos($stylesheet='')
	{
		require_once(WPSSCONTROLLERS."front_end.controller.php");
		$front_end = new IPM_FrontEnd($this);
		$front_end->show_photos($stylesheet);
	}
	
	
	//some stylesheets used by templates, etc are set as .xsl files.  Since we're using PHP files instead, we can run the stylesheet
	// name through this function instead of having to replace the stylesheet filename in EVERY instance across the site.
	public function convert_stylesheet($stylesheet)
	{
		if(substr($stylesheet, -3) == "xsl")
		{
			$stylesheet = substr($stylesheet, 0, strlen($stylesheet) - 3);
			$stylesheet .= "php";
		}
		return $stylesheet;
			
	}
	
	//called in backend when you're trying to add photos to posts
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
	
	
	
	//must be called during runtime functions to get the wordpress post on the fly
	public function get_post()
	{
		global $post;
		$this->post = $post;
	}
		
	//prints all the javascript functions needed by the admin-box
	// called in the admin_head action
	public function admin_head_scripts()
	{
		 echo $this->render_backend_view("admin_head_javascript.php", array());
		
	}
	
	//registers scripts used in the admin section
	// called in the admin_print_scripts action
	public function admin_print_scripts()
	{
		$google_api_key = $this->google_api_key;
		
		//Register javascript 
		wp_register_script( 'google_map_api', 'http://maps.google.com/maps?file=api&amp;v=2&amp;key='.$google_api_key);
		//wp_register_script('google_map_api_utils', WPSSDIR.'/google_maps_utils.js', array ('google_map_api'));
		if(file_exists(ABSPATH.PLUGINDIR.'/wfiu_utils/google_maps_utils.js')){
			wp_register_script('google_map_api_utils', get_bloginfo('url').'/wp-content/plugins/wfiu_utils/google_maps_utils.js', array ('google_map_api'));
		}else{
			wp_register_script('google_map_api_utils', $this->plugin_url().'google_maps_utils.js', array ('google_map_api'));
		}
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

	public function save_slideshow($post_id, $post)
	{
		$this->get_post();
		
		$slideshows = $this->plugin->_post["slideshow"];
	
		$post_slideshows = new IPM_PostSlideshows($this, "", $this->_post['post_ID']);
		$post_slideshows->post_image_id = $this->_post['wpss_post_photo'];
		$post_slideshows->save_post_image();
	
		if(is_array($slideshows))
		{
			foreach($slideshows as $key => $slideshow)
			{
					
				if($key == "single")
				{
					//@Priyank Wrote this loop	
					foreach($slideshow['photos'] as $key => $photo)
					{
						$ipm_photo = new IPM_SlideshowPhoto($this, $key);
						$ipm_photo->title = $photo['title'];
						$ipm_photo->photo_credit = $photo['photo_credit'];
						$ipm_photo->geo_location = $photo['geo_location'];
						$ipm_photo->original_url = $photo['original_url'];
						$ipm_photo->alt = $photo['alt'];
						$ipm_photo->caption = $photo['caption'];
						$ipm_photo->update();
					}						
				}
				else
				{
					$ipm_slideshow = new IPM_Slideshow($this, $key);
					$ipm_slideshow->title = $slideshow['title'];	
					$ipm_slideshow->photo_credit = $slideshow['photo_credit'];	
					$ipm_slideshow->geo_location = $slideshow['geo_location'];	
					$ipm_slideshow->description = $slideshow['description'];
					$ipm_slideshow->update();
					
					foreach($slideshow['photos'] as $key => $photo)
					{
						$ipm_photo = new IPM_SlideshowPhoto($this, $key);
						$ipm_photo->title = $photo['title'];
						$ipm_photo->photo_credit = $photo['photo_credit'];
						$ipm_photo->geo_location = $photo['geo_location'];
						$ipm_photo->original_url = $photo['original_url'];
						$ipm_photo->alt = $photo['alt'];
						$ipm_photo->caption = $photo['caption'];
						//@Priyank added next line
						$ipm_photo->update();
					}		
				}	
			}
		}		
	}
		
		
		
		
		
	
	//generic render view function
	private function render_view($view, $parameter, $end= "")
	{
		ob_start();
		if(is_array($parameter))
		{
			extract($parameter);
		}
		
		include(WPSSVIEWS .  $view);
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	//render_view wrapper that only gets backend views
	public function render_backend_view($view, $parameters = array())
	{
		$view = "backend/".$view;
		$output = $this->render_view($view, $parameters, "backend");
		return $output;
	}
	//render_view wrapper that only gets backend views
	public function render_frontend_view($view, $parameters = array())
	{
		$view = "frontend/".$view;
		$output = $this->render_view($view, $parameters, "backend");
		return $output;
	}
		
		
}


?>