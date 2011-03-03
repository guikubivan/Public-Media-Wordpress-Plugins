			
			<span class="question"
			title="All posts currently containing the category '<?=$cobj->name?>' will be added to or removed from the categories selected below."
			>&#10082;</span>
			Select categories to assign to all posts of category <b><?=$cobj->name?></b> :
			<br/>
			
			<form action='?page=<?=$_GET['page']?>&categories_set=true#catman_mass_change' method='post'>
				<table style="text-align:center">
					<tr>
						<th>&nbsp;</th>
						<th>Add |</th>
						<th>Remove |</th>
						<th>Do nothing</th>
					</tr>
			<?	
			$alt = "alt_row";
			foreach ($categories as $cat) {
					$alt = empty($alt)?"alt_row":"";
					$style='';
					/*if($cat->parent != 0){
						$style='padding-left:13px;';
					}*/
			?>
					<tr class="<?=$alt?>" >
						<td style='text-align:left;<?=$style?>'><b><?= $cat->name ?></b></td>
						<td><input type="radio" name="<?= $cat->term_id ?>" value="set"> </td>
						<td><input type="radio" name="<?= $cat->term_id ?>" value="unset"></td>
						<td><input type="radio" name="<?= $cat->term_id ?>" value="donothing" checked></td>
					</tr>
			<?
				}
			?>
			</table>
			<div style="width:500px;clear:both;text-align:center"><input type="submit" value="Mass change categories" /></div>
			<INPUT TYPE="hidden" NAME="chosen_category" VALUE="<?=$_POST['cat']?>">
		</form>