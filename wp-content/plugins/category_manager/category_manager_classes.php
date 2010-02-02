<?php
/*category_bin - category bin

PROPERTIES
@prop string name
@prop bool multiple_cats
@prop bool required
@prop number_array category_ids

METHODS

add_cats(ids): add categories from ids array given 
@param number_array ids

replace_cats(ids): replace all categories from ids array given
@param number_array ids

*/

class category_bin {
	public $name;
	public $id;//id of category that is the head of the group of categories
	public $multiple_cats;
	public $required;
	public $category_ids = array();
	public $children = array();
	public $parent = null;
	public function __construct($name, $allowmultiple, $req, $parent_id=null, $id = null){
		$this->name = $name;
		$this->multiple_cats = $allowmultiple;
		$this->required = $req;
		$this->parent_id = $parent_id;
		$this->id = $id;
	}

	function set_parent_id($id){
		$this->parent_id = $id;
	}

	function set_id($id){
		$this->id = $id;
	}

	function add_child($name){
		$this->children[] = $name;
	}

	function add_cats($ids){
		$this->category_ids = array_merge($this->category_ids, $ids);
	}

	function replace_cats($ids){
		$this->category_ids = $ids;
	}

	function get_cat_ids(){
		return $this->category_ids;
	}
	function get_short_name(){
		return strtolower(str_replace(' ', '_', $this->name));
	}


}

/*
category_manager - represents a category bin type
PROPERTIES
@prop array_map relationships
@prop category_bins array of category_bin objects

METHODS
post_form()

config_form()

add_category_bin(name, mult, req)

get_category_bin(name)

add_categories(name, cat_ids)
@param string name name of category bin
@param num_array cat_ids category ids

add_relationship(cat_id, cat_ids)
@param num cat_id parent id
@param array cat_ids child category ids

print_relationships()

*/


/*===========TO-DO=================
-Check relationships are still valid after category groups are rearranged
-Add button on relationships to update categories on existing posts and implement such thing


=================================*/
class category_manager{
	//private $top_bin;
	private $category_bins = array();//the first category_bin item contains the rest of categories that where not part of another category_bin
	private $relationships = array();
	private $group_ids = array();
	private $hide_uncategorized = true;
	public function __construct($top_bin_name){
		$this->category_bins[] = new category_bin($top_bin_name, true, true);
		$catQ = "fields=ids&hierarchical=1&hide_empty=0&exclude=1";
		$cat_ids = get_categories($catQ);
		//$top_bin->add_cats($cat_ids);
		$this->add_categories($top_bin_name, $cat_ids);
		//$donothing='sdf';
	}

	function process_masschange(){


	}

	function config_form_masschange(){
		global $wpdb;

		if($_GET['categories_choose']){
			$catQ = "hierarchical=1&hide_empty=0";
			$categories = get_categories($catQ);

			$chosen_cat = $_POST['cat'];
			$cobj = get_category($chosen_cat);
			echo "Select categories to assign to all posts of category <b>".$cobj->name."</b> :\n<br/>";

			$buttonsDiv = '<table style="text-align:center"><tr><td>&nbsp;</td><td>Set |</td><td>Unset |</td><td>Omit</td></tr>';
			foreach ($categories as $cat) {
				$style='';
				//print_r($categories);
				if($cat->parent != 0){
					$style='padding-left:13px;';
				}
				$buttonsDiv .= "<tr><td style='text-align:left;$style'><b>".$cat->name."</b></td>
						<td><input type=\"radio\" name=\"".$cat->term_id."\" value=\"set\"> </td>
						<td><input type=\"radio\" name=\"".$cat->term_id."\" value=\"unset\"></td>
						<td><input type=\"radio\" name=\"".$cat->term_id."\" value=\"donothing\" checked></td></tr>";
			}
			$buttonsDiv .="</table>";

			echo "<form action='?page=" . $_GET['page'] . "&categories_set=true#catman_mass_change' method='post'>";
			echo $buttonsDiv;
			echo "<div style=\"width:500px;clear:both;text-align:center\"><input type=\"submit\" value=\"Mass change categories\" /></div>";
			echo '<INPUT TYPE="hidden" NAME="chosen_category" VALUE="'.$_POST['cat'].'">';	
			echo "</form>";
		}else if($_GET['categories_set']){
			//$checkboxes = array();
			$catQ = "fields=ids&hierarchical=1&hide_empty=0";
			$categories = get_categories($catQ);
			$set = array();
			$unset = array();
			foreach ($categories as $cat_id){

				//echo $_POST["$cat->id"];
				//$checkboxes[$cat_id] = $_POST[$cat_id];
				if($_POST[$cat_id]=='set'){
					$set[] = $cat_id;
				}

				if($_POST[$cat_id]=='unset'){
					$unset[] = $cat_id;
				}
			}
			$chosen_cat = $_POST['chosen_category'];
			$request = "SELECT ID, post_title FROM $wpdb->posts, $wpdb->terms, $wpdb->term_taxonomy, $wpdb->term_relationships";
			$request .= " WHERE post_type='post'";
			$request .= " AND $wpdb->term_relationships.object_id = $wpdb->posts.ID";
			$request .= " AND $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id";
			$request .= " AND $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id";
			$request .= " AND $wpdb->term_taxonomy.taxonomy = 'category'";
			$request .= " AND $wpdb->terms.term_id = $chosen_cat;";
			//echo $request;

			$rows = $wpdb->get_results($request);
			$i=1;
			foreach($rows as $row){
				$post_id = $row->ID;
				$post_title = $row->post_title;
				$selected = wp_get_post_categories($post_id);
				echo "$i. $post_title<br />";
				//print_r($selected);
				$new_cats = $set;
				foreach($selected  as $cid){
					if(!in_array($cid, $unset) && !in_array($cid, $set)){
						$new_cats[] = $cid;
					}
				}
				//echo "<br />AFter: ";
				//print_r($new_cats);
				++$i;
				wp_set_post_categories($post_id, $new_cats);
		
			}

			

		}else{
			$catQ = "show_count=1&hierarchical=1&hide_empty=0";
			echo "Select which group of posts to change category:\n<br/>";
			echo "<form action='?page=" . $_GET['page'] . "&categories_choose=true#catman_mass_change' method='post'>";
			wp_dropdown_categories($catQ);
			echo "<div style=\"width:500px;clear:both;text-align:center\"><input type=\"submit\" value=\"Continue\" /></div>";
			echo "</form>";
		

		}

	}

