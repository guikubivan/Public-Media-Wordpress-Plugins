<div class='wrap'>
<?php 

if ($_POST) {
if(function_exists('add_site_option')){
	add_site_option('google_api_key', $_POST[store_locator_api_key]) or update_site_option('google_api_key', $_POST[store_locator_api_key]);

}else{
	update_option('google_api_key', $_POST[store_locator_api_key]);
}
//update_option('sl_language', $_POST[sl_language]);

//$sl_google_map_arr=explode(":", $_POST[google_map_domain]);
//update_option('google_map_country', $sl_google_map_arr[0]);
//update_option('google_map_domain', $sl_google_map_arr[1]);
//update_option('sl_distance_unit', $_POST[sl_distance_unit]);

print "<div class='highlight'>".__("Successful Update", $text_domain)."</div> <!--meta http-equiv='refresh' content='0'-->";
}

print "<h2>".__("Update Your Google API Key", $text_domain)."</h2><br><form action='' method='post'><table class='widefat'><thead><tr ><td colspan='1'>".__("Your API Key Status", $text_domain).":</td><td>";
//load_plugin_textdomain('api-key');
//$text_domain='api=key';
if(function_exists('get_site_option')){
	$slak=get_site_option('google_api_key');
}else{
	$slak=get_option('google_api_key');
}

if (!$slak || trim($slak)=='') {
	print "<div style='border:solid red 1px; padding:5px; background-color:pink; width:400px; color:black'>".__("Please Update Your Google API Key", $text_domain)."</div>";
}
else {
	print "<div style='border:solid green 1px; padding:5px; background-color:LightGreen; width:400px; color:black'>".__("API Key Submitted", $text_domain)."</div>";
}

print "</td></tr></thead><tr><td>".__("Google API Key", $text_domain).":</td><td><input name='store_locator_api_key' value='$slak' size='100'><br><br><div class=''><strong>".__("Note: <br>", $text_domain)."</strong>".__("Each API Key is unique to one web domain. To obtain a Google API Key for this domain ($_SERVER[HTTP_HOST]):<br><br><b>Step 1.</b> Log into your Google Account<br>(If you use any Google Product, such as Gmail, Google Docs, etc., then you have a Google Account. If not, <a href='https://www.google.com/accounts/' target='_blank'>you can sign up for one</a>) <br><br><b>Step 2.</b> Visit: <a href='http://code.google.com/apis/maps/signup.html' target='_blank'>http://code.google.com/apis/maps/signup.html</a> and submit your domain, and receive an API Key from Google", $text_domain)."</div></td></tr>";

/*print "<tr><td>".__("Choose Your Language", $text_domain).":</td><td><select name='sl_language'>";
$the_lang["United States"]="en_EN";
$the_lang["France"]="fr_FR";

foreach ($the_lang as $key=>$value) {
	$selected=(get_option('sl_language')==$value)?" selected " : "";
	print "<option value='$value' $selected>$key ($value)</option>\n";
}
print "</td></tr>";*/


/*
print "<tr><td>".__("Select Your Location", $text_domain).":</td><td><select name='google_map_domain'>";
$the_domain["United States"]="maps.google.com";
$the_domain["Austria"]="maps.google.com.at";
$the_domain["Australia"]="maps.google.com.au";
$the_domain["Bosnia and Herzegovina"]="maps.google.com.ba";
$the_domain["Belgium"]="maps.google.be";
$the_domain["Brazil"]="maps.google.com.br";
$the_domain["Canada"]="maps.google.ca";
$the_domain["Switzerland"]="maps.google.ch";
$the_domain["Czech Republic"]="maps.google.cz";
$the_domain["Germany"]="maps.google.de";
$the_domain["Denmark"]="maps.google.dk";
$the_domain["Spain"]="maps.google.es";
$the_domain["Finland"]="maps.google.fi";
$the_domain["France"]="maps.google.fr";
$the_domain["Italy"]="maps.google.it";
$the_domain["Japan"]="maps.google.jp";
$the_domain["Netherlands"]="maps.google.nl";
$the_domain["Norway"]="maps.google.no";
$the_domain["New Zealand"]="maps.google.co.nz";
$the_domain["Poland"]="maps.google.pl";
$the_domain["Russia"]="maps.google.ru";
$the_domain["Sweden"]="maps.google.se";
$the_domain["Taiwan"]="maps.google.tw";
$the_domain["United Kingdom"]="maps.google.co.uk";

foreach ($the_domain as $key=>$value) {
	$selected=(get_option('sl_google_map_domain')==$value)?" selected " : "";
	print "<option value='$key:$value' $selected>$key ($value)</option>\n";
}
print "</select></td></tr>";

print "<tr><td>".__("Select Distance Unit", $text_domain).":</td><td><select name='sl_distance_unit'>";

$the_distance_unit["".__("Kilometers", $text_domain).""]="km";
$the_distance_unit["".__("Miles", $text_domain).""]="miles";
foreach ($the_distance_unit as $key=>$value) {
	$selected=(get_option('sl_distance_unit')==$value)?" selected " : "";
	print "<option value='$value' $selected>$key</option>\n";
}

print "</select><br><br><input type='submit' value='".__("Update", $text_domain)."' class='button'></td></tr>";
*/
print "<tr><td>&nbsp;</td><td><input type='submit' value='".__("Update", $text_domain)."' class='button'></td></tr>";
print "</table></form>";

?></div>
