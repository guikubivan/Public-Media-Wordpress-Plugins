IPM Wordpress Slideshow

Manage slideshows and images attached to posts.

Files required:
/controllers/*
/lib/*
/models/*
/views/*
wordpress_slideshow.php
ui.tabs.css


The actual "Slideshow Manager" that can be accessed via the wordpress sidebar still uses the old version...
This requires other files such as wordpress_classes.php in the root directory.

The XLS stylesheets need to all be converted into .php files located in the /views/frontend/ directory using
	the same filenames as the xls stylesheets.  It's pretty easy, as it only uses loops and ifs which can, in
	most cases, be converted directly.  Examples of this conversion are wpss_program_single_new.php and
	wpss_program_thumb_small.php in the /views/frontend/ directory.

Models are structured as follows (more details about each model can be found in the specific model's file.)


Wordpress Post (native to wordpress... Post or Page type)
		|
IPM_PostSlideshow
		|____________________________
		|							|	
	  Array					IPM_SlideshowPhoto (if the post only has single photo)
		|________________________________________________ ___ __ _ _  _  _
		|			|				|				|
 IPM_Slideshow	IPM_Slideshow	IPM_Slideshow	IPM_Slideshow		
		|
	  Array
		|_____________________________________________________________ ___ __ _ _  _  _
		|				|					|					|				
IPM_SlideshowPhoto	IPM_SlideshowPhoto	IPM_SlideshowPhoto	IPM_SlideshowPhoto	
		|
   IPM_Photo
		|
Worpress Post (attachment type)