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

class sitewide_categories {
	public $tags_blog_id;
	public $verbose;
	public $blogs = array();
	public $allcats = array();
	public function __construct($tags_blog_id){
		$this->tags_blog_id = get_site_option( 'tags_blog_id' );
		$this->verbose = true;
	}

	function categories_table(){

	}

	function init_variables(){
		global $wpdb;
		$blog_ids = $wpdb->get_col("SELECT blog_id from wp_blogs order by path;");
		
		$this->allcats = array();
		$this->blogs = array();
		foreach($blog_ids as $id){
			switch_to_blog( $id );
			$bd = get_blog_details( $id );
			//echo "<h2>Current blog: $bd->blogname</h2>";
			//echo get_site_option('site_url');
			$this->blogs[$id]['name'] = $bd->blogname;
			$this->blogs[$id]['categories'] = array();
			$catQ = "fields=ids&hierarchical=1&hide_empty=0";
			$categories = get_categories($catQ);
			foreach ($categories as $cat_id){
				$cat = get_category($cat_id);
				$catmysql = $wpdb->get_row("SELECT * FROM $wpdb->terms WHERE term_id = " . $cat_id . ";");


				/*if(($id == 11) && ($cat_id == 24694)){
					print_r($cat);
					 echo "++++++++++++++++++++++<br /><br />";
				}*/
				$this->blogs[$id]['categories'][$cat_id] = array($cat->slug, $cat->count, $cat->category_parent);
				$exists = false;
				foreach($this->allcats as $catItem){
					if( ($catItem['category_nicename'] == $catmysql->slug) && ($catItem['cat_ID'] == $cat->cat_ID)){
						$exists = true;
						break;
					}
				}
				if(!$exists){
					$this->allcats[]  = array();
					$this->allcats[sizeof($this->allcats)-1]['name'] = ucfirst($cat->name);
					$this->allcats[sizeof($this->allcats)-1]['slug'] = $cat->slug;
					$this->allcats[sizeof($this->allcats)-1]['category_nicename'] = $catmysql->slug ? $catmysql->slug : $cat->category_nicename;
					$this->allcats[sizeof($this->allcats)-1]['cat_ID'] = $cat_id;
					$this->allcats[sizeof($this->allcats)-1]['category_parent'] = $cat->category_parent;
					$this->allcats[sizeof($this->allcats)-1]['description'] = $cat->description;
					$this->allcats[sizeof($this->allcats)-1]['blog_id'] = $id;
				}
				//if(!$this->allcats[$cat->cat_ID]['description']){
				//	$this->allcats[$cat->cat_ID]['description'] = $cat->description;
				//}

			}
			//echo "<hr />";
		}
		//print_r($this->blogs);
		$this->allcats = order_object_array($this->allcats, 'name');
		//print_r($this->allcats);
		switch_to_blog( $this->tags_blog_id );
		//die('done');
	}

	function category_lookup(){
		global $wpdb;

		$str = "<h2>Category Lookup</h2>";
		$str .= "<form name='catlookup' id='catlookup' method='post' action='?page=" . $_GET['page'] ."#catman_lookup' >";
		$str .= "<p>Enter id to look up accross all blogs: </p>";


		$str .= "<input type='text' name='cat_ID'/>\n";

		$str .= "<br /><input type='submit' value='Look up'/>";
		//$str .= "</form>";
		//$str .= $_POST['catindex'];
		if(!isset($_POST['cat_ID'])){
			$str .= "</form>\n";
			echo $str;
			return;
		}
		//if(isset($_POST['catindex']))$str .= " <input type='hidden' name='existincatindex' value='".$_POST['catindex']."' />";

		
		$str .= "<p>Number of posts for each category for each blog. We specify whether the category was matched by the id or by the slug.</p>";

		$str .= " <input type='hidden' name='action' value='lookupcats' />\n";
		$str .= "<table>";
		$str .= "<tr>";
		$str .= "<th>&nbsp;</th>";
		foreach($this->blogs as $blog){
			$str .= "<th style='border: 1px solid black;padding: 2px;' >" . $blog['name'] . "</th>";
		}
		$str .= "</tr>";
		$style = "style='border-bottom: 1px solid black;padding-bottom: 2px;border-collapse:collapse;'";
		$count = 1;
		

		$cat_id = $_POST['cat_ID'];
		$str .= "<tr><td $style >\n ";
		$str .= "<span style='background-color:#EBFFD7;' >" .  $cat_id . "</span>\n";
		$str .= "</td>";
		foreach($this->blogs as $bid => $blog){
			switch_to_blog( $bid );
			$found = 'no';
			$matchingID = '';
			foreach($blog['categories'] as $id=>$cat_props){
				if($id == $cat_id){
					$found = 'id';
					$matchingID = $id;
					break;
				}
			}

			$str .= "<td $style >\n";
			switch($found){
				case 'id':
					$str .= "<div>";

					$thiscat = get_category($matchingID);
					$str .= $thiscat->name;
					if($thiscat->category_parent){
						$parcat = get_category($thiscat->category_parent);
						$str .= " | Parent: $parcat->name ";
					}
					$str .= '</div>';
					$str .= "<span style='color: green' >" . $blog['categories'][$cat_id][1] . ' (ID) ';
					break;
				case 'no':
					$query = "SELECT * FROM $wpdb->terms WHERE term_id = $cat_id;";
					$results = $wpdb->get_row($query);
					if( (sizeof($results)>0) && ($results!==false) ){
						$str .= "<span> Term " . $results->name . "</span>";
					}else{
						$str .= '<span style="color:red">x</span> ';
					}
					break;

										
			}
			$str .= "</td>";
		}
		$str .= "</tr>";


		$str .= "</table>";
		//$str .= "<input type='submit' name='submit_assign' value='Assign Categories' />";
		$str .= "</form>";
		echo $str;
		switch_to_blog( $this->tags_blog_id );

	}

