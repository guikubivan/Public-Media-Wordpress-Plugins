		<div class='wrap'>

		<h2>IPM Category Manager Configuration</h2>

		<?
		//echo $this->table_headers(array('&nbsp;', 'Name', 'Allow Multiple', 'Required', 'Categories')); ?>
		
		<form action='?page=<?= $_GET['page'] ?>&editdelete_group=true#catman_main' method='post'>
		<?
			foreach($this->category_bins as $catbin)
			{
				if($catbin->parent_id != null){
					continue;
				}

				$itemid = $catbin->id;
				if(!$catbin->id){
					$catbin->id = 'top';
				}
				?>
				
				<tr >
					<td >
					<?	if($catbin->id == 'top'){ ?>
							&nbsp;
					<?	}else{ ?>
						<input type='radio' name='<?=$this->prefix()?>group_id' value='<?=$catbin->id ?>' />
					<?	}  ?>
					</td>
					<td ><?= $catbin->name ?></td>
					<td style='text-align: center'>
						<?= $catbin->multiple_cats ? 'Yes' : 'No'?>
					</td>
					<td style='text-align: center'>
						<?= $catbin->required ? 'Yes' : 'No' ?>
					</td>
					<td style='text-align: center'>
						<?= $this->print_categories($catbin->get_cat_ids()) ?>
					</td>
				</tr>	
				<?	
				if($catbin->children !=null){
					foreach($child->children as $childname)
					{
						$child = $this->get_category_bin($childname);
						$itemid = $child->id;
						if(!$child->id){
							$child->id = 'top';
						}
						?>
						
						<tr >
							<td >
							<?	if($child->id == 'top'){ ?>
									&nbsp;
							<?	}else{ ?>
								<input type='radio' name='<?=$this->prefix()?>group_id' value='<?=$child->id ?>' />
							<?	}  ?>
							</td>
							<td style="padding-left:20px;"><?= $child->name ?></td>
							<td style='text-align: center'>
								<?= $child->multiple_cats ? 'Yes' : 'No'?>
							</td>
							<td style='text-align: center'>
								<?= $child->required ? 'Yes' : 'No' ?>
							</td>
							<td style='text-align: center'>
								<?= $this->print_categories($child->get_cat_ids()) ?>
							</td>
						</tr>	
					<?	
						
					}
				}
			}
			?>
			
		</table>
		<br /><input class='button' type='submit' name='group_edit' value='Edit selected group'/>
		&nbsp;&nbsp;&nbsp;<input class='button' type='submit' name='group_delete' value='Delete selected group'/>
		</form>
		</div>
