#Setup

To set up, first activate the plugin. For a multi-sites setup, make sure you activate the plugin in a single site first to create the needed tables, then activate it for all sites.

You can now start managing Schedules and add events to your schedules. You can also change some settings in the settings page, such as in which page you want to output your schedule(s).

To view it on the frontend, you will have to add the following to the header file in your theme to pull the css and js required:

    if(function_exists("ps_frontend_enqueue")) ps_frontend_enqueue();
 
*Note*: The plugin piggy-backs on wordpress' defined jquery libraries, so it probably won't work if you have added your own jquery manually for your frontend. However, you can try to derigester Wordpress' libraries and then register yours before wp_head() is called (see http://codex.wordpress.org/Function_Reference/wp_deregister_script). Basically, wordpress has to know about your jquery libraries. Or if you can try this plugin if you'd like to use google's provided jquery libraries: http://wordpress.org/extend/plugins/use-google-libraries.

#Upgrading from Program Scheduler 1.0

As of now, this plugin is not upgradable from Program Scheduler 1.0. Please delete the previous plugin and remove all related tables.

#Template functions

##Main Function

*the_schedule(station_name, mode, $echo = true)*

* station_name is the name of the station/schedule.
    - Required when mode is "weekly", "single", "now", "next", "prev", or "playlist-item-now", "playlist-item-now-ajax".
    - When mode is "single", use a comma separated string of schedule names.
    - Optional when mode is "listing". If station_name is given, it will only retrieve programs for that schedule, otherwise, it will retrieve all.
    - Should be empty("") when mode is "all".
* mode can be one of:
    - "all" - Retrieves comprehensive view of schedules.
    - "weekly" - Week view of single schedule
    - "single" - Daily view for one or more schedules.
    - "listing" - List of programs and respective air dates across all schedules
    - "now", "next", "prev" - Current, Next, Previous program
    - "playlist-item-now" - Current playlist item of current program
    - "playlist-item-now-ajax" - Dynamic current playlist item of current program (updates at song boundaries automatically)
* $echo only works for modes "now", "next", "prev" and "playlist-item-now". When true, it will output and populate hook functions. When false, it will also populate the hook functions, but won't output anything. Defaults to true.

##Hooks

###Program hooks

Available for when you call the_schedule(station_name, "now"/"next"/"prev", true/false):

* *ps_program_has_url()*
* *ps_program_has_photo_url()*
* *ps_program_show_playlist()*
    * Returns true/false

Note the following echo the output inside the function. To return the result, use its ps\_get\_\* counterpart so for example, ps\_program\_time() will echo the result and ps\_get\_program\_time() will return it. Unless otherwise noted below, the ps\_get\_\* functions will return an empty string ("") if no value exists for that field.

* *ps_program_name()*
    * Echos/returns "N/A" if for some reason there is no program loaded
* *ps_start_time($format)*
    * Optional $format as described in http://us.php.net/manual/en/function.date.php
    * Returns "N/A" if for some reason there is no program loaded
* *ps_end_time($format)*
    * Optional $format as described in http://us.php.net/manual/en/function.date.php
    * Returns "N/A" if for some reason there is no program loaded
* *ps_program_info_link()*
* *ps_program_description()*
* *ps_host_name()*
* *ps_host_bio()*
* *ps_host_photo_url()*
* *ps_host_bio_link()*

###Playlist item hooks
Hook functions for when you call the\_scheduler(station\_name, "playlist-item-now", true/false). Same as above, these all have their ps\_get\_\* counterparts.

* _ps_current_playlist_url()_
    * Echos a link to the playlist. The ps\_get counterpart just returns the url.
* _ps_pitem_composer()_
* _ps_pitem_title()_
* _ps_pitem_artist()_
* _ps_pitem_album()_
* _ps_pitem_label()_
* _ps_pitem_release_year()_
* _ps_pitem_asin()_
* _ps_pitem_notes()_
* _ps_pitem_start_time($format)_
    * Optional $format as described in http://us.php.net/manual/en/function.date.php
* _ps_pitem_duration($format)_
    * Returns a string formatted by default as "mm:ss". Optional $format as described in http://us.php.net/manual/en/function.sprintf.php (default is "%02s:%02s")
* _ps_pitem_label_id()_