	function add_relationship($cat_id, $cat_ids){
		//echo $cat_id . ' ';
		//print_r($cat_ids);
		if(!is_array($cat_ids)){
			$cat_ids = array($cat_ids);
		}
		$this->relationships[$cat_id] = $cat_ids;
		//print_r($this->relationships);
	}

	function add_category_bin($name, $mult, $req, $parent_id=null, $cat_id){
		/*if(is_int($parent)){
			$categoryObj = get_category($parent);
			$parent =  $categoryObj->name;
		}*/
		$this->category_bins[] = new category_bin($name, $mult, $req, $parent_id, $cat_id);
		if($parent_id !=null){
			$cat_bin = &$this->get_category_bin($parent_id);
			$cat_bin->add_child($name);
		}
	}



	function add_categories($name, $cat_ids){
		if(!is_array($cat_ids)){
			$cat_ids = array($cat_ids);
		}
		$cat_bin = &$this->get_category_bin($name);
		//print_r($cat_ids);
		$cat_bin->add_cats($cat_ids);
		//print_r($cat_bin->category_ids);
	}

	function add_category_group($cat_group_id, $mult, $req, $parent_id=null){
	/*	if($parent_id == null){
			echo $cat_group_id . " is null <br />";
		}else{
			echo "Parent id for " . $cat_group_id . " is " . $parent_id . "<br />";

		}*/
		$categoryObj = get_category($cat_group_id);
		$catQ = "fields=ids&hierarchical=0&hide_empty=0&child_of=".$cat_group_id;
		$children = get_categories($catQ);
		//print_r($children);
		$this->add_category_bin($categoryObj->cat_name, $mult, $req, $parent_id, $cat_group_id);
		$this->add_categories($categoryObj->cat_name, $children);
		$this->group_ids[] = $cat_group_id;


		//Update top category bin
/*		$catQ = "fields=ids&hierarchical=1&hide_empty=0";
		$exclude = '';
		foreach($this->group_ids as $cid){
				$exclude .= $cid . ',';
		}

		if($this->hide_uncategorized){
			$exclude .= '1';
		}else if($exclude){
			$exclude = substr($exclude, 0, -1);
		}

		if($exclude){
			$catQ .= '&exclude='.$exclude;
		}
	
		$other_cats = get_categories($catQ);
*/
		$other_cats = $this->get_extra_cats();//skip top cats
		//print_r($other_cats);
		$this->category_bins[0]->replace_cats($other_cats);
	}
	
	
	function get_unused_cats(){
		$catQ = "fields=ids&hierarchical=0&hide_empty=0";
		$exclude = '';
		$i = 0;
		foreach($this->category_bins as $catbin){
			++$i;
			if($i == 1){
				continue;
			}
			$exclude .= $catbin->id . ',';

			//echo $catbin->name. " <br />";
			foreach($catbin->get_cat_ids() as $cid){
			//	echo $cid . ', ';
				$exclude .= $cid . ',';
			}
			//echo "<br />";
			
		}
		if($this->hide_uncategorized){
			$exclude .= '1';
		}else if($exclude){
			$exclude = substr($exclude, 0, -1);
		}
		//echo $exclude . ' <br />' ;
		if($exclude){
			$catQ .= '&exclude='.$exclude;
		}
		//echo $catQ . ' <br />' ;
		return get_categories($catQ);
	}


	//deprecated! :)
	function add_all_others($name, $mult, $req){
		$catQ = "fields=ids&hierarchical=1&hide_empty=0";
		$exclude = '';
		foreach($this->group_ids as $cid){
				$exclude .= $cid . ',';
		}
		$exclude = substr($exclude, 0, -1);
		if($exclude){
			$catQ .= '&exclude='.$exclude;
		}


		$all_cats = get_categories($catQ);
		//print_r($all_cats);
		$this->category_bins[] = $this->category_bins[sizeof($this->category_bins)-1];


		for($i=sizeof($this->category_bins)-2; $i>0; --$i){
			$this->category_bins[$i] = $this->category_bins[$i-1];
		}
		$this->category_bins[0] = new category_bin($name, $mult, $req);

		$this->add_categories($name, $all_cats);
		
	}

	function get_category_bin($key){
		if(intval($key)>0){
			foreach($this->group_ids as $index => $id){
				if($id == $key){
					return $this->category_bins[$index+1];
				}
			}
		}

		foreach($this->category_bins as $catbin){
			if($catbin->name == $key){
				return $catbin;
			}
		}
		return false;
	}


	function plugin_url(){
		$result = get_bloginfo('url').'/wp-content/plugins/category_manager/';
		return $result;
	}

	function php_get_panel(){
	/*$retString = "document.getElementById('station_latitude').value = '". $lat . "';
			document.getElementById('station_longitude').value = '". $long . "';";
	*/

		$start .= "<h3>WFIU CATEGORIES</h3><div class='inside'><div id='postWFIUCATCONTENT'>";
		$end .= "</div></div>";

		$retString = 'jQuery("#categorydiv").html("'.$start . str_replace("\n",' ',addslashes($this->post_form( $_POST['post_id'] ) ) ).$end.'");';
		//$retString = 'jQuery("#categorydiv").remove();';
		
//		$retString = 'alert("sdf");';
		die($retString);
	}

	function php_get_panel_generic(){
		//$retString = "alert('".$_POST[$this->prefix()."relationship_key"]."');";
		$retString = 'jQuery("#'.$this->prefix().'mapto").html("'. str_replace("\n",' ',addslashes($this->get_categories_panel( $_POST[$this->prefix()."relationship_key"] ) ) ).'");';
		
//		$retString = 'alert("sdf");';
		die($retString);
	}

	function array_flatten($multi_lev_array){
		$retarray = array();
		foreach($multi_lev_array as $key => $val){
			$retarray[] = $key;
			foreach($val as $id){
				$retarray[] = $id;
			}
		}
		return $retarray;
	}

	function sort_cat_ids($cats){
		$parentless = $cats;
		$new_cats = array();
		foreach($cats as $key => $cat_id){
			$categoryObj = get_category($cat_id);
			if($categoryObj->parent != 0){

				if(false !== ($index = array_search($categoryObj->parent, $cats))){
					//if(sizeof($new_cats[$parentless[$index]])>0){
					//echo $parentless[$index] . ' -> ' . $categoryObj->parent  . ' <br />';
					$new_cats[$cats[$index]][] = $cat_id;
					unset($parentless[$index]);
					unset($parentless[$key]);
					//}else{
					//	$new_cats[$parentless[$index]] = $categoryObj->parent;
					//}
				}
			}
		}

		$new_cats = $this->array_flatten($new_cats);

	
		$new_cats = array_merge($new_cats, $parentless);
		return $new_cats;
	}