	function categories_summary(){
		//echo "<form name='catselectform' id='catselectform' method='post' action='?page=" . $_GET['page'] ."' > <input type='hidden' name='action' value='selectcat' />";
		echo "<h2>Sitewide category summary</h2>";
		echo "<form name='catsummary' id='catsummary' method='post' action='?page=" . $_GET['page'] ."#catman_view' >";
		echo "<p>Select categories to inspect: </p>";
		if(isset($_POST['catindex']))$catindices = array_keys($_POST['catindex']);		
		
		//echo "<select name='catindex'>";
		foreach($this->allcats as $index=>$catProps){
			//$selected = (($index == $_POST['catindex']) && isset($_POST['catindex'])) ? 'SELECTED="selected"' : '';
			//echo "<option value='$index' $selected >".$cat['name']."(ID: ".$cat['cat_ID'].")</option>	";
			if(isset($_POST['catindex']))$selected = in_array($index, $catindices) ? "CHECKED='CHECKED'": '';
			echo "<input $selected type='checkbox' name='catindex[$index]' id='catindex[$index]' /> "; 
			echo "<label for='catindex[$index]' style='padding-right: 8px;' ><strong>".$catProps['name'] . "</strong> (" . $catProps['cat_ID'].")</label>";
		}
		//echo "\n</select>\n<br />";
		echo "<br /><input type='submit' value='Show'/>";
		
		//echo $_POST['catindex'];
		if(!isset($_POST['catindex'])){
			echo "</form>";
			return;
		}
		//if(isset($_POST['catindex']))echo " <input type='hidden' name='existincatindex' value='".$_POST['catindex']."' />";

		
		echo "<p>Number of posts for each category for each blog. We specify whether the category was matched by the id or by the slug.</p>";

		echo " <input type='hidden' name='action' value='modifycats' />
		<input type='hidden' name='deleteIndex' id='deleteIndex' value='' /> \n
		<input type='hidden' name='blogCat' id='blogCat' value='' /> \n";
		echo "<table>";
		echo "<tr>";
		echo "<th>&nbsp;</th>";
		foreach($this->blogs as $blog){
			echo "<th style='border: 1px solid black;padding: 2px;' colspan='2'>" . $blog['name'] . "</th>";
		}
		echo "</tr>";
		$style = "style='border-bottom: 1px solid black;padding-bottom: 2px;border-collapse:collapse;'";
		$count = 1;
		
		foreach($this->allcats as $index=>$catProps){
			if(!in_array($index, $catindices))continue;
			$cat_id = $catProps['cat_ID'];
			echo "<tr><td $style > $count. ";
			echo "<input type='hidden' name='merge_indeces[".$index."]' value='true' />";
			echo "<span class='button' onclick=\"if(confirm('This will delete the category ".$catProps['name']." (ID: ".$cat_id.") from all blogs, as well as from the wp_sitecategories table. OK?')){jQuery('#deleteIndex').val($index);jQuery('#catsummary').submit();}\" >Delete</span>";

			echo "<span style='background-color:#EBFFD7;' >" . $catProps['name'] ."/".$catProps['category_nicename']. "</span> | " . $cat_id;

			echo "<div style='color: #8E8E8E;font-style:italic;width: 200px;' >".$catProps['description']."</div>";
			echo "</td>";
			foreach($this->blogs as $bid => $blog){
				switch_to_blog( $bid );
				$found = 'no';
				$matchingID = '';
				foreach($blog['categories'] as $id=>$cat_props){
					if(($id == $cat_id) && ($cat_props[0] == $catProps['slug'])){
						$found = 'id';
						$matchingID = $id;
						break;
					}
				}
				//Find by slug
				if($found=='no'){
					foreach($blog['categories'] as $id=>$cat_props){
						if($cat_props[0] == $catProps['slug']){
							$found = 'none';//$cat_props[1];//0=> slug, 1=>count, 2=>category parent
							$matchingID = $id;
							break;
						}
					}
				}

				echo "<td $style >\n";
				switch($found){
					case 'id':
						echo "<span style='color: green' >" . $blog['categories'][$cat_id][1] . ' (ID) ';
						$thiscat = get_category($matchingID);
						if($thiscat->category_parent){
							$parcat = get_category($thiscat->category_parent);
							echo " | Parent: $parcat->name";
						}
						echo '</span>';
						break;
					case 'no':
						echo '<span style="color:red">x</span> <input type="checkbox" id="cats['.$bid.']['.$index.']" name="cats['.$bid.']['.$index.']" />
							<label for="cats['.$bid.']['.$index.']" >Add</label> | <label for="parents['.$bid.']['.$index.']"  onclick="jQuery(this).next().show();jQuery(this).hide();">Parent</label> ';
						echo '<select style="display:none" name="parents['.$bid.']['.$index.']" id="parents['.$bid.']['.$index.']" ><option value="-1">None</option>';
						foreach($this->allcats as $i=>$cprops){
							echo "<option value='$i'>";
							echo $cprops['name'] . " (" . $i . ")";
							echo "</option>";
						}
						echo '</select>';
						break;
					default:
						echo "<span style='color: green' >" . $found . ' (slug)</span>';
											
				}
				echo "</td><td $style> ";
				if($found != 'no'){
					echo "<span class='button' onclick=\"if(confirm('This will copy the category ".$catProps['name']." (ID: ".$cat_id."), as well as its respective parents, if any, to all the blogs. OK?')){jQuery('#blogCat').val('${bid}_${cat_id}');jQuery('#catsummary').submit();}\" >Copy</span>";
				}
				echo "</td>";
			}
			echo "</tr>";
			++$count;
		}

		echo "</table>";
		echo "<table><tr>";
		echo "<td  style='vertical-align:top'><input type='submit' name='submit_assign' value='Assign Selected' /></td>";
		echo "<td style='padding-left:30px;vertical-align:top;'>Merge categories shown above as: </td>";
		echo "<td><select name='merge_index' onchange=\"if(this.value > -1){jQuery(this).nextAll().show();}else{jQuery(this).nextAll().hide();}\">";
		echo "<option value='-1'></option>";
		foreach($this->allcats as $index=>$catProps){
			if(!in_array($index, $catindices))continue;
				echo "<option value='$index'>";
				echo $catProps['name'] . " (ID: " . $catProps['cat_ID'] . ")";
				echo "</option>";
		}
		echo "</select><div style='display:none;margin-top: 10px'>Enter description for category (optional):</div>";
		echo "<textarea name='merge_description' style='display:none;width:100%'></textarea>";
		echo "</td>\n<td style='vertical-align:top'>";
		echo "<input type='submit' name='submit_merge' value='Merge' /></td></tr></table>";
		echo "</form>";
		switch_to_blog( $this->tags_blog_id );

	}

	function validate_transaction(){
		if ( !wp_verify_nonce($_POST['add-category-nonce'], 'add-category')) {
			return $post_id;
		}

		if ( !current_user_can('manage_categories') )
			wp_die(__('Cheatin&#8217; uh?'));

	}
	
