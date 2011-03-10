<?php

/*
 * This class is used primarily for scoping the action functions and holding globals
 */
class wpss_main{
	public $post;
	
	public $_post;
	public $_get;
	
	public $tab_order = 200;
	
	public $plugin_prefix = 'wpss_';
	public $plugin_url;
	
	public $google_api_key = 'ABQIAAAAr-xNI7Kn6NVsjnBBFpVvTBSqyx3ou9-UoyELVa_btXTyMYHxNRSdDo_WgOuZ0Zn8SSPShIN_pETOsg';
		
	
	public $wpdb;
	public function __construct($wpdb)
	{
		$this->wpdb = $wpdb;
		$this->_post = $_POST;
		$this->_get = $_GET;
		
		$this->plugin_url = get_bloginfo('url').'/wp-content/plugins/ipm-wordpress-slideshow-MVC/';
		
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
		 $this->render_backend_view("admin_head_javascript.php");
		
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
		
		include(WPSSVIEWS . $end . "/" . $view);
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
		$output = $this->render_view($view, $parameters, "backend");
		return $output;
	}
		
		
}


?>