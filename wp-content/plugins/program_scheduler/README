Template functions
==

the_schedule(station_name, mode, $echo = true)
  * station_name is the name of the station/schedule.
    - Required when mode is "weekly", "single", or "now".
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
  * $echo only works for modes "now", "next", "prev" and "playlist-item-now". When true, it will output and populate hook functions. When false, it will also populate the hook functions, but won't output anything. Defaults to true.


Hook functions available for when you call the_schedule(station_name, "now"/"next"/"prev", true/false):

ps_program_has_url() - returns true/false
ps_program_has_photo_url() - returns true/false
ps_program_show_playlist() - returns true/false

Note the following echo the output inside the function. To return the result, use its ps_get_* counterpart so for example, ps_program_time() will echo the result and ps_get_program_time() will return it. Unless otherwise noted below, the ps_get_* functions will return an empty string ("") if no value exists for that field.

ps_program_name() - Echos/returns "N/A" if for some reason there is no program loaded
ps_start_time($format) - Optional $format as described in http://us.php.net/manual/en/function.date.php
                - Returns "N/A" if for some reason there is no program loaded
ps_end_time($format) - Optional $format as described in http://us.php.net/manual/en/function.date.php
              - Returns "N/A" if for some reason there is no program loaded
ps_program_info_link()
ps_program_description()
ps_host_name()
ps_host_bio()
ps_host_photo_url()
ps_host_bio_link()

Hook functions for when you call the_scheduler(station_name, "playlist-item-now", true/false). Same as above, these all have their ps_get_* counterparts.

ps_current_playlist_url() - Echos a <a> tag with a link to the playlist. The ps_get counterpart just returns the url.

ps_pitem_composer()
ps_pitem_title()
ps_pitem_artist()
ps_pitem_album()
ps_pitem_label()
ps_pitem_release_year()
ps_pitem_asin()
ps_pitem_notes()
ps_pitem_start_time($format) - Optional $format as described in http://us.php.net/manual/en/function.date.php
ps_pitem_duration($format) - Returns a string formatted by default as "mm:ss". Optional $format as described in http://us.php.net/manual/en/function.sprintf.php (default is "%02s:%02s")
ps_pitem_label_id()



