<? $search_exp = $GLOBALS['search_exp']; ?>
<form action="<?= $GLOBALS['PHP_SELF'] ?>" method="post" style="display: inline">	
	<?
	if ($search_exp) :
		$users = getSearchResults(trim($GLOBALS['search_exp']), $role_id);
		if ($users) : 
	?>
	<select name="persons_to_add[]" size="10" multiple style="width: 90%">
		<? foreach ($users as $user) : ?>
		<option value="<?= $user['username']?>">
			<?= htmlReady(my_substr($user['fullname'],0,35)) ?> (<?= $user['username'] ?>), <?= $user['perms'] ?>
		</option>
		<? endforeach; ?>
	</select>
	<input type="image" valign="bottom" name="search" src="<?= Assets::image_path('rewind.gif') ?>" border="0" value="<?=_("Personen suchen")?>" <?= tooltip(_("neue Suche")) ?>>&nbsp;
	<br/><br/>		
	<input type="hidden" name="cmd" value="addPersonsToRoleSearch">
	<input type="image" <?=makebutton('eintragen', 'src')?>>
	<br/>
		<? else : // no users there ?>
	<?= _("kein Treffer") ?>
	<input type="image" valign="bottom" name="search" src="<?= Assets::image_path('rewind.gif') ?>" border="0" value="<?=_("Personen suchen")?>" <?= tooltip(_("neue Suche")) ?>>&nbsp;
		<? endif; // users there? ?>
	<? else : ?>
		<input type="text" name="search_exp" value="" style="width: 90%">
		<input type="image" name="search" src="<?= Assets::image_path('suchen.gif') ?>" border="0" value="Personen suchen" <?= tooltip(_("Person suchen")) ?>>&nbsp;
		<br/><br/>
	<? endif;	?>
	<input type="hidden" name="role_id" value="<?= $role_id ?>">
	<input type="hidden" name="range_id" value="<?= $range_id ?>">
</form>