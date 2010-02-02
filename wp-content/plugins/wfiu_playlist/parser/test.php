<?php
global $wfiuPlaylist;
echo "doPlaylist?: ".$_GET['doPlaylist']."\n<br/>";
echo "post_id: " . $_GET['post_id']."\n<br/>";
echo "url: ".$_GET['playlist_url']."\n<br/>";
$wfiuPlaylist->importOnePlaylist($_GET['post_id'],$_GET['playlist_url']);
//echo "slkdfjskdfj<br/>\nhasdifstuff!!!!";
?>
