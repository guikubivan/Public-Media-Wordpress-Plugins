<?php
/*
Plugin Name: Breadcrumb - Wrapper
Plugin URI: http://www.wfiu.org
Description: Wrapper plugin built for omitting the undesired catgories from the trail returned by the Breadcrumb NavXT plugin.
Version: 1.0
Author: Priyank Shah
Author URI: http://www.wfiu.org
*/


function bcn_display_omit($omit=array()){
	if(function_exists('bcn_display')) {
		$string = bcn_display(true);
		$temp = array();
		$delimiter = "</span>";
		$temp = explode($delimiter, $string);
		$count = count($temp);
		
		foreach($omit as $remove){
			for($i=1;$i<=$count;$i++){
				if ((!strpos($temp[$i], $remove) == false) || ($temp[$i] == "")){
					unset($temp[$i]);
				}
			}
		}

		$new_string ="";	

		foreach($temp as $value){
			$new_string.= $value."</span>";
		}	
		echo $new_string;
	}
}
?>