	function merge_categories($merge_index, $indeces,  $merge_desc){
		global $wpdb;
		if($merge_index < 0 ){
			echo "<div style='background-color:#FF9564 '>No merge index given. Nothing done.</div>";
			return;
		}
		$merge_cat = $this->allcats[$merge_index];
		$merge_id = $merge_cat['cat_ID'];
		//print_r($merge_cat);
		$blog_ids = $wpdb->get_col("SELECT blog_id from wp_blogs order by path;");
		echo "<div class='message'>Category <i>" . $merge_cat['name'] . '</i> (ID: '.$merge_cat['cat_ID'].')<br /> ';
		foreach($blog_ids as $bid){
			switch_to_blog( $bid );
			$bd = get_blog_details( $bid );
			echo "<h4>" . $bd->blogname . "</h4>";
			$exists = get_category($merge_cat['cat_ID']);
			//print_r($exists);
			if($exists->term_id && preg_match("/". preg_replace("/\-.*/",'',$merge_cat['category_nicename']) ."/i",$exists->category_nicename)){
				//echo "Category already exists, skipping...<br />";

				foreach($indeces as $i){
					if($i == $merge_index)continue;
					//echo "Index $i with cat_ID = " .$this->allcats[$i]['cat_ID'] . "<br />";
					echo "<div style='margin-left:10px;border-left:1px solid black' >";
					//$this->global_move_cat($this->allcats[$i]['cat_ID'], $merge_cat['cat_ID'], $merge_cat['category_nicename']);
					//$this->set_global_to_all($merge_cat['cat_ID'], $this->allcats[$i]['cat_ID'], $merge_cat['category_nicename']);
					
					//we already know that the merge category exists
					$cid = $this->allcats[$i]['cat_ID'];
					//now test to see if category to be indexed matches in name and id
					$query = "SELECT * from $wpdb->terms WHERE term_id=$cid;";
					$curcat = $wpdb->get_row($query);
					if($curcat->term_id && ($curcat->slug == $this->allcats[$i]['category_nicename'])){
						//ready to merge
						//delete category to be indexed from terms table
						$query = "DELETE FROM $wpdb->terms WHERE term_id = $cid;";
						echo "Deleteing category to be merged <br />";
						if($wpdb->query($query) !==false){
							$wpdb->update( $wpdb->term_taxonomy, array( 'parent' => $merge_id ), array( 'parent' => $cid ) );

							//check to see if category to be merged is a category and/or a tag
							$query = "SELECT term_taxonomy_id, taxonomy, count from $wpdb->term_taxonomy WHERE term_id = $cid;";
							$results = $wpdb->get_results($query);
							if(sizeof($results)>0){
								foreach($results as $row){
									$query = "SELECT count, term_taxonomy_id FROM $wpdb->term_taxonomy WHERE term_id = $merge_id AND taxonomy='$row->taxonomy';";
									//echo $query;
									$row2 = $wpdb->get_row($query);
									//print_r($row2);
									//continue;
									if(sizeof($row2)==0){
										echo "Inserting merge $row->taxonomy into taxonomy table<br />";
										$query = "INSERT INTO $wpdb->term_taxonomy VALUES($merge_id, '$row->taxonomy', '', 0, $row->count);";
										if(false === $wpdb->query($query) ){
											echo "Error $query <br />";echo "</div></div>";return;
										}
										$taxID = $wpdb->get_var('SELECT LAST_INSERT_ID();');//new merge taxonomy id
										
									}else if(sizeof($row2)>0){
										$taxID = $row2->term_taxonomy_id;
										//print_r($row2);
										//continue;
										$query = "UPDATE $wpdb->term_taxonomy SET count = (count+$row->count) WHERE term_id=$merge_id AND taxonomy='$row->taxonomy';";
										echo "Updating count value for merge $row->taxonomy <br />";
										//if(false === $wpdb->query($query) ){
										//	echo "Error $query <br />";return;
										//}

									}else{
										echo "Error $query <br />";return;
									}

									echo "Deleting old $row->taxonomy in taxonomy table (will be merged to new category/tag).<br />";
									$query = "DELETE from $wpdb->term_taxonomy WHERE term_taxonomy_id = $row->term_taxonomy_id;";
									if(false === $wpdb->query($query) ){
										echo "Error $query <br />";echo "</div></div>";return;
									}

									$query = "SELECT object_id, term_taxonomy_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = $taxID 
										AND object_Id IN ( SELECT object_id FROM $wpdb->term_relationships WHERE term_taxonomy_id = $row->term_taxonomy_id);";
									//echo $query . "<br />";
									$rowstodelete = $wpdb->get_results($query);
									if(sizeof($rowstodelete)>0){
										foreach($rowstodelete as $rowdel){
											$query = "DELETE FROM $wpdb->term_relationships WHERE object_id = $rowdel->object_id AND term_taxonomy_id = $rowdel->term_taxonomy_id";
											//echo $query;
											if($wpdb->query($query) ===false){
												echo "Error $query </div></div>";	
												return;
											}
										}
									}									
									
									$query = "UPDATE $wpdb->term_relationships SET term_taxonomy_id=$taxID WHERE term_taxonomy_id = $row->term_taxonomy_id;";
									echo "Updating all posts to new $row->taxonomy id <br />";
									if($wpdb->query($query) !==false){
										echo ucfirst($row->taxonomy) . " <i>$curcat->name </i> succesfully merged to <i>" . $merge_cat['name']. "</i><br />";
									}else{
										echo "Error: $query <br />";
									}
									//break;

								}
							


							}


						}else{
							echo "Error: $query <br />";
						}

					}else{
						echo "Category to be merged, <i>".$this->allcats[$i]['name']."</i>, does not exist ";
						echo $curcat->term_id ? "(<i>".$curcat->name."</i> does not match in name)": '';
						//echo $curcat->term_id ? "$curcat->slug == " . $this->allcats[$i]['category_nicename']. "<br />" : '';
						//echo $curcat->term_id ? "$query<br />" : '';
						echo ".<hr />";
					}
					
					echo "</div>";
				}

			}else if($exists->term_id){
				echo "Conflicting merge category ".$exists->name."($exists->category_nicename not same as ".$merge_cat['category_nicename'].") exists, skipping blog...<br />";
			}else{
				echo "Merge category does not exist, skipping blog...\n<br />";
			}
		}
		echo "-Done</div>";
		switch_to_blog( $this->tags_blog_id );
	}

	function add_sitecats($cat_ids) {
		global $wpdb;
		$lastupdated = date("Y-m-d H:i:s");
		
		foreach($cat_ids as $cid=>$on){
			$query = "INSERT INTO wp_sitecategories VALUES (".$cid.",\"".addslashes($this->allcats[$cid]['name'])."\",\"".$this->allcats[$cid]['category_nicename']."\",\"".$lastupdated."\" );";
			//echo $query;
			//continue;
			$result = $wpdb->query($query);
			if($result === false){
				echo "Category id $cid already exists, so updating. <br />";
				
				$query = "UPDATE wp_sitecategories set cat_name=\"".$this->allcats[$cid]['name']."\",
					category_nicename = \"".$this->allcats[$cid]['category_nicename']."\",
					last_updated = \"".$lastupdated."\"  WHERE cat_ID=$cid;";
				$result = $wpdb->query($query);

				if($result === false){
					echo "Error updating ".$this->allcats[$cid]['name'] . " ($cid). <br />";
				}
				
			}
		}
	}


	function assign_categories($post_vars){
		global $wpdb;
		$cats = $post_vars['cats'];
		if(sizeof($cats)==0){echo "No categories selected, nothing done.<br />"; return;}
		$parents = $post_vars['parents'];
		$this->init_variables();
		
		//echo "cats <br />";
		//print_r($cats);
		//echo "<br />parents <br />";
		//print_r($parents);
		//echo "<br /> <br />";
		
		//print_r($this->allcats);
		
		foreach($cats as $bid => $indexholder){
			$index = key($indexholder);
			switch_to_blog( $bid );
			$bd = get_blog_details( $bid );
			echo "<ul>Blog $bd->blogname <li>";
			
			// test for parent, if so, then test to see if it exists, otherwise add it, assume it's same as global
			if($parents[$bid][$index] > -1){
				$parent = $this->allcats[$parents[$bid][$index]];
				$parent = get_category($parent['cat_ID']);
				if(!$parent->term_id){
					
					echo "Adding parent since it doesn't exist.<br />";	
					//$parent_cat['cat_ID'] = $this->allcats[$parents[$bid][$index]]['cat_ID'];
					$parent_cat['cat_name'] = $this->allcats[$parents[$bid][$index]]['name'];
					$parent_cat['category_description'] = $this->allcats[$parents[$bid][$index]]['description'];
					$parent_cat['category_nicename'] = $this->allcats[$parents[$bid][$index]]['category_nicename'];
					
					//continue;
					if( wp_insert_category($parent_cat) ) {
						
						echo "Parent added successfully.<br />";
						print_r($parent_cat);
						echo "<br />";
					} else {
						echo "error(blog id = $bid)";
					}
				}else{
					//make sure it's the correct parent
					if( $parent->term_id && ($this->allcats[$parents[$bid][$index]]['category_nicename'] != $parent->category_nicename) ){
						echo "Parent category nicename does not match, aborting <br /></li></ul>\n";
							return;
					}
				}
			}
			
			
			//category does not exist, so just add it with the appropriate parent id
			//$mycat['cat_ID'] = $this->allcats[$index]['cat_ID'];
			$mycat['cat_name'] = $this->allcats[$index]['name'];
			if($parents[$bid][$index] > -1) $mycat['category_parent'] = $this->allcats[$parents[$bid][$index]]['cat_ID'];
			$mycat['category_description'] = $this->allcats[$index]['description'];
			$mycat['category_nicename'] = $this->allcats[$index]['category_nicename'];
			
				//print_r($mycat);
				//continue;
			if( wp_insert_category($mycat) ) {
				echo "Category added succesfully<br />";print_r($mycat);
			} else {
				echo "Error(blog id = $bid)<br />";
			}
			echo "</li></ul>";

		}
		switch_to_blog( $this->tags_blog_id );

	}

