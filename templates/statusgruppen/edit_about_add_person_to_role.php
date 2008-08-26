<tr>
	<td class="steelkante">&nbsp;<b><?=_("Person einer Gruppe zuordnen")?></b></td>
</tr>
<tr>
	<td class="blank" valign="top">
		<div style="padding-left: 10px; background-image: url('<?=$GLOBALS['ASSETS_URL']?>/images/steel1.jpg');">
		<form action="<?= URLHelper::getLink('?view=Karriere') ?>" method="post">
			<br/>			
			<? if (!$subview_id || !($groups = GetAllStatusgruppen($subview_id))) { ?>
			<?=_("Einrichtung ausw�hlen")?>:<br/>
			<select name="subview_id">						
				<option value="NULL"><?=_("-- bitte Einrichtung ausw&auml;hlen --")?></option>
				<? if (is_array($admin_insts)) foreach ($admin_insts as $data) : ?>
				<option value="<?=$data['Institut_id']?>" style="<?=($data["is_fak"] ? "font-weight:bold;" : "")?>" <?=($subview_id==$data['Institut_id'])? 'selected="selected"':''?>><?=htmlReady(substr($data["Name"], 0, 70))?></option>
				<?
					if ($data["is_fak"]) foreach ($sub_admin_insts as $sub_data) : ?>
						<option <?= ($subview_id == $sub_data['Institut_id']) ? 'selected="selected"' : '' ?> value="<?= $sub_data['Institut_id'] ?>">&nbsp;&nbsp;&nbsp;&nbsp;<?= htmlReady(substr($sub_data['Name'], 0, 70)) ?></option>
					<? endforeach;
				endforeach;
			?>
			</select>
			<br/>
			<input type="image" <?=makeButton('anzeigen','src')?>>
			<?
			} else {
				$data = $admin_insts[$subview_id];
				echo _("Einrichtung") . ':&nbsp;<i>' . $data['Name'] . '</i>';
			?>
				<a href="<?= URLHelper::getLink('?view=Daten&subview=AddPersonToRole&username='. $username) ?>">
					<img src="<?=$GLOBALS['ASSETS_URL']?>/images/rewind.gif" border="0">
				</a>
				<br/><br/>
				<?=_("Funktion ausw�hlen")?>:<br/>
				<select name="role_id">
				<?
				Statusgruppe::displayOptionsForRoles($groups);
				//displayChildsSelectBox($groups);
				?>
				</select>
				<br/>
				<input type="hidden" name="studipticket" value="<?= get_ticket() ?>">
				<input type="hidden" name="subview_id" value="<?=$subview_id?>">
				<input type="hidden" name="cmd" value="addToGroup">
				<input type="image" <?=makeButton('zuordnen','src')?>>
			<?
			}
			if ($subview_id && !$groups) :
				echo '<br/><font color="red">' . _("In dieser Einrichtung gibt es keine Gruppen!") . '</font>';
			endif;
			?>
			<input type="hidden" name="view" value="Karriere">
			<input type="hidden" name="subview" value="addPersonToRole">
			<input type="hidden" name="studipticket" value="<?=get_ticket()?>">
			<input type="hidden" name="username" value="<?=$username?>">
		</form>
	</div> 
	</td>
</tr>
