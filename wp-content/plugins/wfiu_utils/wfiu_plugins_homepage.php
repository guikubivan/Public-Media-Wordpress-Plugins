<div class='wrap'>
<h2>Plugins Home</h2>

<?php
/*
global $menu;

foreach($menu as $m){
	print_r($m);
	echo "<hr />";
}
echo "<hr /><hr />";
*/
global $submenu;

if($submenu['wfiu_utils/wfiu_plugins_homepage.php'][0][0] == 'WFIU Plugins'){
	unset($submenu['wfiu_utils/wfiu_plugins_homepage.php'][0]);
}
/*foreach($submenu as $key => $m){
	echo "<h2>$key</h2>";
	print_r($m);
	echo "<hr />";
}*/

if(sizeof($submenu['wfiu_utils/wfiu_plugins_homepage.php'])>0){
	echo "<ul>";
	/*for($i=1; $i<sizeof($submenu['wfiu_utils/wfiu_plugins_homepage.php']);++$i){
		$name = $submenu['wfiu_utils/wfiu_plugins_homepage.php'][$i][0];
		echo "<li><b>".$name ."</b>: <a href='".get_bloginfo('url')."/wp-admin/admin.php?page=$name' > Go to page</a></li>";

	}*/
	foreach($submenu['wfiu_utils/wfiu_plugins_homepage.php'] as $sm){
		$name = $sm[0];
		echo "<li><b>".$name ."</b>: <a href='".get_bloginfo('url')."/wp-admin/admin.php?page=$name' > Go to page</a></li>";

	}
	echo "</ul>";


}

?> 



</div>