	function copy_to_all_blogs($blog_id, $cat_id){
		global $wpdb;
		$blog_ids = $wpdb->get_col("SELECT blog_id from wp_blogs order by path;");
		switch_to_blog( $blog_id );

		$temp = get_category($cat_id);
		$insertcats = array();
		$insertcats[] = array('cat_ID' => $cat_id, 'cat_name' => $temp->name, 'category_description' => $temp->description, 'category_nicename' => $temp->category_nicename,'category_parent' => $temp->category_parent);
		$cur_parent = $temp->category_parent;
		while($cur_parent){
			$curcat = get_category($cur_parent);
			$insertcats[] = array('cat_ID' => $curcat->term_id, 'cat_name' => $curcat->name, 'category_description' => $curcat->description, 'category_nicename' => $curcat->category_nicename, 'category_parent' => $curcat->category_parent);
			$cur_parent = $curcat->category_parent;
		}
		
		//print_r($insertcats);
		//return;
		echo "<div class='message'>Copying category <i>" . $temp->name . '</i> ';
		
		foreach($blog_ids as $bid){
			if($bid == $blog_id) continue;
			echo "<ul>Blog $bid";
			switch_to_blog( $bid );
			foreach($insertcats as $mycat){
				echo "<li>";
				$temp = get_category($mycat['cat_ID']);
				if($temp->term_id && ($temp->category_nicename == $mycat['category_nicename'])){
					echo "Category <i>".$mycat['cat_name']."</i> already exists.<br />\n";
					continue;
				}				
				if(!$temp->term_id){
					unset($mycat['cat_ID']);
					echo "blog does not have id: " .$mycat['cat_ID'] . "<br />";
				}
				
				if( wp_insert_category($mycat) ) {
					echo "Inserted: ";
					print_r($mycat);

				} else {
					echo "error(blog id = $bid)";
				}
				echo "</li>";
			}
			echo "</ul>";
		}
		echo "</div>";
		switch_to_blog( $this->tags_blog_id );
	}
	
	function get_next_id($get_global = false){
		global $wpdb;
		$row = $wpdb->get_row("SHOW TABLE STATUS LIKE '$wpdb->terms';");
		$id_terms = $row->Auto_increment;
		
		$row = $wpdb->get_row("SHOW TABLE STATUS LIKE 'wp_sitecategories';");
		$id_site = $row->Auto_increment;
		if( ($id_terms > $id_site) && !$get_global){
			return $id_terms;	
		}else{
			return $id_site;
		}
	}

	function delete_category($catIndex){
		global $wpdb;
		$cat = $this->allcats[$catIndex];
		$cat_ID = $cat['cat_ID'];
		echo "<div style='background-color: #FFE88C' >Deleting category " . $cat['name'] . " (ID: " . $cat_ID . ") <br />";
		
		foreach($this->blogs as $bid => $blog){
			switch_to_blog( $bid );
			$bd = get_blog_details( $bid );
			echo "Blog <b>$bd->blogname</b><br />";

			$curcat = get_category($cat_ID);			
			if($curcat->term_id){
					
				if($curcat->category_nicename == $cat['category_nicename']){
					wp_delete_category($cat_ID);
					echo "Deleted.<br />";
				}
			}
			
		}
		$wpdb->query("DELETE FROM wp_sitecategories WHERE cat_ID=$cat_ID;");
		echo "Done</div>";
		switch_to_blog( $this->tags_blog_id );
	}
	
	function set_global_to_all($cid, $local_id, $nicename){
		global $wpdb;
		if($this->verbose)echo "<h3>Updating local category $local_id with global category $cid ($nicename) </h3>";
		$row0 = $wpdb->get_row("SELECT * from wp_sitecategories WHERE cat_ID=$cid;");
		
		$blog_ids = $wpdb->get_col("SELECT blog_id from wp_blogs order by path;");
		foreach($blog_ids as $bid){
			switch_to_blog( $bid );
			$bd = get_blog_details( $bid );

			//First, test to see if the target category we are moving to exists
			$query = "SELECT * FROM $wpdb->terms WHERE (name like \"$nicename\") OR (slug like \"$nicename\");";
			$row = $wpdb->get_row($query);
			if(sizeof($row)==0){
				continue;
			}

			$query = "SELECT * from $wpdb->terms WHERE term_id=$cid;";
			$curcat = $wpdb->get_row($query);
			//echo "<br />blogname: " . $bd->blogname . "<br /><br />";
			//print_r($curcat);
			if($curcat->term_id){
				
					
				if($this->verbose)echo "<h4>" . $bd->blogname . "</h4>";
				if($curcat->slug != $row0->category_nicename){
					$newID = -1;
					if($this->verbose)echo "Local id already taken, so updating existing one.<br />";
					$newID = $this->get_next_id();
					$query = "INSERT INTO wp_sitecategories VALUES($newID,\"".addslashes($curcat->name)."\",\"".$curcat->slug."\",\"".date("Y-m-d H:i:s")."\")";
					if($this->verbose)echo "Inserting new category in global categories: " .  $query . "<br />";
					if($wpdb->query($query) !==false){
						//$newID = $wpdb->get_var('SELECT LAST_INSERT_ID();');	
						if($this->verbose)echo "Changing id of local category $curcat->name from $cid to $newID <br />";	
						$query = "UPDATE $wpdb->terms SET term_id=$newID WHERE term_id=$cid;";
						if($this->verbose)echo "Updating existing id: " . $query. "<br />";
						if($wpdb->query($query)!==false){
							$query = "UPDATE $wpdb->term_taxonomy SET term_id=$newID WHERE term_id=$cid;";
							if($wpdb->query($query)!==false){
								if($this->verbose)echo "Conflicting category succesfully moved to new one.\n<br />";
							}else{echo "Error<br />";return;}
						}else{echo "Error<br />";return;}
					}else{echo "Error<br />";return;}
				}
				if($curcat->slug == $row0->category_nicename){
					continue;
				}

				if($this->verbose)echo "Updating target local id with global id<br />";
				$query = "UPDATE $wpdb->terms SET term_id=$cid WHERE term_id=$local_id;";
				//echo $query;
				
				if($wpdb->query($query)!==false){
					$query = "UPDATE $wpdb->term_taxonomy SET term_id=$cid WHERE term_id=$local_id;";
					if($wpdb->query($query)!==false){
						$query = "UPDATE $wpdb->term_taxonomy SET parent=$cid WHERE parent=$local_id;";
						if($wpdb->query($query)!==false){
							if($this->verbose)echo "Update successful, category updated to global category.<br />";
						}else{echo "Error<br />";}

					}else{echo "Error<br />";}
				}else{echo "Error<br />";}
				
			}else if(!$curcat->term_id){//local category table does not have the global id
				//echo "$cid , $local_id, $nicename";
				if($this->verbose)echo "<h4>" . $bd->blogname . "</h4>";
				$query = "SELECT * FROM $wpdb->terms WHERE (name like \"$nicename\") OR (slug like \"$nicename\");";
				//echo $query;
				$row = $wpdb->get_row($query);
				if(sizeof($row)>0){
					if($this->verbose)echo "Local category table does not contain the global id $cid, so just setting it to that.<br />";
					$query = "UPDATE $wpdb->terms SET term_id=$cid WHERE term_id=$row->term_id;";
					if($this->verbose)echo "Updating existing id. <br />";
					if($wpdb->query($query)!==false){
						$query = "UPDATE $wpdb->term_taxonomy SET term_id=$cid WHERE term_id=$row->term_id;";
						if($wpdb->query($query)!==false){
							$query = "UPDATE $wpdb->term_taxonomy SET parent=$cid WHERE parent=$row->term_id;";
							if($wpdb->query($query)!==false){
									if($this->verbose)echo "Category updated succesfully <br />";
							}else{echo "Error<br />";}
						}else{echo "Error<br />";}
					}else{echo "Error<br />";}
						
				}
				//only update if this blog has the local category we are looking for

			}
		}
		switch_to_blog( $this->tags_blog_id );
	}

	function global_move_cat($fromid, $toid, $nicename){
		global $wpdb;
		echo "Updating existing global id $fromid to new global id $toid. <br />";
		$query = "UPDATE wp_sitecategories SET cat_ID = $toid WHERE cat_ID = $fromid;";
		//echo $query;
		if($wpdb->query($query)!==false){
			echo "<div style='border-left: 1px solid black; padding-left:5px;'>\n";
			$this->set_global_to_all($toid, $fromid, $nicename);
			echo "</div>";
			return true;
		}else{echo "Error $query<br />";}

		return false;

	}

	function global_move_to_new($gid, $nicename){
		global $wpdb;
		//first, must add new category to hold global category being replaced
		$newID = $this->get_next_id(true);//get next free global id
		//echo "new id : " .$newID;
		return $this->global_move_cat($gid, $newID, $nicename);

	}