	function sort_cids_alphabet($cats){
		$cat_names = array();
		foreach($cats as $cat_id){
			$cObj = get_category($cat_id);
			$cat_names[] = $cObj->name;
		}
		asort($cat_names);

		$new_cids = array();	
		foreach($cat_names as $index => $name){
			$new_cids[] = $cats[$index];
		}	
		return $new_cids;
	}

	function get_children($cat_id){
		$children = array();
		foreach($this->category_bins as $catbin){
			foreach($catbin->get_cat_ids() as $cid){
				$categoryObj = get_category($cid);
				if($categoryObj->parent == $cat_id){
					$children[] = $cid;
				}
			}
		}
		if(sizeof($children)>0){
			return $children;
		}else{
			return false;
		}
	}

	function get_extra_cats_ids($skip_top = false){
		$catQ = "fields=ids&hierarchical=0&hide_empty=0";
		$exclude = '';
		$i = 0;
		foreach($this->category_bins as $catbin){
			++$i;
			if($i == 1){
				continue;
			}
			//echo $catbin->name. " <br />";
			if(!$skip_top && ($catbin->id != null)){
				$exclude .= $catbin->id .',';
			}
			
			foreach($catbin->get_cat_ids() as $cid){
			//	echo $cid . ', ';
				$exclude .= $cid . ',';
			}
			//echo "<br />";
			
		}

		if($this->hide_uncategorized){
			$exclude .= '1';
		}else if($exclude){
			$exclude = substr($exclude, 0, -1);
		}

		//echo $exclude . ' <br />' ;
		if($exclude){
			$catQ .= '&exclude='.$exclude;
		}

		//echo $catQ . ' <br />' ;
		return get_categories($catQ);
	}

	function get_extra_cats($skip_top=false){
		$categories = $this->get_extra_cats_ids($skip_top);
		$categories = $this->sort_cat_ids($categories);
		//print_r($categories);

		return $categories;
	}

	function get_post_variables($forceParent=true){
		$save_cats = array();
		foreach($this->category_bins as $catbin){
			$cats = $catbin->get_cat_ids();
			foreach($cats as $id){
				$key = '';
				if($catbin->multiple_cats){
					$key = $this->prefix().$id;
					if($_POST[$key]){
						$save_cats[] = $id;
						if( ($catbin->id!=null) && !in_array($catbin->id,$save_cats) && $forceParent){
							$save_cats[] = $catbin->id;
						}
					}
				}

			}
			if(!$catbin->multiple_cats){
				//echo $catbin->name . ": not mult<br />";
				$key = $this->prefix().$catbin->get_short_name();
				if($_POST[$key] != 'none' && $_POST[$key]){
				//	echo "found one: " .$_POST[$key] . "<br />" ;
					$save_cats[] = $_POST[$key];
					if( ($catbin->id!=null) && !in_array($catbin->id,$save_cats) && $forceParent){
						$save_cats[] = $catbin->id;
					}
				}
			}
		}
		//print_r($save_cats);
		return $save_cats;
	}

	function save_post($post_id){
		if ( !wp_verify_nonce( $_POST[$this->prefix.'noncemane'], plugin_basename(__FILE__) )) {
			return $post_id;
		}

		if ( 'page' == $_POST['post_type'] ) {
			if ( !current_user_can( 'edit_page', $post_id ))
			    return $post_id;
		} else {
			if ( !current_user_can( 'edit_post', $post_id ))
			    return $post_id;
		}

		$this->load_category_groups();
		$save_cats = $this->get_post_variables();

		$extra_cats = $this->get_extra_cats_ids();
		foreach($extra_cats as $id){
			$key = $this->prefix().$id;
			if($_POST[$key]){
				$save_cats[] = $id;
			}
		}
		
		wp_set_post_categories($post_id, $save_cats);
	}


		//**************************AJAX STUFF*****************************
	function admin_head(){ // this is a PHP function
		if($_GET['page'] == "WFIU Category Manager"){
			echo "<!-- Category manager tabs-->";
			echo '<link rel="stylesheet" href="'.$this->plugin_url().'ui.tabs.css" type="text/css" />'."\n";
			wp_enqueue_script( 'jquery-ui-tabs' );
			echo "<!-- END Category manager tabs-->";
		}

	 	 // use JavaScript SACK library for Ajax
		  wp_print_scripts( array( 'sack' ));

		  // Define custom JavaScript function
	?>
		<script type="text/javascript">
		//<![CDATA[
		/*Category Manager Javascript begin

		*/

		function category_manager_js_get_panel()
		{
			
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  mysack.execute = 1;
		  mysack.method = 'POST';
		  mysack.setVar( "action", "action_get_panel" );
		  if(document.getElementById('post_ID') != null){
		  	mysack.setVar( "post_id", document.getElementById('post_ID').value );
		  }
		  mysack.encVar( "cookie", document.cookie, false );
		  //mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
		  //mysack.onCompletion = whenImportCompleted;
		  mysack.runAJAX();
		  return true;
		}

		function category_manager_js_get_panel_generic()
		{
		   var mysack = new sack( 
		       "<?php bloginfo( 'wpurl' ); ?>/wp-admin/admin-ajax.php" );    

		  mysack.execute = 1;
		  mysack.method = 'POST';
		  mysack.setVar( "action", "action_get_panel_generic" );
		  if(document.getElementById('<?php echo $this->prefix()."relationship_key"; ?>') != null){
		  	mysack.setVar( "<?php echo $this->prefix()."relationship_key"; ?>",document.getElementById('<?php echo $this->prefix()."relationship_key"; ?>').value );
		  }
		  mysack.encVar( "cookie", document.cookie, false );
		  //mysack.onError = function() { alert('Ajax error in getting coordinates for given location.' )};
		  //mysack.onCompletion = whenImportCompleted;
		  mysack.runAJAX();
		  return true;
		}

		 // end of JavaScript function myplugin_ajax_elevation
		//]]>
		function clearCheckBoxes(ids){
			//alert(ids[0]);
			if(document.getElementById(ids[0]).checked == false){
				for (var i=1;i<ids.length;i++){
					if( document.getElementById(ids[i])!=null){
					  document.getElementById(ids[i]).checked = false;
					}
				}
			}
		}


		function setChecked(ids){
			//alert(ids[0]);
			if(document.getElementById(ids[0]).checked == true){
				for (var i=1;i<ids.length;i++){
					if( document.getElementById(ids[i])!=null){
					  document.getElementById(ids[i]).checked = true;
					}
				}
			}
		}

		function changeSelectedCategory(id){

			sel = document.getElementById('<?php echo $this->prefix()."relationship_key"; ?>');
			//alert(sel.value);			
			for (i=0; i<sel.options.length; i++) {
				if (sel.options[i].value == id) {
					sel.selectedIndex = i;
					category_manager_js_get_panel_generic();
					//alert("found");
					i=sel.options.length;
				}
			}

		}

		</script>
	<?php


		//$outStr .= '<div id="postWFIUCATBOX" class="postbox if-js-open">';
		//echo '<div id="postMAPPERBOX" class="postbox if-js-closed">';
		
		//echo '<script type="text/javascript" src="'.$this->plugin_url().'category_manager_editpost.js"></script>'."\n";

	} // end of PHP function myplugin_js_admin_header
	//**************************END AJAX STUFF*****************************

