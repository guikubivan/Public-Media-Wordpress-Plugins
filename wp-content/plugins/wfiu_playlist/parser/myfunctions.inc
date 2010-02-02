<?php
//Needed functions for PHP < 5
//========================================
if(!function_exists('str_ireplace')) {
	function str_ireplace($search,$replace,$subject) {
		$search = preg_quote($search, "/");
		return preg_replace("/".$search."/i", $replace, $subject);
	}
}



if(!function_exists('get_headers'))
{
function get_headers($url,$format=0) {
       $url_info=parse_url($url);
       $port = isset($url_info['port']) ? $url_info['port'] : 80;
       $fp=fsockopen($url_info['host'], $port, $errno, $errstr, 30);
       if($fp) {
           if(!$url_info['path']){
                         $url_info['path'] = "/";
                     }
                     if($url_info['path'] && !$url_info['host']){
                        $url_info['host'] = $url_info['path'];
                        $url_info['path'] = "/";
                     }
                     if( $url_info['host'][(strlen($url_info['host'])-1)] == "/" ){
                        $url_info['host'][(strlen($url_info['host'])-1)] = "";
                     }
                     if(!$url_array[scheme]){
                         $url_array[scheme] = "http"; //we always use http links
                        }
                     $head = "HEAD ".@$url_info['path'];
                     if( $url_info['query'] ){
                         $head .= "?".@$url_info['query'];
                        }
                        //print_r($url_info);
           $head .= " HTTP/1.0\r\nHost: ".@$url_info['host']."\r\n\r\n";
           //echo $head;
                     fputs($fp, $head);
           while(!feof($fp)) {
               if($header=trim(fgets($fp, 1024))) {
                   if($format == 1) {
                       $h2 = explode(':',$header);
                       // the first element is the http header type, such as HTTP/1.1 200 OK,
                       // it doesn't have a separate name, so we have to check for it.
                       if($h2[0] == $header) {
                           $headers['status'] = $header;
                       }
                       else {
                           $headers[strtolower($h2[0])] = trim($h2[1]);
                       }
                   }
                   else {
                       $headers[] = $header;
                   }
               }
           }
           return $headers;
       }
       else {
           return false;
       }
   }
}


//=============================
function getURLLastMod($link){
	$a= get_headers($link,0);

	$audioLastModified =trim(substr($a[3], 14));
	$audioLastModified =  strtotime($audioLastModified);
	return $audioLastModified;
}
//================================

if(!function_exists('str_ireplace')) {
	function str_ireplace($search,$replace,$subject) {
		$search = preg_quote($search, "/");
		return preg_replace("/".$search."/i", $replace, $subject); 
	}
}

function url_exists($f = NULL) {
	if(!$fp = @ fopen ($f, "r")){
		return false;
	}
	fclose ($fp);
	return true;	
}

function url_exists2($f = NULL) {
  $h = array();
  $ret = FALSE;
  if(!is_null($f)):
    if(preg_match('/^http|https|ftp/',$f)): //test protocol EXTERN
      $h = @get_headers($f);
      if(@array_key_exists(0,$h)) :
        $ret = (bool) preg_match('/200|301/',$h[0]); /* HTTP/1.1 301 DAP (directory) */
      endif;
    else: //else FS
      $ret = (file_exists($f) && is_readable($f));
    endif;
  endif;
 
  return (($ret === TRUE) ? TRUE : FALSE);
}

//Find last ocurrence of string $needle in $haystack
function strlpos($haystack, $needle, $offset=0) {
// same as strrpos, except $needle can be a string
	$strrpos = false;
	if (is_string($haystack) && is_string($needle) && is_numeric($offset)) {
		$strlen = strlen($haystack);
		$strpos = strpos(strrev(substr($haystack, $offset)), strrev($needle));
		if (is_numeric($strpos)) {
			$strrpos = $strlen - $strpos - strlen($needle);
		}
	}
	return $strrpos;
}





//============================================
//Formats a tag labeled as $name correctly,
//adding a backslash to closing tags, and adding it's attributes
function getTag($type,$name, $attrib){
	global $END, $IMAGE;
	//$END = 2;

	$retString = "<";
	if($type == $END){
		$retString .= "/";
	}
	$retString .= $name;
	$a_Keys = array_keys($attrib);
	foreach($a_Keys as $s_Key) {
		$retString .= " " . $s_Key .  "=\"" . $attrib[$s_Key] . "\"";
	}
	
	if($type == $IMAGE){
		$retString .= "/";
	}
	
	$retString .= ">";
  return $retString;
}
//========================================
//Save the full path to the current file... 
//but don't neeed!!!  tooo late, I'll leave it here anyways
function urlPath() { 
	$_SERVER['FULL_PATH'] = 'http';
	if($_SERVER['HTTPS']=='on'){$_SERVER['FULL_PATH'] .=  's';}
	$_SERVER['FULL_PATH'] .=  '://';
	if($_SERVER['SERVER_PORT']!='80')
		$_SERVER['FULL_PATH'] .=  $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].$_SERVER['SCRIPT_NAME'];
	else
		$_SERVER['FULL_PATH'] .=  $_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
} 

