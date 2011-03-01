		</td>
	</tr>
	<tr>
		<th class='label'>
			<label for='attachments[<?= $pid ?>][<?= $fieldname ?>]'>
				<span class='alignleft'><?=$fieldlable?></span>";
		<? if($req){ ?>
				<span class='alignright'>
					<abbr class='required' title='required'>*</abbr>
				</span>
		<?	} ?>
				<br class='clear'/>
			</label>
		</th>
		<td class='field'>
			<input type="text" name="attachments[<?=$pid ?>][<?= $fieldname?>]" id="attachments[<?=$pid?>][<?= $fieldname ?>]" />
