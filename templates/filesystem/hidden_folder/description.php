<div><?= htmlReady($type) ?>:</div>
<br/>
<label>
    <div><?= _("Ein unsichtbarer Ordner, welcher nur vom Dozenten und Tutoren gesehen werden kann.") ?></div>
    <div><?= _("Der Ordner l�sst sich auch f�r Studenten sichtbar schalten.") ?></div>
</label>
<label>
    <input disabled type="checkbox" value="1" <?= $folder->isReadable() ? "checked" : "" ?>>
    <b>r</b> - <?= _("Lesen (Dateien k�nnen heruntergeladen werden)") ?>
</label>
<br>
<label>
    <input disabled type="checkbox" value="1" <?= $folder->isWritable() ? "checked" : "" ?>>
    <b>w</b> - <?= _("Schreiben (Dateien k�nnen heraufgeladen werden)") ?>
</label>
<br>
<label>
    <input disabled type="checkbox" value="1" <?= $folder->isVisible() ? "checked" : "" ?>>
    <b>x</b> - <?= _("Sichtbarkeit (Ordner wird angezeigt)") ?>
</label>


