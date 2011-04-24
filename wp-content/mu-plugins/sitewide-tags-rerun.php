<?php
/* 
Plugin Name: Sitewide Tags Rerun
Plugin URI: http://wfiu.org
Version: 1.0
Description: Runs sitewides tags for selected posts
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/

require_once(ABSPATH.PLUGINDIR.'/wfiu_utils/plugin_functions.php');

function compare_posts($blog_id, $gp, $lp){
	$str ='';

	//return implode(',', $diffs);	
	$tags_blog_id = get_site_option( 'tags_blog_id' );

	$lp_cats = my_get_post_categories($blog_id, $lp['ID']);
	$lp_cids = array_keys($lp_cats);
	//print_r($lp_cats);echo "<br />";
	$gp_cats = my_get_post_categories($tags_blog_id, $gp['ID']);
	$gp_cids = array_keys($gp_cats);
	//print_r($gp_cats);echo "<br />";
	
	$missing_cats = array();
	
	for($i=0;$i<sizeof($lp_cids);++$i){
		if(!in_array($lp_cids[$i], $gp_cids)){
			$missing_cats[] = $lp_cats[$i];
		}
	}
	
	if(sizeof($missing_cats)>0){
		$str .= "<div style='float:left; text-align: center; padding: 3px; margin-top: 5px; border: 1px solid;background-color: #D8D8D8;'>\n";
		$str .= "<div style='color:#A2271E' >Missing categories</div>\n";
		$str .= "<div style='border-top: 1px solid;' >".implode(',', $missing_cats) ."</div>\n";

		$str .= "</div>";
	}


	$lp_tags = my_get_post_tags($blog_id, $lp['ID']);
	$gp_tags = my_get_post_tags($tags_blog_id, $gp['ID']);
	
	$missing_tags = array();
	foreach($lp_tags as $tag){
		if(!in_array($tag, $gp_tags)){
			$missing_tags[] = $tag;
		}
	}
	
	if(sizeof($missing_tags)>0){
		$str .= "<div style='float:left; text-align: center; padding: 3px; margin-top: 5px; border: 1px solid;background-color: #D8D8D8;'>\n";
		$str .= "<div style='color:#A2271E' >Missing tags</div>\n";
		$str .= "<div style='border-top: 1px solid;' >".implode(',', $missing_tags) ."</div>\n";

		$str .= "</div>";
	}





	$diffs = array();

	unset($gp['ID'], $gp['guid']);
	unset($lp['ID'], $lp['guid']);
	foreach($lp as $field=>$val){
			if($gp[$field] != $val){
				$diffs[] = $field;				
			}
	}
	
	foreach($diffs as $field){
		$str .= "<div style='float:left; text-align: center; padding: 3px; margin-top: 5px; border: 1px solid;'>";//</div>background-color:#E3D69A;'>";
		//$str .= "<span style='color:#A2271E' >$field</span>";
		$str .= "<div >$field</div>\n";// style='color:#A2271E'
		//$str .= "<hr />";
		$str .= "<div style='border-top: 1px solid;' class='global_post' >".$gp[$field]."</div>\n";
		//$str .= $gp[$field];
		//$str .= "<hr />";
		$str .= "<div style='border-top: 1px solid;' class='local_post' >".$lp[$field]."</div>\n";
		//$str .= $lp[$field];
		$str .= "</div>\n";
	}

	return $str;
}

function get_global_post($bid, $ID){
	global $wpdb;

	$tags_blog_id = get_site_option( 'tags_blog_id' );
	$query = "SELECT * FROM wp_${tags_blog_id}_posts WHERE guid = \"${bid}.$ID\";";
	//echo $query . "<br />\n";
	$p = $wpdb->get_row($query);
	unset($p->comment_status);
	unset($p->ping_status);

	return $p;
}

function get_local_post($bid, $ID){
	global $wpdb;

	$p = $wpdb->get_row("SELECT * FROM wp_${bid}_posts WHERE ID = $ID;");
	unset($p->comment_status);
	unset($p->ping_status);

	return $p;
}

function do_compare_table($blog_id, $gp, $lp, $start=0, $n = 15){
	global $wpdb;
	/*echo "<h2>Global posts</h2>";
	print_r($gp);
	echo "<h2>Local posts</h2>";
	print_r($lp);*/
	?>
	<script type='text/javascript'>
		jQuery(document).ready(function() {
			jQuery("#menu_box").click(function(){
		   		if(jQuery(this).attr('checked')){
		   			jQuery('.myboxes').attr('checked', true);
		   		}else{
		   			jQuery('.myboxes').attr('checked', false);
		   		}
			});

			
		});
	</script>
	<style type='text/css'>
	.global_post {
		/*color: #FFB90F;*/
		background-color: #AAA4E9;
		
	}
	.local_post {
		
		/*color: #5548DC; */
		background-color: #FFF0A1
	}
	</style>
	<?php
	
	$lp = order_array_by_keys($lp);	
	$i=0;
	$diff_array = array();
	foreach($lp as $key=>$local_post){
		$local_post = get_local_post($blog_id, $key);
		$global_post = get_global_post($blog_id, $key);
		//echo $local_post->post_name . '  and ' . $global_post->post_name . " <br />\n";
		//echo $local_post->post_date_gmt . '  and ' . $global_post->post_date_gmt . " <br />\n";
		//$global_post = $gp[$key];

		$title  = ($global_post->post_title == $local_post->post_title) ? $global_post->post_title : "<span class='global_post'>".$global_post->post_title . "</span><br /><span class='local_post'>".$local_post->post_title . "</span>";
		if(!isset($local_post) || !isset($global_post)){
			$diff= '';
			$title = isset($global_post) ? $title : $local_post->post_title;
		}else{
			$diff = compare_posts($blog_id, (array)$global_post, (array)$local_post);
		}

		if($diff || !isset($global_post)){
			/*if(!isset($global_post)){
				echo '|' .$key . '|';
				print_r($global_post);
			}*/
			/*print_r($local_post);
			echo "<br /> <br />";*/
			$diff_array[] = table_data_fill(($i+1).'. <input type="checkbox" class="myboxes" name="posts[]" value="'.$key.'" />',"$key", $title, isset($global_post) ? '<div style="color:green;width:100%;text-align:center;">found ('.$global_post->ID.')</div>' :'<span style="color:red">missing</span>' , array("style='text-align:left;'", $diff ? $diff."\n" : '<div style="color:green;width:100%;text-align:center" >none</div>'));
		}
		++$i;
	}
	//print_r($diff_array);


	echo "<form action='?page=" . $_GET['page'] . "' method='post'>\n";
	echo "<input type='hidden' name='blog_ids' value='$blog_id' />";
	$end = (($start+sizeof($diff_array))>($start+$n)) ? $start+$n : $start+sizeof($diff_array);
	$start = ($start > -1) ? $start : 0;
	$suffix = sizeof($diff_array) . ' conflicts in ' . sizeof($lp) . ' posts | Start at: <input type="text" name="start_at" value="'.$start.'" style="width: 60px" /><input type="hidden" name="blog_ids" value="'.$blog_id.'"/><input type="submit" value="Go" />';

	echo "<table>";

	$page = "?page=" . $_GET['page'];
	echo table_data_fill(array('style="text-align: right;" colspan="5"',"Global posts <span class='global_post' style='border: 1px solid; padding-right: 5px; padding-top: 4px;'>&nbsp;</span>
		<br /><br />
		 Local posts <span class='local_post' style='border: 1px solid; padding-right: 5px; padding-top: 4px;'>&nbsp;</span>	<br /><br />"));

	if($start > 0){
		if($end < sizeof($diff_array)){
			echo table_data_fill(array('style="text-align: right;" colspan="5"', " <a href='$page&blog_ids=$blog_id&start_at=".($start-$n)."'> << </a>" ."Displaying ".($start+1)."-".$end." of ".sizeof($diff_array)." <a href='$page&blog_ids=$blog_id&start_at=$end'> >> </a>"));
		}else{
			echo table_data_fill(array('style="text-align: right;" colspan="5"', " <a href='$page&blog_ids=$blog_id&start_at=".($start-$n)."'> << </a>".($start+1)."-".$end." of ".$suffix));
		}
	}else if($end < sizeof($diff_array)){
		echo table_data_fill(array('style="text-align: right;" colspan="5"', "Displaying ".($start+1)."-".$end." of ".$suffix. " <a href='$page&blog_ids=$blog_id&start_at=$end'> >> </a>"));
	}else{
			echo table_data_fill(array('style="text-align: right;" colspan="5"', "Displaying ".($start+1)."-".$end." of ".$suffix));
	}

	echo table_headers('<input type="checkbox"  id="menu_box" />',array('style="border:1px solid"','Post ID'), array('style="border:1px solid"','Post Title'), array('style="border:1px solid;" class="global_post"','Global post (id)'), array('style="border: 1px solid"', 'Differences'));


	//$i=0;
	$i=$start;
	$doform = false;
	foreach($diff_array as $diff){
		//echo $i . "<br />";
		//if($i < $start){++$i;continue;}
		
		if($i==$end)break;
		

		if($diff){
			echo $diff;
		}
		++$i;
	}
	echo "</table><br />";
	$doform = true;//override
	echo $doform ? "<input class='button' type='submit' value='Archive/Update selected posts' />" :'';
	echo "</form>";
}
function archive_posts($blog_id, $posts){
	$bd = get_blog_details( $blog_id );

	include_once(ABSPATH .'wp-content/mu-plugins/sitewide-tags.php');
	echo "<h2>Archived/Updated posts for $bd->blogname</h2>";
	//echo get_blog_status($blog_id, "public");
	switch_to_blog( $blog_id );
	$i = 0;
	echo "<ol style='list-style-type: decimal' >";
	foreach($posts as $pid){
		$post = get_post($pid);

		//$post->post_excerpt = str_replace('"','!!poop', $post->post_excerpt);
		//echo $post->post_excerpt;
		sitewide_tags_post($pid, $post);
		echo "<li>". $post->post_title ." <span style='color:white;background-color: green;padding: 2px;'>&#8730;</style> </li>\n";
		++$i;
	}
	echo "</ol>";
	$tags_blog_id = get_site_option( 'tags_blog_id' );

	switch_to_blog( $tags_blog_id );
	
	do_post_comparison($blog_id, 0);
	
	return;
}

function do_post_comparison($blog_id, $start=0, $n = 40){
	global $wpdb;
	$limit = 1000;
	//echo $start . "|<br />";
	//echo $end;
	$tags_blog_id = get_site_option( 'tags_blog_id' );
	$bd = get_blog_details( $blog_id );

	//$query = "SELECT * FROM wp_${tags_blog_id}_posts WHERE post_type='post' AND (post_status='publish' OR post_status='future') AND guid LIKE \"${blog_id}.%\" ORDER BY guid LIMIT $limit;";
	$query = "SELECT guid FROM wp_${tags_blog_id}_posts WHERE post_type='post' AND (post_status='publish') AND guid LIKE \"${blog_id}.%\" ORDER BY guid;";
	$global_p = $wpdb->get_results($query);

	//echo "|$query|";
	//print_r($global_p);
	//return;
	//$global_posts = array();
	$gp_content = array();
	foreach($global_p as $post){
		//$global_posts[] = substr(strrchr($post->guid, '.'), 1 );
		$gp_content[substr(strrchr($post->guid, '.'), 1 )] = '';

		unset($post->comment_status);
		unset($post->ping_status);
	}
	
	//*****************	
	/*$group = "(".implode(",", $global_posts).")";
	if(sizeof($posts)>0){
		//$query = "SELECT ID, post_title, post_content FROM wp_${blog_id}_posts WHERE post_type='post' AND (post_status='publish' OR post_status='future') AND ID NOT IN $group ORDER BY ID LIMIT $limit;";
		//$query = "SELECT ID, post_title, post_content FROM wp_${blog_id}_posts WHERE post_type='post' AND (post_status='publish') AND ID NOT IN $group ORDER BY ID LIMIT $limit;";
	}else{
		//$query = "SELECT ID, post_title, post_content FROM wp_${blog_id}_posts WHERE post_type='post' AND (post_status='publish' OR post_status='future') ORDER BY ID LIMIT $limit;";
		$query = "SELECT ID, post_title, post_content FROM wp_${blog_id}_posts WHERE post_type='post' AND (post_status='publish') ORDER BY ID LIMIT $limit OFFSET $start;";

	}*/
	//echo $query;
	//$unarchived_posts = $wpdb->get_results($query);
	//*****************
	//$query = "SELECT * FROM wp_${blog_id}_posts WHERE post_type='post' AND (post_status='publish' OR post_status='future') ORDER BY ID LIMIT $limit;";
	$query = "SELECT ID FROM wp_${blog_id}_posts WHERE post_type='post' AND (post_status='publish') ORDER BY ID LIMIT $start, $n;";
	//echo $query;
	$local_posts = $wpdb->get_results($query);
//	$unarchived_posts = $local_posts;
	//echo "im here2";
	$bp_content = array();
	foreach($local_posts as $key=>$post){
		$pid=$post->ID;
		unset($post->comment_status);
		unset($post->ping_status);

		$bp_content[$pid] = '';
	}	
	//print_r($bp_content);
	//return;
	//echo sizeof($bp_content);

	//*****************
	if(sizeof($local_posts)==0){
		echo "<div id='message' class='updated fade below-h2'>All <b>".sizeof($local_posts)."</b> posts from blog <b>$bd->blogname</b> are indexed on the <b>Global Posts</b> blog.</div>";
		//if(sizeof($local_posts)>0){
		//	do_compare_table($blog_id, $gp_content, $bp_content, $start, $n);
		//}

	}else{
		echo "<h2>Post comparison for $bd->blogname blog</h2>";
		//print_r($unarchived_posts);
		
		//if(sizeof($local_posts)>0){
			do_compare_table($blog_id, $gp_content, $bp_content, $start, $n);
		//}
	}


	return;
	
	
}



function sitewide_tags_rerun_page(){
	global $wpdb;
	echo "<div class='wrap'>";
	if(isset($_POST['posts'])){
		archive_posts($_POST['blog_ids'], $_POST['posts']);
	
	}else if(isset($_REQUEST['blog_ids'])){
		$start = $_REQUEST['start_at'] ? $_REQUEST['start_at'] : 0;
		do_post_comparison($_REQUEST['blog_ids'], $start);
	}
	$tags_blog_id = get_site_option( 'tags_blog_id' );
	//echo $tags_blog_id;
	
	$post_blog_id = $wpdb->blogid;
	
	$blogs = $wpdb->get_col("SELECT blog_id FROM wp_blogs;");
	echo "<div class='wrap'>";
	echo "<h2>Select blog to inspect:</h2>";
	echo "<form action='?page=" . $_GET['page'] . "' method='post'>\n";
	echo "<select name='blog_ids'>";
	foreach($blogs as $blog_id){
		if($blog_id == $tags_blog_id)continue;
		echo "<option value='".$blog_id."' >";
		$bd = get_blog_details( $blog_id );
		
		echo $bd->blogname . " (";
		echo $bd->public ? "Public" : "Private";
		echo ")";
		echo "</option>";	
		
	}
	echo "</select>";
	echo "<input type='submit' value='Submit' class='button'/>";
	echo "</form>";
	//print_r($bd);
	echo "</div>";
}

function sitewide_tags_rerun_menu() {
	global $map_item_class, $wpdb;
// Add a new top-level menu:
//     add_menu_page('WFIU Plugins', 'WFIU Plugins', 7, __FILE__ , '');

	$tags_blog_id = get_site_option( 'tags_blog_id' );
	if($wpdb->blogid != $tags_blog_id)return;
	//add_menu_page('Home', 'Home', 7, ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php');
	//if(get_bloginfo('version')>= 2.7 ){
	//	wfiu_do_main_page();
	//	add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Sitewide Tags', 'Sitewide Tags', 9, 'Sitewide Tags', 'sitewide_tags_rerun_page');
	//}else{
		add_menu_page('Sitewide Tags', 'Sitewide Tags', 9, __FILE__ , 'sitewide_tags_rerun_page');
	//}
//     add_menu_page('Mapper', 'Mapper', 7, __FILE__ , 'adminMenu');

// Add submenus to the custom top-level menu:
     //add_submenu_page(__FILE__, 'Configuration', 'Configuration', 7, 'Configuration', 'gb_contact_form_admin');
     //add_submenu_page(__FILE__, 'Styling', 'Styling', 7, 'Styling', 'gb_contact_form_admin_style');
     //add_submenu_page(__FILE__, 'Documentation', 'Documentation', 7, 'Documentation', 'gb_contact_form_admin_docs');
}


//activate plugin
//add_action('activate_mapper/mapper.php', array(&$map_item_class, 'activate'));

//add_action( "admin_print_scripts", array(&$map_item_class,'plugin_head'));

//Edit form hooks
/*add_action('simple_edit_form', array(&$map_item_class, 'post_form'));
add_action('edit_form_advanced', array(&$map_item_class, 'post_form'));
add_action('edit_page_form', array(&$map_item_class, 'post_form'));*/
add_action('admin_menu', 'sitewide_tags_rerun_menu');
//add_action('admin_menu', array(&$map_item_class, 'mapper_box'), 1);//add slideshow box

//save and delete post hook
//add_action('save_post', array(&$map_item_class,'saveItem'));
//add_action('delete_post', array(&$map_item_class,'deleteItem'));


//add_filter('manage_posts_columns', array(&$map_item_class,'add_column'));
//add_filter('manage_posts_custom_column', array(&$map_item_class,'do_column'), 10, 2);
?>
