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



 ?>
