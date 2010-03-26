<?php

require_once('../../../wp-config.php');
require_once('../../../wp-includes/wp-db.php');
global $wpdb, $post;

$mytable = '';
if(preg_match("/wp_\d*_/",$_GET['blog']) ){
	$mytable = $_GET['blog'] . "wfiu_playlist";
}else{

	$query = "SELECT blog_id FROM wp_blogs WHERE path LIKE '%" . $_GET['blog'] . "%';";
	$blog_id = $wpdb->get_var($query);

	if(!$blog_id){
		die("Blog not found");
	}
	$mytable = "wp_" . $blog_id . "_wfiu_playlist";
}
//***********
preg_match("/wp_(\d*)_/",$_GET['blog'], $matches);
$blog_id = $matches[1];
//***********
$query = "SELECT post_title, post_modified FROM wp_" . $blog_id . "_posts WHERE ID=" . $_GET['post_id'];

$post_info = $wpdb->get_row($query);

$query = "SELECT * FROM ".$mytable ." WHERE post_id=".$_GET['post_id'] . " ORDER BY playlist_item_id ASC";
//echo $query;
$playlist = $wpdb->get_results($query);
if(sizeof($playlist)==0){
 die();
}
header('Content-type: text/xml;charset=UTF-8'); // send the headers
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?xml-stylesheet type="text/xsl" href="wfiu_playlist_stylesheet.xsl" version="1.0"?>' . "\n";
?>

<post date="<?php echo $post_info->post_modified; ?>" >
<showtitle><?php echo $post_info->post_title; ?></showtitle>


<?php
function preparefield($field){
	global $wpdb;
	return htmlspecialchars($field);
	$badChars = array('“', '”', '’', '‘', '’', "…", 'é');
	$repChars = array('"', '"', "'", "'", "'", "...", '&eacute;');
	return htmlentities(str_replace($badChars, $repChars, $field, $count));
		//if($count>0)echo "alert('replaced one');";
}

foreach($playlist as $playlistItem){
	$artist = preparefield($playlistItem->artist);
	//$artist = str_replace('&','\&',$artist);
	$composer = preparefield($playlistItem->composer);
	$title = preparefield($playlistItem->title);

	$album = preparefield($playlistItem->album);
	$label = preparefield($playlistItem->label);
	$asin = preparefield($playlistItem->asin);

	$notes = preparefield($playlistItem->notes);
	//$notes = str_replace(array('"',"'", "\r", "\n", "\0"), array('\"','\\\'','\r', '\n', '\0'), $notes);
	$rel_year = $playlistItem->release_year;

	echo "<item>\n";
	echo $title ? "<title>$title</title>\n" : '';;
	echo $artist ? "<artist>$artist</artist>\n" : '';
	echo $composer ? "<composer>$composer</composer>\n" : '';
	echo $album ? "<album>$album</album>\n" : '';
//sadlfj(sdfd);
	echo $label ? "<label>$label</label>\n" : '';
	echo $rel_year ? "<release_year>$rel_year</release_year>\n" : '';
	echo $asin ? "<asin>$asin</asin>\n" : '';
	echo $notes ? "<notes><![CDATA[$notes]]></notes>\n" : '';
	echo "</item>\n\n";

}
?>

</post>
