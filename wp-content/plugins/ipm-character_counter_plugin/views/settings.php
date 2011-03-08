<div class='wrap'>

<h2>Character Count Settings</h2>
<?= $messages ?>
<br>
<form action='' method='post'>

<div>
	<p>
		<label>Use in pages: <input type='checkbox' name='field[in_pages]' <?= $fields[in_pages] ? 'checked' : '' ?> /> </label>
	</p>
</div>
<div>
	<p>
		<label>Activate Teaser: <input type='checkbox' name='field[teaser_activated]' <?= $fields[teaser_activated] ? 'checked' : '' ?> /> </label>
	</p>
</div>

<table style='width: 400px ?>' class='widefat'>
	<thead><tr> 
		<th>Field</th> 
		<th>Max/Min Characters</th> 
		<th>Optional?</th> 
	</tr></thead>
	<tr>
		<td>Title:</td>
		<td> <input name='field[title][min]' value='<?= $fields[title][min] ?>' size='3'> Min <input name='field[title][max]' value='<?= $fields[title][max] ?>' size='3'> Max</td>
		<td><input type='checkbox' name='field[title][optional]' <?= $fields[title][optional] ? 'checked' : '' ?> /> </td>
	</tr>
	<tr>
		<td>Teaser:</td>
		<td> <input name='field[teaser][min]' value='<?= $fields[teaser][min] ?>' size='3'> Min <input name='field[teaser][max]' value='<?= $fields[teaser][max] ?>' size='3'> Max</td>
		<td><input type='checkbox' name='field[teaser][optional]' <?= $fields[teaser][optional] ? 'checked' : '' ?> /> </td>
	</tr>
	<tr>
		<td>Excerpt:</td>
		<td> <input name='field[excerpt][min]' value='<?= $fields[excerpt][min] ?>' size='3'> Min <input name='field[excerpt][max]' value='<?= $fields[excerpt][max] ?>' size='3'> Max</td>
		<td><input type='checkbox' name='field[excerpt][optional]' <?= $fields[excerpt][optional] ? 'checked' : '' ?> /> </td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td ><input type='submit' value='Update' class='button' /> <input type='submit' name='reset' value='Defaults' class='button' /></td>
		<td>&nbsp;</td>
	</tr>
</table>
</form>
</div>