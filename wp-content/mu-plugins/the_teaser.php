<?php

/* 
Plugin Name: IPM - the_teaser
Plugin URI: http://wfiu.org
Version: 1
Description: Defines the the_teaser function so that it can be removed from the IPM - Character Counter.
Author: Ben Serrette
Author URI: http://www.wfiu.org
*/
/*

if(!function_exists('the_teaser'))
{
	function the_teaser()
	{
		global $post, $drop_caps_plugin;
		$teaser = get_post_meta($post->ID, 'teaser_text', false);
		$teaser = "<p>".stripslashes($teaser[0]). "</p>";


		if(class_exists('DropCaps')) {
			//require_once(ABSPATH.PLUGINDIR.'/wp_drop_caps.php');
			$options = $drop_caps_plugin->get_options();

			if ($options['the_excerpt'] && $drop_caps_plugin->is_enabled($options) && !$drop_caps_plugin->is_excluded($options)){
				//We're look for the first paragraph tag followed by a capital letter
				$pattern = '/<p>((&quot;|&#8220;|&#8216;|&lsquo;|&ldquo;|\')?[A-Z])/';
				$replacement = '<p class="first-child"><span title="$1" class="cap"><span>$1</span></span>';
				$teaser = preg_replace($pattern, $replacement, $teaser, 1 );
			}
		}

		echo $teaser;	
	}
}

*/

?>