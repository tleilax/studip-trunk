<?
if (!$sd_open[$tpl['md_id']] || $_LOCKED) { ?>
<TR>
	<TD class="steel1" colspan="9">
		<A name="<?=$tpl['md_id']?>">
		<TABLE cellpadding="2" cellspacing="0" border="0" width="100%">
			<TR>
				<TD width="5%" align="right" valign="top" class="<?=$tpl['class']?>">					
					<? if (!$_LOCKED || !$sd_open[$tpl['md_id']]) { ?>
					<A href="<?=$PHP_SELF?>?cmd=open&open_close_id=<?=$tpl['md_id']?>#<?=$tpl['md_id']?>">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgrau.gif" border="0" align="abstop">
					</A>
					<? } else { ?>
					<A href="<?=$PHP_SELF?>?cmd=close&open_close_id=<?=$tpl['md_id']?>#<?=$tpl['md_id']?>">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgraurunt.gif" border="0" align="abstop">
					</A>
					<? } ?>
				</TD>
				<TD width="20%" nowrap class="<?=$tpl['class']?>">
					<? if (!$_LOCKED || !$sd_open[$tpl['md_id']]) { ?>
					<A class="tree" href="<?=$PHP_SELF?>?cmd=open&open_close_id=<?=$tpl['md_id']?>#<?=$tpl['md_id']?>">
					<? } else { ?>
					<A class="tree" href="<?=$PHP_SELF?>?cmd=close&open_close_id=<?=$tpl['md_id']?>#<?=$tpl['md_id']?>">
					<? } ?>
						<FONT size="-1">
							<?=$tpl['date']?>
						</FONT>
					</A>
				</TD>
				<? if ($GLOBALS['RESOURCES_ENABLE']) { ?>
				<TD width="35%" nowrap class="<?=$tpl['class']?>">
					<FONT size="-1">
						<B><?=_("Raum:")?></B>
						<?=$tpl['room']?>
					</FONT>
					<? /* rotes Ausrufungszeichen */?>
					<? if ($tpl['ausruf']) { ?>
					<A href="javascript:alert('<?=$tpl['ausruf']?>')">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/ausrufezeichen_rot.gif" alt="<?=$tpl['ausruf']?>" border="0" align="absmiddle">
					</A>
					<? } ?>
				</TD>
				<TD width="20%" nowrap class="<?=$tpl['class']?>">
					<FONT size="-1">
						<B><?=_("Raumanfragen:")?></B>
						<?=$tpl['anfragen']?>
					</FONT>
				</TD>
				<? } else { ?>
				<TD width="75%" class="<?=$tpl['class']?>">&nbsp;</TD>
				<? } ?>
				<TD width="20%" nowrap class="<?=$tpl['class']?>" align="right">
					<? if (!$_LOCKED) { ?>
					<A href="<?=$PHP_SELF?>?cmd=deleteCycle&cycle_id=<?=$tpl['md_id']?>">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/trash.gif" border="0" align="absmiddle">
					</A>
					<? } ?>
				</TD>
			</TR>
		</TABLE>
	</TD>
<?
} else { ?>
<TR>
	<TD class="steel1" colspan="9">
		<A name="<?=$tpl['md_id']?>">
		<TABLE cellpadding="2" cellspacing="0" border="0" width="100%">
			<TR>
				<TD width="5%" align="right" valign="top" class="<?=$tpl['class']?>" nowrap>
					<A href="<?=$PHP_SELF?>?cmd=close&open_close_id=<?=$tpl['md_id']?>#<?=$tpl['md_id']?>">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/forumgraurunt.gif" border="0" valign="absmiddle">
					</A>
				</TD>
				<TD width="90%" nowrap class="<?=$tpl['class']?>">
					<FORM action="<?=$PHP_SELF?>" method="post" name="EditCycle">
						<FONT size="-1"><B>
							<SELECT name="day">
								<OPTION value="1"<?=($tpl['mdDayNumber']=='1') ? 'selected="selected"' : ''?>>Montag</OPTION>
								<OPTION value="2"<?=($tpl['mdDayNumber']=='2') ? 'selected="selected"' : ''?>>Dienstag</OPTION>
								<OPTION value="3"<?=($tpl['mdDayNumber']=='3') ? 'selected="selected"' : ''?>>Mittwoch</OPTION>
								<OPTION value="4"<?=($tpl['mdDayNumber']=='4') ? 'selected="selected"' : ''?>>Donnerstag</OPTION>
								<OPTION value="5"<?=($tpl['mdDayNumber']=='5') ? 'selected="selected"' : ''?>>Freitag</OPTION>
								<OPTION value="6"<?=($tpl['mdDayNumber']=='6') ? 'selected="selected"' : ''?>>Samstag</OPTION>
								<OPTION value="0"<?=($tpl['mdDayNumber']=='0') ? 'selected="selected"' : ''?>>Sonntag</OPTION>
							</SELECT>,
							<INPUT type="text" name="start_stunde" maxlength="2" size="2" value="<?=leadingZero($tpl['mdStartHour'])?>"> :
							<INPUT type="text" name="start_minute" maxlength="2" size="2" value="<?=leadingZero($tpl['mdStartMinute'])?>">
							bis
							<INPUT type="text" name="end_stunde" maxlength="2" size="2" value="<?=leadingZero($tpl['mdEndHour'])?>"> :
							<INPUT type="text" name="end_minute" maxlength="2" size="2" value="<?=leadingZero($tpl['mdEndMinute'])?>"> Uhr
							<?=Termin_Eingabe_javascript(2,0,0,$tpl['mdStartHour'],$tpl['mdStartMinute'],$tpl['mdEndHour'],$tpl['mdEndMinute']);?>
							&nbsp;&nbsp;Beschreibung: <INPUT type="text" name="description" value="<?=$tpl['mdDescription']?>">
							&nbsp;&nbsp;<INPUT type="image" name="editCycle" align="absmiddle" <?=makebutton('uebernehmen', 'src')?>>
							<INPUT type="hidden" name="cycle_id" value="<?=$tpl['md_id']?>">
						</B></FONT>
					</FORM>
				</TD>
				<TD width="5%" nowrap class="<?=$tpl['class']?>" align="right" nowrap>
					<A href="<?=$PHP_SELF?>?cmd=deleteCycle&cycle_id=<?=$tpl['md_id']?>">
						<IMG src="<?=$GLOBALS['ASSETS_URL']?>images/trash.gif" border="0" valign="absmiddle">
					</A>
				</TD>
			</TR>
		</TABLE>
	</TD>
<?
}
unset($tpl);