	function global_insert_to_new($local_id, $nicename, $name = '', $newID = -1){
		global $wpdb;
		//first, must add new category to hold global category being replaced
		$newID = ($newID == -1) ? $this->get_next_id(true) : $newID;//get next free global id
		//echo "new id : " .$newID;
		echo "Inserting local category id $local_id ($nicename) into new category id $newID in the global categories table<br />\n";
		$cat_name = ($name == '') ? $this->get_name($local_id, $nicename) : $name;
		$query = "INSERT INTO wp_sitecategories VALUES($newID,\"".addslashes($cat_name)."\",\"".$nicename."\",\"".date("Y-m-d H:i:s")."\")";
		if($wpdb->query($query)!==false){
			echo "<div style='border-left: 1px solid black; padding-left:5px;'>\n";
			$this->set_global_to_all($newID, $local_id, $nicename);
			echo "</div>";
			return true;
		}else{echo "Error $query<br />";}

		return false;
	}

	function get_name($id, $nicename){
		$name = '';
		foreach($this->allcats as $cat){
			if(($cat['category_nicename']==$nicename) && ($cat['cat_ID']==$id)){
				$name = !$name ? $cat['name']  : $name;
			}
		}
		
		return $name ? $name : $nicename;
	}
	
	function set_local_to_global($local_id, $global_id, $nicename){
		global $wpdb;
		
		echo "<h3>$local_id ($nicename)</h3>";
		//echo "global id: " .  $global_id;
		if($global_id && ($global_id>-1)){
			//test to see if local id exists in global table and if so, make it a new id
			$row = $wpdb->get_row("SELECT * FROM wp_sitecategories WHERE cat_ID=$local_id;");
			if(sizeof($row)>0){
				$this->global_move_to_new($local_id, $row->category_nicename);
			}
			echo "Updating global id $global_id to local id $local_id. <br />";	
			$query = "UPDATE wp_sitecategories SET cat_ID = $local_id WHERE cat_ID = $global_id;";
			if($wpdb->query($query)!==false){
					echo "Updating new global category id for all blogs<br />";
					echo "<div style='border-left: 1px solid black; padding-left:5px;'>\n";
					$this->set_global_to_all($local_id, $global_id, $nicename);
					echo "</div>";
			}else{echo "Error<br />";}
		}else{//category name does not exist in global categories table, but id does
			$row = $wpdb->get_row("SELECT * FROM wp_sitecategories WHERE cat_ID=$local_id;");
			if($this->global_move_to_new($local_id, $row->category_nicename)){//move conflicting category to a new id
				echo "Inserting local category id into the empty category id in the global categories table<br />\n";
				$cat_name = $this->get_name($local_id, $nicename);
				$query = "INSERT INTO wp_sitecategories VALUES($local_id,\"".addslashes($cat_name)."\",\"".$nicename."\",\"".date("Y-m-d H:i:s")."\")";
				echo "Inserting new category in global categories: " .  $query . "<br />";
				if($wpdb->query($query) !==false){
					echo "Local category enforced successfully as global.<br />\n";
					//echo "<div style='border-left: 1px solid black; padding-left:5px;'>\n";
					//$this->set_global_to_all($local_id, $local_id, $nicename);
					//echo "</div>div>\n";
				}else{echo "Error<br />";}
			}
			
			
		}
		return;
		$row0 = $wpdb->get_row("SELECT * from wp_sitecategories WHERE cat_ID=$cid;");
		
		$blog_ids = $wpdb->get_col("SELECT blog_id from wp_blogs order by path;");
		foreach($blog_ids as $bid){
			switch_to_blog( $bid );
			$bd = get_blog_details( $bid );
			echo "<h4>" . $bd->blogname . "</h4>";
			$curcat = $wpdb->get_row("SELECT * from $wpdb->terms WHERE term_id=$cid;");
		
			if($curcat->term_id &&
				($curcat->slug != $row0->category_nicename)){
				//$global_existing = $wpdb->get_row("SELECT * from wp_sitecategories WHERE category_nicename like \"".$existing->slug."\";");
				$newID = -1;
				//if($global_existing->cat_ID){
				//	$newID = $global_existing->cat_ID;
				//}else{
				echo "Local id already taken, so updating existing one.<br />";
				$newID = $this->get_next_id();
				$query = "INSERT INTO wp_sitecategories VALUES($newID,\"".addslashes($curcat->name)."\",\"".$curcat->slug."\",\"".date("Y-m-d H:i:s")."\")";
				echo "Inserting new category in global categories: " .  $query . "<br />";
				if($wpdb->query($query) !==false){
					//$newID = $wpdb->get_var('SELECT LAST_INSERT_ID();');	
					echo "New id: $newID <br />";
					
					$query = "UPDATE $wpdb->terms SET term_id=$newID WHERE term_id=$cid;";
					echo "Updating existing id: " . $query. "<br />";
					if($wpdb->query($query)!==false){
						$query = "UPDATE $wpdb->term_taxonomy SET term_id=$newID WHERE term_id=$cid;";
						if($wpdb->query($query)!==false){
							echo "Updating local id with global id<br />";
							$query = "UPDATE $wpdb->terms SET term_id=$cid WHERE term_id=$local_id;";
							if($wpdb->query($query)!==false){
								$query = "UPDATE $wpdb->term_taxonomy SET term_id=$cid WHERE term_id=$local_id;";
								if($wpdb->query($query)!==false){
									echo "Update successful, category updated to global category.<br />";
								}else{echo "Error<br />";}
							}else{echo "Error<br />";}
						}else{echo "Error<br />";}
					}else{echo "Error<br />";}
				}else{echo "Error<br />";}
				
				
			}else if(!$curcat->term_id){//local category table does not have the global id
				//print_r($curcat);
				$curcat = get_category($local_id);
				//only update if this blog has the local category we are looking for

			}
		}
		switch_to_blog( $this->tags_blog_id );
	}

	function category_fixtags(){
		echo "fixing tags";
	}

	function get_blog_select(){
		global $wpdb;
		$blog_ids = $wpdb->get_col("SELECT blog_id from wp_blogs order by path;");
		$str = "<select name='blog_id' >";
		foreach($blog_ids as $bid){
			//switch_to_blog( $bid );
			$bd = get_blog_details($bid);
			$str.= "<option value='$bid'>$bd->blogname</option>";
		}
		$str.= "</select>";
		//switch_to_blog( $this->tags_blog_id );
		return $str;
	}

	function maybe_category($id){
		foreach($this->allcats as $mycat){
			$cid =  $mycat['cat_ID'];
			if($id == $cid){
				return true;
			}
		}
		return false;
	}

