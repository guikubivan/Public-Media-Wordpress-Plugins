<?php
/* 
Plugin Name: Override Scheduling
Plugin URI: http://wfiu.org
Version: 1.0
Description: Allows post to published, even if date is in the future...
Author: Pablo Vanwoerkom
Author URI: http://www.wfiu.org
*/

//****TO-DO***********
// somehow shows that posts are force published


//********************

//require_once(ABSPATH.'wp-includes/formatting.php');
/*
if(!class_exists('schedule_override')) {
	require_once(ABSPATH.PLUGINDIR.'/schedule_override/functions.php');
	require_once(ABSPATH.PLUGINDIR.'/schedule_override/schedule_override_classes.php');

	//echo "<h1>sdklfj</h1>";
	//print_r(get_declared_classes());
	$schedule_override  =  new schedule_override('Picture Manager');
}
*/

function override_schedule($post_id, $post){
	global $wpdb;
	if(wp_is_post_revision($post_id)) { 
		return $post_id;
	}
	
	if ($post->post_status == 'inherit' || $post->post_type != 'post' )
			return;

	$pstat = $_POST['force_publish'];
	
	if(isset($pstat) && $pstat){
		//echo $post_id . "<br />".$post->post_parent ."<br />";
		//$query = "UPDATE $wpdb->posts SET post_content = \"".addslashes($content)."\" WHERE ID=$post_id;";
		$pname ='';
		if(!$post->post_name){
			$pname = ", post_name=\"" . sanitize_title($post->post_title)."\" ";
			//echo $pname;
		}
		
		$query = "UPDATE $wpdb->posts SET post_status = 'publish' $pname WHERE ID=$post_id;";
		$wpdb->query($query);
	}
}
//save and delete post hook
add_action('wp_insert_post', 'override_schedule', 1 , 2);
//add_action('save_post', 'override_schedule', 1 , 2);
//add_action('delete_post', array(&$schedule_override,'deleteItem'));



//add_action('simple_edit_form', 'update_publish_status');
//add_action('edit_form_advanced', 'update_publish_status'));
//add_action('edit_page_form', 'update_publish_status');
//*******************CONTENT FUNCTIONS***********************
//add_action('the_content', array(&$schedule_override, 'insert_content')); //used a tag isntead
//*******************************************
function add_schedule_override(){
	global $post, $wpdb;

	$thispost= get_post($post->ID);

	if((strtotime($thispost->post_date) > time()) && ($thispost->post_status =='publish')){
	?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				<?php if(get_bloginfo('version')>= 2.7 ){ ?>
					jQuery('#publish').attr('value','Schedule');
					jQuery('#publishing-action').append("<input type='submit' class='button-primary' name='force_publish' value='Update Post' />");
				<?php }else{ ?>
					jQuery('#save-post').attr('value','Schedule');
					jQuery('#save-post').parent().append("<input type='submit' class='button' name='force_publish' value='Save' />");
				
				<?php } ?>
 			});
		
		
		</script>	
	<?php
	}else{
	
	?>
		<script type="text/javascript">
jQuery(document).ready(function(){	
	var x='lkj';

	<?php if(get_bloginfo('version')>= 2.7 ){ ?>
		jQuery('#publishing-action').append("<input type='submit' class='button-primary' name='force_publish' value='Force Publish' />");
	<?php }else{ ?>
		jQuery('#save-post').parent().append("<input type='submit' class='button-primary' name='force_publish' value='Force Publish' />");
	<?php } ?>
});
		
		
		</script>	
	<?php
	}
	return;

}


function add_schedule_override_old(){
?>


	<script type="text/javascript">
		function renamePublish(theSel){
		    for(i=0; i<theSel.length; i++)   {

		      if (theSel.options[i].value == 'publish') {
			//alert(theSel.options[i].value);
			theSel.options[i].innerHTML = 'Publish/Schedule';	
		      }
		    }

		}


		function appendOldSchool(theSel, newText, newValue){
		
		  if (theSel.length == 0) {
		    var newOpt1 = new Option(newText, newValue);
		    theSel.options[0] = newOpt1;
		    theSel.selectedIndex = 0;
		  } else if (theSel.selectedIndex != -1) {
		    var selText = new Array();
		    var selValues = new Array();
		    var selIsSel = new Array();
		    var newCount = -1;
		    var newSelected = -1;
		    var i;
		    for(i=0; i<theSel.length; i++)
		    {
		      newCount++;
		      selText[newCount] = theSel.options[i].text;
		      selValues[newCount] = theSel.options[i].value;
		      selIsSel[newCount] = theSel.options[i].selected;
		      
		      if (newCount == theSel.selectedIndex) {
			newCount++;
			selText[newCount] = newText;
			selValues[newCount] = newValue;
			selIsSel[newCount] = false;
			newSelected = newCount - 1;
		      }
		    }
		    for(i=0; i<=newCount; i++)
		    {
		      var newOpt = new Option(selText[i], selValues[i]);
		      theSel.options[i] = newOpt;
		      theSel.options[i].selected = selIsSel[i];
		    }
		  }
		}

  
	      jQuery(document).ready(function() {
			postStatusElement = document.getElementById("post_status");
			if(postStatusElement != null){
		  		appendOldSchool(postStatusElement,'Force Publish','force');
				renamePublish(postStatusElement);
				pub_button = document.getElementById("publish");
				if(pub_button != null){
					if ( pub_button.parentNode && pub_button.parentNode.removeChild ) {
						pub_button.parentNode.removeChild(pub_button);
					}
				}
			}
			//alert(pub_button);
			//document.remove(pub_button);
			//pub_button = null;
	       });



	</script>

<?php
}

add_action('admin_head', 'add_schedule_override' );
/*add_action('simple_edit_form', 'add_schedule_override' );
add_action('edit_form_advanced', 'add_schedule_override' );
add_action('edit_page_form', 'add_schedule_override' );
*/

?>
