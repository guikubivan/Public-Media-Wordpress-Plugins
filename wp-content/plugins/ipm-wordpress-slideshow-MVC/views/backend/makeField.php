		</td>
	</tr>
	<tr>
		<th class='label'>
			<label for='attachments[<?php echo $pid ?>][<?php echo $fieldname ?>]'>
				<span class='alignleft'><?php echo $fieldlable?></span>";
		<? if($req){ ?>
				<span class='alignright'>
					<abbr class='required' title='required'>*</abbr>
				</span>
		<?	} ?>
				<br class='clear'/>
			</label>
		</th>
		<td class='field'>
			<input type="text" name="attachments[<?php echo $pid ?>][<?php echo $fieldname ?>]" id="attachments[<?php echo $pid ?>][<?php echo $fieldname ?>]" />
