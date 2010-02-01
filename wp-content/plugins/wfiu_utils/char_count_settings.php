<div class='wrap'>
<?php
$defaults= array();

$defaults['title']['min']=0;
$defaults['title']['max']=66;
$defaults['title']['optional']='off';

$defaults['teaser']['min']=50;
$defaults['teaser']['max']=160;
$defaults['teaser']['optional']='off';

$defaults['excerpt']['min']=0;
$defaults['excerpt']['max']=350;
$defaults['excerpt']['optional']='on';


$prefix='char_count_';
if ($_POST) {
	if($_POST['reset']){
		foreach($defaults as $name => $props){
			//echo "*";
			foreach($props as $p => $option_val){
				//echo "-";
				
				$option_name = $prefix . $name . '_' . $p;
				delete_option($option_name);
			}
		}	
		die("<div class='highlight'>Character counter settings reset to defaults. </div> <a href=''>View Settings</a></div>");
		
		
	}
	
	foreach($_POST[field] as $name => $props){
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
			delete_option($option_name);
		}
	}
	print "<div class='highlight'>Succesful update</div> <!--meta http-equiv='refresh' content='0'-->";
}

//update_option('google_api_key', $_POST[store_locator_api_key]);
$fields = array();

foreach($defaults as $name => $props){
	//echo "*";
	foreach($props as $p => $blah){
		//echo "-";
		$option_name = $prefix . $name . '_' . $p;
		$val = get_option($option_name);
		//echo $option_name  . ': ' . $val  .  "<br />";
		if( ($p =='optional') && ($val=='off') ){
			$donuthin='';
		}else{
			//echo 'getting: '. $option_name .'<br />';
			$fields[$name][$p] = $val;
		}
	}
}
//print_r($defaults);
//print_r($fields);


print "<h2>Character Count Settings</h2><br><form action='' method='post'><table style='width: 400px;' class='widefat'>";
print "<thead><tr> <th>Field</th> <th>Max/Min Characters</th> <th>Optional?</th> </tr></thead>";
$min = $fields[title][min];
$max = $fields[title][max];
$checked = $fields[title][optional] ? 'checked' : '';
print "	<tr><td>Title:</td><td> <input name='field[title][min]' value='$min' size='3'> Min <input name='field[title][max]' value='$max' size='3'> Max</td><td><input type='checkbox' name='field[title][optional]' ". $checked ." /> </td></tr>";

$min = $fields[teaser][min];
$max = $fields[teaser][max];
$checked = $fields[teaser][optional] ? 'checked' : '';
print "	<tr><td>Teaser:</td><td> <input name='field[teaser][min]' value='$min' size='3'> Min <input name='field[teaser][max]' value='$max' size='3'> Max</td><td><input type='checkbox' name='field[teaser][optional]' ". $checked ." /> </td></tr>";

$min = $fields[excerpt][min];
$max = $fields[excerpt][max];
$checked = $fields[excerpt][optional] ? 'checked' : '';
print "	<tr><td>Excerpt:</td><td> <input name='field[excerpt][min]' value='$min' size='3'> Min <input name='field[excerpt][max]' value='$max' size='3'> Max</td><td><input type='checkbox' name='field[excerpt][optional]' ". $checked ." /> </td></tr>";


print "<tr><td>&nbsp;</td><td ><input type='submit' value='Update' class='button' /> <input type='submit' name='reset' value='Defaults' class='button' /></td><td>&nbsp;</td></tr>";
print "</table></form>";

?></div>
