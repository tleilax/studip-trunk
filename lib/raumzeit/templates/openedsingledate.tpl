<? if (!$tpl['deleted']) : ?>
<TR>
	<TD class="printcontent" colspan="9">
		<A name="<?=$tpl['sd_id']?>">
		<TABLE cellpadding="2" cellspacing="0" border="0" width="100%">
			<TR>
				<TD width="2%" align="left" valign="top" class="<?=$tpl['class']?>">
					<A href="<?=$PHP_SELF?>?cmd=close&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgraurunt.gif" border="0">
					</A>
				</TD>
				<TD width="43%" nowrap class="<?=$tpl['class']?>">
					<FONT size="-1">
						<INPUT type="text" id="day" name="day" maxlength="2" size="2" value="<?=$tpl['day']?>">.
						<INPUT type="text" id="month" name="month" maxlength="2" size="2" value="<?=$tpl['month']?>">.
						<INPUT type="text" id="year" name="year" maxlength="4" size="4" value="<?=$tpl['year']?>">
						<B><?=_("von")?></B>
						<INPUT type="text" id="start_stunde" name="start_stunde" maxlength="2" size="2" value="<?=$tpl['start_stunde']?>">:
						<INPUT type="text" id="start_minute" name="start_minute" maxlength="2" size="2" value="<?=$tpl['start_minute']?>">
						<B><?=_("bis")?></B>
						<INPUT type="text" id="end_stunde" name="end_stunde" maxlength="2" size="2" value="<?=$tpl['end_stunde']?>">:
						<INPUT type="text" id="end_minute" name="end_minute" maxlength="2" size="2" value="<?=$tpl['end_minute']?>">&nbsp;<?=_("Uhr")?>
					</FONT>
					<?=Termin_Eingabe_javascript(1,0,mktime(12,0,0,$tpl['month'],$tpl['day'],$tpl['year']),$tpl['start_stunde'],$tpl['start_minute'],$tpl['end_stunde'],$tpl['end_minute']);?>
				</TD>
				<TD width="45%" nowrap class="<?=$tpl['class']?>">
				  <FONT size="-1"<?=($tpl['class'] == 'steelred') ? ' color="#000000"' : ''?>>
			      <?=$tpl['room']?>
			    </FONT>
					<? if ($tpl['ausruf']) { ?>
					<A href="javascript:;" onClick="alert('<?=$tpl['ausruf']?>')">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/ausrufezeichen_rot.gif" alt="<?=$tpl['ausruf']?>" border="0" align="absmiddle">
					</A>
					<? } ?>
				</TD>
				<TD width="5%" class="<?=$tpl['class']?>" align="right">
					<A href="<?=$PHP_SELF?>?cmd=delete_singledate&sd_id=<?=$tpl['sd_id']?>&cycle_id=<?=($tpl['cycle_id']) ? $tpl['cycle_id'] : '' ?>">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/trash.gif" border="0" align="absmiddle">
					</A>
				</TD>
			</TR>
			<TR>
				<TD class="printcontent">&nbsp;</TD>
				<TD class="printcontent" colspan="2">
					<FONT size="-1">
					<? if ($GLOBALS['RESOURCES_ENABLE']) { ?>
					<?=_("Raum:")?>
					<SELECT name="room_sd">
						<OPTION value="nochange" selected><?=_("keine &Auml;nderung")?></option>
						<OPTION value="retreat"><?=_("Raumbuchung aufheben")?></option>
						<OPTION value="nothing"><?=_("keine Buchung, nur Textangabe")?></option>
						<?
						$resList->reset();
						if ($resList->numberOfRooms()) {
							while ($res = $resList->next()) {
								echo '<OPTION value="'.$res['resource_id'].'">'.my_substr(htmlReady($res["name"]), 0, 30)."</OPTION>\n";
							}
						}
						?>
					</SELECT>
					<BR/>
					<? } ?>
					<?=_("freie Raumangabe:")?>
					<INPUT type="text" name="freeRoomText_sd" size="50" maxlength="255" value="<?=$tpl['freeRoomText']?>">
					<?=$GLOBALS['RESOURCES_ENABLE'] ? _("(f&uuml;hrt <em>nicht</em> zu einer Raumbuchung)"): ''?><BR/>
					<? if ($GLOBALS['RESOURCES_ENABLE'] && $GLOBALS['RESOURCES_ALLOW_ROOM_REQUESTS']) { ?>
					<?=_("Raumanfrage")?>
					<A href="admin_room_requests.php?seminar_id=<?=$tpl['seminar_id']?>&termin_id=<?=$tpl['sd_id']?>">
						<IMG <?=($tpl['room_request']) ? makebutton('bearbeiten', 'src') : makebutton('erstellen', 'src')?> border="0" align="absmiddle">
					</A>
					<? if ($tpl['room_request']) { ?>
					<?=_("oder")?>
					<A href="<?=$PHP_SELF?>?cmd=removeRequest&cycle_id=<?=$tpl['cycle_id']?>&singleDateID=<?=$tpl['sd_id']?>">
						<IMG <?=($tpl['room_request']) ? makebutton('zurueckziehen', 'src') : ''?> border="0" align="absmiddle">
					</A>
					<? } ?>
					<BR/>
					<? } ?>
					</FONT>
				</TD>
				<TD class="printcontent" valign="top" colspan="2" align="right" nowrap>
					<FONT size="-1">
					<?=_("Art:")?>
					<SELECT name="dateType">
					<?
					if (!$tpl['type']) $tpl['type'] = 1;
					foreach ($TERMIN_TYP as $key => $val) {
						echo '<OPTION value="'.$key.'"';
						if ($tpl['type'] == $key) {
							echo ' selected';
						}
						echo '>'.$val['name']."</OPTION>\n";
					}
					?>
					</SELECT>
					</FONT>
				</TD>
			</TR>
			<TR>
				<TD align="center" class="printcontent" colspan="4" style="text-align: center">
					<INPUT type="hidden" name="cmd" value="doAddSingleDate">
					<INPUT type="image" <?=makebutton('uebernehmen', 'src')?>>
					<A href="<?=$PHP_SELF?>?cmd=close&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
						<IMG <?=makebutton('abbrechen', 'src')?> border="0">
					</A>
				</TD>
			</TR>
		</TABLE>
		<INPUT type="hidden" name="cmd" value="editSingleDate">
		<INPUT type="hidden" name="singleDateID" value="<?=$tpl['sd_id']?>">
		<? if ($tpl['cycle_id']) { ?>
		<INPUT type="hidden" name="cycle_id" value="<?=$tpl['cycle_id']?>">
		<? } ?>
	</TD>