	function get_group_relationship_targets($group_ids){
		$all_group_rels = array();
		foreach($this->relationships as $key => $targets){

			if(in_array($key, $group_ids)){

				foreach($targets as $cid){
					if(!in_array($cid, $all_group_rels)){
						//echo "alert('$cid');";
						$all_group_rels[]= $cid;
					}
				}
			}
		}
		return $all_group_rels;
	}


	function ajax_post_form(){

		$this->load_category_groups();
		$this->load_relationships();
		echo '<script type="text/javascript">';
/*
my_cars["cool"]="Mustang";
my_cars["family"]="Station Wagon";
my_cars["big"]="SUV";*/

		echo "var relationships= new Array(".sizeof($this->category_bins).");\n";
		//echo "var group_type = new Array(".sizeof($this->category_bins).");\n";
		for($i=0;$i<sizeof($this->category_bins);++$i){
			$targets = $this->get_group_relationship_targets($this->category_bins[$i]->get_cat_ids());
			echo "	relationships['".$this->category_bins[$i]->get_short_name()."']=new Array(".sizeof($targets).");\n";
			for($j=0;$j<sizeof($targets);++$j){
				echo "relationships['".$this->category_bins[$i]->get_short_name()."'][$j]= ".$targets[$j].";\n";
			}
			/*if($this->category_bins[$i]->multiple_cats){
				echo "group_type['".$this->category_bins[$i]->get_short_name()."']='checkbox';";
			}else{
				echo "group_type['".$this->category_bins[$i]->get_short_name()."']='radio';";
			}*/
		}

/*
		foreach($this->relationships as $key => $targets){
			echo "relationships['k$key'] = new Array([";
			for($i=0;$i<sizeof($targets)-1;++$i){
				echo $targets[$i] . ', ';
			}
			echo $targets[sizeof($targets)-1];
			echo "]);\n";
		}*/


		echo 'function '.$this->prefix() . "clearChecks(name){\n";
			//echo "alert(name);";
			echo "for(i=0;i<relationships[name].length;++i){\n";
				//echo "alert(relationships[index].length);";
			//	echo "alert(id);";

			//echo "	if(group_type[name]=='radio'){\n";
			//echo "  alert('radio!');";
			echo "		id = '" . $this->prefix() . "' + relationships[name][i];";
			//echo "		document.getElementById(id).focus();\n";
			echo "		document.getElementById(id).checked=false;\n";
			//echo "	}\n";
			//echo "	else{\n";
			//echo "		id = '" . $this->prefix() . "' + relationships[name][i];";
			//echo "		document.getElementById(id).focus();\n";
			//echo "		document.getElementById(id).checked=false;\n";
			//echo "	}\n";
			echo "}\n";
			/*foreach($this->relationships as $targets){
				foreach($targets as $target_id){
					echo 'document.getElementById(\'' . $this->prefix() . $target_id . '\').checked=false;';
				}
			}*/

		echo '}';


		echo '</script>';



		echo '<script type="text/javascript">';
		echo 'category_manager_js_get_panel("'.$post_id.'");';
		echo '</script>';

		//echo $this->post_form();
	}

	function postBox(){
		add_meta_box('wfiu_categories-box-div', __('WFIU Categories', 'wfiu_categories'), array(&$this, 'echo_post_form'), 'post', 'normal', 'high');
		add_meta_box('wfiu_categories-box-div', __('WFIU Categories', 'wfiu_categories'), array(&$this, 'echo_post_form'), 'page', 'normal', 'high');
	}

	function echo_post_form(){
		echo "<script type='text/javascript'>jQuery(document).ready(function(){jQuery('#categorydiv').remove();});</script>";
		echo $this->post_form();	
	}

	function post_form($post_id = null){
		global $post;
		$this->load_category_groups();
		$this->load_relationships();
		$outStr = '<input type="hidden" name="'.$this->prefix.'noncemane" id="'.$this->prefix.'noncemane" value="' .wp_create_nonce( plugin_basename(__FILE__) ) .'" />';
		//$outStr .= '<input type="hidden" name="'.$this->prefix().'edit_form" value="true" />';
		//$outStr .= '<h3>WFIU CATEGORIES</h3>
		//	<div class="inside">
		//		<div id="postWFIUCATCONTENT">';

		$i=0;
		$outStr .= "<table><tr>\n";

		if($post_id == null){
			$post_id = $post->ID;
		}

		$selected = wp_get_post_categories($post_id);
		$i= 0;
		foreach($this->category_bins as $catbin){
			if($catbin->parent_id != null){
				continue;
			}
			//echo $catbin->name;
			/*if(sizeof($this->category_bins)-1 == $i){
				echo "<div>\n";
			}else{
				echo "<div style='float:left'>\n";
			}*/
			$outStr .= "<td style='vertical-align:top' >\n";
			$required = '';
			if($catbin->required){
				$required = "<br />(required)";
			}

			$outStr .= "<div style='text-align:center;font-weight:bold;height:40px;'>".$catbin->name."$required</div>\n";

			if($catbin->multiple_cats){//print checkboxes
				$outStr .= $this->print_categories($catbin->get_cat_ids(), 'checkbox', null, $selected, true, $catbin->get_short_name());
			}else{
				$outStr .= $this->print_categories($catbin->get_cat_ids(), 'radio', $catbin->get_short_name(), $selected, true, $catbin->get_short_name());
			}

			if($catbin->children !=null){
				foreach($catbin->children as $childname){
					$child = $this->get_category_bin($childname);
					$outStr .= "<hr />";
					
					if($child->multiple_cats){//print checkboxes
						$outStr .= $this->print_categories($child->get_cat_ids(), 'checkbox', null, $selected, true, $child->get_short_name());
					}else{
						$outStr .= $this->print_categories($child->get_cat_ids(), 'radio', $child->get_short_name(), $selected,true, $child->get_short_name());
					}
				}
			}
			
			//echo "</div>\n";
			$outStr .= "</td>\n";
			++$i;
		}
		//exclude' => ''
		//**************extra cats********************
		/*
		$extra_cats = $this->get_extra_cats();
		$outStr .= "<td style='vertical-align:top' >\n";
		$outStr .= "<div style='text-align:center;font-weight:bold;'>Other</div>\n";
		$outStr .=  $this->print_categories($extra_cats,'checkbox', null, $selected);
		$outStr .= "</td>";
		*/
		//********************************************
		

		//echo "<div style='clear:both'>\n";
			$outStr .=  "</tr></table>\n";
		//$outStr .=  "		</div>
		//	</div>";
		//$outStr .=  "</div>";
		//echo $outStr;
		return $outStr;
	}
	function table_headers($titles){
		$arg_list = func_get_args();

		$retStr = "<table>\n<tr>\n";
		foreach($arg_list[0] as $title) {
			$retStr .= "<th style='border-bottom: solid 3px;'>$title</th>\n";
		}
		$retStr .= "</tr>\n";
		return $retStr;
	}

