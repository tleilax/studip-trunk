<?
if (get_config("EXTERNAL_HELP")) {
	$help_url=format_help_url("Basis.VeranstaltungenVerwaltenGruppen");
} else {
	$help_url="help/index.php?referrer_page=admin_statusgruppe.php";
}
?>
<tr>
	<td class="blank">
		<br/>
		<blockquote>
			<?= _("Es sind noch keine Gruppen oder Funktionen angelegt worden.") ?><br/>
			<?= sprintf(_("Um f�r diesen Bereich Gruppen anzulegen, klicken Sie auf %sneue Gruppe anlegen%s in der Infobox."), '<u>', '</u>') ?><br/>
			<?=  _("Wenn Sie Gruppen angelegt haben, k�nnen Sie diesen Personen zuordnen. Jeder Gruppe k�nnen beliebig viele Personen zugeordnet werden. Jede Person kann beliebig vielen Gruppen zugeordnet werden.") ?><br/><br/>
			<?= sprintf(_("Lesen Sie weitere Bedienungshinweise in der %sHilfe%s nach!"), "<a href=\"".$help_url."\">", "</a>") ?>
		</blockquote>
</tr>

<?
$msg = "info�" . _("Es sind noch keine Gruppen oder Funktionen angelegt worden.")
. "<br>" . _("Um für diesen Bereich Gruppen oder Funktionen anzulegen, nutzen Sie bitte die obere Zeile!")
. "<br><br>" . _("Mit dem Feld 'Gruppengr��e' haben Sie die M�glichkeit, die Sollst�rke f�r eine Gruppe festzulegen. Dieser Wert wird nur f�r die Anzeige benutzt - es k�nnen auch mehr Personen eingetragen werden.")
. "<br>" . _("Wenn Sie Gruppen angelegt haben, k�nnen Sie diesen Personen zuordnen. Jeder Gruppe k�nnen beliebig viele Personen zugeordnet werden. Jede Person kann beliebig vielen Gruppen zugeordnet werden.")
. "<br><br>" . sprintf(_("Lesen Sie weitere Bedienungshinweise in der %sHilfe%s nach!"), "<a href=\"".$help_url."\">", "</a>")
. "�";
