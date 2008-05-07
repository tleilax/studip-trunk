<?
	$cssSw = new cssClassSwitcher();
	$num = 0;	
?>
<tr>
	<td colspan="5" class="blank">
		<form action="<?= $GLOBALS['PHP_SELF'] ?>#<?= $role_data['id'] ?>" method="post">
		<table cellspacing="0" cellpadding="1" border="0" width="100%">
			<tr>
				<td class="printhead" colspan="2">
					&nbsp;<b><?= _("Neue Gruppe anlegen") ?></b>
				</td>
			</tr>
			<tr>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<font size="-1">
						<?= _("Gruppenname") ?>:
					</font>
				</td>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<font size="-1">
						<input type="text" name="new_name" value="<?= htmlReady($role_data['name']) ?>">
				</font>
				</td>
			</tr>						
			<? if ($range_type != 'sem') : ?>
			<? $cssSw->switchClass() ?>
			<tr>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<font size="-1">
						<?= _("�bergeordnete Gruppe") ?>:
					</font>
				</td>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<font size="-1">
						<select name="vather">
							<option value="root"> -- <?= _("Hauptebene") ?> -- </option> 
					 		<? Statusgruppe::displayOptionsForRoles($all_roles); ?>
						</select>
					</font>
				</td>
			</tr>
			<? endif; ?>

			<? $cssSw->switchClass() ?>
			<tr>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<font size="-1">
						<?= _("Gruppengr��e") ?>:
						&nbsp;<img style="{cursor:pointer; vertical-align:bottom;}" src="<?=$GLOBALS['ASSETS_URL']?>images/info.gif" <?=tooltip(_("Mit dem Feld 'Gruppengr��e' haben Sie die M�glichkeit, die Sollst�rke f�r eine Gruppe festzulegen. Dieser Wert wird nur f�r die Anzeige benutzt - es k�nnen auch mehr Personen eingetragen werden."), TRUE, TRUE)?>>
					</font>
				</td>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<input type="text" name="new_size" value="<?= $role_data['size'] ?>"><br/>
				</td>
			</tr>
			
			<? if ($range_type == 'sem') : ?>
			<? $cssSw->switchClass() ?>
			<tr>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<font size="-1">
						<?=_("Selbsteintrag") ?>:
					</font>
				</td>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<input type="checkbox" name="new_selfassign" value="1" <?= $role_data['selfassign']? 'checked="checked"' : '' ?>>
					<input type="hidden" name="vather" value="root">
				</td>
			</tr>

			<? $cssSw->switchClass() ?>
			<tr>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<font size="-1">
						<?=_("Gruppenordner:") ?>:
					</font>
				</td>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<input type="checkbox" name="groupfolder" value="1">
				</td>
			</tr>

			<? endif; ?>
			
			<? if ($range_type != 'sem' && is_array($role_data['datafields'])) foreach ($role_data['datafields'] as $field) : ?>
			<? $cssSw->switchClass() ?>
			<tr>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<?=$field['invalid']?'<font color="red" size="-1"><b>':'<font size="-1">'?>
					<?=$field['name']?>
					<?=$field['invalid']?'</b></font>':'</font>'?>
				</td>
				<td class="<?= $cssSw->getClass() ?>" width="50%" nowrap>
					<font size="-1">
						<?=$field['html']?>
					</font>
				</td>
			</tr>
			<input type="HIDDEN" name="datafield_id[]" value="<?= $field['datafield_id'] ?>">
			<input type="HIDDEN" name="datafield_type[]" value="<?= $field['datafield_type'] ?>">
			<input type="HIDDEN" name="datafield_sec_range_id[]" value="<?= $role->getId() ?>">
			<? endforeach; ?>
			<tr>
				<td class="blank" align="right" colspan="2">
					<br/>					
					<input type="image" <?= makebutton('speichern', 'src') ?> align="absbottom">
					&nbsp;
					<a href="<?= $GLOBALS['PHP_SELF'] ?>?range_id=<?= $range_id ?>">
						<?= makebutton('abbrechen') ?>
					</a>					
				</td>
			</tr>
		</table>
		<input type="hidden" name="cmd" value="addRole">
		<input type="hidden" name="role_id" value="<?= $role->getId() ?>">
		<input type="hidden" name="range_id" value="<?= $range_id ?>">
		</form>
	</td>
</tr>
