<?php

class playlist_parser {
	//var $path_prefix = '';
	var $verbosity = 0;
	var $doLog = 0;
	var $doPlayListXML = 1;

	var $start =1;
	var $limit = 200;


	var $playlist = array();
	var $reruns = array();
	var $playlistLink = '';

	function playlist_parser($playlistLink) {
		$this->playlistLink = $playlistLink;
		require_once(ABSPATH.PLUGINDIR.'/wfiu_playlist/parser/htmlparser.inc');
		require_once(ABSPATH.PLUGINDIR.'/wfiu_playlist/parser/myfunctions.inc');

	}

	function emptyItemArray(){
		return array('Name:' => '' ,
			'Name of CD:' => '',
			'Track Label:' => '',
			'Notes:' => '',
			'Release Year:' => '',
			'Trackname:'=>'');
	}

	//parse and return item array	
	function parse(){

		$START = 1;
		$END = 2;
		$TEXT = 3;
		$COMMENT = 4;
		$IMAGE = 5;
		$NULL_ELEMENT = "-999";

		$MAIN = 5;
		$PROCESSING = 6;
		$IDENTIFYING = 8;


		$status = $MAIN;
		$counter = 1;

		//assume rerun, if has items, then must not be
		$rerun = true;
			
		//echo "curent file: ". $this->playlistLink. "\n<br/>";
		$this->playlist = array();
		if($this->playlistLink && $playlistParser = HtmlParser_ForFile($this->playlistLink) ){
			$PSTATUS = 0;
			$trackName = '';
			$artist = '';
			$cdName = '';
			$label = '';
			$year = '';

			$properties = $this->emptyItemArray();
			$tags = array_keys($properties);	
			

			$playlistItems = "";

			while ( $playlistParser->parse() ) {
				//echo 'pstatus: ' . $PSTATUS . "\n";
				$element = strtolower($playlistParser->iNodeName);
				//echo 'element: ' . $element . "\n";
				$type = $playlistParser->iNodeType;
				$value = trim($playlistParser->iNodeValue);
				$myArray = $playlistParser->iNodeAttributes;
		

				if($element == "table" &&  $type == $START){// && strtolower($myArray['class']) == 'playlist3' ){
					///echo $element . $type . $myArray['class'] . "\n";
					//print_r($myArray);
					if($PSTATUS <2){
						$PSTATUS = $PSTATUS + 1;
					}
				}

				if($element == "table" && $type == $END ){
					if($PSTATUS >0){
						$PSTATUS = $PSTATUS - 1;
					}
				}

				//echo 'pstatus: ' . $PSTATUS . "\n";
				//echo 'element: ' . $element . "\n";

				if($element == "th" && $PSTATUS == 2){
					//print_r($myArray);
					$playlistParser->parse();
					$playlistParser->parse();
					$playlistParser->parse();
			
					$trackName = trim($playlistParser->iNodeValue);
					if( !$trackName ){
						$playlistParser->parse();
						$trackName = trim($playlistParser->iNodeValue);
					}
					if ($this->verbosity) echo "Trackname: " .  $trackName;
					$properties['Trackname:'] = $trackName;
				
					while( $element != "img" || !preg_match("/amazon.gif/", strtolower($myArray['src']) ) ){
						$playlistParser->parse();
						$myArray = $playlistParser->iNodeAttributes;
						$element = strtolower($playlistParser->iNodeName);
						
						$type = $playlistParser->iNodeType;
						$value = cleanText($playlistParser->iNodeValue);
						//echo "value: |" . $value."|\n<br/>";
						$index = array_search($value, $tags);
						if ($type == $TEXT && $PSTATUS ==2 && ($index !== false) ){
							//echo "$value\n<br/>";
							$playlistParser->parse();
							$playlistParser->parse();
							$playlistParser->parse();
							$propValue = trim($playlistParser->iNodeValue);
							//echo 'propovalue: ' . $propValue . "\n";
							//$index = $index -1;
							$properties[$tags[$index]]=$propValue;
						}

					}

					if(($nitems = arrayIsComplete($properties))>0){
						$rerun = false;
						if($this->verbosity)echo "...OK " . $nitems . "\n";
						$this->playlist[] = $properties;
						//print_r($properties);
						
					}else{
						echo "...ERROR:\n";
						print_r($properties);
						//return false;
					}
					$properties = $this->emptyItemArray();
					
				}
			}
		}
		if(sizeof($this->playlist)>0){
			return $this->playlist;
		}else{
			return false;
		}

	}

}




?>
