			
			<span class="question"
			title="All posts currently containing the selected category will be added to or removed from the categories selected on the next page."
			>&#10082;</span>
			Select which group of posts to change category:
			<br/>
			<form action='?page=<?= $_GET['page'] ?>&categories_choose=true#catman_mass_change' method='post'>
				<?= $category_dropdown ?>
				<div style="width:500px;clear:both;text-align:center"><input type="submit" value="Continue" /></div>
			</form>