	function category_tagsform(){
		global $wpdb;

		$str = "<h2>Fix tag conflicts</h2>";
		$str .= '<form name="fixtags" id="fixtags" method="post" action="?page='.$_GET['page'].'#catman_fixtags" >';
		$str .= "<input type='hidden' name='action' value='fixtags' />";
		$str .= $this->get_blog_select();
		$str .= "<input type='submit' name='inspectblog' value='Submit' />";

		if(!$_POST['blog_id'] && !$_POST['prev_blog_id']){echo $str."</form>";return;

		}else if( !$_POST['prev_blog_id'] || $_POST['inspectblog']){
			$str .= "<input type='hidden' name='prev_blog_id' value='".$_POST['blog_id']."' />";
		}else if( $_POST['prev_blog_id']){
			$str .= "<input type='hidden' name='prev_blog_id' value='".$_POST['prev_blog_id']."' />";
		}

		$bid = $_POST['inspectblog'] ? $_POST['blog_id'] : $_POST['prev_blog_id'];
		$processtags = $_POST['process'] ? true : false;
		$bd = get_blog_details($bid);
		$str .= "<h2>$bd->blogname</h2>";
		$str .= "<div>";
		switch_to_blog( $bid );
		$maxtags = 1000;

		$query = "SELECT t.term_id, tt.term_taxonomy_id, t.name, t.slug, tt.count FROM $wpdb->term_taxonomy AS tt JOIN $wpdb->terms AS t ON (tt.term_id = t.term_id) WHERE taxonomy ='post_tag' AND tt.term_id NOT IN (SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy ='category') ORDER BY t.term_id ASC;";//LIMIT 3000 OFFSET 6000
		//echo $query;
		$tags = $wpdb->get_results($query);
		//echo $tags . "!<br />";
		//echo sizeof($tags). ' tags<br />';
		//echo "<table><tr><th>Tag name</th>
		$usedtags = array();
		$emptytags = array();
		$tagcounts = array();

		$i=0;

		foreach($tags as $t){
			//if($i >= $maxtags)break;
			//if($this->maybe_category($t->term_id)){
			//	$str .= $t->name . "(" . $t->term_id . ")"." is also a category, skipping. \n<br />";
			//	continue;
			//}
			$posts = $wpdb->get_results("SELECT post_title, post_status from $wpdb->term_relationships as tr JOIN $wpdb->posts as p on (ID=object_id) WHERE term_taxonomy_id = $t->term_taxonomy_id;");
			$usedtags[$t->term_id]['count']=$t->count;
			if(sizeof($posts)==0){
				$emptytags[$t->term_id] = array('name'=>$t->name,'term_taxonomy_id'=>$t->term_taxonomy_id);
				
			}else{
				foreach($posts as $p){
					if(isset($usedtags[$t->term_id][$p->post_status])){
						++$usedtags[$t->term_id][$p->post_status];
					}else{
						$usedtags[$t->term_id][$p->post_status]=1;
						$usedtags[$t->term_id]['name']=$t->name;
						$usedtags[$t->term_id]['slug']=$t->slug;
					}

					if(isset($tagcounts[$p->post_status])){
						++$tagcounts[$p->post_status];
					}else{
						$tagcounts[$p->post_status]=1;
					}
				}
			}
			//$str .= "<span>";
			//print_r($t);
			//$str .="</span>";
			++$i;
		}
		$str .="</div>";


		if($_POST['delete_empty'] && $_POST['prev_blog_id']){
			
			$str .= "<div><h3>Deleting empty tags for blog $bd->blogname </h3>";
			if(sizeof($emptytags)>0){
				foreach($emptytags as $term_id=> $tag){
					$str .= "<div style='border-bottom: 1px solid grey'>Deleting tag <i>".$tag['name']."</i> (id: $term_id, ttid: ".$tag['term_taxonomy_id'].") </div>";
					wp_delete_term( $term_id, 'post_tag');
					unset($emptytags[$term_id]);
				}

			}
			$str .= "</div>"; 
		
			
		}//else{

			if(sizeof($emptytags)>0){
				$str .= "<div><h3>Empty tags</h3>";
				foreach($emptytags as $tag){
					$str .= "<span style='border: 1px solid grey'>".$tag['name']."(tt: ".$tag['term_taxonomy_id'].") </span>";
				}
				$str .="<input type='submit' name='delete_empty' value='Delete Empty' />";
				$str .= "</div>\n";
			}else{
				$str .="<div >No empty tags</div>";
				$i = 0;
				$conflicts = 0;
				$oktags = 0;
				$nonexisting = 0;
				$existing = 0;
				$maxitems = (intval($_POST['maxitems'])>0) ? $_POST['maxitems'] : 20;
				if($maxitems > 20) { $this->verbose=false;}
				foreach($usedtags as $term_id => $props){
					if($processtags && ($i>=$maxitems)) break;
					$str .= "*";

					$row = $wpdb->get_row("SELECT * FROM wp_sitecategories WHERE cat_ID = $term_id;");
					if($row===false){
						$str .= "error $term_id <br />";
					}else if(sizeof($row)==0){
						++$nonexisting;
						if($processtags){
							$str .= "New: " . $props['name'] . " (id: " . $term_id . ")";
							$this->global_insert_to_new($term_id, $props['slug'], $props['name']);

						}
						//$this->set_local_to_global($term_id, $term_id, $props['slug']);
						++$i;
					}else{
						//$str .= $row->category_nicename . "/" . $props['slug'] . "";
						if($row->category_nicename == $props['slug']){
							++$oktags;
						}else{
							++$i;
							++$conflicts;
							$exists = $wpdb->get_row("SELECT * from wp_sitecategories WHERE category_nicename like \"" . $props['slug']. "\";");
							if(sizeof($exists)>0){
								//$str .= "Exists";
								++$existing;
								if($processtags){
									$str .= "Setting to existing global: " . $props['name'] . " (id: " . $exists->cat_ID . ")";
									$this->set_global_to_all($exists->cat_ID, $term_id, $props['slug']);
								}
							}else if($processtags){
								$str .= "Resolve conflict by new: " . $props['name'] . " (id: " . $term_id . ")";
								$this->global_insert_to_new($term_id, $props['slug'], $props['name']);
							}
						}
					}


				}
				$this->verbose = true;
				$str .= "<br /> # of conflict tags: " . $conflicts . "<br />";
				$str .= "# of missing tags: " . $nonexisting . "<br />";
				$str .= "# of existing tags: " . $existing . "<br />";
				$str .= "# of ok tags: " . $oktags . "<br />";
				if(($nonexisting > 0) || ($conflicts> 0)){
					$str .= "Maximum conflicting and missing tags to process: <input type='text' name='maxitems' /> <br />";
					$str .= "<input type='submit' name='process' value='Fix tags' />";
				}
/*
				$str .="<div>";
				$str .='<table><tr><th>Term id</th>';
				foreach($tagcounts as $post_status => $temp){
					$str .= "<th>$post_status</th>";
				}
				$str .= '</tr>';
				foreach($usedtags as $term_id => $counts){
					if($counts['count']>0)continue;
					$style = $counts['count'] == 0 ? "style='background-color: #FFB5B5'" : '';

					$str .= "<tr $style><td>$term_id (count: ".$counts['count'].")</td>";
					foreach($tagcounts as $post_status => $temp){
						$str .= "<td>".$counts[$post_status]."</td>\n";
					}
				}
	

				$str .= "</table>";
				$str .= "</div>";
*/

			}


		//}
		$str .= "</form>";
		

		echo $str;
		switch_to_blog( $this->tags_blog_id );
	}

	function fix_conflicts($cat_ids, $method, $nicename) {
		global $wpdb;

		echo "<br />";

		foreach($cat_ids as $cid=>$on){
			$row = $wpdb->get_row("SELECT * from wp_sitecategories WHERE category_nicename like \"" . $nicename[$cid] . "\";");						
			if($method[$cid] == 'global-existing'){
				
				$this->set_global_to_all($row->cat_ID, $cid, $nicename[$cid]);
			}else if($method[$cid] == 'local'){
				$this->set_local_to_global($cid, $row->cat_ID, $nicename[$cid]);
			}else if($method[$cid] == 'local-new'){
				$this->global_insert_to_new($cid, $nicename[$cid]);
			}
			echo "<hr />";
			continue;
			//$mycat = $this->find_cat($cid);
			$row0 = $wpdb->get_row("SELECT * from wp_sitecategories WHERE cat_ID=$cid;");
			
			if(sizeof($row0)==0){
				echo "<option value='local'>Enforce local id $cid </option>";
				if(sizeof($row)>0){
					echo "<option value='global-existing'>Enforce existing global id $row->cat_ID</option>";
				}

			}else{
				echo "<option value='local'>Enforce local id $cid </option>";
				if(sizeof($row)>0){
					echo "<option value='global-existing'>Enforce existing global id $row->cat_ID</option>";
				}
			}
			return;
			$query = "INSERT INTO wp_sitecategories VALUES (".$cid.",\"".addslashes($this->allcats[$cid]['name'])."\",\"".$this->allcats[$cid]['category_nicename']."\",\"".$lastupdated."\" );";
			//echo $query;
			//continue;
			$result = $wpdb->query($query);
			if($result === false){
				$query = "UPDATE wp_sitecategories set cat_name=\"".$this->allcats[$cid]['name']."\",
					category_nicename = \"".$this->allcats[$cid]['category_nicename']."\",
					last_updated = \"".$lastupdated."\"  WHERE cat_ID=$cid;";
				$result = $wpdb->query($query);
				echo "Category id $cid already exists, so updating. <br />";
				if($result === false){
					echo "Error updating ".$this->allcats[$cid]['name'] . " ($cid). <br />";
				}
				
			}
		}
			
	}



