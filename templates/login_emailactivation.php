<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="topic" colspan=3 align="left">
 <img src="<?=$GLOBALS['ASSETS_URL']?>/images/mailnachricht.gif" border="0" align="absmiddle">
 <b><?= _('E-Mail Aktivierung') ?></b>
</td></tr>
<tr><td style="background-color: #fff; padding: 1.5em;">
<?= _('Sie haben Ihre E-Mail Adresse ge�ndert. Um diese frei zu schalten m�ssen Sie den Ihnen an Ihre neue Adresse zugeschickten Aktivierungs Schl�ssel im unten stehenden Eingabefeld eintragen.'); ?>
<br><form action="activate_email.php" method="post">
 <input name="key" />
 <input name="uid" type="hidden" value="<?= $uid ?>" />
 <?= makeButton("abschicken","input") ?></form><br><br>
</td></tr></table></div><br />


<div align="center">
<table width="70%" border=0 cellpadding=0 cellspacing=0>
<tr><td class="topic" colspan=3 align="left">
 <img src="<?=$GLOBALS['ASSETS_URL']?>/images/mailnachricht.gif" border="0" align="absmiddle">
 <b><?= _('E-Mail Aktivierung neu senden') ?></b>
</td></tr>
<tr><td style="background-color: #fff; padding: 1.5em;">
<?= _('Sollten Sie keine E-Mail erhalten haben, k�nnen Sie sich einen neuen Aktivierungsschl�ssel zuschicken lassen. Geben Sie dazu Ihre gew�nschte E-Mail Adresse unten an:'); ?>
<form action="activate_email.php" method="post">
<input type="hidden" name="uid" value="<?= $uid ?>" />
<table><tr><td><?= _('E-Mail:') ?></td><td><input name="email1" /></td></tr>
<tr><td><?= _('Wiederholung:') ?></td><td><input name="email2" /></td></tr></table>
<?= makeButton("abschicken", "input") ?>
</form>
</td></tr></table></div><br />


