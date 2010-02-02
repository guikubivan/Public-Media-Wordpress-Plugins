<?php 
echo "\nvar num2day  = ['";
$days = get_days('D', 'w');
echo implode("','", $days);
echo "'];";

$mf = 'M';

echo "var month_names  = { \n";
$month_array = array();
for($i=0; $i<12; ++$i){
	$month_array[] = $i . ": \"" . date("$mf", mktime(0, 0, 0, $i+2, 0, 2009)) . "\"";
}
echo implode(',', $month_array);
echo "};";

?>

function ajax_get_program(schedule_name, id, element, leftright){
	var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-content/plugins/program_scheduler/ajax_get_programs.php" );    
	mysack.execute = 0;
	mysack.method = 'POST';
	mysack.setVar( "action", "action_single_program" );
	mysack.setVar('schedule_name', schedule_name);

	mysack.setVar( 'content', 'full' );
	mysack.setVar( 'program_id', id);


	mysack.encVar( "cookie", document.cookie, false );
	mysack.onError = function() { alert('Ajax error in sending event.' ); };
	
	mysack.onCompletion = function completed(){
		htmlText = mysack.response;
		jQuery('.single_details:first').remove();
		
		jQuery(element).before("<div style='' class='single_details_wrapper_"+leftright+"'></div>");
		jQuery(element).prev().prepend(htmlText);
	};
	
	mysack.runAJAX();
	return true;
}

function ajax_get_single_day(date_str){
	var mysack = new sack("<?php bloginfo( 'wpurl' ); ?>/wp-content/plugins/program_scheduler/ajax_get_programs.php" );    
	mysack.execute = 0;
	mysack.method = 'POST';
	mysack.setVar( "action", "action_single_day" );
	mysack.setVar( 'start_date', date_str );



	mysack.encVar( "cookie", document.cookie, false );
	mysack.onError = function() { alert('Ajax error in sending event.' ); };
	
	mysack.onCompletion = function completed(){
		htmlText = mysack.response;
		if(htmlText){
			jQuery("#both-single").html(htmlText);
		}
	};
	
	mysack.runAJAX();
	return true;
}



function getURLVar(urlVarName) {
//divide the URL in half at the '?'
	var urlHalves = String(document.location).split('?');
	var urlVarValue = '';
	if(urlHalves[1]){
		//load all the name/value pairs into an array
		var urlVars = urlHalves[1].split('&');
		//loop over the list, and find the specified url variable
		for(i=0; i<=(urlVars.length); i++){
			if(urlVars[i]){
				//load the name/value pair into an array
				var urlVarPair = urlVars[i].split('=');
				if (urlVarPair[0] && urlVarPair[0] == urlVarName) {
					//I found a variable that matches, load it's value into the return variable
					urlVarValue = urlVarPair[1];
					break;
				}
			}
		}
	}
	return urlVarValue;
}

function dumpProps(obj, parent) {
   // Go through all the properties of the passed-in object
   for (var i in obj) {
      // if a parent (2nd parameter) was passed in, then use that to
      // build the message. Message includes i (the object's property name)
      // then the object's property value on a new line
      if (parent) { var msg = parent + "." + i + "\n" + obj[i]; } else { var msg = i + "\n" + obj[i]; }
      // Display the message. If the user clicks "OK", then continue. If they
      // click "CANCEL" then quit this level of recursion
      if (!confirm(msg)) { return; }
      // If this property (i) is an object, then recursively process the object
      if (typeof obj[i] == "object") {
         if (parent) { dumpProps(obj[i], parent + "." + i); } else { dumpProps(obj[i], i); }
      }
   }
}

function isUnsignedInteger(s) {
	return (s.toString().search(/^[0-9]+$/) == 0);
}

function find_previous_day(dayIndex, myDate){
	while(myDate.getDay() != dayIndex){
		myDate.setDate(myDate.getDate()-1);
	}
	return myDate;
}

function getXMLObject(text){
	try { //Internet Explorer
		xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async="false";
		xmlDoc.loadXML(text);
		return xmlDoc;
	}catch(e){
		parser=new DOMParser();
		xmlDoc=parser.parseFromString(text,"text/xml");
		return xmlDoc;
	}
}

function goToToday(){
	var myDate= new Date();
	//alert(myDate);
	if(isSingle){
		update_date(find_previous_day(myDate.getDay(),myDate));
	}else{
		update_date(find_previous_day(1,myDate));
	}
}

function isArray(obj) {
   if (obj.constructor.toString().indexOf("Array") == -1)
      return false;
   else
      return true;
}


function offset_date(offset){
	var myDate= parse_start_date();
	myDate.setDate(myDate.getDate()+offset);
	update_date(myDate);

}


function parse_start_date(){
	var year = jQuery(".calendar_year:first").text();
	var myDate;
	for(var i=0; i<7; ++i){
		if(jQuery(".day_"+ i + ':first').text()){
			myDate= new Date(jQuery(".day_"+i+':first').text() + ", " + year);
			break;
		}
	}
	return myDate;
}

/*  Depreacted since month no longer by year*/
function parse_month_year(){
	return jQuery(".calendar_year:first").text().split(' ');
}

function parse_month_day(dayIndex){
	//alert(dayIndex);
	return jQuery(".day_" + dayIndex + ':first').text().split(' ');
}

function update_date(myDate){
	jQuery(".calendar_year:first").text(myDate.getFullYear());
	for(i=0;i<7;++i){
		if(jQuery(".day_" + i + ':first').text()){
			jQuery(".day_" + i).text(month_names[myDate.getMonth()] + ' ' + myDate.getDate());
			if(isSingle){
				jQuery(".day_" + i).parent().find('span.day').text(num2day[myDate.getDay()]);
			}
			myDate.setDate(myDate.getDate()+1);
		}
	}
}

function parse_date_single(){
	return new Date(jQuery('#single_date').text());
}

function update_single_date(myDate){
	//alert(num2day[myDate.getDay()] + ', ' + month_names[myDate.getMonth()] + ' ' + myDate.getDate() + ', ' + myDate.getFullYear());
	jQuery('#single_date').html(num2day[myDate.getDay()] + ' ' + month_names[myDate.getMonth()] + ' ' + myDate.getDate() + ', ' + myDate.getFullYear());
	return;
}

function single_change_date(offset){
	var currentDate = parse_date_single();
	currentDate.setDate(currentDate.getDate()+offset);
	//update_single_date(currentDate);
	newDateStr = num2day[currentDate.getDay()] + ' ' + month_names[currentDate.getMonth()] + ' ' + currentDate.getDate() + ', ' + currentDate.getFullYear();
	ajax_get_single_day(newDateStr);
	update_date(find_previous_day(1,currentDate));	
}