	function get_relationships($cat_id){
		if($this->relationships[$cat_id]){
			return $this->relationships[$cat_id];
		}else{
			return false;
		}
	}
	
	function prefix(){
		return "category_manager_";
	}



	function print_categories($cat_ids, $format='name', $radio_group=null, $selected = array(), $do_rels = true, $name = null){

		if(!is_array($cat_ids)){
			$cat_ids = array($cat_ids);
		}
		$outStr = '';
		$curID = -9999;


		/*print_r($cat_ids);

		echo "<br />";
		print_r($selected);
		echo "<br /> <hr />";*/
		$radio_selected = false;
		foreach($cat_ids as $id){
			//echo "|$id|";
			$categoryObj = get_category($id);
			$itemStyle = '';
			$itemJS = '';
			if($curID == $categoryObj->parent){
				$itemStyle = 'style="margin-left:20px;" ';
				$itemJS .='setChecked([\''.$this->prefix().$id.'\',\''.$this->prefix().$curID.'\']);';
			}else{
				$curID = $id;
			}


			if($format=="radio" && ($rels = $this->get_relationships($id)) && $do_rels==true){
				$itemJS = $this->prefix() . "clearChecks('$name');";
				//print_r($rels);
				foreach($rels as $target_id){
					$itemJS .='document.getElementById(\''.$this->prefix().$target_id.'\').checked=true;';
				}
			}

			if($format=='checkbox'){
				if($children = $this->get_children($id)){
					//print_r($children);
					$itemJS .= 'clearCheckBoxes([\''.$this->prefix().$id.'\'';
					foreach($children as $child_id){
						$itemJS .= ',\''. $this->prefix().$child_id.'\'';
					}
					$itemJS .= ']);';
					//echo $itemJS;	
					//echo '<br /> ';
				}
				if(($rels = $this->get_relationships($id)) && $do_rels==true){
					$itemJS .= 'clearCheckBoxes([\''.$this->prefix().$id.'\'';
					foreach($rels as $target_id){
						$itemJS .= ',\''. $this->prefix().$target_id.'\'';
					}
					$itemJS .= ']);';

					$itemJS .= 'setChecked([\''.$this->prefix().$id.'\'';
					foreach($rels as $target_id){
						$itemJS .= ',\''. $this->prefix().$target_id.'\'';
					}
					$itemJS .= ']);';
				}
			}

			if($itemJS){
				$itemJS = 'onChange="javascript:'.$itemJS.'" ';
			}

			//print_r($categoryObj);return;
			//print_r($selected);
			$checked = '';
			//echo $id;
			if(in_array($id, $selected)){
				$checked .= ' checked="yes"';
				$radio_selected = true;
			//	echo "bingo ||";
			}
			//echo ' <br />';

			if($format=='checkbox'){
				//echo $itemJS;
				$outStr .= '<input '.$itemStyle.'type="checkbox" '.$itemJS.'id="'.$this->prefix().$id.'"';
				$outStr .= $checked;
				$outStr .= ' name="'.$this->prefix().$categoryObj->term_id.'" /> ';
				$outStr .= $categoryObj->cat_name;
				$outStr .= ' <br />';
			}else if($format=="radio" && $radio_group!=null){
				$outStr .='<input '.$itemStyle.'type="radio" '.$itemJS.'id="'.$this->prefix().$id.'"';
				$outStr .= $checked;
				$outStr .= ' value="' . $id . '"';
				$outStr .= ' name="'.$this->prefix().$radio_group.'" /> '.$categoryObj->cat_name.' <br />';
			}else if($format=='single'){
				$outStr .= $categoryObj->cat_name;
			}else{
				$outStr .= '<span class="'.$this->prefix().'cat_name" style="border: 1px solid;" >'.$categoryObj->cat_name . '</span> ';
					//print_r($categoryObj);
			}
		}

		if($format=='radio'){
			 $outStr .='<input type="radio" name="'.$this->prefix().$radio_group.'" value="none" onChange="javascript:'.$this->prefix() . "clearChecks('$name');\"";
			if(!$radio_selected){
				$outStr .=' checked="yes"';
			}
			 $outStr .= ' /> None <br />';
		}

		//if($format=='name') $outStr = substr($outStr, 0, -2);

		
		return $outStr;

	}

	function activate(){
		add_option($this->prefix().'groups','');
		add_option($this->prefix().'relationships','');
	}

	function process_add_group(){

		$cat_id = $_POST['cat'];

		if(!$cat_id){
			echo "<div id='message' class='updated fade'><b>No post data received, try again.</div>";
			return;
		}
		$mult = $_POST[$this->prefix().'multiple'] ? true: false;
		$req = $_POST[$this->prefix().'required'] ? true : false;
		$parent_id = intval($_POST[$this->prefix()."parent_id"]);
		$categoryObj = get_category($cat_id);
		$message = "<div id='message' class='updated fade'><b>".$categoryObj->name."</b> category group added...</div>";

		$old_id = $_POST[$this->prefix()."group_id"];
		//$this->add_category_group($cat_id, $mult, $req);//Content Type


		$groups = stripslashes(get_option($this->prefix().'groups'));
		$groups = unserialize($groups);
		if(!$groups){
			$groups = array();
		}


		
		//$newItem = array('multiple' => $mult, 'required' => $req, 'parent_id' => $parent_id);
		$groups[$cat_id] =  array('multiple' => $mult, 'required' => $req, 'parent_id' => $parent_id);
		//print_r($groups);
		if($old_id){
			$message = "<div id='message' class='updated fade'><b>".$categoryObj->name."</b> category group updated...</div>";
			$old_id = intval($old_id);
			//echo $old_id;
			if($cat_id != $old_id){
				unset($groups[$old_id]);
			}

		}

		//echo $mult . ' ' . $req;
		//print_r($groups);
		$groups = serialize($groups);

		update_option($this->prefix().'groups', $groups);
		echo $message;

	}

