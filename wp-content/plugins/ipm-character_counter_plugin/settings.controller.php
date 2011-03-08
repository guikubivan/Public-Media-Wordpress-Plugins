
<?php
$defaults= array();
$messages = "";
$defaults['title']['min']=0;
$defaults['title']['max']=66;
$defaults['title']['optional']='off';

$defaults['teaser']['min']=50;
$defaults['teaser']['max']=160;
$defaults['teaser']['optional']='off';

$defaults['excerpt']['min']=0;
$defaults['excerpt']['max']=350;
$defaults['excerpt']['optional']='on';

$defaults['in_pages']='off';
$defaults['teaser_activated'] = 'off';

$prefix='char_count_';
if ($_POST) {
	if($_POST['reset']){
		foreach($defaults as $name => $props){
			//echo "*";
			if(is_array($props)){
				foreach($props as $p => $option_val){
					//echo "-";
				
					$option_name = $prefix . $name . '_' . $p;
					delete_option($option_name);
				}
			}else{
				$option_name = $prefix . $name ;
				delete_option($option_name);
			}
		}	
		die("<div class='updated fade'>Character counter settings reset to defaults. </div> <a href=''>View Settings</a></div>");
		
		
	}
	
	foreach($_POST[field] as $name => $props){
		if(is_array($props)){
			foreach($props as $p => $val){
				$option_name = $prefix . $name . '_' . $p;
				$option_val = $defaults[$name][$p];
				if(isset($val) && $val){
					$option_val = $val;
				}
				
				//echo $option_name  . ': ' . $option_val .  "<br />";
				if ( get_option($option_name) !== false) {
					update_option($option_name, $option_val);
				} else {
					add_option($option_name, $option_val);
				}
			}
			if(!isset($props['optional']) ){
				$option_name = $prefix . $name . '_optional';
				//delete_option($option_name);
				update_option($option_name, "off");
			}
		}else{
			$option_name = $prefix . $name;
			$option_val = $defaults[$name];
			if(isset($props) && $props){
				$option_val = $props;
			}
			//echo $option_name  . ': ' . $option_val .  "<br />";
			if ( get_option($option_name) !== false) {
				update_option($option_name, $option_val);
			} else {
				add_option($option_name, $option_val);
			}
		}
	}
	if(!isset($_POST[field][in_pages]) ){
		$option_name = $prefix . 'in_pages';
		delete_option($option_name);
	}
	if(!isset($_POST[field][teaser_activated]) ){
		$option_name = $prefix . 'teaser_activated';
		delete_option($option_name);
	}
	$messages .= "<div class='updated fade'><p><strong>Succesful update</strong></p></div> <!--meta http-equiv='refresh' content='0'-->";
}

//update_option('google_api_key', $_POST[store_locator_api_key]);
$fields = array();

foreach($defaults as $name => $props){
	//echo "*";
	if(is_array($props)){
		foreach($props as $p => $blah){
			//echo "-";
			$option_name = $prefix . $name . '_' . $p;
			$val = get_option($option_name);
			//echo $option_name  . ': ' . $val  .  "<br />";
			if( ($p !='optional') || ($val!='off') ){
				//echo 'getting: '. $option_name .'<br />';
				$fields[$name][$p] = $val;
			}
		}
	}else{
		$option_name = $prefix . $name;
		$val = get_option($option_name);
		if( $val !='off' ){
			$fields[$name] = $val ;
		}
	}
}
//print_r($defaults);
//print_r($fields);

include("views/settings.php");

?>