function cleanText($someText){
	$cleanText = str_ireplace("&nbsp;", " ", $someText);	
	$cleanText = ereg_replace("\n", " ", $cleanText);
	$cleanText = trim($cleanText);
	$cleanText = ereg_replace("  +", " ", $cleanText);
	return $cleanText;
}

//function that makes sure there is a value for every key
function arrayIsComplete($array){
	$count = 0;
	foreach(array_keys($array) as $key){
		//echo $key;
		if($array[$key]){
			$count +=1;
		}
		
	}
	return $count;
}

function printProperties($parserObject){
	$element = strtolower($parserObject->iNodeName);
	$type = $parserObject->iNodeType;
	$value = trim($parserObject->iNodeValue);
	$myArray = $parserObject->iNodeAttributes;
	echo $element. "(".$type.") - ". $value."\n";
	print_r($myArray);
}


//echo "strcasecmp: ". strcasecmp("95%", "95%"). "\n";
function getResults(){
	//echo "*True\n";
	return true;
}
function testProperties($parserObject, $myelement, $props=null, $values=null){
	global $END, $START, $TEXT;
	$mytype = '';
	if(!preg_match("/text/i", $myelement) ){
		$mytype = preg_match("/\/(.*)/", $myelement,$matches) ? $END:$START;
		if(sizeof($matches)>1) $myelement=$matches[1];
	}else{

		$mytype = $TEXT;
		$myelement = "text";
	}
	$element = strtolower($parserObject->iNodeName);
	$type = $parserObject->iNodeType;
	$nodeValue = cleanText($parserObject->iNodeValue);
	
	if(!preg_match("/".$myelement."/i", $element) || $mytype!=$type){

		return false;
	}elseif($props==null && $values==null){return getResults();}
	
	$myArray = $parserObject->iNodeAttributes;

	if(sizeof($props)==1){
		if($values==null){		
			if($myArray[$props]){
				//echo $myArray[$props];
				return $myArray[$props];
			
			}else{
				return false;
			}
		}else if(sizeof($values)<=1){

			//echo $values . "-".$props."-".$myArray[$props]."\n";
			if(!preg_match("/".preg_quote($values, '/')."/i", $myArray[$props])){
				return false;			
			}else{
				return $myArray[$props];
			}
		}else{
			foreach($values as $val){
				//echo "a2";
				//echo $val . "\n";
				if(preg_match("/".preg_quote($val,'/')."/i", $myArray[$props])){
					return $myArray[$props];
				}
				
			}
			return false;
		}
	}else if($props !=null){
		for($i=0;$i<sizeof($props);$i++){
			if(!preg_match("/".$values[$i]."/i", $myArray[$props[$i]]) ){
				//echo "b1";
				return false;
			}
		}
	}else{
		if(sizeof($values)<=1){			
			//echo $values . "vs ". cleanText($parserObject->iNodeValue)."\n";
			if(!preg_match("/".preg_quote($values, '/')."/i", $nodeValue)){
				return false;		
			}
		
		}else{
			foreach($values as $val){
				//echo "a2";
				//echo $val . "\n";
				if(preg_match("/".preg_quote($val,'/')."/i", $nodeValue)){
					return getResults();
				}
				
			}
			return false;
		}
	}

	return getResults();
}

function str_word_count2($word_string, $format = null)
{

	if ($format !== 1 && $format !== 2 && $format !== null) {
	    user_error('str_word_count() The specified format parameter, "' . $format . '" is invalid',
		E_USER_WARNING);
	    return false;
	}

	//$word_string = preg_replace('/[0-9]+/', '', $string);//Pablo edit, so it can include numbers
	$word_array  = preg_split('/[^A-Za-z0-9_\']+/', $word_string, -1, PREG_SPLIT_NO_EMPTY);
	
	switch ($format) {
	    case null:
		$result = count($word_array);
		break;

	    case 1:
		$result = $word_array;
		break;

	    case 2:
		$lastmatch = 0;
		$word_assoc = array();
		foreach ($word_array as $word) {
		    $word_assoc[$lastmatch = strpos($string, $word, $lastmatch)] = $word;
		    $lastmatch += strlen($word);
		}
		$result = $word_assoc;
		break;
	}

	return $result;
}


function simplifyTitle($string,$limit=3){
	$string = strtolower($string);
	$string = trim(str_replace('-', ' ', $string));

/*	preg_match_all("/([^a-zA-Z]?[0-9]+[^a-zA-Z])/",$string, $matches);
	if(sizeof($matches)>0 && $matches[0]){
		print_r($matches);
	}
*/	

	$words = str_word_count2($string, 1);
	//print_r($words);
	$uselesschars = array('the', 'to','in', 'a','and','of','an');
	$newstr = '';
	if(sizeof($words)>0){
		$count=0;
		foreach($words as $w){
			if($count==$limit)break;
			if(!in_array($w, $uselesschars) ){
				++$count;
				$newstr .= $w . ' ';
			}
			
		}
	}else{
		//echo "Error in simplifyTitle: |$string|\n";
		$newstr = $string;
	}

	return trim($newstr);
}

function simplifyLink($link){
	$pinfo = pathinfo($link);
	$newString = str_replace(".".$pinfo['extension'],'',$pinfo['basename']);
	$newString = preg_replace("/^[0-9]+[_|-]/",'',$newString);
	$newString = str_replace(array("-","_"),'',$newString);
	return $newString;
}

?>