	function config_form_edit_group(){
		echo "<div class='wrap'>";


		$cid = $_POST[$this->prefix()."group_id"];
		//echo $cid;
		if(!$cid){
			echo "<div id='message' class='updated fade'>No category group selected, try again.</div>\n";
			return;
		}

		$chosen_group = $this->get_category_bin($cid);
		echo "<h2>".$chosen_group->name."</h2><br />";
		echo "<form action='?page=" . $_GET['page'] . "&add_group=true' method='post'>\n";
		if($cid !='top'){
			//echo " not top<br />";
			//print_r($this->get_category_bin($cid));
			$this->print_item_form_fields($chosen_group);
		}
		echo "<input type='hidden' name='".$this->prefix()."group_id' value='".$cid."'/>";
		echo '<br /><input type="submit" name="submit" value="Update category group" />';
		echo "</form>\n";
		echo "</div>\n";
	}

	function config_form_delete_group(){
		$cid = $_POST[$this->prefix()."group_id"];
		if(!$cid){
			echo "<div id='message' class='updated fade'>No category group selected, try again.</div>\n";
			return;
		}
		$cid = intval($cid);
		$categoryObj = get_category($cid);
		$message = "<div id='message' class='updated fade'><b>".$categoryObj->name."</b> category group deleted...</div>";

		
		$groups = stripslashes(get_option($this->prefix().'groups'));
		$groups = unserialize($groups);
		
		if(is_array($groups[$cid])){
			unset($groups[$cid]);
		}else{
			$message = "<div id='message' class='updated fade'><b>".$categoryObj->name."</b> category group does not exist, nothing done.</div>";
		}

		$groups = serialize($groups);
		update_option($this->prefix().'groups', $groups);
		echo $message;
	}

	function load_category_groups(){
		if(sizeof($this->group_ids)>0){
			return;
		}
		$groups = stripslashes(get_option($this->prefix().'groups'));
		//echo "groups:" .$groups;
		$groups = unserialize($groups);
		//echo sizeof($groups);
		//print_r($groups);
		//echo "<h1>sldkfsdkjf</h1>";
		if(is_array($groups)){
			foreach($groups as $group_id => $group_props){
				$mult = $group_props['multiple'] ? true: false;
				$req = $group_props['required'] ? true : false;
				$parent_id = intval($group_props['parent_id']) > 0 ? intval($group_props['parent_id']) : null;
				$this->add_category_group($group_id, $mult, $req, $parent_id);
			}

		}

	}

	function print_item_table_row($catbin, $format='parent'){//($name, $mult, $req, $cat_ids, $format='parent'){
		echo "<tr >\n";
		//echo "<td>$i.</td>";
		$itemid = $catbin->id;
		if(!$itemid){
			$itemid = 'top';
		}
		echo "<td >";
		if($itemid == 'top'){
			echo "&nbsp;";
		}else{
			echo "<input type='radio' name='".$this->prefix()."group_id' value='" . $itemid . "' />";
		}
		echo "</td>";
		$style = '';
		if($format =='child'){
			$style = 'style="padding-left:20px;"';
		}
		
		echo "<td $style>" . $catbin->name . "</td>";
		echo "<td style='text-align: center'>";
			echo $catbin->multiple_cats ? 'Yes' : 'No';
		echo "</td>";
		echo "<td style='text-align: center'>";
			echo $catbin->required ? 'Yes' : 'No';
		echo "</td>";
		echo "<td style='text-align: center'>";
			echo $this->print_categories($catbin->get_cat_ids());
		echo "</td>";

		/*echo "<td style='width:50px'>";
		echo "<input type='button'  value='&uarr;' />";
		echo "<input type='button'  value='&darr;' />";
		echo "</td>";
*/

		echo "</tr>\n";

	}

	function main_config_form(){
/*
			 <li><a href="<?php echo get_bloginfo('url').'/wp-admin/admin.php?page=Category Manager Config';?>"><span>Main</span></a></li>
			 <li><a href="<?php echo get_bloginfo('url').'/wp-admin/admin.php?page=Relationships';?>"><span>Relationships</span></a></li>
			 <li><a href="<?php echo get_bloginfo('url').'/wp-admin/admin.php?page=Mass Change';?>"><span>Mass Change</span></a></li>
*/
?>
		<div id="catman_tabs">
		     <ul>
			 <li><a href="#catman_main"><span>Main</span></a></li>
			 <li><a href="#catman_relationships"><span>Relationships</span></a></li>
			 <li><a href="#catman_mass_change"><span>Mass Change</span></a></li>
		     </ul>
		</div>

		<div id='catman_main'>
			<?php $this->config_form();?>
		</div>
		<div id='catman_relationships'>
			<?php $this->config_form_relationships();?>
		</div>
		<div id='catman_mass_change'>
			<?php $this->config_form_masschange();?>
		</div>
		<script type='text/javascript'>
		 jQuery(document).ready(function(){
			jQuery('#catman_tabs').tabs({ remote: true });
		});
		</script>
<?php
		//$this->config_form();
	}