	function insert_category($post_vars){
		global $wpdb;
		//print_r($post_vars);
		$blog_ids = $wpdb->get_col("SELECT blog_id from wp_blogs order by path;");
		echo "<div class='message'>Category " . $post_vars['cat_name'] . ' ';
		foreach($blog_ids as $bid){
			switch_to_blog( $bid );
			if( wp_insert_category($post_vars)) {
				echo "*";
			} else {
				echo "error(blog id = $bid)";
			}
		}
		echo "-Done</div>";
		switch_to_blog( $this->tags_blog_id );
	}

	function admin_head(){
		//global $wpdb;

		//$tags_blog_id = get_site_option( 'tags_blog_id' );
		//if($wpdb->blogid != $tags_blog_id)return;

		if($_GET['page'] == "sitewide-categories-manager.php"){
			echo "\n<!-- Sitewide category manager tabs-->\n";
			echo '<link rel="stylesheet" href="'.get_bloginfo('url').'/wp-content/plugins/wfiu_utils/ui.tabs.css" type="text/css" />'."\n";
			wp_enqueue_script( 'jquery-ui-tabs' );
			echo "\n<!-- END Sitewide category manager tabs-->\n";
		}

	}

	function print_conflicts(){
		global $wpdb;

?>
<script type='text/javascript'>
	jQuery(document).ready(function() {
		jQuery("#fix_select_all").click(function(){
	   		if(jQuery(this).attr('checked')){
	   			jQuery('.fix_box').attr('checked', true);
	   		}else{
	   			jQuery('.fix_box').attr('checked', false);
	   		}
		});

		
	});
</script>

<?php 
		$hasconflicts = false;
		$str = "<h2>Category Conficts</h2>";
		$str .= '<form name="fixcat" id="fixcat" method="post" action="?page='.$_GET['page'].'#catman_conflicts" >';
		$str .= "<input type='hidden' name='action' value='fixcats' />";
		$str .= "<table><tr><th style='width: 100px;padding:0px;text-align: left;'><input type='checkbox' name='fix_select_all' id='fix_select_all' /> <label for='fix_select_all'>Select all</label> </th><th>Category</th><th>Conflicts</th></tr>";
		$tdstyle = "style='border-top:1px solid black;'";
		$allcatsIDS = array_keys($this->allcats);
		foreach($this->allcats as $mycat){
			$cid =  $mycat['cat_ID'];
			if($cid ==1 ) continue;
			$row0 = $wpdb->get_row("SELECT * from wp_sitecategories WHERE cat_ID =" . $cid . ";");
			//echo "<br />\n";


			if($row0->category_nicename == $mycat['category_nicename']){
				continue;
			}else{
				$catmysql = $wpdb->get_row("SELECT * FROM wp_".$mycat['blog_id']."_terms WHERE term_id = " . $cid . ";");
				if((sizeof($catmysql)>0) && ($row0->category_nicename == $catmysql->slug)){
					continue;
				}
			}
			//print_r($mycat);
			//print_r($row0);
			$hasconflicts = true;
			if ($row0 === false){
				$str .= "<div> Error... ".$mycat['name']."</div>";
			}				
			$row = $wpdb->get_row("SELECT * from wp_sitecategories WHERE category_nicename like \"" . $mycat['category_nicename'] . "\";");			
			$str .= "<tr >";
			$str .= "<td style='width: 230px'><input type='hidden' class='fix_box' name='fix_category_nicename[${cid}]' value='".$mycat['category_nicename']."' />";
			$str .= "		<input type='checkbox' class='fix_box' id='fix_conflict[${cid}]' name='fix_conflict[${cid}]' />";
			//echo "	<label for='fix_conflict[${cid}]'>Fix</label> ";
			
			$str .= "<select name='fix_method[${cid}]' onclick=\"jQuery('#fix_conflict\\\\[${cid}\\\\]').attr('checked', true);
\" >";
			if(sizeof($row0)==0){
				
				if(sizeof($row)>0){
					$str .= "<option value='global-existing'>Enforce existing global id $row->cat_ID</option>";
				}
				$str .= "<option value='local'>Enforce local id $cid </option>";
			}else{
				
				if(sizeof($row)>0){
					$str .= "<option value='global-existing'>Enforce existing global id $row->cat_ID</option>";
				}else{
					$str .= "<option value='local-new'>Convert into new global category</option>";
				}
				$str .= "<option value='local'>Enforce local id $cid </option>";
			}
			$str .= "</select>";
			//$str .= "<input type='submit' name='
			$str .= "</td>";
			$bname = $wpdb->get_var("SELECT option_value from wp_".$mycat['blog_id']."_options WHERE option_name =\"blogname\";");

			$str .= "<td $tdstyle >" . $mycat['name'] . "/" .$mycat['category_nicename']." ($cid) on blog <i>$bname</i></td>";	

			$str .= "<td $tdstyle>";
			if(sizeof($row0)==0){
				$str .= "No global category exists that matches this id";

			}else{
				$str .= "Local category id does not match global category id, instead it's ". $row0->cat_name." (".$row0->category_nicename.")";
			}
			

			
			if(sizeof($row)>0){
				//if(in_array($row->cat_ID, $allcatsIDS)){
					$str .= "<br />Also, this category already exists in the global table as: $row->cat_ID, ".$row->cat_name." (".$row->category_nicename.")";
				//}else{
				//	$str .= "<br />Also, this tag/category already exists in the global table as $row->cat_ID: ".$row->cat_name." (".$row->category_nicename.")";
				//}
			}
			$str .= "</td>";
			$str .= "</tr>";

		}
		$str .= "</table><input type='submit' value='Fix' /> </form>";
		if($hasconflicts){
			echo $str;
		}else{
			echo "<p>No category conflicts found.</p>";
		}

		
	}