</TR>
<? else : ?>
<tr>
	<td class="printcontent" colspan="9">
		<a name="<?=$tpl['sd_id']?>">
		<table cellpadding="2" cellspacing="0" border="0" width="100%">
			<tr>
				<td width="2%" align="left" valign="center" class="<?=$tpl['class']?>" nowrap="nowrap">
					<a href="<?=$PHP_SELF?>?cmd=close&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
						<img src="<?=$GLOBALS['ASSETS_URL']?>images/forumgraurunt.gif" border="0" align="abstop">
					</a>
				</td>
				<td width="43%" nowrap class="<?=$tpl['class']?>">
					<font size="-1" color="#666666">
						<?=$tpl['date']?>
					</font>
				</td>
				<td width="30%" nowrap class="<?=$tpl['class']?>">
					<font size="-1" color="#666666">
						<?=$tpl['room']?>
					</font>
				</td>
				<td width="20%" nowrap class="<?=$tpl['class']?>" align="right">
					<? if (!$_LOCKED) { ?>
						<a href="<?=$PHP_SELF?>?cmd=undelete_singledate&sd_id=<?=$tpl['sd_id']?>&cycle_id=<?=($tpl['cycle_id']) ? $tpl['cycle_id'] : '' ?>">
							<img src="<?=$GLOBALS['ASSETS_URL']?>images/untrash.gif" border="0" align="absmiddle">
						</a>
					<? } ?>
				</td>
			</tr>
			<tr>
				<td width="5%" valign="top" class="printcontent">
				</td>
				<td valign="top" class="printcontent" colspan="10">
					<font size="-1">
						<?=_("Der hier eingegebene Kommentar wird im Ablaufplan der Veranstaltung angezeigt.")?><br/>
						<br/>
						<?=_("Kommentar")?>: <input type="text" name="comment" size="50" value="<?=$tpl['comment']?>">
					</font>
				</td>
			</tr>
			<tr>
				<td align="center" class="printcontent" colspan="4" style="text-align: center">
					<input type="image" <?=makebutton('uebernehmen', 'src')?>>
					<a href="<?=$PHP_SELF?>?cmd=close&open_close_id=<?=$tpl['sd_id']?>#<?=$tpl['sd_id']?>">
						<img <?=makebutton('abbrechen', 'src')?> border="0">
					</a>
				</td>
			</tr>
		</table>
		<input type="hidden" name="cmd" value="editDeletedSingleDate">
		<input type="hidden" name="singleDateID" value="<?=$tpl['sd_id']?>">
		<? if ($tpl['cycle_id']) { ?>
		<input type="hidden" name="cycle_id" value="<?=$tpl['cycle_id']?>">
		<? } ?>	
	</td>
</tr>
<? endif; ?>