	function config_form(){

		if($_GET['add_group']){
			$this->process_add_group();

		}else if($_GET['editdelete_group']){
			if($_POST['group_edit']){
				$this->load_category_groups();
				$this->config_form_edit_group();
				return;
			}else if ($_POST['group_delete']){
				$this->config_form_delete_group();
			}
		}
		$this->load_category_groups();

		echo "<div class='wrap'>";

		echo "<h2>IPM Category Manager Configuration</h2>";

		echo $this->table_headers(array('&nbsp;', 'Name', 'Allow Multiple', 'Required', 'Categories'));
		echo "<form action='?page=" . $_GET['page'] . "&editdelete_group=true#catman_main' method='post'>";
			foreach($this->category_bins as $catbin){

				/*if($catbin->parent_id == null){
					echo $catbin->name. " is null <br />";
				}else{
					echo "Parent id for " . $catbin->name . " is " . $catbin->parent_id . "<br />";

				}
				print_r($catbin->children);
				echo "<br />";*/
				if($catbin->parent_id != null){
					//echo $catbin->parent_id . $catbin->name ."%<br />";
					continue;
				}

				$this->print_item_table_row($catbin);//->name, $catbin->multiple_cats, $catbin->required, $catbin->get_cat_ids());

				if($catbin->children !=null){
					//echo $catbin->parent_id . $catbin->name ."!<br />";
					foreach($catbin->children as $childname){

						$child = $this->get_category_bin($childname);
						//echo $child->parent_id . $child->name ."*<br />";
						//echo $child->parent_id . ' -> ' . $child->name . ' |' ;
						
						$this->print_item_table_row($child, 'child');//->name, $child->multiple_cats, $child->required, $child->get_cat_ids(), 'child');
					}
				}


			}
			
		echo "</table>";
		echo "<br /><input class='button' type='submit' name='group_edit' value='Edit selected group'/>";
		echo "&nbsp;&nbsp;&nbsp;<input class='button' type='submit' name='group_delete' value='Delete selected group'/>";
		echo "</form>\n";
		echo "</div>";
		//print_r($this->category_bins[1]);

		$this->print_add_group_form();	
		echo "<br /><br />";


	}
	
	function config_form_relationships(){
		$this->print_relationships();
	}


	function get_parent_ids(){
		$parent_ids = array();
		for($i=1; $i<sizeof($this->category_bins);$i++){
			if($this->category_bins[$i]->parent_id == null){
				$parent_ids[] = $this->category_bins[$i]->id;
			}
		}
		return $parent_ids;
	}

	//Print form of a single item to add
	function print_item_form_fields($catbin = null){
		//echo $catbin->name;

		echo "<b>Category Parent</b>: ";
		$catQ = "hide_empty=0&show_count=1&hierarchical=1";
		if($catbin != null){
			//echo ' id ' .$catbin->id;
			$catQ .= '&selected=' . $catbin->id;
		}
		$exclude = '';
		foreach($this->group_ids as $index =>$cid){
			if($cid != $catbin->id){
				$exclude .= $cid . ',';
				foreach($this->category_bins[$index+1]->get_cat_ids() as $cid2){
					$exclude .= $cid2 . ',';
				}
			}
		}
		if($this->hide_uncategorized){
			$exclude .= '1';
		}else if($exclude){
			$exclude = substr($exclude, 0, -1);
		}

		//echo $exclude . ' <br />' ;
		if($exclude){
			$catQ .= '&exclude='.$exclude;
		}
		wp_dropdown_categories($catQ);

		echo "<br /><b>Group settings</b>: ";

		$checked ='';
		if($catbin->multiple_cats){
			$checked = "checked='yes'";
		}
		echo "<input type='checkbox' $checked name='".$this->prefix()."multiple' /> Allow multiple?";

		$checked ='';
		if($catbin->required){
			$checked = "checked='yes'";
		}
		echo "<input type='checkbox' $checked name='".$this->prefix()."required' /> Required? <br />";

		echo "<b>".$catbin->parent_id."</b><br />";
		$parent_groups_ids = $this->get_parent_ids();

		if(sizeof($parent_groups_ids)>0){
			echo "<b>Parent group</b>: <select name='".$this->prefix()."parent_id' class='postform' >\n";
			echo "<option value='none'>None</option>\n";
			for($i=1; $i<sizeof($this->category_bins);$i++){
				if($this->category_bins[$i]->parent_id == null && ($this->category_bins[$i]->id != $catbin->id)){

					if($catbin->parent_id && $catbin->parent_id == $this->category_bins[$i]->id){
						echo "<option selected='selected' value='".$this->category_bins[$i]->id."'>".$this->category_bins[$i]->name."</option>\n";
					}else{
						echo "<option value='".$this->category_bins[$i]->id."'>".$this->category_bins[$i]->name."</option>\n";
					}
					
				}
			}
				
			echo "</select><br />\n";
		}



	}


	function print_add_group_form(){
		echo "<br />";
		echo "<div class='wrap'>";
		echo "<h2>Add category group</h2>";
		echo "<form action='?page=" . $_GET['page'] . "&add_group=true#catman_main' method='post'>";

		$this->print_item_form_fields();
		echo '<input type="submit" name="submit" value="Add category group" />';
		echo '</form>';
		echo "</div>";

	}

	function print_bin_cats($name){
		foreach($this->category_bins as $catbin){
			if($catbin->name == $name){
				echo $this->print_categories($catbin->get_cat_ids());
				return;
			}
		}
	}

	function print_relationships(){
		if($_GET['add_relationship']){
			$this->load_category_groups();
			$this->process_add_relationship();

		}else if($_GET['editdelete_relationship']){
			if ($_POST['relationship_delete']){
				$this->process_delete_relationship();
			}
		}else{
			$this->load_category_groups();
		}

		$this->load_relationships();
		
		echo "<div class='wrap'>";
		echo "<h2>Relationships</h2>";

		echo "<form action='?page=" . $_GET['page'] . "&editdelete_relationship=true#catman_relationships' method='post'>";
		echo $this->table_headers(array('&nbsp;', 'Category', 'Must Have'));
		foreach($this->relationships as $key=>$cats){
				echo "<tr >\n";
				echo "<td>";
				echo "<input type='radio' onclick='javascript:changeSelectedCategory(this.value);' name='".$this->prefix()."relationship_key' value='" . $key . "' />";
				echo "</td>";
				echo "<td style='text-align: center'>";
				echo $this->print_categories($key, 'single');
				echo "</td>";
				echo "<td style='text-align: center'>";
				echo $this->print_categories($cats);
				echo "</td>";
				echo "</tr>\n";
		}
		echo "</table>";

		//echo "<br /><input class='button' type='submit' name='relationship_edit' value='Edit selected relationship'/>";
		echo "&nbsp;&nbsp;&nbsp;<input class='button' type='submit' name='relationship_delete' value='Delete selected relationship'/>";
		echo "</form>\n";

		echo "</div>";

		$this->form_add_relationship();
		//echo $this->get_categories_panel(23);

	}

	function form_add_relationship(){
		echo "<div class='wrap' style='margin-top:40px;'>";
		echo "<h2>Add/Update relationship group</h2>";
		echo "<form action='?page=" . $_GET['page'] . "&add_relationship=true#catman_relationships' method='post'>";
		$this->print_relationship_fields();


		echo '</form>';
		echo "</div>";

	}
	function load_relationships(){
		if(sizeof($this->relationships)>0){
			return;
		}
		$rels =stripslashes(get_option($this->prefix().'relationships'));
		$rels = unserialize($rels);
		if(is_array($rels)){
			foreach($rels as $key => $targets){
				$this->add_relationship(intval($key), $targets);
			}
		}

	}

