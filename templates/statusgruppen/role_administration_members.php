<form action="<?= URLHelper::getLink('') ?>" method="post" style="display: inline">
	<select size="10" name="persons_to_add[]" multiple="multiple" style="width:100%">
	<? if (is_array($inst_persons)) foreach ($inst_persons as $key => $val) : ?>
		<option <?=($val['hasgroup'])?'style="{color: #777777}"':''?> value="<?=$val['username']?>">
		<?=htmlReady(my_substr($val['fullname'], 0, 20))?> (<?=$val['username']?>) - <?=$val['perms']?>
		</option>
	<? endforeach; ?>
	</select><br>
	<br>
	<? if ($indirect) : ?>
	<input type="hidden" name="cmd" value="addPersonsToRoleIndirect">
	<? else : ?>
	<input type="hidden" name="cmd" value="addPersonsToRoleDirect">
	<? endif; ?>
	<input type="hidden" name="role_id" value="<?= $role_id ?>">
	<input type="image" <?= makebutton('eintragen', 'src') ?>>
	<input type="hidden" name="range_id" value="<?= $range_id ?>">
</form>
