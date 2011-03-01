<?php
if(!class_exists ('custom_field_plugin')) {
	abstract class custom_field_plugin {
		// Force Extending class to define this method
		abstract public function editForm();

		protected $boxname;
		protected $fieldname;
		protected $inputname;

		public function __construct($boxname, $fieldname, $inputname){
			$this->boxname = $boxname;
			$this->fieldname = $fieldname;
			$this->inputname = $inputname;
			add_action('save_post', array(&$this,'saveItem'));
			add_action('delete_post', array(&$this,'deleteItem'));
		}


		public function saveItem($post_id){
			if ( !wp_verify_nonce( $_POST[$this->inputname.'noncename'], plugin_basename($this->inputname . 'nonce') )) {
				return $post_id;
			}
			if(wp_is_post_revision($post_id)) { 
				return $post_id;
			}

			$val = $_POST[$this->inputname];
			//echo "stuff".$this->inputname;
			//$val = addslashes($val);
		
			if(strip_tags($val) || get_post_meta($post_id, $this->fieldname)){
				//echo "stuff".$this->inputname;
				if(strip_tags($val)){
					add_post_meta($post_id, $this->fieldname, $val, true) or update_post_meta($post_id, $this->fieldname, $val);
				}else{
					delete_post_meta($post_id, $this->fieldname); 
				}
			}
		}

		public function deleteItem($post_id){
			delete_post_meta($post_id, $this->fieldname); 
		}
	}
}

if(!class_exists ('box_custom_field_plugin')) {

	class box_custom_field_plugin extends custom_field_plugin{
		private $box_position;
		private $element;
		
		public function __construct($element, $box_position, $boxname, $fieldname, $inputname){
			custom_field_plugin::__construct($boxname, $fieldname, $inputname);
			$this->box_position = $box_position;
			$this->element = $element;
			add_action('admin_menu', array(&$this, 'add_box'), 1);//add teaser box

		}


		function add_box(){
			$box_position = (get_bloginfo('version')>=2.7) ? $this->box_position : 'normal';//default to normal if version less than 2.7
			add_meta_box($this->inputname . '-box-div', __($this->boxname, $this->inputname.'box'), array(&$this, 'editForm'), 'post', $box_position, 'high');
			add_meta_box($this->inputname . '-box-div', __($this->boxname, $this->inputname.'box'), array(&$this, 'editForm'), 'page', $box_position, 'high');
		}

		function editForm(){
			global $post;
			//echo $this->fieldname;
			$el = $this->element;
			if(is_array($this->element)){
				$props = $this->element['settings'];
				$el = $this->element['element'];
				$before = $this->element['before'];
				$after = $this->element['after'];
			}
			echo'<input type="hidden" name="'.$this->inputname.'noncename" id="'.$this->inputname.'noncename" value="' . wp_create_nonce( $this->inputname . 'nonce') .'" />';
			
			$value = get_post_meta($post->ID, $this->fieldname);
			if(is_array($value)){
				$value = $value[0];
			}
			$value = stripslashes($value);

			switch ($el) {
			    case "textarea":
					echo $before . "<$el name=\"".$this->inputname."\" id=\"".$this->inputname."\" $props >$value</$el>" . $after;
					break;
				default:
					echo $before . "<$el name=\"".$this->inputname."\" $props >$value</$el>" . $after;
			}
		}

	}
	
}
?>