	function process_delete_relationship(){
		$key_id = $_POST[$this->prefix()."relationship_key"];
		if(!$key_id){
			echo "<div id='message' class='updated fade'>No key category selected, try again.</div>\n";
			return;
		}

		$key_id = intval($key_id);
		$cObj = get_category($key_id);
		$message = "<div id='message' class='updated fade'>Relationship with <b>".$cObj->name."</b> deleted.</div>\n";

		
		$rels = stripslashes(get_option($this->prefix().'relationships'));
		$rels = unserialize($rels);
		
		if(is_array($rels[$key_id])){
			unset($rels[$key_id]);
		}else{
			$message = "<div id='message' class='updated fade'>Relationship with <b>".$cObj->name."</b> does not exist, nothing done.</div>";
		}

		$out = serialize($rels);
		update_option($this->prefix().'relationships', $out);
		echo $message;

	}

	function process_add_relationship(){
		$rel_id = $_POST[$this->prefix()."relationship_key"];
		if(!$rel_id){
			echo "<div id='message' class='updated fade'>No key category selected, try again.</div>\n";
			return;
		}
		$cObj = get_category($rel_id);
		$message = "<div id='message' class='updated fade'>Relationship with <b>".$cObj->name."</b> added.</div>\n";

		//$targets = $_POST[$this->prefix()."relationship_targets"];
		$targets = $this->get_post_variables(false);//don't force parent id

		//print_r($targets);
		$rels = stripslashes(get_option($this->prefix().'relationships'));
		$rels = unserialize($rels);
		if(!$rels){
			$rels = array();
		}

		$rels[$rel_id] = $targets;
	
		//print_r($rels);
		$out = serialize($rels);

		update_option($this->prefix().'relationships', $out);

		echo $message;

	}


	
	function get_categories_panel($key_id){
		/*if(!$key_id){
			return "Error from get_categories_panel: No category id passed.<br />";
		}*/
		$this->load_category_groups();
		$this->load_relationships();
		$retStr = '';
		$targets = $this->relationships[$key_id];
		if(!is_array($targets)){
			$targets = array();
		}
		foreach($this->category_bins as $catbin){
			if($catbin->parent_id != null){
				continue;
			}


			if($catbin->multiple_cats){//print checkboxes
				$retStr .= "<div style='border:1px dashed; float:left;margin-left:10px;'><b>".$catbin->name."</b><br />";
				$retStr .= $this->print_categories($catbin->get_cat_ids(), 'checkbox', null, $targets, $do_rels = false);
				//echo $this->print_categories($catbin->get_cat_ids(), 'checkbox', null, $selected);
			}else{
				if(!in_array(intval($key_id), $catbin->get_cat_ids())){
					$retStr .= "<div style='border:1px dashed; float:left;margin-left:10px;'><b>".$catbin->name."</b><br />";
					$retStr .= $this->print_categories($this->sort_cids_alphabet($catbin->get_cat_ids()), 'radio', $catbin->get_short_name(), $targets, $do_rels = false);
				}
				//echo $this->print_categories($catbin->get_cat_ids(), 'radio', $catbin->get_short_name(), $selected);
			}


			if($catbin->children !=null){
				foreach($catbin->children as $childname){
					$child = $this->get_category_bin($childname);

					if($child->multiple_cats){//print checkboxes
						$retStr .= "<div style='border:1px dashed; margin-left:5px;'><b>".$child->name."</b><br />";
						$retStr .= $this->print_categories($child->get_cat_ids(), 'checkbox', null, $targets, $do_rels = false);
						$retStr .= "</div>";
						//echo $this->print_categories($catbin->get_cat_ids(), 'checkbox', null, $selected);
					}else{
						if(!in_array(intval($key_id), $child->get_cat_ids())){
							$retStr .= "<div style='border:1px dashed; margin-left:5px;'><b>".$child->name."</b><br />";
							$retStr .= $this->print_categories($this->sort_cids_alphabet($child->get_cat_ids()), 'radio', $child->get_short_name(), $targets, $do_rels = false);
							//echo $this->print_categories($catbin->get_cat_ids(), 'radio', $catbin->get_short_name(), $selected);
							$retStr .= "</div>";
						}
					}


				}

			}

			$retStr .= "</div>";

		}

		return $retStr;

	}

	function print_relationship_fields($key_id = null){//only one relationship

		echo "<br <b>Key</b>: <select name='".$this->prefix()."relationship_key' id='".$this->prefix()."relationship_key' class='postform' onChange='category_manager_js_get_panel_generic();' >\n";

		//echo "<option value='none'>None</option>\n";

		//$existing_keys = array_keys($this->relationships);
		/*$all_cats= array();
		foreach($this->category_bins as $catbin){
			$all_cats = array_merge($all_cats, $catbin->get_cat_ids());
		}*/


		$catQ = "fields=ids&hierarchical=1&hide_empty=0";
		$exclude .= '1';
		$catQ .= '&exclude='.$exclude;
		$all_cats = get_categories($catQ);


		echo "<option value=''> </option>\n";
		foreach($this->sort_cids_alphabet($all_cats) as $cid){
			if(in_array($cid, $this->group_ids))continue;
			//if($this->category_bins[$i]->parent_id == null && ($this->category_bins[$i]->id != $catbin->id)){

			//if($catbin->parent_id && $catbin->parent_id == $this->category_bins[$i]->id){	
			$cobj = get_category($cid);
			if($cid == $key_id){
				echo "<option selected='selected' value='".$cid."'>".$cobj->name."</option>\n";
			}else{
				echo "<option value='".$cid."'>".$cobj->name."</option>\n";
			}
		
		}

		$catsPerLine = 4;
		$count = 0;
		echo "</select>\n";

		echo '<input style="margin-left:5px;height:30px;" type="submit" class="button" name="submit" value="Add/Update relationship" />';

		echo "<br /><b>Map to</b>: \n";
		//echo "<option value='none'>None</option>\n";
		$header = array_fill(0,$catsPerLine, '&nbsp;');
		//echo $this->table_headers($header);
		//echo "<tr>";

		echo "<div id='".$this->prefix()."mapto'>";
		//echo $this->get_categories_panel();

		echo "</div>";
		echo '<script type="text/javascript">';
		echo 'category_manager_js_get_panel_generic();';
		echo '</script>';
		echo "<div style='clear:both'></div><br />";
		/*if(($diff = $count % $catsPerLine) != 0){
			echo $diff;
		}*/	
		//echo "</tr></table><br />\n";
		


	}



}


 ?>
