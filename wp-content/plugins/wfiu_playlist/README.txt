This little plugin adds a playlist to posts and pages.  A table is created that keeps tracks of your playlists, and this is the SQL query that creates the table:

CREATE TABLE [your blog prefix + wfiu_playlist] (
      playlist_item_id INT(9) NOT NULL AUTO_INCREMENT,
      post_id INT(9) NOT NULL DEFAULT 0,
      title VARCHAR(255) NOT NULL,
      composer VARCHAR(255),
      artist VARCHAR(255) NOT NULL,
      album VARCHAR(255),
      label VARCHAR(255),
      release_year INT(4) DEFAULT 0,
      asin VARCHAR(20),
      notes VARCHAR(1023),
      PRIMARY KEY (playlist_item_id),
      KEY post_id (post_id)
	);


As you can see, it was developed for Wordpress MU, with latest version as of 07/28/08.

