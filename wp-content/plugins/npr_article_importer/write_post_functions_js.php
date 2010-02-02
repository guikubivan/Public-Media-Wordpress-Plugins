<?php
	require_once(dirname(__FILE__).'/../../../wp-load.php');
	if($story_id = $_GET['story_id']){
		if($_GET[type]=='minimal'){
			$url = "http://api.npr.org/query?id=$story_id&fields=title,teaser,byline&output=NPRML&apiKey=MDAyMjcyMzExMDEyMjYzNDYzMDI0MDk3Yw001";
			//echo $url;
			$outText = file_get_contents($url);
			if($outText){
				if(!preg_match("/There were no results that matched your query criteria/i",$outText) && !preg_match("/Requested List/i",$outText)){
					if(preg_match("/.*<nprml[^>]*>.*<list[^>]*>.*<story[^>]*>(.*)<\/story>.*<\/list>.*<\/nprml>.*/sim", $outText, $matches)){
						$story = $matches[1];
						/*preg_match("/<link type=\"html\">(.*)<\/link>/", $story, $link);
						$story2['link'] = trim($link[1]);
						*/
						preg_match("/<title>(.*)<\/title>/", $story, $title);
						$story2['title'] = trim($title[1]);

						preg_match("/<teaser[^>]*>\s*<!\[CDATA\[(.*)\]\]>\s*<\/teaser>/", $story, $teaser);
						$story2['teaser'] = html_entity_decode(trim($teaser[1]));

						/*
						preg_match("/<byline[^>]*>.*<name[^>]*>(.*)<\/name>.*<\/byline>/mis", $story, $author);
						$story2['author'] = trim($author[1]);
						*/

						die(json_encode($story2));

					}
				}else{
					die(json_encode(false));
				}
			}

			
			
		}
	}
	//require_once(dirname(__FILE__).'/../../../wp-admin/admin.php');
	header("Content-Type: text/javascript");
?>


jQuery(document).ready( function(){
	jQuery("#npr_importer_activate").click(function(){
		if( jQuery(this).attr('checked')){
			story_id = jQuery('#npr_article_id_input').val();
			//example story id: 120587627
			//alert('story id: ' + story_id);
			if(!story_id){
				alert("First please enter a valid story id.");
				jQuery(this).attr('checked', false);
				return;
			}
			url = "<?php bloginfo( 'wpurl' ); ?>/wp-content/plugins/npr_article_importer/write_post_functions_js.php?type=minimal&story_id="+ story_id;
			//alert(url);
			jQuery.getJSON(url, function(data){
				jQuery("#title").val(data.title);
				jQuery("#teaser").val(data.teaser);
			});
		}

	});

});