	function print_conflicts2(){
		global $wpdb;

?>
<script type='text/javascript'>
	jQuery(document).ready(function() {
		jQuery("#fix_select_all").click(function(){
	   		if(jQuery(this).attr('checked')){
	   			jQuery('.fix_box').attr('checked', true);
	   		}else{
	   			jQuery('.fix_box').attr('checked', false);
	   		}
		});

		
	});
</script>

<?php 
		echo '<form name="fixcat" id="fixcat" method="post" action="?page='.$_GET['page'].'" >';
		echo "<input type='hidden' name='action' value='fixcats' />";
		echo "<table><tr><th style='width: 100px;padding:0px;text-align: left;'><input type='checkbox' name='fix_select_all' id='fix_select_all' /> <label for='fix_select_all'>Select all</label> </th><th>Category</th><th>Conflicts</th></tr>";
		$tdstyle = "style='border-top:1px solid black;'";
		$allcatsIDS = array_keys($this->allcats);
		foreach($this->allcats as $mycat){
			$cid =  $mycat['cat_ID'];
			$row0 = $wpdb->get_row("SELECT * from wp_sitecategories WHERE category_nicename like \"" . $mycat['category_nicename'] . "\";");

			if ($row0 === false){
				echo "<div> Error...</div>";
			}else if(sizeof($row0)>0){
				//echo $row0->cat_ID . ' and ' . $cid . "\n<br />";
				if($row0->cat_ID != $cid){
					$row=$wpdb->get_row("SELECT * from wp_sitecategories WHERE cat_ID=$cid;");
					
					echo "<tr >";
					echo "<td><input type='checkbox' class='fix_box' name='fix_conflict[${cid}]' /> Fix";
					echo "<select name='fix_method[${cid}]' >";
					//echo 
					//if(sizeof($row)>0){
					//		
					//}
					echo "</select>";
					//echo "<input type='submit' name='
					echo "</td>";
					echo "<td $tdstyle >" . $mycat['name'] . "($cid)</td>";	
					echo "<td $tdstyle >Nicename matches, but ids dont, its: ".$row0->cat_name." (".$row0->cat_ID.")";


				}//else{
				//	echo "<div> Category nicename matches ID.</div>";
				//}
			}else if(sizeof($row0)==0){
				echo "<tr style='border:1px solid black;' >";
				echo "<td><input type='checkbox' class='fix_box' name='sitecat_add[${cid}]' /> Fix</td>";
				echo "<td $tdstyle>" . $mycat['name'] . "($cid)</td>";	
				echo "<td $tdstyle>no row exists that matches the category nicename <i>".$mycat['category_nicename']."</i>";

			}
			
			if ((sizeof($row0)>= 0)&& ($row0->cat_ID != $cid)){
				//print_r($row0);
				$row=$wpdb->get_row("SELECT * from wp_sitecategories WHERE cat_ID=$cid;");
				if(sizeof($row)>0){
					if(in_array($row->cat_ID, $allcatsIDS)){
						echo "<br />Also, id $cid is already taken by category: ".$row->cat_name." (".$row->category_nicename.")";
					}else{
						echo "<br />Also, id $cid is already taken by tag/category: ".$row->cat_name." (".$row->category_nicename.")";
					}
				}
				echo "</td>";
				echo "</tr>";
			}
		}
		echo "</table><input type='submit' value='Fix' /> </form>";
		
	}

	function addcat_form(){
?>



<div id="col-left">
  <div class="col-wrap">

	<?php if ( current_user_can('manage_categories') ) { ?>
	<?php $category = (object) array(); $category->parent = 0; do_action('add_category_form_pre', $category); ?>

	<div class="form-wrap">
		<h3><?php _e('Add Sitewide Category'); ?></h3>

		<form name="addcat" id="addcat" method="post" action="?page=<?php echo $_GET['page']; ?>#catman_add" >
		<input type="hidden" name="action" value="addcat" />
		<input type="hidden" name="add-category-nonce" id="add-category-nonce" value="<?php echo wp_create_nonce('add-category'); ?>" />

		<div class="form-field form-required">
			<label for="cat_name"><?php _e('Category Name') ?></label>
			<input name="cat_name" id="cat_name" type="text" value="" size="40" aria-required="true" />
		    <p><?php _e('The name is used to identify the category almost everywhere, for example under the post or in the category widget.'); ?></p>
		</div>

		<div class="form-field">
			<label for="category_parent"><?php _e('Category Parent') ?></label>
			<select class="postform" id="category_parent" name="category_parent">
				<option value="-1" class='level-0'>None</option>
			<?php
				foreach($this->allcats as $cid=>$cprops){
					echo "<option value='$cid' class='level-0'>";
					echo $cprops['name'];
					echo "</option>";
				}

			?>

			</select>
		    <p><?php _e('Categories, unlike tags, can have a hierarchy. You might have a Jazz category, and under that have children categories for Bebop and Big Band. Totally optional.'); ?></p>
		</div>

		<div class="form-field">
			<label for="category_description"><?php _e('Description') ?></label>
			<textarea name="category_description" id="category_description" rows="5" cols="40"></textarea>
		    <p><?php _e('The description is not prominent by default, however some themes may show it.'); ?></p>
		</div>

		<p class="submit"><input type="submit" class="button" name="submit" value="<?php _e('Add Category'); ?>" /></p>

		</form>
	</div>

	<?php } ?>

  </div>
</div><!-- /col-left -->

<?php 

	}

	function main_page(){
		global $wpdb;
		wp_reset_vars( array('action', 'cat') );
		$this->init_variables();
		//echo $_POST['action'];
		switch($_POST['action']){
			case 'addcat':
				$this->validate_transaction();
				$this->insert_category($_POST);

				//exit;
				break;
			case 'modifycats':
				$this->validate_transaction();
				if($_POST['submit_assign']){
					$this->assign_categories($_POST);
				}else if($_POST['submit_merge']){
					$this->merge_categories($_POST['merge_index'], array_keys($_POST['merge_indeces']), $_POST['merge_description']);
				}else if($_POST['deleteIndex']){
					$this->delete_category($_POST['deleteIndex']);
				}else if($_POST['blogCat']){
					$blog_and_cat = explode('_', $_POST['blogCat']);

					$this->copy_to_all_blogs($blog_and_cat[0], $blog_and_cat[1]);
				}
				break;
			case 'fixcats':
			
				$this->validate_transaction();
				if($_POST['fix_conflict']){
					//echo "!fixcats";
					$this->fix_conflicts($_POST['fix_conflict'], $_POST['fix_method'], $_POST['fix_category_nicename']);
				}

				if($_POST['sitecat_add']){
					$this->add_sitecats($_POST['sitecat_add']);
				}

				break;
			case 'fixtags':

				$this->validate_transaction();
				$this->category_fixtags();
		}
		$this->init_variables();
		echo "<div class='wrap'>";
?>

		<div id="catman_tabs">
		     <ul>
			 <li><a href="#catman_conflicts"><span>View Conflicts</span></a></li>
			 <li><a href="#catman_add"><span>Add category</span></a></li>
			 <li><a href="#catman_view"><span>View and edit categories</span></a></li>
			 <li><a href="#catman_lookup"><span>Look up by ID</span></a></li>
			 <li><a href="#catman_fixtags"><span>Fix blog tags</span></a></li>
		     </ul>
		</div>

		<div id='catman_conflicts'>
			<?php $this->print_conflicts();?>
		</div>
		<div id='catman_add'>
			<?php $this->addcat_form();?>
		</div>
		<div id='catman_view'>
			<?php $this->categories_summary();?>
		</div>
		<div id='catman_lookup'>
			<?php $this->category_lookup();?>
		</div>

		<div id='catman_fixtags'>
			<?php $this->category_tagsform();?>
		</div>

		<script type='text/javascript'>
		 jQuery(document).ready(function(){
			jQuery('#catman_tabs').tabs({ remote: true });
		});
		</script>

<?php

		echo "</div>";
		switch_to_blog( $this->tags_blog_id );
	}

}


function sitewide_categories_manager_menu() {
	global $wpdb;

	$tags_blog_id = get_site_option( 'tags_blog_id' );
	if($wpdb->blogid != $tags_blog_id)return;
	$sitewideCategoryManager = new sitewide_categories($tags_blog_id);
	//add_menu_page('Home', 'Home', 7, ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php');
	//if(get_bloginfo('version')>= 2.7 ){
	//	wfiu_do_main_page();
	//	add_submenu_page(ABSPATH.PLUGINDIR.'/wfiu_utils/wfiu_plugins_homepage.php', 'Sitewide Tags', 'Sitewide Tags', 9, 'Sitewide Tags', 'sitewide_tags_rerun_page');
	//}else{
	add_menu_page('Sitewide Categories Manager', 'Sitewide Categories Manager', 9, __FILE__ , array(&$sitewideCategoryManager, 'main_page'));
	add_action('admin_print_scripts', array(&$sitewideCategoryManager, 'admin_head') );
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
add_action('admin_menu', 'sitewide_categories_manager_menu');
//add_action('admin_menu', array(&$map_item_class, 'mapper_box'), 1);//add slideshow box

//save and delete post hook
//add_action('save_post', array(&$map_item_class,'saveItem'));
//add_action('delete_post', array(&$map_item_class,'deleteItem'));


//add_filter('manage_posts_columns', array(&$map_item_class,'add_column'));
//add_filter('manage_posts_custom_column', array(&$map_item_class,'do_column'), 10, 2);
?>
