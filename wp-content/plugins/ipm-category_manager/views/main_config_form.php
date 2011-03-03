		<style type="text/css">
			<?= $styles ?>	
			
			#catman_main table, #catman_relationships table
			{
				width: 100%;
				border-collapse: collapse;
			}
			#catman_main table td, #catman_relationships table td
			{
				padding: 4px;
			}
				
			#catman_wrapper th.cat_man_allow_multiple, #catman_wrapper th.cat_man_required
			{
				width: 10%;
			}
			#catman_wrapper th.cat_man_
			{
				width: 3ex;
			}
			#catman_wrapper th.cat_man_name, #catman_wrapper th.cat_man_key
			{
				width: 15%;
			}
			
			tr.alt_row
			{
				background-color: #EEEEEE;
			}
			
			.question
			{
				display: inline;
				display: inline-block;
				cursor: help;
				padding: 2px;
				margin: 2px;
				width: 1.3em;
				height: 1.3em;
				border: solid black 1px;
				-moz-border-radius: 20px;
			    -webkit-border-radius: 20px;
			    -khtml-border-radius: 20px;
			    border-radius: 20px;
			    text-align: center;
			    font-weight: bold;
			    background-color: white;

			}
		</style>
	<div class="wrap"><h2 style="display: none;" >TEST</h2></div>
	<div id="catman_wrapper">
		<div id="catman_tabs">
		     <ul>
			 <li><a href="#catman_main"><span>Main</span></a></li>
			 <li><a href="#catman_relationships"><span>Relationships</span></a></li>
			 <li><a href="#catman_mass_change"><span>Mass Change</span></a></li>
		     </ul>
		</div>

		<div id='catman_main'>
			<?= $config_form?>
		</div>
		<div id='catman_relationships'>
			<?= $relationships ?>
		</div>
		<div id='catman_mass_change'>
			<?= $masschange ?>
		</div>
		<script type='text/javascript'>
		 jQuery(document).ready(function(){
			jQuery('#catman_wrapper').tabs();
			//jQuery('#catman_tabs').tabs({ remote: true });
		});
		</script>
	</div>